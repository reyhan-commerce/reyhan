# فهرست و نقشه راه پلاگین‌های پنل مدیریت (Filament Plugins Registry)

این سند مرجع پلاگین‌های رسمی و جامعه کاربری (Community) برای پنل مدیریت فیلامنت در پروژه EasyShop است؛ شامل پلاگین‌های نصب‌شده، فعال و همچنین افزونه‌های ارزیابی‌شده جهت پیاده‌سازی در فازهای آینده.

---

## ۱. پلاگین‌های نصب‌شده و فعال (Active & Installed)

| نام پلاگین | پکیج کامپوزر | کاربرد در EasyShop | وضعیت |
| :--- | :--- | :--- | :--- |
| **Filament Shield** | `bezhansalleh/filament-shield` | مدیریت نقش‌ها، دسترسی‌ها و گیت‌های امنیتی ادمین‌ها | فعال ✅ |
| **Spatie Laravel Backup** | `shuvroroy/filament-spatie-laravel-backup` | پشتیبان‌گیری خودکار/دستی دیتابیس PostgreSQL و فایل‌ها بر بستر صف Redis | فعال ✅ |
| **Filament Tree Table** | `alareqi/filament-tree` | ساختار جدول درختی یکپارچه با پشتیبانی از Reordering کشیدن و رها کردن و فیلد TreeSelect | فعال ✅ |
| **Spatie Settings Plugin** | `filament/spatie-laravel-settings-plugin` | مدیریت تنظیمات سراسری، پیامک، درگاه‌ها و هویت بصری فروشگاه | فعال ✅ |
| **Media Library Plugin** | `filament/spatie-laravel-media-library-plugin` | بارگذاری و مدیریت تصاویر و رسانه‌های محصولات و مقالات | فعال ✅ |

---

## ۲. پلاگین‌های کاندید برای آینده (Future Roadmap & Backlog)

در این بخش، افزونه‌هایی که کارایی آن‌ها بررسی و تایید شده است، برای استفاده در فازهای بعدی لیست می‌شوند:

### ۲.۱. Page Header (هدر پیشرفته و چسبان صفحات)

* **نام و سازنده:** Page Header توسط Pedro Monteiro (`MortalKiller`)
* **پکیج کامپوزر:** `mortalkiller/filament-page-header`
* **سازگاری:** Filament v4 و Filament v5
* **لینک‌ها:**
  * [صفحه رسمی در مارکت‌پلیس فیلامنت](https://filamentphp.com/plugins/pedro-monteiro-page-header)
  * [مستندات و راهنما](https://docs.pedromonteiro.dev/filament-page-header/)
  * [مخزن گیت‌هاب](https://github.com/mortalkiller/filament-page-header)
* **کاربرد و ارزش افزوده در EasyShop:**
  * **صفحه سفارشات (`OrderResource`):** نمایش هدر مدرن و چسبان (Sticky) شامل وضعیت سفارش (Badge رنگی)، مبلغ کل پرداختی، شماره و نام مشتری، به همراه دکمه‌های پرکاربرد (چاپ فاکتور، تغییر وضعیت) بدون نیاز به اسکرول.
  * **صفحه کالاها (`ProductResource`):** نمایش تصویر اصلی محصول، قیمت، موجودی فعلی انبار و دکمه‌های سریع فعال/غیرفعال کردن کالا در بالای صفحه.
  * **صفحه مشتریان (`UserResource`):** نمایش آواتار، سطح وفاداری مشتری (VIP / Bronze) و آمار سفارشات.
### ۲.۲. Realtime Driver (ارتباط وب‌سوکت بلادرنگ و پوش نوتیفیکیشن)

* **نام و سازنده:** Realtime Driver توسط Marcus Bassalobre (`marcusvbda`)
* **پکیج کامپوزر:** `marcusvbda/filament-realtime-driver`
* **سازگاری:** Laravel 11/12/13 و Filament v5
* **لینک‌ها:**
  * [صفحه رسمی در مارکت‌پلیس فیلامنت](https://filamentphp.com/plugins/mv-bassalobre-realtime-driver)
  * [مخزن گیت‌هاب](https://github.com/marcusvbda/filament-realtime-driver)
* **کاربرد و ارزش افزوده در EasyShop:**
  * **جایگزینی Polling با WebSocket:** حذف درخواست‌های تکراری هر ۳۰ ثانیه سرور (`wire:poll`) و استفاده از وب‌سوکت فوق‌العاده سبک (~۴ کیلوبایت بدون نیاز به نصب `laravel-echo` یا `pusher-js`).
  * **به‌روزرسانی در لحظه سفارشات (`Table::socket()`):** به محض ثبت خرید توسط مشتری در فرانت Nuxt، سطر جدید در جدول سفارشات ادمین بدون نیاز به رفرش صفحه ظاهر می‌شود.
  * **نوتیفیکیشن‌های زنده دیتابیس:** به صدا درآمدن آنی زنگوله نوتیفیکیشن‌های پنل ادمین (اتمام موجودی کالا، ثبت تیکت جدید، پرداخت موفق).
  * **مصرف بهینه منابع:** استفاده از یک سوکت اشتراکی تکی برای کل صفحه بدون بار اضافه روی سرور.
* **پیشنیازهای فنی:** نیازمند نصب و فعال‌سازی سرور وب‌سوکت Laravel Reverb (`composer require laravel/reverb`).
* **فاز پیشنهادی برای اجرا:** فاز سفارشات و نوتیفیکیشن‌های بلادرنگ (Orders & Live Operations Phase).

---

## ۳. چک‌لیست ارزیابی قبل از افزودن هر پلاگین جدید

قبل از نصب هر پلاگین جدید در پروژه، موارد زیر بر اساس منشور معماری `farshid-laravel` باید احراز شوند:
1. **سازگاری نسخه:** حتماً از نسخه ۵ فیلامنت (`filament/filament: ^5.0`) و لاراول ۱۳ به بعد پشتیبانی کند.
2. **عدم تداخل لایسنس و وابستگی‌ها:** سبک بودن وابستگی‌های جانبی و عدم ایجاد تداخل در Alpine/Livewire.
3. **پشتیبانی از گارد ادمین:** سازگاری کامل با مدل `Admin` و سیستم سطوح دسترسی Filament Shield.
4. **راستی‌آزمایی تستی:** پوشش کامل تست‌های Pest Feature پس از ادغام افزونه.
