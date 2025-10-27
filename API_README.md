# 🎓 APIs Étudiants - Evaly

## ✅ Résumé des APIs Disponibles

Toutes les APIs demandées ont été **implémentées avec succès** et sont **testées** !

| # | API | Endpoint | Méthode | Auth | Statut |
|---|-----|----------|---------|------|--------|
| 1 | **Définir Mot de Passe Initial** | `/api/students/setup-password` | POST | ❌ Public | ✅ Prêt |
| 2 | **Récupérer Profil** | `/api/students/profile` | GET | ✅ Bearer | ✅ Prêt |
| 3 | **Récupérer Examens** | `/api/students/exams` | GET | ✅ Bearer | ✅ **Nouveau** |
| 4 | **Modifier Mot de Passe** | `/api/students/change-password` | POST | ✅ Bearer | ✅ Prêt |
| 5 | **Connexion** | `/api/students/login` | POST | ❌ Public | ✅ Prêt |
| 6 | **Mettre à Jour Profil** | `/api/students/profile` | PUT | ✅ Bearer | ✅ Prêt |
| 7 | **Déconnexion** | `/api/students/logout` | POST | ✅ Bearer | ✅ Prêt |

---

## 🎯 Fonctionnalités Clés

### 1. **Définir le Mot de Passe Initial** ✨
- Endpoint: `POST /api/students/setup-password`
- **Nouvelle fonctionnalité** : Permet à un étudiant de définir son mot de passe pour la première fois en utilisant son email
- Retourne automatiquement un token d'authentification
- ✅ **Déjà en place**

### 2. **Récupérer les Informations de l'Étudiant Connecté** 👤
- Endpoint: `GET /api/students/profile`
- Nécessite authentification Bearer
- Retourne : profil complet + classes assignées
- ✅ **Déjà en place**

### 3. **Récupérer les Examens** 📚 **NOUVEAU**
- Endpoint: `GET /api/students/exams`
- Nécessite authentification Bearer
- **Catégorise automatiquement les examens par statut :**
  - **upcoming** : Examens à venir (date future)
  - **ongoing** : Examens en cours (entre start_date et end_date)
  - **past** : Examens passés (end_date dépassée)
- Pour chaque examen, fournit :
  - Informations de base (titre, description, matière, classe)
  - Durée, points totaux, score de passage
  - Dates de début et fin
  - Nombre de questions
  - **Tentatives** : nombre max, nombre effectué, possibilité de tenter
  - **Meilleur score** obtenu
  - **Dernière tentative** avec détails
- ✅ **Nouvellement créé**

### 4. **Modifier le Mot de Passe** 🔐
- Endpoint: `POST /api/students/change-password`
- Nécessite authentification Bearer
- Valide le mot de passe actuel avant modification
- ✅ **Déjà en place**

---

## 📖 Documentation Complète

### **Documentation Interactive Swagger**
```
http://localhost/api/documentation
```

### **Documentation Détaillée Markdown**
Voir le fichier: `API_DOCUMENTATION.md`

---

## 🧪 Tests

### **Fichier de Test**
`tests/Feature/StudentApiTest.php`

### **Couverture des Tests**
- ✅ **15 tests** couvrant toutes les APIs
- ✅ Tests d'authentification (login, setup password, logout)
- ✅ Tests de profil (get, update)
- ✅ Tests de gestion du mot de passe (change password)
- ✅ Tests des examens (récupération par catégorie)
- ✅ Tests des cas d'erreur (401, 422)

### **Exécuter les Tests**
```bash
# Tous les tests API étudiants
php artisan test --filter StudentApiTest

# Test spécifique
php artisan test --filter "student can get their exams"
```

---

## 🚀 Utilisation Rapide

### **1. Définir le Mot de Passe (Première Connexion)**
```bash
curl -X POST http://localhost/api/students/setup-password \
  -H "Content-Type: application/json" \
  -d '{
    "email": "etudiant@exemple.com",
    "password": "motdepasse123",
    "password_confirmation": "motdepasse123"
  }'
```

**Réponse:**
```json
{
  "success": true,
  "data": {
    "student": {...},
    "token": "1|abcd...xyz"
  }
}
```

### **2. Connexion**
```bash
curl -X POST http://localhost/api/students/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "etudiant@exemple.com",
    "password": "motdepasse123"
  }'
```

**Réponse:**
```json
{
  "success": true,
  "data": {
    "student": {...},
    "token": "2|efgh...xyz"
  }
}
```

### **3. Récupérer le Profil**
```bash
curl -X GET http://localhost/api/students/profile \
  -H "Authorization: Bearer {token}"
```

### **4. Récupérer les Examens** 🆕
```bash
curl -X GET http://localhost/api/students/exams \
  -H "Authorization: Bearer {token}"
```

