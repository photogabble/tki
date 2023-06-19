<template>
  <div class="p-10 flex flex-col h-screen w-screen justify-center items-center">
    <!-- Page Content -->
    <main class="w-full max-h-screen font-mono text-ui-orange-500 relative overflow-hidden">
      <!-- Modals -->
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
        <div class="flex flex-col">
<!--          <PlayerState />-->
          <!-- TODO: if sidebar slot is available then col-span-2 on middle area -->
          <slot name="sidebar"/>
        </div>

        <!-- Middle Area -->
        <div class="w-full px-1 flex flex-col">
          <div class="text-sm flex flex-row border-t border-ui-orange-500/50 justify-between">
            <div class="border-t border-ui-orange-500 border-l border-partway-r p-1 px-2">
              <span class="uppercase text-white">Turns Available:</span> X.001
              <span class="text-ui-yellow">&middot;&nbsp;</span>
              <span class="uppercase text-white">Turns Used:</span> X.002
              <span class="text-ui-yellow">&middot;&nbsp;</span>
              <span class="uppercase text-white">Credits</span> X.002
            </div>

            <div class="border-ui-orange-500 border-partway-t px-2 p-1">
              <span class="uppercase text-white">Score</span> X.X
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
import PlayerState from "@/components/molecules/PlayerState.vue";
import PlayerProfilePanel from "@/components/molecules/PlayerProfilePanel.vue";
import NavigationColumn from "@/components/atoms/navigation/NavigationColumn.vue";
import ModalBackdrop from "@/components/atoms/modal/ModalBackdrop.vue";
import HelpPanel from "@/components/molecules/HelpPanel.vue";
import {TeleportTarget} from "vue-safe-teleport";
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