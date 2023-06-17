<template>
  <div class="w-full h-full">
    <l-map ref="playerMap" :min-zoom="1" :max-zoom="6" :zoom="2" :center="[47.41322, -1.219482]" :use-global-leaflet="false">
      <l-tile-layer
          url="/api/map/{z}/{x}/{y}.png"
          layer-type="base"
          name="Geographic"
      />
    </l-map>
  </div>
</template>

<script setup lang="ts">
import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer } from "@vue-leaflet/vue-leaflet";
</script>