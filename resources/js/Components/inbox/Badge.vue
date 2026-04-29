<script setup>
import { computed } from 'vue';
const props = defineProps({
  variant: { type: String, default: 'neutral' }, // neutral|accent|urgent|success|info|warn|review
  size: { type: String, default: 'md' }, // sm|md
});
const tone = computed(() => {
  const map = {
    neutral: { bg: 'var(--bg-sunken)', fg: 'var(--fg-muted)', border: 'var(--border)' },
    accent:  { bg: 'var(--accent-soft)', fg: 'var(--accent-fg)', border: 'transparent' },
    urgent:  { bg: 'var(--urgent-soft)', fg: 'var(--urgent-fg)', border: 'transparent' },
    success: { bg: 'var(--success-soft)', fg: 'var(--success-fg)', border: 'transparent' },
    info:    { bg: 'var(--info-soft)', fg: 'var(--info-fg)', border: 'transparent' },
    warn:    { bg: 'var(--warn-soft)', fg: 'var(--warn-fg)', border: 'transparent' },
    review:  { bg: 'var(--review-soft)', fg: 'var(--review-fg)', border: 'transparent' },
  };
  return map[props.variant] || map.neutral;
});
</script>

<template>
  <span class="ix-badge" :class="size" :style="{ background: tone.bg, color: tone.fg, borderColor: tone.border }">
    <slot />
  </span>
</template>

<style scoped>
.ix-badge {
  display: inline-flex; align-items: center; gap: 4px;
  border: 1px solid;
  border-radius: var(--r-sm);
  padding: 2px 8px;
  font-size: 12px; line-height: 1.4;
  font-weight: 500;
  white-space: nowrap;
}
.ix-badge.sm { padding: 1px 6px; font-size: 11px; }
</style>
