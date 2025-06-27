<script setup>
    import { ref, watch } from 'vue';
    import { Head, Link, useForm, router } from '@inertiajs/vue3';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import ConfirmationModal from '@/Components/ConfirmationModal.vue';
    import FloatButton from '@/Components/FloatButton.vue';
    import { useToast } from 'vue-toast-notification';
    import 'vue-toast-notification/dist/theme-sugar.css';
    import {
        PencilIcon,
        TrashIcon,
        MagnifyingGlassIcon,
        ChevronLeftIcon,
        ChevronRightIcon,
        EyeIcon,
    } from '@heroicons/vue/24/outline';

    const props = defineProps({
        clients: {
            type: Object,
            required: true,
        },
        filters: {
            type: Object,
            default: () => ({}),
        },
        page: {
            type: Object,
            required: true
        }
    });

    const $toast = useToast();
    const search_term = ref(props.filters.search || '');

    // Lógica de busca com debounce
    let searchTimeout = null;
    watch(search_term, () => {
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
        searchTimeout = setTimeout(() => {
            router.get(route('clients.index'), {
                search: search_term.value,
            }, { preserveState: true, replace: true });
        }, 300);
    });

    // Lógica do Modal de Exclusão
    const isConfirmingDelete = ref(false);
    const clientToDelete = ref(null);
    const deleteForm = useForm({});

    const confirmDelete = (client) => {
        clientToDelete.value = client;
        isConfirmingDelete.value = true;
    };

    const closeModal = () => {
        isConfirmingDelete.value = false;
        clientToDelete.value = null;
    };

    const deleteClient = () => {
        if (clientToDelete.value) {
            deleteForm.delete(route('clients.destroy', clientToDelete.value.id), {
                preserveScroll: true,
                onSuccess: () => {
                    closeModal();
                    $toast.success('Cliente excluído com sucesso!');
                },
                onError: (errors) => {
                    console.error(errors);
                    $toast.error('Erro ao excluir o cliente.');
                }
            });
        }
    };

    const formatContacts = (contacts) => {
        if (!contacts || contacts.length === 0) return 'N/A';
        return contacts.join(', ');
    };

    const goToPage = (url) => {
        if (url) {
            router.get(url, {
                search: search_term.value,
            }, { preserveState: true, replace: true });
        }
    };
</script>

