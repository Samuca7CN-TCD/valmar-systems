<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const props = defineProps({
    href: String,
    active: Boolean,
    has_dropdown: {
        type: Boolean,
        default: false
    },
});

const classes = computed(() => {
    return props.active
        ? 'inline-flex items-center align-middle px-1 pt-1 border-b-2 border-green-400 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-green-700 transition'
        : 'inline-flex items-center align-middle px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition';
});
</script>

<template>
    <Link v-if="has_dropdown" :href="href" :class="classes">
    <Dropdown align="left">
        <template #trigger>
            <span class="inline-flex rounded-md pt-2">
                <slot name="trigger" />
            </span>

        </template>

        <template #content>
            <slot name="options" />
        </template>
    </Dropdown>
    </Link>

    <Link v-else :href="href" :class="classes">
    <slot />
    </Link>
</template>
