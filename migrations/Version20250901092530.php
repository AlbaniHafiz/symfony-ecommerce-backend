<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250901092530 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create all tables with soft delete support (deletedAt column)';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article_commande (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, commande_id INTEGER NOT NULL, produit_id INTEGER NOT NULL, quantite INTEGER NOT NULL, prix_unitaire NUMERIC(10, 2) NOT NULL, deleted_at DATETIME DEFAULT NULL, CONSTRAINT FK_3B02521682EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3B025216F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_3B02521682EA2E54 ON article_commande (commande_id)');
        $this->addSql('CREATE INDEX IDX_3B025216F347EFB ON article_commande (produit_id)');
        $this->addSql('CREATE TABLE article_panier (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, panier_id INTEGER NOT NULL, produit_id INTEGER NOT NULL, quantite INTEGER NOT NULL, deleted_at DATETIME DEFAULT NULL, CONSTRAINT FK_4E0B9A72F77D927C FOREIGN KEY (panier_id) REFERENCES panier (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_4E0B9A72F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_4E0B9A72F77D927C ON article_panier (panier_id)');
        $this->addSql('CREATE INDEX IDX_4E0B9A72F347EFB ON article_panier (produit_id)');
        $this->addSql('CREATE TABLE categorie (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, description CLOB DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, active BOOLEAN NOT NULL, date_creation DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL)');
        $this->addSql('CREATE TABLE commande (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, utilisateur_id INTEGER NOT NULL, statut VARCHAR(20) NOT NULL, date_creation DATETIME NOT NULL, date_modification DATETIME NOT NULL, total NUMERIC(10, 2) NOT NULL, adresse_livraison CLOB NOT NULL, deleted_at DATETIME DEFAULT NULL, CONSTRAINT FK_6EEAA67DFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_6EEAA67DFB88E14F ON commande (utilisateur_id)');
        $this->addSql('CREATE TABLE livraison (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, commande_id INTEGER NOT NULL, livreur_id INTEGER DEFAULT NULL, statut VARCHAR(20) NOT NULL, date_attribution DATETIME DEFAULT NULL, date_livraison DATETIME DEFAULT NULL, commentaire CLOB DEFAULT NULL, date_creation DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, CONSTRAINT FK_A60C9F1F82EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_A60C9F1FF8646701 FOREIGN KEY (livreur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A60C9F1F82EA2E54 ON livraison (commande_id)');
        $this->addSql('CREATE INDEX IDX_A60C9F1FF8646701 ON livraison (livreur_id)');
        $this->addSql('CREATE TABLE paiement (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, commande_id INTEGER NOT NULL, montant NUMERIC(10, 2) NOT NULL, methode_paiement VARCHAR(30) NOT NULL, statut VARCHAR(20) NOT NULL, date_transaction DATETIME NOT NULL, reference_transaction VARCHAR(255) DEFAULT NULL, commentaire CLOB DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, CONSTRAINT FK_B1DC7A1E82EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_B1DC7A1E82EA2E54 ON paiement (commande_id)');
        $this->addSql('CREATE TABLE panier (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, utilisateur_id INTEGER NOT NULL, date_creation DATETIME NOT NULL, date_modification DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, CONSTRAINT FK_24CC0DF2FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_24CC0DF2FB88E14F ON panier (utilisateur_id)');
        $this->addSql('CREATE TABLE produit (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, categorie_id INTEGER NOT NULL, nom VARCHAR(150) NOT NULL, description CLOB DEFAULT NULL, prix NUMERIC(10, 2) NOT NULL, image VARCHAR(255) DEFAULT NULL, stock INTEGER NOT NULL, date_creation DATETIME NOT NULL, actif BOOLEAN NOT NULL, deleted_at DATETIME DEFAULT NULL, CONSTRAINT FK_29A5EC27BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_29A5EC27BCF5E72D ON produit (categorie_id)');
        $this->addSql('CREATE TABLE utilisateur (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, telephone VARCHAR(20) NOT NULL, mot_de_passe VARCHAR(255) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , adresse CLOB DEFAULT NULL, date_creation DATETIME NOT NULL, actif BOOLEAN NOT NULL, deleted_at DATETIME DEFAULT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1D1C63B3E7927C74 ON utilisateur (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE article_commande');
        $this->addSql('DROP TABLE article_panier');
        $this->addSql('DROP TABLE categorie');
        $this->addSql('DROP TABLE commande');
        $this->addSql('DROP TABLE livraison');
        $this->addSql('DROP TABLE paiement');
        $this->addSql('DROP TABLE panier');
        $this->addSql('DROP TABLE produit');
        $this->addSql('DROP TABLE utilisateur');
    }
}
