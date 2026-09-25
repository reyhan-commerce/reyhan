<script setup lang="ts">
import { reviewSchema } from '~/utils/schemas'

const props = defineProps<{
  productId: number
  productName: string
}>()

const emit = defineEmits<{
  (e: 'submitted'): void
}>()

const isOpen = defineModel<boolean>('open', { default: false })
const api = useApi()
const toast = useToast()

const form = reactive({
  rating: 5,
  criteria_ratings: {
    longevity: 5,
    coverage: 5,
    value: 5
  },
  longevity_rating: 5,
  coverage_rating: 5,
  value_rating: 5,
  comment: '',
  strengths: [] as string[],
  weaknesses: [] as string[]
})

const strengthInput = ref('')
const weaknessInput = ref('')
const isSubmitting = ref(false)

function addStrength() {
  const val = strengthInput.value.trim()
  if (val && !form.strengths.includes(val) && form.strengths.length < 5) {
    form.strengths.push(val)
    strengthInput.value = ''
  }
}

function removeStrength(index: number) {
  form.strengths.splice(index, 1)
}

function addWeakness() {
  const val = weaknessInput.value.trim()
  if (val && !form.weaknesses.includes(val) && form.weaknesses.length < 5) {
    form.weaknesses.push(val)
    weaknessInput.value = ''
  }
}

function removeWeakness(index: number) {
  form.weaknesses.splice(index, 1)
}

