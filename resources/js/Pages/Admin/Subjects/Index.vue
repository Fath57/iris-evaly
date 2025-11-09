<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Matières</h1>
                    <p class="mt-1 text-sm text-gray-600">Gestion des matières enseignées</p>
                </div>
                <div class="flex gap-3">
                    <Button
                        v-if="can('create subjects')"
                        variant="secondary"
                        @click="showImportModal = true"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        Importer
                    </Button>
                    <Button
                        v-if="can('create subjects')"
                        @click="router.visit(route('admin.subjects.create'))"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nouvelle matière
                    </Button>
                </div>
            </div>

            <!-- Subjects DataTable -->
            <DataTable
                :data="subjects"
                :columns="columns"
                route-name="admin.subjects.index"
                search-placeholder="Rechercher une matière..."
                empty-message="Aucune matière trouvée"
            >
                <template #cell-name="{ row }">
                    <div class="flex items-center gap-3">
                        <div 
                            class="h-10 w-10 rounded-full flex items-center justify-center"
                            :style="{ backgroundColor: row.color || '#FF5E0E' }"
                        >
                            <span class="text-sm font-medium text-white">
                                {{ row.code.substring(0, 2) }}
                            </span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ row.name }}</p>
                            <p class="text-sm text-gray-500">{{ row.code }}</p>
                        </div>
                    </div>
                </template>

                <template #cell-color="{ row }">
                    <div class="flex items-center gap-2">
                        <div 
                            class="h-6 w-6 rounded border border-gray-300"
                            :style="{ backgroundColor: row.color || '#FF5E0E' }"
                        ></div>
                        <span class="text-sm text-gray-600">{{ row.color || '#FF5E0E' }}</span>
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
                            v-if="can('edit subjects')"
                            variant="secondary"
                            size="sm"
                            @click="editSubject(row.id)"
                        >
                            Modifier
                        </Button>
                        <Button
                            v-if="can('delete subjects')"
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
                title="Importer des matières"
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
                            <li><strong>name</strong> : Nom de la matière (requis)</li>
                            <li><strong>code</strong> : Code unique (requis)</li>
                            <li><strong>description</strong> : Description (optionnel)</li>
                            <li><strong>color</strong> : Couleur hex (optionnel, par défaut #FF5E0E)</li>
                            <li><strong>is_active</strong> : 1 pour actif, 0 pour inactif (optionnel)</li>
                        </ul>
                        <div class="mt-3">
                            <a
                                :href="route('admin.subjects.template.download')"
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
                title="Supprimer la matière"
                :message="`Êtes-vous sûr de vouloir supprimer ${subjectToDelete?.name} ? Cette action est irréversible.`"
                confirm-text="Supprimer"
                type="danger"
                @close="showDeleteModal = false"
                @confirm="deleteSubject"
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
    subjects: Object,
    filters: Object,
});

const page = usePage();
const showImportModal = ref(false);
const showDeleteModal = ref(false);
const subjectToDelete = ref(null);
const selectedFile = ref(null);

const importForm = useForm({
    file: null,
});

const columns = [
    { key: 'name', label: 'Matière', sortable: true },
    { key: 'color', label: 'Couleur', sortable: false },
    { key: 'status', label: 'Statut', sortable: false },
];

const can = (permission) => {
    return page.props.auth.user.permissions.includes(permission);
};

const handleFileChange = (event) => {
    selectedFile.value = event.target.files[0];
    importForm.file = event.target.files[0];
};

const submitImport = () => {
    importForm.post(route('admin.subjects.import'), {
        onSuccess: () => {
            showImportModal.value = false;
            selectedFile.value = null;
            importForm.reset();
        },
    });
};

const editSubject = (id) => {
    router.visit(route('admin.subjects.edit', id));
};

const confirmDelete = (subject) => {
    subjectToDelete.value = subject;
    showDeleteModal.value = true;
};

const deleteSubject = () => {
    if (subjectToDelete.value) {
        router.delete(route('admin.subjects.destroy', subjectToDelete.value.id), {
            onSuccess: () => {
                showDeleteModal.value = false;
                subjectToDelete.value = null;
            },
        });
    }
};
</script>








