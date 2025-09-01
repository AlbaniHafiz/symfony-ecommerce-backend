<?php

namespace App\Controller\Admin;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/admin', name: 'admin_')]
class DashboardController extends AbstractController
{
    public function __construct(
        private UtilisateurRepository $utilisateurRepository,
        private CommandeRepository $commandeRepository,
        private ProduitRepository $produitRepository,
        private CategorieRepository $categorieRepository
    ) {}

    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si l'utilisateur est déjà connecté et est admin, rediriger vers le dashboard
        $user = $this->getUser();
        if ($user instanceof Utilisateur && $user->estAdmin()) {
            return $this->redirectToRoute('admin_dashboard');
        }

        // Obtenir l'erreur de connexion s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();
        // Dernier nom d'utilisateur saisi par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('admin/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'logout')]
    public function logout(): void
    {
        throw new \LogicException('Cette méthode peut être vide - elle sera interceptée par la clé de déconnexion de votre firewall.');
    }

    #[Route('/', name: 'dashboard')]
    public function dashboard(): Response
    {
        // $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Statistiques générales
        $nombreUtilisateurs = $this->utilisateurRepository->compterParRole('ROLE_ETUDIANT');
        $nombreLivreurs = $this->utilisateurRepository->compterParRole('ROLE_LIVREUR');
        $nombreProduits = count($this->produitRepository->findActifs());
        $nombreCategories = count($this->categorieRepository->findActives());

        // Commandes
        $commandesAujourdhui = $this->commandeRepository->findAujourdhui();
        $chiffreAffairesMois = $this->commandeRepository->getChiffreAffairesMois();
        $statistiquesCommandes = $this->commandeRepository->compterParStatut();

        // Commandes récentes
        $commandesRecentes = $this->commandeRepository->findRecentes(10);

        // Produits en rupture
        $produitsRupture = $this->produitRepository->findRuptureStock();

        return $this->render('admin/dashboard.html.twig', [
            'nombreUtilisateurs' => $nombreUtilisateurs,
            'nombreLivreurs' => $nombreLivreurs,
            'nombreProduits' => $nombreProduits,
            'nombreCategories' => $nombreCategories,
            'commandesAujourdhui' => count($commandesAujourdhui),
            'chiffreAffairesMois' => $chiffreAffairesMois,
            'statistiquesCommandes' => $statistiquesCommandes,
            'commandesRecentes' => $commandesRecentes,
            'produitsRupture' => $produitsRupture,
        ]);
    }

    #[Route('/utilisateurs', name: 'utilisateurs')]
    public function utilisateurs(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $utilisateurs = $this->utilisateurRepository->findActifs();

        return $this->render('admin/utilisateurs.html.twig', [
            'utilisateurs' => $utilisateurs,
        ]);
    }

    #[Route('/commandes', name: 'commandes')]
    public function commandes(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $commandes = $this->commandeRepository->findBy([], ['dateCreation' => 'DESC']);

        return $this->render('admin/commandes.html.twig', [
            'commandes' => $commandes,
        ]);
    }

    #[Route('/produits', name: 'produits')]
    public function produits(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $produits = $this->produitRepository->findBy([], ['nom' => 'ASC']);

        return $this->render('admin/produits.html.twig', [
            'produits' => $produits,
        ]);
    }
}