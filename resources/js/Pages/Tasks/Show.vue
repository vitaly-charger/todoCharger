<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import InboxLayout from '@/Layouts/InboxLayout.vue';
import Card from '@/Components/inbox/Card.vue';
import Button from '@/Components/inbox/Button.vue';
import Badge from '@/Components/inbox/Badge.vue';
import PriorityBadge from '@/Components/inbox/PriorityBadge.vue';
import SourceBadge from '@/Components/inbox/SourceBadge.vue';
import SourceGlyph from '@/Components/inbox/SourceGlyph.vue';
import Confidence from '@/Components/inbox/Confidence.vue';
import Avatar from '@/Components/inbox/Avatar.vue';
import Icon from '@/Components/inbox/Icon.vue';

defineOptions({ layout: InboxLayout });

const props = defineProps({ task: Object });

const STATUSES = ['inbox','todo','in_progress','waiting','done','ignored'];
const PRIORITIES = ['low','normal','high','urgent'];
const STATUS_LABELS = {
  inbox: 'Inbox', todo: 'To Do', in_progress: 'In Progress',
  waiting: 'Waiting', done: 'Done', ignored: 'Ignored',
};

const editing = ref(false);
const form = useForm({
  title: props.task.title,
  description: props.task.description,
  status: props.task.status,
  priority: props.task.priority,
  due_date: props.task.due_date ? props.task.due_date.split('T')[0] : null,
});

function update(field, value) {
  form[field] = value;
  router.patch('/tasks/' + props.task.id, { [field]: value }, { preserveScroll: true, preserveState: true });
}

function saveAll() {
  form.patch('/tasks/' + props.task.id, { preserveScroll: true, onSuccess: () => editing.value = false });
}

function reanalyze() {
  router.post('/tasks/' + props.task.id + '/reanalyze', {}, { preserveScroll: true });
}

function destroy() {
  if (confirm('Delete this task?')) router.delete('/tasks/' + props.task.id);
}

const comment = ref('');
function addComment() {
  if (!comment.value.trim()) return;
  router.post('/tasks/' + props.task.id + '/comments', { body: comment.value }, {
    preserveScroll: true, onSuccess: () => comment.value = '',
  });
}

