<?php

namespace App\Controller\Api;

use App\Entity\Utilisateur;
use App\Entity\Commande;
use App\Entity\Paiement;
use App\Entity\Livraison;
use App\Repository\CommandeRepository;
use App\Service\PanierService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/commandes', name: 'api_commandes_')]
class CommandeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CommandeRepository $commandeRepository,
        private PanierService $panierService,
        private SerializerInterface $serializer
    ) {}

    #[Route('', name: 'liste', methods: ['GET'])]
    public function mesCommandes(): JsonResponse
    {
        $utilisateur = $this->getUser();
        
        if (!$utilisateur instanceof Utilisateur) {
            return $this->json(['message' => 'Utilisateur non authentifié'], 401);
        }

        $commandes = $this->commandeRepository->findByUtilisateur($utilisateur);

        $commandesData = [];
        foreach ($commandes as $commande) {
            $commandesData[] = [
                'id' => $commande->getId(),
                'statut' => $commande->getStatut(),
                'statutLibelle' => $commande->getStatutLibelle(),
                'dateCreation' => $commande->getDateCreation()->format('Y-m-d H:i:s'),
                'total' => $commande->getTotal(),
                'adresseLivraison' => $commande->getAdresseLivraison(),
                'nombreArticles' => count($commande->getArticlesCommande())
            ];
        }

        return $this->json(['commandes' => $commandesData]);
    }

    #[Route('/{id}', name: 'detail', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function detailCommande(int $id): JsonResponse
    {
        $utilisateur = $this->getUser();
        
        if (!$utilisateur instanceof Utilisateur) {
            return $this->json(['message' => 'Utilisateur non authentifié'], 401);
        }

        $commande = $this->commandeRepository->find($id);

        if (!$commande || $commande->getUtilisateur() !== $utilisateur) {
            return $this->json(['message' => 'Commande non trouvée'], 404);
        }

        $articles = [];
        foreach ($commande->getArticlesCommande() as $article) {
            $produit = $article->getProduit();
            $articles[] = [
                'id' => $article->getId(),
                'produit' => [
                    'id' => $produit->getId(),
                    'nom' => $produit->getNom(),
                    'image' => $produit->getImage()
                ],
                'quantite' => $article->getQuantite(),
                'prixUnitaire' => $article->getPrixUnitaire(),
                'sousTotal' => $article->getSousTotalString()
            ];
        }

        $livraison = $commande->getLivraison();
        $livraisonData = null;
        if ($livraison) {
            $livraisonData = [
                'id' => $livraison->getId(),
                'statut' => $livraison->getStatut(),
                'statutLibelle' => $livraison->getStatutLibelle(),
                'dateAttribution' => $livraison->getDateAttribution()?->format('Y-m-d H:i:s'),
                'dateLivraison' => $livraison->getDateLivraison()?->format('Y-m-d H:i:s'),
                'livreur' => $livraison->getLivreur() ? $livraison->getLivreur()->getNomComplet() : null,
                'commentaire' => $livraison->getCommentaire()
            ];
        }

        $paiements = [];
        foreach ($commande->getPaiements() as $paiement) {
            $paiements[] = [
                'id' => $paiement->getId(),
                'montant' => $paiement->getMontant(),
                'methodePaiement' => $paiement->getMethodePaiement(),
                'methodePaiementLibelle' => $paiement->getMethodePaiementLibelle(),
                'statut' => $paiement->getStatut(),
                'statutLibelle' => $paiement->getStatutLibelle(),
                'dateTransaction' => $paiement->getDateTransaction()->format('Y-m-d H:i:s'),
                'referenceTransaction' => $paiement->getReferenceTransaction()
            ];
        }

        return $this->json([
            'commande' => [
                'id' => $commande->getId(),
                'statut' => $commande->getStatut(),
                'statutLibelle' => $commande->getStatutLibelle(),
                'dateCreation' => $commande->getDateCreation()->format('Y-m-d H:i:s'),
                'dateModification' => $commande->getDateModification()->format('Y-m-d H:i:s'),
                'total' => $commande->getTotal(),
                'adresseLivraison' => $commande->getAdresseLivraison()
            ],
            'articles' => $articles,
            'livraison' => $livraisonData,
            'paiements' => $paiements
        ]);
    }

    #[Route('', name: 'creer', methods: ['POST'])]
    public function creerCommande(Request $request): JsonResponse
    {
        $utilisateur = $this->getUser();
        
        if (!$utilisateur instanceof Utilisateur) {
            return $this->json(['message' => 'Utilisateur non authentifié'], 401);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['adresseLivraison'])) {
            return $this->json(['message' => 'Adresse de livraison requise'], 400);
        }

        $adresseLivraison = trim($data['adresseLivraison']);
        if (empty($adresseLivraison)) {
            return $this->json(['message' => 'Adresse de livraison invalide'], 400);
        }

        $commande = $this->panierService->convertirEnCommande($utilisateur, $adresseLivraison);

        if (!$commande) {
            return $this->json(['message' => 'Impossible de créer la commande (panier vide ou stock insuffisant)'], 400);
        }

        // Créer automatiquement un paiement en attente
        $paiement = new Paiement();
        $paiement->setCommande($commande)
                 ->setMontant($commande->getTotal())
                 ->setMethodePaiement($data['methodePaiement'] ?? Paiement::METHODE_ESPECES_LIVRAISON);

        // Créer automatiquement une livraison
        $livraison = new Livraison();
        $livraison->setCommande($commande);

        $this->entityManager->persist($paiement);
        $this->entityManager->persist($livraison);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Commande créée avec succès',
            'commande' => [
                'id' => $commande->getId(),
                'statut' => $commande->getStatut(),
                'total' => $commande->getTotal(),
                'dateCreation' => $commande->getDateCreation()->format('Y-m-d H:i:s')
            ]
        ], 201);
    }

    #[Route('/{id}/statut', name: 'mettre_a_jour_statut', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function mettreAJourStatut(int $id, Request $request): JsonResponse
    {
        $utilisateur = $this->getUser();
        
        if (!$utilisateur instanceof Utilisateur) {
            return $this->json(['message' => 'Utilisateur non authentifié'], 401);
        }

        $commande = $this->commandeRepository->find($id);

        if (!$commande || $commande->getUtilisateur() !== $utilisateur) {
            return $this->json(['message' => 'Commande non trouvée'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['statut'])) {
            return $this->json(['message' => 'Statut requis'], 400);
        }

        $nouveauStatut = $data['statut'];

        // Seules certaines transitions sont autorisées pour l'utilisateur
        if ($commande->getStatut() === Commande::STATUT_EN_ATTENTE && $nouveauStatut === Commande::STATUT_ANNULEE) {
            $commande->setStatut($nouveauStatut);
            $this->entityManager->flush();

            return $this->json([
                'message' => 'Statut mis à jour',
                'statut' => $commande->getStatut(),
                'statutLibelle' => $commande->getStatutLibelle()
            ]);
        }

        return $this->json(['message' => 'Transition de statut non autorisée'], 403);
    }

    #[Route('/{id}/annuler', name: 'annuler', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function annulerCommande(int $id): JsonResponse
    {
        $utilisateur = $this->getUser();
        
        if (!$utilisateur instanceof Utilisateur) {
            return $this->json(['message' => 'Utilisateur non authentifié'], 401);
        }

        $commande = $this->commandeRepository->find($id);

        if (!$commande || $commande->getUtilisateur() !== $utilisateur) {
            return $this->json(['message' => 'Commande non trouvée'], 404);
        }

        // Seules les commandes en attente peuvent être annulées par l'utilisateur
        if ($commande->getStatut() !== Commande::STATUT_EN_ATTENTE) {
            return $this->json(['message' => 'Cette commande ne peut plus être annulée'], 403);
        }

        $commande->setStatut(Commande::STATUT_ANNULEE);

        // Remettre en stock les produits
        foreach ($commande->getArticlesCommande() as $article) {
            $produit = $article->getProduit();
            $produit->setStock($produit->getStock() + $article->getQuantite());
        }

        $this->entityManager->flush();

        return $this->json([
            'message' => 'Commande annulée avec succès',
            'statut' => $commande->getStatut(),
            'statutLibelle' => $commande->getStatutLibelle()
        ]);
    }
}