<script setup>
    import { ref, onMounted, watch } from 'vue'
    import AppLayout from '@/Layouts/AppLayout.vue';
    import { Head, useForm } from '@inertiajs/vue3';
    import SeeRecords from '@/Components/Dashboard/SeeRecords.vue'
    import ExtraOptionsButton from '@/Components/ExtraOptionsButton.vue'
    import { formatDate, toMoney } from '@/general.js';
    import Chart from 'chart.js/auto';

    const props = defineProps({
        page: Object,
        title: String,
        procedures: Array,
        parameters: Object,
        users: Array,
        actions: Array,
        departments: Array,
        amount_values: Object,
        weekly_amount_values: {
            type: Object,
            default: () => ({
                weekly_payments: {},
                weekly_services: {},
                weekly_warehouse: {}
            })
        },
        lowStockItems: Array,
        topItemsToday: Array,
        latestItems: Array,
        latestPayments: Array,
        latestServices: Array,
        upcomingDeadlines: Array,
    })

    const modal = ref(false);
    const selected_procedure = ref(null);

    const form = useForm({
        'user': props.parameters.user,
        'action': props.parameters.action,
        'department': props.parameters.department,
        'start_date': props.parameters.start_date,
        'end_date': props.parameters.end_date,
    })

    const getLogs = () => {
        let timer;
        clearTimeout(timer);
        timer = setTimeout(() => {
            form.get(route('dashboard.index'), {
                preserveScroll: true,
                // preserveState: true,
                replace: true,
            });
        }, 300);
    }

    watch(
        [() => form.user, () => form.department, () => form.action],
        (newFormValues) => {
            getLogs();
        },
        { deep: true }
    );


    const applyFilters = () => {
        form.get(route('dashboard.index'), {
            preserveScroll: true,
            preserveState: false,
            replace: true,
        });
    };


    const heritage_data = ref({
        labels: [
            'Almoxarifado',
            'Pagamentos',
            'Serviços'
        ],
        datasets: [{
            label: 'R$',
            data: [props.amount_values.warehouse, props.amount_values.payments, props.amount_values.services],
            backgroundColor: [
                'rgb(54, 162, 235)',
                'rgb(255, 205, 86)',
                'rgb(255, 99, 132)',
            ],
            hoverOffset: 4
        }]
    });

    const heritage_config = {
        type: 'doughnut',
        data: heritage_data.value,
    };

    const weekly_heritage_data = ref({
        labels: Object.keys(props.weekly_amount_values.weekly_payments), // Assume que a chave é a semana
        datasets: [
            {
                label: 'Pagamentos',
                data: Object.values(props.weekly_amount_values.weekly_payments),
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                fill: false,
            },
            {
                label: 'Serviços',
                data: Object.values(props.weekly_amount_values.weekly_services),
                borderColor: 'rgb(255, 205, 86)',
                backgroundColor: 'rgba(255, 205, 86, 0.2)',
                fill: false,
            },
            {
                label: 'Estoque',
                data: Object.values(props.weekly_amount_values.weekly_warehouse),
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                fill: false,
            }
        ]
    });

    const weekly_heritage_config = {
        type: 'line',
        data: weekly_heritage_data.value,
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
                        maxTicksLimit: 12, // Limita o número de ticks no eixo X
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
    };

    const openModal = (procedure) => {
        selected_procedure.value = procedure;
        modal.value = true;
    }

    const closeModal = () => {
        selected_procedure.value = null;
        modal.value = false;
    }

    // Initialize the chart after the component has been mounted
    onMounted(() => {
        const heritage_ctx = document.getElementById('heritage-chart');
        new Chart(heritage_ctx, heritage_config);

        // const weekly_heritage_ctx = document.getElementById('weekly-heritage-chart');
        // new Chart(weekly_heritage_ctx, weekly_heritage_config);
    });

    const heritage_total_amount = () => {
        return props.amount_values.warehouse + props.amount_values.payments + props.amount_values.services
    }

    const clearFilters = () => {
        const today = new Date();
        const twoWeeksAgo = new Date();
        twoWeeksAgo.setDate(today.getDate() - 2);

        // Formatação em 'YYYY-MM-DD'
        const formatDate = (date) => date.toISOString().split('T')[0];

        form.user = 0;
        form.action = 0;
        form.department = 0;
        form.start_date = formatDate(twoWeeksAgo);
        form.end_date = formatDate(today);
        // getLogs();
    };

</script>

