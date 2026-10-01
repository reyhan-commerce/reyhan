<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'

const props = defineProps<{
  modelValue: boolean
  variantId: number
  productName: string
  variantTitle?: string
}>()

const emit = defineEmits<{
  'update:modelValue': [val: boolean]
}>()

const authStore = useAuthStore()
const api = useApi()
const toast = useToast()

const mobile = ref(authStore.user?.mobile || '')
const isSubmitting = ref(false)
const isSuccess = ref(false)

watch(() => props.modelValue, (isOpen) => {
  if (isOpen) {
    mobile.value = authStore.user?.mobile || ''
    isSuccess.value = false
  }
})

async function handleSubmit() {
  if (!mobile.value || !/^09\d{9}$/.test(mobile.value)) {
    toast.add({
      title: 'شماره نامعتبر',
      description: 'لطفاً یک شماره موبایل معتبر ۱۱ رقمی (مانند ۰۹۱۲۳۴۵۶۷۸۹) وارد فرمایید.',
      color: 'warning'
    })
    return
  }

  isSubmitting.value = true
  try {
    await api('/catalog/stock-alerts', {
      method: 'POST',
      body: {
        variant_id: props.variantId,
        mobile: mobile.value
      }
    })

    isSuccess.value = true
    toast.add({
      title: 'ثبت شد!',
      description: 'به محض موجود شدن این کالا، پیامک اطلاع‌رسانی برای شما ارسال خواهد شد.',
      color: 'success'
    })

    setTimeout(() => {
      emit('update:modelValue', false)
    }, 1600)
  } catch {
    toast.add({
      title: 'خطا در ثبت درخواست',
      description: 'ثبت اطلاع‌رسانی با خطا مواجه شد. لطفاً دوباره تلاش نمایید.',
      color: 'error'
    })
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <UModal
    :open="modelValue"
    @update:open="(val: boolean) => emit('update:modelValue', val)"
  >
    <template #content>
      <div class="p-6 flex flex-col gap-5">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
            <UIcon
              name="i-lucide-bell-ring"
              class="size-5"
            />
          </div>
          <div>
            <h3 class="font-bold text-base text-neutral-900 dark:text-white">
              خبرم کن وقتی موجود شد
            </h3>
            <p class="text-xs text-neutral-500 line-clamp-1">
              {{ productName }} <span v-if="variantTitle">({{ variantTitle }})</span>
            </p>
          </div>
        </div>

        <div
          v-if="!isSuccess"
          class="flex flex-col gap-4"
        >
          <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
            شماره موبایل خود را وارد نمایید تا به محض شارژ مجدد موجودی این محصول در انبار، از طریق پیامک به شما اطلاع دهیم.
          </p>

          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-semibold text-neutral-700 dark:text-neutral-300">
              شماره تلفن همراه
            </label>
            <UInput
              v-model="mobile"
              placeholder="۰۹۱۲۳۴۵۶۷۸۹"
              dir="ltr"
              icon="i-lucide-phone"
              maxlength="11"
              class="text-start"
              :disabled="isSubmitting"
            />
          </div>

          <div class="flex items-center justify-end gap-2 pt-2">
            <UButton
              color="neutral"
              variant="ghost"
              size="sm"
              @click="emit('update:modelValue', false)"
            >
              انصراف
            </UButton>
            <UButton
              color="primary"
              size="sm"
              :loading="isSubmitting"
              trailing-icon="i-lucide-check"
              @click="handleSubmit"
            >
              ثبت اطلاع‌رسانی
            </UButton>
          </div>
        </div>

        <div
          v-else
          class="flex flex-col items-center justify-center py-6 gap-2 text-center"
        >
          <UIcon
            name="i-lucide-badge-check"
            class="size-12 text-success"
          />
          <span class="font-bold text-sm text-neutral-900 dark:text-white">
            شماره شما با موفقیت ثبت شد
          </span>
          <span class="text-xs text-neutral-500">
            به محض موجودی پیامک دریافت خواهید کرد.
          </span>
        </div>
      </div>
    </template>
  </UModal>
</template>
