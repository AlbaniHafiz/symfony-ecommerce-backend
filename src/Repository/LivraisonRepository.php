<?php

namespace App\Repository;

use App\Entity\Livraison;
use App\Repository\Trait\SoftDeleteRepositoryTrait;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Livraison>
 */
class LivraisonRepository extends ServiceEntityRepository
{
    use SoftDeleteRepositoryTrait;
    
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Livraison::class);
    }

    /**
     * Trouve les livraisons d'un livreur
     * @return Livraison[]
     */
    public function findByLivreur(Utilisateur $livreur): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.livreur = :livreur')
            ->setParameter('livreur', $livreur)
            ->orderBy('l.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les livraisons par statut
     * @return Livraison[]
     */
    public function findByStatut(string $statut): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.statut = :statut')
            ->setParameter('statut', $statut)
            ->orderBy('l.dateCreation', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les livraisons en attente d'attribution
     * @return Livraison[]
     */
    public function findEnAttente(): array
    {
        return $this->findByStatut('EN_ATTENTE');
    }

    /**
     * Trouve les livraisons assignées aujourd'hui
     * @return Livraison[]
     */
    public function findAssigneesAujourdhui(): array
    {
        $aujourd_hui = new \DateTime('today');
        $demain = new \DateTime('tomorrow');

        return $this->createQueryBuilder('l')
            ->andWhere('l.dateAttribution >= :aujourd_hui')
            ->andWhere('l.dateAttribution < :demain')
            ->setParameter('aujourd_hui', $aujourd_hui)
            ->setParameter('demain', $demain)
            ->orderBy('l.dateAttribution', 'DESC')
            ->getQuery()
            ->getResult();
    }
}