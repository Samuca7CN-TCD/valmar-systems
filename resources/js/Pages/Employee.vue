<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import CreateUpdateEmployeesModal from '@/Components/Employees/CreateUpdateEmployeesModal.vue'
import FloatButton from '@/Components/FloatButton.vue'
import ExtraOptionsButton from '@/Components/ExtraOptionsButton.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { EyeIcon, MagnifyingGlassIcon, PencilIcon, XCircleIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import { toMoney, formatDate, formatPhoneNumber, calcDeadlineDays } from '@/general.js'

// =============================================
// Informações exteriores
const props = defineProps({
    page: Object,
    page_options: {
        type: Array,
        default: null,
    },
    employees_list: Object,
})


// =============================================
// Informações do OBJETO
const employee_data = useForm({
    'id': null,
    'name': '',
    'surname': '',
    'salary': 0,
    'agreement': 0,
    'contacts': [],
    'function_name': '',
    'transpotation_voucher': false,
    'payment_method_id': null,
    'bank_id': null,
    'pix_cpf': '',
    'pix_email': '',
    'pix_phone_number': '',
    'pix_token': '',
    'bank_ag': '',
    'account_type_id': null,
    'account_number': '',
})

const search_term = ref("")

const filtered_employees_list = computed(() => {
    const searchTermLower = search_term.value.toLowerCase();
    return props.employees_list.filter(el => {
        const { id, name, surname, salary, contacts, function_name } = el;
        const fieldsToCheck = [
            id?.toString() ?? "",
            name?.toLowerCase() ?? "",
            surname?.toLowerCase() ?? "",
            salary?.toString() ?? "",
            toMoney(salary || '-'),
            function_name?.toLowerCase() ?? "",
        ];

        // Verifica se algum contato contém o termo de busca
        const contactsContainTerm = JSON.parse(contacts).some(contact =>
            contact?.includes(searchTermLower)
        );

        for (let field of fieldsToCheck) {
            if (field.includes(searchTermLower)) {
                return true;
            }
        }

        // Verifica se algum item dentro dos contacts contém o termo de busca
        return contactsContainTerm;
    });
});


