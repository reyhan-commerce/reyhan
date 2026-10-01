<script setup lang="ts">
// Minimalist, print-dedicated layout for invoices and official receipts
</script>

<template>
  <div class="min-h-screen bg-neutral-100 dark:bg-neutral-950 text-neutral-900 dark:text-neutral-100 print:bg-white print:text-black py-4 sm:py-8 px-2 sm:px-4 print:p-0 print:m-0 font-sans">
    <slot />
  </div>
</template>

<style>
@media print {
  @page {
    size: A4 portrait;
    margin: 10mm;
  }

  body {
    background-color: #ffffff !important;
    color: #000000 !important;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }

  /* Prevent page breaks inside cards, tables, and rows */
  .no-break-inside {
    break-inside: avoid;
    page-break-inside: avoid;
  }

  /* Hide scrollbars during print */
  ::-webkit-scrollbar {
    display: none;
  }
}
</style>
