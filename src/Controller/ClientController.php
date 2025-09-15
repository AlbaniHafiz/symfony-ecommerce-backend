<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Utilisateur;
use App\Entity\Categorie;
use App\Entity\Panier;
use App\Entity\ArticlePanier;
use App\Repository\ProduitRepository;
use App\Repository\CategorieRepository;
use App\Repository\PanierRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/client', name: 'client_')]
class ClientController extends AbstractController
{
    public function __construct(
        private ProduitRepository $produitRepository,
        private CategorieRepository $categorieRepository,
        private PanierRepository $panierRepository,
        private UtilisateurRepository $utilisateurRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('', name: 'homepage')]
    public function homepage(Request $request): Response
    {
        $search = $request->query->get('search', '');
        $category = $request->query->get('category', '');
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 12;

        // Get filtered products
        $produits = $this->produitRepository->findWithFilters($search, $category, 'actif', $page, $limit);
        $totalProduits = $this->produitRepository->countWithFilters($search, $category, 'actif');
        $totalPages = ceil($totalProduits / $limit);

        // Get categories for filter
        $categories = $this->categorieRepository->findActives();

        // Featured products
        $produitsVedettes = $this->produitRepository->findBy(['actif' => true], ['id' => 'DESC'], 6);

        // Statistics for homepage
        $stats = [
            'totalProduits' => $this->produitRepository->count(['actif' => true]),
            'totalCategories' => count($categories),
            'totalVendeurs' => $this->utilisateurRepository->countByRole('ROLE_VENDEUR'),
        ];

        return $this->render('client/homepage.html.twig', [
            'produits' => $produits,
            'produitsVedettes' => $produitsVedettes,
            'categories' => $categories,
            'stats' => $stats,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalProduits,
            'filters' => [
                'search' => $search,
                'category' => $category,
            ],
        ]);
    }

    #[Route('/produit/{id}', name: 'product_detail', requirements: ['id' => '\d+'])]
    public function productDetail(Produit $produit): Response
    {
        if (!$produit->isActif()) {
            throw $this->createNotFoundException('Produit non disponible');
        }

        // Get related products from same category
        $produitsLies = $this->produitRepository->findBy([
            'categorie' => $produit->getCategorie(),
            'actif' => true
        ], ['id' => 'DESC'], 4);

        // Remove current product from related products
        $produitsLies = array_filter($produitsLies, fn($p) => $p->getId() !== $produit->getId());

        return $this->render('client/product/detail.html.twig', [
            'produit' => $produit,
            'produitsLies' => $produitsLies,
        ]);
    }

    #[Route('/panier', name: 'cart')]
    public function cart(): Response
    {
        $panier = $this->getCurrentCart();
        
        return $this->render('client/cart/index.html.twig', [
            'panier' => $panier,
        ]);
    }

    #[Route('/panier/ajouter/{id}', name: 'cart_add', requirements: ['id' => '\d+'])]
    public function addToCart(Request $request, Produit $produit): JsonResponse
    {
        if (!$produit->isActif()) {
            return $this->json(['success' => false, 'message' => 'Produit non disponible'], 400);
        }

        $quantite = max(1, $request->request->getInt('quantite', 1));
        
        if ($quantite > $produit->getStock()) {
            return $this->json(['success' => false, 'message' => 'Stock insuffisant'], 400);
        }

        $panier = $this->getCurrentCart();
        
        // Check if product already in cart
        foreach ($panier->getArticlesPanier() as $article) {
            if ($article->getProduit()->getId() === $produit->getId()) {
                $nouvelleQuantite = $article->getQuantite() + $quantite;
                if ($nouvelleQuantite > $produit->getStock()) {
                    return $this->json(['success' => false, 'message' => 'Stock insuffisant'], 400);
                }
                $article->setQuantite($nouvelleQuantite);
                $this->entityManager->flush();
                
                return $this->json([
                    'success' => true, 
                    'message' => 'Quantité mise à jour',
                    'cartCount' => $panier->getArticlesPanier()->count()
                ]);
            }
        }

        // Add new item to cart
        $articlePanier = new ArticlePanier();
        $articlePanier->setPanier($panier)
                     ->setProduit($produit)
                     ->setQuantite($quantite)
                     ->setPrix($produit->getPrix());

        $this->entityManager->persist($articlePanier);
        $this->entityManager->flush();

        return $this->json([
            'success' => true, 
            'message' => 'Produit ajouté au panier',
            'cartCount' => $panier->getArticlesPanier()->count()
        ]);
    }

