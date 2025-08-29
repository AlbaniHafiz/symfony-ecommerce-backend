<?php

namespace App\DataFixtures;

use App\Entity\Utilisateur;
use App\Entity\Categorie;
use App\Entity\Produit;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // Créer des utilisateurs de test
        $admin = new Utilisateur();
        $admin->setNom('Admin')
              ->setPrenom('Super')
              ->setEmail('admin@ecommerce.test')
              ->setTelephone('+221 77 123 45 67')
              ->setAdresse('123 Rue de l\'Administration, Dakar')
              ->setRoles(['ROLE_ADMIN']);

        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'admin123');
        $admin->setMotDePasse($hashedPassword);

        $etudiant = new Utilisateur();
        $etudiant->setNom('Diallo')
                 ->setPrenom('Fatou')
                 ->setEmail('fatou.diallo@etudiant.test')
                 ->setTelephone('+221 76 234 56 78')
                 ->setAdresse('456 Cité Universitaire, Dakar')
                 ->setRoles(['ROLE_ETUDIANT']);

        $hashedPassword = $this->passwordHasher->hashPassword($etudiant, 'etudiant123');
        $etudiant->setMotDePasse($hashedPassword);

        $livreur = new Utilisateur();
        $livreur->setNom('Ndiaye')
               ->setPrenom('Moussa')
               ->setEmail('moussa.ndiaye@livreur.test')
               ->setTelephone('+221 78 345 67 89')
               ->setAdresse('789 Quartier Liberté 6, Dakar')
               ->setRoles(['ROLE_LIVREUR']);

        $hashedPassword = $this->passwordHasher->hashPassword($livreur, 'livreur123');
        $livreur->setMotDePasse($hashedPassword);

        $manager->persist($admin);
        $manager->persist($etudiant);
        $manager->persist($livreur);

        // Créer des catégories
        $categorie1 = new Categorie();
        $categorie1->setNom('Électronique')
                   ->setDescription('Appareils électroniques et accessoires pour étudiants')
                   ->setImage('electronique.jpg');

        $categorie2 = new Categorie();
        $categorie2->setNom('Papeterie')
                   ->setDescription('Fournitures scolaires et de bureau')
                   ->setImage('papeterie.jpg');

        $categorie3 = new Categorie();
        $categorie3->setNom('Alimentation')
                   ->setDescription('Snacks et boissons pour étudiants')
                   ->setImage('alimentation.jpg');

        $categorie4 = new Categorie();
        $categorie4->setNom('Vêtements')
                   ->setDescription('Vêtements décontractés pour étudiants')
                   ->setImage('vetements.jpg');

        $manager->persist($categorie1);
        $manager->persist($categorie2);
        $manager->persist($categorie3);
        $manager->persist($categorie4);

        // Créer des produits
        $produits = [
            // Électronique
            ['Écouteurs Bluetooth', 'Écouteurs sans fil avec réduction de bruit', '15000', 25, $categorie1, 'ecouteurs.jpg'],
            ['Clé USB 32GB', 'Clé USB haute vitesse pour stocker vos cours', '8000', 50, $categorie1, 'cle-usb.jpg'],
            ['Chargeur portable', 'Batterie externe 10000mAh pour smartphone', '12000', 30, $categorie1, 'chargeur.jpg'],
            ['Souris sans fil', 'Souris ergonomique pour ordinateur portable', '6000', 40, $categorie1, 'souris.jpg'],

            // Papeterie
            ['Pack cahiers A4', 'Lot de 5 cahiers 200 pages pour cours', '3500', 100, $categorie2, 'cahiers.jpg'],
            ['Stylos multicolores', 'Set de 10 stylos de différentes couleurs', '2500', 75, $categorie2, 'stylos.jpg'],
            ['Calculatrice scientifique', 'Calculatrice pour études scientifiques', '18000', 20, $categorie2, 'calculatrice.jpg'],
            ['Agenda étudiant', 'Agenda 2024-2025 spécial université', '4000', 60, $categorie2, 'agenda.jpg'],

            // Alimentation
            ['Biscuits énergie', 'Biscuits riches en énergie pour révisions', '1500', 200, $categorie3, 'biscuits.jpg'],
            ['Café soluble', 'Café instantané pour rester éveillé', '3000', 80, $categorie3, 'cafe.jpg'],
            ['Barres céréales', 'Pack de 6 barres aux céréales et fruits', '2800', 150, $categorie3, 'barres.jpg'],
            ['Jus de fruits', 'Jus naturels variés 500ml', '1200', 120, $categorie3, 'jus.jpg'],

            // Vêtements
            ['T-shirt université', 'T-shirt avec logo de l\'université', '8500', 45, $categorie4, 'tshirt.jpg'],
            ['Casquette', 'Casquette ajustable style décontracté', '5500', 35, $categorie4, 'casquette.jpg'],
            ['Sac à dos', 'Sac à dos spacieux pour livres et ordinateur', '22000', 25, $categorie4, 'sac.jpg'],
            ['Sweat-shirt', 'Sweat-shirt confortable pour campus', '15000', 30, $categorie4, 'sweat.jpg'],
        ];

        foreach ($produits as [$nom, $description, $prix, $stock, $categorie, $image]) {
            $produit = new Produit();
            $produit->setNom($nom)
                   ->setDescription($description)
                   ->setPrix($prix)
                   ->setStock($stock)
                   ->setCategorie($categorie)
                   ->setImage($image);

            $manager->persist($produit);
        }

        $manager->flush();
    }
}