<script setup lang="ts">
import NavigationConfirmPopup from "@/Components/organisms/NavigationConfirmPopup.vue";
import SidebarPanel from "@/Components/atoms/layout/SidebarPanel.vue";
import SectorNavButton from "@/Components/atoms/SectorNavButton.vue";
import TextButton from "@/Components/atoms/form/TextButton.vue";
import {SectorResource} from "@/types/resources/sector";
import {useApi} from "@/Composables/useApi";
import {computed, ref} from "vue";
import {router} from "@inertiajs/vue3";

const selectedSector = ref(-1);

const api = useApi();

const props = defineProps<{
  sector: SectorResource;
}>();

const hasLinks = computed(() => {
  if (!props.sector.links) return false;
  return props.sector.links.length > 0;
});

const navigateTo = async (sector: number) => {
  const response = await api.post(route('warp.move'), {sector});

  if (response.ok) {
    router.visit(route('dashboard'), {data:{navigation: true}});
  } else if (response.status === 300) {
    // An encounter!
  } else if (response.status === 404) {
    // Warp link does not exist
  } else {
    // Mystery error
  }
}

</script>

<template>
  <navigation-confirm-popup v-model="selectedSector" mode="Warp" />
  <sidebar-panel>
    <template #heading>
      <span class="text-white flex-grow">Sector Warps</span>
      <text-button @click="selectedSector = 0">[Nav Computer]</text-button>
    </template>
    <section class="flex flex-col">
      <ul>
        <li v-for="link in sector.links" class="flex">
          <sector-nav-button :link="link" @click="navigateTo(link.to_sector_id)" />
          <text-button>[Scan]</text-button>
        </li>
        <li v-if="!hasLinks" class="text-red-600">
          No warps found
        </li>
      </ul>
      <text-button v-if="hasLinks" class="self-end">[Full Scan]</text-button>
    </section>
  </sidebar-panel>
</template>