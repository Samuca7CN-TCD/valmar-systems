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
    sell: {
        type: Object,
        default: null,
    },
    items: Array,
});

const dateRegex = /^\d{4}-\d{2}-\d{2}$/
const canAddItems = computed(() => {
    const { client, date } = props.sell;

    if (!client || !client.length) return false;
    const isDateValid = date && dateRegex.test(date);

    return isDateValid;
});

const enableSubmit = computed(() => {
    const { client, date, estimated_value, total_value, entry_value, items_list } = props.sell;

    if (!client || !client.length) return false;
    if (!date || !dateRegex.test(date)) return false;

    const areValuesValid = estimated_value > 0 && total_value > 0 && entry_value <= total_value;
    if (!areValuesValid) return false;

    const items_okay = items_list.every(item => {
        return item.quantity > 0 && item.movement_quantity <= item.quantity;
    });

    return items_okay; // Early return based on item checks
});

const partialValue = computed(() => {
    return props.sell.total_value - props.sell.entry_value
})

const see_disabled = computed(() => {
    return props.modal.mode === 'see'
})


const searchTerm = ref("");
const showResults = ref(false);

const filteredItems = computed(() => {
    return props.items.filter((item) =>
        item.name.toLowerCase().includes(searchTerm.value.toLowerCase()) &&
        !props.sell.items_list.some(sellItem => sellItem.id === item.id)
    );
});

const handleInput = (type) => {
    if (type === 'in') showResults.value = true;
    if (type === 'out') showResults.value = false;
}

const updateEstimatedValue = () => {
    props.sell.estimated_value = props.sell.items_list.reduce((total, item) => total + (item.price * item.movement_quantity), 0);
    calc_final_values('estimated_value')
}

