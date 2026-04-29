<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';
import InboxLayout from '@/Layouts/InboxLayout.vue';
import Card from '@/Components/inbox/Card.vue';
import Button from '@/Components/inbox/Button.vue';
import SourceBadge from '@/Components/inbox/SourceBadge.vue';
import PriorityBadge from '@/Components/inbox/PriorityBadge.vue';
import Badge from '@/Components/inbox/Badge.vue';

const props = defineProps({
  tasks: Object,
  filters: Object,
  sourceAccounts: Array,
});
defineOptions({ layout: InboxLayout });

const filters = reactive({ ...props.filters });

let timer = null;
watch(filters, (val) => {
  clearTimeout(timer);
  timer = setTimeout(() => {
    router.get('/tasks', val, { preserveState: true, replace: true });
  }, 250);
}, { deep: true });

const STATUSES = ['inbox', 'todo', 'in_progress', 'waiting', 'done', 'ignored'];
const PRIORITIES = ['urgent', 'high', 'normal', 'low'];
const SOURCE_TYPES = ['manual', 'gmail', 'slack', 'telegram', 'monday', 'wrike'];
</script>

<template>
  <Head title="Tasks" />
  <div class="page">
    <header class="page-header">
      <div>
        <h1>Tasks</h1>
        <p class="muted">{{ tasks.total }} total</p>
      </div>
      <Link href="/tasks/create"><Button variant="primary">+ New task</Button></Link>
    </header>

    <Card>
      <div class="filters">
        <input v-model="filters.search" placeholder="Search title or description…" class="ix-input flex-1" />
        <select v-model="filters.status" class="ix-input">
          <option value="">All statuses</option>
          <option v-for="s in STATUSES" :key="s" :value="s">{{ s }}</option>
        </select>
        <select v-model="filters.priority" class="ix-input">
          <option value="">Any priority</option>
          <option v-for="p in PRIORITIES" :key="p" :value="p">{{ p }}</option>
        </select>
        <select v-model="filters.source_type" class="ix-input">
          <option value="">Any source</option>
          <option v-for="s in SOURCE_TYPES" :key="s" :value="s">{{ s }}</option>
        </select>
      </div>
    </Card>

    <Card>
      <div v-if="tasks.data.length === 0" class="empty">No tasks match.</div>
      <table v-else class="ix-table">
        <thead>
          <tr><th>Status</th><th>Priority</th><th>Title</th><th>Source</th><th>Due</th></tr>
        </thead>
        <tbody>
          <tr v-for="t in tasks.data" :key="t.id" @click="router.visit(`/tasks/${t.id}`)">
            <td><Badge size="sm">{{ t.status }}</Badge></td>
            <td><PriorityBadge :priority="t.priority" /></td>
            <td class="title">{{ t.title }}</td>
            <td><SourceBadge :type="t.source_type" /></td>
            <td>{{ t.due_date || '—' }}</td>
          </tr>
        </tbody>
      </table>

      <nav v-if="tasks.last_page > 1" class="pagination">
        <Link v-for="link in tasks.links" :key="link.label" :href="link.url || '#'"
          class="page-link" :class="{ active: link.active, disabled: !link.url }"
          v-html="link.label" />
      </nav>
    </Card>
  </div>
</template>

<style scoped>
.page { display: flex; flex-direction: column; gap: 16px; }
.page-header { display: flex; align-items: end; justify-content: space-between; }
.page-header h1 { font-size: 22px; margin: 0; font-weight: 600; }
.muted { color: var(--fg-muted); font-size: 13px; margin: 4px 0 0; }
.filters { display: flex; gap: 8px; flex-wrap: wrap; }
.flex-1 { flex: 1; min-width: 200px; }
.ix-input { padding: 7px 10px; border-radius: var(--r-md); border: 1px solid var(--border-strong); background: var(--bg-elev); font-size: 13px; color: var(--fg); }
.ix-input:focus { outline: none; box-shadow: var(--sh-focus); }
.ix-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.ix-table thead th { text-align: left; padding: 8px; color: var(--fg-subtle); font-weight: 500; font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; border-bottom: 1px solid var(--border); }
.ix-table tbody tr { cursor: pointer; }
.ix-table tbody tr:hover { background: var(--bg-hover); }
.ix-table td { padding: 10px 8px; border-bottom: 1px solid var(--border); }
.ix-table td.title { font-weight: 500; }
.empty { color: var(--fg-subtle); padding: 24px; text-align: center; }
.pagination { display: flex; gap: 4px; margin-top: 12px; flex-wrap: wrap; }
.page-link { padding: 6px 10px; border-radius: var(--r-sm); font-size: 12px; color: var(--fg-muted); border: 1px solid var(--border); }
.page-link.active { background: var(--accent-soft); color: var(--accent-fg); border-color: transparent; }
.page-link.disabled { opacity: 0.4; pointer-events: none; }
</style>
