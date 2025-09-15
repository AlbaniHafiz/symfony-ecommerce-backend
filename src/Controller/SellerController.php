<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Utilisateur;
use App\Repository\ProduitRepository;
use App\Repository\CategorieRepository;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/seller', name: 'seller_')]
class SellerController extends AbstractController
{
    public function __construct(
        private ProduitRepository $produitRepository,
        private CategorieRepository $categorieRepository,
        private CommandeRepository $commandeRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('', name: 'dashboard')]
    public function dashboard(): Response
    {
        // $this->denyAccessUnlessGranted('ROLE_VENDEUR');
        
        // Mock user for demo
        $vendeur = (object) [
            'nomComplet' => 'Test Seller'
        ];
        
        // Dashboard statistics for vendor
        $stats = [
            'totalProduits' => 0, // Will be implemented when vendor-product relation is added
            'totalCommandes' => 0,
            'chiffreAffaires' => 0,
            'commissionsGagnees' => 0,
        ];

        // Recent products (mock for now)
        $recentProducts = $this->produitRepository->findBy(['actif' => true], ['id' => 'DESC'], 5);
        
        // Recent orders (mock for now)
        $recentOrders = $this->commandeRepository->findBy([], ['dateCreation' => 'DESC'], 5);

        return $this->render('seller/dashboard.html.twig', [
            'vendeur' => $vendeur,
            'stats' => $stats,
            'recentProducts' => $recentProducts,
            'recentOrders' => $recentOrders,
        ]);
    }

    #[Route('/produits', name: 'products')]
    public function products(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_VENDEUR');
        
        $search = $request->query->get('search', '');
        $category = $request->query->get('category', '');
        $status = $request->query->get('status', '');
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 12;

        // For now, show all products (later will be filtered by vendor)
        $produits = $this->produitRepository->findWithFilters($search, $category, $status, $page, $limit);
        $totalProduits = $this->produitRepository->countWithFilters($search, $category, $status);
        $totalPages = ceil($totalProduits / $limit);

        $categories = $this->categorieRepository->findActives();

        return $this->render('seller/products/index.html.twig', [
            'produits' => $produits,
            'categories' => $categories,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalProduits,
            'filters' => [
                'search' => $search,
                'category' => $category,
                'status' => $status,
            ],
        ]);
    }

    #[Route('/produits/nouveau', name: 'products_new')]
    public function newProduct(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_VENDEUR');

        $produit = new Produit();
        $categories = $this->categorieRepository->findActives();

        if ($request->isMethod('POST')) {
            return $this->handleProductForm($request, $produit, true);
        }

        return $this->render('seller/products/form.html.twig', [
            'produit' => $produit,
            'categories' => $categories,
            'isEdit' => false,
        ]);
    }

    #[Route('/produits/{id}', name: 'products_show', requirements: ['id' => '\d+'])]
    public function showProduct(Produit $produit): Response
    {
        $this->denyAccessUnlessGranted('ROLE_VENDEUR');

        return $this->render('seller/products/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[Route('/produits/{id}/modifier', name: 'products_edit', requirements: ['id' => '\d+'])]
    public function editProduct(Request $request, Produit $produit): Response
    {
        $this->denyAccessUnlessGranted('ROLE_VENDEUR');

        $categories = $this->categorieRepository->findActives();

        if ($request->isMethod('POST')) {
            return $this->handleProductForm($request, $produit, false);
        }

        return $this->render('seller/products/form.html.twig', [
            'produit' => $produit,
            'categories' => $categories,
            'isEdit' => true,
        ]);
    }

    #[Route('/commandes', name: 'orders')]
    public function orders(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_VENDEUR');
        
        // For now, show all orders (later will be filtered by vendor products)
        $commandes = $this->commandeRepository->findBy([], ['dateCreation' => 'DESC'], 20);

        return $this->render('seller/orders/index.html.twig', [
            'commandes' => $commandes,
        ]);
    }

    #[Route('/commandes/{id}', name: 'orders_show', requirements: ['id' => '\d+'])]
    public function showOrder($id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_VENDEUR');
        
        $commande = $this->commandeRepository->find($id);
        
        if (!$commande) {
            throw $this->createNotFoundException('Commande non trouvée');
        }

        return $this->render('seller/orders/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/finances', name: 'finances')]
    public function finances(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_VENDEUR');
        
        $vendeur = $this->getUser();
        
        // Mock financial data
        $financialData = [
            'chiffreAffairesTotal' => 125000,
            'commissionsGagnees' => 8750,
            'paiementsRecus' => 112500,
            'paiementsEnAttente' => 12500,
            'tauxCommission' => 7, // 7%
        ];

        // Mock monthly data for charts
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = new \DateTime("-{$i} months");
            $monthlyData[] = [
                'month' => $date->format('M Y'),
                'revenue' => rand(15000, 25000),
                'commission' => rand(1000, 1750),
            ];
        }

        return $this->render('seller/finances/index.html.twig', [
            'vendeur' => $vendeur,
            'financialData' => $financialData,
            'monthlyData' => $monthlyData,
        ]);
    }

    #[Route('/profil', name: 'profile')]
    public function profile(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_VENDEUR');
        
        $vendeur = $this->getUser();

        if ($request->isMethod('POST')) {
            // Handle profile update
            $data = $request->request->all();
            
            if (!empty($data['nom'])) $vendeur->setNom($data['nom']);
            if (!empty($data['prenom'])) $vendeur->setPrenom($data['prenom']);
            if (!empty($data['telephone'])) $vendeur->setTelephone($data['telephone']);
            if (!empty($data['adresse'])) $vendeur->setAdresse($data['adresse']);
            
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Profil mis à jour avec succès.');
            return $this->redirectToRoute('seller_profile');
        }

        return $this->render('seller/profile/index.html.twig', [
            'vendeur' => $vendeur,
        ]);
    }

    private function handleProductForm(Request $request, Produit $produit, bool $isNew): Response
    {
        $data = $request->request->all();

        try {
            // Validate required fields
            $requiredFields = ['nom', 'description', 'prix', 'stock', 'categorieId'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new \InvalidArgumentException("Le champ {$field} est obligatoire.");
                }
            }

            // Find category
            $categorie = $this->categorieRepository->find($data['categorieId']);
            if (!$categorie) {
                throw new \InvalidArgumentException("Catégorie non trouvée.");
            }

            // Set product data
            $produit->setNom($data['nom'])
                   ->setDescription($data['description'])
                   ->setPrix($data['prix'])
                   ->setStock((int)$data['stock'])
                   ->setCategorie($categorie)
                   ->setActif(isset($data['actif']));

            if ($isNew) {
                // For new products, set status to pending approval
                $produit->setActif(false); // Will be activated by admin
                $this->entityManager->persist($produit);
            }

            $this->entityManager->flush();

            $this->addFlash('success', $isNew ? 
                'Produit soumis pour approbation.' : 
                'Produit modifié avec succès.');

            return $this->redirectToRoute('seller_products_show', ['id' => $produit->getId()]);

        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            
            $categories = $this->categorieRepository->findActives();
            return $this->render('seller/products/form.html.twig', [
                'produit' => $produit,
                'categories' => $categories,
                'isEdit' => !$isNew,
                'errors' => [$e->getMessage()],
            ]);
        }
    }
}