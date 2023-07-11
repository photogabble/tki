<script setup lang="ts">
import NavigationConfirmPopup from "@/Components/organisms/NavigationConfirmPopup.vue";
import PresetEditorPopup from "@/Components/organisms/PresetEditorPopup.vue";
import SidebarPanel from "@/Components/atoms/layout/SidebarPanel.vue";
import SectorNavButton from "@/Components/atoms/SectorNavButton.vue";
import TextButton from "@/Components/atoms/form/TextButton.vue";
import {PresetResource} from "@/types/resources/preset";
import {ref} from "vue";

const selectedSector = ref<number>(-1);
const editingPreset = ref<PresetResource|undefined>();

const props = defineProps<{
  presets: Array<PresetResource>;
}>();

</script>

<template>
  <navigation-confirm-popup v-model="selectedSector" mode="RealSpace" />
  <preset-editor-popup v-model="editingPreset" />
  <sidebar-panel>
    <template #heading>
      <span class="text-white">{{ __('common.l_realspace') }}</span>
    </template>
    <section class="flex flex-col">
      <ul>
        <li v-for="preset in presets" class="flex" :key="`realspace-preset-${preset.id}`">
          <sector-nav-button :link="preset.link" @click="selectedSector = preset.link.to_sector_id" />
          <text-button @click="editingPreset = preset">[Set]</text-button>
        </li>
      </ul>
      <text-button @click="selectedSector = 0" class="self-end">[{{ __('main.l_main_other') }}]</text-button>
    </section>
  </sidebar-panel>
</template>