<?php

namespace App\Controller\Admin;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/admin/utilisateurs', name: 'admin_utilisateurs_')]
class UserAdminController extends AbstractController
{
    public function __construct(
        private UtilisateurRepository $utilisateurRepository,
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    #[Route('', name: 'index')]
    public function index(Request $request): Response
    {
        // $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $search = $request->query->get('search', '');
        $role = $request->query->get('role', '');
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 15;

        // Build query
        $qb = $this->utilisateurRepository->createQueryBuilder('u')
            ->orderBy('u.dateCreation', 'DESC');

        if ($search) {
            $qb->andWhere('u.nom LIKE :search OR u.prenom LIKE :search OR u.email LIKE :search')
              ->setParameter('search', '%' . $search . '%');
        }

        if ($role) {
            $qb->andWhere('u.roles LIKE :role')
              ->setParameter('role', '%' . $role . '%');
        }

        // Get total count
        $totalQuery = clone $qb;
        $totalUtilisateurs = $totalQuery->select('COUNT(u.id)')
                                       ->getQuery()
                                       ->getSingleScalarResult();

        // Get paginated results
        $utilisateurs = $qb->setFirstResult(($page - 1) * $limit)
                          ->setMaxResults($limit)
                          ->getQuery()
                          ->getResult();

        $totalPages = ceil($totalUtilisateurs / $limit);

        // Statistics
        $stats = [
            'total' => $this->utilisateurRepository->count([]),
            'etudiants' => $this->utilisateurRepository->countByRole('ROLE_ETUDIANT'),
            'vendeurs' => $this->utilisateurRepository->countByRole('ROLE_VENDEUR'),
            'livreurs' => $this->utilisateurRepository->countByRole('ROLE_LIVREUR'),
            'admins' => $this->utilisateurRepository->countByRole('ROLE_ADMIN'),
        ];

        return $this->render('admin/users/index.html.twig', [
            'utilisateurs' => $utilisateurs,
            'stats' => $stats,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalUtilisateurs,
            'filters' => [
                'search' => $search,
                'role' => $role,
            ],
        ]);
    }

    #[Route('/nouveau', name: 'new')]
    public function new(Request $request): Response
    {
        // $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($request->isMethod('POST')) {
            return $this->handleForm($request, new Utilisateur(), true);
        }

        return $this->render('admin/users/form.html.twig', [
            'utilisateur' => new Utilisateur(),
            'isEdit' => false,
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(Utilisateur $utilisateur): Response
    {
        // $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/users/show.html.twig', [
            'utilisateur' => $utilisateur,
        ]);
    }

    #[Route('/{id}/modifier', name: 'edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, Utilisateur $utilisateur): Response
    {
        // $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($request->isMethod('POST')) {
            return $this->handleForm($request, $utilisateur, false);
        }

        return $this->render('admin/users/form.html.twig', [
            'utilisateur' => $utilisateur,
            'isEdit' => true,
        ]);
    }

    #[Route('/{id}/toggle-status', name: 'toggle_status', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function toggleStatus(Utilisateur $utilisateur): JsonResponse
    {
        // $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $utilisateur->setActif(!$utilisateur->isActif());
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => $utilisateur->isActif() ? 'Utilisateur activé' : 'Utilisateur désactivé',
            'status' => $utilisateur->isActif()
        ]);
    }

    #[Route('/stats', name: 'stats')]
    public function getStats(): JsonResponse
    {
        // $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $stats = [
            'total' => $this->utilisateurRepository->count([]),
            'etudiants' => $this->utilisateurRepository->countByRole('ROLE_ETUDIANT'),
            'vendeurs' => $this->utilisateurRepository->countByRole('ROLE_VENDEUR'),
            'livreurs' => $this->utilisateurRepository->countByRole('ROLE_LIVREUR'),
            'admins' => $this->utilisateurRepository->countByRole('ROLE_ADMIN'),
            'nouveaux_cette_semaine' => $this->utilisateurRepository->countNewThisWeek(),
        ];

        return $this->json($stats);
    }

    private function handleForm(Request $request, Utilisateur $utilisateur, bool $isNew): Response
    {
        $data = $request->request->all();

        try {
            // Validate required fields
            $requiredFields = ['nom', 'prenom', 'email', 'telephone', 'adresse'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new \InvalidArgumentException("Le champ {$field} est obligatoire.");
                }
            }

            // Check unique email (except for current user if editing)
            $existingUser = $this->utilisateurRepository->findOneByEmail($data['email']);
            if ($existingUser && ($isNew || $existingUser->getId() !== $utilisateur->getId())) {
                throw new \InvalidArgumentException("Cette adresse email est déjà utilisée.");
            }

            // Set user data
            $utilisateur->setNom($data['nom'])
                       ->setPrenom($data['prenom'])
                       ->setEmail($data['email'])
                       ->setTelephone($data['telephone'])
                       ->setAdresse($data['adresse']);

            // Set roles
            $roles = $data['roles'] ?? ['ROLE_ETUDIANT'];
            if (!is_array($roles)) {
                $roles = [$roles];
            }
            $utilisateur->setRoles($roles);

            // Set password if provided
            if (!empty($data['motDePasse'])) {
                $hashedPassword = $this->passwordHasher->hashPassword($utilisateur, $data['motDePasse']);
                $utilisateur->setMotDePasse($hashedPassword);
            } elseif ($isNew) {
                throw new \InvalidArgumentException("Le mot de passe est obligatoire pour un nouvel utilisateur.");
            }

            // Set active status
            $utilisateur->setActif(isset($data['actif']));

            if ($isNew) {
                $this->entityManager->persist($utilisateur);
            }

            $this->entityManager->flush();

            $this->addFlash('success', $isNew ? 'Utilisateur créé avec succès.' : 'Utilisateur modifié avec succès.');

            return $this->redirectToRoute('admin_utilisateurs_show', ['id' => $utilisateur->getId()]);

        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            
            return $this->render('admin/users/form.html.twig', [
                'utilisateur' => $utilisateur,
                'isEdit' => !$isNew,
                'errors' => [$e->getMessage()],
            ]);
        }
    }
}