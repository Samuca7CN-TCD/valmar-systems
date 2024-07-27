<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import CreateUpdateEmployeesModal from '@/Components/Employees/CreateUpdateEmployeesModal.vue'
import FloatButton from '@/Components/FloatButton.vue'
import ExtraOptionsButton from '@/Components/ExtraOptionsButton.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { ArrowDownIcon, ArrowsUpDownIcon, ChevronDownIcon, DocumentDuplicateIcon, EyeIcon, MagnifyingGlassIcon, PencilIcon, XCircleIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import { toMoney, formatDate, formatPhoneNumber, calcDeadlineDays } from '@/general.js'
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
    employees_list: Array,
    account_types_list: Array,
    payment_methods_list: Array,
    banks_list: Array,
    total_fifty_percent_value: Number,
    total_hundred_percent_value: Number,
})

const $toast = useToast();

const employees = ref(JSON.parse(JSON.stringify(props.employees_list)));
const original_data = JSON.parse(JSON.stringify(props.employees_list));
const check_mode = ref(false);
const show_fifty = ref(true)
const show_hundred = ref(true)
const show_disabled_employees = ref(false)

const resetTable = () => {
    $toast.clear();
    employees.value = original_data
    $toast.success(`Tabela resetada com sucesso!`);
}

const toogleCheckRows = () => {
    employees.value.forEach(el => hideEmployee(el, check_mode.value))
    check_mode.value = !check_mode.value
}

const getPixType = (codigo) => {
    switch (codigo) {
        case 'pix_email': return "E-mail"
        case 'pix_cpf': return "Cpf"
        case 'pix_phone_number': return "Telefone"
        case 'pix_token': return "Token"
    }
    return '-'
}

const copyToClipboard = (employee) => {
    let text = employee[employee.overtime_payment_method.cod];
    // Verifica se a API Clipboard está disponível
    $toast.clear();
    if (navigator.clipboard && navigator.clipboard.writeText) {
        return navigator.clipboard.writeText(text)
            .then(() => {
                $toast.success(`PIX de ${employee.name} copiado para a área de transferência`);
                console.log('Texto copiado para a área de transferência');
            })
            .catch(err => {
                $toast.error(`Não foi possível copiar o PIX de ${employee.name} para a área de transferência`);
                console.error('Erro ao copiar texto para a área de transferência', err);
            });
    } else {
        // Fallback para navegadores que não suportam a API Clipboard
        const textArea = document.createElement("textarea");
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        try {
            document.execCommand('copy');
            $toast.success(`PIX de ${employee.name} copiado para a área de transferência`);
            console.log('Texto copiado para a área de transferência');
        } catch (err) {
            $toast.error(`Não foi possível copiar o PIX de ${employee.name} para a área de transferência`);
            console.error('Erro ao copiar texto para a área de transferência', err);
        }

        document.body.removeChild(textArea);
    }
}

const applyToEntireCol = (value, camp_name) => {
    employees.value.forEach(employee => {
        employee[camp_name] = value
    })
}

const totalFiftyPercentValue = ref(props.total_fifty_percent_value);
const totalHundredPercentValue = ref(props.total_hundred_percent_value);

const parseTime = (timeStr) => {
    const [hours, minutes] = timeStr.split(':').map(Number);
    return hours * 60 + minutes; // Retorna o tempo em minutos
};

const calcTotalTime = (employee) => {
    if (!employee.check_in_time || !employee.leave_for_lunch_break || !employee.check_in_after_lunch_break || !employee.check_out_time) {
        employee.total_time = 0;
        employee.fifty_percent_value = 0;
        employee.hundred_percent_value = 0;
        return 0;
    }

    const check_in_time = parseTime(employee.check_in_time);
    const leave_for_lunch_break = parseTime(employee.leave_for_lunch_break);
    const check_in_after_lunch_break = parseTime(employee.check_in_after_lunch_break);
    const check_out_time = parseTime(employee.check_out_time);

    const morningMinutes = leave_for_lunch_break - check_in_time;
    const afternoonMinutes = check_out_time - check_in_after_lunch_break;

    const totalMinutes = morningMinutes + afternoonMinutes;
    const totalHours = totalMinutes / 60;

    employee.total_time = totalHours.toFixed(2);
    employee.fifty_percent_value = calcTotalValue(employee, 0.5);
    employee.hundred_percent_value = calcTotalValue(employee, 1);

    updateTotals(); // Atualiza os totais globalmente após as mudanças

    return totalHours.toFixed(2);
};

