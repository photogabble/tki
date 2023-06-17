<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import GameUI from "@/Layouts/GameUI.vue";
import SidebarPanel from "@/Components/atoms/layout/SidebarPanel.vue";
import MainPanel from "@/Components/atoms/layout/MainPanel.vue";
</script>

<template>
    <Head title="Dashboard" />

    <GameUI>
      <template #sidebar>
        <sidebar-panel>
          <template #heading>
            <span class="text-white">XX.501 &middot;</span> XXXX.XXXXXX
          </template>
        </sidebar-panel>
      </template>

      <main-panel centered>
        &rarr;&nbsp;<span class="text-white">XXX.501</span>&nbsp;&mdash; Implement Layout &larr;
      </main-panel>
    </GameUI>
</template>
