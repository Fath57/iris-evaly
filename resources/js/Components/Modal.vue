<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 bg-black bg-opacity-30 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4"
                @click="closeOnBackdrop && $emit('close')"
            >
                <Transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-active-class="transition ease-in duration-150"
                    leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                >
                    <div
                        v-if="show"
                        :class="sizeClasses"
                        class="bg-white rounded-lg shadow-xl transform transition-all my-8"
                        @click.stop
                    >
                        <!-- Header -->
                        <div v-if="$slots.header || title" class="px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900">
                                    <slot name="header">{{ title }}</slot>
                                </h3>
                                <button
                                    v-if="closeable"
                                    @click="$emit('close')"
                                    class="text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-[#FAB133] rounded-lg p-1 transition-colors"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Body -->
                        <div :class="{'px-6 py-4': !$slots.header && !title}">
                            <slot />
                        </div>

                        <!-- Footer -->
                        <div v-if="$slots.footer" class="px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-lg">
                            <slot name="footer" />
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { computed, watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: '',
    },
    size: {
        type: String,
        default: 'md', // sm, md, lg, xl, 2xl, 3xl, 4xl, 5xl, full
    },
    closeable: {
        type: Boolean,
        default: true,
    },
    closeOnBackdrop: {
        type: Boolean,
        default: true,
    },
    maxWidth: {
        type: String,
        default: null,
    },
});

defineEmits(['close']);

const sizeClasses = computed(() => {
    // Si maxWidth est spécifié directement, l'utiliser
    if (props.maxWidth) {
        return `max-w-${props.maxWidth} w-full`;
    }

    const sizes = {
        sm: 'max-w-sm w-full',
        md: 'max-w-md w-full',
        lg: 'max-w-lg w-full',
        xl: 'max-w-xl w-full',
        '2xl': 'max-w-2xl w-full',
        '3xl': 'max-w-3xl w-full',
        '4xl': 'max-w-4xl w-full',
        '5xl': 'max-w-5xl w-full',
        '6xl': 'max-w-6xl w-full',
        '7xl': 'max-w-7xl w-full',
        full: 'max-w-full w-full mx-4',
    };

    return sizes[props.size] || sizes.md;
});

// Empêcher le scroll du body quand le modal est ouvert
watch(() => props.show, (newVal) => {
    if (newVal) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});
</script>
