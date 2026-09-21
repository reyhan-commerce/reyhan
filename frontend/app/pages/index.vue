<script setup lang="ts">
import CategoryNav from '~/components/catalog/CategoryNav.vue'
import ProductCard from '~/components/catalog/ProductCard.vue'

const catalogStore = useCatalogStore()
const { toPersianDigits } = usePersian()

// Fetch category tree and products in SSR
await useAsyncData('home-catalog', async () => {
  await Promise.all([
    catalogStore.fetchCategoryTree(),
    catalogStore.fetchProducts({ sort: 'featured', page: 1 })
  ])
  return true
})

// Flash deals (products with discounts)
const flashDeals = computed(() => {
  return catalogStore.products.filter(p => p.has_discount).slice(0, 4)
})

// Featured products
const featuredProducts = computed(() => {
  return catalogStore.products.slice(0, 8)
})

// Countdown timer for flash deals
const timeLeft = ref({ hours: 14, minutes: 35, seconds: 20 })
let timerInterval: ReturnType<typeof setInterval> | null = null

onMounted(() => {
  timerInterval = setInterval(() => {
    if (timeLeft.value.seconds > 0) {
      timeLeft.value.seconds--
    } else if (timeLeft.value.minutes > 0) {
      timeLeft.value.minutes--
      timeLeft.value.seconds = 59
    } else if (timeLeft.value.hours > 0) {
      timeLeft.value.hours--
      timeLeft.value.minutes = 59
      timeLeft.value.seconds = 59
    }
  }, 1000)
})

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval)
})

const trustBadges = [
  { icon: 'i-lucide-shield-check', title: 'ضمانت ۱۰۰٪ اصالت کالا', desc: 'تمامی کالاها با برچسب اصالت' },
  { icon: 'i-lucide-truck', title: 'ارسال سریع به سراسر ایران', desc: 'پست پیشتاز و تیپاکس اکسپرس' },
  { icon: 'i-lucide-rotate-ccw', title: '۷ روز ضمانت بازگشت', desc: 'امکان عودت کالا در صورت نارضایتی' },
  { icon: 'i-lucide-headphones', title: 'مشاوره تخصصی زیبایی', desc: 'پشتیبانی تخصصی پوستی و آرایشی' }
]

const brands = [
  { name: 'لورآل', slug: 'loreal', name_en: 'L\'Oréal', icon: 'i-lucide-sparkles' },
  { name: 'نیوآ', slug: 'nivea', name_en: 'Nivea', icon: 'i-lucide-droplet' },
  { name: 'سینره', slug: 'cinere', name_en: 'Cinere', icon: 'i-lucide-flower-2' },
  { name: 'مای', slug: 'my', name_en: 'My', icon: 'i-lucide-heart' },
  { name: 'ایزادورا', slug: 'isadora', name_en: 'IsaDora', icon: 'i-lucide-gem' }
]
</script>

