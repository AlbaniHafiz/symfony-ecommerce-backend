<?php

namespace App\Repository;

use App\Entity\Panier;
use App\Entity\Utilisateur;
use App\Repository\Trait\SoftDeleteRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Panier>
 */
class PanierRepository extends ServiceEntityRepository
{
    use SoftDeleteRepositoryTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Panier::class);
    }

    /**
     * Trouve le panier actif d'un utilisateur
     */
    public function findPanierActif(Utilisateur $utilisateur): ?Panier
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.utilisateur = :utilisateur')
            ->setParameter('utilisateur', $utilisateur)
            ->orderBy('p.dateModification', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve ou crée un panier pour un utilisateur
     */
    public function findOuCreerPanier(Utilisateur $utilisateur): Panier
    {
        $panier = $this->findPanierActif($utilisateur);
        
        if (!$panier) {
            $panier = new Panier();
            $panier->setUtilisateur($utilisateur);
            $this->getEntityManager()->persist($panier);
            $this->getEntityManager()->flush();
        }

        return $panier;
    }
}