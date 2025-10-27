<template>
  <AppLayout title="Gestion des étudiants">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Gestion des étudiants
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <!-- Filtres et recherche -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
              <FormInput
                id="search"
                v-model="search"
                label="Rechercher"
                placeholder="Nom, email, numéro..."
                @keyup.enter="applyFilters"
              />
              <FormSelect
                id="class_id"
                v-model="selectedClass"
                label="Classe"
                placeholder="Toutes les classes"
                :options="classOptions"
                @change="applyFilters"
              />
              <FormSelect
                id="is_active"
                v-model="isActive"
                label="Statut"
                placeholder="Tous les statuts"
                :options="statusOptions"
                @change="applyFilters"
              />
              <div class="flex items-end gap-2">
                <Button variant="primary" @click="applyFilters">
                  <i class="fas fa-search mr-2"></i> Filtrer
                </Button>
                <Button variant="secondary" @click="resetFilters">
                  <i class="fas fa-undo mr-2"></i> Réinitialiser
                </Button>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col md:flex-row gap-4 mb-6">
              <Link :href="route('admin.students.create')" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <i class="fas fa-plus mr-2"></i> Ajouter un étudiant
              </Link>
              <Button
                variant="primary"
                @click="showImportModal = true"
              >
                <i class="fas fa-file-import mr-2"></i> Importer des étudiants
              </Button>
            </div>

            <!-- Tableau des étudiants avec DataTable -->
            <DataTable
              :data="students"
              :columns="tableColumns"
              route-name="admin.students.index"
              search-placeholder="Rechercher par nom, email, numéro..."
              empty-message="Aucun étudiant trouvé"
            >
              <template #cell-name="{ row }">
                <div class="text-sm font-medium text-gray-900">
                  {{ row.last_name }} {{ row.first_name }}
                </div>
              </template>

              <template #cell-classes="{ row }">
                <div v-if="row.classes && row.classes.length > 0">
                  <span v-for="classItem in row.classes" :key="classItem.id" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-1 mb-1">
                    {{ classItem.name }}
                  </span>
                </div>
                <span v-else class="text-gray-500">-</span>
              </template>

              <template #cell-is_active="{ row }">
                <span v-if="row.is_active" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                  Actif
                </span>
                <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                  Inactif
                </span>
              </template>

              <template #actions="{ row }">
                <div class="flex space-x-2">
                  <Link :href="route('admin.students.edit', row.id)" class="text-indigo-600 hover:text-indigo-900">
                    <i class="fas fa-edit"></i>
                  </Link>
                  <button @click="confirmDelete(row)" class="text-red-600 hover:text-red-900">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </template>
            </DataTable>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal d'import des étudiants -->
    <Modal :show="showImportModal" @close="closeImportModal">
      <div class="p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">
          <i class="fas fa-file-import text-blue-600 mr-2"></i>
          Importer des étudiants
        </h2>

        <div class="space-y-4">
          <FormSelect
            id="import_class_id"
            v-model="importClassId"
            label="Classe"
            placeholder="Sélectionner une classe"
            :options="classOptions"
            required
          />

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Fichier Excel/CSV
              <span class="text-red-500">*</span>
            </label>
            <input
              type="file"
              ref="fileInput"
              class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer"
              accept=".xlsx,.xls,.csv"
              @change="handleFileChange"
            />
            <p class="mt-1 text-sm text-gray-500">
              Formats acceptés : .xlsx, .xls, .csv
            </p>
          </div>

          <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-600"></i>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Instructions</h3>
                <div class="mt-2 text-sm text-blue-700">
                  <ul class="list-disc list-inside space-y-1">
                    <li>Téléchargez le modèle Excel ci-dessous</li>
                    <li>Remplissez les colonnes : nom, prénom, email</li>
                    <li>Importez le fichier rempli</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <div class="flex items-center justify-between pt-4 border-t">
            <a
              :href="route('admin.students.template.download')"
              class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800 font-medium"
              download
            >
              <i class="fas fa-download mr-2"></i>
              Télécharger le modèle Excel
            </a>
            <div class="flex space-x-3">
              <Button variant="secondary" @click="closeImportModal">
                Annuler
              </Button>
              <Button
                variant="primary"
                @click="importStudents"
                :disabled="!importClassId || !selectedFile || isImporting"
              >
                <i class="fas fa-upload mr-2"></i>
                {{ isImporting ? 'Import en cours...' : 'Importer' }}
              </Button>
            </div>
          </div>
        </div>
      </div>
    </Modal>

    <!-- Modal de confirmation de suppression -->
    <ConfirmModal
      :show="confirmingDeletion"
      title="Supprimer l'étudiant"
      message="Êtes-vous sûr de vouloir supprimer cet étudiant ? Cette action est irréversible. Toutes les données associées seront supprimées."
      confirm-text="Supprimer"
      cancel-text="Annuler"
      type="danger"
      :processing="isDeleting"
      @close="closeModal"
      @confirm="deleteStudent"
    />

    <!-- Toast notifications -->
    <Toast
      :show="toast.show"
      :type="toast.type"
      :title="toast.title"
      :message="toast.message"
      @close="hideToast"
    />
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FormInput from '@/Components/FormInput.vue';
import FormSelect from '@/Components/FormSelect.vue';
import Button from '@/Components/Button.vue';
import DataTable from '@/Components/DataTable.vue';
import Modal from '@/Components/Modal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import Toast from '@/Components/Toast.vue';

