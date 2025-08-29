# API E-commerce Étudiant - Documentation

## Authentification

### Inscription
- **POST** `/api/inscription`
- Corps de la requête :
```json
{
    "nom": "Diallo",
    "prenom": "Aminata",
    "email": "aminata@test.com",
    "telephone": "+221 77 123 45 67",
    "motDePasse": "motdepasse123",
    "adresse": "123 Rue de l'Université, Dakar"
}
```

### Connexion
- **POST** `/api/connexion`
- Corps de la requête :
```json
{
    "username": "fatou@test.com",
    "password": "etudiant123"
}
```
- Retourne un token JWT à utiliser dans les requêtes suivantes

### Profil utilisateur
- **GET** `/api/profil`
- Headers: `Authorization: Bearer {token}`

- **PUT** `/api/profil`
- Headers: `Authorization: Bearer {token}`
- Corps de la requête :
```json
{
    "nom": "Nouveau nom",
    "prenom": "Nouveau prénom",
    "telephone": "+221 77 999 88 77",
    "adresse": "Nouvelle adresse"
}
```

## Produits

### Liste des produits
- **GET** `/api/produits`
- Paramètres optionnels :
  - `page` : Numéro de page (défaut: 1)
  - `limit` : Nombre d'éléments par page (défaut: 20)
  - `categorie` : ID de la catégorie
  - `recherche` : Terme de recherche
  - `enStock` : true pour afficher seulement les produits en stock

### Détail d'un produit
- **GET** `/api/produits/{id}`

### Recherche de produits
- **GET** `/api/produits/rechercher?q={terme}`

### Produits les plus vendus
- **GET** `/api/produits/plus-vendus?limit=10`

### Nouveautés
- **GET** `/api/produits/nouveautes?limit=10`

### Catégories
- **GET** `/api/categories`

## Panier

### Afficher le panier
- **GET** `/api/panier`
- Headers: `Authorization: Bearer {token}`

### Ajouter au panier
- **POST** `/api/panier/ajouter`
- Headers: `Authorization: Bearer {token}`
- Corps de la requête :
```json
{
    "produitId": 1,
    "quantite": 2
}
```

### Modifier la quantité
- **PUT** `/api/panier/modifier`
- Headers: `Authorization: Bearer {token}`
- Corps de la requête :
```json
{
    "articleId": 1,
    "quantite": 3
}
```

### Supprimer un article
- **DELETE** `/api/panier/supprimer`
- Headers: `Authorization: Bearer {token}`
- Corps de la requête :
```json
{
    "articleId": 1
}
```

### Vider le panier
- **DELETE** `/api/panier/vider`
- Headers: `Authorization: Bearer {token}`

## Commandes

### Mes commandes
- **GET** `/api/commandes`
- Headers: `Authorization: Bearer {token}`

### Détail d'une commande
- **GET** `/api/commandes/{id}`
- Headers: `Authorization: Bearer {token}`

### Créer une commande
- **POST** `/api/commandes`
- Headers: `Authorization: Bearer {token}`
- Corps de la requête :
```json
{
    "adresseLivraison": "123 Rue de Livraison, Dakar",
    "methodePaiement": "ESPECES_LIVRAISON"
}
```

### Annuler une commande
- **PUT** `/api/commandes/{id}/annuler`
- Headers: `Authorization: Bearer {token}`

## Administration

### Connexion admin
- **Interface web** : `/admin/login`
- Identifiants : `admin@test.com` / `admin123`

### Dashboard
- **Interface web** : `/admin/`
- Statistiques, commandes récentes, alertes stock

### Gestion des utilisateurs
- **Interface web** : `/admin/utilisateurs`

### Gestion des commandes
- **Interface web** : `/admin/commandes`

### Gestion des produits
- **Interface web** : `/admin/produits`

## Codes de réponse

- `200` : Succès
- `201` : Créé avec succès
- `400` : Erreur de validation ou données invalides
- `401` : Non authentifié
- `403` : Accès refusé
- `404` : Ressource non trouvée
- `409` : Conflit (ex: email déjà utilisé)

## Exemple de flux complet

1. **Inscription** : POST `/api/inscription`
2. **Connexion** : POST `/api/connexion` → Récupération du token
3. **Parcourir les produits** : GET `/api/produits`
4. **Ajouter au panier** : POST `/api/panier/ajouter`
5. **Voir le panier** : GET `/api/panier`
6. **Passer commande** : POST `/api/commandes`
7. **Suivre la commande** : GET `/api/commandes/{id}`

## Données de test

- **Admin** : `admin@test.com` / `admin123`
- **Étudiant** : `fatou@test.com` / `etudiant123`
- **Produits** : Écouteurs Bluetooth, Pack cahiers A4
- **Catégories** : Électronique, Papeterie