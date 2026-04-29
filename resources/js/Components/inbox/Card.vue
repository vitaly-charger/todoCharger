<template>
  <div class="ix-card"><slot /></div>
</template>

<style scoped>
.ix-card {
  background: var(--bg-elev);
  border: 1px solid var(--border);
  border-radius: var(--r-lg);
  padding: var(--pad-card);
  box-shadow: var(--sh-1);
}
</style>
