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
  <div class="flex flex-col gap-12 sm:gap-16 pb-16">
    <!-- Hero Banner -->
    <section class="relative overflow-hidden rounded-3xl bg-linear-to-bl from-primary/15 via-primary/5 to-transparent border border-primary/20 p-6 sm:p-12 mt-4">
      <div class="max-w-2xl flex flex-col gap-5 text-start">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-primary/10 text-primary text-xs sm:text-sm font-bold w-fit">
          <UIcon
            name="i-lucide-sparkles"
            class="size-4"
          />
          <span>تخفیف‌های ویژه فصل مراقبت پوست</span>
        </div>

        <h1 class="text-3xl sm:text-5xl font-black text-neutral-900 dark:text-white leading-tight">
          درخشش و سلامت پوست با اصیل‌ترین برندهای آرایشی
        </h1>

        <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-300 leading-relaxed">
          مجموعه‌ای گلچین‌شده از بهترین ضدآفتاب‌ها، کرم‌پودرها و سرم‌های درمانی با ضمانت رسمی اصالت کالا و ارسال سریع به سراسر کشور.
        </p>

        <div class="flex flex-wrap items-center gap-3 pt-2">
          <UButton
            to="/products"
            color="primary"
            variant="solid"
            size="xl"
            icon="i-lucide-shopping-bag"
            class="min-h-12 px-6 font-bold"
          >
            مشاهده کل کاتالوگ
          </UButton>
          <UButton
            to="/categories"
            color="neutral"
            variant="outline"
            size="xl"
            icon="i-lucide-grid"
            class="min-h-12 px-5"
          >
            دسته‌بندی‌های کالا
          </UButton>
        </div>
      </div>
    </section>

    <!-- Trust Badges Strip -->
    <section class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
      <div
        v-for="badge in trustBadges"
        :key="badge.title"
        class="flex items-center gap-3 p-3.5 sm:p-4 rounded-2xl bg-neutral-50 dark:bg-neutral-900 border border-neutral-200/60 dark:border-neutral-800"
      >
        <div class="size-10 sm:size-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
          <UIcon
            :name="badge.icon"
            class="size-5 sm:size-6"
          />
        </div>
        <div class="flex flex-col">
          <span class="text-xs sm:text-sm font-bold text-neutral-800 dark:text-neutral-100">{{ badge.title }}</span>
          <span class="text-[11px] sm:text-xs text-neutral-400 leading-snug">{{ badge.desc }}</span>
        </div>
      </div>
    </section>

    <!-- Visual Category Navigation -->
    <section class="flex flex-col gap-5">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2.5">
          <div class="w-1.5 h-6 rounded-full bg-primary" />
          <h2 class="text-xl sm:text-2xl font-black text-neutral-900 dark:text-white">
            دسته‌بندی‌های برگزیده
          </h2>
        </div>
        <UButton
          to="/categories"
          color="neutral"
          variant="ghost"
          trailing-icon="i-lucide-arrow-left"
          size="sm"
        >
          مشاهده نقشه کامل
        </UButton>
      </div>

      <CategoryNav :categories="catalogStore.categoryTree" />
    </section>

    <!-- Flash Deals Section with Countdown -->
    <section
      v-if="flashDeals.length > 0"
      class="rounded-3xl bg-linear-to-r from-red-600/10 via-red-500/5 to-transparent border border-red-500/20 p-5 sm:p-8 flex flex-col gap-6"
    >
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="size-10 rounded-xl bg-error/15 text-error flex items-center justify-center shrink-0 animate-pulse">
            <UIcon
              name="i-lucide-flame"
              class="size-6 text-red-500"
            />
          </div>
          <div>
            <h2 class="text-lg sm:text-2xl font-black text-neutral-900 dark:text-white">
              پیشنهادات شگفت‌انگیز روز
            </h2>
            <p class="text-xs text-neutral-500">
              تخفیف‌های محدود با زمان باقی‌مانده
            </p>
          </div>
        </div>

        <!-- Live Countdown Timer -->
        <div class="flex items-center gap-2 self-start sm:self-auto font-mono text-xs sm:text-sm font-bold">
          <div class="size-10 rounded-xl bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 flex items-center justify-center text-red-500 shadow-xs">
            {{ toPersianDigits(String(timeLeft.hours).padStart(2, '0')) }}
          </div>
          <span>:</span>
          <div class="size-10 rounded-xl bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 flex items-center justify-center text-red-500 shadow-xs">
            {{ toPersianDigits(String(timeLeft.minutes).padStart(2, '0')) }}
          </div>
          <span>:</span>
          <div class="size-10 rounded-xl bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 flex items-center justify-center text-red-500 shadow-xs">
            {{ toPersianDigits(String(timeLeft.seconds).padStart(2, '0')) }}
          </div>
        </div>
      </div>

      <!-- Flash Deals Cards -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5 sm:gap-4">
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
        <div class="flex items-center gap-2.5">
          <div class="w-1.5 h-6 rounded-full bg-primary" />
          <h2 class="text-xl sm:text-2xl font-black text-neutral-900 dark:text-white">
            محبوب‌ترین محصولات زیبایی
          </h2>
        </div>
        <UButton
          to="/products"
          color="neutral"
          variant="ghost"
          trailing-icon="i-lucide-arrow-left"
          size="sm"
        >
          مشاهده همه
        </UButton>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5 sm:gap-5">
        <ProductCard
          v-for="product in featuredProducts"
          :key="product.id"
          :product="product"
        />
      </div>
    </section>

    <!-- Brand Showcase Strip -->
    <section class="flex flex-col gap-4 py-4 border-t border-neutral-200 dark:border-neutral-800">
      <h3 class="text-center font-bold text-sm text-neutral-400">
        محبوب‌ترین برندهای آرایشی و مراقبت پوست
      </h3>
      <div class="flex flex-wrap items-center justify-center gap-4 sm:gap-8">
        <NuxtLink
          v-for="brand in brands"
          :key="brand.name"
          :to="`/products?brand=${brand.slug}`"
          class="flex items-center gap-2 px-5 py-3 rounded-2xl bg-neutral-50 dark:bg-neutral-900 border border-neutral-200/70 dark:border-neutral-800 hover:border-primary/50 transition-all hover:shadow-xs"
        >
          <UIcon
            :name="brand.icon"
            class="size-4 text-primary"
          />
          <span class="font-bold text-sm text-neutral-800 dark:text-neutral-200">{{ brand.name }}</span>
          <span class="text-xs text-neutral-400 font-mono">({{ brand.name_en }})</span>
        </NuxtLink>
      </div>
    </section>
  </div>
</template>
