<script setup>
    import { Head, Link, useForm } from '@inertiajs/vue3';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import { useToast } from 'vue-toast-notification';
    import 'vue-toast-notification/dist/theme-sugar.css';
    import InputError from '@/Components/InputError.vue';
    import { TrashIcon, PlusIcon } from '@heroicons/vue/24/outline';
    import {
        formatPhoneNumber,
        formatCPF,
        clearFormat,
        formatCNPJ,
        formatarCEP,
    } from '@/general.js';


    const props = defineProps({
        page: {
            type: Object,
            required: true,
        },
        client: {
            type: Object,
            required: true,
        }
    });

    const $toast = useToast();

    const form = useForm({
        _method: 'PUT',
        type: props.client.type,
        name: props.client.name,
        trade_name: props.client.trade_name,
        document: props.client.type === 'fisica' ? formatCPF(props.client.document) : formatCNPJ(props.client.document),
        email: props.client.email,
        contacts: props.client.contacts && props.client.contacts.length > 0 ? props.client.contacts.map(c => formatPhoneNumber(c)) : [''],
        postal_code: formatarCEP(props.client.postal_code),
        address_street: props.client.address_street,
        address_number: props.client.address_number,
        address_complement: props.client.address_complement,
        address_neighborhood: props.client.address_neighborhood,
        address_city: props.client.address_city,
        address_state: props.client.address_state,
        observations: props.client.observations,
    });

    const addContact = () => {
        form.contacts.push('');
    };

    const removeContact = (index) => {
        if (form.contacts.length > 1) {
            form.contacts.splice(index, 1);
        }
    };

    const handlePhoneInput = (event, index) => {
        form.contacts[index] = formatPhoneNumber(event.target.value);
    };

    const handleCpfCnpjInput = (event) => {
        let cleaned = clearFormat(event.target.value);
        if (cleaned.length <= 11) {
            form.document = formatCPF(cleaned);
        } else {
            form.document = formatCNPJ(cleaned);
        }
    };

    const handleCepInput = (event) => {
        let value = clearFormat(event.target.value);
        form.postal_code = formatarCEP(value);
        if (form.postal_code.length === 9) {
            fillAddressByCep();
        }
    };

    const fillAddressByCep = async () => {
        let cep = clearFormat(form.postal_code);

        if (cep.length === 8) {
            try {
                const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                const data = await response.json();

                if (!data.erro) {
                    form.address_street = data.logradouro;
                    form.address_neighborhood = data.bairro;
                    form.address_city = data.localidade;
                    form.address_state = data.uf;
                    $toast.success('Endereço preenchido automaticamente!', { position: 'top-right' });
                } else {
                    $toast.error('CEP não encontrado.', { position: 'top-right' });
                }
            } catch (error) {
                console.error("Erro ao buscar CEP:", error);
                $toast.error('Erro ao consultar o serviço de CEP.', { position: 'top-right' });
            }
        }
    };

    const submit = () => {
        const formToSend = { ...form.data() };
        formToSend.document = clearFormat(formToSend.document);
        formToSend.postal_code = clearFormat(formToSend.postal_code);
        formToSend.contacts = formToSend.contacts.map(c => clearFormat(c)).filter(Boolean);

        // O Inertia lida com o _method automaticamente para requisições PUT/PATCH/DELETE
        form.transform(() => formToSend).put(route('clients.update', props.client.id), {
            onSuccess: () => {
                $toast.success('Cliente atualizado com sucesso!');
            },
            onError: (errors) => {
                console.error(errors);
                $toast.error('Verifique os campos e tente novamente.');
            }
        });
    };
</script>

