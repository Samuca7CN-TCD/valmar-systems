<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue'; // Remova se não for utilizado

const props = defineProps({
    active: Boolean,
    href: String,
    as: String,
    has_dropdown: {
        type: Boolean,
        default: false
    },
});

const classes = computed(() => {
    return props.active
        ? 'cursor-pointer block pl-3 pr-4 py-2 border-l-4 border-green-400 text-base font-medium text-green-700 bg-green-50 focus:outline-none focus:text-green-800 focus:bg-green-100 focus:border-green-700 transition'
        : 'cursor-pointer block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition';
});
</script>

<template>
    <div v-if="has_dropdown" :class="classes">
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
    </div>

    <template v-else>
        <template v-if="as === 'button'">
            <button :class="classes" class="w-full text-left">
                <slot />
            </button>
        </template>
        <template v-else>
            <Link :href="href" :class="classes">
            <slot />
            </Link>
        </template>
    </template>
</template>
