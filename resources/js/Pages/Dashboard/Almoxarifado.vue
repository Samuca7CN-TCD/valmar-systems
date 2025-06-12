<script setup>
    import { ref, onMounted, watch, computed } from 'vue'; // Adicionado 'computed'
    import { formatDate, toMoney } from '@/general.js'; // 
    import { Link } from '@inertiajs/vue3'; // 
    import axios from 'axios'; // 

    // Dados existentes
    const lowStockItems = ref([]);
    const topItemsToday = ref([]);
    const latestItems = ref([]);

    // NOVOS DADOS: Movimentações Recentes
    const recentMovements = ref([]);
    const recentMovementsCurrentPage = ref(1);
    const recentMovementsLastPage = ref(1);
    const recentMovementsTotal = ref(0);
    const recentMovementsPerPage = ref(10); // Valor padrão

    // NOVOS FILTROS para Movimentações Recentes
    const searchRecentMovements = ref('');
    const movementTypeFilter = ref('all'); // 'all', 'entrada', 'saida', 'servico'

    // Estado de carregamento para a seção de movimentações (opcional, mas bom para UX)
    const isLoadingRecentMovements = ref(true);

    // FUNÇÃO existente: buscar os 3 primeiros blocos de dados do almoxarifado
    const fetchWarehouseData = async () => {
        try {
            const response = await axios.get(route('dashboard.data.warehouse'));
            const data = response.data;
            lowStockItems.value = data.lowStockItems;
            topItemsToday.value = data.topItemsToday;
            latestItems.value = data.latestItems;
        } catch (error) {
            console.error('Erro ao carregar dados do almoxarifado:', error);
        }
    };

    // NOVA FUNÇÃO: buscar as movimentações recentes com filtros e paginação
    const fetchRecentMovements = async () => {
        isLoadingRecentMovements.value = true;
        try {
            const response = await axios.get(route('dashboard.data.warehouse'), {
                params: { // Use params para enviar os filtros como query parameters
                    recent_movements_page: recentMovementsCurrentPage.value,
                    recent_movements_per_page: recentMovementsPerPage.value,
                    search_recent_movements: searchRecentMovements.value || null, // Envia null se vazio
                    movement_type: movementTypeFilter.value,
                }
            });
            const data = response.data.recentMovements; // Acessa o objeto recentMovements

            recentMovements.value = data.data;
            recentMovementsCurrentPage.value = data.current_page;
            recentMovementsLastPage.value = data.last_page;
            recentMovementsTotal.value = data.total;
            recentMovementsPerPage.value = data.per_page;

        } catch (error) {
            console.error('Erro ao carregar movimentações recentes do almoxarifado:', error);
        } finally {
            isLoadingRecentMovements.value = false;
        }
    };

    // Funções de paginação e filtro
    const goToRecentMovementsPage = (page) => {
        if (page >= 1 && page <= recentMovementsLastPage.value) {
            recentMovementsCurrentPage.value = page;
        }
    };

    const applyRecentMovementsFilters = () => {
        recentMovementsCurrentPage.value = 1; // Reseta para a primeira página ao aplicar filtros
        fetchRecentMovements();
    };

    // Lógica de Paginação Inteligente (reutilizada de Servico.vue)
    const generatePaginationLinks = (currentPage, lastPage, maxVisibleLinks = 5) => {
        const pages = [];
        const ellipsis = '...';
        const delta = Math.floor(maxVisibleLinks / 2);

        if (lastPage <= maxVisibleLinks + 2) {
            for (let i = 1; i <= lastPage; i++) {
                pages.push(i);
            }
            return pages;
        }

        let leftBound = currentPage - delta;
        let rightBound = currentPage + delta;

        if (leftBound < 1) {
            leftBound = 1;
            rightBound = maxVisibleLinks;
        }
        if (rightBound > lastPage) {
            rightBound = lastPage;
            leftBound = lastPage - maxVisibleLinks + 1;
        }

        leftBound = Math.max(1, leftBound);

        if (leftBound > 1) {
            pages.push(1);
            if (leftBound > 2) {
                pages.push(ellipsis);
            }
        }

        for (let i = leftBound; i <= rightBound; i++) {
            pages.push(i);
        }

        if (rightBound < lastPage) {
            if (rightBound < lastPage - 1) {
                pages.push(ellipsis);
            }
            pages.push(lastPage);
        }

        return pages;
    };

    // Computed property para os links de paginação das movimentações recentes
    const recentMovementsPaginationLinks = computed(() => {
        return generatePaginationLinks(recentMovementsCurrentPage.value, recentMovementsLastPage.value);
    });

    // Watchers para os filtros de movimentações recentes
    watch([searchRecentMovements, movementTypeFilter], () => {
        // Debounce para a pesquisa de texto
        if (searchRecentMovements.timeout) {
            clearTimeout(searchRecentMovements.timeout);
        }
        searchRecentMovements.timeout = setTimeout(() => {
            applyRecentMovementsFilters();
        }, 300); // Espera 300ms após a digitação
        if (movementTypeFilter.value) { // Aplica filtro de tipo imediatamente
            applyRecentMovementsFilters();
        }
    });

    watch(recentMovementsCurrentPage, () => {
        fetchRecentMovements(); // Re-busca dados ao mudar a página
    });


    onMounted(() => {
        fetchWarehouseData(); // Carrega os dados dos 3 blocos principais
        fetchRecentMovements(); // Carrega a nova tabela de movimentações
    });
