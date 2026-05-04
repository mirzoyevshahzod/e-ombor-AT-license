<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;

class EomborLogin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eombor:login';

    /**
     * The console command description.
     *
     * @var string
     */

    protected $description = 'Login to e-ombor and save cookies';


    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Opening browser for manual login...');

        $driver = RemoteWebDriver::create(
            'http://localhost:4444/wd/hub',
            DesiredCapabilities::chrome()
        );

        $driver->get('https://e-ombor.customs.uz/');

        $this->warn('👉 Login qiling (sertifikat tanlab). Tugagach ENTER bosing...');
        readline();

        $cookies = $driver->manage()->getCookies();

        file_put_contents(
            storage_path('app/eombor_cookies.json'),
            json_encode($cookies, JSON_PRETTY_PRINT)
        );

        $this->info('✅ Cookies saved successfully.');

        $driver->quit();
    }
}
