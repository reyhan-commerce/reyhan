<script setup lang="ts">
const props = defineProps<{
  modelValue: string
}>()

const emit = defineEmits<{
  'update:modelValue': [value: string]
  'keyChange': [key: string]
}>()

const authStore = useAuthStore()
const captchaSvg = ref<string>('')
const captchaKey = ref<string>('')
const isRefreshing = ref<boolean>(false)

const loadCaptcha = async () => {
  isRefreshing.value = true
  const data = await authStore.fetchCaptcha()
  if (data) {
    captchaKey.value = data.key
    captchaSvg.value = data.svg
    emit('keyChange', data.key)
  }
  isRefreshing.value = false
}

onMounted(() => {
  loadCaptcha()
})
</script>

<template>
  <div class="space-y-1.5">
    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
      کد امنیتی
    </label>
    <div class="flex items-center gap-2">
      <UInput
        :model-value="props.modelValue"
        placeholder="پاسخ را وارد کنید"
        inputmode="numeric"
        class="flex-1 min-h-12"
        size="lg"
        icon="i-lucide-shield-check"
        @update:model-value="emit('update:modelValue', $event as string)"
      />

      <!-- Captcha SVG Canvas -->
      <!-- eslint-disable-next-line vue/no-v-html -->
      <div
        class="h-12 min-w-[130px] rounded-lg border border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-900 flex items-center justify-center overflow-hidden shrink-0 cursor-pointer"
        title="کلیک برای نوسازی کد"
        @click="loadCaptcha"
        v-html="captchaSvg"
      />

      <!-- Reload button -->
      <UButton
        color="neutral"
        variant="subtle"
        size="lg"
        icon="i-lucide-refresh-cw"
        aria-label="تغییر کد امنیتی"
        class="min-h-12 min-w-12 shrink-0"
        :loading="isRefreshing"
        @click="loadCaptcha"
      />
    </div>
  </div>
</template>
