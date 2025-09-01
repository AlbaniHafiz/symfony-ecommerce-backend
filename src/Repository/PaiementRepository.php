<?php

namespace App\Repository;

use App\Entity\Paiement;
use App\Repository\Trait\SoftDeleteRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Paiement>
 */
class PaiementRepository extends ServiceEntityRepository
{
    use SoftDeleteRepositoryTrait;
    
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Paiement::class);
    }

    /**
     * Trouve les paiements par statut
     * @return Paiement[]
     */
    public function findByStatut(string $statut): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.statut = :statut')
            ->setParameter('statut', $statut)
            ->orderBy('p.dateTransaction', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques des paiements
     */
    public function getStatistiques(): array
    {
        return $this->createQueryBuilder('p')
            ->select('
                p.statut,
                p.methodePaiement,
                COUNT(p.id) as nombre,
                SUM(p.montant) as montantTotal
            ')
            ->groupBy('p.statut, p.methodePaiement')
            ->getQuery()
            ->getResult();
    }
}