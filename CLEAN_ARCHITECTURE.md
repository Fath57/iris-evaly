# Architecture Clean - Evaly

## 🏗️ Pattern Repository/Service/Controller

L'application suit maintenant une architecture propre et maintenable avec séparation des responsabilités.

## 📁 Structure des dossiers

```
app/
├── Interfaces/              # Contrats/Interfaces
│   ├── RepositoryInterface.php
│   ├── UserRepositoryInterface.php
│   └── ClassRepositoryInterface.php
│
├── Repositories/            # Couche d'accès aux données
│   ├── BaseRepository.php
│   ├── UserRepository.php
│   └── ClassRepository.php
│
├── Services/                # Logique métier
│   ├── UserService.php
│   ├── ClassService.php
│   └── InvitationService.php
│
├── Http/Controllers/        # Contrôleurs légers
│   ├── Auth/
│   │   ├── AuthController.php
│   │   ├── InvitationController.php
│   │   └── ProfileSetupController.php
│   └── Admin/
│       ├── DashboardController.php
│       ├── UserController.php
│       ├── ClassController.php
│       └── RoleController.php
│
└── Providers/
    └── RepositoryServiceProvider.php  # Injection de dépendances
```

## 🔄 Flux de données

```
Vue Component
    ↓ (Inertia Request)
Controller (léger, validation)
    ↓ (appelle)
Service (logique métier, logging)
    ↓ (appelle)
Repository (accès BDD)
    ↓ (utilise)
Model (Eloquent)
    ↓
Database
```

## 📦 Composants Vue réutilisables

```
resources/js/Components/
├── Button.vue              # Bouton avec variants (primary, secondary, danger, success)
├── Modal.vue               # Modal réutilisable avec transitions
├── ConfirmModal.vue        # Modal de confirmation pour suppressions
├── FormInput.vue           # Input avec label, erreurs, helper
├── FormSelect.vue          # Select avec label, erreurs, helper
├── Badge.vue               # Badge de statut avec variants
└── DataTable.vue           # Table avec recherche, tri, pagination serveur
```

## 🎨 Composants DataTable

### Utilisation

```vue
<DataTable
    :data="users"
    :columns="columns"
    route-name="admin.users.index"
    search-placeholder="Rechercher..."
>
    <!-- Custom cell rendering -->
    <template #cell-name="{ row, value }">
        <strong>{{ value }}</strong>
    </template>

    <!-- Actions column -->
    <template #actions="{ row }">
        <Button @click="edit(row)">Modifier</Button>
        <Button variant="danger" @click="delete(row)">Supprimer</Button>
    </template>
</DataTable>
```

### Fonctionnalités

