<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
#[ORM\Table(name: 'produit')]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: 'Le nom du produit est obligatoire.')]
    #[Assert\Length(max: 150, maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'Le prix est obligatoire.')]
    #[Assert\PositiveOrZero(message: 'Le prix doit être positif.')]
    private ?string $prix = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Le stock est obligatoire.')]
    #[Assert\PositiveOrZero(message: 'Le stock doit être positif ou nul.')]
    private ?int $stock = 0;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'La catégorie est obligatoire.')]
    private ?Categorie $categorie = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column]
    private bool $actif = true;

    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: ArticleCommande::class)]
    private Collection $articlesCommande;

    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: ArticlePanier::class)]
    private Collection $articlesPanier;

    public function __construct()
    {
        $this->articlesCommande = new ArrayCollection();
        $this->articlesPanier = new ArrayCollection();
        $this->dateCreation = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getPrix(): ?string
    {
        return $this->prix;
    }

    public function setPrix(string $prix): static
    {
        $this->prix = $prix;
        return $this;
    }

    public function getPrixFloat(): float
    {
        return (float) $this->prix;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): static
    {
        $this->stock = $stock;
        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): static
    {
        $this->categorie = $categorie;
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

    public function isActif(): bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;
        return $this;
    }

    /**
     * @return Collection<int, ArticleCommande>
     */
    public function getArticlesCommande(): Collection
    {
        return $this->articlesCommande;
    }

    public function addArticlesCommande(ArticleCommande $articlesCommande): static
    {
        if (!$this->articlesCommande->contains($articlesCommande)) {
            $this->articlesCommande->add($articlesCommande);
            $articlesCommande->setProduit($this);
        }

        return $this;
    }

    public function removeArticlesCommande(ArticleCommande $articlesCommande): static
    {
        if ($this->articlesCommande->removeElement($articlesCommande)) {
            // set the owning side to null (unless already changed)
            if ($articlesCommande->getProduit() === $this) {
                $articlesCommande->setProduit(null);
            }
        }

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
            $articlesPanier->setProduit($this);
        }

        return $this;
    }

    public function removeArticlesPanier(ArticlePanier $articlesPanier): static
    {
        if ($this->articlesPanier->removeElement($articlesPanier)) {
            // set the owning side to null (unless already changed)
            if ($articlesPanier->getProduit() === $this) {
                $articlesPanier->setProduit(null);
            }
        }

        return $this;
    }

    public function estEnStock(): bool
    {
        return $this->stock > 0;
    }

    public function __toString(): string
    {
        return $this->nom ?? '';
    }
}