<template>
  <div class="flex flex-col gap-10 sm:gap-14 lg:gap-18 pt-4 pb-16">
    <!-- Hero Banner with Liquid Glass & Luxury Accents -->
    <section class="relative overflow-hidden rounded-3xl bg-linear-to-bl from-rose-500/15 via-rose-500/5 to-transparent border border-rose-500/20 p-6 sm:p-12 lg:p-16">
      <!-- Glow background orbs -->
      <div class="absolute -top-24 -left-24 size-72 rounded-full bg-primary/20 blur-3xl pointer-events-none" />
      <div class="absolute -bottom-24 -right-24 size-72 rounded-full bg-rose-400/15 blur-3xl pointer-events-none" />

      <div class="relative max-w-2xl flex flex-col gap-5 sm:gap-6 text-start">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/80 dark:bg-neutral-800/80 backdrop-blur-md border border-rose-500/25 text-rose-600 dark:text-rose-400 text-xs sm:text-sm font-black w-fit shadow-xs">
          <UIcon
            name="i-lucide-sparkles"
            class="size-4 animate-pulse"
          />
          <span>تخفیف‌های ویژه فصل مراقبت و زیبایی پوست</span>
        </div>

        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-neutral-900 dark:text-white leading-[1.15] tracking-tight">
          درخشش و سلامت پوست با اصیل‌ترین برندهای آرایشی
        </h1>

        <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-300 leading-relaxed font-normal">
          مجموعه‌ای برگزیده از معتبرترین کرم‌های ضدآفتاب، سرم‌های جوانساز و محصولات آرایشی ارگانیک با برچسب رسمی اصالت کالا و ارسال اکسپرس.
        </p>

        <div class="flex flex-wrap items-center gap-3.5 pt-2">
          <UButton
            to="/products"
            color="primary"
            variant="solid"
            size="xl"
            icon="i-lucide-shopping-bag"
            class="min-h-12 px-7 font-black rounded-2xl shadow-md shadow-primary/25"
          >
            مشاهده کل کاتالوگ
          </UButton>
          <UButton
            to="/categories"
            color="neutral"
            variant="outline"
            size="xl"
            icon="i-lucide-layout-grid"
            class="min-h-12 px-6 font-bold rounded-2xl"
          >
            دسته‌بندی‌های کالا
          </UButton>
        </div>
      </div>
    </section>

    <!-- Trust Badges Strip -->
    <section class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-5">
      <div
        v-for="badge in trustBadges"
        :key="badge.title"
        class="flex items-center gap-3.5 p-4 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 shadow-xs"
      >
        <div class="size-11 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
          <UIcon
            :name="badge.icon"
            class="size-5.5"
          />
        </div>
        <div class="flex flex-col">
          <span class="text-xs sm:text-sm font-bold text-neutral-800 dark:text-neutral-100">{{ badge.title }}</span>
          <span class="text-[11px] text-neutral-400 mt-0.5 leading-snug">{{ badge.desc }}</span>
        </div>
      </div>
    </section>

    <!-- Visual Category Navigation Section -->
    <section class="flex flex-col gap-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-1.5 h-6 rounded-full bg-primary" />
          <h2 class="text-xl sm:text-2xl font-black text-neutral-900 dark:text-white">
            دسته‌بندی‌های تخصصی
          </h2>
        </div>
        <UButton
          to="/categories"
          color="neutral"
          variant="ghost"
          trailing-icon="i-lucide-arrow-left"
          size="sm"
          class="font-bold hover:text-primary"
        >
          مشاهده نقشه کامل
        </UButton>
      </div>

      <CategoryNav :categories="catalogStore.categoryTree" />
    </section>

    <!-- Flash Deals Section with Countdown (Liquid Glass Card) -->
    <section
      v-if="flashDeals.length > 0"
      class="rounded-3xl bg-linear-to-r from-rose-500/10 via-rose-500/5 to-transparent border border-rose-500/20 p-6 sm:p-8 flex flex-col gap-6"
    >
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
          <div class="size-11 rounded-2xl bg-rose-500/15 text-rose-500 flex items-center justify-center shrink-0">
            <UIcon
              name="i-lucide-flame"
              class="size-6 text-rose-500 animate-bounce"
            />
          </div>
          <div>
            <h2 class="text-lg sm:text-2xl font-black text-neutral-900 dark:text-white">
              پیشنهادات شگفت‌انگیز روز
            </h2>
            <p class="text-xs text-neutral-500">
              فرصت محدود با تخفیف‌های ویژه تا پایان امروز
            </p>
          </div>
        </div>

        <!-- Live Glass Countdown Timer -->
        <div class="flex items-center gap-2 self-start sm:self-auto text-sm font-black">
          <div class="size-11 rounded-2xl bg-white/90 dark:bg-neutral-900/90 backdrop-blur-md border border-neutral-200/80 dark:border-neutral-800 flex items-center justify-center text-rose-600 dark:text-rose-400 shadow-xs">
            {{ toPersianDigits(String(timeLeft.hours).padStart(2, '0')) }}
          </div>
          <span class="text-rose-500">:</span>
          <div class="size-11 rounded-2xl bg-white/90 dark:bg-neutral-900/90 backdrop-blur-md border border-neutral-200/80 dark:border-neutral-800 flex items-center justify-center text-rose-600 dark:text-rose-400 shadow-xs">
            {{ toPersianDigits(String(timeLeft.minutes).padStart(2, '0')) }}
          </div>
          <span class="text-rose-500">:</span>
          <div class="size-11 rounded-2xl bg-white/90 dark:bg-neutral-900/90 backdrop-blur-md border border-neutral-200/80 dark:border-neutral-800 flex items-center justify-center text-rose-600 dark:text-rose-400 shadow-xs">
            {{ toPersianDigits(String(timeLeft.seconds).padStart(2, '0')) }}
          </div>
        </div>
      </div>

      <!-- Flash Deals Cards Grid -->
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
        <ProductCard
          v-for="product in flashDeals"
          :key="product.id"
          :product="product"
        />
      </div>
    </section>

    <!-- Best Sellers / Featured Products -->
    <section class="flex flex-col gap-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-1.5 h-6 rounded-full bg-primary" />
          <h2 class="text-xl sm:text-2xl font-black text-neutral-900 dark:text-white">
            محبوب‌ترین محصولات زیبایی و مراقبت
          </h2>
        </div>
        <UButton
          to="/products"
          color="neutral"
          variant="ghost"
          trailing-icon="i-lucide-arrow-left"
          size="sm"
          class="font-bold hover:text-primary"
        >
          مشاهده همه کاتالوگ
        </UButton>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
        <ProductCard
          v-for="product in featuredProducts"
          :key="product.id"
          :product="product"
        />
      </div>
    </section>

    <!-- Brand Showcase Strip -->
    <section class="flex flex-col gap-5 py-6 border-t border-neutral-200/80 dark:border-neutral-800">
      <h3 class="text-center font-black text-xs sm:text-sm text-neutral-400">
        اصیل‌ترین برندهای معتبر جهانی و ایرانی
      </h3>
      <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-6">
        <NuxtLink
          v-for="brand in brands"
          :key="brand.name"
          :to="`/products?brand=${brand.slug}`"
          class="flex items-center gap-2.5 px-5 py-3 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 hover:border-primary/50 dark:hover:border-primary/50 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5"
        >
          <UIcon
            :name="brand.icon"
            class="size-4.5 text-primary"
          />
          <span class="font-bold text-sm text-neutral-800 dark:text-neutral-200">{{ brand.name }}</span>
          <span class="text-xs text-neutral-400 font-en">({{ brand.name_en }})</span>
        </NuxtLink>
      </div>
    </section>
  </div>
</template>
