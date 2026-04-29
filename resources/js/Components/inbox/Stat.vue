<script setup>
import Icon from './Icon.vue';

defineProps({
  label: String,
  value: [String, Number],
  delta: String,
  deltaTone: { type: String, default: 'neutral' }, // up|down|neutral
  icon: String,
  accent: Boolean,
  sub: String,
});
</script>

<template>
  <div
    class="bg-bg-elev border border-border rounded-lg shadow-e1 px-[18px] py-4 flex flex-col gap-2.5 min-w-0"
    :class="accent ? 'border-t-2 border-t-accent' : ''"
  >
    <div class="flex items-center justify-between">
      <span class="text-[11.5px] text-fg-muted font-medium">{{ label }}</span>
      <span
        v-if="icon"
        class="w-[22px] h-[22px] rounded-md inline-flex items-center justify-center bg-bg-sunken text-fg-muted"
      >
        <Icon :name="icon" :size="13" />
      </span>
    </div>
    <div class="flex items-baseline gap-2 leading-none">
      <span class="text-[28px] font-semibold tracking-tight4 tabular-nums">{{ value }}</span>
      <span
        v-if="delta"
        class="text-[11.5px] font-medium tabular-nums"
        :class="{
          'text-success-fg': deltaTone === 'up',
          'text-urgent-fg': deltaTone === 'down',
          'text-fg-subtle': deltaTone === 'neutral',
        }"
      >{{ delta }}</span>
    </div>
    <div v-if="sub" class="text-[11.5px] text-fg-subtle leading-snug">{{ sub }}</div>
  </div>
</template>
