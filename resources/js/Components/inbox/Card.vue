<script setup>
defineProps({
  title: { type: [String, Object], default: null },
  accent: { type: Boolean, default: false },
  padded: { type: Boolean, default: true },
});
</script>

<template>
  <div
    class="bg-bg-elev border border-border rounded-lg shadow-e1 overflow-hidden"
    :class="accent ? 'border-t-2 border-t-accent' : ''"
  >
    <div
      v-if="title || $slots.title || $slots.action"
      class="flex items-center justify-between px-4 py-3 border-b border-border"
    >
      <div class="text-[12.5px] font-semibold text-fg tracking-[-0.005em]">
        <slot name="title">{{ title }}</slot>
      </div>
      <div v-if="$slots.action"><slot name="action" /></div>
    </div>
    <div :class="padded ? 'p-5' : ''">
      <slot />
    </div>
  </div>
</template>
