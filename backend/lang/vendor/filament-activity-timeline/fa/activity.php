<?php

declare(strict_types=1);

return [

    'model' => [
        'singular' => 'رویداد و فعالیت',
        'plural' => 'گزارش رویدادها (Audit Log)',
    ],

    'sections' => [
        'changes' => 'تغییرات ثبت‌شده',
    ],

    'fields' => [
        'event' => 'نوع رویداد',
        'created_at' => 'زمان ثبت',
        'subject' => 'رکورد مربوطه',
        'subject_type' => 'نوع موجودیت',
        'causer' => 'عامل تغییر',
        'log_name' => 'دسته‌بندی لاگ',
        'description' => 'توضیحات',
        'date_from' => 'از تاریخ',
        'date_until' => 'تا تاریخ',
    ],

    'events' => [
        'created' => 'ایجاد شد',
        'updated' => 'ویرایش شد',
        'deleted' => 'حذف شد',
        'restored' => 'بازیابی شد',
    ],

    'boolean' => [
        'true' => 'بله',
        'false' => 'خیر',
    ],

    'causer' => [
        'system' => 'سیستم (خودکار)',
    ],

    'indicators' => [
        'from' => 'از',
        'until' => 'تا',
    ],

    'empty' => [
        'heading' => 'رویدادی یافت نشد',
        'description' => 'به محض ایجاد یا تغییر هر رکورد، تاریخچه رویدادهای آن در اینجا نمایش داده می‌شود.',
        'timeline' => 'هنوز هیچ رویدادی برای این رکورد ثبت نشده است.',
    ],

    'no_changes' => 'هیچ تغییری در فیلدها ثبت نشده است.',

    'action' => [
        'label' => 'تاریخچه رویدادها',
        'heading' => 'تاریخچه رویدادها',
        'close' => 'بستن',
    ],

    'timeline' => [
        'truncated' => 'نمایش :shown از :total رویداد',
        'show_all' => 'نمایش همه',
    ],

    'actions' => [
        'open_subject' => 'مشاهده رکورد',
    ],

    'restore' => [
        'label' => 'بازیابی این نسخه',
        'heading' => 'بازیابی به مقادیر قبلی',
        'description' => 'این رکورد به مقادیر قبلی ذخیره‌شده در این رویداد بازگردانده می‌شود. این عملیات خود به عنوان یک رویداد جدید ثبت خواهد شد.',
        'submit' => 'تایید و بازیابی',
        'failed_title' => 'امکان بازیابی وجود ندارد',
        'failed_body' => 'رکورد مورد نظر در سیستم یافت نشد یا حذف دائم شده است.',
        'unchanged_title' => 'نیازی به بازیابی نیست',
        'unchanged_body' => 'رکورد در حال حاضر همین مقادیر را دارد.',
        'restored_title' => 'رکورد با موفقیت بازیابی شد',
        'restored_body' => 'مقادیر قبلی بر روی رکورد اعمال گردید.',
    ],

    'formats' => [
        'datetime_full' => 'Y/m/d H:i:s',
    ],

];
