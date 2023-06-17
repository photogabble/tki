<template>
  <pull-out :is-visible="isOpen" @visible="isOpened = $event">
    <UpdateProfileInformationForm :focus="isOpened" />
    <UpdatePasswordForm />
    <DeleteUserForm />
  </pull-out>
</template>

<script setup lang="ts">
import PullOut from "@/Components/atoms/modal/PullOut.vue";
import {useModal} from "@/Composables/useModal";
import UpdateProfileInformationForm from "@/Components/molecules/UpdateProfileInformationForm.vue";
import UpdatePasswordForm from "@/Components/molecules/UpdatePasswordForm.vue";
import {ref} from "vue";
import DeleteUserForm from "@/Components/molecules/DeleteUserForm.vue";

const {isOpen} = useModal('profile');
const isOpened = ref<Boolean>(false);
</script>