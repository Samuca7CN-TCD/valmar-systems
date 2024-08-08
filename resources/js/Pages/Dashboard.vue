<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Welcome from '@/Components/Welcome.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue'
import SeeRecords from '@/Components/Dashboard/SeeRecords.vue'
import ExtraOptionsButton from '@/Components/ExtraOptionsButton.vue'
import { formatDate } from '@/general.js';

const props = defineProps({
    page: Object,
    title: String,
    procedures: Array,
    parameters: Object,
    users: Array,
    actions: Array,
    departments: Array,
})

const modal = ref(false);
const selected_procedure = ref(null);

const form = useForm({
    'user': props.parameters.user,
    'action': props.parameters.action,
    'department': props.parameters.department,
    'start_date': props.parameters.start_date,
    'end_date': props.parameters.end_date,
})

const getMovementType = (type_cod) => {
    [
        'Pagamento',
        'Serviço',
        'Venda de Material',
        'Uso de Material',
        'Entrada de Material'
    ][type_cod]
}

const openModal = (procedure) => {
    selected_procedure.value = procedure;
    modal.value = true;
}

const closeModal = () => {
    selected_procedure.value = null;
    modal.value = false;
}

</script>

<template>

    <Head :title="page.name" />
    <AppLayout :page="page">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ page.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 print:hidden">
                        <div>
                            <label for="filter-procedure-start_date" class="text-xs text-neutral-500">Data
                                (início)</label>
                            <input id="filter-procedure-start_date" type="date" class="simple-input"
                                v-model="form.start_date" />
                        </div>
                        <div>
                            <label for="filter-procedure-end_date" class="text-xs text-neutral-500">Data (fim)</label>
                            <input id="filter-procedure-end_date" type="date" class="simple-input"
                                v-model="form.end_date" />
                        </div>
                        <div>
                            <label for="filter-procedure-user" class="text-xs text-neutral-500">Usuário</label>
                            <select id="filter-procedure-user" class="simple-select" v-model="form.user">
                                <option :value="0">Todos</option>
                                <option v-for="user in users" :value="user.id" :key="user.id">
                                    {{ user.name }} {{ user.surname }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label for="filter-procedure-action" class="text-xs text-neutral-500">Ação</label>
                            <select id="filter-procedure-action" class="simple-select" v-model="form.action">
                                <option :value="0">Todos</option>
                                <option v-for="action in actions" :value="action.id" :key="action.id"
                                    class="capitalize">
                                    {{ action.past }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label for="filter-procedure-department"
                                class="text-xs text-neutral-500">Departamento</label>
                            <select id="filter-procedure-department" class="simple-select" v-model="form.department">
                                <option :value="0">Todos</option>
                                <option v-for="department in departments" :value="department.id" :key="department.id">
                                    {{ department.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table v-if="procedures.length" class="table-auto min-w-full divide-y divide-neutral-200 my-5">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-neutral-900">
                                        Usuário
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-neutral-900">
                                        Ação
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-neutral-900">
                                        Departamento</th>
                                    <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-neutral-900">
                                        Data
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-neutral-900">
                                        Ações
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-neutral-200">
                                <tr v-for="procedure in procedures" :key="procedure.id">
                                    <td class="px-6 py-4 text-sm text-neutral-900">
                                        {{ procedure.user.name }} {{ procedure.user.surname }}
                                    </td>
                                    <td class="px-6 py-4 text-sm ">
                                        {{ procedure.action.past }}
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <span v-if="procedure.department.type === 'payslip'">Registro de</span>
                                        <span v-if="procedure.department.type === 'warehouse'">Item de</span>
                                        {{ procedure.department.name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-neutral-500">
                                        {{ formatDate(procedure.created_at, 'reading_date_time') }}
                                    </td>
                                    <td class="hidden px-6 py-4 text-sm">
                                        <button class="text-green-500 hover:text-green-700"
                                            @click="openModal(procedure)">Ver
                                            registros</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <p v-else class="text-sm text-neutral-500 text-center pt-5">Nenhum procedimento registrado no
                            período especificado.</p>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
    <ExtraOptionsButton :mode="['rollup', 'print_page']" />
    <SeeRecords :modal="modal" :procedure="selected_procedure" @close="closeModal" />
</template>
