<script setup>
defineProps({
  size: { type: [Number, String], default: 22 },
  withWord: { type: Boolean, default: true },
});
</script>

<template>
  <span class="inline-flex items-center gap-2 text-fg">
    <svg :width="size" :height="size" viewBox="0 0 28 28" fill="none" aria-hidden="true">
      <rect x="2" y="9" width="24" height="15" rx="3.5" stroke="currentColor" stroke-width="1.6" />
      <path d="M2 16h7l1.5 2.5h7L19 16h7" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" />
      <path d="M14 3v4M11.5 4.5 14 7l2.5-2.5M14 7l-2.5 2.5M14 7l2.5 2.5"
        stroke="var(--accent)" stroke-width="1.6" stroke-linecap="round" />
    </svg>
    <span v-if="withWord" class="font-semibold text-sm tracking-tight2">AI Inbox</span>
  </span>
</template>
