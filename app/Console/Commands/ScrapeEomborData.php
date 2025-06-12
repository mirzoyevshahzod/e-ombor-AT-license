<?php

namespace App\Console\Commands;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Exception;

class ScrapeEomborData extends Command
{
    protected $signature = 'scrape:eombor';
    protected $description = 'Scrape e-ombor data and export to Excel';

    public function handle()
    {
        $this->info('Starting e-ombor data scraping...');

        $driver = null;
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $rowIndex = 1;
        $timestamp = now()->format('Ymd_His'); // Masalan: 20250611_1806
        $filePath = storage_path("app/transit_data_{$timestamp}.xlsx");

        try {
            // 1. Selenium WebDriver'ni ishga tushirish
            $host = 'http://localhost:4444/wd/hub';
            $capabilities = DesiredCapabilities::chrome();
            $driver = RemoteWebDriver::create($host, $capabilities);

            // 2. Saytga kirish
            $driver->get('https://e-ombor.customs.uz/');
            $this->info('Saytga kirish muvaffaqiyatli');

            // 3. Kirish tugmasi
            $driver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('lload'))
            )->click();

            // 4. Sertifikat tanlash
            $driver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('.dropdown-toggle'))
            )->click();

            $driver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('a[onclick^="uiComboSelect"]'))
            )->click();

            // 5. "Kirish" tugmasi va modal
            $driver->findElement(WebDriverBy::cssSelector('a.sign-in'))->click();

            // Kutish: modal yoki URL
            try {
                $driver->wait(10)->until(
                    WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.modal'))
                );
            } catch (Exception $e) {
                // modal chiqmasa, URL o‘zgarishini kutamiz
            }

            // 6. URL o‘zgarishini kutish
            $driver->wait(60)->until(function ($driver) {
                return strpos($driver->getCurrentURL(), '/uzOmbor/indexUzOmbor.jsp') !== false;
            });

            // 7. Qidiruv menyusiga kirish
            $driver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('a.has-arrow'))
            )->click();

            // 8. Tranzit bo‘limiga o‘tish
            $driver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('a[onclick="MainUzOmbor(507)"]'))
            )->click();

            // 9. Sarlavhalarni yozish
            $headings = [
                'Document Number',
                'Custom Code',
                'Custom Date',
                'TEBHN Number',
                'Transport Number',
                'Gross Weight',
                'Recipient Name',
                'Delivery Post',
                'Delivery Date',
                'Arrival Place',
                'Status'
            ];
            foreach ($headings as $colIndex => $heading) {
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
                $sheet->setCellValue($columnLetter . $rowIndex, $heading);
            }
            $rowIndex++;

            // 10. Boshlang‘ich tranzit ID’sini ko‘rsatish uchun so‘raladi
            $startTransitId = $this->ask('Iltimos, boshlang‘ich tranzit ID kiriting (masalan, AT20250363000):');
            if (!$startTransitId) {
                throw new Exception('Tranzit ID kiritilmagan.');
            }

            // 11. 200 ta datani bosqichma-bosqich yig‘ish
            $chunkSize = 50; // Har safar 20 ta qidiruv
            for ($i = 0; $i < 3500; $i += $chunkSize) {
                $endIndex = min($i + $chunkSize, 3500);
                for ($j = $i; $j < $endIndex; $j++) {
                    $currentTransitId = $this->generateNextTransitId($startTransitId, $j);

                    $atrwInput = $driver->wait(10)->until(
                        WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('ATRW'))
                    );
                    $atrwInput->clear();
                    $atrwInput->sendKeys($currentTransitId);

                    $searchButton = $driver->wait(10)->until(
                        WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('button[onclick="qidirishEtranzit()"]'))
                    );
                    $searchButton->click();

                    sleep(5);

                    $driver->wait(15)->until(
                        WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#win table'))
                    );

                    $rows = $driver->findElements(WebDriverBy::cssSelector('#win table tbody tr'));

                    foreach ($rows as $row) {
                        $cells = $row->findElements(WebDriverBy::cssSelector('td'));
                        $count = count($cells);
                        $this->info("Qator #{$j}: {$count} ta ustun topildi");

                        if ($count === 1 && trim($cells[0]->getText()) === "Жадвал бўш") {
                            $this->warn("⚠️ Jadvalda faqat bitta ustun topildi: \"Жадвал бўш\"");
                            continue;
                        }

                        foreach ($cells as $colIndex => $cell) {
                            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
                            $sheet->setCellValue($columnLetter . $rowIndex, trim($cell->getText()) ?? null);
                        }
                        $rowIndex++;
                    }
                }
            }

            // 12. Excel faylini saqlash
            $writer = new Xlsx($spreadsheet);
            $writer->save($filePath);

            if (!file_exists($filePath) || filesize($filePath) == 0) {
                throw new Exception('Fayl yozishda xatolik yuzaga keldi: ' . $filePath);
            }

            $this->info('Fayl muvaffaqiyatli saqlandi: ' . $filePath);
            $this->info('Faylni quyidagi URL orqali ko‘rish mumkin: ' . Storage::url("transit_data_{$timestamp}.xlsx"));
        } catch (Exception $e) {
            $this->error('Xatolik yuzaga keldi: ' . $e->getMessage());
        } finally {
            if ($driver) {
                $driver->quit();
            }
            if (isset($spreadsheet)) {
                $spreadsheet->disconnectWorksheets();
                unset($spreadsheet);
            }
        }
    }

    // Tranzit ID'dan keyingi ID'larni generatsiya qilish
    private function generateNextTransitId($startTransitId, $increment)
    {
        $prefix = substr($startTransitId, 0, 2); // "AT"
        $year = substr($startTransitId, 2, 4);   // "2025"
        $number = (int)substr($startTransitId, 6); // "0346677"

        $newNumber = $number + $increment;
        $newNumberPadded = str_pad($newNumber, 7, '0', STR_PAD_LEFT); // 7 xonali qilish

        return $prefix . $year . $newNumberPadded;
    }
}