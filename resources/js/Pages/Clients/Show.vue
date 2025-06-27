<script setup>
    import { Head, Link } from '@inertiajs/vue3';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import { toMoney, formatDate } from '@/general.js';
    import { BuildingOffice2Icon, UserIcon, MapPinIcon, ChatBubbleBottomCenterTextIcon, EnvelopeIcon, PhoneIcon, IdentificationIcon } from '@heroicons/vue/24/outline';

    const props = defineProps({
        page: {
            type: Object,
            required: true,
        },
        client: {
            type: Object,
            required: true,
        }
    });

    const formatFullAddress = (client) => {
        const parts = [
            client.address_street,
            client.address_number,
            client.address_complement,
        ].filter(Boolean).join(', ');

        const neighborhood = client.address_neighborhood;
        const cityState = [client.address_city, client.address_state].filter(Boolean).join(' - ');
        const postalCode = client.postal_code ? `CEP: ${client.postal_code}` : '';

        const finalAddress = [parts, neighborhood, cityState, postalCode].filter(Boolean).join('<br>');

        return finalAddress || 'Endereço não informado';
    }

</script>

<template>

    <Head :title="'Detalhes de ' + page.singular_name" />
    <AppLayout :page="page">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Perfil do Cliente: {{ client.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row justify-between md:items-start">
                            <div>
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <UserIcon v-if="client.type === 'fisica'" class="h-12 w-12 text-gray-500" />
                                        <BuildingOffice2Icon v-else class="h-12 w-12 text-gray-500" />
                                    </div>
                                    <div>
                                        <h3 class="text-2xl font-bold text-gray-900">{{ client.name }}</h3>
                                        <p v-if="client.type === 'juridica' && client.trade_name"
                                            class="text-sm text-gray-600">
                                            {{ client.trade_name }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex space-x-2 mt-4 md:mt-0">
                                <Link :href="route('clients.index')" class="simple-button-neutral">Voltar à Lista</Link>
                                <Link :href="route('clients.edit', client.id)" class="simple-button-blue">Editar</Link>
                            </div>
                        </div>

                        <div class="mt-6 border-t border-gray-200 pt-6">
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-8">
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500 flex items-center">
                                        <IdentificationIcon class="w-5 h-5 mr-2" />{{ client.type === 'fisica' ? 'CPF' :
                                            'CNPJ'
                                        }}
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ client.document }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500 flex items-center">
                                        <EnvelopeIcon class="w-5 h-5 mr-2" />E-mail
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ client.email || 'Não informado' }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500 flex items-center">
                                        <PhoneIcon class="w-5 h-5 mr-2" />Contatos
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ client.contacts && client.contacts.length
                                        ?
                                        client.contacts.join(' / ') : 'Não informado' }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500 flex items-center">
                                        <MapPinIcon class="w-5 h-5 mr-2" />Endereço
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900 leading-relaxed"
                                        v-html="formatFullAddress(client)">
                                    </dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500 flex items-center">
                                        <ChatBubbleBottomCenterTextIcon class="w-5 h-5 mr-2" />Observações
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900 italic">
                                        {{ client.observations || 'Nenhuma observação registrada.' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Histórico de Atividades -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Histórico do Cliente</h3>

                        <!-- Aba de Orçamentos -->
                        <div class="mb-8">
                            <h4 class="font-semibold text-lg text-gray-700 mb-4">Orçamentos</h4>
                            <div class="overflow-x-auto border rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                ID</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Título
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Data
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Status
                                            </th>
                                            <th
                                                class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                                Valor
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-if="client.budgets.length === 0">
                                            <td colspan="5" class="text-center py-4 text-gray-500">Nenhum orçamento
                                                encontrado.
                                            </td>
                                        </tr>
                                        <tr v-for="budget in client.budgets" :key="'budget-' + budget.id"
                                            class="hover:bg-gray-50">
                                            <td class="px-4 py-4 text-sm font-medium text-gray-600">#{{ budget.id }}
                                            </td>
                                            <td class="px-4 py-4 text-sm text-gray-800">{{ budget.title }}</td>
                                            <td class="px-4 py-4 text-sm text-gray-600">{{
                                                formatDate(budget.budget_date) }}
                                            </td>
                                            <td class="px-4 py-4 text-sm text-gray-600">{{ budget.status }}</td>
                                            <td class="px-4 py-4 text-sm text-gray-800 font-semibold text-right">{{
                                                toMoney(budget.total_value) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Aba de Movimentos (Serviços, Vendas, etc) -->
                        <div>
                            <h4 class="font-semibold text-lg text-gray-700 mb-4">Serviços e Vendas</h4>
                            <div class="overflow-x-auto border rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                ID</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Motivo
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Data
                                            </th>
                                            <th
                                                class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                                Valor
                                                Total</th>
                                            <th
                                                class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                                Valor
                                                Pendente</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-if="client.movements.length === 0">
                                            <td colspan="5" class="text-center py-4 text-gray-500">Nenhum movimento
                                                encontrado.
                                            </td>
                                        </tr>
                                        <tr v-for="movement in client.movements" :key="'movement-' + movement.id"
                                            class="hover:bg-gray-50">
                                            <td class="px-4 py-4 text-sm font-medium text-gray-600">#{{ movement.id }}
                                            </td>
                                            <td class="px-4 py-4 text-sm text-gray-800">{{ movement.motive }}</td>
                                            <td class="px-4 py-4 text-sm text-gray-600">{{ formatDate(movement.date) }}
                                            </td>
                                            <td class="px-4 py-4 text-sm text-gray-800 font-semibold text-right">{{
                                                toMoney(movement.accounting.total_value) }}</td>
                                            <td class="px-4 py-4 text-sm font-semibold text-right"
                                                :class="{ 'text-red-600': movement.accounting.partial_value > 0 }">{{
                                                    toMoney(movement.accounting.partial_value) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
