<script setup>
import CreateUpdateModal from '@/Components/CreateUpdateModal.vue';
import { PlusIcon, PencilIcon, XMarkIcon, BanknotesIcon, EyeIcon } from '@heroicons/vue/24/outline';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ref, computed, reactive } from 'vue';
import { formatDate, toMoney } from '@/general.js';
import { useForm } from '@inertiajs/vue3';

const emit = defineEmits(['close', 'submit']);
const props = defineProps({
    modal: Object,
    entry: {
        type: Object,
        default: null,
    },
    items: Array,
});

const dateRegex = /^\d{4}-\d{2}-\d{2}$/
const canAddItems = computed(() => {
    const { motive, employee, date } = props.entry;

    if (!employee || !employee.length) return false;
    if (!motive || !motive.length) return false;
    const isDateValid = date && dateRegex.test(date);

    return isDateValid;
});

const enableSubmit = computed(() => {
    const { employee, motive, date, items_list } = props.entry;
    if (!employee || !employee.length) return false;
    if (!motive || !motive.length) return false;
    if (!date || !dateRegex.test(date)) return false;
    if (items_list.length < 1) return false;
    const items_okay = items_list.every(item => {
        return item.quantity > 0 && item.movement_quantity > 0 && item.movement_quantity <= item.quantity;
    });
    return items_okay; // Early return based on item checks
});

const see_disabled = computed(() => {
    return props.modal.mode === 'see'
})


const searchTerm = ref("");
const showResults = ref(false);

const filteredItems = computed(() => {
    return props.items.filter((item) =>
        item.name.toLowerCase().includes(searchTerm.value.toLowerCase()) &&
        !props.entry.items_list.some(entryItem => entryItem.id === item.id)
    );
});

const handleInput = (type) => {
    if (type === 'in') showResults.value = true;
    if (type === 'out') showResults.value = false;
}

const updateEstimatedValue = () => {
    props.entry.estimated_value = props.entry.items_list.reduce((total, item) => total + (item.price * item.movement_quantity), 0);
    props.entry.total_value = props.entry.estimated_value
}

const selectItem = (item) => {
    props.entry.items_list.push({
        id: item.id,
        name: item.name,
        quantity: item.quantity,
        max_quantity: item.max_quantity,
        min_quantity: item.min_quantity,
        movement_quantity: 1,
        measurement_unit: item.measurement_unit.abbreviation,
        price: item.price,
        amount: item.price,
    })

    searchTerm.value = "";
    showResults.value = false;
    updateEstimatedValue()
}

const removeSelectedItem = (index) => {
    props.entry.items_list = props.entry.items_list.filter((item) => item.id !== index)
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
                        <p class="mt-1 text-sm leading-6 text-gray-600">Informações sobre a entrada como nome do
                            funcionário, data
                            da entrada e observações</p>

                        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            <div class="sm:col-span-3">
                                <label for="entry-employee"
                                    class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Funcionário</label>
                                <div class="mt-2">
                                    <input type="text" name="entry-employee" id="entry-employee" autocomplete="on"
                                        class="simple-input disabled:bg-gray-200" placeholder="Nome do funcionario"
                                        v-model="entry.employee" required>
                                    <p v-if="entry.errors.employee" class="text-red-500 text-sm">{{
        entry.errors.employee }}</p>
                                </div>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="entry-date"
                                    class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Data
                                    do
                                    Entrada</label>
                                <div class="mt-2">
                                    <input type="date" name="entry-date" id="entry-date" autocomplete="on"
                                        class="simple-input disabled:bg-gray-200" autofocus="true"
                                        placeholder="Data do Entrada" :max="formatDate()" v-model="entry.date" required>
                                    <p v-if="entry.errors.date" class="text-red-500 text-sm">{{
        entry.errors.date }}</p>
                                </div>
                            </div>

                            <div class="sm:col-span-6">
                                <label for="entry-motive"
                                    class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Motivo</label>
                                <div class="mt-2">
                                    <input type="text" name="entry-motive" id="entry-motive" autocomplete="on"
                                        class="simple-input disabled:bg-gray-200" placeholder="Motivo do Entrada"
                                        v-model="entry.motive" required>
                                    <p v-if="entry.errors.motive" class="text-red-500 text-sm">{{
        entry.errors.motive }}</p>
                                </div>
                            </div>

                            <div class="sm:col-span-6">
                                <label for="entry-observations"
                                    class="block text-sm font-medium leading-6 text-gray-900">Observações</label>
                                <div class="mt-2">
                                    <textarea type="text" observations="entry-observations" id="entry-observations"
                                        autocomplete="on" class="simple-input disabled:bg-gray-200"
                                        placeholder="Descreva a título mais detalhadamente ou insira informações adicionais"
                                        v-model="entry.observations"></textarea>
                                    <p v-if="entry.errors.observations" class="text-red-500 text-sm">{{
        entry.errors.observations }}</p>
                                </div>
                            </div>
                        </div>
                    </section>
                    <section v-if="canAddItems || see_disabled" class="py-4">
                        <h2 class="text-base font-semibold leading-7 text-gray-900">Lista de Itens</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">Adicione os itens do entrada.</p>

                        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-4">

                            <div class="sm:col-span-4">
                                <div class="relative">
                                    <label for="item-selecter"
                                        class="block text-sm font-medium leading-6 text-gray-900">Selecione os itens de
                                        entrada</label>
                                    <input id="item-selecter" v-model="searchTerm" @focus="handleInput('in')"
                                        @blur="handleInput('out')"
                                        class="simple-input disabled:bg-gray-200 px-4 py-2 w-full"
                                        placeholder="Digite para pesquisar" :disabled="see_disabled" />

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
                                <table v-if="props.entry.items_list.length"
                                    class="min-w-full text-left block text-sm font-medium leading-6 text-gray-900">
                                    <thead class="border-b font-medium dark:border-neutral-500">
                                        <tr>
                                            <th scope="col" class="px-6 py-4 text-center">#</th>
                                            <!--<th scope="col" class="px-6 py-4 text-center">Retirado?</th>-->
                                            <th scope="col" class="px-6 py-4 text-center">Item</th>
                                            <th scope="col" class="px-6 py-4 text-center">Qtd. Estoque</th>
                                            <th scope="col" class="px-6 py-4 text-center">Qtd. Entrada</th>
                                            <th scope="col" class="px-6 py-4 text-center">Und. Medida</th>
                                            <th v-if="modal.mode !== 'see'" scope="col" class="px-6 py-4 text-center">
                                                Deletar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item) in props.entry.items_list" :key="item.id"
                                            class="border-b transition duration-300 ease-in-out hover:bg-neutral-100 dark:border-neutral-500 dark:hover:bg-neutral-600"
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
                                                    :disabled="modal.mode !== 'create'">
                                            </td>
                                            <td class="whitespace-nowrap py-4 text-center font-medium">{{
        item.measurement_unit }}</td>

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
            <SecondaryButton v-if="modal.mode !== 'see'" :class="{ 'disabled': entry.processing }" @click="close()">
                Cancelar
            </SecondaryButton>
            <PrimaryButton :class="{ 'disabled': (entry.processing || !enableSubmit) }"
                :disabled="(entry.processing || !enableSubmit)" @click="submit()">{{
        modal.primary_button_txt
    }}</PrimaryButton>
        </template>
    </CreateUpdateModal>
</template>