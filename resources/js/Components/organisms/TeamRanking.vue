<script setup lang="ts">
import {usePage, Link} from "@inertiajs/vue3";
import type {RankingPageProps} from "@/types/resources/ranking";
import SectionHeader from "@/Components/atoms/layout/SectionHeader.vue";
import {TeamRankingParams} from "@/types/resources/ranking";
import ColumnSortLink from "@/Components/molecules/ColumnSortLink.vue";

const {ranking, sorts, sorting_by, sorting_direction}  = usePage<RankingPageProps>().props.team;
const linkParams = (s: string) => {
  const ret : TeamRankingParams = {sort_teams_by: s};
  if (sorting_by === s) {
    ret.sort_teams_direction = sorting_direction === 'ASC'
        ? 'DESC'
        : 'ASC'
  }

  return ret;
}
</script>

<template>
  <section>
    <section-header>
      <template #actions>
        <span class="mr-2">Page {{ ranking.meta.current_page }}/{{ ranking.meta.last_page }}</span>
        <nav class="space-x-2" v-if="ranking.meta.last_page > ranking.meta.current_page">
          <Link v-for="link of ranking.meta.links" :class="`${link.active ? 'text-ui-yellow' : 'hover:text-ui-yellow' }`" :href="link.url" v-html="link.label"/>
        </nav>
      </template>
      <span class="text-white">Teams Ranking</span>
    </section-header>

    <table class="w-full">
      <thead>
      <tr class="border-b">
        <th class="p-1 text-left">Rank</th>
        <th class="p-1 text-left text-white">
          <column-sort-link :href="route('ranking', linkParams('score'))" :is-sorting="sorting_by === 'score'" :direction="sorting_direction">
            Score
          </column-sort-link>
        </th>
        <th class="p-1 text-left">Team Name</th>
        <th class="p-1 text-left text-white">
          <column-sort-link :href="route('ranking', linkParams('members'))" :is-sorting="sorting_by === 'members'" :direction="sorting_direction">
            # Players
          </column-sort-link>
        </th>
        <th class="p-1 text-left text-white">
          <Link :href="route('ranking', {sort_teams_by: 'good'})" :class="`${sorting_by === 'good' ? 'text-ui-yellow' : 'hover:text-ui-yellow' }`">Good</Link>/<Link :href="route('ranking', {sort_teams_by: 'bad'})" :class="`${sorting_by === 'bad' ? 'text-ui-yellow' : 'hover:text-ui-yellow' }`">Evil</Link>
        </th>
        <th class="p-1 text-left text-white">
          <column-sort-link :href="route('ranking', linkParams('efficiency'))" :is-sorting="sorting_by === 'efficiency'" :direction="sorting_direction">
            Eff. Rating
          </column-sort-link>
        </th>
      </tr>
      </thead>
      <tbody>
        <tr v-for="(team, idx) in ranking.data">
          <td class="p-1">{{ (ranking.meta.per_page * (ranking.meta.current_page-1)) + (idx+1) }}</td>
          <td class="p-1">{{ team.score }}</td>
          <td class="p-1">{{ team.name }}</td>
          <td class="p-1">{{ team.player_count }}</td>
          <td :class="['p-1', {'text-red-600': team.rating < 0, 'text-green-600': team.rating > 0}]">{{ team.rating }}</td>
          <td class="p-1">{{ team.efficiency }}</td>
        </tr>
      </tbody>
    </table>

  </section>
</template>