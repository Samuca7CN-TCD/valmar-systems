<script setup>
    import AppLayout from '@/Layouts/AppLayout.vue'
    import CreateUpdatePayModal from '@/Components/Payments/CreateUpdatePayModal.vue'
    import FloatButton from '@/Components/FloatButton.vue'
    import ExtraOptionsButton from '@/Components/ExtraOptionsButton.vue'
    import { Head, useForm } from '@inertiajs/vue3'
    import { computed, ref } from 'vue'
    import { BanknotesIcon, MagnifyingGlassIcon, PencilIcon, XMarkIcon } from '@heroicons/vue/24/outline'
    import { toMoney, formatDate, calcDeadlineDays } from '@/general.js'
    import { useToast } from 'vue-toast-notification';
    import 'vue-toast-notification/dist/theme-sugar.css';
    // =============================================
    // Informações exteriores
    const props = defineProps({
        page: Object,
        page_options: {
            type: Array,
            default: null,
        },
        payments_list: Object,
    })

    const $toast = useToast();
    // =============================================
    // Informações do OBJETO
    const payment_data = useForm({
        'id': null,
        'debt': '',
        'debtor': '',
        'total_value': 0,
        'partial_value': 0,
        'observations': '',
        'records_list': {
            'enable_records': false,
            'data': [],
        }
    })

    const search_term = ref("")
    const show_services = ref(true)
    const show_user_data = ref(false);

    const filtered_payments_list = computed(() => {
        const searchTermLower = search_term.value.toLowerCase();

        return props.payments_list.filter(el =>
            (el.motive.toLowerCase().includes(searchTermLower)) ||
            (el.entity_name.toLowerCase().includes(searchTermLower)) ||
            (toMoney(el.accounting.total_value).toString().toLowerCase().includes(searchTermLower)) ||
            (toMoney(el.accounting.partial_value).toString().toLowerCase().includes(searchTermLower))
        );
    });

    const total_payments_amount = computed(() => {
        return props.payments_list.reduce((accumulator, payment) => {
            return accumulator + payment.accounting.partial_value;
        }, 0);
    });

    const total_payments_amount_without_services = computed(() => {
        return props.payments_list.filter(el => el.type !== 1).reduce((accumulator, payment) => {
            return accumulator + payment.accounting.partial_value;
        }, 0);
    });

    // =============================================
    // Controle de Modal
    const modal = ref({
        mode: 'create',
        show: false,
        get title() {
            switch (this.mode) {
                case 'create': return "Criar pagamento"
                case 'update': return "Editar pagamento"
                case 'pay': return "Pagar pagamento"
            }
        },
        get primary_button_txt() {
            switch (this.mode) {
                case 'create': return "Cadastrar"
                case 'update': return "Atualizar"
                case 'pay': return "Pagar"
            }
        }
    })

    const openModal = (mode, payment_id = null) => {
        const isUpdateOrPayMode = ['update', 'pay'].includes(mode);

        if (payment_id !== null && isUpdateOrPayMode) {
            const payment = props.payments_list.find(payment => payment.id === payment_id);

            if (payment) {
                const { id, type, motive, entity_name, observations, records, accounting } = payment;
                payment_data.id = id;
                payment_data.type = type;
                payment_data.debt = motive;
                payment_data.debtor = entity_name;
                payment_data.total_value = accounting.total_value;
                payment_data.partial_value = accounting.partial_value;
                payment_data.observations = observations;
                payment_data.records_list.enable_records = Boolean(records.length);
                payment_data.records_list.data = records.map((record) => { return useForm(record) })
            }
        }

        modal.value.mode = mode;
        modal.value.show = true;
    };


    const closeModal = () => {
        payment_data.reset()
        modal.value.show = false
    }



    // =============================================
    // Métodos de CRUD
    const createPayment = () => {
        payment_data.post(route('payments.store'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: (error) => { console.log(error) }
        })
    }

    const updatePayment = () => {
        payment_data.put(route('payments.update', payment_data.id), {
            preserveScroll: true,
            onSuccess: () => closeModal()
        })
    }

    const payPayment = () => {
        payment_data.put(route('payments.pay', payment_data.id), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: (error) => { console.log(error) }
        })
    }

    const deletePayment = (payment_id, payment_name) => {
        if (confirm(`Você tem certeza que deseja excluir o pagamento "${payment_name}"? Esta ação não poderá ser desfeita!`)) {
            payment_data.delete(route('payments.destroy', payment_id), {
                preserveScroll: true,
                onSuccess: () => $toast.success('Pagamento deletado com sucesso!')
            })
        }
    }

    const submit = () => {
        switch (modal.value.mode) {
            case 'create': return createPayment()
            case 'update': return updatePayment()
            case 'pay': return payPayment()
            default: $toast.error('Método desconhecido. Informar o Técnico.')
        }
    }
