    <script setup>
        import CreateUpdateModal from '@/Components/CreateUpdateModal.vue';
        import { CheckBadgeIcon, XMarkIcon } from '@heroicons/vue/24/outline';
        import PrimaryButton from '@/Components/PrimaryButton.vue';
        import SecondaryButton from '@/Components/SecondaryButton.vue';
        import { computed } from 'vue';
        import { formatDate } from '@/general.js';

        const emit = defineEmits(['close', 'submit']);
        const props = defineProps({
            showMotivoCancelamentoModal: {
                type: Boolean,
                default: true,
            },
            service: {
                type: Object,
                default: null,
            },
        });

        const close = () => {
            emit('close')
        }

        const submit = () => {
            emit('submit')
        }

</script>
    <template>
        <CreateUpdateModal :show="showMotivoCancelamentoModal" @close="close">
            <template #icon>
                <div
                    class="mx-auto shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-400 sm:mx-0 sm:h-10 sm:w-10">
                    <XMarkIcon class="w-5 h-5 text-white" />
                </div>
            </template>

            <template #title>
                Cancelar serviço
            </template>

            <template #content>
                <form @submit.prevent="submit">
                    <div class="pb-12 space-y-12 divide-y-2">

                        <!-- SEÇÃO DE INFORMAÇÕES DE PAGAMENTOS -->
                        <section class="py-4">
                            <h2 class="text-base font-semibold leading-7 text-gray-900">{{ service.title }}</h2>
                            <p class="mt-1 text-sm leading-6 text-gray-600">Informe o motivo do cancelamento deste
                                serviço</p>

                            <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

                                <div class="sm:col-span-6">
                                    <label for="service-delay-reason"
                                        class="block text-sm font-medium leading-6 text-gray-900">Motivo do
                                        Cancelamento</label>
                                    <div class="mt-2">
                                        <textarea type="text" name="service-delay-reason" id="service-delay-reason"
                                            autocomplete="on" class="simple-input disabled:bg-gray-200"
                                            placeholder="Descreva o motivo do atraso do serviço"
                                            v-model="service.cancellation_reason"></textarea>
                                        <p v-if="service.errors.cancellation_reason" class="text-red-500 text-sm">{{
                                            service.errors.cancellation_reason }}</p>
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
                <PrimaryButton :class="{ 'disabled': (service.processing || !service.cancellation_reason?.length) }"
                    :disabled="(service.processing || !service.cancellation_reason?.length)" @click="submit()">Cancelar
                    Serviço</PrimaryButton>
            </template>
        </CreateUpdateModal>
    </template>