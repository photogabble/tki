<template>
  <pull-out :is-visible="isOpen">
    <bordered-heading><span class="text-white">How to play</span></bordered-heading>
    <div>
      <ol class="space-y-2 divide-y divide-solid divide-ui-orange-500 text-white">
        <li class="pt-2"><Link href="/help/introduction">Introduction to Space Mines</Link></li>
        <li class="pt-2"><Link href="/help/building-structures">Building structures</Link></li>
        <li class="pt-2"><Link href="/help/population-needs">What your population needs</Link></li>
        <li class="pt-2"><Link href="/help/resource-harvesting">Resource Harvesting</Link></li>
        <li class="pt-2"><Link href="/help/manufacturing">Manufacturing</Link></li>
        <li class="pt-2"><Link href="/help/marketplace">Buying and Selling</Link></li>
      </ol>
    </div>
  </pull-out>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import PullOut from "@/Components/atoms/modal/PullOut.vue";
import BorderedHeading from "@/Components/atoms/layout/BorderedHeading.vue";
import {useModal} from "@/Composables/useModal";


const {isOpen} = useModal('help');
</script>