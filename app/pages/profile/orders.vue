<script setup lang="ts">
const api = useApi()
const { formatPrice, toPersianDigits } = usePersian()

useSeoMeta({
  title: 'سفارش‌های من - ایزیشاپ',
})

const isLoading = ref(true)
const orders = ref<any[]>([])
const activeTab = ref('all')
const expandedOrder = ref<string | null>(null)
const isCopied = ref<Record<string, boolean>>({})

const fetchOrders = async () => {
  isLoading.value = true
  try {
    const res = await api<any>('/orders')
    orders.value = res.data || []
  } catch {
    // handled by useApi
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchOrders()
})

const tabs = [
  { id: 'all', label: 'همه سفارش‌ها' },
  { id: 'processing', label: 'در حال پردازش' },
  { id: 'delivered', label: 'تحویل داده شده' },
  { id: 'cancelled', label: 'لغو شده' },
]

const filteredOrders = computed(() => {
  if (activeTab.value === 'all') return orders.value
  return orders.value.filter((o) => o.status === activeTab.value)
})

const toggleExpand = (orderNumber: string) => {
  expandedOrder.value = expandedOrder.value === orderNumber ? null : orderNumber
}

const copyToClipboard = async (text: string, key: string) => {
  try {
    await navigator.clipboard.writeText(text)
    isCopied.value[key] = true
    setTimeout(() => {
      isCopied.value[key] = false
    }, 2000)
  } catch {
    // fallback
  }
}
</script>

