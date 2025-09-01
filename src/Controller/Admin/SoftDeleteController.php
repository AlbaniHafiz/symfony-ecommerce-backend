<?php

namespace App\Controller\Admin;

use App\Repository\UtilisateurRepository;
use App\Repository\ProduitRepository;
use App\Repository\CommandeRepository;
use App\Repository\CategorieRepository;
use App\Service\SoftDeleteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/soft-delete', name: 'admin_soft_delete_')]
class SoftDeleteController extends AbstractController
{
    public function __construct(
        private UtilisateurRepository $utilisateurRepository,
        private ProduitRepository $produitRepository,
        private CommandeRepository $commandeRepository,
        private CategorieRepository $categorieRepository,
        private SoftDeleteService $softDeleteService
    ) {}

    #[Route('/utilisateurs/deleted', name: 'utilisateurs_deleted', methods: ['GET'])]
    public function utilisateursDeleted(): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $utilisateurs = $this->utilisateurRepository->findDeleted();
        
        $data = [];
        foreach ($utilisateurs as $utilisateur) {
            $data[] = [
                'id' => $utilisateur->getId(),
                'nom' => $utilisateur->getNom(),
                'prenom' => $utilisateur->getPrenom(),
                'email' => $utilisateur->getEmail(),
                'deletedAt' => $utilisateur->getDeletedAt()->format('Y-m-d H:i:s'),
            ];
        }
        
        return $this->json(['utilisateurs' => $data]);
    }

    #[Route('/utilisateurs/{id}/restore', name: 'utilisateur_restore', methods: ['POST'])]
    public function restaurerUtilisateur(int $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $utilisateur = $this->utilisateurRepository->createQueryBuilder('u')
            ->andWhere('u.id = :id')
            ->andWhere('u.deletedAt IS NOT NULL')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
        
        if (!$utilisateur) {
            return $this->json(['message' => 'Utilisateur non trouvé'], 404);
        }
        
        $this->softDeleteService->restore($utilisateur);
        
        return $this->json(['message' => 'Utilisateur restauré avec succès']);
    }

    #[Route('/produits/deleted', name: 'produits_deleted', methods: ['GET'])]
    public function produitsDeleted(): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $produits = $this->produitRepository->findDeleted();
        
        $data = [];
        foreach ($produits as $produit) {
            $data[] = [
                'id' => $produit->getId(),
                'nom' => $produit->getNom(),
                'prix' => $produit->getPrix(),
                'stock' => $produit->getStock(),
                'deletedAt' => $produit->getDeletedAt()->format('Y-m-d H:i:s'),
            ];
        }
        
        return $this->json(['produits' => $data]);
    }

    #[Route('/produits/{id}/restore', name: 'produit_restore', methods: ['POST'])]
    public function restaurerProduit(int $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $produit = $this->produitRepository->createQueryBuilder('p')
            ->andWhere('p.id = :id')
            ->andWhere('p.deletedAt IS NOT NULL')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
        
        if (!$produit) {
            return $this->json(['message' => 'Produit non trouvé'], 404);
        }
        
        $this->softDeleteService->restore($produit);
        
        return $this->json(['message' => 'Produit restauré avec succès']);
    }

    #[Route('/commandes/deleted', name: 'commandes_deleted', methods: ['GET'])]
    public function commandesDeleted(): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $commandes = $this->commandeRepository->findDeleted();
        
        $data = [];
        foreach ($commandes as $commande) {
            $data[] = [
                'id' => $commande->getId(),
                'utilisateur' => $commande->getUtilisateur() ? $commande->getUtilisateur()->getNomComplet() : 'N/A',
                'statut' => $commande->getStatut(),
                'total' => $commande->getTotal(),
                'dateCreation' => $commande->getDateCreation()->format('Y-m-d H:i:s'),
                'deletedAt' => $commande->getDeletedAt()->format('Y-m-d H:i:s'),
            ];
        }
        
        return $this->json(['commandes' => $data]);
    }

    #[Route('/commandes/{id}/restore', name: 'commande_restore', methods: ['POST'])]
    public function restaurerCommande(int $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $commande = $this->commandeRepository->createQueryBuilder('c')
            ->andWhere('c.id = :id')
            ->andWhere('c.deletedAt IS NOT NULL')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
        
        if (!$commande) {
            return $this->json(['message' => 'Commande non trouvée'], 404);
        }
        
        $this->softDeleteService->restore($commande);
        
        return $this->json(['message' => 'Commande restaurée avec succès']);
    }

    #[Route('/categories/deleted', name: 'categories_deleted', methods: ['GET'])]
    public function categoriesDeleted(): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $categories = $this->categorieRepository->findDeleted();
        
        $data = [];
        foreach ($categories as $categorie) {
            $data[] = [
                'id' => $categorie->getId(),
                'nom' => $categorie->getNom(),
                'active' => $categorie->isActive(),
                'deletedAt' => $categorie->getDeletedAt()->format('Y-m-d H:i:s'),
            ];
        }
        
        return $this->json(['categories' => $data]);
    }

    #[Route('/categories/{id}/restore', name: 'categorie_restore', methods: ['POST'])]
    public function restaurerCategorie(int $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $categorie = $this->categorieRepository->createQueryBuilder('c')
            ->andWhere('c.id = :id')
            ->andWhere('c.deletedAt IS NOT NULL')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
        
        if (!$categorie) {
            return $this->json(['message' => 'Catégorie non trouvée'], 404);
        }
        
        $this->softDeleteService->restore($categorie);
        
        return $this->json(['message' => 'Catégorie restaurée avec succès']);
    }
}