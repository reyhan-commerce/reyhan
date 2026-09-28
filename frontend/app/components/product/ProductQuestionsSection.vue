<script setup lang="ts">
import type { ProductQuestion } from '~/types/customerCare'

const props = defineProps<{
  productSlug: string
}>()

const api = useApi()
const authStore = useAuthStore()
const toast = useToast()
const { toPersianDigits } = usePersian()

const { data: response, pending: isLoading, refresh } = await useAsyncData(`product-questions-${props.productSlug}`, () =>
  api<ApiResponse<{ data: ProductQuestion[] }>>(`/products/${props.productSlug}/questions`)
)

const questions = computed(() => response.value?.data?.data ?? [])

// Question modal state
const isQuestionModalOpen = ref(false)
const newQuestionText = ref('')
const isSubmittingQuestion = ref(false)

// Answer modal state
const isAnswerModalOpen = ref(false)
const activeQuestionId = ref<number | null>(null)
const newAnswerText = ref('')
const isSubmittingAnswer = ref(false)

async function handleAskQuestion() {
  if (!authStore.isAuthenticated) {
    toast.add({
      title: 'ورود به حساب کاربری',
      description: 'برای ثبت پرسش، لطفاً ابتدا وارد حساب کاربری خود شوید.',
      color: 'warning'
    })
    return
  }

  if (!newQuestionText.value.trim() || newQuestionText.value.trim().length < 5) {
    toast.add({
      title: 'خطا',
      description: 'متن پرسش باید حداقل ۵ کاراکتر باشد.',
      color: 'error'
    })
    return
  }

  isSubmittingQuestion.value = true
  try {
    const res = await api<{ success: boolean; message: string }>(`/products/${props.productSlug}/questions`, {
      method: 'POST',
      body: { question: newQuestionText.value.trim() }
    })

    toast.add({
      title: 'ثبت شد',
      description: res.message || 'پرسش شما با موفقیت ثبت شد و پس از تأیید نمایش داده خواهد شد.',
      color: 'success'
    })

    newQuestionText.value = ''
    isQuestionModalOpen.value = false
  } catch (err: unknown) {
    const errorObj = err as { response?: { _data?: { message?: string } } }
    toast.add({
      title: 'خطا در ثبت پرسش',
      description: errorObj.response?._data?.message || 'ثبت پرسش با خطا مواجه شد.',
      color: 'error'
    })
  } finally {
    isSubmittingQuestion.value = false
  }
}

function openAnswerModal(questionId: number) {
  if (!authStore.isAuthenticated) {
    toast.add({
      title: 'ورود به حساب کاربری',
      description: 'برای ثبت پاسخ، لطفاً ابتدا وارد حساب کاربری خود شوید.',
      color: 'warning'
    })
    return
  }
  activeQuestionId.value = questionId
  newAnswerText.value = ''
  isAnswerModalOpen.value = true
}

async function handleAnswerQuestion() {
  if (!activeQuestionId.value || !newAnswerText.value.trim()) return

  isSubmittingAnswer.value = true
  try {
    const res = await api<{ success: boolean; message: string }>(`/questions/${activeQuestionId.value}/answers`, {
      method: 'POST',
      body: { answer: newAnswerText.value.trim() }
    })

    toast.add({
      title: 'ثبت شد',
      description: res.message || 'پاسخ شما با موفقیت ثبت شد.',
      color: 'success'
    })

    isAnswerModalOpen.value = false
  } catch (err: unknown) {
    const errorObj = err as { response?: { _data?: { message?: string } } }
    toast.add({
      title: 'خطا در ثبت پاسخ',
      description: errorObj.response?._data?.message || 'ثبت پاسخ با خطا مواجه شد.',
      color: 'error'
    })
  } finally {
    isSubmittingAnswer.value = false
  }
}

async function handleLikeQuestion(q: ProductQuestion) {
  try {
    const res = await api<{ success: boolean; data: { likes_count: number } }>(`/questions/${q.id}/like`, {
      method: 'POST'
    })
    if (res.data) {
      q.likes_count = res.data.likes_count
    }
  } catch {
    // silently ignore
  }
}
</script>

