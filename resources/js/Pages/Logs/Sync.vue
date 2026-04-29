<script setup>
import { Head } from '@inertiajs/vue3';
import InboxLayout from '@/Layouts/InboxLayout.vue';
import Card from '@/Components/inbox/Card.vue';
import Badge from '@/Components/inbox/Badge.vue';
import SourceBadge from '@/Components/inbox/SourceBadge.vue';
defineProps({ logs: Object });
defineOptions({ layout: InboxLayout });
</script>

<template>
  <Head title="Sync logs" />
  <div class="page">
    <h1>Sync logs</h1>
    <Card>
      <table class="ix-table">
        <thead><tr><th>Started</th><th>Source</th><th>Account</th><th>Status</th><th>Imported</th><th>Created</th><th>Error</th></tr></thead>
        <tbody>
          <tr v-for="l in logs.data" :key="l.id">
            <td>{{ l.started_at }}</td>
            <td><SourceBadge :type="l.source_type" /></td>
            <td>{{ l.source_account?.name || '—' }}</td>
            <td><Badge size="sm" :variant="l.status === 'success' ? 'success' : (l.status === 'failed' ? 'urgent' : 'info')">{{ l.status }}</Badge></td>
            <td>{{ l.imported_count }}</td>
            <td>{{ l.created_task_count }}</td>
            <td class="err">{{ l.error_message || '' }}</td>
          </tr>
          <tr v-if="logs.data.length === 0"><td colspan="7" class="empty">No syncs yet.</td></tr>
        </tbody>
      </table>
    </Card>
  </div>
</template>

<style scoped>
.page { display: flex; flex-direction: column; gap: 16px; }
h1 { font-size: 22px; font-weight: 600; margin: 0; }
.ix-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.ix-table th, .ix-table td { padding: 8px; text-align: left; border-bottom: 1px solid var(--border); }
.ix-table th { color: var(--fg-subtle); font-weight: 500; font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; }
.err { color: var(--urgent-fg); font-size: 11px; }
.empty { text-align: center; color: var(--fg-subtle); padding: 16px; }
</style>
