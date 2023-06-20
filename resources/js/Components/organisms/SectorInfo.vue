<script setup lang="ts">
import {ref} from "vue";
import ZoneInfoPopup from "@/Components/organisms/ZoneInfoPopup.vue";
import {useAuth} from "@/Composables/useAuth";

const displayingZoneInfo = ref(false);
const {user} = useAuth();
</script>

<template>
  <section>
    <div class="w-full h-8 flex justify-end items-center pr-2">
      <span>Sector {{ user.ship.sector_id }}</span>&nbsp;in&nbsp;<button class="text-yellow-500 underline" @click="displayingZoneInfo=true">{{ user.ship.sector.zone.name }}</button>
    </div>

    <zone-info-popup v-model="displayingZoneInfo" :zone="user.ship.sector.zone" />
  </section>
</template>