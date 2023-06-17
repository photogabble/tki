<template>
  <div class="p-10 flex flex-col h-screen w-screen justify-center items-center">
    <!-- Page Content -->
    <main class="w-full max-h-screen font-mono text-ui-orange-500 relative overflow-hidden">
      <!-- Modals -->
      <player-profile-panel />
      <help-panel />

      <!-- Modal backdrop -->
      <modal-backdrop />

      <div class="grid grid-cols-[1fr_64px] w-full h-full">
        <div class="pr-1">
          <!-- Middle Area -->
          <div class="border-partway-b w-full h-full flex flex-col" style="--border-part-b-height:12px;">
            <div class="h-8 flex flex-row border-t border-ui-orange-500/50 justify-between">
              <div class="border-t border-ui-orange-500 border-l border-partway-r p-1 px-2">
                <span class="uppercase text-white">{{ title }}</span> <span aria-hidden="true">X.001</span>
              </div>

              <div class="border-ui-orange-500 border-partway-t px-2 p-1">
                <span class="uppercase text-white"><help-icon/></span>
              </div>
            </div>

            <div class="p-2">
              <slot/>
            </div>

          </div>
        </div>
        <navigation-column/>
      </div>
    </main>
  </div>
</template>
<script setup lang="ts">
import PlayerProfilePanel from "@/Components/molecules/PlayerProfilePanel.vue";
import NavigationColumn from "@/Components/atoms/navigation/NavigationColumn.vue";
import ModalBackdrop from "@/Components/atoms/modal/ModalBackdrop.vue";
import HelpPanel from "@/Components/molecules/HelpPanel.vue";
import {useModal} from "@/Composables/useModal";
import HelpIcon from "@/Components/atoms/icons/HelpIcon.vue";

defineProps({
  title: String,
});

onMounted(() => {
  const {closeModal} = useModal();
  closeModal();
});
</script>

<style lang="postcss" scoped>
main {
  padding: 10px;
  border: 1px solid;
  border-radius: 3px;
  background: #0003;

  max-height: 1024px;
  height: 100vh;
  max-width: 1440px;
  margin: 0 auto;
}
</style>