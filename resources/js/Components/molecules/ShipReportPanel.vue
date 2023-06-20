<template>
  <pull-out-left :is-visible="isOpen">
    <bordered-heading><span class="text-white">{{ __('report.l_ship_levels')}}</span></bordered-heading>
    <ul class="mb-4">
      <li class="flex"><span class="flex-grow">{{ __('report.l_hull') }}</span> Lv {{ship.fitting.hull}}</li>
      <li class="flex"><span class="flex-grow">{{ __('report.l_engines') }}</span> Lv {{ship.fitting.engines}}</li>
      <li class="flex"><span class="flex-grow">{{ __('report.l_power') }}</span> Lv {{ship.fitting.power}}</li>
      <li class="flex"><span class="flex-grow">{{ __('report.l_computer') }}</span> Lv {{ship.fitting.computer}}</li>
      <li class="flex"><span class="flex-grow">{{ __('report.l_sensors') }}</span> Lv {{ship.fitting.sensors}}</li>
      <li class="flex"><span class="flex-grow">{{ __('report.l_armor') }}</span> Lv {{ship.fitting.armor}}</li>
      <li class="flex"><span class="flex-grow">{{ __('report.l_shields') }}</span> Lv {{ship.fitting.shields}}</li>
      <li class="flex"><span class="flex-grow">{{ __('report.l_beams') }}</span> Lv {{ship.fitting.beams}}</li>
      <li class="flex"><span class="flex-grow ">{{ __('report.l_torp_launch') }}</span> Lv {{ship.fitting.torp_launchers}}</li>
      <li class="flex"><span class="flex-grow">{{ __('report.l_cloak') }}</span> Lv {{ship.fitting.cloak}}</li>
      <li class="flex border-t border-t-ui-orange-500 mt-3 pt-2"><span class="flex-grow">{{ __('report.l_shipavg') }}</span> Lv {{ ship.avg_fitting_level }}</li>
    </ul>

    <bordered-heading><span class="text-white">{{ __('report.l_arm_weap')}}</span></bordered-heading>
    <ul class="mb-4">
      <li class="flex"><span class="flex-grow">{{__('common.l_armorpts')}}:</span> {{ ship.armor.current }}/{{ ship.armor.max }}</li>
      <li class="flex"><span class="flex-grow">{{__('common.l_fighters')}}:</span> {{ship.weapons.fighters.current}}/{{ship.weapons.fighters.max}}</li>
      <li class="flex"><span class="flex-grow">{{__('common.l_torps')}}:</span> {{ship.weapons.torpedoes.current}}/{{ship.weapons.torpedoes.max}}</li>
    </ul>

    <bordered-heading><span class="text-white">{{ __('device.l_device_title')}}</span></bordered-heading>
    <ul>
      <li class="flex"><span class="flex-grow">{{ __('report.l_beacons')}}</span> {{ ship.devices.beacon }}</li>
      <li class="flex"><span class="flex-grow">{{ __('report.l_warpedit')}}</span> {{ ship.devices.warp_edit }}</li>
      <li class="flex"><span class="flex-grow">{{ __('report.l_genesis')}}</span> {{ ship.devices.genesis }}</li>
      <li class="flex"><span class="flex-grow">{{ __('report.l_deflect')}}</span> {{ ship.devices.mine_deflector }}</li>
      <li class="flex"><span class="flex-grow">{{ __('report.l_ewd')}}</span> {{ ship.devices.emergency_warp }}</li>

      <li class="flex"><span class="flex-grow">{{ __('report.l_escape_pod')}}</span>{{ ship.devices.escape_pod_installed}}</li>
      <li class="flex"><span class="flex-grow">{{ __('report.l_fuel_scoop')}}</span>{{ ship.devices.fuel_scoop_installed}}</li>
      <li class="flex"><span class="flex-grow">{{ __('device.l_lssd')}}</span>{{ ship.devices.lssd_installed}}</li>
    </ul>
  </pull-out-left>
</template>

<script setup lang="ts">
import BorderedHeading from "@/Components/atoms/layout/BorderedHeading.vue";
import {useModal} from "@/Composables/useModal";
import PullOutLeft from "@/Components/atoms/modal/PullOutLeft.vue";
import {useAuth} from "@/Composables/useAuth";
const {isOpen} = useModal('ship-report');
const {ship} = useAuth();

</script>