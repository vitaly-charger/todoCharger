<script setup>
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import InboxLayout from '@/Layouts/InboxLayout.vue';
import Card from '@/Components/inbox/Card.vue';
import Button from '@/Components/inbox/Button.vue';
import Badge from '@/Components/inbox/Badge.vue';
import SourceGlyph from '@/Components/inbox/SourceGlyph.vue';
import Icon from '@/Components/inbox/Icon.vue';

defineOptions({ layout: InboxLayout });

const props = defineProps({ accounts: Array, types: Array, telegram_user_supported: { type: Boolean, default: false } });
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
// gmail = full OAuth; others = paste a personal/bot token (validated server-side).
const HAS_OAUTH = { gmail: true };
const TOKEN_HELP = {
  slack: {
    label: 'Slack Bot User OAuth Token',
    placeholder: 'xoxb-…',
    hint: 'Create a Slack app, add scopes (channels:history, im:history, users:read), install to workspace, then copy "Bot User OAuth Token".',
    href: 'https://api.slack.com/apps',
  },
  telegram: {
    label: 'Telegram Bot Token',
    placeholder: '123456:ABC-DEF…',
    hint: 'Create a bot via @BotFather and paste the HTTP API token. Add the bot to chats you want to monitor.',
    href: 'https://t.me/BotFather',
  },
  monday: {
    label: 'monday.com API Token',
    placeholder: 'eyJhbGciOi…',
    hint: 'monday.com → Avatar → Developers → My Access Tokens → copy your personal API token.',
    href: 'https://monday.com/developers/v2',
  },
  wrike: {
    label: 'Wrike Permanent Access Token',
    placeholder: 'eyJ0…',
    hint: 'Wrike → Apps & Integrations → API → Create new permanent token.',
    href: 'https://www.wrike.com/frontend/apps/index.html#/api',
  },
};

const showAdd = ref(false);
const open = reactive({}); // per-type expand state for token form
const tokenForms = reactive({});
function tokenForm(type) {
  if (!tokenForms[type]) tokenForms[type] = useForm({ token: '' });
  return tokenForms[type];
}
function submitToken(type) {
  tokenForm(type).post(`/sources/${type}/connect-token`, {
    preserveScroll: true,
    onSuccess: () => { tokenForm(type).reset(); open[type] = false; },
  });
}

// --- Telegram user-account (MTProto) login ---------------------------------
const tgTab = ref('user'); // 'user' | 'bot'
const tgPhoneForm = useForm({ phone: '' });
const tgCodeForm = useForm({ account_id: null, code: '' });
const tgPwdForm  = useForm({ account_id: null, password: '' });
const tgPending = computed(
  () => (props.accounts || []).find(
    a => a.type === 'telegram' && (a.mtproto_state === 'awaiting_code' || a.mtproto_state === 'awaiting_password')
  ) || null,
);
function submitTelegramPhone() {
  tgPhoneForm.post('/sources/telegram/user/start', { preserveScroll: true });
}
function submitTelegramCode() {
  if (!tgPending.value) return;
  tgCodeForm.account_id = tgPending.value.id;
  tgCodeForm.post('/sources/telegram/user/code', {
    preserveScroll: true,
    onSuccess: () => tgCodeForm.reset('code'),
  });
}
function submitTelegramPassword() {
  if (!tgPending.value) return;
  tgPwdForm.account_id = tgPending.value.id;
  tgPwdForm.post('/sources/telegram/user/password', {
    preserveScroll: true,
    onSuccess: () => tgPwdForm.reset('password'),
  });
}
function cancelTelegramLogin() {
  if (!tgPending.value) return;
  if (!confirm('Cancel this Telegram login? You will need to start over.')) return;
  router.delete('/sources/' + tgPending.value.id + '/telegram/user', { preserveScroll: true });
}

