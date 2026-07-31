# Laravel Staff Export Loyihasi (Telegram & Queue)

Ushbu loyiha xodimlar (Staff) ma'lumotlarini Excel formatida eksport qilish va tayyor faylni fon rejimida (Queue orqali) Telegram botga yuborish vazifasini bajaradi.

## Loyihani ishga tushirish qadamlari

Mentor, loyihani kompyuteringizda muvaffaqiyatli sinab ko'rishingiz uchun quyidagi ketma-ketlikni bajaring:

### 1. Bog'liqliklarni o'rnatish (Dependencies)
Terminalda loyiha papkasiga kiring va quyidagi buyruqlarni bajaring:
```bash
composer install
```

### 2. Muhit faylini (.env) sozlash
`.env.example` faylidan nusxa olib, yangi `.env` faylini yarating va unga maxsus kalit generatorini ishga tushiring:
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Ma'lumotlar bazasi (Database)
`.env` faylingizda mahalliy ma'lumotlar bazasi (MySQL/PostgreSQL) sozlamalarini kiriting (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`). So'ngra migratsiyalarni ishga tushiring:
```bash
php artisan migrate
```
*(Agar xohlasangiz, test uchun xodimlar ro'yxatini shakllantirish uchun seederlarni ham ishga tushirishingiz mumkin: `php artisan db:seed`)*

### 4. Telegram Bot va Chat ID sozlamalari (Tayyor test rejimi)
Sizga qulay bo'lishi uchun quyida tayyor test boti va chat ID ma'lumotlarini qoldiryapman. Bularni `.env` faylingizning eng pastki qismiga joylashtiring:

```ini
TELEGRAM_BOT_TOKEN="sizning_bot_tokeningiz_bu_yerda"
CHAT_ID="sizning_chat_id_ingiz_bu_yerda"

# Navbat (Queue) ulanishi
QUEUE_CONNECTION=database
```
*Eslatma: Eksport qilingan Excel fayli to'g'ridan-to'g'ri ushbu Telegram chatga yuboriladi.*

### 5. MUHIM: Navbat ishchisini (Queue Worker) ishga tushirish
Loyiha fonsi topshiriqlardan (`ExportStaffExcelJob`) foydalangani sababli, eksport jarayoni ishlashi uchun alohida terminal oynasida navbat ishchisini faollashtirishingiz shart:
```bash
php artisan queue:work
```

### 6. Serverni sozlash va sinab ko'rish
Asosiy terminalda Laravel lokal serverini yoqing:
```bash
php artisan serve
```

Endi brauzeringiz yoki Postman orqali quyidagi manzilga so'rov yuborib (yoki API controllerga bog'langan marshrutni chaqirib) tizimni sinab ko'rishingiz mumkin:
`http://127.0.0`
