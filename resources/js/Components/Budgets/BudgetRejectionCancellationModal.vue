<script setup>
    import CreateUpdateModal from '@/Components/CreateUpdateModal.vue';
    import { CheckBadgeIcon, XMarkIcon } from '@heroicons/vue/24/outline'; // Ajustei para usar CheckBadgeIcon e XMarkIcon
    import PrimaryButton from '@/Components/PrimaryButton.vue';
    import SecondaryButton from '@/Components/SecondaryButton.vue';
    import { computed } from 'vue'; // Computed é útil para o título/ícone

    const emit = defineEmits(['close', 'submit']);

    const props = defineProps({
        show: { // Renomeado de showMotivoCancelamentoModal para show
            type: Boolean,
            default: false, // Default para false
        },
        budget: { // Agora é 'budget' em vez de 'service'
            type: Object,
            default: null, // Deve conter id, actionType, rejection_reason, cancellation_reason
        },
    });

    // Computed para o título do modal
    const modalTitle = computed(() => {
        if (!props.budget || !props.budget.actionType) return 'Ação de Orçamento';
        switch (props.budget.actionType) {
            case 'reject': return 'Rejeitar Orçamento';
            case 'cancel': return 'Cancelar Orçamento';
            default: return 'Ação de Orçamento';
        }
    });

    // Computed para o ícone do modal
    const modalIcon = computed(() => {
        if (!props.budget || !props.budget.actionType) return XMarkIcon; // Default
        switch (props.budget.actionType) {
            case 'reject': return XMarkIcon; // Ícone para rejeitar
            case 'cancel': return XMarkIcon; // Ícone para cancelar
            default: return XMarkIcon;
        }
    });

    // Computed para a cor de fundo do ícone
    const iconBgClass = computed(() => {
        if (!props.budget || !props.budget.actionType) return 'bg-gray-500';
        switch (props.budget.actionType) {
            case 'reject': return 'bg-orange-500'; // Vermelho para rejeitar
            case 'cancel': return 'bg-red-500'; // Laranja para cancelar
            default: return 'bg-gray-500';
        }
    });

    // Computed para o placeholder do textarea
    const textareaPlaceholder = computed(() => {
        if (!props.budget || !props.budget.actionType) return 'Descreva o motivo...';
        switch (props.budget.actionType) {
            case 'reject': return 'Descreva o motivo da rejeição do orçamento';
            case 'cancel': return 'Descreva o motivo do cancelamento do orçamento';
            default: return 'Descreva o motivo...';
        }
    });

    // Computed para o v-model do motivo (rejeição ou cancelamento)
    const reasonModel = computed({
        get() {
            return props.budget.actionType === 'reject' ? props.budget.rejection_reason : props.budget.cancellation_reason;
        },
        set(value) {
            if (props.budget.actionType === 'reject') {
                props.budget.rejection_reason = value;
            } else if (props.budget.actionType === 'cancel') {
                props.budget.cancellation_reason = value;
            }
        }
    });

    // Computed para o texto do botão primário
    const primaryButtonText = computed(() => {
        if (!props.budget || !props.budget.actionType) return 'Confirmar';
        switch (props.budget.actionType) {
            case 'reject': return 'Rejeitar Orçamento';
            case 'cancel': return 'Cancelar Orçamento';
            default: return 'Confirmar';
        }
    });

    // Computed para desabilitar o botão primário
    const isPrimaryButtonDisabled = computed(() => {
        if (!props.budget) return true;
        const currentReason = reasonModel.value;
        return props.budget.processing || !currentReason || currentReason.length < 10;
    });

    const close = () => {
        emit('close');
    };

    const submit = () => {
        emit('submit');
    };
</script>

<template>
    <CreateUpdateModal :show="show" @close="close">
        <template #icon>
            <div class="mx-auto shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10"
                :class="iconBgClass">
                <component :is="modalIcon" class="w-5 h-5 text-white" />
            </div>
        </template>

        <template #title>
            {{ modalTitle }}
        </template>

        <template #content>
            <form @submit.prevent="submit">
                <div class="pb-12 space-y-12 divide-y-2">
                    <section class="py-4">
                        <h2 class="text-base font-semibold leading-7 text-gray-900">{{ budget?.client_name }}</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">
                            {{ budget?.description
                                ? 'Orçamento: ' + budget.description
                                : 'Informe o motivo para prosseguir.' }}
                        </p>

                        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            <div class="sm:col-span-6">
                                <label for="budget-reason"
                                    class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Motivo</label>
                                <div class="mt-2">
                                    <textarea type="text" name="budget-reason" id="budget-reason" autocomplete="off"
                                        class="simple-input" :placeholder="textareaPlaceholder"
                                        v-model="reasonModel"></textarea>
                                    <p v-if="budget?.errors?.rejection_reason || budget?.errors?.cancellation_reason"
                                        class="text-red-500 text-sm mt-1">
                                        {{ budget.errors.rejection_reason || budget.errors.cancellation_reason }}
                                    </p>
                                    <p class="text-gray-500 text-sm mt-1">O motivo deve ter pelo menos 10 caracteres.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </form>
        </template>

        <template #footer>
            <SecondaryButton @click="close()">
                Cancelar
            </SecondaryButton>
            <PrimaryButton :disabled="isPrimaryButtonDisabled" @click="submit()">
                {{ primaryButtonText }}
            </PrimaryButton>
        </template>
    </CreateUpdateModal>
</template>