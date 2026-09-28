<script setup lang="ts">
import type { Order } from '~/types/order'

definePageMeta({
  layout: 'invoice'
})

const route = useRoute()
const api = useApi()
const settingsStore = useSettingsStore()
const { toPersianDigits, formatPrice, formatRials } = usePersian()

const orderNumber = computed(() => String(route.params.orderNumber))
const order = ref<Order | null>(null)
const isLoading = ref(true)
const errorMessage = ref<string | null>(null)

// Mode toggle: Standard Invoice vs Corporate Tax Invoice (ماده ۱۹)
const invoiceType = ref<'standard' | 'tax'>(route.query.type === 'tax' ? 'tax' : 'standard')

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
      const queryParams = new URLSearchParams(route.query as Record<string, string>).toString()
      endpoint = `/orders/${orderNumber.value}/invoice/signed?${queryParams}`
    } else {
      endpoint = `/orders/${orderNumber.value}/invoice`
    }

    const response = await api<ApiResponse<Order>>(endpoint)
    if (response.data) {
      order.value = response.data
      if (order.value.is_corporate_invoice && route.query.type !== 'standard') {
        invoiceType.value = 'tax'
      }
    } else {
      errorMessage.value = 'اطلاعات سفارش دریافت نشد.'
    }
  } catch (err: unknown) {
    const errorObj = err as { response?: { status?: number, data?: { message?: string } } }
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

// Calculations for Tax Invoice (ماده ۱۹ ارزش افزوده - ۱۰٪ محاسبه مستقیم روی مبلغ خالص پس از تخفیف)
const vatRate = 0.10 // 10% Iranian standard VAT (Exclusive add-on)
const taxableItems = computed(() => {
  if (!order.value?.items) return []
  return order.value.items.map((item) => {
    const unitPrice = item.unit_price
    const discount = item.discount_amount
    const netTotal = item.total_price // Price after catalog discount for total quantity
    const vat = Math.round(netTotal * vatRate) // Exactly 10% of net total (e.g. 950,000)
    const grandTotal = netTotal + vat // Net + VAT (e.g. 10,450,000)
    const netUnit = item.final_price

    return {
      ...item,
      unitPrice,
      discount,
      netUnit,
      netTotal,
      vat,
      grandTotal
    }
  })
})

// Gross items total (sum of unit_price * quantity before discounts)
const grossItemsTotal = computed(() => {
  if (!order.value) return 0
  if (order.value.original_items_subtotal) {
    return order.value.original_items_subtotal
  }
  return order.value.items_subtotal + (order.value.discount_amount || 0)
})

const totalCatalogDiscount = computed(() => order.value?.discount_amount || 0)
const totalCouponDiscount = computed(() => order.value?.coupon_discount || 0)
const totalAllDiscounts = computed(() => totalCatalogDiscount.value + totalCouponDiscount.value)
const totalShippingFee = computed(() => order.value?.shipping_fee || 0)

const totalVatAmount = computed(() => {
  if (invoiceType.value !== 'tax') return 0
  return taxableItems.value.reduce((acc, curr) => acc + curr.vat, 0)
})

// Grand Total of Invoice
const grandInvoiceTotal = computed(() => {
  if (!order.value) return 0
  const base = order.value.final_payable || 0
  if (invoiceType.value === 'tax') {
    return base + totalVatAmount.value
  }
  return base
})

const walletPaidAmount = computed(() => order.value?.wallet_paid_amount || 0)

// Gateway / Cash paid amount is only what was actually paid beyond wallet for the base order
const gatewayPaidAmount = computed(() => {
  if (!order.value) return 0
  return Math.max(0, (order.value.final_payable || 0) - walletPaidAmount.value)
})

const isOrderPaid = computed(() => {
  return Boolean(order.value?.paid_at)
    || order.value?.status === 'processing'
    || order.value?.status === 'shipped'
    || order.value?.status === 'delivered'
})

function handlePrint() {
  window.print()
}

useHead({
  title: computed(() => {
    if (!order.value) return 'فاکتور سفارش'
    return invoiceType.value === 'tax'
      ? `صورتحساب مالیاتی رسمی سفارش ${order.value.order_number}`
      : `فاکتور سفارش ${order.value.order_number}`
  })
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
        <!-- Invoice Mode Switcher (Standard vs Corporate Tax) -->
        <div
          v-if="order?.is_corporate_invoice"
          class="flex items-center bg-neutral-100 dark:bg-neutral-800 p-1 rounded-xl text-xs"
        >
          <button
            type="button"
            class="px-3 py-1.5 rounded-lg font-bold transition-all cursor-pointer"
            :class="invoiceType === 'standard' ? 'bg-white dark:bg-neutral-900 text-neutral-900 dark:text-white shadow-xs' : 'text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200'"
            @click="invoiceType = 'standard'"
          >
            فاکتور معمولی
          </button>
          <button
            type="button"
            class="px-3 py-1.5 rounded-lg font-bold transition-all cursor-pointer flex items-center gap-1.5"
            :class="invoiceType === 'tax' ? 'bg-white dark:bg-neutral-900 text-primary shadow-xs' : 'text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200'"
            @click="invoiceType = 'tax'"
          >
            <UIcon
              name="i-lucide-building-2"
              class="size-3.5"
            />
            فاکتور رسمی ماده ۱۹ (حقوقی)
          </button>
        </div>

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
      <span class="text-sm font-medium text-neutral-500">در حال آماده‌سازی فاکتور...</span>
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

    <!-- ==================== PRINTABLE INVOICE SHEET ==================== -->
    <div
      v-else-if="order"
      class="invoice-sheet bg-white text-neutral-900 border border-neutral-200/90 rounded-2xl p-6 sm:p-10 shadow-xs print:border-none print:shadow-none print:rounded-none print:p-0 print:m-0 no-break-inside"
    >
      <!-- HEADER -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 pb-6 border-b border-neutral-200">
        <!-- Store Brand Identity -->
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

        <!-- Document Type & Meta -->
        <div class="flex flex-col sm:items-end gap-1.5 self-stretch sm:self-auto text-start sm:text-end">
          <span
            v-if="invoiceType === 'tax'"
            class="text-xs font-black text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200 uppercase tracking-wider print:border-black print:text-black print:bg-transparent"
          >
            صورت‌حساب الکترونیکی رسمی (ماده ۱۹ ارزش افزوده)
          </span>
          <span
            v-else
            class="text-xs font-black text-neutral-500 uppercase tracking-wider"
          >
            صورت‌حساب فروش کالا و خدمات
          </span>

          <div class="flex items-center gap-2">
            <span class="text-xs text-neutral-500">شماره سفارش / فاکتور:</span>
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

      <!-- PARTIES: SELLER & BUYER GRID -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 py-5 border-b border-neutral-200 text-xs leading-relaxed">
        <!-- Seller Box -->
        <div class="p-3.5 rounded-xl bg-neutral-50/70 border border-neutral-100 print:bg-transparent print:border-neutral-300 flex flex-col gap-1.5">
          <div class="font-black text-neutral-800 flex items-center gap-1.5 pb-1 border-b border-neutral-200/60">
            <UIcon
              name="i-lucide-store"
              class="size-3.5 text-neutral-600"
            />
            <span>مشخصات فروشنده (شخص حقوقی)</span>
          </div>
          <div>
            <span class="text-neutral-500">فروشگاه / شرکت:</span>
            <span class="font-bold text-neutral-900 mr-1.5">{{ settingsStore.settings.store_name }}</span>
          </div>
          <div
            v-if="invoiceType === 'tax'"
            class="grid grid-cols-2 gap-2 text-[11px] pt-1 border-t border-neutral-100"
          >
            <div>
              <span class="text-neutral-500">کد اقتصادی:</span>
              <span class="font-mono text-neutral-800 mr-1.5 [direction:ltr]">۴۱۱۴۸۵۲۹۷۵۳۱</span>
            </div>
            <div>
              <span class="text-neutral-500">شناسه ملی:</span>
              <span class="font-mono text-neutral-800 mr-1.5 [direction:ltr]">۱۰۳۲۰۸۷۶۵۴۳</span>
            </div>
            <div>
              <span class="text-neutral-500">شماره ثبت:</span>
              <span class="font-mono text-neutral-800 mr-1.5 [direction:ltr]">۵۸۲۱۴۰</span>
            </div>
          </div>
          <div v-if="settingsStore.settings.support_phone">
            <span class="text-neutral-500">تلفن:</span>
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
            <span v-if="invoiceType === 'tax'">مشخصات خریدار (شخص حقوقی / حقیقی طرف قرارداد)</span>
            <span v-else>مشخصات خریدار / تحویل‌گیرنده</span>
          </div>

          <template v-if="invoiceType === 'tax' && order.corporate_data">
            <div>
              <span class="text-neutral-500">نام شرکت / سازمان:</span>
              <span class="font-bold text-neutral-900 mr-1.5">{{ order.corporate_data.company_name || order.shipping_address?.recipient_name }}</span>
            </div>
            <div class="grid grid-cols-2 gap-2 text-[11px] pt-1 border-t border-neutral-100">
              <div>
                <span class="text-neutral-500">شناسه ملی:</span>
                <span class="font-mono font-bold text-neutral-900 mr-1.5 [direction:ltr]">{{ order.corporate_data.national_id || '—' }}</span>
              </div>
              <div>
                <span class="text-neutral-500">کد اقتصادی:</span>
                <span class="font-mono text-neutral-800 mr-1.5 [direction:ltr]">{{ order.corporate_data.economic_code || '—' }}</span>
              </div>
              <div v-if="order.corporate_data.registration_number">
                <span class="text-neutral-500">شماره ثبت:</span>
                <span class="font-mono text-neutral-800 mr-1.5 [direction:ltr]">{{ order.corporate_data.registration_number }}</span>
              </div>
            </div>
          </template>

          <template v-else>
            <div>
              <span class="text-neutral-500">نام خریدار:</span>
              <span class="font-bold text-neutral-900 mr-1.5">
                {{ order.shipping_address?.recipient_name || order.user?.name || 'مشتری گرامی' }}
              </span>
            </div>
          </template>

          <div>
            <span class="text-neutral-500">شماره تماس:</span>
            <span class="font-mono text-neutral-800 mr-1.5 [direction:ltr]">
              {{ order.shipping_address?.recipient_mobile || order.user?.mobile || '—' }}
            </span>
          </div>
          <div>
            <span class="text-neutral-500">نشانی اقامتگاه / تحویل:</span>
            <span class="text-neutral-800 mr-1.5">
              {{ order.shipping_address?.full_address || order.shipping_address?.address_line || [order.shipping_address?.province_name, order.shipping_address?.city_name].filter(Boolean).join('، ') || '—' }}
            </span>
          </div>
          <div v-if="order.shipping_address?.postal_code">
            <span class="text-neutral-500">کد پستی ۱۰ رقمی:</span>
            <span class="font-mono text-neutral-800 mr-1.5 [direction:ltr]">
              {{ order.shipping_address?.postal_code }}
            </span>
          </div>
        </div>
      </div>

      <!-- ==================== ITEMS TABLE ==================== -->
      <div class="py-5 border-b border-neutral-200">
        <h2 class="text-xs font-black text-neutral-700 mb-3 flex items-center gap-1.5">
          <UIcon
            name="i-lucide-package-check"
            class="size-3.5 text-neutral-500"
          />
          <span v-if="invoiceType === 'tax'">مشخصات کالا و خدمات مورد معامله با محاسبه مالیات بر ارزش افزوده</span>
          <span v-else>ریز اقلام و مشخصات سفارش</span>
        </h2>

        <!-- STANDARD TABLE -->
        <div
          v-if="invoiceType === 'standard'"
          class="overflow-x-auto"
        >
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
                  قیمت واحد (ریال)
                </th>
                <th class="py-2.5 px-3 font-bold text-start w-28">
                  تخفیف (ریال)
                </th>
                <th class="py-2.5 px-3 font-bold text-start w-32">
                  مبلغ کل (ریال)
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
                <td class="py-3 px-3 font-medium text-neutral-700 whitespace-nowrap font-mono">
                  {{ formatRials(item.unit_price) }}
                </td>
                <td class="py-3 px-3 font-medium text-neutral-500 whitespace-nowrap font-mono">
                  {{ item.discount_amount > 0 ? formatRials(item.discount_amount) : '۰ ریال' }}
                </td>
                <td class="py-3 px-3 font-bold text-neutral-900 whitespace-nowrap font-mono">
                  {{ formatRials(item.total_price) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- TAX INVOICE (ماده ۱۹) TABLE WITH VAT BREAKDOWN -->
        <div
          v-else
          class="overflow-x-auto"
        >
          <table class="w-full text-right text-[11px] border-collapse">
            <thead>
              <tr class="bg-neutral-100 text-neutral-800 border-y border-neutral-300">
                <th class="py-2 px-2 font-bold text-center w-8">
                  ردیف
                </th>
                <th class="py-2 px-2 font-bold">
                  شرح کالا یا خدمات
                </th>
                <th class="py-2 px-2 font-bold text-center w-12">
                  تعداد
                </th>
                <th class="py-2 px-2 font-bold text-start w-24">
                  مبلغ واحد (ریال)
                </th>
                <th class="py-2 px-2 font-bold text-start w-20">
                  تخفیف (ریال)
                </th>
                <th class="py-2 px-2 font-bold text-start w-24">
                  مبلغ پس از تخفیف (ریال)
                </th>
                <th class="py-2 px-2 font-bold text-start w-24 text-emerald-800">
                  مالیات و عوارض (۱۰٪)
                </th>
                <th class="py-2 px-2 font-bold text-start w-28">
                  جمع کل با مالیات (ریال)
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200">
              <tr
                v-for="(item, idx) in taxableItems"
                :key="item.id"
                class="hover:bg-neutral-50/50 print:hover:bg-transparent"
              >
                <td class="py-2 px-2 text-center text-neutral-400 font-mono">
                  {{ toPersianDigits(idx + 1) }}
                </td>
                <td class="py-2 px-2">
                  <div class="font-bold text-neutral-900 leading-snug">
                    {{ item.product_name }}
                  </div>
                  <div class="text-[10px] text-neutral-500 font-mono [direction:ltr] inline-block">
                    {{ item.sku ? `SKU: ${item.sku}` : '' }}
                  </div>
                </td>
                <td class="py-2 px-2 text-center font-bold text-neutral-900 font-mono">
                  {{ toPersianDigits(item.quantity) }}
                </td>
                <td class="py-2 px-2 font-medium whitespace-nowrap font-mono">
                  {{ formatRials(item.unit_price) }}
                </td>
                <td class="py-2 px-2 font-medium text-neutral-500 whitespace-nowrap font-mono">
                  {{ item.discount_amount > 0 ? formatRials(item.discount_amount) : '۰ ریال' }}
                </td>
                <td class="py-2 px-2 font-bold whitespace-nowrap font-mono">
                  {{ formatRials(item.netTotal) }}
                </td>
                <td class="py-2 px-2 font-bold text-emerald-700 whitespace-nowrap font-mono">
                  {{ formatRials(item.vat) }}
                </td>
                <td class="py-2 px-2 font-black text-neutral-950 whitespace-nowrap font-mono">
                  {{ formatRials(item.grandTotal) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ==================== FINANCIALS & TOTALS ==================== -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 py-5 border-b border-neutral-200 text-xs">
        <!-- Logistics & Payment Details -->
        <div class="flex flex-col gap-2.5 text-neutral-600">
          <div class="flex items-center gap-2">
            <span class="text-neutral-500">روش ارسال مرسوله:</span>
            <span class="font-bold text-neutral-800">{{ order.shipping_method_title || 'پست پیشتاز' }}</span>
          </div>

          <div
            v-if="order.delivery_time_slot"
            class="flex items-center gap-2"
          >
            <span class="text-neutral-500">بازه زمانی تحویل:</span>
            <span class="font-bold text-neutral-800">{{ order.delivery_time_slot }}</span>
          </div>

          <div
            v-if="order.tracking_code"
            class="flex items-center gap-2"
          >
            <span class="text-neutral-500">کد رهگیری مرسوله:</span>
            <a
              :href="order.tracking_url || `https://tracking.post.ir/?id=${order.tracking_code}`"
              target="_blank"
              rel="noopener noreferrer"
              class="font-mono font-bold text-primary hover:underline"
            >
              {{ order.tracking_code }} ↗
            </a>
          </div>

          <div
            v-if="order.paid_at"
            class="flex items-center gap-2"
          >
            <span class="text-neutral-500">زمان پرداخت و تسویه:</span>
            <span class="font-medium text-neutral-800">{{ formatJalaliDate(order.paid_at, true) }}</span>
          </div>

          <div
            v-if="order.card_receipt"
            class="p-2.5 rounded-lg bg-amber-50/70 border border-amber-200 text-[11px] flex flex-col gap-1 text-amber-900"
          >
            <div class="font-bold flex items-center gap-1">
              <UIcon
                name="i-lucide-credit-card"
                class="size-3.5"
              />
              <span>پرداخت کارت‌به‌کارت بانکی</span>
            </div>
            <div>شماره پیگیری: <span class="font-mono font-bold">{{ order.card_receipt.tracking_number }}</span></div>
            <div>وضعیت فیش: <span class="font-bold">{{ order.card_receipt.status === 'approved' ? 'تأیید شده' : 'در انتظار بررسی' }}</span></div>
          </div>

          <div
            v-if="order.notes"
            class="mt-1 p-2.5 rounded-lg bg-neutral-50 border border-neutral-200/70 text-[11px] leading-relaxed"
          >
            <span class="font-bold text-neutral-700 block mb-0.5">یادداشت سفارش:</span>
            <span class="text-neutral-600 whitespace-pre-line">{{ order.notes }}</span>
          </div>
        </div>

        <!-- Calculations Summary Box -->
        <div class="flex flex-col gap-2 p-4 rounded-xl bg-neutral-50/80 border border-neutral-200/80 print:bg-transparent print:border-neutral-300">
          <div class="flex justify-between items-center text-neutral-600">
            <span>مجموع اقلام (ناخالص):</span>
            <span class="font-bold font-mono text-neutral-800">{{ formatRials(grossItemsTotal) }}</span>
          </div>

          <div
            v-if="totalAllDiscounts > 0"
            class="flex justify-between items-center text-emerald-700 font-medium"
          >
            <span>مجموع تخفیف‌های اعمال شده:</span>
            <span class="font-mono">- {{ formatRials(totalAllDiscounts) }}</span>
          </div>

          <div
            v-if="invoiceType === 'tax'"
            class="flex justify-between items-center text-neutral-700 font-medium"
          >
            <span>مبلغ پس از تخفیف:</span>
            <span class="font-mono">{{ formatRials(grossItemsTotal - totalAllDiscounts) }}</span>
          </div>

          <div
            v-if="invoiceType === 'tax' && totalVatAmount > 0"
            class="flex justify-between items-center text-emerald-800 font-bold"
          >
            <span>مالیات و عوارض ارزش افزوده (۱۰٪):</span>
            <span class="font-mono">+ {{ formatRials(totalVatAmount) }}</span>
          </div>

          <div class="flex justify-between items-center text-neutral-600">
            <span>هزینه بسته‌بندی و ارسال:</span>
            <span class="font-medium font-mono text-neutral-800">
              {{ totalShippingFee > 0 ? formatRials(totalShippingFee) : 'رایگان' }}
            </span>
          </div>

          <div class="pt-2 border-t border-neutral-200 flex justify-between items-center text-xs font-black text-neutral-900">
            <span>جمع کل صورتحساب:</span>
            <span class="font-black text-sm font-mono text-neutral-950">{{ formatRials(grandInvoiceTotal) }}</span>
          </div>

          <div
            v-if="walletPaidAmount > 0"
            class="flex justify-between items-center text-emerald-700 font-bold pt-1.5 border-t border-dashed border-neutral-200"
          >
            <span>پرداخت از طریق کیف پول:</span>
            <span class="font-mono">- {{ formatRials(walletPaidAmount) }}</span>
          </div>

          <div
            v-if="gatewayPaidAmount > 0 && isOrderPaid"
            class="flex justify-between items-center text-neutral-700 font-bold"
          >
            <span>پرداخت آنلاین / درگاه بانکی:</span>
            <span class="font-mono">{{ formatRials(gatewayPaidAmount) }}</span>
          </div>

          <div class="pt-2 mt-0.5 border-t-2 border-neutral-900/10 flex justify-between items-center text-xs sm:text-sm font-black text-neutral-900">
            <span>وضعیت تسویه فاکتور:</span>
            <span
              v-if="isOrderPaid || gatewayPaidAmount === 0"
              class="text-emerald-700 font-black flex items-center gap-1"
            >
              <UIcon
                name="i-lucide-check-circle-2"
                class="size-4"
              />
              <span>تسویه کامل (۰ ریال)</span>
            </span>
            <span
              v-else
              class="text-primary font-black font-mono"
            >
              {{ formatRials(gatewayPaidAmount) }} (در انتظار پرداخت)
            </span>
          </div>
        </div>
      </div>

      <!-- FORMAL FOOTER & SIGNATURES -->
      <div class="pt-6 flex flex-col gap-8 text-xs text-neutral-500">
        <div class="grid grid-cols-2 gap-8 text-center pt-2">
          <div class="flex flex-col items-center justify-between h-24 border border-dashed border-neutral-200 rounded-xl p-3 print:border-neutral-400">
            <span class="font-bold text-neutral-700">مهر و امضای فروشنده (صادرکننده)</span>
            <span class="text-[10px] text-neutral-400">سیستمی ممهور به مهر الکترونیکی شد</span>
          </div>

          <div class="flex flex-col items-center justify-between h-24 border border-dashed border-neutral-200 rounded-xl p-3 print:border-neutral-400">
            <span class="font-bold text-neutral-700">امضا و مهر خریدار / تحویل‌گیرنده</span>
            <span class="text-[10px] text-neutral-400">صحت مندرجات فاکتور و دریافت کالا تأیید می‌شود</span>
          </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-2 text-[10px] text-neutral-400 border-t border-neutral-100 pt-3">
          <span v-if="invoiceType === 'tax'">
            {{ settingsStore.settings.tax_invoice_notice || 'در صورت درخواست فاکتور رسمی، ارائه شناسه ملی و کد اقتصادی الزامی است و ارزش افزوده طبق قوانین جاری محاسبه می‌گردد.' }}
          </span>
          <span v-else>این سند صورتحساب معتبر خرید اینترنتی بوده و پیگیری آن از طریق سامانه استعلام فروشگاه امکان‌پذیر است.</span>
          <span v-if="printTime">تاریخ و زمان چاپ: {{ printTime }}</span>
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
