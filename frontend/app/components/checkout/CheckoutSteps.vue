<script setup lang="ts">
defineProps<{
  currentStep: 1 | 2 | 3
}>()

const emit = defineEmits<{
  (e: 'update:currentStep', step: 1 | 2 | 3): void
}>()
</script>

<template>
  <div class="rounded-3xl bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800/80 p-4 sm:p-6 shadow-xs">
    <div class="flex items-center justify-between max-w-2xl mx-auto">
      <!-- Step 1 -->
      <button
        type="button"
        class="flex items-center gap-2.5 sm:gap-3 transition-colors text-start cursor-pointer"
        :class="currentStep === 1 ? 'text-primary font-black' : 'text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200'"
        @click="emit('update:currentStep', 1)"
      >
        <div
          class="size-9 sm:size-10 rounded-2xl flex items-center justify-center font-bold text-sm transition-all"
          :class="currentStep >= 1 ? 'bg-primary text-white shadow-md shadow-primary/25' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-400'"
        >
          <UIcon
            v-if="currentStep > 1"
            name="i-lucide-check"
            class="size-5"
          />
          <span v-else>۱</span>
        </div>
        <div class="hidden sm:flex flex-col">
          <span class="text-xs font-bold">مرحله اول</span>
          <span class="text-sm">آدرس تحویل</span>
        </div>
      </button>

      <div class="flex-1 h-0.5 mx-3 sm:mx-6 bg-neutral-200 dark:bg-neutral-800 rounded-full overflow-hidden">
        <div
          class="h-full bg-primary transition-all duration-300"
          :style="{ width: currentStep >= 2 ? '100%' : '0%' }"
        />
      </div>

      <!-- Step 2 -->
      <button
        type="button"
        class="flex items-center gap-2.5 sm:gap-3 transition-colors text-start cursor-pointer"
        :class="currentStep === 2 ? 'text-primary font-black' : 'text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200'"
        @click="emit('update:currentStep', 2)"
      >
        <div
          class="size-9 sm:size-10 rounded-2xl flex items-center justify-center font-bold text-sm transition-all"
          :class="currentStep >= 2 ? 'bg-primary text-white shadow-md shadow-primary/25' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-400'"
        >
          <UIcon
            v-if="currentStep > 2"
            name="i-lucide-check"
            class="size-5"
          />
          <span v-else>۲</span>
        </div>
        <div class="hidden sm:flex flex-col">
          <span class="text-xs font-bold">مرحله دوم</span>
          <span class="text-sm">شیوه ارسال</span>
        </div>
      </button>

      <div class="flex-1 h-0.5 mx-3 sm:mx-6 bg-neutral-200 dark:bg-neutral-800 rounded-full overflow-hidden">
        <div
          class="h-full bg-primary transition-all duration-300"
          :style="{ width: currentStep === 3 ? '100%' : '0%' }"
        />
      </div>

      <!-- Step 3 -->
      <button
        type="button"
        class="flex items-center gap-2.5 sm:gap-3 transition-colors text-start cursor-pointer"
        :class="currentStep === 3 ? 'text-primary font-black' : 'text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200'"
        @click="emit('update:currentStep', 3)"
      >
        <div
          class="size-9 sm:size-10 rounded-2xl flex items-center justify-center font-bold text-sm transition-all"
          :class="currentStep === 3 ? 'bg-primary text-white shadow-md shadow-primary/25' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-400'"
        >
          <span>۳</span>
        </div>
        <div class="hidden sm:flex flex-col">
          <span class="text-xs font-bold">مرحله سوم</span>
          <span class="text-sm">پرداخت آنلاین</span>
        </div>
      </button>
    </div>
  </div>
</template>
