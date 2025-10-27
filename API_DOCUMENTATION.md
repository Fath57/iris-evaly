# 📚 Documentation API Étudiants - Evaly

## 🌐 URL de Base
```
http://localhost/api
```

## 📖 Documentation Swagger
Accédez à la documentation interactive Swagger à l'adresse :
```
http://localhost/api/documentation
```

---

## 🔐 Authentification

### 1. **Définir le Mot de Passe Initial** ✨

**Endpoint:** `POST /api/students/setup-password`

**Description:** Permet à un étudiant de définir son mot de passe pour la première fois en utilisant son email.

**Corps de la requête:**
```json
{
  "email": "jean.dupont@example.com",
  "password": "motdepasse123",
  "password_confirmation": "motdepasse123"
}
```

**Réponse Succès (200):**
```json
{
  "success": true,
  "data": {
    "student": {
      "id": 1,
      "first_name": "Jean",
      "last_name": "Dupont",
      "email": "jean.dupont@example.com",
      "student_number": "STU202500001"
    },
    "token": "1|abcd...xyz"
  },
  "message": "Mot de passe défini avec succès."
}
```

**Réponse Erreur (422):**
```json
{
  "success": false,
  "errors": {
    "password": ["Le mot de passe doit contenir au moins 8 caractères."]
  }
}
```

---

### 2. **Connexion Étudiant** 🔑

**Endpoint:** `POST /api/students/login`

**Description:** Authentifie un étudiant avec email et mot de passe.

**Corps de la requête:**
```json
{
  "email": "jean.dupont@example.com",
  "password": "motdepasse123"
}
```

**Réponse Succès (200):**
```json
{
  "success": true,
  "data": {
    "student": {
      "id": 1,
      "first_name": "Jean",
      "last_name": "Dupont",
      "email": "jean.dupont@example.com",
      "student_number": "STU202500001",
      "phone": "+33123456789",
      "is_active": true,
      "classes": [
        {
          "id": 1,
          "name": "Terminale S1"
        }
      ]
    },
    "token": "2|efgh...xyz"
  },
  "message": "Connexion réussie."
}
```

**Réponse Erreur - Pas de Mot de Passe (401):**
```json
{
  "success": false,
  "message": "Veuillez définir votre mot de passe avant de vous connecter.",
  "requires_password_setup": true,
  "student_id": 1
}
```

**Réponse Erreur - Identifiants Invalides (401):**
```json
{
  "success": false,
  "message": "Email ou mot de passe incorrect."
}
```

---

## 👤 Profil Étudiant

> **Note:** Toutes ces routes nécessitent un token d'authentification Bearer.
>
> Header requis: `Authorization: Bearer {token}`

### 3. **Récupérer le Profil** 📋

**Endpoint:** `GET /api/students/profile`

**Description:** Récupère les informations du profil de l'étudiant connecté.

**Headers:**
```
Authorization: Bearer {token}
```

**Réponse Succès (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "first_name": "Jean",
    "last_name": "Dupont",
    "email": "jean.dupont@example.com",
    "student_number": "STU202500001",
    "phone": "+33123456789",
    "address": "123 Rue de la Paix, Paris",
    "date_of_birth": "2000-01-15",
    "parent_contact": "parent@example.com",
    "is_active": true,
    "profile_completed": true,
    "enrollment_date": "2025-09-01T00:00:00.000000Z",
    "classes": [
      {
        "id": 1,
        "name": "Terminale S1",
        "description": "Classe de Terminale Scientifique"
      }
    ]
  },
  "message": "Profil récupéré avec succès."
}
```

---

### 4. **Mettre à Jour le Profil** ✏️

**Endpoint:** `PUT /api/students/profile`

**Description:** Met à jour les informations du profil de l'étudiant connecté.

**Headers:**
```
Authorization: Bearer {token}
```

**Corps de la requête:**
```json
{
  "first_name": "Jean",
  "last_name": "Dupont",
  "phone": "+33123456789",
  "address": "456 Avenue des Champs-Élysées, Paris",
  "date_of_birth": "2000-01-15"
}
```

**Réponse Succès (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "first_name": "Jean",
    "last_name": "Dupont",
    "email": "jean.dupont@example.com",
    "phone": "+33123456789",
    "address": "456 Avenue des Champs-Élysées, Paris"
  },
  "message": "Profil mis à jour avec succès."
}
```

