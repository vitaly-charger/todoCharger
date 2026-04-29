<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import InboxLayout from '@/Layouts/InboxLayout.vue';
import Card from '@/Components/inbox/Card.vue';
import Button from '@/Components/inbox/Button.vue';
import Badge from '@/Components/inbox/Badge.vue';
import SourceGlyph from '@/Components/inbox/SourceGlyph.vue';
import Icon from '@/Components/inbox/Icon.vue';

defineOptions({ layout: InboxLayout });

const props = defineProps({ accounts: Array, types: Array });

const TYPE_LABELS = {
  gmail: 'Gmail', slack: 'Slack', telegram: 'Telegram',
  monday: 'monday.com', wrike: 'Wrike', manual: 'Manual',
};

const showAdd = ref(false);
const form = useForm({ type: 'gmail', name: '', identifier: '', enabled: true });
function submit() {
  form.post('/sources', {
    onSuccess: () => { form.reset(); showAdd.value = false; },
  });
}
function sync(id) { router.post(`/sources/${id}/sync`); }
function toggle(id) { router.post(`/sources/${id}/toggle`, {}, { preserveScroll: true }); }

const groups = computed(() => {
  const out = {};
  for (const t of props.types) out[t] = [];
  for (const a of props.accounts) (out[a.type] = out[a.type] || []).push(a);
  return out;
});

function fmtDate(d) {
  if (!d) return 'never';
  const dt = new Date(d);
  const diffH = (Date.now() - dt) / 3.6e6;
  if (diffH < 1) return Math.max(1, Math.round(diffH * 60)) + 'm ago';
  if (diffH < 24) return Math.round(diffH) + 'h ago';
  return Math.round(diffH / 24) + 'd ago';
}
</script>

<template>
  <Head title="Sources" />
  <div class="px-8 py-6 flex flex-col gap-5 max-w-[1100px]">
    <header class="flex items-end justify-between">
      <div>
        <h1 class="text-[18px] font-semibold tracking-tight3">Sources</h1>
        <p class="text-[12.5px] text-fg-muted mt-1">
          Connect inboxes and tools so your AI assistant can extract tasks automatically.
        </p>
      </div>
      <Button variant="primary" size="sm" icon="plus" @click="showAdd = !showAdd">Add source</Button>
    </header>

    <!-- Summary cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
      <div v-for="t in ['gmail','slack','telegram','monday','wrike','manual']" :key="t"
           class="bg-bg-elev border border-border rounded-lg p-4 flex items-center gap-3">
        <SourceGlyph :source="t" :size="22" />
        <div class="flex-1 min-w-0">
          <div class="text-[12.5px] font-[550] text-fg capitalize">{{ TYPE_LABELS[t] }}</div>
          <div class="text-[11px] text-fg-subtle">
            {{ groups[t]?.length || 0 }} account{{ (groups[t]?.length || 0) === 1 ? '' : 's' }}
          </div>
        </div>
        <Badge :variant="(groups[t]?.length || 0) > 0 ? 'success' : 'neutral'" size="xs">
          {{ (groups[t]?.length || 0) > 0 ? 'Connected' : 'Off' }}
        </Badge>
      </div>
    </div>

    <!-- Add form -->
    <Card v-if="showAdd" title="Connect a source">
      <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
        <label class="flex flex-col gap-1">
          <span class="text-[11px] font-semibold text-fg-subtle uppercase tracking-[0.04em]">Type</span>
          <select v-model="form.type" class="h-9 px-2 bg-bg-sunken border border-border rounded-md text-[13px] capitalize">
            <option v-for="t in types" :key="t" :value="t">{{ TYPE_LABELS[t] || t }}</option>
          </select>
        </label>
        <label class="flex flex-col gap-1">
          <span class="text-[11px] font-semibold text-fg-subtle uppercase tracking-[0.04em]">Name</span>
          <input v-model="form.name" required class="h-9 px-2.5 bg-bg-sunken border border-border rounded-md text-[13px]" />
        </label>
        <label class="flex flex-col gap-1">
          <span class="text-[11px] font-semibold text-fg-subtle uppercase tracking-[0.04em]">Identifier</span>
          <input v-model="form.identifier" placeholder="e.g. you@gmail.com" class="h-9 px-2.5 bg-bg-sunken border border-border rounded-md text-[13px]" />
        </label>
        <Button type="submit" variant="primary" size="md" icon="link" :disabled="form.processing">Connect</Button>
      </form>
    </Card>

    <!-- Connected accounts -->
    <Card title="Connected accounts" :padded="false">
      <ul v-if="accounts.length" class="flex flex-col">
        <li v-for="a in accounts" :key="a.id"
            class="flex items-center gap-3 px-5 py-3 border-b border-border last:border-0">
          <SourceGlyph :source="a.type" :size="20" />
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
              <Link :href="'/sources/' + a.id" class="text-[13px] font-[550] text-fg hover:text-accent-fg truncate">
                {{ a.name }}
              </Link>
              <Badge :variant="a.enabled ? 'success' : 'neutral'" size="xs" dot>
                {{ a.enabled ? 'Active' : 'Paused' }}
              </Badge>
              <Badge v-if="a.last_sync_status === 'failed'" variant="urgent" size="xs" icon="alert">Failed</Badge>
            </div>
            <div class="text-[11.5px] text-fg-subtle truncate mt-0.5">
              <span class="capitalize">{{ TYPE_LABELS[a.type] || a.type }}</span>
              <span v-if="a.identifier"> &middot; {{ a.identifier }}</span>
              <span> &middot; last sync {{ fmtDate(a.last_synced_at) }}</span>
            </div>
          </div>
          <span class="text-[11.5px] text-fg-subtle font-mono tabular-nums">
            {{ a.tasks_count }} tasks
          </span>
          <Button variant="ghost" size="xs" icon="refresh" @click="sync(a.id)">Sync</Button>
          <Button variant="ghost" size="xs" :icon="a.enabled ? 'pause' : 'check'" @click="toggle(a.id)">
            {{ a.enabled ? 'Pause' : 'Enable' }}
          </Button>
        </li>
      </ul>
      <div v-else class="px-5 py-12 text-center text-fg-subtle">
        <Icon name="link" :size="28" class="mx-auto text-fg-faint" />
        <p class="mt-3 text-[13px]">No sources yet. Connect one to start extracting tasks.</p>
      </div>
    </Card>
  </div>
</template>
