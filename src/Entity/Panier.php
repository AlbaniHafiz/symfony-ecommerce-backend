<?php

namespace App\Entity;

use App\Repository\PanierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PanierRepository::class)]
#[ORM\Table(name: 'panier')]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'paniers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'L\'utilisateur est obligatoire.')]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateModification = null;

    #[ORM\OneToMany(mappedBy: 'panier', targetEntity: ArticlePanier::class, cascade: ['persist', 'remove'])]
    private Collection $articlesPanier;

    public function __construct()
    {
        $this->articlesPanier = new ArrayCollection();
        $this->dateCreation = new \DateTime();
        $this->dateModification = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getDateModification(): ?\DateTimeInterface
    {
        return $this->dateModification;
    }

    public function setDateModification(\DateTimeInterface $dateModification): static
    {
        $this->dateModification = $dateModification;
        return $this;
    }

    /**
     * @return Collection<int, ArticlePanier>
     */
    public function getArticlesPanier(): Collection
    {
        return $this->articlesPanier;
    }

    public function addArticlesPanier(ArticlePanier $articlesPanier): static
    {
        if (!$this->articlesPanier->contains($articlesPanier)) {
            $this->articlesPanier->add($articlesPanier);
            $articlesPanier->setPanier($this);
        }

        return $this;
    }

    public function removeArticlesPanier(ArticlePanier $articlesPanier): static
    {
        if ($this->articlesPanier->removeElement($articlesPanier)) {
            // set the owning side to null (unless already changed)
            if ($articlesPanier->getPanier() === $this) {
                $articlesPanier->setPanier(null);
            }
        }

        return $this;
    }

    public function calculerTotal(): float
    {
        $total = 0;
        foreach ($this->articlesPanier as $article) {
            $total += $article->getProduit()->getPrixFloat() * $article->getQuantite();
        }
        return $total;
    }

    public function getTotalString(): string
    {
        return number_format($this->calculerTotal(), 2, '.', '');
    }

    public function getNombreArticles(): int
    {
        $total = 0;
        foreach ($this->articlesPanier as $article) {
            $total += $article->getQuantite();
        }
        return $total;
    }

    public function estVide(): bool
    {
        return $this->articlesPanier->isEmpty();
    }

    public function vider(): static
    {
        $this->articlesPanier->clear();
        $this->dateModification = new \DateTime();
        return $this;
    }

    public function toucherModification(): static
    {
        $this->dateModification = new \DateTime();
        return $this;
    }
}