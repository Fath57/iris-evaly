<template>
    <div>
        <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 mb-1">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>
        <input
            :id="id"
            :type="type"
            :value="modelValue"
            :required="required"
            :disabled="disabled"
            :placeholder="placeholder"
            :class="inputClasses"
            @input="$emit('update:modelValue', $event.target.value)"
        />
        <p v-if="error" class="mt-1 text-sm text-red-600">
            {{ error }}
        </p>
        <p v-else-if="helper" class="mt-1 text-sm text-gray-500">
            {{ helper }}
        </p>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    id: String,
    type: {
        type: String,
        default: 'text',
    },
    modelValue: [String, Number],
    label: String,
    placeholder: String,
    required: Boolean,
    disabled: Boolean,
    error: String,
    helper: String,
});

defineEmits(['update:modelValue']);

const inputClasses = computed(() => {
    const base = 'w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 transition-colors';
    const error = props.error ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-orange-500 focus:border-orange-500';
    const disabled = props.disabled ? 'bg-gray-100 cursor-not-allowed' : '';
    
    return `${base} ${error} ${disabled}`;
});
</script>

