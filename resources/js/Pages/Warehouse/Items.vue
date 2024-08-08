<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import ImageModal from '@/Components/ImageModal.vue'
import CreateUpdateItemsModal from '@/Components/Warehouse/CreateUpdateItemsModal.vue'
import FloatButton from '@/Components/FloatButton.vue'
import Dropdown from '@/Components/Dropdown.vue'
import DropdownItem from '@/Components/DropdownItem.vue'
import FilteredList from '@/Components/FilteredList.vue'
import ExtraOptionsButton from '@/Components/ExtraOptionsButton.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import { DocumentDuplicateIcon, EllipsisVerticalIcon, PencilIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import { toMoney, measurementUnitResolver } from '@/general.js'
import { useToast } from 'vue-toast-notification';
import 'vue-toast-notification/dist/theme-sugar.css';
import { computed } from 'vue'
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
    measurement_units_list: Object,
})
const $toast = useToast();
const filtered_items_list = ref(JSON.parse(JSON.stringify(props.items_list)));

// =============================================
// Informações do OBJETO
const item_data = useForm({
    'id': null,
    'profile_img_url': null,
    'profile_img': null,
    'name': '',
    'category_id': 1,
    'price': null,
    'quantity': null,
    'min_quantity': null,
    'max_quantity': null,
    'measurement_unit_id': 1,
    'unit_equivalent': 1,
    'list_in_uses': false,
})

// =============================================
// Controle de Modal
const modal = ref({
    mode: 'create',
    show: false,
    get title() {
        return this.mode === 'create' ? "Criar o item " : "Editar o item "
    },
    get primary_button_txt() {
        return this.mode === 'create' ? "Cadastrar" : "Atualizar"
    }
})

const openModal = (mode, category_id = null, item_id = null) => {
    if (mode == 'update') {
        const category = props.items_list.find(category => category.id === category_id)
        if (!category) {
            $toast.error("Item não encontrado. Informar o Técnico.")
            return
        }

        const item = category.items.find(item => item.id === item_id)
        if (!item) {
            $toast.error("Item não encontrado. Informar o Técnico.")
            return
        }
        item_data.id = item.id
        item_data.profile_img_url = item.profile_img ? '/storage/img/items/' + item.profile_img.split('/')[3] : null
        item_data.profile_img = null
        item_data.name = item.name
        item_data.category_id = item.category_id
        item_data.price = item.price
        item_data.quantity = item.quantity
        item_data.min_quantity = item.min_quantity
        item_data.max_quantity = item.max_quantity
        item_data.measurement_unit_id = item.measurement_unit_id
        item_data.unit_equivalent = item.unit_equivalent
        item_data.list_in_uses = item.list_in_uses
    }

    modal.value.mode = mode
    modal.value.show = true
}

const closeModal = () => {
    item_data.reset()
    modal.value.show = false
}



// =============================================
// Controle de Modal de Imagem
const image_modal = ref({
    show: false,
    url: '',
})

const resolveItemImg = (profile_img) => {
    return profile_img ? '/storage/img/items/' + profile_img.split('/')[3] : '/storage/img/items/default.jpeg'
}

const openImage = (image_url) => {
    image_modal.value.url = image_url
    image_modal.value.show = true
}

const closeImage = () => {
    image_modal.value.show = false
    image_modal.value.url = ''
}


// =============================================
// Métodos de CRUD
const createItem = () => {
    item_data.post(route('warehouse.store'), {
        preserveScroll: true,
        onSuccess: () => {
            $toast.success(`Item "${item_data.name}" cadastrado com sucesso!`)
            closeModal()
        },
        onError: (error) => {
            $toast.error("Erro no cadastro do item. Revise os dados inseridos.")
        },
    })
}

const duplicateItem = (item) => {
    if (!confirm("Tem certeza que você deseja duplicar o item " + item.name + "?")) {
        return;
    }

    item_data.profile_img_url = item.profile_img ? '/storage/img/items/' + item.profile_img.split('/')[3] : null
    item_data.profile_img = null
    item_data.name = item.name + " (Cópia)"
    item_data.category_id = item.category_id
    item_data.price = item.price
    item_data.quantity = item.quantity
    item_data.min_quantity = item.min_quantity
    item_data.max_quantity = item.max_quantity
    item_data.measurement_unit_id = item.measurement_unit_id
    item_data.unit_equivalent = item.unit_equivalent
    item_data.list_in_uses = item.list_in_uses

    while (props.items_list.find(category => category.items.find(item => item.name.toLowerCase() === item_data.name.toLowerCase())) !== undefined) {
        item_data.name += " (Cópia)"
    }
    createItem()
}

const updateItem = () => {
    if (item_data.profile_img_url && item_data.profile_img_url.indexOf('data:') > -1)
        item_data.profile_img_url = null


    item_data.post(route('warehouse.update', item_data.id), {
        _method: 'put',
        preserveScroll: true,
        onSuccess: () => {
            $toast.success(`Item "${item_data.name}" editado com sucesso!`)
            closeModal()
        },
    })
}

const deleteItem = (item_id, item_name) => {
    if (confirm("Você tem certeza que deseja excluir o item \"" + item_name + "\" ? Esta ação não poderá ser desfeita!")) {
        item_data.delete(route('warehouse.destroy', item_id), {
            preserveScroll: true,
            onSuccess: () => $toast.success('Item deletado com sucesso!')
        })
    }
}