</script>

<template>

    <Head :title="page.name" />
    <AppLayout :page="page" :page_options="page_options">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ page.name }} <span v-if="$page.props.auth.user.hierarchy < 2">| {{
                    toMoney(total_payments_amount_without_services)
                }}</span><span v-if="$page.props.auth.user.hierarchy < 2" class="text-neutral-400"> | {{
                        toMoney(total_payments_amount)
                    }} <span class="text-xs"> (pagamentos + serviços)</span></span>
            </h2>
            <FloatButton :icon="'plus'" @click="openModal('create')" title="Cadastrar Payment" class="print:hidden" />
        </template>

        <div class="pt-12 print:hidden">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                    <div class="flex flex-row items-center space-x-3">
                        <MagnifyingGlassIcon class="w-5 y-5" />
                        <input type="text" name="search_term" id="search_term" autocomplete="on" class="simple-input"
                            autofocus="true" placeholder="Pesquisar termo..." v-model="search_term" />
                    </div>
                    <div class="ml-8 mt-1">
                        <input type="checkbox" id="show-services"
                            class="focus:ring-green-700 text-green-700 border-neutral-500 rounded-sm disabled:opacity-50"
                            v-model="show_services" :checked="show_services" />
                        <label for="show-services" class="ml-3 text-xs text-neutral-500 select-none">Mostrar
                            serviços</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-12 print:py-0">
            <div class="max-w-7xl mx-auto print:max-w-full">
                <div class="w-full flex justify-end pb-3 print:hidden">
                    <button v-if="!show_user_data" class="text-blue-500 text-xs justify-end"
                        @click="show_user_data = !show_user_data">Mostrar
                        dados de
                        usuário</button>
                    <button v-if="show_user_data" class="text-red-500 text-xs justify-end"
                        @click="show_user_data = !show_user_data">Esconder dados de usuário</button>
                </div>
                <div class="px-0 print:px-0">
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg print:shadow-none">
                        <div class="flex flex-col">
                            <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
                                <div class="inline-block min-w-full py-2 sm:px-6 lg:px-8">
                                    <div class="overflow-hidden">
                                        <table class="w-full text-left text-sm font-light">
                                            <thead class="border-b font-medium ">
                                                <tr>
                                                    <th scope="col" class="px-6 py-4 text-center">#</th>
                                                    <th scope="col" class="px-6 py-4 text-center">Dívida</th>
                                                    <th scope="col" class="px-6 py-4 text-center">Devedor</th>
                                                    <th scope="col" class="px-6 py-4 text-center">À pagar</th>
                                                    <th scope="col" class="px-6 py-4 text-center">Valor total</th>
                                                    <th scope="col" class="px-6 py-4 text-center print:hidden">Pagar
                                                    </th>
                                                    <th scope="col" class="px-6 py-4 text-center print:hidden">Editar
                                                    </th>
                                                    <th scope="col" class="px-6 py-4 text-center print:hidden">Excluir
                                                    </th>
                                                    <th scope="col" class="px-6 py-4 text-center print:hidden"
                                                        v-if="show_user_data">Criado
                                                        em</th>
                                                    <th scope="col" class="px-6 py-4 text-center print:hidden"
                                                        v-if="show_user_data">Criado
                                                        por</th>
                                                    <th scope="col" class="px-6 py-4 text-center print:hidden"
                                                        v-if="show_user_data">
                                                        Modificado em</th>
                                                    <th scope="col" class="px-6 py-4 text-center print:hidden"
                                                        v-if="show_user_data">
                                                        Modificado por
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-if="filtered_payments_list.length"
                                                    v-for="payment in filtered_payments_list"
                                                    class="border-b transition duration-300 ease-in-out hover:bg-neutral-100 print:break-inside-avoid"
                                                    :class="{ 'hidden': payment.type === 1 && show_services === false }">
                                                    <td class="whitespace-nowrap py-4 text-center font-medium">{{
                                                        payment.id }}
                                                    </td>
                                                    <td class="break-words px-6 py-4 text-center">{{
                                                        payment.motive }}
                                                        <span v-if="payment.observations" :title="payment.observations"
                                                            class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10 cursor-help">OBS</span>
                                                    </td>
                                                    <td class="whitespace-normal px-6 py-4 text-center">{{
                                                        payment.entity_name }}</td>
                                                    <td class="whitespace-nowrap px-2 py-4 text-center">{{
                                                        toMoney(payment.accounting.partial_value) }}</td>
                                                    <td class="whitespace-nowrap px-2 py-4 text-center">{{
                                                        toMoney(payment.accounting.total_value) }}</td>
                                                    <td class="whitespace-nowrap px-4 py-4 text-center cursor-pointer hover:text-green-700 active:text-green-900 select-none print:hidden"
                                                        :title="'PAGAR: ' + payment.motive + '(' + payment.entity_name + ')'"
                                                        @click="openModal('pay', payment.id)">
                                                        <BanknotesIcon class="w-5 h-5 m-auto" />
                                                    </td>
                                                    <td class="whitespace-nowrap px-4 py-4 text-center cursor-pointer hover:text-yellow-700 active:text-yellow-900 select-none print:hidden"
                                                        :title="'EDITAR: ' + payment.motive + '(' + payment.entity_name + ')'"
                                                        @click="openModal('update', payment.id)">
                                                        <PencilIcon class="w-5 h-5 m-auto" />
                                                    </td>
                                                    <td v-if="payment.type !== 2"
                                                        class="whitespace-nowrap px-4 py-4 text-center cursor-pointer hover:text-red-700 active:text-red-900 select-none print:hidden"
                                                        :title="'EXCLUIR: ' + payment.motive + '(' + payment.entity_name + ')'"
                                                        @click="deletePayment(payment.id, payment.motive)">
                                                        <XMarkIcon class="w-5 h-5 m-auto" />
                                                    </td>
                                                    <td v-else
                                                        class="whitespace-nowrap px-4 py-4 text-center select-none print:hidden">
                                                        Venda
                                                    </td>
                                                    <td class="whitespace-nowrap px-2 py-4 text-center print:hidden"
                                                        v-if="show_user_data">
                                                        {{
                                                            formatDate(payment.created_at, 'reading_date_time') }}
                                                    </td>
                                                    <td class="whitespace-nowrap px-2 py-4 text-center print:hidden"
                                                        v-if="show_user_data">
                                                        {{
                                                            payment.procedures[0].user.name }}
                                                    </td>
                                                    <td class="whitespace-nowrap px-2 py-4 text-center print:hidden"
                                                        v-if="show_user_data">
                                                        {{
                                                            formatDate(payment.updated_at, 'reading_date_time') }}
                                                    </td>
                                                    <td class="whitespace-nowrap px-2 py-4 text-center print:hidden"
                                                        v-if="show_user_data">
                                                        {{
                                                            payment.procedures[payment.procedures?.length - 1].user.name }}
                                                    </td>
                                                </tr>
                                                <tr v-else
                                                    class="transition duration-300 ease-in-out hover:bg-neutral-100 print:break-inside-avoid">
                                                    <td class="whitespace-nowrap px-6 py-4 text-center" colspan="9">Não
                                                        há
                                                        pagamentos cadastrados no momento!</td>
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

        <CreateUpdatePayModal :show="modal.show" :modal="modal" :payment="payment_data" @submit="submit"
            @close="closeModal" />

        <ExtraOptionsButton :mode="['rollup', 'print_page', 'link']" :link_type="['previous']"
            :link="[route('payments.previous')]" />
    </AppLayout>
</template>
