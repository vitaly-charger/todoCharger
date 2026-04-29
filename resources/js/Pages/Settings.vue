<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import InboxLayout from '@/Layouts/InboxLayout.vue';
import Card from '@/Components/inbox/Card.vue';
import Icon from '@/Components/inbox/Icon.vue';
import Button from '@/Components/inbox/Button.vue';

defineOptions({ layout: InboxLayout });

const sections = [
  { id: 'detection', label: 'AI detection', icon: 'sparkles' },
  { id: 'triggers',  label: 'Triggers',     icon: 'bolt' },
  { id: 'notify',    label: 'Notifications', icon: 'bell' },
  { id: 'profile',   label: 'Profile',      icon: 'user' },
];
const active = ref('detection');
</script>

<template>
  <Head title="Settings" />
  <div class="px-8 py-6 grid gap-6 max-w-[1080px]" style="grid-template-columns: 200px 1fr">
    <nav class="flex flex-col gap-0.5 sticky top-0">
      <h2 class="text-[10.5px] font-semibold text-fg-faint uppercase tracking-[0.06em] px-2 pb-1.5">Settings</h2>
      <button v-for="s in sections" :key="s.id" @click="active = s.id"
              class="flex items-center gap-2 h-8 px-2.5 text-[12.5px] rounded-md tracking-[-0.005em]"
              :class="active === s.id ? 'bg-bg-active text-fg font-[550]' : 'text-fg-muted hover:bg-bg-hover hover:text-fg font-medium'">
        <Icon :name="s.icon" :size="14" />
        <span>{{ s.label }}</span>
      </button>
    </nav>

    <div class="flex flex-col gap-4">
      <Card v-show="active === 'detection'" title="AI detection sensitivity">
        <p class="text-[12.5px] text-fg-muted leading-relaxed">
          Higher sensitivity captures more potential tasks but may surface more items for review.
        </p>
        <div class="mt-4 flex items-center gap-1 bg-bg-sunken border border-border rounded-md p-1 w-fit">
          <button v-for="lv in ['Conservative','Balanced','Aggressive']" :key="lv"
                  class="h-7 px-3 rounded-md text-[12px] font-medium"
                  :class="lv === 'Balanced' ? 'bg-bg-elev text-fg shadow-e1' : 'text-fg-muted hover:text-fg'">
            {{ lv }}
          </button>
        </div>
        <div class="mt-4 text-[11.5px] text-fg-subtle">
          Currently: <span class="text-fg font-medium">Balanced</span> &middot; review threshold 60%.
        </div>
      </Card>

      <Card v-show="active === 'triggers'" title="What counts as a task?">
        <ul class="flex flex-col divide-y divide-border -mt-1">
          <li v-for="r in [
            { id: 'mention', label: 'Direct mentions of you', desc: 'You are addressed by name or @handle.', on: true },
            { id: 'verb', label: 'Action verbs', desc: 'Messages with imperatives like “send”, “review”, “follow up”.', on: true },
            { id: 'date', label: 'Dates and deadlines', desc: 'Detect explicit due dates and time references.', on: true },
            { id: 'q', label: 'Open questions to you', desc: 'Surface questions awaiting your reply.', on: false },
          ]" :key="r.id" class="flex items-center gap-3 py-3">
            <div class="flex-1">
              <div class="text-[13px] font-[550] text-fg">{{ r.label }}</div>
              <div class="text-[12px] text-fg-muted leading-snug">{{ r.desc }}</div>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" :checked="r.on" class="sr-only peer">
              <div class="w-9 h-5 bg-bg-sunken border border-border rounded-full peer-checked:bg-accent peer-checked:border-accent transition-colors relative">
                <span class="absolute top-0.5 left-0.5 w-3.5 h-3.5 bg-bg-elev rounded-full shadow-e1 transition-transform peer-checked:translate-x-4" />
              </div>
            </label>
          </li>
        </ul>
      </Card>

      <Card v-show="active === 'notify'" title="Notifications">
        <ul class="flex flex-col divide-y divide-border -mt-1">
          <li v-for="r in [
            { id: 'urgent', label: 'New urgent task', desc: 'Notify immediately when AI detects something urgent.' },
            { id: 'review', label: 'Daily review digest', desc: 'Summary at 9am of items needing your judgment.' },
            { id: 'sync', label: 'Sync failures', desc: 'Alert when a connected source fails to sync.' },
          ]" :key="r.id" class="flex items-center gap-3 py-3">
            <div class="flex-1">
              <div class="text-[13px] font-[550] text-fg">{{ r.label }}</div>
              <div class="text-[12px] text-fg-muted leading-snug">{{ r.desc }}</div>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" checked class="sr-only peer">
              <div class="w-9 h-5 bg-bg-sunken border border-border rounded-full peer-checked:bg-accent peer-checked:border-accent transition-colors relative">
                <span class="absolute top-0.5 left-0.5 w-3.5 h-3.5 bg-bg-elev rounded-full shadow-e1 transition-transform peer-checked:translate-x-4" />
              </div>
            </label>
          </li>
        </ul>
      </Card>

      <Card v-show="active === 'profile'" title="Profile">
        <Link href="/profile" class="text-[13px] text-accent-fg hover:underline">
          Edit your profile, password, and account →
        </Link>
      </Card>
    </div>
  </div>
</template>
