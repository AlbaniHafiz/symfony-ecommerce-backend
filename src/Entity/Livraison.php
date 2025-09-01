<?php

namespace App\Entity;

use App\Entity\Trait\SoftDeleteable;
use App\Repository\LivraisonRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LivraisonRepository::class)]
#[ORM\Table(name: 'livraison')]
class Livraison
{
    use SoftDeleteable;
    public const STATUT_EN_ATTENTE = 'EN_ATTENTE';
    public const STATUT_ASSIGNEE = 'ASSIGNEE';
    public const STATUT_EN_COURS = 'EN_COURS';
    public const STATUT_LIVREE = 'LIVREE';
    public const STATUT_ECHEC = 'ECHEC';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'livraison', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'La commande est obligatoire.')]
    private ?Commande $commande = null;

    #[ORM\ManyToOne(inversedBy: 'livraisons')]
    private ?Utilisateur $livreur = null;

    #[ORM\Column(length: 20)]
    private string $statut = self::STATUT_EN_ATTENTE;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateAttribution = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateLivraison = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
    }

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

    public function getLivreur(): ?Utilisateur
    {
        return $this->livreur;
    }

    public function setLivreur(?Utilisateur $livreur): static
    {
        $this->livreur = $livreur;
        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        
        // Mettre à jour les dates automatiquement selon le statut
        if ($statut === self::STATUT_ASSIGNEE && !$this->dateAttribution) {
            $this->dateAttribution = new \DateTime();
        } elseif ($statut === self::STATUT_LIVREE && !$this->dateLivraison) {
            $this->dateLivraison = new \DateTime();
        }
        
        return $this;
    }

    public function getDateAttribution(): ?\DateTimeInterface
    {
        return $this->dateAttribution;
    }

    public function setDateAttribution(?\DateTimeInterface $dateAttribution): static
    {
        $this->dateAttribution = $dateAttribution;
        return $this;
    }

    public function getDateLivraison(): ?\DateTimeInterface
    {
        return $this->dateLivraison;
    }

    public function setDateLivraison(?\DateTimeInterface $dateLivraison): static
    {
        $this->dateLivraison = $dateLivraison;
        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;
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

    public function getStatutLibelle(): string
    {
        return match($this->statut) {
            self::STATUT_EN_ATTENTE => 'En attente',
            self::STATUT_ASSIGNEE => 'Assignée',
            self::STATUT_EN_COURS => 'En cours',
            self::STATUT_LIVREE => 'Livrée',
            self::STATUT_ECHEC => 'Échec',
            default => 'Inconnu'
        };
    }

    public static function getStatutsDisponibles(): array
    {
        return [
            self::STATUT_EN_ATTENTE => 'En attente',
            self::STATUT_ASSIGNEE => 'Assignée',
            self::STATUT_EN_COURS => 'En cours',
            self::STATUT_LIVREE => 'Livrée',
            self::STATUT_ECHEC => 'Échec',
        ];
    }

    public function assignerLivreur(Utilisateur $livreur): static
    {
        $this->livreur = $livreur;
        $this->statut = self::STATUT_ASSIGNEE;
        $this->dateAttribution = new \DateTime();
        return $this;
    }

    public function marquerLivree(): static
    {
        $this->statut = self::STATUT_LIVREE;
        $this->dateLivraison = new \DateTime();
        return $this;
    }
}