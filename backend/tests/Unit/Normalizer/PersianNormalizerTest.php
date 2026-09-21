<?php

declare(strict_types=1);

use App\Pipelines\Normalizer\PersianNormalizer;

test('normalizes Arabic characters to Persian', function () {
    $input = 'كتابخانه زيبا';
    $output = PersianNormalizer::normalizeText($input);

    expect($output)->toBe('کتابخانه زیبا');
});

test('strips Arabic tashkeel diacritics', function () {
    $input = 'شَامپُو ضِدِّ شُورَه';
    $output = PersianNormalizer::normalizeText($input);

    expect($output)->toBe('شامپو ضد شوره');
});

test('normalizes digits to Persian in text', function () {
    $input = 'قیمت 12500 تومان با 5% تخفیف';
    $output = PersianNormalizer::normalizeText($input);

    expect($output)->toBe('قیمت ۱۲۵۰۰ تومان با ۵% تخفیف');
});

test('cleans and normalizes ZWNJ and whitespaces', function () {
    $input = "می\u{200c}\u{200c}شود   خوب\u{200c}   است";
    $output = PersianNormalizer::normalizeText($input);

    expect($output)->toBe("می\u{200c}شود خوب است");
});

test('normalizes Iranian mobile numbers to standard 11-digit ASCII format', function () {
    expect(PersianNormalizer::normalizeMobile('+989123456789'))->toBe('09123456789');
    expect(PersianNormalizer::normalizeMobile('00989123456789'))->toBe('09123456789');
    expect(PersianNormalizer::normalizeMobile('۰۹۱۲۳۴۵۶۷۸۹'))->toBe('09123456789');
    expect(PersianNormalizer::normalizeMobile('9123456789'))->toBe('09123456789');
});

test('normalizes Iranian national code with leading zeros', function () {
    expect(PersianNormalizer::normalizeNationalCode('۰۱۲۳۴۵۶۷۸۹'))->toBe('0123456789');
    expect(PersianNormalizer::normalizeNationalCode('12345678'))->toBe('0012345678');
});
