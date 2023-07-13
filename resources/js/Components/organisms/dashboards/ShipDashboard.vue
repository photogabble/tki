<script setup lang="ts">
/**
 * ShipDashboard.vue from The Kabal Invasion.
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
 */

import NavigationReportPopup from "@/Components/organisms/NavigationReportPopup.vue";
import ZoneInfoPopup from "@/Components/organisms/ZoneInfoPopup.vue";
import TextButton from "@/Components/atoms/form/TextButton.vue";
import {DashboardPageProps} from "@/types/dashboard";
import {useAuth} from "@/Composables/useAuth";
import {usePage} from "@inertiajs/vue3";
import {computed, ref} from "vue";

const displayingZoneInfo = ref(false);
const { sector, presets, ship, user } = useAuth();
const { navigation, route, encounters } = usePage<DashboardPageProps>().props;
const navcomVisible = ref<boolean>(typeof route !== 'undefined' && route.remaining > 0);
const autopilotStatus = computed(() => {
  if (navcomVisible.value) return 'Autopilot Online';
  if (route) return 'Autopilot Paused';
  return 'Autopilot Offline';
});
</script>

<template>
  <section v-if="user?.ship">
    <navigation-report-popup v-model="navcomVisible" v-if="encounters.length === 0" :route="route" />
    <zone-info-popup v-model="displayingZoneInfo" :zone="user.ship.sector.zone" />

    <div class="w-full h-8 flex justify-end items-center pr-2 space-x-2">
      <text-button v-if="route && route.remaining > 0" @click="navcomVisible = true" class="text-green-600">[ {{autopilotStatus}} ]</text-button>
      <span>Sector {{ user.ship.sector_id }}</span>&nbsp;in&nbsp;<button class="text-yellow-500 underline" @click="displayingZoneInfo=true">{{ user.ship.sector.zone.name }}</button>
    </div>

    <pre>{{ route }}</pre>
  </section>
</template>