<script setup>
    import { computed } from 'vue';
    import { XMarkIcon } from '@heroicons/vue/24/outline';
    import { formatDate, getPaymentMethodLabel, toMoney } from '@/general.js';

    const emit = defineEmits(['add-record', 'remove-record']);

    const props = defineProps({
        modal_mode: String,
        service: Object, // O objeto service completo, incluindo records_list
        record_form: Object, // O objeto 'record' do useForm
        remaining_amount: Number,
        invalid_record_date: Boolean,
        invalid_record_amount: Boolean,
    });

    const records_list = computed(() => {
        return props.service.records_list.data.sort((a, b) => { return new Date(a.date) - new Date(b.date) })
    });

    const addRecordHandler = () => {
        emit('add-record');
    };

    const removeRecordHandler = (record_id, record_item) => {
        emit('remove-record', record_id, record_item);
    };
</script>

<template>
    <section class="py-4">
        <div v-if="modal_mode === 'create'">
            <h2 class="text-base font-semibold leading-7 text-gray-900">Pagamentos concluídos</h2>
            <p class="mt-1 text-sm leading-6 text-gray-600">Insira o adiantamento ou os serviços parciais já realizando
                desta título</p>
        </div>
        <div v-if="modal_mode === 'update'">
            <h2 class="text-base font-semibold leading-7 text-gray-900">Lista de pagamentos concluídos</h2>
            <p class="mt-1 text-sm leading-6 text-gray-600">Abaixo está listado os registros de serviços realizados.
                Para adicionar novos serviços, ou remover algum serviço existente, entre na seção de serviço.</p>
        </div>
        <div v-if="modal_mode === 'pay'">
            <h2 class="text-base font-semibold leading-7 text-gray-900">Realize um novo pagamento</h2>
            <p class="mt-1 text-sm leading-6 text-gray-600">Adicione um serviço ou remova um serviço existente.</p>

        </div>
        <div v-if="modal_mode === 'see'">
            <h2 class="text-base font-semibold leading-7 text-gray-900">Veja a lista de pagamentos realizados</h2>

            <p class="mt-1 text-sm leading-6 text-gray-600">Abaixo segue a lista dos pagamentos concluídos deste serviço
            </p>
        </div>

        <div v-if="modal_mode !== 'update' && modal_mode !== 'see'"
            class="mt-10 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-6">
            <div class="col-span-2">
                <label for="record-amount"
                    class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Valor (R$)</label>

                <div class="mt-2">
                    <input id="record-amount" name="record-amount" type="number" step="0.01" min="0"
                        :max="remaining_amount" autocomplete="off"
                        class="simple-input disabled:bg-gray-200 disabled:opacity-75" :disabled="remaining_amount == 0"
                        placeholder="Valor do serviço parcial" v-model="record_form.amount" />
                    <p class="text-gray-400 text-xs py-1">Valor Restante: {{ toMoney(remaining_amount) }}</p>
                    <p v-if="record_form.errors.amount" class="text-red-500 text-sm">{{ record_form.errors.amount }}</p>

                </div>
            </div>

            <div class="sm:col-span-2">
                <label for="record-date"
                    class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Data do
                    serviço</label>
                <div class="mt-2">
                    <input id="record-date" name="record-date" type="date" :max="formatDate()" autocomplete="off"
                        class="simple-input disabled:bg-gray-200" :disabled="remaining_amount == 0"
                        placeholder="Data de serviço do valor parcial" v-model="record_form.register_date" />
                    <p class="text-gray-400 text-xs py-1">Não aceita datas no futuro</p>
                    <p v-if="record_form.errors.register_date" class="text-red-500 text-sm">{{
                        record_form.errors.register_date }}</p>
                </div>
            </div>

            <div class="sm:col-span-2">
                <label for="record-date"
                    class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Forma de
                    Pagamento</label>
                <div class="mt-2">
                    <select name="payment_method" class="simple-input" v-model="record_form.payment_method">
                        <option value="pix">PIX</option>
                        <option value="dinheiro">Dinheiro</option>
                        <option value="cartao_credito">Cartão de Crédito</option>
                        <option value="cartao_debito">Cartão de Débito</option>
                        <option value="ted">TED</option>
                        <option value="cheque">Cheque</option>
                    </select>
                    <p v-if="record_form.errors.payment_method" class="text-red-500 text-sm">{{
                        record_form.errors.payment_method }}</p>
                </div>
            </div>
        </div>

        <div v-if="modal_mode !== 'update' && modal_mode !== 'see'" class="w-full py-5 flex justify-end">
            <button type="button"
                class="w-full px-4 py-2 bg-blue-600 border border-gray-300 rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-blue-700 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition"
                :disabled="!record_form.amount || !record_form.register_date || !record_form.payment_method || record_form.amount > remaining_amount"
                @click="addRecordHandler()">
                Inserir registro
            </button>
        </div>

        <div v-if="service.records_list.data.length" class="flex flex-col">
            <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 sm:px-6 lg:px-8">
                    <div class="overflow-hidden">
                        <table class="min-w-full text-left text-sm font-light">
                            <thead class="border-b bg-white font-medium ">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-center">Valor</th>
                                    <th scope="col" class="px-6 py-4 text-center">Data</th>
                                    <th scope="col" class="px-6 py-4 text-center">Método de Pagamento</th>
                                    <th v-if="modal_mode !== 'create'" scope="col" class="px-6 py-4 text-center">Usuário
                                    </th>
                                    <th v-if="modal_mode !== 'pay' && modal_mode !== 'see'" scope="col"
                                        class="px-6 py-4 text-center">Excluir</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="record_item in records_list" class="border-b bg-white "
                                    :key="record_item.id">
                                    <td class="whitespace-nowrap px-1  text-center">
                                        <div v-if="modal_mode !== 'update' && !record_item.past"
                                            class="w-full relative flex flex-wrap items-stretch">
                                            <span
                                                class="w-1/4 select-none flex items-center whitespace-nowrap rounded-l border border-r-0 border-solid border-neutral-300 px-1 py-[0.25rem] text-center text-xs font-normal leading-[1.6] text-neutral-700 "
                                                id="basic-addon1">R$</span>
                                            <input id="record_item-amount-edit" name="record_item-amount-edit"
                                                type="number" step="0.01" min="0" :max="remaining_amount"
                                                autocomplete="off"
                                                class="relative m-0 block min-w-0 flex-auto rounded-r border border-solid border-neutral-300 bg-transparent bg-clip-padding px-1 py-[0.25rem] text-xs font-normal leading-[1.6] text-neutral-700 outline-none transition duration-200 ease-in-out focus:z-[3] focus:border-primary focus:text-neutral-700 focus:shadow-[inset_0_0_0_1px_rgb(59,113,202)] focus:outline-none disabled:bg-gray-200"
                                                placeholder="Editar serviço parcial" v-model="record_item.amount" />

                                        </div>
                                        <p v-else>{{ toMoney(record_item.amount) }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-1 py-4 text-center">
                                        <div v-if="modal_mode !== 'update' && !record_item.past"
                                            class="w-full relative flex flex-wrap items-stretch">
                                            <input id="record-date" name="record-date" type="date" :max="formatDate()"
                                                autocomplete="off"
                                                class="w-full relative m-0 block min-w-0 flex-auto rounded-r border border-solid border-neutral-300 bg-transparent bg-clip-padding px-1 py-[0.25rem] text-xs font-normal leading-[1.6] text-neutral-700 outline-none transition duration-200 ease-in-out focus:z-[3] focus:border-primary focus:text-neutral-700 focus:shadow-[inset_0_0_0_1px_rgb(59,113,202)] focus:outline-none disabled:bg-gray-200"
                                                placeholder="Data de serviço do valor parcial"
                                                v-model="record_item.register_date" />
                                        </div>
                                        <p v-else>{{ formatDate(record_item.register_date, true) }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-1 py-4 text-center">
                                        <div v-if="modal_mode !== 'update' && !record_item.past"
                                            class="w-full relative flex flex-wrap items-stretch">
                                            <select name="payment_method"
                                                class="w-full relative m-0 block min-w-0 flex-auto rounded-r border border-solid border-neutral-300 bg-transparent bg-clip-padding px-1 py-[0.25rem] text-xs font-normal leading-[1.6] text-neutral-700 outline-none transition duration-200 ease-in-out focus:z-[3] focus:border-primary focus:text-neutral-700 focus:shadow-[inset_0_0_0_1px_rgb(59,113,202)] focus:outline-none disabled:bg-gray-200"
                                                v-model="record_item.payment_method">
                                                <option value="pix">PIX</option>
                                                <option value="dinheiro">Dinheiro</option>
                                                <option value="cartao_credito">Cartão de Crédito</option>
                                                <option value="cartao_debito">Cartão de Débito</option>
                                                <option value="ted">TED</option>
                                                <option value="cheque">Cheque</option>
                                            </select>
                                        </div>
                                        <p v-else>{{ getPaymentMethodLabel(record_item.payment_method) }}</p>
                                    </td>
                                    <td v-if="modal_mode !== 'create'" class="whitespace-nowrap px-6 py-4 select-none">
                                        {{ record_item?.procedure?.user?.name
                                            || $page.props.auth.user.name
                                            || "Não identificado" }}
                                    </td>
                                    <td v-if="modal_mode !== 'pay' && modal_mode !== 'see'"
                                        class="whitespace-nowrap px-6 py-4 select-none">
                                        <XMarkIcon
                                            class="w-5 h-5 cursor-pointer m-auto text-gray-700 hover:text-red-500 active:text-red-700"
                                            @click="removeRecordHandler(record_item.id, record_item)"
                                            title="Remover este registro" />
                                    </td>
                                    <p v-if="record_item.errors?.amount" class="text-red-500 text-sm">{{
                                        record_item.errors.amount }}</p>
                                    <p v-if="record_item.errors?.date" class="text-red-500 text-sm">{{
                                        record_item.errors.date }}</p>
                                </tr>
                            </tbody>
                        </table>
                        <p v-if="remaining_amount < 0" class="text-red-500 text-sm py-5 text-center">A soma dos serviços
                            não pode ser maior que {{ toMoney(service.total_value) }}</p>
                        <p v-if="invalid_record_date" class="text-red-500 text-sm py-5 text-center">Todas as datas de
                            registro devem ser não nulas e menores ou iguais a {{ formatDate(new Date()) }}</p>
                        <p v-if="invalid_record_amount" class="text-red-500 text-sm py-5 text-center">Todos os valores
                            devem ser maiores que R$ 0,00</p>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="py-5 text-gray-400 text-center">Não há registro de pagamento no momento.</div>
    </section>
</template>