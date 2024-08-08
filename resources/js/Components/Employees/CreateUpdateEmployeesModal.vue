<script setup>
import CreateUpdateModal from '@/Components/CreateUpdateModal.vue';
import { PlusIcon, PencilIcon, XMarkIcon, BanknotesIcon, EyeIcon } from '@heroicons/vue/24/outline';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ref, computed, reactive } from 'vue';
import { formatDate, formatPhoneNumber, validateCPF, formatCPF, formatAgency, formatBankAccount, toMoney, clearFormat } from '@/general.js';
import { useForm } from '@inertiajs/vue3';

const emit = defineEmits(['close', 'submit']);
const props = defineProps({
    modal: Object,
    employee: {
        type: Object,
        default: null,
    },
    account_types_list: Array,
    payment_methods_list: Array,
    banks_list: Array,
});

const new_contact = ref("");

const addContact = (event = null) => {
    if (event) event.preventDefault();

    const number = clearFormat(new_contact.value);
    if (number.length === 11) {
        props.employee.errors.contacts = "";
        if (props.employee.contacts.includes(number)) {
            props.employee.errors.contacts = "Número já adicionado na lista";
            return;
        }
        props.employee.contacts.push(new_contact.value);
        new_contact.value = "";
    } else {
        props.employee.errors.contacts = "Números de contato precisam ter 11 dígitos";
    }
};

const removeContact = (contact) => {
    props.employee.contacts = props.employee.contacts.filter(el => el !== contact);
};

const formatted_contact = computed(() => formatPhoneNumber(new_contact.value));

const formatted_pix_phone_number = computed(() => formatPhoneNumber(props.employee.pix_phone_number));

const formatted_pix_cpf = computed(() => formatCPF(props.employee.pix_cpf));

const formatted_bank_ag = computed(() => formatAgency(props.employee.bank_ag));

const formatted_account_number = computed(() => formatBankAccount(props.employee.account_number));

const enableSubmit = computed(() => {
    const { name, surname, salary, contacts, function_name, payment_method_id, overtime_payment_method_id, bank_id, pix_cpf, pix_email, pix_phone_number, pix_token, bank_ag, account_type_id, account_number } = props.employee;
    if (!name || !surname || !salary || salary < 0 || !contacts.length || !function_name || !payment_method_id || !overtime_payment_method_id) return false;

    let flag = false
    switch (payment_method_id) {
        case 1:
        case 6:
            flag = account_type_id && bank_id && bank_ag && account_number && pix_cpf;
        case 3:
            flag = !!pix_email;
        case 4:
            flag = !!pix_cpf;
        case 5:
            flag = !!pix_phone_number;
        case 7:
            flag = !!pix_token;
        default:
            flag = true;
    }

    if (!flag) return false

    if (enable_overtime_account_data) {
        switch (overtime_payment_method_id) {
            case 3:
                return !!pix_email;
            case 4:
                return !!pix_cpf;
            case 5:
                return !!pix_phone_number;
            case 7:
                return !!pix_token;
            default:
                return true;
        }
    }
});

const see_disabled = computed(() => props.modal.mode === 'see');

const enable_account_data = computed(() => [1, 6].includes(props.employee.payment_method_id));

const enable_overtime_account_data = computed(() => props.employee.payment_method_id !== props.employee.overtime_payment_method_id);

const disable_add_phone_number = computed(() => props.employee.contacts.length > 3);

const inputCpf = (event) => {
    const cpf = event.target.value;
    props.employee.errors.pix_cpf = validateCPF(cpf) ? "" : "CPF inválido!";
    if (!props.employee.errors.pix_cpf) props.employee.pix_cpf = clearFormat(cpf);
};

const inputContact = (event) => new_contact.value = clearFormat(event.target.value);

const inputPixPhoneNumber = (event) => props.employee.pix_phone_number = clearFormat(event.target.value);

const inputAgency = (event) => props.employee.bank_ag = clearFormat(event.target.value);

const inputBankAccount = (event) => props.employee.account_number = clearFormat(event.target.value);

const close = () => emit('close');

const submit = () => emit('submit');
</script>
<template>
    <CreateUpdateModal :show="modal.show" :maxWidth="'2xl'" @close="close">
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
            <form @keydown.enter.prevent>
                <div class="pb-12 space-y-12 divide-y-2">

                    <!-- SEÇÃO DE DADOS PESSOAIS -->
                    <section class="py-4">
                        <h2 class="text-base font-semibold leading-7 text-gray-900">Informações Básicas</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">Informações sobre o funcionário como nome,
                            salário e contato</p>

                        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            <div class="sm:col-span-3">
                                <label for="employee-name" class="block text-sm fontSamuel@samuel-pc:~$ 
