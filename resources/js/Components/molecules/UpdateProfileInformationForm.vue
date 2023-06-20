<script setup lang="ts">
import BorderedHeading from "@/Components/atoms/layout/BorderedHeading.vue";
import InputLabel from "@/Components/atoms/form/InputLabel.vue";
import TextInput from "@/Components/atoms/form/TextInput.vue";
import PrimaryButton from "@/Components/atoms/form/PrimaryButton.vue";
import InputError from "@/Components/atoms/form/InputError.vue";
import { Link, useForm, usePage } from '@inertiajs/vue3';
import {ref, watch} from "vue";

const user = usePage().props.auth.user;
const username = ref<HTMLInputElement | null>(null);

const form = useForm({
  name: user.name,
  email: user.email,
});

const props = defineProps({
  focus: Boolean,
});

watch(() => props.focus, (a,b) => {
  console.log(a,b);
  if (username.value !== null && a) username.value.focus();
});

</script>

<template>
  <section>
    <bordered-heading>
      <span class="text-white">Your Profile &middot;</span> <span aria-hidden="true">2023.0422XX</span>
    </bordered-heading>

    <form @submit.prevent="form.patch(route('profile.update'))" class="space-y-4 mb-5 flex flex-col">
      <div>
        <input-label for="email" value="Email"/>
        <text-input id="email" ref="username" type="email" v-model="form.email" required autocomplete="username" />
        <input-error class="mt-2" :message="form.errors.email" />
      </div>

      <div>
        <input-label for="name" :value="__('new.l_new_pname')"/>
        <text-input id="name" type="text" v-model="form.name" required autocomplete="name"/>
        <input-error class="mt-2" :message="form.errors.name" />
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