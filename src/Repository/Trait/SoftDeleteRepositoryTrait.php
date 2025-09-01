<?php

namespace App\Repository\Trait;

use Doctrine\ORM\QueryBuilder;

trait SoftDeleteRepositoryTrait
{
    /**
     * Add a condition to exclude soft deleted entities
     */
    public function addNotDeletedCondition(QueryBuilder $qb, string $alias = null): QueryBuilder
    {
        if ($alias === null) {
            $alias = $qb->getRootAliases()[0];
        }
        
        return $qb->andWhere($alias . '.deletedAt IS NULL');
    }

    /**
     * Find all non-deleted entities
     */
    public function findAllNotDeleted(): array
    {
        $qb = $this->createQueryBuilder('e');
        $this->addNotDeletedCondition($qb);
        
        return $qb->getQuery()->getResult();
    }

    /**
     * Find one non-deleted entity by id
     */
    public function findOneNotDeleted($id)
    {
        $qb = $this->createQueryBuilder('e');
        $this->addNotDeletedCondition($qb);
        $qb->andWhere('e.id = :id')
           ->setParameter('id', $id);
        
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Soft delete an entity
     */
    public function softDelete($entity): void
    {
        $entity->softDelete();
        $this->getEntityManager()->flush();
    }

    /**
     * Restore a soft deleted entity
     */
    public function restore($entity): void
    {
        $entity->restore();
        $this->getEntityManager()->flush();
    }

    /**
     * Find all soft deleted entities
     */
    public function findDeleted(): array
    {
        $qb = $this->createQueryBuilder('e');
        $qb->andWhere('e.deletedAt IS NOT NULL');
        
        return $qb->getQuery()->getResult();
    }

    /**
     * Override the default findAll to exclude soft deleted entities
     */
    public function findAll(): array
    {
        return $this->findAllNotDeleted();
    }

    /**
     * Override the default find to exclude soft deleted entities
     */
    public function find(mixed $id, \Doctrine\DBAL\LockMode|int|null $lockMode = null, ?int $lockVersion = null): ?object
    {
        $entity = parent::find($id, $lockMode, $lockVersion);
        
        if ($entity && method_exists($entity, 'isDeleted') && $entity->isDeleted()) {
            return null;
        }
        
        return $entity;
    }

    /**
     * Override findBy to exclude soft deleted entities by default
     */
    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
    {
        $qb = $this->createQueryBuilder('e');
        $this->addNotDeletedCondition($qb);

        foreach ($criteria as $field => $value) {
            $qb->andWhere("e.$field = :$field")
               ->setParameter($field, $value);
        }

        if ($orderBy) {
            foreach ($orderBy as $field => $direction) {
                $qb->addOrderBy("e.$field", $direction);
            }
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        if ($offset) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Override findOneBy to exclude soft deleted entities by default
     */
    public function findOneBy(array $criteria, ?array $orderBy = null): ?object
    {
        $results = $this->findBy($criteria, $orderBy, 1);
        return $results ? $results[0] : null;
    }
}