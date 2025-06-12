<script setup>
    import { ref, computed, onMounted, onUnmounted, watch } from "vue"; // Importe 'watch' também

    const props = defineProps({
        options: {
            type: Array,
            required: true,
            default: () => [],
        },
        disable_services_inputs: {
            type: Boolean,
            default: true,
        },
        // Nova prop para o valor inicial
        initialValue: {
            type: Number, // Pode ser número (ID) ou string (se for o caso)
            default: null,
        }
    });

    const emit = defineEmits(['update:modelValue']);

    const search = ref("");
    const dropdownOpen = ref(false);
    const selectedSaleId = ref(props.options.find(el => el.id === props.initialValue));

    const openDropdown = () => {
        dropdownOpen.value = true;
    };

    const closeDropdown = () => {
        dropdownOpen.value = false;
    };

    const formatItems = (items) => {
        if (!items || items.length === 0) {
            return 'Nenhum item';
        }
        const itemNames = items.map(item => item.name);
        const truncatedItems = itemNames.join(', ');
        const maxLength = 50;
        return truncatedItems.length > maxLength ? truncatedItems.substring(0, maxLength) + '...' : truncatedItems;
    };

    // Refatoramos a lógica de preenchimento para ser reutilizável
    const fillInputWithSale = (option) => {
        if (option && option.id !== null) {
            search.value = `#${option.id} ${option.entity_name || '-'} | R$ ${option.accounting?.total_value?.toFixed(2) || '0.00'} (${option.date ? new Date(option.date).toLocaleDateString() : '-'})`;
            selectedSaleId.value = option.id;
        } else {
            search.value = "";
            selectedSaleId.value = null;
        }
    };

    const selectOption = (option) => {
        if (typeof option === "string") {
            search.value = option;
            selectedSaleId.value = null;
        } else {
            fillInputWithSale(option?.id ? option : null);
            closeDropdown();
        }
        emit('update:modelValue', selectedSaleId.value);
    };


    const filteredOptions = computed(() => {
        const filtered = props.options.filter(
            (option) =>
                (option.motive && option.motive.toLowerCase().includes(search.value.toLowerCase())) ||
                (option.entity_name && option.entity_name.toLowerCase().includes(search.value.toLowerCase())) ||
                String(option.id).includes(search.value)
        );

        return [
            {
                id: null,
                entity_name: "Nenhuma venda relacionada",
                motive: null,
                date: null,
                accounting: { total_value: 0 }
            },
            ...filtered
        ];
    });


    const handleClickOutside = (event) => {
        const dropdownElement = event.target.closest('.dropdown-container');
        if (!dropdownElement) {
            closeDropdown();
        }
    };

    // Lógica para inicializar o campo quando o componente é montado
    onMounted(() => {
        document.addEventListener('click', handleClickOutside);

        // Se houver um initialValue, tenta encontrar a venda correspondente
        if (props.initialValue !== null && props.initialValue !== undefined) {
            const initialSale = props.options.find(
                (option) => option.id === props.initialValue
            );
            if (initialSale) {
                fillInputWithSale(initialSale);
                emit('update:modelValue', initialSale.id); // Garante que o modelValue é emitido na inicialização
            }
        }
    });

    // Opcional: Adicionar um watcher para `initialValue` se ele puder mudar dinamicamente
    // após a montagem inicial (ex: quando o `service` é resetado ou carregado novamente)
    watch(() => props.initialValue, (newValue) => {
        if (newValue !== null && newValue !== undefined) {
            const initialSale = props.options.find(
                (option) => option.id === newValue
            );
            if (initialSale) {
                fillInputWithSale(initialSale);
                emit('update:modelValue', initialSale.id);
            }
        } else {
            // Se initialValue for limpo, limpa o campo também
            fillInputWithSale(null);
        }
    });

    onUnmounted(() => {
        document.removeEventListener('click', handleClickOutside);
    });
</script>

<template>
    <div class="relative dropdown-container">
        <input v-model="search" @focus="openDropdown" @click.stop @input="selectOption($event.target.value)"
            :disabled="disable_services_inputs" class="simple-input disabled:bg-gray-200" type="text"
            placeholder="Selecione ou escreva o ID, cliente ou motivo da venda" />
        <ul v-if="dropdownOpen"
            class="absolute w-full mt-2 bg-white border rounded shadow-md max-h-40 overflow-y-auto z-10">
            <li v-for="(option, index) in filteredOptions" :key="index" @click="selectOption(option)"
                class="px-4 py-2 space-y-1 cursor-pointer hover:bg-gray-200 text-sm">
                <div v-if="option.id !== null">
                    <div class="flex gap-2 justify-between">
                        <div class="flex gap-2">
                            <div class="font-bold text-gray-800">#{{ option.id }}</div>
                            <div class="text-gray-600">{{ option.entity_name || '-' }}</div>
                        </div>
                        <div class="text-gray-600 justify-end">{{ option.date ? new
                            Date(option.date).toLocaleDateString() : '-'
                        }}
                        </div>
                    </div>
                    <div class="text-gray-600">R${{ option.accounting ? option.accounting.total_value.toFixed(2) :
                        '0.00' }}
                    </div>
                </div>
                <div v-else>
                    <div class="flex gap-2">
                        <div class="text-gray-600">Nenhuma venda relacionada</div>
                    </div>
                </div>
            </li>
            <li v-if="filteredOptions.length === 0" class="px-4 py-2 text-gray-500">
                Nenhuma opção encontrada
            </li>
        </ul>
    </div>
</template>