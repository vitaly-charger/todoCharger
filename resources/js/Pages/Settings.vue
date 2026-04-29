<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import InboxLayout from '@/Layouts/InboxLayout.vue';
import Card from '@/Components/inbox/Card.vue';
import { computed } from 'vue';
const user = computed(() => usePage().props.auth.user);
defineOptions({ layout: InboxLayout });
</script>

<template>
  <Head title="Settings" />
  <div class="page">
    <h1>Settings</h1>

    <Card>
      <h3>Account</h3>
      <dl>
        <dt>Name</dt><dd>{{ user.name }}</dd>
        <dt>Email</dt><dd>{{ user.email }}</dd>
      </dl>
    </Card>

    <Card>
      <h3>AI provider</h3>
      <p class="muted small">Configured via server environment (<code>.env</code>): <code>AI_PROVIDER</code>, <code>ANTHROPIC_API_KEY</code> / <code>OPENAI_API_KEY</code>.</p>
      <p class="muted small">Minimum confidence to auto-create tasks: <code>AI_MIN_CONFIDENCE</code> (default 0.6).</p>
    </Card>

    <Card>
      <h3>Sync intervals (minutes)</h3>
      <p class="muted small">Set per-source via env: <code>SYNC_GMAIL_MINUTES</code>, <code>SYNC_SLACK_MINUTES</code>, <code>SYNC_MONDAY_MINUTES</code>, <code>SYNC_WRIKE_MINUTES</code>, <code>SYNC_TELEGRAM_MINUTES</code>.</p>
    </Card>
  </div>
</template>

<style scoped>
.page { display: flex; flex-direction: column; gap: 16px; max-width: 720px; }
h1 { font-size: 22px; font-weight: 600; margin: 0; }
h3 { font-size: 13px; font-weight: 600; margin: 0 0 8px; }
.muted.small { color: var(--fg-muted); font-size: 12px; }
dl { display: grid; grid-template-columns: max-content 1fr; gap: 4px 16px; font-size: 13px; margin: 0; }
dt { color: var(--fg-subtle); font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; }
dd { margin: 0; }
code { font-family: var(--font-mono); font-size: 11px; background: var(--bg-sunken); padding: 1px 5px; border-radius: 4px; }
</style>