<template>

    <Head :title="page.name" />
    <AppLayout :page="page">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ page.name }} | {{ toMoney(heritage_total_amount()) }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-10 items-center align-middle justify-center">
                        <div></div>
                        <div>
                            <canvas id="heritage-chart"></canvas>
                            <ul
                                class="m-2 rounded-lg overflow-hidden border border-neutral-400 divide-y divide-neutral-400 p-2 text-neutral-700 text-center bg-neutral-100">
                                <li class="py-2 hover:font-bold hover:text-blue-500 hover:bg-blue-50">Almoxarifado: {{
                                    toMoney(amount_values.warehouse) }}</li>
                                <li class="py-2 hover:font-bold hover:text-yellow-500 hover:bg-yellow-50">Pagamentos: {{
                                    toMoney(amount_values.payments) }}</li>
                                <li class="py-2 hover:font-bold hover:text-red-500 hover:bg-red-50">Serviços: {{
                                    toMoney(amount_values.services) }}</li>
                            </ul>
                        </div>
                        <div></div>
                        <!--<div class="col-span-2">
                            <canvas id="weekly-heritage-chart"></canvas>
                            <p class="my-5 text-neutral-700 text-sm text-center">Este gráfico trás uma aproximação do
                                comportamento
                                do
                                patrimônio
                                nas ultimas 12 semanas
                                (3 meses)
                            </p>
                        </div>-->
                    </div>

                    <div class="mb-6">
                        <h2 class="text-xl font-bold text-neutral-800 border-l-4 border-blue-500 pl-4 mb-4">📦
                            Almoxarifado em Destaque</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div class="bg-white rounded-lg shadow-sm p-5 border border-blue-100">
                                <h3 class="text-md font-semibold text-blue-700 mb-2">🔻 Itens com Baixo Estoque</h3>
                                <ul class="space-y-1 text-sm text-neutral-700">
                                    <li v-for="item in lowStockItems" :key="item.id">
                                        {{ item.name }} <span class="text-neutral-400">({{ item.quantity }} {{
                                            item.measurement_unit }})</span>
                                    </li>
                                </ul>
                                <p v-if="!lowStockItems.length" class="text-sm text-neutral-400 mt-2">Nenhum item com
                                    baixo estoque.</p>
                            </div>

                            <div class="bg-white rounded-lg shadow-sm p-5 border border-green-100">
                                <h3 class="text-md font-semibold text-green-700 mb-2">🔥 Mais Usados/Vendidos Hoje</h3>
                                <ul class="space-y-1 text-sm text-neutral-700">
                                    <li v-for="item in topItemsToday" :key="item.id">
                                        {{ item.name }} <span class="text-neutral-400">({{ item.total_quantity }}
                                            usos/vendas)</span>
                                    </li>
                                </ul>
                                <p v-if="!topItemsToday.length" class="text-sm text-neutral-400 mt-2">Nenhum registro
                                    hoje.</p>
                            </div>

                            <div class="bg-white rounded-lg shadow-sm p-5 border border-purple-100">
                                <h3 class="text-md font-semibold text-purple-700 mb-2">🆕 Últimos Itens Movimentados
                                </h3>
                                <ul class="space-y-1 text-sm text-neutral-700">
                                    <li v-for="item in latestItems" :key="item.id">
                                        {{ item.name }} <span class="text-neutral-400"> ({{ formatDate(item.updated_at,
                                            'short_date') }})</span>
                                    </li>
                                </ul>
                                <p v-if="!latestItems.length" class="text-sm text-neutral-400 mt-2">Nenhum novo item
                                    recente.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h2 class="text-xl font-bold text-neutral-800 border-l-4 border-yellow-500 pl-4 mb-4">💰
                            Financeiro</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div class="bg-white rounded-lg shadow-sm p-5 border border-yellow-100">
                                <h3 class="text-md font-semibold text-yellow-700 mb-2">💸 Últimos Pagamentos Cadastrados
                                </h3>
                                <ul class="space-y-1 text-sm text-neutral-700">
                                    <li v-for="payment in latestPayments" :key="payment.id">
                                        {{ payment.entity_name || 'N/A' }}: <span class="font-medium">{{
                                            toMoney(payment.accounting?.partial_value) }}</span>
                                        <span class="text-neutral-400"> ({{ formatDate(payment.date, 'short_date')
                                            }})</span>
                                    </li>
                                </ul>
                                <p v-if="!latestPayments.length" class="text-sm text-neutral-400 mt-2">Nenhum pagamento
                                    recente.</p>
                            </div>

                            <div class="bg-white rounded-lg shadow-sm p-5 border border-red-100">
                                <h3 class="text-md font-semibold text-red-700 mb-2">⚙️ Últimos Serviços Cadastrados</h3>
                                <ul class="space-y-1 text-sm text-neutral-700">
                                    <li v-for="service in latestServices" :key="service.id">
                                        {{ service.entity_name || 'N/A' }}: <span class="font-medium">{{
                                            toMoney(service.accounting?.partial_value) }}</span>
                                        <span class="text-neutral-400"> ({{ formatDate(service.date, 'short_date')
                                            }})</span>
                                    </li>
                                </ul>
                                <p v-if="!latestServices.length" class="text-sm text-neutral-400 mt-2">Nenhum serviço
                                    recente.</p>
                            </div>

                            <div class="bg-white rounded-lg shadow-sm p-5 border border-orange-100">
                                <h3 class="text-md font-semibold text-orange-700 mb-2">⏰ Próximos Vencimentos</h3>
                                <ul class="space-y-1 text-sm text-neutral-700">
                                    <li v-for="upcoming in upcomingDeadlines" :key="upcoming.id">
                                        {{ upcoming.entity_name || 'N/A' }}: <span class="font-medium">{{
                                            toMoney(upcoming.accounting?.partial_value) }}</span>
                                        <span class="text-neutral-400"> (Até {{ formatDate(upcoming.deadline,
                                            'short_date') }})</span>
                                    </li>
                                </ul>
                                <p v-if="!upcomingDeadlines.length" class="text-sm text-neutral-400 mt-2">Nenhum
                                    vencimento
                                    próximo.</p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="w-full rounded-lg bg-neutral-200 text-neutral-700 uppercase text-xl font-bold text-center p-2 my-10">
                        Registro de Atividades
                    </div>

                    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 print:hidden">
                        <div>
                            <label for="filter-procedure-start_date" class="text-xs text-neutral-500">Data
                                (início)</label>
                            <input id="filter-procedure-start_date" type="date" class="simple-input"
                                v-model="form.start_date" @change="getLogs()" />
                        </div>
                        <div>
                            <label for="filter-procedure-end_date" class="text-xs text-neutral-500">Data
                                (fim)</label>
                            <input id="filter-procedure-end_date" type="date" class="simple-input"
                                v-model="form.end_date" @change="getLogs()" />
                        </div>
                        <div>
                            <label for="filter-procedure-user" class="text-xs text-neutral-500">Usuário</label>
                            <select id="filter-procedure-user" class="simple-select" v-model="form.user">
                                <option :value="0">Todos</option>
                                <option v-for="user in users" :value="user.id" :key="user.id">
                                    {{ user.name }} {{ user.surname }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label for="filter-procedure-action" class="text-xs text-neutral-500">Ação</label>
                            <select id="filter-procedure-action" class="simple-select" v-model="form.action">
                                <option :value="0">Todos</option>
                                <option v-for="action in actions" :value="action.id" :key="action.id"
                                    class="capitalize">
                                    {{ action.past }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label for="filter-procedure-department"
                                class="text-xs text-neutral-500">Departamento</label>
                            <select id="filter-procedure-department" class="simple-select" v-model="form.department">
                                <option :value="0">Todos</option>
                                <option v-for="department in departments" :value="department.id" :key="department.id">
                                    {{ department.name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="col-span-full flex justify-end mt-2 gap-5">
                        <!--<button @click="applyFilters" class="text-blue-500 transition text-xs">
                            Aplicar Filtros
                        </button>-->
                        <button @click="clearFilters()" class=" text-red-500 transition text-xs">
                            Resetar filtros
                        </button>
                    </div>


                    <div class="overflow-x-auto">
                        <table v-if="procedures.length" class="table-auto min-w-full divide-y divide-neutral-200 my-5">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-neutral-900">
                                        Usuário
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-neutral-900">
                                        Ação
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-neutral-900">
                                        Departamento</th>
                                    <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-neutral-900">
                                        Data
                                    </th>
                                    <!--<th scope="col" class="px-6 py-3 text-left text-sm font-medium text-neutral-900">
                                        Ações
                                    </th>-->
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-neutral-200">
                                <tr v-for="procedure in procedures" :key="procedure.id">
                                    <td class="px-6 py-4 text-sm text-neutral-900">
                                        {{ procedure.user.name }} {{ procedure.user.surname }}
                                    </td>
                                    <td class="px-6 py-4 text-sm capitalize">
                                        {{ procedure.action.past }}
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <span v-if="procedure.department.type === 'payslip'">Registro de</span>
                                        <span v-if="procedure.department.type === 'warehouse'">Item de</span>
                                        {{ procedure.department.name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-neutral-500">
                                        {{ formatDate(procedure.created_at, 'reading_date_time') }}
                                    </td>
                                    <td class="hidden px-6 py-4 text-sm">
                                        <button class="text-green-500 hover:text-green-700"
                                            @click="openModal(procedure)">Ver
                                            registros</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <p v-else class="text-sm text-neutral-500 text-center pt-5">Nenhum procedimento registrado
                            no
                            período especificado.</p>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
    <ExtraOptionsButton :mode="['rollup', 'print_page']" />
    <SeeRecords :modal="modal" :procedure="selected_procedure" @close="closeModal" />
</template>
