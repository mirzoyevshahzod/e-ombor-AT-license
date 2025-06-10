<?php

namespace App\Http\Controllers;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransitExport;
use Illuminate\Http\Request;
use Exception;

class EomborController extends Controller
{
    public function loginPage(Request $request)
    {
        set_time_limit(0); // Cheksiz vaqtga ruxsat beradi
        ini_set('memory_limit', '4096M'); // Xotira limitini oshirish

        $driver = null;

        try {
            // 1. Foydalanuvchi kiritgan tranzit ID'ni olish
            $startTransitId = $request->input('transit_id');
            if (!$startTransitId) {
                return response()->json([
                    'success' => false,
                    'error' => 'Tranzit ID kiritilmagan.'
                ], 400, [], JSON_UNESCAPED_UNICODE);
            }

            // 2. Selenium WebDriver'ni ishga tushirish
            $host = 'http://localhost:4444/wd/hub';
            $capabilities = DesiredCapabilities::chrome();
            $driver = RemoteWebDriver::create($host, $capabilities);

            // 3. Saytga kirish
            $driver->get('https://e-ombor.customs.uz/');

            // 4. Kirish tugmasi
            $driver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('lload'))
            )->click();

            // 5. Sertifikat tanlash
            $driver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('.dropdown-toggle'))
            )->click();

            $driver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('a[onclick^="uiComboSelect"]'))
            )->click();

            // 6. "Kirish" tugmasi va modal
            $driver->findElement(WebDriverBy::cssSelector('a.sign-in'))->click();

            // Kutish: modal yoki URL
            try {
                $driver->wait(10)->until(
                    WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.modal'))
                );
            } catch (Exception $e) {
                // modal chiqmasa, URL o‘zgarishini kutamiz
            }

            // 7. URL o‘zgarishini kutish
            $driver->wait(60)->until(function ($driver) {
                return strpos($driver->getCurrentURL(), '/uzOmbor/indexUzOmbor.jsp') !== false;
            });

            // 8. Qidiruv menyusiga kirish
            $driver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('a.has-arrow'))
            )->click();

            // 9. Tranzit bo‘limiga o‘tish
            $driver->wait(10)->until(
                WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('a[onclick="MainUzOmbor(507)"]'))
            )->click();

            // 10. Ma'lumotlarni yig‘ish uchun massiv
            $allData = [];

            // 11. Tranzit ID'dan boshlab 100 ta elementni qidirish
            for ($i = 0; $i < 100; $i++) {
                // Dinamik tranzit ID yaratish
                $currentTransitId = $this->generateNextTransitId($startTransitId, $i);

                // 12. ATRW inputga qiymat kiritish
                $atrwInput = $driver->wait(10)->until(
                    WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('ATRW'))
                );
                $atrwInput->clear();
                $atrwInput->sendKeys($currentTransitId);

                // 13. Qidiruv tugmasi
                $searchButton = $driver->wait(10)->until(
                    WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('button[onclick="qidirishEtranzit()"]'))
                );
                $searchButton->click();

                // Sahifa yuklanishini kutish
                sleep(7); // 7 soniya kutish


                // 14. Jadval ma'lumotlarini kutish
                $driver->wait(15)->until(
                    WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('#win table'))
                );

                $rows = $driver->findElements(WebDriverBy::cssSelector('#win table tbody tr'));

                foreach ($rows as $rowIndex => $row) {
                    $cells = $row->findElements(WebDriverBy::cssSelector('td'));
                    $count = count($cells);
                    echo "Qator #{$rowIndex}: {$count} ta ustun topildi\n";

                    // "Жадвал бўш" holatini tekshirish
                    if ($count === 1 && trim($cells[0]->getText()) === "Жадвал бўш") {
                        echo "⚠️ Jadvalda faqat bitta ustun topildi: \"Жадвал бўш\"\n";
                        continue;
                    }

                    // if ($count !== 11) {
                    //     echo "⚠️ Ogohlantirish: Kutilgan 11 ta ustun emas, {$count} ta topildi.\n";
                    //     continue;
                    // }

                    $allData[] = [
                        'document_number'   => isset($cells[0]) ? trim($cells[0]->getText()) ?? null : null,
                        'custom_code'       => isset($cells[1]) ? trim($cells[1]->getText()) ?? null : null,
                        'custom_date'       => isset($cells[2]) ? trim($cells[2]->getText()) ?? null : null,
                        'tebhn_number'      => isset($cells[3]) ? trim($cells[3]->getText()) ?? null : null,
                        'transport_number'  => isset($cells[4]) ? trim($cells[4]->getText()) ?? null : null,
                        'gross_weight'      => isset($cells[5]) ? trim($cells[5]->getText()) ?? null : null,
                        'recipient_name'    => isset($cells[6]) ? trim($cells[6]->getText()) ?? null : null,
                        'delivery_post'     => isset($cells[7]) ? trim($cells[7]->getText()) ?? null : null,
                        'delivery_date'     => isset($cells[8]) ? trim($cells[8]->getText()) ?? null : null,
                        'arrival_place'     => isset($cells[9]) ? trim($cells[9]->getText()) ?? null : null,
                        'status'            => isset($cells[10]) ? trim($cells[10]->getText()) ?? null : null,
                    ];
                }
            }

            // 15. Agar ma'lumot topilmasa
            if (empty($allData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hech qanday ma\'lumot topilmadi.'
                ], 404, [], JSON_UNESCAPED_UNICODE);
            }

            // 16. Ma'lumotlarni Excel faylida qaytarish
            return Excel::download(new TransitExport($allData), 'transit_data.xlsx');
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        } finally {
            if ($driver) {
                $driver->quit();
            }
        }
    }

    // Tranzit ID'dan keyingi ID'larni generatsiya qilish
    private function generateNextTransitId($startTransitId, $increment)
    {
        // Tranzit ID formatini tahlil qilish (masalan: AT20250346677)
        $prefix = substr($startTransitId, 0, 2); // "AT"
        $year = substr($startTransitId, 2, 4);   // "2025"
        $number = (int) substr($startTransitId, 6); // "0346677"

        // Raqamni oshirish
        $newNumber = $number + $increment;
        $newNumberPadded = str_pad($newNumber, 7, '0', STR_PAD_LEFT); // 7 xonali qilish

        // Yangi tranzit ID'ni qaytarish
        return $prefix . $year . $newNumberPadded;
    }
}
