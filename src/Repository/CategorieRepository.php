<?php

namespace App\Repository;

use App\Entity\Categorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Categorie>
 */
class CategorieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Categorie::class);
    }

    /**
     * Trouve les catégories actives
     * @return Categorie[]
     */
    public function findActives(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.active = :active')
            ->setParameter('active', true)
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve une catégorie active par son ID
     */
    public function findActiveById(int $id): ?Categorie
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.id = :id')
            ->andWhere('c.active = :active')
            ->setParameter('id', $id)
            ->setParameter('active', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Compte le nombre de produits par catégorie
     */
    public function getStatistiquesCategories(): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.id, c.nom, COUNT(p.id) as nombreProduits')
            ->leftJoin('c.produits', 'p')
            ->groupBy('c.id')
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}