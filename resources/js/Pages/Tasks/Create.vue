<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import InboxLayout from '@/Layouts/InboxLayout.vue';
import Card from '@/Components/inbox/Card.vue';
import Button from '@/Components/inbox/Button.vue';

defineProps({ sourceAccounts: Array });
defineOptions({ layout: InboxLayout });

const form = useForm({
  title: '',
  description: '',
  status: 'todo',
  priority: 'normal',
  due_date: '',
});
function submit() { form.post('/tasks'); }
</script>

<template>
  <Head title="New task" />
  <div class="page">
    <header class="page-header">
      <Link href="/tasks" class="link-muted">← Tasks</Link>
      <h1>New task</h1>
    </header>
    <Card>
      <form @submit.prevent="submit" class="form">
        <label>Title<input v-model="form.title" required class="ix-input" /></label>
        <label>Description<textarea v-model="form.description" rows="6" class="ix-input" /></label>
        <div class="row">
          <label>Status
            <select v-model="form.status" class="ix-input">
              <option v-for="s in ['inbox','todo','in_progress','waiting','done','ignored']" :key="s">{{ s }}</option>
            </select>
          </label>
          <label>Priority
            <select v-model="form.priority" class="ix-input">
              <option v-for="p in ['urgent','high','normal','low']" :key="p">{{ p }}</option>
            </select>
          </label>
          <label>Due
            <input type="date" v-model="form.due_date" class="ix-input" />
          </label>
        </div>
        <div class="actions">
          <Button variant="primary" type="submit" :disabled="form.processing">Create task</Button>
        </div>
        <div v-if="form.errors.title" class="error">{{ form.errors.title }}</div>
      </form>
    </Card>
  </div>
</template>

<style scoped>
.page { display: flex; flex-direction: column; gap: 16px; max-width: 720px; }
.page-header { display: flex; flex-direction: column; gap: 4px; }
.link-muted { color: var(--fg-muted); font-size: 13px; }
h1 { font-size: 22px; font-weight: 600; margin: 0; }
.form { display: flex; flex-direction: column; gap: 12px; }
.form label { display: flex; flex-direction: column; gap: 4px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; color: var(--fg-subtle); }
.ix-input { padding: 8px 10px; border-radius: var(--r-md); border: 1px solid var(--border-strong); background: var(--bg-elev); font-size: 13px; color: var(--fg); font-family: inherit; }
.row { display: flex; gap: 8px; flex-wrap: wrap; }
.row label { flex: 1; min-width: 140px; }
.actions { display: flex; justify-content: flex-end; }
.error { color: var(--urgent-fg); font-size: 12px; }
</style>
