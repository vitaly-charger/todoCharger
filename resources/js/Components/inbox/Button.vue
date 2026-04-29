<script setup>
import { computed } from 'vue';
import Icon from './Icon.vue';
import KBD from './KBD.vue';

const props = defineProps({
  variant: { type: String, default: 'ghost' }, // primary | secondary | ghost | outline | danger
  size: { type: String, default: 'md' }, // xs | sm | md | lg
  icon: { type: String, default: null },
  iconRight: { type: String, default: null },
  kbd: { type: String, default: null },
  fullWidth: { type: Boolean, default: false },
  type: { type: String, default: 'button' },
  disabled: { type: Boolean, default: false },
});

const sz = computed(
  () =>
    ({
      xs: { h: 'h-[22px]', px: 'px-[7px]', txt: 'text-[11.5px]', gap: 'gap-[5px]', icon: 13 },
      sm: { h: 'h-7',      px: 'px-[10px]', txt: 'text-[12.5px]', gap: 'gap-[6px]', icon: 14 },
      md: { h: 'h-8',      px: 'px-3',     txt: 'text-[13px]',   gap: 'gap-[6px]', icon: 15 },
      lg: { h: 'h-[38px]', px: 'px-4',     txt: 'text-[14px]',   gap: 'gap-2',    icon: 16 },
    })[props.size] || {},
);

const variantClass = computed(
  () =>
    ({
      primary:
        'bg-accent text-accent-on border-accent-hover shadow-e1 hover:bg-accent-hover font-[550]',
      secondary:
        'bg-bg-elev text-fg border-border-strong shadow-e1 hover:bg-bg-hover font-medium',
      ghost:
        'bg-transparent text-fg-muted border-transparent hover:bg-bg-hover hover:text-fg font-medium',
      outline:
        'bg-transparent text-fg border-border-strong hover:bg-bg-hover font-medium',
      danger:
        'bg-transparent text-urgent-fg border-urgent-soft hover:bg-urgent-soft/60 font-medium',
    })[props.variant] || '',
);
</script>

<template>
  <button
    :type="type"
    :disabled="disabled"
    :class="[
      'inline-flex items-center justify-center rounded-md border tracking-[-0.005em] transition-colors',
      'disabled:opacity-50 disabled:cursor-default cursor-pointer whitespace-nowrap',
      sz.h, sz.px, sz.txt, sz.gap,
      variantClass,
      fullWidth ? 'w-full' : '',
    ]"
  >
    <Icon v-if="icon" :name="icon" :size="sz.icon" />
    <span v-if="$slots.default"><slot /></span>
    <Icon v-if="iconRight" :name="iconRight" :size="sz.icon" />
    <KBD v-if="kbd">{{ kbd }}</KBD>
  </button>
</template>
