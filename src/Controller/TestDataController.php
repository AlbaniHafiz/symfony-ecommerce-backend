<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Entity\Produit;
use App\Entity\Categorie;
use App\Entity\Commande;
use App\Entity\ArticleCommande;
use App\Repository\UtilisateurRepository;
use App\Repository\ProduitRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/test-data', name: 'test_data_')]
class TestDataController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UtilisateurRepository $utilisateurRepository,
        private ProduitRepository $produitRepository,
        private CategorieRepository $categorieRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('test_data/index.html.twig', [
            'stats' => [
                'users' => $this->utilisateurRepository->count([]),
                'products' => $this->produitRepository->count([]),
                'categories' => $this->categorieRepository->count([]),
            ]
        ]);
    }

    #[Route('/generate-users/{count}', name: 'generate_users', requirements: ['count' => '\d+'], methods: ['POST'])]
    public function generateUsers(int $count = 10): JsonResponse
    {
        $generated = 0;
        $names = [
            ['nom' => 'Diop', 'prenom' => 'Aminata'],
            ['nom' => 'Fall', 'prenom' => 'Ousmane'],
            ['nom' => 'Sow', 'prenom' => 'Mariama'],
            ['nom' => 'Gueye', 'prenom' => 'Mamadou'],
            ['nom' => 'Ndiaye', 'prenom' => 'Fatima'],
            ['nom' => 'Sarr', 'prenom' => 'Ibrahima'],
            ['nom' => 'Diouf', 'prenom' => 'Aissatou'],
            ['nom' => 'Ba', 'prenom' => 'Cheikh'],
            ['nom' => 'Sy', 'prenom' => 'Khadija'],
            ['nom' => 'Cisse', 'prenom' => 'Alioune'],
        ];

        $roles = [
            ['ROLE_ETUDIANT'],
            ['ROLE_VENDEUR'],
            ['ROLE_LIVREUR'],
        ];

        for ($i = 0; $i < $count && $i < 50; $i++) {
            $nameData = $names[$i % count($names)];
            $role = $roles[array_rand($roles)];
            
            $utilisateur = new Utilisateur();
            $utilisateur->setNom($nameData['nom'])
                       ->setPrenom($nameData['prenom'])
                       ->setEmail(strtolower($nameData['prenom'] . '.' . $nameData['nom'] . ($i > 9 ? $i : '') . '@etudiant.test'))
                       ->setTelephone('+221 7' . rand(6, 8) . ' ' . rand(100, 999) . ' ' . rand(10, 99) . ' ' . rand(10, 99))
                       ->setAdresse(rand(1, 999) . ' Rue de ' . ['Dakar', 'Thiès', 'Saint-Louis', 'Kaolack', 'Ziguinchor'][array_rand(['Dakar', 'Thiès', 'Saint-Louis', 'Kaolack', 'Ziguinchor'])])
                       ->setRoles($role)
                       ->setActif(true);

            $hashedPassword = $this->passwordHasher->hashPassword($utilisateur, 'password123');
            $utilisateur->setMotDePasse($hashedPassword);

            $this->entityManager->persist($utilisateur);
            $generated++;
        }

        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => "{$generated} utilisateurs générés avec succès",
            'generated' => $generated
        ]);
    }

    #[Route('/generate-products/{count}', name: 'generate_products', requirements: ['count' => '\d+'], methods: ['POST'])]
    public function generateProducts(int $count = 20): JsonResponse
    {
        $categories = $this->categorieRepository->findAll();
        if (empty($categories)) {
            return $this->json([
                'success' => false,
                'message' => 'Aucune catégorie trouvée. Créez des catégories d\'abord.'
            ]);
        }

        $productData = [
            'Électronique' => [
                ['nom' => 'MacBook Pro', 'description' => 'Ordinateur portable haute performance pour étudiants', 'prix' => 1200000],
                ['nom' => 'iPad', 'description' => 'Tablette parfaite pour prendre des notes', 'prix' => 450000],
                ['nom' => 'AirPods Pro', 'description' => 'Écouteurs sans fil avec réduction de bruit', 'prix' => 180000],
                ['nom' => 'Webcam HD', 'description' => 'Caméra pour cours en ligne', 'prix' => 35000],
                ['nom' => 'Disque dur externe', 'description' => 'Stockage 2TB pour projets', 'prix' => 65000],
            ],
            'Papeterie' => [
                ['nom' => 'Lot cahiers premium', 'description' => 'Pack de 10 cahiers haute qualité', 'prix' => 4500],
                ['nom' => 'Stylos Pilot', 'description' => 'Set de 20 stylos de qualité', 'prix' => 3200],
                ['nom' => 'Marqueurs colorés', 'description' => 'Boîte de 48 marqueurs', 'prix' => 5500],
                ['nom' => 'Règle architecte', 'description' => 'Règle triangulaire pour plans', 'prix' => 2800],
                ['nom' => 'Compas professionnel', 'description' => 'Compas de précision pour géométrie', 'prix' => 6500],
            ],
            'Alimentation' => [
                ['nom' => 'Pack snacks énergie', 'description' => 'Assortiment de barres énergétiques', 'prix' => 3500],
                ['nom' => 'Thé vert bio', 'description' => 'Boîte de 50 sachets de thé', 'prix' => 2200],
                ['nom' => 'Smoothie protéine', 'description' => 'Poudre protéinée pour smoothies', 'prix' => 4800],
                ['nom' => 'Fruits secs mélangés', 'description' => 'Mix de noix et fruits séchés', 'prix' => 2800],
                ['nom' => 'Chocolat noir 70%', 'description' => 'Tablettes de chocolat premium', 'prix' => 1500],
            ],
            'Vêtements' => [
                ['nom' => 'Hoodie université', 'description' => 'Sweat à capuche confortable', 'prix' => 18000],
                ['nom' => 'Jean décontracté', 'description' => 'Jean confortable pour tous les jours', 'prix' => 25000],
                ['nom' => 'Baskets running', 'description' => 'Chaussures de sport légères', 'prix' => 35000],
                ['nom' => 'Chemise classique', 'description' => 'Chemise pour présentations', 'prix' => 15000],
                ['nom' => 'Veste imperméable', 'description' => 'Veste coupe-vent légère', 'prix' => 28000],
            ]
        ];

        $generated = 0;
        $totalProducts = [];
        foreach ($productData as $catName => $products) {
            $totalProducts = array_merge($totalProducts, $products);
        }

        for ($i = 0; $i < $count && $i < count($totalProducts); $i++) {
            $productInfo = $totalProducts[$i % count($totalProducts)];
            $category = $categories[array_rand($categories)];
            
            $produit = new Produit();
            $produit->setNom($productInfo['nom'] . ($i > count($totalProducts) - 1 ? ' ' . ($i + 1) : ''))
                   ->setDescription($productInfo['description'])
                   ->setPrix($productInfo['prix'])
                   ->setStock(rand(10, 100))
                   ->setCategorie($category)
                   ->setActif(rand(0, 10) > 1); // 90% chance d'être actif

            $this->entityManager->persist($produit);
            $generated++;
        }

        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => "{$generated} produits générés avec succès",
            'generated' => $generated
        ]);
    }

    #[Route('/generate-orders/{count}', name: 'generate_orders', requirements: ['count' => '\d+'], methods: ['POST'])]
    public function generateOrders(int $count = 15): JsonResponse
    {
        $utilisateurs = $this->utilisateurRepository->findBy(['actif' => true]);
        $produits = $this->produitRepository->findBy(['actif' => true]);

        if (empty($utilisateurs) || empty($produits)) {
            return $this->json([
                'success' => false,
                'message' => 'Pas assez d\'utilisateurs ou de produits pour générer des commandes.'
            ]);
        }

        $statuts = [
            Commande::STATUT_EN_ATTENTE,
            Commande::STATUT_CONFIRMEE,
            Commande::STATUT_EN_PREPARATION,
            Commande::STATUT_EN_LIVRAISON,
            Commande::STATUT_LIVREE,
        ];

        $generated = 0;

        for ($i = 0; $i < $count; $i++) {
            $utilisateur = $utilisateurs[array_rand($utilisateurs)];
            $statut = $statuts[array_rand($statuts)];
            
            $commande = new Commande();
            $commande->setUtilisateur($utilisateur)
                    ->setStatut($statut)
                    ->setAdresseLivraison($utilisateur->getAdresse())
                    ->setTotal('0.00');

            // Create random date in last 30 days
            $randomDays = rand(0, 30);
            $dateCreation = new \DateTime("-{$randomDays} days");
            $commande->setDateCreation($dateCreation);
            $commande->setDateModification($dateCreation);

            $this->entityManager->persist($commande);

            // Add 1-4 articles to the order
            $articleCount = rand(1, 4);
            $total = 0;

            for ($j = 0; $j < $articleCount; $j++) {
                $produit = $produits[array_rand($produits)];
                $quantite = rand(1, 3);
                
                $article = new ArticleCommande();
                $article->setCommande($commande)
                       ->setProduit($produit)
                       ->setQuantite($quantite)
                       ->setPrix($produit->getPrix());

                $total += $produit->getPrixFloat() * $quantite;
                $this->entityManager->persist($article);
            }

            $commande->setTotal(number_format($total, 2, '.', ''));
            $generated++;
        }

        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => "{$generated} commandes générées avec succès",
            'generated' => $generated
        ]);
    }

    #[Route('/reset-data', name: 'reset_data', methods: ['POST'])]
    public function resetData(): JsonResponse
    {
        try {
            // Delete in order to respect foreign key constraints
            $this->entityManager->createQuery('DELETE FROM App\Entity\ArticleCommande')->execute();
            $this->entityManager->createQuery('DELETE FROM App\Entity\ArticlePanier')->execute();
            $this->entityManager->createQuery('DELETE FROM App\Entity\Commande')->execute();
            $this->entityManager->createQuery('DELETE FROM App\Entity\Panier')->execute();
            $this->entityManager->createQuery('DELETE FROM App\Entity\Produit')->execute();
            
            // Keep admin users, delete others
            $this->entityManager->createQuery('DELETE FROM App\Entity\Utilisateur u WHERE u.roles NOT LIKE :role')
                               ->setParameter('role', '%ROLE_ADMIN%')
                               ->execute();

            return $this->json([
                'success' => true,
                'message' => 'Données de test supprimées avec succès (utilisateurs admin conservés)'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ]);
        }
    }
}