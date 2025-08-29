<?php

namespace App\Service;

use App\Entity\Utilisateur;
use App\Entity\Panier;
use App\Entity\Produit;
use App\Entity\ArticlePanier;
use App\Entity\Commande;
use App\Entity\ArticleCommande;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;

class PanierService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PanierRepository $panierRepository
    ) {}

    /**
     * Obtient ou crée un panier pour l'utilisateur
     */
    public function obtenirPanier(Utilisateur $utilisateur): Panier
    {
        return $this->panierRepository->findOuCreerPanier($utilisateur);
    }

    /**
     * Ajoute un produit au panier
     */
    public function ajouterProduit(Utilisateur $utilisateur, Produit $produit, int $quantite): bool
    {
        if ($quantite <= 0 || $quantite > $produit->getStock()) {
            return false;
        }

        $panier = $this->obtenirPanier($utilisateur);

        // Vérifier si le produit est déjà dans le panier
        $articleExistant = $this->trouverArticlePanier($panier, $produit);

        if ($articleExistant) {
            $nouvelleQuantite = $articleExistant->getQuantite() + $quantite;
            
            if ($nouvelleQuantite > $produit->getStock()) {
                return false;
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

        return true;
    }

    /**
     * Modifie la quantité d'un article dans le panier
     */
    public function modifierQuantite(Utilisateur $utilisateur, int $articleId, int $quantite): bool
    {
        if ($quantite <= 0) {
            return false;
        }

        $panier = $this->panierRepository->findPanierActif($utilisateur);

        if (!$panier) {
            return false;
        }

        $articlePanier = $this->trouverArticleParId($panier, $articleId);

        if (!$articlePanier || $quantite > $articlePanier->getProduit()->getStock()) {
            return false;
        }

        $articlePanier->setQuantite($quantite);
        $panier->toucherModification();
        
        $this->entityManager->flush();

        return true;
    }

    /**
     * Supprime un article du panier
     */
    public function supprimerArticle(Utilisateur $utilisateur, int $articleId): bool
    {
        $panier = $this->panierRepository->findPanierActif($utilisateur);

        if (!$panier) {
            return false;
        }

        $articlePanier = $this->trouverArticleParId($panier, $articleId);

        if (!$articlePanier) {
            return false;
        }

        $panier->removeArticlesPanier($articlePanier);
        $this->entityManager->remove($articlePanier);
        $panier->toucherModification();
        
        $this->entityManager->flush();

        return true;
    }

    /**
     * Vide complètement le panier
     */
    public function viderPanier(Utilisateur $utilisateur): bool
    {
        $panier = $this->panierRepository->findPanierActif($utilisateur);

        if (!$panier) {
            return false;
        }

        foreach ($panier->getArticlesPanier() as $article) {
            $this->entityManager->remove($article);
        }

        $panier->vider();
        $this->entityManager->flush();

        return true;
    }

    /**
     * Convertit le panier en commande
     */
    public function convertirEnCommande(Utilisateur $utilisateur, string $adresseLivraison): ?Commande
    {
        $panier = $this->panierRepository->findPanierActif($utilisateur);

        if (!$panier || $panier->estVide()) {
            return null;
        }

        // Vérifier le stock de tous les articles
        foreach ($panier->getArticlesPanier() as $articlePanier) {
            if ($articlePanier->getQuantite() > $articlePanier->getProduit()->getStock()) {
                return null; // Stock insuffisant
            }
        }

        $commande = new Commande();
        $commande->setUtilisateur($utilisateur)
                 ->setAdresseLivraison($adresseLivraison);

        $total = 0;

        foreach ($panier->getArticlesPanier() as $articlePanier) {
            $articleCommande = new ArticleCommande();
            $articleCommande->setCommande($commande)
                           ->setProduit($articlePanier->getProduit())
                           ->setQuantite($articlePanier->getQuantite())
                           ->setPrixUnitaire($articlePanier->getProduit()->getPrix());

            $this->entityManager->persist($articleCommande);
            $commande->addArticleCommande($articleCommande);

            $total += $articleCommande->getSousTotal();

            // Décrémenter le stock
            $produit = $articlePanier->getProduit();
            $produit->setStock($produit->getStock() - $articlePanier->getQuantite());
        }

        $commande->setTotal((string) number_format($total, 2, '.', ''));

        $this->entityManager->persist($commande);

        // Vider le panier
        foreach ($panier->getArticlesPanier() as $article) {
            $this->entityManager->remove($article);
        }
        $panier->vider();

        $this->entityManager->flush();

        return $commande;
    }

    /**
     * Trouve un article dans le panier par produit
     */
    private function trouverArticlePanier(Panier $panier, Produit $produit): ?ArticlePanier
    {
        foreach ($panier->getArticlesPanier() as $article) {
            if ($article->getProduit()->getId() === $produit->getId()) {
                return $article;
            }
        }
        return null;
    }

    /**
     * Trouve un article dans le panier par ID
     */
    private function trouverArticleParId(Panier $panier, int $articleId): ?ArticlePanier
    {
        foreach ($panier->getArticlesPanier() as $article) {
            if ($article->getId() === $articleId) {
                return $article;
            }
        }
        return null;
    }
}