<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import FloatButton from '@/Components/FloatButton.vue'
import Checkbox from '@/Components/Checkbox.vue'
import { Head, router } from '@inertiajs/vue3'
import ExtraOptionsButton from '@/Components/ExtraOptionsButton.vue'
import MultiSelect from 'primevue/multiselect';
import { computed, ref } from 'vue'

// =============================================
// Informações exteriores
const props = defineProps({
    page: Object,
    page_options: {
        type: Array,
        default: null,
    },
    items_list: Object,
    categories_list: Object,
    buy_option: {
        type: Boolean,
        default: false,
    }
})

const selected_categories = ref(props.categories_list.map(function (category) {
    return {
        id: category.id,
        category: category.name_plural,
    }
}))

const categories_list_mapped = ref(props.categories_list.map(function (category) {
    return {
        id: category.id,
        category: category.name_plural,
    }
}))

const items_list_filtered = computed(() => {
    const copy = JSON.parse(JSON.stringify(props.items_list))
    if (selected_categories.value) {
        const filteredItems = copy.filter(items => selected_categories.value.find(cat => cat.id == items.category_id))
        return filteredItems
    }
    return copy
})

const hidden_print_display_list = ref([])
const toggle_print_display = (item_id) => {
    if (hidden_print_display_list.value.includes(item_id)) {
        hidden_print_display_list.value = hidden_print_display_list.value.filter((id => id != item_id))
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
    <AppLayout :page="page" :page_options="page_options">
        <template #header class="print:hidden">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ page.name }} > Listagem de Items
            </h2>
            <FloatButton :icon="'printer'" @click="printList()" title="Imprimir Lista" />
        </template>
        <div class="py-12 print:p-0">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 print:max-w-full print:mx-0 print:sm:px-0 print:lg:px-0">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg min-h-[300px] print:shadow-none">
                    <div class="print:hidden flex-col-config p-5 w-full">
                        <MultiSelect v-model="selected_categories" display="chip" :options="categories_list_mapped"
                            optionLabel="category" placeholder="Selecione Categorias"
                            :pt="{ root: { class: 'border-2 flex-row-config w-full' } }" />
                    </div>
                    <div class="flex flex-col">
                        <div class="overflow-x-auto print:overflow-hidden sm:-mx-6 lg:-mx-8">
                            <div class="inline-block min-w-full sm:px-6 lg:px-8">
                                <div class="overflow-hidden">
                                    <table class="min-w-full text-center text-sm font-light">
                                        <thead
                                            class="border-b bg-neutral-800 font-medium text-white dark:border-neutral-500 dark:bg-neutral-900">
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
                                            <tr v-for="item in items_list_filtered"
                                                class="border-b transition duration-150 ease-in-out hover:bg-neutral-100 dark:border-neutral-500 dark:hover:bg-neutral-600"
                                                :class="{ 'print:hidden': hidden_print_display_list.includes(item.id) }">
                                                <td class="whitespace-nowrap px-6 py-2 print:hidden">
                                                    <Checkbox @input="toggle_print_display(item.id)" checked />
                                                </td>
                                                <td class="whitespace-nowrap px-6 py-2 font-medium">{{ item.name }}</td>
                                                <td class="whitespace-nowrap px-6 py-2">{{ item.quantity
                                                }} {{ item.quantity
    == 1 ? item.measurement_unit.name :
    item.measurement_unit.name_plural }}
                                                </td>
                                                <td v-if="buy_option" class="whitespace-nowrap px-6 py-2">{{
                                                    item.min_quantity - item.quantity
                                                }} {{ (item.min_quantity - item.quantity) == 1 ?
    item.measurement_unit.name : item.measurement_unit.name_plural
}}</td>
                                                <td class="whitespace-nowrap px-6 py-2">{{ item.category.name }}</td>
                                            </tr>
                                        </tbody>
                                        <tbody v-else>
                                            <tr
                                                class="border-b transition duration-300 ease-in-out hover:bg-neutral-200 dark:border-neutral-500 dark:hover:bg-neutral-600">
                                                <td colspan="5" class="whitespace-nowrap px-6 py-2 font-medium">Não há
                                                    items
                                                    nas categorias selecionadas</td>
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
        <ExtraOptionsButton mode="rollup" />
    </AppLayout>
</template>
<style src="@vueform/multiselect/themes/default.css"></style>