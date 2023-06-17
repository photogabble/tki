<script setup lang="ts">
import BorderedHeading from "@/Components/atoms/layout/BorderedHeading.vue";
import InputLabel from "@/Components/atoms/form/InputLabel.vue";
import TextInput from "@/Components/atoms/form/TextInput.vue";
import PrimaryButton from "@/Components/atoms/form/PrimaryButton.vue";
import InputError from "@/Components/atoms/form/InputError.vue";
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref<HTMLInputElement | null>(null);
const currentPasswordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const updatePassword = () => {
  form.put(route('password.update'), {
    preserveScroll: true,
    onSuccess: () => form.reset(),
    onError: () => {
      if (form.errors.password) {
        form.reset('password', 'password_confirmation');
        passwordInput.value?.focus();
      }
      if (form.errors.current_password) {
        form.reset('current_password');
        currentPasswordInput.value?.focus();
      }
    },
  });
};
</script>

<template>
  <section>
    <bordered-heading>
      <span class="text-white">Update Password</span>
    </bordered-heading>

    <form @submit.prevent="updatePassword" class="space-y-4 mb-5 flex flex-col">
      <div>
        <input-label for="current_password" value="Current Password"/>
        <text-input id="current_password" ref="currentPasswordInput" type="password" v-model="form.current_password" required autocomplete="current-password" />
        <input-error class="mt-2" :message="form.errors.current_password" />
      </div>

      <div>
        <input-label for="password" value="New Password"/>
        <text-input id="password" ref="passwordInput" type="password" v-model="form.password" required autocomplete="new-password"/>
        <input-error class="mt-2" :message="form.errors.password" />
      </div>

      <div>
        <input-label for="password_confirmation" value="Confirm Password"/>
        <text-input id="password_confirmation" type="password" v-model="form.password_confirmation" required autocomplete="new-password"/>
        <input-error class="mt-2" :message="form.errors.password_confirmation" />
      </div>

      <div class="flex items-center gap-4">
        <primary-button :disabled="form.processing" class="h-8">Save</primary-button>
        <Transition enter-from-class="opacity-0" leave-to-class="opacity-0" class="transition ease-in-out">
          <p v-if="form.recentlySuccessful" class="text-sm text-ui-orange-500">Changes Saved.</p>
        </Transition>
      </div>
    </form>
  </section>
</template>