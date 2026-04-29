<script setup>
import { Head, Link } from '@inertiajs/vue3';
import InboxLayout from '@/Layouts/InboxLayout.vue';
import Card from '@/Components/inbox/Card.vue';
import Badge from '@/Components/inbox/Badge.vue';
import SourceBadge from '@/Components/inbox/SourceBadge.vue';
import PriorityBadge from '@/Components/inbox/PriorityBadge.vue';

defineProps({
  stats: Object,
  recent_ai_tasks: Array,
  needs_review: Array,
  today: Array,
  by_source: Array,
  failed_syncs: Array,
  sources_count: Number,
  ai_logs_count: Number,
});

defineOptions({ layout: InboxLayout });
</script>

<template>
  <Head title="Dashboard" />
  <div class="page">
    <header class="page-header">
      <h1>Dashboard</h1>
      <p class="muted">Your inbox at a glance.</p>
    </header>

    <div class="stat-grid">
      <Card>
        <div class="stat-label">Open tasks</div>
        <div class="stat-value">{{ stats.open_tasks }}</div>
      </Card>
      <Card>
        <div class="stat-label">Urgent</div>
        <div class="stat-value">{{ stats.urgent }}</div>
      </Card>
      <Card>
        <div class="stat-label">Needs review</div>
        <div class="stat-value">{{ stats.needs_review }}</div>
      </Card>
      <Card>
        <div class="stat-label">Closed this week</div>
        <div class="stat-value">{{ stats.closed_this_week }}</div>
      </Card>
    </div>

    <div class="grid-2">
      <Card>
        <div class="section-header">
          <h2>Today</h2>
          <Link href="/tasks" class="link-muted">All tasks →</Link>
        </div>
        <div v-if="today.length === 0" class="empty">Nothing due today.</div>
        <ul v-else class="task-list">
          <li v-for="t in today" :key="t.id">
            <Link :href="`/tasks/${t.id}`" class="task-row">
              <PriorityBadge :priority="t.priority" />
              <span class="task-title">{{ t.title }}</span>
              <SourceBadge :type="t.source_type" />
            </Link>
          </li>
        </ul>
      </Card>

      <Card>
        <div class="section-header"><h2>Needs review</h2></div>
        <div v-if="needs_review.length === 0" class="empty">All clear.</div>
        <ul v-else class="task-list">
          <li v-for="t in needs_review" :key="t.id">
            <Link :href="`/tasks/${t.id}`" class="task-row">
              <Badge variant="review" size="sm">Review</Badge>
              <span class="task-title">{{ t.title }}</span>
              <SourceBadge :type="t.source_type" />
            </Link>
          </li>
        </ul>
      </Card>
    </div>

    <div class="grid-2">
      <Card>
        <div class="section-header"><h2>Recent AI-created tasks</h2></div>
        <div v-if="recent_ai_tasks.length === 0" class="empty">No AI tasks yet.</div>
        <ul v-else class="task-list">
          <li v-for="t in recent_ai_tasks" :key="t.id">
            <Link :href="`/tasks/${t.id}`" class="task-row">
              <SourceBadge :type="t.source_type" />
              <span class="task-title">{{ t.title }}</span>
              <span v-if="t.ai_confidence != null" class="muted small">{{ Math.round(t.ai_confidence * 100) }}%</span>
            </Link>
          </li>
        </ul>
      </Card>

      <Card>
        <div class="section-header"><h2>Open by source</h2></div>
        <ul class="task-list">
          <li v-for="row in by_source" :key="row.source_type" class="src-row">
            <SourceBadge :type="row.source_type" />
            <span class="muted">{{ row.count }} open</span>
          </li>
          <li v-if="by_source.length === 0" class="empty">No open tasks.</li>
        </ul>
      </Card>
    </div>

    <Card v-if="failed_syncs.length">
      <div class="section-header"><h2>Failed syncs</h2></div>
      <ul class="task-list">
        <li v-for="log in failed_syncs" :key="log.id" class="task-row">
          <Badge variant="urgent" size="sm">Failed</Badge>
          <span class="task-title">{{ log.source_type }}</span>
          <span class="muted small">{{ log.error_message }}</span>
        </li>
      </ul>
    </Card>
  </div>
</template>

<style scoped>
.page { display: flex; flex-direction: column; gap: 20px; }
.page-header h1 { font-size: 22px; font-weight: 600; margin: 0; }
.muted { color: var(--fg-muted); }
.muted.small { font-size: 12px; }
.page-header p { color: var(--fg-muted); margin: 4px 0 0; }
.stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; }
.stat-label { font-size: 12px; color: var(--fg-subtle); text-transform: uppercase; letter-spacing: 0.06em; }
.stat-value { font-size: 28px; font-weight: 600; margin-top: 4px; }
.grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 12px; }
.section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.section-header h2 { font-size: 14px; font-weight: 600; margin: 0; }
.link-muted { color: var(--fg-muted); font-size: 12px; }
.link-muted:hover { color: var(--fg); }
.task-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 4px; }
.task-row { display: flex; align-items: center; gap: 8px; padding: 8px; border-radius: var(--r-sm); }
.task-row:hover { background: var(--bg-hover); }
.task-title { flex: 1; font-size: 13px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.src-row { display: flex; align-items: center; gap: 8px; padding: 6px 0; }
.empty { color: var(--fg-subtle); font-size: 13px; padding: 8px; }
</style>
