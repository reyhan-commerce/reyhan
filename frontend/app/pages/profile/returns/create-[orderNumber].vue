<script setup lang="ts">
import type { Order } from '~/types/order'

definePageMeta({
  middleware: 'auth'
})

useSeoMeta({
  title: 'ثبت درخواست مرجوعی کالا',
  description: 'فرم آنلاین مرجوعی کالا و استرداد وجه'
})

const route = useRoute()
const router = useRouter()
const api = useApi()
const toast = useToast()
const settingsStore = useSettingsStore()
const { formatPrice, toPersianDigits } = usePersian()

const orderNumber = computed(() => String(route.params.orderNumber))

const { data: orderResponse, pending: isLoadingOrder } = await useAsyncData(`order-${orderNumber.value}`, () =>
  api<ApiResponse<Order>>(`/orders/${orderNumber.value}`)
)

const order = computed(() => orderResponse.value?.data)

const reasons = [
  'عدم تطابق مشخصات یا رنگ کالا با اطلاعات سایت',
  'ایراد فنی، نقص عملکردی یا خرابی ظاهری',
  'آسیب‌دیدگی فیزیکی در حین حمل و نقل',
  'انصراف از خرید (پلمپ و بسته‌بندی کاملاً باز نشده)',
  'ارسال اشتباه کالا توسط فروشگاه',
]

const selectedReason = ref(reasons[0])
const description = ref('')
const selectedItems = ref<Record<number, boolean>>({})
const itemQuantities = ref<Record<number, number>>({})
const isSubmitting = ref(false)

watch(order, (val) => {
  if (val?.items) {
    val.items.forEach((item) => {
      selectedItems.value[item.id] = true
      itemQuantities.value[item.id] = item.quantity
    })
  }
}, { immediate: true })

