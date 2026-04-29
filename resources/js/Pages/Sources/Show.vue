<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import InboxLayout from '@/Layouts/InboxLayout.vue';
import Card from '@/Components/inbox/Card.vue';
import Button from '@/Components/inbox/Button.vue';
import Badge from '@/Components/inbox/Badge.vue';
import SourceGlyph from '@/Components/inbox/SourceGlyph.vue';
import Icon from '@/Components/inbox/Icon.vue';

defineOptions({ layout: InboxLayout });

const props = defineProps({ account: Object, recent_logs: Array });

const form = useForm({
  name: props.account.name,
  identifier: props.account.identifier,
  enabled: props.account.enabled,
});
function save() { form.patch('/sources/' + props.account.id, { preserveScroll: true }); }
function sync() { router.post('/sources/' + props.account.id + '/sync'); }
function destroy() {
  if (confirm('Disconnect this source? Linked tasks will remain.')) router.delete('/sources/' + props.account.id);
}

function fmtDate(d) {
  if (!d) return '—';
  return new Date(d).toLocaleString(undefined, { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
  <Head :title="account.name" />
  <div class="px-8 py-6 flex flex-col gap-5 max-w-[1000px]">
    <nav class="text-[11.5px] text-fg-subtle flex items-center gap-1.5">
      <Link href="/sources" class="hover:text-fg">Sources</Link>
      <Icon name="chevronRight" :size="12" />
      <span class="capitalize">{{ account.type }}</span>
    </nav>

    <header class="flex items-center gap-3">
      <SourceGlyph :source="account.type" :size="28" />
      <div class="flex-1">
        <h1 class="text-[18px] font-semibold tracking-tight3">{{ account.name }}</h1>
        <p class="text-[12.5px] text-fg-muted">
          <span class="capitalize">{{ account.type }}</span>
          <span v-if="account.identifier"> &middot; {{ account.identifier }}</span>
          &middot; {{ account.tasks_count }} tasks &middot; {{ account.messages_count }} messages
        </p>
      </div>
      <Badge :variant="account.enabled ? 'success' : 'neutral'" size="sm" dot>
        {{ account.enabled ? 'Active' : 'Paused' }}
      </Badge>
      <Button variant="secondary" size="sm" icon="refresh" @click="sync">Sync now</Button>
      <Button variant="ghost" size="sm" icon="trash" @click="destroy" />
    </header>

    <Card title="Connection settings">
      <form @submit.prevent="save" class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
        <label class="flex flex-col gap-1">
          <span class="text-[11px] font-semibold text-fg-subtle uppercase tracking-[0.04em]">Name</span>
          <input v-model="form.name" class="h-9 px-2.5 bg-bg-sunken border border-border rounded-md text-[13px]" />
        </label>
        <label class="flex flex-col gap-1">
          <span class="text-[11px] font-semibold text-fg-subtle uppercase tracking-[0.04em]">Identifier</span>
          <input v-model="form.identifier" class="h-9 px-2.5 bg-bg-sunken border border-border rounded-md text-[13px]" />
        </label>
        <label class="inline-flex items-center gap-2 h-9 text-[12.5px] text-fg-muted">
          <input type="checkbox" v-model="form.enabled" class="rounded border-border" />
          Enabled
        </label>
        <Button type="submit" variant="primary" size="sm" icon="check" :disabled="form.processing">Save</Button>
      </form>
    </Card>

    <Card title="Recent sync activity" :padded="false">
      <ul v-if="recent_logs.length" class="flex flex-col">
        <li v-for="log in recent_logs" :key="log.id"
            class="grid items-center gap-3 px-5 py-2.5 border-b border-border last:border-0"
            style="grid-template-columns: 90px 1fr 90px 130px">
          <Badge :variant="log.status === 'failed' ? 'urgent' : log.status === 'success' ? 'success' : 'neutral'" size="xs" dot>
            {{ log.status }}
          </Badge>
          <span class="text-[12.5px] text-fg truncate">{{ log.error_message || ('Imported ' + (log.imported_count ?? 0) + ', created ' + (log.created_task_count ?? 0)) }}</span>
          <span class="text-[11.5px] text-fg-subtle font-mono tabular-nums">
            {{ log.imported_count ?? 0 }}/{{ log.created_task_count ?? 0 }}
          </span>
          <span class="text-[11px] text-fg-subtle font-mono">{{ fmtDate(log.created_at) }}</span>
        </li>
      </ul>
      <div v-else class="px-5 py-10 text-center text-fg-subtle text-[12.5px]">
        No syncs yet. Trigger one with &ldquo;Sync now&rdquo;.
      </div>
    </Card>
  </div>
</template>
