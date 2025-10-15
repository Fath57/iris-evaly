# Evaly - Plateforme d'examens en ligne

Plateforme moderne d'examens en ligne construite avec Laravel, Inertia.js et Vue 3.

## 🚀 Fonctionnalités

### Système d'authentification
- Connexion sécurisée avec Laravel
- Gestion des sessions
- Protection CSRF

### Gestion des rôles et permissions (Spatie Laravel Permission)
- **Super Admin** : Accès complet à toutes les fonctionnalités
- **Admin** : Gestion des utilisateurs, classes, matières et examens
- **Teacher** (Professeur) : Création d'examens, gestion des classes assignées, notation
- **Student** (Étudiant) : Participation aux examens, consultation des résultats
- **Assistant** : Aide à la création de questions et notation

### Gestion des classes
- Création et modification de classes
- Attribution de professeurs principaux
- Inscription d'étudiants
- Association de matières
- Niveaux : Primaire, Collège, Lycée, Université

### Gestion des matières
- Création de matières avec codes uniques
- Attribution de couleurs pour identification visuelle
- Association aux classes
- Attribution de professeurs par matière

### Gestion des examens
- Création d'examens par classe et matière
- Configuration de la durée
- Types de questions : QCM, Vrai/Faux, Réponse courte, Essay
- Mélange aléatoire des questions
- Limitation du nombre de tentatives
- Statuts : Brouillon, Publié, En cours, Terminé, Archivé

### Dashboard
- Vue d'ensemble des statistiques
- Nombre d'utilisateurs, classes, examens
- Activités récentes
- Examens en cours

## 📋 Prérequis

- PHP 8.2+
- Composer
- Node.js 20+
- MySQL/MariaDB
- Laravel 12

## 🔧 Installation

1. **Cloner le projet**
```bash
git clone <repository-url>
cd evaly
```

2. **Installer les dépendances PHP**
```bash
composer install
```

3. **Installer les dépendances Node.js**
```bash
npm install
```

4. **Configuration de l'environnement**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Configurer la base de données dans `.env`**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=evaly
DB_USERNAME=root
DB_PASSWORD=
```

6. **Exécuter les migrations**
```bash
php artisan migrate
```

7. **Exécuter les seeders**
```bash
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=AdminUserSeeder
php artisan db:seed --class=ClassSubjectSeeder
```

8. **Compiler les assets**
```bash
npm run build
# ou pour le développement
npm run dev
```

9. **Lancer le serveur**
```bash
php artisan serve
```

L'application sera accessible sur `http://localhost:8000`

## 👥 Comptes de test

### Super Admin
- **Email** : admin@evaly.com
- **Mot de passe** : password

### Admin
- **Email** : admin2@evaly.com
- **Mot de passe** : password

### Professeurs
- **Email** : prof.math@evaly.com
- **Mot de passe** : password
- **Email** : prof.sciences@evaly.com
- **Mot de passe** : password

### Assistant
- **Email** : assistant@evaly.com
- **Mot de passe** : password

### Étudiants
- **Email** : etudiant1@evaly.com à etudiant5@evaly.com
- **Mot de passe** : password

## 🗄️ Structure de la base de données

### Tables principales

- **users** : Utilisateurs du système
- **roles** : Rôles (super-admin, admin, teacher, student, assistant)
- **permissions** : Permissions granulaires
- **classes** : Classes/Groupes d'étudiants
- **subjects** : Matières d'enseignement
- **exams** : Examens
- **questions** : Questions des examens
- **exam_attempts** : Tentatives d'examen par les étudiants

### Tables de liaison

- **class_student** : Étudiants inscrits dans les classes
- **class_subject** : Matières enseignées dans les classes
- **model_has_roles** : Attribution des rôles aux utilisateurs
- **role_has_permissions** : Permissions associées aux rôles

## 🎨 Stack technique

### Backend
- **Laravel 12** : Framework PHP
- **Spatie Laravel Permission** : Gestion des rôles et permissions
- **Inertia.js** : Pont entre Laravel et Vue.js

### Frontend
- **Vue 3** : Framework JavaScript
- **Vite** : Build tool et dev server
- **Tailwind CSS** : Framework CSS utility-first
- **Ziggy** : Utilisation des routes Laravel dans Vue

## 📁 Structure du projet

```
evaly/
├── app/
│   ├── Http/Controllers/
│   │   ├── Auth/           # Authentification
│   │   └── Admin/          # Contrôleurs du backoffice
│   └── Models/             # Modèles Eloquent
├── database/
│   ├── migrations/         # Migrations de base de données
│   └── seeders/            # Seeders
├── resources/
│   ├── js/
│   │   ├── Pages/          # Pages Vue/Inertia
│   │   │   ├── Auth/       # Pages d'authentification
│   │   │   └── Admin/      # Pages du backoffice
│   │   ├── Layouts/        # Layouts Vue
│   │   └── Components/     # Composants réutilisables
│   ├── css/                # Styles CSS
│   └── views/              # Vue Blade (template root)
└── routes/
    └── web.php             # Routes web
```

## 🔐 Permissions disponibles

### Gestion des utilisateurs
- view users, create users, edit users, delete users

### Gestion des rôles
- view roles, create roles, edit roles, delete roles

### Gestion des classes
- view classes, create classes, edit classes, delete classes, manage class students

### Gestion des matières
- view subjects, create subjects, edit subjects, delete subjects

### Gestion des examens
- view exams, create exams, edit exams, delete exams, publish exams, grade exams

### Gestion des questions
- view questions, create questions, edit questions, delete questions

### Étudiants
- take exams, view own results, view class materials

### Autres
- view dashboard, view analytics, view settings, edit settings

## 🎯 Couleur primaire

La couleur primaire du projet est **#FF5E0E** (orange vif), utilisée dans toute l'interface pour maintenir une cohérence visuelle [[memory:8828545]].

## 📱 Design

Le projet suit une approche **mobile-first** pour garantir une expérience optimale sur tous les appareils [[memory:8828545]].

## 🔄 Développement

### Lancer en mode développement
```bash
npm run dev
php artisan serve
```

### Build pour la production
```bash
npm run build
```

### Réinitialiser la base de données
```bash
php artisan migrate:fresh --seed
```

## 🤝 Contribution

Ce projet utilise uniquement **Tailwind CSS** pour le styling, sans CSS personnalisé [[memory:8830459]].

## 📝 License

MIT License

## 📧 Support

Pour toute question ou problème, veuillez contacter l'équipe de développement.
