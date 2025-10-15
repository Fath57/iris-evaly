<template>
    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
        <div class="flex justify-between items-start mb-4">
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ exam.title }}</h3>
                <p class="text-sm text-gray-600">{{ exam.subject?.name }}</p>
            </div>
            <span 
                :class="statusClass"
                class="px-3 py-1 text-xs font-semibold rounded-full"
            >
                {{ statusText }}
            </span>
        </div>

        <p v-if="exam.description" class="text-sm text-gray-700 mb-4 line-clamp-2">
            {{ exam.description }}
        </p>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div class="flex items-center gap-2 text-sm text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ exam.duration_minutes }} min</span>
            </div>
            <div class="flex items-center gap-2 text-sm text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>{{ exam.questions_count || 0 }} questions</span>
            </div>
        </div>

        <div class="flex items-center gap-2 text-sm text-gray-600 mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>{{ formatDate(exam.start_date) }} - {{ formatDate(exam.end_date) }}</span>
        </div>

        <div class="flex justify-end gap-2">
            <button 
                v-if="canView"
                @click="$emit('view', exam)"
                class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
            >
                Voir
            </button>
            <button 
                v-if="canEdit"
                @click="$emit('edit', exam)"
                class="px-4 py-2 text-sm bg-blue-500 text-white rounded-md hover:bg-blue-600"
            >
                Modifier
            </button>
            <button 
                v-if="canStart"
                @click="$emit('start', exam)"
                class="px-4 py-2 text-sm bg-primary text-white rounded-md hover:bg-primary-600"
            >
                Commencer
            </button>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ExamCard',
    props: {
        exam: {
            type: Object,
            required: true,
        },
        userRole: {
            type: String,
            default: 'student',
        },
    },
    computed: {
        statusClass() {
            switch (this.exam.status) {
                case 'draft':
                    return 'bg-gray-200 text-gray-700';
                case 'published':
                    return 'bg-green-100 text-green-800';
                case 'ongoing':
                    return 'bg-blue-100 text-blue-800';
                case 'completed':
                    return 'bg-purple-100 text-purple-800';
                case 'archived':
                    return 'bg-red-100 text-red-800';
                default:
                    return 'bg-gray-200 text-gray-700';
            }
        },
        statusText() {
            const statuses = {
                draft: 'Brouillon',
                published: 'Publié',
                ongoing: 'En cours',
                completed: 'Terminé',
                archived: 'Archivé',
            };
            return statuses[this.exam.status] || this.exam.status;
        },
        canView() {
            return true;
        },
        canEdit() {
            return this.userRole === 'teacher' || this.userRole === 'admin';
        },
        canStart() {
            return this.userRole === 'student' && this.exam.status === 'published';
        },
    },
    methods: {
        formatDate(date) {
            if (!date) return '';
            const d = new Date(date);
            return d.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
            });
        },
    },
};
</script>

