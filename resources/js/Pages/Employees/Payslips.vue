<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import CreateUpdatePayModal from '@/Components/Payments/CreateUpdatePayModal.vue'
import FloatButton from '@/Components/FloatButton.vue'
import ExtraOptionsButton from '@/Components/ExtraOptionsButton.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { BanknotesIcon, MagnifyingGlassIcon, PencilIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import { toMoney } from '@/general.js'


const props = defineProps({
    page: Object,
    page_options: Array,
    employee: {
        type: Object,
        default: null,
    }
})

const printList = () => {
    window.print()
}
</script>
<template>

    <Head :title="page.name" />
    <AppLayout :page="page" :page_options="page_options">
        <template #header class="print:hidden">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Holerite
            </h2>
            <FloatButton icon="printer" @click="printList" title="Imprimir Lista" />
        </template>
        <div class="py-12 print:py-0">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 print:max-w-full print:mx-0 print:sm:px-0 print:lg:px-0">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg min-h-[300px] print:shadow-none">
                    Olá Holerite
                </div>
            </div>
        </div>
    </AppLayout>
</template>