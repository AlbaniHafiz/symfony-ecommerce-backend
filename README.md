# Symfony E-commerce Backend

Backend Symfony pour une application mobile d'e-commerce étudiante avec toutes les entités et API en français.

## 🚀 Fonctionnalités

- **API REST complète** en français pour application mobile Flutter
- **Interface d'administration** web avec Bootstrap
- **Authentification JWT** pour l'API mobile
- **Gestion des rôles** (étudiant, admin, livreur)
- **Gestion complète** des produits, commandes, paniers, paiements
- **Système de livraison** avec attribution aux livreurs
- **Base de données** SQLite/MySQL avec entités en français

## 📋 Prérequis

- PHP 8.1+
- Composer 2.x
- Symfony CLI (optionnel)

## 🛠️ Installation

1. **Cloner le projet**
```bash
git clone https://github.com/AlbaniHafiz/symfony-ecommerce-backend.git
cd symfony-ecommerce-backend
```

2. **Installer les dépendances**
```bash
composer install
```

3. **Configurer la base de données**
```bash
# Créer la base de données
php bin/console doctrine:database:create

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Charger les données de test
php bin/console app:init-data
```

4. **Générer les clés JWT**
```bash
mkdir -p config/jwt
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass pass:votre_passphrase
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout -passin pass:votre_passphrase
```

5. **Démarrer le serveur**
```bash
php bin/console server:run
# ou avec Symfony CLI
symfony serve
```

## 🔐 Comptes de test

- **Administrateur** : `admin@test.com` / `admin123`
- **Étudiant** : `fatou@test.com` / `etudiant123`

## 📱 API Endpoints

### Authentification
- `POST /api/inscription` - Inscription utilisateur
- `POST /api/connexion` - Connexion et récupération du token JWT
- `GET /api/profil` - Profil utilisateur
- `PUT /api/profil` - Modifier le profil

### Produits
- `GET /api/produits` - Liste des produits
- `GET /api/produits/{id}` - Détail d'un produit
- `GET /api/categories` - Liste des catégories
- `GET /api/produits/rechercher` - Recherche de produits

### Panier
- `GET /api/panier` - Afficher le panier
- `POST /api/panier/ajouter` - Ajouter un produit
- `PUT /api/panier/modifier` - Modifier la quantité
- `DELETE /api/panier/supprimer` - Supprimer un article
- `DELETE /api/panier/vider` - Vider le panier

### Commandes
- `GET /api/commandes` - Mes commandes
- `GET /api/commandes/{id}` - Détail d'une commande
- `POST /api/commandes` - Créer une commande
- `PUT /api/commandes/{id}/annuler` - Annuler une commande

## 🖥️ Interface Admin Moderne

