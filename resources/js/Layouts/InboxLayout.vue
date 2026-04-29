<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Logo from '@/Components/inbox/Logo.vue';
import Icon from '@/Components/inbox/Icon.vue';
import Avatar from '@/Components/inbox/Avatar.vue';
import KBD from '@/Components/inbox/KBD.vue';
import Button from '@/Components/inbox/Button.vue';
import SourceGlyph from '@/Components/inbox/SourceGlyph.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const sidebar = computed(() => page.props.sidebar || {});
const flash = computed(() => page.props.flash || {});

const sidebarOpen = ref(true);
const search = ref('');

const STATUS_META = [
  { key: 'inbox',       label: 'Inbox',       icon: 'inbox', href: '/tasks?status=inbox',       highlight: true },
  { key: 'todo',        label: 'To Do',       icon: 'list',  href: '/tasks?status=todo' },
  { key: 'in_progress', label: 'In Progress', icon: 'bolt',  href: '/tasks?status=in_progress' },
  { key: 'waiting',     label: 'Waiting',     icon: 'clock', href: '/tasks?status=waiting' },
  { key: 'done',        label: 'Done',        icon: 'check', href: '/tasks?status=done' },
  { key: 'ignored',     label: 'Ignored',     icon: 'archive', href: '/tasks?status=ignored' },
];

const SMART_VIEWS = [
  { id: 'review', label: 'Needs review', icon: 'sparkle', href: '/tasks?needs_review=1', accent: true, key: 'review' },
  { id: 'today',  label: 'Due today',    icon: 'calendar', href: '/tasks?due_today=1', key: 'today' },
  { id: 'urgent', label: 'Urgent',       icon: 'flame',    href: '/tasks?priority=urgent', key: 'urgent' },
];

const SOURCES = [
  { key: 'gmail',    label: 'Gmail',    href: '/tasks?source_type=gmail' },
  { key: 'slack',    label: 'Slack',    href: '/tasks?source_type=slack' },
  { key: 'telegram', label: 'Telegram', href: '/tasks?source_type=telegram' },
  { key: 'manual',   label: 'Manual',   href: '/tasks?source_type=manual' },
];

const url = computed(() => page.url);
function isActive(href) {
  if (href.includes('?')) {
    const [path, qs] = href.split('?');
    return url.value.startsWith(path) && url.value.includes(qs);
  }
  return url.value === href || url.value.startsWith(href + '/');
}

function logout() { router.post('/logout'); }
function newTask() { router.visit('/tasks/create'); }

function submitSearch(e) {
  e.preventDefault();
  router.get('/tasks', { search: search.value }, { preserveState: true });
}
</script>

