<script setup>
    const emit = defineEmits(['toggle-enable-records']);

    const props = defineProps({
        modal_mode: String,
        can_add_record: Boolean,
        enable_records: Boolean, // Prop para controlar o estado do checkbox
    });

    const toggleEnableRecords = () => {
        emit('toggle-enable-records');
    };
</script>

<template>
    <section v-if="modal_mode === 'create'" class="py-4">
        <div class="sm:col-span-6">
            <div class="mt-2 relative flex gap-x-3"
                :title="!can_add_record ? 'Preencha ao menos a título, o cliente e o valor total.' : ''">
                <div class="flex h-6 items-center">
                    <input id="service-enable-records" name="service-enable-records" type="checkbox"
                        @change="toggleEnableRecords()" :disabled="!can_add_record"
                        class="h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-600 disabled:border-gray-100 disabled:text-green-200 disabled:bg-gray-300"
                        :checked="enable_records" />
                </div>
                <div class="text-sm leading-6">
                    <label for="service-enable-records" class="font-medium text-gray-900 select-none cursor-pointer"
                        :class="{ 'text-gray-300': !can_add_record, 'cursor-not-allowed': !can_add_record }">Adicionar
                        pagamentos concluídos</label>
                </div>
            </div>
        </div>
    </section>
</template>