<script setup lang="ts">
import {usePage, Link} from "@inertiajs/vue3";
import type {RankingPageProps} from "@/types/ranking";
import SectionHeader from "@/Components/atoms/layout/SectionHeader.vue";
</script>

<template>
  <section>
    <section-header>
      <template #actions>
        TODO
      </template>
      <span class="text-white">Teams Ranking</span>
    </section-header>

    <table class="w-full">
      <thead>
      <tr class="border-b">
        <th class="p-1 text-left text-white">Rank</th>
        <th class="p-1 text-left text-white">Score</th>
        <th class="p-1 text-left text-white">Team Name</th>
        <th class="p-1 text-left text-white"># Players</th>
        <th class="p-1 text-left text-white">Last Login</th>
        <th class="p-1 text-left text-white">Good/Evil</th>
        <th class="p-1 text-left text-white">Eff. Rating</th>
      </tr>
      </thead>
      <tbody>
      </tbody>
    </table>

  </section>
</template>