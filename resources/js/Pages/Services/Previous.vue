<script setup>
    import AppLayout from '@/Layouts/AppLayout.vue'
    import CreateUpdatePayModal from '@/Components/Services/CreateUpdatePayModal.vue'
    import ExtraOptionsButton from '@/Components/ExtraOptionsButton.vue'
    import { Head, Link, useForm, router } from '@inertiajs/vue3';
    import { computed, onMounted, ref, watch } from 'vue'
    import { EyeIcon, MagnifyingGlassIcon, ArrowUturnLeftIcon } from '@heroicons/vue/24/outline'
    // Importar os ícones para a paginação
    import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/20/solid' //
    import { toMoney, formatDate } from '@/general.js'

    // =============================================
    // Informações exteriores
    const props = defineProps({
        page: Object,
        page_options: {
            type: Array,
            default: null,
        },
        services_list: Object, // Agora é um objeto paginado (Laravel Paginator)
        sells_list: {
            type: Array,
            default: [],
        },
        show_service_id: {
            type: [String, Number], // Pode ser string se o ID for UUID
            default: null,
        },
        filters: Object, // Apenas para Services/Previous.vue
    })

    const service_data = useForm({
        'id': null,
        'previous_id': null,
        'title': '',
        'client': '',
        'total_value': 0,
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

    const search_term = ref(props.filters?.search || "");

    let searchTimeout = null;
    watch(search_term, (newSearchTerm) => {
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
        searchTimeout = setTimeout(() => {
            router.get(route('services.previous'), { search: newSearchTerm }, { preserveState: true, replace: true });
        }, 300);
    });

    const show_user_data = ref(false);

    // filtered_services_list agora apenas retorna os dados da página atual,
    // pois a busca é feita no backend
    const filtered_services_list = computed(() => {
        return props.services_list.data;
    });

    // =============================================
    // Controle de Modal
    const modal = ref({
        mode: 'see',
        show: false,
        get title() {
            switch (this.mode) {
                case 'see': return "Ver informações do serviço"
                default: return "Detalhes do Serviço"
            }
        },
        get primary_button_txt() {
            switch (this.mode) {
                case 'see': return "Fechar"
                default: return "Ok"
            }
        }
    })

    const setServiceData = (service_id) => {
        const service = props.services_list.data.find(service => service.id === parseInt(service_id));

        if (service) {
            const { id, previous_id, motive, entity_name, deadline, observations, cancellation_reason, delay_reason, delayed, completion_date, records, accounting, status } = service;

            service_data.id = id;
            service_data.previous_id = previous_id;
            service_data.title = motive;
            service_data.client = entity_name;
            service_data.total_value = accounting.total_value;
            service_data.partial_value = accounting.partial_value;
            service_data.deadline = deadline;
            service_data.observations = observations;
            service_data.service_status = status;
            service_data.cancellation_reason = cancellation_reason;
            service_data.delay_reason = delay_reason;
            service_data.completion_date = formatDate(new Date(), 'new_date');
            service_data.delayed = delayed;
            service_data.records_list.enable_records = Boolean(records.length);
            service_data.records_list.data = records.map((record) => { return useForm(record) })
        }
    }

    const openModal = (mode, service_id = null) => {
        if (service_id !== null) {
            setServiceData(service_id);
        }
        modal.value.mode = mode;
        modal.value.show = true;
    };

    const closeModal = () => {
        service_data.reset()
        modal.value.show = false
    }

    onMounted(() => {
        if (props.show_service_id) {
            // Encontra o serviço na lista. Para Services/Previous, talvez precise de props.services_list.data
            openModal('see', props.show_service_id);
        }
    });

    const goToPage = (url) => {
        if (url) {
            router.get(url, { search: search_term.value }, { preserveState: true, preserveScroll: true }); // Passar o termo de busca ao mudar de página
        }
    };

</script>

<template>

    <Head :title="page.name" />
    <AppLayout :page="page" :page_options="page_options">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Serviços Concluídos
                </h2>
                <Link :href="route('services.index')"
                    class="text-gray-600 hover:text-gray-900 flex items-center space-x-2">
                <ArrowUturnLeftIcon class="w-4 h-4" />
                <span>Voltar para Serviços</span>
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
                                                    <th scope="col" class="px-6 py-4 print:px-3 print:py-2 text-center">
                                                        #</th>
                                                    <th scope="col" class="px-6 py-4 print:px-3 print:py-2 text-center">
                                                        Título
                                                    </th>
                                                    <th scope="col" class="px-6 py-4 print:px-3 print:py-2 text-center">
                                                        Cliente
                                                    </th>
                                                    <th scope="col" class="px-6 py-4 print:px-3 print:py-2 text-center">
                                                        Valor
                                                        total</th>
                                                    <th scope="col"
                                                        class="px-6 py-4 print:px-3 print:py-2 text-center print:hidden">
                                                        Entregue ou Cancelado em
                                                    </th>
                                                    <th scope="col" class="px-6 py-4 print:px-3 print:py-2 text-center">
                                                        Status
                                                    </th>
                                                    <th scope="col"
                                                        class="px-6 py-4 print:px-3 print:py-2 text-center print:hidden">
                                                        Ações
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
                                                    <td class="whitespace-normal px-6 py-4 text-center trim">{{
                                                        service.motive }}
                                                        <span v-if="service.observations" :title="service.observations"
                                                            class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10 cursor-help">OBS</span>
                                                    </td>
                                                    <td class="whitespace-normal px-6 py-4 text-center">{{
                                                        service.entity_name }}</td>
                                                    <td class="whitespace-nowrap px-2 py-4 text-center">{{
                                                        toMoney(service.accounting.total_value) }}</td>
                                                    <td class="whitespace-nowrap px-2 py-4 text-center">{{
                                                        formatDate(service.completion_date || service.deleted_at,
                                                            'read') }}
                                                    </td>
                                                    <td class="whitespace-nowrap px-2 py-4 text-center">
                                                        <label :class="{
                                                            'text-blue-500': service.service_status === 'Não Iniciado',
                                                            'text-yellow-500': service.service_status === 'Em Andamento',
                                                            'text-orange-500': service.service_status === 'Pausado',
                                                            'text-green-500': service.service_status === 'Finalizado',
                                                            'text-red-500': service.service_status === 'Cancelado',
                                                        }">{{ service.service_status }}</label>
                                                    </td>
                                                    <td class="whitespace-nowrap px-4 py-4 text-center cursor-pointer hover:text-green-700 active:text-green-900 select-none print:hidden"
                                                        :title="'VER: ' + service.motive + '(' + service.entity_name + ')'"
                                                        @click="openModal('see', service.id)">
                                                        <EyeIcon class="w-4 h-4 m-auto" />
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
                                                    <td class="whitespace-nowrap px-6 py-4 text-center" colspan="9">Não
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

        <div class="print:hidden flex items-center justify-between border-t border-gray-200 bg-white px-24 py-3">
            <div class="flex flex-1 justify-between xl:hidden">
                <button :href="services_list.prev_page_url" :disabled="!services_list.prev_page_url"
                    @click="goToPage(services_list.prev_page_url)"
                    class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Anterior</button>
                <button :href="services_list.next_page_url" :disabled="!services_list.next_page_url"
                    @click="goToPage(services_list.next_page_url)"
                    class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Próximo</button>
            </div>

            <div class="hidden xl:flex sm:flex-1 sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Mostrando
                        {{ ' ' }}
                        <span class="font-medium">{{ services_list.from }}</span>
                        {{ ' ' }}
                        à
                        {{ ' ' }}
                        <span class="font-medium">{{ services_list.to }}</span>
                        {{ ' ' }}
                        de
                        {{ ' ' }}
                        <span class="font-medium">{{ services_list.total }}</span>
                        {{ ' ' }}
                        registros
                    </p>
                </div>
                <div>
                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-xs" aria-label="Pagination">
                        <template v-for="(link, index) in services_list.links">
                            <template v-if="link.url && !['&laquo; Previous', 'Next &raquo;'].includes(link.label)">
                                <button :key="index" :href="link.url" :disabled="!link.url" @click="goToPage(link.url)"
                                    aria-current="page"
                                    class="relative inline-flex items-center px-4 py-2 text-sm font-semibold focus:z-20 focus-visible:outline-2 focus-visible:outline-offset-2"
                                    :class="{
                                        'z-10 bg-green-600 text-white focus-visible:outline-green-600': link.active,
                                        'text-gray-900 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus:outline-offset-0': !link.active,
                                        'rounded-r-md': index === services_list.links.length - 1 && !link.active && services_list.links[services_list.links.length - 1].label !== 'Next &raquo;',
                                        'rounded-l-md': index === 0 && !link.active && services_list.links[0].label !== '&laquo; Previous'
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


        <CreateUpdatePayModal :show="modal.show" :modal="modal" :service="service_data" :sells_list="sells_list"
            @close="closeModal" />
        <ExtraOptionsButton :mode="['rollup', 'print_page']" />
    </AppLayout>
</template>