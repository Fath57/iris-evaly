<template>
  <AppLayout title="Modifier un étudiant">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Modifier un étudiant
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <form @submit.prevent="submit">
              <div class="max-w-2xl space-y-6">
                <h3 class="text-lg font-medium text-gray-900 border-b pb-2">Informations de l'étudiant</h3>

                <FormInput
                  id="last_name"
                  v-model="form.last_name"
                  type="text"
                  label="Nom"
                  required
                  :error="form.errors.last_name"
                />

                <FormInput
                  id="first_name"
                  v-model="form.first_name"
                  type="text"
                  label="Prénom"
                  required
                  :error="form.errors.first_name"
                />

                <FormInput
                  id="email"
                  v-model="form.email"
                  type="email"
                  label="Email"
                  required
                  :error="form.errors.email"
                />

                <FormInput
                  id="student_number"
                  v-model="form.student_number"
                  type="text"
                  label="Numéro étudiant"
                  disabled
                  helper="Le numéro étudiant ne peut pas être modifié"
                />

                <div>
                  <label for="is_active" class="block text-sm font-medium text-gray-700 mb-1">
                    Statut
                  </label>
                  <select
                    id="is_active"
                    v-model="form.is_active"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors"
                  >
                    <option :value="true">Actif</option>
                    <option :value="false">Inactif</option>
                  </select>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                  <div class="flex">
                    <div class="flex-shrink-0">
                      <i class="fas fa-info-circle text-blue-600"></i>
                    </div>
                    <div class="ml-3">
                      <p class="text-sm text-blue-700">
                        Seules les informations de base peuvent être modifiées ici.
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="flex items-center justify-end mt-8 space-x-3">
                <Link :href="route('admin.students.index')">
                  <Button variant="secondary">
                    <i class="fas fa-times mr-2"></i> Annuler
                  </Button>
                </Link>
                <Button
                  type="submit"
                  variant="success"
                  :disabled="form.processing"
                >
                  <i class="fas fa-save mr-2"></i> Mettre à jour
                </Button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

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
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FormInput from '@/Components/FormInput.vue';
import Button from '@/Components/Button.vue';
import Toast from '@/Components/Toast.vue';

const props = defineProps({
  student: Object,
});

const form = useForm({
  first_name: props.student.first_name,
  last_name: props.student.last_name,
  email: props.student.email,
  student_number: props.student.student_number,
  is_active: props.student.is_active,
});

// Toast state
const toast = ref({
  show: false,
  type: 'info',
  title: '',
  message: '',
});

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

const submit = () => {
  form.put(route('admin.students.update', props.student.id), {
    onSuccess: () => {
      showToast('success', 'Succès', 'Étudiant modifié avec succès');
    },
    onError: () => {
      showToast('error', 'Erreur', 'Veuillez corriger les erreurs dans le formulaire');
    },
  });
};
</script>
