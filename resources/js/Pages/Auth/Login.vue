<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Logo from '@/Components/inbox/Logo.vue';
import Icon from '@/Components/inbox/Icon.vue';
import KBD from '@/Components/inbox/KBD.vue';

defineProps({ status: String });
</script>

<template>
  <Head title="Sign in" />
  <div class="min-h-screen w-full flex items-center justify-center bg-bg text-fg px-6">
    <div class="w-full max-w-[380px] flex flex-col items-stretch gap-7">
      <div class="flex flex-col items-center gap-3">
        <Logo :size="32" :with-word="false" />
        <h1 class="text-[22px] font-semibold tracking-tight3">Welcome to AI Inbox</h1>
        <p class="text-[13px] text-fg-muted text-center leading-snug">
          Capture every task hiding in your inbox &mdash; reviewed, ranked, and ready to act on.
        </p>
      </div>

      <div class="bg-bg-elev border border-border rounded-lg shadow-e1 p-5 flex flex-col gap-3">
        <div v-if="status" class="text-[12.5px] text-success-fg bg-success-soft rounded-md px-3 py-2">
          {{ status }}
        </div>
        <a
          href="/auth/google/redirect"
          class="inline-flex items-center justify-center gap-2 h-10 rounded-md bg-fg text-bg font-medium text-[13.5px] hover:opacity-90 transition-opacity"
        >
          <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
            <path fill="#4285F4" d="M22.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.5h5.9c-.3 1.4-1 2.5-2.2 3.3v2.7h3.5c2-1.9 3.3-4.7 3.3-8.2z"/>
            <path fill="#34A853" d="M12 23c2.9 0 5.4-1 7.2-2.6l-3.5-2.7c-1 .7-2.2 1.1-3.7 1.1-2.8 0-5.2-1.9-6.1-4.5H2.3v2.8C4.1 20.7 7.8 23 12 23z"/>
            <path fill="#FBBC05" d="M5.9 14.3c-.2-.7-.4-1.5-.4-2.3s.1-1.6.4-2.3V6.9H2.3A11 11 0 0 0 1 12c0 1.8.4 3.5 1.3 5.1l3.6-2.8z"/>
            <path fill="#EA4335" d="M12 5.5c1.6 0 3 .6 4.1 1.6l3.1-3.1C17.4 2.1 14.9 1 12 1 7.8 1 4.1 3.3 2.3 6.9l3.6 2.8C6.8 7.4 9.2 5.5 12 5.5z"/>
          </svg>
          Continue with Google
        </a>
        <p class="text-[11.5px] text-fg-subtle text-center leading-snug">
          We only request read access to identify you.
          <br />Your inbox content stays in your sources of choice.
        </p>
      </div>

      <div class="flex items-center justify-center gap-1.5 text-[11.5px] text-fg-subtle">
        Press <KBD>⌘</KBD><KBD>K</KBD> after sign-in for the command palette.
      </div>
    </div>
  </div>
</template>
