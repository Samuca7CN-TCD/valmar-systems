<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: String,
    active: Boolean,
});

const classes = computed(() => {
    return props.active
        ? 'py-4 text-[#818cff] flex-row-config md:flex-any-no-config md:pl-4 cursor-pointer hover:transition-all'
        : 'py-4 hover:text-[#4B55C9] flex-row-config md:flex-any-no-config md:hover:text-white md:hover:pl-4 cursor-pointer hover:transition-all';
});
</script>

<template>
    <Link :href="href" :class="classes">
    <slot />
    </Link>
</template>