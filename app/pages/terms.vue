<script setup lang="ts">
useSeoMeta({
  title: 'قوانین و رویه بازگشت کالا - ایزیشاپ',
  description: 'شرایط و ضوابط خرید اینترنتی، رویه مرجوعی ۷ روزه و استانداردهای بهداشتی محصولات در ایزیشاپ.',
})

const sections = [
  {
    title: '۱. قوانین عمومی و شرایط ثبت سفارش',
    content: 'تمامی فعالیت‌های فروشگاه ایزیشاپ منطبق بر قوانین جمهوری اسلامی ایران، قانون تجارت الکترونیک و قانون حمایت از مصرف‌کننده است. کاربر موظف است هنگام ثبت سفارش، اطلاعات هویتی و نشانی پستی خود را به صورت دقیق و کامل وارد نماید.',
  },
  {
    title: '۲. ضوابط بهداشتی و سلامت کالاها',
    content: 'با توجه به ماهیت بهداشتی محصولات مراقبت از پوست، مو و لوازم آرایشی، حفظ پلمپ اولیه و سلامت بسته‌بندی کارخانه ضامن سلامت شما و سایر مصرف‌کنندگان است. در نتیجه پس از باز شدن پلمپ یا استفاده از محصول، امکان استرداد آن به دلایل بهداشتی مقدور نمی‌باشد.',
  },
  {
    title: '۳. رویه بازگرداندن ۷ روزه در صورت مغایرت یا آسیب',
    content: 'در صورتی که کالای تحویل گرفته شده با سفارش ثبت شده مغایرت داشته باشد، یا دارای آسیب‌دیدگی فیزیکی ناشی از حمل و نقل باشد، مشتری گرامی می‌تواند حداکثر ظرف مدت ۷ روز کاری پس از تحویل، با ارائه عکس یا فاکتور، مراتب را به پشتیبانی اطلاع داده و کالا را عودت دهد. کلیه هزینه‌های بازگشت کالا در این شرایط بر عهده ایزیشاپ خواهد بود.',
  },
  {
    title: '۴. سیاست قیمت‌گذاری و اصالت کالا',
    content: 'ایزیشاپ تضمین می‌کند که کلیه محصولات ارائه شده دارای فاکتور رسمی و برچسب اصالت بوده و با قیمت مصوب نمایندگی‌ها عرضه می‌گردند. در صورت تغییر قیمت‌های رسمی پیش از تایید نهایی سفارش، موضوع به اطلاع خریدار خواهد رسید.',
  },
  {
    title: '۵. حفظ حریم خصوصی کاربران',
    content: 'ایزیشاپ متعهد می‌شود که از اطلاعات هویتی، نشانی‌ها و شماره‌های تماس کاربران محافظت کرده و از آن صرفاً جهت فرآیندهای لجستیکی، پردازش سفارش و ارسال اطلاع‌رسانی استفاده نماید.',
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
      <span class="text-neutral-700 dark:text-neutral-300 font-medium">قوانین و مقررات</span>
    </nav>

    <!-- Header Section -->
    <div class="text-center max-w-xl mx-auto flex flex-col gap-3">
      <span class="px-3 py-1 rounded-full bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 text-xs font-bold w-fit mx-auto">
        شفافیت و حقوق مصرف‌کننده
      </span>
      <h1 class="text-2xl sm:text-3xl font-black text-neutral-900 dark:text-white">
        قوانین، مقررات و رویه بازگشت کالا
      </h1>
      <p class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400 leading-relaxed">
        ضوابط خرید، استانداردهای بهداشتی و فرآیند تضمین رضایت ۷ روزه خریداران
      </p>
    </div>

    <!-- Terms Content Cards -->
    <div class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-6 sm:p-10 shadow-xs flex flex-col gap-8">
      <div
        v-for="(sec, idx) in sections"
        :key="idx"
        class="flex flex-col gap-2.5 pb-6 border-b border-neutral-100 dark:border-neutral-800 last:border-0 last:pb-0"
      >
        <h2 class="text-sm sm:text-base font-bold text-neutral-900 dark:text-white flex items-center gap-2">
          <span class="w-2 h-2 rounded-full bg-primary-500 shrink-0" />
          {{ sec.title }}
        </h2>
        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-loose pr-4">
          {{ sec.content }}
        </p>
      </div>
    </div>
  </div>
</template>
