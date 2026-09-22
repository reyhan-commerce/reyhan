<script setup lang="ts">
// Skeleton placeholder for Cart slideover items and cart table
</script>

<template>
  <div class="flex items-center gap-3 p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-(--ui-radius,0.75rem) border border-zinc-200/60 dark:border-zinc-700/60 animate-pulse">
    <!-- Thumbnail -->
    <USkeleton class="h-16 w-16 rounded-md shrink-0" />

    <!-- Info -->
    <div class="flex-1 space-y-2">
      <USkeleton class="h-4 w-3/4 rounded-sm" />
      <USkeleton class="h-3 w-1/2 rounded-sm" />
      <div class="flex items-center justify-between pt-1">
        <USkeleton class="h-4 w-20 rounded-sm" />
        <USkeleton class="h-7 w-24 rounded-md" />
      </div>
    </div>
  </div>
</template>