<template>

    <Head :title="page.name" />
    <AppLayout :page="page">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ page.name }}
            </h2>

            <Link :href="route('clients.create')">
            <FloatButton :icon="'plus'" title="Cadastrar Cliente" class="print:hidden" />
            </Link>
        </template>

        <div class="pt-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                    <div class="flex items-center space-x-3">
                        <MagnifyingGlassIcon class="w-5 h-5 text-gray-500" />
                        <input type="text" name="search_term" id="search_term" autocomplete="on"
                            class="simple-input flex-grow" autofocus="true"
                            placeholder="Pesquisar por nome, CPF/CNPJ ou e-mail..." v-model="search_term" />
                    </div>
                </div>
            </div>
        </div>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="flex flex-col">
                        <div class="overflow-x-auto">
                            <div class="inline-block min-w-full">
                                <div class="overflow-hidden">
                                    <table class="min-w-full text-left text-sm font-light">
                                        <thead class="border-b bg-gray-50 font-medium">
                                            <tr>
                                                <th scope="col" class="px-6 py-4 text-left">Nome / Razão Social</th>
                                                <th scope="col" class="px-6 py-4 text-left">CPF / CNPJ</th>
                                                <th scope="col" class="px-6 py-4 text-left">E-mail</th>
                                                <th scope="col" class="px-6 py-4 text-left">Contatos</th>
                                                <th scope="col" class="px-6 py-4 text-center">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-if="clients.data.length === 0">
                                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                                    Nenhum cliente encontrado.
                                                </td>
                                            </tr>
                                            <tr v-for="client in clients.data" :key="client.id"
                                                class="border-b transition duration-300 ease-in-out hover:bg-neutral-100">
                                                <td class="whitespace-nowrap px-6 py-4">
                                                    <div class="font-medium text-gray-900">{{ client.name }}</div>
                                                    <div v-if="client.trade_name" class="text-xs text-gray-500">{{
                                                        client.trade_name }}</div>
                                                </td>
                                                <td class="whitespace-nowrap px-6 py-4">{{ client.document }}</td>
                                                <td class="whitespace-nowrap px-6 py-4">{{ client.email || 'N/A' }}</td>
                                                <td class="whitespace-nowrap px-6 py-4">{{
                                                    formatContacts(client.contacts) }}
                                                </td>
                                                <td class="whitespace-nowrap px-6 py-4">
                                                    <div class="flex justify-center items-center space-x-5">
                                                        <Link :href="route('clients.show', client.id)"
                                                            class="hover:text-blue-500" title="Ver Cliente">
                                                        <EyeIcon class="w-5 h-5" />
                                                        </Link>
                                                        <Link :href="route('clients.edit', client.id)"
                                                            class="hover:text-yellow-500" title="Editar Cliente">
                                                        <PencilIcon class="w-5 h-5" />
                                                        </Link>
                                                        <button @click="confirmDelete(client)"
                                                            class="hover:text-red-500" title="Excluir Cliente">
                                                            <TrashIcon class="w-5 h-5" />
                                                        </button>
                                                    </div>
                                                </td>
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

        <div v-if="clients.links.length > 3" class="max-w-7xl mx-auto sm:px-6 lg:px-8 pb-12">
            <div
                class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 mt-6 rounded-lg shadow-xl">
                <div class="flex flex-1 justify-between sm:hidden">
                    <button @click="goToPage(clients.prev_page_url)" :disabled="!clients.prev_page_url"
                        class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Anterior</button>
                    <button @click="goToPage(clients.next_page_url)" :disabled="!clients.next_page_url"
                        class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Próxima</button>
                </div>

                <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Mostrando de <span class="font-medium">{{ clients.from }}</span> a <span
                                class="font-medium">{{
                                    clients.to }}</span> de <span class="font-medium">{{ clients.total }}</span> resultados
                        </p>
                    </div>
                    <div>
                        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                            <button @click="goToPage(clients.prev_page_url)" :disabled="!clients.prev_page_url"
                                class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                <span class="sr-only">Anterior</span>
                                <ChevronLeftIcon class="h-5 w-5" aria-hidden="true" />
                            </button>

                            <template v-for="(link, index) in clients.links">
                                <template v-if="!['&laquo; Previous', 'Next &raquo;'].includes(link.label)">
                                    <button :key="index" @click="goToPage(link.url)" :disabled="!link.url"
                                        aria-current="page"
                                        class="relative inline-flex items-center px-4 py-2 text-sm font-semibold focus:z-20 focus-visible:outline-2 focus-visible:outline-offset-2"
                                        :class="{
                                            'z-10 bg-indigo-600 text-white focus-visible:outline-indigo-600': link.active,
                                            'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-offset-0': !link.active,
                                        }" v-html="link.label"></button>
                                </template>
                            </template>

                            <button @click="goToPage(clients.next_page_url)" :disabled="!clients.next_page_url"
                                class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                <span class="sr-only">Próxima</span>
                                <ChevronRightIcon class="h-5 w-5" aria-hidden="true" />
                            </button>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <ConfirmationModal :show="isConfirmingDelete" @close="closeModal">
            <template #title>
                Confirmar Exclusão de Cliente
            </template>
            <template #content>
                Você tem certeza que deseja excluir o cliente <strong>{{ clientToDelete?.name }}</strong>? Esta ação não
                poderá ser desfeita.
            </template>
            <template #footer>
                <SecondaryButton @click="closeModal">Cancelar</SecondaryButton>
                <button @click="deleteClient"
                    class="ml-3 inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    :class="{ 'opacity-25': deleteForm.processing }" :disabled="deleteForm.processing">
                    Excluir
                </button>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
