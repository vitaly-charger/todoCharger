<script setup>
defineProps({
  source: { type: String, default: 'manual' },
  size: { type: [Number, String], default: 16 },
});
</script>

<template>
  <svg v-if="source === 'gmail'" :width="size" :height="size" viewBox="0 0 16 16" aria-hidden="true" class="shrink-0 inline-block">
    <rect x="1" y="3" width="14" height="10" rx="1.6" fill="#fff" stroke="#e6c8c4" stroke-width=".7"/>
    <path d="M1.5 4 8 9l6.5-5" stroke="#d93025" stroke-width="1.4" fill="none" stroke-linejoin="round"/>
    <path d="M1.5 4v8M14.5 4v8" stroke="#d93025" stroke-width="1.4" fill="none"/>
  </svg>
  <svg v-else-if="source === 'slack'" :width="size" :height="size" viewBox="0 0 16 16" aria-hidden="true" class="shrink-0 inline-block">
    <rect x="2" y="6" width="4.5" height="2" rx="1" fill="#36c5f0"/>
    <rect x="8" y="9.5" width="4.5" height="2" rx="1" fill="#2eb67d"/>
    <rect x="6" y="2" width="2" height="4.5" rx="1" fill="#ecb22e"/>
    <rect x="9.5" y="8" width="2" height="4.5" rx="1" fill="#e01e5a"/>
    <rect x="2" y="9.5" width="2" height="2" rx="1" fill="#ecb22e"/>
    <rect x="12" y="6" width="2" height="2" rx="1" fill="#36c5f0"/>
    <rect x="6" y="12" width="2" height="2" rx="1" fill="#2eb67d"/>
    <rect x="6" y="6" width="2" height="2" rx="1" fill="#e01e5a"/>
  </svg>
  <svg v-else-if="source === 'telegram'" :width="size" :height="size" viewBox="0 0 16 16" aria-hidden="true" class="shrink-0 inline-block">
    <circle cx="8" cy="8" r="7" fill="#229ed9"/>
    <path d="M3.6 8.2 11.7 5l-1.3 6.6c-.1.5-.4.6-.8.4l-2-1.5-1 1c-.1.1-.2.2-.4.2l.1-2.1 3.8-3.4c.2-.1 0-.2-.2-.1l-4.6 2.9-2-.6c-.4-.1-.4-.4.1-.6Z" fill="#fff"/>
  </svg>
  <svg v-else-if="source === 'monday'" :width="size" :height="size" viewBox="0 0 16 16" aria-hidden="true" class="shrink-0 inline-block">
    <circle cx="3.5" cy="8" r="2" fill="#ff3d57"/>
    <circle cx="8" cy="8" r="2" fill="#ffcb00"/>
    <circle cx="12.5" cy="8" r="2" fill="#00ca72"/>
  </svg>
  <svg v-else-if="source === 'wrike'" :width="size" :height="size" viewBox="0 0 16 16" aria-hidden="true" class="shrink-0 inline-block">
    <circle cx="8" cy="8" r="7" fill="#08c"/>
    <path d="M5 8l2.5 2.5L11 5.5" stroke="#fff" stroke-width="1.4" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
  </svg>
  <svg v-else :width="size" :height="size" viewBox="0 0 16 16" aria-hidden="true" class="shrink-0 inline-block text-fg-muted">
    <circle cx="8" cy="8" r="7" fill="none" stroke="currentColor" stroke-width="1.2" opacity=".5"/>
    <path d="M5 10.5 10.5 5M7 5h3.5v3.5" stroke="currentColor" stroke-width="1.4" fill="none" stroke-linecap="round"/>
  </svg>
</template>
