<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Classes</h1>
                    <p class="mt-1 text-sm text-gray-600">Gestion des classes et leurs étudiants</p>
                </div>
                <div class="flex gap-3">
                    <Button
                        v-if="can('create classes')"
                        variant="secondary"
                        @click="showImportModal = true"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        Importer
                    </Button>
                    <Button
                        v-if="can('create classes')"
                        @click="router.visit(route('admin.classes.create'))"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nouvelle classe
                    </Button>
                </div>
            </div>

            <!-- Classes DataTable -->
            <DataTable
                :data="classes"
                :columns="columns"
                route-name="admin.classes.index"
                search-placeholder="Rechercher une classe..."
                empty-message="Aucune classe trouvée"
            >
                <template #cell-name="{ row }">
                    <div>
                        <p class="font-medium text-gray-900">{{ row.name }}</p>
                        <p class="text-sm text-gray-500">{{ row.code }}</p>
                    </div>
                </template>

                <template #cell-level="{ row }">
                    <Badge :variant="getLevelColor(row.level)">
                        {{ getLevelLabel(row.level) }}
                    </Badge>
                </template>

                <template #cell-teachers="{ row }">
                    <div v-if="row.main_teacher_name" class="text-sm">
                        <p class="font-medium text-gray-900">{{ row.main_teacher_name }}</p>
                        <p v-if="row.teachers.length > 1" class="text-gray-500">
                            +{{ row.teachers.length - 1 }} autre(s)
                        </p>
                    </div>
                    <span v-else class="text-sm text-gray-400">Aucun professeur</span>
                </template>

                <template #cell-stats="{ row }">
                    <div class="text-sm text-gray-600">
                        <p>{{ row.students_count }} étudiants</p>
                        <p>{{ row.subjects_count }} matières</p>
                    </div>
                </template>

                <template #cell-status="{ row }">
                    <Badge :variant="row.is_active ? 'success' : 'default'">
                        {{ row.is_active ? 'Active' : 'Inactive' }}
                    </Badge>
                </template>

                <template #actions="{ row }">
                    <div class="flex items-center justify-end gap-2">
                        <Button
                            v-if="can('edit classes')"
                            variant="secondary"
                            size="sm"
                            @click="editClass(row.id)"
                        >
                            Modifier
                        </Button>
                        <Button
                            v-if="can('delete classes')"
                            variant="danger"
                            size="sm"
                            @click="confirmDelete(row)"
                        >
                            Supprimer
                        </Button>
                    </div>
                </template>
            </DataTable>

            <!-- Import Modal -->
            <Modal
                :show="showImportModal"
                title="Importer des classes"
                size="lg"
                @close="showImportModal = false"
            >
                <div class="space-y-4">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-blue-900 mb-2">Format du fichier</h4>
                        <p class="text-sm text-blue-700 mb-3">
                            Le fichier doit contenir les colonnes suivantes :
                        </p>
                        <ul class="text-sm text-blue-700 list-disc list-inside space-y-1">
                            <li><strong>name</strong> : Nom de la classe (requis)</li>
                            <li><strong>code</strong> : Code unique (requis)</li>
                            <li><strong>description</strong> : Description (optionnel)</li>
                            <li><strong>academic_year</strong> : Année (optionnel)</li>
                            <li><strong>level</strong> : primaire, college, lycee, universite (optionnel)</li>
                            <li><strong>is_active</strong> : 1 pour actif, 0 pour inactif (optionnel)</li>
                        </ul>
                        <div class="mt-3">
                            <a
                                :href="route('admin.classes.template.download')"
                                class="text-sm text-blue-600 hover:text-blue-700 font-medium underline"
                            >
                                Télécharger le modèle de fichier
                            </a>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Fichier à importer
                        </label>
                        <input
                            type="file"
                            accept=".xlsx,.xls,.csv"
                            @change="handleFileChange"
                            class="block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-lg file:border-0
                                file:text-sm file:font-medium
                                file:bg-orange-50 file:text-orange-700
                                hover:file:bg-orange-100
                                cursor-pointer"
                        />
                    </div>
                </div>

                <template #footer>
                    <div class="flex justify-end gap-3">
                        <Button
                            variant="secondary"
                            @click="showImportModal = false"
                        >
                            Annuler
                        </Button>
                        <Button
                            :disabled="!selectedFile || importForm.processing"
                            @click="submitImport"
                        >
                            <span v-if="importForm.processing">Importation...</span>
                            <span v-else>Importer</span>
                        </Button>
                    </div>
                </template>
            </Modal>

            <!-- Delete Confirmation Modal -->
            <ConfirmModal
                :show="showDeleteModal"
                title="Supprimer la classe"
                :message="`Êtes-vous sûr de vouloir supprimer la classe ${classToDelete?.name} ? Tous les examens et données associés seront également supprimés.`"
                confirm-text="Supprimer"
                type="danger"
                @close="showDeleteModal = false"
                @confirm="deleteClass"
            />
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router, usePage, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import Modal from '@/Components/Modal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import Button from '@/Components/Button.vue';
import Badge from '@/Components/Badge.vue';

const props = defineProps({
    classes: Object,
    filters: Object,
});

const page = usePage();
const showImportModal = ref(false);
const showDeleteModal = ref(false);
const classToDelete = ref(null);
const selectedFile = ref(null);

const importForm = useForm({
    file: null,
});

const columns = [
    { key: 'name', label: 'Classe', sortable: true },
    { key: 'level', label: 'Niveau', sortable: true },
    { key: 'teachers', label: 'Professeur(s)', sortable: false },
    { key: 'stats', label: 'Statistiques', sortable: false },
    { key: 'status', label: 'Statut', sortable: false },
];

const can = (permission) => {
    return page.props.auth.user.permissions.includes(permission);
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

const getLevelColor = (level) => {
    const colors = {
        'primaire': 'info',
        'college': 'success',
        'lycee': 'primary',
        'universite': 'warning',
    };
    return colors[level] || 'default';
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('fr-FR');
};

const editClass = (id) => {
    router.visit(route('admin.classes.edit', id));
};

const confirmDelete = (classItem) => {
    classToDelete.value = classItem;
    showDeleteModal.value = true;
};

const handleFileChange = (event) => {
    selectedFile.value = event.target.files[0];
    importForm.file = event.target.files[0];
};

const submitImport = () => {
    importForm.post(route('admin.classes.import'), {
        onSuccess: () => {
            showImportModal.value = false;
            selectedFile.value = null;
            importForm.reset();
        },
    });
};

const deleteClass = () => {
    if (classToDelete.value) {
        router.delete(route('admin.classes.destroy', classToDelete.value.id), {
            onSuccess: () => {
                showDeleteModal.value = false;
                classToDelete.value = null;
            },
        });
    }
};
</script>
