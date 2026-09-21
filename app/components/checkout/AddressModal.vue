<script setup lang="ts">
import { useCheckoutStore } from '~/stores/checkout'

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'saved', addressId: number): void
}>()

const checkoutStore = useCheckoutStore()
const toast = useToast()

const isOpen = defineModel<boolean>('open', { default: false })

const form = reactive({
  province_id: undefined as number | undefined,
  city_id: undefined as number | undefined,
  recipient_name: '',
  recipient_mobile: '',
  postal_code: '',
  address_line: '',
  building_number: '',
  unit: '',
  is_default: true,
})

const isSubmitting = ref(false)

// Fetch provinces when opening
watch(isOpen, async (val) => {
  if (val) {
    await checkoutStore.fetchProvinces()
  }
})

// When province changes, load cities
watch(() => form.province_id, async (provId) => {
  form.city_id = undefined
  if (provId) {
    await checkoutStore.fetchCities(provId)
  }
})

const provinceOptions = computed(() => {
  return checkoutStore.provinces.map(p => ({
    label: p.name,
    value: p.id,
  }))
})

const cityOptions = computed(() => {
  return checkoutStore.cities.map(c => ({
    label: c.name,
    value: c.id,
  }))
})

async function handleSubmit() {
  if (!form.province_id || !form.city_id) {
    toast.add({ title: 'خطا', description: 'لطفاً استان و شهر را انتخاب کنید.', color: 'error' })
    return
  }
  if (!form.recipient_name.trim()) {
    toast.add({ title: 'خطا', description: 'نام گیرنده الزامی است.', color: 'error' })
    return
  }
  if (!/^09\d{9}$/.test(form.recipient_mobile)) {
    toast.add({ title: 'خطا', description: 'شماره موبایل باید با ۰۹ شروع شده و ۱۱ رقم باشد.', color: 'error' })
    return
  }
  if (!/^\d{10}$/.test(form.postal_code)) {
    toast.add({ title: 'خطا', description: 'کد پستی باید دقیقاً ۱۰ رقم باشد.', color: 'error' })
    return
  }
  if (!form.address_line.trim()) {
    toast.add({ title: 'خطا', description: 'نشانی دقیق پستی الزامی است.', color: 'error' })
    return
  }

  isSubmitting.value = true
  try {
    const created = await checkoutStore.createAddress({
      province_id: form.province_id,
      city_id: form.city_id,
      recipient_name: form.recipient_name.trim(),
      recipient_mobile: form.recipient_mobile.trim(),
      postal_code: form.postal_code.trim(),
      address_line: form.address_line.trim(),
      building_number: form.building_number.trim() || undefined,
      unit: form.unit.trim() || undefined,
      is_default: form.is_default,
    })

    toast.add({
      title: 'موفقیت',
      description: 'آدرس جدید با موفقیت ثبت شد.',
      color: 'success',
    })

    isOpen.value = false
    emit('saved', created.id)
  } catch (err: any) {
    toast.add({
      title: 'خطا در ثبت آدرس',
      description: err?.data?.message || err?.message || 'اطلاعات وارد شده نامعتبر است.',
      color: 'error',
    })
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <UModal
    v-model:open="isOpen"
    :ui="{
      content: 'sm:max-w-lg rounded-3xl p-6 bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800'
    }"
  >
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2.5">
          <div class="size-10 rounded-2xl bg-primary/10 text-primary flex items-center justify-center">
            <UIcon
              name="i-lucide-map-pin"
              class="size-5"
            />
          </div>
          <div>
            <h3 class="font-black text-neutral-900 dark:text-white text-base">
              افزودن آدرس جدید
            </h3>
            <p class="text-xs text-neutral-500">
              مشخصات گیرنده و نشانی تحویل مرسوله
            </p>
          </div>
        </div>
        <UButton
          color="neutral"
          variant="ghost"
          icon="i-lucide-x"
          class="rounded-xl"
          @click="isOpen = false"
        />
      </div>
    </template>

    <template #body>
      <form
        class="space-y-4 pt-2"
        @submit.prevent="handleSubmit"
      >
        <!-- Province & City Selection -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
          <div class="space-y-1.5">
            <label class="text-xs font-bold text-neutral-700 dark:text-neutral-300">استان *</label>
            <USelect
              v-model="form.province_id"
              :items="provinceOptions"
              placeholder="انتخاب استان"
              class="w-full"
              size="md"
            />
          </div>

          <div class="space-y-1.5">
            <label class="text-xs font-bold text-neutral-700 dark:text-neutral-300">شهر *</label>
            <USelect
              v-model="form.city_id"
              :items="cityOptions"
              :disabled="!form.province_id"
              placeholder="انتخاب شهر"
              class="w-full"
              size="md"
            />
          </div>
        </div>

        <!-- Recipient Details -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
          <div class="space-y-1.5">
            <label class="text-xs font-bold text-neutral-700 dark:text-neutral-300">نام و نام خانوادگی گیرنده *</label>
            <UInput
              v-model="form.recipient_name"
              placeholder="مثال: سارا رضایی"
              size="md"
              class="w-full"
            />
          </div>

          <div class="space-y-1.5">
            <label class="text-xs font-bold text-neutral-700 dark:text-neutral-300">شماره موبایل گیرنده *</label>
            <UInput
              v-model="form.recipient_mobile"
              placeholder="09123456789"
              dir="ltr"
              maxlength="11"
              size="md"
              class="w-full font-mono font-en text-end"
            />
          </div>
        </div>

        <!-- Postal Code -->
        <div class="space-y-1.5">
          <label class="text-xs font-bold text-neutral-700 dark:text-neutral-300">کد پستی ۱۰ رقمی *</label>
          <UInput
            v-model="form.postal_code"
            placeholder="۱۲۳۴۵۶۷۸۹۰"
            dir="ltr"
            maxlength="10"
            size="md"
            class="w-full font-mono font-en text-end"
          />
        </div>

        <!-- Full Street Address -->
        <div class="space-y-1.5">
          <label class="text-xs font-bold text-neutral-700 dark:text-neutral-300">نشانی دقیق پستی *</label>
          <UTextarea
            v-model="form.address_line"
            placeholder="خیابان، کوچه، پلاک، طبقه و توضیحات تکمیلی..."
            :rows="3"
            class="w-full"
          />
        </div>

        <!-- Building Number & Unit -->
        <div class="grid grid-cols-2 gap-3.5">
          <div class="space-y-1.5">
            <label class="text-xs font-medium text-neutral-600 dark:text-neutral-400">پلاک (اختیاری)</label>
            <UInput
              v-model="form.building_number"
              placeholder="مثال: ۱۲"
              size="md"
              class="w-full"
            />
          </div>

          <div class="space-y-1.5">
            <label class="text-xs font-medium text-neutral-600 dark:text-neutral-400">واحد (اختیاری)</label>
            <UInput
              v-model="form.unit"
              placeholder="مثال: ۴"
              size="md"
              class="w-full"
            />
          </div>
        </div>

        <!-- Default Checkbox -->
        <div class="flex items-center gap-2 pt-1">
          <UCheckbox
            v-model="form.is_default"
            label="تنظیم به عنوان آدرس پیش‌فرض تحویل"
          />
        </div>

        <!-- Submit Button -->
        <div class="pt-4 flex items-center justify-end gap-2.5">
          <UButton
            color="neutral"
            variant="ghost"
            size="md"
            class="rounded-xl px-5"
            @click="isOpen = false"
          >
            انصراف
          </UButton>
          <UButton
            type="submit"
            color="primary"
            variant="solid"
            size="md"
            :loading="isSubmitting"
            class="rounded-xl px-7 font-bold shadow-md shadow-primary/20"
          >
            ثبت و ذخیره آدرس
          </UButton>
        </div>
      </form>
    </template>
  </UModal>
</template>
