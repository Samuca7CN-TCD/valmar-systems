<script setup>
import { ref, computed } from 'vue'
import { defineProps } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import FloatButton from '@/Components/FloatButton.vue'
import Checkbox from '@/Components/Checkbox.vue'
import { Head, router } from '@inertiajs/vue3'
import ExtraOptionsButton from '@/Components/ExtraOptionsButton.vue'
import MultiSelect from 'primevue/multiselect'

// =============================================
// Informações exteriores
const props = defineProps({
    page: Object,
    items_list: {
        type: Array,
        default: () => [],
    },
    categories_list: {
        type: Array,
        default: () => [],
    },
    buy_option: {
        type: Boolean,
        default: false,
    }
})

const categories_list_mapped = ref(props.categories_list.map(category => ({
    id: category.id,
    category: category.name_plural,
})))

const selected_categories = ref([...categories_list_mapped.value])

const toggleCategory = (cat) => {
    const index = selected_categories.value.findIndex(el => el.id === cat.id)
    if (index !== -1) {
        selected_categories.value.splice(index, 1)
    } else {
        selected_categories.value.push(cat)
    }
}

const markAll = () => {
    selected_categories.value = [...categories_list_mapped.value]
}

const dismarkAll = () => {
    selected_categories.value = []
}

const items_list_filtered = computed(() => {
    if (selected_categories.value.length) {
        return props.items_list.filter(item =>
            selected_categories.value.some(cat => cat.id == item.category_id)
        )
    }
    return []
})

const hidden_print_display_list = ref([])

const toggle_print_display = (item_id) => {
    const index = hidden_print_display_list.value.indexOf(item_id)
    if (index !== -1) {
        hidden_print_display_list.value.splice(index, 1)
    } else {
        hidden_print_display_list.value.push(item_id)
    }
}

const printList = () => {
    window.print()
}
</script>

<template>

    <Head :title="page.name" />
    <AppLayout :page="page">
        <template #header class="print:hidden">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ buy_option ? "Lista de Compra" : "Listagem Geral de Items" }}
            </h2>
            <FloatButton icon="printer" @click="printList" title="Imprimir Lista" />
        </template>
        <div class="py-12 print:py-0">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 print:max-w-full print:mx-0 print:sm:px-0 print:lg:px-0">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg min-h-[300px] print:shadow-none">
                    <div class="print:hidden flex-col-config p-5 w-full">
                        <ul class="w-full grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-1">
                            <li v-for="cat in categories_list_mapped" :key="cat.id"
                                class="py-1 px-2 rounded-lg flex flex-row items-center space-x bg-neutral-100 gap-3">
                                <span>
                                    <input type="checkbox" :checked="selected_categories.some(el => el.id === cat.id)"
                                        @change="toggleCategory(cat)"
                                        class="appearance-none transition-colors cursor-pointer w-7 h-7 rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-black text-green-500 focus:ring-green-500 focus:bg-green-200 checked:bg-green-500 bg-green-200" />
                                </span>
                                <span class="text-xs sm:text-sm md:text-md whitespace-nowrap truncate">{{ cat.category
                                    }}</span>
                            </li>
                        </ul>

                        <div class="flex flex-row space-x-5 items-center text-center mt-3">
                            <span @click="markAll"
                                class="text-blue-500 font-bold hover:bg-blue-100 active:bg-blue-200 p-3 rounded-full cursor-pointer select-none">Marcar
                                todos</span>
                            <span @click="dismarkAll"
                                class="text-red-500 font-bold hover:bg-red-100 active:bg-red-200 p-3 rounded-full cursor-pointer select-none">Desmarcar
                                todos</span>
                        </div>

                        <!-- <MultiSelect v-model="selected_categories" display="chip" :options="categories_list_mapped"
                            optionLabel="category" placeholder="Selecione Categorias"
                            :pt="{ root: { class: 'border-2 flex-row-config w-full' } }" /> -->
                    </div>
                    <div class="flex flex-col w-full print:w-fit">
                        <div class="overflow-x-auto print:overflow-hidden sm:-mx-6 lg:-mx-8">
                            <div class="inline-block min-w-full sm:px-6 lg:px-8">
                                <div class="break-inside-avoid-page">
                                    <div class="overflow-hidden">
                                        <table class="min-w-full text-center text-sm font-light">
                                            <thead
                                                class="border-b bg-neutral-800 font-medium text-white table-header-group">
                                                <tr>
                                                    <th scope="col" class="px-6 py-2 print:hidden">Imprimir</th>
                                                    <th scope="col" class="px-6 py-2">Nome</th>
                                                    <th scope="col" class="px-6 py-2">Qtd. em estoque</th>
                                                    <th v-if="buy_option" scope="col" class="px-6 py-2">Qtd. para compra
                                                    </th>
                                                    <th scope="col" class="px-6 py-2">Categoria</th>
                                                </tr>
                                            </thead>
                                            <tbody v-if="items_list_filtered.length">
                                                <tr v-for="item in items_list_filtered" :key="item.id"
                                                    :class="{ 'print:hidden': hidden_print_display_list.includes(item.id) }"
                                                    class="border-b transition duration-150 ease-in-out hover:bg-neutral-100 print:break-inside-avoid-page">
                                                    <td class="whitespace-nowrap px-6 py-2 print:hidden">
                                                        <Checkbox @input="toggle_print_display(item.id)" checked />
                                                    </td>
                                                    <td class="px-6 py-2 font-medium">{{ item.name }}</td>
                                                    <td class="whitespace-nowrap px-6 py-2">{{ item.quantity }} {{
        item.quantity == 1 ? item.measurement_unit.name :
            item.measurement_unit.name_plural
    }}</td>
                                                    <td v-if="buy_option" class="whitespace-nowrap px-6 py-2">{{
        item.min_quantity - item.quantity }} {{ (item.min_quantity -
        item.quantity) == 1 ?
                                                        item.measurement_unit.name : item.measurement_unit.name_plural
                                                        }}
                                                    </td>
                                                    <td class="whitespace-nowrap px-6 py-2">{{ item.category.name }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                            <tbody v-else>
                                                <tr
                                                    class="border-b transition duration-300 ease-in-out hover:bg-neutral-200">
                                                    <td colspan="5" class="whitespace-nowrap px-6 py-2 font-medium">Não
                                                        há items nas categorias selecionadas</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <ExtraOptionsButton mode="rollup" />
    </AppLayout>
</template>
<!--<style src="@vueform/multiselect/themes/default.css"></style>-->