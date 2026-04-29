<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import InboxLayout from '@/Layouts/InboxLayout.vue';
import Card from '@/Components/inbox/Card.vue';
import Button from '@/Components/inbox/Button.vue';
import SourceBadge from '@/Components/inbox/SourceBadge.vue';
import PriorityBadge from '@/Components/inbox/PriorityBadge.vue';
import Badge from '@/Components/inbox/Badge.vue';

const props = defineProps({ task: Object });
defineOptions({ layout: InboxLayout });

const editForm = useForm({
  title: props.task.title,
  description: props.task.description || '',
  status: props.task.status,
  priority: props.task.priority,
  due_date: props.task.due_date || '',
});

const commentForm = useForm({ body: '' });

function save() { editForm.patch(`/tasks/${props.task.id}`, { preserveScroll: true }); }
function reanalyze() { router.post(`/tasks/${props.task.id}/reanalyze`, {}, { preserveScroll: true }); }
function destroy() { if (confirm('Delete this task?')) router.delete(`/tasks/${props.task.id}`); }
function addComment() {
  commentForm.post(`/tasks/${props.task.id}/comments`, {
    preserveScroll: true,
    onSuccess: () => commentForm.reset('body'),
  });
}
</script>

<template>
  <Head :title="task.title" />
  <div class="page">
    <header class="page-header">
      <Link href="/tasks" class="link-muted">← Tasks</Link>
      <div class="actions">
        <Button @click="reanalyze">Re-analyze</Button>
        <Button variant="danger" @click="destroy">Delete</Button>
      </div>
    </header>

    <div class="grid">
      <div class="main">
        <Card>
          <input v-model="editForm.title" class="title-input" />
          <textarea v-model="editForm.description" class="desc-input" placeholder="Description…" rows="6" />
          <div class="row">
            <label>Status
              <select v-model="editForm.status" class="ix-input">
                <option v-for="s in ['inbox','todo','in_progress','waiting','done','ignored']" :key="s">{{ s }}</option>
              </select>
            </label>
            <label>Priority
              <select v-model="editForm.priority" class="ix-input">
                <option v-for="p in ['urgent','high','normal','low']" :key="p">{{ p }}</option>
              </select>
            </label>
            <label>Due
              <input type="date" v-model="editForm.due_date" class="ix-input" />
            </label>
            <Button variant="primary" @click="save" :disabled="editForm.processing">Save</Button>
          </div>
        </Card>

        <Card v-if="task.ai_summary || task.ai_reasoning_short || task.follow_up_suggestion">
          <h3>AI analysis</h3>
          <p v-if="task.ai_summary"><strong>Summary:</strong> {{ task.ai_summary }}</p>
          <p v-if="task.ai_reasoning_short"><strong>Why a task:</strong> {{ task.ai_reasoning_short }}</p>
          <p v-if="task.follow_up_suggestion"><strong>Suggested next step:</strong> {{ task.follow_up_suggestion }}</p>
          <p v-if="task.ai_confidence != null" class="muted small">Confidence: {{ Math.round(task.ai_confidence * 100) }}%</p>
        </Card>

        <Card v-if="task.context_text">
          <h3>Original context</h3>
          <pre class="ctx">{{ task.context_text }}</pre>
        </Card>

        <Card>
          <h3>Comments</h3>
          <ul v-if="task.comments?.length" class="comments">
            <li v-for="c in task.comments" :key="c.id">
              <div class="meta">{{ c.user?.name || 'System' }} · {{ c.created_at }}</div>
              <div>{{ c.body }}</div>
            </li>
          </ul>
          <p v-else class="muted small">No comments yet.</p>
          <form @submit.prevent="addComment" class="comment-form">
            <textarea v-model="commentForm.body" rows="2" class="ix-input flex-1" placeholder="Add a comment…" />
            <Button variant="primary" type="submit" :disabled="commentForm.processing">Post</Button>
          </form>
        </Card>
      </div>

      <aside class="side">
        <Card>
          <h3>Meta</h3>
          <dl class="meta-list">
            <dt>Status</dt><dd><Badge size="sm">{{ task.status }}</Badge></dd>
            <dt>Priority</dt><dd><PriorityBadge :priority="task.priority" /></dd>
            <dt>Source</dt><dd><SourceBadge :type="task.source_type" /></dd>
            <dt v-if="task.source_url">Link</dt>
            <dd v-if="task.source_url"><a :href="task.source_url" target="_blank" rel="noopener">Open external</a></dd>
            <dt>Created</dt><dd>{{ task.created_at }}</dd>
            <dt v-if="task.completed_at">Completed</dt>
            <dd v-if="task.completed_at">{{ task.completed_at }}</dd>
            <dt v-if="task.needs_review">Review</dt>
            <dd v-if="task.needs_review"><Badge variant="review" size="sm">Needs review</Badge></dd>
            <dt v-if="task.external_sync_status">External sync</dt>
            <dd v-if="task.external_sync_status">{{ task.external_sync_status }}</dd>
          </dl>
        </Card>

        <Card v-if="task.events?.length">
          <h3>Activity</h3>
          <ul class="events">
            <li v-for="e in task.events" :key="e.id">
              <span class="event-name">{{ e.event }}</span>
              <span class="muted small">{{ e.created_at }}</span>
            </li>
          </ul>
        </Card>
      </aside>
    </div>
  </div>
