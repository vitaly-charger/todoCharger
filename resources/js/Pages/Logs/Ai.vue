<script setup>
import { Head, Link } from '@inertiajs/vue3';
import InboxLayout from '@/Layouts/InboxLayout.vue';
import Card from '@/Components/inbox/Card.vue';
import Badge from '@/Components/inbox/Badge.vue';
import Confidence from '@/Components/inbox/Confidence.vue';
import Icon from '@/Components/inbox/Icon.vue';

defineOptions({ layout: InboxLayout });

defineProps({ logs: Object });

function fmtDate(d) {
  if (!d) return '—';
  return new Date(d).toLocaleString(undefined, { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
  <Head title="AI logs" />
  <div class="px-8 py-6 flex flex-col gap-4 max-w-[1100px]">
    <header>
      <h1 class="text-[18px] font-semibold tracking-tight3 flex items-center gap-2">
        <Icon name="sparkles" :size="16" class="text-accent" />
        AI analysis logs
      </h1>
      <p class="text-[12.5px] text-fg-muted mt-1">{{ logs.total }} entries</p>
    </header>

    <Card :padded="false">
      <div class="grid items-center gap-3 px-5 py-2 text-[10.5px] font-semibold text-fg-faint uppercase tracking-[0.06em] border-b border-border"
           style="grid-template-columns: 80px 1fr 90px 110px 130px">
        <span>Verdict</span>
        <span>Source &middot; Title</span>
        <span>Confidence</span>
        <span>Tokens</span>
        <span>When</span>
      </div>
      <ul v-if="logs.data.length" class="flex flex-col">
        <li v-for="log in logs.data" :key="log.id"
            class="grid items-center gap-3 px-5 py-2 border-b border-border last:border-0 hover:bg-bg-hover"
            style="grid-template-columns: 80px 1fr 90px 110px 130px">
          <Badge :variant="log.is_task ? 'success' : 'neutral'" size="xs" dot>
            {{ log.is_task ? 'Task' : 'Skip' }}
          </Badge>
          <div class="min-w-0">
            <div class="text-[12.5px] text-fg truncate">
              {{ log.error_message || (log.task_id ? ('Task #' + log.task_id) : 'Source message #' + (log.source_message_id ?? '—')) }}
            </div>
            <div class="text-[11px] text-fg-subtle font-mono">
              {{ log.provider }}{{ log.model ? ' &middot; ' + log.model : '' }}
            </div>
          </div>
          <Confidence v-if="log.confidence" :value="Number(log.confidence)" />
          <span v-else class="text-fg-faint text-[11px]">—</span>
          <span class="text-[11.5px] font-mono text-fg-subtle tabular-nums">
            {{ log.tokens_used ?? '—' }}
          </span>
          <span class="text-[11px] text-fg-subtle font-mono">{{ fmtDate(log.created_at) }}</span>
        </li>
      </ul>
      <div v-else class="px-5 py-12 text-center text-fg-subtle text-[12.5px]">No AI calls yet.</div>
    </Card>

    <div v-if="logs.last_page > 1" class="flex items-center justify-between text-[12px] text-fg-muted">
      <Link v-if="logs.prev_page_url" :href="logs.prev_page_url" class="hover:text-fg">← Prev</Link><span v-else />
      <span class="font-mono">{{ logs.from }}-{{ logs.to }} of {{ logs.total }}</span>
      <Link v-if="logs.next_page_url" :href="logs.next_page_url" class="hover:text-fg">Next →</Link><span v-else />
    </div>
  </div>
</template>
