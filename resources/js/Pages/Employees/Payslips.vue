<script setup>
    import AppLayout from '@/Layouts/AppLayout.vue'
    import FloatButton from '@/Components/FloatButton.vue'
    import ExtraOptionsButton from '@/Components/ExtraOptionsButton.vue'
    import { Head, useForm, router } from '@inertiajs/vue3'
    import { ref, computed, onMounted } from 'vue'
    import { ChevronLeftIcon, ChevronRightIcon, XMarkIcon, PlusIcon, PencilIcon, DocumentDuplicateIcon, CheckIcon } from '@heroicons/vue/24/outline'
    import { toMoney, absolute, printList } from '@/general.js'
    import { useToast } from 'vue-toast-notification';
    import 'vue-toast-notification/dist/theme-sugar.css';
    import { debounce } from 'lodash';

    const props = defineProps({
        page: Object,
        page_options: Array,
        payslips_list: {
            type: Array,
            default: () => [] // Garantir que o valor padrão seja um array vazio
        },
        employee: {
            type: Object,
            default: null,
        },
        payslip_month: Number,
        payslip_year: Number,
        observations: String,
    })

    const $toast = useToast();

    const new_income = useForm({
        'id': null,
        'employee_id': props.employee.id,
        'description': '',
        'value': 0,
        'detail': true,
        'month': props.payslip_month,
        'year': props.payslip_year,
        'mode': 'in'
    })

    const new_outcome = useForm({
        'id': null,
        'employee_id': props.employee.id,
        'description': '',
        'value': 0,
        'detail': false,
        'month': props.payslip_month,
        'year': props.payslip_year,
        'mode': 'out'
    })

    const today = new Date()
    const current_month = today.getMonth() + 1
    const current_year = today.getFullYear()
    const months_names = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro']
    const payslips = ref([...props.payslips_list]) // Inicialize como um array, se necessário
    const edit_record_id = ref(null);
    const disable_all = ref(false);
    const months = computed(() => Array.from({ length: 12 }, (_, i) => i + 1))
    const payslip_observations = ref(props.observations)
    const payslip_observations_error = ref('')

    const previousMonth = computed(() => {
        if (props.payslip_month === 1) {
            return { month: 12, year: props.payslip_year - 1 }
        }
        return { month: props.payslip_month - 1, year: props.payslip_year }
    })

    const nextMonth = computed(() => {
        if (props.payslip_month === 12) {
            return { month: 1, year: props.payslip_year + 1 }
        }
        return { month: props.payslip_month + 1, year: props.payslip_year }
    })

    const entradas = computed(() => {
        return props.payslips_list.filter(el => el.value > 0);
    });

    const total_entradas = computed(() => {
        return entradas.value.reduce((acc, el) => acc + el.value, 0);
    });

    const saidas = computed(() => {
        return props.payslips_list.filter(el => el.value < 0);
    });

    const total_saidas = computed(() => {
        return saidas.value.reduce((acc, el) => acc + el.value, 0);
    });

    const total_geral = computed(() => {
        return total_entradas.value + total_saidas.value;
    });

    const general_disable = computed(() => {
        return new_income.processing || new_outcome.processing || disable_all.value
    })

    const validateForm = (form) => {
        form.clearErrors()
        if (!form.description) {
            form.setError('description', "Insira uma descrição")
            return false
        }
        if (!form.value || form.value <= 0) {
            form.setError('value', "Insira um valor válido")
            return false
        }
        return true
    }

    const createPayslip = (form, isIncome) => {
        if (!validateForm(form)) return
        form.transform((data) => ({
            ...data,
            value: isIncome ? data.value : -data.value
        })).post(route('payslip.store'), {
            preserveScroll: true,
            onSuccess: (response) => {
                // Adicione o novo payslip à lista de payslips
                console.log("RESPONSE: ", response)
                //payslips.value.push(response.props.payslip);
                form.reset();
                $toast.success("Registro cadastrado com sucesso!")
            },
            onError: (errors) => {
                console.log('Error:', errors)
                $toast.error("Erro ao cadastrar registro! Comunique o técnico!")
            }
        })
    }


    const createIncome = () => createPayslip(new_income, true)
    const createOutcome = () => createPayslip(new_outcome, false)

    const editMode = (id) => {
        if (edit_record_id.value !== null) return;
        const payslip = props.payslips_list.find(el => el.id === id);

        if (payslip.value > 0) {
            new_income.id = payslip.id;
            new_income.employee_id = payslip.employee_id;
            new_income.description = payslip.description;
            new_income.value = payslip.value;
            new_income.detail = payslip.detail;
            new_income.month = payslip.month;
            new_income.year = payslip.year;
        } else {
            new_outcome.id = payslip.id;
            new_outcome.employee_id = payslip.employee_id;
            new_outcome.description = payslip.description;
            new_outcome.value = absolute(payslip.value);
            new_outcome.detail = payslip.detail;
            new_outcome.month = payslip.month;
            new_outcome.year = payslip.year;
        }

        edit_record_id.value = id;
    }

    const cancelEditMode = () => {
        edit_record_id.value = null;
        new_income.reset();
        new_outcome.reset();
    }

    const updatePayslip = (form) => {
        if (!validateForm(form)) return
        if (!confirm(`Tem certeza que deseja alterar o registro para "${form.description}: ${toMoney(absolute(form.value))}"?`)) return;
        // Envia o campo específico que está sendo atualizado
        disable_all.value = true
        form.transform((data) => ({
            ...data,
            value: form.mode === 'in' ? data.value : -data.value
        })).put(route('payslip.update', form.id), {
            onSuccess: () => {
                cancelEditMode();
                $toast.success("Registro atualizado com sucesso!")
            },
            onError: (errors) => {
                console.log('Error:', errors)
            }, onFinish: () => {
                disable_all.value = false
            }
        })
    }

    const updatePayslipDetail = (id, detail) => {
        disable_all.value = true;

        router.put(route('payslip.update_detail', id), {}, {
            preserveScroll: true,
            onError: (errors) => {
                console.log('Error:', errors);
                $toast.error(`Erro ao desabilitar o registro! Tente novamente.`);
            },
            onFinish: () => {
                disable_all.value = false;
            }
        });
    };



    const deletePayslip = (id) => {
        const payslip = props.payslips_list.find(el => el.id === id);
        if (!confirm(`Você tem certeza que deseja EXCLUIR o registro "${payslip.description}: ${toMoney(payslip.value)}"?`)) return;
        disable_all.value = true;
        router.delete(route('payslip.destroy', id), {
            preserveScroll: true,
            onSuccess: () => {
                $toast.success(`Registro deletado com sucesso`);
            },
            onError: (errors) => {
                console.log('Error:', errors)
                $toast.error(`Erro ao deletar registro! Tente novamente.`);
            },
            onFinish: () => {
                disable_all.value = false
                cancelEditMode();
            }
        })
    }

    const debouncedResizeTextarea = debounce((event) => {
        const payslip_observations = event.target.value;

        router.put(route('payslip.observations', {
            employee: props.employee.id,
            month: props.payslip_month,
            year: props.payslip_year
        }), { payslip_observations }, {
            preserveScroll: true,
            onSuccess: () => {
                const textarea = event.target;
                textarea.style.height = 'auto';
                textarea.style.height = `${textarea.scrollHeight}px`;
                payslip_observations_error.value = ''
            }, onError: (error) => {
                console.log(error);
                payslip_observations_error.value = error
            }
        });
    }, 500);

    const resizeTextarea = (event = null) => {
        if (!event) {
            const textarea = document.querySelector("#observations");
            textarea.style.height = 'auto';
            textarea.style.height = `${textarea.scrollHeight}px`;
            return;
        }
        debouncedResizeTextarea(event);
    };
    onMounted(() => {
        resizeTextarea();
    });

