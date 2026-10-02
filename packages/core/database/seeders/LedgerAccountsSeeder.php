<?php

declare(strict_types=1);

namespace Reyhan\Core\Database\Seeders;

use Reyhan\Core\Models\LedgerAccount;
use Illuminate\Database\Seeder;

class LedgerAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'code' => '10101',
                'name' => 'موجودی نقد و بانک (درگاه پرداخت شاپرک)',
                'type' => 'asset',
                'description' => 'وجوه واریز شده از طریق درگاه‌های پرداخت آنلاین شاپرک',
            ],
            [
                'code' => '10102',
                'name' => 'موجودی نقد و بانک (واریز به حساب / کارت به کارت)',
                'type' => 'asset',
                'description' => 'وجوه دریافت شده از طریق کارت به کارت و ساتنا',
            ],
            [
                'code' => '20101',
                'name' => 'بستانکاران تجاری / کیف پول مشتریان',
                'type' => 'liability',
                'description' => 'موجودی امانی کیف پول کاربران در سامانه',
            ],
            [
                'code' => '20301',
                'name' => 'مالیات بر ارزش افزوده پرداختنی (سازمان امور مالیاتی)',
                'type' => 'liability',
                'description' => 'مالیات و عوارض ارزش افزوده ۱۰٪ اخذ شده از خریداران',
            ],
            [
                'code' => '40101',
                'name' => 'درآمد ناخالص فروش کالا',
                'type' => 'revenue',
                'description' => 'مجموع قیمت فروش ناخالص کالاهای سفارش داده شده',
            ],
            [
                'code' => '40201',
                'name' => 'درآمد خدمات ارسال و لجستیک',
                'type' => 'revenue',
                'description' => 'کرایه حمل و ارسال دریافتی از مشتریان',
            ],
            [
                'code' => '50101',
                'name' => 'تخفیفات اعطایی فروش (کوپن و پروموشن)',
                'type' => 'expense',
                'description' => 'مجموع تخفیف‌های کوپنی و پروموشنی اعطا شده به مشتریان',
            ],
        ];

        foreach ($accounts as $account) {
            LedgerAccount::firstOrCreate(['code' => $account['code']], $account);
        }
    }
}
