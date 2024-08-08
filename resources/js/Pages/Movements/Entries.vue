<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import CreateUpdateEntriesModal from '@/Components/Movements/CreateUpdateEntriesModal.vue'
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
    entries_list: Object,
    employees_list: Array,
    items: Object,
    parameters: Object,
})

const $toast = useToast();
// =============================================
// Informações do OBJETO
const entry_data = useForm({
    'id': null,
    'employee': '',
    'motive': '',
    'date': formatDate(),
    'total_value': 0,
    'observations': '',
    'items_list': [],
})

const entry_filter_toggle = ref(false)
const entry_filter = useForm({
    employee_id: props.parameters.employee_id,
    motive: props.parameters.motive,
    start_date: props.parameters.start_date,
    end_date: props.parameters.end_date,
})

const search_term = ref("")

const filtered_entries_list = computed(() => {
    const searchTermLower = search_term.value.toLowerCase();
    return props.entries_list.filter(el => {
        const { id, entity_name, motive, observations, date, items } = el;
        const fieldsToCheck = [
            id.toString(),
            entity_name?.toLowerCase() ?? "",
            motive?.toLowerCase() ?? "",
            observations?.toLowerCase() ?? "",
            date?.toLowerCase() ?? "",
        ];
        for (let field of fieldsToCheck) {
            if (field.includes(searchTermLower)) {
                return true;
            }
        }
        return items.some(item => item.name.toLowerCase().includes(searchTermLower));
    });
});