</script>

<template>

    <Head :title="page.name" />
    <AppLayout :page="page" :page_options="page_options" :payslip_month="payslip_month" :payslip_year="payslip_year">
        <template #header class="print:hidden">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Holerite | {{ props.employee.name }} {{ props.employee.surname }}
            </h2>
        </template>

        <div class="pt-6 print:hidden print:pt-0">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 print:w-full">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                    <div class="mb-2">
                        <p class="text-neutral-500 text-xs mb-2">Opções de Impressão</p>
                        <div class="flex  gap-2 divide-neutral-400 text-xs text-neutral-700">
                            <a v-if="employee"
                                :href="route('payslip.print', { mode: 'payslip', employee: props.employee.id, month: props.payslip_month, year: props.payslip_year })"
                                class="bg-green-500 hover:bg-green-700 active:bg-green-900 text-white rounded-md px-2">
                                Holerite
                                individual</a>
                            <a :href="route('payslip.print', { mode: 'payslip', employee: 0, month: props.payslip_month, year: props.payslip_year })"
                                class="bg-green-500 hover:bg-green-700 active:bg-green-900 text-white rounded-md px-2">Todos
                                os holerites</a>
                            <a v-if="employee && employee.transportation_voucher"
                                :href="route('payslip.print', { mode: 'transportation-voucher', employee: props.employee.id, month: props.payslip_month, year: props.payslip_year })"
                                class="bg-green-500 hover:bg-green-700 active:bg-green-900 text-white rounded-md px-2">Vale
                                transporte Individual</a>
                            <a v-if="employee.transportation_voucher"
                                :href="route('payslip.print', { mode: 'transportation-voucher', employee: 0, month: props.payslip_month, year: props.payslip_year })"
                                class="bg-green-500 hover:bg-green-700 active:bg-green-900 text-white rounded-md px-2">Todos
                                os vales transporte</a>
                            <a :href="route('payslip.print', { mode: 'control', employee: 0, month: props.payslip_month, year: props.payslip_year })"
                                class="bg-green-500 hover:bg-green-700 active:bg-green-900 text-white rounded-md px-2">Controle</a>
                        </div>
                    </div>
                    <div>
                        <label for="observations" class="text-neutral-500 text-xs">Observações</label>
                        <textarea id="observations" v-model="payslip_observations" @input="resizeTextarea"
                            class="simple-input resize-none overflow-hidden min-h-[50px] p-2 border rounded-md"
                            placeholder="Observações do Holerite..."></textarea>
                        <p class="text-xs text-red-500 mt-2">{{ payslip_observations_error.payslip_observations }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-6 print:py-0">
            <div class="md:max-w-7xl mx-auto sm:px-6 lg:px-8 print:max-w-full print:mx-0 print:sm:px-0 print:lg:px-0">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg print:shadow-none">
                    <div
                        class="flex flex-col lg:flex-row justify-between items-center w-full bg-neutral-700 text-white px-3 print:hidden">
                        <div class="w-[50px]">
                            <span class="uppercase">{{ months_names[payslip_month -
                                1] }}/{{ payslip_year }}</span>
                        </div>
                        <ul class="flex items-center justify-center">
                            <li v-if="payslip_year > 1 || payslip_month > 1" class="rounded-sm text-white">
                                <a :href="route('payslip.filter', { employee: employee.id, month: previousMonth.month, year: previousMonth.year })"
                                    class="group py-1 px-3">
                                    <ChevronLeftIcon class="w-4 group-hover:scale-150" />
                                </a>
                            </li>
                            <li v-for="month in months" :key="month" class="rounded-sm text-white">
                                <a :href="route('payslip.filter', { employee: employee.id, month: month, year: payslip_year })"
                                    class="hover:bg-neutral-600 active:bg-neutral-800 py-3 px-3"
                                    :class="{ 'h-full bg-neutral-900': payslip_month === month }"
                                    :title="months_names[month - 1]">
                                    {{ month }}
                                </a>
                            </li>
                            <li v-if="payslip_year < current_year || (payslip_year === current_year && payslip_month < 12)"
                                class="rounded-sm text-white">
                                <a :href="route('payslip.filter', { employee: employee.id, month: nextMonth.month, year: nextMonth.year })"
                                    class="group py-1 px-3">
                                    <ChevronRightIcon class="w-4 group-hover:scale-150" />
                                </a>
                            </li>
                        </ul>
                        <a :href="route('payslip.filter', { employee: employee.id, month: current_month, year: current_year })"
                            class="text-yellow-500 hover:text-yellow-600 active:text-yellow-700">Resetar data</a>
                    </div>

                    <div class="w-full p-3 bg-neutral-600 text-white text-center print:hidden">Total: {{
                        toMoney(total_geral) }}
                    </div>

                    <div class=" grid gris-cols-1 lg:grid-cols-2 gap-0.5">
                        <div class="relative overflow-x-auto">
                            <table class="w-full text-sm text-left rtl:text-right text-gray-500 ">
                                <thead class="text-xs bg-green-500 text-white uppercase">
                                    <tr class="w-full bg-green-600">
                                        <th scope="col" colspan="4" class="px-6 py-3 text-center">
                                            Entradas
                                        </th>
                                    </tr>
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-center">Descrição</th>
                                        <th scope="col" class="px-6 py-3 text-center">Valor</th>
                                        <th scope="col" class="px-6 py-3 text-center" colspan="2">Controles</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="entradas.length" v-for="entrada in entradas" :key="entrada.id"
                                        class="bg-white border-b even:bg-neutral-50">



                                        <td v-if="edit_record_id !== entrada.id"
                                            class="px-6 py-4 text-center truncate hover:text-wrap max-w-xs">
                                            {{
                                                entrada.description }}
                                        </td>
                                        <td v-else class="px-6 py-4 text-center truncate hover:text-wrap max-w-xs">
                                            <input type="text" placeholder="Descrição" v-model="new_income.description"
                                                class="simple-input" :disabled="general_disable" />
                                            <p v-if="new_income.errors.description" class="text-red-500 text-xs">
                                                {{ new_income.errors.description }}</p>
                                        </td>



                                        <td v-if="edit_record_id !== entrada.id" class="px-6 py-4 text-center">{{
                                            toMoney(entrada.value) }}
                                        </td>
                                        <td v-else class="px-6 py-4 text-center">
                                            <div class="relative">
                                                <div
                                                    class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                    <span class="text-gray-500 sm:text-sm">R$</span>
                                                </div>
                                                <input type="number" min="0.01" step="0.01" placeholder="Valor"
                                                    v-model="new_income.value" class="simple-input pl-9"
                                                    :disabled="general_disable" />
                                                <p v-if="new_income.errors.value" class="text-red-500 text-xs">
                                                    {{ new_income.errors.value }}</p>
                                            </div>
                                        </td>



                                        <td v-if="edit_record_id !== entrada.id"
                                            class="px-6 py-4 text-center group cursor-pointer select-none"
                                            :title="'Editar ' + entrada.description"
                                            :class="{ 'opacity-50': edit_record_id !== null }"
                                            @click="editMode(entrada.id)">
                                            <PencilIcon class="w-5 h-5 mx-auto"
                                                :class="{ 'group-hover:scale-125 group-active:scale-90': edit_record_id === null }" />
                                        </td>
                                        <td v-else
                                            class="px-6 py-4 text-center group cursor-pointer select-none space-y-1"
                                            :title="'Salvar alterações'">
                                            <CheckIcon class="w-5 h-5 mx-auto hover:scale-125 active:scale-90"
                                                @click="updatePayslip(new_income)" />
                                            <p class="text-xs text-red-500 hover:scale-125 active:scale-90"
                                                @click="cancelEditMode()">Cancelar</p>
                                        </td>



                                        <td class="px-6 py-4 text-center group cursor-pointer select-none"
                                            :title="'Deletar ' + entrada.description"
                                            @click="deletePayslip(entrada.id)">
                                            <XMarkIcon
                                                class="w-5 h-5 mx-auto group-hover:scale-125 group-active:scale-90" />
                                        </td>
                                    </tr>
                                    <tr v-else class="bg-white border-b">
                                        <td class="px-6 py-4 text-center" colspan="3">Não há entradas cadastradas
                                        </td>
                                    </tr>
                                    <tr v-if="edit_record_id === null" class="border-b select-none bg-neutral-200">
                                        <td class="px-1 py-1 text-center">
                                            <input type="text" placeholder="Descrição" v-model="new_income.description"
                                                class="simple-input" :disabled="general_disable" />
                                            <p v-if="new_income.errors.description" class="text-red-500 text-xs">
                                                {{ new_income.errors.description }}</p>
                                        </td>
                                        <td class="px-1 py-1 text-center">
                                            <div class="relative">
                                                <div
                                                    class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                    <span class="text-gray-500 sm:text-sm">R$</span>
                                                </div>
                                                <input type="number" min="0.01" step="0.01" placeholder="Valor"
                                                    v-model="new_income.value" class="simple-input pl-9"
                                                    :disabled="general_disable" />
                                                <p v-if="new_income.errors.value" class="text-red-500 text-xs">
                                                    {{ new_income.errors.value }}</p>
                                            </div>
                                        </td>
                                        <td colspan="2" class="px-3 py-2 text-center group cursor-pointer"
                                            @click="createIncome">
                                            <PlusIcon
                                                class="w-5 h-5 mx-auto group-hover:scale-125 group-active:scale-90" />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="text-center bg-neutral-600 text-white p-3 ">Total Entradas: {{
                                toMoney(total_entradas) }}</div>
                        </div>
                        <div class="relative overflow-x-auto">
                            <table class="w-full text-sm text-left rtl:text-right text-gray-500">
                                <thead class="text-xs bg-red-500 text-white uppercase">
                                    <tr class="w-full bg-red-600">
                                        <th scope="col" colspan="5" class="px-6 py-3 text-center">Saídas</th>
                                    </tr>
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-center">Descrição</th>
                                        <th scope="col" class="px-6 py-3 text-center">Valor</th>
                                        <th scope="col" class="px-6 py-3 text-center">Listar</th>
                                        <th colspan="2" scope="col" class="px-6 py-3 text-center">Controles</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="saidas.length" v-for="saida in saidas" :key="saida.id"
                                        class="bg-white border-b even:bg-neutral-50"
                                        :class="{ 'opacity-80 print:hidden': !saida.detail }">
                                        <td v-if="edit_record_id !== saida.id"
                                            class="px-6 py-4 text-center truncate hover:text-wrap max-w-xs">
                                            {{
                                                saida.description }}
                                        </td>
                                        <td v-else class="px-6 py-4 text-center truncate hover:text-wrap max-w-xs">
                                            <input type="text" placeholder="Descrição" v-model="new_outcome.description"
                                                class="simple-input" :disabled="general_disable" />
                                            <p v-if="new_outcome.errors.description" class="text-red-500 text-xs">
                                                {{ new_outcome.errors.description }}</p>
                                        </td>



                                        <td v-if="edit_record_id !== saida.id" class="px-6 py-4 text-center">{{
                                            toMoney(absolute(saida.value)) }}
                                        </td>
                                        <td v-else class="px-6 py-4 text-center">
                                            <div class="relative">
                                                <div
                                                    class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                    <span class="text-gray-500 sm:text-sm">R$</span>
                                                </div>
                                                <input type="number" min="0.01" step="0.01" placeholder="Valor"
                                                    v-model="new_outcome.value" class="simple-input pl-9"
                                                    :disabled="general_disable" />
                                                <p v-if="new_outcome.errors.value" class="text-red-500 text-xs">
                                                    {{ new_outcome.errors.value }}</p>
                                            </div>
                                        </td>



                                        <td class="px-6 py-4 text-center">
                                            <input type="checkbox" :checked="saida.detail"
                                                @change="updatePayslipDetail(saida.id, $event.target.checked)"
                                                :disabled="edit_record_id !== null || general_disable"
                                                class="focus:ring-red-500 text-red-500 border-neutral-500 rounded-sm disabled:opacity-10" />
                                        </td>



                                        <td v-if="edit_record_id !== saida.id"
                                            class="px-6 py-4 text-center group cursor-pointer select-none"
                                            :title="'Editar ' + saida.description"
                                            :class="{ 'opacity-50': edit_record_id !== null }"
                                            @click="editMode(saida.id)">
                                            <PencilIcon class="w-5 h-5 mx-auto"
                                                :class="{ 'group-hover:scale-125 group-active:scale-90': edit_record_id === null }" />
                                        </td>
                                        <td v-else
                                            class="px-6 py-4 text-center group cursor-pointer select-none space-y-1"
                                            :title="'Salvar alterações'">
                                            <CheckIcon class="w-5 h-5 mx-auto hover:scale-125 active:scale-90"
                                                @click="updatePayslip(new_outcome)" />
                                            <p class="text-xs text-red-500 hover:scale-125 active:scale-90"
                                                @click="cancelEditMode()">Cancelar</p>
                                        </td>



                                        <td class="px-6 py-4 text-center group cursor-pointer select-none"
                                            :title="'Deletar ' + saida.description" @click="deletePayslip(saida.id)">
                                            <XMarkIcon
                                                class="w-5 h-5 mx-auto group-hover:scale-125 group-active:scale-90" />
                                        </td>
                                    </tr>
                                    <tr v-else class="bg-white border-b even:bg-neutral-50">
                                        <td class="px-6 py-4 text-center" colspan="4">Não há saídas cadastradas</td>
                                    </tr>
                                    <tr v-if="edit_record_id === null" class="border-b select-none bg-neutral-200">
                                        <td class="px-1 py-1 text-center">
                                            <input type="text" placeholder="Descrição" v-model="new_outcome.description"
                                                class="simple-input focus:ring-red-500" :disabled="general_disable" />
                                            <p v-if="new_outcome.errors.description" class="text-red-500 text-xs">{{
                                                new_outcome.errors.description }}</p>
                                        </td>
                                        <td class="px-1 py-1 text-center">
                                            <input type="number" min="0.01" step="0.01" placeholder="Valor"
                                                v-model="new_outcome.value" class="simple-input focus:ring-red-500"
                                                :disabled="general_disable" />
                                            <p v-if="new_outcome.errors.value" class="text-red-500 text-xs">{{
                                                new_outcome.errors.value }}</p>
                                        </td>
                                        <td class="px-1 py-1 text-center">
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="checkbox" v-model="new_outcome.detail" class="sr-only peer"
                                                    :disabled="general_disable">
                                                <p v-if="new_outcome.errors.detail" class="text-red-500 text-xs">{{
                                                    new_outcome.errors.detail }}</p>
                                                <div
                                                    class="relative w-11 h-6 bg-gray-100 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 rounded-full peer  peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600">
                                                </div>
                                            </label>
                                        </td>
                                        <td colspan="2" class="px-1 py-1 text-center group cursor-pointer"
                                            @click="createOutcome">
                                            <PlusIcon
                                                class="w-5 h-5 mx-auto group-hover:scale-125 group-active:scale-90" />
                                        </td>
                                    </tr>
                                </tbody>

                            </table>
                            <div class="text-center bg-neutral-600 text-white p-3 ">Total Saídas: {{
                                toMoney(absolute(total_saidas)) }}
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