-medium leading-6 text-gray-900 required-input-label">Nome</label>
                                <div class="mt-2">
                                    <input type="text" name="employee-name" id="employee-name" autocomplete="on"
                                        class="simple-input disabled:bg-gray-200" placeholder="Nome"
                                        v-model="employee.name" :disabled="see_disabled" required>
                                    <p v-if="employee.errors.name" class="text-red-500 text-sm">{{
                                        employee.errors.name }}</p>
                                </div>
                            </div>
                            <div class="sm:col-span-3">
                                <label for="employee-surname"
                                    class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Sobrenome</label>
                                <div class="mt-2">
                                    <input type="text" name="employee-surname" id="employee-surname" autocomplete="on"
                                        class="simple-input disabled:bg-gray-200" placeholder="Sobrenome"
                                        v-model="employee.surname" :disabled="see_disabled" required>
                                    <p v-if="employee.errors.surname" class="text-red-500 text-sm">{{
                                        employee.errors.surname }}</p>
                                </div>
                            </div>
                            <div class="sm:col-span-3">
                                <label for="employee-salary"
                                    class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Salário</label>
                                <div class="mt-2 relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-gray-500 sm:text-sm">R$</span>
                                    </div>
                                    <input type="number" min="0" step="0.01" name="employee-salary" id="employee-salary"
                                        autocomplete="on" class="simple-input disabled:bg-gray-200 pl-9"
                                        placeholder="Salário" v-model="employee.salary" :disabled="see_disabled"
                                        required>
                                    <p v-if="employee.errors.salary" class="text-red-500 text-sm">{{
                                        employee.errors.salary }}</p>
                                </div>
                            </div>
                            <div class="sm:col-span-3">
                                <label for="employee-agreement"
                                    class="block text-sm font-medium leading-6 text-gray-900">Acordo</label>
                                <div class="mt-2 relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-gray-500 sm:text-sm">R$</span>
                                    </div>
                                    <input type="number" min="0" step="0.01" name="employee-agreement"
                                        id="employee-agreement" autocomplete="on"
                                        class="simple-input disabled:bg-gray-200 pl-9"
                                        placeholder="Valor de acrécimo (+) ou desconto (-) combinado por fora"
                                        v-model="employee.agreement" :disabled="see_disabled" required>
                                    <p v-if="employee.errors.agreement" class="text-red-500 text-sm">{{
    employee.errors.agreement }}</p>
                                </div>
                            </div>
                            <div class="sm:col-span-6">
                                <label for="employee-contacts"
                                    class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Números
                                    de contato</label>
                                <div class="mt-2 relative">
                                    <input v-if="modal.mode !== 'see'" type="text" minlength="11" maxlength="16"
                                        name="employee-contacts" id="employee-contacts" autocomplete="on"
                                        class="simple-input disabled:bg-gray-200 pr-9"
                                        placeholder="Adicione números de contato do funcionário"
                                        :value="formatted_contact" @input="inputContact" @keydown.enter="addContact"
                                        :disabled="disable_add_phone_number || see_disabled">
                                    <div v-if="modal.mode !== 'see'" class="absolute inset-y-0 right-0 p-1.5">
                                        <button type="button"
                                            class="w-full text-white text-center p-0.5 rounded-lg bg-green-500 hover:bg-green-600 active:bg-green-700"
                                            @click="addContact" :disabled="disable_add_phone_number">
                                            <PlusIcon class="w-5 h-5" />
                                        </button>
                                    </div>
                                    <ul class="select-none py-1">
                                        <li v-for="(contact, index) in employee?.contacts" :key="index"
                                            class="float-left px-2 py-1 m-1 bg-green-500 text-white text-xs rounded-lg flex flex-row items-center justify-between gap-1">
                                            {{ formatPhoneNumber(contact) }}
                                            <button v-if="modal.mode !== 'see'" class="text-white"
                                                @click.stop.prevent="removeContact(contact)">
                                                <XMarkIcon class="w-3 h-3 text-white" />
                                            </button>
                                        </li>
                                    </ul>
                                    <p v-if="employee.errors && employee.errors.contacts" class="text-red-500 text-sm">
                                        {{ employee.errors.contacts }}
                                    </p>
                                </div>
                            </div>
                            <div class="sm:col-span-6">
                                <label for="employee-function-name"
                                    class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Função</label>
                                <div class="mt-2">
                                    <input type="text" name="employee-function-name" id="employee-function-name"
                                        autocomplete="on" class="simple-input disabled:bg-gray-200"
                                        placeholder="Nome da função que o funcionário excerce"
                                        v-model="employee.function_name" :disabled="see_disabled" required>
                                    <p v-if="employee.errors.function_name" class="text-red-500 text-sm">{{
                                        employee.errors.function_name }}</p>
                                </div>
                            </div>
                            <div class="sm:col-span-6">
                                <div class="relative flex gap-x-3">
                                    <div class="flex h-6 items-center">
                                        <input id="employee-transportation-voucher"
                                            name="employee-transportation-voucher" type="checkbox"
                                            class="h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-600"
                                            v-model="employee.transportation_voucher" true-value="1" false-value="0"
                                            :disabled="see_disabled" required />
                                    </div>
                                    <div class="text-sm leading-6">
                                        <label for="employee-transportation-voucher"
                                            class="font-medium text-gray-900">Recebe Vale
                                            Transporte?</label>
                                        <p class="text-gray-500">Assinalando esta opção, o valor do vale transporte será
                                            adicionado todo mês ao Holerite</p>
                                        <p v-if="employee.errors.transportation_voucher" class="text-red-500 font-mono">
                                            {{
                                            employee.errors.transportation_voucher }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- SEÇÃO DE DADOS DE PAGAMENTO -->
                    <section class="py-4">
                        <h2 class="text-base font-semibold leading-7 text-gray-900">Dados de Pagamento</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">Seção para especificar os dados necessários para
                            o pagamento do funcionário.</p>

                        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-6">

                            <div class="sm:col-span-6">
                                <label for="employee-payment-method"
                                    class="text-sm font-medium leading-6 text-gray-900 required-input-label">Método
                                    de pagamento</label>
                                <div class="mt-2">
                                    <select name="employee-payment-method" id="employee-payment-method"
                                        class="simple-select disabled:bg-gray-200" v-model="employee.payment_method_id"
                                        :disabled="see_disabled" required>
                                        <option key="0" :value="null" selected disabled>Selecione um método de pagamento
                                        </option>
                                        <option v-for="payment_method in payment_methods_list" :key="payment_method.id"
                                            :value="payment_method.id">{{ payment_method.name }}</option>
                                    </select>
                                    <p v-if="employee.errors.payment_method_id" class="text-red-500 text-sm">{{
                                        employee.errors.payment_method_id }}</p>
                                </div>
                            </div>

                            <div v-if="enable_account_data"
                                class="sm:col-span-6 sm:grid sm:grid-cols-2 md:grid-cols-4 gap-1">

                                <div class="sm:col-span-2 md:col-span-4 mb-5">
                                    <label for="employee-account-type"
                                        class="text-sm font-medium leading-6 text-gray-900 required-input-label">Tipo de
                                        conta</label>
                                    <div class="mt-2">
                                        <select name="employee-account-type" id="employee-account-type"
                                            class="simple-select disabled:bg-gray-200"
                                            v-model="employee.account_type_id" :disabled="see_disabled" required>
                                            <option key="0" :value="null" selected disabled>Selecione um tipo de conta
                                            </option>
                                            <option v-for="account_type in account_types_list" :key="account_type.id"
                                                :value="account_type.id">{{ account_type.name }}</option>
                                        </select>
                                        <p v-if="employee.errors.account_type" class="text-red-500 text-sm">{{
                                            employee.errors.account_type }}</p>
                                    </div>
                                </div>

                                <div>
                                    <label for="employee-bank"
                                        class="text-sm font-medium leading-6 text-gray-900 required-input-label">Banco</label>
                                    <div class="mt-2">
                                        <select name="employee-bank" id="employee-bank"
                                            class="simple-select disabled:bg-gray-200" v-model="employee.bank_id"
                                            :disabled="see_disabled" required>
                                            <option key="0" :value="null" selected disabled>Selecione um banco</option>
                                            <option v-for="bank in banks_list" :key="bank.id" :value="bank.id">{{
                                                bank.code }} - {{
                                                bank.name }}</option>
                                        </select>
                                        <p v-if="employee.errors.bank_id" class="text-red-500 text-sm">{{
                                            employee.errors.bank_id }}</p>
                                    </div>
                                </div>
                                <div>
                                    <label for="employee-bank-ag"
                                        class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Agência</label>
                                    <div class="mt-2">
                                        <input type="text" min="4" maxlength="4" name="employee-bank-ag"
                                            id="employee-bank-ag" autocomplete="on"
                                            class="simple-input disabled:bg-gray-200" placeholder="Sem dígito"
                                            :value="formatted_bank_ag" @input="inputAgency" :disabled="see_disabled"
                                            required>
                                        <p v-if="employee.errors.bank_ag" class="text-red-500 text-sm">{{
                                            employee.errors.bank_ag }}</p>
                                    </div>
                                </div>

                                <div>
                                    <label for="employee-account-number"
                                        class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Com
                                        dígito</label>
                                    <div class="mt-2">
                                        <input type="text" minlength="11" maxlength="12" name="employee-account-number"
                                            id="employee-account-number" autocomplete="on"
                                            class="simple-input disabled:bg-gray-200" placeholder="Com dígito"
                                            :value="formatted_account_number" @input="inputBankAccount"
                                            :disabled="see_disabled" required>
                                        <p v-if="employee.errors.account_number" class="text-red-500 text-sm">{{
                                            employee.errors.account_number }}</p>
                                    </div>
                                </div>

                                <div>
                                    <label for="employee-pix-cpf"
                                        class="block text-sm font-medium leading-6 text-gray-900 required-input-label">CPF</label>
                                    <div class="mt-2">
                                        <input type="text" minlength="11" maxlength="14" name="employee-pix-cpf"
                                            id="employee-pix-cpf" autocomplete="on"
                                            class="simple-input disabled:bg-gray-200" placeholder="CPF"
                                            :value="formatted_pix_cpf" @input="inputCpf" :disabled="see_disabled"
                                            required>
                                        <p v-if="employee.errors.pix_cpf" class="text-red-500 text-sm">{{
                                            employee.errors.pix_cpf }}</p>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="sm:col-span-6">
                                <div v-if="employee.payment_method_id === 3">
                                    <label for="employee-pix-email"
                                        class="block text-sm font-medium leading-6 text-gray-900 required-input-label">E-mail</label>
                                    <div class="mt-2">
                                        <input type="text" name="employee-pix-email" id="employee-pix-email"
                                            autocomplete="on" class="simple-input disabled:bg-gray-200"
                                            placeholder="E-mail" v-model="employee.pix_email" :disabled="see_disabled"
                                            required>
                                        <p v-if="employee.errors.pix_email" class="text-red-500 text-sm">{{
                                            employee.errors.pix_email }}</p>
                                    </div>
                                </div>
                                <div v-if="employee.payment_method_id === 4">
                                    <label for="employee-pix-cpf"
                                        class="block text-sm font-medium leading-6 text-gray-900 required-input-label">CPF</label>
                                    <div class="mt-2">
                                        <input type="text" minlength="11" maxlength="14" name="employee-pix-cpf"
                                            id="employee-pix-cpf" autocomplete="on"
                                            class="simple-input disabled:bg-gray-200" placeholder="CPF"
                                            :value="formatted_pix_cpf" @input="inputCpf" :disabled="see_disabled"
                                            required>
                                        <p v-if="employee.errors.pix_cpf" class="text-red-500 text-sm">{{
                                            employee.errors.pix_cpf }}</p>
                                    </div>
                                </div>
                                <div v-if="employee.payment_method_id === 5">
                                    <label for="employee-pix-phone-number"
                                        class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Número
                                        de telefone</label>
                                    <div class="mt-2">
                                        <input type="text" minlength="11" maxlength="16"
                                            name="employee-pix-phone-number" id="employee-pix-phone-number"
                                            autocomplete="on" class="simple-input disabled:bg-gray-200"
                                            placeholder="Número de telefone" :value="formatted_pix_phone_number"
                                            @input="inputPixPhoneNumber" :disabled="see_disabled" required>
                                        <p v-if="employee.errors.pix_phone_number" class="text-red-500 text-sm">{{
                                            employee.errors.pix_phone_number }}</p>
                                    </div>
                                </div>
                                <div v-if="employee.payment_method_id === 7">
                                    <label for="employee-pix-token"
                                        class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Token</label>
                                    <div class="mt-2">
                                        <input type="text" name="employee-pix-token" id="employee-pix-token"
                                            autocomplete="on" class="simple-input disabled:bg-gray-200"
                                            placeholder="Token" v-model="employee.pix_token" :disabled="see_disabled"
                                            required>
                                        <p v-if="employee.errors.pix_token" class="text-red-500 text-sm">{{
                                            employee.errors.pix_token }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- SEÇÃO DE DADOS DE PAGAMENTO DE HORAS EXTRAS -->
                    <section class="py-4">
                        <h2 class="text-base font-semibold leading-7 text-gray-900">Dados de Pagamento de Horas Extras
                        </h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">As horas extras precisam ser pagas via PIX,
                            então precisamos de pelo menos 1 pix para pagamento. Essa informação será utilizada na
                            página de Cálculo de Horas Extras.</p>

                        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-6">

                            <div class="sm:col-span-6">
                                <label for="employee-payment-method"
                                    class="text-sm font-medium leading-6 text-gray-900 required-input-label">PIX</label>
                                <div class="mt-2">
                                    <select name="employee-payment-method" id="employee-payment-method"
                                        class="simple-select disabled:bg-gray-200"
                                        v-model="employee.overtime_payment_method_id" :disabled="see_disabled" required>
                                        <option key="0" :value="null" selected disabled>Selecione um método PIX
                                        </option>
                                        <option
                                            v-for="payment_method in payment_methods_list.filter(el => [3, 4, 5, 7].includes(el.id))"
                                            :key="payment_method.id" :value="payment_method.id">{{ payment_method.name
                                            }}</option>
                                    </select>
                                    <p v-if="employee.errors.overtime_payment_method_id" class="text-red-500 text-sm">{{
                                        employee.errors.overtime_payment_method_id }}</p>
                                </div>
                            </div>

                            <div v-if="enable_overtime_account_data" class="sm:col-span-6">
                                <div v-if="employee.overtime_payment_method_id === 3">
                                    <label for="employee-overtime-pix-email"
                                        class="block text-sm font-medium leading-6 text-gray-900 required-input-label">E-mail</label>
                                    <div class="mt-2">
                                        <input type="text" name="employee-overtime-pix-email"
                                            id="employee-overtime-pix-email" autocomplete="on"
                                            class="simple-input disabled:bg-gray-200" placeholder="E-mail"
                                            v-model="employee.pix_email" :disabled="see_disabled" required>
                                        <p v-if="employee.errors.pix_email" class="text-red-500 text-sm">{{
                                            employee.errors.pix_email }}</p>
                                    </div>
                                </div>
                                <div v-if="employee.overtime_payment_method_id === 4">
                                    <label for="employee-overtime-pix-cpf"
                                        class="block text-sm font-medium leading-6 text-gray-900 required-input-label">CPF</label>
                                    <div class="mt-2">
                                        <input type="text" minlength="11" maxlength="14"
                                            name="employee-overtime-pix-cpf" id="employee-overtime-pix-cpf"
                                            autocomplete="on" class="simple-input disabled:bg-gray-200"
                                            placeholder="CPF" :value="formatted_pix_cpf" @input="inputCpf"
                                            :disabled="see_disabled" required>
                                        <p v-if="employee.errors.pix_cpf" class="text-red-500 text-sm">{{
                                            employee.errors.pix_cpf }}</p>
                                    </div>
                                </div>
                                <div v-if="employee.overtime_payment_method_id === 5">
                                    <label for="employee-overtime-pix-phone-number"
                                        class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Número
                                        de telefone</label>
                                    <div class="mt-2">
                                        <input type="text" minlength="11" maxlength="16"
                                            name="employee-overtime-pix-phone-number"
                                            id="employee-overtime-pix-phone-number" autocomplete="on"
                                            class="simple-input disabled:bg-gray-200" placeholder="Número de telefone"
                                            :value="formatted_pix_phone_number" @input="inputPixPhoneNumber"
                                            :disabled="see_disabled" required>
                                        <p v-if="employee.errors.pix_phone_number" class="text-red-500 text-sm">{{
                                            employee.errors.pix_phone_number }}</p>
                                    </div>
                                </div>
                                <div v-if="employee.overtime_payment_method_id === 7">
                                    <label for="employee-overtime-pix-token"
                                        class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Token</label>
                                    <div class="mt-2">
                                        <input type="text" name="employee-overtime-pix-token"
                                            id="employee-overtime-pix-token" autocomplete="on"
                                            class="simple-input disabled:bg-gray-200" placeholder="Token"
                                            v-model="employee.pix_token" :disabled="see_disabled" required>
                                        <p v-if="employee.errors.pix_token" class="text-red-500 text-sm">{{
                                            employee.errors.pix_token }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </form>
        </template>

        <template #footer>
            <SecondaryButton v-if="modal.mode !== 'see'" :class="{ 'disabled': employee.processing }" @click="close()">
                Cancelar
            </SecondaryButton>
            <PrimaryButton :class="{ 'disabled': (employee.processing || !enableSubmit) }"
                :disabled="(employee.processing || !enableSubmit)" @click="submit()">{{
                modal.primary_button_txt
                }}</PrimaryButton>
        </template>
    </CreateUpdateModal>
</template>