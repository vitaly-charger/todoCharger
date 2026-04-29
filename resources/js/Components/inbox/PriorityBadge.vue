<script setup>
import Badge from './Badge.vue';
const props = defineProps({ priority: { type: String, required: true } });
const map = {
  urgent: { label: 'Urgent', variant: 'urgent' },
  high: { label: 'High', variant: 'warn' },
  normal: { label: 'Normal', variant: 'neutral' },
  low: { label: 'Low', variant: 'info' },
};
const it = map[props.priority] || { label: props.priority, variant: 'neutral' };
</script>

<template>
  <Badge :variant="it.variant" size="sm">{{ it.label }}</Badge>
</template>
