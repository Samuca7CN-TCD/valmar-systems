<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import FloatButton from '@/Components/FloatButton.vue'
import ExtraOptionsButton from '@/Components/ExtraOptionsButton.vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import Multiselect from 'vue-multiselect'
import { useToast } from 'vue-toast-notification'
import 'vue-toast-notification/dist/theme-sugar.css'
import 'vue-multiselect/dist/vue-multiselect.css';
import { toMoney, absolute, printList, formatDate, formatPhoneNumber, formatCPF, formatAgency, formatBankAccount } from '@/general.js'
import { PlusIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    page: Object,
    payslips_list: {
        type: Object,
        default: () => []
    },
    employee_id: Number,
    employees: Array,
    mode: String,
    payslip_month: Number,
    payslip_year: Number,
    transportation_voucher_value: Number,
})

const $toast = useToast()

const today = new Date()
const current_month = today.getMonth() + 1
const current_year = today.getFullYear()
const max_month = (formatDate()).substring(0, 7)
const months_names = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro']
const disable_all = ref(false)
const transp_vouc_val = ref(props.transportation_voucher_value.toFixed(2))
const observations = ref("");

const form = useForm({
    employees: props.employee_id
        ? [props.employees.find(el => el.id === props.employee_id)]
        : [...props.employees], // Array com todos os empregados
    month: formatDate(new Date(`${props.payslip_year}-${props.payslip_month}-01`), 'new_date').substring(0, 7),
    mode: props.mode,
});

const control_records = ref([]);
const new_control_record = useForm({
    name: '',
    value: 0,
    payment_data: '',
    payment_method: '',
});

const handleChange = () => {
    disable_all.value = true
    router.visit(route('payslip.print', {
        mode: form.mode,
        employee: props.employee_id || 0,
        month: parseInt(form.month.substring(5, 7)),
        year: form.month.substring(0, 4),
    }, {
        onFinish: () => {
            disable_all.value = false
        }
    }));
};

const get_payslips = (employee_id) => {
    return props.payslips_list[employee_id] || []
}

const entradas = (employee_id) => {
    return get_payslips(employee_id).filter(el => el.value > 0);
};

const total_entradas = (employee_id) => {
    return entradas(employee_id).reduce((acc, el) => acc + el.value, 0);
};

const saidas = (employee_id) => {
    return get_payslips(employee_id).filter(el => el.value < 0);
};

const saidas_detalhadas = (employee_id) => {
    return saidas(employee_id).filter(el => el.detail);
};

const saidas_nao_detalhadas = (employee_id) => {
    return saidas(employee_id).filter(el => !el.detail);
};

const total_saidas = (employee_id) => {
    return saidas(employee_id).reduce((acc, el) => acc + el.value, 0);
};

const total_saidas_detalhadas = (employee_id) => {
    return saidas_detalhadas(employee_id).reduce((acc, el) => acc + el.value, 0);
}

const total_saidas_nao_detalhadas = (employee_id) => {
    return saidas_nao_detalhadas(employee_id).reduce((acc, el) => acc + el.value, 0);
}

const total_geral = (employee_id) => {
    return total_entradas(employee_id) + total_saidas(employee_id)
};

const total_final = () => {
    const total_salarios = props.employees.reduce((acc, employee) => acc + total_geral(employee.id), 0);
    const total_registros = control_records.value.reduce((acc, record) => acc + record.value, 0);
    return total_salarios + total_registros;
}

const add_record = () => {
    new_control_record.clearErrors();
    if (!new_control_record.name) {
        new_control_record.errors.name = "Insira uma descrição"
        return;
    }
    if (!new_control_record.value) {
        new_control_record.errors.value = "Insira um valor"
        return;
    }

    if (!new_control_record.payment_data) {
        new_control_record.errors.payment_data = "Insira algum dado de pagamento"
        return;
    }
    control_records.value.push({
        name: new_control_record.name,
        value: new_control_record.value,
        payment_data: new_control_record.payment_data,
        payment_method: new_control_record.payment_method
    });
    new_control_record.reset();
}

const remove_record = (index) => {
    control_records.value.splice(index, 1);
}

// Funções e Computed Properties (se necessário)
// const someFunction = () => { ... }

</script>

