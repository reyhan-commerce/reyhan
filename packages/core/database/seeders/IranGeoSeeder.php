<?php

declare(strict_types=1);

namespace Reyhan\Core\Database\Seeders;

use Reyhan\Core\Models\City;
use Reyhan\Core\Models\Province;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IranGeoSeeder extends Seeder
{
    /**
     * Seed all 31 provinces and major provincial cities.
     */
    public function run(): void
    {
        $geoData = [
            [
                'name' => 'تهران',
                'slug' => 'tehran',
                'latitude' => 35.6892,
                'longitude' => 51.3890,
                'order' => 1,
                'cities' => [
                    ['name' => 'تهران', 'slug' => 'tehran-city', 'postal_prefix' => '11'],
                    ['name' => 'کرج و حومه (فردیس)', 'slug' => 'fardis', 'postal_prefix' => '31'],
                    ['name' => 'اسلامشهر', 'slug' => 'eslamshahr', 'postal_prefix' => '33'],
                    ['name' => 'شهریار', 'slug' => 'shahriar', 'postal_prefix' => '33'],
                    ['name' => 'شهر قدس', 'slug' => 'shahr-e-qods', 'postal_prefix' => '37'],
                    ['name' => 'ورامین', 'slug' => 'varamin', 'postal_prefix' => '33'],
                    ['name' => 'پردیس', 'slug' => 'pardis', 'postal_prefix' => '16'],
                    ['name' => 'دماوند', 'slug' => 'damavand', 'postal_prefix' => '39'],
                ],
            ],
            [
                'name' => 'خراسان رضوی',
                'slug' => 'razavi-khorasan',
                'latitude' => 36.2972,
                'longitude' => 59.6067,
                'order' => 2,
                'cities' => [
                    ['name' => 'مشهد', 'slug' => 'mashhad', 'postal_prefix' => '91'],
                    ['name' => 'نیشابور', 'slug' => 'neyshabur', 'postal_prefix' => '93'],
                    ['name' => 'سبزوار', 'slug' => 'sabzevar', 'postal_prefix' => '96'],
                    ['name' => 'تربت حیدریه', 'slug' => 'torbat-heydariyeh', 'postal_prefix' => '95'],
                ],
            ],
            [
                'name' => 'اصفهان',
                'slug' => 'isfahan',
                'latitude' => 32.6546,
                'longitude' => 51.6680,
                'order' => 3,
                'cities' => [
                    ['name' => 'اصفهان', 'slug' => 'isfahan-city', 'postal_prefix' => '81'],
                    ['name' => 'کاشان', 'slug' => 'kashan', 'postal_prefix' => '87'],
                    ['name' => 'نجف‌آباد', 'slug' => 'najafabad', 'postal_prefix' => '85'],
                    ['name' => 'شاهین‌شهر', 'slug' => 'shahin-shahr', 'postal_prefix' => '83'],
                ],
            ],
            [
                'name' => 'فارس',
                'slug' => 'fars',
                'latitude' => 29.5918,
                'longitude' => 52.5837,
                'order' => 4,
                'cities' => [
                    ['name' => 'شیراز', 'slug' => 'shiraz', 'postal_prefix' => '71'],
                    ['name' => 'مرودشت', 'slug' => 'marvdasht', 'postal_prefix' => '73'],
                    ['name' => 'جهرم', 'slug' => 'jahrom', 'postal_prefix' => '74'],
                    ['name' => 'فسا', 'slug' => 'fasa', 'postal_prefix' => '74'],
                ],
            ],
            [
                'name' => 'البرز',
                'slug' => 'alborz',
                'latitude' => 35.8400,
                'longitude' => 50.9391,
                'order' => 5,
                'cities' => [
                    ['name' => 'کرج', 'slug' => 'karaj', 'postal_prefix' => '31'],
                    ['name' => 'نظرآباد', 'slug' => 'nazarabad', 'postal_prefix' => '33'],
                    ['name' => 'هشتگرد', 'slug' => 'hashtgerd', 'postal_prefix' => '33'],
                ],
            ],
            [
                'name' => 'آذربایجان شرقی',
                'slug' => 'east-azerbaijan',
                'latitude' => 38.0800,
                'longitude' => 46.2919,
                'order' => 6,
                'cities' => [
                    ['name' => 'تبریز', 'slug' => 'tabriz', 'postal_prefix' => '51'],
                    ['name' => 'مراغه', 'slug' => 'maragheh', 'postal_prefix' => '55'],
                    ['name' => 'مرند', 'slug' => 'marand', 'postal_prefix' => '54'],
                ],
            ],
            [
                'name' => 'مازندران',
                'slug' => 'mazandaran',
                'latitude' => 36.5659,
                'longitude' => 53.0586,
                'order' => 7,
                'cities' => [
                    ['name' => 'ساری', 'slug' => 'sari', 'postal_prefix' => '48'],
                    ['name' => 'بابل', 'slug' => 'babol', 'postal_prefix' => '47'],
                    ['name' => 'آمل', 'slug' => 'amol', 'postal_prefix' => '46'],
                    ['name' => 'قائم‌شهر', 'slug' => 'qaemshahr', 'postal_prefix' => '47'],
                    ['name' => 'چالوس', 'slug' => 'chalous', 'postal_prefix' => '46'],
                ],
            ],
            [
                'name' => 'خوزستان',
                'slug' => 'khuzestan',
                'latitude' => 31.3183,
                'longitude' => 48.6706,
                'order' => 8,
                'cities' => [
                    ['name' => 'اهواز', 'slug' => 'ahvaz', 'postal_prefix' => '61'],
                    ['name' => 'دزفول', 'slug' => 'dezful', 'postal_prefix' => '64'],
                    ['name' => 'آبادان', 'slug' => 'abadan', 'postal_prefix' => '63'],
                ],
            ],
            [
                'name' => 'گیلان',
                'slug' => 'gilan',
                'latitude' => 37.2808,
                'longitude' => 49.5832,
                'order' => 9,
                'cities' => [
                    ['name' => 'رشت', 'slug' => 'rasht', 'postal_prefix' => '41'],
                    ['name' => 'بندر انزلی', 'slug' => 'bandar-anzali', 'postal_prefix' => '43'],
                    ['name' => 'لاهیجان', 'slug' => 'lahijan', 'postal_prefix' => '44'],
                ],
            ],
            [
                'name' => 'قم',
                'slug' => 'qom',
                'latitude' => 34.6401,
                'longitude' => 50.8764,
                'order' => 10,
                'cities' => [
                    ['name' => 'قم', 'slug' => 'qom-city', 'postal_prefix' => '37'],
                ],
            ],
            [
                'name' => 'کرمان',
                'slug' => 'kerman',
                'latitude' => 30.2839,
                'longitude' => 57.0834,
                'order' => 11,
                'cities' => [
                    ['name' => 'کرمان', 'slug' => 'kerman-city', 'postal_prefix' => '76'],
                    ['name' => 'سیرجان', 'slug' => 'sirjan', 'postal_prefix' => '78'],
                    ['name' => 'رفسنجان', 'slug' => 'rafsanjan', 'postal_prefix' => '77'],
                ],
            ],
            [
                'name' => 'هرمزگان',
                'slug' => 'hormozgan',
                'latitude' => 27.1832,
                'longitude' => 56.2666,
                'order' => 12,
                'cities' => [
                    ['name' => 'بندرعباس', 'slug' => 'bandar-abbas', 'postal_prefix' => '79'],
                    ['name' => 'کیش', 'slug' => 'kish', 'postal_prefix' => '79'],
                    ['name' => 'قشم', 'slug' => 'qeshm', 'postal_prefix' => '79'],
                ],
            ],
            [
                'name' => 'یزد',
                'slug' => 'yazd',
                'latitude' => 31.8974,
                'longitude' => 54.3569,
                'order' => 13,
                'cities' => [
                    ['name' => 'یزد', 'slug' => 'yazd-city', 'postal_prefix' => '89'],
                    ['name' => 'میبد', 'slug' => 'meybod', 'postal_prefix' => '89'],
                ],
            ],
            [
                'name' => 'همدان',
                'slug' => 'hamedan',
                'latitude' => 34.7989,
                'longitude' => 48.5150,
                'order' => 14,
                'cities' => [
                    ['name' => 'همدان', 'slug' => 'hamedan-city', 'postal_prefix' => '65'],
                    ['name' => 'ملایر', 'slug' => 'malayer', 'postal_prefix' => '65'],
                ],
            ],
            [
                'name' => 'قزوین',
                'slug' => 'qazvin',
                'latitude' => 36.2797,
                'longitude' => 50.0049,
                'order' => 15,
                'cities' => [
                    ['name' => 'قزوین', 'slug' => 'qazvin-city', 'postal_prefix' => '34'],
                ],
            ],
            [
                'name' => 'مرکزی',
                'slug' => 'markazi',
                'latitude' => 34.0954,
                'longitude' => 49.7013,
                'order' => 16,
                'cities' => [
                    ['name' => 'اراک', 'slug' => 'arak', 'postal_prefix' => '38'],
                    ['name' => 'ساوه', 'slug' => 'saveh', 'postal_prefix' => '39'],
                ],
            ],
            [
                'name' => 'سمنان',
                'slug' => 'semnan',
                'latitude' => 35.5769,
                'longitude' => 53.3953,
                'order' => 17,
                'cities' => [
                    ['name' => 'سمنان', 'slug' => 'semnan-city', 'postal_prefix' => '35'],
                    ['name' => 'شاهرود', 'slug' => 'shahroud', 'postal_prefix' => '36'],
                ],
            ],
            [
                'name' => 'زنجان',
                'slug' => 'zanjan',
                'latitude' => 36.6736,
                'longitude' => 48.4787,
                'order' => 18,
                'cities' => [
                    ['name' => 'زنجان', 'slug' => 'zanjan-city', 'postal_prefix' => '45'],
                ],
            ],
            [
                'name' => 'گلستان',
                'slug' => 'golestan',
                'latitude' => 36.8456,
                'longitude' => 54.4392,
                'order' => 19,
                'cities' => [
                    ['name' => 'گرگان', 'slug' => 'gorgan', 'postal_prefix' => '49'],
                    ['name' => 'گنبد کاووس', 'slug' => 'gonbad', 'postal_prefix' => '49'],
                ],
            ],
            [
                'name' => 'کرمانشاه',
                'slug' => 'kermanshah',
                'latitude' => 34.3277,
                'longitude' => 47.0778,
                'order' => 20,
                'cities' => [
                    ['name' => 'کرمانشاه', 'slug' => 'kermanshah-city', 'postal_prefix' => '67'],
                ],
            ],
            [
                'name' => 'لرستان',
                'slug' => 'lorestan',
                'latitude' => 33.4878,
                'longitude' => 48.3558,
                'order' => 21,
                'cities' => [
                    ['name' => 'خرم‌آباد', 'slug' => 'khorramabad', 'postal_prefix' => '68'],
                    ['name' => 'بروجرد', 'slug' => 'borujerd', 'postal_prefix' => '69'],
                ],
            ],
            [
                'name' => 'بوشهر',
                'slug' => 'bushehr',
                'latitude' => 28.9234,
                'longitude' => 50.8203,
                'order' => 22,
                'cities' => [
                    ['name' => 'بوشهر', 'slug' => 'bushehr-city', 'postal_prefix' => '75'],
                    ['name' => 'عسلویه', 'slug' => 'asaluyeh', 'postal_prefix' => '75'],
                ],
            ],
            [
                'name' => 'آذربایجان غربی',
                'slug' => 'west-azerbaijan',
                'latitude' => 37.5527,
                'longitude' => 45.0761,
                'order' => 23,
                'cities' => [
                    ['name' => 'ارومیه', 'slug' => 'urmia', 'postal_prefix' => '57'],
                    ['name' => 'خوی', 'slug' => 'khoy', 'postal_prefix' => '58'],
                ],
            ],
            [
                'name' => 'اردبیل',
                'slug' => 'ardabil',
                'latitude' => 38.2498,
                'longitude' => 48.2933,
                'order' => 24,
                'cities' => [
                    ['name' => 'اردبیل', 'slug' => 'ardabil-city', 'postal_prefix' => '56'],
                ],
            ],
            [
                'name' => 'سیستان و بلوچستان',
                'slug' => 'sistan-and-baluchestan',
                'latitude' => 29.4963,
                'longitude' => 60.8629,
                'order' => 25,
                'cities' => [
                    ['name' => 'زاهدان', 'slug' => 'zahedan', 'postal_prefix' => '98'],
                    ['name' => 'چابهار', 'slug' => 'chabahar', 'postal_prefix' => '99'],
                ],
            ],
            [
                'name' => 'کردستان',
                'slug' => 'kurdistan',
                'latitude' => 35.3144,
                'longitude' => 46.9988,
                'order' => 26,
                'cities' => [
                    ['name' => 'سنندج', 'slug' => 'sanandaj', 'postal_prefix' => '66'],
                    ['name' => 'سقز', 'slug' => 'saqqez', 'postal_prefix' => '66'],
                ],
            ],
            [
                'name' => 'چهارمحال و بختیاری',
                'slug' => 'chaharmahal-and-bakhtiari',
                'latitude' => 32.3256,
                'longitude' => 50.8644,
                'order' => 27,
                'cities' => [
                    ['name' => 'شهرکرد', 'slug' => 'shahrekord', 'postal_prefix' => '88'],
                ],
            ],
            [
                'name' => 'کهگیلویه و بویراحمد',
                'slug' => 'kohgiluyeh-and-boyer-ahmad',
                'latitude' => 30.6684,
                'longitude' => 51.5876,
                'order' => 28,
                'cities' => [
                    ['name' => 'یاسوج', 'slug' => 'yasuj', 'postal_prefix' => '75'],
                ],
            ],
            [
                'name' => 'خراسان جنوبی',
                'slug' => 'south-khorasan',
                'latitude' => 32.8663,
                'longitude' => 59.2211,
                'order' => 29,
                'cities' => [
                    ['name' => 'بیرجند', 'slug' => 'birjand', 'postal_prefix' => '97'],
                ],
            ],
            [
                'name' => 'خراسان شمالی',
                'slug' => 'north-khorasan',
                'latitude' => 37.4747,
                'longitude' => 57.3249,
                'order' => 30,
                'cities' => [
                    ['name' => 'بجنورد', 'slug' => 'bojnurd', 'postal_prefix' => '94'],
                ],
            ],
            [
                'name' => 'ایلام',
                'slug' => 'ilam',
                'latitude' => 33.6374,
                'longitude' => 46.4227,
                'order' => 31,
                'cities' => [
                    ['name' => 'ایلام', 'slug' => 'ilam-city', 'postal_prefix' => '69'],
                ],
            ],
        ];

        DB::transaction(function () use ($geoData): void {
            foreach ($geoData as $provinceData) {
                $cities = $provinceData['cities'];
                unset($provinceData['cities']);

                $province = Province::firstOrCreate(
                    ['slug' => $provinceData['slug']],
                    $provinceData
                );

                foreach ($cities as $index => $cityData) {
                    City::firstOrCreate(
                        [
                            'province_id' => $province->id,
                            'slug' => $cityData['slug'],
                        ],
                        [
                            'name' => $cityData['name'],
                            'postal_prefix' => $cityData['postal_prefix'],
                            'order' => $index + 1,
                            'is_active' => true,
                        ]
                    );
                }
            }
        });
    }
}
