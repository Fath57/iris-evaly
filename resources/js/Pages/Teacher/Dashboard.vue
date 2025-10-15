<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Tableau de bord Professeur</h1>
                <p class="mt-1 text-sm text-gray-600">Bienvenue {{ $page.props.auth.user.name }}</p>
            </div>

            <!-- Quick Actions -->
            <div class="mb-8">
                <Button
                    v-if="can('create exams')"
                    size="lg"
                    @click="createExam"
                >
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Créer un nouvel examen
                </Button>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                <!-- My Classes -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Mes classes
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">
                                            {{ stats.total_classes }}
                                        </div>
                                        <div v-if="stats.main_classes > 0" class="ml-2 flex items-baseline text-sm font-semibold text-orange-600">
                                            {{ stats.main_classes }} principales
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Students -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Étudiants
                                    </dt>
                                    <dd class="text-2xl font-semibold text-gray-900">
                                        {{ stats.total_students }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Exams -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-orange-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Examens créés
                                    </dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900">
                                            {{ stats.total_exams }}
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ongoing Exams -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        En cours
                                    </dt>
                                    <dd class="text-2xl font-semibold text-gray-900">
                                        {{ stats.ongoing_exams }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- My Classes -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-medium text-gray-900">Mes classes</h2>
                </div>
                
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="classItem in classes" :key="classItem.id" class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow">
                        <div class="px-6 py-5">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-lg font-semibold text-gray-900">{{ classItem.name }}</h3>
                                <Badge v-if="classItem.is_main_teacher" variant="primary">
                                    Principal
                                </Badge>
                            </div>

                            <p class="text-sm text-gray-600 mb-2">{{ classItem.code }}</p>

                            <div v-if="classItem.subject_name" class="mb-3">
                                <span 
                                    class="inline-flex items-center px-2 py-1 rounded text-xs font-medium text-white"
                                    :style="{ backgroundColor: classItem.subject_color || '#FF5E0E' }"
                                >
                                    {{ classItem.subject_name }}
                                </span>
                            </div>

                            <div class="text-sm text-gray-600">
                                <p>Niveau : {{ getLevelLabel(classItem.level) }}</p>
                                <p>Année : {{ classItem.academic_year }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="classes.length === 0" class="text-center py-12 bg-white rounded-lg shadow">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune classe assignée</h3>
                    <p class="mt-1 text-sm text-gray-500">Contactez l'administrateur pour vous assigner des classes</p>
                </div>
            </div>

            <!-- Exams Overview -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Upcoming Exams -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Examens à venir</h3>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <div v-for="exam in upcomingExams" :key="exam.id" class="px-6 py-4 hover:bg-gray-50 transition-colors">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <p class="font-medium text-gray-900">{{ exam.title }}</p>
                                    <Badge :variant="getStatusVariant(exam.status)">
                                        {{ getStatusLabel(exam.status) }}
                                    </Badge>
                                </div>
                                <p class="text-sm text-gray-500">{{ exam.subject_name }} - {{ exam.class_name }}</p>
                                <p class="text-sm text-gray-600 mt-1">
                                    <svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ formatDateTime(exam.start_date) }}
                                </p>
                            </div>
                        </div>
                        <div v-if="upcomingExams.length === 0" class="px-6 py-8 text-center text-sm text-gray-500">
                            Aucun examen à venir
                        </div>
                    </div>
                </div>

                <!-- Recent Exams -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Examens récents</h3>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <div v-for="exam in recentExams" :key="exam.id" class="px-6 py-4 hover:bg-gray-50 transition-colors">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <p class="font-medium text-gray-900">{{ exam.title }}</p>
                                    <Badge :variant="getStatusVariant(exam.status)">
                                        {{ getStatusLabel(exam.status) }}
                                    </Badge>
                                </div>
                                <p class="text-sm text-gray-500">{{ exam.subject_name }} - {{ exam.class_name }}</p>
                                <p class="text-sm text-gray-600 mt-1">
                                    Durée : {{ exam.duration_minutes }} min • {{ exam.total_points }} pts
                                </p>
                            </div>
                        </div>
                        <div v-if="recentExams.length === 0" class="px-6 py-8 text-center text-sm text-gray-500">
                            Aucun examen créé
                        </div>
                    </div>
                </div>
            </div>

            <!-- Exams Stats -->
            <div class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="bg-white px-4 py-5 shadow rounded-lg">
                    <dt class="text-sm font-medium text-gray-500 truncate">Brouillons</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ stats.draft_exams }}</dd>
                </div>
                <div class="bg-white px-4 py-5 shadow rounded-lg">
                    <dt class="text-sm font-medium text-gray-500 truncate">Publiés</dt>
                    <dd class="mt-1 text-3xl font-semibold text-blue-600">{{ stats.published_exams }}</dd>
                </div>
                <div class="bg-white px-4 py-5 shadow rounded-lg">
                    <dt class="text-sm font-medium text-gray-500 truncate">En cours</dt>
                    <dd class="mt-1 text-3xl font-semibold text-green-600">{{ stats.ongoing_exams }}</dd>
                </div>
                <div class="bg-white px-4 py-5 shadow rounded-lg">
                    <dt class="text-sm font-medium text-gray-500 truncate">Terminés</dt>
                    <dd class="mt-1 text-3xl font-semibold text-purple-600">{{ stats.completed_exams }}</dd>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from '@/Components/Button.vue';
import Badge from '@/Components/Badge.vue';

const props = defineProps({
    stats: Object,
    classes: Array,
    mainClasses: Array,
    recentExams: Array,
    upcomingExams: Array,
});

const page = usePage();

const can = (permission) => {
    return page.props.auth.user.permissions.includes(permission);
};

const createExam = () => {
    // TODO: Implémenter la création d'examen
    router.visit('/admin/exams/create');
};

const getLevelLabel = (level) => {
    const labels = {
        'primaire': 'Primaire',
        'college': 'Collège',
        'lycee': 'Lycée',
        'universite': 'Université',
    };
    return labels[level] || level;
};

const getStatusLabel = (status) => {
    const labels = {
        'draft': 'Brouillon',
        'published': 'Publié',
        'ongoing': 'En cours',
        'completed': 'Terminé',
        'archived': 'Archivé',
    };
    return labels[status] || status;
};

const getStatusVariant = (status) => {
    const variants = {
        'draft': 'default',
        'published': 'info',
        'ongoing': 'success',
        'completed': 'primary',
        'archived': 'default',
    };
    return variants[status] || 'default';
};

const formatDateTime = (dateTime) => {
    return new Date(dateTime).toLocaleDateString('fr-FR', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>