const calcTotalValue = (employee, percent) => {
    const total_time = parseFloat(employee.total_time) || 0;
    const hourly_rate = employee.salary / 220;
    const percent_rate = 1 + percent;
    return (hourly_rate * total_time) * percent_rate;
};

const updateTotals = () => {
    // Recalcula os totais a partir da lista de funcionários
    let newTotalFiftyPercentValue = 0;
    let newTotalHundredPercentValue = 0;

    employees.value.forEach(employee => {
        newTotalFiftyPercentValue += employee.fifty_percent_value || 0;
        newTotalHundredPercentValue += employee.hundred_percent_value || 0;
    });

    totalFiftyPercentValue.value = newTotalFiftyPercentValue;
    totalHundredPercentValue.value = newTotalHundredPercentValue;
};

const hideEmployee = (employee, mode = null) => {
    const total_time = 8.75;
    const hourly_rate = employee.salary / 220;
    const fifty_percent_value = (hourly_rate * total_time) * 1.5;
    const hundred_percent_value = (hourly_rate * total_time) * 2;

    if (mode) employee.show = !mode;

    if (employee.show) {
        // Remove current values from totals
        totalFiftyPercentValue.value -= employee.fifty_percent_value || 0;
        totalHundredPercentValue.value -= employee.hundred_percent_value || 0;

        // Hide employee and reset values
        employee.show = false;
        employee.check_in_time = null;
        employee.leave_for_lunch_break = null;
        employee.check_in_after_lunch_break = null;
        employee.check_out_time = null;
        employee.total_time = 0;
        employee.fifty_percent_value = 0;
        employee.hundred_percent_value = 0;

        updateTotals(); // Atualiza os totais após a remoção do funcionário
    } else {
        // Show employee and set values
        employee.show = true;
        employee.check_in_time = "07:00";
        employee.leave_for_lunch_break = "12:00";
        employee.check_in_after_lunch_break = "13:15";
        employee.check_out_time = "17:00";
        employee.total_time = total_time;
        employee.fifty_percent_value = fifty_percent_value;
        employee.hundred_percent_value = hundred_percent_value;

        // Add new values to totals
        totalFiftyPercentValue.value += fifty_percent_value;
        totalHundredPercentValue.value += hundred_percent_value;

        updateTotals(); // Atualiza os totais após a adição do funcionário
    }
}

const computedTotals = computed(() => ({
    totalFiftyPercentValue: totalFiftyPercentValue.value,
    totalHundredPercentValue: totalHundredPercentValue.value
}));


