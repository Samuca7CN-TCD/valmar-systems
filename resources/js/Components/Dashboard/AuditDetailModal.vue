<script setup>
    import CreateUpdateModal from '@/Components/CreateUpdateModal.vue';
    import { EyeIcon } from '@heroicons/vue/24/outline';
    import SecondaryButton from '@/Components/SecondaryButton.vue';
    import { computed } from 'vue';
    import { formatDate } from '@/general.js';

    const emit = defineEmits(['close']);

    const props = defineProps({
        show: {
            type: Boolean,
            default: false,
        },
        procedure: {
            type: Object,
            default: null,
        },
    });

    const close = () => {
        emit('close');
    };

    /**
     * Propriedade computada para encontrar e extrair o registro de auditoria correto
     * do array 'records'. O registro de auditoria é aquele que contém 'before_state' ou 'after_state'.
     */
    const auditRecord = computed(() => {
        if (!props.procedure?.records?.length) {
            return null;
        }
        // Procura no array 'records' pelo primeiro registro que seja de auditoria.
        return props.procedure.records.find(record => record.before_state || record.after_state) || null;
    });

    /**
     * Determina o tipo de ação ('create', 'update', 'delete', ou 'custom')
     * para controlar qual seção do modal será renderizada.
     */
    const actionType = computed(() => {
        // Usamos o campo 'type' da ação, que é mais consistente para a lógica.
        const type = props.procedure?.action?.type?.toLowerCase();
        if (!type) {
            return 'unknown';
        }

        if (['create', 'update', 'delete'].includes(type)) {
            return type;
        }

        // Todas as outras ações (pay, conclude, print, etc.) mostrarão um "snapshot" do objeto.
        return 'custom';
    });

    /**
     * Título dinâmico para o modal, usando a forma no passado da ação.
     */
    const modalTitle = computed(() => {
        if (!props.procedure?.action?.name) return 'Detalhes da Ação';
        return `Detalhes: ${props.procedure.action.name}`;
    });

    // Extrai os dados de 'antes' e 'depois' de forma segura do registro de auditoria encontrado.
    const dataBefore = computed(() => auditRecord.value?.before_state || {});
    const dataAfter = computed(() => auditRecord.value?.after_state || {});

    /**
     * Gera uma lista de todas as alterações para ações de 'update'.
     * Compara os campos entre 'before_state' and 'after_state'.
     */
    const changes = computed(() => {
        if (actionType.value !== 'update' || !auditRecord.value) return [];

        const beforeKeys = Object.keys(dataBefore.value);
        const afterKeys = Object.keys(dataAfter.value);
        const allKeys = new Set([...beforeKeys, ...afterKeys]);

        // Campos que geralmente não são úteis para o usuário final e podem ser ignorados.
        const excludedKeys = ['id', 'created_at', 'updated_at', 'deleted_at', 'auditable_id', 'auditable_type'];
        excludedKeys.forEach(key => allKeys.delete(key));

        const diff = [];
        allKeys.forEach(key => {
            const beforeValue = dataBefore.value[key];
            const afterValue = dataAfter.value[key];

            // Compara os valores como strings JSON para lidar com objetos/arrays de forma confiável.
            if (JSON.stringify(beforeValue) !== JSON.stringify(afterValue)) {
                diff.push({
                    field: key.replace(/_/g, ' '), // Formata o nome do campo para leitura
                    before: beforeValue,
                    after: afterValue,
                });
            }
        });
        return diff;
    });

    /**
     * Formata os valores para exibição, tratando nulos, booleanos, imagens e objetos.
     */
    const formatValue = (value) => {
        if (value === null || value === undefined) {
            return '<span class="italic text-gray-500">N/A</span>';
        }

        // Trata valores booleanos ou que se comportam como tal (0, 1, "0", "1")
        if (value === true) {
            return 'Sim';
        }
        if (value === false) {
            return 'Não';
        }

        if (typeof value === 'string') {
            // Verifica se o valor é um caminho de imagem e a renderiza.
            if (value.startsWith('public/img/')) {
                const imageUrl = `/storage/${value.replace('public/', '')}`;
                return `<img src="${imageUrl}" alt="Imagem do item" class="max-w-xs max-h-48 rounded-lg shadow-md border" onerror="this.onerror=null;this.src='/storage/img/items/default.jpeg'">`;
            }

            // Verifica se o valor é uma data no formato YYYY-MM-DD
            let dateRegex = /^\d{4}-\d{2}-\d{2}$/;
            if (dateRegex.test(value)) {
                return formatDate(value); // Usa o formato padrão de formatDate (dd/mm/yyyy)
            }

            dateRegex = /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/;
            if (dateRegex.test(value)) {
                return formatDate(value, 'reading_date_time'); // Usa o formato padrão de formatDate (dd/mm/yyyy)
            }

            // Detecta se é um JSON de contatos e formata de maneira legível.
            try {
                const parsed = JSON.parse(value);
                if (Array.isArray(parsed)) {
                    return parsed.join(', ');
                }
            } catch (e) {
                // Não é um JSON, continua o fluxo normal.
            }
        }

        // Exibe outros objetos/arrays como JSON formatado para facilitar a leitura.
        if (typeof value === 'object') {
            return `<pre class="whitespace-pre-wrap text-xs bg-gray-100 p-2 rounded">${JSON.stringify(value, null, 2)}</pre>`;
        }

        return String(value);
    };