</script>

<template>
    <div class="mb-6">
        <h2 class="text-xl font-bold text-neutral-800 border-l-4 border-blue-500 pl-4 mb-4">📦
            Almoxarifado em Destaque</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div
                class="bg-white rounded-lg shadow-sm p-5 border border-blue-100 flex flex-col items-center justify-center text-center">
                <h3 class="text-xl font-semibold text-blue-700 mb-3">🔻 Itens com Baixo Estoque</h3>
                <ul v-if="lowStockItems.length" class="space-y-2 text-base text-neutral-700 w-full">
                    <li v-for="item in lowStockItems" :key="item.id"
                        class="flex justify-between items-center bg-blue-50 p-2 rounded">
                        <span class="font-medium">{{ item.name }}</span>
                        <span class="text-neutral-500 text-sm">({{ item.quantity }} {{ item.measurement_unit }})</span>
                    </li>
                </ul>
                <p v-else class="text-base text-neutral-500 mt-4">Nenhum item com baixo estoque.</p>
            </div>

            <div
                class="bg-white rounded-lg shadow-sm p-5 border border-green-100 flex flex-col items-center justify-center text-center">
                <h3 class="text-xl font-semibold text-green-700 mb-3">🔥 Mais Usados/Vendidos Hoje</h3>
                <ul v-if="topItemsToday.length" class="space-y-2 text-base text-neutral-700 w-full">
                    <li v-for="item in topItemsToday" :key="item.id"
                        class="flex justify-between items-center bg-green-50 p-2 rounded">
                        <span class="font-medium">{{ item.name }}</span>
                        <span class="text-neutral-500 text-sm">({{ item.total_quantity }} usos/vendas)</span>
                    </li>
                </ul>
                <p v-else class="text-base text-neutral-500 mt-4">Nenhum registro hoje.</p>
            </div>

            <div
                class="bg-white rounded-lg shadow-sm p-5 border border-purple-100 flex flex-col items-center justify-center text-center">
                <h3 class="text-xl font-semibold text-purple-700 mb-3">🆕 Últimos Itens Movimentados</h3>
                <ul v-if="latestItems.length" class="space-y-2 text-base text-neutral-700 w-full">
                    <li v-for="item in latestItems" :key="item.id"
                        class="flex justify-between items-center bg-purple-50 p-2 rounded">
                        <span class="font-medium">{{ item.name }}</span>
                        <span class="text-neutral-500 text-sm">({{ formatDate(item.updated_at, 'short_date') }})</span>
                    </li>
                </ul>
                <p v-else class="text-base text-neutral-500 mt-4">Nenhum novo item recente.</p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border border-orange-200">
            <h3 class="text-xl font-bold text-orange-700 border-l-4 border-orange-500 pl-4 mb-6">🔄 Movimentações
                Recentes (Últimos 7 Dias)</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label for="search-recent-movements"
                        class="block text-sm font-medium text-neutral-700 mb-1">Pesquisar (Motivo, Entidade,
                        Usuário):</label>
                    <input id="search-recent-movements" type="text" v-model="searchRecentMovements"
                        placeholder="Pesquisar..." class="simple-input w-full">
                </div>
                <div>
                    <label for="movement-type-filter" class="block text-sm font-medium text-neutral-700 mb-1">Tipo de
                        Movimentação:</label>
                    <select id="movement-type-filter" v-model="movementTypeFilter" class="simple-select w-full">
                        <option value="all">Todos</option>
                        <option value="entrada">Entrada</option>
                        <option value="saida">Saída/Venda</option>
                        <option value="servico">Serviço</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table v-if="recentMovements.length" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tipo</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Motivo / Entidade</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Usuário</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Data</th>
                            <!--<th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ações</th>-->
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="movement in recentMovements" :key="movement.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-neutral-900">{{ movement.id
                            }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-700 capitalize">
                                {{
                                    movement.type === 0 ? 'Entrada' :
                                        (movement.type === 1 ? 'Serviço' :
                                            (movement.type === 3 ? 'Saída (Uso)' :
                                                (movement.type === 4 ? 'Saída (Venda)' : 'Outro')))
                                }}
                            </td>
                            <td class="px-6 py-4 text-sm text-neutral-700">
                                <span class="font-semibold">{{ movement.motive }}</span>
                                <span v-if="movement.entity_name" class="block text-xs text-neutral-500">({{
                                    movement.entity_name }})</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-700">{{ movement.user_name }} {{
                                movement.user_surname }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500">{{
                                formatDate(movement.date, 'short_date') }}</td>
                            <!--<td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <Link :href="route('services.show', movement.id)" target="_blank"
                                    class="text-orange-600 hover:text-orange-900">Ver</Link>
                            </td>-->
                        </tr>
                    </tbody>
                </table>
                <p v-else class="text-sm text-neutral-500 text-center py-6">Nenhuma movimentação recente encontrada para
                    os filtros selecionados.</p>
            </div>

            <div v-if="recentMovementsLastPage > 1" class="flex justify-center mt-6 space-x-1 flex-wrap">
                <button @click="goToRecentMovementsPage(1)" :disabled="recentMovementsCurrentPage === 1"
                    class="px-4 py-2 border rounded-md text-sm transition-colors duration-150 ease-in-out"
                    :class="{ 'bg-gray-200 text-gray-500 cursor-not-allowed': recentMovementsCurrentPage === 1, 'hover:bg-orange-100 hover:text-orange-700': recentMovementsCurrentPage !== 1 }">
                    Primeira
                </button>
                <button @click="goToRecentMovementsPage(recentMovementsCurrentPage - 1)"
                    :disabled="recentMovementsCurrentPage === 1"
                    class="px-4 py-2 border rounded-md text-sm transition-colors duration-150 ease-in-out"
                    :class="{ 'bg-gray-200 text-gray-500 cursor-not-allowed': recentMovementsCurrentPage === 1, 'hover:bg-orange-100 hover:text-orange-700': recentMovementsCurrentPage !== 1 }">
                    Anterior
                </button>

                <template v-for="page in recentMovementsPaginationLinks" :key="page">
                    <span v-if="page === '...'" class="px-4 py-2 text-sm text-gray-500">...</span>
                    <button v-else @click="goToRecentMovementsPage(page)"
                        class="px-4 py-2 border rounded-md text-sm transition-colors duration-150 ease-in-out"
                        :class="{ 'bg-orange-600 text-white border-orange-600': page === recentMovementsCurrentPage, 'bg-gray-100 text-orange-700 hover:bg-orange-200 hover:text-orange-800': page !== recentMovementsCurrentPage }">
                        {{ page }}
                    </button>
                </template>

                <button @click="goToRecentMovementsPage(recentMovementsCurrentPage + 1)"
                    :disabled="recentMovementsCurrentPage === recentMovementsLastPage"
                    class="px-4 py-2 border rounded-md text-sm transition-colors duration-150 ease-in-out"
                    :class="{ 'bg-gray-200 text-gray-500 cursor-not-allowed': recentMovementsCurrentPage === recentMovementsLastPage, 'hover:bg-orange-100 hover:text-orange-700': recentMovementsCurrentPage !== recentMovementsLastPage }">
                    Próxima
                </button>
                <button @click="goToRecentMovementsPage(recentMovementsLastPage)"
                    :disabled="recentMovementsCurrentPage === recentMovementsLastPage"
                    class="px-4 py-2 border rounded-md text-sm transition-colors duration-150 ease-in-out"
                    :class="{ 'bg-gray-200': recentMovementsCurrentPage === recentMovementsLastPage, 'hover:bg-orange-100 hover:text-orange-700': recentMovementsCurrentPage !== recentMovementsLastPage }">
                    Última
                </button>
            </div>
        </div>
    </div>
</template>