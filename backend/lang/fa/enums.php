<?php

declare(strict_types=1);

return [
    'order_status' => [
        'pending_payment' => 'در انتظار پرداخت',
        'processing' => 'در حال پردازش و بسته‌بندی',
        'shipped' => 'تحویل به پست / ارسال شده',
        'delivered' => 'تحویل داده شده به مشتری',
        'cancelled' => 'لغو شده',
        'refunded' => 'مسترد شده',
    ],

    'payment_status' => [
        'pending' => 'در انتظار پرداخت',
        'success' => 'موفق و تأیید شده',
        'failed' => 'ناموفق',
    ],

    'payment_gateway' => [
        'sandbox' => 'سندباکس تستی',
        'zarinpal' => 'درگاه پرداخت زرین‌پال',
        'saman' => 'درگاه پرداخت اینترنتی بانک سامان (SEP)',
        'mellat' => 'درگاه پرداخت اینترنتی به‌پرداخت ملت',
        'snapp_pay' => 'خرید اقساطی اسنپ‌پی (۴ قسط)',
        'card_to_card' => 'کارت به کارت آفلاین',
        'wallet' => 'کیف پول کاربری',
    ],

    'payment_gateway_description' => [
        'sandbox' => 'شبیه‌ساز پرداخت تستی (بدون کسر وجه از حساب)',
        'zarinpal' => 'پرداخت امن و سریع با کلیه کارت‌های عضو شتاب',
        'saman' => 'درگاه پرداخت مستقیم اینترنتی بانک سامان (SEP)',
        'mellat' => 'درگاه پرداخت اینترنتی به‌پرداخت بانک ملت',
        'snapp_pay' => 'خرید اقساطی در ۴ قسط بدون کارمزد و ضامن',
        'card_to_card' => 'انتقال وجه کارت به کارت و ثبت شماره پیگیری واریز',
        'wallet' => 'پرداخت آنی و مستقیم از موجودی کیف پول کاربری',
    ],

    'order_return_status' => [
        'pending' => 'در انتظار بررسی پشتیبانی',
        'approved' => 'تأیید اولیه (آماده ارسال کالا)',
        'rejected' => 'رد درخواست مرجوعی',
        'item_received' => 'کالا تحویل انبار شد',
        'refunded' => 'مبلغ به کیف پول مسترد شد',
        'cancelled' => 'لغو شده توسط کاربر',
    ],

    'ticket_department' => [
        'support' => 'پشتیبانی فنی و عمومی',
        'finance' => 'امور مالی و حسابداری',
        'sales' => 'مشاوره فروش و پیش از خرید',
        'shipping' => 'پیگیری ارسال و مرسولات',
        'complaints' => 'انتقادات و شکایات',
    ],

    'ticket_priority' => [
        'low' => 'کم',
        'medium' => 'متوسط',
        'high' => 'زیاد',
        'urgent' => 'فوری و اضطراری',
    ],

    'ticket_status' => [
        'open' => 'در انتظار بررسی',
        'answered' => 'پاسخ داده شده',
        'awaiting_reply' => 'در انتظار پاسخ مشتری',
        'closed' => 'بسته شده',
    ],

    'banner_position' => [
        'home_slider' => 'اسلایدر اصلی صفحه نخست (سراسری)',
        'home_middle' => 'بنر عریض میانی صفحه نخست',
        'home_grid' => 'بنرهای چهارگانه تبلیغاتی',
        'sidebar' => 'بنر سایدبار صفحات کاتالوگ',
    ],

    'referral_status' => [
        'pending' => 'در انتظار اولین خرید دوست',
        'completed' => 'تکمیل شده (پاداش واریز شد)',
        'expired' => 'منقضی شده',
    ],

    'wallet_transaction_type' => [
        'deposit' => 'شارژ و واریز به کیف پول',
        'withdraw' => 'پرداخت سفارش از کیف پول',
        'refund' => 'استرداد وجه به کیف پول',
        'cashback' => 'پاداش نقدی و کش‌بک',
        'admin_adjustment' => 'اصلاح دستی مدیریت',
    ],

    'coupon_type' => [
        'percentage' => 'درصدی (%)',
        'fixed' => 'مبلغ ثابت (ریال)',
        'free_shipping' => 'ارسال رایگان',
    ],

    'coupon_scope' => [
        'all' => 'تمامی سفارش‌ها',
        'category' => 'دسته‌بندی خاص',
        'product' => 'محصولات خاص',
    ],

    'review_status' => [
        'pending' => 'در انتظار بررسی',
        'approved' => 'تأیید شده',
        'rejected' => 'رد شده',
    ],

    'shipping_method' => [
        'post_pishtaz' => 'پست پیشتاز سراسری',
        'express_courier' => 'پیک موتوری اکسپرس شهری',
        'tipax' => 'تیپاکس اکسپرس',
        'freight' => 'باربری و تحویل کالای سنگین',
    ],

    'stock_status' => [
        'in_stock' => 'موجود در انبار',
        'out_of_stock' => 'ناموجود',
        'pre_order' => 'پیش‌خرید',
    ],

    'attribute_type' => [
        'text' => 'متن کوتاه',
        'select' => 'تک انتخابی (Select)',
        'color' => 'رنگ (Color)',
        'boolean' => 'بله / خیر',
        'number' => 'عدد',
    ],
];
