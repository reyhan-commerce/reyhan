<script setup lang="ts">
// Reusable skeleton placeholder for ProductCard
</script>

<template>
  <div class="flex flex-col bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-(--ui-radius,0.75rem) p-3 shadow-xs animate-pulse">
    <!-- Image placeholder -->
    <USkeleton class="aspect-square w-full rounded-(--ui-radius,0.75rem) mb-3" />

    <!-- Brand badge -->
    <USkeleton class="h-4 w-16 mb-2 rounded-md" />

    <!-- Title lines -->
    <USkeleton class="h-4 w-full mb-1.5 rounded-md" />
    <USkeleton class="h-4 w-3/4 mb-3 rounded-md" />

    <!-- Price & Button footer -->
    <div class="mt-auto pt-2 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
      <div class="space-y-1">
        <USkeleton class="h-3 w-12 rounded-sm" />
        <USkeleton class="h-5 w-20 rounded-md" />
      </div>
      <USkeleton class="h-8 w-8 rounded-full" />
    </div>
  </div>
</template>
