<script setup>
    import { ref, watch, onMounted } from 'vue';
    import { Link } from '@inertiajs/vue3';
    import axios from 'axios';
    import { debounce } from 'lodash';

    const props = defineProps({
        modelValue: [String, Number, null],
        required: {
            type: Boolean,
            default: false,
        },
        disabled: {
            type: Boolean,
            default: false,
        }
    });

    const emit = defineEmits(['update:modelValue']);

    const searchTerm = ref('');
    const searchResults = ref([]);
    const selectedClientName = ref('');
    const isLoading = ref(false);
    const showResults = ref(false);

    const handleBlur = () => {
        // Esta função envolve o setTimeout para evitar problemas de contexto no template.
        // O atraso permite que o evento mousedown na lista de resultados dispare antes que a lista desapareça.
        setTimeout(() => {
            showResults.value = false;
        }, 200);
    };


    const searchClients = debounce(async () => {
        if (searchTerm.value.length < 2 || searchTerm.value === selectedClientName.value) {
            searchResults.value = [];
            return;
        }
        isLoading.value = true;
        try {
            const response = await axios.get(route('clients.search', { term: searchTerm.value }));
            searchResults.value = response.data;
        } catch (error) {
            console.error('Erro ao buscar clientes:', error);
            searchResults.value = [];
        } finally {
            isLoading.value = false;
        }
    }, 300);

    watch(searchTerm, (newValue) => {
        if (newValue === selectedClientName.value) {
            return;
        }
        if (newValue === '') {
            emit('update:modelValue', null);
            selectedClientName.value = '';
        }
        searchClients();
    });

    const selectClient = (client) => {
        emit('update:modelValue', client.id);
        const displayName = `${client.name} (${client.document})`;
        selectedClientName.value = displayName;
        searchTerm.value = displayName;
        showResults.value = false;
    };

    const clearSelection = () => {
        emit('update:modelValue', null);
        selectedClientName.value = '';
        searchTerm.value = '';
        searchResults.value = [];
    };

    const fetchInitialClient = async () => {
        if (props.modelValue) {
            try {
                const response = await axios.get(route('clients.show', { client: props.modelValue }), {
                    headers: { 'Accept': 'application/json' }
                });

                if (response.data && response.data.client) {
                    const client = response.data.client;
                    const displayName = `${client.name} (${client.document})`;
                    selectedClientName.value = displayName;
                    searchTerm.value = displayName;
                }
            } catch (error) {
                console.error('Erro ao buscar dados do cliente inicial:', error);
            }
        }
    };

    onMounted(() => {
        fetchInitialClient();
    });

</script>

<template>
    <div class="relative">
        <input type="text" v-model="searchTerm" placeholder="Digite para buscar um cliente..."
            @focus="showResults = true" @blur="handleBlur" class="simple-input w-full disabled:bg-gray-200"
            :required="required && !modelValue" :disabled="disabled" />
        <button v-if="modelValue && !disabled" @click="clearSelection" type="button"
            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-red-500"
            title="Limpar seleção">
            &times;
        </button>

        <div v-if="showResults && searchTerm.length > 1 && searchTerm !== selectedClientName"
            class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
            <div v-if="isLoading" class="px-4 py-2 text-sm text-gray-500">Buscando...</div>
            <ul v-else-if="searchResults.length > 0">
                <li v-for="client in searchResults" :key="client.id" @mousedown.prevent="selectClient(client)"
                    class="px-4 py-2 text-sm text-gray-700 cursor-pointer hover:bg-gray-100">
                    {{ client.name }} <span class="text-xs text-gray-500">({{ client.document }})</span>
                </li>
            </ul>
            <div v-else-if="!isLoading" class="px-4 py-2 text-sm text-gray-500">
                Nenhum cliente encontrado.
                <a :href="route('clients.create')" target="_blank" class="text-blue-500 hover:underline">Cadastrar
                    novo?</a>
            </div>
        </div>
    </div>
</template>
