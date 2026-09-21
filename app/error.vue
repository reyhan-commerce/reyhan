<script setup lang="ts">
import type { NuxtError } from '#app'

defineProps<{
  error: NuxtError
}>()

const handleError = () => clearError({ redirect: '/' })
</script>

<template>
  <div
    class="min-h-screen flex items-center justify-center p-4 bg-gray-50 dark:bg-gray-950 font-sans"
    dir="rtl"
  >
    <div class="max-w-md w-full text-center space-y-6 bg-white dark:bg-gray-900 p-8 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm">
      <div class="w-20 h-20 rounded-full bg-red-50 dark:bg-red-950/40 text-red-500 flex items-center justify-center mx-auto text-3xl font-black">
        {{ error.statusCode || 500 }}
      </div>
      <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">
        {{ error.statusCode === 404 ? 'صفحه مورد نظر یافت نشد' : 'خطایی در پردازش رخ داد' }}
      </h1>
      <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
        {{ error.message || 'متاسفانه صفحه یا منبع درخواستی شما در دسترس نیست.' }}
      </p>
      <UButton
        color="primary"
        size="lg"
        block
        icon="i-lucide-home"
        @click="handleError"
      >
        بازگشت به صفحه اصلی
      </UButton>
    </div>
  </div>
</template>
