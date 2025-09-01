<?php

namespace App\Controller\Admin;

use App\Entity\Produit;
use App\Repository\ProduitRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/produits', name: 'admin_produits_')]
class ProduitAdminController extends AbstractController
{
    public function __construct(
        private ProduitRepository $produitRepository,
        private CategorieRepository $categorieRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('', name: 'index')]
    public function index(Request $request): Response
    {
        // $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $search = $request->query->get('search', '');
        $category = $request->query->get('category', '');
        $status = $request->query->get('status', '');
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        // Get filtered products
        $produits = $this->produitRepository->findWithFilters($search, $category, $status, $page, $limit);
        $totalProduits = $this->produitRepository->countWithFilters($search, $category, $status);
        $totalPages = ceil($totalProduits / $limit);

        // Get categories for filter
        $categories = $this->categorieRepository->findActives();

        // Statistics
        $stats = [
            'total' => $this->produitRepository->count([]),
            'actifs' => $this->produitRepository->count(['actif' => true]),
            'inactifs' => $this->produitRepository->count(['actif' => false]),
            'rupture' => count($this->produitRepository->findRuptureStock()),
            'categories' => count($categories),
        ];

        return $this->render('admin/produits/index.html.twig', [
            'produits' => $produits,
            'categories' => $categories,
            'stats' => $stats,
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

    #[Route('/nouveau', name: 'new')]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $produit = new Produit();
        $categories = $this->categorieRepository->findActives();

        if ($request->isMethod('POST')) {
            return $this->handleForm($request, $produit, true);
        }

        return $this->render('admin/produits/form.html.twig', [
            'produit' => $produit,
            'categories' => $categories,
            'isEdit' => false,
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(Produit $produit): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Get product statistics
        $stats = $this->produitRepository->getProductStats($produit->getId());

        return $this->render('admin/produits/show.html.twig', [
            'produit' => $produit,
            'stats' => $stats,
        ]);
    }

    #[Route('/{id}/modifier', name: 'edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, Produit $produit): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $categories = $this->categorieRepository->findActives();

        if ($request->isMethod('POST')) {
            return $this->handleForm($request, $produit, false);
        }

        return $this->render('admin/produits/form.html.twig', [
            'produit' => $produit,
            'categories' => $categories,
            'isEdit' => true,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Produit $produit): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->request->get('_token'))) {
            // Check if product has orders before deletion
            if ($this->produitRepository->hasOrders($produit->getId())) {
                $this->addFlash('error', 'Ce produit ne peut pas être supprimé car il a des commandes associées.');
            } else {
                $this->entityManager->remove($produit);
                $this->entityManager->flush();
                $this->addFlash('success', 'Produit supprimé avec succès.');
            }
        }

        return $this->redirectToRoute('admin_produits_index');
    }

    #[Route('/{id}/toggle-status', name: 'toggle_status', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function toggleStatus(Request $request, Produit $produit): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('toggle_status'.$produit->getId(), $request->request->get('_token'))) {
            $produit->setActif(!$produit->isActif());
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'newStatus' => $produit->isActif(),
                'message' => $produit->isActif() ? 'Produit activé' : 'Produit désactivé'
            ]);
        }

        return new JsonResponse(['success' => false, 'message' => 'Token CSRF invalide'], 400);
    }

    #[Route('/api/stats', name: 'api_stats', methods: ['GET'])]
    public function getStats(): JsonResponse
    {
        // $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $stats = $this->produitRepository->getDashboardStats();

        return new JsonResponse($stats);
    }

    #[Route('/test', name: 'test')]
    public function test(Request $request): Response
    {
        // Test route without authentication
        $search = '';
        $category = '';
        $status = '';
        $page = 1;
        $limit = 10;

        // Get filtered products
        $produits = $this->produitRepository->findWithFilters($search, $category, $status, $page, $limit);
        $totalProduits = $this->produitRepository->countWithFilters($search, $category, $status);
        $totalPages = ceil($totalProduits / $limit);

        // Get categories for filter
        $categories = $this->categorieRepository->findActives();

        // Statistics
        $stats = [
            'total' => $this->produitRepository->count([]),
            'actifs' => $this->produitRepository->count(['actif' => true]),
            'inactifs' => $this->produitRepository->count(['actif' => false]),
            'rupture' => count($this->produitRepository->findRuptureStock()),
            'categories' => count($categories),
        ];

        return $this->render('admin/produits/simple.html.twig', [
            'produits' => $produits,
            'categories' => $categories,
            'stats' => $stats,
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

    private function handleForm(Request $request, Produit $produit, bool $isNew): Response
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
                $this->entityManager->persist($produit);
            }

            $this->entityManager->flush();

            $this->addFlash('success', $isNew ? 'Produit créé avec succès.' : 'Produit modifié avec succès.');

            return $this->redirectToRoute('admin_produits_show', ['id' => $produit->getId()]);

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur: ' . $e->getMessage());

            $categories = $this->categorieRepository->findActives();
            return $this->render('admin/produits/form.html.twig', [
                'produit' => $produit,
                'categories' => $categories,
                'isEdit' => !$isNew,
                'formData' => $data,
            ]);
        }
    }
}