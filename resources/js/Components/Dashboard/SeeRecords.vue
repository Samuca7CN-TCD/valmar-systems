<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios'; // Certifique-se de que o Axios está instalado
import { PaperClipIcon, EyeIcon } from '@heroicons/vue/24/outline';
import CreateUpdateModal from '@/Components/CreateUpdateModal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { toMoney, formatDate, formatPhoneNumber, calcDeadlineDays } from '@/general.js';

const emit = defineEmits(['close']);
const props = defineProps({
    modal: Boolean,
    procedure: Array,
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
            <div class="px-4 sm:px-0">
                <h3 class="text-base font-semibold leading-7 text-gray-900 capitalize">{{ procedure.action?.name }}
                    -
                    {{ procedure.department?.name }}</h3>
                <p class="mt-1 max-w-2xl text-sm leading-6 text-gray-500">{{ procedure.user?.name }}
                    {{ procedure.user?.surname }}</p>
            </div>
        </template>

        <template #content>
            <div>


            </div>
            <pre>
                <ul v-if="procedure.department.type === 'warehouse'">
                    <li></li>
                </ul>
                {{ procedure }}
                </pre>
        </template>

        <template #footer>
            <SecondaryButton @click="close()">
                Fechar
            </SecondaryButton>
        </template>
    </CreateUpdateModal>
</template>