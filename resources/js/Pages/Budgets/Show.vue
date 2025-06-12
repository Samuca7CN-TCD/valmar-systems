<script setup>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import { Head, Link, useForm, router } from '@inertiajs/vue3';
    import { computed, ref } from 'vue';
    import {
        PencilIcon,
        ArrowPathIcon, // Ícone para "recriar/revisar"
        PaperAirplaneIcon, // Ícone para enviar
        CheckCircleIcon, // Ícone para aprovar
        XCircleIcon, // Ícone para rejeitar
        PrinterIcon,
        DocumentDuplicateIcon,
        ArrowUturnLeftIcon // Ícone para voltar
    } from '@heroicons/vue/24/outline'; // Ajustei os ícones
    import { useToast } from 'vue-toast-notification';
    import 'vue-toast-notification/dist/theme-sugar.css';
    import { toMoney, formatDate } from '@/general.js';
    import BudgetRejectionCancellationModal from '@/Components/Budgets/BudgetRejectionCancellationModal.vue';

    const props = defineProps({
        page: Object,
        budget: Object,
        budgetChain: Array, // Nova prop: a lista de orçamentos na mesma cadeia
        currentBudgetId: Number, // Nova prop: o ID do orçamento sendo visualizado
    });

    const $toast = useToast();

    // Variáveis para controle do modal de rejeição/cancelamento
    const showActionModal = ref(false);
    const modalBudget = useForm({
        id: null,
        rejection_reason: null,
        cancellation_reason: null,
        actionType: null, // 'reject' ou 'cancel'
    });

    // Propriedade computada para organizar o histórico de orçamentos
    const sortedBudgetChain = computed(() => {
        // Ordena a cadeia de orçamentos pela data de criação para exibir cronologicamente
        return [...props.budgetChain].sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
    });

    const openActionModal = (budget_id, actionType) => {
        modalBudget.id = budget_id;
        modalBudget.actionType = actionType;
        modalBudget.rejection_reason = null;
        modalBudget.cancellation_reason = null;
        showActionModal.value = true;
    };

    const closeActionModal = () => {
        showActionModal.value = false;
        modalBudget.reset();
    };

    const submitAction = () => {
        if (modalBudget.actionType === 'reject') {
            rejectBudget(modalBudget.id, modalBudget.rejection_reason);
        } else if (modalBudget.actionType === 'cancel') {
            cancelBudget(modalBudget.id, modalBudget.cancellation_reason);
        }
    };

    const approveBudget = () => {
        if (confirm('Tem certeza que deseja APROVAR este orçamento e gerar um serviço?')) {
            router.post(route('budgets.approve', props.budget.id), {}, {
                preserveScroll: true,
                onSuccess: () => $toast.success('Orçamento aprovado e serviço gerado!'),
                onError: (errors) => {
                    console.error(errors);
                    $toast.error(errors.message || 'Erro ao aprovar orçamento.');
                }
            });
        }
    };

    const sendBudget = () => {
        if (confirm('Tem certeza que deseja marcar este orçamento como ENVIADO?')) {
            router.post(route('budgets.send', props.budget.id), {}, {
                preserveScroll: true,
                onSuccess: () => $toast.success('Orçamento marcado como enviado!'),
                onError: (errors) => {
                    console.error(errors);
                    $toast.error(errors.message || 'Erro ao marcar como enviado.');
                }
            });
        }
    };

    const rejectBudget = (budget_id, reason) => {
        if (!reason || reason.length < 10) {
            $toast.error('O motivo da rejeição deve ter pelo menos 10 caracteres.');
            return;
        }
        if (confirm('Tem certeza que deseja REJEITAR este orçamento?')) {
            router.post(route('budgets.reject', budget_id), { rejection_reason: reason }, {
                preserveScroll: true,
                onSuccess: () => {
                    $toast.success('Orçamento rejeitado!');
                    closeActionModal();
                },
                onError: (errors) => {
                    console.error(errors);
                    $toast.error(errors.message || 'Erro ao rejeitar orçamento.');
                }
            });
        }
    };


    const cancelBudget = (budget_id, reason) => {
        if (!reason || reason.length < 10) {
            $toast.error('O motivo do cancelamento deve ter pelo menos 10 caracteres.');
            return;
        }
        if (confirm('Tem certeza que deseja CANCELAR este orçamento?')) {
            router.post(route('budgets.cancel', budget_id), { cancellation_reason: reason }, {
                preserveScroll: true,
                onSuccess: () => {
                    $toast.success('Orçamento cancelado!');
                    closeActionModal();
                },
                onError: (errors) => {
                    console.error(errors);
                    $toast.error(errors.message || 'Erro ao cancelar orçamento.');
                }
            });
        }
    };

    // Duplicar orçamento (abre formulário de criação com dados preenchidos)
    const duplicateBudget = () => {
        if (confirm('Tem certeza que deseja DUPLICAR este orçamento? Isso criará um NOVO orçamento sem vínculo.')) {
            router.get(route('budgets.create', { duplicate_from: props.budget.id }));
        }
    };

    // Recriar orçamento (cancela o atual e cria um novo vinculado)
    const recreateBudget = () => {
        if (confirm('Tem certeza que deseja RECRIAR este orçamento? O orçamento atual será CANCELADO e um NOVO orçamento será criado como "Correção" vinculado a este.')) {
            router.post(route('budgets.recreate', props.budget.id), {}, {
                // Remova o onSuccess. O Inertia automaticamente fará o redirect do backend.
                // O toast de sucesso virá do backend como uma flash message se configurado.
                preserveScroll: true,
                onError: (errors) => {
                    console.error(errors);
                    $toast.error(errors.message || 'Erro ao recriar orçamento.');
                }
            });
        }
    };

    const printBudget = () => {
        window.open(route('budgets.print', props.budget.id), '_blank');
    };

    // Computed property para o status do orçamento
    const statusClass = computed(() => {
        switch (props.budget.status) {
            case 'Rascunho': return 'bg-gray-100 text-gray-800';
            case 'Enviado': return 'bg-blue-100 text-blue-800';
            case 'Aprovado': return 'bg-green-100 text-green-800';
            case 'Rejeitado': return 'bg-orange-100 text-orange-800'; // Novo status
            case 'Cancelado': return 'bg-red-100 text-red-800';
            case 'Expirado': return 'bg-purple-100 text-purple-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    });

    const getProcedureUser = (procedure_type) => {
        const procedure = props.budget.procedures.find(p => p.action_id === procedure_type); // 1 = criação, 2 = aprovação, etc.
        return procedure ? procedure.user.name : '-';
    };

    const getProcedureDate = (procedure_type) => {
        const procedure = props.budget.procedures.find(p => p.action_id === procedure_type);
        return procedure ? formatDate(procedure.created_at, 'reading_date_time') : '-';
    };
</script>

<template>

    <Head :title="`Orçamento #${budget.id}`" />

    <AppLayout :page="page">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Detalhes do Orçamento #{{ budget.id }}
                </h2>
                <Link :href="route('budgets.index')"
                    class="text-gray-600 hover:text-gray-900 flex items-center space-x-2">
                <ArrowUturnLeftIcon class="w-4 h-4" />
                <span>Voltar para Orçamentos</span>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
                        <div class="flex flex-wrap gap-3 mb-6 border-b pb-4 items-center justify-between">
                            <h3 class="text-xl font-bold text-gray-700">Ações do Orçamento</h3>
                            <div class="flex flex-wrap gap-2 select-none">

                                <Link v-if="['Rascunho'].includes(budget.status)"
                                    :href="route('budgets.edit', budget.id)"
                                    class="inline-flex items-center px-2 py-1 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 focus:bg-yellow-600 active:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <PencilIcon class="w-4 h-4 mr-2" /> Editar
                                </Link>

                                <button v-if="['Rascunho'].includes(budget.status)" @click="sendBudget()"
                                    class="inline-flex items-center px-2 py-1 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 focus:bg-blue-600 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <PaperAirplaneIcon class="w-4 h-4 mr-2" /> Marcar como Enviado
                                </button>

                                <button v-if="['Enviado'].includes(budget.status)"
                                    @click="openActionModal(budget.id, 'reject')"
                                    class="inline-flex items-center px-2 py-1 bg-orange-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-600 focus:bg-orange-600 active:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <XCircleIcon class="w-4 h-4 mr-2" /> Rejeitar
                                </button>

                                <button v-if="['Enviado'].includes(budget.status)" @click="approveBudget()"
                                    class="inline-flex items-center px-2 py-1 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 focus:bg-green-600 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <CheckCircleIcon class="w-4 h-4 mr-2" /> Aprovar
                                </button>

                                <button @click="duplicateBudget()"
                                    class="inline-flex items-center px-2 py-1 bg-purple-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-600 focus:bg-purple-600 active:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <DocumentDuplicateIcon class="w-4 h-4 mr-2" /> Duplicar (Novo)
                                </button>

                                <button v-if="['Enviado'].includes(budget.status)" @click="recreateBudget()"
                                    class="inline-flex items-center px-2 py-1 bg-indigo-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-600 focus:bg-indigo-600 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <ArrowPathIcon class="w-4 h-4 mr-2" /> Recriar (Revisão)
                                </button>

                                <button @click="printBudget()"
                                    class="inline-flex items-center px-2 py-1 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:bg-gray-600 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <PrinterIcon class="w-4 h-4 mr-2" /> Imprimir
                                </button>

                                <template
                                    v-if="!['Aprovado', 'Rejeitado', 'Cancelado'].includes(budget.status) && $page.props.auth.user.hierarchy < 2">
                                    <button @click="openActionModal(budget.id, 'cancel')"
                                        class="inline-flex items-center px-2 py-1 bg-red-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-600 focus:bg-red-600 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <XCircleIcon class="w-4 h-4 mr-2" /> Cancelar Orçamento
                                    </button>
                                </template>
                            </div>
                        </div>

                        <div v-if="budgetChain && budgetChain.length > 1"
                            class="bg-gray-50 p-4 rounded-lg shadow-sm mb-6">
                            <h4 class="text-lg font-semibold text-gray-800 mb-3">Histórico de Recriações</h4>
                            <div class="flex flex-wrap gap-2 items-center">
                                <template v-for="(budgetItem, index) in sortedBudgetChain" :key="budgetItem.id">
                                    <Link :href="route('budgets.show', budgetItem.id)" preserve-scroll class="px-3 py-1 rounded-full text-xs font-semibold
                                        transition ease-in-out duration-150
                                        " :class="{
                                            'bg-teal-600 text-white shadow-md': budgetItem.id === currentBudgetId,
                                            'bg-gray-200 text-gray-700 hover:bg-gray-300': budgetItem.id !== currentBudgetId
                                        }">
                                    {{ budgetItem.title }} (#{{ budgetItem.id }})
                                    <span class="ml-1" :class="{
                                        'text-red-300': budgetItem.status === 'Cancelado',
                                        'text-orange-300': budgetItem.status === 'Rejeitado',
                                        'text-green-300': budgetItem.status === 'Aprovado',
                                        'text-blue-300': budgetItem.status === 'Enviado',
                                        'text-neutral-300': budgetItem.status === 'Rascunho',
                                    }">
                                        ({{ budgetItem.status }})
                                    </span>
                                    </Link>
                                    <span v-if="index < sortedBudgetChain.length - 1" class="text-gray-400">→</span>
                                </template>
                            </div>
                            <p class="text-sm text-gray-500 mt-2">
                                Clique para navegar entre os orçamentos da mesma cadeia de recriação.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                                <h4 class="text-lg font-semibold text-gray-800 mb-3">Informações Gerais</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Título:</p>
                                        <p class="text-sm text-gray-900 font-bold">{{ budget.title }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Status:</p>
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            :class="statusClass">
                                            {{ budget.status }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Valor Total:</p>
                                        <p class="text-sm text-gray-900 font-bold">{{ toMoney(budget.total_value) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Data do Orçamento:</p>
                                        <p class="text-sm text-gray-900">{{ formatDate(budget.budget_date, true) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Validade (dias):</p>
                                        <p class="text-sm text-gray-900">{{ budget.validity }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Tipo de Orçamento:</p>
                                        <p class="text-sm text-gray-900">{{ budget.budget_type }}</p>
                                    </div>
                                    <div v-if="budget.original_budget_id">
                                        <p class="text-sm font-medium text-gray-500">Orçamento Original:</p>
                                        <Link :href="route('budgets.show', budget.original_budget_id)"
                                            class="text-sm text-blue-600 hover:underline">
                                        #{{ budget.original_budget_id }}
                                        </Link>
                                    </div>
                                    <div v-if="budget.generated_service_id">
                                        <p class="text-sm font-medium text-gray-500">Serviço Gerado:</p>
                                        <a :href="route('services.show', budget.generated_service_id)" target="_blank"
                                            class="text-sm text-blue-600 hover:underline">
                                            Ver Serviço #{{ budget.generated_service_id }}
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                                <h4 class="text-lg font-semibold text-gray-800 mb-3">Informações do Cliente</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Nome do Cliente:</p>
                                        <p class="text-sm text-gray-900">{{ budget.client_name }}</p>
                                    </div>
                                    <div v-if="budget.client_email">
                                        <p class="text-sm font-medium text-gray-500">Email:</p>
                                        <p class="text-sm text-gray-900">{{ budget.client_email }}</p>
                                    </div>
                                    <div v-if="budget.client_phone">
                                        <p class="text-sm font-medium text-gray-500">Telefone:</p>
                                        <p class="text-sm text-gray-900">{{ budget.client_phone }}</p>
                                    </div>
                                    <div v-if="budget.client_cpf_cnpj">
                                        <p class="text-sm font-medium text-gray-500">CPF/CNPJ:</p>
                                        <p class="text-sm text-gray-900">{{ budget.client_cpf_cnpj }}</p>
                                    </div>
                                    <div v-if="budget.client_address">
                                        <p class="text-sm font-medium text-gray-500">Endereço:</p>
                                        <p class="text-sm text-gray-900">{{ budget.client_address }}</p>
                                    </div>
                                    <div v-if="budget.client_cep">
                                        <p class="text-sm font-medium text-gray-500">CEP:</p>
                                        <p class="text-sm text-gray-900">{{ budget.client_cep }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="budget.rejection_reason" class="bg-gray-50 p-4 rounded-lg shadow-sm col-span-2 mb-5">
                            <h4 class="text-lg font-semibold text-gray-800 mb-3">Motivo <span
                                    v-if="budget.deleted_at?.length">do
                                    Cancelamento</span><span v-else>da
                                    Rejeição</span></h4>
                            <p class="text-sm font-medium text-gray-500">{{ budget.rejection_reason }}</p>
                            <p v-show="budget.deleted_at?.length" class="text-sm font-medium text-gray-500">Data do
                                cancelamento: {{
                                    formatDate(budget.deleted_at, 'read') }}</p>
                        </div>

                        <div class="bg-white p-4 rounded-lg shadow-sm mb-6 border border-gray-200">
                            <h4 class="text-lg font-semibold text-gray-800 mb-3">Itens do Orçamento</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Item</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Preço</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="item in budget.items" :key="item.id">
                                            <td class="px-6 py-4 text-sm  text-gray-900">{{
                                                item.item_name }}</td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">
                                                {{ toMoney(item.subtotal) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-4 flex justify-end items-center space-x-4 text-sm">
                                <p class="text-gray-600">Desconto:</p>
                                <p class="font-semibold text-red-600">{{ toMoney(budget.discount_amount) }}</p>
                                <p class="text-gray-600">Acréscimo:</p>
                                <p class="font-semibold text-green-600">{{ toMoney(budget.additional_amount) }}</p>
                                <p class="text-gray-800 text-lg font-bold">Total Final:</p>
                                <p class="text-gray-900 text-lg font-bold">{{ toMoney(budget.total_value) }}</p>
                            </div>
                        </div>

                        <div v-if="budget?.description" class="bg-gray-50 p-4 rounded-lg shadow-sm mb-6">
                            <h4 class="text-lg font-semibold text-gray-800 mb-3">Observações</h4>
                            <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ budget.description }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                                <h4 class="text-lg font-semibold text-gray-800 mb-3">Prazos e Responsabilidades</h4>
                                <div class="space-y-2">
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Prazo de Entrega:</p>
                                        <p class="text-sm text-gray-900">{{ budget.deadline }} {{
                                            budget.deadline_type
                                        }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Início do Prazo:</p>
                                        <p class="text-sm text-gray-900 whitespace-pre-wrap">{{
                                            budget.deadline_start_description }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Responsabilidade do Contratado:
                                        </p>
                                        <p class="text-sm text-gray-900 whitespace-pre-wrap">{{
                                            budget.contracted_responsibility
                                        }}</p>
                                    </div>
                                    <div v-if="budget.contractor_responsibility">
                                        <p class="text-sm font-medium text-gray-500">Responsabilidade do
                                            Contratante:
                                        </p>
                                        <p class="text-sm text-gray-900 whitespace-pre-wrap">{{
                                            budget.contractor_responsibility
                                        }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                                <h4 class="text-lg font-semibold text-gray-800 mb-3">Informações Financeiras</h4>
                                <div class="space-y-2">
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Método de Pagamento:</p>
                                        <p class="text-sm text-gray-900 whitespace-pre-wrap">{{
                                            budget.payment_method_description }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Informações Bancárias:</p>
                                        <p class="text-sm text-gray-900 whitespace-pre-wrap">{{
                                            budget.bank_info_description }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!--<div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                            <h4 class="text-lg font-semibold text-gray-800 mb-3">Histórico do Orçamento</h4>
                            <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                                <li v-for="procedure in budget.procedures" :key="procedure.id">
                                    <span class="font-semibold">{{ procedure.user?.name || 'Sistema' }}</span>
                                    {{ procedure.action.description }} em
                                    {{ formatDate(procedure.created_at, 'reading_date_time') }}.
                                    <span v-if="procedure.rejection_reason" class="text-red-500">
                                        Motivo da Rejeição: {{ procedure.rejection_reason }}
                                    </span>
                                    <span v-if="procedure.cancellation_reason" class="text-red-500">
                                        Motivo do Cancelamento: {{ procedure.cancellation_reason }}
                                    </span>
                                </li>
                            </ul>
                            <div v-if="!budget.procedures.length" class="text-sm text-gray-500">
                                Sem histórico de procedimentos.
                            </div>
                        </div>-->

                    </div>
                </div>
            </div>
        </div>

        <BudgetRejectionCancellationModal :show="showActionModal" :budget="modalBudget" @submit="submitAction"
            @close="closeActionModal" />
    </AppLayout>
</template>