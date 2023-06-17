<template>
  <div>
    <section-header>
      <template #actions>
        <button @click="display='table'" :class="`${display === 'table' ? 'text-ui-yellow' : 'hover:text-ui-yellow' }`">Table</button> | <button @click="display='graph'" :class="`${display === 'graph' ? 'text-ui-yellow' : 'hover:text-ui-yellow' }`">Graph</button>
      </template>
      {{ title }} <span class="text-white">Market Orders</span>
    </section-header>

    <div v-if="display === 'graph'">Graph</div>
    <div v-if="display === 'table'">Table</div>

  </div>
</template>
<script setup lang="ts">
import SectionHeader from "@/Components/atoms/layout/SectionHeader.vue";
defineProps({
  title: String,
});

const display = ref<'table'|'graph'>('table');

</script>