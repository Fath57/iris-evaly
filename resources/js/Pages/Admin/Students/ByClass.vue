<template>
  <AdminLayout :title="`Étudiants de la classe ${classData.name}`">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Étudiants de la classe {{ classData.name }}
        </h2>
        <div class="flex space-x-2">
          <Link :href="route('admin.classes.index')" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
            <i class="fas fa-arrow-left mr-2"></i> Retour aux classes
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <!-- Actions -->
            <div class="flex flex-col md:flex-row justify-between mb-6 space-y-4 md:space-y-0">
              <div class="flex space-x-2">
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
              <div>
                <FormInput
                  id="search"
                  type="text"
                  v-model="search"
                  placeholder="Rechercher un étudiant..."
                  @input="filterStudents"
                />
              </div>
            </div>

            <!-- Tableau des étudiants -->
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Numéro
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Nom
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Email
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Téléphone
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Statut
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="student in filteredStudents" :key="student.id">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ student.student_number }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">
                        {{ student.last_name }} {{ student.first_name }}
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ student.email }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ student.phone || '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span v-if="student.is_active" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Actif
                      </span>
                      <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        Inactif
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <div class="flex space-x-2">
                        <Link :href="route('admin.students.edit', student.id)" class="text-indigo-600 hover:text-indigo-900">
                          <i class="fas fa-edit"></i>
                        </Link>
                        <button @click="confirmDelete(student)" class="text-red-600 hover:text-red-900">
                          <i class="fas fa-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="filteredStudents.length === 0">
                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                      Aucun étudiant trouvé dans cette classe
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal d'import des étudiants -->
    <Modal :show="showImportModal" @close="closeImportModal">
      <div class="p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">
          <i class="fas fa-file-import text-blue-600 mr-2"></i>
          Importer des étudiants dans {{ classData.name }}
        </h2>

        <div class="space-y-4">
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
                    <li>Les étudiants seront ajoutés à la classe {{ classData.name }}</li>
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
                :disabled="!selectedFile || isImporting"
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
      @close="closeModal"
      @confirm="deleteStudent"
    />
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import FormInput from '@/Components/FormInput.vue';
import Button from '@/Components/Button.vue';
import Modal from '@/Components/Modal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';

const props = defineProps({
  students: Array,
  class: Object,
});

// Rename class to classData to avoid conflicts with the reserved keyword
const classData = props.class;

// État local
const search = ref('');
const showImportModal = ref(false);
const fileInput = ref(null);
const selectedFile = ref(null);
const isImporting = ref(false);
const confirmingDeletion = ref(false);
const studentToDelete = ref(null);
const allStudents = ref(props.students);

// Filtrer les étudiants en fonction de la recherche
const filteredStudents = computed(() => {
  if (!search.value) return allStudents.value;

  const searchLower = search.value.toLowerCase();
  return allStudents.value.filter(student =>
    student.first_name.toLowerCase().includes(searchLower) ||
    student.last_name.toLowerCase().includes(searchLower) ||
    student.email.toLowerCase().includes(searchLower) ||
    (student.student_number && student.student_number.toLowerCase().includes(searchLower))
  );
});

const filterStudents = () => {
  // Cette fonction est appelée à chaque modification de la recherche
  // Le filtrage est géré par le computed property
};

const handleFileChange = (event) => {
  const file = event.target.files?.[0];
  selectedFile.value = file || null;
};

const closeImportModal = () => {
  showImportModal.value = false;
  selectedFile.value = null;
  if (fileInput.value) {
    fileInput.value.value = null;
  }
};

const importStudents = () => {
  if (!selectedFile.value) {
    return;
  }

  isImporting.value = true;
  const formData = new FormData();
  formData.append('file', selectedFile.value);

  router.post(route('admin.students.import', classData.id), formData, {
    onSuccess: () => {
      isImporting.value = false;
      closeImportModal();
    },
    onError: () => {
      isImporting.value = false;
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
    router.delete(route('admin.students.destroy', studentToDelete.value.id), {
      onSuccess: () => {
        closeModal();
        // Mettre à jour la liste des étudiants après la suppression
        allStudents.value = allStudents.value.filter(s => s.id !== studentToDelete.value.id);
      },
    });
  }
};
</script>
