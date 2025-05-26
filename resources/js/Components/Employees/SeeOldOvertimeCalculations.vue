<script setup>
    import { ref, onMounted, watch } from 'vue';
    import axios from 'axios'; // Certifique-se de que o Axios está instalado
    import { PlusIcon, PencilIcon, XMarkIcon, BanknotesIcon, EyeIcon } from '@heroicons/vue/24/outline';
    import CreateUpdateModal from '@/Components/CreateUpdateModal.vue';
    import PrimaryButton from '@/Components/PrimaryButton.vue';
    import SecondaryButton from '@/Components/SecondaryButton.vue';
    import { toMoney, formatDate, formatPhoneNumber, calcDeadlineDays } from '@/general.js';

    const emit = defineEmits(['close']);
    const props = defineProps({
        modal: Boolean,
    });

    const records_list = ref([]);
    const processing = ref(true);
    const records_date = ref(null);

    // A função que obtém os últimos registros
    const get_last_records = async (date = '') => {
        processing.value = true;
        try {
            const response = await axios.get(`${route('employee.overtime_calculation_list')}/${date}`);
            records_list.value = response.data;
        } catch (error) {
            console.error('Erro ao buscar registros:', error);
        } finally {
            processing.value = false;
        }
    };

    // Atualize records_date sempre que records_list mudar
    watch(records_list, () => {
        records_date.value = records_list.value.length ? formatDate(records_list.value[0].created_at) : formatDate();
    }, { immediate: true });

    // Obtém registros ao montar o componente
    onMounted(() => {
        get_last_records();
    });

    const close = () => emit('close');
</script>
<template>
    <CreateUpdateModal :show="modal" :maxWidth="'2xl'" @close="close">
        <template #icon>
            <div
                class="mx-auto shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-400 sm:mx-0 sm:h-10 sm:w-10">
                <EyeIcon class="w-5 h-5 text-white" />
            </div>
        </template>

        <template #title>
            Tabelas anteriores
        </template>

        <template #content>
            <div class="my-3">
                <label for="older-records-date" class="text-xs text-neutral-400">Dia dos registros</label>
                <input type="date" id="older-records-date" name="older-records-date" :max="formatDate()"
                    v-model="records_date" @change="get_last_records($event.target.value)"
                    class="simple-input disabled:opacity-50" :disabled="processing" />
            </div>

            <p v-if="processing" class="w-full text-center">Carregando dados...</p>

            <ul v-if="records_list.length" class="text-sm text-neutral-500">
                <li v-for="(record, index) in records_list"
                    class="flex justify-between p-3 even:bg-neutral-100 hover:bg-neutral-200">
                    <span>{{ record?.procedure?.user?.name }} {{ record?.procedure?.user?.surname }}</span>
                    <span>{{ formatDate(record.created_at, 'reading_date_time') }}</span>
                    <a :href="route('employee.overtime_calculation', record.procedure.id)"
                        class="text-green-500 hover:text-green-700 active:text-green-900">Ver</a>
                </li>
            </ul>
            <p v-else>Nenhum registro encontrado.</p>
        </template>

        <template #footer>
            <SecondaryButton @click="close()">
                Fechar
            </SecondaryButton>
        </template>
    </CreateUpdateModal>
</template>