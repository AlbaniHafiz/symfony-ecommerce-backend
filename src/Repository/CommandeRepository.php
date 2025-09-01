<?php

namespace App\Repository;

use App\Entity\Commande;
use App\Entity\Utilisateur;
use App\Repository\Trait\SoftDeleteRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commande>
 */
class CommandeRepository extends ServiceEntityRepository
{
    use SoftDeleteRepositoryTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    /**
     * Trouve les commandes d'un utilisateur
     * @return Commande[]
     */
    public function findByUtilisateur(Utilisateur $utilisateur): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.utilisateur = :utilisateur')
            ->setParameter('utilisateur', $utilisateur)
            ->orderBy('c.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les commandes par statut
     * @return Commande[]
     */
    public function findByStatut(string $statut): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.statut = :statut')
            ->setParameter('statut', $statut)
            ->orderBy('c.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les commandes en attente de livraison
     * @return Commande[]
     */
    public function findEnAttentelivraison(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.statut IN (:statuts)')
            ->setParameter('statuts', ['CONFIRMEE', 'EN_PREPARATION', 'EN_LIVRAISON'])
            ->orderBy('c.dateCreation', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques des commandes par période
     */
    public function getStatistiquesParPeriode(\DateTimeInterface $debut, \DateTimeInterface $fin): array
    {
        return $this->createQueryBuilder('c')
            ->select('
                COUNT(c.id) as nombreCommandes,
                SUM(c.total) as chiffreAffaires,
                AVG(c.total) as panierMoyen,
                c.statut
            ')
            ->andWhere('c.dateCreation BETWEEN :debut AND :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->groupBy('c.statut')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les commandes récentes
     * @return Commande[]
     */
    public function findRecentes(int $limit = 10): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.dateCreation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les commandes par statut
     */
    public function compterParStatut(): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.statut, COUNT(c.id) as nombre')
            ->groupBy('c.statut')
            ->getQuery()
            ->getResult();
    }

    /**
     * Chiffre d'affaires du mois en cours
     */
    public function getChiffreAffairesMois(): float
    {
        $debut = new \DateTime('first day of this month');
        $fin = new \DateTime('last day of this month');

        $result = $this->createQueryBuilder('c')
            ->select('SUM(c.total)')
            ->andWhere('c.dateCreation BETWEEN :debut AND :fin')
            ->andWhere('c.statut != :annulee')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->setParameter('annulee', 'ANNULEE')
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0);
    }

    /**
     * Trouve les commandes d'aujourd'hui
     * @return Commande[]
     */
    public function findAujourdhui(): array
    {
        $aujourd_hui = new \DateTime('today');
        $demain = new \DateTime('tomorrow');

        return $this->createQueryBuilder('c')
            ->andWhere('c.dateCreation >= :aujourd_hui')
            ->andWhere('c.dateCreation < :demain')
            ->setParameter('aujourd_hui', $aujourd_hui)
            ->setParameter('demain', $demain)
            ->orderBy('c.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }
}