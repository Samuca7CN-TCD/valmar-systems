<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import CreateUpdatePayModal from '@/Components/Services/CreateUpdatePayModal.vue'
import FloatButton from '@/Components/FloatButton.vue'
import ExtraOptionsButton from '@/Components/ExtraOptionsButton.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { BanknotesIcon, CheckBadgeIcon, MagnifyingGlassIcon, PencilIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import { toMoney, formatDate, calcDeadlineDays } from '@/general.js'

// =============================================
// Informações exteriores
const props = defineProps({
    page: Object,
    page_options: {
        type: Array,
        default: null,
    },
    services_list: Object,
})


// =============================================
// Informações do OBJETO
const service_data = useForm({
    'id': null,
    'title': '',
    'client': '',
    'total_value': 0,
    'partial_value': 0,
    'observations': '',
    'deadline': formatDate(new Date(Date.now() + 15 * 24 * 60 * 60 * 1000), 'new_date'),
    'records_list': {
        'enable_records': false,
        'data': [],
    }
})

const search_term = ref("")

const filtered_services_list = computed(() => {
    const searchTermLower = search_term.value.toLowerCase()

    return props.services_list.filter(el => 
        (el.motive.toLowerCase().includes(searchTermLower)) ||
        (el.entity_name.toLowerCase().includes(searchTermLower)) ||
        (el.observations.toLowerCase().includes(searchTermLower)) ||
        (toMoney(el.accounting.total_value).toString().toLowerCase().includes(searchTermLower)) ||
        (toMoney(el.accounting.partial_value).toString().toLowerCase().includes(searchTermLower)) ||
        (el.deadline.includes(searchTermLower))
    );
});

