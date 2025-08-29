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
}