![Interface Admin](https://github.com/user-attachments/assets/aeaaca12-bec3-4e6f-a294-7daeb473c51a)

### ✨ Fonctionnalités Exceptionnelles

L'interface d'administration a été **complètement modernisée** avec :

- **🎨 Design Ultra-Moderne** : Interface avec gradients CSS3, animations fluides et palette harmonieuse
- **📊 Dashboard Avancé** : Statistiques en temps réel avec graphiques interactifs  
- **🔧 Gestion Séparée par Entité** : Interface dédiée pour chaque module (Produits, Commandes, Utilisateurs, etc.)
- **🔍 Filtres Dynamiques** : Recherche avancée et filtrage en temps réel
- **📱 Design Responsive** : Compatible mobile, tablette et desktop
- **⚡ Actions Rapides** : Toggle d'état, modals interactifs, notifications

### 🚀 Accès à l'Administration

```bash
# URL de l'interface admin
http://localhost:8080/admin

# Comptes de test
Administrateur : admin@ecommerce.test / admin123
Étudiant      : fatou.diallo@etudiant.test / etudiant123
Livreur       : moussa.ndiaye@livreur.test / livreur123
```

### 📋 Modules Disponibles

- **📊 Dashboard** : Vue d'ensemble avec statistiques temps réel
- **📦 Gestion des Produits** : CRUD complet avec filtres avancés et gestion des stocks
- **🛒 Gestion des Commandes** : Suivi des commandes par statut et attribution aux livreurs
- **👥 Gestion des Utilisateurs** : Administration des comptes étudiants, livreurs et admins
- **💳 Gestion des Paiements** : Suivi des transactions et méthodes de paiement
- **🚛 Gestion des Livraisons** : Attribution et suivi des livraisons
- **🏷️ Gestion des Catégories** : Organisation du catalogue produits
- **📚 Documentation Intégrée** : Guide utilisateur accessible depuis l'admin

## 🗄️ Configuration Base de Données avec PHPMyAdmin

### 🐳 Installation avec Docker (Recommandée)

Le projet inclut une configuration Docker complète avec MySQL et PHPMyAdmin :

```bash
# Démarrer tous les services
docker-compose up -d

# Services disponibles :
# - Application Symfony : http://localhost:8080
# - PHPMyAdmin : http://localhost:8081
# - MySQL : localhost:3306
```

### 🔐 Accès PHPMyAdmin

```
URL      : http://localhost:8081
Server   : mysql
Username : root
Password : root_password
Database : ecommerce_etudiant
```

### ⚙️ Configuration Manuelle (Alternative)

Si vous préférez une installation locale sans Docker :

1. **Installer MySQL 8.0**
```bash
# Ubuntu/Debian
sudo apt update && sudo apt install mysql-server

# macOS
brew install mysql

# Windows : Télécharger depuis mysql.com
```

2. **Créer la base de données**
```sql
CREATE DATABASE ecommerce_etudiant CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'ecommerce_user'@'localhost' IDENTIFIED BY 'ecommerce_password';
GRANT ALL PRIVILEGES ON ecommerce_etudiant.* TO 'ecommerce_user'@'localhost';
FLUSH PRIVILEGES;
```

3. **Configurer l'application**
```bash
# Mettre à jour .env
DATABASE_URL="mysql://ecommerce_user:ecommerce_password@127.0.0.1:3306/ecommerce_etudiant?serverVersion=8.0.32&charset=utf8mb4"

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Charger les données de test
php bin/console doctrine:fixtures:load
```

4. **Installer PHPMyAdmin (optionnel)**
```bash
# Ubuntu/Debian
sudo apt install phpmyadmin

# macOS avec Homebrew
brew install phpmyadmin

# Ou télécharger depuis phpmyadmin.net
```

### 🚀 Migration de SQLite vers MySQL

Le projet peut facilement basculer entre SQLite et MySQL :

```bash
# Sauvegarder les données actuelles
php bin/console doctrine:fixtures:load --no-interaction

# Mettre à jour .env pour MySQL
DATABASE_URL="mysql://ecommerce_user:ecommerce_password@127.0.0.1:3306/ecommerce_etudiant?serverVersion=8.0.32&charset=utf8mb4"

# Recréer la structure
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

## 🗄️ Structure de la base de données

### Entités principales (en français)
- **Utilisateur** - Gestion des comptes (étudiants, livreurs, admins)
- **Produit** - Catalogue des produits
- **Categorie** - Classification des produits
- **Commande** - Commandes des utilisateurs
- **ArticleCommande** - Détails des articles commandés
- **Panier** - Paniers des utilisateurs
- **ArticlePanier** - Articles dans les paniers
- **Paiement** - Gestion des paiements
- **Livraison** - Suivi des livraisons

### Statuts et énumérations
- **Rôles** : ETUDIANT, ADMIN, LIVREUR
- **Statuts commandes** : EN_ATTENTE, CONFIRMEE, EN_PREPARATION, EN_LIVRAISON, LIVREE, ANNULEE
- **Statuts paiements** : EN_ATTENTE, PAYE, ECHEC, REMBOURSE
- **Méthodes paiement** : ESPECES_LIVRAISON, NITA, AMANA, CARTE_BANCAIRE

## 🔧 Configuration

### Variables d'environnement (.env)
```bash
# Base de données
DATABASE_URL="sqlite:///%kernel.project_dir%/var/ecommerce.db"

# JWT
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=votre_passphrase
```

## 📚 Documentation

- [Documentation API détaillée](API_DOCUMENTATION.md)
- [Guide de développement](docs/development.md)

## 🤝 Contribution

1. Fork le projet
2. Créer une branche (`git checkout -b feature/nouvelle-fonctionnalite`)
3. Commit les changements (`git commit -am 'Ajouter nouvelle fonctionnalité'`)
4. Push la branche (`git push origin feature/nouvelle-fonctionnalite`)
5. Créer une Pull Request

## 📄 Licence

Ce projet est sous licence MIT - voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 🎯 Roadmap

- [ ] Upload d'images pour les produits
- [ ] Système de notifications push
- [ ] Intégration Nita/Amana
- [ ] API de géolocalisation pour les livraisons
- [ ] Système de commentaires et notes
- [ ] Programme de fidélité étudiante
- [ ] Configuration Docker
- [ ] Tests automatisés

## 📞 Support

Pour toute question ou problème, ouvrez une issue sur GitHub.
