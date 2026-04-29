<script setup>
const props = defineProps({
  variant: { type: String, default: 'secondary' }, // primary|secondary|ghost|danger
  size: { type: String, default: 'md' }, // sm|md|lg
  type: { type: String, default: 'button' },
  as: { type: String, default: 'button' },
});
</script>

<template>
  <component :is="as" :type="as === 'button' ? type : undefined" class="ix-btn" :class="[variant, size]">
    <slot />
  </component>
</template>

<style scoped>
.ix-btn {
  display: inline-flex; align-items: center; justify-content: center; gap: 6px;
  border-radius: var(--r-md);
  border: 1px solid var(--border-strong);
  background: var(--bg-elev);
  color: var(--fg);
  padding: 6px 12px;
  font-size: 13px; font-weight: 500;
  line-height: 1; cursor: pointer;
  transition: background 120ms, border-color 120ms, transform 120ms;
}
.ix-btn:hover { background: var(--bg-hover); }
.ix-btn:active { transform: translateY(1px); }
.ix-btn.sm { padding: 4px 8px; font-size: 12px; }
.ix-btn.lg { padding: 10px 16px; font-size: 14px; }
.ix-btn.primary { background: var(--accent); color: var(--accent-on); border-color: transparent; }
.ix-btn.primary:hover { background: var(--accent-hover); }
.ix-btn.ghost { background: transparent; border-color: transparent; color: var(--fg-muted); }
.ix-btn.ghost:hover { background: var(--bg-hover); color: var(--fg); }
.ix-btn.danger { background: var(--urgent); color: #fff; border-color: transparent; }
</style>