<template>

    <Head :title="'Editar ' + page.singular_name" />
    <AppLayout :page="page">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Cliente: {{ client.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <section class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-2 mb-4">DADOS GERAIS</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Tipo de Cliente</label>
                                    <div class="mt-2 flex items-center space-x-6">
                                        <label class="inline-flex items-center"><input type="radio" v-model="form.type"
                                                value="fisica" class="form-radio h-4 w-4 text-indigo-600"><span
                                                class="ml-2">Pessoa Física</span></label>
                                        <label class="inline-flex items-center"><input type="radio" v-model="form.type"
                                                value="juridica" class="form-radio h-4 w-4 text-indigo-600"><span
                                                class="ml-2">Pessoa Jurídica</span></label>
                                    </div>
                                </div>
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">{{ form.type ===
                                        'fisica'
                                        ? 'Nome Completo' : 'Razão Social' }} <span
                                            class="text-red-500">*</span></label>
                                    <input v-model="form.name" type="text" id="name" class="simple-input mt-1" required>
                                    <InputError class="mt-2" :message="form.errors.name" />
                                </div>
                                <div v-if="form.type === 'juridica'">
                                    <label for="trade_name" class="block text-sm font-medium text-gray-700">Nome
                                        Fantasia <span class="text-red-500">*</span></label>
                                    <input v-model="form.trade_name" type="text" id="trade_name"
                                        class="simple-input mt-1" required>
                                    <InputError class="mt-2" :message="form.errors.trade_name" />
                                </div>
                                <div>
                                    <label for="document" class="block text-sm font-medium text-gray-700">{{ form.type
                                        ===
                                        'fisica' ? 'CPF' : 'CNPJ' }} <span class="text-red-500">*</span></label>
                                    <input v-model="form.document" @input="handleCpfCnpjInput" type="text" id="document"
                                        class="simple-input mt-1" required>
                                    <InputError class="mt-2" :message="form.errors.document" />
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">E-mail</label>
                                    <input v-model="form.email" type="email" id="email" class="simple-input mt-1">
                                    <InputError class="mt-2" :message="form.errors.email" />
                                </div>
                            </div>
                        </section>

                        <section class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-2 mb-4">CONTATOS</h3>
                            <div class="space-y-4">
                                <div v-for="(contact, index) in form.contacts" :key="index"
                                    class="flex items-center space-x-3">
                                    <div class="flex-grow">
                                        <label :for="'contact-' + index" class="sr-only">Contato {{ index + 1 }}</label>
                                        <input v-model="form.contacts[index]" @input="handlePhoneInput($event, index)"
                                            type="text" :id="'contact-' + index" class="simple-input w-full"
                                            placeholder="Telefone ou outro contato">
                                    </div>
                                    <button v-if="form.contacts.length > 1" @click.prevent="removeContact(index)"
                                        type="button"
                                        class="text-red-500 hover:text-red-700 p-1 rounded-full bg-red-100">
                                        <TrashIcon class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                            <button @click.prevent="addContact" type="button"
                                class="mt-4 px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 flex items-center text-sm">
                                <PlusIcon class="w-5 h-5 mr-1" /> Adicionar Contato
                            </button>
                        </section>

                        <section class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-2 mb-4">ENDEREÇO</h3>
                            <div class="grid grid-cols-1 md:grid-cols-6 gap-6">
                                <div class="md:col-span-2">
                                    <label for="postal_code" class="block text-sm font-medium text-gray-700">CEP</label>
                                    <input v-model="form.postal_code" @input="handleCepInput" type="text"
                                        id="postal_code" class="simple-input mt-1">
                                    <InputError class="mt-2" :message="form.errors.postal_code" />
                                </div>
                                <div class="md:col-span-4">
                                    <label for="address_street" class="block text-sm font-medium text-gray-700">Rua /
                                        Logradouro</label>
                                    <input v-model="form.address_street" type="text" id="address_street"
                                        class="simple-input mt-1">
                                    <InputError class="mt-2" :message="form.errors.address_street" />
                                </div>
                                <div class="md:col-span-2">
                                    <label for="address_number"
                                        class="block text-sm font-medium text-gray-700">Número</label>
                                    <input v-model="form.address_number" type="text" id="address_number"
                                        class="simple-input mt-1">
                                    <InputError class="mt-2" :message="form.errors.address_number" />
                                </div>
                                <div class="md:col-span-4">
                                    <label for="address_complement"
                                        class="block text-sm font-medium text-gray-700">Complemento</label>
                                    <input v-model="form.address_complement" type="text" id="address_complement"
                                        class="simple-input mt-1">
                                </div>
                                <div class="md:col-span-3">
                                    <label for="address_neighborhood"
                                        class="block text-sm font-medium text-gray-700">Bairro</label>
                                    <input v-model="form.address_neighborhood" type="text" id="address_neighborhood"
                                        class="simple-input mt-1">
                                    <InputError class="mt-2" :message="form.errors.address_neighborhood" />
                                </div>
                                <div class="md:col-span-2">
                                    <label for="address_city"
                                        class="block text-sm font-medium text-gray-700">Cidade</label>
                                    <input v-model="form.address_city" type="text" id="address_city"
                                        class="simple-input mt-1">
                                    <InputError class="mt-2" :message="form.errors.address_city" />
                                </div>
                                <div class="md:col-span-1">
                                    <label for="address_state"
                                        class="block text-sm font-medium text-gray-700">Estado</label>
                                    <input v-model="form.address_state" type="text" id="address_state"
                                        class="simple-input mt-1" maxlength="2">
                                    <InputError class="mt-2" :message="form.errors.address_state" />
                                </div>
                            </div>
                        </section>

                        <section>
                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-2 mb-4">OBSERVAÇÕES</h3>
                            <textarea v-model="form.observations" id="observations" rows="4" class="simple-input mt-1"
                                placeholder="Informações adicionais sobre o cliente..."></textarea>
                            <InputError class="mt-2" :message="form.errors.observations" />
                        </section>

                        <div class="mt-8 pt-5 border-t border-gray-200">
                            <div class="flex justify-end space-x-3">
                                <Link :href="route('clients.index')" class="simple-button-neutral">Cancelar</Link>
                                <button type="submit" class="simple-button-green" :disabled="form.processing">Atualizar
                                    Cliente</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
