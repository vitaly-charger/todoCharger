<script setup>
import { computed } from 'vue';

const props = defineProps({
  value: { type: Number, default: 0.8 },
});
const pct = computed(() => Math.round(props.value * 100));
const tone = computed(() =>
  props.value >= 0.8 ? 'bg-success' : props.value >= 0.6 ? 'bg-info' : 'bg-warn',
);
</script>

<template>
  <span class="inline-flex items-center gap-[5px] text-[10.5px] text-fg-subtle font-medium">
    <span class="relative w-[26px] h-1 rounded-full bg-border-strong overflow-hidden">
      <span class="absolute inset-y-0 left-0" :class="tone" :style="{ width: pct + '%' }" />
    </span>
    <span class="font-mono text-[10px]">{{ pct }}%</span>
  </span>
</template>