async function handleSubmit() {
  const chosenItems = order.value?.items?.filter(item => selectedItems.value[item.id]) ?? []

  if (chosenItems.length === 0) {
    toast.add({
      title: 'انتخاب کالا',
      description: 'لطفاً حداقل یک کالا را جهت مرجوعی انتخاب کنید.',
      color: 'error'
    })
    return
  }

  isSubmitting.value = true
  try {
    const payload = {
      reason: selectedReason.value,
      description: description.value.trim() || undefined,
      refund_method: 'wallet',
      items: chosenItems.map(item => ({
        order_item_id: item.id,
        quantity: itemQuantities.value[item.id] || item.quantity,
        reason: selectedReason.value,
      }))
    }

    const res = await api<{ success: boolean; message: string }>(`/orders/${orderNumber.value}/returns`, {
      method: 'POST',
      body: payload
    })

    toast.add({
      title: 'ثبت موفق',
      description: res.message || 'درخواست مرجوعی با موفقیت ثبت شد.',
      color: 'success'
    })

    router.push('/profile/returns')
  } catch (err: unknown) {
    const errorObj = err as { response?: { _data?: { message?: string } } }
    toast.add({
      title: 'خطا در ثبت درخواست',
      description: errorObj.response?._data?.message || 'ثبت درخواست مرجوعی با خطا مواجه شد.',
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
        <NuxtLink to="/profile/orders" class="hover:text-primary">سفارشات</NuxtLink>
        <span>/</span>
        <span class="text-neutral-700 dark:text-neutral-300">سفارش {{ orderNumber }}</span>
        <span>/</span>
        <span>مرجوعی کالا</span>
      </div>
      <h1 class="text-xl sm:text-2xl font-black text-neutral-900 dark:text-white mt-1">
        ثبت درخواست مرجوعی سفارش {{ orderNumber }}
      </h1>
      <p class="text-xs text-neutral-500">
        {{ settingsStore.settings.return_policy_notice || 'اقلام مورد نظر را انتخاب و علت مرجوعی را مشخص فرمایید.' }}
      </p>
    </div>

    <!-- Items Selection Form -->
    <div v-if="order" class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-6 sm:p-8 shadow-xs flex flex-col gap-6">
      <h2 class="font-bold text-base text-neutral-900 dark:text-white pb-3 border-b border-neutral-100 dark:border-neutral-800">
        انتخاب اقلام جهت استرداد
      </h2>

      <div class="flex flex-col divide-y divide-neutral-100 dark:divide-neutral-800">
        <div
          v-for="item in order.items"
          :key="item.id"
          class="py-4 flex items-center justify-between gap-4"
        >
          <div class="flex items-center gap-3">
            <input
              v-model="selectedItems[item.id]"
              type="checkbox"
              class="size-5 rounded-md border-neutral-300 text-primary focus:ring-primary cursor-pointer"
            />
            <div>
              <h4 class="font-bold text-sm text-neutral-900 dark:text-white">
                {{ item.product_name }}
              </h4>
              <span v-if="item.variant_title" class="text-xs text-neutral-500">
                تنوع: {{ item.variant_title }}
              </span>
              <div class="text-xs font-mono font-bold text-primary mt-1">
                {{ formatPrice(item.unit_price) }}
              </div>
            </div>
          </div>

          <div v-if="selectedItems[item.id]" class="flex items-center gap-2">
            <span class="text-xs text-neutral-500">تعداد:</span>
            <select
              v-model.number="itemQuantities[item.id]"
              class="px-3 py-1.5 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800 text-xs font-bold"
            >
              <option
                v-for="n in item.quantity"
                :key="n"
                :value="n"
              >
                {{ toPersianDigits(n) }} عدد
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- Reason & Description -->
      <div class="pt-4 border-t border-neutral-100 dark:border-neutral-800 flex flex-col gap-4">
        <div class="space-y-1.5">
          <label class="text-xs font-bold text-neutral-700 dark:text-neutral-300">
            علت درخواست مرجوعی:
          </label>
          <select
            v-model="selectedReason"
            class="w-full px-4 py-2.5 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800 text-neutral-900 dark:text-white text-xs font-medium focus:outline-none focus:border-primary"
          >
            <option v-for="r in reasons" :key="r" :value="r">{{ r }}</option>
          </select>
        </div>

        <div class="space-y-1.5">
          <label class="text-xs font-bold text-neutral-700 dark:text-neutral-300">
            توضیحات تکمیلی یا جزئیات ایراد:
          </label>
          <UTextarea
            v-model="description"
            placeholder="لطفاً شرح کاملی از علت مرجوعی بنویسید تا بررسی سریع‌تر انجام شود..."
            :rows="3"
            class="w-full"
          />
        </div>
      </div>

      <!-- Refund Destination Note -->
      <div class="p-3.5 rounded-2xl bg-primary/5 border border-primary/20 text-xs text-neutral-600 dark:text-neutral-300 flex items-start gap-2.5">
        <UIcon name="i-lucide-wallet" class="size-5 text-primary shrink-0 mt-0.5" />
        <div>
          <span class="font-bold block text-neutral-900 dark:text-white mb-0.5">شیوه استرداد وجه:</span>
          <span>پس از تأیید کارشناسان و دریافت کالا در انبار، مبلغ کل اقلام به صورت آنی به کیف پول کاربری شما واریز خواهد شد.</span>
        </div>
      </div>

      <!-- Submit Action -->
      <div class="flex items-center justify-end gap-3 pt-2">
        <NuxtLink
          to="/profile/orders"
          class="px-5 py-2.5 rounded-xl border border-neutral-200 dark:border-neutral-700 text-xs font-bold text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50"
        >
          انصراف
        </NuxtLink>
        <UButton
          color="primary"
          size="lg"
          icon="i-lucide-check-circle"
          :loading="isSubmitting"
          class="rounded-xl px-6 font-bold cursor-pointer"
          @click="handleSubmit"
        >
          ثبت نهایی درخواست مرجوعی
        </UButton>
      </div>
    </div>
  </div>
</template>
