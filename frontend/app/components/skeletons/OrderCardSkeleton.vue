<script setup lang="ts">
// Skeleton placeholder for Order history list
</script>

<template>
  <div class="p-4 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-(--ui-radius,0.75rem) space-y-4 animate-pulse">
    <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
      <div class="space-y-1.5">
        <USkeleton class="h-4 w-28 rounded-md" />
        <USkeleton class="h-3 w-36 rounded-sm" />
      </div>
      <USkeleton class="h-6 w-20 rounded-full" />
    </div>

    <div class="flex items-center gap-3">
      <USkeleton v-for="i in 3" :key="i" class="h-14 w-14 rounded-md shrink-0" />
    </div>

    <div class="flex items-center justify-between pt-2">
      <USkeleton class="h-4 w-32 rounded-sm" />
      <USkeleton class="h-8 w-24 rounded-md" />
    </div>
  </div>
</template>
