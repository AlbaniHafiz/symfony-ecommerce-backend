<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
#[ORM\Table(name: 'commande')]
class Commande
{
    public const STATUT_EN_ATTENTE = 'EN_ATTENTE';
    public const STATUT_CONFIRMEE = 'CONFIRMEE';
    public const STATUT_EN_PREPARATION = 'EN_PREPARATION';
    public const STATUT_EN_LIVRAISON = 'EN_LIVRAISON';
    public const STATUT_LIVREE = 'LIVREE';
    public const STATUT_ANNULEE = 'ANNULEE';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'L\'utilisateur est obligatoire.')]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(length: 20)]
    private string $statut = self::STATUT_EN_ATTENTE;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateModification = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $total = '0.00';

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'L\'adresse de livraison est obligatoire.')]
    private ?string $adresseLivraison = null;

    #[ORM\OneToMany(mappedBy: 'commande', targetEntity: ArticleCommande::class, cascade: ['persist', 'remove'])]
    private Collection $articlesCommande;

    #[ORM\OneToMany(mappedBy: 'commande', targetEntity: Paiement::class)]
    private Collection $paiements;

    #[ORM\OneToOne(mappedBy: 'commande', cascade: ['persist', 'remove'])]
    private ?Livraison $livraison = null;

    public function __construct()
    {
        $this->articlesCommande = new ArrayCollection();
        $this->paiements = new ArrayCollection();
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

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        $this->dateModification = new \DateTime();
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

    public function getTotal(): ?string
    {
        return $this->total;
    }

    public function setTotal(string $total): static
    {
        $this->total = $total;
        return $this;
    }

    public function getTotalFloat(): float
    {
        return (float) $this->total;
    }

    public function getAdresseLivraison(): ?string
    {
        return $this->adresseLivraison;
    }

    public function setAdresseLivraison(string $adresseLivraison): static
    {
        $this->adresseLivraison = $adresseLivraison;
        return $this;
    }

    /**
     * @return Collection<int, ArticleCommande>
     */
    public function getArticlesCommande(): Collection
    {
        return $this->articlesCommande;
    }

    public function addArticleCommande(ArticleCommande $articleCommande): static
    {
        if (!$this->articlesCommande->contains($articleCommande)) {
            $this->articlesCommande->add($articleCommande);
            $articleCommande->setCommande($this);
        }

        return $this;
    }

    public function removeArticleCommande(ArticleCommande $articleCommande): static
    {
        if ($this->articlesCommande->removeElement($articleCommande)) {
            // set the owning side to null (unless already changed)
            if ($articleCommande->getCommande() === $this) {
                $articleCommande->setCommande(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Paiement>
     */
    public function getPaiements(): Collection
    {
        return $this->paiements;
    }

    public function addPaiement(Paiement $paiement): static
    {
        if (!$this->paiements->contains($paiement)) {
            $this->paiements->add($paiement);
            $paiement->setCommande($this);
        }

        return $this;
    }

    public function removePaiement(Paiement $paiement): static
    {
        if ($this->paiements->removeElement($paiement)) {
            // set the owning side to null (unless already changed)
            if ($paiement->getCommande() === $this) {
                $paiement->setCommande(null);
            }
        }

        return $this;
    }

    public function getLivraison(): ?Livraison
    {
        return $this->livraison;
    }

    public function setLivraison(?Livraison $livraison): static
    {
        // unset the owning side of the relation if necessary
        if ($livraison === null && $this->livraison !== null) {
            $this->livraison->setCommande(null);
        }

        // set the owning side of the relation if necessary
        if ($livraison !== null && $livraison->getCommande() !== $this) {
            $livraison->setCommande($this);
        }

        $this->livraison = $livraison;

        return $this;
    }

    public function calculerTotal(): string
    {
        $total = 0;
        foreach ($this->articlesCommande as $article) {
            $total += $article->getPrixUnitaireFloat() * $article->getQuantite();
        }
        $this->total = (string) number_format($total, 2, '.', '');
        return $this->total;
    }

    public function getStatutLibelle(): string
    {
        return match($this->statut) {
            self::STATUT_EN_ATTENTE => 'En attente',
            self::STATUT_CONFIRMEE => 'Confirmée',
            self::STATUT_EN_PREPARATION => 'En préparation',
            self::STATUT_EN_LIVRAISON => 'En livraison',
            self::STATUT_LIVREE => 'Livrée',
            self::STATUT_ANNULEE => 'Annulée',
            default => 'Inconnu'
        };
    }

    public static function getStatutsDisponibles(): array
    {
        return [
            self::STATUT_EN_ATTENTE => 'En attente',
            self::STATUT_CONFIRMEE => 'Confirmée',
            self::STATUT_EN_PREPARATION => 'En préparation',
            self::STATUT_EN_LIVRAISON => 'En livraison',
            self::STATUT_LIVREE => 'Livrée',
            self::STATUT_ANNULEE => 'Annulée',
        ];
    }
}