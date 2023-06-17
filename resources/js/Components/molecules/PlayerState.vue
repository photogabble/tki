<template>
    <div class="border-b border-ui-orange-500 pb-0.5 grid grid-cols-5 grid-rows-2">
        <div class="col-span-2 border-ui-orange-500 border-partway-y aspect-square p-1">
            <div class="bg-ui-orange-500/25 h-full w-full relative">
                <small class="font-mono absolute top-1 left-1 text-ui-grey-900">X000.01</small>
<!--                <img src="~/assets/logo.png" alt="Space Mine's logo: A rocket zooms around a planet with three stars shining in the distance.">-->
            </div>
        </div>

        <div class="col-span-3 relative border-t border-ui-orange-500/50 pt-5 flex flex-col">
            <small class="font-mono absolute top-1 left-2 text-gray-100/25">X000.02</small>
            <p :class="['uppercase font-thin text-center mb-2', (isLoggedIn ? 'text-ui-yellow text-5xl' : 'text-ui-salmon text-[2.8rem]')]">
                {{ isLoggedIn ? 'Online' : 'Offline' }}
            </p>

            <div class="border-t border-ui-orange-500 ml-1 border-partway-y flex-grow flex flex-col justify-center" style="--border-part-t-height: 40%; --border-part-b-opacity:.5">
                <p class="text-3xl font-thin text-center">{{ isLoggedIn ? '22' : '00' }}d &middot; {{ isLoggedIn ? '12' : '00' }}h</p>
            </div>
        </div>

        <div class="relative aspect-square col-span-2 border-ui-orange-500 border-partway-tl border-partway-br mt-1 p-1" style="--border-part-t-height:25%; --border-part-b-height:50%; --border-part-b-opacity:.5">
            <small class="font-mono absolute top-1 left-1 text-gray-100/25 uppercase">K000.07{{ 'Unknown' }}</small>
<!--            <img v-if="isLoggedIn" :src="authUser.avatar" />-->
<!--            <img v-else src="~/assets/person.png" />-->
        </div>

        <div class="col-span-3 border-ui-orange-500 border-b p-2 uppercase space-y-2 flex flex-col">
            <template v-if="isLoggedIn">
                Player Stats...
            </template>
            <div class="ml-2" v-else>59 6F 75 20 73 68 6F 75 6C 64 20 70 6C 61 79 20 74 68 69 73 20 67 61 6D 65 00 00 00 00 00</div>
        </div>
    </div>
</template>

<script setup>
import {usePage} from "@inertiajs/vue3";
import {computed} from "vue";

const pageProps = usePage();
const isLoggedIn = computed<Boolean>(() => typeof pageProps.props.auth !== 'undefined');
</script>
