<?php

namespace App\Entity;

use App\Entity\Trait\SoftDeleteable;
use App\Repository\ArticlePanierRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArticlePanierRepository::class)]
#[ORM\Table(name: 'article_panier')]
class ArticlePanier
{
    use SoftDeleteable;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'articlesPanier')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Le panier est obligatoire.')]
    private ?Panier $panier = null;

    #[ORM\ManyToOne(inversedBy: 'articlesPanier')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Le produit est obligatoire.')]
    private ?Produit $produit = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'La quantité est obligatoire.')]
    #[Assert\Positive(message: 'La quantité doit être positive.')]
    private ?int $quantite = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPanier(): ?Panier
    {
        return $this->panier;
    }

    public function setPanier(?Panier $panier): static
    {
        $this->panier = $panier;
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

    public function getSousTotal(): float
    {
        return $this->produit ? $this->produit->getPrixFloat() * $this->quantite : 0;
    }

    public function getSousTotalString(): string
    {
        return number_format($this->getSousTotal(), 2, '.', '');
    }
}