---

## 🔒 Gestion du Mot de Passe

### 5. **Modifier le Mot de Passe** 🔐

**Endpoint:** `POST /api/students/change-password`

**Description:** Permet à un étudiant connecté de changer son mot de passe.

**Headers:**
```
Authorization: Bearer {token}
```

**Corps de la requête:**
```json
{
  "current_password": "ancienmdp123",
  "password": "nouveaumdp456",
  "password_confirmation": "nouveaumdp456"
}
```

**Réponse Succès (200):**
```json
{
  "success": true,
  "message": "Mot de passe modifié avec succès."
}
```

**Réponse Erreur - Mot de Passe Actuel Incorrect (422):**
```json
{
  "success": false,
  "errors": {
    "current_password": ["Le mot de passe actuel est incorrect."]
  }
}
```

**Réponse Erreur - Confirmation Non Correspondante (422):**
```json
{
  "success": false,
  "errors": {
    "password": ["Les mots de passe ne correspondent pas."]
  }
}
```

---

## 📝 Examens

### 6. **Récupérer les Examens** 📚

**Endpoint:** `GET /api/students/exams`

**Description:** Récupère les examens de l'étudiant connecté, catégorisés par statut (à venir, en cours, passés).

**Headers:**
```
Authorization: Bearer {token}
```

**Réponse Succès (200):**
```json
{
  "success": true,
  "data": {
    "upcoming": [
      {
        "id": 1,
        "title": "Examen de Mathématiques",
        "description": "Examen sur les équations du second degré",
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
        "description": "Examen sur la mécanique",
        "subject": {
          "id": 2,
          "name": "Physique"
        },
        "class": {
          "id": 1,
          "name": "Terminale S1"
        },
        "duration": 90,
        "total_points": 20,
        "passing_score": 10,
        "start_date": "2025-10-27T09:00:00.000000Z",
        "end_date": "2025-10-27T18:00:00.000000Z",
        "questions_count": 15,
        "max_attempts": 1,
        "attempt_count": 0,
        "can_attempt": true,
        "best_score": null,
        "last_attempt": null
      }
    ],
    "past": [
      {
        "id": 3,
        "title": "Examen de Chimie",
        "description": "Examen sur les réactions chimiques",
        "subject": {
          "id": 3,
          "name": "Chimie"
        },
        "class": {
          "id": 1,
          "name": "Terminale S1"
        },
        "duration": 60,
        "total_points": 20,
        "passing_score": 10,
        "start_date": "2025-10-20T14:00:00.000000Z",
        "end_date": "2025-10-20T16:00:00.000000Z",
        "questions_count": 10,
        "max_attempts": 2,
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

**Description des Catégories:**
- **upcoming:** Examens à venir (date de début future)
- **ongoing:** Examens en cours (entre date de début et date de fin)
- **past:** Examens passés (date de fin dépassée)

**Détails des Champs:**
- `can_attempt`: Indique si l'étudiant peut encore tenter l'examen (basé sur max_attempts)
- `attempt_count`: Nombre de tentatives déjà effectuées
- `best_score`: Meilleur score obtenu (null si aucune tentative)
- `last_attempt`: Détails de la dernière tentative (null si aucune tentative)

---

## 🚪 Déconnexion

### 7. **Déconnexion** 👋

**Endpoint:** `POST /api/students/logout`

**Description:** Déconnecte l'étudiant et révoque son token d'authentification.

**Headers:**
```
Authorization: Bearer {token}
```

**Réponse Succès (200):**
```json
{
  "success": true,
  "message": "Déconnexion réussie."
}
```

---

## 🛠️ Codes de Statut HTTP

| Code | Signification |
|------|--------------|
| **200** | Succès - Requête traitée avec succès |
| **201** | Créé - Ressource créée avec succès |
| **401** | Non autorisé - Token invalide ou absent |
| **422** | Erreur de validation - Données invalides |
| **500** | Erreur serveur - Erreur interne |

---

## 💡 Exemples d'Utilisation

### Exemple avec cURL

**1. Définir le mot de passe initial:**
```bash
curl -X POST http://localhost/api/students/setup-password \
  -H "Content-Type: application/json" \
  -d '{
    "email": "jean.dupont@example.com",
    "password": "motdepasse123",
    "password_confirmation": "motdepasse123"
  }'
