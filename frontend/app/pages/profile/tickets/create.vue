<script setup lang="ts">
import type { TicketDepartment, TicketPriority } from '~/types/customerCare'

definePageMeta({
  middleware: 'auth'
})

useSeoMeta({
  title: 'ارسال تیکت پشتیبانی جدید',
  description: 'ثبت درخواست و تیکت پشتیبانی جدید'
})

const router = useRouter()
const api = useApi()
const toast = useToast()
const settingsStore = useSettingsStore()

const departments = [
  { value: 'support', label: 'پشتیبانی فنی و عمومی' },
  { value: 'finance', label: 'امور مالی و حسابداری' },
  { value: 'sales', label: 'فروش و مشاوره خرید' },
  { value: 'shipping', label: 'پیگیری ارسال و مرسولات' },
  { value: 'complaints', label: 'انتقادات و شکایات' },
]

const priorities = [
  { value: 'low', label: 'کم' },
  { value: 'medium', label: 'متوسط' },
  { value: 'high', label: 'زیاد' },
  { value: 'urgent', label: 'فوری و اضطراری' },
]

const form = ref({
  subject: '',
  department: 'support' as TicketDepartment,
  priority: 'medium' as TicketPriority,
  message: '',
})

const isSubmitting = ref(false)

async function handleSubmit() {
  if (!form.value.subject.trim()) {
    toast.add({ title: 'خطا', description: 'لطفاً موضوع تیکت را وارد نمایید.', color: 'error' })
    return
  }

  if (!form.value.message.trim() || form.value.message.trim().length < 5) {
    toast.add({ title: 'خطا', description: 'متن پیام تیکت باید حداقل ۵ کاراکتر باشد.', color: 'error' })
    return
  }

  isSubmitting.value = true
  try {
    const res = await api<{
      success: boolean
      message: string
      data: { ticket_number: string }
    }>('/tickets', {
      method: 'POST',
      body: form.value
    })

    toast.add({
      title: 'ثبت موفق',
      description: res.message || 'تیکت پشتیبانی شما با موفقیت ثبت شد.',
      color: 'success'
    })

    router.push(`/profile/tickets/${res.data.ticket_number}`)
  } catch (err: unknown) {
    const errorObj = err as { response?: { _data?: { message?: string } } }
    toast.add({
      title: 'خطا در ثبت تیکت',
      description: errorObj.response?._data?.message || 'ارسال تیکت با خطا مواجه شد.',
      color: 'error'
    })
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <div class="flex flex-col gap-6">
    <div class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-6 sm:p-8 shadow-xs flex flex-col gap-2">
      <div class="flex items-center gap-2 text-xs text-neutral-400">
        <NuxtLink to="/profile/tickets" class="hover:text-primary">تیکت‌ها</NuxtLink>
        <span>/</span>
        <span>تیکت جدید</span>
      </div>
      <h1 class="text-xl sm:text-2xl font-black text-neutral-900 dark:text-white mt-1">
        ارسال تیکت پشتیبانی جدید
      </h1>
      <p class="text-xs text-neutral-500">
        پیام خود را ارسال نمایید، کارشناسان ما در سریع‌ترین زمان ممکن پاسخ خواهند داد.
      </p>
    </div>

    <div
      v-if="settingsStore.settings.support_work_hours_notice"
      class="p-4 rounded-2xl bg-primary/5 border border-primary/20 text-xs text-neutral-600 dark:text-neutral-300 flex items-start gap-3"
    >
      <UIcon name="i-lucide-clock" class="size-5 text-primary shrink-0 mt-0.5" />
      <div>
        <span class="font-bold block text-neutral-900 dark:text-white mb-0.5">ساعات کاری و پاسخگویی:</span>
        <span>{{ settingsStore.settings.support_work_hours_notice }}</span>
      </div>
    </div>

    <!-- Ticket Form -->
    <div class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-6 sm:p-8 shadow-xs flex flex-col gap-5">
      <div class="space-y-1.5">
        <label class="text-xs font-bold text-neutral-700 dark:text-neutral-300">
          موضوع تیکت <span class="text-red-500">*</span>
        </label>
        <input
          v-model="form.subject"
          type="text"
          placeholder="مثال: سوال درباره زمان تحویل سفارش"
          class="w-full px-4 py-2.5 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800 text-neutral-900 dark:text-white text-xs font-medium focus:outline-none focus:border-primary"
        />
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="space-y-1.5">
          <label class="text-xs font-bold text-neutral-700 dark:text-neutral-300">
            دپارتمان مربوطه:
          </label>
          <select
            v-model="form.department"
            class="w-full px-4 py-2.5 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800 text-neutral-900 dark:text-white text-xs font-medium focus:outline-none focus:border-primary"
          >
            <option v-for="d in departments" :key="d.value" :value="d.value">{{ d.label }}</option>
          </select>
        </div>

        <div class="space-y-1.5">
          <label class="text-xs font-bold text-neutral-700 dark:text-neutral-300">
            اولویت پاسخگویی:
          </label>
          <select
            v-model="form.priority"
            class="w-full px-4 py-2.5 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800 text-neutral-900 dark:text-white text-xs font-medium focus:outline-none focus:border-primary"
          >
            <option v-for="p in priorities" :key="p.value" :value="p.value">{{ p.label }}</option>
          </select>
        </div>
      </div>

      <div class="space-y-1.5">
        <label class="text-xs font-bold text-neutral-700 dark:text-neutral-300">
          متن پیام تیکت <span class="text-red-500">*</span>
        </label>
        <UTextarea
          v-model="form.message"
          placeholder="شرح کامل پرسش، درخواست یا مشکل خود را به همراه جزئیات بنویسید..."
          :rows="6"
          class="w-full"
        />
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center justify-end gap-3 pt-3 border-t border-neutral-100 dark:border-neutral-800">
        <NuxtLink
          to="/profile/tickets"
          class="px-5 py-2.5 rounded-xl border border-neutral-200 dark:border-neutral-700 text-xs font-bold text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50"
        >
          انصراف
        </NuxtLink>
        <UButton
          color="primary"
          size="lg"
          icon="i-lucide-send"
          :loading="isSubmitting"
          class="rounded-xl px-7 font-bold cursor-pointer"
          @click="handleSubmit"
        >
          ارسال تیکت پشتیبانی
        </UButton>
      </div>
    </div>
  </div>
</template>
