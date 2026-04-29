<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import InboxLayout from '@/Layouts/InboxLayout.vue';
import Card from '@/Components/inbox/Card.vue';
import Button from '@/Components/inbox/Button.vue';
import Badge from '@/Components/inbox/Badge.vue';
import SourceBadge from '@/Components/inbox/SourceBadge.vue';

const props = defineProps({ account: Object, recent_logs: Array });
defineOptions({ layout: InboxLayout });

const form = useForm({
  name: props.account.name,
  identifier: props.account.identifier,
  enabled: props.account.enabled,
  settings: props.account.settings || {},
  credentials: {},
});
const settingsRaw = ref(JSON.stringify(props.account.settings || {}, null, 2));
const credsRaw = ref('');

function save() {
  let settings, credentials;
  try { settings = settingsRaw.value ? JSON.parse(settingsRaw.value) : {}; }
  catch (e) { alert('Settings: invalid JSON'); return; }
  try { credentials = credsRaw.value ? JSON.parse(credsRaw.value) : null; }
  catch (e) { alert('Credentials: invalid JSON'); return; }

  const payload = { name: form.name, identifier: form.identifier, enabled: form.enabled, settings };
  if (credentials !== null) payload.credentials = credentials;
  router.patch(`/sources/${props.account.id}`, payload, { preserveScroll: true });
}
function sync() { router.post(`/sources/${props.account.id}/sync`); }
function toggle() { router.post(`/sources/${props.account.id}/toggle`); }
function destroy() { if (confirm('Delete?')) router.delete(`/sources/${props.account.id}`); }
</script>

<template>
  <Head :title="account.name" />
  <div class="page">
    <header class="page-header">
      <Link href="/sources" class="link-muted">← Sources</Link>
      <div class="actions">
        <Button @click="sync">Sync now</Button>
        <Button @click="toggle">{{ account.enabled ? 'Disable' : 'Enable' }}</Button>
        <Button variant="danger" @click="destroy">Delete</Button>
      </div>
    </header>

    <Card>
      <div class="head">
        <SourceBadge :type="account.type" />
        <h1>{{ account.name }}</h1>
        <Badge size="sm" :variant="account.enabled ? 'success' : 'neutral'">{{ account.enabled ? 'On' : 'Off' }}</Badge>
      </div>
      <p class="muted small">{{ account.messages_count }} messages · {{ account.tasks_count }} tasks</p>
    </Card>

    <Card>
      <h3>Configuration</h3>
      <div class="form">
        <label>Name<input v-model="form.name" class="ix-input" /></label>
        <label>Identifier<input v-model="form.identifier" class="ix-input" /></label>
        <label>Settings (JSON)<textarea v-model="settingsRaw" rows="8" class="ix-input mono" /></label>
        <label>Credentials (JSON, leave blank to keep existing)
          <textarea v-model="credsRaw" rows="6" class="ix-input mono" placeholder='{"api_token": "..."}' />
        </label>
        <div class="actions"><Button variant="primary" @click="save">Save</Button></div>
      </div>
    </Card>

    <Card>
      <h3>Recent syncs</h3>
      <table class="ix-table">
        <thead><tr><th>Started</th><th>Status</th><th>Imported</th><th>Created</th><th>Error</th></tr></thead>
        <tbody>
          <tr v-for="l in recent_logs" :key="l.id">
            <td>{{ l.started_at }}</td>
            <td><Badge size="sm" :variant="l.status === 'success' ? 'success' : (l.status === 'failed' ? 'urgent' : 'info')">{{ l.status }}</Badge></td>
            <td>{{ l.imported_count }}</td>
            <td>{{ l.created_task_count }}</td>
            <td class="err">{{ l.error_message || '—' }}</td>
          </tr>
          <tr v-if="recent_logs.length === 0"><td colspan="5" class="empty">No syncs yet.</td></tr>
        </tbody>
      </table>
    </Card>
  </div>
</template>

<style scoped>
.page { display: flex; flex-direction: column; gap: 16px; }
.page-header { display: flex; justify-content: space-between; align-items: center; }
.link-muted { color: var(--fg-muted); font-size: 13px; }
.actions { display: flex; gap: 8px; }
.head { display: flex; align-items: center; gap: 8px; }
.head h1 { font-size: 18px; font-weight: 600; margin: 0; }
.muted.small { color: var(--fg-muted); font-size: 12px; margin: 4px 0 0; }
.form { display: flex; flex-direction: column; gap: 10px; }
.form label { display: flex; flex-direction: column; gap: 4px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; color: var(--fg-subtle); }
.ix-input { padding: 8px 10px; border-radius: var(--r-md); border: 1px solid var(--border-strong); background: var(--bg-elev); font-size: 13px; color: var(--fg); font-family: inherit; }
.ix-input.mono { font-family: var(--font-mono); font-size: 12px; }
h3 { font-size: 13px; font-weight: 600; margin: 0 0 8px; }
.ix-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.ix-table th, .ix-table td { padding: 8px; text-align: left; border-bottom: 1px solid var(--border); }
.ix-table th { font-weight: 500; color: var(--fg-subtle); font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; }
.empty { color: var(--fg-subtle); text-align: center; padding: 16px; }
.err { color: var(--urgent-fg); font-size: 11px; }
</style>
