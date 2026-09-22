<script setup lang="ts">
const settingsStore = useSettingsStore()
const currentYear = new Date().getFullYear()

const storeName = computed(() => settingsStore.settings.store_name || 'ایزیشاپ')
const storeSlogan = computed(() => settingsStore.settings.store_slogan || 'مرجع تخصصی خرید آنلاین محصولات آرایشی، مراقبت پوست و مو')
const phone = computed(() => settingsStore.settings.support_phone || '۰۲۱-۸۸۸۸۹۹۹۹')
const email = computed(() => settingsStore.settings.support_email || 'support@easyshop.ir')

const appFeatures = useFeatures()

const features = [
  { icon: 'i-lucide-truck', title: 'ارسال سریع و مطمئن', desc: 'تحویل اکسپرس در تهران و پست پیشتاز سراسری' },
  { icon: 'i-lucide-shield-check', title: 'تضمین اصالت کالا', desc: 'تمامی کالاها با برچسب اصالت و ضمانت رسمی' },
  { icon: 'i-lucide-rotate-ccw', title: '۷ روز ضمانت بازگشت', desc: 'امکان عودت کالا در صورت عدم رضایت یا مغایرت' },
  { icon: 'i-lucide-headphones', title: 'پشتیبانی تخصصی پوستی', desc: 'مشاوره رایگان زیبایی توسط کارشناسان' }
]

const quickLinks = computed(() => [
  { label: 'درباره ما', to: '/about' },
  { label: 'تماس با ما', to: '/contact' },
  ...(appFeatures.hasFeature('blog') ? [{ label: 'مجله و وبلاگ', to: '/blog' }] : []),
  { label: 'کاتالوگ همه محصولات', to: '/products' },
  { label: 'پیشنهادات شگفت‌انگیز', to: '/products?sort=featured' },
  { label: 'سبد خرید من', to: '/cart' }
])

const customerServiceLinks = computed(() => [
  { label: 'پرسش‌های متداول (FAQ)', to: '/faq' },
  ...(appFeatures.hasFeature('loyalty') ? [{ label: 'باشگاه مشتریان (VIP)', to: '/profile/club' }] : []),
  { label: 'رویه‌های بازگرداندن کالا', to: '/terms' },
  { label: 'شرایط و قوانین استفاده', to: '/terms' },
  { label: 'سفارش‌ها و پیگیری مرسوله', to: '/profile/orders' },
  { label: 'لیست علاقه‌مندی‌ها', to: '/profile/wishlist' }
])
</script>

