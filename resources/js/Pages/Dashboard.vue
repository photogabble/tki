<script setup lang="ts">
/**
 * main.php from The Kabal Invasion.
 * The Kabal Invasion is a Free & Opensource (FOSS), web-based 4X space/strategy game.
 *
 * @copyright 2023 Simon Dann, The Kabal Invasion development team, Ron Harwood, and the BNT development team
 *
 * @license GNU AGPL version 3.0 or (at your option) any later version.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * ---
 *
 * This component is the result of refactoring the legacy main.php. It deals with
 * displaying information of the sector the player is currently in.
 *
 * If the player has landed on a planet or structure (future enhancement) then
 * the sub-component `<planet-info>` or `<structure-info>` is displayed else
 * `<sector-info>` is displayed.
 *
 * main.php handled a lot of functionality which I have refactored into multiple
 * components as well as backend functions.
 */
import {Head, usePage} from '@inertiajs/vue3';
import GameUI from "@/Layouts/GameUI.vue";
import SidebarPanel from "@/Components/atoms/layout/SidebarPanel.vue";
import MainPanel from "@/Components/atoms/layout/MainPanel.vue";
import SectorInfo from "@/Components/organisms/SectorInfo.vue";
import {useAuth} from "@/Composables/useAuth";
import SectorWarpsPanel from "@/Components/organisms/SectorWarpsPanel.vue";
import PlayerPresetsPanel from "@/Components/organisms/PlayerPresetsPanel.vue";
import ShipCargoPanel from "@/Components/organisms/ShipCargoPanel.vue";
import PlayerShipPanel from "@/Components/organisms/PlayerShipPanel.vue";
import {DashboardPageProps} from "@/types/dashboard";
import NavigationReportPopup from "@/Components/organisms/NavigationReportPopup.vue";
import {ref} from "vue";
import EncounterPopup from "@/Components/organisms/EncounterPopup.vue";

const { sector, presets, ship } = useAuth();
const { navigation, route, encounters } = usePage<DashboardPageProps>().props

const hasNavigated = ref<boolean>(Boolean(navigation));

</script>

<template>
    <Head title="Dashboard" />

    <GameUI>
      <template #sidebar>
        <player-ship-panel v-if="ship" :ship="ship" />

        <div class="flex flex-col flex-grow overflow-y-scroll max-h-full space-y-2 mt-2">
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
        <navigation-report-popup v-model="hasNavigated" />
        <encounter-popup v-model="encounters" />

        <div v-if="navigation">
          Nav
          <pre>{{ route }}</pre>
        </div>

      </main-panel>
    </GameUI>
</template>
