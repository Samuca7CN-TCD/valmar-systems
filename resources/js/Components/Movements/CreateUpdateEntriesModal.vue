<script setup>
    import CreateUpdateModal from '@/Components/CreateUpdateModal.vue';
    import { PlusIcon, PencilIcon, XMarkIcon, BanknotesIcon, EyeIcon, ArrowsRightLeftIcon } from '@heroicons/vue/24/outline';
    import PrimaryButton from '@/Components/PrimaryButton.vue';
    import SecondaryButton from '@/Components/SecondaryButton.vue';
    import { ref, computed, reactive } from 'vue';
    import { formatDate, toMoney } from '@/general.js';
    import { useForm } from '@inertiajs/vue3';
    import { useToast } from 'vue-toast-notification';
    import SelectSearchService from '@/Components/SelectSearchService.vue'
    import SelectSearchItem from '@/Components/SelectSearchItem.vue'
    import 'vue-toast-notification/dist/theme-sugar.css';

    const emit = defineEmits(['close', 'submit']);
    const props = defineProps({
        modal: Object,
        entry: {
            type: Object,
            default: null,
        },
        items: Array,
        employees_list: Array,
        services_list: Array,
    });

    const $toast = useToast();
    const dateRegex = /^\d{4}-\d{2}-\d{2}$/

    const canAddItems = computed(() => {
        const { motive, /*employee,*/ date } = props.entry;

        //if (!employee || !employee.length) return false;
        if (!motive || !motive.length) return false;
        const isDateValid = date && dateRegex.test(date);

        return isDateValid;
    });

    const enableSubmit = computed(() => {
        const { employee, motive, date, items_list } = props.entry;

        // Verifica se todos os campos obrigatórios estão preenchidos
        if (!motive || !motive.length) return false;
        if (!date || !dateRegex.test(date)) return false;
        if (!items_list || items_list.length < 1) return false;

        // Verifica se todos os itens da lista atendem aos critérios
        const items_okay = items_list.every(item =>
            item.movement_quantity > 0 &&
            item.employee_id > 0 //&&
            // item.motive && item.motive.length
        );

        return items_okay;
    });


    const see_disabled = computed(() => {
        return props.modal.mode === 'see'
    })


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
        props.entry.estimated_value = props.entry.items_list.reduce((total, item) => total + (item.price * item.movement_quantity), 0);
        props.entry.total_value = props.entry.estimated_value
    }

    const last_selected_employee = ref(props.employees_list ? props.employees_list[0]?.id : null);
    // const last_inputed_motive_mode = ref(true);
    // const last_inputed_motive = ref(null);
    const selectItem = (item) => {
        const new_item = {
            order_id: (props.entry.items_list[props.entry.items_list.length - 1]?.order_id ?? 0) + 1,
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
            // motive_mode: last_inputed_motive_mode.value,
            // motive: last_inputed_motive.value,
        }
        props.entry.items_list.push(new_item)
        searchTerm.value = "";
        showResults.value = false;
        updateEstimatedValue()
    }

    const changeMotiveMode = (item_id) => {
        const item = props.entry.items_list.find(el => el.id === item_id);
        item.motive_mode = !item.motive_mode
        item.motive = ""
    }

    const removeSelectedItem = async (index, event) => {
        event.preventDefault(); // Adicione isso para evitar o envio do formulário

        if (props.modal.mode === 'see') {
            if (!confirm("Você tem certeza que deseja deletar esse item? Esta ação não poderá ser desfeita!")) return;

            try {
                const response = await axios.post(route('entries.delete_item'), { entry_id: props.entry.id, item_id: index });
                props.entry.items_list = props.entry.items_list.filter((item) => item.id !== index);
                updateEstimatedValue();
                $toast.success('Item deletado da entrada com sucesso');
            } catch (error) {
                console.error('Erro ao deletar o item:', error.message);
                $toast.error(`Erro ao deletar o item: ${error.message}`);
            }
        } else {
            props.entry.items_list = props.entry.items_list.filter((item) => item.order_id !== index);
            updateEstimatedValue();
        }
    };

    const updateMotive = (newMotive) => {
        props.entry.motive = newMotive;
    };

    const updateItem = (newItem) => {
        selectItem(newItem)
    }

    const close = () => {
        last_selected_employee.value = props.employees_list ? props.employees_list[0]?.id : null
        emit('close')
    }

    const submit = () => {
        last_selected_employee.value = props.employees_list ? props.employees_list[0]?.id : null
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
                            <!--<div class="sm:col-span-3">
                                <label for="entry-employee"
                                    class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Funcionário</label>
                                <div class="mt-2">
                                    <input type="text" name="entry-employee" id="entry-employee" autocomplete="on"
                                        class="simple-input disabled:bg-gray-200" placeholder="Nome do funcionario"
                                        v-model="entry.employee" required>
                                    <p v-if="entry.errors.employee" class="text-red-500 text-sm">{{
        entry.errors.employee }}</p>
                                </div>
                            </div>-->

                            <div class="sm:col-span-6">
                                <label for="entry-date"
                                    class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Data
                                    do
                                    Entrada</label>
                                <div class="mt-2">
                                    <input type="date" name="entry-date" id="entry-date" autocomplete="on"
                                        class="simple-input disabled:bg-gray-200" autofocus="true"
                                        placeholder="Data do Entrada" :max="formatDate()" :disabled="see_disabled"
                                        v-model="entry.date" required>
                                    <p v-if="entry.errors.date" class="text-red-500 text-sm">{{
                                        entry.errors.date }}</p>
                                </div>
                            </div>

                            <div class="sm:col-span-6">
                                <label for="entry-motive"
                                    class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Motivo</label>
                                <div class="mt-2">
                                    <input v-if="see_disabled" type="text" name="entry-motive" id="entry-motive"
                                        autocomplete="on" class="simple-input disabled:bg-gray-200"
                                        :disabled="see_disabled" placeholder="Motivo do Entrada" v-model="entry.motive"
                                        required>

                                    <SelectSearchService v-else :options="services_list"
                                        @update:modelValue="updateMotive" />
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
                                        :disabled="see_disabled" v-model="entry.observations"></textarea>
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

                            <div v-if="!see_disabled" class="sm:col-span-4">
                                <label for="item-selecter"
                                    class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Selecione
                                    os itens de entrada</label>
                                <div class="mt-2">
                                    <SelectSearchItem :options="items" @update:modelValue="updateItem" />
                                </div>
                            </div>
                            <!--<div v-if="!see_disabled" class="sm:col-span-4">
                                <div class="relative">
                                    <label for="item-selecter"
                                        class="block text-sm font-medium leading-6 text-gray-900">Selecione os itens
                                        de
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
                                            <span class="inline">({{ item.quantity }})</span> {{ item.name }}
                                        </li>
                                    </ul>
                                </div>
                            </div>-->

                            <div class="sm:col-span-4">
                                <table v-if="entry.items_list.length"
                                    class="min-w-full text-left text-sm font-medium leading-6 text-gray-900">
                                    <thead class="border-b font-medium">
                                        <tr>
                                            <th scope="col" class="px-6 py-4 text-center">#</th>
                                            <!--<th scope="col" class="px-6 py-4 text-center">Retirado?</th>-->
                                            <th scope="col" class="px-6 py-4 text-center">Item</th>
                                            <th scope="col" class="px-6 py-4 text-center">Qtd. Estoque</th>
                                            <th scope="col" class="px-6 py-4 text-center">Qtd. Entrada</th>
                                            <th scope="col" class="px-6 py-4 text-center">Und. Medida</th>
                                            <th scope="col" class="px-6 py-4 text-center">Funcionário</th>
                                            <!--<th scope="col" class="px-6 py-4 text-center">Motivo</th>-->
                                            <th v-if="!see_disabled" scope="col" class="px-6 py-4 text-center">
                                                Deletar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item) in props.entry.items_list" :key="item.id"
                                            class="border-b transition duration-300 ease-in-out hover:bg-neutral-100 print:break-inside-avoid"
                                            :class="{ 'opaciti-50': item.deleted_at }">
                                            <!--<td class="whitespace-nowrap py-4 text-center font-medium"><input
                                                    type="checkbox"
                                                    class="rounded border-gray-500 text-green-600 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50"
                                                    checked></td>-->
                                            <td class="whitespace-nowrap py-4 text-center font-medium">{{ item.id }}
                                            </td>
                                            <td class="whitespace-nowrap py-4 text-center font-medium">{{ item.name }}
                                            </td>
                                            <td class="whitespace-nowrap py-4 text-center font-medium"> {{ item.quantity
                                                }}
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
                                            <td class="whitespace-nowrap py-4 text-center font-medium">
                                                <select class="simple-select disabled:bg-gray-200"
                                                    :disabled="modal.mode !== 'create'" v-model="item.employee_id"
                                                    @change="last_selected_employee = $event.target.value" required>
                                                    <option v-for="employee in employees_list" :key="employee.id"
                                                        :value="employee.id">{{
                                                            employee.name }}
                                                        {{ employee.surname }}</option>
                                                </select>
                                            </td>
                                            <!--<td
                                                class="whitespace-nowrap py-4 text-center font-medium flex gap-3 items-center">
                                                <select v-if="item.motive_mode"
                                                    class="simple-select disabled:bg-gray-200"
                                                    :disabled="modal.mode !== 'create'" v-model="item.motive"
                                                    @change="last_inputed_motive = $event.target.value" required>
                                                    <option value="" selected disabled>Selecione um serviço</option>
                                                    <option v-for="service in services_list" :key="service.id"
                                                        :value="service.motive + (service.entity_name)">{{
        service.motive }} ({{ service.entity_name
                                                        }})</option>
                                                </select>
                                                <input v-else type="text" class="simple-input disabled:bg-gray-200"
                                                    :disabled="modal.mode !== 'create'" v-model="item.motive"
                                                    placeholder="Descreva o motivo do movimento"
                                                    @input="last_inputed_motive = $event.target.value" required />
                                                <button @click="changeMotiveMode(item.id)"
                                                    title="Alterar modo de motivo">
                                                    <ArrowsRightLeftIcon class="w-5 h-4" />
                                                </button>
                                            </td>-->
                                            <td v-if="!see_disabled"
                                                class="whitespace-nowrap py-4 text-center font-mono text-2xl"><button
                                                    @click="removeSelectedItem((modal.mode === 'see' ? item.id : item.order_id), $event)"
                                                    type="button" :title="'Remover ' + item.name">&times;</button></td>
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