<template>
  <pull-out :is-visible="isOpen">
    <bordered-heading><span class="text-white">Your Profile &middot;</span> <span aria-hidden="true">2023.0422XX</span></bordered-heading>
    <div class="space-y-4">
      <div>
        <input-label value="Registration Date" />
        <text-input readonly value="April 21st 2023" />
      </div>

      <div>
        <input-label value="User Ident" />
        <text-input readonly value="carbontwelve@notacult.social" />
      </div>

      <hr class="border-ui-orange-500"/>

      <div>
        <input-label value="Display Name" />
        <text-input value="Mx. Autumn" />
      </div>
    </div>
  </pull-out>
</template>

<script setup lang="ts">
import PullOut from "@/Components/atoms/modal/PullOut.vue";
import BorderedHeading from "@/Components/atoms/layout/BorderedHeading.vue";
import InputLabel from "@/Components/atoms/form/InputLabel.vue";
import TextInput from "@/Components/atoms/form/TextInput.vue";
import {useModal} from "@/Composables/useModal";
const {isOpen} = useModal('profile');
</script>