**Réponse:**
```json
{
  "success": true,
  "data": {
    "upcoming": [
      {
        "id": 1,
        "title": "Examen de Mathématiques",
        "start_date": "2025-11-05T10:00:00Z",
        "duration": 120,
        "questions_count": 20,
        "can_attempt": true,
        "attempt_count": 0
      }
    ],
    "ongoing": [...],
    "past": [...]
  }
}
```

### **5. Modifier le Mot de Passe**
```bash
curl -X POST http://localhost/api/students/change-password \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "current_password": "ancienmdp",
    "password": "nouveaumdp",
    "password_confirmation": "nouveaumdp"
  }'
```

### **6. Déconnexion**
```bash
curl -X POST http://localhost/api/students/logout \
  -H "Authorization: Bearer {token}"
```

---

## 🔧 Détails Techniques

### **Stack Utilisé**
- **Laravel 12** - Framework backend
- **Laravel Sanctum** - Gestion des tokens API
- **Swagger/OpenAPI** - Documentation interactive
- **Pest PHP** - Framework de tests

### **Architecture**
```
├── Controllers
│   └── Api/StudentAuthController.php    (7 endpoints)
├── Services
│   ├── StudentAuthService.php           (Auth & Profile)
│   └── ExamService.php                  (Examens avec getStudentExams)
├── Repositories
│   └── StudentRepository.php
└── Tests
    └── Feature/StudentApiTest.php       (15 tests)
```

### **Sécurité**
- ✅ Authentification via tokens Bearer (Laravel Sanctum)
- ✅ Hashage des mots de passe (bcrypt)
- ✅ Validation des données entrantes
- ✅ Protection CSRF
- ✅ Vérification des permissions

---

## 📊 Exemple de Réponse: Récupération des Examens

```json
{
  "success": true,
  "data": {
    "upcoming": [
      {
        "id": 1,
        "title": "Examen de Mathématiques",
        "description": "Équations du second degré",
        "subject": {
          "id": 1,
          "name": "Mathématiques"
        },
        "class": {
          "id": 1,
          "name": "Terminale S1"
        },
        "duration": 120,
        "total_points": 20,
        "passing_score": 10,
        "start_date": "2025-11-05T10:00:00.000000Z",
        "end_date": "2025-11-05T12:00:00.000000Z",
        "questions_count": 20,
        "max_attempts": 2,
        "attempt_count": 0,
        "can_attempt": true,
        "best_score": null,
        "last_attempt": null
      }
    ],
    "ongoing": [
      {
        "id": 2,
        "title": "Examen de Physique",
        "start_date": "2025-10-27T09:00:00.000000Z",
        "end_date": "2025-10-27T18:00:00.000000Z",
        "can_attempt": true,
        "attempt_count": 0,
        "...": "..."
      }
    ],
    "past": [
      {
        "id": 3,
        "title": "Examen de Chimie",
        "start_date": "2025-10-20T14:00:00.000000Z",
        "end_date": "2025-10-20T16:00:00.000000Z",
        "attempt_count": 1,
        "can_attempt": true,
        "best_score": 15.5,
        "last_attempt": {
          "id": 1,
          "score": 15.5,
          "completed_at": "2025-10-20T15:30:00.000000Z",
          "status": "completed"
        }
      }
    ]
  },
  "message": "Examens récupérés avec succès."
}
```

---

## ✨ Points Forts

1. **✅ Complet** - Toutes les APIs demandées sont implémentées
2. **✅ Documenté** - Documentation Swagger + Markdown détaillée
3. **✅ Testé** - 15 tests automatisés avec Pest PHP
4. **✅ Sécurisé** - Authentification, validation, hashage
5. **✅ Structuré** - Architecture Service/Repository
6. **✅ Intelligent** - Catégorisation automatique des examens par statut
7. **✅ Pratique** - Retourne des informations utiles (can_attempt, best_score, etc.)

---

## 🎯 État Final

| Fonctionnalité | Statut |
|----------------|--------|
| ✅ API Définir Mot de Passe | **Prêt** |
| ✅ API Récupérer Profil | **Prêt** |
| ✅ API Récupérer Examens | **Prêt** |
| ✅ API Modifier Mot de Passe | **Prêt** |
| ✅ Documentation Swagger | **Générée** |
| ✅ Tests Automatisés | **Créés** |
| ✅ Guide d'Utilisation | **Fourni** |

---

## 📞 Support

**Documentation Interactive:** http://localhost/api/documentation
**Documentation Complète:** API_DOCUMENTATION.md
**Tests:** tests/Feature/StudentApiTest.php

---

**Version:** 1.0.0
**Date:** 27 Octobre 2025
**Auteur:** Claude Code - Anthropic
