<script setup lang="ts">
useSeoMeta({
  title: 'پرسش‌های متداول (FAQ) - ایزیشاپ',
  description: 'پاسخ به سوالات متداول مشتریان پیرامون شیوه ارسال، درگاه‌های پرداخت، اصالت کالا و مرجوعی.',
})

const faqItems = [
  {
    label: 'آیا محصولات موجود در ایزیشاپ اصل و دارای تاریخ انقضا هستند؟',
    content: 'بله، تمامی محصولات موجود در ایزیشاپ به طور مستقیم از نمایندگی‌های رسمی و شرکت‌های معتبر واردکننده تامین می‌شوند. تمامی محصولات دارای لیبل اصالت کالا (برچسب سازمان غذا و دارو) و تاریخ انقضای معتبر و طولانی هستند.',
  },
  {
    label: 'سفارش من چه زمانی تحویل داده خواهد شد؟',
    content: 'سفارش‌های شهر تهران در صورت انتخاب گزینه اکسپرس، در همان روز کاری و با هماهنگی تلفنی تحویل می‌گردند. سفارش‌های سایر نقاط کشور از طریق پست پیشتاز ظرف مدت ۲ الی ۴ روز کاری تحویل مشتریان عزیز می‌گردد.',
  },
  {
    label: 'کد رهگیری مرسوله پستی را چگونه دریافت کنم؟',
    content: 'به محض تحویل بسته به اداره پست، کد رهگیری ۲۴ رقمی پستی از طریق پیامک برای شما ارسال می‌شود. همچنین در پنل کاربری بخش «سفارش‌های من» همواره می‌توانید کد رهگیری و وضعیت لحظه‌ای بسته خود را مشاهده نمایید.',
  },
  {
    label: 'هزینه ارسال چقدر است و آیا امکان ارسال رایگان وجود دارد؟',
    content: 'هزینه ارسال پستی برای سفارش‌های زیر حد نصاب، ۵۰,۰۰۰ تومان است. اما برای سفارش‌های بالاتر از ۱,۵۰۰,۰۰۰ تومان، هزینه ارسال در سراسر ایران به صورت کاملاً رایگان محاسبه می‌گردد.',
  },
  {
    label: 'رویه بازگرداندن کالا در صورت وجود مشکل چگونه است؟',
    content: 'به دلیل ماهیت بهداشتی محصولات آرایشی، در صورتی که پلمپ کالا باز نشده باشد یا محصول با فاکتور مغایرت داشته باشد، تا ۷ روز پس از تحویل می‌توانید با واحد پشتیبانی تماس گرفته و کالا را مرجوع نمایید.',
  },
  {
    label: 'آیا برای خرید از سایت باید حتماً ثبت‌نام کنم؟',
    content: 'شما می‌توانید سبد خرید خود را به راحتی بچینید؛ هنگام ثبت نهایی سفارش، کافی است شماره موبایل خود را وارد کرده و با یک کد تایید پیامکی (OTP) ساده و بدون نیاز به رمز عبور وارد شوید.',
  },
]
</script>

<template>
  <div class="py-8 sm:py-12 flex flex-col gap-10 max-w-4xl mx-auto">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-neutral-400">
      <NuxtLink
        to="/"
        class="hover:text-primary transition-colors"
      >
        صفحه اصلی
      </NuxtLink>
      <span>/</span>
      <span class="text-neutral-700 dark:text-neutral-300 font-medium">پرسش‌های متداول</span>
    </nav>

    <!-- Header Section -->
    <div class="text-center max-w-xl mx-auto flex flex-col gap-3">
      <span class="px-3 py-1 rounded-full bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 text-xs font-bold w-fit mx-auto">
        راهنمای خرید و پاسخ به سوالات
      </span>
      <h1 class="text-2xl sm:text-3xl font-black text-neutral-900 dark:text-white">
        پرسش‌های متداول مشتریان
      </h1>
      <p class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400 leading-relaxed">
        پاسخ کامل به رایج‌ترین پرسش‌های خریداران در مورد نحوه خرید، تحویل کالا و پشتیبانی
      </p>
    </div>

    <!-- FAQ Accordion Card -->
    <div class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-6 sm:p-8 shadow-xs">
      <UAccordion
        :items="faqItems"
        :ui="{
          item: 'border-b border-neutral-100 dark:border-neutral-800 last:border-0 py-4',
          trigger: 'text-sm font-bold text-neutral-800 dark:text-neutral-200 hover:text-primary-600 dark:hover:text-primary-400 transition-colors py-2 text-right',
          content: 'text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-loose pt-2 pb-4 text-right',
        }"
      />
    </div>

    <!-- Need More Help Banner -->
    <div class="bg-gradient-to-tr from-primary-50 to-rose-50 dark:from-primary-950/30 dark:to-rose-950/20 border border-primary-100 dark:border-primary-900/40 rounded-3xl p-6 sm:p-8 text-center flex flex-col items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-white dark:bg-neutral-800 text-primary-600 dark:text-primary-400 flex items-center justify-center shadow-xs">
        <UIcon
          name="i-lucide-headset"
          class="w-6 h-6"
        />
      </div>
      <div class="flex flex-col gap-1">
        <h3 class="font-bold text-base text-neutral-900 dark:text-white">
          پاسخ سوال خود را پیدا نکردید؟
        </h3>
        <p class="text-xs text-neutral-500 dark:text-neutral-400">
          تیم پشتیبانی ما آماده راهنمایی و پاسخگویی به تمامی سوالات شماست.
        </p>
      </div>
      <UButton
        to="/contact"
        color="primary"
        size="md"
        icon="i-lucide-phone"
        class="font-bold cursor-pointer"
      >
        تماس با پشتیبانی
      </UButton>
    </div>
  </div>
</template>
