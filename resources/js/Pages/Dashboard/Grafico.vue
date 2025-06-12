<script setup>
    import { ref, onMounted } from 'vue'
    import { toMoney } from '@/general.js';
    import Chart from 'chart.js/auto';
    import axios from 'axios'; // Importar axios

    // Remover props, os dados serão carregados internamente
    // const props = defineProps({ /* REMOVIDO */ });

    const amount_values = ref({
        warehouse: 0,
        payments: 0,
        services: 0,
    });
    const weekly_amount_values = ref({
        weekly_payments: {},
        weekly_services: {},
        weekly_warehouse: {}
    });

    // Instâncias dos gráficos
    let heritageChartInstance = null;
    let weeklyHeritageChartInstance = null;

    // Função para buscar os dados
    const fetchGeneralData = async () => {
        try {
            const response = await axios.get(route('dashboard.data.general'));
            const data = response.data;
            amount_values.value = data.amount_values;
            weekly_amount_values.value = data.weekly_amount_values;

            // Atualiza os gráficos com os novos dados
            updateCharts();

        } catch (error) {
            console.error('Erro ao carregar dados gerais do dashboard:', error);
        }
    };

    // Funções para atualizar os dados dos gráficos existentes
    const updateChartData = (chartInstance, newData, newLabels) => {
        if (chartInstance) {
            chartInstance.data.labels = newLabels;
            chartInstance.data.datasets[0].data = newData;
            chartInstance.update();
        }
    };

    const updateWeeklyChartData = (chartInstance, newLabels, newPaymentsData, newServicesData, newWarehouseData) => {
        if (chartInstance) {
            chartInstance.data.labels = newLabels;
            chartInstance.data.datasets[0].data = newPaymentsData;
            chartInstance.data.datasets[1].data = newServicesData;
            chartInstance.data.datasets[2].data = newWarehouseData;
            chartInstance.update();
        }
    };

    const updateCharts = () => {
        // Gráfico de Patrimônio (Doughnut) 
        updateChartData(
            heritageChartInstance,
            [amount_values.value.warehouse, amount_values.value.payments, amount_values.value.services], [
            'Almoxarifado', 'Pagamentos', 'Serviços'
        ]
        );

        // Gráfico Semanal (Line) 
        updateWeeklyChartData(
            weeklyHeritageChartInstance,
            Object.keys(weekly_amount_values.value.weekly_payments), // Labels 
            Object.values(weekly_amount_values.value.weekly_payments), // Pagamentos 
            Object.values(weekly_amount_values.value.weekly_services), // Serviços 
            Object.values(weekly_amount_values.value.weekly_warehouse)  // Estoque 
        );
    };

    const heritage_data_config = ref({
        type: 'doughnut',
        data: {
            labels: ['Almoxarifado', 'Pagamentos', 'Serviços'],
            datasets: [{
                label: 'R$',
                data: [0, 0, 0], // Valores iniciais zerados 
                backgroundColor: [
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)',
                    'rgb(255, 99, 132)',
                ],
                hoverOffset: 4
            }]
        },
    });

    const weekly_heritage_data_config = ref({
        type: 'line',
        data: {
            labels: [], // Inicia vazio
            datasets: [
                {
                    label: 'Pagamentos',
                    data: [], // Inicia vazio
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    fill: false,
                },
                {
                    label: 'Serviços',
                    data: [], // Inicia vazio
                    borderColor: 'rgb(255, 205, 86)',
                    backgroundColor: 'rgba(255, 205, 86, 0.2)',
                    fill: false,
                },
                {
                    label: 'Estoque',
                    data: [], // Inicia vazio
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    fill: false,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function (tooltipItem) {
                            return tooltipItem.dataset.label + ': ' + tooltipItem.raw;
                        },
                    },
                },
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Semana',
                    },
                    ticks: {
                        autoSkip: true,
                        maxTicksLimit: 12,
                    },
                },
                y: {
                    title: {
                        display: true,
                        text: 'Valor',
                    },
                    beginAtZero: true,
                },
            },
        },
    });

    onMounted(() => {
        const heritage_ctx = document.getElementById('heritage-chart');
        heritageChartInstance = new Chart(heritage_ctx, heritage_data_config.value);

        const weekly_heritage_ctx = document.getElementById('weekly-heritage-chart');
        weeklyHeritageChartInstance = new Chart(weekly_heritage_ctx, weekly_heritage_data_config.value);

        fetchGeneralData(); // Carrega os dados na montagem
    });

    const selected_graph = ref(0); 
</script>

<template>

    <div class="w-full flex lg:hidden my-5">
        <select v-model="selected_graph" class="w-full">
            <option :value="0" selected>Gráfico Total</option>
            <option :value="1">Gráfico Semanal</option>
        </select>
    </div>
    <div class="w-full grid grid-cols-1 lg:grid-cols-2 items-center justify-center align-middle gap-5">
        <div class="max-h-[360px] lg:flex items-center justify-center align-middle"
            :class="{ 'hidden': selected_graph !== 0, 'flex': selected_graph === 0 }">
            <canvas id="heritage-chart" />
        </div>
        <div class="max-h-[360px] lg:flex items-center justify-center align-middle"
            :class="{ 'hidden': selected_graph !== 1, 'flex': selected_graph === 1 }">
            <canvas id="weekly-heritage-chart" />
        </div>
        <div class="col-span-2 grid grid-cols-2 md:grid-cols-3 gap-5">
            <div
                class="w-full bg-neutral-100 rounded-md border border-neutral-200 p-5 text-center hover:text-blue-500 hover:bg-blue-50">
                <h2 class="text-xs sm:text-sm md:text-md lg:text-lg xl:text-xl">Almoxarifado</h2>
                <p class="font-extrabold text-sm sm:text-md md:text-lg lg:text-xl xl:text-2xl">{{
                    toMoney(amount_values.warehouse) }}</p>
            </div>
            <div
                class="w-full bg-neutral-100 rounded-md border border-neutral-200 p-5 text-center hover:text-yellow-500 hover:bg-yellow-50">
                <h2 class="text-xs sm:text-sm md:text-md lg:text-lg xl:text-xl">Pagamentos</h2>
                <p class="font-extrabold text-sm sm:text-md md:text-lg lg:text-xl xl:text-2xl">{{
                    toMoney(amount_values.payments) }}</p>
            </div>
            <div
                class="w-full bg-neutral-100 rounded-md border border-neutral-200 p-5 text-center hover:text-red-500 hover:bg-red-50">
                <h2 class="text-xs sm:text-sm md:text-md lg:text-lg xl:text-xl">Serviços</h2>
                <p class="font-extrabold text-sm sm:text-md md:text-lg lg:text-xl xl:text-2xl">{{
                    toMoney(amount_values.services) }}</p>
            </div>
        </div>
    </div>
</template>