<script setup lang="ts">
import {ZonePermission} from "@/types/zone-info";

withDefaults(
    defineProps<{
      value?: ZonePermission;
    }>(),
    {
      value: 'N',
    }
);
</script>

<template>
  <span v-if="value === 'Y'" class="text-green-600">{{__('zoneinfo.l_zi_allow')}}</span>
  <span v-else-if="value === 'N'" class="text-red-600">{{__('zoneinfo.l_zi_notallow')}}</span>
  <span v-else-if="value === 'L'" class="text-ui-orange-500">{{__('zoneinfo.l_zi_limit')}}</span>
</template>
