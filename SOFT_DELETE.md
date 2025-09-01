# Système de Suppression Logique (Soft Delete)

## Vue d'ensemble

Ce projet implémente un système de suppression logique (soft delete) pour toutes les entités principales. Au lieu de supprimer physiquement les données de la base de données, les entités sont marquées comme supprimées avec un timestamp `deletedAt`.

## Entités supportées

- `Utilisateur` (User)
- `Produit` (Product)
- `Commande` (Order)
- `ArticleCommande` (Order Item)
- `Paiement` (Payment)
- `Livraison` (Delivery)
- `Categorie` (Category)
- `Panier` (Cart)
- `ArticlePanier` (Cart Item)

## Fonctionnalités

### Trait SoftDeleteable

Toutes les entités utilisent le trait `App\Entity\Trait\SoftDeleteable` qui fournit :

- `deletedAt` : Champ nullable datetime pour marquer la suppression
- `getDeletedAt()` : Récupère la date de suppression
- `setDeletedAt()` : Définit la date de suppression
- `isDeleted()` : Vérifie si l'entité est supprimée
- `softDelete()` : Marque l'entité comme supprimée
- `restore()` : Restaure l'entité

### Repository Trait

Le trait `App\Repository\Trait\SoftDeleteRepositoryTrait` fournit :

- Filtrage automatique des entités supprimées dans les requêtes par défaut
- `findAllNotDeleted()` : Trouve toutes les entités non supprimées
- `findOneNotDeleted()` : Trouve une entité non supprimée par ID
- `softDelete()` : Supprime logiquement une entité
- `restore()` : Restaure une entité
- `findDeleted()` : Trouve toutes les entités supprimées

### Service SoftDeleteService

Le service `App\Service\SoftDeleteService` centralise la gestion :

- `softDelete($entity)` : Supprime logiquement une entité
- `restore($entity)` : Restaure une entité
- `softDeleteBatch($entities)` : Suppression logique en lot
- `restoreBatch($entities)` : Restauration en lot

## Utilisation dans les contrôleurs

### API Controllers

```php
// Au lieu de $entityManager->remove($entity)
$this->softDeleteService->softDelete($entity);

// Pour restaurer
$this->softDeleteService->restore($entity);
```

### Admin Controllers

Des endpoints d'administration sont disponibles pour gérer les entités supprimées :

- `GET /admin/soft-delete/utilisateurs/deleted` : Liste des utilisateurs supprimés
- `POST /admin/soft-delete/utilisateurs/{id}/restore` : Restaurer un utilisateur
- `GET /admin/soft-delete/produits/deleted` : Liste des produits supprimés
- `POST /admin/soft-delete/produits/{id}/restore` : Restaurer un produit
- Etc. pour toutes les entités

## Comportement par défaut

- **Requêtes normales** : Les entités supprimées sont automatiquement exclues
- **Méthodes repository** : Toutes les méthodes custom incluent le filtrage soft delete
- **Relations** : Les relations vers des entités supprimées sont automatiquement filtrées

## Migration de base de données

La migration `Version20250901092530` ajoute la colonne `deleted_at` à toutes les tables.

## Avantages

1. **Récupération de données** : Possibilité de restaurer les données supprimées
2. **Audit** : Conservation de l'historique des suppressions
3. **Intégrité référentielle** : Préservation des relations entre entités
4. **Transparence** : Le système fonctionne de manière transparente pour l'application existante

## Exemples d'utilisation

### Suppression logique d'un produit
```php
$produit = $produitRepository->find($id);
$softDeleteService->softDelete($produit);
```

### Restauration d'un produit
```php
$produit = $produitRepository->createQueryBuilder('p')
    ->andWhere('p.id = :id')
    ->andWhere('p.deletedAt IS NOT NULL')
    ->setParameter('id', $id)
    ->getQuery()
    ->getOneOrNullResult();

$softDeleteService->restore($produit);
```

### Lister les entités supprimées
```php
$deletedProducts = $produitRepository->findDeleted();
```