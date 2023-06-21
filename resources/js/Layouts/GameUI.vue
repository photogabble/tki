<template>
  <div class="p-10 flex flex-col h-screen w-screen justify-center items-center">
    <!-- Page Content -->
    <main class="w-full max-h-screen font-mono text-ui-orange-500 relative overflow-hidden">
      <!-- Modals -->
      <ship-report-panel />
      <player-profile-panel />
      <help-panel />

      <teleport-target id="modal-target" />

      <!--
        TODO: Add MOTD that displays once each fresh login or once a day. This can show player updates
              any news items and message from admins. Very much like the "It is a New Day!" from
              improbable island.
      -->

      <!-- Modal backdrop -->
      <modal-backdrop />

      <div class="grid grid-cols-[350px_minmax(350px,_1fr)_64px] w-full h-full">
        <!-- Left Sidebar -->
        <div v-if="slots.sidebar" class="flex flex-col h-full overflow-hidden">
          <slot name="sidebar"/>
        </div>

        <!-- Middle Area -->
        <div :class="['w-full flex flex-col', {'col-span-2 pr-1': !slots.sidebar, 'px-1': slots.sidebar}]">
          <div class="text-sm flex flex-row border-t border-ui-orange-500/50 justify-between">
            <div class="border-t border-ui-orange-500 border-l border-partway-r p-1 px-2">
              <span class="uppercase text-white">Turns Available:</span> {{ isLoggedIn ? numberFormatter(user.turns) : 'X.001' }}
              <span class="text-ui-yellow">&middot;&nbsp;</span>
              <span class="uppercase text-white">Turns Used:</span> {{ isLoggedIn ? numberFormatter(user.turns_used) : 'X.002' }}
              <span class="text-ui-yellow">&middot;&nbsp;</span>
              <span class="uppercase text-white">Credits</span> {{ isLoggedIn ? numberFormatter(user.credits) : 'X.003' }}
            </div>

            <div class="border-ui-orange-500 border-partway-t px-2 p-1">
              <span class="uppercase text-white">Score</span> {{ isLoggedIn ? numberFormatter(user.score) : 'X.X' }}
            </div>
          </div>
          <slot/>
        </div>
        <navigation-column/>
      </div>
    </main>
  </div>
</template>
<script setup lang="ts">
import PlayerProfilePanel from "@/components/molecules/PlayerProfilePanel.vue";
import NavigationColumn from "@/components/atoms/navigation/NavigationColumn.vue";
import ModalBackdrop from "@/components/atoms/modal/ModalBackdrop.vue";
import HelpPanel from "@/components/molecules/HelpPanel.vue";
import {TeleportTarget} from "vue-safe-teleport";
import {useAuth} from "@/Composables/useAuth";
import {useFormattedNumber} from "@/Composables/useFormattedNumber";
import ShipReportPanel from "@/Components/molecules/ShipReportPanel.vue";
import {useSlots} from "vue";

const {isLoggedIn, user} = useAuth();
const numberFormatter = useFormattedNumber();
const slots = useSlots();
</script>

<style lang="postcss" scoped>
main {
  padding: 10px;
  border: 1px solid;
  border-radius: 3px;
  background: rgba(0, 0, 0, 0.75);
  z-index: 0;

  max-height: 1024px;
  height: 100vh;
  max-width: 1440px;
  margin: 0 auto;
}
</style>