<template>
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <!-- Header with search and actions -->
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex-1 max-w-md">
                    <div class="relative">
                        <input
                            v-model="searchQuery"
                            type="text"
                            :placeholder="searchPlaceholder"
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            @input="debounceSearch"
                        />
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th
                            v-for="column in columns"
                            :key="column.key"
                            scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            :class="column.sortable ? 'cursor-pointer hover:bg-gray-100' : ''"
                            @click="column.sortable && sort(column.key)"
                        >
                            <div class="flex items-center gap-2">
                                <span>{{ column.label }}</span>
                                <svg
                                    v-if="column.sortable && sortBy === column.key"
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        v-if="sortOrder === 'asc'"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M5 15l7-7 7 7"
                                    />
                                    <path
                                        v-else
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 9l-7 7-7-7"
                                    />
                                </svg>
                            </div>
                        </th>
                        <th v-if="$slots.actions" scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-if="loading" class="hover:bg-gray-50">
                        <td :colspan="columns.length + ($slots.actions ? 1 : 0)" class="px-6 py-4 text-center text-sm text-gray-500">
                            Chargement...
                        </td>
                    </tr>
                    <tr v-else-if="data.data.length === 0" class="hover:bg-gray-50">
                        <td :colspan="columns.length + ($slots.actions ? 1 : 0)" class="px-6 py-4 text-center text-sm text-gray-500">
                            {{ emptyMessage }}
                        </td>
                    </tr>
                    <tr v-else v-for="(row, index) in data.data" :key="row.id || index" class="hover:bg-gray-50 transition-colors">
                        <td v-for="column in columns" :key="column.key" class="px-6 py-4 whitespace-nowrap text-sm">
                            <slot :name="`cell-${column.key}`" :row="row" :value="getNestedValue(row, column.key)">
                                {{ getNestedValue(row, column.key) }}
                            </slot>
                        </td>
                        <td v-if="$slots.actions" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <slot name="actions" :row="row" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="data.last_page > 1" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="text-sm text-gray-700">
                    Affichage de <span class="font-medium">{{ data.from }}</span> à 
                    <span class="font-medium">{{ data.to }}</span> sur 
                    <span class="font-medium">{{ data.total }}</span> résultats
                </div>
                <div class="flex items-center gap-2">
                    <button
                        v-for="page in paginationPages"
                        :key="page"
                        :disabled="page === '...' || page === data.current_page"
                        :class="page === data.current_page ? 'bg-orange-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                        class="px-3 py-1 border border-gray-300 rounded-lg text-sm font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        @click="page !== '...' && changePage(page)"
                    >
                        {{ page }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    data: {
        type: Object,
        required: true,
    },
    columns: {
        type: Array,
        required: true,
    },
    searchPlaceholder: {
        type: String,
        default: 'Rechercher...',
    },
    emptyMessage: {
        type: String,
        default: 'Aucune donnée disponible',
    },
    routeName: {
        type: String,
        required: true,
    },
});

defineEmits(['close']);

const searchQuery = ref('');
const sortBy = ref('created_at');
const sortOrder = ref('desc');
const loading = ref(false);
let searchTimeout = null;

const debounceSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        performSearch();
    }, 500);
};

const performSearch = () => {
    loading.value = true;
    router.get(
        route(props.routeName),
        {
            search: searchQuery.value,
            sort_by: sortBy.value,
            sort_order: sortOrder.value,
            page: 1,
        },
        {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => {
                loading.value = false;
            },
        }
    );
};

const sort = (column) => {
    if (sortBy.value === column) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = column;
        sortOrder.value = 'asc';
    }
    performSearch();
};

const changePage = (page) => {
    loading.value = true;
    router.get(
        route(props.routeName),
        {
            search: searchQuery.value,
            sort_by: sortBy.value,
            sort_order: sortOrder.value,
            page: page,
        },
        {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => {
                loading.value = false;
            },
        }
    );
};

const paginationPages = computed(() => {
    const pages = [];
    const current = props.data.current_page;
    const last = props.data.last_page;
    
    if (last <= 7) {
        for (let i = 1; i <= last; i++) {
            pages.push(i);
        }
    } else {
        if (current <= 3) {
            for (let i = 1; i <= 5; i++) pages.push(i);
            pages.push('...');
            pages.push(last);
        } else if (current >= last - 2) {
            pages.push(1);
            pages.push('...');
            for (let i = last - 4; i <= last; i++) pages.push(i);
        } else {
            pages.push(1);
            pages.push('...');
            for (let i = current - 1; i <= current + 1; i++) pages.push(i);
            pages.push('...');
            pages.push(last);
        }
    }
    
    return pages;
});

const getNestedValue = (obj, path) => {
    return path.split('.').reduce((current, key) => current?.[key], obj);
};
</script>

