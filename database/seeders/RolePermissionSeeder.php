<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\PermissionModule;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create modules
        $modules = [
            [
                'name' => 'Gestion des utilisateurs',
                'slug' => 'users',
                'description' => 'Gérer les utilisateurs du système',
                'icon' => 'users',
                'order' => 1,
            ],
            [
                'name' => 'Gestion des rôles',
                'slug' => 'roles',
                'description' => 'Gérer les rôles et les permissions',
                'icon' => 'shield',
                'order' => 2,
            ],
            [
                'name' => 'Gestion des classes',
                'slug' => 'classes',
                'description' => 'Gérer les classes et les inscriptions',
                'icon' => 'academic-cap',
                'order' => 3,
            ],
            [
                'name' => 'Gestion des matières',
                'slug' => 'subjects',
                'description' => 'Gérer les matières enseignées',
                'icon' => 'book-open',
                'order' => 4,
            ],
            [
                'name' => 'Gestion des examens',
                'slug' => 'exams',
                'description' => 'Créer et gérer les examens',
                'icon' => 'document-text',
                'order' => 5,
            ],
            [
                'name' => 'Gestion des questions',
                'slug' => 'questions',
                'description' => 'Créer et gérer les questions d\'examen',
                'icon' => 'question-mark-circle',
                'order' => 6,
            ],
            [
                'name' => 'Espace étudiant',
                'slug' => 'student',
                'description' => 'Fonctionnalités pour les étudiants',
                'icon' => 'user-group',
                'order' => 7,
            ],
            [
                'name' => 'Tableau de bord',
                'slug' => 'dashboard',
                'description' => 'Accès au tableau de bord et statistiques',
                'icon' => 'chart-bar',
                'order' => 8,
            ],
            [
                'name' => 'Paramètres',
                'slug' => 'settings',
                'description' => 'Paramètres système',
                'icon' => 'cog',
                'order' => 9,
            ],

        ];

        $moduleIds = [];
        foreach ($modules as $moduleData) {
            $module = PermissionModule::updateOrCreate($moduleData);
            $moduleIds[$module->slug] = $module->id;
        }

        // Create permissions with descriptions and modules
        $permissions = [
            // User management
            ['name' => 'view users', 'description' => 'Voir la liste des utilisateurs', 'module' => 'users'],
            ['name' => 'create users', 'description' => 'Créer de nouveaux utilisateurs', 'module' => 'users'],
            ['name' => 'edit users', 'description' => 'Modifier les utilisateurs existants', 'module' => 'users'],
            ['name' => 'delete users', 'description' => 'Supprimer des utilisateurs', 'module' => 'users'],

            // Role management
            ['name' => 'view roles', 'description' => 'Voir la liste des rôles', 'module' => 'roles'],
            ['name' => 'create roles', 'description' => 'Créer de nouveaux rôles', 'module' => 'roles'],
            ['name' => 'edit roles', 'description' => 'Modifier les rôles existants', 'module' => 'roles'],
            ['name' => 'delete roles', 'description' => 'Supprimer des rôles', 'module' => 'roles'],

            // Class management
            ['name' => 'view classes', 'description' => 'Voir la liste des classes', 'module' => 'classes'],
            ['name' => 'create classes', 'description' => 'Créer de nouvelles classes', 'module' => 'classes'],
            ['name' => 'edit classes', 'description' => 'Modifier les classes existantes', 'module' => 'classes'],
            ['name' => 'delete classes', 'description' => 'Supprimer des classes', 'module' => 'classes'],
            ['name' => 'manage class students', 'description' => 'Gérer les inscriptions des étudiants', 'module' => 'classes'],

            // Subject management
            ['name' => 'view subjects', 'description' => 'Voir la liste des matières', 'module' => 'subjects'],
            ['name' => 'create subjects', 'description' => 'Créer de nouvelles matières', 'module' => 'subjects'],
            ['name' => 'edit subjects', 'description' => 'Modifier les matières existantes', 'module' => 'subjects'],
            ['name' => 'delete subjects', 'description' => 'Supprimer des matières', 'module' => 'subjects'],

            // Exam management
            ['name' => 'view exams', 'description' => 'Voir la liste des examens', 'module' => 'exams'],
            ['name' => 'create exams', 'description' => 'Créer de nouveaux examens', 'module' => 'exams'],
            ['name' => 'edit exams', 'description' => 'Modifier les examens existants', 'module' => 'exams'],
            ['name' => 'delete exams', 'description' => 'Supprimer des examens', 'module' => 'exams'],
            ['name' => 'publish exams', 'description' => 'Publier des examens', 'module' => 'exams'],
            ['name' => 'grade exams', 'description' => 'Noter les examens', 'module' => 'exams'],

            // Question management
            ['name' => 'view questions', 'description' => 'Voir la liste des questions', 'module' => 'questions'],
            ['name' => 'create questions', 'description' => 'Créer de nouvelles questions', 'module' => 'questions'],
            ['name' => 'edit questions', 'description' => 'Modifier les questions existantes', 'module' => 'questions'],
            ['name' => 'delete questions', 'description' => 'Supprimer des questions', 'module' => 'questions'],

            // Student permissions
            ['name' => 'take exams', 'description' => 'Passer des examens', 'module' => 'student'],
            ['name' => 'view own results', 'description' => 'Consulter ses propres résultats', 'module' => 'student'],
            ['name' => 'view class materials', 'description' => 'Consulter le matériel de cours', 'module' => 'student'],
            ['name' => 'manage profile', 'description' => 'Gérer son profil personnel', 'module' => 'student'],
            ['name' => 'change password', 'description' => 'Modifier son mot de passe', 'module' => 'student'],
            ['name' => 'view schedule', 'description' => 'Consulter son emploi du temps', 'module' => 'student'],
            ['name' => 'submit assignments', 'description' => 'Soumettre des devoirs', 'module' => 'student'],

            // Student management
            ['name' => 'view students', 'description' => 'Voir la liste des étudiants', 'module' => 'student'],
            ['name' => 'create students', 'description' => 'Créer de nouveaux étudiants', 'module' => 'student'],
            ['name' => 'edit students', 'description' => 'Modifier les étudiants existants', 'module' => 'student'],
            ['name' => 'delete students', 'description' => 'Supprimer des étudiants', 'module' => 'student'],
            ['name' => 'import students', 'description' => 'Importer des étudiants', 'module' => 'student'],
            ['name' => 'assign students to class', 'description' => 'Assigner des étudiants à une classe', 'module' => 'student'],

            // Dashboard
            ['name' => 'view dashboard', 'description' => 'Accéder au tableau de bord', 'module' => 'dashboard'],
            ['name' => 'view analytics', 'description' => 'Voir les statistiques et analyses', 'module' => 'dashboard'],

            // Settings
            ['name' => 'view settings', 'description' => 'Voir les paramètres système', 'module' => 'settings'],
            ['name' => 'edit settings', 'description' => 'Modifier les paramètres système', 'module' => 'settings'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::updateOrCreate([
                'name' => $permissionData['name'],
                'description' => $permissionData['description'],
                'module_id' => $moduleIds[$permissionData['module']],
            ]);
        }

        // Create roles with descriptions and assign permissions

        // Super Admin - All permissions
        $superAdminRole = Role::updateOrCreate([
            'name' => 'super-admin',
            'description' => 'Super administrateur avec tous les droits sur le système',
        ]);
        $superAdminRole->syncPermissions(Permission::all());

        // Admin - Manage everything except system settings
        $adminRole = Role::updateOrCreate([
            'name' => 'admin',
            'description' => 'Administrateur avec droits de gestion complète sauf les paramètres système',
        ]);
        $adminRole->syncPermissions([
            'view users', 'create users', 'edit users', 'delete users',
            'view roles', 'view classes', 'create classes', 'edit classes', 'delete classes',
            'manage class students', 'view subjects', 'create subjects', 'edit subjects', 'delete subjects',
            'view exams', 'create exams', 'edit exams', 'delete exams', 'publish exams', 'grade exams',
            'view questions', 'create questions', 'edit questions', 'delete questions',
            'view students', 'create students', 'edit students', 'delete students', 'import students', 'assign students to class',
            'view dashboard', 'view analytics',
        ]);

        // Teacher - Manage their classes, create exams, grade
        $teacherRole = Role::updateOrCreate([
            'name' => 'teacher',
            'description' => 'Professeur pouvant gérer ses classes, créer des examens et noter',
        ]);
        $teacherRole->syncPermissions([
            'view classes', 'edit classes', 'manage class students',
            'view subjects', 'view exams', 'create exams', 'edit exams', 'publish exams', 'grade exams',
            'view questions', 'create questions', 'edit questions', 'delete questions',
            'view students', 'edit students', 'import students', 'assign students to class',
            'view dashboard', 'view analytics',
        ]);

        // Student - Take exams and view results
        $studentRole = Role::updateOrCreate([
            'name' => 'student',
            'description' => 'Étudiant pouvant passer des examens et consulter ses résultats',
        ]);
        $studentRole->syncPermissions([
            'view classes', 'view subjects', 'view exams',
            'take exams', 'view own results', 'view class materials',
            'view dashboard', 'manage profile', 'change password',
            'view schedule', 'submit assignments',
        ]);

        // Assistant Teacher - Help with grading and question management
        $assistantRole = Role::updateOrCreate([
            'name' => 'assistant',
            'description' => 'Assistant pédagogique aidant à la création de questions et à la notation',
        ]);
        $assistantRole->syncPermissions([
            'view classes', 'view subjects', 'view exams',
            'view questions', 'create questions', 'edit questions',
            'grade exams', 'view dashboard',
        ]);
    }
}