const total_entries_amount = computed(() => {
    return props.entries_list.reduce((accumulator, entry) => {
        return accumulator + entry.accounting.partial_value;
    }, 0);
});
// =============================================
// Controle de Modal
const modal = ref({
    mode: 'create',
    show: false,
    get title() {
        switch (this.mode) {
            case 'create': return "Criar entrada"
            case 'update': return "Editar entrada"
            case 'see': return "Ver informações da entrada"
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

const openModal = (mode, entry_id = null) => {
    const isUpdateOrSeeMode = ['update', 'see'].includes(mode);
    if (entry_id !== null && isUpdateOrSeeMode) {
        const entry = props.entries_list.find(entry => entry.id === entry_id);
        if (entry) {
            const { id, entity_name, date, motive, observations, accounting, items } = entry;

            entry_data.id = id;
            entry_data.employee = entity_name;
            entry_data.motive = motive;
            entry_data.date = date;
            entry_data.estimated_value = accounting.estimated_value;
            entry_data.total_value = accounting.total_value;
            entry_data.partial_value = accounting.partial_value;
            entry_data.observations = observations;
            entry_data.items_list = items
        }
    }
    modal.value.mode = mode;
    modal.value.show = true;
};


const closeModal = () => {
    entry_data.reset()
    modal.value.show = false
}


// =============================================
// Métodos de CRUD
const createUse = () => {
    entry_data.post(route('entries.store'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: (error) => { console.log(error) }
    })
}

const get_filtered_data = () => {
    entry_filter.post(route('entries.filter'), {
        preserveScroll: true,
    });
};

const updateUse = () => {
    entry_data.put(route('entries.update', entry_data.id), {
        preserveScroll: true,
        onSuccess: () => closeModal()
    })
}


const deleteUse = (entry_id, entry_name) => {
    if (confirm(`Você tem certeza que deseja excluir a entrada de material para "${entry_name}"? Todos os materiais comprados voltarão para o estoque! Esta ação não poderá ser desfeita!`)) {
        entry_data.delete(route('entries.destroy', entry_id), {
            preserveScroll: true,
            onSuccess: () => $toast.success('Entrada deletado com sucesso!')
        })
    }
}

const submit = () => {
    switch (modal.value.mode) {
        case 'create': return createUse()
        case 'update': return updateUse()
        case 'see': return closeModal()
        default: $toast.error('Método desconhecido. Informar o Técnico.')
    }
}
</script>

<template>

    <Head :title="page.name" />
    <AppLayout :page="page" :page_options="page_options">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ page.name }} | {{ toMoney(total_entries_amount) }}
            </h2>
            <FloatButton :icon="'plus'" @click="openModal('create')" title="Cadastrar Entry" class="print:hidden" />
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

                        <span v-if="!entry_filter_toggle" class="select-none cursor-pointer"
                            title="Abrir opções de filtro" @click="entry_filter_toggle = true">
                            <ChevronDownIcon class="w-5 y-5" />
                        </span>
                        <span v-else class="select-none cursor-pointer" title="Fechar opções de filtro"
                            @click="entry_filter_toggle = false">
                            <ChevronUpIcon class="w-5 y-5" />
                        </span>

                    </div>
                    <div v-show="entry_filter_toggle" class="mt-3 grid grid-cols-4 gap-2">
                        <div class="col-span-4 md:col-span-1">
                            <label for="search-by-employee"
                                class="block text-sm font-medium leading-6 text-gray-900">Funcionário</label>
                            <div class="mt-2">
                                <select name="search-by-employee" id="search-by-employee"
                                    class="simple-select disabled:bg-gray-200" v-model="entry_filter.employee_id"
                                    @change="get_filtered_data">
                                    <option :value="0">Todos</option>
                                    <option v-for="employee in employees_list" :key="employee.id" :value="employee.id">
                                        {{ employee.name }} {{ employee.surname }}
                                    </option>
                                </select>
                                <p v-if="entry_filter.errors.employee_id" class="text-red-500 text-sm">{{
        entry_filter.errors.employee_id }}</p>
                            </div>
                        </div>

                        <div class="col-span-4 md:col-span-1">
                            <label for="search-by-motive"
                                class="block text-sm font-medium leading-6 text-gray-900">Motivo</label>
                            <div class="mt-2">
                                <input type="text" name="search-by-motive" id="search-by-motive"
                                    class="simple-input disabled:bg-gray-200" placeholder="Filtrar por motivo"
                                    v-model="entry_filter.motive" @input="get_filtered_data" required>
                                <p v-if="entry_filter.errors.motive" class="text-red-500 text-sm">{{
        entry_filter.errors.motive }}</p>
                            </div>
                        </div>

                        <div class="col-span-2 md:col-span-1">
                            <label for="search-by-start-date"
                                class="block text-sm font-medium leading-6 text-gray-900">Data
                                (início)</label>
                            <div class="mt-2">
                                <input type="date" name="search-by-start-date" id="search-by-start-date"
                                    class="simple-input disabled:bg-gray-200" placeholder="Filtrar por período (início)"
                                    v-model="entry_filter.start_date" @input="get_filtered_data" required>
                                <p v-if="entry_filter.errors.start_date" class="text-red-500 text-sm">{{
        entry_filter.errors.start_date }}</p>
                            </div>
                        </div>

                        <div class="col-span-2 md:col-span-1">
                            <label for="search-by-end-date"
                                class="block text-sm font-medium leading-6 text-gray-900">Data
                                (fim)</label>
                            <div class="mt-2">
                                <input type="date" name="search-by-end-date" id="search-by-end-date"
                                    class="simple-input disabled:bg-gray-200" placeholder="Filtrar por período (fim)"
                                    v-model="entry_filter.end_date" @input="get_filtered_data" required>
                                <p v-if="entry_filter.errors.end_date" class="text-red-500 text-sm">{{
        entry_filter.errors.end_date }}</p>
                            </div>

                        </div>
                        <a :href="route('entries.index')"
                            class="text-red-500 hover:text-red-700 active:text-red-900 text-sm select-none mt-2"
                            @click="entry_filter.reset()">Resetar busca</a>
                        <!--<button type="button"
                            class="col-span-4 p-1 text-white rounded-md bg-green-500 hover:bg-green-700 active:bg-green-900"
                            @click="get_filtered_data">
                            Pesquisar
                        </button>-->
                    </div>

                </div>
            </div>
        </div>

        <div class="py-12 print:py-6">
            <div class="max-w-7xl mx-auto print:max-w-full">
                <div class="px-0 print:px-0">
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg print:shadow-none">
                        <div class="flex flex-col">
                            <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
                                <div class="inline-block min-w-full py-2 sm:px-6 lg:px-8">
                                    <div class="overflow-hidden">
                                        <table class="min-w-full text-left text-sm font-light">
                                            <thead class="border-b font-medium">
                                                <tr>
                                                    <th scope="col" class="px-6 py-4 text-center">#</th>
                                                    <!--<th scope="col" class="px-6 py-4 text-center">Funcionário</th>-->
                                                    <th scope="col" class="px-6 py-4 text-center">Motivo</th>
                                                    <th scope="col" class="px-6 py-4 text-center">Data do Entrada
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
                                                <tr v-if="filtered_entries_list.length"
                                                    v-for="entry in filtered_entries_list"
                                                    class="border-b transition duration-300 ease-in-out hover:bg-neutral-100 print:break-inside-avoid">
                                                    <td class="whitespace-nowrap py-4 text-center font-medium">{{
        entry.id }}
                                                    </td>
                                                    <!--<td class="whitespace-normal px-6 py-4 text-center trim">{{
        entry.entity_name }}
                                                        
                                                    </td>-->
                                                    <td class="whitespace-nowrap px-2 py-4 text-center">{{
        toMoney(entry.motive) }}
                                                        <span v-if="entry.observations" :title="entry.observations"
                                                            class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10 cursor-help">OBS</span>
                                                    </td>
                                                    <td class="whitespace-nowrap px-2 py-4 text-center print:hidden">{{
        formatDate(entry.date, true) }}</td>
                                                    <!--
                                                        <td class="whitespace-nowrap px-4 py-4 text-center cursor-pointer hover:text-yellow-700 active:text-yellow-900 select-none print:hidden"
                                                        :title="'EDITAR: Compra de ' + entry.entity_name"
                                                        @click="openModal('update', entry.id)">
                                                            <PencilIcon class="w-5 h-5 m-auto" />
                                                        </td>
                                                    -->
                                                    <td class="whitespace-nowrap px-4 py-4 text-center cursor-pointer hover:text-indigo-700 active:text-indigo-900 select-none print:hidden"
                                                        title='Ver Entrada' @click="openModal('see', entry.id)">
                                                        <EyeIcon class="w-5 h-5 m-auto" />
                                                    </td>
                                                    <td class="whitespace-nowrap px-4 py-4 text-center cursor-pointer hover:text-red-700 active:text-red-900 select-none print:hidden"
                                                        title="Excluir entrada"
                                                        @click="deleteUse(entry.id, entry.motive)">
                                                        <XMarkIcon class="w-5 h-5 m-auto" />
                                                    </td>
                                                </tr>
                                                <tr v-else
                                                    class="transition duration-300 ease-in-out hover:bg-neutral-100 print:break-inside-avoid">
                                                    <td class="whitespace-nowrap px-6 py-4 text-center" colspan="9">Não
                                                        há
                                                        entradas cadastradas no momento!</td>
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

        <CreateUpdateEntriesModal :modal="modal" :entry="entry_data" :items="items" :employees_list="employees_list"
            @submit="submit" @close="closeModal" />

    </AppLayout>
</template>
