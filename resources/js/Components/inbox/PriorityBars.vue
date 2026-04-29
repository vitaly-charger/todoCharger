<script setup>
import { computed } from 'vue';

const props = defineProps({
  level: { type: String, default: 'normal' }, // urgent|high|normal|medium|low
});

const meta = computed(() => {
  const m = {
    urgent: { bars: 3, color: 'var(--urgent)' },
    high:   { bars: 3, color: 'var(--accent)' },
    medium: { bars: 2, color: 'var(--fg-muted)' },
    normal: { bars: 2, color: 'var(--fg-muted)' },
    low:    { bars: 1, color: 'var(--fg-subtle)' },
  };
  return m[props.level] || m.normal;
});
</script>

<template>
  <span class="inline-flex items-end gap-[2px] h-3">
    <span
      v-for="i in 3"
      :key="i"
      class="w-[3px] rounded-[1px]"
      :style="{
        height: (4 + i * 2) + 'px',
        background: i <= meta.bars ? meta.color : 'var(--border-strong)',
      }"
    />
  </span>
</template>