<template>
  <div class="flex flex-col gap-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-neutral-100 dark:border-neutral-800">
      <div class="flex items-center gap-2.5">
        <div class="size-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
          <UIcon name="i-lucide-help-circle" class="size-5" />
        </div>
        <div>
          <h3 class="font-black text-base sm:text-lg text-neutral-900 dark:text-white">
            پرسش و پاسخ پیرامون کالا
          </h3>
          <p class="text-xs text-neutral-500">
            پرسش‌های کاربران و پاسخ‌های رسمی کارشناسان و خریداران
          </p>
        </div>
      </div>

      <UButton
        color="primary"
        size="sm"
        icon="i-lucide-plus"
        class="font-bold cursor-pointer"
        @click="isQuestionModalOpen = true"
      >
        ثبت پرسش جدید
      </UButton>
    </div>

    <!-- Empty State -->
    <div
      v-if="!questions || questions.length === 0"
      class="py-8 flex flex-col items-center justify-center gap-2 text-center"
    >
      <div class="size-12 rounded-2xl bg-neutral-100 dark:bg-neutral-800 text-neutral-400 flex items-center justify-center">
        <UIcon name="i-lucide-message-circle-question" class="size-6" />
      </div>
      <p class="font-bold text-sm text-neutral-700 dark:text-neutral-300">
        هنوز پرسشی برای این کالا ثبت نشده است
      </p>
      <p class="text-xs text-neutral-400">
        اولین نفری باشید که درباره این کالا سوال می‌پرسد.
      </p>
    </div>

    <!-- Questions List -->
    <div v-else class="flex flex-col gap-5">
      <div
        v-for="q in questions"
        :key="q.id"
        class="p-5 rounded-2xl bg-neutral-50/60 dark:bg-neutral-800/30 border border-neutral-200/70 dark:border-neutral-800 flex flex-col gap-3.5"
      >
        <div class="flex items-start justify-between gap-3">
          <div class="flex items-start gap-2.5">
            <span class="size-6 rounded-lg bg-primary/15 text-primary text-xs font-black flex items-center justify-center shrink-0 mt-0.5">
              ؟
            </span>
            <div class="flex flex-col gap-1">
              <p class="font-bold text-sm text-neutral-900 dark:text-white leading-relaxed">
                {{ q.question }}
              </p>
              <div class="flex items-center gap-2 text-[11px] text-neutral-400">
                <span>{{ q.author_name }}</span>
                <span>•</span>
                <span>{{ q.created_at_jalali }}</span>
              </div>
            </div>
          </div>

          <button
            type="button"
            class="flex items-center gap-1 text-xs text-neutral-400 hover:text-primary transition-colors cursor-pointer px-2 py-1 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800"
            @click="handleLikeQuestion(q)"
          >
            <UIcon name="i-lucide-thumbs-up" class="size-3.5" />
            <span class="font-mono">{{ toPersianDigits(q.likes_count) }}</span>
          </button>
        </div>

        <!-- Answers Thread -->
        <div
          v-if="q.answers && q.answers.length > 0"
          class="pr-5 sm:pr-8 flex flex-col gap-2.5 border-r-2 border-primary/30 mt-1"
        >
          <div
            v-for="a in q.answers"
            :key="a.id"
            class="p-3.5 rounded-xl bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800 text-xs flex flex-col gap-1.5 shadow-2xs"
          >
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span
                  class="font-bold"
                  :class="a.is_staff ? 'text-primary' : 'text-neutral-800 dark:text-neutral-200'"
                >
                  {{ a.author_name }}
                </span>
                <span
                  v-if="a.is_staff"
                  class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-primary/10 text-primary"
                >
                  پاسخ رسمی
                </span>
              </div>
              <span class="text-[10px] text-neutral-400">{{ a.created_at_jalali }}</span>
            </div>

            <p class="text-neutral-700 dark:text-neutral-300 leading-relaxed whitespace-pre-line">
              {{ a.answer }}
            </p>
          </div>
        </div>

        <!-- Add Answer Button -->
        <div class="flex justify-end pt-1">
          <button
            type="button"
            class="text-xs text-primary font-bold hover:underline cursor-pointer flex items-center gap-1"
            @click="openAnswerModal(q.id)"
          >
            <UIcon name="i-lucide-reply" class="size-3.5" />
            <span>ثبت پاسخ به این پرسش</span>
          </button>
        </div>
      </div>
    </div>

    <!-- ASK QUESTION MODAL -->
    <UModal v-model:open="isQuestionModalOpen">
      <template #content>
        <div class="p-6 flex flex-col gap-4">
          <div class="flex items-center justify-between pb-3 border-b border-neutral-100 dark:border-neutral-800">
            <h3 class="font-bold text-base text-neutral-900 dark:text-white">
              ثبت پرسش جدید درباره کالا
            </h3>
            <UButton color="neutral" variant="ghost" icon="i-lucide-x" size="xs" @click="isQuestionModalOpen = false" />
          </div>

          <div class="space-y-1.5">
            <label class="text-xs font-bold text-neutral-700 dark:text-neutral-300">
              متن پرسش شما:
            </label>
            <UTextarea
              v-model="newQuestionText"
              placeholder="پرسش خود را شفاف و دقیق مطرح نمایید..."
              :rows="4"
              class="w-full"
            />
          </div>

          <div class="flex items-center justify-end gap-2 pt-2">
            <UButton color="neutral" variant="outline" size="sm" @click="isQuestionModalOpen = false">انصراف</UButton>
            <UButton
              color="primary"
              size="sm"
              icon="i-lucide-send"
              :loading="isSubmittingQuestion"
              class="font-bold cursor-pointer"
              @click="handleAskQuestion"
            >
              ارسال پرسش
            </UButton>
          </div>
        </div>
      </template>
    </UModal>

    <!-- ANSWER MODAL -->
    <UModal v-model:open="isAnswerModalOpen">
      <template #content>
        <div class="p-6 flex flex-col gap-4">
          <div class="flex items-center justify-between pb-3 border-b border-neutral-100 dark:border-neutral-800">
            <h3 class="font-bold text-base text-neutral-900 dark:text-white">
              ثبت پاسخ به پرسش
            </h3>
            <UButton color="neutral" variant="ghost" icon="i-lucide-x" size="xs" @click="isAnswerModalOpen = false" />
          </div>

          <div class="space-y-1.5">
            <label class="text-xs font-bold text-neutral-700 dark:text-neutral-300">
              متن پاسخ شما:
            </label>
            <UTextarea
              v-model="newAnswerText"
              placeholder="تجربه یا اطلاعات خود درباره این کالا را بنویسید..."
              :rows="4"
              class="w-full"
            />
          </div>

          <div class="flex items-center justify-end gap-2 pt-2">
            <UButton color="neutral" variant="outline" size="sm" @click="isAnswerModalOpen = false">انصراف</UButton>
            <UButton
              color="primary"
              size="sm"
              icon="i-lucide-send"
              :loading="isSubmittingAnswer"
              class="font-bold cursor-pointer"
              @click="handleAnswerQuestion"
            >
              ارسال پاسخ
            </UButton>
          </div>
        </div>
      </template>
    </UModal>
  </div>
</template>
