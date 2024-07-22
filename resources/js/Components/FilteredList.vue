<script setup>
import { MagnifyingGlassIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { ref, computed } from 'vue'

const props = defineProps({
    base_list: Object,
})

const search_text = ref("")
const filteredList = computed(() => {
    const copy = JSON.parse(JSON.stringify(props.base_list))
    if (search_text.value) {
        const filteredCategories = copy.filter(category => {
            category.items = category.items.filter(item => item.name.toLowerCase().includes(search_text.value.toLowerCase()))
            return category.items.length > 0
        })
        return filteredCategories
    }
    return copy
})

const clearInput = () => {
    search_text.value = ""
    document.querySelector('#search-obj').focus()
}

</script>
<template>
    <div class="py-6 space-y-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                <div class="mt-2 flex flex-row space-x-2 items-center">
                    <label v-if="search_text" for="search-obj" class="cursor-pointer" title="Limpar Pesquisa"
                        @click="clearInput()">
                        <XMarkIcon class="w-5 h-5 text-gray-400 hover:text-gray-700 active:text-gray-900" />
                    </label>
                    <label v-else for="search-obj" class="cursor-pointer" title="Pesquisar">
                        <MagnifyingGlassIcon class="w-5 h-5 text-gray-400 hover:text-gray-700 active:text-gray-900" />
                    </label>
                    <input type="text" name="search-obj" id="search-obj" autocomplete="on" class="simple-input"
                        autofocus="true" placeholder="Pesquise um item pelo nome..." v-model="search_text">
                </div>
            </div>
        </div>
        <slot :filteredList="filteredList">Nenhum elemento encontrado!</slot>
    </div>
</template>