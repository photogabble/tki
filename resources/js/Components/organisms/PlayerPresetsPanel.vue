<script setup lang="ts">
import SidebarPanel from "@/Components/atoms/layout/SidebarPanel.vue";
import {PresetResource} from "@/types/preset";
import SectorNavButton from "@/Components/atoms/SectorNavButton.vue";

const props = defineProps<{
  presets: Array<PresetResource>;
}>();

</script>

<template>
  <sidebar-panel>
    <template #heading>
      <span class="text-white">Realspace</span>
    </template>
    <section class="flex flex-col">
      <ul>
        <li v-for="preset in presets" class="flex">
          <sector-nav-button :link="preset.link" />
          <button>[Set]</button>
        </li>
      </ul>
      <a href="#" class="self-end">[Other]</a>
    </section>
  </sidebar-panel>
</template>