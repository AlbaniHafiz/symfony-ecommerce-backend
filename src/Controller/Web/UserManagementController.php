<?php

namespace App\Controller\Web;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/admin/users', name: 'admin_users_')]
class UserManagementController extends AbstractController
{
    public function __construct(
        private UtilisateurRepository $utilisateurRepository,
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    #[Route('/', name: 'index')]
    public function index(Request $request): Response
    {
        $page = max(1, $request->query->get('page', 1));
        $limit = 20;
        $search = $request->query->get('search', '');
        $role = $request->query->get('role', '');
        $status = $request->query->get('status', '');

        $users = $this->utilisateurRepository->findWithFilters($search, $role, $status, $page, $limit);
        $totalUsers = $this->utilisateurRepository->countWithFilters($search, $role, $status);
        $totalPages = ceil($totalUsers / $limit);

        $stats = [
            'total' => $this->utilisateurRepository->count([]),
            'active' => $this->utilisateurRepository->count(['actif' => true]),
            'customers' => $this->utilisateurRepository->compterParRole('ROLE_ETUDIANT'),
            'sellers' => $this->utilisateurRepository->compterParRole('ROLE_SELLER') ?? 0,
            'admins' => $this->utilisateurRepository->compterParRole('ROLE_ADMIN') ?? 0,
        ];

        return $this->render('admin/users/index.html.twig', [
            'users' => $users,
            'stats' => $stats,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'role' => $role,
            'status' => $status,
        ]);
    }

    #[Route('/create', name: 'create')]
    public function create(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            return $this->handleUserCreation($request);
        }

        return $this->render('admin/users/create.html.twig');
    }

    #[Route('/edit/{id}', name: 'edit')]
    public function edit(Utilisateur $user, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            return $this->handleUserUpdate($user, $request);
        }

