<script setup lang="ts">
const { $pwa } = useNuxtApp()

const isOnline = useOnline()
const toast = useToast()

watch(isOnline, (online) => {
  if (!online) {
    toast.add({
      id: 'pwa-offline',
      title: 'اتصال اینترنت قطع شد',
      description: 'شما در حالت آفلاین هستید. محتوای ذخیره‌شده همچنان در دسترس است.',
      color: 'warning',
      icon: 'i-lucide-wifi-off',
      duration: 5000
    })
  } else {
    toast.remove('pwa-offline')
    toast.add({
      title: 'اتصال مجدد برقرار شد',
      description: 'ارتباط شما با سرور متصل است.',
      color: 'success',
      icon: 'i-lucide-wifi',
      duration: 3000
    })
  }
})
</script>

<template>
  <ClientOnly>
    <div
      v-if="$pwa?.needRefresh"
      class="fixed bottom-20 sm:bottom-6 left-4 right-4 sm:left-auto sm:right-6 z-50 max-w-sm p-4 bg-white dark:bg-neutral-900 rounded-2xl border border-primary/20 shadow-2xl flex items-center justify-between gap-4 animate-bounce"
    >
      <div class="flex items-center gap-3">
        <div class="p-2 rounded-xl bg-primary/10 text-primary">
          <UIcon
            name="i-lucide-refresh-cw"
            class="w-5 h-5 animate-spin"
          />
        </div>
        <div class="flex flex-col text-xs">
          <span class="font-bold text-neutral-900 dark:text-white">نسخه جدید برنامه آماده است</span>
          <span class="text-neutral-500">برای به‌روزرسانی برنامه کلیک کنید</span>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <UButton
          size="xs"
          color="primary"
          @click="$pwa?.updateServiceWorker()"
        >
          به‌روزرسانی
        </UButton>
        <UButton
          size="xs"
          variant="ghost"
          color="neutral"
          icon="i-lucide-x"
          @click="$pwa.needRefresh = false"
        />
      </div>
    </div>
  </ClientOnly>
</template>
