<template>
    <button
        :type="type"
        :disabled="disabled"
        :class="buttonClasses"
        @click="$emit('click', $event)"
    >
        <slot />
    </button>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    type: {
        type: String,
        default: 'button',
    },
    variant: {
        type: String,
        default: 'primary', // primary, secondary, danger, success
    },
    size: {
        type: String,
        default: 'md', // sm, md, lg
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['click']);

const buttonClasses = computed(() => {
    const base = 'inline-flex items-center justify-center font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed';

    const sizes = {
        sm: 'px-3 py-1.5 text-sm',
        md: 'px-4 py-2 text-sm',
        lg: 'px-6 py-3 text-base',
    };

    const variants = {
        primary: 'bg-[#FAB133] hover:bg-[#F19F0D] text-white focus:ring-2 focus:ring-offset-2 focus:ring-[#FAB133]',
        secondary: 'bg-white hover:bg-gray-50 text-gray-900 border border-gray-300 focus:ring-2 focus:ring-offset-2 focus:ring-gray-500',
        danger: 'bg-red-600 hover:bg-red-700 text-white focus:ring-2 focus:ring-offset-2 focus:ring-red-500',
        success: 'bg-green-600 hover:bg-green-700 text-white focus:ring-2 focus:ring-offset-2 focus:ring-green-500',
    };

    return `${base} ${sizes[props.size]} ${variants[props.variant]}`;
});
</script>
