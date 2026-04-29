<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import InboxLayout from '@/Layouts/InboxLayout.vue';
import Button from '@/Components/inbox/Button.vue';
import Badge from '@/Components/inbox/Badge.vue';
import PriorityBadge from '@/Components/inbox/PriorityBadge.vue';
import SourceBadge from '@/Components/inbox/SourceBadge.vue';
import Icon from '@/Components/inbox/Icon.vue';
import Avatar from '@/Components/inbox/Avatar.vue';
import KBD from '@/Components/inbox/KBD.vue';

defineOptions({ layout: InboxLayout });

const props = defineProps({
  tasks: Object,
  filters: Object,
  sourceAccounts: Array,
});

const STATUSES = [
  { key: '',            label: 'All' },
  { key: 'inbox',       label: 'Inbox' },
  { key: 'todo',        label: 'To Do' },
  { key: 'in_progress', label: 'In Progress' },
  { key: 'waiting',     label: 'Waiting' },
  { key: 'done',        label: 'Done' },
  { key: 'ignored',     label: 'Ignored' },
];

const PRIORITIES = ['', 'urgent', 'high', 'normal', 'low'];
const SOURCE_TYPES = ['', 'gmail', 'slack', 'telegram', 'monday', 'wrike', 'manual'];

const search = ref(props.filters.search || '');
let t;
watch(search, (v) => {
  clearTimeout(t);
  t = setTimeout(() => {
    router.get('/tasks', { ...props.filters, search: v || undefined }, { preserveState: true, replace: true });
  }, 250);
});

function applyFilter(key, value) {
  router.get('/tasks', { ...props.filters, [key]: value || undefined }, { preserveState: true, replace: true });
}

function clearFilters() {
  router.get('/tasks', {}, { preserveState: false });
}

const activeStatus = computed(() => props.filters.status || '');
const filterCount = computed(() => Object.values(props.filters).filter(Boolean).length);

function fmtDue(d) {
  if (!d) return null;
  const dt = new Date(d);
  const today = new Date(); today.setHours(0,0,0,0);
  const target = new Date(dt); target.setHours(0,0,0,0);
  const diff = Math.round((target - today) / 86400000);
  if (diff === 0) return 'Today';
  if (diff === 1) return 'Tomorrow';
  if (diff === -1) return 'Yesterday';
  if (diff > 1 && diff < 7) return dt.toLocaleDateString(undefined, { weekday: 'short' });
  return dt.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
}
function dueClass(d) {
  if (!d) return 'text-fg-subtle';
  const diff = Math.round((new Date(d) - new Date()) / 86400000);
  if (diff < 0) return 'text-urgent-fg font-medium';
  if (diff <= 1) return 'text-warn-fg font-medium';
  return 'text-fg-muted';
}
</script>

