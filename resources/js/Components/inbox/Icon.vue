<script setup>
// Lucide-style icon set, 24x24 viewBox, currentColor stroke. 1.5px default.
import { computed } from 'vue';

const props = defineProps({
  name: { type: String, required: true },
  size: { type: [Number, String], default: 16 },
  stroke: { type: [Number, String], default: 1.5 },
});

const dim = computed(() => Number(props.size));

const ICONS = {
  inbox: 'M22 12h-6l-2 3h-4l-2-3H2|M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11Z',
  search: 'CIRCLE:11,11,7|m21 21-4.3-4.3',
  plus: 'M12 5v14M5 12h14',
  filter: 'M3 6h18M7 12h10M10 18h4',
  sort: 'm3 16 4 4 4-4M7 20V4M21 8l-4-4-4 4M17 4v16',
  more: 'CIRCLE:5,12,1|CIRCLE:12,12,1|CIRCLE:19,12,1',
  check: 'M20 6 9 17l-5-5',
  doubleCheck: 'm2 12 5 5L18 6M22 6 11 17',
  x: 'M18 6 6 18M6 6l12 12',
  chevronDown: 'm6 9 6 6 6-6',
  chevronRight: 'm9 18 6-6-6-6',
  chevronLeft: 'm15 18-6-6 6-6',
  arrowRight: 'M5 12h14M13 5l7 7-7 7',
  arrowUpRight: 'M7 17 17 7M7 7h10v10',
  external: 'M15 3h6v6|M10 14 21 3|M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6',
  flag: 'M4 22V4M4 4h13l-2 4 2 4H4',
  flame: 'M8.5 14.5A2.5 2.5 0 0 0 11 17c2 0 3-1.5 3-3 0-2-1-3-1-5 0-1.5-.5-2.5-1.5-3-.5 1.5-1.5 2-2.5 3-1 1-2 2-2 4 0 .8.3 1.5 1.5 1.5Z|M12 2c1 3 4 5 4 9 0 4-3 7-7 7s-7-3-7-7c0-3 2-5 4-7 1 2 3 3 3 5 0 1-1 2-1 3',
  clock: 'CIRCLE:12,12,9|M12 7v5l3 2',
  calendar: 'RECT:3,5,18,16,2|M16 3v4M8 3v4M3 11h18',
  user: 'CIRCLE:12,8,4|M4 21a8 8 0 0 1 16 0',
  users: 'CIRCLE:9,8,3.5|M2 21a7 7 0 0 1 14 0|M16 4.5a3.5 3.5 0 0 1 0 7M22 21a7 7 0 0 0-5-6.7',
  at: 'CIRCLE:12,12,4|M16 12v1.5a2.5 2.5 0 0 0 5 0V12a9 9 0 1 0-3.6 7.2',
  bell: 'M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9|M10 21a2 2 0 0 0 4 0',
  star: 'm12 2 3.1 6.4 7 1-5 4.9 1.2 7L12 18l-6.3 3.3 1.2-7-5-4.9 7-1z',
  sparkles: 'M12 3v4M12 17v4M3 12h4M17 12h4|m5.6 5.6 2.8 2.8M15.6 15.6l2.8 2.8M5.6 18.4l2.8-2.8M15.6 8.4l2.8-2.8',
  sparkle: 'M12 3 13.5 9 19.5 10.5 13.5 12 12 18 10.5 12 4.5 10.5 10.5 9z|M19 4v3M19 17v3M21.5 5.5h-3M3 12h2',
  wand: 'M15 4V2M15 16v-2M8 9h2M20 9h2M17.8 11.8 19 13M15 9h0M17.8 6.2 19 5M3 21l9-9M12.2 6.2 11 5',
  link: 'M10 14a5 5 0 0 0 7 0l3-3a5 5 0 0 0-7-7l-1 1|M14 10a5 5 0 0 0-7 0l-3 3a5 5 0 0 0 7 7l1-1',
  paperclip: 'm21.4 11.6-9.2 9.2a6 6 0 0 1-8.5-8.5L13 2.9a4 4 0 1 1 5.7 5.7L9.4 17.9a2 2 0 1 1-2.8-2.8l8.5-8.5',
  message: 'M21 11.5a8.4 8.4 0 0 1-9.5 8.4L3 21l1.1-3.5A8.4 8.4 0 1 1 21 11.5Z',
  edit: 'M12 20h9|M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4Z',
  trash: 'M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6',
  archive: 'RECT:2,3,20,5,1|M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8M10 12h4',
  pause: 'RECT:6,4,4,16,1|RECT:14,4,4,16,1',
  refresh: 'M3 12a9 9 0 0 1 15-6.7L21 8|M21 3v5h-5|M21 12a9 9 0 0 1-15 6.7L3 16|M3 21v-5h5',
  settings: 'CIRCLE:12,12,3|M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1A1.7 1.7 0 0 0 4.6 9a1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1Z',
  bolt: 'm13 2-9 12h7l-1 8 9-12h-7z',
  eye: 'M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z|CIRCLE:12,12,3',
  globe: 'CIRCLE:12,12,9|M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18',
  layers: 'm12 3 9 5-9 5-9-5 9-5Z|m3 13 9 5 9-5M3 18l9 5 9-5',
  list: 'M8 6h13M8 12h13M8 18h13|CIRCLE:3.5,6,0.5|CIRCLE:3.5,12,0.5|CIRCLE:3.5,18,0.5',
  grid: 'RECT:3,3,7,7,1|RECT:14,3,7,7,1|RECT:3,14,7,7,1|RECT:14,14,7,7,1',
  command: 'M18 3a3 3 0 1 0 0 6h-3V6a3 3 0 0 0-6 0v3H6a3 3 0 1 0 0 6h3v3a3 3 0 1 0 6 0v-3h3a3 3 0 1 0 0-6h-3V6a3 3 0 0 1 3-3z',
  trending: 'm3 17 6-6 4 4 8-8|M14 7h7v7',
  alert: 'M12 9v4M12 17h0|M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z',
  info: 'CIRCLE:12,12,9|M12 16v-5M12 8h0',
  pin: 'm9 4 6 6-2 6-3-3-5 5 5-5-3-3 6-2-4-4Z',
  hash: 'M5 9h14M5 15h14M10 3 8 21M16 3l-2 18',
  send: 'm22 2-7 20-4-9-9-4Z|m22 2-11 11',
  forward: 'm15 17 5-5-5-5|M4 18v-2a4 4 0 0 1 4-4h12',
  reply: 'm9 17-5-5 5-5|M20 18v-2a4 4 0 0 0-4-4H4',
};

const parts = computed(() => {
  const raw = ICONS[props.name];
  if (!raw) return [];
  return raw.split('|').map((p) => {
    if (p.startsWith('CIRCLE:')) {
      const [cx, cy, r] = p.slice(7).split(',');
      return { type: 'circle', cx, cy, r };
    }
    if (p.startsWith('RECT:')) {
      const [x, y, w, h, rx] = p.slice(5).split(',');
      return { type: 'rect', x, y, width: w, height: h, rx };
    }
    return { type: 'path', d: p };
  });
});
</script>

<template>
  <svg
    xmlns="http://www.w3.org/2000/svg"
    :width="dim"
    :height="dim"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    :stroke-width="stroke"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
    class="shrink-0 inline-block"
  >
    <template v-for="(p, i) in parts" :key="i">
      <path v-if="p.type === 'path'" :d="p.d" />
      <circle v-else-if="p.type === 'circle'" :cx="p.cx" :cy="p.cy" :r="p.r" />
      <rect v-else-if="p.type === 'rect'" :x="p.x" :y="p.y" :width="p.width" :height="p.height" :rx="p.rx" />
    </template>
  </svg>
</template>
