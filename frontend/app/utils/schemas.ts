import { z } from 'zod'

export const normalizeDigitsString = (val: string): string => {
  const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹']
  const arabicDigits = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩']
  let str = String(val || '').trim()
  for (let i = 0; i < 10; i++) {
    str = str.replace(new RegExp(persianDigits[i]!, 'g'), String(i))
    str = str.replace(new RegExp(arabicDigits[i]!, 'g'), String(i))
  }
  return str
}

export const otpRequestSchema = z.object({
  mobile: z
    .string()
    .min(1, 'شماره موبایل الزامی است')
    .transform(normalizeDigitsString)
    .pipe(
      z
        .string()
        .regex(/^09\d{9}$/, 'شماره موبایل باید ۱۱ رقم بوده و با ۰۹ شروع شود')
    ),
  captcha: z
    .string()
    .min(1, 'کد امنیتی الزامی است')
    .transform(normalizeDigitsString)
    .pipe(
      z.string().min(1, 'کد امنیتی را وارد کنید')
    )
})

export const otpVerifySchema = z.object({
  code: z
    .string()
    .min(1, 'کد تایید پیامکی الزامی است')
    .transform(normalizeDigitsString)
    .pipe(
      z.string().regex(/^\d{6}$/, 'کد تایید باید دقیقاً ۶ رقم باشد')
    )
})

export const addressSchema = z.object({
  title: z.string().min(2, 'عنوان آدرس الزامی است (مثال: منزل، محل کار)'),
  recipient_name: z.string().min(3, 'نام و نام خانوادگی تحویل‌گیرنده الزامی است'),
  recipient_mobile: z
    .string()
    .transform(normalizeDigitsString)
    .pipe(z.string().regex(/^09\d{9}$/, 'شماره موبایل گیرنده معتبر نیست')),
  province_id: z.number().min(1, 'انتخاب استان الزامی است'),
  city_id: z.number().min(1, 'انتخاب شهر الزامی است'),
  address: z.string().min(10, 'نشانی دقیق پستی باید حداقل ۱۰ کاراکتر باشد'),
  postal_code: z
    .string()
    .transform(normalizeDigitsString)
    .pipe(z.string().regex(/^\d{10}$/, 'کد پستی باید دقیقاً ۱۰ رقم باشد'))
})

export const reviewSchema = z.object({
  rating: z.number().min(1, 'حداقل ۱ ستاره الزامی است').max(5, 'حداکثر ۵ ستاره'),
  comment: z.string().min(3, 'متن دیدگاه باید حداقل ۳ کاراکتر باشد').max(2000, 'متن دیدگاه بسیار طولانی است'),
  criteria_ratings: z.record(z.string(), z.number().min(1).max(5)).optional(),
  strengths: z.array(z.string()).max(5, 'حداکثر ۵ نقطه قوت').optional(),
  weaknesses: z.array(z.string()).max(5, 'حداکثر ۵ نقطه ضعف').optional()
})

export type OtpRequestInput = z.infer<typeof otpRequestSchema>
export type OtpVerifyInput = z.infer<typeof otpVerifySchema>
export type AddressInput = z.infer<typeof addressSchema>
export type ReviewInput = z.infer<typeof reviewSchema>
