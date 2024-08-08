<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import SeeOldOvertimeCalculations from '@/Components/Employees/SeeOldOvertimeCalculations.vue'
import FloatButton from '@/Components/FloatButton.vue'
import ExtraOptionsButton from '@/Components/ExtraOptionsButton.vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { ArrowDownIcon, ArrowsUpDownIcon, ChevronDownIcon, DocumentDuplicateIcon, EyeIcon, MagnifyingGlassIcon, PencilIcon, XCircleIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import { toMoney, formatDate, formatPhoneNumber, calcDeadlineDays } from '@/general.js'
import { useToast } from 'vue-toast-notification';
import 'vue-toast-notification/dist/theme-sugar.css';

// =============================================
// Informações exteriores
const props = defineProps({
    page: Object,
    page_mode: String,
    user: Object,
    procedure_date: String,
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
const isModified = ref(false)

const totalFiftyPercentValue = ref(props.total_fifty_percent_value);
const totalHundredPercentValue = ref(props.total_hundred_percent_value);

const resetTable = () => {
    $toast.clear();
    employees.value = original_data
    isModified.value = false;
    $toast.success(`Tabela resetada com sucesso!`);
}

const toggleCheckRows = () => {
    employees.value.forEach(el => hideEmployee(el, check_mode.value))
    check_mode.value = !check_mode.value
    isModified.value = true;
}

const disable_all = computed(() => {
    return props.page_mode === 'old';
})

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
    if (!employees || !employees.value || !Array.isArray(employees.value)) {
        console.error('As informações dos funcionários não estão devidamente formatadas.');
        return;
    }

    employees.value.forEach(employee => {
        if (employee.show) {
            console.log("SHOW: ", employee.show)
            employee[camp_name] = value;
            isModified.value = true;
        }
    });
};

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

    if (mode !== null) employee.show = !mode;

    if (employee.show) {
        // Remove current values from totals
        totalFiftyPercentValue.value -= employee.fifty_percent_value || 0;
        totalHundredPercentValue.value -= employee.hundred_percent_value || 0;

        // Hide employee and reset values
        if (mode !== null) employee.show = mode;
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
        if (mode !== null) employee.show = mode;
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
    if (mode === null) employee.show = !employee.show;
    isModified.value = true;
}

const computedTotals = computed(() => ({
    totalFiftyPercentValue: totalFiftyPercentValue.value,
    totalHundredPercentValue: totalHundredPercentValue.value
}));

const modal = ref(false);
const openModal = () => {
    modal.value = true;
}

const closeModal = () => {
    modal.value = false;
}

const set_last_records = async () => {
    console.log("KKK")
    if (!isModified.value) return;
    console.log("hihihi")
    if (props.page_mode === 'old') return;
    console.log("rsrsrs")
    router.post(route('employee.overtime_calculation_save'), { employees: JSON.stringify(employees.value) }, {
        preserveScroll: true,
        onSuccess: () => console.log("Dados salvos com sucesso"),
        onError: (error) => console.log("Erro ao salvar dados: ", error),
        onFinish: () => isModified.value = false
    });
}

// Executa set_last_records a cada 1 minuto
let intervalId = null;
const startAutoSave = () => {
    intervalId = setInterval(set_last_records, 10000); // 60000 ms = 1 minuto
};

const stopAutoSave = () => {
    if (intervalId) {
        clearInterval(intervalId);
    }
};

const handleBeforeUnload = (event) => {
    // Faz o save se o usuário estiver saindo da página
    set_last_records();
    // Personalize a mensagem do navegador
    // event.preventDefault();
    // event.returnValue = '';
};

onMounted(() => {
    startAutoSave();
    window.addEventListener('beforeunload', handleBeforeUnload);
});

onUnmounted(() => {
    stopAutoSave();
    window.removeEventListener('beforeunload', handleBeforeUnload);
});

</script>
<template>

    <Head :title="page.name" />
    <AppLayout :page="page" :page_options="page_options">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex justify-between">
                Cálculo de Horas Extras
                <div v-if="user" class="text-right">
                    <p class="text-sm text-neutral-500">{{ user.name }} {{ user.surname }}</p>
                    <p class="text-xs text-neutral-400">{{ formatDate(procedure_date) }}</p>
                </div>
            </h2>
        </template>

        <div class="pt-6 print:hidden">
            <div class="w-fit max-w-7xl mx-auto sm:px-6 lg:px-8 print:w-full">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                    <div class="flex flex-col items-center justify-center align-middle gap-5">
                        <h2 class="text-green-700 font-extrabold text-2xl">Controles da Tabela</h2>
                        <div v-if="!disable_all" class="flex flex-col items-center justify-center align-middle gap-5">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" :disabled="disable_all" v-model="show_disabled_employees"
                                    class="sr-only peer disabled:opacity-50">
                                <div
                                    class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer  peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600">
                                </div>
                                <span class="ms-3 text-sm font-medium text-gray-900">Mostrar funcionário
                                    omitidos</span>
                            </label>

                            <button
                                class="p-2 rounded-md bg-green-700 hover:bg-green-800 active:bg-green-900 text-white  disabled:opacity-50"
                                @click="resetTable" :disabled="disable_all">Resetar
                                Tabela</button>
                        </div>
                        <button
                            class="text-xs text-green-600 hover:text-green-800 active:text-green-950 select-none  disabled:opacity-50"
                            @click="openModal">Ver
                            registros anteriores</button>
                        <a v-if="disable_all" :href="route('employee.overtime_calculation')"
                            class="text-xs text-orange-500 hover:text-orange-700 active:text-orange-900 select-none  disabled:opacity-50">Voltar
                            para tabela normal</a>
                        <div v-else>
                            <p v-if="isModified" class="text-xs text-red-300">Existem alterações não salvas (aguarde até
                                10
                                segundos)</p>
                            <p v-else class="text-xs text-neutral-500">Todas as alteraçẽos estão salvas</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-6 print:py-0">
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
                                                    <th v-if="!disable_all" scope="col"
                                                        class="px-6 py-4 print:px-3 print:py-2 text-center print:hidden">
                                                        Mostrar
                                                        <span class="print:hidden">
                                                            <button
                                                                class="text-green-700 hover:text-green-800 active:text-green-900 text-xs disabled:opacity-50"
                                                                @click="toggleCheckRows"
                                                                :disabled="disable_all">Alternar</button>
                                                        </span>

                                                    </th>
                                                    <th scope="col" class="px-6 py-4 print:px-3 print:py-2 text-center">
                                                        Funcionário
                                                    </th>
                                                    <th scope="col"
                                                        class="px-6 py-4 print:px-3 print:py-2 text-center print:hidden">
                                                        Salário
                                                    </th>
                                                    <th scope="col"
                                                        class="px-6 py-4 print:px-3 print:py-2 text-center print:hidden"
                                                        title="Início do expediente">
                                                        H. Entrada</th>
                                                    <th scope="col"
                                                        class="px-6 py-4 print:px-3 print:py-2 text-center print:hidden"
                                                        title="Saída para o horário de Almoço">S. Almoço</th>
                                                    <th scope="col"
                                                        class="px-6 py-4 print:px-3 print:py-2 text-center print:hidden"
                                                        title="Entrada da volta do horário de Almoço">E. Almoço</th>
                                                    <th scope="col"
                                                        class="px-6 py-4 print:px-3 print:py-2 text-center print:hidden"
                                                        title="Saída do expediente">H.
                                                        Saída</th>
                                                    <th scope="col"
                                                        class="px-6 py-4 print:px-3 print:py-2 text-center print:hidden"
                                                        title="Total de horas do Expediente">Total Horas</th>
                                                    <th scope="col" class="px-6 py-4 print:px-3 print:py-2 text-center"
                                                        :class="{ 'opacity-50 print:hidden': !show_fifty }">
                                                        Valor 50%
                                                        <span class="print:hidden">
                                                            <input type="checkbox"
                                                                class="focus:bg-green-700 focus:ring-green-700 text-green-700 border-neutral-500 rounded-sm disabled:opacity-50"
                                                                v-model="show_fifty" />
                                                        </span>
                                                    </th>
                                                    <th scope="col" class="px-6 py-4 print:px-3 print:py-2 text-center"
                                                        :class="{ 'opacity-50 print:hidden': !show_hundred }">
                                                        Valor 100%
                                                        <span class="print:hidden">
                                                            <input type="checkbox"
                                                                class="focus:bg-green-700 focus:ring-green-700 text-green-700 border-neutral-500 rounded-sm disabled:opacity-50"
                                                                v-model="show_hundred" />
                                                        </span>
                                                    </th>
                                                    <th scope="col" class="px-6 py-4 print:px-3 print:py-2 text-center"
                                                        colspan="2">
                                                        Pix</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-if="employees.length" v-for="employee in employees"
                                                    class="border-b transition duration-100 hover:bg-neutral-100 print:break-inside-avoid text-sm print:text-md "
                                                    :class="{ 'opacity-50 print:hidden': !employee.show, 'hidden': !employee.show && !show_disabled_employees }">
                                                    <td v-if="!disable_all"
                                                        class="whitespace-nowrap py-1 print:p-0 text-center font-medium print:hidden">
                                                        <input type="checkbox"
                                                            class=" focus:bg-green-700 focus:ring-green-700 text-green-700 border-neutral-500 rounded-sm disabled:opacity-50"
                                                            :value="employee.show" @change="hideEmployee(employee)"
                                                            :checked="employee.show" :disabled="disable_all" />
                                                    </td>
                                                    <td class="py-4 text-center font-medium">
                                                        {{ employee.name + ' ' + employee.surname }}</td>
                                                    <td
                                                        class="whitespace-nowrap py-1 print:p-0 text-center font-medium  print:hidden">
                                                        {{
        toMoney(employee.salary || 0) }}
                                                    </td>
                                                    <td
                                                        class="whitespace-nowrap py-1 print:p-0 text-center font-medium print:hidden relative">
                                                        <button
                                                            class="absolute left-0 top-1/2 transform -translate-y-1/2 text-neutral-300 hover:text-neutral-500 active:text-neutral-700 ml-3 disabled:opacity-50"
                                                            @click="applyToEntireCol(employee.check_in_time, 'check_in_time')"
                                                            :disabled="disable_all">
                                                            <ArrowsUpDownIcon class="w-3 h-3" />
                                                        </button>
                                                        <input type="time"
                                                            class="focus:ring-green-700 simple-input pl-8 disabled:opacity-50"
                                                            :disabled="!employee.show || disable_all"
                                                            v-model="employee.check_in_time"
                                                            @input="isModified = true" />
                                                    </td>
                                                    <td
                                                        class="whitespace-nowrap py-1 print:p-0 text-center font-medium print:hidden relative">
                                                        <button
                                                            class="absolute left-0 top-1/2 transform -translate-y-1/2 text-neutral-300 hover:text-neutral-500 active:text-neutral-700 ml-3 disabled:opacity-50"
                                                            @click="applyToEntireCol(employee.leave_for_lunch_break, 'leave_for_lunch_break')"
                                                            :disabled="disable_all">
                                                            <ArrowsUpDownIcon class="w-3 h-3" />
                                                        </button>
                                                        <input type="time"
                                                            class="focus:ring-green-700 simple-input pl-8 disabled:opacity-50"
                                                            :disabled="!employee.show || disable_all"
                                                            v-model="employee.leave_for_lunch_break"
                                                            @input="isModified = true" />
                                                    </td>
                                                    <td
                                                        class="whitespace-nowrap py-1 print:p-0 text-center font-medium print:hidden relative">
                                                        <button
                                                            class="absolute left-0 top-1/2 transform -translate-y-1/2 text-neutral-300 hover:text-neutral-500 active:text-neutral-700 ml-3 disabled:opacity-50"
                                                            @click="applyToEntireCol(employee.check_in_after_lunch_break, 'check_in_after_lunch_break')"
                                                            :disabled="disable_all">
                                                            <ArrowsUpDownIcon class="w-3 h-3" />
                                                        </button>
                                                        <input type="time"
                                                            class="focus:ring-green-700 simple-input pl-8 disabled:opacity-50"
                                                            :disabled="!employee.show || disable_all"
                                                            v-model="employee.check_in_after_lunch_break"
                                                            @input="isModified = true" />
                                                    </td>
                                                    <td
                                                        class="whitespace-nowrap py-1 print:p-0 text-center font-medium print:hidden relative">
                                                        <button
                                                            class="absolute left-0 top-1/2 transform -translate-y-1/2 text-neutral-300 hover:text-neutral-500 active:text-neutral-700 ml-3 disabled:opacity-50"
                                                            @click="applyToEntireCol(employee.check_out_time, 'check_out_time')"
                                                            :disabled="disable_all">
                                                            <ArrowsUpDownIcon class="w-3 h-3" />
                                                        </button>
                                                        <input type="time"
                                                            class="focus:ring-green-700 simple-input pl-8 disabled:opacity-50"
                                                            :disabled="!employee.show || disable_all"
                                                            v-model="employee.check_out_time"
                                                            @input="isModified = true" />
                                                    </td>

                                                    <td
                                                        class="whitespace-nowrap py-1 print:p-0 text-center font-medium print:hidden">
                                                        {{ calcTotalTime(employee) }}
                                                    </td>
                                                    <td class="whitespace-nowrap py-1 print:p-0 text-center font-medium bg-neutral-50"
                                                        :class="{ 'opacity-50 print:hidden': !show_fifty }">
                                                        {{ toMoney(employee.fifty_percent_value || 0) }}</td>
                                                    <td class="whitespace-nowrap py-1 print:p-0 text-center font-medium bg-neutral-100"
                                                        :class="{ 'opacity-50 print:hidden': !show_hundred }">
                                                        {{ toMoney(employee.hundred_percent_value || 0) }}</td>
                                                    <td class="whitespace-nowrap py-1 print:p-0 text-center font-medium hover:text-green-600 active:text-green-700 cursor-pointer"
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
                                                        class="whitespace-nowrap py-1 print:p-0 text-center font-medium uppercase">
                                                        {{ getPixType(employee.overtime_payment_method?.cod || '-') }}
                                                    </td>
                                                </tr>
                                                <tr v-if="employees.length"
                                                    class="border-b transition duration-300 ease-in-out print:break-inside-avoid text-sm print:text-md bg-neutral-200">

                                                    <td v-if="disable_all" class="print:hidden" colspan="6"></td>
                                                    <td v-else class="print:hidden" colspan="7"></td>
                                                    <td
                                                        class="whitespace-nowrap py-1 print:p-0 font-medium text-center">
                                                        Total
                                                    </td>
                                                    <td class="whitespace-nowrap py-1 print:p-0 text-center font-medium"
                                                        :class="{ 'opacity-50 print:hidden': !show_fifty }">
                                                        {{ toMoney(computedTotals.totalFiftyPercentValue || 0) }}</td>
                                                    <td class="whitespace-nowrap py-1 print:p-0 text-center font-medium"
                                                        :class="{ 'opacity-50 print:hidden': !show_hundred }">
                                                        {{ toMoney(computedTotals.totalHundredPercentValue || 0) }}</td>
                                                    <td class="whitespace-nowrap py-1 print:p-0 text-center font-medium"
                                                        colspan="2">
                                                    </td>
                                                </tr>
                                                <tr v-else
                                                    class="transition duration-300 ease-in-out hover:bg-neutral-100 print:break-inside-avoid">
                                                    <td class="whitespace-nowrap px-6 py-1 text-center" colspan="11">Não
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
        <SeeOldOvertimeCalculations :modal="modal" @close="closeModal" />
    </AppLayout>
</template>