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
    employees_list: Array,
})

const search_term = ref("")

const contacts_length = computed(() => {
    return props.employees_list.contacts ? JSON.parse(props.employees_list.contacts).length : 0
})

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
</script>

<template>

    <Head :title="page.name" />
    <AppLayout :page="page" :page_options="page_options">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ page.name }} | {{ toMoney(total_employees_amount) }}
            </h2>
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
                                                    <td class="grid px-2 py-4 text-center print:hidden " :class="{
        'grid-cols-1': contacts_length === 1,
        'grid-cols-2': contacts_length > 1
    }">
                                                        <a v-for="contact in JSON.parse(employee.contacts)"
                                                            :href="'tel:' + contact"
                                                            class="px-2 hover:text-green-500 active:text-green-700">{{
        formatPhoneNumber(contact)
                                                            }}</a>
                                                    </td>
                                                    <td class="whitespace-nowrap px-2 py-4 text-center print:hidden">
                                                        {{ employee.function_name }}</td>


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

        <ExtraOptionsButton :mode="['rollup', 'print_page']" />
    </AppLayout>
</template>