<template>
  <footer class="mt-16 border-t border-neutral-200/80 dark:border-neutral-800 bg-white dark:bg-neutral-900/60 transition-colors">
    <!-- Top Feature Bar -->
    <div class="border-b border-neutral-200/60 dark:border-neutral-800/80 py-8 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto grid grid-cols-2 lg:grid-cols-4 gap-6">
        <div
          v-for="feat in features"
          :key="feat.title"
          class="flex items-center gap-3.5 p-3 rounded-2xl bg-neutral-50/70 dark:bg-neutral-800/40 border border-neutral-200/50 dark:border-neutral-800"
        >
          <div class="size-11 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
            <UIcon
              :name="feat.icon"
              class="size-6"
            />
          </div>
          <div class="flex flex-col">
            <span class="text-xs sm:text-sm font-bold text-neutral-800 dark:text-neutral-100">{{ feat.title }}</span>
            <span class="text-[11px] text-neutral-400 mt-0.5 leading-tight">{{ feat.desc }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Footer Grid -->
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-8 lg:gap-12">
      <!-- Col 1: About & Info (5 cols) -->
      <div class="lg:col-span-4 flex flex-col gap-4">
        <div class="flex items-center gap-2.5">
          <div class="size-9 rounded-xl bg-primary flex items-center justify-center text-white font-black text-xl shadow-md shadow-primary/20">
            {{ storeName.charAt(0) }}
          </div>
          <span class="text-lg font-black text-neutral-900 dark:text-white">
            {{ storeName }}
          </span>
        </div>

        <p class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400 leading-relaxed">
          {{ storeSlogan }}
        </p>

        <!-- Contact Points -->
        <div class="flex flex-col gap-2 pt-2 text-xs text-neutral-600 dark:text-neutral-300">
          <div class="flex items-center gap-2">
            <UIcon
              name="i-lucide-phone-call"
              class="size-4 text-primary shrink-0"
            />
            <span class="font-medium">پشتیبانی تلفنی:</span>
            <span class="font-bold font-en font-mono [direction:ltr] text-neutral-800 dark:text-neutral-200">{{ phone }}</span>
            <span class="text-[11px] text-neutral-400">(شنبه تا پنج‌شنبه ۹ الی ۱۸)</span>
          </div>

          <div class="flex items-center gap-2">
            <UIcon
              name="i-lucide-mail"
              class="size-4 text-primary shrink-0"
            />
            <span class="font-medium">ایمیل ارتباطی:</span>
            <span class="font-en font-mono text-neutral-800 dark:text-neutral-200">{{ email }}</span>
          </div>
        </div>
      </div>

      <!-- Col 2: Quick Links (3 cols) -->
      <div class="lg:col-span-3 flex flex-col gap-3">
        <h3 class="text-sm font-black text-neutral-900 dark:text-white flex items-center gap-2">
          <span class="w-1.5 h-4 rounded-full bg-primary" />
          <span>دسترسی سریع</span>
        </h3>
        <ul class="flex flex-col gap-2.5 text-xs text-neutral-600 dark:text-neutral-400">
          <li
            v-for="link in quickLinks"
            :key="link.label"
          >
            <NuxtLink
              :to="link.to"
              class="hover:text-primary transition-colors flex items-center gap-1.5 group"
            >
              <UIcon
                name="i-lucide-chevron-left"
                class="size-3 text-neutral-400 group-hover:text-primary transition-transform group-hover:-translate-x-0.5"
              />
              <span>{{ link.label }}</span>
            </NuxtLink>
          </li>
        </ul>
      </div>

      <!-- Col 3: Customer Service (2 cols) -->
      <div class="lg:col-span-2 flex flex-col gap-3">
        <h3 class="text-sm font-black text-neutral-900 dark:text-white flex items-center gap-2">
          <span class="w-1.5 h-4 rounded-full bg-primary" />
          <span>خدمات مشتریان</span>
        </h3>
        <ul class="flex flex-col gap-2.5 text-xs text-neutral-600 dark:text-neutral-400">
          <li
            v-for="link in customerServiceLinks"
            :key="link.label"
          >
            <NuxtLink
              :to="link.to"
              class="hover:text-primary transition-colors flex items-center gap-1.5 group"
            >
              <UIcon
                name="i-lucide-chevron-left"
                class="size-3 text-neutral-400 group-hover:text-primary transition-transform group-hover:-translate-x-0.5"
              />
              <span>{{ link.label }}</span>
            </NuxtLink>
          </li>
        </ul>
      </div>

      <!-- Col 4: Trust & Social (3 cols) -->
      <div class="lg:col-span-3 flex flex-col gap-4">
        <h3 class="text-sm font-black text-neutral-900 dark:text-white flex items-center gap-2">
          <span class="w-1.5 h-4 rounded-full bg-primary" />
          <span>نمادهای اعتماد و مجوزها</span>
        </h3>

        <!-- Trust Badges -->
        <div class="flex items-center gap-3">
          <div class="h-20 w-20 rounded-2xl bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 flex flex-col items-center justify-center p-2 text-center">
            <UIcon
              name="i-lucide-shield-alert"
              class="size-7 text-primary mb-1"
            />
            <span class="text-[10px] font-bold text-neutral-600 dark:text-neutral-300">نماد اعتماد</span>
          </div>

          <div class="h-20 w-20 rounded-2xl bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 flex flex-col items-center justify-center p-2 text-center">
            <UIcon
              name="i-lucide-award"
              class="size-7 text-primary mb-1"
            />
            <span class="text-[10px] font-bold text-neutral-600 dark:text-neutral-300">نشان ساماندهی</span>
          </div>
        </div>

        <!-- Social Media Links -->
        <div class="flex items-center gap-2 pt-2">
          <a
            :href="settingsStore.settings.instagram_url || '#'"
            target="_blank"
            rel="noopener noreferrer"
            class="size-9 rounded-xl bg-neutral-100 dark:bg-neutral-800 hover:bg-primary/10 hover:text-primary text-neutral-600 dark:text-neutral-300 flex items-center justify-center transition-colors"
            aria-label="اینستاگرام"
          >
            <UIcon
              name="i-lucide-camera"
              class="size-4.5"
            />
          </a>
          <a
            :href="settingsStore.settings.telegram_url || '#'"
            target="_blank"
            rel="noopener noreferrer"
            class="size-9 rounded-xl bg-neutral-100 dark:bg-neutral-800 hover:bg-primary/10 hover:text-primary text-neutral-600 dark:text-neutral-300 flex items-center justify-center transition-colors"
            aria-label="تلگرام"
          >
            <UIcon
              name="i-lucide-send"
              class="size-4.5"
            />
          </a>
          <a
            :href="settingsStore.settings.whatsapp_url || '#'"
            target="_blank"
            rel="noopener noreferrer"
            class="size-9 rounded-xl bg-neutral-100 dark:bg-neutral-800 hover:bg-primary/10 hover:text-primary text-neutral-600 dark:text-neutral-300 flex items-center justify-center transition-colors"
            aria-label="پشتیبانی واتساپ"
          >
            <UIcon
              name="i-lucide-message-circle"
              class="size-4.5"
            />
          </a>
        </div>
      </div>
    </div>

    <!-- Bottom Copyright Strip -->
    <div class="border-t border-neutral-200/60 dark:border-neutral-800 py-4 px-4 sm:px-6 lg:px-8 text-center text-xs text-neutral-400">
      <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
        <p>
          تمامی حقوق مادی و معنوی متعلق به فروشگاه {{ storeName }} می‌باشد • © {{ currentYear }}
        </p>
        <p class="text-[11px] flex items-center gap-1">
          <span>طراحی شده با رعایت استانداردهای تجربه کاربری و تجارت الکترونیک</span>
          <UIcon
            name="i-lucide-heart"
            class="size-3.5 text-primary fill-primary"
          />
        </p>
      </div>
    </div>
  </footer>
</template>
