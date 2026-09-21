<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'

const authStore = useAuthStore()
const api = useApi()
const toast = useToast()

useSeoMeta({
  title: 'تنظیمات حساب کاربری - ایزیشاپ',
})

const form = reactive({
  first_name: authStore.user?.first_name || '',
  last_name: authStore.user?.last_name || '',
  national_code: authStore.user?.national_code || '',
  email: authStore.user?.email || '',
})

// Sync if authStore.user changes
watch(() => authStore.user, (u) => {
  if (u) {
    form.first_name = u.first_name || ''
    form.last_name = u.last_name || ''
    form.national_code = u.national_code || ''
    form.email = u.email || ''
  }
}, { immediate: true })

const isSubmitting = ref(false)

const handleSave = async () => {
  if (form.national_code && form.national_code.length !== 10) {
    toast.add({
      title: 'خطای اعتبارسنجی',
      description: 'کد ملی باید دقیقاً ۱۰ رقم باشد.',
      color: 'error',
    })
    return
  }

  isSubmitting.value = true
  try {
    const res = await api<{ success: boolean, message: string, data: any }>('/profile', {
      method: 'PUT',
      body: {
        first_name: form.first_name.trim() || null,
        last_name: form.last_name.trim() || null,
        national_code: form.national_code.trim() || null,
        email: form.email.trim() || null,
      },
    })

    if (res.data) {
      authStore.user = res.data
    }

    toast.add({
      title: 'موفقیت‌آمیز',
      description: res.message || 'اطلاعات هویتی با موفقیت به‌روزرسانی شد.',
      color: 'success',
    })
  } catch (err: any) {
    toast.add({
      title: 'خطا',
      description: err?.data?.message || 'مشکلی در ذخیره اطلاعات رخ داده است.',
      color: 'error',
    })
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <div class="flex flex-col gap-6">
    <!-- Header -->
    <div class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-5 shadow-xs">
      <h2 class="text-lg font-black text-neutral-900 dark:text-neutral-100">
        اطلاعات فردی و هویتی
      </h2>
      <p class="text-xs text-neutral-400 mt-0.5">
        جهت صدور فاکتور رسمی و ارسال سفارش‌ها، اطلاعات خود را کامل نمایید
      </p>
    </div>

    <!-- Settings Form Card -->
    <div class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-6 sm:p-8 shadow-xs">
      <form
        class="flex flex-col gap-6 max-w-2xl"
        @submit.prevent="handleSave"
      >
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <!-- First Name -->
          <div>
            <label class="block text-xs font-bold text-neutral-700 dark:text-neutral-300 mb-2">
              نام
            </label>
            <UInput
              v-model="form.first_name"
              placeholder="مثال: سارا"
              size="lg"
              class="w-full"
            />
          </div>

          <!-- Last Name -->
          <div>
            <label class="block text-xs font-bold text-neutral-700 dark:text-neutral-300 mb-2">
              نام خانوادگی
            </label>
            <UInput
              v-model="form.last_name"
              placeholder="مثال: رضایی"
              size="lg"
              class="w-full"
            />
          </div>

          <!-- National Code -->
          <div>
            <label class="block text-xs font-bold text-neutral-700 dark:text-neutral-300 mb-2">
              کد ملی (۱۰ رقم)
            </label>
            <UInput
              v-model="form.national_code"
              placeholder="مثال: ۰۰۱۲۳۴۵۶۷۸"
              maxlength="10"
              size="lg"
              class="w-full font-mono font-en text-left"
            />
          </div>

          <!-- Mobile Phone (Readonly) -->
          <div>
            <label class="block text-xs font-bold text-neutral-700 dark:text-neutral-300 mb-2">
              شماره تلفن همراه
            </label>
            <div class="relative">
              <UInput
                :model-value="authStore.user?.mobile"
                disabled
                size="lg"
                class="w-full font-mono font-en text-left bg-neutral-50 dark:bg-neutral-800/50 cursor-not-allowed opacity-80"
              />
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold bg-emerald-50 dark:bg-emerald-950/60 px-2 py-0.5 rounded-md">
                تایید شده
              </span>
            </div>
          </div>

          <!-- Email -->
          <div class="sm:col-span-2">
            <label class="block text-xs font-bold text-neutral-700 dark:text-neutral-300 mb-2">
              نشانی ایمیل
            </label>
            <UInput
              v-model="form.email"
              type="email"
              placeholder="example@mail.com"
              size="lg"
              class="w-full text-left font-mono font-en"
            />
          </div>
        </div>

        <!-- Submit Button -->
        <div class="pt-4 border-t border-neutral-100 dark:border-neutral-800 flex justify-end">
          <UButton
            type="submit"
            color="primary"
            size="lg"
            :loading="isSubmitting"
            icon="i-lucide-check"
            class="font-bold px-8 cursor-pointer"
          >
            ذخیره تغییرات
          </UButton>
        </div>
      </form>
    </div>
  </div>
</template>
