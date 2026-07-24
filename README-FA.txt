راه‌اندازی رایگان مانیتور سرورهای Iran Vmess
============================================

این بسته دو بخش دارد:

1) مخزن GitHub:
   GitHub Actions هر پنج دقیقه لینک ساب را با Xray Checker بررسی می‌کند.
   فقط نام سرور، آنلاین/آفلاین و پینگ عمومی می‌شود.
   لینک ساب داخل GitHub Secret قرار می‌گیرد و در فایل‌ها نوشته نمی‌شود.

2) فایل ivsupport/server-status.php:
   پس از فعال‌شدن GitHub Pages این فایل را روی هاست ivsupport.ir می‌گذاری
   تا نتیجه در دامنه خودت نمایش داده شود.

فایل‌های لازم برای مخزن GitHub:
--------------------------------
.github/workflows/monitor.yml
scripts/build_status.py
site/index.html

مراحل کلی:
----------
1. در GitHub یک Repository عمومی بساز.
2. سه پوشه و فایل بالا را با همین ساختار وارد Repository کن.
3. وارد Settings > Secrets and variables > Actions شو.
4. یک Repository secret با نام SUBSCRIPTION_URL بساز.
5. لینک ساب را فقط داخل مقدار Secret قرار بده.
6. وارد Settings > Pages شو.
7. Source را روی GitHub Actions بگذار.
8. وارد Actions شو و Workflow با نام Iran Vmess Server Monitor را دستی Run کن.
9. پس از موفقیت، آدرس Pages شبیه این می‌شود:
   https://USERNAME.github.io/REPOSITORY/
10. نتیجه JSON:
   https://USERNAME.github.io/REPOSITORY/status.json

نمایش داخل ivsupport.ir:
------------------------
1. فایل ivsupport/server-status.php را باز کن.
2. مقدار STATUS_JSON_URL را با آدرس واقعی status.json عوض کن.
3. فایل را داخل public_html هاست آپلود کن.
4. صفحه:
   https://ivsupport.ir/server-status.php

نکته امنیتی:
-------------
مخزن باید عمومی باشد تا GitHub Actions و Pages رایگان بمانند،
اما لینک ساب نباید داخل هیچ فایل، Commit یا تصویر قرار بگیرد.
لینک را فقط به‌صورت Secret ثبت کن.

نکات نسخه اصلاح‌شده:
--------------------
- لینک ساب اختصاصی مانیتور را فقط در Secret با نام SUBSCRIPTION_URL قرار بده.
- نیازی به WEB_PUBLIC نیست؛ خروجی امن /api/v1/public/proxies به‌صورت پیش‌فرض قابل خواندن است.
- برای مانیتور بهتر است سرویس آزمایشی تاریخ انقضای کوتاه نداشته باشد.
- حجم پیشنهادی برای 20 نود و تست هر 5 دقیقه حداقل 10 گیگابایت در ماه است.
- نام کانفیگ‌ها همان چیزی است که مشتری در صفحه وضعیت می‌بیند؛ نام‌ها را مرتب و عمومی انتخاب کن.
