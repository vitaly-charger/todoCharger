<script setup>
import { computed } from 'vue';

const props = defineProps({
  name: { type: String, default: '?' },
  size: { type: [Number, String], default: 24 },
  src: { type: String, default: null },
  ring: { type: String, default: null },
});

const palette = [
  ['#fef3c7', '#92400e'],
  ['#dbeafe', '#1e40af'],
  ['#dcfce7', '#166534'],
  ['#fce7f3', '#9d174d'],
  ['#e0e7ff', '#3730a3'],
  ['#fee2e2', '#991b1b'],
  ['#f1f5f9', '#334155'],
];

const initials = computed(() =>
  (props.name || '?').split(' ').map((p) => p[0]).slice(0, 2).join('').toUpperCase(),
);
const colorIdx = computed(() => {
  let h = 0;
  for (const c of props.name || '?') h = (h * 31 + c.charCodeAt(0)) & 0xffff;
  return h % palette.length;
});
const dim = computed(() => Number(props.size));
const fontSize = computed(() => (dim.value <= 20 ? 9.5 : dim.value <= 28 ? 10.5 : 12));
</script>

<template>
  <span
    class="inline-flex items-center justify-center rounded-full font-semibold shrink-0 select-none"
    :style="{
      width: dim + 'px',
      height: dim + 'px',
      background: src ? 'transparent' : palette[colorIdx][0],
      color: palette[colorIdx][1],
      fontSize: fontSize + 'px',
      letterSpacing: '0.2px',
      boxShadow: ring ? `0 0 0 2px ${ring}` : 'none',
      backgroundImage: src ? `url(${src})` : 'none',
      backgroundSize: 'cover',
    }"
  >
    <template v-if="!src">{{ initials }}</template>
  </span>
</template>
