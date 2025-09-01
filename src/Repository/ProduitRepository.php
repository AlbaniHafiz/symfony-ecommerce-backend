<?php

namespace App\Repository;

use App\Entity\Produit;
use App\Entity\Categorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produit>
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    /**
     * Trouve les produits actifs
     * @return Produit[]
     */
    public function findActifs(): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.categorie', 'c')
            ->andWhere('p.actif = :actif')
            ->andWhere('c.active = :active')
            ->setParameter('actif', true)
            ->setParameter('active', true)
            ->orderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les produits en stock
     * @return Produit[]
     */
    public function findEnStock(): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.categorie', 'c')
            ->andWhere('p.actif = :actif')
            ->andWhere('c.active = :active')
            ->andWhere('p.stock > 0')
            ->setParameter('actif', true)
            ->setParameter('active', true)
            ->orderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les produits par catégorie
     * @return Produit[]
     */
    public function findByCategorie(Categorie $categorie): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.categorie = :categorie')
            ->andWhere('p.actif = :actif')
            ->setParameter('categorie', $categorie)
            ->setParameter('actif', true)
            ->orderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche de produits par nom ou description
     * @return Produit[]
     */
    public function rechercher(string $terme): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.categorie', 'c')
            ->andWhere('p.nom LIKE :terme OR p.description LIKE :terme')
            ->andWhere('p.actif = :actif')
            ->andWhere('c.active = :active')
            ->setParameter('terme', '%' . $terme . '%')
            ->setParameter('actif', true)
            ->setParameter('active', true)
            ->orderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les produits les plus vendus
     * @return Produit[]
     */
    public function findPlusVendus(int $limit = 10): array
    {
        return $this->createQueryBuilder('p')
            ->select('p, SUM(ac.quantite) as quantiteVendue')
            ->innerJoin('p.articlesCommande', 'ac')
            ->innerJoin('ac.commande', 'c')
            ->innerJoin('p.categorie', 'cat')
            ->andWhere('p.actif = :actif')
            ->andWhere('cat.active = :active')
            ->andWhere('c.statut != :annulee')
            ->setParameter('actif', true)
            ->setParameter('active', true)
            ->setParameter('annulee', 'ANNULEE')
            ->groupBy('p.id')
            ->orderBy('quantiteVendue', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les produits en rupture de stock
     * @return Produit[]
     */
    public function findRuptureStock(): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.categorie', 'c')
            ->andWhere('p.stock = 0')
            ->andWhere('p.actif = :actif')
            ->andWhere('c.active = :active')
            ->setParameter('actif', true)
            ->setParameter('active', true)
            ->orderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Filtre les produits par prix
     * @return Produit[]
     */
    public function findByPrixRange(float $prixMin, float $prixMax): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.categorie', 'c')
            ->andWhere('p.prix BETWEEN :prixMin AND :prixMax')
            ->andWhere('p.actif = :actif')
            ->andWhere('c.active = :active')
            ->setParameter('prixMin', $prixMin)
            ->setParameter('prixMax', $prixMax)
            ->setParameter('actif', true)
            ->setParameter('active', true)
            ->orderBy('p.prix', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les produits avec filtres pour l'admin
     * @return Produit[]
     */
    public function findWithFilters(string $search = '', string $category = '', string $status = '', int $page = 1, int $limit = 10): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.categorie', 'c')
            ->addSelect('c');

        if (!empty($search)) {
            $qb->andWhere('p.nom LIKE :search OR p.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if (!empty($category)) {
            $qb->andWhere('c.id = :category')
               ->setParameter('category', $category);
        }

        if ($status === 'actif') {
            $qb->andWhere('p.actif = true');
        } elseif ($status === 'inactif') {
            $qb->andWhere('p.actif = false');
        } elseif ($status === 'rupture') {
            $qb->andWhere('p.stock = 0');
        }

        return $qb->orderBy('p.dateCreation', 'DESC')
                  ->setFirstResult(($page - 1) * $limit)
                  ->setMaxResults($limit)
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Compte les produits avec filtres
     */
    public function countWithFilters(string $search = '', string $category = '', string $status = ''): int
    {
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->leftJoin('p.categorie', 'c');

        if (!empty($search)) {
            $qb->andWhere('p.nom LIKE :search OR p.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if (!empty($category)) {
            $qb->andWhere('c.id = :category')
               ->setParameter('category', $category);
        }

        if ($status === 'actif') {
            $qb->andWhere('p.actif = true');
        } elseif ($status === 'inactif') {
            $qb->andWhere('p.actif = false');
        } elseif ($status === 'rupture') {
            $qb->andWhere('p.stock = 0');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Vérifie si un produit a des commandes associées
     */
    public function hasOrders(int $productId): bool
    {
        $count = $this->createQueryBuilder('p')
            ->select('COUNT(ac.id)')
            ->leftJoin('p.articlesCommande', 'ac')
            ->where('p.id = :id')
            ->setParameter('id', $productId)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * Obtient les statistiques d'un produit
     */
    public function getProductStats(int $productId): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('
                p.id,
                p.nom,
                p.stock,
                COALESCE(SUM(ac.quantite), 0) as totalVendu,
                COUNT(DISTINCT ac.commande) as nombreCommandes,
                COALESCE(SUM(ac.quantite * ac.prixUnitaire), 0) as chiffreAffaires
            ')
            ->leftJoin('p.articlesCommande', 'ac')
            ->leftJoin('ac.commande', 'c')
            ->where('p.id = :id')
            ->andWhere('c.statut != :annulee OR c.statut IS NULL')
            ->setParameter('id', $productId)
            ->setParameter('annulee', 'ANNULEE')
            ->groupBy('p.id');

        $result = $qb->getQuery()->getOneOrNullResult();

        return $result ?: [
            'id' => $productId,
            'nom' => '',
            'stock' => 0,
            'totalVendu' => 0,
            'nombreCommandes' => 0,
            'chiffreAffaires' => 0
        ];
    }

    /**
     * Obtient les statistiques pour le dashboard
     */
    public function getDashboardStats(): array
    {
        // Produits par catégorie
        $parCategorie = $this->createQueryBuilder('p')
            ->select('c.nom as categorie, COUNT(p.id) as nombre')
            ->leftJoin('p.categorie', 'c')
            ->groupBy('c.id')
            ->orderBy('nombre', 'DESC')
            ->getQuery()
            ->getResult();

        // Évolution des stocks
        $stocksFaibles = $this->createQueryBuilder('p')
            ->select('p.nom, p.stock')
            ->where('p.stock <= 5')
            ->andWhere('p.actif = true')
            ->orderBy('p.stock', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        // Produits les plus vendus ce mois
        $plusVendus = $this->createQueryBuilder('p')
            ->select('p.nom, SUM(ac.quantite) as vendu')
            ->leftJoin('p.articlesCommande', 'ac')
            ->leftJoin('ac.commande', 'c')
            ->where('c.dateCreation >= :debutMois')
            ->andWhere('c.statut != :annulee')
            ->setParameter('debutMois', new \DateTime('first day of this month'))
            ->setParameter('annulee', 'ANNULEE')
            ->groupBy('p.id')
            ->orderBy('vendu', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        return [
            'parCategorie' => $parCategorie,
            'stocksFaibles' => $stocksFaibles,
            'plusVendus' => $plusVendus,
        ];
    }
}