<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import InboxLayout from '@/Layouts/InboxLayout.vue';
import Card from '@/Components/inbox/Card.vue';
import Button from '@/Components/inbox/Button.vue';
import Icon from '@/Components/inbox/Icon.vue';

defineOptions({ layout: InboxLayout });

defineProps({ sourceAccounts: Array });

const form = useForm({
  title: '', description: '', priority: 'normal', status: 'inbox', due_date: null,
});

function submit() { form.post('/tasks'); }
</script>

<template>
  <Head title="New task" />
  <div class="px-8 py-6 max-w-[680px] flex flex-col gap-5">
    <header class="flex items-center justify-between">
      <div>
        <h1 class="text-[18px] font-semibold tracking-tight3">New task</h1>
        <p class="text-[12.5px] text-fg-muted mt-1">Capture something that doesn&rsquo;t live in a connected source yet.</p>
      </div>
      <Link href="/tasks"><Button variant="ghost" size="sm" icon="x">Cancel</Button></Link>
    </header>

    <Card>
      <form @submit.prevent="submit" class="flex flex-col gap-4">
        <label class="flex flex-col gap-1.5">
          <span class="text-[11px] font-semibold text-fg-subtle uppercase tracking-[0.04em]">Title</span>
          <input v-model="form.title" required maxlength="500"
            class="h-9 px-3 bg-bg-sunken border border-border rounded-md text-[13.5px] text-fg focus:outline-none focus:border-border-focus" />
          <span v-if="form.errors.title" class="text-[11.5px] text-urgent-fg">{{ form.errors.title }}</span>
        </label>

        <label class="flex flex-col gap-1.5">
          <span class="text-[11px] font-semibold text-fg-subtle uppercase tracking-[0.04em]">Notes</span>
          <textarea v-model="form.description" rows="5"
            class="px-3 py-2 bg-bg-sunken border border-border rounded-md text-[13px] text-fg focus:outline-none focus:border-border-focus" />
        </label>

        <div class="grid grid-cols-3 gap-3">
          <label class="flex flex-col gap-1.5">
            <span class="text-[11px] font-semibold text-fg-subtle uppercase tracking-[0.04em]">Status</span>
            <select v-model="form.status"
              class="h-9 px-2.5 bg-bg-sunken border border-border rounded-md text-[13px] text-fg focus:border-border-focus capitalize">
              <option value="inbox">Inbox</option>
              <option value="todo">To Do</option>
              <option value="in_progress">In Progress</option>
              <option value="waiting">Waiting</option>
            </select>
          </label>
          <label class="flex flex-col gap-1.5">
            <span class="text-[11px] font-semibold text-fg-subtle uppercase tracking-[0.04em]">Priority</span>
            <select v-model="form.priority"
              class="h-9 px-2.5 bg-bg-sunken border border-border rounded-md text-[13px] text-fg focus:border-border-focus capitalize">
              <option value="low">Low</option>
              <option value="normal">Medium</option>
              <option value="high">High</option>
              <option value="urgent">Urgent</option>
            </select>
          </label>
          <label class="flex flex-col gap-1.5">
            <span class="text-[11px] font-semibold text-fg-subtle uppercase tracking-[0.04em]">Due</span>
            <input type="date" v-model="form.due_date"
              class="h-9 px-2.5 bg-bg-sunken border border-border rounded-md text-[13px] text-fg focus:border-border-focus" />
          </label>
        </div>

        <div class="flex justify-end gap-2 pt-1">
          <Link href="/tasks"><Button variant="ghost" size="md">Cancel</Button></Link>
          <Button type="submit" variant="primary" size="md" icon="check" :disabled="form.processing">
            Create task
          </Button>
        </div>
      </form>
    </Card>
  </div>
</template>