✅ **Recherche côté serveur** avec debounce (500ms)  
✅ **Tri des colonnes** (cliquer sur l'en-tête)  
✅ **Pagination** avec navigation intelligente  
✅ **Slots personnalisables** pour chaque cellule  
✅ **Responsive** et optimisé mobile  
✅ **Loading states** pendant les requêtes  

## 🧩 Composants Modal

### Modal basique

```vue
<Modal :show="showModal" title="Mon titre" @close="showModal = false">
    <p>Contenu du modal</p>
    
    <template #footer>
        <Button @click="save">Enregistrer</Button>
    </template>
</Modal>
```

### Modal de confirmation

```vue
<ConfirmModal
    :show="showDelete"
    title="Supprimer"
    message="Êtes-vous sûr ?"
    type="danger"
    @close="showDelete = false"
    @confirm="handleDelete"
/>
```

## 🎯 Repository Pattern

### Interface

```php
interface RepositoryInterface {
    public function all(): Collection;
    public function paginate(int $perPage, array $filters): LengthAwarePaginator;
    public function find(int $id): ?Model;
    public function create(array $data): Model;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function search(string $query, array $columns): Collection;
}
```

### Implémentation

```php
class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email) { ... }
    public function getUsersWithRoles(): Collection { ... }
}
```

## 🔧 Service Pattern

### Responsabilités

- ✅ Logique métier complexe
- ✅ Orchestration de plusieurs repositories
- ✅ Logging des opérations [[memory:3175861]]
- ✅ Gestion des transactions
- ✅ Validation métier

### Exemple

```php
class UserService
{
    protected UserRepository $userRepository;

    public function createUser(array $data)
    {
        try {
            $data['password'] = Hash::make($data['password']);
            $user = $this->userRepository->create($data);
            
            if (isset($data['roles'])) {
                $user->syncRoles($data['roles']);
            }

            Log::info('User created', ['user_id' => $user->id]);
            
            return $user;
        } catch (\Exception $e) {
            Log::error('Error creating user', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
```

## 🎮 Controller Pattern

### Responsabilités

- ✅ Validation des requêtes
- ✅ Appel des services
- ✅ Retour des réponses Inertia
- ✅ Gestion des redirections

### Exemple

```php
class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([...]);
        
        $this->userService->createUser($validated);
        
        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur créé');
    }
}
```

## 🔌 Injection de dépendances

### RepositoryServiceProvider

```php
class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ClassRepositoryInterface::class, ClassRepository::class);
    }
}
```

Enregistré dans `bootstrap/providers.php`

## 📊 Pagination côté serveur

### Controller

```php
public function index(Request $request)
{
    $filters = [
        'search' => $request->input('search'),
        'role' => $request->input('role'),
        'sort_by' => $request->input('sort_by', 'created_at'),
        'sort_order' => $request->input('sort_order', 'desc'),
    ];

    $users = $this->userService->getUsersPaginated(15, $filters);
    
    return Inertia::render('Admin/Users/Index', [
        'users' => $users,
        'filters' => $filters,
    ]);
}
```

### Repository

```php
public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
{
    $query = $this->model->with('roles');

    if (!empty($filters['search'])) {
        $query->where(function ($q) use ($filters) {
            $q->where('name', 'like', "%{$filters['search']}%")
              ->orWhere('email', 'like', "%{$filters['search']}%");
        });
    }

    return $query->paginate($perPage);
}
```

## 🎨 Layouts dynamiques

### Menu horizontal/vertical

L'utilisateur peut choisir entre deux layouts :

**AppLayout.vue** (wrapper dynamique)
```vue
<component :is="layoutComponent">
    <slot />
</component>

const layoutComponent = computed(() => {
    const menuLayout = page.props.auth.user?.menu_layout || 'horizontal';
    return menuLayout === 'vertical' ? AdminLayoutVertical : AdminLayout;
});
```

**Changement de layout**
```js
const toggleMenuLayout = () => {
    router.patch(route('profile.update-preferences'), {
        menu_layout: newLayout
    });
};
```

## 🔐 Système d'invitation

### Flux d'invitation

```
1. Admin → Envoie invitation (email + rôle)
2. UserInvitation créée (token unique, expire 7 jours)
3. Lien envoyé : /invitation/{token}
4. Utilisateur clique → Page AcceptInvitation
5. Renseigne : prénom, nom, password
6. Compte créé + rôle assigné
7. Si professeur → Sélection des classes
8. Redirection → Dashboard
```

### Tables

**user_invitations**
```sql
id, email, token, role, invited_by, expires_at, accepted_at, created_at, updated_at
```

**teacher_classes** (pivot)
```sql
id, teacher_id, class_id, subject_id, is_main_teacher, created_at, updated_at
```

## 🎯 Avantages de cette architecture

### ✅ Séparation des responsabilités
- **Repository** : Accès aux données uniquement
- **Service** : Logique métier et orchestration
- **Controller** : Validation et réponses HTTP

### ✅ Testabilité
- Chaque couche peut être testée indépendamment
- Injection de dépendances facilite les mocks

### ✅ Réutilisabilité
- Repositories réutilisables dans plusieurs services
- Services réutilisables dans plusieurs controllers
- Composants Vue réutilisables partout

### ✅ Maintenabilité
- Code organisé et prévisible
- Facile à comprendre et modifier
- Pas d'imbrication excessive

### ✅ Scalabilité
- Facile d'ajouter de nouveaux modules
- Pattern cohérent dans toute l'application

## 📝 Conventions de code

### Naming

**Repositories**
```php
UserRepository
ClassRepository
ExamRepository
```

**Services**
```php
UserService
ClassService
InvitationService
```

**Interfaces**
```php
UserRepositoryInterface
ClassRepositoryInterface
```

### Structure des méthodes

**Repository**
```php
// CRUD basique
all(), find($id), create($data), update($id, $data), delete($id)

// Recherche et filtrage
search($query, $columns), paginate($perPage, $filters)

// Méthodes spécifiques
findByEmail($email), getActiveClasses()
```

**Service**
```php
// Opérations métier
createUser($data), updateUser($id, $data), deleteUser($id)

// Orchestration
manageStudents($classId, $studentIds)

// Queries complexes
getUsersWithRoles(), getClassWithRelations($id)
```

## 🚀 Ajout d'un nouveau module

### 1. Créer l'interface

```php
// app/Interfaces/ExamRepositoryInterface.php
interface ExamRepositoryInterface extends RepositoryInterface
{
    public function getExamsByClass(int $classId): Collection;
}
```

### 2. Créer le repository

```php
// app/Repositories/ExamRepository.php
class ExamRepository extends BaseRepository implements ExamRepositoryInterface
{
    public function __construct(Exam $model)
    {
        parent::__construct($model);
    }
    
    public function getExamsByClass(int $classId): Collection
    {
        return $this->model->where('class_id', $classId)->get();
    }
}
```

### 3. Créer le service

```php
// app/Services/ExamService.php
class ExamService
{
    protected ExamRepository $examRepository;

    public function __construct(ExamRepository $examRepository)
    {
        $this->examRepository = $examRepository;
    }

    public function createExam(array $data)
    {
        try {
            $exam = $this->examRepository->create($data);
            Log::info('Exam created', ['exam_id' => $exam->id]);
            return $exam;
        } catch (\Exception $e) {
            Log::error('Error creating exam', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
```

### 4. Créer le controller

```php
// app/Http/Controllers/Admin/ExamController.php
class ExamController extends Controller
{
    protected ExamService $examService;

    public function __construct(ExamService $examService)
    {
        $this->examService = $examService;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([...]);
        $this->examService->createExam($validated);
        return redirect()->route('admin.exams.index');
    }
}
```

### 5. Enregistrer dans le provider

```php
// app/Providers/RepositoryServiceProvider.php
$this->app->bind(ExamRepositoryInterface::class, ExamRepository::class);
```

### 6. Créer la page Vue

```vue
<!-- resources/js/Pages/Admin/Exams/Index.vue -->
<DataTable
    :data="exams"
    :columns="columns"
    route-name="admin.exams.index"
>
    <template #actions="{ row }">
        <Button @click="edit(row)">Modifier</Button>
    </template>
</DataTable>
```

## 🎨 Composants réutilisables

### Button

```vue
<Button variant="primary" size="md" @click="handleClick">
    Cliquer ici
</Button>

<!-- Variants: primary, secondary, danger, success -->
<!-- Sizes: sm, md, lg -->
```

### FormInput

```vue
<FormInput
    v-model="form.email"
    label="Email"
    type="email"
    required
    :error="form.errors.email"
    helper="Entrez votre adresse email"
/>
```

### FormSelect

```vue
<FormSelect
    v-model="form.role"
    label="Rôle"
    :options="roleOptions"
    required
    :error="form.errors.role"
/>
```

### Badge

```vue
<Badge variant="success">Actif</Badge>
<Badge variant="danger">Inactif</Badge>

<!-- Variants: default, primary, success, danger, warning, info -->
```

### Modal

```vue
<Modal :show="show" title="Titre" @close="show = false">
    <p>Contenu</p>
    
    <template #footer>
        <Button @click="save">Enregistrer</Button>
    </template>
</Modal>
```

### ConfirmModal

```vue
<ConfirmModal
    :show="showDelete"
    title="Supprimer"
    message="Confirmer la suppression ?"
    confirm-text="Supprimer"
    type="danger"
    @close="showDelete = false"
    @confirm="handleDelete"
/>
```

### DataTable

```vue
<DataTable
    :data="paginatedData"
    :columns="[
        { key: 'name', label: 'Nom', sortable: true },
        { key: 'email', label: 'Email', sortable: true },
    ]"
    route-name="admin.users.index"
>
    <template #actions="{ row }">
        <Button size="sm" @click="edit(row)">Modifier</Button>
    </template>
</DataTable>
```

## 🔄 Layouts dynamiques

### Horizontal (par défaut)

Navigation en haut avec liens horizontaux

### Vertical

Sidebar gauche avec navigation verticale

### Changement

L'utilisateur peut basculer via le menu utilisateur. La préférence est sauvegardée en BDD et appliquée automatiquement.

## 📊 Pagination serveur

### Paramètres URL

```
?search=john
&sort_by=created_at
&sort_order=desc
&page=2
```

### Controller

```php
$filters = [
    'search' => $request->input('search'),
    'sort_by' => $request->input('sort_by', 'created_at'),
    'sort_order' => $request->input('sort_order', 'desc'),
];

$data = $service->getPaginated(15, $filters);
```

### Repository

```php
public function paginate(int $perPage, array $filters): LengthAwarePaginator
{
    $query = $this->model->query();

    if (!empty($filters['search'])) {
        $query->where('name', 'like', "%{$filters['search']}%");
    }

    $query->orderBy($filters['sort_by'], $filters['sort_order']);

    return $query->paginate($perPage);
}
```

## 🎓 Système d'invitation

### Workflow

1. **Admin invite** : Email + Rôle
2. **Token généré** : Unique, expire 7 jours
3. **Utilisateur accepte** : Prénom, Nom, Password
4. **Compte créé** : Rôle assigné automatiquement
5. **Si professeur** : Sélection des classes d'intervention
6. **Redirection** : Dashboard

### Avantages

- ✅ Pas de création manuelle de password par l'admin
- ✅ Utilisateur choisit son propre password
- ✅ Professeurs sélectionnent leurs classes
- ✅ Étudiants ne sont pas invités (gestion différente)
- ✅ Tokens sécurisés avec expiration

## 🏫 Gestion multi-classes pour enseignants

### Table pivot : teacher_classes

```sql
teacher_id, class_id, subject_id, is_main_teacher
```

### Relations

Un professeur peut :
- ✅ Intervenir dans plusieurs classes
- ✅ Enseigner différentes matières par classe
- ✅ Être professeur principal d'une ou plusieurs classes

### Utilisation

```php
// Get teacher's classes
$teacher->teachingClasses;

// Get main classes
$teacher->mainClasses();

// Get class teachers
$class->teachers;

// Get main teacher
$class->mainTeacher();
```

## 📝 Logging

Tous les logs utilisent la facade Log de Laravel [[memory:3175861]] :

```php
Log::info('User created', ['user_id' => $user->id]);
Log::error('Error creating user', ['error' => $e->getMessage()]);
```

## 🎨 Design System

### Couleurs

**Primaire** : `#FF5E0E` (orange) [[memory:8828545]]
```
bg-orange-600, text-orange-600, border-orange-600
hover:bg-orange-700, focus:ring-orange-500
```

### Mobile-first [[memory:8828545]]

```vue
<!-- Mobile par défaut -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
```

### Tailwind uniquement [[memory:8830459]]

Pas de CSS personnalisé, uniquement classes Tailwind.

## ✨ Bonnes pratiques

### ✅ DRY (Don't Repeat Yourself)
- Composants réutilisables
- BaseRepository pour logique commune
- Services pour logique métier partagée

### ✅ SOLID Principles
- Single Responsibility : Chaque classe a une responsabilité unique
- Open/Closed : Extensible via interfaces
- Dependency Inversion : Dépendances via interfaces

### ✅ Clean Code
- Noms explicites
- Méthodes courtes et focalisées
- Commentaires uniquement si nécessaire
- Logging systématique des opérations importantes

## 🧪 Tests (À implémenter)

### Repository Tests

```php
test('user repository can create user', function () {
    $repo = new UserRepository(new User);
    $user = $repo->create(['name' => 'Test', 'email' => 'test@test.com']);
    expect($user)->toBeInstanceOf(User::class);
});
```

### Service Tests

```php
test('user service logs user creation', function () {
    Log::shouldReceive('info')->once()->with('User created', Mockery::any());
    $service->createUser([...]);
});
```

### Component Tests (Vitest)

```js
test('Button renders correctly', () => {
    const wrapper = mount(Button, {
        props: { variant: 'primary' }
    });
    expect(wrapper.classes()).toContain('bg-orange-600');
});
```

## 🎊 Résultat

Architecture **propre**, **maintenable**, **scalable** et **testable** ! 🚀

