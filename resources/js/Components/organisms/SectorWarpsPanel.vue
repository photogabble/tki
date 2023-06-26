<script setup lang="ts">
import SidebarPanel from "@/Components/atoms/layout/SidebarPanel.vue";
import {SectorResource} from "@/types/resources/sector";
import SectorNavButton from "@/Components/atoms/SectorNavButton.vue";
import NavigationConfirmPopup from "@/Components/organisms/NavigationConfirmPopup.vue";
import {computed, ref} from "vue";
const selectedSector = ref(-1);

const props = defineProps<{
  sector: SectorResource;
}>();

const hasLinks = computed(() => {
  if (!props.sector.links) return false;
  return props.sector.links.length > 0;
})

</script>

<template>
  <navigation-confirm-popup v-model="selectedSector" mode="Warps" />
  <sidebar-panel>
    <template #heading>
      <span class="text-white flex-grow">Sector Warps</span>
      <a href="#">[Nav Computer]</a>
    </template>
    <section class="flex flex-col">
      <ul>
        <li v-for="link in sector.links" class="flex">
          <sector-nav-button :link="link" />
          <button>[Scan]</button>
        </li>
        <li v-if="!hasLinks" class="text-red-600">
          No warps found
        </li>
      </ul>
      <button class="self-end">[Full Scan]</button>
    </section>
  </sidebar-panel>
</template>