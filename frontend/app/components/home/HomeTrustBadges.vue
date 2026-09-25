<script setup lang="ts">
interface TrustBadge {
  icon: string
  title: string
  desc: string
}

const trustBadges: TrustBadge[] = [
  { icon: 'i-lucide-shield-check', title: 'ضمانت ۱۰۰٪ اصالت کالا', desc: 'تمامی کالاها با برچسب اصالت و ضمانت سلامت' },
  { icon: 'i-lucide-truck', title: 'ارسال سریع به سراسر ایران', desc: 'پست پیشتاز و تیپاکس اکسپرس' },
  { icon: 'i-lucide-rotate-ccw', title: '۷ روز ضمانت بازگشت', desc: 'امکان عودت کالا در صورت نارضایتی' },
  { icon: 'i-lucide-headphones', title: 'مشاوره و پشتیبانی خرید', desc: 'پاسخگویی سریع و راهنمایی تخصصی انتخاب محصول' }
]
</script>

<template>
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
</template>
