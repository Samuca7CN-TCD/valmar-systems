<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import CreateUpdateSellsModal from '@/Components/Movements/CreateUpdateSellsModal.vue'
import FloatButton from '@/Components/FloatButton.vue'
import ExtraOptionsButton from '@/Components/ExtraOptionsButton.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { ChevronDownIcon, ChevronUpIcon, InformationCircleIcon, EyeIcon, MagnifyingGlassIcon, PencilIcon, XMarkIcon } from '@heroicons/vue/24/outline'
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
    sells_list: Object,
    items: Object,
    parameters: Object,
})
const $toast = useToast();

// =============================================
// Informações do OBJETO
const sell_data = useForm({
    'id': null,
    'client': '',
    'date': formatDate(),
    'estimated_value': 0,
    'total_value': 0,
    'partial_value': 0,
    'discount_percent': 0,
    'discount': 0,
    'entry_value': 0,
    'observations': '',
    'items_list': [],
})

const sell_filter_toggle = ref(false)
const sell_filter = useForm({
    entity_name: props.parameters.entity_name,
    start_date: props.parameters.start_date,
    end_date: props.parameters.end_date,
})

const search_term = ref("")

const filtered_sells_list = computed(() => {
    const searchTermLower = search_term.value.toLowerCase();
    return props.sells_list.filter(el => {
        const { id, entity_name, observations, date, accounting, items } = el;
        const { total_value, partial_value } = accounting;
        const fieldsToCheck = [
            id.toString(),
            entity_name?.toLowerCase() ?? "",
            observations?.toLowerCase() ?? "",
            date?.toLowerCase() ?? "",
            toMoney(total_value).toLowerCase() ?? "",
            toMoney(partial_value).toLowerCase() ?? ""
        ];
        for (let field of fieldsToCheck) {
            if (field.includes(searchTermLower)) {
                return true;
            }
        }
        return items.some(item => item.name.toLowerCase().includes(searchTermLower));
    });
});


const total_sells_amount = computed(() => {
    return props.sells_list.reduce((accumulator, sell) => {
        return accumulator + sell.accounting.partial_value;
    }, 0);
});
// =============================================
// Controle de Modal
const modal = ref({
    mode: 'create',
    show: false,
    get title() {
        switch (this.mode) {
            case 'create': return "Criar venda"
            case 'update': return "Editar venda"
            case 'see': return "Ver informações da venda"
        }
    },
    get primary_button_txt() {
        switch (this.mode) {
            case 'create': return "Cadastrar"
            case 'update': return "Atualizar"
            case 'see': return "Fechar"
        }
    }
})

const openModal = (mode, sell_id = null) => {
    const isUpdateOrSeeMode = ['update', 'see'].includes(mode);
    if (sell_id !== null && isUpdateOrSeeMode) {
        const sell = props.sells_list.find(sell => sell.id === sell_id);
        if (sell) {
            const { id, entity_name, date, observations, accounting, items } = sell;

            sell_data.id = id;
            sell_data.client = entity_name;
            sell_data.date = date;
            sell_data.estimated_value = accounting.estimated_value;
            sell_data.total_value = accounting.total_value;
            sell_data.partial_value = accounting.partial_value;
            sell_data.entry_value = 0;
            sell_data.discount = accounting.estimated_value - accounting.total_value;
            sell_data.discount_percent = (sell_data.discount / (accounting.estimated_value || 1)) * 100;
            sell_data.observations = observations;
            sell_data.items_list = items
        }
    }
    modal.value.mode = mode;
    modal.value.show = true;
};


const closeModal = () => {
    sell_data.reset()
    modal.value.show = false
}


// =============================================
// Métodos de CRUD
const createSell = () => {
    sell_data.post(route('sells.store'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: (error) => { console.log(error) }
    })
}

const get_filtered_data = () => {
    sell_filter.post(route('sells.filter'), {
        preserveScroll: true,
    });
};

const updateSell = () => {
    sell_data.put(route('sells.update', sell_data.id), {
        preserveScroll: true,
        onSuccess: () => closeModal()
    })
}