function fmtDate(d) {
  if (!d) return null;
  return new Date(d).toLocaleString(undefined, { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}
function fmtDay(d) {
  if (!d) return null;
  return new Date(d).toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
}
</script>

<template>
  <Head :title="task.title" />
  <div class="grid h-full" style="grid-template-columns: 1fr 320px">
    <!-- Main -->
    <article class="overflow-auto">
      <div class="px-8 py-6 max-w-[760px] flex flex-col gap-5">
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-1.5 text-[11.5px] text-fg-subtle">
          <Link href="/tasks" class="hover:text-fg">Tasks</Link>
          <Icon name="chevronRight" :size="12" />
          <span class="capitalize">{{ STATUS_LABELS[task.status] }}</span>
          <Icon name="chevronRight" :size="12" />
          <span class="font-mono text-fg-faint">#{{ task.id }}</span>
        </nav>

        <!-- Title + actions -->
        <header class="flex items-start gap-3">
          <input
            v-if="editing"
            v-model="form.title"
            class="flex-1 text-[20px] font-semibold tracking-tight3 bg-transparent border-0 focus:ring-0 p-0 text-fg"
          />
          <h1 v-else class="flex-1 text-[20px] font-semibold tracking-tight3 leading-snug">{{ task.title }}</h1>
          <div class="flex gap-1.5 shrink-0">
            <Button v-if="!editing" variant="ghost" size="sm" icon="edit" @click="editing = true">Edit</Button>
            <Button v-if="editing" variant="primary" size="sm" icon="check" @click="saveAll">Save</Button>
            <Button variant="ghost" size="sm" icon="sparkles" @click="reanalyze">Re-analyze</Button>
            <Button variant="ghost" size="sm" icon="trash" @click="destroy" />
          </div>
        </header>

        <!-- Property row -->
        <div class="flex items-center gap-2 flex-wrap">
          <label class="inline-flex items-center gap-1.5 h-7 px-2 bg-bg-elev border border-border rounded-md text-[12px] text-fg-muted">
            <Icon name="flag" :size="13" />
            <select :value="task.status" @change="(e) => update('status', e.target.value)"
                    class="bg-transparent border-0 p-0 focus:ring-0 text-fg capitalize text-[12px]">
              <option v-for="s in STATUSES" :key="s" :value="s" class="capitalize">{{ STATUS_LABELS[s] }}</option>
            </select>
          </label>
          <label class="inline-flex items-center gap-1.5 h-7 px-2 bg-bg-elev border border-border rounded-md text-[12px] text-fg-muted">
            <Icon name="bolt" :size="13" />
            <select :value="task.priority" @change="(e) => update('priority', e.target.value)"
                    class="bg-transparent border-0 p-0 focus:ring-0 text-fg capitalize text-[12px]">
              <option v-for="p in PRIORITIES" :key="p" :value="p" class="capitalize">{{ p === 'normal' ? 'Medium' : p }}</option>
            </select>
          </label>
          <label class="inline-flex items-center gap-1.5 h-7 px-2 bg-bg-elev border border-border rounded-md text-[12px] text-fg-muted">
            <Icon name="calendar" :size="13" />
            <input type="date" :value="form.due_date" @change="(e) => update('due_date', e.target.value || null)"
                   class="bg-transparent border-0 p-0 focus:ring-0 text-fg text-[12px]" />
          </label>
          <SourceBadge :source="task.source_type" :meta="task.source_account_label" />
          <Badge v-if="task.needs_review" variant="review" size="sm" icon="sparkle">Needs review</Badge>
          <a v-if="task.source_url" :href="task.source_url" target="_blank" rel="noopener"
             class="inline-flex items-center gap-1.5 h-7 px-2 bg-bg-elev border border-border rounded-md text-[12px] text-fg-muted hover:text-fg">
            <Icon name="external" :size="13" />
            Open original
          </a>
        </div>

        <!-- AI summary -->
        <Card v-if="task.ai_summary" accent>
          <template #title>
            <span class="inline-flex items-center gap-1.5">
              <Icon name="sparkles" :size="13" class="text-accent" /> AI summary
            </span>
          </template>
          <template #action>
            <Confidence v-if="task.ai_confidence" :value="Number(task.ai_confidence)" />
          </template>
          <p class="text-[13px] leading-relaxed text-fg">{{ task.ai_summary }}</p>
          <p v-if="task.ai_reasoning_short" class="mt-2 text-[12px] text-fg-muted leading-relaxed">
            <span class="font-semibold text-fg-subtle">Why:</span> {{ task.ai_reasoning_short }}
          </p>
        </Card>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <Card v-if="task.follow_up_suggestion" title="Suggested next step">
            <p class="text-[12.5px] leading-relaxed text-fg whitespace-pre-line">{{ task.follow_up_suggestion }}</p>
          </Card>
          <Card v-if="task.context_text || task.description" title="Original context">
            <p class="text-[12px] leading-relaxed text-fg-muted whitespace-pre-line line-clamp-[14]">
              {{ task.context_text || task.description }}
            </p>
          </Card>
        </div>

        <!-- Description (editor) -->
        <Card v-if="editing" title="Notes">
          <textarea v-model="form.description" rows="6"
            class="w-full bg-transparent border border-border rounded-md p-2 text-[13px] text-fg leading-relaxed focus:outline-none focus:border-border-focus" />
        </Card>
        <Card v-else-if="task.description && !task.context_text" title="Notes">
          <p class="text-[13px] text-fg leading-relaxed whitespace-pre-line">{{ task.description }}</p>
        </Card>

        <!-- Comments -->
        <Card title="Comments">
          <ul v-if="task.comments && task.comments.length" class="flex flex-col gap-3 mb-4">
            <li v-for="c in task.comments" :key="c.id" class="flex gap-2.5">
              <Avatar :name="c.user?.name || '?'" :size="24" />
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                  <span class="text-[12.5px] font-[550] text-fg">{{ c.user?.name || 'Someone' }}</span>
                  <span class="text-[11px] text-fg-subtle font-mono">{{ fmtDate(c.created_at) }}</span>
                </div>
                <p class="text-[12.5px] text-fg leading-relaxed mt-0.5 whitespace-pre-line">{{ c.body }}</p>
              </div>
            </li>
          </ul>
          <div class="flex gap-2 items-start">
            <textarea v-model="comment" rows="2" placeholder="Leave a note for your future self…"
              class="flex-1 bg-bg-sunken border border-border rounded-md p-2 text-[13px] text-fg focus:outline-none focus:border-border-focus" />
            <Button variant="primary" size="sm" icon="send" @click="addComment">Send</Button>
          </div>
        </Card>
      </div>
    </article>

    <!-- Right rail -->
    <aside class="border-l border-border overflow-auto bg-bg-sunken/30">
      <div class="p-5 flex flex-col gap-5">
        <section v-if="task.source_account || task.source_url">
          <h3 class="text-[10.5px] font-semibold text-fg-faint uppercase tracking-[0.06em] mb-2">Source</h3>
          <div class="flex items-center gap-2">
            <SourceGlyph :source="task.source_type" :size="20" />
            <div class="min-w-0">
              <div class="text-[12.5px] font-[550] text-fg capitalize truncate">{{ task.source_account?.name || task.source_type }}</div>
              <div v-if="task.source_account_label" class="text-[11.5px] text-fg-subtle truncate">{{ task.source_account_label }}</div>
            </div>
          </div>
          <a v-if="task.source_url" :href="task.source_url" target="_blank" rel="noopener"
             class="mt-2 inline-flex items-center gap-1 text-[11.5px] text-accent-fg hover:underline">
            Open in source <Icon name="external" :size="11" />
          </a>
        </section>

        <section>
          <h3 class="text-[10.5px] font-semibold text-fg-faint uppercase tracking-[0.06em] mb-2">People</h3>
          <div class="flex items-center gap-2">
            <Avatar :name="$page.props.auth.user.name" :size="22" />
            <span class="text-[12.5px] text-fg">{{ $page.props.auth.user.name }}</span>
            <Badge variant="neutral" size="xs">Owner</Badge>
          </div>
        </section>

        <section>
          <h3 class="text-[10.5px] font-semibold text-fg-faint uppercase tracking-[0.06em] mb-2">Activity</h3>
          <ol class="flex flex-col gap-3 relative">
            <li v-for="ev in task.events" :key="ev.id" class="flex gap-2.5">
              <span class="w-1.5 h-1.5 rounded-full bg-border-strong mt-1.5 shrink-0" />
              <div class="min-w-0">
                <div class="text-[12px] text-fg">
                  <span class="font-[550]">{{ ev.user?.name || 'System' }}</span>
                  <span class="text-fg-muted"> {{ ev.event.replace('_', ' ') }}</span>
                </div>
                <div class="text-[10.5px] text-fg-faint font-mono">{{ fmtDate(ev.created_at) }}</div>
              </div>
            </li>
            <li v-if="!task.events?.length" class="text-[11.5px] text-fg-subtle">No activity yet.</li>
          </ol>
        </section>
      </div>
    </aside>
  </div>
</template>
