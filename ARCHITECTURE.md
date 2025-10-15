# Architecture du projet Evaly

## 🏗️ Vue d'ensemble

Evaly est une plateforme d'examens en ligne construite avec une architecture moderne et scalable utilisant Laravel 12 + Inertia.js + Vue 3.

## 📐 Architecture technique

### Stack technologique

```
┌─────────────────────────────────────┐
│         Frontend (Vue 3)            │
│  - Inertia.js (SPA-like)            │
│  - Tailwind CSS                     │
│  - Composition API                  │
└─────────────┬───────────────────────┘
              │
              │ HTTP/AJAX
              │
┌─────────────▼───────────────────────┐
│      Backend (Laravel 12)           │
│  - RESTful Controllers              │
│  - Inertia Middleware               │
│  - Spatie Permission                │
└─────────────┬───────────────────────┘
              │
              │ Eloquent ORM
              │
┌─────────────▼───────────────────────┐
│       Database (MySQL)              │
│  - Users & Roles                    │
│  - Classes & Subjects               │
│  - Exams & Questions                │
└─────────────────────────────────────┘
```

## 🗂️ Structure des dossiers

```
evaly/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/              # Authentification
│   │   │   │   └── AuthController.php
│   │   │   └── Admin/             # Backoffice
│   │   │       ├── DashboardController.php
│   │   │       ├── UserController.php
│   │   │       └── ClassController.php
│   │   └── Middleware/
│   │       └── HandleInertiaRequests.php  # Partage de données globales
│   └── Models/
│       ├── User.php               # + HasRoles trait
│       ├── ClassModel.php
│       ├── Subject.php
│       ├── Exam.php
│       ├── Question.php
│       └── ExamAttempt.php
│
├── database/
│   ├── migrations/
│   │   ├── create_permission_tables.php    # Spatie
│   │   ├── create_classes_table.php
│   │   ├── create_subjects_table.php
│   │   ├── create_exams_table.php
│   │   ├── create_questions_table.php
│   │   └── create_exam_attempts_table.php
│   └── seeders/
│       ├── RolePermissionSeeder.php        # Rôles + Permissions
│       ├── AdminUserSeeder.php             # Utilisateurs de test
│       └── ClassSubjectSeeder.php          # Données de base
│
├── resources/
│   ├── js/
│   │   ├── app.js                 # Point d'entrée Vue
│   │   ├── Pages/                 # Pages Inertia
│   │   │   ├── Auth/
│   │   │   │   └── Login.vue
│   │   │   └── Admin/
│   │   │       ├── Dashboard.vue
│   │   │       ├── Users/
│   │   │       │   ├── Index.vue
│   │   │       │   ├── Create.vue
│   │   │       │   └── Edit.vue
│   │   │       └── Classes/
│   │   │           ├── Index.vue
│   │   │           ├── Create.vue
│   │   │           └── Edit.vue
│   │   ├── Layouts/
│   │   │   └── AdminLayout.vue    # Layout principal
│   │   └── Components/            # Composants réutilisables
│   ├── css/
│   │   └── app.css                # Tailwind imports
│   └── views/
│       └── app.blade.php          # Template root Inertia
│
└── routes/
    └── web.php                    # Routes web avec permissions
```

## 🔐 Système de permissions

### Hiérarchie des rôles

```
Super Admin
    ↓ (Toutes permissions)
Admin
    ↓ (Gestion complète sauf système)
Teacher
    ↓ (Classes, Examens, Notation)
Assistant
    ↓ (Questions, Aide notation)
Student
    ↓ (Examens, Résultats)
```

### Permissions par module

#### Users (11 permissions)
- view users
- create users
- edit users
- delete users
- view roles
- create roles
- edit roles
- delete roles
- view dashboard
- view analytics
- view settings
- edit settings

#### Classes (5 permissions)
- view classes
- create classes
- edit classes
- delete classes
- manage class students

#### Subjects (4 permissions)
- view subjects
- create subjects
- edit subjects
- delete subjects

#### Exams (6 permissions)
- view exams
- create exams
- edit exams
- delete exams
- publish exams
- grade exams

#### Questions (4 permissions)
- view questions
- create questions
- edit questions
- delete questions

#### Students (3 permissions)
- take exams
- view own results
- view class materials

## 🗄️ Schéma de base de données

### Entités principales

#### users
```sql
id, name, email, password, email_verified_at, created_at, updated_at
```

#### roles
```sql
id, name, guard_name, created_at, updated_at
```

#### permissions
```sql
id, name, guard_name, created_at, updated_at
```

#### classes
```sql
id, name, code, description, teacher_id, academic_year, level, is_active, created_at, updated_at
```

#### subjects
```sql
id, name, code, description, color, is_active, created_at, updated_at
```

#### exams
```sql
id, title, description, subject_id, class_id, created_by, duration_minutes, 
total_points, passing_score, start_date, end_date, status, shuffle_questions, 
show_results_immediately, max_attempts, created_at, updated_at, deleted_at
```

#### questions
```sql
id, exam_id, type, question_text, options (JSON), correct_answer, points, 
order, explanation, created_at, updated_at
```

#### exam_attempts
```sql
id, exam_id, student_id, started_at, completed_at, answers (JSON), score, 
percentage, status, time_spent_seconds, created_at, updated_at
```

### Relations

```
User (1) ─────→ (N) Classes [teacher]
User (N) ←─────→ (N) Classes [students via class_student]
User (1) ─────→ (N) Exams [creator]
User (1) ─────→ (N) ExamAttempts [student]

Class (1) ─────→ (N) Exams
Class (N) ←────→ (N) Subjects [via class_subject]

Subject (1) ───→ (N) Exams

Exam (1) ──────→ (N) Questions
Exam (1) ──────→ (N) ExamAttempts
```