</template>

<style scoped>
.page { display: flex; flex-direction: column; gap: 16px; }
.page-header { display: flex; align-items: center; justify-content: space-between; }
.actions { display: flex; gap: 8px; }
.link-muted { color: var(--fg-muted); font-size: 13px; }
.grid { display: grid; grid-template-columns: 1fr 320px; gap: 16px; }
@media (max-width: 900px) { .grid { grid-template-columns: 1fr; } }
.main { display: flex; flex-direction: column; gap: 12px; }
.side { display: flex; flex-direction: column; gap: 12px; }
.title-input { width: 100%; font-size: 18px; font-weight: 600; border: 0; background: transparent; padding: 4px 0 8px; color: var(--fg); }
.title-input:focus { outline: none; }
.desc-input { width: 100%; border: 1px solid var(--border); border-radius: var(--r-md); padding: 8px; background: var(--bg-sunken); color: var(--fg); font-family: inherit; font-size: 13px; resize: vertical; }
.row { display: flex; gap: 8px; align-items: end; flex-wrap: wrap; margin-top: 12px; }
.row label { display: flex; flex-direction: column; gap: 4px; font-size: 11px; color: var(--fg-subtle); text-transform: uppercase; letter-spacing: 0.06em; }
.ix-input { padding: 7px 10px; border-radius: var(--r-md); border: 1px solid var(--border-strong); background: var(--bg-elev); font-size: 13px; color: var(--fg); }
.flex-1 { flex: 1; }
h3 { font-size: 13px; font-weight: 600; margin: 0 0 8px; }
.muted { color: var(--fg-muted); }
.muted.small { font-size: 12px; }
.ctx { white-space: pre-wrap; font-family: var(--font-mono); font-size: 12px; background: var(--bg-sunken); padding: 12px; border-radius: var(--r-md); max-height: 320px; overflow: auto; }
.comments { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px; }
.comments .meta { font-size: 11px; color: var(--fg-subtle); margin-bottom: 4px; }
.comment-form { display: flex; gap: 8px; margin-top: 12px; }
.meta-list { display: grid; grid-template-columns: max-content 1fr; gap: 4px 12px; font-size: 13px; margin: 0; }
.meta-list dt { color: var(--fg-subtle); font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; }
.meta-list dd { margin: 0; }
.events { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 4px; }
.event-name { font-size: 12px; }
</style>
