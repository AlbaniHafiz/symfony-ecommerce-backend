<?php

namespace App\Repository;

use App\Entity\ArticleCommande;
use App\Repository\Trait\SoftDeleteRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ArticleCommande>
 */
class ArticleCommandeRepository extends ServiceEntityRepository
{
    use SoftDeleteRepositoryTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArticleCommande::class);
    }
}