<script setup lang="ts">
import SidebarPanel from "@/Components/atoms/layout/SidebarPanel.vue";
import {SectorResource} from "@/types/sector";
import SectorNavButton from "@/Components/atoms/SectorNavButton.vue";

const props = defineProps<{
  sector: SectorResource;
}>();

</script>

<template>
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
      </ul>
      <button class="self-end">[Full Scan]</button>
    </section>
  </sidebar-panel>
</template>