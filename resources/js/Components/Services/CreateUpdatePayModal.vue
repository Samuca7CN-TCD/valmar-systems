<script setup>
    import CreateUpdateModal from '@/Components/CreateUpdateModal.vue';
    import { PlusIcon, PencilIcon, XMarkIcon, BanknotesIcon, EyeIcon } from '@heroicons/vue/24/outline';
    import PrimaryButton from '@/Components/PrimaryButton.vue';
    import SecondaryButton from '@/Components/SecondaryButton.vue';
    import { ref, computed, watch } from 'vue';
    import { formatDate, toMoney } from '@/general.js';
    import { useForm } from '@inertiajs/vue3';

    // Importar os novos componentes
    import ServiceBasicInfoSection from '@/Components/Services/ServiceBasicInfoSection.vue';
    import ServiceEnableRecordsSection from '@/Components/Services/ServiceEnableRecordsSection.vue';
    import ServiceRecordsListSection from '@/Components/Services/ServiceRecordsListSection.vue';

    const emit = defineEmits(['close', 'submit']);

    const props = defineProps({
        modal: Object,
        service: {
            type: Object,
            default: null,
        },
        sells_list: {
            type: Array,
            default: [],
        }
    });

    const calc_record = computed(() => props.service.records_list.data.length + 1);
    const remaining_amount = computed(() => {
        // Certifique-se de que `enable_records_inputs` seja acessado com `.value`
        return enable_records_inputs.value && (props.service.total_value - props.service.records_list.data.reduce((sum, record) => sum + record.amount, 0));
    });

    const invalid_record_date = computed(() => {
        const today = new Date();
        return props.service.records_list.data.some(data => new Date(data.date) > today);
    });

    const invalid_record_amount = computed(() => {
        return props.service.records_list.data.some(data => data.amount <= 0);
    });

    const disable_services_inputs = computed(() => {
        return props.modal.mode === 'pay' || props.modal.mode === 'see'
    });

    const enable_records_inputs = computed(() => {
        return props.modal.mode !== 'create' || props.service.records_list.enable_records;
    });

    const can_add_record = computed(() =>
        !!(props.service.title.length && props.service?.client_id && props.service.total_value > 0)
    );

    watch(
        () => [props.service.service_value, props.service.previous_id],
        ([newServiceValue, newSellId]) => {
            // Garante que o valor do serviço seja um número
            const serviceValue = parseFloat(newServiceValue) || 0;

            // Encontra a venda selecionada e pega seu valor
            const selectedSell = props.sells_list.find(sell => sell.id === newSellId);
            const sellValue = selectedSell ? parseFloat(selectedSell.accounting.total_value) : 0;

            // Soma os valores e atualiza o 'total_value' do formulário
            props.service.total_value = serviceValue + sellValue;
        },
        {
            immediate: true // 'immediate' faz com que a lógica rode assim que o modal é montado
        }
    );

    // O objeto `record` do useForm não deve ter um 'id' se for para criar um novo.
    // O ID deve ser gerado pelo backend.
    const record = useForm({
        amount: 0,
        past: false,
        register_date: formatDate(),
        payment_method: 'cartao_credito',
        filepath: null,
    });

    const insert_file = ref(false);

    const toggle_enable_records = () => {
        if (props.service.records_list.enable_records && props.service.records_list.data.length) {
            if (!confirm('Tem certeza que deseja desmarcar? Todos os registros serão excluídos!')) {
                return;
            } else {
                props.service.records_list.data.length = 0;
                record.reset();
            }
        }
        props.service.records_list.enable_records = !props.service.records_list.enable_records;
    };

    const addRecord = () => {
        if (!record.amount || record.amount > remaining_amount.value) {
            record.errors.amount = `O valor do registro deve ser maior que R$ 0,00 e menor que ${toMoney(remaining_amount.value)}`;
            return;
        }
        record.errors.amount = "";

        if (!record.register_date || new Date(record.register_date) > new Date()) {
            record.errors.date = `A data do registro deve ser menor ou igual a ${formatDate()}`;
            return;
        }
        record.errors.date = "";

        if (!record.payment_method) {
            record.errors.payment_method = `Um método de pagamento deve ser selecionado`;
            return;
        }
        record.errors.payment_method = "";

        if (!insert_file.value) record.filepath = null;

        // Ao adicionar um novo registro à lista 'data', adicionamos o 'id' temporário para o frontend
        // mas este 'id' não faz parte do objeto `record` do useForm que é enviado ao backend.
        props.service.records_list.data.push({
            id: calc_record.value, // Adiciona o ID temporário apenas para o v-for no frontend
            amount: record.amount,
            past: record.past,
            register_date: record.register_date,
            payment_method: record.payment_method,
            filepath: record.filepath,
        });

        insert_file.value = false;
        record.reset();
    };

    const removeRecord = (record_id, record_item = null) => {
        if (props.modal.mode === 'create') {
            // Em modo 'create', o ID é temporário, apenas remove do array local
            props.service.records_list.data = props.service.records_list.data.filter((r) => r.id !== record_id);
            return;
        } else {
            if (confirm("Você tem certeza que deseja excluir esse registro? (Essa ação não pode ser desfeita)")) {
                // Em outros modos, o ID é do banco de dados, então chama o backend
                // Certifique-se que record_item é um objeto Inertia.js Form ou tem o método delete
                // Se record_item for um objeto Inertia.js Form, use ele. Caso contrário, use o 'service' principal se ele for um Form
                // ou ajuste para fazer um request Inertia.js diretamente.
                // Vou assumir que 'service' é um Form.
                // A linha abaixo estava incorreta na última resposta, vou corrigi-la para usar `useForm` ou uma requisição Inertia.js.
                // Se o 'record_item' é um item simples de um array, você precisa criar um novo Form para deletá-lo.
                useForm({}).delete(route('records.destroy', record_id), {
                    preserveScroll: true,
                    onSuccess: () => {
                        // Após a deleção bem-sucedida, atualiza a lista de registros no frontend.
                        // Certifique-se que `data` é o array correto.
                        props.service.records_list.data = props.service.records_list.data.filter((r) => r.id !== record_id);
                    },
                    onError: (errors) => {
                        console.error('Erro ao deletar registro:', errors);
                        alert('Erro ao deletar registro. Verifique o console para mais detalhes.');
                    }
                });
            }
        }
    };

    const close = () => {
        emit('close')
    }

    const submit = () => {
        emit('submit')
    }
