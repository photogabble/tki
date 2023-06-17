<template>
  <form class="text-center justify-center items-center max-w-150 py6 flex flex-col gap-3" @submit.prevent="">
    <input-label for="server" value="Server Url"/>

    <text-input
        ref="input"
        autocapitalize="off"
        inputmode="url"
        spellcheck="false"
        autocomplete="off"
        autofocus
    />

    <div class="flex w-full space-x-4">
      <primary-button class="flex-grow">{{ __('login.l_login_title')}}</primary-button>
      <primary-button class="flex-grow">{{ __('login.l_login_title')}}</primary-button>
    </div>
  </form>
</template>

<script setup lang="ts">
import InputLabel from "@/Components/atoms/form/InputLabel.vue";
import TextInput from "@/Components/atoms/form/TextInput.vue";
import PrimaryButton from "@/Components/atoms/form/PrimaryButton.vue";
import {ref} from "vue";

const input = ref<HTMLInputElement | undefined>();
</script>