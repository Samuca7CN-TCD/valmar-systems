<script setup>
    import { ref, onMounted, watch } from 'vue'
    import AppLayout from '@/Layouts/AppLayout.vue';
    import { Head, useForm } from '@inertiajs/vue3';
    import SeeRecords from '@/Components/Dashboard/SeeRecords.vue'
    import ExtraOptionsButton from '@/Components/ExtraOptionsButton.vue'
    import { formatDate, toMoney } from '@/general.js';
    import Chart from 'chart.js/auto';
    import Grafico from './Dashboard/Grafico.vue';
    import Almoxarifado from './Dashboard/Almoxarifado.vue';
    import Financeiro from './Dashboard/Financeiro.vue'; // RENOMEADO 
    import Servico from './Dashboard/Servico.vue'; // NOVO COMPONENTE (Antigo ServicesAndBudgets) 
    import Atividades from './Dashboard/Atividades.vue';

    const props = defineProps({
        page: Object,
        title: String,
        procedures: Array,
        parameters: Object,
        users: Array,
        actions: Array,
        departments: Array,
        // TODAS AS OUTRAS PROPS DE DADOS FORAM REMOVIDAS DAQUI
    })

    const modal = ref(false);
    const selected_procedure = ref(null);
    const activeTab = ref('geral'); // 'geral', 'almoxarifado', 'financeiro', 'servico' 

    const openModal = (procedure) => {
        selected_procedure.value = procedure;
        modal.value = true;
    }

    const closeModal = () => {
        selected_procedure.value = null;
        modal.value = false;
    }

    // Heritage total amount agora precisa ser calculado com base em dados carregados assincronamente ou removido do header
    // Por simplicidade, vamos remover do header por enquanto, ou o 'Grafico' precisaria emitir o total.
    // Ou, se os "current amounts" forem carregados na tab 'geral', poderíamos ter um prop reativo no Dashboard.
    // Para manter a simplicidade, e já que a intenção é descarregar o index, vou remover por agora.
    /*
    const heritage_total_amount = () => {
        return props.amount_values.warehouse + props.amount_values.payments + props.amount_values.services
    }
    */

    // Um estado reativo para o total geral pode ser necessário, se você quiser manter isso no cabeçalho
    const totalHeritage = ref(0);

    // O componente Grafico será responsável por emitir este total
    // ou o Dashboard fará uma requisição separada para um total leve.
    // Por enquanto, vamos simplificar o header.
</script>

<template>

    <Head :title="page.name" />

    <AppLayout :page="page">

        <template #header>

            <h2 class="font-semibold text-xl text-gray-800 leading-tight">

                {{ page.name }} </h2>

        </template>
        <div class="py-12">
            <div class="min-w-7xl max-w-fit mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                    <div class="hidden md:flex border-b border-gray-200 mb-6">
                        <button @click="activeTab = 'geral'"
                            :class="{ 'border-b-2 border-teal-500 text-teal-600 font-semibold': activeTab === 'geral', 'text-gray-600': activeTab !== 'geral' }"
                            class="py-2 px-4 text-center focus:outline-none hover:text-teal-600 hover:border-teal-500 transition duration-150 ease-in-out">
                            Geral
                        </button>
                        <button @click="activeTab = 'almoxarifado'"
                            :class="{ 'border-b-2 border-teal-500 text-teal-600 font-semibold': activeTab === 'almoxarifado', 'text-gray-600': activeTab !== 'almoxarifado' }"
                            class="py-2 px-4 text-center focus:outline-none hover:text-teal-600 hover:border-teal-500 transition duration-150 ease-in-out">
                            Almoxarifado
                        </button>
                        <button @click="activeTab = 'financeiro'"
                            :class="{ 'border-b-2 border-teal-500 text-teal-600 font-semibold': activeTab === 'financeiro', 'text-gray-600': activeTab !== 'financeiro' }"
                            class="py-2 px-4 text-center focus:outline-none hover:text-teal-600 hover:border-teal-500 transition duration-150 ease-in-out">
                            Financeiro
                        </button>
                        <button @click="activeTab = 'servico'"
                            :class="{ 'border-b-2 border-teal-500 text-teal-600 font-semibold': activeTab === 'servico', 'text-gray-600': activeTab !== 'servico' }"
                            class="py-2 px-4 text-center focus:outline-none hover:text-teal-600 hover:border-teal-500 transition duration-150 ease-in-out">
                            Serviços & Orçamentos
                        </button>
                    </div>

                    <div class="md:hidden mb-6">
                        <label for="dashboard-tab-select" class="sr-only">Selecionar Aba</label>
                        <select id="dashboard-tab-select" v-model="activeTab"
                            class="block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                            <option value="geral">Geral</option>
                            <option value="almoxarifado">Almoxarifado</option>
                            <option value="financeiro">Financeiro</option>
                            <option value="servico">Serviços & Orçamentos</option>
                        </select>
                    </div>

                    <div>
                        <div v-show="activeTab === 'geral'">
                            <Grafico />
                            <Atividades :procedures="procedures" :parameters="parameters" :users="users"
                                :actions="actions" :departments="departments" />
                        </div>

                        <div v-show="activeTab === 'almoxarifado'">
                            <Almoxarifado />
                        </div>

                        <div v-show="activeTab === 'financeiro'">
                            <Financeiro />
                        </div>

                        <div v-show="activeTab === 'servico'">
                            <Servico />
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <ExtraOptionsButton :mode="['rollup', 'print_page']" />
        <SeeRecords :modal="modal" :procedure="selected_procedure" @close="closeModal" />
    </AppLayout>
</template>