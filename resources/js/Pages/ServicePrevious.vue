<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import CreateUpdatePayModal from '@/Components/Services/CreateUpdatePayModal.vue'
import ExtraOptionsButton from '@/Components/ExtraOptionsButton.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { EyeIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline'
import { toMoney, formatDate } from '@/general.js'

// =============================================
// Informações exteriores
const props = defineProps({
    page: Object,
    page_options: {
        type: Array,
        default: null,
    },
    services_list: Object,
})

const search_term = ref("")

const filtered_services_list = computed(() => {
    const searchTermLower = search_term.value.toLowerCase()

    return props.services_list.filter(el =>
        (el.motive.toLowerCase().includes(searchTermLower)) ||
        (el.entity_name.toLowerCase().includes(searchTermLower)) ||
        (el.observations.toLowerCase().includes(searchTermLower)) ||
        (toMoney(el.accounting.total_value).toString().toLowerCase().includes(searchTermLower)) ||
        (toMoney(el.accounting.partial_value).toString().toLowerCase().includes(searchTermLower)) ||
        (el.deadline.includes(searchTermLower))
    );
});


const closeModal = () => {
    service_data.reset()
    modal.value.show = false
}

</script>

<template>

    <Head :title="page.name" />
    <AppLayout :page="page" :page_options="page_options">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ page.name }} Prévio
            </h2>
        </template>

        <div class="pt-12 print:hidden print:pt-0">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 print:w-full">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                    <div class="flex flex-row items-center space-x-3">
                        <MagnifyingGlassIcon class="w-5 y-5" />
                        <input type="text" name="search_term" id="search_term" autocomplete="on" class="simple-input"
                            autofocus="true" placeholder="Pesquisar termo..." v-model="search_term" />
                    </div>
                </div>
            </div>
        </div>

        <div class="py-12 print:py-6">
            <div class="max-w-7xl mx-auto print:max-w-full">
                <div class="px-0 print:px-0">
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg print:shadow-none">
                        <div class="flex flex-col">
                            <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
                                <div class="inline-block min-w-full py-2 sm:px-6 lg:px-8">
                                    <div class="overflow-hidden">
                                        <table class="min-w-full text-left text-sm font-light">
                                            <thead class="border-b font-medium dark:border-neutral-500">
                                                <tr>
                                                    <th scope="col" class="px-6 py-4 text-center">#</th>
                                                    <th scope="col" class="px-6 py-4 text-center">Título</th>
                                                    <th scope="col" class="px-6 py-4 text-center">Cliente</th>
                                                    <th scope="col" class="px-6 py-4 text-center">Valor total</th>
                                                    <th scope="col" class="px-6 py-4 text-center print:hidden">Ações
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-if="filtered_services_list.length"
                                                    v-for="service in filtered_services_list"
                                                    class="border-b transition duration-300 ease-in-out hover:bg-neutral-100 print:break-inside-avoid">
                                                    <td class="whitespace-nowrap py-4 text-center font-medium">{{
        service.id }}
                                                    </td>
                                                    <td class="whitespace-normal px-6 py-4 text-center trim">{{
        service.motive }}
                                                        <span v-if="service.observations" :title="service.observations"
                                                            class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10 cursor-help">OBS</span>
                                                    </td>
                                                    <td class="whitespace-normal px-6 py-4 text-center">{{
        service.entity_name }}</td>
                                                    <td class="whitespace-nowrap px-2 py-4 text-center">{{
        toMoney(service.accounting.total_value) }}</td>
                                                    <td class="whitespace-nowrap px-4 py-4 text-center cursor-pointer hover:text-green-700 active:text-green-900 select-none print:hidden"
                                                        :title="'VER: ' + service.motive + '(' + service.entity_name + ')'"
                                                        @click="openModal('see', service.id)">
                                                        <EyeIcon class="w-4 h-4 m-auto" />
                                                    </td>
                                                </tr>
                                                <tr v-else
                                                    class="transition duration-300 ease-in-out hover:bg-neutral-100 print:break-inside-avoid">
                                                    <td class="whitespace-nowrap px-6 py-4 text-center" colspan="9">Não
                                                        há
                                                        serviços cadastrados no momento!</td>
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

        <ExtraOptionsButton :mode="['rollup', 'print_page']" />
    </AppLayout>
</template>
