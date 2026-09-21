<script setup lang="ts">
useSeoMeta({
  title: 'تماس با ما - پشتیبانی ایزیشاپ',
  description: 'راه‌های ارتباطی، نشانی پستی دفتر مرکزی و فرم ارسال پیام به واحد پشتیبانی ایزیشاپ.',
})

const toast = useToast()

const form = reactive({
  name: '',
  mobile: '',
  subject: '',
  message: '',
})

const isSubmitting = ref(false)

const handleSubmit = () => {
  if (!form.name || !form.mobile || !form.message) {
    toast.add({
      title: 'خطای اعتبارسنجی',
      description: 'لطفاً نام، شماره تماس و متن پیام خود را وارد نمایید.',
      color: 'error',
    })
    return
  }

  isSubmitting.value = true
  setTimeout(() => {
    isSubmitting.value = false
    toast.add({
      title: 'پیام دریافت شد',
      description: 'پیام شما با موفقیت ثبت گردید. کارشناسان پشتیبانی به زودی با شما تماس خواهند گرفت.',
      color: 'success',
    })
    form.name = ''
    form.mobile = ''
    form.subject = ''
    form.message = ''
  }, 600)
}

const contactInfo = [
  {
    title: 'تلفن تماس پشتیبانی',
    value: '۰۲۱-۸۸۸۸۹۹۹۹',
    sub: 'پاسخگویی در تمامی روزهای هفته از ۹ الی ۱۸',
    icon: 'i-lucide-phone-call',
  },
  {
    title: 'نشانی دفتر مرکزی',
    value: 'تهران، خیابان ولیعصر، برج تجارت، طبقه ۵',
    sub: 'مراجعه حضوری با هماهنگی قبلی',
    icon: 'i-lucide-map-pin',
  },
  {
    title: 'پست الکترونیک',
    value: 'support@easyshop.test',
    sub: 'پاسخگویی حداکثر ظرف ۴ ساعت کاری',
    icon: 'i-lucide-mail',
  },
]
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
        ارتباط با پشتیبانی ایزیشاپ
      </h1>
      <p class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400 leading-relaxed">
        سوال، پیشنهاد یا نیاز به راهنمایی در ثبت سفارش دارید؟ تیم پشتیبانی ما همیشه مشتاق شنیدن صدای گرم شماست.
      </p>
    </div>

    <!-- Contact Info Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div
        v-for="(c, idx) in contactInfo"
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
        <span class="text-sm font-black text-neutral-900 dark:text-white">{{ c.value }}</span>
        <span class="text-[11px] text-neutral-400">{{ c.sub }}</span>
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
              نام و نام خانوادگی <span class="text-rose-500">*</span>
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
              شماره تلفن همراه <span class="text-rose-500">*</span>
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
            متن پیام شما <span class="text-rose-500">*</span>
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
