<?php

namespace App\Controller\Api;

use App\Entity\Utilisateur;
use App\Entity\ArticlePanier;
use App\Repository\PanierRepository;
use App\Repository\ProduitRepository;
use App\Service\SoftDeleteService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/panier', name: 'api_panier_')]
class PanierController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PanierRepository $panierRepository,
        private ProduitRepository $produitRepository,
        private SoftDeleteService $softDeleteService,
        private SerializerInterface $serializer
    ) {}

    #[Route('', name: 'afficher', methods: ['GET'])]
    public function afficher(): JsonResponse
    {
        $utilisateur = $this->getUser();
        
        if (!$utilisateur instanceof Utilisateur) {
            return $this->json(['message' => 'Utilisateur non authentifié'], 401);
        }

        $panier = $this->panierRepository->findPanierActif($utilisateur);

        if (!$panier) {
            return $this->json([
                'panier' => null,
                'articles' => [],
                'total' => '0.00',
                'nombreArticles' => 0
            ]);
        }

        $articles = [];
        foreach ($panier->getArticlesPanier() as $article) {
            $produit = $article->getProduit();
            $articles[] = [
                'id' => $article->getId(),
                'produit' => [
                    'id' => $produit->getId(),
                    'nom' => $produit->getNom(),
                    'prix' => $produit->getPrix(),
                    'image' => $produit->getImage(),
                    'stock' => $produit->getStock()
                ],
                'quantite' => $article->getQuantite(),
                'sousTotal' => $article->getSousTotalString()
            ];
        }

        return $this->json([
            'panier' => [
                'id' => $panier->getId(),
                'dateModification' => $panier->getDateModification()->format('Y-m-d H:i:s')
            ],
            'articles' => $articles,
            'total' => $panier->getTotalString(),
            'nombreArticles' => $panier->getNombreArticles()
        ]);
    }

    #[Route('/ajouter', name: 'ajouter', methods: ['POST'])]
    public function ajouter(Request $request): JsonResponse
    {
        $utilisateur = $this->getUser();
        
        if (!$utilisateur instanceof Utilisateur) {
            return $this->json(['message' => 'Utilisateur non authentifié'], 401);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['produitId']) || !isset($data['quantite'])) {
            return $this->json(['message' => 'Données invalides'], 400);
        }

        $produit = $this->produitRepository->find($data['produitId']);

        if (!$produit || !$produit->isActif() || !$produit->getCategorie()->isActive()) {
            return $this->json(['message' => 'Produit non trouvé'], 404);
        }

        $quantite = (int) $data['quantite'];

        if ($quantite <= 0) {
            return $this->json(['message' => 'La quantité doit être positive'], 400);
        }

        if ($quantite > $produit->getStock()) {
            return $this->json(['message' => 'Stock insuffisant'], 400);
        }

        $panier = $this->panierRepository->findOuCreerPanier($utilisateur);

        // Vérifier si le produit est déjà dans le panier
        $articleExistant = null;
        foreach ($panier->getArticlesPanier() as $article) {
            if ($article->getProduit()->getId() === $produit->getId()) {
                $articleExistant = $article;
                break;
            }
        }

        if ($articleExistant) {
            $nouvelleQuantite = $articleExistant->getQuantite() + $quantite;
            
            if ($nouvelleQuantite > $produit->getStock()) {
                return $this->json(['message' => 'Stock insuffisant'], 400);
            }
            
            $articleExistant->setQuantite($nouvelleQuantite);
        } else {
            $articlePanier = new ArticlePanier();
            $articlePanier->setPanier($panier)
                          ->setProduit($produit)
                          ->setQuantite($quantite);
            
            $this->entityManager->persist($articlePanier);
            $panier->addArticlesPanier($articlePanier);
        }

        $panier->toucherModification();
        $this->entityManager->flush();

        return $this->json(['message' => 'Produit ajouté au panier'], 201);
    }

    #[Route('/modifier', name: 'modifier', methods: ['PUT'])]
    public function modifier(Request $request): JsonResponse
    {
        $utilisateur = $this->getUser();
        
        if (!$utilisateur instanceof Utilisateur) {
            return $this->json(['message' => 'Utilisateur non authentifié'], 401);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['articleId']) || !isset($data['quantite'])) {
            return $this->json(['message' => 'Données invalides'], 400);
        }

        $quantite = (int) $data['quantite'];

        if ($quantite <= 0) {
            return $this->json(['message' => 'La quantité doit être positive'], 400);
        }

        $panier = $this->panierRepository->findPanierActif($utilisateur);

        if (!$panier) {
            return $this->json(['message' => 'Panier non trouvé'], 404);
        }

        $articlePanier = null;
        foreach ($panier->getArticlesPanier() as $article) {
            if ($article->getId() === (int) $data['articleId']) {
                $articlePanier = $article;
                break;
            }
        }

        if (!$articlePanier) {
            return $this->json(['message' => 'Article non trouvé dans le panier'], 404);
        }

        if ($quantite > $articlePanier->getProduit()->getStock()) {
            return $this->json(['message' => 'Stock insuffisant'], 400);
        }

        $articlePanier->setQuantite($quantite);
        $panier->toucherModification();
        
        $this->entityManager->flush();

        return $this->json(['message' => 'Quantité mise à jour']);
    }

    #[Route('/supprimer', name: 'supprimer', methods: ['DELETE'])]
    public function supprimer(Request $request): JsonResponse
    {
        $utilisateur = $this->getUser();
        
        if (!$utilisateur instanceof Utilisateur) {
            return $this->json(['message' => 'Utilisateur non authentifié'], 401);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['articleId'])) {
            return $this->json(['message' => 'ID de l\'article requis'], 400);
        }

        $panier = $this->panierRepository->findPanierActif($utilisateur);

        if (!$panier) {
            return $this->json(['message' => 'Panier non trouvé'], 404);
        }

        $articlePanier = null;
        foreach ($panier->getArticlesPanier() as $article) {
            if ($article->getId() === (int) $data['articleId']) {
                $articlePanier = $article;
                break;
            }
        }

        if (!$articlePanier) {
            return $this->json(['message' => 'Article non trouvé dans le panier'], 404);
        }

        $panier->removeArticlesPanier($articlePanier);
        $this->softDeleteService->softDelete($articlePanier);
        $panier->toucherModification();
        
        $this->entityManager->flush();

        return $this->json(['message' => 'Article supprimé du panier']);
    }

    #[Route('/vider', name: 'vider', methods: ['DELETE'])]
    public function vider(): JsonResponse
    {
        $utilisateur = $this->getUser();
        
        if (!$utilisateur instanceof Utilisateur) {
            return $this->json(['message' => 'Utilisateur non authentifié'], 401);
        }

        $panier = $this->panierRepository->findPanierActif($utilisateur);

        if (!$panier) {
            return $this->json(['message' => 'Panier non trouvé'], 404);
        }

        $articlesPanier = $panier->getArticlesPanier()->toArray();
        $this->softDeleteService->softDeleteBatch($articlesPanier);

        $panier->vider();
        
        $this->entityManager->flush();

        return $this->json(['message' => 'Panier vidé']);
    }
}