    #[Route('/panier/supprimer/{id}', name: 'cart_remove', requirements: ['id' => '\d+'])]
    public function removeFromCart(ArticlePanier $article): JsonResponse
    {
        $panier = $this->getCurrentCart();
        
        if ($article->getPanier()->getId() !== $panier->getId()) {
            return $this->json(['success' => false, 'message' => 'Article non trouvé'], 404);
        }

        $this->entityManager->remove($article);
        $this->entityManager->flush();

        return $this->json([
            'success' => true, 
            'message' => 'Article supprimé',
            'cartCount' => $panier->getArticlesPanier()->count()
        ]);
    }

    #[Route('/panier/modifier/{id}', name: 'cart_update', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function updateCartItem(Request $request, ArticlePanier $article): JsonResponse
    {
        $panier = $this->getCurrentCart();
        
        if ($article->getPanier()->getId() !== $panier->getId()) {
            return $this->json(['success' => false, 'message' => 'Article non trouvé'], 404);
        }

        $quantite = max(1, $request->request->getInt('quantite', 1));
        
        if ($quantite > $article->getProduit()->getStock()) {
            return $this->json(['success' => false, 'message' => 'Stock insuffisant'], 400);
        }

        $article->setQuantite($quantite);
        $this->entityManager->flush();

        return $this->json([
            'success' => true, 
            'message' => 'Quantité mise à jour',
            'cartCount' => $panier->getArticlesPanier()->count()
        ]);
    }

    #[Route('/inscription', name: 'register')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            
            // Basic validation
            $requiredFields = ['nom', 'prenom', 'email', 'telephone', 'adresse', 'motDePasse'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $this->addFlash('error', "Le champ {$field} est obligatoire.");
                    return $this->render('client/auth/register.html.twig');
                }
            }

            // Check if email already exists
            if ($this->utilisateurRepository->findOneByEmail($data['email'])) {
                $this->addFlash('error', 'Cette adresse email est déjà utilisée.');
                return $this->render('client/auth/register.html.twig');
            }

            $utilisateur = new Utilisateur();
            $utilisateur->setNom($data['nom'])
                       ->setPrenom($data['prenom'])
                       ->setEmail($data['email'])
                       ->setTelephone($data['telephone'])
                       ->setAdresse($data['adresse'])
                       ->setRoles(['ROLE_ETUDIANT']);

            $hashedPassword = $passwordHasher->hashPassword($utilisateur, $data['motDePasse']);
            $utilisateur->setMotDePasse($hashedPassword);

            $this->entityManager->persist($utilisateur);
            $this->entityManager->flush();

            $this->addFlash('success', 'Inscription réussie ! Vous pouvez maintenant vous connecter.');
            return $this->redirectToRoute('client_login');
        }

        return $this->render('client/auth/register.html.twig');
    }

    #[Route('/connexion', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('client/auth/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/deconnexion', name: 'logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/profil', name: 'profile')]
    public function profile(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ETUDIANT');
        
        $utilisateur = $this->getUser();
        
        return $this->render('client/profile/index.html.twig', [
            'utilisateur' => $utilisateur,
        ]);
    }

    #[Route('/commandes', name: 'orders')]
    public function orders(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ETUDIANT');
        
        $utilisateur = $this->getUser();
        $commandes = $utilisateur->getCommandes();
        
        return $this->render('client/orders/index.html.twig', [
            'commandes' => $commandes,
        ]);
    }

    private function getCurrentCart(): Panier
    {
        // For now, we'll use session-based cart (can be improved later with user-based cart)
        $session = $this->container->get('request_stack')->getCurrentRequest()->getSession();
        $cartId = $session->get('cart_id');
        
        if ($cartId) {
            $panier = $this->panierRepository->find($cartId);
            if ($panier) {
                return $panier;
            }
        }

        // Create new cart
        $panier = new Panier();
        
        $this->entityManager->persist($panier);
        $this->entityManager->flush();
        
        $session->set('cart_id', $panier->getId());
        
        return $panier;
    }
}