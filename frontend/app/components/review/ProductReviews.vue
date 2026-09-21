<script setup lang="ts">
import SubmitReviewModal from '~/components/review/SubmitReviewModal.vue'
import { useAuthStore } from '~/stores/auth'

const props = defineProps<{
  productId: number
  productName: string
}>()

const api = useApi()
const authStore = useAuthStore()
const { toPersianDigits } = usePersian()

const isLoading = ref(true)
const isModalOpen = ref(false)
const reviews = ref<any[]>([])
const stats = ref({
  average_rating: 5,
  average_longevity: 5,
  average_coverage: 5,
  average_value: 5,
  total_reviews: 0,
})

const fetchReviews = async () => {
  isLoading.value = true
  try {
    const res = await api<any>(`/products/${props.productId}/reviews`)
    if (res.data) {
      stats.value = res.data.stats || stats.value
      reviews.value = res.data.reviews?.data || []
    }
  } catch {
    // handled
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchReviews()
})

function handleOpenModal() {
  if (!authStore.isAuthenticated) {
    authStore.openAuthModal()
    return
  }
  isModalOpen.value = true
}
</script>

<template>
  <div class="flex flex-col gap-8">
    <!-- Reviews Header & Summary Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
      <!-- Rating Summary Card (4 cols) -->
      <div class="lg:col-span-4 bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-6 shadow-xs flex flex-col gap-5">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-neutral-400">امتیاز کاربران</span>
          <span class="text-xs text-neutral-400">
            {{ toPersianDigits(stats.total_reviews) }} دیدگاه ثبت شده
          </span>
        </div>

        <!-- Big Score Display -->
        <div class="flex items-baseline gap-2">
          <span class="text-4xl sm:text-5xl font-black text-neutral-900 dark:text-white">
            {{ toPersianDigits(stats.average_rating) }}
          </span>
          <span class="text-sm text-neutral-400">از ۵</span>
        </div>

        <!-- Stars Row -->
        <div class="flex items-center gap-1 dir-ltr text-amber-400">
          <UIcon
            v-for="i in 5"
            :key="i"
            name="i-lucide-star"
            class="w-5 h-5"
            :class="[
              i <= Math.round(stats.average_rating)
                ? 'fill-amber-400'
                : 'text-neutral-300 dark:text-neutral-700'
            ]"
          />
        </div>

        <!-- Multi-Dimensional Progress Bars -->
        <div class="space-y-3 pt-3 border-t border-neutral-100 dark:border-neutral-800 text-xs">
          <!-- Longevity -->
          <div class="space-y-1">
            <div class="flex justify-between text-neutral-600 dark:text-neutral-300">
              <span>ماندگاری</span>
              <span class="font-bold">{{ toPersianDigits(stats.average_longevity) }} از ۵</span>
            </div>
            <div class="w-full h-1.5 rounded-full bg-neutral-100 dark:bg-neutral-800 overflow-hidden">
              <div
                class="h-full bg-primary-500 rounded-full transition-all duration-500"
                :style="{ width: `${(stats.average_longevity / 5) * 100}%` }"
              />
            </div>
          </div>

          <!-- Coverage -->
          <div class="space-y-1">
            <div class="flex justify-between text-neutral-600 dark:text-neutral-300">
              <span>میزان پوشانندگی</span>
              <span class="font-bold">{{ toPersianDigits(stats.average_coverage) }} از ۵</span>
            </div>
            <div class="w-full h-1.5 rounded-full bg-neutral-100 dark:bg-neutral-800 overflow-hidden">
              <div
                class="h-full bg-primary-500 rounded-full transition-all duration-500"
                :style="{ width: `${(stats.average_coverage / 5) * 100}%` }"
              />
            </div>
          </div>

          <!-- Value -->
          <div class="space-y-1">
            <div class="flex justify-between text-neutral-600 dark:text-neutral-300">
              <span>ارزش خرید</span>
              <span class="font-bold">{{ toPersianDigits(stats.average_value) }} از ۵</span>
            </div>
            <div class="w-full h-1.5 rounded-full bg-neutral-100 dark:bg-neutral-800 overflow-hidden">
              <div
                class="h-full bg-primary-500 rounded-full transition-all duration-500"
                :style="{ width: `${(stats.average_value / 5) * 100}%` }"
              />
            </div>
          </div>
        </div>

        <!-- Primary CTA: Write Review -->
        <UButton
          color="primary"
          variant="solid"
          size="lg"
          block
          icon="i-lucide-pen-line"
          class="mt-2 font-bold cursor-pointer"
          @click="handleOpenModal"
        >
          ثبت دیدگاه تخصصی شما
        </UButton>
      </div>

      <!-- Reviews Feed (8 cols) -->
      <div class="lg:col-span-8 flex flex-col gap-4">
        <!-- Loading State -->
        <div
          v-if="isLoading"
          class="flex items-center justify-center p-16"
        >
          <UIcon
            name="i-lucide-loader-2"
            class="w-8 h-8 text-primary-500 animate-spin"
          />
        </div>

        <!-- Empty State -->
        <div
          v-else-if="reviews.length === 0"
          class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-10 text-center shadow-xs flex flex-col items-center justify-center gap-4"
        >
          <div class="w-16 h-16 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-400 flex items-center justify-center">
            <UIcon
              name="i-lucide-message-square"
              class="w-8 h-8"
            />
          </div>
          <div class="flex flex-col gap-1">
            <h3 class="font-bold text-neutral-800 dark:text-neutral-200 text-sm sm:text-base">
              هنوز دیدگاهی برای این محصول ثبت نشده است
            </h3>
            <p class="text-xs text-neutral-400">
              شما اولین نفری باشید که تجربه استفاده از این محصول آرایشی را با دیگران به اشتراک می‌گذارد.
            </p>
          </div>
          <UButton
            color="primary"
            variant="subtle"
            size="md"
            icon="i-lucide-plus"
            class="font-bold cursor-pointer"
            @click="handleOpenModal"
          >
            نوشتن اولین نظر
          </UButton>
        </div>

        <!-- Review Cards -->
        <div
          v-else
          class="flex flex-col gap-4"
        >
          <div
            v-for="review in reviews"
            :key="review.id"
            class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-5 sm:p-6 shadow-xs flex flex-col gap-4"
          >
            <!-- User Info & Verified Buyer Badge -->
            <div class="flex flex-wrap items-center justify-between gap-3">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 font-bold flex items-center justify-center text-xs">
                  {{ review.user_name?.[0] || 'ک' }}
                </div>
                <div class="flex flex-col">
                  <span class="font-bold text-xs sm:text-sm text-neutral-900 dark:text-white">
                    {{ review.user_name }}
                  </span>
                  <!-- Verified Buyer Chip -->
                  <div
                    v-if="review.is_verified_purchase"
                    class="flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400"
                  >
                    <UIcon
                      name="i-lucide-badge-check"
                      class="w-3.5 h-3.5"
                    />
                    <span>خریدار این کالا</span>
                  </div>
                </div>
              </div>

              <!-- Rating Stars -->
              <div class="flex items-center gap-0.5 dir-ltr text-amber-400">
                <UIcon
                  v-for="s in 5"
                  :key="s"
                  name="i-lucide-star"
                  class="w-4 h-4"
                  :class="[
                    s <= review.rating
                      ? 'fill-amber-400'
                      : 'text-neutral-200 dark:text-neutral-700'
                  ]"
                />
              </div>
            </div>

            <!-- Comment Body -->
            <p class="text-xs sm:text-sm text-neutral-700 dark:text-neutral-300 leading-relaxed">
              {{ review.comment }}
            </p>

            <!-- Strengths and Weaknesses -->
            <div
              v-if="(review.strengths && review.strengths.length > 0) || (review.weaknesses && review.weaknesses.length > 0)"
              class="flex flex-wrap gap-2 pt-1"
            >
              <span
                v-for="(str, idx) in review.strengths"
                :key="`str-${idx}`"
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200/50 dark:border-emerald-800/30"
              >
                <span>+</span>
                <span>{{ str }}</span>
              </span>

              <span
                v-for="(wk, idx) in review.weaknesses"
                :key="`wk-${idx}`"
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-200/50 dark:border-rose-800/30"
              >
                <span>-</span>
                <span>{{ wk }}</span>
              </span>
            </div>

            <!-- Admin Reply if present -->
            <div
              v-if="review.admin_reply"
              class="mt-2 p-3.5 rounded-2xl bg-neutral-50 dark:bg-neutral-800/60 border border-neutral-200/60 dark:border-neutral-700/50 text-xs flex flex-col gap-1"
            >
              <span class="font-bold text-primary-600 dark:text-primary-400 flex items-center gap-1">
                <UIcon
                  name="i-lucide-shield-check"
                  class="w-4 h-4"
                />
                پاسخ کارشناس پشتیبانی ایزیشاپ:
              </span>
              <p class="text-neutral-600 dark:text-neutral-300 leading-relaxed">
                {{ review.admin_reply }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Submit Modal -->
    <SubmitReviewModal
      v-model:open="isModalOpen"
      :product-id="productId"
      :product-name="productName"
      @submitted="fetchReviews"
    />
  </div>
</template>
