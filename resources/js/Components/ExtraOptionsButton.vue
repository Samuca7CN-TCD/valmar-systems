<script setup>
import { ArrowUpIcon, ArrowUturnLeftIcon, PrinterIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    mode: {
        type: Array,
        default: ['rollup'],
    },
    link_type: {
        type: Array,
        default: [],
    },
    link: {
        type: Array,
        default: [],
    },
})

const backToTop = () => {
    window.scrollTo(0, 0);
}

const printPage = () => {
    window.print()
}

</script>
<template>
    <div class="fixed bottom-5 right-5 flex-col-config space-y-1 print:hidden">
        <div v-if="mode.includes('link')" class="flex-col-config space-y-1">
            <div v-for="(link_item, index) in link_type" :key="index">
                <a v-if="['previous', 'payments'].includes(link_item)" :href="link[index]">
                    <div
                        class="p-3 rounded-full cursor-pointer select-none bg-gray-900 hover:bg-gray-800 active:bg-gray-700">
                        <ArrowUturnLeftIcon class="w-5 h-5 text-white" />
                    </div>
                </a>
            </div>
        </div>
        <div v-if="mode.includes('print_page')"
            class="bg-orange-700 p-3 rounded-full cursor-pointer select-none hover:bg-orange-800 active:bg-orange-700"
            title="Imprimir Página" @click="printPage()">
            <PrinterIcon class="w-5 h-5 text-white" />
        </div>
        <div v-if="mode.includes('rollup')"
            class="bg-gray-700 p-5 rounded-full cursor-pointer select-none hover:bg-gray-800 active:bg-gray-700"
            title="Voltar ao topo da página" @click="backToTop()">
            <ArrowUpIcon class="w-5 h-5 text-white" />
        </div>
    </div>
</template>