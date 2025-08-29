<?php

namespace App\Controller\Api;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api', name: 'api_')]
class AuthentificationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UtilisateurRepository $utilisateurRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator,
        private SerializerInterface $serializer
    ) {}

    #[Route('/inscription', name: 'inscription', methods: ['POST'])]
    public function inscription(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['message' => 'Données JSON invalides'], 400);
        }

        // Vérifier si l'email existe déjà
        $existingUser = $this->utilisateurRepository->findOneByEmail($data['email'] ?? '');
        if ($existingUser) {
            return $this->json(['message' => 'Cette adresse email est déjà utilisée'], 409);
        }

        $utilisateur = new Utilisateur();
        $utilisateur->setNom($data['nom'] ?? '')
                   ->setPrenom($data['prenom'] ?? '')
                   ->setEmail($data['email'] ?? '')
                   ->setTelephone($data['telephone'] ?? '')
                   ->setAdresse($data['adresse'] ?? null);

        // Hasher le mot de passe
        if (isset($data['motDePasse'])) {
            $hashedPassword = $this->passwordHasher->hashPassword($utilisateur, $data['motDePasse']);
            $utilisateur->setMotDePasse($hashedPassword);
        }

        // Validation
        $errors = $this->validator->validate($utilisateur);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['message' => 'Erreurs de validation', 'errors' => $errorMessages], 400);
        }

        $this->entityManager->persist($utilisateur);
        $this->entityManager->flush();

        $userData = $this->serializer->serialize($utilisateur, 'json', ['groups' => ['user:read']]);

        return $this->json([
            'message' => 'Inscription réussie',
            'utilisateur' => json_decode($userData)
        ], 201);
    }

    #[Route('/connexion', name: 'connexion', methods: ['POST'])]
    public function connexion(): JsonResponse
    {
        // Cette route est gérée par le système de sécurité de Symfony
        // En cas de succès, le token JWT sera retourné automatiquement
        // En cas d'échec, une erreur sera retournée
        return $this->json(['message' => 'Connexion réussie']);
    }

    #[Route('/profil', name: 'profil', methods: ['GET'])]
    public function profil(): JsonResponse
    {
        $utilisateur = $this->getUser();
        
        if (!$utilisateur instanceof Utilisateur) {
            return $this->json(['message' => 'Utilisateur non authentifié'], 401);
        }

        $userData = $this->serializer->serialize($utilisateur, 'json', ['groups' => ['user:read']]);

        return $this->json(json_decode($userData));
    }

    #[Route('/profil', name: 'modifier_profil', methods: ['PUT'])]
    public function modifierProfil(Request $request): JsonResponse
    {
        $utilisateur = $this->getUser();
        
        if (!$utilisateur instanceof Utilisateur) {
            return $this->json(['message' => 'Utilisateur non authentifié'], 401);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['message' => 'Données JSON invalides'], 400);
        }

        // Mise à jour des informations
        if (isset($data['nom'])) {
            $utilisateur->setNom($data['nom']);
        }
        if (isset($data['prenom'])) {
            $utilisateur->setPrenom($data['prenom']);
        }
        if (isset($data['telephone'])) {
            $utilisateur->setTelephone($data['telephone']);
        }
        if (isset($data['adresse'])) {
            $utilisateur->setAdresse($data['adresse']);
        }

        // Changement de mot de passe
        if (isset($data['nouveauMotDePasse']) && !empty($data['nouveauMotDePasse'])) {
            // Vérifier l'ancien mot de passe si fourni
            if (isset($data['ancienMotDePasse'])) {
                if (!$this->passwordHasher->isPasswordValid($utilisateur, $data['ancienMotDePasse'])) {
                    return $this->json(['message' => 'Ancien mot de passe incorrect'], 400);
                }
            }
            
            $hashedPassword = $this->passwordHasher->hashPassword($utilisateur, $data['nouveauMotDePasse']);
            $utilisateur->setMotDePasse($hashedPassword);
        }

        // Validation
        $errors = $this->validator->validate($utilisateur);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['message' => 'Erreurs de validation', 'errors' => $errorMessages], 400);
        }

        $this->entityManager->flush();

        $userData = $this->serializer->serialize($utilisateur, 'json', ['groups' => ['user:read']]);

        return $this->json([
            'message' => 'Profil mis à jour avec succès',
            'utilisateur' => json_decode($userData)
        ]);
    }
}