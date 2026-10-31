# دليل النشر على استضافة DirectAdmin

> الهدف: تشغيل المنصة على الدومين والاستضافة الحالية للتجربة الكاملة.
> سنفترض أن دومين المنصة `platform.sd` واسم مستخدم الاستضافة `USER`. غيّرهما لقيمك الفعلية.

## 1. المتطلبات في DirectAdmin
- **PHP 8.3** (من *Domain Setup ← PHP Version Selector* أو *Select PHP version* في CloudLinux).
- الإضافات: `pdo_mysql`, `mbstring`, `intl`, `gd`, `fileinfo`, `bcmath`, `openssl`, `curl`, `zip`, `exif`.
- في `php.ini`: `upload_max_filesize = 10M` و `post_max_size = 12M` و `memory_limit = 256M`.
- **SSH مفعّل** (مستحسن). إن لم يكن متاحاً، راجع القسم 9.
- قاعدة بيانات MySQL أو MariaDB: من *MySQL Management ← Create new Database*. احفظ الاسم والمستخدم وكلمة المرور.

## 2. رفع الكود
```bash
ssh USER@platform.sd
cd ~/domains/platform.sd
git clone https://github.com/mamon7136/momen.git app
cd app
composer install --no-dev --optimize-autoloader
```

## 3. توجيه الدومين إلى مجلد `public`
DirectAdmin يخدم الملفات من `public_html`. نجعله رابطاً رمزياً إلى `app/public`:
```bash
cd ~/domains/platform.sd
mv public_html public_html_old
ln -s app/public public_html
```
> الأفضل أمنياً ألا يكون مجلد `app` (وفيه `.env`) داخل `public_html`. الطريقة أعلاه تحقق ذلك.

## 4. ملف البيئة `.env`
```bash
cp .env.example .env
php artisan key:generate
nano .env
```
القيم المهمة:
```dotenv
APP_NAME=Momen
APP_ENV=production
APP_DEBUG=false
APP_URL=https://platform.sd
APP_LOCALE=ar

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=USER_momen
DB_USERNAME=USER_momen
DB_PASSWORD=********

PLATFORM_NAME="مومن"
PLATFORM_DOMAIN=platform.sd
PLATFORM_SCHEME=https
# ابدأ بـ path لو لم تجهز الـ Wildcard بعد، ثم حوّلها إلى host
STOREFRONT_MODE=path
CUSTOM_DOMAIN_TARGET=stores.platform.sd

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

ADMIN_EMAIL=you@platform.sd
ADMIN_PHONE=2499XXXXXXXX
ADMIN_PASSWORD=كلمة-مرور-قوية

BILLING_BANK_NAME="بنك الخرطوم"
BILLING_ACCOUNT_NAME="اسم حسابك"
BILLING_ACCOUNT_NUMBER=XXXXXXX
```

## 5. تهيئة قاعدة البيانات والملفات
```bash
php artisan migrate --force
php artisan db:seed --class=PlatformSeeder --force      # الباقات، الثيمات، حساب المدير
# اختياري للتجربة: متجر تجريبي "demo" بمنتجات وكوبون
SEED_DEMO=1 php artisan db:seed --force
php artisan storage:link
chmod -R 775 storage bootstrap/cache
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

## 6. Cron Job (ضروري للطوابير ورسائل واتساب والاشتراكات)
من *Advanced Features ← Cron Jobs* أضف مهمة **كل دقيقة**:
```
* * * * * /usr/local/bin/php /home/USER/domains/platform.sd/app/artisan schedule:run >> /dev/null 2>&1
```
(تأكد من مسار PHP 8.3 بالأمر `which php` أو `ls /usr/local/php83/bin/php`).

## 7. روابط المتاجر الفرعية `store.platform.sd` (Wildcard)
**الخيار A — Cloudflare (الموصى به، ويوفر SSL مجاني للنطاقات الفرعية):**
1. انقل الـ Nameservers للدومين إلى Cloudflare.
2. أضف سجلات DNS: `A @ → IP_السيرفر` (Proxied) و `A * → IP_السيرفر` (Proxied).
3. في SSL/TLS اختر **Full**. شهادة Universal SSL تغطي `*.platform.sd`.
4. في DirectAdmin اجعل Apache يقبل أي نطاق فرعي: *Custom HTTPD Configurations ← platform.sd* وأضف `ServerAlias *.platform.sd` (أو اطلب من الدعم تفعيل Wildcard subdomain).
5. غيّر `STOREFRONT_MODE=host` ثم نفّذ `php artisan config:cache`.

**الخيار B — DirectAdmin فقط:** أنشئ Subdomain باسم `*` (إن سمح المدير بذلك)، وأصدر شهادة Let's Encrypt Wildcard (تتطلب أن يكون DNS على نفس السيرفر).

> **قبل جاهزية الـ Wildcard** تعمل المتاجر فوراً على `https://platform.sd/s/store-slug`.

