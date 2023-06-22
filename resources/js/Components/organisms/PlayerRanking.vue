<script setup lang="ts">
import {usePage, Link} from "@inertiajs/vue3";
import type {RankingPageProps, PlayerRankingParams} from "@/types/resources/ranking";
import SectionHeader from "@/Components/atoms/layout/SectionHeader.vue";
import ColumnSortLink from "@/Components/molecules/ColumnSortLink.vue";
import PaginationPrevNext from "@/Components/atoms/pagination/PaginationPrevNext.vue";

const {ranking, sorts, sorting_by, sorting_direction}  = usePage<RankingPageProps>().props.player;
const linkParams = (s: string) => {
  const ret : PlayerRankingParams = {sort_players_by: s};
  if (sorting_by === s) {
    ret.sort_players_direction = sorting_direction === 'ASC'
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
        <pagination-prev-next :pagination="ranking" />
      </template>
      <span class="text-white">Players Ranking</span>
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
          <th class="p-1 text-left">Player</th>
          <th class="p-1 text-left text-white">
            <column-sort-link :href="route('ranking', linkParams('turns'))" :is-sorting="sorting_by === 'turns'" :direction="sorting_direction">
              Turns Used
            </column-sort-link>
          </th>
          <th class="p-1 text-left text-white">
            <column-sort-link :href="route('ranking', linkParams('login'))" :is-sorting="sorting_by === 'login'" :direction="sorting_direction">
              Last Login
            </column-sort-link>
          </th>
          <th class="p-1 text-left text-white">
            <Link :href="route('ranking', {sort_players_by: 'good'})" :class="`${sorting_by === 'good' ? 'text-ui-yellow' : 'hover:text-ui-yellow' }`">Good</Link>/<Link :href="route('ranking', {sort_players_by: 'bad'})" :class="`${sorting_by === 'bad' ? 'text-ui-yellow' : 'hover:text-ui-yellow' }`">Evil</Link>
          </th>
          <th class="p-1 text-left text-white">
            <column-sort-link :href="route('ranking', linkParams('efficiency'))" :is-sorting="sorting_by === 'efficiency'" :direction="sorting_direction">
              Eff. Rating
            </column-sort-link>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="player in ranking.data">
          <td class="p-1">{{ (ranking.meta.per_page * (ranking.meta.current_page-1)) + (idx+1) }}</td>
          <td class="p-1">{{ player.score }}</td>
          <td class="p-1">{{ player.insignia }} {{ player.name }}</td>
          <td class="p-1">{{ player.turns_used }}</td>
          <td class="p-1">{{ player.last_login.nice }}</td>
          <td :class="['p-1', {'text-red-600': player.rating < 0, 'text-green-600': player.rating > 0}]">{{ player.rating }}</td>
          <td class="p-1">{{player.efficiency}}</td>
        </tr>
      </tbody>
    </table>

    <p>Total number of players: {{ ranking.meta.total }}. Players with destroyed ships are not counted.</p>
  </section>
</template>