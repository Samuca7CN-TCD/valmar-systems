<script setup>
    import AppLayout from '@/Layouts/AppLayout.vue'
    import CreateUpdatePayModal from '@/Components/Payments/CreateUpdatePayModal.vue'
    import FloatButton from '@/Components/FloatButton.vue'
    import ExtraOptionsButton from '@/Components/ExtraOptionsButton.vue'
    import { Head, useForm, router } from '@inertiajs/vue3' // Importar o router
    import { computed, ref, watch } from 'vue' // Importar o watch
    import { BanknotesIcon, MagnifyingGlassIcon, PencilIcon, XMarkIcon, ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline'
    import { toMoney, formatDate, calcDeadlineDays } from '@/general.js'
    import { useToast } from 'vue-toast-notification';
    import 'vue-toast-notification/dist/theme-sugar.css';

    // =============================================
    // Informações exteriores
    const props = defineProps({
        page: Object,
        page_options: {
            type: Array,
            default: null,
        },
        payments_list: Object, // Continua sendo um Objeto (Paginator do Laravel)
        filters: Object, // Prop para receber os filtros do controller
    })

    const $toast = useToast();
    // =============================================
    // Informações do OBJETO
    const payment_data = useForm({
        'id': null,
        'debt': '',
        'client_id': '',
        'total_value': 0,
        'partial_value': 0,
        'observations': '',
        'records_list': {
            'enable_records': false,
            'data': [],
        }
    })

    // O termo de busca agora é inicializado com o filtro vindo do controller
    const search_term = ref(props.filters?.search || '');
    const show_services = ref(true)
    const show_user_data = ref(false);
    let searchTimeout = null;

    // Lógica de busca agora é feita no backend com um "watch"
    watch(search_term, (newSearchTerm) => {
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
        searchTimeout = setTimeout(() => {
            // Usa o router do Inertia para fazer uma nova requisição GET para a mesma página
            router.get(route('payments.index'), { search: newSearchTerm }, { preserveState: true, replace: true });
        }, 300); // Debounce de 300ms para não sobrecarregar o servidor
    });

    // Esta computed property agora apenas aponta para os dados da página atual
    const filtered_payments_list = computed(() => {
        return props.payments_list.data;
    });

    // ATENÇÃO: Estes totais agora refletem apenas os itens da PÁGINA ATUAL.
    // Se precisar de um total geral, ele deve ser calculado e enviado separadamente pelo controller.
    const total_payments_amount = computed(() => {
        // Acessa o array de dados com .data
        return props.payments_list.data.reduce((accumulator, payment) => {
            return accumulator + payment.accounting.partial_value;
        }, 0);
    });

    const total_payments_amount_without_services = computed(() => {
        // Acessa o array de dados com .data
        return props.payments_list.data.filter(el => el.type !== 1).reduce((accumulator, payment) => {
            return accumulator + payment.accounting.partial_value;
        }, 0);
    });

    // =============================================
    // Controle de Modal
    const modal = ref({
        mode: 'create',
        show: false,
        get title() {
            switch (this.mode) {
                case 'create': return "Criar pagamento"
                case 'update': return "Editar pagamento"
                case 'pay': return "Pagar pagamento"
            }
        },
        get primary_button_txt() {
            switch (this.mode) {
                case 'create': return "Cadastrar"
                case 'update': return "Atualizar"
                case 'pay': return "Pagar"
            }
        }
    })

    const openModal = (mode, payment_id = null) => {
        const isUpdateOrPayMode = ['update', 'pay'].includes(mode);

        if (payment_id !== null && isUpdateOrPayMode) {
            // Procura o pagamento dentro do array .data
            const payment = props.payments_list.data.find(payment => payment.id === payment_id);

            if (payment) {
                const { id, type, client_id, client, motive, entity_name, observations, records, accounting } = payment;
                payment_data.id = id;
                payment_data.type = type;
                payment_data.debt = motive;
                payment_data.client_id = client_id;
                payment_data.client = client;
                payment_data.total_value = accounting.total_value;
                payment_data.partial_value = accounting.partial_value;
                payment_data.observations = observations;
                payment_data.records_list.enable_records = Boolean(records.length);
                payment_data.records_list.data = records.map((record) => { return useForm(record) })
            }
        }

        modal.value.mode = mode;
        modal.value.show = true;
    };

    const closeModal = () => {
        payment_data.reset()
        modal.value.show = false
    }

    // Método para navegar entre as páginas de resultados
    const goToPage = (url) => {
        if (url) {
            router.get(url, { search: search_term.value }, { preserveState: true, preserveScroll: true });
        }
    };

    // =============================================
    // Métodos de CRUD (permanecem os mesmos)
    const createPayment = () => {
        payment_data.post(route('payments.store'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: (error) => { console.log(error) }
        })
    }

    const updatePayment = () => {
        payment_data.put(route('payments.update', payment_data.id), {
            preserveScroll: true,
            onSuccess: () => closeModal()
        })
    }

    const payPayment = () => {
        payment_data.put(route('payments.pay', payment_data.id), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: (error) => { console.log(error) }
        })
    }

    const deletePayment = (payment_id, payment_name) => {
        if (confirm(`Você tem certeza que deseja excluir o pagamento "${payment_name}"? Esta ação não poderá ser desfeita!`)) {
            payment_data.delete(route('payments.destroy', payment_id), {
                preserveScroll: true,
                onSuccess: () => $toast.success('Pagamento deletado com sucesso!')
            })
        }
    }

    const submit = () => {
        switch (modal.value.mode) {
            case 'create': return createPayment()
            case 'update': return updatePayment()
            case 'pay': return payPayment()
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
                    toMoney(total_payments_amount_without_services)
                }}</span><span v-if="$page.props.auth.user.hierarchy < 2" class="text-neutral-400"> | {{
                        toMoney(total_payments_amount)
                    }} <span class="text-xs"> (pagamentos + serviços)</span></span>
            </h2>
            <FloatButton :icon="'plus'" @click="openModal('create')" title="Cadastrar Payment" class="print:hidden" />
        </template>

        <div class="pt-12 print:hidden">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                    <div class="flex flex-row items-center space-x-3">
                        <MagnifyingGlassIcon class="w-5 y-5" />
                        <input type="text" name="search_term" id="search_term" autocomplete="on" class="simple-input"
                            autofocus="true" placeholder="Pesquisar termo..." v-model="search_term" />
                    </div>
                    <div class="ml-8 mt-1">
                        <input type="checkbox" id="show-services"
                            class="focus:ring-green-700 text-green-700 border-neutral-500 rounded-sm disabled:opacity-50"
                            v-model="show_services" :checked="show_services" />
                        <label for="show-services" class="ml-3 text-xs text-neutral-500 select-none">Mostrar
                            serviços</label>
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
                                        <table class="w-full text-left text-sm font-light">
                                            <thead class="border-b font-medium ">
                                                <tr>
                                                    <th scope="col" class="px-6 py-4 text-center">#</th>
                                                    <th scope="col" class="px-6 py-4 text-center">Dívida</th>
                                                    <th scope="col" class="px-6 py-4 text-center">Devedor</th>
                                                    <th scope="col" class="px-6 py-4 text-center">À pagar</th>
                                                    <th scope="col" class="px-6 py-4 text-center">Valor total</th>
                                                    <th scope="col" class="px-6 py-4 text-center print:hidden">Pagar
                                                    </th>
                                                    <th scope="col" class="px-6 py-4 text-center print:hidden">Editar
                                                    </th>
                                                    <th scope="col" class="px-6 py-4 text-center print:hidden">Excluir
                                                    </th>
                                                    <th scope="col" class="px-6 py-4 text-center print:hidden"
                                                        v-if="show_user_data">Criado
                                                        em</th>
                                                    <th scope="col" class="px-6 py-4 text-center print:hidden"
                                                        v-if="show_user_data">Criado
                                                        por</th>
                                                    <th scope="col" class="px-6 py-4 text-center print:hidden"
                                                        v-if="show_user_data">
                                                        Modificado em</th>
                                                    <th scope="col" class="px-6 py-4 text-center print:hidden"
                                                        v-if="show_user_data">
                                                        Modificado por
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-if="filtered_payments_list.length"
                                                    v-for="payment in filtered_payments_list" :key="payment.id"
                                                    class="border-b transition duration-300 ease-in-out hover:bg-neutral-100 print:break-inside-avoid"
                                                    :class="{ 'hidden': payment.type === 1 && show_services === false }">
                                                    <td class="whitespace-nowrap py-4 text-center font-medium">{{
                                                        payment.id }}
                                                    </td>
                                                    <td class="break-words px-6 py-4 text-center">{{
                                                        payment.motive }}
                                                        <span v-if="payment.observations" :title="payment.observations"
                                                            class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10 cursor-help">OBS</span>
                                                    </td>
                                                    <td class="whitespace-normal px-6 py-4 text-center">{{
                                                        payment?.client?.name || payment.entity_name }}</td>
                                                    <td class="whitespace-nowrap px-2 py-4 text-center">{{
                                                        toMoney(payment.accounting.partial_value) }}</td>
                                                    <td class="whitespace-nowrap px-2 py-4 text-center">{{
                                                        toMoney(payment.accounting.total_value) }}</td>
                                                    <td class="whitespace-nowrap px-4 py-4 text-center cursor-pointer hover:text-green-700 active:text-green-900 select-none print:hidden"
                                                        :title="'PAGAR: ' + payment.motive + '(' + payment.entity_name + ')'"
                                                        @click="openModal('pay', payment.id)">
                                                        <BanknotesIcon class="w-5 h-5 m-auto" />
                                                    </td>
                                                    <td class="whitespace-nowrap px-4 py-4 text-center cursor-pointer hover:text-yellow-700 active:text-yellow-900 select-none print:hidden"
                                                        :title="'EDITAR: ' + payment.motive + '(' + payment.entity_name + ')'"
                                                        @click="openModal('update', payment.id)">
                                                        <PencilIcon class="w-5 h-5 m-auto" />
                                                    </td>
                                                    <td v-if="payment.type !== 2"
                                                        class="whitespace-nowrap px-4 py-4 text-center cursor-pointer hover:text-red-700 active:text-red-900 select-none print:hidden"
                                                        :title="'EXCLUIR: ' + payment.motive + '(' + payment.entity_name + ')'"
                                                        @click="deletePayment(payment.id, payment.motive)">
                                                        <XMarkIcon class="w-5 h-5 m-auto" />
                                                    </td>
                                                    <td v-else
                                                        class="whitespace-nowrap px-4 py-4 text-center select-none print:hidden">
                                                        Venda
                                                    </td>
                                                    <td class="whitespace-nowrap px-2 py-4 text-center print:hidden"
                                                        v-if="show_user_data">
                                                        {{
                                                            formatDate(payment.created_at, 'reading_date_time') }}
                                                    </td>
                                                    <td class="whitespace-nowrap px-2 py-4 text-center print:hidden"
                                                        v-if="show_user_data">
                                                        {{
                                                            payment.procedures[0].user.name }}
                                                    </td>
                                                    <td class="whitespace-nowrap px-2 py-4 text-center print:hidden"
                                                        v-if="show_user_data">
                                                        {{
                                                            formatDate(payment.updated_at, 'reading_date_time') }}
                                                    </td>
                                                    <td class="whitespace-nowrap px-2 py-4 text-center print:hidden"
                                                        v-if="show_user_data">
                                                        {{
                                                            payment.procedures[payment.procedures?.length - 1].user.name }}
                                                    </td>
                                                </tr>
                                                <tr v-else
                                                    class="transition duration-300 ease-in-out hover:bg-neutral-100 print:break-inside-avoid">
                                                    <td class="whitespace-nowrap px-6 py-4 text-center" colspan="9">Não
                                                        há
                                                        pagamentos cadastrados no momento!</td>
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

        <div v-if="payments_list.total > 0"
            class="print:hidden flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
            <div class="flex flex-1 justify-between sm:hidden">
                <button @click="goToPage(payments_list.prev_page_url)" :disabled="!payments_list.prev_page_url"
                    class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50">Anterior</button>
                <button @click="goToPage(payments_list.next_page_url)" :disabled="!payments_list.next_page_url"
                    class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50">Próximo</button>
            </div>
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Mostrando de
                        <span class="font-medium">{{ payments_list.from }}</span>
                        a
                        <span class="font-medium">{{ payments_list.to }}</span>
                        de
                        <span class="font-medium">{{ payments_list.total }}</span>
                        resultados
                    </p>
                </div>
                <div>
                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                        <template v-for="(link, index) in payments_list.links" :key="index">
                            <button @click="goToPage(link.url)" :disabled="!link.url"
                                class="relative inline-flex items-center px-4 py-2 text-sm font-semibold focus:z-20"
                                :class="{
                                    'z-10 bg-green-600 text-white focus-visible:outline-green-600': link.active,
                                    'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-offset-0 disabled:opacity-50': !link.active,
                                    'rounded-l-md': index === 0,
                                    'rounded-r-md': index === payments_list.links.length - 1
                                }" v-html="link.label" />
                        </template>
                    </nav>
                </div>
            </div>
        </div>


        <CreateUpdatePayModal :show="modal.show" :modal="modal" :payment="payment_data" @submit="submit"
            @close="closeModal" />

        <ExtraOptionsButton :mode="['rollup', 'print_page', 'link']" :link_type="['previous']"
            :link="[route('payments.previous')]" />
    </AppLayout>
</template>