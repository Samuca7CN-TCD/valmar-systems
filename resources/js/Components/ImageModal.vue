<script setup>
import Modal from './Modal.vue';

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
    image_url: {
        type: String,
        default: '',
    }
});

const close = () => {
    emit('close');
};
</script>

<template>
    <Modal :show="show" :max-width="maxWidth" @close="close">
        <img :src="image_url" class="w-full h-full object-contain" />

    </Modal>
</template>
