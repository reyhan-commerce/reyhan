<script setup lang="ts">
import type { Order } from '~/types/order'

const api = useApi()
const { formatPrice, toPersianDigits } = usePersian()

useSeoMeta({
  title: 'سفارش‌های من'
})

const isLoading = ref(true)
const orders = ref<Order[]>([])
const activeTab = ref('all')
const expandedOrder = ref<string | null>(null)
const isCopied = ref<Record<string, boolean>>({})

const fetchOrders = async () => {
  isLoading.value = true
  try {
    const res = await api<{ success: boolean, data: Order[] }>('/orders')
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
  { id: 'cancelled', label: 'لغو شده' }
]

const filteredOrders = computed(() => {
  if (activeTab.value === 'all') return orders.value
  return orders.value.filter(o => o.status === activeTab.value)
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

type BadgeColor = 'neutral' | 'primary' | 'secondary' | 'success' | 'info' | 'warning' | 'error'
const getBadgeColor = (color?: string): BadgeColor => {
  const validColors: readonly string[] = ['neutral', 'primary', 'secondary', 'success', 'info', 'warning', 'error']
  if (color && validColors.includes(color)) {
    return color as BadgeColor
  }
  return 'neutral'
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
            <!-- Scheduled Delivery Time Slot Badge -->
            <div
              v-if="order.delivery_time_slot"
              class="hidden sm:flex items-center gap-1.5 text-xs text-amber-700 dark:text-amber-300 bg-amber-500/10 border border-amber-500/20 px-2.5 py-1 rounded-xl font-bold"
            >
              <UIcon
                name="i-lucide-calendar-clock"
                class="size-3.5"
              />
              <span>{{ order.delivery_time_slot }}</span>
            </div>

            <!-- Status Badge -->
            <UBadge
              :color="getBadgeColor(order.status_color)"
              variant="subtle"
              size="md"
              class="font-bold px-3 py-1 rounded-full text-xs"
            >
              {{ order.status_label }}
            </UBadge>
          </div>
        </div>

        <!-- 5-Step Order Status Timeline (For non-cancelled orders) -->
        <div
          v-if="order.status !== 'cancelled' && order.status !== 'refunded'"
          class="py-3 border-b border-neutral-100 dark:border-neutral-800"
        >
          <div class="grid grid-cols-5 gap-1 items-center text-center">
            <!-- Step 1: Created -->
            <div class="flex flex-col items-center gap-1">
              <div
                class="size-6 sm:size-7 rounded-full flex items-center justify-center text-xs font-bold transition-all"
                :class="['pending', 'processing', 'shipped', 'delivered'].includes(order.status) ? 'bg-primary text-white ring-2 ring-primary/20' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-400'"
              >
                <UIcon name="i-lucide-file-text" class="size-3.5" />
              </div>
              <span class="text-[10px] sm:text-xs font-bold text-neutral-700 dark:text-neutral-300">ثبت سفارش</span>
            </div>

            <!-- Step 2: Paid -->
            <div class="flex flex-col items-center gap-1">
              <div
                class="size-6 sm:size-7 rounded-full flex items-center justify-center text-xs font-bold transition-all"
                :class="['processing', 'shipped', 'delivered'].includes(order.status) || order.paid_at ? 'bg-primary text-white ring-2 ring-primary/20' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-400'"
              >
                <UIcon name="i-lucide-credit-card" class="size-3.5" />
              </div>
              <span class="text-[10px] sm:text-xs font-bold text-neutral-700 dark:text-neutral-300">پرداخت موفق</span>
            </div>

            <!-- Step 3: Packaging -->
            <div class="flex flex-col items-center gap-1">
              <div
                class="size-6 sm:size-7 rounded-full flex items-center justify-center text-xs font-bold transition-all"
                :class="['processing', 'shipped', 'delivered'].includes(order.status) ? 'bg-primary text-white ring-2 ring-primary/20' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-400'"
              >
                <UIcon name="i-lucide-package" class="size-3.5" />
              </div>
              <span class="text-[10px] sm:text-xs font-bold text-neutral-700 dark:text-neutral-300">بسته‌بندی</span>
            </div>

            <!-- Step 4: Shipped -->
            <div class="flex flex-col items-center gap-1">
              <div
                class="size-6 sm:size-7 rounded-full flex items-center justify-center text-xs font-bold transition-all"
                :class="['shipped', 'delivered'].includes(order.status) ? 'bg-primary text-white ring-2 ring-primary/20' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-400'"
              >
                <UIcon name="i-lucide-truck" class="size-3.5" />
              </div>
              <span class="text-[10px] sm:text-xs font-bold text-neutral-700 dark:text-neutral-300">تحویل به پست/پیک</span>
            </div>

            <!-- Step 5: Delivered -->
            <div class="flex flex-col items-center gap-1">
              <div
                class="size-6 sm:size-7 rounded-full flex items-center justify-center text-xs font-bold transition-all"
                :class="order.status === 'delivered' ? 'bg-emerald-600 text-white ring-2 ring-emerald-500/20' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-400'"
              >
                <UIcon name="i-lucide-check-circle" class="size-3.5" />
              </div>
              <span class="text-[10px] sm:text-xs font-bold text-neutral-700 dark:text-neutral-300">تحویل نهایی</span>
            </div>
          </div>
        </div>

        <!-- Postal Tracking Banner (When tracking code exists) -->
        <div
          v-if="order.tracking_code"
          class="my-3 p-3.5 rounded-2xl bg-primary/5 dark:bg-primary/10 border border-primary/20 flex flex-wrap items-center justify-between gap-3"
        >
          <div class="flex items-center gap-3">
            <div class="size-9 rounded-xl bg-primary/20 text-primary flex items-center justify-center shrink-0">
              <UIcon
                name="i-lucide-truck"
                class="size-5"
              />
            </div>
            <div class="flex flex-col">
              <span class="text-[11px] text-neutral-500 dark:text-neutral-400 font-medium">کد رهگیری مرسوله پستی / تیپاکس:</span>
              <span class="font-mono font-black text-sm text-neutral-900 dark:text-white tracking-wider">
                {{ order.tracking_code }}
              </span>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <UButton
              size="xs"
              color="neutral"
              variant="subtle"
              icon="i-lucide-copy"
              class="rounded-xl font-bold cursor-pointer"
              @click="copyToClipboard(order.tracking_code!, 'trk_' + order.order_number)"
            >
              {{ isCopied['trk_' + order.order_number] ? 'کپی شد' : 'کپی کد' }}
            </UButton>

            <a
              :href="order.tracking_url || `https://tracking.post.ir/?id=${order.tracking_code}`"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary/90 transition-all shadow-xs cursor-pointer"
            >
              <UIcon
                name="i-lucide-external-link"
                class="size-3.5"
              />
              <span>پیگیری آنلاین مرسوله</span>
            </a>
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
              {{ order.shipping_address?.full_address || order.shipping_address?.address_line || order.shipping_address?.city_name || '—' }}
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

          <div class="flex items-center gap-2 shrink-0">
            <NuxtLink
              v-if="order.status === 'delivered' || order.status === 'shipped'"
              :to="`/profile/returns/create-${order.order_number}`"
              class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-700 dark:text-amber-400 hover:text-amber-800 transition-colors py-1.5 px-3 rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50/50 dark:bg-amber-950/20"
            >
              <UIcon
                name="i-lucide-undo-2"
                class="size-3.5"
              />
              <span>مرجوعی کالا</span>
            </NuxtLink>

            <NuxtLink
              :to="`/invoice/${order.order_number}`"
              target="_blank"
              class="inline-flex items-center gap-1.5 text-xs font-bold text-neutral-600 dark:text-neutral-400 hover:text-primary transition-colors py-1.5 px-3 rounded-xl border border-neutral-200/80 dark:border-neutral-700/80 hover:border-primary/50"
            >
              <UIcon
                name="i-lucide-printer"
                class="size-3.5"
              />
              <span>چاپ فاکتور</span>
            </NuxtLink>

            <button
              type="button"
              class="flex items-center gap-1.5 text-xs font-bold text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors shrink-0 cursor-pointer py-1.5 px-2.5"
              @click="toggleExpand(order.order_number)"
            >
              <span>{{ expandedOrder === order.order_number ? 'بستن ریز اقلام' : 'ریز اقلام' }}</span>
              <UIcon
                :name="expandedOrder === order.order_number ? 'i-lucide-chevron-up' : 'i-lucide-chevron-down'"
                class="w-4 h-4"
              />
            </button>
          </div>
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
              class="flex justify-between text-primary font-medium"
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