## 8. الدومينات الخاصة للتجار
1. التاجر يضيف `shop.example.com` من *الإعدادات والدومين*، ويضيف عند مزوّده `CNAME → stores.platform.sd` (أو TXT للتحقق).
2. اجعل `stores.platform.sd` يشير لـ IP السيرفر.
3. لكي يخدم Apache الدومين:
   - **يدوياً في البداية**: DirectAdmin ← *Domain Pointers* ← أضف الدومين كـ Alias لـ `platform.sd`، ثم فعّل له SSL.
   - **عند النمو**: Cloudflare for SaaS (Custom Hostnames). أول 100 دومين مجاناً، وSSL آلي بلا تدخل يدوي.
4. التاجر يضغط "تحقق الآن"، فيتفعّل الدومين ويُحدَّث الكاش تلقائياً.

## 9. بدون SSH؟
1. على جهازك: `composer install --no-dev -o` ثم اضغط المشروع ZIP (مع `vendor`).
2. ارفعه عبر *File Manager* إلى `domains/platform.sd/app` وفك الضغط.
3. نفّذ الخطوات 3 إلى 5 عبر سكربت مؤقت أو اطلب من الدعم تنفيذ أوامر `artisan`.

## 10. واتساب (WhatsApp Cloud API)
1. أنشئ تطبيقاً في [Meta for Developers](https://developers.facebook.com/) ← WhatsApp ← أضف رقم الأعمال.
2. في `.env`:
```dotenv
WHATSAPP_DRIVER=cloud
WHATSAPP_PHONE_NUMBER_ID=...
WHATSAPP_TOKEN=...            # System User Token دائم
WHATSAPP_VERIFY_TOKEN=أي-نص-سري
```
3. Webhook URL: `https://platform.sd/webhooks/whatsapp` مع نفس Verify Token.
4. أنشئ قوالب Utility بالعربية (تأكيد طلب، تحديث حالة، تأكيد دفع) واعتمدها من Meta لإرسالها خارج نافذة 24 ساعة.
> حتى تجهز Meta، اترك `WHATSAPP_DRIVER=log`، فتُكتب الرسائل في `storage/logs/laravel.log`.

## 11. SMS محلي (OTP)
```dotenv
SMS_DRIVER=http
SMS_URL=https://مزود-الرسائل/api/send
SMS_USERNAME=...
SMS_PASSWORD=...
SMS_SENDER=MOMEN
```
عدّل أسماء الحقول في `app/Services/Messaging/Sms.php` حسب وثيقة مزود الرسائل.

## 12. التحديثات اللاحقة
```bash
cd ~/domains/platform.sd/app
php artisan down
git pull
composer install --no-dev -o
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan up
```

## 13. قائمة فحص بعد النشر
- [ ] `https://platform.sd` يفتح الصفحة الرئيسية
- [ ] تسجيل دخول المدير ← `/admin`
- [ ] تسجيل تاجر جديد بالباقة المجانية ← يفتح `/dashboard`
- [ ] رابط المتجر يعمل (path أو subdomain)
- [ ] إضافة منتج بصورة ← تظهر في المتجر (أي أن `storage:link` يعمل)
- [ ] طلب تجريبي ← رسالة واتساب في السجل أو على الهاتف
- [ ] رفع إيصال بنكك ← يظهر للتاجر ← تأكيد
- [ ] Cron يعمل: `php artisan schedule:list`
- [ ] النسخ الاحتياطي اليومي مفعّل في DirectAdmin
