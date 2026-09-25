<script setup lang="ts">
const settingsStore = useSettingsStore()
</script>

<template>
  <div class="min-h-screen flex flex-col bg-stone-50 dark:bg-neutral-950 text-neutral-900 dark:text-neutral-100 transition-colors">
    <!-- Minimal Checkout Header -->
    <header class="sticky top-0 z-40 bg-white/95 dark:bg-neutral-900/95 backdrop-blur-md border-b border-neutral-200/80 dark:border-neutral-800 transition-colors">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 h-18 flex items-center justify-between">
        <!-- Logo -->
        <NuxtLink
          to="/"
          class="flex items-center gap-2.5 focus-visible:outline-none"
        >
          <div class="size-10 rounded-2xl bg-gradient-to-tr from-primary-600 to-primary-400 flex items-center justify-center text-white font-black text-xl shadow-md shadow-primary/25">
            {{ settingsStore.settings.store_name?.charAt(0) || 'E' }}
          </div>
          <div class="flex flex-col">
            <span class="font-black text-lg sm:text-xl text-neutral-900 dark:text-white tracking-tight leading-tight">
              {{ settingsStore.settings.store_name || 'ایزیشاپ' }}
            </span>
            <span class="text-[10px] text-neutral-400 font-medium">
              تسویه‌حساب امن
            </span>
          </div>
        </NuxtLink>

        <!-- Trust & Support Info -->
        <div class="flex items-center gap-4 sm:gap-6 text-xs text-neutral-500 dark:text-neutral-400">
          <div class="hidden sm:flex items-center gap-2 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 px-3 py-1.5 rounded-full border border-emerald-200/60 dark:border-emerald-800/40">
            <UIcon
              name="i-lucide-shield-check"
              class="size-4"
            />
            <span class="font-medium text-[11px]">درگاه پرداخت رسمی شاپرک</span>
          </div>

          <NuxtLink
            to="/cart"
            class="flex items-center gap-1.5 text-neutral-600 dark:text-neutral-300 hover:text-primary transition-colors font-medium text-xs"
          >
            <UIcon
              name="i-lucide-shopping-cart"
              class="size-4"
            />
            <span>بازگشت به سبد خرید</span>
          </NuxtLink>

          <UColorModeButton class="min-h-9 px-2 text-neutral-600 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 rounded-xl" />
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-6xl mx-auto px-4 sm:px-6 w-full py-6 sm:py-8">
      <slot />
    </main>

    <!-- Minimal Checkout Footer -->
    <footer class="border-t border-neutral-200 dark:border-neutral-800/80 bg-white dark:bg-neutral-900 py-6 text-center text-xs text-neutral-400 transition-colors">
      <div class="max-w-6xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-3">
        <p>تمامی حقوق و اطلاعات پرداخت شما محفوظ و تحت پروتکل رمزنگاری امن SSL پردازش می‌شود.</p>
        <span class="flex items-center gap-1 font-mono [direction:ltr]">
          <UIcon
            name="i-lucide-phone"
            class="size-3.5 text-primary"
          />
          پشتیبانی سفارشات: ۰۲۱-۸۸۸۸۹۹۹۹
        </span>
      </div>
    </footer>
  </div>
</template>
