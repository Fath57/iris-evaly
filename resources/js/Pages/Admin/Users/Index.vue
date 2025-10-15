<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Utilisateurs</h1>
                    <p class="mt-1 text-sm text-gray-600">Gestion des utilisateurs et invitations</p>
                </div>
                <Button
                    v-if="can('create users')"
                    @click="showInviteModal = true"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Inviter un utilisateur
                </Button>
            </div>

            <!-- Pending Invitations -->
            <div v-if="pendingInvitations.length > 0" class="mb-8">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Invitations en attente</h2>
                <div class="bg-white shadow overflow-hidden rounded-lg">
                    <div class="divide-y divide-gray-200">
                        <div v-for="invitation in pendingInvitations" :key="invitation.id" class="px-6 py-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-900">{{ invitation.email }}</p>
                                    <p class="text-sm text-gray-500">
                                        Rôle: {{ getRoleLabel(invitation.role) }} • 
                                        Invité par: {{ invitation.inviter.name }} • 
                                        Expire le: {{ formatDate(invitation.expires_at) }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Badge variant="warning">En attente</Badge>
                                    <Button
                                        variant="secondary"
                                        size="sm"
                                        @click="copyInvitationLink(invitation.token)"
                                    >
                                        Copier le lien
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users DataTable -->
            <DataTable
                :data="users"
                :columns="columns"
                route-name="admin.users.index"
                search-placeholder="Rechercher par nom ou email..."
                empty-message="Aucun utilisateur trouvé"
            >
                <template #cell-name="{ row }">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 rounded-full bg-orange-100 flex items-center justify-center">
                                <span class="text-sm font-medium text-orange-800">
                                    {{ row.name.charAt(0).toUpperCase() }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ row.name }}</p>
                            <p class="text-sm text-gray-500">{{ row.email }}</p>
                        </div>
                    </div>
                </template>

                <template #cell-roles="{ row }">
                    <div class="flex flex-wrap gap-1">
                        <Badge
                            v-for="role in row.roles"
                            :key="role.id"
                            variant="info"
                        >
                            {{ getRoleLabel(role.name) }}
                        </Badge>
                    </div>
                </template>

                <template #cell-created_at="{ value }">
                    {{ formatDate(value) }}
                </template>

                <template #actions="{ row }">
                    <div class="flex items-center justify-end gap-2">
                        <Button
                            v-if="can('edit users') && isTeacher(row)"
                            variant="success"
                            size="sm"
                            @click="assignClasses(row.id)"
                        >
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Classes
                        </Button>
                        <Button
                            v-if="can('edit users')"
                            variant="secondary"
                            size="sm"
                            @click="editUser(row.id)"
                        >
                            Modifier
                        </Button>
                        <Button
                            v-if="can('delete users') && row?.id !== $page.props.auth.user.id"
                            variant="danger"
                            size="sm"
                            @click="confirmDelete(row)"
                        >
                            Supprimer
                        </Button>
                    </div>
                </template>
            </DataTable>

            <!-- Invite Modal -->
            <Modal
                :show="showInviteModal"
                title="Inviter un utilisateur"
                size="md"
                @close="showInviteModal = false"
            >
                <form @submit.prevent="sendInvitation" class="space-y-4">
                    <FormInput
                        id="email"
                        v-model="inviteForm.email"
                        type="email"
                        label="Adresse email"
                        placeholder="utilisateur@example.com"
                        required
                        :error="inviteForm.errors.email"
                    />

                    <FormSelect
                        id="role"
                        v-model="inviteForm.role"
                        label="Rôle"
                        placeholder="Sélectionner un rôle"
                        required
                        :options="roleOptions"
                        :error="inviteForm.errors.role"
                    />
                </form>

                <template #footer>
                    <div class="flex justify-end gap-3">
                        <Button
                            variant="secondary"
                            @click="showInviteModal = false"
                        >
                            Annuler
                        </Button>
                        <Button
                            :disabled="inviteForm.processing"
                            @click="sendInvitation"
                        >
                            <span v-if="inviteForm.processing">Envoi...</span>
                            <span v-else>Envoyer l'invitation</span>
                        </Button>
                    </div>
                </template>
            </Modal>

            <!-- Delete Confirmation Modal -->
            <ConfirmModal
                :show="showDeleteModal"
                title="Supprimer l'utilisateur"
                :message="`Êtes-vous sûr de vouloir supprimer ${userToDelete?.name} ? Cette action est irréversible.`"
                confirm-text="Supprimer"
                type="danger"
                @close="showDeleteModal = false"
                @confirm="deleteUser"
            />
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, usePage, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import Modal from '@/Components/Modal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import Button from '@/Components/Button.vue';
import FormInput from '@/Components/FormInput.vue';
import FormSelect from '@/Components/FormSelect.vue';
import Badge from '@/Components/Badge.vue';

const props = defineProps({
    users: Object,
    pendingInvitations: Array,
    roles: Array,
    filters: Object,
});

const page = usePage();
const showInviteModal = ref(false);
const showDeleteModal = ref(false);
const userToDelete = ref(null);

const inviteForm = useForm({
    email: '',
    role: '',
});

const columns = [
    { key: 'name', label: 'Utilisateur', sortable: true },
    { key: 'roles', label: 'Rôles', sortable: false },
    { key: 'created_at', label: 'Créé le', sortable: true },
];

const roleOptions = computed(() => {
    return props.roles.map(role => ({
        value: role.name,
        label: getRoleLabel(role.name),
    }));
});

const can = (permission) => {
    return page.props.auth.user.permissions.includes(permission);
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

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const sendInvitation = () => {
    inviteForm.post(route('admin.users.invite'), {
        onSuccess: () => {
            showInviteModal.value = false;
            inviteForm.reset();
        },
    });
};

const copyInvitationLink = (token) => {
    const link = window.location.origin + route('invitation.show', token);
    navigator.clipboard.writeText(link);
    // TODO: Show toast notification
    alert('Lien d\'invitation copié !');
};

const isTeacher = (user) => {
    return user.roles.some(role => role.name === 'teacher');
};

const assignClasses = (id) => {
    router.visit(route('admin.users.assign-classes', id));
};

const editUser = (id) => {
    router.visit(route('admin.users.edit', id));
};

const confirmDelete = (user) => {
    userToDelete.value = user;
    showDeleteModal.value = true;
};

const deleteUser = () => {
    if (userToDelete.value) {
        router.delete(route('admin.users.destroy', userToDelete.value.id), {
            onSuccess: () => {
                showDeleteModal.value = false;
                userToDelete.value = null;
            },
        });
    }
};
</script>
