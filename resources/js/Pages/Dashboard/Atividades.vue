<script setup>
    import { ref, watch } from 'vue'
    import { useForm } from '@inertiajs/vue3';
    import { formatDate, } from '@/general.js';
    import { PaperClipIcon, EyeIcon } from '@heroicons/vue/24/outline';
    import AuditDetailModal from '@/Components/Dashboard/AuditDetailModal.vue';

    const props = defineProps({
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

    const getLogs = () => {
        let timer;
        clearTimeout(timer);
        timer = setTimeout(() => {
            form.get(route('dashboard.index'), {
                preserveScroll: true,
                // preserveState: true,
                replace: true,
            });
        }, 300);
    }

    watch(
        [() => form.user, () => form.department, () => form.action],
        (newFormValues) => {
            getLogs();
        },
        { deep: true }
    );


    const applyFilters = () => {
        form.get(route('dashboard.index'), {
            preserveScroll: true,
            preserveState: false,
            replace: true,
        });
    };


    const openModal = (procedure) => {
        selected_procedure.value = procedure;
        modal.value = true;
    }

    const closeModal = () => {
        selected_procedure.value = null;
        modal.value = false;
    }

    const clearFilters = () => {
        const today = new Date();
        const twoWeeksAgo = new Date();
        twoWeeksAgo.setDate(today.getDate() - 2);

        // Formatação em 'YYYY-MM-DD'
        const formatDate = (date) => date.toISOString().split('T')[0];

        form.user = 0;
        form.action = 0;
        form.department = 0;
        form.start_date = formatDate(twoWeeksAgo);
        form.end_date = formatDate(today);
        // getLogs();
    };

    const showDetails = (procedure) => {
        selected_procedure.value = procedure;
        modal.value = true;
    }

</script>

<template>
    <div class="w-full rounded-lg bg-neutral-200 text-neutral-700 uppercase text-xl font-bold text-center p-2 my-10">
        Registro de Atividades
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 print:hidden">
        <div>
            <label for="filter-procedure-start_date" class="text-xs text-neutral-500">Data
                (início)</label>
            <input id="filter-procedure-start_date" type="date" class="simple-input" v-model="form.start_date"
                @change="getLogs()" />
        </div>
        <div>
            <label for="filter-procedure-end_date" class="text-xs text-neutral-500">Data
                (fim)</label>
            <input id="filter-procedure-end_date" type="date" class="simple-input" v-model="form.end_date"
                @change="getLogs()" />
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
                <option v-for="action in actions" :value="action.id" :key="action.id" class="capitalize">
                    {{ action.past }}
                </option>
            </select>
        </div>
        <div>
            <label for="filter-procedure-department" class="text-xs text-neutral-500">Departamento</label>
            <select id="filter-procedure-department" class="simple-select" v-model="form.department">
                <option :value="0">Todos</option>
                <option v-for="department in departments" :value="department.id" :key="department.id">
                    {{ department.name }}
                </option>
            </select>
        </div>
    </div>

    <div class="col-span-full flex justify-end mt-2 gap-5">
        <button @click="clearFilters()" class=" text-red-500 transition text-xs">
            Resetar filtros
        </button>
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
                    <th scope="col" class="px-6 py-3 text-center text-sm font-medium text-neutral-900">
                        Detalhes
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-neutral-200">
                <tr v-for="procedure in procedures" :key="procedure.id">
                    <td class="px-6 py-4 text-sm text-neutral-900">
                        {{ procedure.user.name }} {{ procedure.user.surname }}
                    </td>
                    <td class="px-6 py-4 text-sm capitalize">
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
                    <td class="px-6 py-4 text-sm text-center">
                        <button v-i @click="showDetails(procedure)"
                            class="mx-auto text-teal-600 hover:text-teal-900 focus:outline-none" title="Ver Detalhes">
                            <EyeIcon class="w-5 h-5 text-green" />
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
        <p v-else class="text-sm text-neutral-500 text-center pt-5">Nenhum procedimento registrado
            no
            período especificado.</p>
    </div>
    <AuditDetailModal :show="modal" :procedure="selected_procedure" @close="closeModal" />
</template>