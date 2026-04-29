<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
const page = usePage();
const flashError = computed(() => page.props.flash?.error);
</script>

<template>
  <Head title="Sign in" />
  <div class="app accent-amber login-shell">
    <div class="login-card">
      <div class="brand">
        <span class="dot" />
        <span>AI Inbox To-Do</span>
      </div>
      <h1>Sign in</h1>
      <p class="muted">Restricted access — Google account only.</p>
      <a href="/auth/google/redirect" class="google-btn">
        <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true">
          <path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.7 32.9 29.3 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3 0 5.7 1.1 7.8 2.9l5.7-5.7C33.6 6.1 29.1 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.2-.1-2.4-.4-3.5z"/>
          <path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.8 16.1 19 13 24 13c3 0 5.7 1.1 7.8 2.9l5.7-5.7C33.6 6.1 29.1 4 24 4 16 4 9.2 8.6 6.3 14.7z"/>
          <path fill="#4CAF50" d="M24 44c5 0 9.6-1.9 13.1-5.1l-6-5C29 35.7 26.6 36.5 24 36.5c-5.3 0-9.7-3.1-11.3-7.5l-6.6 5.1C9 39.3 16 44 24 44z"/>
          <path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.7 2-2 3.7-3.6 4.9l6 5C41.5 35 44 30 44 24c0-1.2-.1-2.4-.4-3.5z"/>
        </svg>
        <span>Continue with Google</span>
      </a>
      <p v-if="flashError" class="error">{{ flashError }}</p>
    </div>
  </div>
</template>

<style scoped>
.login-shell { min-height: 100vh; display: grid; place-items: center; padding: 24px; }
.login-card { background: var(--bg-elev); border: 1px solid var(--border); border-radius: var(--r-xl); padding: 32px; max-width: 380px; width: 100%; box-shadow: var(--sh-2); }
.brand { display: flex; align-items: center; gap: 8px; font-weight: 600; margin-bottom: 24px; }
.dot { width: 10px; height: 10px; border-radius: 999px; background: var(--accent); }
h1 { font-size: 22px; font-weight: 600; margin: 0 0 6px; }
.muted { color: var(--fg-muted); font-size: 13px; margin: 0 0 24px; }
.google-btn {
  display: flex; align-items: center; justify-content: center; gap: 10px;
  width: 100%; padding: 12px 16px; border-radius: var(--r-md);
  background: var(--bg-elev); border: 1px solid var(--border-strong);
  font-weight: 500; font-size: 14px; color: var(--fg);
  transition: background 120ms;
}
.google-btn:hover { background: var(--bg-hover); }
.error { color: var(--urgent-fg); background: var(--urgent-soft); padding: 8px 12px; border-radius: var(--r-md); margin-top: 16px; font-size: 13px; }
</style>
