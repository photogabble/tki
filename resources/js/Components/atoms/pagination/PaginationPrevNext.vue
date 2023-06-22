<script setup lang="ts">
import {Link} from "@inertiajs/vue3";
import {PaginatedResource} from "@/types/laravel/pagination";
defineProps<{
  pagination: PaginatedResource<unknown>;
}>();
</script>

<template>
  <span class="mr-2">Page {{ pagination.meta.current_page }}/{{ pagination.meta.last_page }}</span>
  <nav class="space-x-2" v-if="pagination.meta.last_page > 1">
    <Link v-for="link of pagination.meta.links" :class="`${link.active ? 'text-ui-yellow' : 'hover:text-ui-yellow' }`" :href="link.url" v-html="link.label"/>
  </nav>
</template>