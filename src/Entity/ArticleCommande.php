<?php

namespace App\Entity;

use App\Repository\ArticleCommandeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArticleCommandeRepository::class)]
#[ORM\Table(name: 'article_commande')]
class ArticleCommande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'articlesCommande')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'La commande est obligatoire.')]
    private ?Commande $commande = null;

    #[ORM\ManyToOne(inversedBy: 'articlesCommande')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Le produit est obligatoire.')]
    private ?Produit $produit = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'La quantité est obligatoire.')]
    #[Assert\Positive(message: 'La quantité doit être positive.')]
    private ?int $quantite = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'Le prix unitaire est obligatoire.')]
    #[Assert\PositiveOrZero(message: 'Le prix unitaire doit être positif.')]
    private ?string $prixUnitaire = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(?Commande $commande): static
    {
        $this->commande = $commande;
        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): static
    {
        $this->produit = $produit;
        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;
        return $this;
    }

    public function getPrixUnitaire(): ?string
    {
        return $this->prixUnitaire;
    }

    public function setPrixUnitaire(string $prixUnitaire): static
    {
        $this->prixUnitaire = $prixUnitaire;
        return $this;
    }

    public function getPrixUnitaireFloat(): float
    {
        return (float) $this->prixUnitaire;
    }

    public function getSousTotal(): float
    {
        return $this->getPrixUnitaireFloat() * $this->quantite;
    }

    public function getSousTotalString(): string
    {
        return number_format($this->getSousTotal(), 2, '.', '');
    }
}