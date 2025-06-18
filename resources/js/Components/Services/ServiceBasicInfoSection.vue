<script setup>
    import SelectSearchSell from '@/Components/SelectSearchSell.vue'; //	
    import { calcDeadlineDays, formatDate, toMoney } from '@/general.js';

    const props = defineProps({
        service: Object,
        sells_list: Array,
        disable_services_inputs: Boolean,
        modal_mode: String,
    });
</script>

<template>
    <section class="py-4">
        <h2 class="text-base font-semibold leading-7 text-gray-900">Informações Básicas</h2>
        <p class="mt-1 text-sm leading-6 text-gray-600">Informações sobre o serviço como título do serviço, nome do
            cliente, valores de pagamento e prazo para conclusão</p>

        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
            <div class="sm:col-span-6">
                <label for="service-title"
                    class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Título</label>
                <div class="mt-2">
                    <input type="text" name="service-title" id="service-title" autocomplete="on"
                        class="simple-input disabled:bg-gray-200" autofocus="true" placeholder="Título da título"
                        :disabled="disable_services_inputs" v-model="service.title" required>
                    <p v-if="service.errors.title" class="text-red-500 text-sm">{{ service.errors.title }}</p>
                </div>
            </div>

            <div class="sm:col-span-6">
                <label for="service-client"
                    class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Cliente</label>

                <div class="mt-2">
                    <input type="text" name="service-client" id="service-client" autocomplete="on"
                        class="simple-input disabled:bg-gray-200" placeholder="Nome do cliente"
                        :disabled="disable_services_inputs" v-model="service.client" required>
                    <p v-if="service.errors.client" class="text-red-500 text-sm">{{ service.errors.client }}</p>
                </div>
            </div>

            <div class="sm:col-span-3">
                <label for="service-value"
                    class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Valor do
                    Serviço (Ex: Mão de Obra)</label>

                <div class="mt-2">
                    <input id="service-value" name="service-value" type="number" step="0.01" min="0" autocomplete="off"
                        class="simple-input disabled:bg-gray-200" placeholder="Valor da mão de obra"
                        :disabled="disable_services_inputs" v-model="service.service_value" required>
                </div>
            </div>

            <div class="sm:col-span-3">
                <label for="service-sell" class="block text-sm font-medium leading-6 text-gray-900">Venda
                    relacionada</label>
                <div class="mt-2">
                    <SelectSearchSell :options="sells_list" @update:modelValue="service.previous_id = $event"
                        :disable_services_inputs="disable_services_inputs" :initialValue="service.previous_id" />
                    <p v-if="service.errors.previous_id" class="text-red-500 text-sm">{{ service.errors.previous_id }}
                    </p>
                </div>
            </div>

            <div class="sm:col-span-6">
                <label for="service-total-amount" class="block text-sm font-medium leading-6 text-gray-900">Valor
                    Total</label>

                <div class="mt-2">
                    <input id="service-total-amount" name="service-total-amount" type="text"
                        :value="toMoney(service.total_value)" class="simple-input bg-gray-100 cursor-not-allowed"
                        readonly>
                    <p v-if="service.errors.total_value" class="text-red-500 text-sm">{{ service.errors.total_value }}
                    </p>
                </div>
            </div>

            <div class="sm:col-span-6">
                <label for="service-deadline"
                    class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Prazo para
                    conclusão</label>
                <div class="mt-2">
                    <input type="date" name="service-deadline" id="service-deadline" autocomplete="on"
                        class="simple-input disabled:bg-gray-200" autofocus="true" placeholder="Prazo para conclusão"
                        :min="formatDate()" :disabled="disable_services_inputs || modal_mode !== 'create'"
                        v-model="service.deadline" required>
                    <p v-if="service.errors.deadline" class="text-red-500 text-sm">{{ service.errors.deadline }}</p>

                </div>
            </div>

            <div class="sm:col-span-6" v-if="modal_mode !== 'create' && calcDeadlineDays(service.deadline) < 4">
                <label for="service-delay-reason" class="block text-sm font-medium leading-6 text-gray-900">Motivo do
                    Atraso</label>
                <div class="mt-2">
                    <textarea type="text" name="service-delay-reason" id="service-delay-reason" autocomplete="on"
                        class="simple-input disabled:bg-gray-200" placeholder="Descreva o motivo do atraso do serviço"
                        :disabled="disable_services_inputs" v-model="service.delay_reason"></textarea>
                    <p v-if="service.errors.delay_reason" class="text-red-500 text-sm">{{ service.errors.delay_reason }}
                    </p>
                </div>
            </div>

            <div class="sm:col-span-6">
                <label for="service-observations"
                    class="block text-sm font-medium leading-6 text-gray-900">Observações</label>
                <div class="mt-2">
                    <textarea type="text" name="service-observations" id="service-observations" autocomplete="on"
                        class="simple-input disabled:bg-gray-200"
                        placeholder="Descreva a título mais detalhadamente ou insira informações adicionais"
                        :disabled="disable_services_inputs" v-model="service.observations"></textarea>
                    <p v-if="service.errors.observations" class="text-red-500 text-sm">{{ service.errors.observations }}
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>