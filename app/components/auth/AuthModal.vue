<script setup lang="ts">
const authStore = useAuthStore()
const toast = useToast()

const step = ref<'mobile' | 'otp'>('mobile')
const mobile = ref('')
const captchaCode = ref('')
const captchaKey = ref('')
const otpCode = ref('')

// Countdown timer for OTP resend (120 seconds)
const countdown = ref(120)
let timerInterval: ReturnType<typeof setInterval> | null = null

const formattedCountdown = computed(() => {
  const minutes = Math.floor(countdown.value / 60)
  const seconds = countdown.value % 60
  return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
})

const startTimer = () => {
  countdown.value = 120
  if (timerInterval) clearInterval(timerInterval)

  timerInterval = setInterval(() => {
    if (countdown.value > 0) {
      countdown.value--
    } else if (timerInterval) {
      clearInterval(timerInterval)
    }
  }, 1000)
}

const handleRequestOtp = async () => {
  if (!mobile.value || !/^09\d{9}$/.test(mobile.value)) {
    toast.add({
      title: 'خطای اعتبارسنجی',
      description: 'لطفاً شماره موبایل معتبر ۱۱ رقمی (شروع با ۰۹) وارد نمایید.',
      color: 'warning',
      icon: 'i-lucide-alert-triangle'
    })
    return
  }

  if (!captchaCode.value) {
    toast.add({
      title: 'کد امنیتی',
      description: 'لطفاً کد امنیتی را وارد فرمایید.',
      color: 'warning',
      icon: 'i-lucide-shield-alert'
    })
    return
  }

  const success = await authStore.requestOtp(mobile.value, captchaKey.value, captchaCode.value)
  if (success) {
    step.value = 'otp'
    startTimer()
    toast.add({
      title: 'ارسال کد تایید',
      description: `کد تایید با موفقیت به شماره ${mobile.value} ارسال شد.`,
      color: 'success',
      icon: 'i-lucide-check-circle'
    })
  }
}

const handleVerifyOtp = async () => {
  if (!otpCode.value || otpCode.value.length < 5) {
    toast.add({
      title: 'خطا',
      description: 'لطفاً کد تایید ۵ رقمی دریافتی را وارد فرمایید.',
      color: 'warning',
      icon: 'i-lucide-alert-triangle'
    })
    return
  }

  const success = await authStore.verifyOtp(mobile.value, otpCode.value)
  if (success) {
    toast.add({
      title: 'خوش آمدید',
      description: 'ورود با موفقیت انجام شد.',
      color: 'success',
      icon: 'i-lucide-smile'
    })
    resetModal()
  }
}

const handleResendOtp = async () => {
  if (countdown.value > 0) return
  step.value = 'mobile'
  captchaCode.value = ''
}

const resetModal = () => {
  step.value = 'mobile'
  mobile.value = ''
  captchaCode.value = ''
  otpCode.value = ''
  if (timerInterval) clearInterval(timerInterval)
}

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval)
})
</script>

<template>
  <UModal
    v-model:open="authStore.isAuthModalOpen"
    :title="step === 'mobile' ? 'ورود / ثبت‌نام در ایزیشاپ' : 'تایید شماره موبایل'"
    :description="step === 'mobile' ? 'جهت ورود یا ایجاد حساب کاربری، شماره موبایل خود را وارد نمایید.' : `کد پیامک‌شده به شماره ${mobile} را وارد فرمایید.`"
    dir="rtl"
  >
    <template #body>
      <!-- Step 1: Mobile & Captcha -->
      <form
        v-if="step === 'mobile'"
        class="space-y-4"
        @submit.prevent="handleRequestOtp"
      >
        <div class="space-y-1.5">
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
            شماره موبایل
          </label>
          <UInput
            v-model="mobile"
            type="tel"
            inputmode="numeric"
            placeholder="۰۹۱۲۳۴۵۶۷۸۹"
            size="xl"
            class="min-h-12 w-full text-lg tracking-wider"
            icon="i-lucide-phone"
            autofocus
          />
        </div>

        <AuthCaptchaInput
          v-model="captchaCode"
          @key-change="captchaKey = $event"
        />

        <UButton
          type="submit"
          color="primary"
          size="xl"
          block
          class="min-h-12 text-base font-semibold"
          :loading="authStore.isLoading"
        >
          دریافت کد تایید
        </UButton>
      </form>

      <!-- Step 2: OTP Verification -->
      <form
        v-else
        class="space-y-4"
        @submit.prevent="handleVerifyOtp"
      >
        <div class="flex items-center justify-between text-sm text-neutral-600 dark:text-neutral-400">
          <span>ارسال شده به: <strong>{{ mobile }}</strong></span>
          <UButton
            variant="link"
            color="neutral"
            size="sm"
            class="p-0"
            @click="step = 'mobile'"
          >
            ویرایش شماره
          </UButton>
        </div>

        <div class="space-y-1.5">
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
            کد تایید ۵ رقمی
          </label>
          <UInput
            v-model="otpCode"
            type="text"
            inputmode="numeric"
            maxlength="5"
            placeholder="• • • • •"
            size="xl"
            class="min-h-14 w-full text-center text-2xl font-bold tracking-widest"
            autofocus
          />
        </div>

        <!-- Timer & Resend -->
        <div class="flex items-center justify-center text-sm">
          <span
            v-if="countdown > 0"
            class="text-neutral-500 flex items-center gap-1.5"
          >
            <UIcon
              name="i-lucide-timer"
              class="size-4"
            />
            زمان باقی‌مانده تا ارسال مجدد:
            <span class="font-bold text-primary">{{ formattedCountdown }}</span>
          </span>
          <UButton
            v-else
            variant="link"
            color="primary"
            class="p-0 font-medium"
            @click="handleResendOtp"
          >
            ارسال مجدد کد تایید
          </UButton>
        </div>

        <UButton
          type="submit"
          color="primary"
          size="xl"
          block
          class="min-h-12 text-base font-semibold"
          :loading="authStore.isLoading"
        >
          تایید و ورود به حساب
        </UButton>
      </form>
    </template>
  </UModal>
</template>