async function handleSubmit() {
  const validation = reviewSchema.safeParse({
    rating: form.rating,
    comment: form.comment.trim(),
    criteria_ratings: {
      longevity: form.longevity_rating,
      coverage: form.coverage_rating,
      value: form.value_rating
    },
    strengths: form.strengths,
    weaknesses: form.weaknesses
  })

  if (!validation.success) {
    const errorMsg = validation.error.issues[0]?.message || 'لطفاً فرم را به درستی تکمیل فرمایید.'
    toast.add({
      title: 'خطای اعتبارسنجی',
      description: errorMsg,
      color: 'error'
    })
    return
  }

  isSubmitting.value = true
  try {
    const res = await api<{ success: boolean, message: string }>(`/products/${props.productId}/reviews`, {
      method: 'POST',
      body: {
        rating: form.rating,
        criteria_ratings: {
          longevity: form.longevity_rating,
          coverage: form.coverage_rating,
          value: form.value_rating
        },
        longevity_rating: form.longevity_rating,
        coverage_rating: form.coverage_rating,
        value_rating: form.value_rating,
        comment: form.comment.trim(),
        strengths: form.strengths,
        weaknesses: form.weaknesses
      }
    })

    toast.add({
      title: 'ثبت نظر',
      description: res.message || 'دیدگاه تخصصی شما با موفقیت ثبت شد.',
      color: 'success'
    })

    isOpen.value = false
    // reset form
    form.comment = ''
    form.strengths = []
    form.weaknesses = []
    emit('submitted')
  } catch (err: unknown) {
    const errObj = err as { data?: { message?: string } }
    toast.add({
      title: 'خطا',
      description: errObj?.data?.message || 'مشکلی در ثبت دیدگاه رخ داده است.',
      color: 'error'
    })
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <UModal
    v-model:open="isOpen"
    title="ثبت نقد و بررسی تخصصی"
    :description="productName"
    :ui="{
      content: 'sm:max-w-xl',
      body: 'p-6 space-y-6'
    }"
  >
    <template #body>
      <form
        class="space-y-6"
        @submit.prevent="handleSubmit"
      >
        <!-- 1. Star Rating: Overall -->
        <div class="flex flex-col items-center justify-center p-4 rounded-2xl bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-100 dark:border-neutral-800 gap-2">
          <span class="text-xs font-bold text-neutral-600 dark:text-neutral-300">
            امتیاز کلی شما به این محصول
          </span>
          <div class="flex items-center gap-1.5 [direction:ltr]">
            <button
              v-for="star in 5"
              :key="star"
              type="button"
              class="p-1 hover:scale-125 transition-transform cursor-pointer"
              @click="form.rating = star"
            >
              <UIcon
                name="i-lucide-star"
                class="w-7 h-7"
                :class="[
                  star <= form.rating
                    ? 'text-amber-400 fill-amber-400'
                    : 'text-neutral-300 dark:text-neutral-600'
                ]"
              />
            </button>
          </div>
        </div>

        <!-- 2. Cosmetic Multi-Dimensional Scores -->
        <div class="space-y-4">
          <span class="block text-xs font-bold text-neutral-800 dark:text-neutral-200">
            ارزیابی ویژگی‌های تخصصی (از ۱ تا ۵):
          </span>

          <div class="space-y-3 bg-neutral-50 dark:bg-neutral-800/40 p-4 rounded-2xl border border-neutral-100 dark:border-neutral-800">
            <!-- Longevity (ماندگاری) -->
            <div class="flex items-center justify-between text-xs gap-4">
              <span class="text-neutral-600 dark:text-neutral-300 font-medium">ماندگاری روی پوست/مو:</span>
              <div class="flex items-center gap-1 [direction:ltr]">
                <button
                  v-for="val in 5"
                  :key="val"
                  type="button"
                  class="w-6 h-6 rounded-md text-xs font-bold transition-colors cursor-pointer"
                  :class="[
                    val <= form.longevity_rating
                      ? 'bg-primary-600 text-white shadow-xs'
                      : 'bg-neutral-200 dark:bg-neutral-700 text-neutral-500'
                  ]"
                  @click="form.longevity_rating = val"
                >
                  {{ val }}
                </button>
              </div>
            </div>

            <!-- Coverage (پوشانندگی) -->
            <div class="flex items-center justify-between text-xs gap-4">
              <span class="text-neutral-600 dark:text-neutral-300 font-medium">میزان پوشانندگی و جلوه نهایی:</span>
              <div class="flex items-center gap-1 [direction:ltr]">
                <button
                  v-for="val in 5"
                  :key="val"
                  type="button"
                  class="w-6 h-6 rounded-md text-xs font-bold transition-colors cursor-pointer"
                  :class="[
                    val <= form.coverage_rating
                      ? 'bg-primary-600 text-white shadow-xs'
                      : 'bg-neutral-200 dark:bg-neutral-700 text-neutral-500'
                  ]"
                  @click="form.coverage_rating = val"
                >
                  {{ val }}
                </button>
              </div>
            </div>

            <!-- Value (ارزش خرید) -->
            <div class="flex items-center justify-between text-xs gap-4">
              <span class="text-neutral-600 dark:text-neutral-300 font-medium">ارزش خرید نسبت به قیمت:</span>
              <div class="flex items-center gap-1 [direction:ltr]">
                <button
                  v-for="val in 5"
                  :key="val"
                  type="button"
                  class="w-6 h-6 rounded-md text-xs font-bold transition-colors cursor-pointer"
                  :class="[
                    val <= form.value_rating
                      ? 'bg-primary-600 text-white shadow-xs'
                      : 'bg-neutral-200 dark:bg-neutral-700 text-neutral-500'
                  ]"
                  @click="form.value_rating = val"
                >
                  {{ val }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- 3. Strengths Tag Input -->
        <div class="space-y-2">
          <label class="block text-xs font-bold text-neutral-700 dark:text-neutral-300">
            نقاط قوت (حداکثر ۵ مورد)
          </label>
          <div class="flex items-center gap-2">
            <UInput
              v-model="strengthInput"
              placeholder="مثال: بافت بسیار سبک و غیرچرب"
              size="sm"
              class="flex-1"
              @keydown.enter.prevent="addStrength"
            />
            <UButton
              type="button"
              color="neutral"
              variant="outline"
              size="sm"
              icon="i-lucide-plus"
              class="cursor-pointer"
              @click="addStrength"
            >
              افزودن
            </UButton>
          </div>
          <div
            v-if="form.strengths.length > 0"
            class="flex flex-wrap gap-1.5 pt-1"
          >
            <span
              v-for="(str, idx) in form.strengths"
              :key="idx"
              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/40"
            >
              <span>+ {{ str }}</span>
              <button
                type="button"
                class="hover:text-red-600 transition-colors"
                @click="removeStrength(idx)"
              >
                <UIcon
                  name="i-lucide-x"
                  class="w-3 h-3"
                />
              </button>
            </span>
          </div>
        </div>

        <!-- 4. Weaknesses Tag Input -->
        <div class="space-y-2">
          <label class="block text-xs font-bold text-neutral-700 dark:text-neutral-300">
            نقاط ضعف (حداکثر ۵ مورد)
          </label>
          <div class="flex items-center gap-2">
            <UInput
              v-model="weaknessInput"
              placeholder="مثال: حجم کم نسبت به قیمت"
              size="sm"
              class="flex-1"
              @keydown.enter.prevent="addWeakness"
            />
            <UButton
              type="button"
              color="neutral"
              variant="outline"
              size="sm"
              icon="i-lucide-plus"
              class="cursor-pointer"
              @click="addWeakness"
            >
              افزودن
            </UButton>
          </div>
          <div
            v-if="form.weaknesses.length > 0"
            class="flex flex-wrap gap-1.5 pt-1"
          >
            <span
              v-for="(wk, idx) in form.weaknesses"
              :key="idx"
              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-red-50 text-red-700 dark:bg-red-950/50 dark:text-red-300 border border-red-200/60 dark:border-red-800/40"
            >
              <span>- {{ wk }}</span>
              <button
                type="button"
                class="hover:text-red-900 transition-colors"
                @click="removeWeakness(idx)"
              >
                <UIcon
                  name="i-lucide-x"
                  class="w-3 h-3"
                />
              </button>
            </span>
          </div>
        </div>

        <!-- 5. Comment Text Area -->
        <div class="space-y-2">
          <label class="block text-xs font-bold text-neutral-700 dark:text-neutral-300">
            متن تجربه و دیدگاه شما <span class="text-red-500">*</span>
          </label>
          <UTextarea
            v-model="form.comment"
            placeholder="تجربه شخصی خود از مصرف این محصول را شرح دهید..."
            :rows="4"
            class="w-full text-sm leading-relaxed"
          />
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-neutral-100 dark:border-neutral-800">
          <UButton
            type="button"
            variant="ghost"
            color="neutral"
            class="cursor-pointer"
            @click="isOpen = false"
          >
            انصراف
          </UButton>
          <UButton
            type="submit"
            color="primary"
            :loading="isSubmitting"
            icon="i-lucide-send"
            class="font-bold px-6 cursor-pointer"
          >
            ثبت دیدگاه
          </UButton>
        </div>
      </form>
    </template>
  </UModal>
</template>