## 🔄 Flux de données

### Authentification

```
1. User →  POST /login → AuthController
2. AuthController → Validation → Auth::attempt()
3. Session créée → Redirect /admin/dashboard
4. HandleInertiaRequests → Partage user + roles + permissions
5. Vue reçoit → $page.props.auth.user
```

### Navigation dans le backoffice

```
1. User clique → Link (Inertia)
2. Inertia → GET /admin/classes → ClassController
3. Controller → DB Query → return Inertia::render('Admin/Classes/Index', $data)
4. Inertia → JSON Response (pas de rechargement)
5. Vue → Update component avec nouvelles props
```

### Vérification des permissions

```
1. Route → middleware('permission:view classes')
2. Spatie vérifie → User->hasPermissionTo('view classes')
3. Si true → Controller
   Si false → 403 Forbidden
```

## 🎨 Frontend (Vue 3)

### Composition API

```vue
<script setup>
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    classes: Array,
});

const form = useForm({
    name: '',
    code: '',
});

const submit = () => {
    form.post(route('admin.classes.store'));
};
</script>
```

### Tailwind CSS

Toutes les classes utilisent Tailwind :
```vue
<div class="bg-white shadow rounded-lg p-6">
  <h2 class="text-xl font-bold text-gray-900">Title</h2>
</div>
```

Couleur primaire : `bg-orange-600`, `text-orange-600`, `border-orange-600`

### Responsive Design (Mobile-first)

```vue
<!-- Mobile par défaut, puis tablette, puis desktop -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
```

## 🔒 Sécurité

### Protection CSRF
Automatique via Laravel + Inertia

### Validation des données
```php
$validated = $request->validate([
    'email' => 'required|email|unique:users',
    'password' => 'required|min:8|confirmed',
]);
```

### Middleware
```php
Route::middleware(['auth', 'permission:view classes'])
```

### Hachage des mots de passe
```php
Hash::make($password)
```

### Protection XSS
Vue échappe automatiquement toutes les variables

## 📊 Performance

### Optimisations implémentées

1. **Eager Loading** dans les controllers
```php
$users = User::with('roles')->get();
```

2. **Vite** pour le build optimisé
- Code splitting automatique
- Minification
- Tree shaking

3. **Cache des permissions**
Spatie met en cache les permissions automatiquement

### Optimisations futures

- [ ] Redis pour les sessions
- [ ] Laravel Horizon pour les queues
- [ ] CDN pour les assets statiques
- [ ] Database indexing sur les colonnes fréquemment recherchées

## 🧪 Tests (À implémenter)

### Tests backend (Pest)
```php
test('admin can view classes', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    
    $this->actingAs($admin)
        ->get(route('admin.classes.index'))
        ->assertOk();
});
```

### Tests frontend (Vitest)
```js
import { mount } from '@vue/test-utils'
import Dashboard from '@/Pages/Admin/Dashboard.vue'

test('displays stats correctly', () => {
    const wrapper = mount(Dashboard, {
        props: {
            stats: { total_users: 10 }
        }
    })
    expect(wrapper.text()).toContain('10')
})
```

## 📈 Évolutivité

### Ajout d'un nouveau module

1. **Migration**
```bash
php artisan make:migration create_modules_table
```

2. **Modèle**
```bash
php artisan make:model Module
```

3. **Contrôleur**
```bash
php artisan make:controller Admin/ModuleController
```

4. **Routes** dans `web.php`
```php
Route::resource('modules', ModuleController::class);
```

5. **Pages Vue** dans `resources/js/Pages/Admin/Modules/`
```
Index.vue, Create.vue, Edit.vue
```

6. **Permissions** dans le seeder
```php
Permission::create(['name' => 'view modules']);
```

## 🌐 Internationalisation (Future)

Structure prête pour Laravel Localization :
```
resources/lang/
├── fr/
│   └── messages.php
└── en/
    └── messages.php
```

## 📝 Conventions de code

### PHP (PSR-12)
```php
namespace App\Http\Controllers\Admin;

class ExamController extends Controller
{
    public function index(): Response
    {
        // Code
    }
}
```

### Vue (Composition API)
```vue
<script setup>
// Imports
import { ref } from 'vue';

// Props
const props = defineProps({
    items: Array,
});

// State
const isOpen = ref(false);

// Methods
const toggle = () => {
    isOpen.value = !isOpen.value;
};
</script>
```

### Naming Conventions
- **Controllers**: `PascalCase` + `Controller` suffix
- **Models**: `PascalCase` singular
- **Routes**: `kebab-case`
- **Variables**: `camelCase`
- **Components Vue**: `PascalCase`

## 🚀 Déploiement

### Checklist production

- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `npm run build`
- [ ] `php artisan config:cache`
- [ ] `php artisan route:cache`
- [ ] `php artisan view:cache`
- [ ] Configurer les queues
- [ ] Configurer le scheduler
- [ ] HTTPS obligatoire
- [ ] Backups automatiques DB

## 📚 Ressources

- [Documentation Laravel](https://laravel.com/docs)
- [Documentation Inertia.js](https://inertiajs.com)
- [Documentation Vue 3](https://vuejs.org)
- [Documentation Spatie Permission](https://spatie.be/docs/laravel-permission)
- [Documentation Tailwind CSS](https://tailwindcss.com)

---

Cette architecture est conçue pour être **scalable**, **maintenable** et **sécurisée**.