</script>
<template>

    <Head :title="page.name" />
    <AppLayout :page="page" :page_options="page_options">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ page.name }}
            </h2>
        </template>

        <div class="pt-6 print:hidden print:pt-0">
            <div class="w-fit max-w-7xl mx-auto sm:px-6 lg:px-8 print:w-full">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                    <div class="flex flex-col items-center justify-center align-middle gap-5">
                        <h2 class="text-green-700 font-extrabold text-2xl">Controles da Tabela</h2>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" v-model="show_disabled_employees" class="sr-only peer">
                            <div
                                class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer  peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-green-600">
                            </div>
                            <span class="ms-3 text-sm font-medium text-gray-900">Mostrar funcionário
                                omitidos</span>
                        </label>

                        <button class="p-2 rounded-md bg-green-700 hover:bg-green-800 active:bg-green-900 text-white"
                            @click="resetTable">Resetar
                            Tabela</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-6 print:py-6">
            <div class="max-w-full mx-auto print:max-w-full">
                <div class="px-0 print:px-0">
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg print:shadow-none">
                        <div class="flex flex-col">
                            <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
                                <div class="inline-block min-w-full sm:px-6 lg:px-8">
                                    <div class="overflow-hidden">
                                        <table class="min-w-full text-left text-sm font-light">
                                            <thead class="border-b font-medium">
                                                <tr class="bg-neutral-200">
                                                    <th scope="col" class="px-6 py-4 text-center print:hidden">
                                                        Mostrar
                                                        <span class="print:hidden">
                                                            <button
                                                                class="text-green-700 hover:text-green-800 active:text-green-900 text-xs"
                                                                @click="toogleCheckRows">Alternar</button>
                                                        </span>

                                                    </th>
                                                    <th scope="col" class="px-6 py-4 text-center">Funcionário</th>
                                                    <th scope="col" class="px-6 py-4 text-center">Salário</th>
                                                    <th scope="col" class="px-6 py-4 text-center print:hidden"
                                                        title="Início do expediente">
                                                        H. Entrada</th>
                                                    <th scope="col" class="px-6 py-4 text-center print:hidden"
                                                        title="Saída para o horário de Almoço">S. Almoço</th>
                                                    <th scope="col" class="px-6 py-4 text-center print:hidden"
                                                        title="Entrada da volta do horário de Almoço">E. Almoço</th>
                                                    <th scope="col" class="px-6 py-4 text-center print:hidden"
                                                        title="Saída do expediente">H.
                                                        Saída</th>
                                                    <th scope="col" class="px-6 py-4 text-center"
                                                        title="Total de horas do Expediente">Total Horas</th>
                                                    <th scope="col" class="px-6 py-4 text-center"
                                                        :class="{ 'opacity-50 print:hidden': !show_fifty }">
                                                        Valor 50%
                                                        <span class="print:hidden">
                                                            <input type="checkbox"
                                                                class="focus:bg-green-700 focus:ring-green-700 text-green-700 border-neutral-500 rounded-sm"
                                                                v-model="show_fifty" />
                                                        </span>
                                                    </th>
                                                    <th scope="col" class="px-6 py-4 text-center"
                                                        :class="{ 'opacity-50 print:hidden': !show_hundred }">
                                                        Valor 100%
                                                        <span class="print:hidden">
                                                            <input type="checkbox"
                                                                class="focus:bg-green-700 focus:ring-green-700 text-green-700 border-neutral-500 rounded-sm"
                                                                v-model="show_hundred" />
                                                        </span>
                                                    </th>
                                                    <th scope="col" class="px-6 py-4 text-center" colspan="2">
                                                        Pix</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-if="employees.length" v-for="employee in employees"
                                                    class="border-b transition duration-100 hover:bg-neutral-200 print:break-inside-avoid text-sm print:text-md "
                                                    :class="{ 'opacity-50 print:hidden': !employee.show, 'hidden': !employee.show && !show_disabled_employees }">
                                                    <td
                                                        class="whitespace-nowrap py-1 text-center font-medium print:hidden">
                                                        <input type="checkbox"
                                                            class=" focus:bg-green-700 focus:ring-green-700 text-green-700 border-neutral-500 rounded-sm"
                                                            :value="employee.show" @change="hideEmployee(employee)"
                                                            :checked="employee.show" />
                                                    </td>
                                                    <td class="py-4 text-center font-medium">
                                                        {{ employee.name + ' ' + employee.surname }}</td>
                                                    <td class="whitespace-nowrap py-1 text-center font-medium">{{
        toMoney(employee.salary || 0) }}
                                                    </td>
                                                    <td
                                                        class="whitespace-nowrap py-1 text-center font-medium print:hidden relative">
                                                        <button
                                                            class="absolute left-0 top-1/2 transform -translate-y-1/2 text-neutral-300 hover:text-neutral-500 active:text-neutral-700 ml-3"
                                                            @click="applyToEntireCol(employee.check_in_time, 'check_in_time')">
                                                            <ArrowsUpDownIcon class="w-3 h-3" />
                                                        </button>
                                                        <input type="time"
                                                            class="focus:ring-green-700 simple-input pl-8"
                                                            :disabled="!employee.show"
                                                            v-model="employee.check_in_time" />
                                                    </td>
                                                    <td
                                                        class="whitespace-nowrap py-1 text-center font-medium print:hidden relative">
                                                        <button
                                                            class="absolute left-0 top-1/2 transform -translate-y-1/2 text-neutral-300 hover:text-neutral-500 active:text-neutral-700 ml-3"
                                                            @click="applyToEntireCol(employee.leave_for_lunch_break, 'leave_for_lunch_break')">
                                                            <ArrowsUpDownIcon class="w-3 h-3" />
                                                        </button>
                                                        <input type="time"
                                                            class="focus:ring-green-700 simple-input pl-8"
                                                            :disabled="!employee.show"
                                                            v-model="employee.leave_for_lunch_break" />
                                                    </td>
                                                    <td
                                                        class="whitespace-nowrap py-1 text-center font-medium print:hidden relative">
                                                        <button
                                                            class="absolute left-0 top-1/2 transform -translate-y-1/2 text-neutral-300 hover:text-neutral-500 active:text-neutral-700 ml-3"
                                                            @click="applyToEntireCol(employee.check_in_after_lunch_break, 'check_in_after_lunch_break')">
                                                            <ArrowsUpDownIcon class="w-3 h-3" />
                                                        </button>
                                                        <input type="time"
                                                            class="focus:ring-green-700 simple-input pl-8"
                                                            :disabled="!employee.show"
                                                            v-model="employee.check_in_after_lunch_break" />
                                                    </td>
                                                    <td
                                                        class="whitespace-nowrap py-1 text-center font-medium print:hidden relative">
                                                        <button
                                                            class="absolute left-0 top-1/2 transform -translate-y-1/2 text-neutral-300 hover:text-neutral-500 active:text-neutral-700 ml-3"
                                                            @click="applyToEntireCol(employee.check_out_time, 'check_out_time')">
                                                            <ArrowsUpDownIcon class="w-3 h-3" />
                                                        </button>
                                                        <input type="time"
                                                            class="focus:ring-green-700 simple-input pl-8"
                                                            :disabled="!employee.show"
                                                            v-model="employee.check_out_time" />
                                                    </td>

                                                    <td class="whitespace-nowrap py-1 text-center font-medium">
                                                        {{ calcTotalTime(employee) }}
                                                    </td>
                                                    <td class="whitespace-nowrap py-1 text-center font-medium bg-neutral-50"
                                                        :class="{ 'opacity-50 print:hidden': !show_fifty }">
                                                        {{ toMoney(employee.fifty_percent_value || 0) }}</td>
                                                    <td class="whitespace-nowrap py-1 text-center font-medium bg-neutral-100"
                                                        :class="{ 'opacity-50 print:hidden': !show_hundred }">
                                                        {{ toMoney(employee.hundred_percent_value || 0) }}</td>
                                                    <td class="whitespace-nowrap py-1 text-center font-medium hover:text-green-600 active:text-green-700 cursor-pointer"
                                                        title="Clique para copiar PIX para área de transferência"
                                                        @click="employee.overtime_payment_method?.cod ? copyToClipboard(employee) : null">
                                                        <span
                                                            class="flex flex-row justify-center items-center align-middle gap-3">
                                                            {{ employee.overtime_payment_method?.cod ?
        employee[employee.overtime_payment_method.cod]
        : '-' }}
                                                            <DocumentDuplicateIcon
                                                                v-if="employee.overtime_payment_method?.cod"
                                                                class="w-4 h-4 print:hidden" />
                                                        </span>
                                                    </td>
                                                    <td
                                                        class="whitespace-nowrap py-1 text-center font-medium uppercase">
                                                        {{ getPixType(employee.overtime_payment_method?.cod || '-') }}
                                                    </td>
                                                </tr>
                                                <tr v-if="employees.length"
                                                    class="border-b transition duration-300 ease-in-out print:break-inside-avoid text-sm print:text-md bg-neutral-200">
                                                    <td class="print:hidden"></td>
                                                    <td colspan="2"></td>
                                                    <td class="print:hidden" colspan="4"></td>
                                                    <td class="whitespace-nowrap py-1 font-medium text-center">
                                                        Total
                                                    </td>
                                                    <td class="whitespace-nowrap py-1 text-center font-medium"
                                                        :class="{ 'opacity-50 print:hidden': !show_fifty }">
                                                        {{ toMoney(computedTotals.totalFiftyPercentValue || 0) }}</td>
                                                    <td class="whitespace-nowrap py-1 text-center font-medium"
                                                        :class="{ 'opacity-50 print:hidden': !show_hundred }">
                                                        {{ toMoney(computedTotals.totalHundredPercentValue || 0) }}</td>
                                                    <td class="whitespace-nowrap py-1 text-center font-medium"
                                                        colspan="2">
                                                    </td>
                                                </tr>
                                                <tr v-else
                                                    class="transition duration-300 ease-in-out hover:bg-neutral-100 print:break-inside-avoid">
                                                    <td class="whitespace-nowrap px-6 py-1 text-center" colspan="9">Não
                                                        há
                                                        funcionários cadastradas no momento!</td>
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


        <ExtraOptionsButton :mode="['rollup', 'print_page']" />
    </AppLayout>
</template>