const total_employees_amount = computed(() => {
    return props.employees_list.reduce((accumulator, employee) => {
        return accumulator + (employee.salary + employee.agreement);
    }, 0);
});
// =============================================
// Controle de Modal
const modal = ref({
    mode: 'create',
    show: false,
    get title() {
        switch (this.mode) {
            case 'create': return "Criar funcionário"
            case 'update': return "Editar funcionário"
            case 'see': return "Ver informações do funcionário"
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

const openModal = (mode, employee_id = null) => {
    const isUpdateOrSeeMode = ['update', 'see'].includes(mode);
    if (employee_id !== null && isUpdateOrSeeMode) {
        const employee = props.employees_list.find(employee => employee.id === employee_id);
        if (employee) {
            const { id, name, surname, salary, agreement, contacts, function_name, transpotation_voucher, payment_method_id, bank_id, pix_cpf, pix_email, pix_phone_number, pix_token, bank_ag, account_type_id, account_number } = employee;

            employee_data.id = id;
            employee_data.name = name;
            employee_data.surname = surname;
            employee_data.salary = salary;
            employee_data.agreement = agreement;
            employee_data.contacts = JSON.parse(contacts);
            employee_data.function_name = function_name;
            employee_data.transpotation_voucher = transpotation_voucher;
            employee_data.payment_method_id = payment_method_id;
            employee_data.bank_id = bank_id;
            employee_data.pix_cpf = pix_cpf;
            employee_data.pix_email = pix_email;
            employee_data.pix_phone_number = pix_phone_number;
            employee_data.pix_token = pix_token;
            employee_data.bank_ag = bank_ag;
            employee_data.account_type_id = account_type_id;
            employee_data.account_number = account_number;
        }
    }
    modal.value.mode = mode;
    modal.value.show = true;
};


const closeModal = () => {
    employee_data.reset()
    modal.value.show = false
}


// =============================================
// Métodos de CRUD
const createEmployee = () => {
    employee_data.post(route('employees.store'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: (error) => { console.log(error) }
    })
}

const updateEmployee = () => {
    employee_data.put(route('employees.update', employee_data.id), {
        preserveScroll: true,
        onSuccess: () => closeModal()
    })
}

const fireEmployee = (employee_id, employee_name) => {
    if (confirm(`Você tem certeza que deseja DEMITIR o funcionário "${employee_name}"?`)) {
        employee_data.post(route('employees.fire', employee_id), {
            preserveScroll: true,
            onSuccess: () => alert('Funcionário demitido com sucesso!')
        })
    }
}

const deleteEmployee = (employee_id, employee_name) => {
    if (confirm(`Você tem certeza que deseja EXCLUIR o funcionário "${employee_name}"?`)) {
        employee_data.delete(route('employees.destroy', employee_id), {
            preserveScroll: true,
            onSuccess: () => alert('Funcionário deletado com sucesso!')
        })
    }
}

const submit = () => {
    switch (modal.value.mode) {
        case 'create': return createEmployee()
        case 'update': return updateEmployee()
        case 'see': return closeModal()
        default: alert('Método desconhecido. Informar o Técnico.')
    }
}
</script>

<template>

    <Head :title="page.name" />
    <AppLayout :page="page" :page_options="page_options">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ page.name }} | {{ toMoney(total_employees_amount) }}
            </h2>
            <FloatButton :icon="'plus'" @click="openModal('create')" title="Cadastrar Employee" class="print:hidden" />
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
                                                    <th scope="col" class="px-6 py-4 text-center">Funcionário</th>
                                                    <th scope="col" class="px-6 py-4 text-center">Salário</th>
                                                    <th scope="col" class="px-6 py-4 text-center">Contatos</th>
                                                    <th scope="col" class="px-6 py-4 text-center">Função</th>
                                                    <th scope="col" class="px-6 py-4 text-center print:hidden"
                                                        colspan="4">
                                                        Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-if="filtered_employees_list.length"
                                                    v-for="employee in filtered_employees_list"
                                                    class="border-b transition duration-300 ease-in-out hover:bg-neutral-100 print:break-inside-avoid">
                                                    <td class="whitespace-nowrap py-4 text-center font-medium">{{
        employee.id }}
                                                    </td>
                                                    <td class="whitespace-normal px-6 py-4 text-center trim">{{
        employee.name + ' ' + employee.surname }}
                                                    </td>
                                                    <td class="whitespace-nowrap px-2 py-4 text-center">{{
        toMoney(employee.salary + employee.agreement) }}
                                                        <span v-if="employee.agreement"
                                                            :title="'Salário: ' + toMoney(employee.salary) + ' + Acordo: ' + toMoney(employee.agreement)"
                                                            class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10 cursor-help">OBS</span>
                                                    </td>
                                                    <td
                                                        class="whitespace-nowrap px-2 py-4 text-center print:hidden divide-x divide-neutral-500">
                                                        <a v-for="contact in JSON.parse(employee.contacts)"
                                                            :href="'tel:' + contact"
                                                            class="px-2 hover:text-green-500 active:text-green-700">{{
        formatPhoneNumber(contact)
    }}</a>
                                                    </td>
                                                    <td class="whitespace-nowrap px-2 py-4 text-center print:hidden">
                                                        {{ employee.function_name }}</td>

                                                    <td class="whitespace-nowrap px-4 py-4 text-center cursor-pointer hover:text-yellow-700 active:text-yellow-900 select-none print:hidden"
                                                        :title="'EDITAR: ' + employee.name + ' ' + employee.surname"
                                                        @click="openModal('update', employee.id)">
                                                        <PencilIcon class="w-5 h-5 m-auto" />
                                                    </td>
                                                    <td class="whitespace-nowrap px-4 py-4 text-center cursor-pointer hover:text-indigo-700 active:text-indigo-900 select-none print:hidden"
                                                        :title="'VER: ' + employee.name + ' ' + employee.surname"
                                                        @click="openModal('see', employee.id)">
                                                        <EyeIcon class="w-5 h-5 m-auto" />
                                                    </td>
                                                    <td class="whitespace-nowrap px-4 py-4 text-center cursor-pointer hover:text-orange-700 active:text-orange-900 select-none print:hidden"
                                                        :title="'Demitir: ' + employee.name + ' ' + employee.surname"
                                                        @click="fireEmployee(employee.id, employee.entity_name)">
                                                        <XCircleIcon class="w-5 h-5 m-auto" />
                                                    </td>
                                                    <td class="whitespace-nowrap px-4 py-4 text-center cursor-pointer hover:text-red-700 active:text-red-900 select-none print:hidden"
                                                        :title="'EXCLUIR: ' + employee.name + ' ' + employee.surname"
                                                        @click="deleteEmployee(employee.id, employee.entity_name)">
                                                        <XMarkIcon class="w-5 h-5 m-auto" />
                                                    </td>
                                                </tr>
                                                <tr v-else
                                                    class="transition duration-300 ease-in-out hover:bg-neutral-100 print:break-inside-avoid">
                                                    <td class="whitespace-nowrap px-6 py-4 text-center" colspan="9">Não
                                                        há
                                                        funcionários cadastradas no momento!</td>
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

        <CreateUpdateEmployeesModal :modal="modal" :employee="employee_data" @submit="submit" @close="closeModal" />

    </AppLayout>
</template>
