<template>
    <Modal :show="show" size="md" @close="$emit('close')">
        <template #header>
            <div class="flex items-center gap-3">
                <div :class="iconColorClass" class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900">
                    {{ title }}
                </h3>
            </div>
        </template>

        <div class="text-sm text-gray-500">
            {{ message }}
        </div>

        <template #footer>
            <div class="flex justify-end gap-3">
                <Button
                    variant="secondary"
                    @click="$emit('close')"
                >
                    {{ cancelText }}
                </Button>
                <Button
                    :variant="type === 'danger' ? 'danger' : 'primary'"
                    :disabled="processing"
                    @click="$emit('confirm')"
                >
                    <span v-if="processing">{{ processingText }}</span>
                    <span v-else>{{ confirmText }}</span>
                </Button>
            </div>
        </template>
    </Modal>
</template>

<script setup>
import { computed } from 'vue';
import Modal from './Modal.vue';
import Button from './Button.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Confirmation',
    },
    message: {
        type: String,
        required: true,
    },
    confirmText: {
        type: String,
        default: 'Confirmer',
    },
    cancelText: {
        type: String,
        default: 'Annuler',
    },
    processingText: {
        type: String,
        default: 'Traitement...',
    },
    processing: {
        type: Boolean,
        default: false,
    },
    type: {
        type: String,
        default: 'danger', // danger, warning, info
    },
});

defineEmits(['close', 'confirm']);

const iconColorClass = computed(() => {
    const classes = {
        danger: 'bg-red-100 text-red-600',
        warning: 'bg-yellow-100 text-yellow-600',
        info: 'bg-blue-100 text-blue-600',
    };
    return classes[props.type] || classes.danger;
});
</script>

