<script setup>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import { Head, Link, useForm, router } from '@inertiajs/vue3';
    import { ref, computed, watch } from 'vue';
    import { PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';
    import { toMoney, formatDate, formatPhoneNumber, formatCPF, formatCNPJ, formatarCEP } from '@/general.js';
    import { useToast } from 'vue-toast-notification';
    import 'vue-toast-notification/dist/theme-sugar.css';
    import ClientSelector from '@/Components/ClientSelector.vue';
    import axios from 'axios';
    import PrimaryButton from '@/Components/PrimaryButton.vue';

    const props = defineProps({
        page: Object,
        budget: Object, // A prop 'budget' já contém o client_id e outros dados
    });

    const $toast = useToast();

    const defaultContractedResponsibility = "Fornecer todo material de fabricação e consumo";
    const defaultDeadlineStartDescription = "a partir do pagamento de entrada";
    const defaultPaymentMethodDescription = "50% entrada" + "\n" + "50% no ato da entrega do serviço";
    const defaultBankInfoDescription = "BANCO DO BRASIL" + "\n" + "AG: 3175-5 | C/C: 20.439-0" + "\n" + "PIX: (73) 9 8855-9571";

    const budgetForm = useForm({
        // Apenas o ID do cliente é mantido no formulário principal
        client_id: props.budget.client_id,

        // Outros campos do orçamento, preenchidos com os dados existentes
        title: props.budget.title,
        validity: props.budget.validity,
        description: props.budget.description,
        budget_date: props.budget.budget_date?.split('T')[0],
        deadline: props.budget.deadline,
        deadline_type: props.budget.deadline_type,
        deadline_start_description: props.budget.deadline_start_description,
        items: props.budget.items.map(item => ({
            id: item.id,
            item_name: item.item_name,
            item_description: item.item_description,
            quantity: item.quantity,
            unit_price: item.unit_price,
            subtotal: item.subtotal,
        })),
        discount_amount: props.budget.discount_amount,
        additional_amount: props.budget.additional_amount,
        total_value: props.budget.total_value,
        contracted_responsibility: props.budget.contracted_responsibility,
        contractor_responsibility: props.budget.contractor_responsibility,
        payment_method_description: props.budget.payment_method_description,
        bank_info_description: props.budget.bank_info_description,
        budget_type: props.budget.budget_type,
        original_budget_id: props.budget.original_budget_id,
    });

    // Lógica para controle de edição
    const canEdit = computed(() => {
        const statusAllowed = ['Rascunho', 'Enviado'].includes(props.budget.status);
        const noServiceGenerated = props.budget.generated_service_id === null;
        return statusAllowed && noServiceGenerated;
    });

    // Estado para armazenar e exibir os dados do cliente selecionado
    const selectedClient = ref(null);
    const isLoadingClient = ref(false);

    // Observa o ID do cliente e busca seus dados
    watch(() => budgetForm.client_id, async (newId) => {
        if (newId) {
            isLoadingClient.value = true;
            selectedClient.value = null;
            try {
                const response = await axios.get(route('clients.show', { client: newId }), {
                    headers: { 'Accept': 'application/json' }
                });
                selectedClient.value = response.data.client;
            } catch (error) {
                console.error("Erro ao buscar dados do cliente:", error);
                $toast.error('Não foi possível carregar os dados do cliente.', { position: 'top-right' });
            } finally {
                isLoadingClient.value = false;
            }
        } else {
            selectedClient.value = null;
        }
    }, { immediate: true });


    // Lógica para Itens e Totais (permanece a mesma)
    const addBudgetItem = () => {
        budgetForm.items.push({ id: null, item_name: '', item_description: '', quantity: 1, unit_price: 0, subtotal: 0 });
    };

    const removeBudgetItem = (index) => {
        if (budgetForm.items.length > 1) {
            budgetForm.items.splice(index, 1);
        }
    };

    const calculateItemSubtotal = (item) => {
        item.subtotal = (item.quantity || 0) * (item.unit_price || 0);
    };

    // Lógica para Cálculos de Totais
    const subtotalCalculated = computed(() => {
        return budgetForm.items.reduce((sum, item) => sum + (item.subtotal || 0), 0);
    });

    const totalValueCalculated = computed(() => {
        const subtotal = parseFloat(subtotalCalculated.value);
        const discount = parseFloat(budgetForm.discount_amount) || 0;
        const additional = parseFloat(budgetForm.additional_amount) || 0;
        return Math.max(0, subtotal - discount + additional);
    });

    // Submissão do Formulário
    const submitBudget = () => {
        budgetForm.total_value = totalValueCalculated.value;

        budgetForm.put(route('budgets.update', props.budget.id), {
            onSuccess: () => {
                $toast.success('Orçamento atualizado com sucesso!', { position: 'top-right' });
                router.visit(route('budgets.index'));
            },
            onError: (errors) => {
                console.error("Erro ao atualizar orçamento:", errors);
                $toast.error('Erro ao atualizar orçamento. Verifique os campos.', { position: 'top-right' });
            }
        });
    };

    const cancelForm = () => {
        if (confirm('Tem certeza que deseja cancelar? Todas as alterações não salvas serão perdidas.')) {
            router.visit(route('budgets.index'));
        }
    };
</script>

<template>

    <Head :title="page.name" />
    <AppLayout :page="page">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edição de Orçamento #{{ budget.id }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <form @submit.prevent="submitBudget" class="p-6">
                        <div v-if="!canEdit" class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6"
                            role="alert">
                            <p class="font-bold">Modo Somente Leitura</p>
                            <p>Este orçamento não pode mais ser editado. Para fazer alterações, você pode
                                <Link :href="route('budgets.create', { duplicate_from: budget.id })"
                                    class="font-semibold underline">duplicá-lo</Link> e criar uma nova versão.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 text-sm items-end">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">ORÇAMENTO Nº: {{ budget.id
                                }}</label>
                                <p v-if="budgetForm.budget_type === 'Correção'"
                                    class="mt-1 font-semibold text-gray-900">
                                    CORREÇÃO</p>
                            </div>
                            <div>
                                <label for="budget_date" class="block text-sm font-medium text-gray-700">EMITIDO
                                    EM:</label>
                                <input type="date" id="budget_date" v-model="budgetForm.budget_date"
                                    class="simple-input mt-1" disabled />
                            </div>
                            <div>
                                <label for="validity" class="block text-sm font-medium text-gray-700">VALIDADE (em
                                    dias)</label>
                                <input type="number" id="validity" v-model.number="budgetForm.validity" min="0"
                                    class="simple-input mt-1" :disabled="!canEdit" />
                            </div>
                        </div>

                        <section class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-2 mb-4">DADOS DO CLIENTE</h3>
                            <div class="grid grid-cols-1 gap-6">
                                <div class="sm:col-span-1">
                                    <label for="client-selector"
                                        class="block text-sm font-medium text-gray-700 required-input-label">Cliente
                                        Selecionado</label>
                                    <ClientSelector v-model="budgetForm.client_id" id="client-selector" class="mt-1"
                                        :required="true" :disabled="!canEdit" />
                                </div>
                                <div v-if="isLoadingClient" class="mt-4 text-center text-sm text-gray-500">
                                    Carregando dados do cliente...
                                </div>
                                <div v-if="selectedClient"
                                    class="mt-4 p-4 border rounded-lg bg-gray-50 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                                    <div class="font-medium text-gray-700">
                                        <span class="font-semibold block text-gray-500">Nome:</span> {{
                                            selectedClient.name }}
                                    </div>
                                    <div class="font-medium text-gray-700">
                                        <span class="font-semibold block text-gray-500">{{ selectedClient.type ===
                                            'fisica' ?
                                            'CPF:' : 'CNPJ:' }}</span> {{ selectedClient.type === 'fisica' ?
                                                formatCPF(selectedClient.document) : formatCNPJ(selectedClient.document) }}
                                    </div>
                                    <div class="font-medium text-gray-700">
                                        <span class="font-semibold block text-gray-500">E-mail:</span> {{
                                            selectedClient.email
                                            || 'N/A' }}
                                    </div>
                                    <div class="font-medium text-gray-700">
                                        <span class="font-semibold block text-gray-500">CEP:</span> {{
                                            formatarCEP(selectedClient.postal_code) || 'Não informado' }}
                                    </div>
                                    <div class="font-medium text-gray-700">
                                        <span class="font-semibold block text-gray-500">Endereço:</span> {{
                                            selectedClient.address_street + ', ' + selectedClient.address_number + ', ' +
                                            selectedClient.address_neighborhood || 'Não informado' }}
                                    </div>
                                    <div class="font-medium text-gray-700">
                                        <span class="font-semibold block text-gray-500">Contatos:</span>
                                        <span v-if="selectedClient.contacts?.length"
                                            v-for="(contact, index) in selectedClient.contacts" :key="index"
                                            class="px-1">
                                            {{ formatPhoneNumber(contact) || 'Não informado' }}
                                        </span>
                                        <span v-else>Não informado</span>
                                    </div>
                                    <div class="lg:col-start-3 text-right">
                                        <Link :href="route('clients.edit', selectedClient.id)" target="_blank"
                                            class="text-blue-600 hover:underline text-xs">
                                        Editar dados do cliente
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-2 mb-4">DADOS DO TÍTULO</h3>
                            <label for="title" class="block text-sm font-medium text-gray-700">TÍTULO IDENTIFICADOR DO
                                SERVIÇO</label>
                            <input type="text" id="title" v-model="budgetForm.title" maxlength="255"
                                class="simple-input mt-1" :disabled="!canEdit" required />
                            <p v-if="budgetForm.errors.title" class="text-red-500 text-xs mt-1">{{
                                budgetForm.errors.title }}
                            </p>
                        </section>

                        <section class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-2 mb-4">ITENS DO ORÇAMENTO</h3>
                            <div class="space-y-4">
                                <div v-for="(item, index) in budgetForm.items" :key="index"
                                    class="p-4 border rounded-lg bg-gray-50 relative">
                                    <button type="button" @click="removeBudgetItem(index)"
                                        v-if="budgetForm.items.length > 1"
                                        class="absolute top-2 right-2 text-red-500 hover:text-red-700 p-1 rounded-full bg-white"
                                        :disabled="!canEdit">
                                        <TrashIcon class="w-5 h-5" />
                                    </button>
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                        <div class="md:col-span-10">
                                            <label class="block text-sm font-medium text-gray-700">DESCRIÇÃO</label>
                                            <textarea v-model="item.item_name" rows="2" class="simple-input mt-1"
                                                required placeholder="Descrição do item"
                                                :disabled="!canEdit"></textarea>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700">VALOR (R$)</label>
                                            <input type="number" v-model.number="item.unit_price"
                                                @input="calculateItemSubtotal(item)" step="0.01" min="0"
                                                class="simple-input mt-1" required :disabled="!canEdit" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" @click="addBudgetItem"
                                class="mt-4 px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 flex items-center text-sm"
                                :disabled="!canEdit">
                                <PlusIcon class="w-5 h-5 mr-1" /> Adicionar Item
                            </button>
                        </section>

                        <section class="mb-8 p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-2 mb-4">TOTAIS</h3>
                            <div class="grid grid-cols-2 gap-4 items-center">
                                <div>
                                    <label for="discount_amount"
                                        class="block text-sm font-medium text-gray-700">DESCONTO
                                        (R$)</label>
                                    <input type="number" id="discount_amount"
                                        v-model.number="budgetForm.discount_amount" step="0.01" min="0"
                                        class="simple-input mt-1" :disabled="!canEdit" />
                                </div>
                                <div>
                                    <label for="additional_amount"
                                        class="block text-sm font-medium text-gray-700">ACRÉSCIMO
                                        (R$)</label>
                                    <input type="number" id="additional_amount"
                                        v-model.number="budgetForm.additional_amount" step="0.01" min="0"
                                        class="simple-input mt-1" :disabled="!canEdit" />
                                </div>
                                <div class="text-lg font-semibold text-gray-900">SUBTOTAL: {{
                                    toMoney(subtotalCalculated) }}
                                </div>
                                <div class="text-lg font-bold text-gray-900 text-right">TOTAL FINAL: {{
                                    toMoney(totalValueCalculated) }}</div>
                            </div>
                        </section>

                        <section class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-2 mb-4">RESPONSABILIDADES</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="contracted_responsibility"
                                        class="block text-sm font-medium text-gray-700">DA
                                        CONTRATADA</label>
                                    <textarea id="contracted_responsibility"
                                        v-model="budgetForm.contracted_responsibility" rows="4"
                                        class="mt-1 block w-full simple-input"
                                        placeholder="Ex: Fornecer todo material de fabricação e consumo"
                                        :disabled="!canEdit"></textarea>
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
                                        placeholder="Ex: Fornecer acesso ao local" :disabled="!canEdit"></textarea>
                                    <p v-if="budgetForm.errors.contractor_responsibility"
                                        class="text-red-500 text-xs mt-1">{{
                                            budgetForm.errors.contractor_responsibility }}</p>
                                </div>
                            </div>
                        </section>

                        <section class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-2 mb-4">OUTRAS INFORMAÇÕES</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h4 class="text-md font-semibold text-gray-800 mb-2">DO SERVIÇO</h4>
                                    <div class="mb-4">
                                        <label for="description"
                                            class="block text-sm font-medium text-gray-700">OBSERVAÇÕES</label>
                                        <textarea id="description" v-model="budgetForm.description" rows="3"
                                            class="mt-1 block w-full simple-input"
                                            placeholder="Detalhes adicionais sobre o orçamento..."
                                            :disabled="!canEdit"></textarea>
                                        <p v-if="budgetForm.errors.description" class="text-red-500 text-xs mt-1">{{
                                            budgetForm.errors.description }}</p>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="deadline" class="block text-sm font-medium text-gray-700">PRAZO
                                                (em
                                                dias)</label>
                                            <input type="number" id="deadline" v-model.number="budgetForm.deadline"
                                                min="0" class="mt-1 block w-full simple-input" :disabled="!canEdit" />
                                            <p v-if="budgetForm.errors.deadline" class="text-red-500 text-xs mt-1">{{
                                                budgetForm.errors.deadline }}</p>
                                        </div>
                                        <div>
                                            <label for="deadline_type"
                                                class="block text-sm font-medium text-gray-700">TIPO DE
                                                PRAZO</label>
                                            <select id="deadline_type" v-model="budgetForm.deadline_type"
                                                class="mt-1 block w-full simple-input" :disabled="!canEdit">
                                                <option value="dias úteis">Dias Úteis</option>
                                                <option value="dias corridos">Dias Corridos</option>
                                            </select>
                                            <p v-if="budgetForm.errors.deadline_type" class="text-red-500 text-xs mt-1">
                                                {{
                                                    budgetForm.errors.deadline_type }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <label for="deadline_start_description"
                                            class="block text-sm font-medium text-gray-700">INÍCIO DO PRAZO</label>
                                        <input type="text" id="deadline_start_description"
                                            v-model="budgetForm.deadline_start_description"
                                            class="mt-1 block w-full simple-input"
                                            placeholder="Ex: a partir do pagamento de entrada" :disabled="!canEdit" />
                                        <p v-if="budgetForm.errors.deadline_start_description"
                                            class="text-red-500 text-xs mt-1">{{
                                                budgetForm.errors.deadline_start_description }}
                                        </p>
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
                                            placeholder="Ex: 50% entrada&#10;50% no ato da entrega do serviço"
                                            :disabled="!canEdit"></textarea>
                                        <p v-if="budgetForm.errors.payment_method_description"
                                            class="text-red-500 text-xs mt-1">{{
                                                budgetForm.errors.payment_method_description }}
                                        </p>
                                    </div>
                                    <div>
                                        <label for="bank_info_description"
                                            class="block text-sm font-medium text-gray-700">INFORMAÇÕES
                                            BANCÁRIAS</label>
                                        <textarea id="bank_info_description" v-model="budgetForm.bank_info_description"
                                            rows="4" class="mt-1 block w-full simple-input"
                                            placeholder="Ex: BANCO DO BRASIL&#10;AG: 3175-5 | C/C: 20.439-0&#10;PIX: (73) 9 8855-9571"
                                            :disabled="!canEdit"></textarea>
                                        <p v-if="budgetForm.errors.bank_info_description"
                                            class="text-red-500 text-xs mt-1">{{
                                                budgetForm.errors.bank_info_description }}</p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <div class="flex justify-end space-x-4 mt-8 border-t pt-5">
                            <button type="button" @click="cancelForm" class="simple-button-neutral">Cancelar</button>
                            <PrimaryButton type="submit"
                                :disabled="budgetForm.processing || !canEdit || !budgetForm.client_id">
                                Salvar Alterações
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
