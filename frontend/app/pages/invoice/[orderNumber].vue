<script setup lang="ts">
import type { Order } from '~/types/order'

definePageMeta({
  layout: 'invoice'
})

const route = useRoute()
const api = useApi()
const settingsStore = useSettingsStore()
const { toPersianDigits, formatPrice } = usePersian()

const orderNumber = computed(() => String(route.params.orderNumber))
const order = ref<Order | null>(null)
const isLoading = ref(true)
const errorMessage = ref<string | null>(null)

// Check if this is a signed URL request (e.g. from Admin panel)
const isSignedRequest = computed(() => {
  return Boolean(route.query.signature && route.query.expires)
})

// Persian Jalali Date Formatter
function formatJalaliDate(isoString: string | null | undefined, includeTime = false): string {
  if (!isoString) return '—'
  try {
    const date = new Date(isoString)
    const options: Intl.DateTimeFormatOptions = {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      ...(includeTime ? { hour: '2-digit', minute: '2-digit' } : {})
    }
    return new Intl.DateTimeFormat('fa-IR', options).format(date)
  } catch {
    return isoString
  }
}

const printTime = ref('')

onMounted(async () => {
  printTime.value = new Intl.DateTimeFormat('fa-IR', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  }).format(new Date())

  await fetchInvoice()
})

async function fetchInvoice() {
  isLoading.value = true
  errorMessage.value = null

  try {
    let endpoint = ''
    if (isSignedRequest.value) {
      // Forward the exact signed query string to the signed API endpoint
      const queryParams = new URLSearchParams(route.query as Record<string, string>).toString()
      endpoint = `/orders/${orderNumber.value}/invoice/signed?${queryParams}`
    } else {
      endpoint = `/orders/${orderNumber.value}/invoice`
    }

    const response = await api<ApiResponse<Order>>(endpoint)
    if (response.data) {
      order.value = response.data
    } else {
      errorMessage.value = 'اطلاعات سفارش دریافت نشد.'
    }
  } catch (err: unknown) {
    const errorObj = err as { response?: { status?: number; data?: { message?: string } } }
    if (errorObj.response?.status === 403) {
      errorMessage.value = 'لینک فاکتور منقضی شده یا دسترسی غیرمجاز است.'
    } else if (errorObj.response?.status === 404) {
      errorMessage.value = 'سفارش مورد نظر یافت نشد.'
    } else if (errorObj.response?.status === 401) {
      errorMessage.value = 'برای مشاهده این فاکتور ابتدا وارد حساب کاربری خود شوید.'
    } else {
      errorMessage.value = 'خطا در برقراری ارتباط و دریافت فاکتور سفارش.'
    }
  } finally {
    isLoading.value = false
  }
}

function handlePrint() {
  window.print()
}

useHead({
  title: computed(() => order.value ? `فاکتور سفارش ${order.value.order_number}` : 'فاکتور سفارش')
})
</script>

