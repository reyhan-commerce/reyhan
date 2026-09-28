<script setup lang="ts">
import type { SupportTicket } from '~/types/customerCare'

definePageMeta({
  middleware: 'auth'
})

const route = useRoute()
const api = useApi()
const toast = useToast()

const ticketNumber = computed(() => String(route.params.ticketNumber))

const { data: ticketResponse, pending: isLoading, refresh } = await useAsyncData(`ticket-${ticketNumber.value}`, () =>
  api<ApiResponse<SupportTicket>>(`/tickets/${ticketNumber.value}`)
)

const ticket = computed(() => ticketResponse.value?.data)

useSeoMeta({
  title: () => ticket.value ? `تیکت ${ticket.value.ticket_number} - ${ticket.value.subject}` : 'تیکت پشتیبانی'
})

const replyMessage = ref('')
const isSendingReply = ref(false)
const isClosing = ref(false)

async function handleSendReply() {
  if (!replyMessage.value.trim() || replyMessage.value.trim().length < 2) {
    toast.add({ title: 'خطا', description: 'متن پاسخ را وارد فرمایید.', color: 'error' })
    return
  }

  isSendingReply.value = true
  try {
    await api(`/tickets/${ticketNumber.value}/messages`, {
      method: 'POST',
      body: { message: replyMessage.value.trim() }
    })

    replyMessage.value = ''
    toast.add({ title: 'ارسال شد', description: 'پاسخ شما با موفقیت ارسال شد.', color: 'success' })
    await refresh()
  } catch (err: unknown) {
    const errorObj = err as { response?: { _data?: { message?: string } } }
    toast.add({
      title: 'خطا در ارسال',
      description: errorObj.response?._data?.message || 'ارسال پاسخ با خطا مواجه شد.',
      color: 'error'
    })
  } finally {
    isSendingReply.value = false
  }
}

async function handleCloseTicket() {
  if (!confirm('آیا از بستن این تیکت اطمینان دارید؟ پس از بستن امکان ارسال پاسخ جدید وجود ندارد.')) {
    return
  }

  isClosing.value = true
  try {
    await api(`/tickets/${ticketNumber.value}/close`, { method: 'PUT' })
    toast.add({ title: 'بسته شد', description: 'تیکت با موفقیت بسته شد.', color: 'info' })
    await refresh()
  } catch {
    toast.add({ title: 'خطا', description: 'بستن تیکت با خطا مواجه شد.', color: 'error' })
  } finally {
    isClosing.value = false
  }
}
</script>

<template>
  <div class="flex flex-col gap-6">
    <!-- Header -->
    <div
      v-if="ticket"
      class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-6 sm:p-8 shadow-xs flex flex-col gap-4"
    >
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-xs text-neutral-400">
          <NuxtLink to="/profile/tickets" class="hover:text-primary">تیکت‌های من</NuxtLink>
          <span>/</span>
          <span class="font-mono text-neutral-700 dark:text-neutral-300">{{ ticket.ticket_number }}</span>
        </div>

        <button
          v-if="ticket.status !== 'closed'"
          type="button"
          :disabled="isClosing"
          class="text-xs text-neutral-500 hover:text-red-600 transition-colors flex items-center gap-1 font-bold cursor-pointer"
          @click="handleCloseTicket"
        >
          <UIcon name="i-lucide-lock" class="size-3.5" />
          <span>بستن تیکت</span>
        </button>
      </div>

      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-lg sm:text-xl font-black text-neutral-900 dark:text-white">
            {{ ticket.subject }}
          </h1>
          <div class="flex flex-wrap items-center gap-2 mt-2 text-xs">
            <span class="bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 font-bold px-2.5 py-0.5 rounded-lg">
              دپارتمان: {{ ticket.department_label }}
            </span>
            <span class="bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 font-bold px-2.5 py-0.5 rounded-lg">
              اولویت: {{ ticket.priority_label }}
            </span>
            <span v-if="ticket.order_number" class="bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 font-bold px-2.5 py-0.5 rounded-lg">
              سفارش: {{ ticket.order_number }}
            </span>
          </div>
        </div>

        <span
          class="px-3 py-1 rounded-full text-xs font-bold self-start sm:self-center"
          :class="{
            'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-400': ticket.status === 'open',
            'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400': ticket.status === 'answered',
            'bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-400': ticket.status === 'awaiting_reply',
            'bg-neutral-100 text-neutral-600 border border-neutral-200 dark:bg-neutral-800 dark:text-neutral-400': ticket.status === 'closed',
          }"
        >
          {{ ticket.status_label }}
        </span>
      </div>
    </div>

    <!-- Message Thread -->
    <div v-if="ticket" class="flex flex-col gap-4">
      <div
        v-for="msg in ticket.messages"
        :key="msg.id"
        class="flex flex-col gap-2 p-5 rounded-2xl shadow-xs"
        :class="msg.is_staff
          ? 'bg-primary/5 dark:bg-primary/10 border border-primary/20 mr-0 sm:mr-8'
          : 'bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 ml-0 sm:ml-8'"
      >
        <div class="flex items-center justify-between pb-2 border-b" :class="msg.is_staff ? 'border-primary/15' : 'border-neutral-100 dark:border-neutral-800'">
          <div class="flex items-center gap-2">
            <div
              class="size-7 rounded-lg flex items-center justify-center text-xs"
              :class="msg.is_staff ? 'bg-primary text-white font-bold' : 'bg-neutral-200 dark:bg-neutral-700 text-neutral-700 dark:text-white'"
            >
              <UIcon :name="msg.is_staff ? 'i-lucide-headset' : 'i-lucide-user'" class="size-4" />
            </div>
            <span class="font-bold text-xs" :class="msg.is_staff ? 'text-primary' : 'text-neutral-900 dark:text-white'">
              {{ msg.author_name }}
            </span>
          </div>

          <span class="text-[11px] text-neutral-400">
            {{ msg.created_at_jalali }}
          </span>
        </div>

        <p class="text-xs sm:text-sm text-neutral-800 dark:text-neutral-200 leading-relaxed whitespace-pre-line pt-1">
          {{ msg.message }}
        </p>
      </div>
    </div>

    <!-- Reply Box (if not closed) -->
    <div
      v-if="ticket && ticket.status !== 'closed'"
      class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-5 sm:p-6 shadow-xs flex flex-col gap-3"
    >
      <label class="text-xs font-bold text-neutral-700 dark:text-neutral-300 flex items-center gap-2">
        <UIcon name="i-lucide-corner-down-left" class="size-4 text-primary" />
        <span>ارسال پاسخ به تیکت:</span>
      </label>

      <UTextarea
        v-model="replyMessage"
        placeholder="پاسخ یا توضیحات جدید خود را بنویسید..."
        :rows="3"
        class="w-full"
      />

      <div class="flex justify-end pt-1">
        <UButton
          color="primary"
          size="md"
          icon="i-lucide-send"
          :loading="isSendingReply"
          class="font-bold px-6 cursor-pointer"
          @click="handleSendReply"
        >
          ارسال پاسخ
        </UButton>
      </div>
    </div>

    <!-- Closed Notice -->
    <div
      v-else-if="ticket && ticket.status === 'closed'"
      class="p-4 rounded-2xl bg-neutral-100 dark:bg-neutral-800/50 border border-neutral-200 dark:border-neutral-700 text-center text-xs text-neutral-500 flex items-center justify-center gap-2"
    >
      <UIcon name="i-lucide-lock" class="size-4" />
      <span>این تیکت بسته شده است. در صورت نیاز می‌توانید یک تیکت جدید ارسال نمایید.</span>
    </div>
  </div>
</template>
