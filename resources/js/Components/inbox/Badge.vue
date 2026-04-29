<script setup>
import { computed } from 'vue';
import Icon from './Icon.vue';

const props = defineProps({
  variant: { type: String, default: 'neutral' }, // neutral|outline|accent|urgent|success|info|warn|review
  size: { type: String, default: 'sm' }, // xs|sm|md
  icon: { type: String, default: null },
  dot: { type: Boolean, default: false },
});

const variantClass = computed(
  () =>
    ({
      neutral: 'bg-bg-sunken text-fg-muted border border-border',
      outline: 'bg-transparent text-fg-muted border border-border-strong',
      accent:  'bg-accent-soft text-accent-fg border border-transparent',
      urgent:  'bg-urgent-soft text-urgent-fg border border-transparent',
      success: 'bg-success-soft text-success-fg border border-transparent',
      info:    'bg-info-soft text-info-fg border border-transparent',
      warn:    'bg-warn-soft text-warn-fg border border-transparent',
      review:  'bg-review-soft text-review-fg border border-transparent',
    })[props.variant],
);

const sz = computed(
  () =>
    ({
      xs: { txt: 'text-[10.5px]', h: 'h-[18px]', pad: 'px-1.5 py-px', gap: 'gap-1', icon: 12 },
      sm: { txt: 'text-[11px]',   h: 'h-5',      pad: 'px-[7px] py-0.5', gap: 'gap-[5px]', icon: 13 },
      md: { txt: 'text-xs',       h: 'h-6',      pad: 'px-[9px] py-[3px]', gap: 'gap-1.5', icon: 14 },
    })[props.size],
);
</script>

<template>
  <span
    :class="[
      'inline-flex items-center rounded-full font-medium leading-none whitespace-nowrap tracking-[-0.005em]',
      sz.h, sz.pad, sz.txt, sz.gap, variantClass,
    ]"
  >
    <span v-if="dot" class="w-1.5 h-1.5 rounded-full bg-current opacity-80" />
    <Icon v-if="icon" :name="icon" :size="sz.icon" />
    <slot />
  </span>
</template>
