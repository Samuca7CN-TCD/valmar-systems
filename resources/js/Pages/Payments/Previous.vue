<script setup>
    import AppLayout from '@/Layouts/AppLayout.vue'
    import CreateUpdatePayModal from '@/Components/Payments/CreateUpdatePayModal.vue'
    import ExtraOptionsButton from '@/Components/ExtraOptionsButton.vue'
    import { Head, Link, useForm, router } from '@inertiajs/vue3';
    import { computed, ref, watch } from 'vue'
    import { EyeIcon, MagnifyingGlassIcon, ArrowUturnLeftIcon } from '@heroicons/vue/24/outline'
    // Importar os ícones para a paginação
    import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/20/solid'
    import { toMoney, formatDate } from '@/general.js'

    // =============================================
    // Informações exteriores
    const props = defineProps({
        page: Object,
        page_options: {
            type: Array,
            default: null,
        },
        payments_list: Object, // Agora é um objeto paginado
        filters: Object,
    })

    const payment_data = useForm({
        'id': null,
        'debt': '',
        'debtor': '',
        'total_value': 0,
        'partial_value': 0,
        'observations': '',
        'records_list': {
            'enable_records': false,
            'data': [],
        }
    })

    const search_term = ref(props.filters?.search || "");
    const show_user_data = ref(false);

    let searchTimeout = null;
    watch(search_term, (newSearchTerm) => {
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
        searchTimeout = setTimeout(() => {
            router.get(route('payments.previous'), { search: newSearchTerm }, { preserveState: true, replace: true });
        }, 300);
    });

    // A busca agora é feita no backend, este computed apenas retorna os dados da página atual
    const filtered_payments_list = computed(() => {
        return props.payments_list.data;
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
                case 'see': return "Ver informações do pagamento"
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

    const openModal = (mode, payment_id = null) => {
        const isUpdateOrPayMode = ['update', 'pay', 'see'].includes(mode);
        if (payment_id !== null && isUpdateOrPayMode) {
            // Busca o pagamento na lista de dados da página atual
            const payment = props.payments_list.data.find(payment => payment.id === payment_id);
            if (payment) {
                const { id, type, motive, entity_name, observations, records, accounting } = payment;
                payment_data.id = id;
                payment_data.type = type;
                payment_data.debt = motive;
                payment_data.debtor = entity_name;
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

    const goToPage = (url) => {
        if (url) {
            router.get(url, { search: search_term.value }, { preserveState: true, preserveScroll: true });
        }
    };

</script>

<template>

    <Head :title="page.name" />
    <AppLayout :page="page" :page_options="page_options">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ page.name }} Concluídos
                </h2>
                <Link :href="route('payments.index')"
                    class="text-gray-600 hover:text-gray-900 flex items-center space-x-2">

                <ArrowUturnLeftIcon class="w-4 h-4" />
                <span>Voltar para Pagamentos</span>
                </Link>
            </div>
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
                                                    <th scope="col" class="px-6 py-4 text-center">#</th>
                                                    <th scope="col" class="px-6 py-4 text-center">Dívida</th>
                                                    <th scope="col" class="px-6 py-4 text-center">Devedor</th>
                                                    <th scope="col" class="px-6 py-4 text-center">Valor total</th>
                                                    <th scope="col" class="px-6 py-4 text-center print:hidden">Ações
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
                                                <tr v-if="filtered_payments_list.length"
                                                    v-for="payment in filtered_payments_list" :key="payment.id"
                                                    class="border-b transition duration-300 ease-in-out hover:bg-neutral-100 print:break-inside-avoid">
                                                    <td class="whitespace-nowrap py-4 text-center font-medium">{{
                                                        payment.id }}
                                                    </td>
                                                    <td class="whitespace-normal px-6 py-4 text-center trim">{{
                                                        payment.motive }}
                                                        <span v-if="payment.observations" :title="payment.observations"
                                                            class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10 cursor-help">OBS</span>
                                                    </td>
                                                    <td class="whitespace-normal px-6 py-4 text-center">{{
                                                        payment.entity_name }}</td>
                                                    <td class="whitespace-nowrap px-2 py-4 text-center">{{
                                                        toMoney(payment.accounting.total_value) }}</td>
                                                    <td class="whitespace-nowrap px-4 py-4 text-center cursor-pointer hover:text-green-700 active:text-green-900 select-none print:hidden"
                                                        :title="'VER: ' + payment.motive + '(' + payment.entity_name + ')'"
                                                        @click="openModal('see', payment.id)">
                                                        <EyeIcon class="w-4 h-4 m-auto" />
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
                                                            payment.procedures[payment.procedures?.length - 1].user.name
                                                        }}
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

        <div class="print:hidden flex items-center justify-between border-t border-gray-200 bg-white px-24 py-3">

            <div class="flex flex-1 justify-between xl:hidden">
                <button :href="payments_list.prev_page_url" :disabled="!payments_list.prev_page_url"
                    @click="goToPage(payments_list.prev_page_url)"
                    class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Anterior</button>
                <button :href="payments_list.next_page_url" :disabled="!payments_list.next_page_url"
                    @click="goToPage(payments_list.next_page_url)"
                    class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Próximo</button>
            </div>

            <div class="hidden xl:flex sm:flex-1 sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Mostrando
                        {{ ' ' }}
                        <span class="font-medium">{{ payments_list.from }}</span>
                        {{ ' ' }}
                        à
                        {{ ' ' }}
                        <span class="font-medium">{{ payments_list.to }}</span>
                        {{ ' ' }}
                        de
                        {{ ' ' }}
                        <span class="font-medium">{{ payments_list.total }}</span>
                        {{ ' ' }}
                        registros
                    </p>
                </div>
                <div>
                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-xs" aria-label="Pagination">
                        <template v-for="(link, index) in payments_list.links">
                            <template v-if="link.url && !['&laquo; Previous', 'Next &raquo;'].includes(link.label)">
                                <button :key="index" :href="link.url" :disabled="!link.url" @click="goToPage(link.url)"
                                    aria-current="page"
                                    class="relative inline-flex items-center px-4 py-2 text-sm font-semibold focus:z-20 focus-visible:outline-2 focus-visible:outline-offset-2"
                                    :class="{
                                        'z-10 bg-green-600 text-white focus-visible:outline-green-600': link.active,
                                        'text-gray-900 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:outline-offset-0': !link.active,
                                        'rounded-r-md': index === payments_list.links.length - 1 && !link.active && payments_list.links[payments_list.links.length - 1].label !== 'Next &raquo;',
                                        'rounded-l-md': index === 0 && !link.active && payments_list.links[0].label !== '&laquo; Previous'
                                    }">
                                    {{ link.label }}
                                </button>
                            </template>
                            <template v-else-if="!link.url && link.label === '...'">
                                <span
                                    class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-gray-300 ring-inset focus:outline-offset-0">...</span>
                            </template>
                        </template>
                    </nav>
                </div>
            </div>
        </div>

        <CreateUpdatePayModal :show="modal.show" :modal="modal" :payment="payment_data" @close="closeModal" />
        <ExtraOptionsButton :mode="['rollup', 'print_page']" />
    </AppLayout>
</template>