<script setup lang="ts">
const settingsStore = useSettingsStore()
const toast = useToast()
const api = useApi()
const { toEnglishDigits } = usePersian()

const storeName = computed(() => settingsStore.settings.store_name || 'ایزیشاپ')
const phone = computed(() => settingsStore.settings.support_phone || '۰۲۱-۸۸۸۸۹۹۹۹')
const address = computed(() => settingsStore.settings.address || 'تهران، خیابان ولیعصر')
const email = computed(() => settingsStore.settings.support_email || 'support@easyshop.ir')
const workHours = computed(() => settingsStore.settings.work_hours || 'شنبه تا چهارشنبه ۹ الی ۱۸ • پنج‌شنبه ۹ الی ۱۴')

useSeoMeta({
  title: () => `تماس با ما - پشتیبانی ${storeName.value}`,
  description: 'راه‌های ارتباطی، نشانی پستی دفتر مرکزی و فرم ارسال پیام به واحد پشتیبانی.'
})

const form = reactive({
  name: '',
  mobile: '',
  subject: '',
  message: ''
})

// Auto-normalize mobile input (accepts Persian, Hindi/Arabic and English digits)
watch(() => form.mobile, (val) => {
  if (val) {
    const converted = toEnglishDigits(val)
    if (converted !== val) form.mobile = converted
  }
})

const isSubmitting = ref(false)

const handleSubmit = async () => {
  const normalizedMobile = toEnglishDigits(form.mobile).trim()
  if (!form.name.trim() || !normalizedMobile || !form.message.trim()) {
    toast.add({
      title: 'خطای اعتبارسنجی',
      description: 'لطفاً نام، شماره تماس و متن پیام خود را وارد نمایید.',
      color: 'error'
    })
    return
  }

  isSubmitting.value = true
  try {
    const res = await api<{ success: boolean, message?: string }>('/contact', {
      method: 'POST',
      body: {
        name: form.name.trim(),
        mobile: normalizedMobile,
        subject: form.subject.trim() || null,
        message: form.message.trim()
      }
    })

    toast.add({
      title: 'پیام دریافت شد',
      description: res.message || 'پیام شما با موفقیت ثبت گردید. کارشناسان پشتیبانی به زودی با شما تماس خواهند گرفت.',
      color: 'success'
    })

    form.name = ''
    form.mobile = ''
    form.subject = ''
    form.message = ''
  } catch (error: unknown) {
    const msg = (error as { data?: { message?: string } })?.data?.message || 'خطا در ثبت پیام. لطفاً اطلاعات ورودی را بررسی کرده و مجدداً تلاش فرمایید.'
    toast.add({
      title: 'خطا در ارسال پیام',
      description: msg,
      color: 'error'
    })
  } finally {
    isSubmitting.value = false
  }
}

const contactCards = computed(() => [
  {
    title: 'تلفن تماس پشتیبانی',
    value: phone.value,
    sub: workHours.value,
    icon: 'i-lucide-phone-call'
  },
  {
    title: 'نشانی دفتر مرکزی',
    value: address.value,
    sub: 'مراجعه حضوری با هماهنگی قبلی',
    icon: 'i-lucide-map-pin'
  },
  {
    title: 'پست الکترونیک',
    value: email.value,
    sub: 'پاسخگویی حداکثر ظرف ۴ ساعت کاری',
    icon: 'i-lucide-mail'
  }
])
</script>

<template>
  <div class="py-8 sm:py-12 flex flex-col gap-10 max-w-5xl mx-auto">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-neutral-400">
      <NuxtLink
        to="/"
        class="hover:text-primary transition-colors"
      >
        صفحه اصلی
      </NuxtLink>
      <span>/</span>
      <span class="text-neutral-700 dark:text-neutral-300 font-medium">تماس با ما</span>
    </nav>

    <!-- Header Section -->
    <div class="text-center max-w-xl mx-auto flex flex-col gap-3">
      <h1 class="text-2xl sm:text-3xl font-black text-neutral-900 dark:text-white">
        ارتباط با پشتیبانی {{ storeName }}
      </h1>
      <p class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400 leading-relaxed">
        سوال، پیشنهاد یا نیاز به راهنمایی در ثبت سفارش دارید؟ تیم پشتیبانی ما همیشه مشتاق شنیدن صدای گرم شماست.
      </p>
    </div>

    <!-- Contact Info Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div
        v-for="(c, idx) in contactCards"
        :key="idx"
        class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-6 shadow-xs flex flex-col gap-3 text-center items-center"
      >
        <div class="w-12 h-12 rounded-2xl bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 flex items-center justify-center">
          <UIcon
            :name="c.icon"
            class="w-6 h-6"
          />
        </div>
        <span class="text-xs font-bold text-neutral-400">{{ c.title }}</span>
        <span class="text-sm font-black text-neutral-900 dark:text-white [direction:ltr]">{{ c.value }}</span>
        <span class="text-[11px] text-neutral-400 leading-relaxed">{{ c.sub }}</span>
      </div>
    </div>

    <!-- Contact Message Form Card -->
    <div class="bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 rounded-3xl p-6 sm:p-10 shadow-xs max-w-3xl mx-auto w-full">
      <div class="mb-6 flex flex-col gap-1">
        <h2 class="text-lg font-black text-neutral-900 dark:text-white">
          ارسال پیام مستقیم
        </h2>
        <p class="text-xs text-neutral-400">
          فرم زیر را پر کنید؛ کارشناسان ما در سریع‌ترین زمان ممکن پاسخگوی شما خواهند بود.
        </p>
      </div>

      <form
        class="space-y-5"
        @submit.prevent="handleSubmit"
      >
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-neutral-700 dark:text-neutral-300 mb-2">
              نام و نام خانوادگی <span class="text-red-500">*</span>
            </label>
            <UInput
              v-model="form.name"
              placeholder="مثال: سارا محمدی"
              size="lg"
              class="w-full"
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-neutral-700 dark:text-neutral-300 mb-2">
              شماره تلفن همراه <span class="text-red-500">*</span>
            </label>
            <UInput
              v-model="form.mobile"
              placeholder="۰۹۱۲۳۴۵۶۷۸۹"
              size="lg"
              class="w-full font-mono text-left"
            />
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-neutral-700 dark:text-neutral-300 mb-2">
            موضوع پیام
          </label>
          <UInput
            v-model="form.subject"
            placeholder="مثال: پیگیری مرسوله پستی یا سوال پیش از خرید"
            size="lg"
            class="w-full"
          />
        </div>

        <div>
          <label class="block text-xs font-bold text-neutral-700 dark:text-neutral-300 mb-2">
            متن پیام شما <span class="text-red-500">*</span>
          </label>
          <UTextarea
            v-model="form.message"
            placeholder="شرح پیام یا پرسش خود را اینجا بنویسید..."
            :rows="5"
            class="w-full text-sm leading-relaxed"
          />
        </div>

        <div class="pt-2 flex justify-end">
          <UButton
            type="submit"
            color="primary"
            size="lg"
            :loading="isSubmitting"
            icon="i-lucide-send"
            class="font-bold px-8 cursor-pointer"
          >
            ارسال پیام
          </UButton>
        </div>
      </form>
    </div>
  </div>
</template>
