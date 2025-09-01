<?php

namespace App\Controller\Api;

use App\Entity\Produit;
use App\Repository\ProduitRepository;
use App\Repository\CategorieRepository;
use App\Service\SoftDeleteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api', name: 'api_')]
class ProduitController extends AbstractController
{
    public function __construct(
        private ProduitRepository $produitRepository,
        private CategorieRepository $categorieRepository,
        private SoftDeleteService $softDeleteService,
        private SerializerInterface $serializer
    ) {}

    #[Route('/produits', name: 'produits_liste', methods: ['GET'])]
    public function liste(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 20);
        $categorieId = $request->query->get('categorie');
        $recherche = $request->query->get('recherche');
        $enStock = $request->query->getBoolean('enStock', false);

        // Filtrage par catégorie
        if ($categorieId) {
            $categorie = $this->categorieRepository->findActiveById($categorieId);
            if (!$categorie) {
                return $this->json(['message' => 'Catégorie non trouvée'], 404);
            }
            $produits = $this->produitRepository->findByCategorie($categorie);
        }
        // Recherche par terme
        elseif ($recherche) {
            $produits = $this->produitRepository->rechercher($recherche);
        }
        // Produits en stock uniquement
        elseif ($enStock) {
            $produits = $this->produitRepository->findEnStock();
        }
        // Tous les produits actifs
        else {
            $produits = $this->produitRepository->findActifs();
        }

        // Pagination simple
        $offset = ($page - 1) * $limit;
        $produitsPagines = array_slice($produits, $offset, $limit);
        $total = count($produits);

        $produitsData = $this->serializer->serialize($produitsPagines, 'json', ['groups' => ['produit:read']]);

        return $this->json([
            'produits' => json_decode($produitsData),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ]);
    }

    #[Route('/produits/{id}', name: 'produit_detail', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function detail(int $id): JsonResponse
    {
        $produit = $this->produitRepository->find($id);

        if (!$produit || !$produit->isActif() || !$produit->getCategorie()->isActive()) {
            return $this->json(['message' => 'Produit non trouvé'], 404);
        }

        $produitData = $this->serializer->serialize($produit, 'json', ['groups' => ['produit:read', 'produit:detail']]);

        return $this->json(json_decode($produitData));
    }

    #[Route('/categories', name: 'categories_liste', methods: ['GET'])]
    public function categories(): JsonResponse
    {
        $categories = $this->categorieRepository->findActives();

        $categoriesData = $this->serializer->serialize($categories, 'json', ['groups' => ['categorie:read']]);

        return $this->json(json_decode($categoriesData));
    }

    #[Route('/produits/rechercher', name: 'produits_rechercher', methods: ['GET'])]
    public function rechercher(Request $request): JsonResponse
    {
        $terme = $request->query->get('q');

        if (!$terme || strlen($terme) < 2) {
            return $this->json(['message' => 'Le terme de recherche doit contenir au moins 2 caractères'], 400);
        }

        $produits = $this->produitRepository->rechercher($terme);

        $produitsData = $this->serializer->serialize($produits, 'json', ['groups' => ['produit:read']]);

        return $this->json([
            'produits' => json_decode($produitsData),
            'terme' => $terme,
            'total' => count($produits)
        ]);
    }

    #[Route('/produits/plus-vendus', name: 'produits_plus_vendus', methods: ['GET'])]
    public function plusVendus(Request $request): JsonResponse
    {
        $limit = $request->query->getInt('limit', 10);
        $produits = $this->produitRepository->findPlusVendus($limit);

        $produitsData = [];
        foreach ($produits as $row) {
            $produit = $row[0]; // Le produit
            $quantiteVendue = $row['quantiteVendue'] ?? 0; // La quantité vendue
            
            $produitArray = json_decode($this->serializer->serialize($produit, 'json', ['groups' => ['produit:read']]), true);
            $produitArray['quantiteVendue'] = $quantiteVendue;
            $produitsData[] = $produitArray;
        }

        return $this->json([
            'produits' => $produitsData,
            'limit' => $limit
        ]);
    }

    #[Route('/produits/nouveautes', name: 'produits_nouveautes', methods: ['GET'])]
    public function nouveautes(Request $request): JsonResponse
    {
        $limit = $request->query->getInt('limit', 10);
        
        // Produits créés dans les 30 derniers jours
        $qb = $this->produitRepository->createQueryBuilder('p');
        $this->produitRepository->addNotDeletedCondition($qb);
        $produits = $qb->innerJoin('p.categorie', 'c')
            ->andWhere('p.actif = :actif')
            ->andWhere('c.active = :active')
            ->andWhere('c.deletedAt IS NULL')
            ->andWhere('p.dateCreation >= :date')
            ->setParameter('actif', true)
            ->setParameter('active', true)
            ->setParameter('date', new \DateTime('-30 days'))
            ->orderBy('p.dateCreation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $produitsData = $this->serializer->serialize($produits, 'json', ['groups' => ['produit:read']]);

        return $this->json([
            'produits' => json_decode($produitsData),
            'limit' => $limit
        ]);
    }

    #[Route('/admin/produits/{id}/soft-delete', name: 'admin_produit_soft_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function softDelete(int $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $produit = $this->produitRepository->find($id);
        
        if (!$produit) {
            return $this->json(['message' => 'Produit non trouvé'], 404);
        }
        
        $this->softDeleteService->softDelete($produit);
        
        return $this->json(['message' => 'Produit supprimé (soft delete)']);
    }
}