const total_services_amount = computed(() => {
    return props.services_list.reduce((accumulator, service) => {
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

const openModal = (mode, service_id = null) => {
    const isUpdateOrPayMode = ['update', 'pay'].includes(mode);

    if (service_id !== null && isUpdateOrPayMode) {
        const  service = props.services_list.find(service => service.id === service_id);

        if (service) {
            const { id, motive, entity_name, observations, records, accounting } = service;

            service_data.id = id;
            service_data.title = motive;
            service_data.client = entity_name;
            service_data.total_value = accounting.total_value;
            service_data.partial_value = accounting.partial_value;
            service_data.observations = observations;
            service_data.records_list.enable_records = Boolean(records.length);
            service_data.records_list.data = records.map((record) => { return useForm(record) })
        }
    }

    modal.value.mode = mode;
    modal.value.show = true;
};


const closeModal = () => {
    service_data.reset()
    modal.value.show = false
}



// =============================================
// Métodos de CRUD
const createService = () => {
    service_data.post(route('services.store'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: (error) => {console.log(error)}
    })
}

const updateService = () => {
    service_data.put(route('services.update', service_data.id), {
        preserveScroll: true,
        onSuccess: () => closeModal()
    })
}

const payService = () => {
    service_data.put(route('services.pay', service_data.id), {
        preserveScroll: true,
        onSuccess: () => closeModal()
    })
}

const concludeService = (service_id, service_partial_value) => {
    if(service_partial_value !== 0){
        if(!confirm("O serviço será considerado como concluído e será listado apenas na página de pagamentos")) return
    }else{
        if(!confirm("O serviço será considerado concluído e pago!")) return
    }
    service_data.put(route('services.conclude', service_id), {
        preserveScroll: true,
    })
}

const deleteService = (service_id, service_name) => {
    if (confirm(`Você tem certeza que deseja excluir o serviço "${service_name}"? Esta ação não poderá ser desfeita!`)) {
        service_data.delete(route('services.destroy', service_id), {
            preserveScroll: true,
            onSuccess: () => alert('Serviço deletado com sucesso!')
        })
    }
}

const submit = () => {
    switch(modal.value.mode){
        case 'create': return createService()
        case 'update': return updateService()
        case 'pay': return payService()
        default: alert('Método desconhecido. Informar o Técnico.')
    }
}
</script>

<template>
    <Head :title="page.name" />
    <AppLayout :page="page" :page_options="page_options">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ page.name }} | {{ toMoney(total_services_amount) }}
            </h2>
            <FloatButton :icon="'plus'" @click="openModal('create')" title="Cadastrar Service" class="print:hidden" />
        </template>
        
        <div class="pt-12 print:hidden print:pt-0">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 print:w-full">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                    <div class="flex flex-row items-center space-x-3">
                        <MagnifyingGlassIcon class="w-5 y-5" />
                        <input type="text" name="search_term" id="search_term" autocomplete="on"
                            class="simple-input" autofocus="true"
                            placeholder="Pesquisar termo..."
                            v-model="search_term" />
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
                                            <thead class="border-b font-medium dark:border-neutral-500">
                                                <tr>
                                                    <th scope="col" class="px-6 py-4 text-center">#</th>
                                                    <th scope="col" class="px-6 py-4 text-center">Prazo</th>
                                                    <th scope="col" class="px-6 py-4 text-center">Título</th>
                                                    <th scope="col" class="px-6 py-4 text-center">Cliente</th>
                                                    <th scope="col" class="px-6 py-4 text-center">À pagar</th>
                                                    <th scope="col" class="px-6 py-4 text-center">Valor total</th>
                                                    <th scope="col" class="px-6 py-4 text-center print:hidden">Entrega</th>
                                                    <th scope="col" class="px-6 py-4 text-center print:hidden" colspan="4">Ações</th>
                                                    <!--
                                                    <th scope="col" class="px-6 py-4 text-center print:hidden">Concluir</th>
                                                    <th scope="col" class="px-6 py-4 text-center print:hidden">Pagar</th>
                                                    <th scope="col" class="px-6 py-4 text-center print:hidden">Editar</th>
                                                    <th scope="col" class="px-6 py-4 text-center print:hidden">Excluir</th>
                                                    -->
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-if="filtered_services_list.length" v-for="service in filtered_services_list"
                                                    class="border-b transition duration-300 ease-in-out hover:bg-neutral-100 dark:border-neutral-500 dark:hover:bg-neutral-600">
                                                    <td class="whitespace-nowrap py-4 text-center font-medium">{{
                                                        service.id }}
                                                    </td>
                                                    <td class="whitespace-nowrap py-4 text-center font-medium" :class="deadlineClass(service.deadline)">
                                                        {{ calcDeadlineDays(service.deadline) }} dias
                                                    </td>
                                                    <td class="whitespace-normal px-6 py-4 text-center trim">{{
                                                        service.motive }}
                                                        <span v-if="service.observations" :title="service.observations"
                                                            class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10 cursor-help">OBS</span>
                                                    </td>
                                                    <td class="whitespace-normal px-6 py-4 text-center">{{
                                                        service.entity_name }}</td>
                                                    <td class="whitespace-nowrap px-2 py-4 text-center">{{
                                                        toMoney(service.accounting.partial_value) }}</td>
                                                    <td class="whitespace-nowrap px-2 py-4 text-center">{{
                                                        toMoney(service.accounting.total_value) }}</td>
                                                    <td class="whitespace-nowrap px-2 py-4 text-center print:hidden">{{
                                                        formatDate(service.deadline, true) }}</td>
                                                    <td class="whitespace-nowrap px-4 py-4 pl-10 text-center cursor-pointer hover:text-blue-700 active:text-blue-900 select-none print:hidden"
                                                        :title="'CONCLUIR: ' + service.motive + '(' + service.entity_name + ')'"
                                                        @click="concludeService(service.id, service.accounting.partial_value)">
                                                        <CheckBadgeIcon  class="w-4 h-4 m-auto" />
                                                    </td>
                                                    <td class="whitespace-nowrap px-4 py-4 text-center cursor-pointer hover:text-green-700 active:text-green-900 select-none print:hidden"
                                                        :title="'PAGAR: ' + service.motive + '(' + service.entity_name + ')'"
                                                        @click="openModal('pay', service.id)">
                                                        <span v-if="service.accounting.partial_value !== 0" >
                                                            <BanknotesIcon class="w-4 h-4 m-auto" />
                                                        </span>
                                                        <span v-else class="m-auto text-center">PAGO</span>
                                                    </td>
                                                    <td class="whitespace-nowrap px-4 py-4 text-center cursor-pointer hover:text-yellow-700 active:text-yellow-900 select-none print:hidden"
                                                        :title="'EDITAR: ' + service.motive + '(' + service.entity_name + ')'"
                                                        @click="openModal('update', service.id)">
                                                        <PencilIcon class="w-4 h-4 m-auto" />
                                                    </td>
                                                    <td class="whitespace-nowrap px-4 py-4 text-center cursor-pointer hover:text-red-700 active:text-red-900 select-none print:hidden"
                                                        :title="'EXCLUIR: ' + service.motive + '(' + service.entity_name + ')'"
                                                        @click="deleteService(service.id, service.name)">
                                                        <XMarkIcon class="w-4 h-4 m-auto" />
                                                    </td>
                                                </tr>
                                                <tr v-else
                                                    class="transition duration-300 ease-in-out hover:bg-neutral-100 dark:border-neutral-500 dark:hover:bg-neutral-600">
                                                    <td class="whitespace-nowrap px-6 py-4 text-center" colspan="9">Não há
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
        
        <CreateUpdatePayModal :show="modal.show" :modal="modal" :service="service_data" @submit="submit"
            @close="closeModal" />

        <ExtraOptionsButton :mode="['rollup', 'print_page', 'link']" :link_type="['previous']" :link="[route('services.previous')]" />
    </AppLayout>
</template>
