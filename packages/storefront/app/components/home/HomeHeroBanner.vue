<script setup lang="ts">
interface Props {
  storeName: string
  storeSlogan?: string | null
  badgeText?: string | null
  primaryButtonText?: string | null
  secondaryButtonText?: string | null
}

defineProps<Props>()
</script>

<template>
  <section class="relative overflow-hidden rounded-3xl bg-linear-to-bl from-primary-500/15 via-primary-500/5 to-transparent border border-primary-500/20 p-6 sm:p-12 lg:p-16">
    <!-- Glow background orbs -->
    <div class="absolute -top-24 -left-24 size-72 rounded-full bg-primary/20 blur-3xl pointer-events-none" />
    <div class="absolute -bottom-24 -right-24 size-72 rounded-full bg-primary-400/15 blur-3xl pointer-events-none" />

    <div class="relative max-w-2xl flex flex-col gap-5 sm:gap-6 text-start">
      <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/80 dark:bg-neutral-800/80 backdrop-blur-md border border-primary-500/25 text-primary-600 dark:text-primary-400 text-xs sm:text-sm font-black w-fit shadow-xs">
        <UIcon
          name="i-lucide-sparkles"
          class="size-4 animate-pulse"
        />
        <span>{{ badgeText || 'تخفیف‌های ویژه و محصولات برگزیده' }}</span>
      </div>

      <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-neutral-900 dark:text-white leading-[1.15] tracking-tight">
        {{ storeName }}
      </h1>

      <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-300 leading-relaxed font-normal">
        {{ storeSlogan || 'مجموعه‌ای برگزیده از اصیل‌ترین برندها با برچسب رسمی اصالت کالا و ارسال سریع به سراسر ایران.' }}
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
          {{ primaryButtonText || 'مشاهده کل کاتالوگ' }}
        </UButton>
        <UButton
          to="/categories"
          color="neutral"
          variant="outline"
          size="xl"
          icon="i-lucide-layout-grid"
          class="min-h-12 px-6 font-bold rounded-2xl"
        >
          {{ secondaryButtonText || 'دسته‌بندی‌های کالا' }}
        </UButton>
      </div>
    </div>
  </section>
</template>
