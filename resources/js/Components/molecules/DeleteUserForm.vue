<script setup lang="ts">
import BorderedHeading from "@/Components/atoms/layout/BorderedHeading.vue";
import { Link, useForm, usePage } from '@inertiajs/vue3';
import {ref, nextTick} from "vue";
import DangerButton from "@/Components/atoms/form/DangerButton.vue";
import PopUp from "@/Components/atoms/modal/PopUp.vue";
import InputLabel from "@/Components/atoms/form/InputLabel.vue";
import TextInput from "@/Components/atoms/form/TextInput.vue";
import InputError from "@/Components/atoms/form/InputError.vue";
import PrimaryButton from "@/Components/atoms/form/PrimaryButton.vue";

const confirmingUserDeletion = ref(false);
const passwordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
  password: '',
});

const confirmUserDeletion = () => {
  confirmingUserDeletion.value = true;
  nextTick(() => passwordInput.value?.focus());
};

const closeModal = () => {
  confirmingUserDeletion.value = false;
  form.reset();
};

const deleteUser = () => {
  form.delete(route('profile.destroy'), {
    preserveScroll: true,
    onSuccess: () => closeModal(),
    onError: () => passwordInput.value?.focus(),
    onFinish: () => form.reset(),
  });
};

</script>

<template>
  <section>
    <bordered-heading>
      <span class="text-white">{{__('self_destruct.l_die_title')}}</span>
    </bordered-heading>
    <p class="mt-1 text-sm text-red-600 dark:text-gray-400">
      {{__('self_destruct.l_die_rusure')}}
    </p>

    <danger-button @click="confirmUserDeletion" class="w-full">{{__('self_destruct.l_die_goodbye')}}</danger-button>

    <pop-up :show="confirmingUserDeletion" @close="closeModal">
      <div class="p-6 w-2/3 border border-ui-orange-500 border-x-4">
        <h2 class="text-lg font-medium text-ui-yellow">
          {{__('self_destruct.l_die_check')}}
        </h2>

        <p class="mt-1 text-sm text-white">
          Once you account is deleted, an in game news obituary will be generated and then all your data
          will be permanently deleted. Please enter your password to confirm you would like
          to permanently delete your account.
        </p>

        <div class="mt-6">
          <input-label for="password" :value="__('login.l_login_pw')"/>
          <text-input id="password" ref="passwordInput" type="password" v-model="form.password" @keyup.enter="deleteUser"/>
          <input-error class="mt-2" :message="form.errors.password" />

          <div class="flex w-full space-x-2 mt-6">
            <primary-button @click="closeModal">{{__('common.l_cancel') }} [ESC]</primary-button>
            <danger-button @click="deleteUser" :disabled="form.processing" :aria-disabled="disabled">{{__('common.l_confirm') }}</danger-button>
          </div>
        </div>
      </div>
    </pop-up>
  </section>
</template>
