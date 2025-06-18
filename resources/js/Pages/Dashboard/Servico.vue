<script setup>
    import { ref, onMounted, watch, nextTick, computed } from 'vue';
    import Chart from 'chart.js/auto';
    import { formatDate, toMoney } from '@/general.js';
    import { Link } from '@inertiajs/vue3';
    import axios from 'axios';

    // ... (Estado dos dados, relatórios, carregamento, etc. - sem alterações nessas partes) ...

    const serviceReportsByCount = ref({});
    const serviceReportsByValue = ref({});
    const budgetReportsByCount = ref({});
    const budgetReportsByValue = ref({});
    const combinedReports = ref({
        total_budgets: 0,
        budgets_with_service: 0,
        approval_rate: '0.00%',
    });

    const services = ref([]);
    const budgets = ref([]);

    const isLoading = ref(true);

    const serviceCurrentPage = ref(1);
    const serviceLastPage = ref(1);
    const serviceTotal = ref(0);
    const servicePerPage = ref(10);

    const budgetCurrentPage = ref(1);
    const budgetLastPage = ref(1);
    const budgetTotal = ref(0);
    const budgetPerPage = ref(10);

    const serviceStatusFilter = ref('all');
    const budgetStatusFilter = ref('all');

    const availableServiceStatuses = ref([
        { value: 'all', label: 'Todos' },
        { value: 'Não Iniciado', label: 'Não Iniciado' },
        { value: 'Em Andamento', label: 'Em Andamento' },
        { value: 'Finalizado', label: 'Finalizado' },
        { value: 'Cancelado', label: 'Cancelado' },
    ]);

    const availableBudgetStatuses = ref([
        { value: 'all', label: 'Todos' },
        { value: 'Criado', label: 'Criado/Rascunho' },
        { value: 'Enviado', label: 'Enviado' },
        { value: 'Aprovado', label: 'Aprovado' },
        { value: 'Rejeitado', label: 'Rejeitado' },
        { value: 'Cancelado', label: 'Cancelado' },
    ]);

    // NOVAS REFS PARA SERVIÇOS NÃO PAGOS
    const unpaidServices = ref([]);
    const unpaidCurrentPage = ref(1);
    const unpaidLastPage = ref(1);
    const unpaidTotal = ref(0);
    const unpaidPerPage = ref(10); // Pode ser diferente de servicePerPage/budgetPerPage

    let serviceChartInstance = ref(null);
    let budgetChartInstance = ref(null);
    let serviceValueChartInstance = ref(null);
    let budgetValueChartInstance = ref(null);

    // Função para configurar e criar/atualizar um gráfico
    const createOrUpdateChart = (ctxId, chartInstanceRef, chartType, dataRef, titleText, colors) => {
        const ctx = document.getElementById(ctxId);
        // console.log(`Attempting to create/update chart for ID: ${ctxId}`, { ctx, data: dataRef.value }); // Debug
        if (!ctx) {
            // console.warn(`Canvas element with ID '${ctxId}' not found.`); // Debug
            return;
        }

        const chartData = {
            labels: Object.keys(dataRef.value),
            datasets: [{
                label: (chartType === 'doughnut') ? 'Quantidade' : 'Valor (R$)',
                data: Object.values(dataRef.value),
                backgroundColor: (chartType === 'doughnut') ? colors : colors.map(color => color.replace('rgb', 'rgba').replace(')', ', 0.6)')),
                borderColor: (chartType === 'bar') ? colors.map(color => color.replace('rgb', 'rgba').replace(')', ', 1)')) : undefined,
                borderWidth: (chartType === 'bar') ? 1 : undefined,
                hoverOffset: (chartType === 'doughnut') ? 4 : undefined,
            }]
        };

        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' },
                title: { display: true, text: titleText + ((chartType === 'doughnut') ? ' (Quantidade)' : ' (Valor)') },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return (chartType === 'bar') ? context.dataset.label + ': ' + toMoney(context.raw) : context.label + ': ' + context.formattedValue;
                        }
                    }
                }
            },
            scales: (chartType === 'bar') ? {
                x: {
                    beginAtZero: true,
                    grid: { display: false }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) { return toMoney(value); }
                    },
                    grid: { color: 'rgba(200, 200, 200, 0.2)' }
                }
            } : {},
        };

        if (chartInstanceRef.value) {
            // console.log(`Updating existing chart for ID: ${ctxId}`); // Debug
            chartInstanceRef.value.data = chartData;
            chartInstanceRef.value.options = chartOptions;
            chartInstanceRef.value.update();
        } else {
            // console.log(`Creating new chart for ID: ${ctxId}`); // Debug
            chartInstanceRef.value = new Chart(ctx, {
                type: chartType,
                data: chartData,
                options: chartOptions,
            });
        }
    };

    // Função para buscar os dados
    const fetchReportsAndLists = async () => {
        isLoading.value = true;
        try {
            const response = await axios.get(route('dashboard.data.services_budgets', {
                service_status: serviceStatusFilter.value === 'all' ? null : serviceStatusFilter.value,
                budget_status: budgetStatusFilter.value === 'all' ? null : budgetStatusFilter.value,
                service_page: serviceCurrentPage.value,
                budget_page: budgetCurrentPage.value,
                service_per_page: servicePerPage.value,
                budget_per_page: budgetPerPage.value,
                start_date: 'last_30_days',
            }));

            const data = response.data;

            serviceReportsByCount.value = data.serviceReports.byCount;

            serviceReportsByValue.value = data.serviceReports.byValue;
            budgetReportsByCount.value = data.budgetReports.byCount;
            budgetReportsByValue.value = data.budgetReports.byValue;
            combinedReports.value = data.combinedReports;

            services.value = data.services.data;
            serviceCurrentPage.value = data.services.current_page;
            serviceLastPage.value = data.services.last_page;
            serviceTotal.value = data.services.total;
            servicePerPage.value = data.services.per_page;

            budgets.value = data.budgets.data;
            budgetCurrentPage.value = data.budgets.current_page;
            budgetLastPage.value = data.budgets.last_page;
            budgetTotal.value = data.budgets.total;
            budgetPerPage.value = data.budgets.per_page;

            nextTick(() => {
                // Padrão de cores para Serviços: azul, amarelo, verde, vermelho (tons pastéis)
                const serviceColors = [
                    'rgb(144, 202, 249)', // Azul Pastel
                    'rgb(255, 241, 118)', // Amarelo Pastel
                    'rgb(165, 214, 167)', // Verde Pastel
                    'rgb(239, 154, 154)', // Vermelho Pastel
                ];
                // Padrão de cores para Orçamentos: azul, verde, laranja, vermelho, cinza (tons pastéis)
                const budgetColors = [
                    'rgb(144, 202, 249)', // Azul Pastel
                    'rgb(165, 214, 167)', // Verde Pastel
                    'rgb(255, 204, 128)', // Laranja Pastel
                    'rgb(239, 154, 154)', // Vermelho Pastel
                    'rgb(207, 216, 220)', // Cinza Pastel
                ];

                createOrUpdateChart('service-status-chart-count', serviceChartInstance, 'doughnut', serviceReportsByCount, 'Serviços por Status', serviceColors);
                createOrUpdateChart('service-status-chart-value', serviceValueChartInstance, 'bar', serviceReportsByValue, 'Serviços por Status', serviceColors);
                createOrUpdateChart('budget-status-chart-count', budgetChartInstance, 'doughnut', budgetReportsByCount, 'Orçamentos por Status', budgetColors);
                createOrUpdateChart('budget-status-chart-value', budgetValueChartInstance, 'bar', budgetReportsByValue, 'Orçamentos por Status', budgetColors);
            });

        } catch (error) {
            console.error('Erro ao carregar dados do dashboard de serviços/orçamentos:', error);
        } finally {
            isLoading.value = false;
        }
    };

    // NOVA FUNÇÃO: Buscar Serviços Concluídos Não Pagos
    const fetchUnpaidServices = async () => {
        try {
            // Não usa isLoading global para não bloquear o restante do dashboard
            // Se precisar de um loader específico para esta seção, crie uma ref separada (ex: isLoadingUnpaid)
            const response = await axios.get(route('dashboard.data.unpaid_completed_services', {
                unpaid_page: unpaidCurrentPage.value,
                unpaid_per_page: unpaidPerPage.value,
            }));

            const data = response.data.unpaid_services_list; // Acessa a lista dentro do objeto

            unpaidServices.value = data.data;
            unpaidCurrentPage.value = data.current_page;
            unpaidLastPage.value = data.last_page;
            unpaidTotal.value = data.total;
            unpaidPerPage.value = data.per_page;

        } catch (error) {
            console.error('Erro ao carregar serviços concluídos não pagos:', error);
        }
    };

    onMounted(() => {
        fetchReportsAndLists(); // Carrega os dados principais
        fetchUnpaidServices(); // Carrega os serviços não pagos
    });

    watch([serviceStatusFilter, budgetStatusFilter, serviceCurrentPage, budgetCurrentPage], () => {
        fetchReportsAndLists();
    });

    // Watch para a paginação dos serviços não pagos
    watch(unpaidCurrentPage, () => {
        fetchUnpaidServices();
    });

    const goToServicePage = (page) => {
        if (page >= 1 && page <= serviceLastPage.value) {
            serviceCurrentPage.value = page;
        }
    };

    const goToBudgetPage = (page) => {
        if (page >= 1 && page <= budgetLastPage.value) {
            budgetCurrentPage.value = page;
        }
    };

    // NOVA FUNÇÃO: goTo para serviços não pagos
    const goToUnpaidServicePage = (page) => {
        if (page >= 1 && page <= unpaidLastPage.value) {
            unpaidCurrentPage.value = page;
        }
    };

    // --- Lógica de Paginação Inteligente (Refatorada para melhor visibilidade de elipses) ---
    // maxVisibleLinks: número máximo de botões de página numérica para exibir (excluindo Anterior/Próxima/Primeira/Última e elipses)
    const generatePaginationLinks = (currentPage, lastPage, maxVisibleLinks = 5) => {
        const pages = [];
        const ellipsis = '...';
        const delta = Math.floor(maxVisibleLinks / 2); // Número de páginas a mostrar de cada lado da atual

        // Caso base: Se o número total de páginas é menor ou igual ao número de links visíveis, exibe todas.
        if (lastPage <= maxVisibleLinks + 2) { // Adicionado +2 para cobrir casos onde 1 e lastPage já ocupam espaço
            for (let i = 1; i <= lastPage; i++) {
                pages.push(i);
            }
            return pages;
        }

        let leftBound = currentPage - delta;
        let rightBound = currentPage + delta;

        // Ajusta os limites para não ultrapassar 1 ou lastPage
        if (leftBound < 1) {
            leftBound = 1;
            rightBound = maxVisibleLinks;
        }
        if (rightBound > lastPage) {
            rightBound = lastPage;
            leftBound = lastPage - maxVisibleLinks + 1;
        }

        // Garante que o limite inferior não seja menor que 1
        leftBound = Math.max(1, leftBound);


        // --- Construindo os links ---

        // Adiciona a primeira página (se não estiver na faixa visível)
        if (leftBound > 1) {
            pages.push(1);
            if (leftBound > 2) { // Adiciona elipse se houver mais de uma página entre 1 e leftBound
                pages.push(ellipsis);
            }
        }

        // Adiciona as páginas dentro da faixa visível
        for (let i = leftBound; i <= rightBound; i++) {
            pages.push(i);
        }

        // Adiciona a última página (se não estiver na faixa visível)
        if (rightBound < lastPage) {
            if (rightBound < lastPage - 1) { // Adiciona elipse se houver mais de uma página entre rightBound e lastPage
                pages.push(ellipsis);
            }
            pages.push(lastPage);
        }

        return pages;
    };


    // Computed properties para os links de paginação (permanecem os mesmos)
    const servicePaginationLinks = computed(() => {
        return generatePaginationLinks(serviceCurrentPage.value, serviceLastPage.value);
    });

    const budgetPaginationLinks = computed(() => {
        return generatePaginationLinks(budgetCurrentPage.value, budgetLastPage.value);
    });

    // NOVA COMPUTED PROPERTY para paginação de serviços não pagos
    const unpaidPaginationLinks = computed(() => {
        return generatePaginationLinks(unpaidCurrentPage.value, unpaidLastPage.value);
    });

