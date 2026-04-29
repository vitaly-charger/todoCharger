<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const flash = computed(() => page.props.flash || {});

const nav = [
  { label: 'Dashboard', href: '/dashboard', name: 'dashboard' },
  { label: 'Tasks', href: '/tasks', name: 'tasks.index' },
  { label: 'Sources', href: '/sources', name: 'sources.index' },
  { label: 'AI Logs', href: '/logs/ai', name: 'logs.ai' },
  { label: 'Sync Logs', href: '/logs/sync', name: 'logs.sync' },
  { label: 'Settings', href: '/settings', name: 'settings' },
];

function isActive(href) {
  return page.url === href || page.url.startsWith(href + '/');
}
function logout() { router.post('/logout'); }
</script>

<template>
  <div class="app accent-amber">
    <div class="ix-shell">
      <aside class="ix-sidebar">
        <div class="ix-brand">
          <span class="ix-dot" />
          <span>Inbox</span>
        </div>
        <nav class="ix-nav">
          <Link v-for="item in nav" :key="item.href" :href="item.href"
            class="ix-nav-link" :class="{ active: isActive(item.href) }">
            {{ item.label }}
          </Link>
        </nav>
        <div class="ix-user">
          <div class="ix-user-name">{{ user?.name }}</div>
          <div class="ix-user-email">{{ user?.email }}</div>
          <button class="ix-logout" @click="logout">Sign out</button>
        </div>
      </aside>
      <main class="ix-main">
        <div v-if="flash.status" class="ix-flash">{{ flash.status }}</div>
        <slot />
      </main>
    </div>
  </div>
</template>

<style scoped>
.ix-shell { display: grid; grid-template-columns: 240px 1fr; min-height: 100vh; }
.ix-sidebar {
  background: var(--bg-sunken);
  border-right: 1px solid var(--border);
  padding: 18px 14px;
  display: flex; flex-direction: column; gap: 18px;
}
.ix-brand { display: flex; align-items: center; gap: 8px; font-weight: 600; padding: 4px 8px; }
.ix-dot { width: 10px; height: 10px; border-radius: 999px; background: var(--accent); display: inline-block; }
.ix-nav { display: flex; flex-direction: column; gap: 2px; }
.ix-nav-link {
  padding: 7px 10px; border-radius: var(--r-sm);
  color: var(--fg-muted); font-size: 13px; font-weight: 500;
}
.ix-nav-link:hover { background: var(--bg-hover); color: var(--fg); }
.ix-nav-link.active { background: var(--accent-soft); color: var(--accent-fg); }
.ix-user { margin-top: auto; padding: 10px; border-top: 1px solid var(--border); }
.ix-user-name { font-size: 13px; font-weight: 500; }
.ix-user-email { font-size: 11px; color: var(--fg-subtle); }
.ix-logout { margin-top: 8px; font-size: 12px; color: var(--fg-muted); background: none; border: 0; cursor: pointer; padding: 0; }
.ix-logout:hover { color: var(--fg); }
.ix-main { padding: 24px 32px; max-width: 1400px; }
.ix-flash { background: var(--success-soft); color: var(--success-fg); padding: 8px 12px; border-radius: var(--r-md); margin-bottom: 12px; font-size: 13px; }
</style>
