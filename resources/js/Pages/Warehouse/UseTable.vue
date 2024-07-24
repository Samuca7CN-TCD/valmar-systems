<script setup>
import { ref, computed } from 'vue'
import { defineProps } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import FloatButton from '@/Components/FloatButton.vue'
import Checkbox from '@/Components/Checkbox.vue'
import { Head, router } from '@inertiajs/vue3'
import ExtraOptionsButton from '@/Components/ExtraOptionsButton.vue'
import MultiSelect from 'primevue/multiselect'
import { formatDate } from '@/general.js'

const props = defineProps({
    'page': Object,
    'page_options': {
        type: Array,
        default: null,
    },
    'items': Array,
    'employees': Array,
    'itemCounts': Array,
    'selectedMonth': String,
})

const this_month = (formatDate()).substr(0, 7);
const date = ref(props.selectedMonth);
const separated_date = date.value.split();
const year = ref(separated_date[0]);
const month = ref(separated_date[1]);

const clearColumnHover = () => {
    // Remove a classe 'bg-neutral-100' de todos os elementos que têm essa classe
    document.querySelectorAll('.bg-neutral-100').forEach(el => {
        el.classList.remove('bg-neutral-100');
    });
}

const columnHover = (index) => {
    // Adiciona a classe 'bg-neutral-100' aos elementos específicos
    document.querySelectorAll(".use-table-col-" + index).forEach(el => {
        el.classList.add('bg-neutral-100');
    });
}

const handleMonthChange = (event) => {
    const selectedMonth = event.target.value;
    window.location.href = `/use-table/${selectedMonth}`;
}

const printList = () => {
    window.print()
}
</script>
<template>

    <Head :title="page.name" />
    <AppLayout :page="page" :page_options="page_options">
        <template #header class="print:hidden">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tabela de Usos
            </h2>
            <FloatButton icon="printer" @click="printList" title="Imprimir Lista" />
        </template>
        <div class="py-12 print:py-0">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 print:max-w-full print:mx-0 print:sm:px-0 print:lg:px-0">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg min-h-[300px] print:shadow-none">
                    <div class="flex flex-col gap-5 mx-auto w-full p-5 print:hidden">
                        <div class="text-center text-neutral-500 text-sm space-y-3">
                            <label for="select-month">Selecione o mês</label>
                            <input id="select-month" type="month" :max="this_month" :value="date"
                                class="w-52 mx-auto simple-input disabled:bg-gray-200" @change="handleMonthChange">
                        </div>
                    </div>
                    <div class="flex flex-col w-full">
                        <div class="overflow-x-auto print:overflow-hidden sm:-mx-6 lg:-mx-8">
                            <div class="inline-block min-w-full sm:px-6 lg:px-8">
                                <div class="overflow-hidden">
                                    <table class="min-w-full text-center text-sm font-light print:break-inside-avoid">
                                        <thead
                                            class="border-b bg-neutral-800 font-medium text-white dark:border-neutral-500 dark:bg-neutral-900">
                                            <tr class="">
                                                <th scope="col" class="px-6 py-2">Material</th>
                                                <th v-for="employee in employees" :key="employee.id"
                                                    :title="employee.name + ' ' + employee.surname">{{ employee.name }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody v-if="items.length && employees.length" class="print:break-inside-avoid">
                                            <tr v-for="item in items" :key="item.id"
                                                class="border-b transition duration-150 ease-in-out hover:bg-neutral-100 print:break-inside-avoid">
                                                <td class="px-6 py-2 text-right">{{ item.name }}</td>
                                                <td v-for="(qtd, index) in itemCounts[item.id]" :key="index"
                                                    :class="'use-table-col-' + index"
                                                    class="use-table-cols whitespace-nowrap px-6 py-2 hover:bg-neutral-200"
                                                    @mouseout="clearColumnHover" @mouseover="columnHover(index)">
                                                    {{ qtd ? qtd : '' }}
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tbody v-else>
                                            <tr
                                                class="border-b transition duration-300 ease-in-out hover:bg-neutral-200 dark:border-neutral-500 dark:hover:bg-neutral-600">
                                                <td colspan="5" class="whitespace-nowrap px-6 py-2 font-medium">Não há
                                                    items nas
                                                    categorias selecionadas</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>