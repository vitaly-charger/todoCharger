<script setup>
import Badge from './Badge.vue';
const props = defineProps({ type: { type: String, required: true } });
const map = {
  gmail: { label: 'Gmail', variant: 'urgent' },
  slack: { label: 'Slack', variant: 'review' },
  telegram: { label: 'Telegram', variant: 'info' },
  monday: { label: 'monday', variant: 'warn' },
  wrike: { label: 'Wrike', variant: 'success' },
  manual: { label: 'Manual', variant: 'neutral' },
};
const it = map[props.type] || { label: props.type, variant: 'neutral' };
</script>

<template>
  <Badge :variant="it.variant" size="sm">{{ it.label }}</Badge>
</template>