const form = useForm({ type: 'manual', name: '', identifier: '', enabled: true });
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
function connectedCount(t) {
  return (groups.value[t] || []).filter(a => a.connected).length;
}
function connectAccount(a) {
  if (HAS_OAUTH[a.type]) {
    window.location.href = '/sources/' + a.type + '/connect';
  } else {
    open[a.type] = true;
    setTimeout(() => {
      const el = document.querySelector('[data-token-form="' + a.type + '"] input');
      if (el) el.focus();
    }, 0);
  }
}

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
          <Badge v-if="connectedCount(t) > 0" variant="success" size="xs" dot>Connected</Badge>
          <Badge v-else variant="neutral" size="xs">Not connected</Badge>
        </div>

        <ul v-if="groups[t]?.length" class="flex flex-col gap-1.5">
          <li v-for="a in groups[t]" :key="a.id"
              v-show="!(a.type === 'telegram' && (a.mtproto_state === 'awaiting_code' || a.mtproto_state === 'awaiting_password'))"
              class="flex items-center gap-2 px-2 py-1.5 rounded-md bg-bg-sunken border border-border">
            <Link :href="'/sources/' + a.id" class="flex-1 min-w-0 text-[12.5px] text-fg hover:text-accent-fg truncate">
              {{ a.identifier || a.name }}
            </Link>
            <span class="text-[11px] text-fg-subtle font-mono tabular-nums">{{ a.tasks_count }}</span>
            <template v-if="a.connected">
              <Badge :variant="a.enabled ? 'success' : 'neutral'" size="xs" dot>
                {{ a.enabled ? 'On' : 'Off' }}
              </Badge>
              <button @click.prevent="sync(a.id)" class="text-fg-subtle hover:text-fg" title="Sync now">
                <Icon name="refresh" :size="13" />
              </button>
            </template>
            <template v-else>
              <Badge variant="warn" size="xs">Not connected</Badge>
              <button @click.prevent="connectAccount(a)"
                      class="text-[11px] font-semibold text-accent-fg hover:underline">Connect</button>
            </template>
          </li>
        </ul>

        <div>
          <a v-if="HAS_OAUTH[t]" :href="'/sources/' + t + '/connect'" class="block">
            <Button variant="primary" size="sm" icon="link" full-width>
              {{ connectedCount(t) > 0 ? 'Connect another account' : 'Connect ' + TYPE_LABELS[t] }}
            </Button>
          </a>

          <!-- Telegram has two paths: My account (MTProto) and Bot token -->
          <template v-else-if="t === 'telegram'">
            <!-- Mid-flow: continue the pending login regardless of tab -->
            <div v-if="tgPending" class="flex flex-col gap-2">
              <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold text-fg-subtle uppercase tracking-[0.05em]">
                  {{ tgPending.mtproto_state === 'awaiting_password' ? 'Step 3 of 3 · 2FA password' : 'Step 2 of 2 · SMS code' }}
                </span>
                <button @click.prevent="cancelTelegramLogin" class="text-[11px] text-fg-subtle hover:text-urgent-fg">Cancel</button>
              </div>
              <p class="text-[11.5px] text-fg-muted">Logging in as <span class="font-mono">{{ tgPending.identifier }}</span></p>

              <form v-if="tgPending.mtproto_state === 'awaiting_code'" @submit.prevent="submitTelegramCode" class="flex flex-col gap-2">
                <input v-model="tgCodeForm.code" required inputmode="numeric" autocomplete="one-time-code"
                       placeholder="12345"
                       class="h-9 px-2.5 bg-bg-sunken border border-border rounded-md text-[13px] font-mono tracking-widest focus:border-border-focus outline-none" />
                <p class="text-[11px] text-fg-faint">
                  Telegram sent a code to your other devices (or SMS). Type it here.
                </p>
                <p v-if="tgCodeForm.errors.code" class="text-[11px] text-urgent-fg">{{ tgCodeForm.errors.code }}</p>
                <Button type="submit" variant="primary" size="sm" :disabled="tgCodeForm.processing" icon="check" full-width>
                  {{ tgCodeForm.processing ? 'Verifying…' : 'Submit code' }}
                </Button>
              </form>

              <form v-else @submit.prevent="submitTelegramPassword" class="flex flex-col gap-2">
                <input v-model="tgPwdForm.password" type="password" required autocomplete="current-password"
                       placeholder="Two-step verification password"
                       class="h-9 px-2.5 bg-bg-sunken border border-border rounded-md text-[13px] focus:border-border-focus outline-none" />
                <p class="text-[11px] text-fg-faint">Your account has 2FA enabled — enter the cloud password.</p>
                <p v-if="tgPwdForm.errors.password" class="text-[11px] text-urgent-fg">{{ tgPwdForm.errors.password }}</p>
                <Button type="submit" variant="primary" size="sm" :disabled="tgPwdForm.processing" icon="check" full-width>
                  {{ tgPwdForm.processing ? 'Verifying…' : 'Submit password' }}
                </Button>
              </form>
            </div>

            <!-- Initial state: choose tab -->
            <div v-else class="flex flex-col gap-2">
              <div class="flex items-center gap-1 p-0.5 bg-bg-sunken border border-border rounded-md text-[11.5px]">
                <button type="button" @click="tgTab = 'user'"
                        :class="['flex-1 px-2 py-1 rounded', tgTab === 'user' ? 'bg-bg-elev text-fg shadow-sm' : 'text-fg-subtle hover:text-fg']">
                  My account
                </button>
                <button type="button" @click="tgTab = 'bot'"
                        :class="['flex-1 px-2 py-1 rounded', tgTab === 'bot' ? 'bg-bg-elev text-fg shadow-sm' : 'text-fg-subtle hover:text-fg']">
                  Bot token
                </button>
              </div>

              <template v-if="tgTab === 'user'">
                <div v-if="!telegram_user_supported"
                     class="text-[11px] text-warn-fg bg-warn-soft border border-warn-soft rounded-md px-2.5 py-2 leading-snug">
                  Server is missing <code class="font-mono">TELEGRAM_API_ID</code> / <code class="font-mono">TELEGRAM_API_HASH</code>.
                  Get them at <a href="https://my.telegram.org" target="_blank" rel="noopener" class="underline">my.telegram.org</a> and add them to <code class="font-mono">.env</code>.
                </div>
                <form v-else @submit.prevent="submitTelegramPhone" class="flex flex-col gap-2">
                  <label class="flex flex-col gap-1">
                    <span class="text-[10.5px] font-semibold text-fg-subtle uppercase tracking-[0.05em]">Phone (with country code)</span>
                    <input v-model="tgPhoneForm.phone" required placeholder="+380501234567"
                           class="h-9 px-2.5 bg-bg-sunken border border-border rounded-md text-[13px] font-mono focus:border-border-focus outline-none" />
                  </label>
                  <p class="text-[11px] text-fg-faint leading-snug">
                    Logs in as your Telegram account (sees DMs &amp; group mentions). Telegram will send a confirmation code to your other devices.
                  </p>
                  <p v-if="tgPhoneForm.errors.phone" class="text-[11px] text-urgent-fg">{{ tgPhoneForm.errors.phone }}</p>
                  <Button type="submit" variant="primary" size="sm" :disabled="tgPhoneForm.processing" icon="link" full-width>
                    {{ tgPhoneForm.processing ? 'Sending code…' : 'Send code' }}
                  </Button>
                </form>
              </template>

              <template v-else>
                <Button v-if="!open[t]" variant="primary" size="sm" icon="link" full-width @click="open[t] = true">
                  {{ connectedCount(t) > 0 ? 'Connect another bot' : 'Connect Telegram bot' }}
                </Button>
                <form v-else :data-token-form="t" @submit.prevent="submitToken(t)" class="flex flex-col gap-2 pt-1">
                  <label class="flex flex-col gap-1">
                    <span class="text-[10.5px] font-semibold text-fg-subtle uppercase tracking-[0.05em]">{{ TOKEN_HELP[t].label }}</span>
                    <input v-model="tokenForm(t).token" type="password" autocomplete="off" required
                           :placeholder="TOKEN_HELP[t].placeholder"
                           class="h-8 px-2 bg-bg-sunken border border-border rounded-md text-[12.5px] font-mono focus:border-border-focus outline-none" />
                  </label>
                  <p class="text-[11px] text-fg-faint leading-snug">
                    {{ TOKEN_HELP[t].hint }}
                    <a :href="TOKEN_HELP[t].href" target="_blank" rel="noopener" class="text-accent-fg hover:underline">Open</a>
                  </p>
                  <p v-if="tokenForm(t).errors.token" class="text-[11px] text-urgent-fg">{{ tokenForm(t).errors.token }}</p>
                  <div class="flex items-center gap-2">
                    <Button type="submit" variant="primary" size="sm" :disabled="tokenForm(t).processing" icon="check">
                      {{ tokenForm(t).processing ? 'Connecting…' : 'Connect' }}
                    </Button>
                    <Button type="button" variant="ghost" size="sm" @click="open[t] = false">Cancel</Button>
                  </div>
                </form>
              </template>
            </div>
          </template>

          <template v-else>
            <Button v-if="!open[t]" variant="primary" size="sm" icon="link" full-width @click="open[t] = true">
              {{ connectedCount(t) > 0 ? 'Connect another account' : 'Connect ' + TYPE_LABELS[t] }}
            </Button>
            <form v-else :data-token-form="t" @submit.prevent="submitToken(t)" class="flex flex-col gap-2 pt-1">
              <label class="flex flex-col gap-1">
                <span class="text-[10.5px] font-semibold text-fg-subtle uppercase tracking-[0.05em]">
                  {{ TOKEN_HELP[t].label }}
                </span>
                <input
                  v-model="tokenForm(t).token"
                  type="password" autocomplete="off" required
                  :placeholder="TOKEN_HELP[t].placeholder"
                  class="h-8 px-2 bg-bg-sunken border border-border rounded-md text-[12.5px] font-mono focus:border-border-focus outline-none"
                />
              </label>
              <p class="text-[11px] text-fg-faint leading-snug">
                {{ TOKEN_HELP[t].hint }}
                <a :href="TOKEN_HELP[t].href" target="_blank" rel="noopener" class="text-accent-fg hover:underline">Open</a>
              </p>
              <p v-if="tokenForm(t).errors.token" class="text-[11px] text-urgent-fg">{{ tokenForm(t).errors.token }}</p>
              <div class="flex items-center gap-2">
                <Button type="submit" variant="primary" size="sm" :disabled="tokenForm(t).processing" icon="check">
                  {{ tokenForm(t).processing ? 'Connecting…' : 'Connect' }}
                </Button>
                <Button type="button" variant="ghost" size="sm" @click="open[t] = false">Cancel</Button>
              </div>
            </form>
          </template>
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
              <Badge v-if="!a.connected" variant="warn" size="xs">Not connected</Badge>
              <Badge v-else :variant="a.enabled ? 'success' : 'neutral'" size="xs" dot>
                {{ a.enabled ? 'Active' : 'Paused' }}
              </Badge>
              <Badge v-if="a.connected && a.last_sync_status === 'failed'" variant="urgent" size="xs" icon="alert">Failed</Badge>
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
          <template v-if="a.connected">
            <Button variant="ghost" size="xs" icon="refresh" @click="sync(a.id)">Sync</Button>
            <Button variant="ghost" size="xs" :icon="a.enabled ? 'pause' : 'check'" @click="toggle(a.id)">
              {{ a.enabled ? 'Pause' : 'Enable' }}
            </Button>
          </template>
          <Button v-else variant="primary" size="xs" icon="link" @click="connectAccount(a)">Connect</Button>
        </li>
      </ul>
    </Card>
  </div>
</template>
