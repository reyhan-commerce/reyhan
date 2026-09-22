<script setup lang="ts">
import { ConfigProvider } from 'reka-ui'
import { otpRequestSchema, normalizeDigitsString } from '~/utils/schemas'

const authStore = useAuthStore()
const toast = useToast()
const { toEnglishDigits } = usePersian()

const step = ref<'mobile' | 'otp'>('mobile')
const mobile = ref('')
const captchaToken = ref('')
const otpValues = ref<string[]>([])
const otpCode = computed(() => otpValues.value.join(''))

// Auto-normalize mobile input (accepts Persian, Hindi/Arabic and English digits)
watch(mobile, (val) => {
  if (val) {
    const converted = toEnglishDigits(val)
    if (converted !== val) {
      mobile.value = converted
    }
  }
})

// Auto-normalize OTP digits when entered/pasted
watch(otpValues, (val) => {
  if (Array.isArray(val)) {
    val.forEach((digit, i) => {
      if (digit) {
        const converted = toEnglishDigits(digit)
        if (converted !== digit) {
          otpValues.value[i] = converted
        }
      }
    })
  }
}, { deep: true })

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
  const normalizedMobile = normalizeDigitsString(mobile.value)
  mobile.value = normalizedMobile

  const result = otpRequestSchema.safeParse({
    mobile: normalizedMobile,
    captcha: captchaToken.value
  })

  if (!result.success) {
    const errorMsg = result.error.issues[0]?.message || 'اطلاعات وارد شده نامعتبر است.'
    toast.add({
      title: 'خطای اعتبارسنجی',
      description: errorMsg,
      color: 'warning',
      icon: 'i-lucide-alert-triangle'
    })
    return
  }

  if (!captchaToken.value) {
    toast.add({
      title: 'تأیید امنیتی',
      description: 'لطفاً تیک «من ربات نیستم» را فعال نمایید.',
      color: 'warning',
      icon: 'i-lucide-shield-alert'
    })
    return
  }

  const success = await authStore.requestOtp(mobile.value, captchaToken.value)
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
  const code = toEnglishDigits(otpCode.value)
  if (!code || code.length !== 6) {
    toast.add({
      title: 'خطا',
      description: 'لطفاً کد تایید ۶ رقمی را وارد فرمایید.',
      color: 'warning',
      icon: 'i-lucide-alert-triangle'
    })
    return
  }

  const success = await authStore.verifyOtp(mobile.value, code)
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
  captchaToken.value = ''
  otpValues.value = []
}

const resetModal = () => {
  step.value = 'mobile'
  mobile.value = ''
  captchaToken.value = ''
  otpValues.value = []
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

        <AuthCaptchaCheckbox
          @verified="captchaToken = $event"
          @reset="captchaToken = ''"
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

        <div class="space-y-3 flex flex-col items-center">
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 self-start">
            کد تایید ۶ رقمی
          </label>
          <div
            class="flex justify-center w-full py-2 dir-ltr"
            dir="ltr"
          >
            <ConfigProvider dir="ltr">
              <UPinInput
                v-model="otpValues"
                :length="6"
                :separator="3"
                otp
                type="text"
                size="xl"
                placeholder="○"
                autofocus
                class="font-mono font-en dir-ltr"
                :ui="{ root: 'flex-row dir-ltr', base: 'font-mono font-en text-center text-lg' }"
                @complete="handleVerifyOtp"
              />
            </ConfigProvider>
          </div>
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