<template>
  <Head title="Tasks" />
  <div class="flex flex-col h-full">
    <!-- Header -->
    <div class="px-8 pt-6 pb-3 flex items-end justify-between gap-4 border-b border-border">
      <div>
        <h1 class="text-[18px] font-semibold tracking-tight3 capitalize">
          {{ activeStatus ? STATUSES.find(s => s.key === activeStatus)?.label : 'All tasks' }}
        </h1>
        <p class="text-[12.5px] text-fg-muted mt-1">
          {{ tasks.total }} task{{ tasks.total === 1 ? '' : 's' }}
          <span v-if="filterCount"> &middot; {{ filterCount }} filter{{ filterCount > 1 ? 's' : '' }}</span>
        </p>
      </div>
      <div class="flex items-center gap-2">
        <div class="relative">
          <Icon name="search" :size="13" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-fg-subtle pointer-events-none" />
          <input
            v-model="search" placeholder="Search tasks…"
            class="h-8 w-[240px] pl-8 pr-2 text-[12.5px] bg-bg-elev border border-border rounded-md text-fg placeholder:text-fg-subtle focus:outline-none focus:border-border-focus"
          />
        </div>
        <Link href="/tasks/create"><Button variant="primary" size="sm" icon="plus">New</Button></Link>
      </div>
    </div>

    <!-- Status tabs -->
    <div class="px-8 flex items-center gap-1 border-b border-border overflow-x-auto">
      <button
        v-for="s in STATUSES" :key="s.key || 'all'"
        @click="applyFilter('status', s.key)"
        class="relative h-9 px-2.5 text-[12.5px] font-medium tracking-[-0.005em] whitespace-nowrap transition-colors"
        :class="activeStatus === s.key ? 'text-fg' : 'text-fg-muted hover:text-fg'"
      >
        {{ s.label }}
        <span v-if="activeStatus === s.key" class="absolute left-1.5 right-1.5 -bottom-px h-[2px] bg-accent rounded-full" />
      </button>
    </div>

    <!-- Filter chip row -->
    <div class="px-8 py-3 flex items-center gap-2 flex-wrap border-b border-border">
      <select
        :value="filters.priority || ''" @change="(e) => applyFilter('priority', e.target.value)"
        class="h-7 pl-2 pr-7 text-[12px] bg-bg-elev border border-border rounded-md text-fg-muted focus:border-border-focus capitalize min-w-[120px]"
      >
        <option value="">Any priority</option>
        <option v-for="p in PRIORITIES.filter(Boolean)" :key="p" :value="p">{{ p }}</option>
      </select>
      <select
        :value="filters.source_type || ''" @change="(e) => applyFilter('source_type', e.target.value)"
        class="h-7 pl-2 pr-7 text-[12px] bg-bg-elev border border-border rounded-md text-fg-muted focus:border-border-focus capitalize min-w-[120px]"
      >
        <option value="">Any source</option>
        <option v-for="s in SOURCE_TYPES.filter(Boolean)" :key="s" :value="s">{{ s }}</option>
      </select>
      <button v-if="filterCount" @click="clearFilters" class="text-[11.5px] text-fg-subtle hover:text-fg">Clear</button>
      <span class="ml-auto text-[11px] text-fg-subtle font-mono tabular-nums">
        Page {{ tasks.current_page }} / {{ tasks.last_page }}
      </span>
    </div>

    <!-- Column headers -->
    <div
      class="px-8 py-2 text-[10.5px] font-semibold text-fg-faint uppercase tracking-[0.06em] grid items-center gap-3 border-b border-border bg-bg"
      style="grid-template-columns: 22px 1fr 110px 110px 90px 28px 32px"
    >
      <span></span>
      <span>Task</span>
      <span>Source</span>
      <span>Priority</span>
      <span>Due</span>
      <span></span>
      <span></span>
    </div>

    <!-- Task rows -->
    <div class="flex-1 overflow-auto">
      <div v-if="!tasks.data.length" class="px-8 py-16 text-center text-fg-subtle">
        <Icon name="inbox" :size="32" class="mx-auto text-fg-faint" />
        <p class="mt-3 text-[13px]">No tasks match these filters.</p>
        <button v-if="filterCount" @click="clearFilters" class="mt-2 text-[12px] text-accent-fg hover:underline">Clear filters</button>
      </div>
      <Link
        v-for="t in tasks.data" :key="t.id" :href="'/tasks/' + t.id"
        class="px-8 py-2 grid items-center gap-3 border-b border-border hover:bg-bg-hover transition-colors group"
        style="grid-template-columns: 22px 1fr 110px 110px 90px 28px 32px"
      >
        <span class="flex items-center justify-center text-fg-subtle">
          <Icon v-if="t.ai_summary" name="sparkle" :size="13" class="text-accent" />
          <span v-else class="w-1.5 h-1.5 rounded-full bg-border-strong" />
        </span>
        <div class="min-w-0">
          <div class="flex items-center gap-2">
            <span class="text-[13px] font-[550] text-fg truncate group-hover:text-fg">{{ t.title }}</span>
            <Badge v-if="t.needs_review" variant="review" size="xs" icon="sparkle">Review</Badge>
          </div>
          <p v-if="t.ai_summary" class="text-[11.5px] text-fg-subtle mt-0.5 line-clamp-1 leading-snug">
            {{ t.ai_summary }}
          </p>
        </div>
        <SourceBadge :source="t.source_type" />
        <PriorityBadge :level="t.priority" />
        <span class="text-[11.5px] tabular-nums" :class="dueClass(t.due_date)">{{ fmtDue(t.due_date) || '—' }}</span>
        <Avatar v-if="t.source_account_label" :name="t.source_account_label" :size="20" />
        <span v-else></span>
        <span class="text-fg-subtle opacity-0 group-hover:opacity-100"><Icon name="chevronRight" :size="14" /></span>
      </Link>
    </div>

    <!-- Pagination -->
    <div v-if="tasks.last_page > 1" class="px-8 py-3 flex items-center justify-between border-t border-border">
      <Link v-if="tasks.prev_page_url" :href="tasks.prev_page_url" preserve-scroll>
        <Button variant="secondary" size="sm" icon="chevronLeft">Prev</Button>
      </Link><span v-else></span>
      <span class="text-[11.5px] text-fg-subtle font-mono">{{ tasks.from }}-{{ tasks.to }} of {{ tasks.total }}</span>
      <Link v-if="tasks.next_page_url" :href="tasks.next_page_url" preserve-scroll>
        <Button variant="secondary" size="sm" icon-right="chevronRight">Next</Button>
      </Link><span v-else></span>
    </div>
  </div>
</template>
