<script setup lang="ts">
import NavigationConfirmPopup from "@/Components/organisms/NavigationConfirmPopup.vue";
import SidebarPanel from "@/Components/atoms/layout/SidebarPanel.vue";
import SectorNavButton from "@/Components/atoms/SectorNavButton.vue";
import {PresetResource} from "@/types/resources/preset";
import {ref} from "vue";

const selectedSector = ref(-1);

const props = defineProps<{
  presets: Array<PresetResource>;
}>();

</script>

<template>
  <navigation-confirm-popup v-model="selectedSector" />
  <sidebar-panel>
    <template #heading>
      <span class="text-white">Realspace</span>
    </template>
    <section class="flex flex-col">
      <ul>
        <li v-for="preset in presets" class="flex">
          <sector-nav-button :link="preset.link" @click="selectedSector = preset.link.to_sector_id" />
          <button>[Set]</button>
        </li>
      </ul>
      <button @click="selectedSector = 0" class="self-end hover:text-white">[Other]</button>
    </section>
  </sidebar-panel>
</template>