```

**2. Se connecter:**
```bash
curl -X POST http://localhost/api/students/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "jean.dupont@example.com",
    "password": "motdepasse123"
  }'
```

**3. Récupérer le profil:**
```bash
curl -X GET http://localhost/api/students/profile \
  -H "Authorization: Bearer {votre-token}"
```

**4. Récupérer les examens:**
```bash
curl -X GET http://localhost/api/students/exams \
  -H "Authorization: Bearer {votre-token}"
```

**5. Changer le mot de passe:**
```bash
curl -X POST http://localhost/api/students/change-password \
  -H "Authorization: Bearer {votre-token}" \
  -H "Content-Type: application/json" \
  -d '{
    "current_password": "ancienmdp123",
    "password": "nouveaumdp456",
    "password_confirmation": "nouveaumdp456"
  }'
```

**6. Se déconnecter:**
```bash
curl -X POST http://localhost/api/students/logout \
  -H "Authorization: Bearer {votre-token}"
```

---

### Exemple avec JavaScript (Fetch API)

```javascript
// 1. Connexion
async function login(email, password) {
  const response = await fetch('http://localhost/api/students/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ email, password }),
  });

  const data = await response.json();

  if (data.success) {
    // Stocker le token
    localStorage.setItem('token', data.data.token);
    return data.data;
  } else {
    throw new Error(data.message);
  }
}

// 2. Récupérer les examens
async function getExams() {
  const token = localStorage.getItem('token');

  const response = await fetch('http://localhost/api/students/exams', {
    method: 'GET',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
  });

  const data = await response.json();
  return data.data;
}

// 3. Mettre à jour le profil
async function updateProfile(profileData) {
  const token = localStorage.getItem('token');

  const response = await fetch('http://localhost/api/students/profile', {
    method: 'PUT',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(profileData),
  });

  const data = await response.json();
  return data.data;
}

// 4. Déconnexion
async function logout() {
  const token = localStorage.getItem('token');

  await fetch('http://localhost/api/students/logout', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
    },
  });

  localStorage.removeItem('token');
}
```

---

## ⚠️ Notes Importantes

1. **Token d'Authentification:**
   - Le token obtenu lors de la connexion ou du setup du mot de passe doit être inclus dans le header `Authorization` pour toutes les routes protégées
   - Format: `Authorization: Bearer {token}`
   - Le token reste valide jusqu'à la déconnexion ou expiration

2. **Sécurité:**
   - Les mots de passe doivent contenir au moins 8 caractères
   - Tous les mots de passe sont hashés avec bcrypt
   - Les tokens sont gérés par Laravel Sanctum

3. **Gestion des Erreurs:**
   - Toutes les réponses incluent un champ `success` (boolean)
   - En cas d'erreur, un champ `errors` ou `message` est présent
   - Les codes HTTP indiquent le type d'erreur

4. **Examens:**
   - Un étudiant ne voit que les examens de ses classes
   - Les examens sont automatiquement catégorisés selon les dates
   - Le champ `can_attempt` indique si l'étudiant peut tenter l'examen

---

## 🧪 Tests

Les tests automatisés sont disponibles dans : `tests/Feature/StudentApiTest.php`

Pour exécuter les tests :
```bash
php artisan test --filter StudentApiTest
```

---

## 📞 Support

Pour toute question ou problème, contactez : support@evaly.com

---

**Version:** 1.0.0
**Dernière mise à jour:** 27 Octobre 2025
