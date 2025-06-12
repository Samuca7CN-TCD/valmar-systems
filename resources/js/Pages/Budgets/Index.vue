<script setup>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import { Head, Link, useForm, router } from '@inertiajs/vue3';
    import { computed, ref, watch } from 'vue';
    import {
        PlusIcon,
        EyeIcon,
        PencilIcon,
        CheckBadgeIcon,
        XMarkIcon,
        DocumentDuplicateIcon,
        PrinterIcon,
        MagnifyingGlassIcon,
        ChevronLeftIcon,
        ChevronRightIcon,
        PaperAirplaneIcon,
    } from '@heroicons/vue/24/outline';

    import { toMoney, formatDate } from '@/general.js';
    import { useToast } from 'vue-toast-notification';
    import 'vue-toast-notification/dist/theme-sugar.css';

    import BudgetRejectionCancellationModal from '@/Components/Budgets/BudgetRejectionCancellationModal.vue';
    import FloatButton from '@/Components/FloatButton.vue';


    const props = defineProps({
        page: Object,
        budgets_list: Object,
        filters: Object,
    });

    const $toast = useToast();

    const search_term = ref(props.filters.search || '');
    const status_filter = ref(props.filters.status || '');
    const client_filter = ref(props.filters.client || '');

    let searchTimeout = null;
    watch([search_term, status_filter, client_filter], () => {
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
        searchTimeout = setTimeout(() => {
            router.get(route('budgets.index'), {
                search: search_term.value,
                status: status_filter.value,
                client: client_filter.value,
            }, { preserveState: true, replace: true });
        }, 300);
    });

    const show_user_data = ref(false);

    const showActionModal = ref(false);
    const modalBudget = useForm({
        id: null,
        rejection_reason: null,
        cancellation_reason: null,
        actionType: null,
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

    const approveBudget = (budget_id) => {
        if (confirm('Tem certeza que deseja APROVAR este orçamento e gerar um serviço?')) {
            router.post(route('budgets.approve', budget_id), {}, {
                preserveScroll: true,
                onSuccess: () => $toast.success('Orçamento aprovado e serviço gerado!'),
                onError: (errors) => {
                    console.error(errors);
                    $toast.error(errors.message || 'Erro ao aprovar orçamento.');
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

    const duplicateBudget = (budget_id) => {
        if (confirm('Tem certeza que deseja DUPLICAR este orçamento?')) {
            // Redireciona para a rota de criação, passando o budget_id como parâmetro de consulta
            router.get(route('budgets.create', { duplicate_from: budget_id }));
        }
    };

    const printBudget = (budget_id) => {
        window.open(route('budgets.print', budget_id), '_blank');
    };

    const sendBudget = (budget_id) => {
        if (confirm('Tem certeza que deseja marcar este orçamento como ENVIADO?')) {
            router.post(route('budgets.send', budget_id), {}, {
                preserveScroll: true,
                onSuccess: () => $toast.success('Orçamento marcado como enviado!'),
                onError: (errors) => {
                    console.error(errors);
                    $toast.error(errors.message || 'Erro ao marcar como enviado.');
                }
            });
        }
    };

    const goToPage = (url) => {
        if (url) {
            router.get(url, {
                search: search_term.value,
                status: status_filter.value,
                client: client_filter.value,
            }, { preserveState: true, replace: true });
        }
    };
</script>

<template>

    <Head :title="page.name" />
    <AppLayout :page="page">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ page.name }}
            </h2>

            <Link :href="route('budgets.create')">
            <FloatButton :icon="'plus'" title="Cadastrar Orçamento" class="print:hidden" />
            </Link>
        </template>

        <div class="pt-12 print:hidden print:pt-0">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 print:w-full">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                    <div class="flex flex-col sm:flex-row sm:items-center space-y-4 sm:space-y-0 sm:space-x-4">
                        <div class="flex items-center space-x-3 flex-grow">
                            <MagnifyingGlassIcon class="w-4 h-4 text-gray-500" />
                            <input type="text" name="search_term" id="search_term" autocomplete="on"
                                class="simple-input flex-grow" autofocus="true" placeholder="Pesquisar termo..."
                                v-model="search_term" />
                        </div>

                        <div class="flex items-center space-x-2">
                            <label for="status_filter"
                                class="text-sm font-medium text-gray-700 whitespace-nowrap">Status:</label>
                            <select id="status_filter" v-model="status_filter" class="simple-input">
                                <option value="">Todos</option>
                                <option value="Rascunho">Rascunho</option>
                                <option value="Enviado">Enviado</option>
                                <option value="Aprovado">Aprovado</option>
                                <option value="Rejeitado">Rejeitado</option>
                                <option value="Cancelado">Cancelado</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="py-12 print:py-0">
            <div class="max-w-fit mx-auto print:max-w-full">
                <div class="w-full flex justify-end pb-3 print:hidden px-6 sm:px-0">
                    <button v-if="!show_user_data" class="text-blue-500 text-xs"
                        @click="show_user_data = !show_user_data">Mostrar
                        dados de
                        usuário</button>
                    <button v-if="show_user_data" class="text-red-500 text-xs"
                        @click="show_user_data = !show_user_data">Esconder dados de usuário</button>
                </div>
                <div class="px-0 sm:px-0 print:px-0">
                    <div class="bg-white sm:rounded-xl print:shadow-none">
                        <div class="flex flex-col">
                            <div class="overflow-x-auto ">
                                <div class="inline-block min-w-full ">
                                    <div class="overflow-hidden">
                                        <table class="max-w-fit text-left text-sm font-light">
                                            <thead class="border-b bg-gray-50 font-medium">
                                                <tr>
                                                    <th scope="col" class="px-6 py-3 text-center">#</th>
                                                    <th scope="col" class="px-6 py-3 text-left">Titulo</th>
                                                    <th scope="col" class="px-6 py-3 text-left">Cliente</th>
                                                    <th scope="col" class="px-6 py-3 text-left">Itens</th>
                                                    <th scope="col" class="px-6 py-3 text-center">Valor Total</th>
                                                    <th scope="col" class="px-6 py-3 text-center">Data Orçamento</th>
                                                    <th scope="col" class="px-6 py-3 text-center">Status</th>
                                                    <th scope="col" class="px-6 py-3 text-center print:hidden">Ações
                                                    </th>
                                                    <th scope="col" class="px-6 py-3 text-center print:hidden"
                                                        v-if="show_user_data">Criado Em</th>
                                                    <th scope="col" class="px-6 py-3 text-center print:hidden"
                                                        v-if="show_user_data">Criado Por</th>
                                                    <th scope="col" class="px-6 py-3 text-center print:hidden"
                                                        v-if="show_user_data">Modificado Em</th>
                                                    <th scope="col" class="px-6 py-3 text-center print:hidden"
                                                        v-if="show_user_data">Modificado Por</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-if="budgets_list.data.length" v-for="budget in budgets_list.data"
                                                    :key="budget.id"
                                                    class="border-b transition duration-300 ease-in-out hover:bg-neutral-100">
                                                    <td class="whitespace-nowrap px-6 py-4 text-center font-medium">{{
                                                        budget.id }}
                                                    </td>
                                                    <td class="whitespace-nowrap px-6 py-4 text-left">{{
                                                        budget.title
                                                        }}</td>
                                                    <td class="whitespace-nowrap px-6 py-4 text-left">{{
                                                        budget.client_name
                                                        }}</td>
                                                    <td class="whitespace-nowrap px-6 py-4 text-left"
                                                        :title="budget.items.map(item => item.item_name).join('; ')">
                                                        <div class="max-w-xs truncate">
                                                            {{budget.items.map(item => item.item_name).join('; ')}}
                                                        </div>
                                                    </td>
                                                    <td class="whitespace-nowrap px-6 py-4 text-center">{{
                                                        toMoney(budget.total_value) }}</td>
                                                    <td class="whitespace-nowrap px-6 py-4 text-center">{{
                                                        formatDate(budget.budget_date, true) }}</td>
                                                    <td class="whitespace-nowrap px-6 py-4 text-center">
                                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                            :class="{
                                                                'bg-gray-100 text-gray-800': budget.status === 'Rascunho',
                                                                'bg-blue-100 text-blue-800': budget.status === 'Enviado',
                                                                'bg-green-100 text-green-800': budget.status === 'Aprovado',
                                                                'bg-orange-100 text-orange-800': budget.status === 'Rejeitado',
                                                                'bg-red-100 text-red-800': budget.status === 'Cancelado',
                                                            }">
                                                            {{ budget.status }}
                                                        </span>
                                                    </td>
                                                    <td class="whitespace-nowrap px-6 py-4 text-center print:hidden">
                                                        <div class="flex justify-center items-center space-x-5">
                                                            <Link :href="route('budgets.show', budget.id)"
                                                                class="hover:text-blue-700" title="Ver Orçamento">
                                                            <EyeIcon class="w-4 h-4" />
                                                            </Link>

                                                            <template
                                                                v-if="['Rascunho'].includes(budget.status) && !budget.generated_service_id">
                                                                <Link :href="route('budgets.edit', budget.id)"
                                                                    class="hover:text-yellow-700"
                                                                    title="Editar Orçamento">
                                                                <PencilIcon class="w-4 h-4" />
                                                                </Link>

                                                                <button @click="sendBudget(budget.id)"
                                                                    class="hover:text-blue-700"
                                                                    title="Enviar Orçamento">
                                                                    <PaperAirplaneIcon class="w-4 h-4" />
                                                                </button>

                                                                <!--
                                                                <button @click="sendBudget(budget.id)"
                                                                    v-if="budget.status === 'Rascunho'"
                                                                    class="text-blue-500 hover:text-blue-700"
                                                                    title="Marcar como Enviado">
                                                                    <CheckBadgeIcon class="w-4 h-4" />
                                                                </button>
                                                                <button @click="approveBudget(budget.id)"
                                                                    class="text-green-500 hover:text-green-700"
                                                                    title="Aprovar Orçamento">
                                                                    <CheckBadgeIcon class="w-4 h-4" />
                                                                </button>
                                                                <button @click="openActionModal(budget.id, 'reject')"
                                                                    class="text-red-500 hover:text-red-700"
                                                                    title="Rejeitar Orçamento">
                                                                    <XMarkIcon class="w-4 h-4" />
                                                                </button>
                                                                -->
                                                            </template>

                                                            <button @click="duplicateBudget(budget.id)"
                                                                class="hover:text-purple-700"
                                                                title="Duplicar Orçamento">
                                                                <DocumentDuplicateIcon class="w-4 h-4" />
                                                            </button>

                                                            <button
                                                                v-if="!['Aprovado', 'Rejeitado', 'Cancelado'].includes(budget.status) && $page.props.auth.user.hierarchy < 2"
                                                                @click="openActionModal(budget.id, 'cancel')"
                                                                class="hover:text-red-700" title="Cancelar Orçamento">
                                                                <XMarkIcon class="w-4 h-4" />
                                                            </button>
                                                            <!--<button @click="printBudget(budget.id)"
                                                                class="text-gray-500 hover:text-gray-700"
                                                                title="Imprimir Orçamento">
                                                                <PrinterIcon class="w-4 h-4" />
                                                            </button>-->
                                                        </div>
                                                    </td>
                                                    <td class="whitespace-nowrap px-6 py-4 text-center print:hidden"
                                                        v-if="show_user_data">
                                                        {{ formatDate(budget.created_at, 'reading_date_time') }}
                                                    </td>
                                                    <td class="whitespace-nowrap px-6 py-4 text-center print:hidden"
                                                        v-if="show_user_data">
                                                        {{ budget.procedures[0]?.user?.name || '-' }}
                                                    </td>
                                                    <td class="whitespace-nowrap px-6 py-4 text-center print:hidden"
                                                        v-if="show_user_data">
                                                        {{ formatDate(budget.updated_at, 'reading_date_time') }}
                                                    </td>
                                                    <td class="whitespace-nowrap px-6 py-4 text-center print:hidden"
                                                        v-if="show_user_data">
                                                        {{ budget.procedures[budget.procedures?.length - 1]?.user?.name
                                                            ||
                                                            '-'
                                                        }}
                                                    </td>
                                                </tr>
                                                <tr v-else>
                                                    <td colspan="10"
                                                        class="whitespace-nowrap px-6 py-4 text-center text-gray-500">
                                                        Nenhum orçamento encontrado.
                                                    </td>
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
        </div>

        <div v-if="budgets_list.links.length > 3"
            class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 mt-6">
            <div class="flex flex-1 justify-between sm:hidden">
                <button :href="budgets_list.prev_page_url" :disabled="!budgets_list.prev_page_url"
                    @click="goToPage(budgets_list.prev_page_url)"
                    class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Previous</button>
                <button :href="budgets_list.next_page_url" :disabled="!budgets_list.next_page_url"
                    @click="goToPage(budgets_list.next_page_url)"
                    class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Next</button>
            </div>

            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing
                        {{ ' ' }}
                        <span class="font-medium">{{ budgets_list.from }}</span>
                        {{ ' ' }}
                        to
                        {{ ' ' }}
                        <span class="font-medium">{{ budgets_list.to }}</span>
                        {{ ' ' }}
                        of
                        {{ ' ' }}
                        <span class="font-medium">{{ budgets_list.total }}</span>
                        {{ ' ' }}
                        results
                    </p>
                </div>
                <div>
                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-xs" aria-label="Pagination">
                        <button :href="budgets_list.prev_page_url" :disabled="!budgets_list.prev_page_url"
                            @click="goToPage(budgets_list.prev_page_url)"
                            class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                            <span class="sr-only">Previous</span>
                            <ChevronLeftIcon class="size-5" aria-hidden="true" />
                        </button>

                        <template v-for="(link, index) in budgets_list.links">
                            <template
                                v-if="link.url && !['&laquo; Previous', 'Next &raquo;', '...'].includes(link.label)">
                                <button :key="index" :href="link.url" :disabled="!link.url" @click="goToPage(link.url)"
                                    aria-current="page"
                                    class="relative inline-flex items-center px-4 py-2 text-sm font-semibold focus:z-20 focus-visible:outline-2 focus-visible:outline-offset-2"
                                    :class="{
                                        'z-10 bg-indigo-600 text-white focus-visible:outline-indigo-600': link.active,
                                        'text-gray-900 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:outline-offset-0': !link.active,
                                    }">
                                    {{ link.label }}
                                </button>
                            </template>
                            <template v-else-if="!link.url && link.label === '...'">
                                <span
                                    class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-gray-300 ring-inset focus:outline-offset-0">...</span>
                            </template>
                        </template>

                        <button :href="budgets_list.next_page_url" :disabled="!budgets_list.next_page_url"
                            @click="goToPage(budgets_list.next_page_url)"
                            class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                            <span class="sr-only">Next</span>
                            <ChevronRightIcon class="size-5" aria-hidden="true" />
                        </button>
                    </nav>
                </div>
            </div>
        </div>


        <BudgetRejectionCancellationModal :show="showActionModal" :budget="modalBudget" @submit="submitAction"
            @close="closeActionModal" />

    </AppLayout>
</template>