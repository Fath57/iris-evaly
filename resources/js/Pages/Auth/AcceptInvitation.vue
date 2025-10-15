<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-orange-50 to-orange-100 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-2xl shadow-xl">
            <div>
                <h2 class="text-center text-3xl font-bold text-gray-900">
                    Bienvenue sur Evaly
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Complétez votre profil pour accéder à la plateforme
                </p>
                <div class="mt-4 p-3 bg-orange-50 rounded-lg">
                    <p class="text-sm text-orange-800">
                        <strong>Email :</strong> {{ invitation.email }}
                    </p>
                    <p class="text-sm text-orange-800">
                        <strong>Rôle :</strong> {{ getRoleLabel(invitation.role) }}
                    </p>
                </div>
            </div>
            
            <form class="mt-8 space-y-6" @submit.prevent="submit">
                <div v-if="Object.keys(form.errors).length" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <ul class="list-disc list-inside text-sm">
                        <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
                    </ul>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">
                            Prénom *
                        </label>
                        <input
                            id="first_name"
                            v-model="form.first_name"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            placeholder="Votre prénom"
                        />
                    </div>
                    
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">
                            Nom *
                        </label>
                        <input
                            id="last_name"
                            v-model="form.last_name"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            placeholder="Votre nom"
                        />
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            Mot de passe *
                        </label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            required
                            class="w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            placeholder="••••••••"
                        />
                    </div>
                    
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                            Confirmer le mot de passe *
                        </label>
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            required
                            class="w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            placeholder="••••••••"
                        />
                    </div>
                </div>

                <div>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <span v-if="form.processing">Création du compte...</span>
                        <span v-else>Créer mon compte</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    invitation: Object,
});

const form = useForm({
    first_name: '',
    last_name: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('invitation.accept', props.invitation.token));
};

const getRoleLabel = (role) => {
    const labels = {
        'super-admin': 'Super Administrateur',
        'admin': 'Administrateur',
        'teacher': 'Professeur',
        'student': 'Étudiant',
        'assistant': 'Assistant',
    };
    return labels[role] || role;
};
</script>
