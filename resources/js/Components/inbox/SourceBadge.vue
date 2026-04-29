<script setup>
import { computed } from 'vue';
import SourceGlyph from './SourceGlyph.vue';

const props = defineProps({
  source: { type: String, default: 'manual' },
  size: { type: String, default: 'sm' }, // sm|md
  showLabel: { type: Boolean, default: true },
  meta: { type: String, default: null },
});

const labels = {
  gmail: 'Gmail', slack: 'Slack', telegram: 'Telegram',
  monday: 'Monday', wrike: 'Wrike', manual: 'Manual',
};
const label = computed(() => labels[props.source] || props.source);
</script>

<template>
  <span
    class="inline-flex items-center gap-1.5 rounded-full bg-bg-sunken border border-border text-fg-muted font-medium"
    :class="size === 'md' ? 'h-6 pl-[5px] pr-2 text-xs' : 'h-5 pl-[5px] pr-2 text-[11px]'"
  >
    <SourceGlyph :source="source" :size="size === 'md' ? 14 : 12" />
    <span v-if="showLabel">{{ label }}</span>
    <span v-if="meta" class="font-mono text-fg-faint text-[0.92em]">{{ meta }}</span>
  </span>
</template>