</script>

<template>
    <div class="mb-6">
        <h2 class="text-xl font-bold text-neutral-800 border-l-4 border-teal-500 pl-4 mb-4">📊
            Serviços e Orçamentos</h2>


        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-6 mb-6">
            <div
                class="bg-white rounded-lg shadow-sm p-5 border border-teal-100 flex justify-center items-center min-h-[350px] relative">
                <canvas id="service-status-chart-count" class="w-full h-full"></canvas>
            </div>
            <div
                class="bg-white rounded-lg shadow-sm p-5 border border-teal-100 flex justify-center items-center min-h-[350px] relative">
                <canvas id="service-status-chart-value" class="w-full h-full"></canvas>
            </div>
            <div
                class="bg-white rounded-lg shadow-sm p-5 border border-teal-100 flex justify-center items-center min-h-[350px] relative">
                <canvas id="budget-status-chart-count" class="w-full h-full"></canvas>
            </div>
            <div
                class="bg-white rounded-lg shadow-sm p-5 border border-teal-100 flex justify-center items-center min-h-[350px] relative">
                <canvas id="budget-status-chart-value" class="w-full h-full"></canvas>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-sm p-5 border border-green-100 text-center">
                <h3 class="text-md font-semibold text-green-700 mb-2">Total de Orçamentos</h3>
                <p class="text-3xl font-bold text-neutral-800">{{ combinedReports.total_budgets }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-5 border border-green-100 text-center">
                <h3 class="text-md font-semibold text-green-700 mb-2">Orçamentos que Geraram Serviço</h3>
                <p class="text-3xl font-bold text-neutral-800">{{ combinedReports.budgets_with_service }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-5 border border-green-100 text-center">
                <h3 class="text-md font-semibold text-green-700 mb-2">Taxa de Aprovação</h3>
                <p class="text-3xl font-bold text-neutral-800">{{ combinedReports.approval_rate }}</p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-5 border border-green-100 mb-6">
            <h3 class="text-lg font-semibold text-green-700 mb-4">Serviços Concluídos e Não Pagos (x{{
                unpaidServices?.length }})</h3>

            <div class="overflow-x-auto">
                <table v-if="unpaidServices.length" class="table-auto min-w-full divide-y divide-neutral-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-center text-sm font-medium text-neutral-900">ID</th>
                            <th class="px-6 py-3 text-center text-sm font-medium text-neutral-900">Entidade</th>
                            <th class="px-6 py-3 text-center text-sm font-medium text-neutral-900">Motivo</th>
                            <th class="px-6 py-3 text-center text-sm font-medium text-neutral-900">Valor Pendente
                            </th>
                            <th class="px-6 py-3 text-center text-sm font-medium text-neutral-900">Concluído em</th>
                            <th class="px-6 py-3 text-center text-sm font-medium text-neutral-900">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-neutral-200">
                        <tr v-for="service in unpaidServices" :key="service.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-neutral-900">{{
                                service.id
                                }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-700">{{ service.entity_name
                                }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-700">{{ service.motive }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-700">
                                {{ toMoney(service.accounting?.partial_value) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-700">
                                {{ formatDate(service.completion_date, 'short_date') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <Link :href="route('services.show', service.id)" target="_blank"
                                    class="text-green-600 hover:text-green-900">Ver</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p v-else class="text-sm text-neutral-500 text-center py-4 border-t border-neutral-200 mt-4 pt-4">
                    Nenhum serviço concluído e não pago encontrado.
                </p>
            </div>

            <div v-if="unpaidLastPage > 1" class="flex justify-center mt-4 space-x-1 flex-wrap">
                <button @click="goToUnpaidServicePage(1)" :disabled="unpaidCurrentPage === 1"
                    class="px-3 py-1 border rounded-md text-sm transition-colors duration-150 ease-in-out"
                    :class="{ 'bg-gray-200 text-gray-500 cursor-not-allowed': unpaidCurrentPage === 1, 'hover:bg-green-100 hover:text-green-700': unpaidCurrentPage !== 1 }">
                    Primeira
                </button>
                <button @click="goToUnpaidServicePage(unpaidCurrentPage - 1)" :disabled="unpaidCurrentPage === 1"
                    class="px-3 py-1 border rounded-md text-sm transition-colors duration-150 ease-in-out"
                    :class="{ 'bg-gray-200 text-gray-500 cursor-not-allowed': unpaidCurrentPage === 1, 'hover:bg-green-100 hover:text-green-700': unpaidCurrentPage !== 1 }">
                    Anterior
                </button>

                <template v-for="page in unpaidPaginationLinks" :key="page">
                    <span v-if="page === '...'" class="px-3 py-1 text-sm text-gray-500">...</span>
                    <button v-else @click="goToUnpaidServicePage(page)"
                        class="px-3 py-1 border rounded-md text-sm transition-colors duration-150 ease-in-out"
                        :class="{ 'bg-green-600 text-white border-green-600': page === unpaidCurrentPage, 'bg-gray-100 text-green-700 hover:bg-green-200 hover:text-green-800': page !== unpaidCurrentPage }">
                        {{ page }}
                    </button>
                </template>

                <button @click="goToUnpaidServicePage(unpaidCurrentPage + 1)"
                    :disabled="unpaidCurrentPage === unpaidLastPage"
                    class="px-3 py-1 border rounded-md text-sm transition-colors duration-150 ease-in-out"
                    :class="{ 'bg-gray-200 text-gray-500 cursor-not-allowed': unpaidCurrentPage === unpaidLastPage, 'hover:bg-green-100 hover:text-green-700': unpaidCurrentPage !== unpaidLastPage }">
                    Próxima
                </button>
                <button @click="goToUnpaidServicePage(unpaidLastPage)" :disabled="unpaidCurrentPage === unpaidLastPage"
                    class="px-3 py-1 border rounded-md text-sm transition-colors duration-150 ease-in-out"
                    :class="{ 'bg-gray-200': unpaidCurrentPage === unpaidLastPage, 'hover:bg-green-100 hover:text-green-700': unpaidCurrentPage !== unpaidLastPage }">
                    Última
                </button>
            </div>
        </div>

        <div>
            <div class="bg-white rounded-lg shadow-sm p-5 border border-green-100 mb-6">
                <h3 class="text-lg font-semibold text-green-700 mb-4">Serviços</h3>
                <div class="mb-4 flex items-center gap-2">
                    <label for="service-status-filter" class="text-sm text-neutral-600">Filtrar por Status:</label>
                    <select id="service-status-filter" v-model="serviceStatusFilter" class="simple-select w-48">
                        <option v-for="status in availableServiceStatuses" :key="status.value" :value="status.value">
                            {{ status.label }}
                        </option>
                    </select>
                </div>

                <div class="overflow-x-auto">
                    <table v-if="services.length" class="table-auto min-w-full divide-y divide-neutral-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-center text-sm font-medium text-neutral-900">ID</th>
                                <th class="px-6 py-3 text-center text-sm font-medium text-neutral-900">Entidade</th>
                                <th class="px-6 py-3 text-center text-sm font-medium text-neutral-900">Status</th>
                                <th class="px-6 py-3 text-center text-sm font-medium text-neutral-900">Valor Parcial
                                </th>
                                <th class="px-6 py-3 text-center text-sm font-medium text-neutral-900">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-neutral-200">
                            <tr v-for="service in services" :key="service.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-neutral-900">{{
                                    service.id
                                    }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-700">{{
                                    service.entity_name
                                    }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-700">{{
                                    service.service_status
                                    }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-700">
                                    {{ toMoney(service.accounting?.partial_value) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <Link :href="route('services.show', service.id)" target="_blank"
                                        class="text-green-600 hover:text-green-900">Ver</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-else class="text-sm text-neutral-500 text-center py-4 border-t border-neutral-200 mt-4 pt-4">
                        Nenhum serviço encontrado com este
                        status.
                    </p>
                </div>

                <div v-if="serviceLastPage > 1" class="flex justify-center mt-4 space-x-1 flex-wrap">
                    <button @click="goToServicePage(1)" :disabled="serviceCurrentPage === 1"
                        class="px-3 py-1 border rounded-md text-sm transition-colors duration-150 ease-in-out"
                        :class="{ 'bg-gray-200 text-gray-500 cursor-not-allowed': serviceCurrentPage === 1, 'hover:bg-green-100 hover:text-green-700': serviceCurrentPage !== 1 }">
                        Primeira
                    </button>
                    <button @click="goToServicePage(serviceCurrentPage - 1)" :disabled="serviceCurrentPage === 1"
                        class="px-3 py-1 border rounded-md text-sm transition-colors duration-150 ease-in-out"
                        :class="{ 'bg-gray-200 text-gray-500 cursor-not-allowed': serviceCurrentPage === 1, 'hover:bg-green-100 hover:text-green-700': serviceCurrentPage !== 1 }">
                        Anterior
                    </button>

                    <template v-for="page in servicePaginationLinks" :key="page">
                        <span v-if="page === '...'" class="px-3 py-1 text-sm text-gray-500">...</span>
                        <button v-else @click="goToServicePage(page)"
                            class="px-3 py-1 border rounded-md text-sm transition-colors duration-150 ease-in-out"
                            :class="{ 'bg-green-600 text-white border-green-600': page === serviceCurrentPage, 'bg-gray-100 text-green-700 hover:bg-green-200 hover:text-green-800': page !== serviceCurrentPage }">
                            {{ page }}
                        </button>
                    </template>

                    <button @click="goToServicePage(serviceCurrentPage + 1)"
                        :disabled="serviceCurrentPage === serviceLastPage"
                        class="px-3 py-1 border rounded-md text-sm transition-colors duration-150 ease-in-out"
                        :class="{ 'bg-gray-200 text-gray-500 cursor-not-allowed': serviceCurrentPage === serviceLastPage, 'hover:bg-green-100 hover:text-green-700': serviceCurrentPage !== serviceLastPage }">
                        Próxima
                    </button>
                    <button @click="goToServicePage(serviceLastPage)" :disabled="serviceCurrentPage === serviceLastPage"
                        class="px-3 py-1 border rounded-md text-sm transition-colors duration-150 ease-in-out"
                        :class="{ 'bg-gray-200': serviceCurrentPage === serviceLastPage, 'hover:bg-green-100 hover:text-green-700': serviceCurrentPage !== serviceLastPage }">
                        Última
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-5 border border-green-100">
                <h3 class="text-lg font-semibold text-green-700 mb-4">Orçamentos</h3>
                <div class="mb-4 flex items-center gap-2">
                    <label for="budget-status-filter" class="text-sm text-neutral-600">Filtrar por Status:</label>
                    <select id="budget-status-filter" v-model="budgetStatusFilter" class="simple-select w-48">
                        <option v-for="status in availableBudgetStatuses" :key="status.value" :value="status.value">
                            {{ status.label }}
                        </option>
                    </select>
                </div>

                <div class="overflow-x-auto">
                    <table v-if="budgets.length" class="table-auto min-w-full divide-y divide-neutral-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-center text-sm font-medium text-neutral-900">ID</th>
                                <th class="px-6 py-3 text-center text-sm font-medium text-neutral-900">Título</th>
                                <th class="px-6 py-3 text-center text-sm font-medium text-neutral-900">Status</th>
                                <th class="px-6 py-3 text-center text-sm font-medium text-neutral-900">Valor Total
                                </th>
                                <th class="px-6 py-3 text-center text-sm font-medium text-neutral-900">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-neutral-200">
                            <tr v-for="budget in budgets" :key="budget.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-neutral-900">{{
                                    budget.id
                                    }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-700">{{ budget.title }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-700">{{ budget.status }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-700">
                                    {{ toMoney(budget.total_value) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <Link :href="route('budgets.show', budget.id)" target="_blank"
                                        class="text-green-600 hover:text-green-900">Ver</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-else class="text-sm text-neutral-500 text-center py-4 border-t border-neutral-200 mt-4 pt-4">
                        Nenhum orçamento encontrado com este
                        status.
                    </p>
                </div>

                <div v-if="budgetLastPage > 1" class="flex justify-center mt-4 space-x-1 flex-wrap">
                    <button @click="goToBudgetPage(1)" :disabled="budgetCurrentPage === 1"
                        class="px-3 py-1 border rounded-md text-sm transition-colors duration-150 ease-in-out"
                        :class="{ 'bg-gray-200 text-gray-500 cursor-not-allowed': budgetCurrentPage === 1, 'hover:bg-green-100 hover:text-green-700': budgetCurrentPage !== 1 }">
                        Primeira
                    </button>
                    <button @click="goToBudgetPage(budgetCurrentPage - 1)" :disabled="budgetCurrentPage === 1"
                        class="px-3 py-1 border rounded-md text-sm transition-colors duration-150 ease-in-out"
                        :class="{ 'bg-gray-200 text-gray-500 cursor-not-allowed': budgetCurrentPage === 1, 'hover:bg-green-100 hover:text-green-700': budgetCurrentPage !== 1 }">
                        Anterior
                    </button>

                    <template v-for="page in budgetPaginationLinks" :key="page">
                        <span v-if="page === '...'" class="px-3 py-1 text-sm text-gray-500">...</span>
                        <button v-else @click="goToBudgetPage(page)"
                            class="px-3 py-1 border rounded-md text-sm transition-colors duration-150 ease-in-out"
                            :class="{ 'bg-green-600 text-white border-green-600': page === budgetCurrentPage, 'bg-gray-100 text-green-700 hover:bg-green-200 hover:text-green-800': page !== budgetCurrentPage }">
                            {{ page }}
                        </button>
                    </template>

                    <button @click="goToBudgetPage(budgetCurrentPage + 1)"
                        :disabled="budgetCurrentPage === budgetLastPage"
                        class="px-3 py-1 border rounded-md text-sm transition-colors duration-150 ease-in-out"
                        :class="{ 'bg-gray-200 text-gray-500 cursor-not-allowed': budgetCurrentPage === budgetLastPage, 'hover:bg-green-100 hover:text-green-700': budgetCurrentPage !== budgetLastPage }">
                        Próxima
                    </button>
                    <button @click="goToBudgetPage(budgetLastPage)" :disabled="budgetCurrentPage === budgetLastPage"
                        class="px-3 py-1 border rounded-md text-sm transition-colors duration-150 ease-in-out"
                        :class="{ 'bg-gray-200': budgetCurrentPage === budgetLastPage, 'hover:bg-green-100 hover:text-green-700': budgetCurrentPage !== budgetLastPage }">
                        Última
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>