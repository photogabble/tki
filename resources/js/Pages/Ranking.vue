<script setup lang="ts">
import GameUI from "@/Layouts/GameUI.vue";
import MainPanel from "@/Components/atoms/layout/MainPanel.vue";
import PlayerRanking from "@/Components/organisms/PlayerRanking.vue";
import TeamRanking from "@/Components/organisms/TeamRanking.vue";

</script>

<template>
  <GameUI>
    <main-panel class="grid grid-rows-2 divide-y-4 divide-double divide-ui-orange-500/50">
      <player-ranking />
      <team-ranking />
    </main-panel>
  </GameUI>
</template>