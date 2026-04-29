<script setup>
import { Head } from '@inertiajs/vue3';
import InboxLayout from '@/Layouts/InboxLayout.vue';
import Card from '@/Components/inbox/Card.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';

defineOptions({ layout: InboxLayout });

defineProps({ mustVerifyEmail: Boolean, status: String });
</script>

<template>
  <Head title="Profile" />
  <div class="px-8 py-6 flex flex-col gap-5 max-w-[760px]">
    <header>
      <h1 class="text-[18px] font-semibold tracking-tight3">Profile</h1>
      <p class="text-[12.5px] text-fg-muted mt-1">Manage your account, password, and data.</p>
    </header>

    <Card title="Profile information">
      <UpdateProfileInformationForm
        :must-verify-email="mustVerifyEmail"
        :status="status"
        class="max-w-xl"
      />
    </Card>

    <Card title="Update password">
      <UpdatePasswordForm class="max-w-xl" />
    </Card>

    <Card title="Delete account">
      <DeleteUserForm class="max-w-xl" />
    </Card>
  </div>
</template>
