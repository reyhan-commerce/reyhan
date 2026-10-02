<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Validator;
use Reyhan\Core\Rules\CardNumberRule;
use Reyhan\Core\Rules\CompanyNationalIdRule;
use Reyhan\Core\Rules\IranianMobileRule;
use Reyhan\Core\Rules\IranianPhoneRule;
use Reyhan\Core\Rules\NationalCodeRule;
use Reyhan\Core\Rules\NoPersianRule;
use Reyhan\Core\Rules\PersianTextRule;
use Reyhan\Core\Rules\PostalCodeRule;
use Reyhan\Core\Rules\ShebaRule;

it('validates authentic Iranian National Codes and rejects invalid algorithms or repeating digits', function (): void {
    $rule = new NationalCodeRule;

    // Valid Iranian National Codes (Mathematically verified checksum)
    $validCodes = ['0010000003', '0499370899', '1288278780', '0063854880'];
    foreach ($validCodes as $code) {
        $validator = Validator::make(['code' => $code], ['code' => $rule]);
        expect($validator->passes())->toBeTrue("National code {$code} should be valid");

        // Test string rule alias
        $aliasValidator = Validator::make(['code' => $code], ['code' => 'ir_national_code']);
        expect($aliasValidator->passes())->toBeTrue("Alias ir_national_code for {$code} should pass");
    }

    // Invalid: repeating digits
    $invalidRepeating = ['0000000000', '1111111111', '2222222222'];
    foreach ($invalidRepeating as $code) {
        $validator = Validator::make(['code' => $code], ['code' => $rule]);
        expect($validator->fails())->toBeTrue("Repeating code {$code} should fail");
    }

    // Invalid checksum / length
    $invalidChecksums = ['1234567890', '0010000005', '123', 'abcdefghij'];
    foreach ($invalidChecksums as $code) {
        $validator = Validator::make(['code' => $code], ['code' => $rule]);
        expect($validator->fails())->toBeTrue("Checksum for {$code} should fail");
    }
});

it('validates authentic Iranian Sheba IBANs and rejects invalid formats or checksums', function (): void {
    $rule = new ShebaRule;

    // Valid Iranian Sheba numbers (ISO 7064 Mod 97-10 check digits = 16)
    $validShebas = [
        'IR160120000000001234567890', // Valid mod 97
        '160120000000001234567890',   // Valid without IR prefix
    ];

    foreach ($validShebas as $sheba) {
        $validator = Validator::make(['sheba' => $sheba], ['sheba' => $rule]);
        expect($validator->passes())->toBeTrue("Sheba {$sheba} should be valid");

        $aliasValidator = Validator::make(['sheba' => $sheba], ['sheba' => 'ir_sheba']);
        expect($aliasValidator->passes())->toBeTrue("Alias ir_sheba for {$sheba} should pass");
    }

    // Invalid Shebas
    $invalidShebas = [
        'IR000000000000000000000000',
        'IR820120000000001234567899',
        'IR123',
        'US820120000000001234567890',
    ];

    foreach ($invalidShebas as $sheba) {
        $validator = Validator::make(['sheba' => $sheba], ['sheba' => $rule]);
        expect($validator->fails())->toBeTrue("Sheba {$sheba} should fail");
    }
});

it('validates 10-digit Iranian Postal Codes and rejects invalid prefixes or repetitions', function (): void {
    $rule = new PostalCodeRule;

    // Valid: 10 digits, doesn't start with 0 or 2, 5th digit not 0
    $validPostalCodes = ['1998835111', '1451673419', '3813955677'];
    foreach ($validPostalCodes as $code) {
        $validator = Validator::make(['postal_code' => $code], ['postal_code' => $rule]);
        expect($validator->passes())->toBeTrue("Postal code {$code} should be valid");

        $aliasValidator = Validator::make(['postal_code' => $code], ['postal_code' => 'ir_postal_code']);
        expect($aliasValidator->passes())->toBeTrue("Alias ir_postal_code for {$code} should pass");
    }

    // Invalid: starts with 0 or 2
    $invalidPrefix = ['0123456789', '2123456789'];
    foreach ($invalidPrefix as $code) {
        $validator = Validator::make(['postal_code' => $code], ['postal_code' => $rule]);
        expect($validator->fails())->toBeTrue("Postal code {$code} starting with 0/2 should fail");
    }

    // Invalid: 5th digit is 0
    $invalid5th = ['1998035111', '3813055677'];
    foreach ($invalid5th as $code) {
        $validator = Validator::make(['postal_code' => $code], ['postal_code' => $rule]);
        expect($validator->fails())->toBeTrue("Postal code {$code} with 5th digit 0 should fail");
    }

    // Invalid length / non-numeric
    $invalidFormat = ['12345', '199883511122', '19988abcde'];
    foreach ($invalidFormat as $code) {
        $validator = Validator::make(['postal_code' => $code], ['postal_code' => $rule]);
        expect($validator->fails())->toBeTrue("Format for {$code} should fail");
    }
});

