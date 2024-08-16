<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";

const props = defineProps({
    options: {
        type: Array,
        required: true,
        default: () => [],
    },
});

const emit = defineEmits(['update:modelValue']); // Define o evento personalizado

const search = ref("");
const dropdownOpen = ref(false);

const openDropdown = () => {
    dropdownOpen.value = true;
};

const closeDropdown = () => {
    dropdownOpen.value = false;
};

const selectOption = (option) => {
    console.log("OPTIONS: ", option)
    if (typeof option === "string") {
        search.value = option;
    } else {
        search.value = `${option.motive} (${option.entity_name})`;
        closeDropdown();
    }
    console.log("sdlzfjk: ", search.value)
    emit('update:modelValue', search.value); // Emite o evento com o valor selecionado
};

const filteredOptions = computed(() => {
    return props.options.filter(
        (option) =>
            option.motive.toLowerCase().includes(search.value.toLowerCase()) ||
            option.entity_name.toLowerCase().includes(search.value.toLowerCase())
    );
});

const handleClickOutside = (event) => {
    const dropdownElement = event.target.closest('.dropdown-container');
    if (!dropdownElement) {
        closeDropdown();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

</script>

<template>
    <div class="relative dropdown-container">
        <input v-model="search" @focus="openDropdown" @click.stop @input="selectOption($event.target.value)"
            class="simple-input" type="text" placeholder="Selecione ou escreva um motivo" />
        <ul v-if="dropdownOpen"
            class="absolute w-full mt-2 bg-white border rounded shadow-md max-h-40 overflow-y-auto z-10">
            <li v-for="(option, index) in filteredOptions" :key="index" @click="selectOption(option)"
                class="px-4 py-2 cursor-pointer hover:bg-gray-200">
                <div>{{ option.motive }} ({{ option.entity_name }})</div>
            </li>
            <li v-if="filteredOptions.length === 0" class="px-4 py-2 text-gray-500">
                Nenhuma opção encontrada
            </li>
        </ul>
    </div>
</template>