const deleteSell = (sell_id, sell_name) => {
    if (confirm(`Você tem certeza que deseja excluir a venda de material para "${sell_name}"? Todos os materiais comprados voltarão para o estoque! Esta ação não poderá ser desfeita!`)) {
        sell_data.delete(route('sells.destroy', sell_id), {
            preserveScroll: true,
            onSuccess: () => $toast.success('Venda deletado com sucesso!')
        })
    }
}

const submit = () => {
    switch (modal.value.mode) {
        case 'create': return createSell()
        case 'update': return updateSell()
        case 'see': return closeModal()
        default: $toast.error('Método desconhecido. Informar o Técnico.')
    }
}
</script>

<template>

    <Head class="print:hidden" :title="page.name" />
    <AppLayout :page="page" :page_options="page_options">
        <template class="print:hidden" #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ page.name }} <span v-if="$page.props.auth.user.hierarchy < 2">| {{ toMoney(total_sells_amount)
                    }}</span>
            </h2>
            <FloatButton :icon="'plus'" @click="openModal('create')" title="Cadastrar Sell" class="print:hidden" />
        </template>

        <div class="pt-12 print:hidden print:pt-0">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 print:w-full">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                    <div class="flex flex-row items-center space-x-3">
                        <MagnifyingGlassIcon class="w-5 y-5" />
                        <input type="text" name="search_term" id="search_term" autocomplete="on" class="simple-input"
                            autofocus="true" placeholder="Pesquisar termo..." v-model="search_term" />
                        <span class="cursor-help select-none"
                            title="O campo de pesquisa ao lado filtra as informações carregadas na pagina. O formulário de pesquisa abaixo busca as informações filtradas no banco de dados e lista na página.">
                            <InformationCircleIcon class="w-5 h-5" />
                        </span>

                        <span v-if="!sell_filter_toggle" class="select-none cursor-pointer"
                            title="Abrir opções de filtro" @click="sell_filter_toggle = true">
                            <ChevronDownIcon class="w-5 y-5" />
                        </span>
                        <span v-else class="select-none cursor-pointer" title="Fechar opções de filtro"
                            @click="sell_filter_toggle = false">
                            <ChevronUpIcon class="w-5 y-5" />
                        </span>

                    </div>
                    <div v-show="sell_filter_toggle" class="mt-3 grid grid-cols-3 gap-2">
                        <div class="col-span-3 md:col-span-1">
                            <label for="search-by-entity-name"
                                class="block text-sm font-medium leading-6 text-gray-900">Cliente</label>
                            <div class="mt-2">
                                <input type="text" name="search-by-entity-name" id="search-by-entity-name"
                                    class="simple-input disabled:bg-gray-200" placeholder="Filtrar por nome do cliente"
                                    v-model="sell_filter.entity_name" @input="get_filtered_data" required>
                                <p v-if="sell_filter.errors.entity_name" class="text-red-500 text-sm">{{
        sell_filter.errors.entity_name }}</p>
                            </div>
                        </div>

                        <div class="col-span-3 md:col-span-1">
                            <label for="search-by-start-date"
                                class="block text-sm font-medium leading-6 text-gray-900">Data
                                (início)</label>
                            <div class="mt-2">
                                <input type="date" name="search-by-start-date" id="search-by-start-date"
                                    class="simple-input disabled:bg-gray-200" placeholder="Filtrar por período (início)"
                                    v-model="sell_filter.start_date" @input="get_filtered_data" required>
                                <p v-if="sell_filter.errors.start_date" class="text-red-500 text-sm">{{
        sell_filter.errors.start_date }}</p>
                            </div>
                        </div>

                        <div class="col-span-3 md:col-span-1">
                            <label for="search-by-end-date"
                                class="block text-sm font-medium leading-6 text-gray-900">Data
                                (fim)</label>
                            <div class="mt-2">
                                <input type="date" name="search-by-end-date" id="search-by-end-date"
                                    class="simple-input disabled:bg-gray-200" placeholder="Filtrar por período (fim)"
                                    v-model="sell_filter.end_date" @input="get_filtered_data" required>
                                <p v-if="sell_filter.errors.end_date" class="text-red-500 text-sm">{{
        sell_filter.errors.end_date }}</p>
                            </div>
                        </div>

                        <!--<button type="button"
                            class="col-span-4 p-1 text-white rounded-md bg-green-500 hover:bg-green-700 active:bg-green-900"
                            @click="get_filtered_data">
                            Pesquisar
                        </button>-->
                        <a :href="route('sells.index')"
                            class="text-red-500 hover:text-red-700 active:text-red-900 text-sm select-none mt-2"
                            @click="sell_filter.reset()">Resetar busca</a>
                    </div>

                </div>
            </div>
        </div>

        <div class="py-12 print:py-0">
            <div class="max-w-7xl mx-auto print:max-w-full">
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
                                                    <th scope="col" class="px-6 py-4 text-center">Cliente</th>
                                                    <th scope="col" class="px-6 py-4 text-center">À pagar</th>
                                                    <th scope="col" class="px-6 py-4 text-center">Valor total</th>
                                                    <th scope="col" class="px-6 py-4 text-center">Data da Venda
                                                    </th>
                                                    <th scope="col" class="px-6 py-4 text-center print:hidden"
                                                        colspan="2">
                                                        Ações</th>
                                                    <!--
                                                    <th scope="col" class="px-6 py-4 text-center print:hidden">Concluir</th>
                                                    <th scope="col" class="px-6 py-4 text-center print:hidden">Pagar</th>
                                                    <th scope="col" class="px-6 py-4 text-center print:hidden">Editar</th>
                                                    <th scope="col" class="px-6 py-4 text-center print:hidden">Excluir</th>
                                                    -->
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-if="filtered_sells_list.length"
                                                    v-for="sell in filtered_sells_list"
                                                    class="border-b transition duration-300 ease-in-out hover:bg-neutral-100 print:break-inside-avoid">
                                                    <td class="whitespace-nowrap py-4 text-center font-medium">{{
        sell.id }}
                                                    </td>
                                                    <td class="whitespace-normal px-6 py-4 text-center trim">{{
        sell.entity_name }}
                                                        <span v-if="sell.observations" :title="sell.observations"
                                                            class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10 cursor-help">OBS</span>
                                                    </td>
                                                    <td class="whitespace-nowrap px-2 py-4 text-center">{{
        toMoney(sell.accounting.partial_value) }}</td>
                                                    <td class="whitespace-nowrap px-2 py-4 text-center">{{
        sell.accounting.total_value > 0 ?
            toMoney(sell.accounting.total_value) : 'Pago' }}</td>
                                                    <td class="whitespace-nowrap px-2 py-4 text-center">{{
                                                        formatDate(sell.date, true) }}</td>
                                                    <!--
                                                        <td class="whitespace-nowrap px-4 py-4 text-center cursor-pointer hover:text-yellow-700 active:text-yellow-900 select-none print:hidden"
                                                        :title="'EDITAR: Compra de ' + sell.entity_name"
                                                        @click="openModal('update', sell.id)">
                                                            <PencilIcon class="w-5 h-5 m-auto" />
                                                        </td>
                                                    -->
                                                    <td class="whitespace-nowrap px-4 py-4 text-center cursor-pointer hover:text-indigo-700 active:text-indigo-900 select-none print:hidden"
                                                        :title="'VER: Compra de ' + sell.entity_name"
                                                        @click="openModal('see', sell.id)">
                                                        <EyeIcon class="w-5 h-5 m-auto" />
                                                    </td>
                                                    <td class="whitespace-nowrap px-4 py-4 text-center cursor-pointer hover:text-red-700 active:text-red-900 select-none print:hidden"
                                                        :title="'EXCLUIR: Compra de ' + sell.entity_name"
                                                        @click="deleteSell(sell.id, sell.entity_name)">
                                                        <XMarkIcon class="w-5 h-5 m-auto" />
                                                    </td>
                                                </tr>
                                                <tr v-else
                                                    class="transition duration-300 ease-in-out hover:bg-neutral-100 print:break-inside-avoid">
                                                    <td class="whitespace-nowrap px-6 py-4 text-center" colspan="9">Não
                                                        há
                                                        vendas cadastradas no momento!</td>
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

        <CreateUpdateSellsModal :modal="modal" :sell="sell_data" :items="items" @submit="submit" @close="closeModal" />

    </AppLayout>
</template>
