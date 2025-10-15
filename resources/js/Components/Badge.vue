<template>
    <span :class="badgeClasses">
        <slot />
    </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'default', // default, primary, success, danger, warning, info
    },
    size: {
        type: String,
        default: 'md', // sm, md, lg
    },
});

const badgeClasses = computed(() => {
    const base = 'inline-flex items-center font-medium rounded-full';
    
    const sizes = {
        sm: 'px-2 py-0.5 text-xs',
        md: 'px-2.5 py-0.5 text-xs',
        lg: 'px-3 py-1 text-sm',
    };
    
    const variants = {
        default: 'bg-gray-100 text-gray-800',
        primary: 'bg-orange-100 text-orange-800',
        success: 'bg-green-100 text-green-800',
        danger: 'bg-red-100 text-red-800',
        warning: 'bg-yellow-100 text-yellow-800',
        info: 'bg-blue-100 text-blue-800',
    };
    
    return `${base} ${sizes[props.size]} ${variants[props.variant]}`;
});
</script>

