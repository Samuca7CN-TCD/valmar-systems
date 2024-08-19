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
        search.value = '';
        emit('update:modelValue', option);
        closeDropdown();
    }
};

const filteredOptions = computed(() => {
    return props.options.filter(
        (option) =>
            option.name.toLowerCase().includes(search.value.toLowerCase())
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
            class="absolute w-full mt-2 bg-white border rounded shadow-md max-h-52 overflow-y-auto z-10">
            <li v-for="(option, index) in filteredOptions" :key="index" @click="selectOption(option)"
                class="px-4 py-2 cursor-pointer hover:bg-gray-200">
                <div><span :class="{
            'text-blue-500': option.quantity != 0 && option.quantity >= option.max_quantity,
            'text-green-500': option.quantity < option.max_quantity && option.quantity >= option.min_quantity,
            'text-yellow-500': option.quantity < option.min_quantity && option.quantity > 0,
            'text-red-500': option.quantity == 0
        }">({{ option.quantity }})</span> {{ option.name }}</div>
            </li>
            <li v-if="filteredOptions.length === 0" class="px-4 py-2 text-gray-500">
                Nenhuma opção encontrada
            </li>
        </ul>
    </div>
</template>