</script>

<template>
    <CreateUpdateModal :show="modal.show" @close="close">
        <template #icon>
            <div class="mx-auto shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-400 sm:mx-0 sm:h-10 sm:w-10"
                :class="{ 'bg-yellow-400': modal.mode === 'update', 'bg-green-400': modal.mode === 'pay', 'bg-gray-400': modal.mode === 'see' }">
                <PlusIcon v-if="modal.mode === 'create'" class="w-5 h-5 text-white" />
                <PencilIcon v-if="modal.mode === 'update'" class="w-5 h-5 text-white" />
                <BanknotesIcon v-if="modal.mode === 'pay'" class="w-5 h-5 text-white" />
                <EyeIcon v-if="modal.mode === 'see'" class="w-5 h-5 text-white" />
            </div>
        </template>

        <template #title>
            {{ modal.title }}
        </template>

        <template #content>
            <form @submit.prevent="submit">
                <div class="pb-12 space-y-12 divide-y-2">

                    <ServiceBasicInfoSection :service="service" :sells_list="sells_list"
                        :disable_services_inputs="disable_services_inputs" :modal_mode="modal.mode" />

                    <ServiceEnableRecordsSection :modal_mode="modal.mode" :can_add_record="can_add_record"
                        :enable_records="service.records_list.enable_records"
                        @toggle-enable-records="toggle_enable_records" />

                    <ServiceRecordsListSection v-if="enable_records_inputs" :modal_mode="modal.mode" :service="service"
                        :record_form="record" :remaining_amount="remaining_amount"
                        :invalid_record_date="invalid_record_date" :invalid_record_amount="invalid_record_amount"
                        @add-record="addRecord" @remove-record="removeRecord" />
                </div>
            </form>
        </template>

        <template #footer>
            <SecondaryButton v-if="modal.mode !== 'see'" :class="{ 'disabled': service.processing }" @click="close()">
                Cancelar
            </SecondaryButton>
            <PrimaryButton
                :class="{ 'disabled': (service.processing || remaining_amount < 0 || invalid_record_date || invalid_record_amount) }"
                :disabled="(service.processing || remaining_amount < 0 || invalid_record_date || invalid_record_amount)"
                @click="modal.mode === 'see' ? close() : submit()">{{ modal.primary_button_txt }}</PrimaryButton>
        </template>
    </CreateUpdateModal>
</template>