it('validates 16-digit Iranian Shetab bank cards using Luhn algorithm', function (): void {
    $rule = new CardNumberRule;

    // Valid Luhn card numbers
    $validCards = [
        '6037991199999990',
        '6104337788888889',
        '5892101012345670',
    ];

    foreach ($validCards as $card) {
        $validator = Validator::make(['card' => $card], ['card' => $rule]);
        expect($validator->passes())->toBeTrue("Card {$card} should be valid");

        $aliasValidator = Validator::make(['card' => $card], ['card' => 'ir_bank_card']);
        expect($aliasValidator->passes())->toBeTrue("Alias ir_bank_card for {$card} should pass");
    }

    // Invalid cards
    $invalidCards = [
        '6037991199999993', // Checksum error
        '123456',           // Too short
        '60379911999999990', // Too long
        'abcdefghijklmnop',
    ];

    foreach ($invalidCards as $card) {
        $validator = Validator::make(['card' => $card], ['card' => $rule]);
        expect($validator->fails())->toBeTrue("Card {$card} should fail");
    }
});

it('validates Iranian mobile numbers in all standard formats', function (): void {
    $rule = new IranianMobileRule;

    $validMobiles = [
        '09123456789',
        '+989123456789',
        '00989123456789',
        '989123456789',
        '9123456789',
        '۰۹۱۲۳۴۵۶۷۸۹', // Persian digits
    ];

    foreach ($validMobiles as $mobile) {
        $validator = Validator::make(['mobile' => $mobile], ['mobile' => $rule]);
        expect($validator->passes())->toBeTrue("Mobile {$mobile} should pass");

        $aliasValidator = Validator::make(['mobile' => $mobile], ['mobile' => 'ir_mobile']);
        expect($aliasValidator->passes())->toBeTrue("Alias ir_mobile for {$mobile} should pass");
    }

    $invalidMobiles = [
        '08123456789',
        '0912345678',
        '091234567890',
        'abcdefghijk',
    ];

    foreach ($invalidMobiles as $mobile) {
        $validator = Validator::make(['mobile' => $mobile], ['mobile' => $rule]);
        expect($validator->fails())->toBeTrue("Mobile {$mobile} should fail");
    }
});

it('validates Iranian landline phone numbers with area codes', function (): void {
    $rule = new IranianPhoneRule;

    $validPhones = [
        '02188776655',
        '03132211445',
        '05138899001',
        '۰۲۱۸۸۷۷۶۶۵۵',
    ];

    foreach ($validPhones as $phone) {
        $validator = Validator::make(['phone' => $phone], ['phone' => $rule]);
        expect($validator->passes())->toBeTrue("Phone {$phone} should pass");

        $aliasValidator = Validator::make(['phone' => $phone], ['phone' => 'ir_phone']);
        expect($aliasValidator->passes())->toBeTrue("Alias ir_phone for {$phone} should pass");
    }

    $invalidPhones = [
        '2188776655', // Missing leading 0
        '00982188776655',
        '09123456789', // Mobile prefix 09 not 01-08
        '0218877',
    ];

    foreach ($invalidPhones as $phone) {
        $validator = Validator::make(['phone' => $phone], ['phone' => $rule]);
        expect($validator->fails())->toBeTrue("Phone {$phone} should fail");
    }
});

it('validates authentic Iranian Corporate National IDs (شناسه ملی اشخاص حقوقی)', function (): void {
    $rule = new CompanyNationalIdRule;

    // Valid 11-digit Corporate IDs: 10861676731 (Bank Mellat), 10102766860 (Irancell)
    $validIds = ['10861676731', '10102766860'];

    foreach ($validIds as $id) {
        $validator = Validator::make(['company_id' => $id], ['company_id' => $rule]);
        expect($validator->passes())->toBeTrue("Company ID {$id} should pass");

        $aliasValidator = Validator::make(['company_id' => $id], ['company_id' => 'ir_company_national_id']);
        expect($aliasValidator->passes())->toBeTrue("Alias ir_company_national_id for {$id} should pass");
    }

    $invalidIds = [
        '10100494491', // Invalid check digit
        '1234567890',  // 10 digits instead of 11
        '101004944900', // 12 digits
    ];

    foreach ($invalidIds as $id) {
        $validator = Validator::make(['company_id' => $id], ['company_id' => $rule]);
        expect($validator->fails())->toBeTrue("Company ID {$id} should fail");
    }
});

it('validates Persian text and half-spaces and rejects non-Persian strings', function (): void {
    $persianRule = new PersianTextRule;
    $noPersianRule = new NoPersianRule;

    // Valid Persian
    $persianSamples = [
        'فروشگاه ریحان',
        'کتاب آموزش برنامه‌نویسی لاراول', // With half-space \u200c
        'گچپژ',
    ];

    foreach ($persianSamples as $text) {
        $validator = Validator::make(['text' => $text], ['text' => $persianRule]);
        expect($validator->passes())->toBeTrue("Persian text '{$text}' should pass");

        $noPersianVal = Validator::make(['text' => $text], ['text' => $noPersianRule]);
        expect($noPersianVal->fails())->toBeTrue("NoPersian should fail on '{$text}'");
    }

    // English text
    $englishSamples = [
        'ReyhanCommerce',
        'my-custom-slug',
        'password123!@#',
    ];

    foreach ($englishSamples as $text) {
        $persianVal = Validator::make(['text' => $text], ['text' => $persianRule]);
        expect($persianVal->fails())->toBeTrue("PersianTextRule should fail on english '{$text}'");

        $noPersianVal = Validator::make(['text' => $text], ['text' => $noPersianRule]);
        expect($noPersianVal->passes())->toBeTrue("NoPersianRule should pass on english '{$text}'");
    }
});
