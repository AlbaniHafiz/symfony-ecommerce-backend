<?php

namespace App\Repository;

use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Utilisateur>
 * @implements PasswordUpgraderInterface<Utilisateur>
 */
class UtilisateurRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically when needed.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Utilisateur) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setMotDePasse($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Trouve un utilisateur par son email
     */
    public function findOneByEmail(string $email): ?Utilisateur
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * Trouve les utilisateurs actifs
     * @return Utilisateur[]
     */
    public function findActifs(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.actif = :actif')
            ->setParameter('actif', true)
            ->orderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les livreurs disponibles
     * @return Utilisateur[]
     */
    public function findLivreurs(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :role')
            ->andWhere('u.actif = :actif')
            ->setParameter('role', '%ROLE_LIVREUR%')
            ->setParameter('actif', true)
            ->orderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre d'utilisateurs par rôle
     */
    public function compterParRole(string $role): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->andWhere('u.roles LIKE :role')
            ->andWhere('u.actif = :actif')
            ->setParameter('role', '%' . $role . '%')
            ->setParameter('actif', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Alias pour compterParRole - pour compatibilité
     */
    public function countByRole(string $role): int
    {
        return $this->compterParRole($role);
    }

    /**
     * Compte les nouveaux utilisateurs cette semaine
     */
    public function countNewThisWeek(): int
    {
        $weekAgo = new \DateTime('-1 week');
        
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->andWhere('u.dateCreation >= :date')
            ->setParameter('date', $weekAgo)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Recherche d'utilisateurs par nom, prénom ou email
     * @return Utilisateur[]
     */
    public function rechercher(string $terme): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.nom LIKE :terme OR u.prenom LIKE :terme OR u.email LIKE :terme')
            ->setParameter('terme', '%' . $terme . '%')
            ->orderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
