<script setup lang="ts">
/**
 * galaxy.php from The Kabal Invasion.
 * The Kabal Invasion is a Free & Opensource (FOSS), web-based 4X space/strategy game.
 *
 * @copyright 2020 The Kabal Invasion development team, Ron Harwood, and the BNT development team
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
 */
import NavigationConfirmPopup from "@/Components/organisms/NavigationConfirmPopup.vue";
import PaginationPrevNext from "@/Components/atoms/pagination/PaginationPrevNext.vue";
import type {SectorResourceWithPlayerMeta} from "@/types/resources/sector";
import SectionHeader from "@/Components/atoms/layout/SectionHeader.vue";
import type {GalaxyOverviewPageProps} from "@/types/galaxy-overview";
import MainPanel from "@/Components/atoms/layout/MainPanel.vue";
import MapTile from "@/Components/atoms/MapTile.vue";
import {usePage, router} from "@inertiajs/vue3";
import GameUI from "@/Layouts/GameUI.vue";
import {computed, ref} from "vue";

const hoveringSector = ref<SectorResourceWithPlayerMeta>({id:0} as SectorResourceWithPlayerMeta);
const {sectors, rsMove, navCom} = usePage<GalaxyOverviewPageProps>().props;
const realSpaceOpen = ref(rsMove !== undefined);
const map = ref(null);
const columns = 50;

const tiles = computed(() => {
    const rows : Array<Array<SectorResourceWithPlayerMeta>> = [];
    let cols : Array<SectorResourceWithPlayerMeta> = [];

    for (const sector of sectors.data) {
        cols.push(sector);
        if (cols.length === columns) {
            rows.push(cols);
            cols = [];
        }
    }
    return rows;
});

/**
 * Triggers Inertia::lazy to compute rsMove and return, that value is then used to open a modal
 * in order to display the RealSpace Move info and ask the player if they wish to navigate.
 *
 * @param sector
 */
const computeRsMove = (sector: number) => {
    router.visit(route('explore', {sector}), {
        only: ['rsMove'],
    })
}
</script>

<template>
  <GameUI>
    <navigation-confirm-popup v-model="realSpaceOpen" :rs-move="rsMove" :nav-com="navCom" />
    <main-panel>
      <section ref="map" class="flex flex-col justify-center items-center h-full">
        <div class="border-4 border-double border-ui-orange-500/50 ">
          <section-header class="mb-4">
            <template #actions>
              <pagination-prev-next :pagination="sectors" />
            </template>
            <span class="text-white">{{ __('galaxy.l_map_title') }}</span>
          </section-header>

          <div v-if="tiles.length > 0" v-for="(row, r) in tiles" :key="`row-${r}`" class="flex px-4">
            <button
                v-for="column in row"
                :class="['border-2 hover:border-white', {
                'border-transparent': !column.is_current_sector,
                'border-green-600': column.is_current_sector,
              }]"
                :key="`sector-${column.id}`"
                :aria-label="`${__('main.l_sector')}: ${column.id} - `"
                @mouseenter="hoveringSector = column"
                @click="computeRsMove(column.id)"
            >
              <map-tile :type="column.port_type" :aria-hidden="true" />
            </button>
            <span :class="['text-sm w-8 ml-2 block text-left', {
            'text-white': (hoveringSector.id >= (r * columns) && hoveringSector.id <= (r * columns))
          }]">{{ columns + r * columns }}</span>
          </div>

          <div class="flex w-full px-4">
            <span>Sector: {{ hoveringSector.id }} - {{ hoveringSector.port_type }}</span>
          </div>
        </div>

        <div class="mt-4 space-x-4 flex">
          <span class="flex items-center">
            <img src="../../../images/map-tiles/port-ore.png" class="mr-2"
                 :alt="`${__('main.l_port')}:  ${__('galaxy.l_ore_port')}`"/> {{ __('galaxy.l_ore_port') }}
            </span>
          <span class="flex items-center">
            <img src="../../../images/map-tiles/port-organics.png" class="mr-2"
                 :alt="`${__('main.l_port')}:  ${__('galaxy.l_organics_port')}`"/> {{ __('galaxy.l_organics_port') }}
            </span>
          <span class="flex items-center">
            <img src="../../../images/map-tiles/port-energy.png" class="mr-2"
                 :alt="`${__('main.l_port')}:  ${__('galaxy.l_energy_port')}`"/> {{ __('galaxy.l_energy_port') }}
            </span>
          <span class="flex items-center">
            <img src="../../../images/map-tiles/port-goods.png" class="mr-2"
                 :alt="`${__('main.l_port')}:  ${__('galaxy.l_goods_port')}`"/> {{ __('galaxy.l_goods_port') }}
            </span>
          <span class="flex items-center">
            <img src="../../../images/map-tiles/none.png" class="mr-2"
                 :alt="`${__('main.l_port')}:  ${__('galaxy.l_no_port')}`"/> {{ __('galaxy.l_no_port') }}
            </span>
          <span class="flex items-center">
            <img src="../../../images/map-tiles/unknown.png" class="mr-2"
                 :alt="`${__('main.l_port')}:  ${__('galaxy.l_unexplored')}`"/> {{ __('galaxy.l_unexplored') }}
          </span>
        </div>
      </section>
    </main-panel>
  </GameUI>
</template>