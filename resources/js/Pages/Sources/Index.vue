<script setup>
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import InboxLayout from '@/Layouts/InboxLayout.vue';
import Card from '@/Components/inbox/Card.vue';
import Button from '@/Components/inbox/Button.vue';
import Badge from '@/Components/inbox/Badge.vue';
import SourceGlyph from '@/Components/inbox/SourceGlyph.vue';
import Icon from '@/Components/inbox/Icon.vue';

defineOptions({ layout: InboxLayout });

const props = defineProps({ accounts: Array, types: Array });
const page = usePage();
const flash = computed(() => page.props.flash || {});
const errors = computed(() => page.props.errors || {});

const TYPE_LABELS = {
  gmail: 'Gmail', slack: 'Slack', telegram: 'Telegram',
  monday: 'monday.com', wrike: 'Wrike',
};
const TYPE_DESC = {
  gmail: 'Capture tasks from threads where you are addressed directly.',
  slack: 'Surface DMs and @mentions that need action.',
  telegram: 'Detect tasks from Telegram chats where you are mentioned.',
  monday: 'Pull assigned items from your monday.com boards.',
  wrike: 'Pull assigned tasks from your Wrike spaces.',
};
// Sources with a real OAuth flow:
const HAS_OAUTH = { gmail: true };

const showAdd = ref(false);
const form = useForm({ type: 'slack', name: '', identifier: '', enabled: true });
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
      <Button variant="ghost" size="sm" icon="plus" @click="showAdd = !showAdd">
        {{ showAdd ? 'Cancel' : 'Add manually' }}
      </Button>
    </header>

    <div v-if="flash.status"
         class="flex items-center gap-2 px-3 py-2 rounded-md bg-success-soft text-success-fg text-[12.5px] border border-success-soft">
      <Icon name="check" :size="13" /> {{ flash.status }}
    </div>
    <div v-if="errors.source"
         class="flex items-center gap-2 px-3 py-2 rounded-md bg-urgent-soft text-urgent-fg text-[12.5px] border border-urgent-soft">
      <Icon name="alert" :size="13" /> {{ errors.source }}
    </div>

    <!-- Source connection cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
      <div v-for="t in ['gmail','slack','telegram','monday','wrike']" :key="t"
           class="bg-bg-elev border border-border rounded-lg p-4 flex flex-col gap-3">
        <div class="flex items-start gap-3">
          <SourceGlyph :source="t" :size="22" />
          <div class="flex-1 min-w-0">
            <div class="text-[13.5px] font-[550] text-fg">{{ TYPE_LABELS[t] }}</div>
            <div class="text-[11.5px] text-fg-subtle leading-snug">{{ TYPE_DESC[t] }}</div>
          </div>
          <Badge v-if="(groups[t]?.length || 0) > 0" variant="success" size="xs" dot>Connected</Badge>
          <Badge v-else-if="HAS_OAUTH[t]" variant="neutral" size="xs">Not connected</Badge>
          <Badge v-else variant="outline" size="xs">Coming soon</Badge>
        </div>

        <ul v-if="groups[t]?.length" class="flex flex-col gap-1.5">
          <li v-for="a in groups[t]" :key="a.id"
              class="flex items-center gap-2 px-2 py-1.5 rounded-md bg-bg-sunken border border-border">
            <Link :href="'/sources/' + a.id" class="flex-1 min-w-0 text-[12.5px] text-fg hover:text-accent-fg truncate">
              {{ a.identifier || a.name }}
            </Link>
            <span class="text-[11px] text-fg-subtle font-mono tabular-nums">{{ a.tasks_count }}</span>
            <Badge :variant="a.enabled ? 'success' : 'neutral'" size="xs" dot>
              {{ a.enabled ? 'On' : 'Off' }}
            </Badge>
            <button @click.prevent="sync(a.id)" class="text-fg-subtle hover:text-fg" title="Sync now">
              <Icon name="refresh" :size="13" />
            </button>
          </li>
        </ul>

        <div>
          <a v-if="HAS_OAUTH[t]" :href="'/sources/' + t + '/connect'" class="block">
            <Button variant="primary" size="sm" icon="link" full-width>
              {{ (groups[t]?.length || 0) > 0 ? 'Connect another account' : 'Connect ' + TYPE_LABELS[t] }}
            </Button>
          </a>
          <Button v-else variant="secondary" size="sm" icon="link" full-width disabled>
            Connect (coming soon)
          </Button>
        </div>
      </div>
    </div>

    <Card v-if="showAdd" title="Add a source manually">
      <p class="text-[11.5px] text-fg-subtle -mt-1 mb-3">
        For testing only. Real OAuth connect lives in the cards above &mdash; this just creates a placeholder row without credentials.
      </p>
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
        <Button type="submit" variant="primary" size="md" icon="plus" :disabled="form.processing">Add</Button>
      </form>
    </Card>

    <Card v-if="accounts.length" title="All connected accounts" :padded="false">
      <ul class="flex flex-col">
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
    </Card>
  </div>
</template>
