Voici une version en français complète et adaptée :  

```markdown
# API PHP pour EasyCrea et Deckouverte

Cette API, développée en PHP selon le modèle MVC, est conçue pour prendre en charge les projets [EasyCrea](https://github.com/EasyCrea/Easy_Crea) et [Deckouverte](https://github.com/EasyCrea/Deckouverte). Elle permet de gérer les utilisateurs (créateurs et administrateurs), les decks, les cartes, ainsi que leurs interactions.

---

## 🚀 Fonctionnalités principales
- **Gestion des utilisateurs :**  
  - Inscription et connexion des créateurs.  
  - Assignation de cartes aléatoires.  
  - Vérification de l'état des cartes dans un deck.  
  - Gestion des historiques de parties et des interactions.  

- **Gestion des administrateurs :**  
  - Création, modification, activation ou suppression de decks et cartes.  
  - Gestion des utilisateurs : bannissement, suppression.  
  - Tableau de bord administratif.  

- **Gestion des cartes :**  
  - Création et modification de cartes.  
  - Consultation des cartes créées ou aléatoires.

---

## 📂 Routes de l'API
### Utilisateurs
#### Créateurs
- **Inscription :**  
  `POST /createurs/register`  
  Permet à un utilisateur de s'inscrire.  

- **Connexion :**  
  `POST /createurs/login`  
  Permet à un utilisateur de se connecter.  

- **Vérification du token :**  
  `GET /authorization/checkToken`  
  Vérifie la validité du token d'authentification.  

- **Assignation de carte aléatoire :**  
  `POST /createur/{id_deck}/{id_createur}`  
  Associe une carte aléatoire à un créateur.  

- **Vérification de carte aléatoire :**  
  `GET /createur/{id_deck}/{id_createur}/randomCard`  
  Vérifie si un créateur a déjà une carte dans un deck.

- **Consulter les decks disponibles :**  
  `GET /getAllDeck`  
  Retourne tous les decks disponibles.  

#### Historique des parties
- **Consultation :**  
  `GET /gamehistory/{user_id}/{deck_id}`  
  Récupère l'historique des parties d'un utilisateur dans un deck donné.  

- **Ajout :**  
  `POST /creategamehistory`  
  Ajoute un nouvel historique de partie.  

- **Suppression :**  
  `DELETE /deletegamehistory/{id}`  
  Supprime un historique de partie.

#### Interactions (Likes)
- **Ajout ou consultation :**  
  `POST /like/{id_deck}/{id_createur}`  
  Ajoute ou consulte un "like" d'un créateur pour un deck donné.  

- **Suppression :**  
  `DELETE /like/delete/{id_deck}/{id_createur}`  
  Supprime un "like" pour un deck donné.

---

### Administrateurs
#### Gestion des créateurs
- **Liste des créateurs :**  
  `GET /admin/createurs`  
  Retourne tous les créateurs inscrits.  

- **Suppression d'un créateur :**  
  `DELETE /admin/deleteCreateur/{id}`  
  Supprime un créateur par son ID.  

- **Bannissement d'un créateur :**  
  `PATCH /admin/banCreateur/{id}`  
  Bannis un créateur par son ID.

#### Gestion des decks
- **Création de decks :**  
  `POST /admin/createDeck`  
  Crée un nouveau deck.  

- **Modification d'un deck :**  
  `POST /admin/edit/{id}`  
  Modifie un deck existant par son ID.  

- **Activation/Désactivation :**  
  `PATCH /admin/activate/{id}` ou `PATCH /admin/deactivate/{id}`  
  Active ou désactive un deck par son ID.  

- **Suppression :**  
  `DELETE /admin/delete/deck/{id}`  
  Supprime un deck par son ID.

#### Gestion des cartes
- **Création :**  
  `POST /createCard{id}`  
  Crée une carte pour un deck spécifique.  

- **Modification :**  
  `PATCH /admin/edit/card/{id}`  
  Modifie une carte existante.  

- **Suppression :**  
  `DELETE /admin/delete/card/{id}`  
  Supprime une carte par son ID.

#### Tableau de bord
- **Accès au tableau de bord :**  
  `GET /admin/dashboard`  
  Affiche un tableau de bord pour l'administrateur.

---

## ⚙️ Installation
1. **Cloner ce dépôt :**  
   ```bash
   git clone [<URL_DU_DEPOT>](https://github.com/EasyCrea/DeckApi)
   cd DeckApi
   ```

2. **Configurer l’environnement :**  
   - Créez un fichier `config.local.php` et `config.prod.php` dans `src/Config/` pour vos configurations locales et de production.  
   - Assurez-vous que ces fichiers sont ignorés par Git (ajout dans `.gitignore`).  

3. **Installer les dépendances :**  
   ```bash
   composer install
   ```

4. **Importer la base de données :**  
   Importez le fichier SQL fourni dans votre base de données.

5. **Démarrer le serveur :**  
   ```bash
   php -S localhost:8000 -t public
   ```

---

## 📜 License
Cette API est distribuée sous licence MIT.

---

## 🛠️ Contributions
Les contributions sont les bienvenues. N’hésitez pas à ouvrir une issue ou une pull request !

---
```

Ce README fournit une présentation claire et organisée de l'API, ainsi que des étapes pour l'installation et l'utilisation. Si tu veux ajouter des sections ou modifier certains éléments, n'hésite pas à me le dire !
