<script setup lang="ts">
const { toPersianDigits } = usePersian()

useSeoMeta({
  title: 'درباره ما - فروشگاه اینترنتی ایزیشاپ',
  description: 'آشنایی با داستان، اهداف و ماموریت ایزیشاپ در ارائه تخصصی‌ترین محصولات مراقبت از پوست و زیبایی.',
})

const features = [
  {
    title: 'تضمین اصالت ۱۰۰٪ کالاها',
    desc: 'تمامی محصولات مستقیماً از نمایندگی‌های رسمی داخلی و خارجی تامین شده و دارای برچسب اصالت سلامت هستند.',
    icon: 'i-lucide-shield-check',
    color: 'text-emerald-500 bg-emerald-50 dark:bg-emerald-950/40',
  },
  {
    title: 'ارسال سریع و مطمئن',
    desc: 'بسته‌بندی ایمن و مقاوم در برابر ضربه با ارسال اکسپرس در تهران و پست پیشتاز سراسری به تمام نقاط ایران.',
    icon: 'i-lucide-truck',
    color: 'text-primary-500 bg-primary-50 dark:bg-primary-950/40',
  },
  {
    title: 'مشاوره تخصصی و همراهی',
    desc: 'تیم کارشناسان و داروسازان ایزیشاپ برای انتخاب دقیق‌ترین روتین پوستی مناسب نوع پوست شما در کنارتان هستند.',
    icon: 'i-lucide-sparkles',
    color: 'text-amber-500 bg-amber-50 dark:bg-amber-950/40',
  },
  {
    title: 'ضمانت بازگشت ۷ روزه',
    desc: 'در صورت وجود هرگونه مغایرت یا آسیب‌دیدگی در هنگام تحویل، کالا بدون هیچ قید و شرطی بازگردانده می‌شود.',
    icon: 'i-lucide-refresh-cw',
    color: 'text-rose-500 bg-rose-50 dark:bg-rose-950/40',
  },
]

const stats = [
  { value: '+۱۵,۰۰۰', label: 'مشتری وفادار و خریدار راضی' },
  { value: '+۸۰', label: 'برند معتبر آرایشی و درمانی' },
  { value: '+۱,۵۰۰', label: 'تنوع محصولات اصیل و باکیفیت' },
  { value: '۹۹.۴٪', label: 'رضایت خریداران از تحویل به موقع' },
]
</script>

<template>
  <div class="py-8 sm:py-12 flex flex-col gap-12 max-w-5xl mx-auto">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-neutral-400">
      <NuxtLink
        to="/"
        class="hover:text-primary transition-colors"
      >
        صفحه اصلی
      </NuxtLink>
      <span>/</span>
      <span class="text-neutral-700 dark:text-neutral-300 font-medium">درباره ما</span>
    </nav>

    <!-- Hero Banner -->
    <div class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-8 sm:p-14 shadow-xs relative overflow-hidden text-center flex flex-col items-center gap-6">
      <div class="absolute -top-24 -right-24 w-64 h-64 bg-primary-500/10 rounded-full blur-3xl pointer-events-none" />
      <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-rose-500/10 rounded-full blur-3xl pointer-events-none" />

      <span class="px-4 py-1.5 rounded-full bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400 text-xs font-bold border border-primary-100 dark:border-primary-900/50">
        داستان و تعهد ما در ایزیشاپ
      </span>

      <h1 class="text-2xl sm:text-4xl font-black text-neutral-900 dark:text-white leading-tight max-w-2xl">
        تجربه‌ای نو از خرید آنلاین محصولات پوستی و آرایشی اصیل
      </h1>

      <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-300 leading-loose max-w-3xl">
        ایزیشاپ با چشم‌انداز ایجاد تحولی معتبر در شیوه انتخاب و خرید آنلاین لوازم آرایشی، بهداشتی و مراقبت از پوست متولد شد. باور ما این است که سلامت و شادابی پوست، پایه‌ای‌ترین بخش زیبایی طبیعی هر فرد است و دسترسی به اطلاعات شفاف، قیمت‌گذاری عادلانه و محصولات دارای اصالت قطعی، حق بی‌قید و شرط همه است.
      </p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div
        v-for="(s, idx) in stats"
        :key="idx"
        class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-6 text-center shadow-xs flex flex-col items-center justify-center gap-1.5"
      >
        <span class="text-2xl sm:text-3xl font-black text-primary-600 dark:text-primary-400">
          {{ s.value }}
        </span>
        <span class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">
          {{ s.label }}
        </span>
      </div>
    </div>

    <!-- Features / Core Values -->
    <div class="flex flex-col gap-6">
      <div class="text-center max-w-lg mx-auto">
        <h2 class="text-xl sm:text-2xl font-black text-neutral-900 dark:text-white mb-2">
          چرا خریداران به ایزیشاپ اعتماد دارند؟
        </h2>
        <p class="text-xs sm:text-sm text-neutral-400">
          تعهدات چهارگانه ما برای خلق بالاترین سطح آرامش‌خاطر در سفارش‌های شما
        </p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div
          v-for="(f, idx) in features"
          :key="idx"
          class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-6 shadow-xs flex items-start gap-4 transition-transform hover:-translate-y-0.5"
        >
          <div
            class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0"
            :class="f.color"
          >
            <UIcon
              :name="f.icon"
              class="w-6 h-6"
            />
          </div>
          <div class="flex flex-col gap-1.5">
            <h3 class="font-bold text-sm sm:text-base text-neutral-900 dark:text-white">
              {{ f.title }}
            </h3>
            <p class="text-xs text-neutral-500 dark:text-neutral-400 leading-relaxed">
              {{ f.desc }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
