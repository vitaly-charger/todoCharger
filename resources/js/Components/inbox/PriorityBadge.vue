<script setup>
import { computed } from 'vue';
import Badge from './Badge.vue';
import PriorityBars from './PriorityBars.vue';

const props = defineProps({
  level: { type: String, default: 'normal' },
  size: { type: String, default: 'sm' },
  withBars: { type: Boolean, default: true },
  // legacy alias
  priority: { type: String, default: null },
});

const lvl = computed(() => props.priority || props.level);

const meta = computed(() => {
  const m = {
    urgent: { label: 'Urgent', variant: 'urgent' },
    high:   { label: 'High',   variant: 'warn' },
    medium: { label: 'Medium', variant: 'neutral' },
    normal: { label: 'Normal', variant: 'neutral' },
    low:    { label: 'Low',    variant: 'neutral' },
  };
  return m[lvl.value] || m.normal;
});
</script>

<template>
  <Badge :variant="meta.variant" :size="size">
    <PriorityBars v-if="withBars" :level="lvl" />
    {{ meta.label }}
  </Badge>
</template>
