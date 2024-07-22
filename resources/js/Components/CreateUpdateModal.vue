<script setup>
import Modal from './Modal.vue'

const emit = defineEmits(['close']);

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const close = () => {
    emit('close');
};
</script>

<template>
    <Modal :show="show" :max-width="maxWidth" :closeable="closeable" @close="close">
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
                <slot name="icon" />

                <div class="mt-3 text-center w-full sm:mt-0 sm:ml-4 sm:text-left">
                    <h3 class="text-lg">
                        <slot name="title" />
                    </h3>

                    <div class="mt-2">
                        <slot name="content" />
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-row justify-end px-6 py-4 bg-gray-100 text-right space-x-5">
            <slot name="footer" />
        </div>
    </Modal>
</template>
