<p align="center">
<a href="https://e-ombor.customs.uz/" target="_blank">
<img src="https://upload.wikimedia.org/wikipedia/commons/8/84/Flag_of_Uzbekistan.svg" width="200" alt="Uzbekistan Logo">
</a>
</p>

<p align="center">
<b>E-Ombor AT License Scraping (Automobile)</b>
</p>

<p align="center">
<a href="#"><img src="https://img.shields.io/badge/PHP-8.x-blue" alt="PHP"></a>
<a href="#"><img src="https://img.shields.io/badge/Laravel-Framework-red" alt="Laravel"></a>
<a href="#"><img src="https://img.shields.io/badge/Selenium-WebDriver-green" alt="Selenium"></a>
<a href="#"><img src="https://img.shields.io/badge/Export-Excel-success" alt="Excel"></a>
<a href="#"><img src="https://img.shields.io/badge/License-MIT-lightgrey" alt="License"></a>
</p>

---

## About Project

**E-Ombor AT Scraping** — bu loyiha e-ombor.customs.uz saytiga kirib, **AT (avtomobil) tranzit/litsenziya raqamlari** orqali ma’lumotlarni qidiradi va Excel faylga eksport qiladi.

Loyiha:
- Saytga Selenium orqali login qiladi  
- AT tranzit ID larni avtomatik generatsiya qiladi  
- Har bir ID bo‘yicha ma’lumotni qidiradi  
- INN va kompaniya nomini ajratadi  
- Excel faylga saqlaydi  

---

## Features

- 🚛 AT (avtomobil) tranzit qidiruv  
- 🔢 ID range generatsiya  
- 📊 Excel export (12 ustun)  
- 🤖 Selenium orqali scraping  
- ⚠️ Alert handling (ma’lumot topilmasa skip qiladi)  
- 🧠 INN parsing (recipient ichidan ajratadi)  
- 🧹 Company name tozalash (quotes olib tashlanadi)  

---

## Tech Stack

- Laravel (PHP)
- Selenium WebDriver
- ChromeDriver
- PhpSpreadsheet

---

## Installation

```bash
git clone https://github.com/your-username/e-ombor-at-scraping.git
cd e-ombor-at-scraping
composer install
cp .env.example .env
php artisan key:generate
```

---

## Usage

### 1. Selenium serverni ishga tushiring

```bash
java -jar selenium-server.jar
```

yoki Docker:

```bash
docker run -d -p 4444:4444 selenium/standalone-chrome
```

---

### 2. Scrapingni ishga tushirish

```bash
php artisan scrape:eombor AT2025000001 50 AT2025000050
```

### Parametrlar:
- `start_id` → boshlang‘ich AT ID  
- `count` → nechta ID tekshiriladi  
- `end_id` → fayl nom uchun  

---

## Output

Excel fayl:

```
storage/app/e-ombor-STARTID-ENDID.xlsx
```

---

## Excel Columns

- Document Number  
- Custom Code  
- Custom Date  
- TEBHN Number  
- Transport Number  
- Gross Weight  
- INN  
- Recipient Name  
- Delivery Post  
- Delivery Date  
- Arrival Place  
- Status  

---

## How It Works

- Selenium orqali saytga kiriladi  
- Sertifikat tanlanadi va login qilinadi  
- Tranzit bo‘limiga o‘tiladi  
- AT ID lar bo‘yicha qidiruv qilinadi  
- Alert chiqsa → skip qilinadi  
- Jadvaldan ma’lumot olinadi  
- INN va company ajratiladi  
- Company nomi tozalanadi  
- Excel faylga yoziladi  

---

## Notes

- ⚠️ Login bosqichi qo‘lda tasdiqlanishi mumkin  
- Selenium ishlashi shart (`localhost:4444`)  
- Ba’zi ID lar mavjud bo‘lmasligi mumkin  
- Internet tezligi scrapingga ta’sir qiladi  

---

## Contributing

Pull requestlar ochishingiz mumkin 🚀  

---

## License

MIT License