const submit = () => {
    if (modal.value.mode === 'create') createItem()
    else if (modal.value.mode === 'update') updateItem()
    else $toast.error('Método desconhecido. Informar o Técnico.')
}

const total_prices_amount = computed(() => {
    return props.items_list.reduce((accumulator, category) => {
        // Assumindo que cada categoria tem uma lista de itens
        return accumulator + category.items.reduce((catAccumulator, item) => {
            return catAccumulator + (item.price * item.quantity);
        }, 0);
    }, 0);
});

</script>

<template>

    <Head :title="page.name" />
    <AppLayout :page="page" :page_options="page_options">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ page.name }} <span v-if="$page.props.auth.user.hierarchy < 2">|
                    {{ toMoney(total_prices_amount) }}</span>
            </h2>
            <FloatButton :icon="'plus'" @click="openModal('create')" title="Cadastrar Item" />
        </template>
        <FilteredList :base_list="items_list">
            <template v-slot="list">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                        <main v-if="list.filteredList.length">
                            <section v-for="category in list.filteredList" class="w-full">
                                <div class="lg:flex lg:items-center lg:justify-between bg-slate-100 p-5 rounded-lg">
                                    <div class="min-w-0 flex-1">
                                        <h2
                                            class="text-2xl font-bold leading-7 text-gray-900 md:truncate md:text-3xl sm:tracking-tight">
                                            {{ category.name_plural }}</h2>
                                    </div>
                                </div>
                                <section v-if="category.items.length"
                                    class="w-full p-5 grid grid-cols-1 gap-1 sm:grid-cols-2 sm:gap-2 xl:grid-cols-4 xl:gap-4">
                                    <div v-for="item in category.items"
                                        class="group block border rounded-lg bg-white shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-xl">

                                        <img class="rounded-t-lg w-full h-[100px] object-cover object-center cursor-pointer"
                                            :src="resolveItemImg(item.profile_img)" :alt="item.name"
                                            :title="'Ver imagem de ' + item.name"
                                            @click="openImage(resolveItemImg(item.profile_img))" />

                                        <div class="pt-5 space-y-3 text-center ">
                                            <div class="mb-2 px-3 flex flex-row items-center">
                                                <h5
                                                    class="w-full text-lg text-neutral-800 truncate group-hover:truncate-none">
                                                    {{
        item.name }}</h5>

                                                <Dropdown>
                                                    <template #trigger>
                                                        <EllipsisVerticalIcon class="w-5 h-5" />
                                                    </template>
                                                    <template #content>
                                                        <DropdownItem
                                                            class="flex flex-row align-middle items-center space-x-5"
                                                            @click="openModal('update', category.id,
        item.id)" :title="'Editar ' + item.name">
                                                            <PencilIcon class="w-5 h-5" />
                                                            <span>Editar</span>
                                                        </DropdownItem>
                                                        <DropdownItem
                                                            class="flex flex-row align-middle items-center space-x-5"
                                                            @click="duplicateItem(item)"
                                                            :title="'Duplicar ' + item.name">
                                                            <DocumentDuplicateIcon class="w-5 h-5 text-gray-700" />
                                                            <span>Duplicar</span>
                                                        </DropdownItem>
                                                        <DropdownItem
                                                            class="text-red-500 flex flex-row align-middle items-center space-x-5"
                                                            @click="deleteItem(item.id, item.name)"
                                                            :title="'Deletar ' + item.name">
                                                            <XMarkIcon class="w-5 h-5" />
                                                            <span>Deletar</span>
                                                        </DropdownItem>
                                                    </template>
                                                </Dropdown>
                                            </div>
                                            <div class="">
                                                <span
                                                    class="rounded-full w-fit m-auto px-2 text-white whitespace-nowrap"
                                                    :class="{
        'bg-blue-500': item.quantity != 0 && item.quantity >= item.max_quantity,
        'bg-green-500': item.quantity < item.max_quantity && item.quantity >= item.min_quantity,
        'bg-yellow-500': item.quantity < item.min_quantity && item.quantity > 0,
        'bg-red-500': item.quantity == 0
    }">
                                                    {{ item.quantity }} {{
        measurementUnitResolver(measurement_units_list,
            item.measurement_unit_id,
            item.quantity) }}
                                                </span>
                                            </div>

                                            <p class="text-xl font-bold bg-gray-100 py-3">{{ toMoney(item.price)
                                                }}</p>
                                        </div>
                                    </div>
                                </section>
                                <p v-else class="py-5">Não foi encontrado nenhum item desta categoria!</p>
                            </section>
                        </main>
                        <p v-else class="text-center">Não foi encontrado nenhum item ou categoria!</p>
                    </div>
                </div>
            </template>
        </FilteredList>

        <CreateUpdateItemsModal :show="modal.show" :modal="modal" :item="item_data" :categories_list="categories_list"
            :measurement_units_list="measurement_units_list" @submit="submit" @close="closeModal" />

        <ImageModal :show="image_modal.show" :image_url="image_modal.url" @close="closeImage" />

        <ExtraOptionsButton />
    </AppLayout>
</template>
