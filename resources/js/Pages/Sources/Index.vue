<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import InboxLayout from '@/Layouts/InboxLayout.vue';
import Card from '@/Components/inbox/Card.vue';
import Button from '@/Components/inbox/Button.vue';
import Badge from '@/Components/inbox/Badge.vue';
import SourceBadge from '@/Components/inbox/SourceBadge.vue';

const props = defineProps({ accounts: Array, types: Array });
defineOptions({ layout: InboxLayout });

const showCreate = ref(false);
const form = useForm({ type: 'gmail', name: '', identifier: '', enabled: true, settings: {}, credentials: {} });

function create() {
  form.post('/sources', { onSuccess: () => { showCreate.value = false; form.reset(); } });
}
function sync(id) { router.post(`/sources/${id}/sync`); }
function toggle(id) { router.post(`/sources/${id}/toggle`); }
function destroy(id) { if (confirm('Remove this source?')) router.delete(`/sources/${id}`); }
</script>

<template>
  <Head title="Sources" />
  <div class="page">
    <header class="page-header">
      <div>
        <h1>Sources</h1>
        <p class="muted">Connected inboxes the AI scans for tasks.</p>
      </div>
      <Button variant="primary" @click="showCreate = !showCreate">+ Add source</Button>
    </header>

    <Card v-if="showCreate">
      <form @submit.prevent="create" class="form">
        <div class="row">
          <label>Type
            <select v-model="form.type" class="ix-input">
              <option v-for="t in types" :key="t" :value="t">{{ t }}</option>
            </select>
          </label>
          <label>Name<input v-model="form.name" class="ix-input" required /></label>
          <label>Identifier<input v-model="form.identifier" class="ix-input" placeholder="e.g. user@gmail.com / #channel" /></label>
        </div>
        <p class="muted small">Credentials and per-source settings can be configured after creation.</p>
        <div class="actions">
          <Button @click="showCreate = false" type="button">Cancel</Button>
          <Button variant="primary" type="submit" :disabled="form.processing">Create</Button>
        </div>
      </form>
    </Card>

    <Card>
      <div v-if="accounts.length === 0" class="empty">No sources yet. Add one to start ingesting.</div>
      <ul v-else class="src-list">
        <li v-for="a in accounts" :key="a.id">
          <Link :href="`/sources/${a.id}`" class="src-row">
            <SourceBadge :type="a.type" />
            <div class="src-main">
              <div class="src-name">{{ a.name }}</div>
              <div class="muted small">{{ a.identifier || '—' }} · {{ a.messages_count }} msgs · {{ a.tasks_count }} tasks</div>
            </div>
            <Badge size="sm" :variant="a.enabled ? 'success' : 'neutral'">{{ a.enabled ? 'On' : 'Off' }}</Badge>
            <Badge v-if="a.last_sync_status === 'failed'" variant="urgent" size="sm">Failed</Badge>
          </Link>
          <div class="src-actions">
            <Button size="sm" @click.stop="sync(a.id)">Sync</Button>
            <Button size="sm" @click.stop="toggle(a.id)">{{ a.enabled ? 'Disable' : 'Enable' }}</Button>
            <Button size="sm" variant="danger" @click.stop="destroy(a.id)">Delete</Button>
          </div>
        </li>
      </ul>
    </Card>
  </div>
</template>

<style scoped>
.page { display: flex; flex-direction: column; gap: 16px; }
.page-header { display: flex; align-items: end; justify-content: space-between; }
h1 { font-size: 22px; font-weight: 600; margin: 0; }
.muted { color: var(--fg-muted); font-size: 13px; }
.muted.small { font-size: 12px; }
.form { display: flex; flex-direction: column; gap: 12px; }
.row { display: flex; gap: 8px; flex-wrap: wrap; }
.row label { display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 160px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; color: var(--fg-subtle); }
.ix-input { padding: 7px 10px; border-radius: var(--r-md); border: 1px solid var(--border-strong); background: var(--bg-elev); font-size: 13px; color: var(--fg); }
.actions { display: flex; gap: 8px; justify-content: flex-end; }
.src-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 4px; }
.src-list li { display: flex; align-items: center; gap: 12px; padding: 8px; border-radius: var(--r-md); }
.src-list li:hover { background: var(--bg-hover); }
.src-row { display: flex; align-items: center; gap: 12px; flex: 1; }
.src-main { flex: 1; }
.src-name { font-weight: 500; font-size: 14px; }
.src-actions { display: flex; gap: 6px; }
.empty { color: var(--fg-subtle); padding: 32px; text-align: center; }
</style>
