<?php

namespace App\Repository;

use App\Entity\ArticlePanier;
use App\Repository\Trait\SoftDeleteRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ArticlePanier>
 */
class ArticlePanierRepository extends ServiceEntityRepository
{
    use SoftDeleteRepositoryTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArticlePanier::class);
    }
}