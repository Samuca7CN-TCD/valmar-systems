<script setup>
import CreateUpdateModal from '@/Components/CreateUpdateModal.vue';
import { PlusIcon, PencilIcon, XMarkIcon, BanknotesIcon, EyeIcon } from '@heroicons/vue/24/outline';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ref, computed, reactive } from 'vue';
import { formatDate, toMoney } from '@/general.js';
import { useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toast-notification';
import 'vue-toast-notification/dist/theme-sugar.css';

const emit = defineEmits(['close', 'submit']);
const props = defineProps({
    modal: Object,
    use: {
        type: Object,
        default: null,
    },
    items: Array,
    employees_list: Array,
});

const $toast = useToast();

const choose_employee = ref(true)

const dateRegex = /^\d{4}-\d{2}-\d{2}$/
const canAddItems = computed(() => {
    const { motive, employee_id, employee, date } = props.use;

    /*if (choose_employee.value) {
        if (!employee_id) return false;
    } else {
        if (!employee) return false;
    }*/

    if (!motive || !motive.length) return false;
    const isDateValid = date && dateRegex.test(date);

    return isDateValid;
});

const enableSubmit = computed(() => {
    const { items_list } = props.use;
    if (!canAddItems) return false;
    if (!items_list.length) return false;
    const items_okay = items_list.every(item => {
        return item.movement_quantity > 0 && item.movement_quantity <= item.quantity;
    });
    return items_okay; // Early return based on item checks
});

const disableAllInputs = computed(() => {
    return props.modal.mode === 'see'
})

const see_disabled = computed(() => {
    return props.modal.mode === 'see'
})


const updateEmployee = (employee_data) => {
    if (choose_employee.value) {
        const selected_employee = props.employees_list.find(employee => employee.id === parseInt(employee_data))
        if (!selected_employee) {
            $toast.error('Funcionário não encontrado');
            return;
        }
        props.use.employee_id = employee_data
        props.use.employee = selected_employee.name + ' ' + selected_employee.surname
    } else {
        props.use.employee_id = null
        props.use.employee_name = employee_data
    }
}

const clearEmployee = () => {
    props.use.employee_id = null;
    props.use.employee = "";
};


const searchTerm = ref("");
const showResults = ref(false);

const filteredItems = computed(() => {
    return props.items.filter((item) =>
        item.name.toLowerCase().includes(searchTerm.value.toLowerCase())
    );
});

const handleInput = (type) => {
    if (type === 'in') showResults.value = true;
    if (type === 'out') showResults.value = false;
}

const updateEstimatedValue = () => {
    props.use.estimated_value = props.use.items_list.reduce((total, item) => total + (item.price * item.movement_quantity), 0);
    props.use.total_value = props.use.estimated_value
}

const last_selected_employee = ref(props.employees_list ? props.employees_list[0]?.id : null);
const selectItem = (item) => {
    props.use.items_list.push({
        id: item.id,
        name: item.name,
        quantity: item.quantity,
        max_quantity: item.max_quantity,
        min_quantity: item.min_quantity,
        movement_quantity: 1,
        measurement_unit: item.measurement_unit.abbreviation,
        price: item.price,
        amount: item.price,
        employee_id: last_selected_employee.value,
    })

    searchTerm.value = "";
    showResults.value = false;
    updateEstimatedValue()
}

const removeSelectedItem = (index) => {
    props.use.items_list = props.use.items_list.filter((item) => item.id !== index)
    updateEstimatedValue()
}

const close = () => {
    emit('close')
}

const submit = () => {
    emit('submit')
}
</script>
<template>
    <CreateUpdateModal :show="modal.show" :maxWidth="(canAddItems || see_disabled) ? '3xl' : '2xl'" @close="close">
        <template #icon>
            <div class="mx-auto shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-400 sm:mx-0 sm:h-10 sm:w-10"
                :class="{ 'bg-yellow-400': modal.mode === 'update', 'bg-green-400': modal.mode === 'pay', 'bg-gray-400': modal.mode === 'see' }">
                <PlusIcon v-if="modal.mode === 'create'" class="w-5 h-5 text-white" />
                <PencilIcon v-if="modal.mode === 'update'" class="w-5 h-5 text-white" />
                <BanknotesIcon v-if="modal.mode === 'pay'" class="w-5 h-5 text-white" />
                <EyeIcon v-if="modal.mode === 'see'" class="w-5 h-5 text-white" />
            </div>
        </template>

        <template #title>
            {{ modal.title }}
        </template>

        <template #content>
            <form @submit.prevent="submit">
                <div class="pb-12 space-y-12 divide-y-2">

                    <!-- SEÇÃO DE INFORMAÇÕES DE PAGAMENTOS -->
                    <section class="py-4">
                        <h2 class="text-base font-semibold leading-7 text-gray-900">Informações Básicas</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">Informações sobre a uso como nome do
                            funcionário, data
                            da uso e observações</p>

                        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            <!--
                            <div class="sm:col-span-3">
                                <label for="use-employee"
                                    class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Responsável</label>
                                <div v-if="choose_employee" class="mt-2">
                                    <select name="use-employee" id="use-employee" autocomplete="on"
                                        class="simple-input disabled:bg-gray-200" placeholder="Nome do funcionario"
                                        :value="use.employee_id" @change="updateEmployee($event.target.value)" required
                                        :disabled="disableAllInputs">
                                        <option :value="null" disabled selected>Escolha um funcionário</option>
                                        <option v-for="employee in employees_list" :key="employee.id"
                                            :value="employee.id">{{ employee.name }} {{ employee.surname }}</option>
                                    </select>
                                    <p v-if="use.errors.employee" class="text-red-500 text-sm">{{ use.errors.employee }}
                                    </p>
                                </div>
                                <div v-else class="mt-2">
                                    <input type="text" name="use-entity" id="use-entity" autocomplete="on"
                                        class="simple-input disabled:bg-gray-200" placeholder="Nome do responsável"
                                        :value="use.employee" @input="updateEmployee($event.target.value)"
                                        :disabled="disableAllInputs" required>
                                    <p v-if="use.errors.employee" class="text-red-500 text-sm">{{ use.errors.employee }}
                                    </p>
                                </div>
                                <span class="flex flex-row align-middle items-center space-x-2 mt-2">
                                    <input id="switch_entity_type" type="checkbox"
                                        class="rounded border-gray-500 text-green-600 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50"
                                        v-model="choose_employee" @change="clearEmployee"
                                        :disabled="disableAllInputs" />
                                    <label for="switch_entity_type" class="text-sm text-neutral-500 select-none">
                                        Funcionário?</label>
                                </span>
                            </div>
                        -->

                            <div class="sm:col-span-6">
                                <label for="use-date"
                                    class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Data
                                    do
                                    Uso</label>
                                <div class="mt-2">
                                    <input type="date" name="use-date" id="use-date" autocomplete="on"
                                        class="simple-input disabled:bg-gray-200" autofocus="true"
                                        placeholder="Data do Uso" :max="formatDate()" v-model="use.date"
                                        :disabled="disableAllInputs" required>
                                    <p v-if="use.errors.date" class="text-red-500 text-sm">{{
        use.errors.date }}</p>
                                </div>
                            </div>

                            <div class="sm:col-span-6">
                                <label for="use-motive"
                                    class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Motivo</label>
                                <div class="mt-2">
                                    <input type="text" name="use-motive" id="use-motive" autocomplete="on"
                                        class="simple-input disabled:bg-gray-200" placeholder="Motivo do Uso"
                                        v-model="use.motive" :disabled="disableAllInputs" required>
                                    <p v-if="use.errors.motive" class="text-red-500 text-sm">{{
        use.errors.motive }}</p>
                                </div>
                            </div>

                            <div class="sm:col-span-6">
                                <label for="use-observations"
                                    class="block text-sm font-medium leading-6 text-gray-900">Observações</label>
                                <div class="mt-2">
                                    <textarea type="text" observations="use-observations" id="use-observations"
                                        autocomplete="on" class="simple-input disabled:bg-gray-200"
                                        placeholder="Descreva a título mais detalhadamente ou insira informações adicionais"
                                        v-model="use.observations" :disabled="disableAllInputs"></textarea>
                                    <p v-if="use.errors.observations" class="text-red-500 text-sm">{{
        use.errors.observations }}</p>
                                </div>
                            </div>
                        </div>
                    </section>
                    <section v-if="canAddItems || see_disabled" class="py-4">
                        <h2 class="text-base font-semibold leading-7 text-gray-900">Lista de Itens</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">Adicione os itens do uso.</p>

                        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-4">

                            <div class="sm:col-span-4">
                                <div class="relative">
                                    <label for="item-selecter"
                                        class="block text-sm font-medium leading-6 text-gray-900">Selecione os itens de
                                        uso</label>
                                    <input id="item-selecter" v-model="searchTerm" @focus="handleInput('in')"
                                        @blur="handleInput('out')"
                                        class="simple-input disabled:bg-gray-200 px-4 py-2 w-full"
                                        placeholder="Digite para pesquisar" :disabled="disableAllInputs" />

                                    <ul v-if="showResults"
                                        class="absolute z-10 w-full mx-auto sm:w-96 bg-white border rounded mt-2 h-96 overflow-y-scroll">
                                        <li v-for="(item, index) in filteredItems" :key="index"
                                            class="py-2 px-4 cursor-pointer hover:bg-gray-200"
                                            @mousedown="selectItem(item)">
                                            <span class="inline" :class="{
        'text-blue-500': item.quantity != 0 && item.quantity >= item.max_quantity,
        'text-green-500': item.quantity < item.max_quantity && item.quantity >= item.min_quantity,
        'text-yellow-500': item.quantity < item.min_quantity && item.quantity > 0,
        'text-red-500': item.quantity == 0
    }">({{ item.quantity }})</span> {{ item.name }}
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="sm:col-span-4">
                                <table v-if="props.use.items_list.length"
                                    class="min-w-full text-left text-sm font-medium leading-6 text-gray-900">
                                    <thead class="border-b font-medium">
                                        <tr>
                                            <th scope="col" class="px-6 py-4 text-center">#</th>
                                            <!--<th scope="col" class="px-6 py-4 text-center">Retirado?</th>-->
                                            <th scope="col" class="px-6 py-4 text-center">Item</th>
                                            <th scope="col" class="px-6 py-4 text-center">Qtd. Estoque</th>
                                            <th scope="col" class="px-6 py-4 text-center">Qtd. Uso</th>
                                            <th scope="col" class="px-6 py-4 text-center">Und. Medida</th>
                                            <th scope="col" class="px-6 py-4 text-center">Funcionário</th>
                                            <th v-if="modal.mode !== 'see'" scope="col" class="px-6 py-4 text-center">
                                                Deletar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item) in props.use.items_list" :key="item.id"
                                            class="border-b transition duration-300 ease-in-out hover:bg-neutral-100 print:break-inside-avoid"
                                            :class="{ 'bg-yellow-100': item.movement_quantity == item.quantity, 'bg-red-100': item.movement_quantity > item.quantity }">
                                            <!--<td class="whitespace-nowrap py-4 text-center font-medium"><input
                                                    type="checkbox"
                                                    class="rounded border-gray-500 text-green-600 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50"
                                                    checked></td>-->
                                            <td class="whitespace-nowrap py-4 text-center font-medium">{{ item.id }}
                                            </td>
                                            <td class="whitespace-nowrap py-4 text-center font-medium">{{ item.name }}
                                            </td>
                                            <td class="whitespace-nowrap py-4 text-center font-medium" :class="{
        'text-blue-500': item.quantity != 0 && item.quantity >= item.max_quantity,
        'text-green-500': item.quantity < item.max_quantity && item.quantity >= item.min_quantity,
        'text-yellow-500': item.quantity < item.min_quantity && item.quantity > 0,
        'text-red-500': item.quantity == 0,
    }"> {{ item.quantity }}
                                            </td>
                                            <td class="whitespace-nowrap py-4 text-center font-medium flex
                                                justify-center">
                                                <input type="number" min="0.01" step="0.01"
                                                    :value="item.movement_quantity"
                                                    @input="(e) => { item.movement_quantity = parseFloat(e.target.value); item.amount = item.price * e.target.value; updateEstimatedValue() }"
                                                    class="simple-input disabled:bg-gray-200 w-2/3"
                                                    :disabled="disableAllInputs">
                                            </td>
                                            <td class="whitespace-nowrap py-4 text-center font-medium">{{
        item.measurement_unit }}</td>
                                            <td class="whitespace-nowrap py-4 text-center font-medium flex
                                                justify-center">
                                                <select name="use-employee" class="simple-select disabled:bg-gray-200"
                                                    placeholder="Nome do funcionario" v-model="item.employee_id"
                                                    @change="last_selected_employee = $event.target.value" required
                                                    :disabled="!canAddItems || disableAllInputs">
                                                    <option :value="null" disabled selected>Escolha um funcionário
                                                    </option>
                                                    <option v-for="employee in employees_list" :key="employee.id"
                                                        :value="employee.id">{{ employee.name }} {{ employee.surname }}
                                                    </option>
                                                </select>
                                            </td>

                                            <td v-if="modal.mode !== 'see'"
                                                class="whitespace-nowrap py-4 text-center font-mono text-2xl"><button
                                                    @click="removeSelectedItem(item.id)"
                                                    :title="'Remover ' + item.name">&times;</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <p v-else
                                    class="p-5 bg-gray-200 rounded-md block text-sm font-medium leading-6 text-gray-900">
                                    Nenhum item selecionado até o momento!</p>
                            </div>
                        </div>
                    </section>
                </div>
            </form>
        </template>

        <template #footer>
            <SecondaryButton v-if="modal.mode !== 'see'" :class="{ 'disabled': use.processing }" @click="close()">
                Cancelar
            </SecondaryButton>
            <PrimaryButton :class="{ 'disabled': (use.processing || !enableSubmit) }"
                :disabled="use.processing || !enableSubmit"
                @click="use.processing || !enableSubmit ? $event.preventDefault() : submit()">
                {{ modal.primary_button_txt }}
            </PrimaryButton>
        </template>

    </CreateUpdateModal>
</template>