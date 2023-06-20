<script setup lang="ts">
import SidebarPanel from "@/Components/atoms/layout/SidebarPanel.vue";
import PrimaryButton from "@/Components/atoms/form/PrimaryButton.vue";
import {ShipResource} from "@/types/ship";
import {useModal} from "@/Composables/useModal";

const props = defineProps<{
  ship: ShipResource;
}>();

const { openModal } = useModal('ship-report')

</script>

<template>
  <sidebar-panel>
    <template #heading>
      <span class="text-white">{{ ship.name }} &middot;&nbsp;</span> Lv {{ ship.level }}
    </template>

    <div class="flex space-x-4 mb-4">
      <img src="../../../images/tinyship.png" alt="Small Ship" />
      <ul>
        <li>Armor points: {{ ship.armor.current }}/{{ ship.armor.max }}</li>
        <li>Fighters: {{ship.weapons.fighters.current}}/{{ship.weapons.fighters.max}}</li>
        <li>Torpedoes: {{ship.weapons.torpedoes.current}}/{{ship.weapons.torpedoes.max}}</li>
      </ul>
    </div>
    <div class="flex">
      <primary-button @click="openModal" class="h-6 flex-grow">Report</primary-button>
    </div>
  </sidebar-panel>
</template>