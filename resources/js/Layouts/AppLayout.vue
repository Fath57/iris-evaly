<template>
    <component :is="layoutComponent">
        <slot />
    </component>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AdminLayout from './AdminLayout.vue';
import AdminLayoutVertical from './AdminLayoutVertical.vue';

const page = usePage();

const layoutComponent = computed(() => {
    const menuLayout = page.props.auth.user?.menu_layout || 'horizontal';
    return menuLayout === 'vertical' ? AdminLayoutVertical : AdminLayout;
});
</script>
