<?php

namespace App\Controller\Web;

use App\Repository\UtilisateurRepository;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/enhanced', name: 'admin_enhanced_')]
class AdminDashboardController extends AbstractController
{
    public function __construct(
        private UtilisateurRepository $utilisateurRepository,
        private CommandeRepository $commandeRepository,
        private ProduitRepository $produitRepository,
        private CategorieRepository $categorieRepository
    ) {}

    #[Route('/', name: 'dashboard')]
    public function dashboard(): Response
    {
        // Enhanced statistics for multi-vendor system
        $stats = $this->getEnhancedStats();
        
        return $this->render('admin/enhanced/dashboard.html.twig', [
            'stats' => $stats,
        ]);
    }

    #[Route('/api/stats', name: 'api_stats', methods: ['GET'])]
    public function getStats(): JsonResponse
    {
        $stats = $this->getEnhancedStats();
        return new JsonResponse($stats);
    }

    #[Route('/api/realtime-activity', name: 'api_realtime_activity', methods: ['GET'])]
    public function getRealtimeActivity(): JsonResponse
    {
        // This would typically connect to a real-time system
        // For now, return mock data
        $activities = [
            [
                'type' => 'order',
                'message' => 'Nouvelle commande #' . rand(1000, 9999),
                'timestamp' => time(),
                'user' => 'Client ' . rand(1, 100),
                'amount' => rand(50, 500)
            ],
            [
                'type' => 'user',
                'message' => 'Nouvel utilisateur inscrit',
                'timestamp' => time() - rand(60, 300),
                'user' => 'Utilisateur ' . rand(1, 100)
            ],
            [
                'type' => 'product',
                'message' => 'Stock faible détecté',
                'timestamp' => time() - rand(300, 600),
                'product' => 'Produit ' . rand(1, 50),
                'stock' => rand(1, 5)
            ]
        ];

        return new JsonResponse($activities);
    }

    #[Route('/api/sales-chart', name: 'api_sales_chart', methods: ['GET'])]
    public function getSalesChart(): JsonResponse
    {
        // Generate mock sales data for the last 7 days
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = new \DateTime("-{$i} days");
            $data[] = [
                'date' => $date->format('Y-m-d'),
                'sales' => rand(1000, 5000),
                'orders' => rand(10, 50)
            ];
        }

        return new JsonResponse($data);
    }

    private function getEnhancedStats(): array
    {
        // Enhanced statistics calculation
        $stats = [];
        
        // User statistics
        $stats['users'] = [
            'total' => $this->utilisateurRepository->count([]),
            'customers' => $this->utilisateurRepository->compterParRole('ROLE_ETUDIANT'),
            'sellers' => $this->utilisateurRepository->compterParRole('ROLE_SELLER') ?? 0,
            'admins' => $this->utilisateurRepository->compterParRole('ROLE_ADMIN') ?? 0,
            'active_today' => rand(10, 100) // Mock data
        ];
        
        // Product statistics
        $stats['products'] = [
            'total' => count($this->produitRepository->findAll()),
            'active' => count($this->produitRepository->findActifs()),
            'out_of_stock' => count($this->produitRepository->findRuptureStock()),
            'categories' => count($this->categorieRepository->findActives())
        ];
        
        // Order statistics
        $allOrders = $this->commandeRepository->findAll();
        $todayOrders = $this->commandeRepository->findAujourdhui();
        
        $stats['orders'] = [
            'total' => count($allOrders),
            'today' => count($todayOrders),
            'pending' => count($this->commandeRepository->findBy(['statut' => 'EN_ATTENTE'])),
            'completed' => count($this->commandeRepository->findBy(['statut' => 'LIVREE']))
        ];
        
        // Financial statistics
        $stats['finance'] = [
            'monthly_revenue' => $this->commandeRepository->getChiffreAffairesMois(),
            'daily_revenue' => array_sum(array_map(fn($order) => $order->getTotal(), $todayOrders)),
            'average_order_value' => count($allOrders) > 0 ? 
                array_sum(array_map(fn($order) => $order->getTotal(), $allOrders)) / count($allOrders) : 0,
            'commission_earned' => rand(1000, 5000) // Mock commission data
        ];
        
        // Performance metrics
        $stats['performance'] = [
            'conversion_rate' => rand(2, 8) . '%',
            'bounce_rate' => rand(30, 70) . '%',
            'avg_session_duration' => rand(120, 480) . 's',
            'page_load_time' => rand(800, 2000) . 'ms'
        ];
        
        return $stats;
    }
}