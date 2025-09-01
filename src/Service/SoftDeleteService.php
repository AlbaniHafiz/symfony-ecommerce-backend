<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class SoftDeleteService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Soft delete an entity
     */
    public function softDelete(object $entity): void
    {
        if (!method_exists($entity, 'softDelete')) {
            throw new \InvalidArgumentException('Entity does not support soft delete');
        }

        $entity->softDelete();
        $this->entityManager->flush();
    }

    /**
     * Restore a soft deleted entity
     */
    public function restore(object $entity): void
    {
        if (!method_exists($entity, 'restore')) {
            throw new \InvalidArgumentException('Entity does not support restore');
        }

        $entity->restore();
        $this->entityManager->flush();
    }

    /**
     * Soft delete multiple entities
     */
    public function softDeleteBatch(array $entities): void
    {
        foreach ($entities as $entity) {
            if (!method_exists($entity, 'softDelete')) {
                throw new \InvalidArgumentException('Entity does not support soft delete');
            }
            $entity->softDelete();
        }
        $this->entityManager->flush();
    }

    /**
     * Restore multiple entities
     */
    public function restoreBatch(array $entities): void
    {
        foreach ($entities as $entity) {
            if (!method_exists($entity, 'restore')) {
                throw new \InvalidArgumentException('Entity does not support restore');
            }
            $entity->restore();
        }
        $this->entityManager->flush();
    }

    /**
     * Check if an entity is soft deleted
     */
    public function isDeleted(object $entity): bool
    {
        if (!method_exists($entity, 'isDeleted')) {
            return false;
        }

        return $entity->isDeleted();
    }
}