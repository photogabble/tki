<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import GameUI from "@/Layouts/GameUI.vue";
import SidebarPanel from "@/Components/atoms/layout/SidebarPanel.vue";
import MainPanel from "@/Components/atoms/layout/MainPanel.vue";
import SectorInfo from "@/Components/organisms/SectorInfo.vue";
import {useAuth} from "@/Composables/useAuth";
import SectorWarpsPanel from "@/Components/organisms/SectorWarpsPanel.vue";
import PlayerPresetsPanel from "@/Components/organisms/PlayerPresetsPanel.vue";
import ShipCargoPanel from "@/Components/organisms/ShipCargoPanel.vue";
import PlayerShipPanel from "@/Components/organisms/PlayerShipPanel.vue";

const { sector, presets, ship } = useAuth();

</script>

<template>
    <Head title="Dashboard" />

    <GameUI>
      <template #sidebar>
        <player-ship-panel v-if="ship" :ship="ship" />

        <div class="flex flex-col flex-grow overflow-y-scroll max-h-full">
          <ship-cargo-panel v-if="ship" :cargo="ship.cargo_holds" :energy="ship.energy" />

          <sector-warps-panel v-if="sector" :sector="sector" />

          <player-presets-panel v-if="presets" :presets="presets" />

          <sidebar-panel>
            <template #heading>
              <span class="text-white">Trade Routes</span>
            </template>
            <section class="flex flex-col">
              <ul>
                <li class="flex"><span class="flex-grow">None</span> <a href="#" class="self-end">[Trade Control]</a></li>
              </ul>
            </section>
          </sidebar-panel>
        </div>
      </template>

      <main-panel>
        <!-- If on planet display planet overview else display sector overview -->
        <sector-info />
      </main-panel>
    </GameUI>
</template>
