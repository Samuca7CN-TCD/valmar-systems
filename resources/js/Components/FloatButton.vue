<script setup>
import { PlusIcon, PrinterIcon } from '@heroicons/vue/24/outline';
const props = defineProps({
    'fb_href': {
        type: String,
        default: "",
    },

    'icon': String,
})
</script>
<template>
    <!--
    <a :href="fb_href" class="w-14 h-14 bg-red-500 rounded-full absolute right-10 cursor-pointer">
        <PlusIcon class="text-white w-8 h-8 m-3" />
    </a>
    -->

    <div class="bg-red-500 rounded-full absolute right-10 cursor-pointer p-3 hover:bg-red-600 active:bg-red-500">
        <PlusIcon v-if="icon == 'plus'" class="text-white w-7 h-7" />
        <PrinterIcon v-if="icon == 'printer'" class="text-white w-7 h-7" />
    </div>
</template>