<template>
  <div
    class="grid h-screen w-screen overflow-hidden bg-bg text-fg"
    :style="{
      gridTemplateColumns: sidebarOpen ? '232px 1fr' : '56px 1fr',
      gridTemplateRows: '52px 1fr',
    }"
  >
    <!-- Topbar -->
    <header
      class="col-span-2 row-start-1 grid items-center bg-bg border-b border-border"
      :style="{ gridTemplateColumns: sidebarOpen ? '232px 1fr auto' : '56px 1fr auto' }"
    >
      <div class="flex items-center h-full" :class="sidebarOpen ? 'pl-4' : 'justify-center'">
        <Link href="/dashboard"><Logo :with-word="sidebarOpen" /></Link>
      </div>
      <div class="flex items-center gap-2.5 px-4 h-full">
        <form @submit="submitSearch" class="flex items-center gap-2 h-[30px] px-2.5 flex-1 max-w-[520px] bg-bg-elev border border-border rounded-md text-fg-subtle">
          <Icon name="search" :size="14" />
          <input v-model="search" placeholder="Search tasks, sources, people…"
            class="flex-1 border-0 outline-none bg-transparent text-[13px] text-fg placeholder:text-fg-subtle p-0 focus:ring-0" />
          <KBD>⌘</KBD><KBD>K</KBD>
        </form>
      </div>
      <div class="flex items-center gap-1.5 pr-3.5">
        <Button variant="ghost" size="sm" icon="sparkles">Ask AI</Button>
        <Button variant="ghost" size="sm" icon="bell" />
        <Link href="/settings"><Button variant="ghost" size="sm" icon="settings" /></Link>
        <span class="w-px h-5 bg-border mx-1" />
        <button @click="logout" class="rounded-full" :title="(user?.name || '') + ' — sign out'">
          <Avatar :name="user?.name || '?'" :size="26" />
        </button>
      </div>
    </header>

    <!-- Sidebar -->
    <nav class="row-start-2 col-start-1 bg-bg border-t border-border overflow-auto flex flex-col gap-[18px]"
      :class="sidebarOpen ? 'px-2.5 py-3' : 'px-1.5 py-3'">
      <Button v-if="sidebarOpen" variant="primary" size="sm" icon="plus" full-width @click="newTask">
        New task
      </Button>

      <div class="flex flex-col gap-0.5">
        <div v-if="sidebarOpen" class="text-[10px] font-semibold text-fg-faint tracking-[0.06em] uppercase px-2.5 pt-1.5 pb-1">
          Statuses
        </div>
        <Link v-for="s in STATUS_META" :key="s.key" :href="s.href" preserve-scroll
          class="flex items-center gap-2 h-7 rounded-md cursor-pointer relative tracking-[-0.005em]"
          :class="[
            sidebarOpen ? 'px-2.5 text-[12.5px]' : 'justify-center text-[12.5px]',
            isActive(s.href) ? 'bg-bg-active text-fg font-[550]' : 'text-fg-muted hover:bg-bg-hover hover:text-fg font-medium',
          ]">
          <span v-if="isActive(s.href)" class="absolute left-0 top-1.5 bottom-1.5 w-[2px] bg-accent rounded-full" />
          <Icon :name="s.icon" :size="14" :class="s.highlight ? 'text-accent' : ''" />
          <span v-if="sidebarOpen" class="flex-1 truncate">{{ s.label }}</span>
          <span v-if="sidebarOpen && sidebar.statusCounts" class="font-mono text-[10.5px] text-fg-subtle tabular-nums">
            {{ sidebar.statusCounts[s.key] ?? 0 }}
          </span>
        </Link>
      </div>

      <div class="flex flex-col gap-0.5">
        <div v-if="sidebarOpen" class="text-[10px] font-semibold text-fg-faint tracking-[0.06em] uppercase px-2.5 pt-1.5 pb-1 flex items-center gap-1.5">
          <Icon name="sparkle" :size="10" class="text-accent" />
          Smart views
        </div>
        <Link v-for="v in SMART_VIEWS" :key="v.id" :href="v.href" preserve-scroll
          class="flex items-center gap-2 h-7 rounded-md cursor-pointer relative tracking-[-0.005em]"
          :class="[
            sidebarOpen ? 'px-2.5 text-[12.5px]' : 'justify-center text-[12.5px]',
            isActive(v.href) ? 'bg-bg-active text-fg font-[550]' : 'text-fg-muted hover:bg-bg-hover hover:text-fg font-medium',
          ]">
          <Icon :name="v.icon" :size="14" :class="v.accent ? 'text-accent' : ''" />
          <span v-if="sidebarOpen" class="flex-1 truncate">{{ v.label }}</span>
          <span v-if="sidebarOpen && sidebar.smartCounts" class="font-mono text-[10.5px] tabular-nums"
            :class="v.accent ? 'text-accent-fg bg-accent-soft px-1.5 rounded-full py-px' : 'text-fg-subtle'">
            {{ sidebar.smartCounts[v.key] ?? 0 }}
          </span>
        </Link>
      </div>

      <div class="flex flex-col gap-0.5">
        <div v-if="sidebarOpen" class="text-[10px] font-semibold text-fg-faint tracking-[0.06em] uppercase px-2.5 pt-1.5 pb-1">
          Sources
        </div>
        <Link v-for="s in SOURCES" :key="s.key" :href="s.href" preserve-scroll
          class="flex items-center gap-2 h-7 rounded-md cursor-pointer relative tracking-[-0.005em]"
          :class="[
            sidebarOpen ? 'px-2.5 text-[12.5px]' : 'justify-center text-[12.5px]',
            isActive(s.href) ? 'bg-bg-active text-fg font-[550]' : 'text-fg-muted hover:bg-bg-hover hover:text-fg font-medium',
          ]">
          <SourceGlyph :source="s.key" :size="14" />
          <span v-if="sidebarOpen" class="flex-1 truncate">{{ s.label }}</span>
          <span v-if="sidebarOpen && sidebar.sourceCounts" class="font-mono text-[10.5px] text-fg-subtle tabular-nums">
            {{ sidebar.sourceCounts[s.key] ?? 0 }}
          </span>
        </Link>
      </div>

      <div class="flex-1" />

      <div v-if="sidebarOpen" class="bg-bg-elev border border-border rounded-lg p-3 text-[11.5px] text-fg-muted flex flex-col gap-2">
        <div class="flex items-center gap-1.5 font-semibold text-fg">
          <Icon name="sparkles" :size="13" class="text-accent" />
          AI inbox triage
        </div>
        <div class="leading-snug">
          <span v-if="sidebar.smartCounts">{{ sidebar.smartCounts.review || 0 }} need review. </span>
          <span class="text-fg-subtle">Last sync {{ sidebar.lastSyncAt || 'never' }}.</span>
        </div>
        <div class="flex gap-1.5">
          <Link href="/sources"><Button variant="secondary" size="xs" icon="refresh">Sources</Button></Link>
          <Link href="/logs/sync"><Button variant="ghost" size="xs">Logs</Button></Link>
        </div>
      </div>
    </nav>

    <!-- Main -->
    <main class="row-start-2 col-start-2 overflow-auto bg-bg border-t border-l border-border">
      <div v-if="flash.status" class="mx-8 mt-4 px-3 py-2 rounded-md bg-success-soft text-success-fg text-sm">
        {{ flash.status }}
      </div>
      <div v-if="flash.error" class="mx-8 mt-4 px-3 py-2 rounded-md bg-urgent-soft text-urgent-fg text-sm">
        {{ flash.error }}
      </div>
      <slot />
    </main>
  </div>
</template>
