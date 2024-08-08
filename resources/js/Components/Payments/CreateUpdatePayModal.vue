<script setup>
import CreateUpdateModal from '@/Components/CreateUpdateModal.vue';
import { PlusIcon, PencilIcon, XMarkIcon, BanknotesIcon } from '@heroicons/vue/24/outline';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ref, computed } from 'vue';
import { formatDate, toMoney } from '@/general.js';
import { useForm } from '@inertiajs/vue3';

const emit = defineEmits(['close', 'submit']);
const props = defineProps({
    modal: Object,
    payment: {
        type: Object,
        default: null,
    },
});

const calc_record = computed(() => props.payment.records_list.data.length + 1);

const remaining_amount = computed(() => {
    return enable_records_inputs && (props.payment.total_value - props.payment.records_list.data.reduce((sum, record) => sum + record.amount, 0));
})

const invalid_record_date = computed(() => {
    const today = new Date();
    return props.payment.records_list.data.some(data => new Date(data.date) > today);
});

const invalid_record_amount = computed(() => {
    return props.payment.records_list.data.some(data => data.amount <= 0);
});

const records_list = computed(() => {
    return props.payment.records_list.data.sort((a, b) => { return new Date(a.date) - new Date(b.date) })
})

const disable_payments_inputs = computed(() => {
    return ['pay', 'see'].includes(props.modal.mode)
});

const enable_records_inputs = computed(() => {
    return props.modal.mode !== 'create' || props.payment.records_list.enable_records;
});

const can_add_record = computed(() =>
    props.payment.debt.length && props.payment.debtor.length && props.payment.total_value > 0
);


const record = useForm({
    id: calc_record,
    amount: 0,
    past: false,
    register_date: formatDate(),
    filepath: null,
})

const insert_file = ref(false);

const toggle_enable_records = () => {
    if (props.payment.records_list.enable_records && props.payment.records_list.data.length) {
        if (!confirm('Tem certeza que deseja desmarcar? Todos os registros serão excluídos!')) {
            return;
        } else {
            props.payment.records_list.data.length = [];
            record.reset();
        }
    }
    props.payment.records_list.enable_records = !props.payment.records_list.enable_records;
};

const addRecord = () => {
    if (!record.amount || record.amount > remaining_amount.value) {
        record.errors.amount = `O valor do registro deve ser maior que R$ 0,00 e menor que ${toMoney(remaining_amount.value)}`;
        return;
    }
    record.errors.amount = "";

    if (!record.register_date || new Date(record.register_date) > new Date()) {
        record.errors.date = `A data do registro deve ser menor ou igual a ${formatDate()}`;
        return;
    }
    record.errors.date = "";

    if (!insert_file.value) record.filepath = null;

    props.payment.records_list.data.push({ ...record });
    insert_file.value = false;
    record.reset();
};

const removeRecord = (record_id, record = null) => {
    if (props.modal.mode === 'create') {
        props.payment.records_list.data = props.payment.records_list.data.filter((record) => record.id !== record_id);
        return;
    } else {
        if (confirm("Você tem certeza que deseja excluir esse registro? (Essa ação não pode ser desfeita)")) {
            // Certifique-se de usar record.delete como uma função assíncrona
            record.delete(route('records.destroy', record_id), {
                preserveScroll: true,
                onSuccess: () => {
                    props.payment.records_list.data = props.payment.records_list.data.filter((record) => record.id !== record_id);
                }
            });
            // props.payment.records_list.data.find((record) => record.id === record_id).deleted_at = formatDate();
        }
    }
};

const close = () => {
    emit('close')
}

