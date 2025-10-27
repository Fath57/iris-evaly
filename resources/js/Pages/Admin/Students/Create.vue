<template>
  <AppLayout title="Ajouter un étudiant">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Ajouter un étudiant
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <form @submit.prevent="submit">
              <div class="max-w-2xl">
                <!-- Informations de base -->
                <div class="space-y-6">
                  <h3 class="text-lg font-medium text-gray-900 border-b pb-2">Informations de l'étudiant</h3>

                  <FormInput
                    id="last_name"
                    v-model="form.last_name"
                    type="text"
                    label="Nom"
                    placeholder="Ex: Dupont"
                    required
                    :error="form.errors.last_name"
                  />

                  <FormInput
                    id="first_name"
                    v-model="form.first_name"
                    type="text"
                    label="Prénom"
                    placeholder="Ex: Jean"
                    required
                    :error="form.errors.first_name"
                  />

                  <FormInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    label="Email"
                    placeholder="Ex: jean.dupont@exemple.com"
                    required
                    :error="form.errors.email"
                  />

                  <FormSelect
                    id="class_id"
                    v-model="form.class_id"
                    label="Classe"
                    placeholder="Sélectionner une classe"
                    :options="classOptions"
                    required
                    :error="form.errors.class_id"
                  />

                  <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                    <div class="flex">
                      <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-600"></i>
                      </div>
                      <div class="ml-3">
                        <p class="text-sm text-blue-700">
                          Le numéro étudiant sera généré automatiquement. L'étudiant pourra définir son mot de passe lors de sa première connexion.
                        </p>
                      </div>
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
                  <i class="fas fa-plus mr-2"></i> Créer l'étudiant
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
import { ref, computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import FormInput from '@/Components/FormInput.vue';
import FormSelect from '@/Components/FormSelect.vue';
import Button from '@/Components/Button.vue';
import Toast from '@/Components/Toast.vue';

const props = defineProps({
  classes: Array,
});

const form = useForm({
  first_name: '',
  last_name: '',
  email: '',
  class_id: '',
});

const classOptions = computed(() => {
  return props.classes.map(classItem => ({
    value: classItem.id,
    label: classItem.name,
  }));
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
  form.post(route('admin.students.store'), {
    onSuccess: () => {
      form.reset();
      showToast('success', 'Succès', 'Étudiant créé avec succès');
    },
    onError: (errors) => {
      showToast('error', 'Erreur', 'Veuillez corriger les erreurs dans le formulaire');
    },
  });
};
</script>
