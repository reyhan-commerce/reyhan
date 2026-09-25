<script setup lang="ts">
// Skeleton placeholder for PDP VariantSelector
</script>

<template>
  <div class="space-y-4 animate-pulse">
    <!-- Attribute 1 (e.g. Color) -->
    <div class="space-y-2">
      <div class="flex items-center justify-between">
        <USkeleton class="h-4 w-20 rounded-md" />
        <USkeleton class="h-4 w-16 rounded-md" />
      </div>
      <div class="flex items-center gap-2">
        <USkeleton
          v-for="i in 4"
          :key="i"
          class="h-9 w-9 rounded-full"
        />
      </div>
    </div>

    <!-- Attribute 2 (e.g. Size / Volume) -->
    <div class="space-y-2">
      <div class="flex items-center justify-between">
        <USkeleton class="h-4 w-16 rounded-md" />
        <USkeleton class="h-4 w-12 rounded-md" />
      </div>
      <div class="flex items-center gap-2">
        <USkeleton
          v-for="i in 3"
          :key="i"
          class="h-9 w-16 rounded-md"
        />
      </div>
    </div>
  </div>
</template>
