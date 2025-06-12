<script setup>
    import { ref, onMounted } from 'vue';
    import { formatDate, toMoney } from '@/general.js';
    import axios from 'axios'; // Manter axios

    const latestPayments = ref([]);
    const latestServices = ref([]);
    const upcomingDeadlines = ref([]);

    const fetchFinanceiroData = async () => {
        try {
            const response = await axios.get(route('dashboard.data.financeiro'));
            const data = response.data;
            latestPayments.value = data.latestPayments;
            latestServices.value = data.latestServices;
            upcomingDeadlines.value = data.upcomingDeadlines;
        } catch (error) {
            console.error('Erro ao carregar dados financeiros do dashboard:', error);
        }
    };

    onMounted(() => {
        fetchFinanceiroData();
    });
</script>
<template>
    <div class="mb-6">
        <h2 class="text-xl font-bold text-neutral-800 border-l-4 border-yellow-500 pl-4 mb-4">💰
            Financeiro</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow-sm p-5 border border-yellow-100">
                <h3 class="text-md font-semibold text-yellow-700 mb-2">💸 Últimos Pagamentos
                    Cadastrados
                </h3>
                <ul class="space-y-1 text-sm text-neutral-700">
                    <li v-for="payment in latestPayments" :key="payment.id">
                        {{ payment.entity_name || '-' }}: <span class="font-medium">{{
                            toMoney(payment.accounting?.partial_value) }}</span>
                        <span class="text-neutral-400"> ({{ formatDate(payment.date,
                            'short_date')
                        }})</span>
                    </li>
                </ul>
                <p v-if="!latestPayments.length" class="text-sm text-neutral-400 mt-2">Nenhum
                    pagamento
                    recente.</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-5 border border-red-100">
                <h3 class="text-md font-semibold text-red-700 mb-2">⚙️ Últimos Serviços
                    Cadastrados
                </h3>
                <ul class="space-y-1 text-sm text-neutral-700">
                    <li v-for="service in latestServices" :key="service.id">
                        {{ service.entity_name || '-' }}: <span class="font-medium">{{
                            toMoney(service.accounting?.partial_value) }}</span>
                        <span class="text-neutral-400"> ({{ formatDate(service.date,
                            'short_date')
                        }})</span>
                    </li>
                </ul>
                <p v-if="!latestServices.length" class="text-sm text-neutral-400 mt-2">Nenhum
                    serviço
                    recente.</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-5 border border-orange-100">
                <h3 class="text-md font-semibold text-orange-700 mb-2">⏰ Próximos Vencimentos
                </h3>
                <ul class="space-y-1 text-sm text-neutral-700">
                    <li v-for="upcoming in upcomingDeadlines" :key="upcoming.id">
                        {{ upcoming.entity_name || '-' }}: <span class="font-medium">{{
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
</template>