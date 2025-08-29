<?php

namespace App\Command;

use App\Entity\Utilisateur;
use App\Entity\Categorie;
use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:init-data',
    description: 'Initialise les données de test'
)]
class InitDataCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Initialisation des données de test');

        // Créer un admin
        $admin = new Utilisateur();
        $admin->setNom('Admin')
              ->setPrenom('Super')
              ->setEmail('admin@test.com')
              ->setTelephone('+221 77 123 45 67')
              ->setAdresse('123 Rue Admin, Dakar')
              ->setRoles(['ROLE_ADMIN']);

        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'admin123');
        $admin->setMotDePasse($hashedPassword);

        $this->entityManager->persist($admin);

        // Créer un étudiant
        $etudiant = new Utilisateur();
        $etudiant->setNom('Diallo')
                 ->setPrenom('Fatou')
                 ->setEmail('fatou@test.com')
                 ->setTelephone('+221 76 234 56 78')
                 ->setAdresse('456 Cité Universitaire, Dakar')
                 ->setRoles(['ROLE_ETUDIANT']);

        $hashedPassword = $this->passwordHasher->hashPassword($etudiant, 'etudiant123');
        $etudiant->setMotDePasse($hashedPassword);

        $this->entityManager->persist($etudiant);

        // Créer des catégories
        $categorie1 = new Categorie();
        $categorie1->setNom('Électronique')
                   ->setDescription('Appareils électroniques et accessoires')
                   ->setImage('electronique.jpg');

        $categorie2 = new Categorie();
        $categorie2->setNom('Papeterie')
                   ->setDescription('Fournitures scolaires et de bureau')
                   ->setImage('papeterie.jpg');

        $this->entityManager->persist($categorie1);
        $this->entityManager->persist($categorie2);

        // Créer des produits
        $produit1 = new Produit();
        $produit1->setNom('Écouteurs Bluetooth')
                 ->setDescription('Écouteurs sans fil avec réduction de bruit')
                 ->setPrix('15000')
                 ->setStock(25)
                 ->setCategorie($categorie1)
                 ->setImage('ecouteurs.jpg');

        $produit2 = new Produit();
        $produit2->setNom('Pack cahiers A4')
                 ->setDescription('Lot de 5 cahiers 200 pages pour cours')
                 ->setPrix('3500')
                 ->setStock(100)
                 ->setCategorie($categorie2)
                 ->setImage('cahiers.jpg');

        $this->entityManager->persist($produit1);
        $this->entityManager->persist($produit2);

        $this->entityManager->flush();

        $io->success('Données de test créées avec succès !');
        $io->note('Utilisateur admin: admin@test.com / admin123');
        $io->note('Utilisateur étudiant: fatou@test.com / etudiant123');

        return Command::SUCCESS;
    }
}