<template>

    <Head :title="page.name" />
    <AppLayout :page="page" :payslip_month="payslip_month" :payslip_year="payslip_year">
        <template #header class="print:hidden">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Impressão de Pagamentos
            </h2>
            <FloatButton icon="printer" @click="printList" title="Imprimir Lista" />
        </template>

        <div class="pt-6 print:hidden print:pt-0">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 print:w-full">
                <div class="bg-white shadow-xl sm:rounded-lg p-5 grid grid-cols-2 gap-5">
                    <div class="text-neutral-500 text-sm space-y-3">
                        <label for="select-month">Selecione o mês</label>
                        <input id="select-month" type="month" :max="max_month" v-model="form.month"
                            class="w-full mx-auto simple-input disabled:bg-gray-200" :disabled="disable_all"
                            @change="handleChange">
                    </div>
                    <div class="text-neutral-500 text-sm space-y-3">
                        <label for="select-mode">Tipo de Impressão</label>
                        <select id="select-mode" class="simple-select w-full disabled:bg-gray-200"
                            :disabled="disable_all" v-model="form.mode" @change="handleChange">
                            <option value="payslip">Holerite</option>
                            <option value="transportation-voucher">Vale Transporte</option>
                            <option value="control">Controle</option>
                        </select>
                    </div>
                    <div v-if="mode === 'transportation-voucher'" class="col-span-2 text-neutral-500 text-sm space-y-3">
                        <label for="transportation-voucher-value">Valor do Vale Transporte</label>
                        <input id="transportation-voucher-value" type="number" step="0.01" v-model="transp_vouc_val"
                            class="w-full mx-auto simple-input disabled:bg-gray-200" :disabled="disable_all">
                    </div>
                    <div class="col-span-2 text-neutral-500 text-sm space-y-3">
                        <label for="multiselect-employees"
                            class="typo__label select-none text-sm font-medium leading-6 text-gray-900">Funcionários</label>
                        <Multiselect id="multiselect-employees" v-model="form.employees" :options="employees"
                            :multiple="true" :close-on-select="false" :clear-on-select="false" :preserve-search="true"
                            :allow-empty="true" :hide-selected="true" :disabled="disable_all"
                            placeholder="Selecione alguns" label="name" track-by="name"
                            class="disabled:bg-gray-200 w-full">
                            <template #selection="{ values, isOpen }">
                                <span class="multiselect__single" v-if="values.length && !isOpen">
                                    {{ values.length }} opções selecionadas
                                </span>
                            </template>
                        </Multiselect>
                        <div class="flex gap-2 my-2 text-xs">
                            <span class="text-blue-500 cursor-pointer hover:scale-105 active:scale-95 select-none"
                                @click="form.employees = employees">Adicionar
                                todos</span>
                            <span class="text-red-500 cursor-pointer hover:scale-105 active:scale-95 select-none"
                                @click="form.employees = []">Remover todos</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-6 print:py-0">
            <div
                class="w-screen md:max-w-7xl mx-auto sm:px-6 lg:px-8 print:max-w-full print:mx-0 print:sm:px-0 print:lg:px-0">
                <div
                    class="bg-white overflow-hidden shadow-xl sm:rounded-lg min-h-[300px] print:shadow-none flex flex-col">
                    <div v-if="props.mode === 'payslip'" v-for="employee in form.employees" :key="employee.id"
                        class="w-10/12 mx-auto py-10 break-inside-avoid border-b border-neutral-700 border-dashed">
                        <p class="underline font-extrabold uppercase text-2xl text-center mb-5">Recibo de Recebimento de
                            Salário</p>
                        <p class="text-justify">Eu, <strong class="uppercase font-bold leading-relaxed">{{ employee.name
                                }} {{
        employee.surname }}</strong> ,
                            recebi a
                            quantia de {{ toMoney(total_geral(employee.id)) }} referente ao pagamento do meu
                            salário do mês de <strong class="uppercase font-bold">{{ months_names[props.payslip_month -
        1] }} de
                                {{ props.payslip_year }}</strong>, por meus
                            serviços prestados à empresa <strong class="uppercase font-bold">Nascimento Correia Serviços
                                de
                                Manutenção Industrial e Isolamento Térmico LTDA, CNPJ: 15.544.278/0001-05</strong>,
                            pago por este nesta data.</p>
                        <table class="w-10/12 mx-auto mt-5 table-auto border-collapse border border-neutral-500">
                            <thead>
                                <tr class="bg-pink-100">
                                    <th class="border border-neutral-500">{{ employee.name }} {{ employee.surname }}
                                    </th>
                                    <th class="capitalize border border-neutral-500">{{
        months_names[props.payslip_month] }}/{{ props.payslip_year
                                        }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-center bg-green-500 text-white">
                                    <td colspan="2" class="uppercase font-bold border border-neutral-500">Entradas</td>
                                </tr>
                                <tr v-for="entrada in entradas(employee.id)" :key="entrada.id" class="text-center">
                                    <td class="border border-neutral-500">{{ entrada.description }}</td>
                                    <td class="border border-neutral-500"> {{ toMoney(entrada.value) }}</td>
                                </tr>
                                <tr class="text-center bg-red-500 text-white">
                                    <td colspan="2" class="uppercase font-bold border border-neutral-500">Saídas</td>
                                </tr>
                                <tr v-for="saida in saidas_detalhadas(employee.id)" :key="saida.id" class="text-center">
                                    <td class="border border-neutral-500">{{ saida.description }}</td>
                                    <td class="border border-neutral-500">{{ toMoney(absolute(saida.value)) }}</td>
                                </tr>
                                <tr v-if="saidas_nao_detalhadas(employee.id).length" class="text-center">
                                    <td class="border border-neutral-500">Outros</td>
                                    <td class="border border-neutral-500">{{
        toMoney(absolute(total_saidas_nao_detalhadas(employee.id)))
    }}</td>
                                </tr>
                                <tr class="text-center bg-yellow-500">
                                    <td class="border border-neutral-500 uppercase">A Receber</td>
                                    <td class="border border-neutral-500">{{ toMoney(total_geral(employee.id)) }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <p class="text-center font-mono font-extrabold mt-7">
                            ______________________________________________
                        </p>
                        <p class="text-center font-extrabold text-sm uppercase">{{ employee.name }} {{ employee.surname
                            }}</p>
                        <p class="text-center font-extrabold text-xs uppercase">Itabuna, {{ formatDate(new Date(),
        'reading') }}
                        </p>
                    </div>



                    <div v-if="props.mode === 'transportation-voucher'" v-for="employee in form.employees"
                        :key="employee.id" class="py-10 break-inside-avoid border-b border-neutral-700 border-dashed">
                        <div class="w-9/12 mx-auto border border-neutral-900 rounded-lg p-5">
                            <p class="font-extrabold uppercase text-4xl text-center">Recibo</p>
                            <p class="font-extrabold uppercase text-lg text-center mb-5">Entrega do Vale
                                Transporte</p>
                            <p class="text-justify">
                                Empregador(a): <span class="underline">NASCIMENTO CORREIA SERVIÇOS DE MANUTENÇÃO
                                    INDUSTRIAL
                                    E
                                    ISOLAMENTO
                                    TÉRMICO
                                    LTDA</span></p>
                            <p class="text-justify">
                                Empregado(a): {{ employee.name }} {{ employee.surname }}</p>
                            <p class="text-justify">
                                Recebi: <span class="underline">{{ toMoney(transp_vouc_val) }}</span> ao
                                mês de <span class="underline"><span class="capitalize">{{ months_names[payslip_month]
                                        }}</span> / {{
        payslip_year
    }}</span> pelo
                                que firmo o presente.</p>
                            <p class="text-justify">
                                Local: <span class="underline">ITABUNA/BA</span> - Data: <span class="underline">{{
            formatDate(today, 'normal')
        }}</span>
                            </p>
                            <p class="text-center font-mono font-extrabold mt-7">
                                ______________________________________________
                            </p>
                            <p class="text-center">Assinatura do(a) Empregado(a)</p>
                        </div>
                    </div>


                    <div v-if="props.mode === 'control'" class="py-10">
                        <table class="w-fit mx-auto mt-5 table-auto border-collapse border border-neutral-400">
                            <thead>
                                <tr class="bg-neutral-200">
                                    <th class="border border-neutral-400">Funcionário</th>
                                    <th class="border border-neutral-400">Salário</th>
                                    <th class="border border-neutral-400">Dados de Pagamento</th>
                                    <th class="border border-neutral-400">Método de Pagamento</th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                <tr v-for="employee in form.employees" :key="employee.id"
                                    class="break-inside-avoid even:bg-neutral-50">
                                    <td class="border border-neutral-400 p-2">{{ employee.name }} {{ employee.surname }}
                                    </td>
                                    <td class="border border-neutral-400 p-2 text-red-500">{{
        toMoney(total_geral(employee.id))
    }}
                                    </td>
                                    <td class="border border-neutral-400 p-2">
                                        <span v-if="employee.payment_method.cod === 'salary_account'">-</span>
                                        <span
                                            v-if="employee.payment_method.cod === 'ted' || employee.payment_method.cod === 'pix_bank_data'"
                                            class="flex flex-col text-xs">
                                            <span class="text-neutral-500">{{ employee.bank.name
                                                }}</span>
                                            <span>Ag: {{ formatAgency(employee.bank_ag)
                                                }}</span>
                                            <span>{{ employee.account_type.cod === 'current_account' ? 'C/C' : 'C/P' }}:
                                                {{
        formatBankAccount(employee.account_number)
    }}</span>
                                            <span class="text-neutral-500">CPF: {{ formatCPF(employee.pix_cpf)
                                                }}</span>
                                        </span>
                                        <span v-if="employee.payment_method.cod === 'pix_email'">{{ employee.pix_email
                                            }}</span>
                                        <span v-if="employee.payment_method.cod === 'pix_cpf'">{{
        formatCPF(employee.pix_cpf) }}</span>
                                        <span v-if="employee.payment_method.cod === 'pix_phone_number'">{{
        formatPhoneNumber(employee.pix_phone_number) }}</span>
                                        <span v-if="employee.payment_method.cod === 'pix_token'">{{
        employee.pix_token }}</span>
                                    </td>
                                    <td class="border border-neutral-400 p-2 text-neutral-500"> {{
        employee.payment_method.name
    }} </td>
                                </tr>



                                <tr v-for="(record, index) in control_records" :key="index"
                                    class="break-inside-avoid even:bg-neutral-50 group">
                                    <td class="border border-neutral-400 p-2">{{ record.name }}
                                        <p class="text-xs text-red-500 hover:text-red-700 active:text-red-900 cursor-pointer hidden group-hover:block"
                                            @click="remove_record(index)">Excluir registro</p>
                                    </td>
                                    <td class="border border-neutral-400 p-2 text-red-500">{{
        toMoney(record.value)
    }}
                                    </td>
                                    <td class="border border-neutral-400 p-2">
                                        {{ record.payment_data }}
                                    </td>
                                    <td class="border border-neutral-400 p-2 text-neutral-500"> {{
        record.payment_method
    }} </td>
                                </tr>
                                <tr class="bg-yellow-100">
                                    <td class="text-right border border-neutral-400 p-2">Total</td>
                                    <td class="border border-neutral-400 p-2">{{ toMoney(total_final()) }}</td>
                                    <td colspan="2"></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="w-10/12 mx-auto flex flex-row gap-3 items-center justify-center my-5 print:hidden">
                            <div class="text-neutral-500 text-sm space-y-3">
                                <label for="record-name">Descrição</label>
                                <input id="record-name" type="text" class="simple-input"
                                    v-model="new_control_record.name" placeholder="Insira uma descrição" />
                            </div>
                            <div class="text-neutral-500 text-sm space-y-3">
                                <label for="record-value">Valor (R$)</label>
                                <input id="record-value" type="number" class="simple-input"
                                    v-model="new_control_record.value" placeholder="Insira o valor (diferente de 0)" />

                            </div>
                            <div class="text-neutral-500 text-sm space-y-3">
                                <label for="record-payment-data">Dados de pagamento</label>
                                <input id="record-payment-data" type="text" class="simple-input"
                                    v-model="new_control_record.payment_data"
                                    placeholder="Insira algum dado de pagamento" />

                            </div>
                            <div class="text-neutral-500 text-sm space-y-3">
                                <label for="record-payment-method">Método de pagamento</label>
                                <input id="record-payment-method" type="text" class="simple-input"
                                    v-model="new_control_record.payment_method"
                                    placeholder="Se quiser, especifique o método de pagamento" />

                            </div>
                            <div class="">
                                <button class="p-3 rounded-lg bg-green-500 hover:bg-green-600 active:bg-green-700"
                                    @click="add_record">
                                    <PlusIcon class="w-4 h-4 text-white" />
                                </button>
                            </div>
                        </div>
                        <div class="print:hidden text-center">
                            <span v-if="new_control_record.errors.name" class="text-red-500 text-xs">{{
                                new_control_record.errors.name }}</span>
                            <span v-if="new_control_record.errors.value" class="text-red-500 text-xs">{{
                                new_control_record.errors.value }}</span>
                            <span v-if="new_control_record.errors.payment_data" class="text-red-500 text-xs">{{
                                new_control_record.errors.payment_data }}</span>
                            <span v-if="new_control_record.errors.payment_method" class="text-red-500 text-xs">{{
                                new_control_record.errors.payment_method }}</span>
                        </div>

                        <div class="space-y-3 w-10/12 mx-auto mt-5 break-inside-avoid"
                            :class="{ 'print:hidden': !observations }">
                            <label for="observations" class="text-neutral-500">
                                Observações</label>
                            <textarea id="observations" v-model="observations"
                                class="simple-input print:border-none print:outline-none print:ring-0 print:shadow-none"
                                rows="3"
                                placeholder="Insira aqui alguma anotação que você quer que aparece na impressão (os dados não serão salvos, é apenas para uso momentâneo)"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <ExtraOptionsButton :mode="['rollup', 'print_page']" />
    </AppLayout>
</template>