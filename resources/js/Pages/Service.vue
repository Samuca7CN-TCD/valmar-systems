<script setup>
    import AppLayout from '@/Layouts/AppLayout.vue'
    import CreateUpdatePayModal from '@/Components/Services/CreateUpdatePayModal.vue'
    import FloatButton from '@/Components/FloatButton.vue'
    import ExtraOptionsButton from '@/Components/ExtraOptionsButton.vue'
    import { Head, useForm, router, Link } from '@inertiajs/vue3'
    import { computed, onMounted, ref, watch } from 'vue' // Adicionado 'watch'
    import { BanknotesIcon, CheckBadgeIcon, DocumentTextIcon, MagnifyingGlassIcon, PencilIcon, XMarkIcon, ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline' // Adicionado ícones de paginação
    import { toMoney, formatDate, calcDeadlineDays } from '@/general.js'
    import { useToast } from 'vue-toast-notification';
    import 'vue-toast-notification/dist/theme-sugar.css';
    import MotivoAtrasoModal from '@/Components/Services/MotivoAtrasoModal.vue'
    import MotivoCancelamentoModal from '@/Components/Services/MotivoCancelamentoModal.vue'


    // =============================================
    // Informações exteriores
    const props = defineProps({
        page: Object,
        page_options: {
            type: Array,
            default: null,
        },
        services_list: Object, // É um objeto Paginator do Laravel
        sells_list: {
            type: Array,
            default: [],
        },
        show_service_id: {
            type: [String, Number],
            default: null,
        },
        filters: Object, // Prop para receber os filtros do controller
    })

    const $toast = useToast();
    // =============================================
    // Informações do OBJETO
    const service_data = useForm({
        'id': null,
        'previous_id': null,
        'title': '',
        'client_id': '',
        'total_value': 0,
        'service_value': 0,
        'partial_value': 0,
        'deadline': formatDate(new Date(Date.now() + 15 * 24 * 60 * 60 * 1000), 'new_date'),
        'observations': '',
        'service_status': 'Não Iniciado',
        'cancellation_reason': null,
        'delay_reason': '',
        'delayed': false,
        'completion_date': formatDate(new Date(), 'new_date'),
        'records_list': {
            'enable_records': false,
            'data': [],
        }
    })

    // O termo de busca agora é inicializado com o filtro vindo do controller
    const search_term = ref(props.filters?.search || '');
    const show_user_data = ref(false);
    let searchTimeout = null;

    // Lógica de busca agora é feita no backend com um "watch"
    watch(search_term, (newSearchTerm) => {
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
        searchTimeout = setTimeout(() => {
            router.get(route('services.index'), { search: newSearchTerm }, { preserveState: true, replace: true });
        }, 300); // Debounce de 300ms
    });

    // Esta computed property agora apenas aponta para os dados da página atual
    const filtered_services_list = computed(() => {
        return props.services_list.data;
    });

    // ATENÇÃO: Este total agora reflete apenas os itens da PÁGINA ATUAL.
    const total_services_amount = computed(() => {
        if (!props.services_list.data) return 0;
        return props.services_list.data.reduce((accumulator, service) => {
            return accumulator + service.accounting.partial_value;
        }, 0);
    });

    const deadlineClass = (deadline) => {
        const days = calcDeadlineDays(deadline);
        if (days === 0) return 'text-red-500';
        if (days > 0 && days < 8) return 'text-orange-500';
        if (days > 7 && days < 15) return 'text-yellow-500';
        if (days > 14 && days < 30) return 'text-green-500';
        return 'text-blue-500';
    }

    // =============================================
    // Controle de Modal
    const modal = ref({
        mode: 'create',
        show: false,
        get title() {
            switch (this.mode) {
                case 'create': return "Criar serviço"
                case 'update': return "Editar serviço"
                case 'pay': return "Pagar serviço"
                case 'see': return "Ver informações do serviço"
            }
        },
        get primary_button_txt() {
            switch (this.mode) {
                case 'create': return "Cadastrar"
                case 'update': return "Atualizar"
                case 'pay': return "Pagar"
                case 'see': return "Fechar"
            }
        }
    })

    const setServiceData = (service_id) => {
        // Procura o serviço dentro do array .data
        const service = props.services_list.data.find(service => service.id === parseInt(service_id));

        if (service) {
            const { id, previous_id, motive, client_id, client, service_value, entity_name, deadline, observations, cancellation_reason, delay_reason, delayed, completion_date, records, accounting, service_status } = service;

            const related_sell = props.sells_list.find(s => s.id === previous_id);
            const sell_value = related_sell ? parseFloat(related_sell.total_value) : 0;

            service_data.service_value = parseFloat(accounting.total_value) - sell_value;
            service_data.id = id;
            service_data.previous_id = previous_id;
            service_data.title = motive;
            service_data.client_id = client_id;
            service_data.client = client;
            service_data.total_value = accounting.total_value;
            service_data.service_value = accounting.total_value - props.sells_list?.find(el => el.id === previous_id)?.accounting?.total_value || accounting.total_value;
            service_data.partial_value = accounting.partial_value;
            service_data.deadline = deadline;
            service_data.observations = observations;
            service_data.service_status = service_status;
            service_data.cancellation_reason = cancellation_reason;
            service_data.delay_reason = delay_reason;
            service_data.completion_date = formatDate(new Date(), 'new_date');
            service_data.delayed = delayed;
            service_data.records_list.enable_records = Boolean(records.length);
            service_data.records_list.data = records.map((record) => { return useForm(record) })
        }
    }

    // O restante dos seus métodos (openModal, closeModal, CRUDs, etc.)
    // já devem funcionar corretamente, pois filtered_services_list agora retorna
    // o array correto. A única alteração foi no setServiceData.

    // Método para navegar entre as páginas de resultados
    const goToPage = (url) => {
        if (url) {
            router.get(url, { search: search_term.value }, { preserveState: true, preserveScroll: true });
        }
    };

    const openModal = (mode, service_id = null) => {
        const isUpdateOrPayMode = ['update', 'pay'].includes(mode);

        if (service_id !== null && isUpdateOrPayMode) {
            setServiceData(service_id);
        } else {
            service_data.reset();
            service_data.deadline = formatDate(new Date(Date.now() + 15 * 24 * 60 * 60 * 1000), 'new_date');
            service_data.service_status = 'Não Iniciado';
        }

        modal.value.mode = mode;
        modal.value.show = true;
    };

    const showMotivoAtrasoModal = ref(false);

    const opencompletionModal = (service_id, service_partial_value) => {
        if (service_partial_value !== 0) {
            if (!confirm("O serviço será considerado como concluído e será listado apenas na página de pagamentos")) return
        } else {
            if (!confirm("O serviço será considerado concluído e pago!")) return
        }
        setServiceData(service_id);
        modal.value.mode = 'conclude';
        showMotivoAtrasoModal.value = true
    };

    const showMotivoCancelamentoModal = ref(false);

    const opencancelModal = (service_id) => {
        setServiceData(service_id);
        modal.value.mode = 'cancel';
        showMotivoCancelamentoModal.value = true
    };

    const closeModal = () => {
        service_data.reset();
        modal.value.show = false;
        showMotivoAtrasoModal.value = false;
        showMotivoCancelamentoModal.value = false;
        service_data.deadline = formatDate(new Date(Date.now() + 15 * 24 * 60 * 60 * 1000), 'new_date');
        service_data.service_status = 'Não Iniciado';
    }


    onMounted(() => {
        if (props.show_service_id) {
            openModal('update', props.show_service_id);
        }
    });

    // =============================================
    // Métodos de CRUD (não precisam de alteração)
    const createService = () => {
        service_data.post(route('services.store'), {
            preserveScroll: true,
            onSuccess: () => {
                $toast.success('Serviço criado com sucesso!')
                closeModal()
            },
            onError: (error) => { console.log(error) }
        })
    }

    const updateService = () => {
        service_data.put(route('services.update', service_data.id), {
            preserveScroll: true,
            onSuccess: () => {
                $toast.success('Serviço alterado com sucesso!')
                closeModal()
            }
        })
    }

    const payService = () => {
        service_data.put(route('services.pay', service_data.id), {
            preserveScroll: true,
            onSuccess: () => {
                $toast.success('Serviço pago com sucesso!')
                closeModal()
            }
        })
    }

    const concludeService = () => {
        service_data.put(route('services.conclude', service_data.id), {
            preserveScroll: true,
            onSuccess: () => {
                $toast.success('Serviço concluído com sucesso!')
                closeModal()
            },
            onError: (error) => { console.log(error) }
        });
    }

    const deleteService = (service_id, service_name) => {
        if (confirm(`Você tem certeza que deseja excluir o serviço "${service_name}"? Esta ação não poderá ser desfeita!`)) {
            service_data.delete(route('services.destroy', service_id), {
                preserveScroll: true,
                onSuccess: () => $toast.success('Serviço deletado com sucesso!')
            })
        }
    }

    const cancelService = () => {
        service_data.put(route('services.cancel', service_data.id), {
            preserveScroll: true,
            onSuccess: () => {
                $toast.success('Serviço cancelado com sucesso!')
                closeModal()
            },
            onError: (error) => { console.log(error) }
        });
    }

    const changeStatusService = (service_id, event) => {
        const new_status = event.target.value;
        router.put(route('services.change-status', service_id), { service_status: new_status }, {
            preserveScroll: true,
            onSuccess: () => {
                $toast.success('Status alterado com sucesso!');
                router.reload({ only: ['services_list'] });
            },
            onError: (error) => { console.log(error); $toast.error('Erro ao alterar status!'); }
        });
    }

    const submit = () => {
        switch (modal.value.mode) {
            case 'create': return createService()
            case 'update': return updateService()
            case 'pay': return payService()
            case 'conclude': return concludeService()
            case 'cancel': return cancelService()
            default: $toast.error('Método desconhecido. Informar o Técnico.')
        }
    }
</script>

<template>

    <Head :title="page.name" />
    <AppLayout :page="page" :page_options="page_options">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ page.name }} <span v-if="$page.props.auth.user.hierarchy < 2">| {{
                    toMoney(total_services_amount) }}</span>
            </h2>
            <FloatButton :icon="'plus'" @click="openModal('create')" title="Cadastrar Service" class="print:hidden" />
        </template>

        <div class="pt-12 print:hidden print:pt-0">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 print:w-full">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                    <div class="flex flex-row items-center space-x-3">
                        <MagnifyingGlassIcon class="w-5 y-5" />
                        <input type="text" name="search_term" id="search_term" autocomplete="on" class="simple-input"
                            autofocus="true" placeholder="Pesquisar termo..." v-model="search_term" />
                    </div>
                </div>
            </div>
        </div>

        <div class="py-12 print:py-0">
            <div class="max-w-7xl mx-auto print:max-w-full">
                <div class="w-full flex justify-end pb-3 print:hidden">
                    <button v-if="!show_user_data" class="text-blue-500 text-xs justify-end"
                        @click="show_user_data = !show_user_data">Mostrar
                        dados de
                        usuário</button>
                    <button v-if="show_user_data" class="text-red-500 text-xs justify-end"
                        @click="show_user_data = !show_user_data">Esconder dados de usuário</button>
                </div>
                <div class="px-0 print:px-0">

                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg print:shadow-none">
                        <div class="flex flex-col">
                            <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
                                <div class="inline-block min-w-full py-2 sm:px-6 lg:px-8">
                                    <div class="overflow-hidden">
                                        <table class="min-w-full text-left text-sm font-light">
                                            <thead class="border-b font-medium ">
                                                <tr>
                                                    <th scope="col" class="px-6 py-4 print:px-3 print:py-2 text-center">
                                                        #</th>
                                                    <th scope="col" class="px-6 py-4 print:px-3 print:py-2 text-center">
                                                        Prazo
                                                    </th>
                                                    <th scope="col" class="px-6 py-4 print:px-3 print:py-2 text-center">
                                                        Título
                                                    </th>
                                                    <th scope="col" class="px-6 py-4 print:px-3 print:py-2 text-center">
                                                        Cliente
                                                    </th>
                                                    <th scope="col" class="px-6 py-4 print:px-3 print:py-2 text-center">
                                                        À pagar
                                                    </th>
                                                    <th scope="col" class="px-6 py-4 print:px-3 print:py-2 text-center">
                                                        Valor
                                                        total</th>
                                                    <th scope="col"
                                                        class="px-6 py-4 print:px-3 print:py-2 text-center print:hidden">
                                                        Entrega
                                                    </th>
                                                    <th scope="col" class="px-6 py-4 print:px-3 print:py-2 text-center">
                                                        Status
                                                    </th>
                                                    <th scope="col"
                                                        class="px-6 py-4 print:px-3 print:py-2 text-center print:hidden"
                                                        colspan="5">Ações
                                                    </th>
                                                    <th scope="col"
                                                        class="px-6 py-4 print:px-3 print:py-2 text-center print:hidden"
                                                        v-if="show_user_data">Criado
                                                        em</th>
                                                    <th scope="col"
                                                        class="px-6 py-4 print:px-3 print:py-2 text-center print:hidden"
                                                        v-if="show_user_data">Criado
                                                        por</th>
                                                    <th scope="col"
                                                        class="px-6 py-4 print:px-3 print:py-2 text-center print:hidden"
                                                        v-if="show_user_data">
                                                        Modificado em</th>
                                                    <th scope="col"
                                                        class="px-6 py-4 print:px-3 print:py-2 text-center print:hidden"
                                                        v-if="show_user_data">
                                                        Modificado por
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-if="filtered_services_list.length"
                                                    v-for="service in filtered_services_list" :key="service.id"
                                                    class="border-b transition duration-300 ease-in-out hover:bg-neutral-100 print:break-inside-avoid">
                                                    <td class="whitespace-nowrap py-4 text-center font-medium">{{
                                                        service.id }}
                                                    </td>
                                                    <td class="whitespace-nowrap py-4 text-center font-medium"
                                                        :class="deadlineClass(service.deadline)">
                                                        {{ calcDeadlineDays(service.deadline) }} dias
                                                    </td>
                                                    <td
                                                        class="whitespace-normal px-6 py-4 print:px-3 print:py-2 text-center trim">
                                                        {{
                                                            service.motive }}
                                                        <span v-if="service.observations" :title="service.observations"
                                                            class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10 cursor-help">OBS</span>
                                                    </td>
                                                    <td
                                                        class="whitespace-normal px-6 py-4 print:px-3 print:py-2 text-center">
                                                        {{
                                                            service?.client?.name || service.entity_name }}</td>
                                                    <td class="whitespace-nowrap px-2 py-4 text-center">{{
                                                        toMoney(service.accounting.partial_value) }}</td>
                                                    <td class="whitespace-nowrap px-2 py-4 text-center">{{
                                                        toMoney(service.accounting.total_value) }}</td>
                                                    <td class="whitespace-nowrap px-2 py-4 text-center print:hidden">{{
                                                        formatDate(service.deadline, true) }}</td>
                                                    <td class="whitespace-nowrap px-2 py-4 text-center">
                                                        <select
                                                            class="print:hidden pl-1 pr-7 py-0.5 rounded-full text-xs"
                                                            :class="{
                                                                'text-blue-500 border-blue-500': service.service_status === 'Não Iniciado',
                                                                'text-yellow-500 border-yellow-500': service.service_status === 'Em Andamento',
                                                                'text-orange-500 border-orange-500': service.service_status === 'Pausado',
                                                            }" :value="service.service_status"
                                                            @change="changeStatusService(service.id, $event)">
                                                            <option v-show="service.service_status !== 'Não Iniciado'"
                                                                value="Não Iniciado"
                                                                :selected="service.service_status === 'Não Iniciado'"
                                                                class="text-blue-500">
                                                                Não
                                                                Iniciado
                                                            </option>
                                                            <option v-show="service.service_status !== 'Em Andamento'"
                                                                value="Em Andamento"
                                                                :selected="service.service_status === 'Em Andamento'"
                                                                class="text-yellow-500">Em
                                                                Andamento
                                                            </option>
                                                            <option v-show="service.service_status !== 'Pausado'"
                                                                value="Pausado"
                                                                :selected="service.service_status === 'Pausado'"
                                                                class="text-orange-500">
                                                                Pausado</option>
                                                        </select>
                                                        <label class="hidden print:block" :class="{
                                                            'text-blue-500': service.service_status === 'Não Iniciado',
                                                            'text-yellow-500': service.service_status === 'Em Andamento',
                                                            'text-orange-500': service.service_status === 'Pausado',
                                                        }">{{ service.service_status }}</label>
                                                    </td>
                                                    <td class="whitespace-nowrap px-4 py-4 pl-10 text-center cursor-pointer hover:text-blue-700 active:text-blue-900 select-none print:hidden"
                                                        :title="'CONCLUIR: ' + service.motive + '(' + service.entity_name + ')'"
                                                        @click="opencompletionModal(service.id, service.accounting.partial_value)">
                                                        <CheckBadgeIcon class="w-4 h-4 m-auto" />
                                                    </td>
                                                    <td class="whitespace-nowrap px-4 py-4 text-center cursor-pointer hover:text-green-700 active:text-green-900 select-none print:hidden"
                                                        :title="'PAGAR: ' + service.motive + '(' + service.entity_name + ')'"
                                                        @click="openModal('pay', service.id)">
                                                        <span v-if="service.accounting.partial_value !== 0">
                                                            <BanknotesIcon class="w-4 h-4 m-auto" />
                                                        </span>
                                                        <span v-else class="m-auto text-center">PAGO</span>
                                                    </td>
                                                    <td class="whitespace-nowrap px-4 py-4 text-center cursor-pointer hover:text-yellow-700 active:text-yellow-900 select-none print:hidden"
                                                        :title="'EDITAR: ' + service.motive + '(' + service.entity_name + ')'"
                                                        @click="openModal('update', service.id)">
                                                        <PencilIcon class="w-4 h-4 m-auto" />
                                                    </td>
                                                    <td :title="'ORÇAMENTO DE ' + service.motive + '(' + service.entity_name + ')'"
                                                        class="whitespace-nowrap px-4 py-4 text-center cursor-pointer hover:text-sky-700 active:text-sky-900 select-none print:hidden">
                                                        <a v-if="service.budget?.id"
                                                            :href="route('budgets.show', service.budget.id)"
                                                            target="_blank" class="">
                                                            <DocumentTextIcon class="w-4 h-4 m-auto" />
                                                        </a>
                                                    </td>
                                                    <td v-if="$page.props.auth.user.hierarchy < 2"
                                                        class="whitespace-nowrap px-4 py-4 text-center cursor-pointer hover:text-red-700 active:text-red-900 select-none print:hidden"
                                                        :title="'CANCELAR: ' + service.motive + '(' + service.entity_name + ')'"
                                                        @click="opencancelModal(service.id)">
                                                        <XMarkIcon class="w-4 h-4 m-auto" />
                                                    </td>
                                                    <td class="whitespace-nowrap px-2 py-4 text-center print:hidden"
                                                        v-if="show_user_data">
                                                        {{
                                                            formatDate(service.created_at, 'reading_date_time') }}
                                                    </td>
                                                    <td class="whitespace-nowrap px-2 py-4 text-center print:hidden"
                                                        v-if="show_user_data">
                                                        {{
                                                            service.procedures[0].user.name }}
                                                    </td>
                                                    <td class="whitespace-nowrap px-2 py-4 text-center print:hidden"
                                                        v-if="show_user_data">
                                                        {{
                                                            formatDate(service.updated_at, 'reading_date_time') }}
                                                    </td>
                                                    <td class="whitespace-nowrap px-2 py-4 text-center print:hidden"
                                                        v-if="show_user_data">
                                                        {{
                                                            service.procedures[service.procedures?.length - 1].user.name }}
                                                    </td>
                                                </tr>
                                                <tr v-else
                                                    class="transition duration-300 ease-in-out hover:bg-neutral-100 print:break-inside-avoid">
                                                    <td class="whitespace-nowrap px-6 py-4 print:px-3 print:py-2 text-center"
                                                        colspan="9">Não
                                                        há
                                                        serviços cadastrados no momento!</td>
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

        <div v-if="services_list.total > 0"
            class="print:hidden flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
            <div class="flex flex-1 justify-between sm:hidden">
                <button @click="goToPage(services_list.prev_page_url)" :disabled="!services_list.prev_page_url"
                    class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50">Anterior</button>
                <button @click="goToPage(services_list.next_page_url)" :disabled="!services_list.next_page_url"
                    class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50">Próximo</button>
            </div>
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Mostrando de
                        <span class="font-medium">{{ services_list.from }}</span>
                        a
                        <span class="font-medium">{{ services_list.to }}</span>
                        de
                        <span class="font-medium">{{ services_list.total }}</span>
                        resultados
                    </p>
                </div>
                <div>
                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                        <template v-for="(link, index) in services_list.links" :key="index">
                            <button @click="goToPage(link.url)" :disabled="!link.url"
                                class="relative inline-flex items-center px-4 py-2 text-sm font-semibold focus:z-20"
                                :class="{
                                    'z-10 bg-green-600 text-white focus-visible:outline-green-600': link.active,
                                    'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-offset-0 disabled:opacity-50': !link.active,
                                    'rounded-l-md': index === 0,
                                    'rounded-r-md': index === services_list.links.length - 1
                                }" v-html="link.label" />
                        </template>
                    </nav>
                </div>
            </div>
        </div>


        <CreateUpdatePayModal :show="modal.show" :modal="modal" :service="service_data" :sells_list="sells_list"
            @submit="submit" @close="closeModal" />

        <MotivoAtrasoModal :show="showMotivoAtrasoModal" :service="service_data" @submit="submit" @close="closeModal" />

        <MotivoCancelamentoModal :show="showMotivoCancelamentoModal" :service="service_data" @submit="submit"
            @close="closeModal" />

        <ExtraOptionsButton :mode="['rollup', 'print_page', 'link']" :link_type="['previous']"
            :link="[route('services.previous')]" />
    </AppLayout>
</template>