<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-orange-50 to-orange-100 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-2xl shadow-xl">
            <div>
                <h2 class="text-center text-3xl font-bold text-gray-900">
                    Evaly
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Plateforme d'examens en ligne
                </p>
            </div>
            <form class="mt-8 space-y-6" @submit.prevent="submit">
                <div v-if="form.errors.email" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    {{ form.errors.email }}
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            Adresse email
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 sm:text-sm"
                            placeholder="votre@email.com"
                        />
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            Mot de passe
                        </label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            required
                            class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 sm:text-sm"
                            placeholder="••••••••"
                        />
                    </div>
                </div>

                <div class="flex items-center">
                    <input
                        id="remember"
                        v-model="form.remember"
                        type="checkbox"
                        class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded"
                    />
                    <label for="remember" class="ml-2 block text-sm text-gray-900">
                        Se souvenir de moi
                    </label>
                </div>

                <div>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <span v-if="form.processing">Connexion...</span>
                        <span v-else>Se connecter</span>
                    </button>
                </div>

                <div class="text-center text-xs text-gray-500 mt-4">
                    <p class="font-semibold text-gray-700 mb-2">Comptes de test :</p>
                    <p>Admin: admin@evaly.com / password</p>
                    <p>Prof: prof.math@evaly.com / password</p>
                    <p>Étudiant: etudiant1@evaly.com / password</p>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

