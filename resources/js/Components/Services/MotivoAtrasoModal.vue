    <script setup>
        import CreateUpdateModal from '@/Components/CreateUpdateModal.vue';
        import { PlusIcon, PencilIcon, XMarkIcon, BanknotesIcon, EyeIcon, CheckBadgeIcon } from '@heroicons/vue/24/outline';
        import PrimaryButton from '@/Components/PrimaryButton.vue';
        import SecondaryButton from '@/Components/SecondaryButton.vue';
        import { ref, computed } from 'vue';
        import { calcDeadlineDays, formatDate, toMoney } from '@/general.js';
        import { useForm } from '@inertiajs/vue3';

        const emit = defineEmits(['close', 'submit']);
        const props = defineProps({
            showMotivoAtrasoModal: {
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

        const needsDelayMotive = computed(() => {
            if (!props.service.completion_date || !props.service.deadline) {
                return false; // Não pode calcular se as datas não existem
            }

            // Converter as datas para objetos Date para cálculo correto
            // Atenção: O construtor Date() pode ter comportamento inconsistente com strings YYYY-MM-DD em alguns browsers/locales
            // É mais seguro parsear manualmente ou usar uma biblioteca (ex: Moment.js, date-fns)
            // Para fins de exemplo, vamos assumir que `new Date()` funciona com 'YYYY-MM-DD' aqui.
            // Se `calcDeadlineDays` já faz essa conversão e cálculo, pode usá-la.
            const deadlineDate = new Date(props.service.deadline + 'T00:00:00'); // Adiciona T00:00:00 para evitar problemas de fuso horário
            const completionDate = new Date(props.service.completion_date + 'T00:00:00');

            // Calcula a diferença em milissegundos
            const diffTime = deadlineDate.getTime() - completionDate.getTime();
            // Converte para dias
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); // Math.ceil para arredondar para cima

            return diffDays < 4; // Se a data de entrega for 4 ou mais dias DEPOIS do prazo
        });

        const mustHasDelayReason = computed(() => {
            return needsDelayMotive.value && !props.service.delay_reason?.length
        })
</script>
    <template>
        <CreateUpdateModal :show="showMotivoAtrasoModal" @close="close">
            <template #icon>
                <div
                    class="mx-auto shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-400 sm:mx-0 sm:h-10 sm:w-10">
                    <CheckBadgeIcon class="w-5 h-5 text-white" />
                </div>
            </template>

            <template #title>
                Entregar serviço
            </template>

            <template #content>
                <form @submit.prevent="submit">
                    <div class="pb-12 space-y-12 divide-y-2">

                        <!-- SEÇÃO DE INFORMAÇÕES DE PAGAMENTOS -->
                        <section class="py-4">
                            <h2 class="text-base font-semibold leading-7 text-gray-900">Dados da entrega</h2>
                            <p class="mt-1 text-sm leading-6 text-gray-600">Informe a data da entrega do serviço e, se o
                                deadline for a menos de 4 dias da data da entrega, informe o motivo do atraso.</p>

                            <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                <div class="sm:col-span-6">
                                    <label for="service-deadline"
                                        class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Data
                                        da
                                        entrega</label>
                                    <div class="mt-2">
                                        <input type="date" name="service-completion-date" id="service-completion-date"
                                            autocomplete="on" class="simple-input disabled:bg-gray-200" autofocus="true"
                                            placeholder="Data de entrega do serviço" :max="formatDate()"
                                            v-model="service.completion_date" required>
                                        <p v-if="service.errors.completion_date" class="text-red-500 text-sm">{{
                                            service.errors.completion_date }}</p>
                                    </div>
                                </div>

                                <div class="sm:col-span-6" v-if="needsDelayMotive">
                                    <label for="service-delay-reason"
                                        class="block text-sm font-medium leading-6 text-gray-900">Motivo do
                                        Atraso</label>
                                    <div class="mt-2">
                                        <textarea type="text" name="service-delay-reason" id="service-delay-reason"
                                            autocomplete="on" class="simple-input disabled:bg-gray-200"
                                            placeholder="Descreva o motivo do atraso do serviço"
                                            v-model="service.delay_reason"></textarea>
                                        <p v-if="service.errors.delay_reason" class="text-red-500 text-sm">{{
                                            service.errors.delay_reason }}</p>
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
                <PrimaryButton
                    :class="{ 'disabled': (service.processing || !service.completion_date?.length || mustHasDelayReason) }"
                    :disabled="(service.processing || !service.completion_date?.length || mustHasDelayReason)"
                    @click="submit()">Entregar</PrimaryButton>
            </template>
        </CreateUpdateModal>
    </template>