</script>

<template>
    <CreateUpdateModal :show="show" @close="close" :max-width="'5xl'">
        <template #icon>
            <div
                class="mx-auto shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                <EyeIcon class="w-6 h-6 text-blue-600" />
            </div>
        </template>

        <template #title>
            <label class="capitalize">{{ modalTitle }}</label>
        </template>

        <template #content>
            <!-- Informações do cabeçalho da Ação -->
            <div v-if="procedure" class="text-sm text-gray-600 mb-6 border-b pb-4">
                Ação realizada por <strong>{{ procedure.user.name }}</strong> em <strong>{{
                    formatDate(procedure.created_at, 'reading_date_time') }}</strong> no departamento de <strong>{{
                        procedure.department.name }}</strong>.
            </div>

            <!-- Mensagem para quando nenhum registro de auditoria é encontrado -->
            <div v-if="!auditRecord" class="text-center text-gray-500 py-4">
                Nenhum detalhe de alteração encontrado para esta ação.
            </div>

            <div v-else>
                <!-- Visualização para EDIÇÃO (UPDATE) -->
                <div v-if="actionType === 'update'">
                    <h3 class="text-base/7 font-semibold text-gray-900">Campos Alterados <span
                            class="text-xs text-neutral-400">(os campos editados estão
                            em
                            destaque)</span></h3>
                    <div class="mt-6 border-t border-gray-100">
                        <dl class="divide-y divide-gray-200">
                            <!-- A linha agora tem um fundo e borda para destacar a alteração -->
                            <div v-for="change in changes" :key="change.field"
                                class="px-4 py-2 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0"
                                :class="{ 'bg-yellow-100': change.before != change.after }">
                                <dt class="text-sm/6 font-medium text-gray-900 capitalize">{{ change.field }}</dt>
                                <dd class="mt-1 text-sm/6 text-gray-700 sm:col-span-2 sm:mt-0">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <span class="text-red-500">Antes:</span>
                                            <div class="mt-1 break-words flex justify-center sm:justify-start"
                                                v-html="formatValue(change.before)"></div>
                                        </div>
                                        <div>
                                            <span class="text-green-500">Depois:</span>
                                            <div class="mt-1 break-words flex justify-center sm:justify-start"
                                                v-html="formatValue(change.after)"></div>
                                        </div>
                                    </div>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Visualização para CRIAÇÃO (CREATE) -->
                <div v-else-if="actionType === 'create'">
                    <h3 class="text-base/7 font-semibold text-gray-900">Dados Criados</h3>
                    <div class="mt-6 border-t border-gray-100">
                        <dl class="divide-y divide-gray-200">
                            <div v-for="(value, key) in dataAfter" :key="key"
                                v-if="!['id', 'created_at', 'updated_at', 'deleted_at', 'auditable_id', 'auditable_type'].includes(key)"
                                class="px-4 py-2 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                <dt class="text-sm/6 font-medium text-gray-900 capitalize">{{ key.replace(/_/g, ' ') }}
                                </dt>
                                <dd class="mt-1 text-sm/6 text-gray-700 sm:col-span-2 sm:mt-0 break-words flex justify-center sm:justify-start"
                                    v-html="formatValue(value)"></dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Visualização para EXCLUSÃO (DELETE) -->
                <div v-else-if="actionType === 'delete'">
                    <h3 class="text-base/7 font-semibold text-gray-900">Dados Excluídos</h3>
                    <div class="mt-6 border-t border-gray-100">
                        <dl class="divide-y divide-gray-200">
                            <div v-for="(value, key) in dataBefore" :key="key"
                                v-if="!['id', 'created_at', 'updated_at', 'deleted_at', 'auditable_id', 'auditable_type'].includes(key)"
                                class="px-4 py-2 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                <dt class="text-sm/6 font-medium text-gray-900 capitalize">{{ key.replace(/_/g, ' ') }}
                                </dt>
                                <dd class="mt-1 text-sm/6 text-gray-700 sm:col-span-2 sm:mt-0 break-words flex justify-center sm:justify-start"
                                    v-html="formatValue(value)"></dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Visualização para AÇÕES CUSTOMIZADAS (PAY, PRINT, etc.) -->
                <div v-else>
                    <h3 class="text-base/7 font-semibold text-gray-900">Dados do Objeto no Momento da Ação</h3>
                    <div class="mt-6 border-t border-gray-100">
                        <dl class="divide-y divide-gray-200">
                            <div v-for="(value, key) in dataAfter" :key="key"
                                v-if="!['id', 'created_at', 'updated_at', 'deleted_at', 'auditable_id', 'auditable_type'].includes(key)"
                                class="px-4 py-2 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                <dt class="text-sm/6 font-medium text-gray-900 capitalize">{{ key.replace(/_/g, ' ') }}
                                </dt>
                                <dd class="mt-1 text-sm/6 text-gray-700 sm:col-span-2 sm:mt-0 break-words flex justify-center sm:justify-start"
                                    v-html="formatValue(value)"></dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click="close">
                Fechar
            </SecondaryButton>
        </template>
    </CreateUpdateModal>
</template>
