<script setup>
import { Head } from '@inertiajs/vue3';
import InboxLayout from '@/Layouts/InboxLayout.vue';
import Card from '@/Components/inbox/Card.vue';
defineProps({ logs: Object });
defineOptions({ layout: InboxLayout });
</script>

<template>
  <Head title="AI logs" />
  <div class="page">
    <h1>AI analysis logs</h1>
    <Card>
      <table class="ix-table">
        <thead><tr><th>When</th><th>Provider</th><th>Model</th><th>Task?</th><th>Conf.</th><th>Tokens</th><th>Error</th></tr></thead>
        <tbody>
          <tr v-for="l in logs.data" :key="l.id">
            <td>{{ l.created_at }}</td>
            <td>{{ l.provider }}</td>
            <td class="mono">{{ l.model }}</td>
            <td>{{ l.is_task ? 'yes' : 'no' }}</td>
            <td>{{ l.confidence != null ? Math.round(l.confidence*100)+'%' : '—' }}</td>
            <td>{{ l.tokens_used || '—' }}</td>
            <td class="err">{{ l.error_message || '' }}</td>
          </tr>
          <tr v-if="logs.data.length === 0"><td colspan="7" class="empty">No logs yet.</td></tr>
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
.mono { font-family: var(--font-mono); }
.err { color: var(--urgent-fg); font-size: 11px; }
.empty { text-align: center; color: var(--fg-subtle); padding: 16px; }
</style>