<template>
  <div class="max-w-4xl mx-auto flex flex-col gap-6">
    <!-- Top Action Bar (Hidden on print) -->
    <div class="print:hidden flex flex-wrap items-center justify-between gap-4 p-4 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800 shadow-xs">
      <div class="flex items-center gap-3">
        <NuxtLink
          to="/profile/orders"
          class="flex items-center gap-1.5 text-xs font-bold text-neutral-600 dark:text-neutral-400 hover:text-primary transition-colors"
        >
          <UIcon
            name="i-lucide-arrow-right"
            class="size-4"
          />
          <span>بازگشت به سفارشات</span>
        </NuxtLink>
        <span class="text-neutral-300 dark:text-neutral-700">|</span>
        <span class="text-xs font-mono font-bold text-neutral-800 dark:text-neutral-200">
          {{ orderNumber }}
        </span>
      </div>

      <div class="flex items-center gap-3">
        <UButton
          color="primary"
          icon="i-lucide-printer"
          size="sm"
          class="font-bold cursor-pointer shadow-xs"
          @click="handlePrint"
        >
          چاپ فاکتور یا ذخیره PDF
        </UButton>
      </div>
    </div>

    <!-- Loading State -->
    <div
      v-if="isLoading"
      class="print:hidden bg-white dark:bg-neutral-900 rounded-3xl p-12 text-center flex flex-col items-center justify-center gap-4 border border-neutral-200/80 dark:border-neutral-800"
    >
      <UIcon
        name="i-lucide-loader-2"
        class="size-8 text-primary animate-spin"
      />
      <span class="text-sm font-medium text-neutral-500">در حال آماده‌سازی فاکتور رسمی...</span>
    </div>

    <!-- Error State -->
    <div
      v-else-if="errorMessage"
      class="print:hidden bg-white dark:bg-neutral-900 rounded-3xl p-12 text-center flex flex-col items-center justify-center gap-4 border border-red-200 dark:border-red-900/50"
    >
      <div class="size-14 rounded-full bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center justify-center">
        <UIcon
          name="i-lucide-alert-circle"
          class="size-7"
        />
      </div>
      <h3 class="font-bold text-neutral-900 dark:text-white text-base">
        امکان نمایش فاکتور وجود ندارد
      </h3>
      <p class="text-xs text-neutral-500 max-w-md">
        {{ errorMessage }}
      </p>
      <UButton
        to="/profile/orders"
        color="neutral"
        variant="outline"
        size="sm"
        class="mt-2"
      >
        مشاهده لیست سفارش‌ها
      </UButton>
    </div>

    <!-- Printable Invoice Sheet (A4 Styled) -->
    <div
      v-else-if="order"
      class="invoice-sheet bg-white text-neutral-900 border border-neutral-200/90 rounded-2xl p-6 sm:p-10 shadow-xs print:border-none print:shadow-none print:rounded-none print:p-0 print:m-0 no-break-inside"
    >
      <!-- Invoice Header: Store Brand & Official Details -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 pb-6 border-b border-neutral-200">
        <!-- Store Identity -->
        <div class="flex items-center gap-3.5">
          <div class="size-13 rounded-2xl bg-neutral-900 text-white flex items-center justify-center font-black text-xl shadow-xs shrink-0 print:border print:border-black">
            {{ settingsStore.settings.store_name?.charAt(0) || 'ف' }}
          </div>
          <div class="flex flex-col">
            <h1 class="font-black text-lg sm:text-xl text-neutral-900 tracking-tight">
              {{ settingsStore.settings.store_name }}
            </h1>
            <p class="text-xs text-neutral-500 mt-0.5">
              {{ settingsStore.settings.store_slogan || 'فروشگاه اینترنتی تخصصی' }}
            </p>
          </div>
        </div>

        <!-- Document Type & Stamp -->
        <div class="flex flex-col sm:items-end gap-1.5 self-stretch sm:self-auto text-start sm:text-end">
          <span class="text-xs font-black text-neutral-500 uppercase tracking-wider">
            صورت‌حساب فروش کالا و خدمات
          </span>
          <div class="flex items-center gap-2">
            <span class="text-xs text-neutral-500">شماره سفارش:</span>
            <span class="font-mono font-black text-sm text-neutral-900 [direction:ltr]">
              {{ order.order_number }}
            </span>
          </div>
          <div class="flex items-center gap-2 text-xs text-neutral-500">
            <span>تاریخ ثبت:</span>
            <span class="font-medium text-neutral-800">
              {{ formatJalaliDate(order.created_at) }}
            </span>
          </div>
          <!-- Status Stamp -->
          <div class="mt-1">
            <span
              v-if="order.paid_at || order.status === 'processing' || order.status === 'delivered' || order.status === 'shipped'"
              class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-50 text-emerald-700 border border-emerald-300 print:border-black print:text-black print:bg-transparent"
            >
              <UIcon
                name="i-lucide-check-circle-2"
                class="size-3.5"
              />
              پرداخت شده (تسویه‌شده)
            </span>
            <span
              v-else-if="order.status === 'pending'"
              class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-amber-50 text-amber-700 border border-amber-300 print:border-black print:text-black print:bg-transparent"
            >
              <UIcon
                name="i-lucide-clock"
                class="size-3.5"
              />
              در انتظار پرداخت
            </span>
            <span
              v-else
              class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-neutral-100 text-neutral-700 border border-neutral-300 print:border-black print:text-black print:bg-transparent"
            >
              {{ order.status_label }}
            </span>
          </div>
        </div>
      </div>

      <!-- Parties Grid: Seller & Buyer Details -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 py-5 border-b border-neutral-200 text-xs leading-relaxed">
        <!-- Seller Box -->
        <div class="p-3.5 rounded-xl bg-neutral-50/70 border border-neutral-100 print:bg-transparent print:border-neutral-300 flex flex-col gap-1.5">
          <div class="font-black text-neutral-800 flex items-center gap-1.5 pb-1 border-b border-neutral-200/60">
            <UIcon
              name="i-lucide-store"
              class="size-3.5 text-neutral-600"
            />
            <span>مشخصات فروشنده</span>
          </div>
          <div>
            <span class="text-neutral-500">فروشگاه:</span>
            <span class="font-bold text-neutral-900 mr-1.5">{{ settingsStore.settings.store_name }}</span>
          </div>
          <div v-if="settingsStore.settings.support_phone">
            <span class="text-neutral-500">تلفن پشتیبانی:</span>
            <span class="font-mono text-neutral-800 mr-1.5 [direction:ltr]">{{ settingsStore.settings.support_phone }}</span>
          </div>
          <div v-if="settingsStore.settings.address">
            <span class="text-neutral-500">نشانی:</span>
            <span class="text-neutral-800 mr-1.5">{{ settingsStore.settings.address }}</span>
          </div>
          <div v-if="settingsStore.settings.postal_code">
            <span class="text-neutral-500">کد پستی:</span>
            <span class="font-mono text-neutral-800 mr-1.5 [direction:ltr]">{{ settingsStore.settings.postal_code }}</span>
          </div>
        </div>

        <!-- Buyer Box -->
        <div class="p-3.5 rounded-xl bg-neutral-50/70 border border-neutral-100 print:bg-transparent print:border-neutral-300 flex flex-col gap-1.5">
          <div class="font-black text-neutral-800 flex items-center gap-1.5 pb-1 border-b border-neutral-200/60">
            <UIcon
              name="i-lucide-user"
              class="size-3.5 text-neutral-600"
            />
            <span>مشخصات خریدار / تحویل‌گیرنده</span>
          </div>
          <div>
            <span class="text-neutral-500">نام تحویل‌گیرنده:</span>
            <span class="font-bold text-neutral-900 mr-1.5">
              {{ order.shipping_address?.recipient_name || order.user?.name || 'مشتری گرامی' }}
            </span>
          </div>
          <div>
            <span class="text-neutral-500">شماره تماس:</span>
            <span class="font-mono text-neutral-800 mr-1.5 [direction:ltr]">
              {{ order.shipping_address?.recipient_mobile || order.user?.mobile || '—' }}
            </span>
          </div>
          <div>
            <span class="text-neutral-500">نشانی تحویل:</span>
            <span class="text-neutral-800 mr-1.5">
              {{ order.shipping_address?.full_address || order.shipping_address?.address_line || [order.shipping_address?.province_name, order.shipping_address?.city_name].filter(Boolean).join('، ') || '—' }}
            </span>
          </div>
          <div v-if="order.shipping_address?.postal_code">
            <span class="text-neutral-500">کد پستی:</span>
            <span class="font-mono text-neutral-800 mr-1.5 [direction:ltr]">
              {{ order.shipping_address?.postal_code }}
            </span>
          </div>
        </div>
      </div>

      <!-- Itemized Goods & Services Table -->
      <div class="py-5 border-b border-neutral-200">
        <h2 class="text-xs font-black text-neutral-700 mb-3 flex items-center gap-1.5">
          <UIcon
            name="i-lucide-package-check"
            class="size-3.5 text-neutral-500"
          />
          <span>ریز اقلام و مشخصات سفارش</span>
        </h2>

        <div class="overflow-x-auto">
          <table class="w-full text-right text-xs border-collapse">
            <thead>
              <tr class="bg-neutral-100 text-neutral-700 border-y border-neutral-200">
                <th class="py-2.5 px-3 font-bold text-center w-12">
                  ردیف
                </th>
                <th class="py-2.5 px-3 font-bold">
                  شرح کالا یا خدمات
                </th>
                <th class="py-2.5 px-3 font-bold text-center w-16">
                  تعداد
                </th>
                <th class="py-2.5 px-3 font-bold text-start w-28">
                  قیمت واحد
                </th>
                <th class="py-2.5 px-3 font-bold text-start w-28">
                  تخفیف
                </th>
                <th class="py-2.5 px-3 font-bold text-start w-32">
                  مبلغ کل
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200">
              <tr
                v-for="(item, idx) in order.items"
                :key="item.id"
                class="hover:bg-neutral-50/50 print:hover:bg-transparent"
              >
                <td class="py-3 px-3 text-center text-neutral-400 font-medium font-mono">
                  {{ toPersianDigits(idx + 1) }}
                </td>
                <td class="py-3 px-3">
                  <div class="flex flex-col gap-0.5">
                    <span class="font-bold text-neutral-900 leading-snug">
                      {{ item.product_name }}
                    </span>
                    <span
                      v-if="item.variant_title"
                      class="text-[11px] text-neutral-500"
                    >
                      تنوع: {{ item.variant_title }}
                    </span>
                    <span
                      v-if="item.sku"
                      class="text-[10px] font-mono text-neutral-400 [direction:ltr] inline-block w-fit"
                    >
                      کد کالا: {{ item.sku }}
                    </span>
                  </div>
                </td>
                <td class="py-3 px-3 text-center font-bold text-neutral-900 font-mono">
                  {{ toPersianDigits(item.quantity) }}
                </td>
                <td class="py-3 px-3 font-medium text-neutral-700 whitespace-nowrap">
                  {{ formatPrice(item.unit_price) }}
                </td>
                <td class="py-3 px-3 font-medium text-neutral-500 whitespace-nowrap">
                  {{ item.discount_amount > 0 ? formatPrice(item.discount_amount) : '۰' }}
                </td>
                <td class="py-3 px-3 font-bold text-neutral-900 whitespace-nowrap">
                  {{ formatPrice(item.total_price) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Financials and Summary Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 py-5 border-b border-neutral-200 text-xs">
        <!-- Notes & Logistics Details -->
        <div class="flex flex-col gap-2.5 text-neutral-600">
          <div class="flex items-center gap-2">
            <span class="text-neutral-500">روش ارسال مرسوله:</span>
            <span class="font-bold text-neutral-800">{{ order.shipping_method_title || 'پست پیشتاز' }}</span>
          </div>

          <div
            v-if="order.paid_at"
            class="flex items-center gap-2"
          >
            <span class="text-neutral-500">زمان پرداخت:</span>
            <span class="font-medium text-neutral-800">{{ formatJalaliDate(order.paid_at, true) }}</span>
          </div>

          <div
            v-if="order.notes"
            class="mt-2 p-2.5 rounded-lg bg-neutral-50 border border-neutral-200/70 text-[11px] leading-relaxed"
          >
            <span class="font-bold text-neutral-700 block mb-0.5">یادداشت سفارش:</span>
            <span class="text-neutral-600 whitespace-pre-line">{{ order.notes }}</span>
          </div>
        </div>

        <!-- Accounting Calculations Box -->
        <div class="flex flex-col gap-2 p-4 rounded-xl bg-neutral-50/80 border border-neutral-200/80 print:bg-transparent print:border-neutral-300">
          <div class="flex justify-between items-center text-neutral-600">
            <span>مجموع اقلام (ناخالص):</span>
            <span class="font-medium">{{ formatPrice(order.items_subtotal) }}</span>
          </div>

          <div
            v-if="order.discount_amount || order.coupon_discount"
            class="flex justify-between items-center text-emerald-700 font-medium"
          >
            <span>مجموع تخفیف‌های اعمال شده:</span>
            <span>- {{ formatPrice((order.discount_amount || 0) + (order.coupon_discount || 0)) }}</span>
          </div>

          <div class="flex justify-between items-center text-neutral-600">
            <span>هزینه بسته‌بندی و ارسال:</span>
            <span class="font-medium">
              {{ order.shipping_fee > 0 ? formatPrice(order.shipping_fee) : 'رایگان' }}
            </span>
          </div>

          <div class="pt-2.5 mt-1 border-t-2 border-neutral-900/10 flex justify-between items-center text-sm font-black text-neutral-900">
            <span>مبلغ نهایی پرداختی:</span>
            <span class="text-base text-neutral-950 font-black">
              {{ formatPrice(order.final_payable) }}
            </span>
          </div>
        </div>
      </div>

      <!-- Formal Footer & Signatures -->
      <div class="pt-6 flex flex-col gap-8 text-xs text-neutral-500">
        <!-- Signatures Boxes -->
        <div class="grid grid-cols-2 gap-8 text-center pt-2">
          <div class="flex flex-col items-center justify-between h-20 border border-dashed border-neutral-200 rounded-xl p-3 print:border-neutral-400">
            <span class="font-bold text-neutral-700">مهر و امضای فروشگاه</span>
            <span class="text-[10px] text-neutral-400">سیستمی تأیید شد</span>
          </div>

          <div class="flex flex-col items-center justify-between h-20 border border-dashed border-neutral-200 rounded-xl p-3 print:border-neutral-400">
            <span class="font-bold text-neutral-700">امضای خریدار / تحویل‌گیرنده</span>
            <span class="text-[10px] text-neutral-400">کالا با بسته‌بندی سالم دریافت شد</span>
          </div>
        </div>

        <!-- Verification Notice -->
        <div class="flex flex-wrap items-center justify-between gap-2 text-[10px] text-neutral-400 border-t border-neutral-100 pt-3">
          <span>این فاکتور رسمی به صورت الکترونیکی صادر گردیده و هرگونه تغییر و مخدوش نمودن آن فاقد اعتبار است.</span>
          <span v-if="printTime">تاریخ و ساعت چاپ: {{ printTime }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@media print {
  .invoice-sheet {
    max-width: 100% !important;
    width: 100% !important;
  }
}
</style>
