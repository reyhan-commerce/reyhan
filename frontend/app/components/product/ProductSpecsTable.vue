<script setup lang="ts">
import type { ProductSpecGroup } from '~/types/product'

defineProps<{
  groups?: ProductSpecGroup[]
}>()
</script>

<template>
  <div class="flex flex-col gap-8">
    <div
      v-if="!groups || groups.length === 0"
      class="text-center py-12 text-neutral-400 bg-neutral-50 dark:bg-neutral-800/40 rounded-2xl border border-dashed border-neutral-200 dark:border-neutral-700"
    >
      <UIcon
        name="i-lucide-file-text"
        class="size-10 mx-auto text-neutral-300 dark:text-neutral-600 mb-2"
      />
      <p class="text-sm font-medium">
        هنوز مشخصات فنی رسمی برای این کالا ثبت نشده است.
      </p>
    </div>

    <div
      v-for="group in groups"
      :key="group.group_id"
      class="flex flex-col gap-4"
    >
      <!-- Group Title -->
      <div class="flex items-center gap-2.5 pb-2 border-b border-neutral-200 dark:border-neutral-800">
        <div class="w-1.5 h-5 rounded-full bg-primary" />
        <h3 class="font-bold text-base text-neutral-900 dark:text-white">
          {{ group.group_name }}
        </h3>
      </div>

      <!-- Specs Key-Value Grid -->
      <div class="grid grid-cols-1 md:grid-cols-12 gap-px bg-neutral-200 dark:bg-neutral-800 rounded-xl overflow-hidden border border-neutral-200 dark:border-neutral-800">
        <template
          v-for="item in group.items"
          :key="item.id"
        >
          <!-- Spec Label (4 cols on desktop) -->
          <div class="md:col-span-4 bg-neutral-50 dark:bg-neutral-900/90 p-3.5 sm:px-4 text-xs sm:text-sm font-medium text-neutral-600 dark:text-neutral-400 flex items-center">
            {{ item.name }}
          </div>

          <!-- Spec Value (8 cols on desktop) -->
          <div class="md:col-span-8 bg-white dark:bg-neutral-900 p-3.5 sm:px-4 text-xs sm:text-sm text-neutral-900 dark:text-neutral-100 flex items-center gap-1.5 leading-relaxed font-normal">
            <span>{{ item.value }}</span>
            <span
              v-if="item.unit"
              class="text-neutral-400 text-xs"
            >{{ item.unit }}</span>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>