        return $this->render('admin/users/edit.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/view/{id}', name: 'view')]
    public function view(Utilisateur $user): Response
    {
        // Get user statistics
        $stats = [
            'orders_count' => count($user->getCommandes()),
            'orders_total' => array_sum(array_map(fn($order) => $order->getTotal(), $user->getCommandes()->toArray())),
            'last_login' => new \DateTime(), // Mock data
            'registration_date' => $user->getDateCreation(),
        ];

        return $this->render('admin/users/view.html.twig', [
            'user' => $user,
            'stats' => $stats,
        ]);
    }

    #[Route('/toggle-status/{id}', name: 'toggle_status', methods: ['POST'])]
    public function toggleStatus(Utilisateur $user): JsonResponse
    {
        try {
            $user->setActif(!$user->isActif());
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'status' => $user->isActif(),
                'message' => $user->isActif() ? 'Utilisateur activé' : 'Utilisateur désactivé'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la modification du statut'
            ], 500);
        }
    }

    #[Route('/bulk-action', name: 'bulk_action', methods: ['POST'])]
    public function bulkAction(Request $request): JsonResponse
    {
        $action = $request->request->get('action');
        $userIds = $request->request->get('user_ids', []);

        if (empty($userIds) || !is_array($userIds)) {
            return new JsonResponse(['success' => false, 'message' => 'Aucun utilisateur sélectionné'], 400);
        }

        try {
            $users = $this->utilisateurRepository->findBy(['id' => $userIds]);
            $count = 0;

            foreach ($users as $user) {
                switch ($action) {
                    case 'activate':
                        $user->setActif(true);
                        $count++;
                        break;
                    case 'deactivate':
                        $user->setActif(false);
                        $count++;
                        break;
                    case 'delete':
                        // Soft delete or mark as deleted
                        $user->setActif(false);
                        $count++;
                        break;
                }
            }

            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => "{$count} utilisateur(s) traité(s)",
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors du traitement: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/api/search', name: 'api_search', methods: ['GET'])]
    public function apiSearch(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');
        
        if (strlen($query) < 2) {
            return new JsonResponse([]);
        }

        $users = $this->utilisateurRepository->searchUsers($query, 10);
        
        $results = array_map(function($user) {
            return [
                'id' => $user->getId(),
                'name' => $user->getNomComplet(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
                'active' => $user->isActif()
            ];
        }, $users);

        return new JsonResponse($results);
    }

    #[Route('/export', name: 'export')]
    public function export(Request $request): Response
    {
        $format = $request->query->get('format', 'csv');
        $users = $this->utilisateurRepository->findAll();

        if ($format === 'csv') {
            return $this->exportCsv($users);
        }

        // Default to JSON export
        return $this->exportJson($users);
    }

    private function handleUserCreation(Request $request): Response
    {
        try {
            $user = new Utilisateur();
            $user->setNom($request->request->get('nom'));
            $user->setPrenom($request->request->get('prenom'));
            $user->setEmail($request->request->get('email'));
            $user->setTelephone($request->request->get('telephone'));
            $user->setAdresse($request->request->get('adresse'));
            
            $roles = $request->request->get('roles', []);
            if (!is_array($roles)) {
                $roles = [$roles];
            }
            $user->setRoles($roles);
            
            $user->setActif($request->request->get('actif', false) === '1');

            // Hash password
            $plainPassword = $request->request->get('password');
            if ($plainPassword) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                $user->setMotDePasse($hashedPassword);
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès');
            return $this->redirectToRoute('admin_users_index');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la création: ' . $e->getMessage());
            return $this->render('admin/users/create.html.twig');
        }
    }

    private function handleUserUpdate(Utilisateur $user, Request $request): Response
    {
        try {
            $user->setNom($request->request->get('nom'));
            $user->setPrenom($request->request->get('prenom'));
            $user->setEmail($request->request->get('email'));
            $user->setTelephone($request->request->get('telephone'));
            $user->setAdresse($request->request->get('adresse'));
            
            $roles = $request->request->get('roles', []);
            if (!is_array($roles)) {
                $roles = [$roles];
            }
            $user->setRoles($roles);
            
            $user->setActif($request->request->get('actif', false) === '1');

            // Update password if provided
            $plainPassword = $request->request->get('password');
            if ($plainPassword) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                $user->setMotDePasse($hashedPassword);
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Utilisateur modifié avec succès');
            return $this->redirectToRoute('admin_users_index');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la modification: ' . $e->getMessage());
            return $this->render('admin/users/edit.html.twig', ['user' => $user]);
        }
    }

    private function exportCsv(array $users): Response
    {
        $filename = 'users_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        $response = new Response();
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        $handle = fopen('php://output', 'w');
        
        // CSV Headers
        fputcsv($handle, ['ID', 'Nom', 'Prénom', 'Email', 'Téléphone', 'Rôles', 'Actif', 'Date Création']);
        
        foreach ($users as $user) {
            fputcsv($handle, [
                $user->getId(),
                $user->getNom(),
                $user->getPrenom(),
                $user->getEmail(),
                $user->getTelephone(),
                implode(', ', $user->getRoles()),
                $user->isActif() ? 'Oui' : 'Non',
                $user->getDateCreation()->format('Y-m-d H:i:s')
            ]);
        }

        fclose($handle);
        
        return $response;
    }

    private function exportJson(array $users): Response
    {
        $data = array_map(function($user) {
            return [
                'id' => $user->getId(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'email' => $user->getEmail(),
                'telephone' => $user->getTelephone(),
                'roles' => $user->getRoles(),
                'actif' => $user->isActif(),
                'date_creation' => $user->getDateCreation()->format('Y-m-d H:i:s')
            ];
        }, $users);

        $response = new JsonResponse($data);
        $response->headers->set('Content-Disposition', 'attachment; filename="users_export_' . date('Y-m-d_H-i-s') . '.json"');
        
        return $response;
    }
}