const submit = () => {
    emit('submit')
}
</script>
<template>
    <CreateUpdateModal :show="modal.show" @close="close">
        <template #icon>
            <div class="mx-auto shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-400 sm:mx-0 sm:h-10 sm:w-10"
                :class="{ 'bg-yellow-400': modal.mode === 'update', 'bg-green-400': modal.mode === 'pay' }">
                <PlusIcon v-if="modal.mode === 'create'" class="w-5 h-5 text-white" />
                <PencilIcon v-if="modal.mode === 'update'" class="w-5 h-5 text-white" />
                <BanknotesIcon v-if="modal.mode === 'pay'" class="w-5 h-5 text-white" />
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
                        <p class="mt-1 text-sm leading-6 text-gray-600">Informações sobre o redistro de pagamento como
                            devedor, dívida e valor total da dívida</p>

                        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

                            <div class="sm:col-span-6">
                                <label for="payment-debt"
                                    class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Dívida</label>
                                <div class="mt-2">
                                    <input type="text" name="payment-debt" id="payment-debt" autocomplete="on"
                                        class="simple-input disabled:bg-gray-200" autofocus="true"
                                        placeholder="Título da dívida"
                                        :disabled="disable_payments_inputs || payment.type === 2" v-model="payment.debt"
                                        required>
                                    <p v-if="payment.errors.debt" class="text-red-500 text-sm">{{
        payment.errors.debt }}</p>
                                </div>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="payment-debtor"
                                    class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Devedor</label>
                                <div class="mt-2">
                                    <input type="text" name="payment-debtor" id="payment-debtor" autocomplete="on"
                                        class="simple-input disabled:bg-gray-200" placeholder="Nome do devedor"
                                        :disabled="disable_payments_inputs" v-model="payment.debtor" required>
                                    <p v-if="payment.errors.debtor" class="text-red-500 text-sm">{{
        payment.errors.debtor }}</p>
                                </div>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="payment-total-amount"
                                    class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Valor
                                    total</label>
                                <div class="mt-2">
                                    <input id="payment-total-amount" name="payment-total-amount" type="number"
                                        step="0.01" min="0" autocomplete="off" class="simple-input disabled:bg-gray-200"
                                        placeholder="Valor total inicial da dívida" :disabled="disable_payments_inputs"
                                        v-model="payment.total_value" required>
                                    <p v-if="payment.errors.total_value" class="text-red-500 text-sm">{{
        payment.errors.total_value }}</p>
                                </div>
                            </div>

                            <div class="sm:col-span-6">
                                <label for="payment-observations"
                                    class="block text-sm font-medium leading-6 text-gray-900">Observações</label>
                                <div class="mt-2">
                                    <textarea type="text" observations="payment-observations" id="payment-observations"
                                        autocomplete="on" class="simple-input disabled:bg-gray-200"
                                        placeholder="Descreva a dívida mais detalhadamente ou insira informações adicionais"
                                        :disabled="disable_payments_inputs" v-model="payment.observations"></textarea>
                                    <p v-if="payment.errors.observations" class="text-red-500 text-sm">{{
        payment.errors.observations }}</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- SEÇÃO DE HABILITAÇÃO DE REGISTRO DE PAGAMENTOS -->
                    <section v-if="modal.mode === 'create'" class="py-4">
                        <div class="sm:col-span-6">
                            <div class="mt-2 relative flex gap-x-3"
                                :title="!can_add_record ? 'Preencha ao menos a dívida, o devedor e o valor total.' : ''">
                                <div class="flex h-6 items-center">
                                    <input id="payment-enable-records" name="payment-enable-records" type="checkbox"
                                        @change="toggle_enable_records()" :disabled="!can_add_record"
                                        class="h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-600 disabled:border-gray-100 disabled:text-green-200 disabled:bg-gray-300" />
                                </div>
                                <div class="text-sm leading-6">
                                    <label for="payment-enable-records"
                                        class="font-medium text-gray-900 select-none cursor-pointer"
                                        :class="{ 'text-gray-300': !can_add_record, 'cursor-not-allowed': !can_add_record }">Adicionar
                                        pagamentos
                                        concluídos</label>
                                </div>
                            </div>
                        </div>

                    </section>

                    <!-- SEÇÃO DE REGISTRO DE PAGAMENTOS -->
                    <section class="py-4" v-if="enable_records_inputs">
                        <div v-if="modal.mode === 'create'">
                            <h2 class="text-base font-semibold leading-7 text-gray-900">Pagamentos concluídos</h2>
                            <p class="mt-1 text-sm leading-6 text-gray-600">Insira o adiantamento ou os pagamentos
                                parciais
                                já
                                realizando desta dívida</p>
                        </div>
                        <div v-if="modal.mode === 'update'">
                            <h2 class="text-base font-semibold leading-7 text-gray-900">Lista de pagamentos concluídos
                            </h2>
                            <p class="mt-1 text-sm leading-6 text-gray-600">Abaixo está listado os registros de
                                pagamentos
                                realizados. Para adicionar novos pagamentos, ou remover algum pagamento existente, entre
                                na
                                seção de pagamento.</p>
                        </div>
                        <div v-if="modal.mode === 'pay'">
                            <h2 class="text-base font-semibold leading-7 text-gray-900">Realize um novo pagamento</h2>
                            <p class="mt-1 text-sm leading-6 text-gray-600">Adicione um pagamento ou remova um pagamento
                                existente.</p>
                        </div>

                        <div v-if="modal.mode !== 'update'"
                            class="mt-10 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-6">
                            <div class="col-span-3">
                                <label for="record-amount"
                                    class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Valor
                                    (R$)</label>
                                <div class="mt-2">
                                    <input id="record-amount" name="record-amount" type="number" step="0.01" min="0"
                                        :max="remaining_amount" autocomplete="off"
                                        class="simple-input disabled:bg-gray-200 disabled:opacity-75"
                                        :disabled="remaining_amount == 0" placeholder="Valor do pagamento parcial"
                                        v-model="record.amount" />
                                    <p class="text-gray-400 text-xs py-1">Valor Restante:y {{ toMoney(remaining_amount)
                                        }}
                                    </p>
                                    <p v-if="record.errors.amount" class="text-red-500 text-sm">{{
        record.errors.amount }}</p>
                                </div>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="record-date"
                                    class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Data
                                    do
                                    pagamento</label>
                                <div class="mt-2">
                                    <input id="record-date" name="record-date" type="date" :max="formatDate()"
                                        autocomplete="off" class="simple-input disabled:bg-gray-200"
                                        :disabled="remaining_amount == 0"
                                        placeholder="Data de pagamento do valor parcial"
                                        v-model="record.register_date" />
                                    <p class="text-gray-400 text-xs py-1">Não aceita datas no futuro</p>
                                    <p v-if="record.errors.date" class="text-red-500 text-sm">{{
        record.errors.date }}</p>
                                </div>
                            </div>
                            <!-- UPLOAD FILE
                            <div class="sm:col-span-6">
                                <div class="mt-2 relative flex gap-x-3">
                                    <div class="flex h-6 items-center">
                                        <input id="enable-record-file" name="enable-record-file" type="checkbox"
                                            v-model="insert_file"
                                            class="h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-600" />
                                    </div>
                                    <div class="text-sm leading-6">
                                        <label for="enable-record-file"
                                            class="font-medium text-gray-900 select-none cursor-pointer">Anexar comprovante
                                            de pagamento (PDF).</label>
                                    </div>
                                </div>
                            </div>

                            <div v-if="insert_file" class="sm:col-span-6">
                                <div class="mt-2">

                                    <div class="col-span-full">
                                        <label for="cover-photo"
                                            class="block text-sm font-medium leading-6 text-gray-900">Comprovante de
                                            pagamento</label>
                                        <div v-if="!record.filepath"
                                            class="mt-2 flex justify-center rounded-lg border border-dashed border-gray-900/25 px-6 py-10">
                                            <div class="text-center select-none">
                                                <PhotoIcon class="mx-auto h-12 w-12 text-gray-300" aria-hidden="true" />
                                                <div class="mt-4 flex text-sm leading-6 text-gray-600">
                                                    <label for="record-file"
                                                        class="relative cursor-pointer rounded-md bg-white font-semibold text-green-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-green-600 focus-within:ring-offset-2 hover:text-green-500">
                                                        <span>Faça Upload do comprovante</span>
                                                        <input id="record-file" name="record-file" type="file"
                                                            accept="application/pdf" maxSize="2MB" class="sr-only"
                                                            @input="setRecordFile" />
                                                    </label>
                                                    <p class="pl-1">ou arraste-o e solte-o aqui.</p>
                                                </div>
                                                <p class="text-xs leading-5 text-gray-500">Arquivos PDF até 2MB</p>
                                            </div>
                                        </div>
                                        <p v-if="record.filepath" class="text-gray-700 text-sm py-1 truncate"
                                            :title="record.filepath.name">>> {{
                                                record.filepath.name
                                            }}</p>
                                        <button v-if="record.filepath" type="button" class="m-0 p-0 text-green-500"
                                            @click="record.filepath = null">Trocar comprovante</button>
                                    </div>
                                </div>

                                <p v-if="record.errors.file" class="w-full text-center text-red-500 text-sm py-2">{{
                                    record.errors.file }}</p>
                            </div>
                        -->
                        </div>


                        <div v-if="modal.mode !== 'update'" class="w-full py-5 flex justify-end">
                            <button type="button"
                                class="w-full px-4 py-2 bg-blue-600 border border-gray-300 rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-blue-700 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition"
                                :disabled="!record.amount || !record.register_date || record.amount > remaining_amount"
                                @click=" addRecord()">
                                Inserir registro
                            </button>
                        </div>

                        <div v-if="payment.records_list.data.length" class="flex flex-col">
                            <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
                                <div class="inline-block min-w-full py-2 sm:px-6 lg:px-8">
                                    <div class="overflow-hidden">
                                        <table class="min-w-full text-left text-sm font-light">
                                            <thead class="border-b bg-white font-medium">
                                                <tr>
                                                    <th scope="col" class="px-6 py-4 text-center">Valor</th>
                                                    <th scope="col" class="px-6 py-4 text-center">Data</th>
                                                    <!-- FILE CONTROL
                                                    <th scope="col" class="px-6 py-4 text-center">Arquivo</th>
                                                    -->
                                                    <th v-if="!disable_payments_inputs" scope="col"
                                                        class="px-6 py-4 text-center">Excluir</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                <tr v-for=" record_item in records_list" class="border-b bg-white">
                                                    <td class="whitespace-nowrap px-6  text-center">
                                                        <div v-if="modal.mode !== 'update' && !record_item.past"
                                                            class="w-full relative flex flex-wrap items-stretch">
                                                            <span
                                                                class="w-1/4 select-none flex items-center whitespace-nowrap rounded-l border border-r-0 border-solid border-neutral-300 px-3 py-[0.25rem] text-center text-xs font-normal leading-[1.6] text-neutral-700"
                                                                id="basic-addon1">R$</span>
                                                            <input id="record_item-amount-edit"
                                                                name="record_item-amount-edit" type="number" step="0.01"
                                                                min="0" :max="remaining_amount" autocomplete="off"
                                                                class="relative m-0 block min-w-0 flex-auto rounded-r border border-solid border-neutral-300 bg-transparent bg-clip-padding px-3 py-[0.25rem] text-xs font-normal leading-[1.6] text-neutral-700 outline-none transition duration-200 ease-in-out focus:z-[3] focus:border-primary focus:text-neutral-700 focus:shadow-[inset_0_0_0_1px_rgb(59,113,202)] focus:outline-none disabled:bg-gray-200"
                                                                placeholder="Editar pagamento parcial"
                                                                v-model="record_item.amount" />
                                                        </div>
                                                        <p v-else>{{ toMoney(record_item.amount) }}</p>
                                                    </td>
                                                    <td class="whitespace-nowrap px-6 py-4 text-center">
                                                        <div v-if="modal.mode !== 'update' && !record_item.past"
                                                            class="w-full relative flex flex-wrap items-stretch">
                                                            <input id="record-date" name="record-date" type="date"
                                                                :max="formatDate()" autocomplete="off"
                                                                class="w-3/4 relative m-0 block min-w-0 flex-auto rounded-r border border-solid border-neutral-300 bg-transparent bg-clip-padding px-3 py-[0.25rem] text-xs font-normal leading-[1.6] text-neutral-700 outline-none transition duration-200 ease-in-out focus:z-[3] focus:border-primary focus:text-neutral-700 focus:shadow-[inset_0_0_0_1px_rgb(59,113,202)] focus:outline-none  disabled:bg-gray-200"
                                                                placeholder="Data de pagamento do valor parcial"
                                                                v-model="record_item.register_date" />

                                                        </div>
                                                        <p v-else>{{ formatDate(record_item.register_date, true) }}</p>
                                                    </td>

                                                    <!-- FILE CONTROL
                                                    <td class="whitespace-nowrap px-6 py-4 text-center select-none">
                                                        <div v-if="record_item.file"
                                                            class="flex flex-row space-x-2 items-center align-middle justify-center">
                                                            <span
                                                                class="cursor-pointer m-auto text-gray-700 hover:text-blue-500 active:text-blue-700"
                                                                @click="seeRecord(record_item.file)"
                                                                :title="'Ver arquivo ' + record_item.file.name">
                                                                <EyeIcon class="w-5 h-5" />
                                                            </span>
                                                            <span
                                                                class="cursor-pointer m-auto text-gray-700 hover:text-orange-500 active:text-orange-700"
                                                                @click="record_item.file = null"
                                                                :title="'Remover arquivo ' + record_item.file.name">
                                                                <DocumentMinusIcon class="w-5 h-5" />
                                                            </span>

                                                        </div>
                                                        <div v-else
                                                            class="flex flex-row space-x-2 items-center align-middle justify-center">
                                                            <label :for="'record-file-' + record_item.id"
                                                                class="cursor-pointer m-auto text-gray-700 hover:text-blue-500 active:text-blue-700"
                                                                title="Adicionar arquivo">
                                                                <input :id="'record-file-' + record_item.id"
                                                                    :name="'record-file-' + record_item.id" type="file"
                                                                    accept="application/pdf" maxSize="2MB" class="sr-only"
                                                                    @input.prevent="($event) => setRecordFile($event, record_item)" />
                                                                <DocumentArrowUpIcon class="w-5 h-5" />
                                                            </label>
                                                        </div>
                                                    </td>
                                                    -->

                                                    <td v-if="!disable_payments_inputs"
                                                        class="whitespace-nowrap px-6 py-4  select-none">
                                                        <XMarkIcon
                                                            class="w-5 h-5 cursor-pointer m-auto text-gray-700 hover:text-red-500 active:text-red-700"
                                                            @click="removeRecord(record_item.id, record_item)"
                                                            title="Remover este registro" />
                                                    </td>
                                                    <p v-if="record_item.errors.amount" class="text-red-500 text-sm">{{
        record_item.errors.amount }}</p>

                                                    <p v-if="record_item.errors.date" class="text-red-500 text-sm">{{
        record_item.errors.date }}</p>

                                                </tr>
                                            </tbody>
                                        </table>
                                        <p v-if="remaining_amount < 0" class="text-red-500 text-sm py-5 text-center">A
                                            soma
                                            dos
                                            pagamentos
                                            não pode ser maior que {{ toMoney(payment.total_value) }}</p>
                                        <p v-if="invalid_record_date" class="text-red-500 text-sm py-5 text-center">
                                            Todas as
                                            datas de registro devem ser não nulas e menores ou iguais a {{
        formatDate(new
            Date()) }}</p>
                                        <p v-if="invalid_record_amount" class="text-red-500 text-sm py-5 text-center">
                                            Todos
                                            os valores devem ser maiores que R$ 0,00</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="py-5 text-gray-400 text-center">Não há registro de pagamento no momento.
                        </div>
                    </section>

                </div>
            </form>
        </template>

        <template #footer>
            <SecondaryButton :class="{ 'disabled': payment.processing }" @click="close()">Cancelar
            </SecondaryButton>
            <PrimaryButton
                :class="{ 'disabled': (payment.processing || remaining_amount < 0 || invalid_record_date || invalid_record_amount) }"
                :disabled="(payment.processing || remaining_amount < 0 || invalid_record_date || invalid_record_amount)"
                @click="modal.mode === 'see' ? close() : submit()">{{
                modal.primary_button_txt
                }}</PrimaryButton>
        </template>
    </CreateUpdateModal>
</template>