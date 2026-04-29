<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import InboxLayout from '@/Layouts/InboxLayout.vue';
import Card from '@/Components/inbox/Card.vue';
import Stat from '@/Components/inbox/Stat.vue';
import Badge from '@/Components/inbox/Badge.vue';
import PriorityBadge from '@/Components/inbox/PriorityBadge.vue';
import SourceBadge from '@/Components/inbox/SourceBadge.vue';
import SourceGlyph from '@/Components/inbox/SourceGlyph.vue';
import Confidence from '@/Components/inbox/Confidence.vue';
import Icon from '@/Components/inbox/Icon.vue';
import Button from '@/Components/inbox/Button.vue';

defineOptions({ layout: InboxLayout });

const props = defineProps({
  stats: Object,
  recent_ai_tasks: Array,
  needs_review: Array,
  today: Array,
  by_source: Array,
  failed_syncs: Array,
  sources_count: Number,
  ai_logs_count: Number,
});

const user = computed(() => usePage().props.auth?.user);
const greeting = computed(() => {
  const h = new Date().getHours();
  return h < 5 ? 'Up late' : h < 12 ? 'Good morning' : h < 18 ? 'Good afternoon' : 'Good evening';
});

function fmtDate(d) {
  if (!d) return null;
  const dt = new Date(d);
  return dt.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
}
</script>

<template>
  <Head title="Dashboard" />
  <div class="px-8 py-7 flex flex-col gap-6 max-w-[1200px]">
    <header class="flex items-end justify-between gap-4 flex-wrap">
      <div>
        <h1 class="text-[20px] font-semibold tracking-tight3">{{ greeting }}, {{ user?.name?.split(' ')[0] }}.</h1>
        <p class="text-[13px] text-fg-muted mt-1">
          Here&rsquo;s what your AI assistant pulled out of your inboxes today.
        </p>
      </div>
      <div class="flex gap-2">
        <Link href="/sources"><Button variant="secondary" size="sm" icon="layers">Sources</Button></Link>
        <Link href="/tasks/create"><Button variant="primary" size="sm" icon="plus">New task</Button></Link>
      </div>
    </header>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
      <Stat label="Open tasks" :value="stats.open_tasks" icon="inbox" accent
            :delta="stats.urgent + ' urgent'" delta-tone="down" />
      <Stat label="Needs review" :value="stats.needs_review" icon="sparkle"
            sub="AI flagged for your judgment" />
      <Stat label="Closed this week" :value="stats.closed_this_week" icon="check"
            delta-tone="up" />
      <Stat label="Connected sources" :value="sources_count" icon="layers"
            :sub="ai_logs_count + ' AI calls logged'" />
    </div>

    <!-- AI-detected + by source -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
      <Card title="AI-detected today" class="xl:col-span-2">
        <template #action>
          <Link href="/tasks?needs_review=1" class="text-[12px] text-accent-fg font-medium hover:underline">
            View all
          </Link>
        </template>
        <ul v-if="recent_ai_tasks.length" class="flex flex-col">
          <li
            v-for="t in recent_ai_tasks" :key="t.id"
            class="flex items-start gap-3 py-3 border-b border-border last:border-0"
          >
            <span class="mt-0.5 text-accent shrink-0"><Icon name="sparkle" :size="14" /></span>
            <div class="flex-1 min-w-0">
              <Link :href="'/tasks/' + t.id" class="block text-[13px] font-[550] text-fg hover:text-accent-fg truncate">
                {{ t.title }}
              </Link>
              <p v-if="t.ai_summary" class="text-[12px] text-fg-muted mt-0.5 line-clamp-2 leading-snug">
                {{ t.ai_summary }}
              </p>
              <div class="flex items-center gap-2 mt-1.5">
                <SourceBadge :source="t.source_type" />
                <PriorityBadge :level="t.priority" />
                <Confidence v-if="t.ai_confidence" :value="Number(t.ai_confidence)" />
              </div>
            </div>
          </li>
        </ul>
        <div v-else class="text-[12.5px] text-fg-subtle py-6 text-center">
          No AI-detected tasks yet. Connect a source to get started.
        </div>
      </Card>

      <Card title="By source">
        <template #action>
          <Link href="/sources" class="text-[12px] text-accent-fg font-medium hover:underline">Manage</Link>
        </template>
        <ul v-if="by_source.length" class="flex flex-col gap-2.5">
          <li v-for="row in by_source" :key="row.source_type"
              class="flex items-center justify-between py-1">
            <span class="flex items-center gap-2 text-[12.5px] text-fg">
              <SourceGlyph :source="row.source_type" :size="16" />
              <span class="capitalize">{{ row.source_type }}</span>
            </span>
            <span class="font-mono text-[12px] tabular-nums text-fg-subtle">{{ row.count }}</span>
          </li>
        </ul>
        <div v-else class="text-[12.5px] text-fg-subtle py-4 text-center">
          No tasks from sources yet.
        </div>
      </Card>
    </div>

    <!-- Needs review + Today -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
      <Card title="Needs your review" accent>
        <template #action>
          <Badge variant="review" size="xs" icon="sparkle">{{ stats.needs_review }} flagged</Badge>
        </template>
        <ul v-if="needs_review.length" class="flex flex-col">
          <li v-for="t in needs_review" :key="t.id"
              class="flex items-center gap-3 py-3 border-b border-border last:border-0">
            <Link :href="'/tasks/' + t.id" class="flex-1 min-w-0 text-[13px] font-[550] text-fg hover:text-accent-fg truncate">
              {{ t.title }}
            </Link>
            <SourceBadge :source="t.source_type" />
            <PriorityBadge :level="t.priority" />
          </li>
        </ul>
        <div v-else class="text-[12.5px] text-fg-subtle py-6 text-center">
          Nothing waiting for review &mdash; you&rsquo;re all caught up.
        </div>
      </Card>

      <Card title="Due today">
        <template #action>
          <span class="text-[11.5px] text-fg-subtle">{{ today.length }} item{{ today.length === 1 ? '' : 's' }}</span>
        </template>
        <ul v-if="today.length" class="flex flex-col">
          <li v-for="t in today" :key="t.id"
              class="flex items-center gap-3 py-3 border-b border-border last:border-0">
            <Icon name="calendar" :size="14" class="text-fg-subtle shrink-0" />
            <Link :href="'/tasks/' + t.id" class="flex-1 min-w-0 text-[13px] font-[550] text-fg hover:text-accent-fg truncate">
              {{ t.title }}
            </Link>
            <PriorityBadge :level="t.priority" />
          </li>
        </ul>
        <div v-else class="text-[12.5px] text-fg-subtle py-6 text-center">
          Nothing scheduled for today.
        </div>
      </Card>
    </div>

    <Card v-if="failed_syncs.length" title="Sync issues">
      <template #action>
        <Link href="/logs/sync" class="text-[12px] text-accent-fg font-medium hover:underline">View logs</Link>
      </template>
      <ul class="flex flex-col">
        <li v-for="log in failed_syncs" :key="log.id"
            class="flex items-center gap-3 py-2.5 border-b border-border last:border-0">
          <Badge variant="urgent" size="xs" icon="alert">Failed</Badge>
          <span class="text-[12.5px] text-fg flex-1 truncate">{{ log.error_message || 'Sync error' }}</span>
          <span class="text-[11px] text-fg-subtle font-mono">{{ fmtDate(log.created_at) }}</span>
        </li>
      </ul>
    </Card>
  </div>
</template>
