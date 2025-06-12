<script setup>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import { Head, Link, useForm, router } from '@inertiajs/vue3';
    import { ref, computed, onMounted } from 'vue';
    import { PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';
    import PrimaryButton from '@/Components/PrimaryButton.vue';
    import SecondaryButton from '@/Components/SecondaryButton.vue';
    import {
        toMoney,
        formatDate,
        formatPhoneNumber,
        validateCPF,
        formatCPF,
        clearFormat,
        formatCNPJ,
        validateCNPJ,
        formatarCEP,
    } from '@/general.js';
    import { useToast } from 'vue-toast-notification';
    import 'vue-toast-notification/dist/theme-sugar.css'; // Ou theme-bootstrap.css se configurado em app.js

    const props = defineProps({
        page: Object,
        originalBudget: {
            type: Object,
            default: null,
        },
        originalBudgetItems: {
            type: Array,
            default: () => [],
        },
    });

    const $toast = useToast();

    const defaultContractedResponsibility = "Fornecer todo material de fabricação e consumo";
    const defaultDeadlineStartDescription = "a partir do pagamento de entrada";
    const defaultPaymentMethodDescription = "50% entrada" + "\n" + "50% no ato da entrega do serviço";
    const defaultBankInfoDescription = "BANCO DO BRASIL" + "\n" + "AG: 3175-5 | C/C: 20.439-0" + "\n" + "PIX: (73) 9 8855-9571";

    const budgetForm = useForm({
        // Novos campos de cabeçalho
        title: props.originalBudget?.title || '', // Título identificador do serviço
        validity: props.originalBudget?.validity || 30,

        // Dados do Cliente
        client_name: props.originalBudget?.client_name || '',
        client_email: props.originalBudget?.client_email || '',
        client_phone: props.originalBudget?.client_phone || '',
        client_address: props.originalBudget?.client_address || '',
        client_cpf_cnpj: props.originalBudget?.client_cpf_cnpj || '',
        client_cep: props.originalBudget?.client_cep || '',

        // Descrição Geral e Prazo
        description: props.originalBudget?.description || '',
        budget_date: formatDate(),
        deadline: props.originalBudget?.deadline || 20,
        deadline_type: props.originalBudget?.deadline_type || 'dias úteis',
        deadline_start_description: props.originalBudget?.deadline_start_description || defaultDeadlineStartDescription, // Início do prazo

        // Itens do Orçamento
        items: props.originalBudget?.items.map(item => ({
            id: null,
            item_name: item.item_name,
            item_description: item.item_description,
            quantity: item.quantity,
            unit_price: item.unit_price,
            subtotal: item.subtotal,
        })) || [
                { id: null, item_name: '', item_description: '', quantity: 1, unit_price: 0, subtotal: 0 },
            ],

        // Campos de Valores Totais (Desconto/Acréscimo)
        discount_amount: props.originalBudget?.discount_amount || 0,
        additional_amount: props.originalBudget?.additional_amount || 0,
        total_value: props.originalBudget?.total_value || 0, // Será calculado antes de enviar

        // Campos de Responsabilidade
        contracted_responsibility: props.originalBudget?.contracted_responsibility || defaultContractedResponsibility,
        contractor_responsibility: props.originalBudget?.contractor_responsibility || '',

        // Campos de Pagamento e Bancários
        payment_method_description: props.originalBudget?.payment_method_description || defaultPaymentMethodDescription,
        bank_info_description: props.originalBudget?.bank_info_description || defaultBankInfoDescription,

        // Campos de Tipo de Orçamento (Original/Correção)
        budget_type: 'Original',
        original_budget_id: null,
    });

    if (props.originalBudget) {
        budgetForm.budget_type = 'Correção';
        budgetForm.original_budget_id = props.originalBudget.id;
    }

    onMounted(() => {
        if (props.originalBudget) {
            // Preenche o formulário com os dados do orçamento original
            budgetForm.title = props.originalBudget.title + ' (Cópia)';
            budgetForm.validity = props.originalBudget.validity;
            budgetForm.client_name = props.originalBudget.client_name;
            budgetForm.client_email = props.originalBudget.client_email;
            budgetForm.client_phone = props.originalBudget.client_phone;
            budgetForm.client_address = props.originalBudget.client_address;
            budgetForm.client_cpf_cnpj = props.originalBudget.client_cpf_cnpj;
            budgetForm.client_cep = props.originalBudget.client_cep;
            budgetForm.description = props.originalBudget.description;
            budgetForm.discount_amount = props.originalBudget.discount_amount || 0;
            budgetForm.additional_amount = props.originalBudget.additional_amount || 0;
            budgetForm.contracted_responsibility = props.originalBudget.contracted_responsibility;
            budgetForm.contractor_responsibility = props.originalBudget.contractor_responsibility;
            budgetForm.deadline = props.originalBudget.deadline;
            budgetForm.deadline_start_description = props.originalBudget.deadline_start_description;
            budgetForm.deadline_type = props.originalBudget.deadline_type;
            budgetForm.payment_method_description = props.originalBudget.payment_method_description;
            budgetForm.bank_info_description = props.originalBudget.bank_info_description;
            // Se a intenção é duplicar, o tipo geralmente começa como 'Original',
            // mas você pode ajustar isso conforme sua lógica de negócio.
            // Se a intenção é "Corrigir" um orçamento, defina 'Correção' e 'original_budget_id'
            budgetForm.budget_type = 'Original';
            budgetForm.original_budget_id = null; // Não vincula o novo orçamento ao antigo por padrão na duplicação

            // Preenche os itens do orçamento
            if (props.originalBudgetItems && props.originalBudgetItems.length > 0) {
                budgetForm.items = props.originalBudgetItems.map(item => ({
                    item_name: item.item_name,
                    item_description: item.item_description,
                    quantity: item.quantity,
                    unit_price: item.unit_price,
                }));
            } else {
                budgetForm.items = [{ item_name: '', item_description: '', quantity: 1, unit_price: 0 }];
            }
        }
    });

    // =============================================
    // Lógica para Itens do Orçamento
    const addBudgetItem = () => {
        budgetForm.items.push({ id: null, item_name: '', item_description: '', quantity: 1, unit_price: 0, subtotal: 0 });
    };

    const removeBudgetItem = (index) => {
        if (budgetForm.items.length > 1) {
            budgetForm.items.splice(index, 1);
            calculateTotals();
        }
    };

    const calculateItemSubtotal = (item) => {
        item.subtotal = item.quantity * item.unit_price;
        calculateTotals();
    };

    // =============================================
    // Lógica para Cálculos de Totais
    const subtotalCalculated = computed(() => {
        return budgetForm.items.reduce((sum, item) => sum + item.subtotal, 0);
    });

    const totalValueCalculated = computed(() => {
        let total = subtotalCalculated.value - budgetForm.discount_amount + budgetForm.additional_amount;
        return Math.max(0, total);
    });

    const calculateTotals = () => {
        // As computed properties já são reativas.
    };

    // =============================================
    // Lógica de Máscaras e Validações de Campo (usando funções do general.js)

    const handlePhoneInput = (event) => {
        // Usa a função formatPhoneNumber do general.js
        budgetForm.client_phone = formatPhoneNumber(event.target.value);
    };

    const handleCpfCnpjInput = (event) => {
        // Usa a função formatCPF do general.js. Note que formatCPF é para CPF,
        // você pode precisar de uma função separada para CNPJ ou uma mais genérica.
        // Se formatCPF do general.js não lida com CNPJ, essa parte precisa ser aprimorada.
        let cleaned = clearFormat(event.target.value);
        if (cleaned.length <= 11) {
            budgetForm.client_cpf_cnpj = formatCPF(cleaned);
        } else {
            budgetForm.client_cpf_cnpj = formatCNPJ(cleaned);
        }
    };

    // Função para preencher endereço pelo CEP
    const fillAddressByCep = async () => {
        let cep = clearFormat(budgetForm.client_cep); // Limpa a máscara

        if (cep.length === 8) {
            try {
                const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                const data = await response.json();

                if (!data.erro) {
                    // Preenche apenas logradouro, bairro, localidade e uf no campo de endereço
                    let fullAddress = data.logradouro || '';
                    if (data.bairro) fullAddress += `, ${data.bairro}`;
                    if (data.localidade) fullAddress += `, ${data.localidade}`;
                    if (data.uf) fullAddress += ` - ${data.uf}`;

                    budgetForm.client_address = fullAddress;
                    // $toast.success('Endereço preenchido com sucesso!', { position: 'top-right' });
                } else {
                    //$toast.error('CEP não encontrado.', { position: 'top-right' });
                    budgetForm.client_address = ''; // Limpa o endereço se o CEP não for encontrado
                }
            } catch (error) {
                console.error("Erro ao buscar CEP:", error);
                // $toast.error('Erro ao buscar CEP. Tente novamente mais tarde.', { position: 'top-right' });
            }
        }
    };

    const handleCepInput = (event) => {
        let value = clearFormat(event.target.value);
        if (value.length > 5) {
            value = formatarCEP(value)
        }
        if (value.length > 7) {
            fillAddressByCep();
        }
        budgetForm.client_cep = value;
    };

    // =============================================
    // Submissão do Formulário
    const submitBudget = () => {
        budgetForm.total_value = totalValueCalculated.value;

        const formToSend = { ...budgetForm.data };
        formToSend.client_phone = clearFormat(formToSend.client_phone);
        formToSend.client_cpf_cnpj = clearFormat(formToSend.client_cpf_cnpj);
        formToSend.client_cep = clearFormat(formToSend.client_cep);

        budgetForm.post(route('budgets.store'), {
            data: formToSend, // Envia a versão limpa
            onSuccess: () => {
                $toast.success('Orçamento criado com sucesso!', { position: 'top-right' });
                budgetForm.reset();
                router.visit(route('budgets.index'));
            },
            onError: (errors) => {
                console.error("Erro ao criar orçamento:", errors);
                $toast.error('Erro ao criar orçamento. Verifique os campos.', { position: 'top-right' });
            }
        });
    };

    const cancelForm = () => {
        if (confirm('Tem certeza que deseja cancelar? Todas as alterações serão perdidas.')) {
            budgetForm.reset();
            router.visit(route('budgets.index'));
        }
    };
</script>

<template>

    <Head :title="page.name" />
    <AppLayout :page="page">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ budgetForm.budget_type === 'Correção' ? 'Correção de Orçamento' : 'Novo Orçamento'
                }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <form @submit.prevent="submitBudget" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 text-sm items-end">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">ORÇAMENTO Nº: 0000</label>
                                <p class="mt-1 font-semibold text-gray-900">NOVO</p>
                            </div>
                            <div>
                                <label for="budget_date" class="block text-sm font-medium text-gray-700">EMITIDO
                                    EM:</label>
                                <input type="date" id="budget_date" v-model="budgetForm.budget_date"
                                    class="mt-1 block w-full simple-input disabled:bg-neutral-100" readonly disabled
                                    required />
                                <p v-if="budgetForm.errors.budget_date" class="text-red-500 text-xs mt-1">{{
                                    budgetForm.errors.budget_date }}</p>
                            </div>
                            <div>
                                <label for="validity" class="block text-sm font-medium text-gray-700">VALIDADE (em
                                    dias)</label>
                                <div class="mt-1 flex items-center">
                                    <input type="number" id="validity" v-model.number="budgetForm.validity" min="0"
                                        class="block w-full simple-input mr-2" />
                                </div>
                                <p v-if="budgetForm.errors.validity" class="text-red-500 text-xs mt-1">{{
                                    budgetForm.errors.validity }}</p>
                            </div>
                            <!--
                            <div v-if="props.originalBudget"> <label for="budget_type"
                                    class="block text-sm font-medium text-gray-700">TIPO DE ORÇAMENTO</label>
                                <select id="budget_type" v-model="budgetForm.budget_type"
                                    class="mt-1 block w-full simple-input" disabled>
                                    <option value="Original">Original</option>
                                    <option value="Correção">Correção</option>
                                </select>
                                <div v-if="budgetForm.budget_type === 'Correção' && props.originalBudget"
                                    class="mt-2 text-sm text-gray-600">
                                    Baseado no Orçamento Original #{{ props.originalBudget.id }}
                                </div>
                            </div>
                            -->
                        </div>

                        <section class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-2 mb-4">DADOS DO CLIENTE</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div>
                                    <label for="client_name"
                                        class="block text-sm font-medium text-gray-700">NOME</label>
                                    <input type="text" id="client_name" v-model="budgetForm.client_name"
                                        class="mt-1 block w-full simple-input" required />
                                    <p v-if="budgetForm.errors.client_name" class="text-red-500 text-xs mt-1">{{
                                        budgetForm.errors.client_name }}</p>
                                </div>
                                <div>
                                    <label for="client_phone"
                                        class="block text-sm font-medium text-gray-700">TELEFONE</label>
                                    <input type="text" id="client_phone" v-model="budgetForm.client_phone"
                                        @input="handlePhoneInput" minlength="10" maxlength="16"
                                        class="mt-1 block w-full simple-input" required />
                                    <p v-if="budgetForm.errors.client_phone" class="text-red-500 text-xs mt-1">{{
                                        budgetForm.errors.client_phone }}</p>
                                </div>
                                <div>
                                    <label for="client_email"
                                        class="block text-sm font-medium text-gray-700">E-MAIL</label>
                                    <input type="email" id="client_email" v-model="budgetForm.client_email"
                                        class="mt-1 block w-full simple-input" />
                                    <p v-if="budgetForm.errors.client_email" class="text-red-500 text-xs mt-1">{{
                                        budgetForm.errors.client_email }}</p>
                                </div>
                                <div>
                                    <label for="client_cpf_cnpj"
                                        class="block text-sm font-medium text-gray-700">CPF/CNPJ</label>
                                    <input type="text" id="client_cpf_cnpj" v-model="budgetForm.client_cpf_cnpj"
                                        @input="handleCpfCnpjInput" minlength="11" maxlength="18"
                                        class="mt-1 block w-full simple-input" required />
                                    <p v-if="budgetForm.errors.client_cpf_cnpj" class="text-red-500 text-xs mt-1">{{
                                        budgetForm.errors.client_cpf_cnpj }}</p>
                                </div>
                                <div class="lg:col-span-1">
                                    <label for="client_cep" class="block text-sm font-medium text-gray-700">CEP</label>
                                    <input type="text" id="client_cep" v-model="budgetForm.client_cep"
                                        @input="handleCepInput" minlength="8" maxlength="9"
                                        class="mt-1 block w-full simple-input" required />
                                    <p v-if="budgetForm.errors.client_cep" class="text-red-500 text-xs mt-1">{{
                                        budgetForm.errors.client_cep }}</p>
                                </div>
                                <div class="lg:col-span-1">
                                    <label for="client_address"
                                        class="block text-sm font-medium text-gray-700">ENDEREÇO</label>
                                    <input type="text" id="client_address" v-model="budgetForm.client_address"
                                        class="mt-1 block w-full simple-input"
                                        placeholder="Preenchido automaticamente pelo CEP" />
                                    <p v-if="budgetForm.errors.client_address" class="text-red-500 text-xs mt-1">{{
                                        budgetForm.errors.client_address }}</p>
                                </div>
                            </div>
                        </section>

                        <section class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-2 mb-4">DADOS DO TÍTULO</h3>

                            <label for="title" class="block text-sm font-medium text-gray-700">TÍTULO IDENTIFICADOR DO
                                SERVIÇO</label>
                            <input type="text" id="title" v-model="budgetForm.title" maxlength="255"
                                class="mt-1 block w-full simple-input" required />
                            <p v-if="budgetForm.errors.title" class="text-red-500 text-xs mt-1">{{
                                budgetForm.errors.title
                                }}</p>
                        </section>

                        <section class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-2 mb-4">ITENS DO ORÇAMENTO
                            </h3>
                            <div class="space-y-4">
                                <div v-for="(item, index) in budgetForm.items" :key="index"
                                    class="p-4 border rounded-lg bg-gray-50 relative">
                                    <button type="button" @click="removeBudgetItem(index)"
                                        v-if="budgetForm.items.length > 1"
                                        class="absolute top-2 right-2 text-red-500 hover:text-red-700 p-1 rounded-full bg-white">
                                        <TrashIcon class="w-5 h-5" />
                                    </button>
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                        <div class="md:col-span-10">
                                            <label :for="'item_name_' + index"
                                                class="block text-sm font-medium text-gray-700">DESCRIÇÃO</label>
                                            <textarea :id="'item_name_' + index" v-model="item.item_name" rows="2"
                                                class="mt-1 block w-full simple-input" required
                                                placeholder="Descrição do item"></textarea>
                                            <p v-if="budgetForm.errors[`items.${index}.item_name`]"
                                                class="text-red-500 text-xs mt-1">{{
                                                    budgetForm.errors[`items.${index}.item_name`] }}</p>
                                        </div>
                                        <!-- VOU OCULTAS QUANTIDADE POR ENQUANTO, POIS SERÁ SEMPRE 1
                                        <div class="md:col-span-2">
                                            <label :for="'quantity_' + index"
                                                class="block text-sm font-medium text-gray-700">QUANTIDADE</label>
                                            <input type="number" :id="'quantity_' + index"
                                                v-model.number="item.quantity" @input="calculateItemSubtotal(item)"
                                                min="1" class="mt-1 block w-full simple-input" required />
                                            <p v-if="budgetForm.errors[`items.${index}.quantity`]"
                                                class="text-red-500 text-xs mt-1">{{
                                                    budgetForm.errors[`items.${index}.quantity`] }}</p>
                                        </div>
                                        -->
                                        <div class="md:col-span-2">
                                            <label :for="'unit_price_' + index"
                                                class="block text-sm font-medium text-gray-700">VALOR (R$)</label>
                                            <input type="number" :id="'unit_price_' + index"
                                                v-model.number="item.unit_price" @input="calculateItemSubtotal(item)"
                                                step="0.01" min="0" class="mt-1 block w-full simple-input" required />
                                            <p v-if="budgetForm.errors[`items.${index}.unit_price`]"
                                                class="text-red-500 text-xs mt-1">{{
                                                    budgetForm.errors[`items.${index}.unit_price`]
                                                }}</p>
                                        </div>
                                        <!--JÁ QUE OCUTEI QUANTIDADE NÃO FAZ SENTIDO MOSTRAR SUBTOTAL DO ITEM
                                        <div class="md:col-span-12 text-right">
                                            <p class="text-sm font-medium text-gray-400">SUBTOTAL DO
                                                ITEM:
                                                <span class="text-gray-900">{{
                                                    toMoney(item.subtotal) }}</span>
                                            </p>
                                        </div>
                                        -->
                                    </div>
                                </div>
                            </div>
                            <button type="button" @click="addBudgetItem"
                                class="mt-4 px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 flex items-center">
                                <PlusIcon class="w-5 h-5 mr-1" /> Adicionar
                                Item
                            </button>
                            <p v-if="budgetForm.errors.items" class="text-red-500 text-xs mt-1">
                                {{
                                    budgetForm.errors.items }}
                            </p>
                        </section>
                        <section class="mb-8 p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-2 mb-4">TOTAIS</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="col-span-2 md:col-span-1">
                                    <label for="discount_amount"
                                        class="block text-sm font-medium text-gray-700">DESCONTO
                                        (R$)</label>
                                    <input type="number" id="discount_amount"
                                        v-model.number="budgetForm.discount_amount" @input="calculateTotals" step="0.01"
                                        min="0" class="mt-1 block w-full simple-input" />
                                    <p v-if="budgetForm.errors.discount_amount" class="text-red-500 text-xs mt-1">{{
                                        budgetForm.errors.discount_amount }}</p>
                                </div>
                                <div class="col-span-2 md:col-span-1">
                                    <label for="additional_amount"
                                        class="block text-sm font-medium text-gray-700">ACRÉSCIMO
                                        (R$)</label>
                                    <input type="number" id="additional_amount"
                                        v-model.number="budgetForm.additional_amount" @input="calculateTotals"
                                        step="0.01" min="0" class="mt-1 block w-full simple-input" />
                                    <p v-if="budgetForm.errors.additional_amount" class="text-red-500 text-xs mt-1">
                                        {{
                                            budgetForm.errors.additional_amount }}</p>
                                </div>
                                <div class="col-span-2 md:col-span-1 text-lg font-semibold text-gray-900">
                                    SUBTOTAL: {{ toMoney(subtotalCalculated) }}
                                </div>
                                <div class="col-span-2 md:col-span-1 text-lg font-bold text-gray-900">
                                    TOTAL FINAL: {{ toMoney(totalValueCalculated) }}
                                </div>
                            </div>
                        </section>

                        <section class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-2 mb-4">RESPONSABILIDADES
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="contracted_responsibility"
                                        class="block text-sm font-medium text-gray-700">DA
                                        CONTRATADA</label>
                                    <textarea id="contracted_responsibility"
                                        v-model="budgetForm.contracted_responsibility" rows="4"
                                        class="mt-1 block w-full simple-input"
                                        placeholder="Ex: Fornecer todo material de fabricação e consumo"></textarea>
                                    <p v-if="budgetForm.errors.contracted_responsibility"
                                        class="text-red-500 text-xs mt-1">{{
                                            budgetForm.errors.contracted_responsibility }}</p>
                                </div>
                                <div>
                                    <label for="contractor_responsibility"
                                        class="block text-sm font-medium text-gray-700">DO
                                        CONTRATANTE</label>
                                    <textarea id="contractor_responsibility"
                                        v-model="budgetForm.contractor_responsibility" rows="4"
                                        class="mt-1 block w-full simple-input"
                                        placeholder="Ex: Fornecer acesso ao local"></textarea>
                                    <p v-if="budgetForm.errors.contractor_responsibility"
                                        class="text-red-500 text-xs mt-1">{{
                                            budgetForm.errors.contractor_responsibility }}</p>
                                </div>
                            </div>
                        </section>

                        <section class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-2 mb-4">Outras Informações</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h4 class="text-md font-semibold text-gray-800 mb-2">DO SERVIÇO</h4>
                                    <div class="mb-4">
                                        <label for="description"
                                            class="block text-sm font-medium text-gray-700">OBSERVAÇÕES</label>
                                        <textarea id="description" v-model="budgetForm.description" rows="3"
                                            class="mt-1 block w-full simple-input"
                                            placeholder="Detalhes adicionais sobre o orçamento..."></textarea>
                                        <p v-if="budgetForm.errors.description" class="text-red-500 text-xs mt-1">{{
                                            budgetForm.errors.description }}</p>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="deadline" class="block text-sm font-medium text-gray-700">PRAZO
                                                (em dias)</label>
                                            <input type="number" id="deadline" v-model.number="budgetForm.deadline"
                                                min="0" class="mt-1 block w-full simple-input" />
                                            <p v-if="budgetForm.errors.deadline" class="text-red-500 text-xs mt-1">{{
                                                budgetForm.errors.deadline }}</p>
                                        </div>
                                        <div>
                                            <label for="deadline_type"
                                                class="block text-sm font-medium text-gray-700">TIPO DE
                                                PRAZO</label>
                                            <select id="deadline_type" v-model="budgetForm.deadline_type"
                                                class="mt-1 block w-full simple-input">
                                                <option value="dias úteis">Dias Úteis</option>
                                                <option value="dias corridos">Dias Corridos</option>
                                            </select>
                                            <p v-if="budgetForm.errors.deadline_type" class="text-red-500 text-xs mt-1">
                                                {{ budgetForm.errors.deadline_type }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <label for="deadline_start_description"
                                            class="block text-sm font-medium text-gray-700">INÍCIO DO PRAZO</label>
                                        <input type="text" id="deadline_start_description"
                                            v-model="budgetForm.deadline_start_description"
                                            class="mt-1 block w-full simple-input"
                                            placeholder="Ex: a partir do pagamento de entrada" />
                                        <p v-if="budgetForm.errors.deadline_start_description"
                                            class="text-red-500 text-xs mt-1">{{
                                                budgetForm.errors.deadline_start_description }}</p>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="text-md font-semibold text-gray-800 mb-2">DO PAGAMENTO</h4>
                                    <div class="mb-4">
                                        <label for="payment_method_description"
                                            class="block text-sm font-medium text-gray-700">FORMA DE PAGAMENTO</label>
                                        <textarea id="payment_method_description"
                                            v-model="budgetForm.payment_method_description" rows="4"
                                            class="mt-1 block w-full simple-input"
                                            placeholder="Ex: 50% entrada&#10;50% no ato da entrega do serviço"></textarea>
                                        <p v-if="budgetForm.errors.payment_method_description"
                                            class="text-red-500 text-xs mt-1">{{
                                                budgetForm.errors.payment_method_description }}</p>
                                    </div>
                                    <div>
                                        <label for="bank_info_description"
                                            class="block text-sm font-medium text-gray-700">INFORMAÇÕES
                                            BANCÁRIAS</label>
                                        <textarea id="bank_info_description" v-model="budgetForm.bank_info_description"
                                            rows="4" class="mt-1 block w-full simple-input"
                                            placeholder="Ex: BANCO DO BRASIL&#10;AG: 3175-5 | C/C: 20.439-0&#10;PIX: (73) 9 8855-9571"></textarea>
                                        <p v-if="budgetForm.errors.bank_info_description"
                                            class="text-red-500 text-xs mt-1">{{
                                                budgetForm.errors.bank_info_description
                                            }}</p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <div class="flex justify-end space-x-4">
                            <SecondaryButton @click="cancelForm()">Cancelar</SecondaryButton>
                            <PrimaryButton type="submit" :disabled="budgetForm.processing">
                                Salvar Orçamento
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>