const props = defineProps({
  students: Object,
  classes: Array,
  filters: Object,
});

// État local
const search = ref(props.filters?.search || '');
const selectedClass = ref(props.filters?.class_id || '');
const isActive = ref(props.filters?.is_active || '');
const showImportModal = ref(false);
const importClassId = ref('');
const fileInput = ref(null);
const selectedFile = ref(null);
const isImporting = ref(false);
const confirmingDeletion = ref(false);
const studentToDelete = ref(null);
const isDeleting = ref(false);

// Toast state
const toast = ref({
  show: false,
  type: 'info', // success, error, warning, info
  title: '',
  message: '',
});

// Options pour les selects
const classOptions = computed(() => {
  return props.classes.map(classItem => ({
    value: classItem.id,
    label: classItem.name,
  }));
});

const statusOptions = computed(() => [
  { value: '1', label: 'Actif' },
  { value: '0', label: 'Inactif' },
]);

// Colonnes du tableau
const tableColumns = computed(() => [
  { key: 'student_number', label: 'Numéro', sortable: true },
  { key: 'name', label: 'Nom', sortable: true },
  { key: 'email', label: 'Email', sortable: true },
  { key: 'phone', label: 'Téléphone', sortable: false },
  { key: 'classes', label: 'Classes', sortable: false },
  { key: 'is_active', label: 'Statut', sortable: true },
]);

// Méthodes
const applyFilters = () => {
  router.get(route('admin.students.index'), {
    search: search.value,
    class_id: selectedClass.value,
    is_active: isActive.value,
  }, {
    preserveState: true,
    replace: true,
  });
};

const resetFilters = () => {
  search.value = '';
  selectedClass.value = '';
  isActive.value = '';
  applyFilters();
};

const handleFileChange = (event) => {
  const file = event.target.files?.[0];
  selectedFile.value = file || null;
};

const closeImportModal = () => {
  showImportModal.value = false;
  importClassId.value = '';
  selectedFile.value = null;
  if (fileInput.value) {
    fileInput.value.value = null;
  }
};

const importStudents = () => {
  if (!importClassId.value || !selectedFile.value) {
    showToast('error', 'Erreur', 'Veuillez sélectionner une classe et un fichier');
    return;
  }

  isImporting.value = true;
  const formData = new FormData();
  formData.append('file', selectedFile.value);

  router.post(route('admin.students.import', importClassId.value), formData, {
    onSuccess: () => {
      isImporting.value = false;
      closeImportModal();
      showToast('success', 'Succès', 'Les étudiants ont été importés avec succès');
    },
    onError: (errors) => {
      isImporting.value = false;
      showToast('error', 'Erreur', 'Une erreur est survenue lors de l\'import');
    },
  });
};

const confirmDelete = (student) => {
  studentToDelete.value = student;
  confirmingDeletion.value = true;
};

const closeModal = () => {
  confirmingDeletion.value = false;
  setTimeout(() => {
    studentToDelete.value = null;
  }, 300);
};

const deleteStudent = () => {
  if (studentToDelete.value) {
    isDeleting.value = true;
    router.delete(route('admin.students.destroy', studentToDelete.value.id), {
      onSuccess: () => {
        isDeleting.value = false;
        closeModal();
        showToast('success', 'Succès', 'L\'étudiant a été supprimé avec succès');
      },
      onError: () => {
        isDeleting.value = false;
        showToast('error', 'Erreur', 'Une erreur est survenue lors de la suppression');
      },
    });
  }
};

const showToast = (type, title, message) => {
  toast.value = {
    show: true,
    type,
    title,
    message,
  };
};

const hideToast = () => {
  toast.value.show = false;
};
</script>
