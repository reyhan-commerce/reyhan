<script setup lang="ts">
const emit = defineEmits<{
  verified: [token: string]
  reset: []
}>()

const authStore = useAuthStore()

type CheckboxState = 'idle' | 'verifying' | 'success' | 'error'
const state = ref<CheckboxState>('idle')
const isHovered = ref(false)

// Zero-allocation prefix check on raw SHA-256 byte buffer
const hasTargetPrefix = (bytes: Uint8Array, difficulty: number): boolean => {
  const fullBytes = Math.floor(difficulty / 2)
  for (let i = 0; i < fullBytes; i++) {
    if (bytes[i] !== 0) return false
  }
  if (difficulty % 2 === 1) {
    if (((bytes[fullBytes] ?? 0) >> 4) !== 0) return false
  }
  return true
}

const encoder = new TextEncoder()

const handleCheckboxClick = async () => {
  if (state.value === 'verifying' || state.value === 'success') return

  state.value = 'verifying'
  const startTime = performance.now()

  try {
    if (typeof window === 'undefined' || !window.crypto?.subtle) {
      console.error('[Captcha] crypto.subtle is unavailable (requires HTTPS or localhost).')
      state.value = 'error'
      emit('reset')
      return
    }

    const challenge = await authStore.fetchCaptcha()
    if (!challenge) {
      console.error('[Captcha] Failed to fetch challenge')
      state.value = 'error'
      emit('reset')
      return
    }

    let nonce = 0
    let found = false

    // Solve PoW using high-performance byte-level check
    while (!found && nonce < 200000) {
      const msgBuffer = encoder.encode(challenge.salt + nonce)
      const hashBuffer = await window.crypto.subtle.digest('SHA-256', msgBuffer)
      const bytes = new Uint8Array(hashBuffer)

      if (hasTargetPrefix(bytes, challenge.difficulty)) {
        found = true
        break
      }
      nonce++
    }

    if (!found) {
      console.error('[Captcha] Could not find nonce within limit')
      state.value = 'error'
      emit('reset')
      return
    }

    // Measure interaction + compute time
    const elapsedMs = Math.round(performance.now() - startTime)

    // Send solution to backend
    const verified = await authStore.solveCaptcha(challenge.key, nonce.toString(), Math.max(elapsedMs, 50))

    if (verified) {
      state.value = 'success'
      emit('verified', challenge.key)
    } else {
      console.error('[Captcha] Backend rejected solution')
      state.value = 'error'
      emit('reset')
    }
  } catch (error) {
    console.error('[Captcha] Unexpected error solving PoW:', error)
    state.value = 'error'
    emit('reset')
  }
}
</script>

<template>
  <div
    class="relative select-none flex items-center justify-between p-3.5 rounded-xl border transition-all duration-200 cursor-pointer min-h-[64px]"
    :class="[
      state === 'success'
        ? 'border-emerald-500/40 bg-emerald-500/5 dark:bg-emerald-500/10'
        : state === 'error'
          ? 'border-red-500/40 bg-red-500/5 dark:bg-red-500/10'
          : 'border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900/60 hover:border-primary/50'
    ]"
    role="checkbox"
    :aria-checked="state === 'success'"
    tabindex="0"
    @click="handleCheckboxClick"
    @keydown.space.prevent="handleCheckboxClick"
    @keydown.enter.prevent="handleCheckboxClick"
    @mouseenter="isHovered = true"
    @mouseleave="isHovered = false"
  >
    <!-- Left / Start: Checkbox & Text -->
    <div class="flex items-center gap-3.5">
      <!-- Checkbox box -->
      <div
        class="w-7 h-7 rounded-lg border-2 flex items-center justify-center transition-all duration-300 shrink-0"
        :class="[
          state === 'success'
            ? 'bg-emerald-500 border-emerald-500 text-white scale-105'
            : state === 'verifying'
              ? 'border-primary bg-primary/10'
              : state === 'error'
                ? 'border-red-500 bg-red-500/10 text-red-500'
                : 'border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800'
        ]"
      >
        <!-- Success Check Icon -->
        <UIcon
          v-if="state === 'success'"
          name="i-lucide-check"
          class="w-4 h-4 text-white transition-transform duration-200 scale-100"
        />

        <!-- Verifying Spinner -->
        <UIcon
          v-else-if="state === 'verifying'"
          name="i-lucide-loader-circle"
          class="w-4 h-4 text-primary animate-spin"
        />

        <!-- Error Icon -->
        <UIcon
          v-else-if="state === 'error'"
          name="i-lucide-refresh-cw"
          class="w-4 h-4 text-red-500"
        />
      </div>

      <!-- Label text -->
      <div class="flex flex-col">
        <span
          class="text-sm font-medium transition-colors"
          :class="[
            state === 'success'
              ? 'text-emerald-600 dark:text-emerald-400'
              : state === 'error'
                ? 'text-red-600 dark:text-red-400'
                : 'text-neutral-800 dark:text-neutral-200'
          ]"
        >
          <template v-if="state === 'idle'">من ربات نیستم</template>
          <template v-else-if="state === 'verifying'">در حال بررسی امنیتی...</template>
          <template v-else-if="state === 'success'">تأیید شد</template>
          <template v-else-if="state === 'error'">خطا در تأیید، کلیک مجدد</template>
        </span>
        <span class="text-[11px] text-neutral-400 dark:text-neutral-500">
          حفاظت امنیتی خودکار
        </span>
      </div>
    </div>

    <!-- Right / End: Shield / Brand Icon -->
    <div class="flex flex-col items-center justify-center pl-1 text-neutral-400 dark:text-neutral-600 shrink-0">
      <UIcon
        name="i-lucide-shield-check"
        class="w-6 h-6 transition-transform duration-300"
        :class="[
          state === 'success' ? 'text-emerald-500 scale-110' : 'text-neutral-400 dark:text-neutral-500'
        ]"
      />
      <span class="text-[9px] font-medium tracking-tight text-neutral-400 dark:text-neutral-500">
        EasyShop Guard
      </span>
    </div>
  </div>
</template>
