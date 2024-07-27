<script setup>
import CreateUpdateModal from '@/Components/CreateUpdateModal.vue'
import { PlusIcon, PencilIcon } from '@heroicons/vue/24/outline'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'

const emit = defineEmits(['close', 'submit']);

const props = defineProps({
    modal: Object,
    item: {
        type: Object,
        default: null,
    },
    categories_list: Object,
    measurement_units_list: Object,
})

// =============================================
// Controle de IMG do OBJETO
const removeItemProfilePhoto = () => {
    props.item.profile_img = props.item.profile_img_url = null
    if (props.item.errors.hasOwnProperty("profile_img_url")) delete props.item.errors.profile_img_url
    document.querySelector("input[type='file']").value = ''
}

const setItemProfilePhoto = (event) => {
    const file = event.target.files[0]

    if (file.size > 2097152) {
        props.item.errors.profile_img_url = "A imagem que você tentou inserir é maior do que o tamanho permitido (2MB)"
        return
    }

    removeItemProfilePhoto()
    props.item.profile_img = file

    const reader = new FileReader()
    reader.readAsDataURL(file)
    reader.onloadend = () => {
        props.item.profile_img_url = reader.result
    }
}

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
                :class="{ 'bg-yellow-400': modal.mode !== 'create' }">
                <PlusIcon v-if="modal.mode == 'create'" class="w-5 h-5 text-white" />
                <PencilIcon v-else class="w-5 h-5 text-white" />
            </div>
        </template>

        <template #title>
            {{ modal.title + "'" + item.name + "'" }}
        </template>

        <template #content>
            <form @submit.prevent="submit">
                <div class="pb-12 space-y-12 divide-y-2">
                    <section class="py-4">
                        <h2 class="text-base font-semibold leading-7 text-gray-900">Informações Básicas</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">Imagem representativa, nome, categoria e
                            valor
                            do item</p>

                        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

                            <div class="sm:col-span-full">
                                <label for="item-profile-img"
                                    class="block text-sm font-medium leading-6 text-gray-900">Imagem do
                                    Item</label>
                                <div class="mt-2">
                                    <label type="button" for="item-profile-img"
                                        class="w-full rounded-md bg-white text-center text-sm font-semibold text-gray-600 py-2 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 cursor-pointer">Inserir
                                        Imagem</label>
                                    <p class="text-gray-400 text-xs py-1">PNG ou JPG/JPEG até 2MB</p>
                                    <input id="item-profile-img" name="item-profile-img" type="file"
                                        accept="image/png,image/jpeg,image/jpg" maxSize="2MB" class="sr-only"
                                        @input="setItemProfilePhoto" />
                                </div>
                                <div v-if="item.profile_img || item.profile_img_url" class="mt-2">
                                    <img :src="item.profile_img_url" class="h-max-[200px] m-auto" />
                                    <p v-if="item.profile_img"
                                        class="w-full text-center text-sm text-gray-500 py-2 font-mono">{{
        item.profile_img.name
    }}</p>
                                </div>

                                <p v-if="item.errors.profile_img_url"
                                    class="w-full text-center text-red-500 font-mono py-2">{{
        item.errors.profile_img_url }}</p>
                                <p v-if="item.errors.profile_img"
                                    class="w-full text-center text-red-500 font-mono py-2">{{
        item.errors.profile_img }}</p>

                                <button
                                    v-if="item.profile_img || item.profile_img_url || item.errors.profile_img || item.errors.profile_img_url"
                                    type="button"
                                    class="w-full text-center text-red-700 hover:text-red-500 active:text-red-300 py-2"
                                    @click="removeItemProfilePhoto">
                                    Remover Imagem</button>
                            </div>

                            <div class="sm:col-span-6">
                                <label for="item-name"
                                    class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Nome</label>
                                <div class="mt-2">
                                    <input type="text" name="item-name" id="item-name" autocomplete="on"
                                        class="simple-input" autofocus="true"
                                        placeholder="Insira um nome completo e de fácil pesquisa" v-model="item.name"
                                        required>
                                    <p v-if="item.errors.name" class="text-red-500 font-mono">{{
        item.errors.name }}</p>
                                </div>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="item-category"
                                    class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Categoria</label>
                                <div class="mt-2">
                                    <select id="item-category" name="item-category" class="simple-select"
                                        v-model="item.category_id" required>
                                        <option v-for=" category  in  categories_list " :value="category.id">{{
        category.name }}</option>
                                    </select>
                                    <p v-if="item.errors.category_id" class="text-red-500 font-mono">{{
        item.errors.category_id }}</p>
                                </div>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="item-price"
                                    class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Preço</label>
                                <div class="mt-2">
                                    <input id="item-price" name="item-price" type="number" step="0.01" min="0"
                                        autocomplete="on" class="simple-input" v-model="item.price" required>
                                    <p v-if="item.errors.price" class="text-red-500 font-mono">{{
        item.errors.price }}</p>
                                </div>
                            </div>
                        </div>
                    </section>
                    <section class="py-4">
                        <h2 class="text-base font-semibold leading-7 text-gray-900">Informações de quantidade</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">Quantidade atual, mínima e máxima do item
                        </p>

                        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            <div class="sm:col-span-2">
                                <label for="item-qtd"
                                    class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Quantidade</label>
                                <div class="mt-2">
                                    <input id="item-qtd" name="item-qtd" type="number" step="0.01" min="0"
                                        autocomplete="on" class="simple-input" v-model="item.quantity" required>
                                    <p v-if="item.errors.quantity" class="text-red-500 font-mono">{{
        item.errors.quantity }}</p>
                                </div>
                            </div>
                            <div class="sm:col-span-2">
                                <label for="item-qtd-min"
                                    class="block text-sm font-medium leading-6 text-gray-900">Quantidade
                                    mínima</label>
                                <div class="mt-2">
                                    <input id="item-qtd-min" name="item-qtd-min" type="number" step="0.01" min="1"
                                        autocomplete="on" class="simple-input" v-model="item.min_quantity">
                                    <p v-if="item.errors.min_quantity" class="text-red-500 font-mono">{{
        item.errors.min_quantity }}</p>
                                </div>
                            </div>
                            <div class="sm:col-span-2">
                                <label for="item-qtd-max"
                                    class="block text-sm font-medium leading-6 text-gray-900">Quantidade
                                    máxima</label>
                                <div class="mt-2">
                                    <input id="item-qtd-max" name="item-qtd-max" type="number" step="0.01" min="2"
                                        autocomplete="on" class="simple-input" v-model="item.max_quantity">
                                    <p v-if="item.errors.max_quantity" class="text-red-500 font-mono">{{
        item.errors.max_quantity }}</p>
                                </div>
                            </div>
                        </div>
                    </section>
                    <section class="py-4">
                        <h2 class="text-base font-semibold leading-7 text-gray-900">Unidade de medida</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">Unidade de medida padrão e quantidade
                            equivalente em unidades</p>

                        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            <div class="sm:col-span-3">
                                <label for="item-measurement-unit"
                                    class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Unidade
                                    de
                                    medida</label>
                                <div class="mt-2">
                                    <select id="item-measurement-unit" name="item-measurement-unit"
                                        class="simple-select" v-model="item.measurement_unit_id" required>
                                        <option v-for=" measurement_unit  in  measurement_units_list "
                                            :value="measurement_unit.id">{{
        measurement_unit.name }}</option>
                                    </select>
                                    <p v-if="item.errors.measurement_unit_id" class="text-red-500 font-mono">{{
                                        item.errors.measurement_unit_id }}</p>
                                </div>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="item-qtd-unit-equivalent"
                                    class="block text-sm font-medium leading-6 text-gray-900 required-input-label">Quantidade
                                    equivalente
                                    em unidades</label>
                                <div class="mt-2">
                                    <input id="item-qtd-unit-equivalent" name="item-qtd-unit-equivalent" type="number"
                                        step="0.01" min="0" autocomplete="on" class="simple-input"
                                        v-model="item.unit_equivalent" required>
                                    <p v-if="item.errors.unit_equivalent" class="text-red-500 font-mono">{{
                                        item.errors.unit_equivalent }}</p>
                                </div>
                            </div>
                        </div>
                    </section>
                    <section class="py-4">
                        <h2 class="text-base font-semibold leading-7 text-gray-900">Listagem no setor de
                            controle de Usos</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">Assinale o item abaixo se deseja que
                            este item seja listado na tabela do setor do controle de Usos de Materiais.</p>

                        <div class="mt-6 space-y-6">
                            <div class="relative flex gap-x-3">
                                <div class="flex h-6 items-center">
                                    <input id="comments" name="comments" type="checkbox"
                                        class="h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-600"
                                        v-model="item.list_in_uses" true-value="1" false-value="0" required />
                                </div>
                                <div class="text-sm leading-6">
                                    <label for="comments" class="font-medium text-gray-900 required-input-label">Listar
                                        na tabela de
                                        usos</label>
                                    <p class="text-gray-500">Assinalando esta opção, todos os usos deste item
                                        serão disponibilizados na tabela de Controle de Usos.</p>
                                    <p v-if="item.errors.list_in_uses" class="text-red-500 font-mono">{{
                                        item.errors.list_in_uses }}</p>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </form>
        </template>

        <template #footer>
            <SecondaryButton :class="{ 'disabled': item.processing }" @click="close()">Cancelar
            </SecondaryButton>
            <PrimaryButton :class="{ 'disabled': item.processing }" @click="submit()">{{ modal.primary_button_txt
                }}</PrimaryButton>
        </template>
    </CreateUpdateModal>
</template>