<template>
  <div class="flex flex-col gap-6">
    <!-- Header with Tabs -->
    <div class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-lg font-black text-neutral-900 dark:text-neutral-100">
          سوابق سفارش‌ها
        </h2>
        <p class="text-xs text-neutral-400 mt-0.5">
          مشاهده و پیگیری جزئیات تمامی سفارش‌های ثبت شده
        </p>
      </div>

      <!-- Filter Tabs -->
      <div class="flex items-center gap-1.5 p-1 bg-neutral-100 dark:bg-neutral-800 rounded-2xl overflow-x-auto scrollbar-none">
        <button
          v-for="t in tabs"
          :key="t.id"
          type="button"
          class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap cursor-pointer"
          :class="[
            activeTab === t.id
              ? 'bg-white dark:bg-neutral-900 text-neutral-900 dark:text-white shadow-xs'
              : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white'
          ]"
          @click="activeTab = t.id"
        >
          {{ t.label }}
        </button>
      </div>
    </div>

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
      v-else-if="filteredOrders.length === 0"
      class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-12 text-center shadow-xs flex flex-col items-center justify-center gap-4"
    >
      <div class="w-16 h-16 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-400 flex items-center justify-center">
        <UIcon
          name="i-lucide-package-open"
          class="w-8 h-8"
        />
      </div>
      <div class="flex flex-col gap-1">
        <h3 class="font-bold text-neutral-800 dark:text-neutral-200 text-base">
          سفارشی در این بخش یافت نشد
        </h3>
        <p class="text-xs text-neutral-400">
          سفارش‌های جدید شما پس از تکمیل فرآیند خرید در اینجا نمایش داده خواهند شد.
        </p>
      </div>
      <UButton
        to="/products"
        color="primary"
        size="md"
        icon="i-lucide-shopping-bag"
        class="mt-2 font-bold cursor-pointer"
      >
        مشاهده و خرید محصولات
      </UButton>
    </div>

    <!-- Orders List -->
    <div
      v-else
      class="flex flex-col gap-4"
    >
      <div
        v-for="order in filteredOrders"
        :key="order.order_number"
        class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-5 sm:p-6 shadow-xs transition-shadow hover:shadow-md"
      >
        <!-- Top Row: Order Number, Status, Date -->
        <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-neutral-100 dark:border-neutral-800">
          <div class="flex items-center gap-2">
            <span class="text-xs text-neutral-400">شماره سفارش:</span>
            <span class="font-mono font-bold text-sm text-neutral-900 dark:text-white">
              {{ order.order_number }}
            </span>
            <button
              type="button"
              class="text-neutral-400 hover:text-primary-600 transition-colors p-1"
              title="کپی شماره سفارش"
              @click="copyToClipboard(order.order_number, order.order_number)"
            >
              <UIcon
                :name="isCopied[order.order_number] ? 'i-lucide-check' : 'i-lucide-copy'"
                class="w-3.5 h-3.5"
                :class="{ 'text-emerald-500': isCopied[order.order_number] }"
              />
            </button>
          </div>

          <div class="flex items-center gap-3">
            <!-- Status Badge -->
            <UBadge
              :color="order.status_color || 'neutral'"
              variant="subtle"
              size="md"
              class="font-bold px-3 py-1 rounded-full text-xs"
            >
              {{ order.status_label }}
            </UBadge>
          </div>
        </div>

        <!-- Middle Row: Financials and Shipping -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 py-4 text-xs">
          <div class="flex flex-col gap-1">
            <span class="text-neutral-400">مبلغ پرداخت شده:</span>
            <span class="font-bold text-sm text-neutral-900 dark:text-neutral-100">
              {{ formatPrice(order.final_payable) }}
            </span>
          </div>

          <div class="flex flex-col gap-1">
            <span class="text-neutral-400">روش تحویل:</span>
            <span class="font-semibold text-neutral-700 dark:text-neutral-300">
              {{ order.shipping_method_title }}
            </span>
          </div>

          <div class="flex flex-col gap-1">
            <span class="text-neutral-400">تعداد اقلام:</span>
            <span class="font-semibold text-neutral-700 dark:text-neutral-300">
              {{ toPersianDigits(order.items_count) }} قلم کالا
            </span>
          </div>

          <div class="flex flex-col gap-1">
            <span class="text-neutral-400">آدرس تحویل:</span>
            <span class="font-medium text-neutral-700 dark:text-neutral-300 truncate max-w-[180px]">
              {{ order.shipping_address?.full_address || order.shipping_address?.city || '—' }}
            </span>
          </div>
        </div>

        <!-- Expand / Collapse Items Toggle -->
        <div class="pt-3 border-t border-neutral-100 dark:border-neutral-800 flex items-center justify-between">
          <!-- Items Thumbnails / Quick peek -->
          <div class="flex items-center gap-2 overflow-x-auto py-1">
            <span
              v-for="item in order.items"
              :key="item.id"
              class="text-xs bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 px-2.5 py-1 rounded-lg truncate max-w-[200px]"
            >
              {{ item.product_name }} ({{ toPersianDigits(item.quantity) }})
            </span>
          </div>

          <button
            type="button"
            class="flex items-center gap-1.5 text-xs font-bold text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors shrink-0 cursor-pointer"
            @click="toggleExpand(order.order_number)"
          >
            <span>{{ expandedOrder === order.order_number ? 'بستن فاکتور' : 'مشاهده فاکتور' }}</span>
            <UIcon
              :name="expandedOrder === order.order_number ? 'i-lucide-chevron-up' : 'i-lucide-chevron-down'"
              class="w-4 h-4"
            />
          </button>
        </div>

        <!-- Expanded Itemized Breakdown Table -->
        <div
          v-if="expandedOrder === order.order_number"
          class="mt-4 p-4 rounded-2xl bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200/80 dark:border-neutral-700/60 flex flex-col gap-3 text-xs"
        >
          <div class="font-bold text-neutral-800 dark:text-neutral-200">
            ریز اقلام سفارش:
          </div>

          <div class="divide-y divide-neutral-200/60 dark:divide-neutral-700/50">
            <div
              v-for="item in order.items"
              :key="item.id"
              class="py-2.5 flex items-center justify-between gap-4"
            >
              <div class="flex flex-col gap-0.5">
                <span class="font-semibold text-neutral-800 dark:text-neutral-200">
                  {{ item.product_name }}
                </span>
                <span class="text-[11px] text-neutral-400">
                  تنوع: {{ item.variant_title }} • تعداد: {{ toPersianDigits(item.quantity) }}
                </span>
              </div>

              <span class="font-bold text-neutral-900 dark:text-white">
                {{ formatPrice(item.total_price) }}
              </span>
            </div>
          </div>

          <!-- Total Breakdown Summary -->
          <div class="border-t border-dashed border-neutral-300 dark:border-neutral-700 pt-3 flex flex-col gap-2">
            <div class="flex justify-between text-neutral-500">
              <span>مجموع اقلام:</span>
              <span>{{ formatPrice(order.items_subtotal) }}</span>
            </div>
            <div
              v-if="order.discount_amount || order.coupon_discount"
              class="flex justify-between text-rose-500"
            >
              <span>مجموع تخفیف‌ها:</span>
              <span>{{ formatPrice((order.discount_amount || 0) + (order.coupon_discount || 0)) }}</span>
            </div>
            <div class="flex justify-between text-neutral-500">
              <span>هزینه ارسال:</span>
              <span>{{ order.shipping_fee > 0 ? formatPrice(order.shipping_fee) : 'رایگان' }}</span>
            </div>
            <div class="flex justify-between font-bold text-sm text-neutral-900 dark:text-white pt-2 border-t border-neutral-200 dark:border-neutral-700">
              <span>مبلغ نهایی پرداختی:</span>
              <span class="text-primary-600 dark:text-primary-400">{{ formatPrice(order.final_payable) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