const selectItem = (item) => {
    props.sell.items_list.push({
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
    props.sell.items_list = props.sell.items_list.filter((item) => item.id !== index)
    updateEstimatedValue()
}

const handleInputValue = (e, input) => {
    props.sell[input] = e.target.value
    calc_final_values(input)
}

const decimal_format = (number, exp) => {
    const potencia = 10 ** exp
    return Math.floor(number * potencia) / potencia
}

const calc_final_values = (mode) => {
    let { estimated_value, total_value, discount_percent, discount } = props.sell;
    switch (mode) {
        case 'estimated_value':
        case 'discount_percent':
            total_value = estimated_value * (1 - (discount_percent / 100));
            discount = estimated_value - total_value;
            break;
        case 'total_value':
            discount = estimated_value - total_value;
            discount_percent = (discount / estimated_value) * 100;
            break;
        case 'discount':
            discount_percent = (discount / estimated_value) * 100;
            total_value = estimated_value - discount;
            break;
    }
    Object.assign(props.sell, { estimated_value, total_value, discount_percent, discount });
}

const close = () => {
    emit('close')
}

const submit = () => {
    emit('submit')
}
</script>
<template>
    <CreateUpdateModal :show="modal.show" :maxWidth="(canAddItems || see_disabled) ? '6xl' : '2xl'" @close="close">
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
                        <p class="mt-1 text-sm leading-6 text-gray-600">Informações sobre a venda como nome do cliente,
                            data
                            da venda e observações</p>

                        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            <div class="sm:col-span-3">
                                <label for="sell-client"
                                    class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Cliente</label>
                                <div class="mt-2">
                                    <input type="text" name="sell-client" id="sell-client" autocomplete="on"
                                        class="simple-input disabled:bg-gray-200" placeholder="Nome do cliente"
                                        v-model="sell.client" required>
                                    <p v-if="sell.errors.client" class="text-red-500 text-sm">{{
        sell.errors.client }}</p>
                                </div>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="sell-date"
                                    class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Data
                                    da
                                    Venda</label>
                                <div class="mt-2">
                                    <input type="date" name="sell-date" id="sell-date" autocomplete="on"
                                        class="simple-input disabled:bg-gray-200" autofocus="true"
                                        placeholder="Data da Venda" :max="formatDate()" v-model="sell.date" required>
                                    <p v-if="sell.errors.date" class="text-red-500 text-sm">{{
        sell.errors.date }}</p>
                                </div>
                            </div>

                            <div class="sm:col-span-6">
                                <label for="sell-observations"
                                    class="block text-sm font-medium leading-6 text-gray-900">Observações</label>
                                <div class="mt-2">
                                    <textarea type="text" observations="sell-observations" id="sell-observations"
                                        autocomplete="on" class="simple-input disabled:bg-gray-200"
                                        placeholder="Descreva a título mais detalhadamente ou insira informações adicionais"
                                        v-model="sell.observations"></textarea>
                                    <p v-if="sell.errors.observations" class="text-red-500 text-sm">{{
        sell.errors.observations }}</p>
                                </div>
                            </div>
                        </div>
                    </section>
                    <section v-if="canAddItems || see_disabled" class="py-4">
                        <h2 class="text-base font-semibold leading-7 text-gray-900">Lista de Itens</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">Adicione os itens da venda bem como descontos e
                            valor de pagamento de entrada.</p>

                        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-4">

                            <div class="sm:col-span-4">
                                <div class="relative">
                                    <label for="item-selecter"
                                        class="block text-sm font-medium leading-6 text-gray-900">Selecione os itens de
                                        venda</label>
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

                            <div class="md:col-span-2 lg:col-span-1">
                                <label for="sell-estimated-amount"
                                    class="block text-sm font-medium leading-6 text-gray-900">Valor
                                    estimado
                                </label>
                                <div class="relative mt-2 rounded-md shadow-sm">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-gray-500 sm:text-sm">R$</span>
                                    </div>
                                    <input id="sell-estimated-amount" type="number" step="0.01" min="0"
                                        autocomplete="off" readonly
                                        class="block w-full rounded-md border-0 py-1.5 pl-9 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-green-600 sm:text-sm sm:leading-6 disabled:bg-gray-200"
                                        placeholder="Valor total estimado"
                                        :disabled="!sell.items_list.length || see_disabled"
                                        v-model="sell.estimated_value" required>
                                    <p v-if="sell.errors.estimated_value" class="text-red-500 text-sm">{{
        sell.errors.estimated_value }}</p>
                                </div>
                            </div>

                            <div class="md:col-span-2 lg:col-span-1">
                                <label for="sell-total-amount"
                                    class="block text-sm font-medium leading-6 text-gray-900">Valor final
                                </label>
                                <div class="relative mt-2 rounded-md shadow-sm">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-gray-500 sm:text-sm">R$</span>
                                    </div>
                                    <input id="sell-total-amount" type="number" step="0.01" min="0" autocomplete="off"
                                        class="block w-full rounded-md border-0 py-1.5 pl-9 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-green-600 sm:text-sm sm:leading-6 disabled:bg-gray-200"
                                        placeholder="Valor total estimado"
                                        :disabled="!sell.items_list.length || see_disabled"
                                        :value="decimal_format(sell.total_value, 2)"
                                        @input="e => handleInputValue(e, 'total_value')" required>
                                    <p v-if="sell.errors.total_value" class="text-red-500 text-sm">{{
        sell.errors.total_value }}</p>
                                </div>
                            </div>

                            <div class="md:col-span-2 lg:col-span-1">
                                <label for="sell-discount-percent"
                                    class="block text-sm font-medium leading-6 text-gray-900">Desconto (%)
                                </label>
                                <div class="relative mt-2 rounded-md shadow-sm">

                                    <input id="sell-discount-percent" type="number" step="0.01" min="0"
                                        autocomplete="off"
                                        class="block w-full rounded-md border-0 py-1.5 pr-9 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-green-600 sm:text-sm sm:leading-6 disabled:bg-gray-200"
                                        placeholder="Valor total estimado"
                                        :disabled="!sell.items_list.length || see_disabled"
                                        :value="decimal_format(sell.discount_percent, 3)"
                                        @input="e => handleInputValue(e, 'discount_percent')" required>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                        <span class="text-gray-500 sm:text-sm">%</span>
                                    </div>
                                    <p v-if="sell.errors.discount_percent" class="text-red-500 text-sm">{{
        sell.errors.discount_percent }}</p>
                                </div>
                            </div>

                            <div class="md:col-span-2 lg:col-span-1">
                                <label for="sell-discount"
                                    class="block text-sm font-medium leading-6 text-gray-900">Desconto (R$)
                                </label>
                                <div class="relative mt-2 rounded-md shadow-sm">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-gray-500 sm:text-sm">R$</span>
                                    </div>
                                    <input id="sell-discount" type="number" step="0.01" min="0" autocomplete="off"
                                        class="block w-full rounded-md border-0 py-1.5 pl-9 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-green-600 sm:text-sm sm:leading-6 disabled:bg-gray-200"
                                        placeholder="Valor total estimado"
                                        :disabled="!sell.items_list.length || see_disabled"
                                        :value="decimal_format(sell.discount, 2)"
                                        @input="e => handleInputValue(e, 'discount')" required>
                                    <p v-if="sell.errors.discount" class="text-red-500 text-sm">{{
        sell.errors.discount }}</p>
                                </div>
                            </div>

                            <div class="sm:col-span-4">
                                <table v-if="props.sell.items_list.length"
                                    class="min-w-full text-left text-sm font-medium leading-6 text-gray-900">
                                    <thead class="border-b font-medium dark:border-neutral-500">
                                        <tr>
                                            <th scope="col" class="px-6 py-4 text-center">#</th>
                                            <!--<th scope="col" class="px-6 py-4 text-center">Retirado?</th>-->
                                            <th scope="col" class="px-6 py-4 text-center">Item</th>
                                            <th scope="col" class="px-6 py-4 text-center">Qtd. Estoque</th>
                                            <th scope="col" class="px-6 py-4 text-center">Qtd. Venda</th>
                                            <th scope="col" class="px-6 py-4 text-center">Und. Medida</th>
                                            <th scope="col" class="px-6 py-4 text-center">Preço unitário</th>
                                            <th scope="col" class="px-6 py-4 text-center">Preço montante</th>
                                            <th v-if="modal.mode !== 'see'" scope="col" class="px-6 py-4 text-center">
                                                Deletar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item) in props.sell.items_list" :key="item.id"
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
                                                    @input="(e) => { item.movement_quantity = e.target.value; item.amount = item.price * e.target.value; updateEstimatedValue() }"
                                                    class="simple-input disabled:bg-gray-200 w-2/3"
                                                    :disabled="modal.mode !== 'create'">
                                            </td>
                                            <td class="whitespace-nowrap py-4 text-center font-medium">{{
        item.measurement_unit }}</td>
                                            <td class="whitespace-nowrap py-4 text-center font-medium">{{
        toMoney(item.price) }}</td>
                                            <td class="whitespace-nowrap py-4 text-center font-medium">{{
        toMoney(item.amount ? item.amount : 10) }}</td>
                                            <!-- Substitua 1 pela quantidade real -->
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
                    <section v-if="canAddItems && sell.items_list.length && modal.mode === 'create'"
                        class="w-full py-4">
                        <h2 class="text-base font-semibold leading-7 text-gray-900">Pagamento de entrada</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">Após a conclusão da compra, um registro de
                            pagamento
                            será criado na página de PAGAMENTOS.
                        </p>
                        <p class="mt-1 text-sm leading-6 text-gray-600">Se algum pagamento de entrada (ou pagamento
                            total)
                            for realizado, registre aqui.</p>

                        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-4">

                            <div class="md:col-span-2 lg:col-span-1 text-gray-700">Valor estimado: <span
                                    class="font-extrabold text-green-500">{{
        toMoney(sell.estimated_value) }}</span>
                            </div>
                            <div class="md:col-span-2 lg:col-span-1 text-gray-700">Valor final: <span
                                    class="font-extrabold text-green-500">{{
        toMoney(sell.total_value)
    }}</span></div>
                            <div class="md:col-span-2 lg:col-span-1 text-gray-700">Desconto (%): <span
                                    class="font-extrabold text-green-500">{{
            decimal_format(sell.discount_percent,
                3) }}%</span></div>
                            <div class="md:col-span-2 lg:col-span-1 text-gray-700">Desconto (R$): <span
                                    class="font-extrabold text-green-500">{{
        toMoney(sell.discount)
    }}</span></div>

                            <div class="sm:col-span-2">
                                <label for="sell-entry-value"
                                    class="block text-sm font-medium leading-6 text-gray-900">Pagamento de entrada
                                </label>
                                <div class="relative mt-2 rounded-md shadow-sm">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-gray-500 sm:text-sm">R$</span>
                                    </div>
                                    <input id="sell-entry-value" type="number" step="0.01" min="0" autocomplete="off"
                                        class="block w-full rounded-md border-0 py-1.5 pl-9 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-green-600 sm:text-sm sm:leading-6 disabled:bg-gray-200"
                                        placeholder="Valor de pagamneto de entrada"
                                        :disabled="!sell.items_list.length || see_disabled" v-model="sell.entry_value"
                                        required>
                                    <p v-if="sell.errors.entry_value" class="text-red-500 text-sm">{{
                                        sell.errors.entry_value }}</p>
                                </div>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="sell-partial-value"
                                    class="block text-sm font-medium leading-6 text-gray-900">Valor
                                    restante
                                </label>
                                <div class="relative mt-2 rounded-md shadow-sm">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-gray-500 sm:text-sm">R$</span>
                                    </div>
                                    <input id="sell-partial-value" type="number" step="0.01" min="0" autocomplete="off"
                                        class="block w-full rounded-md border-0 py-1.5 pl-9 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-green-600 sm:text-sm sm:leading-6 disabled:bg-gray-200"
                                        placeholder="Valor restante para pagamento posterior"
                                        :disabled="!sell.items_list.length || see_disabled"
                                        :value="decimal_format(partialValue, 2)" readonly required>
                                    <p v-if="sell.errors.partial_value" class="text-red-500 text-sm">{{
                                        sell.errors.partial_value }}</p>
                                </div>
                            </div>

                        </div>
                    </section>
                </div>
            </form>
        </template>

        <template #footer>
            <SecondaryButton v-if="modal.mode !== 'see'" :class="{ 'disabled': sell.processing }" @click="close()">
                Cancelar
            </SecondaryButton>
            <PrimaryButton :class="{ 'disabled': (sell.processing || !enableSubmit) }"
                :disabled="(sell.processing || !enableSubmit)" @click="submit()">{{
                modal.primary_button_txt
                }}</PrimaryButton>
        </template>
    </CreateUpdateModal>
</template>