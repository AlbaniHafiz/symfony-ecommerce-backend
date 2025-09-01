<?php

namespace App\Entity;

use App\Entity\Trait\SoftDeleteable;
use App\Repository\PaiementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PaiementRepository::class)]
#[ORM\Table(name: 'paiement')]
class Paiement
{
    use SoftDeleteable;
    public const STATUT_EN_ATTENTE = 'EN_ATTENTE';
    public const STATUT_PAYE = 'PAYE';
    public const STATUT_ECHEC = 'ECHEC';
    public const STATUT_REMBOURSE = 'REMBOURSE';

    public const METHODE_ESPECES_LIVRAISON = 'ESPECES_LIVRAISON';
    public const METHODE_NITA = 'NITA';
    public const METHODE_AMANA = 'AMANA';
    public const METHODE_CARTE_BANCAIRE = 'CARTE_BANCAIRE';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'paiements')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'La commande est obligatoire.')]
    private ?Commande $commande = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'Le montant est obligatoire.')]
    #[Assert\Positive(message: 'Le montant doit être positif.')]
    private ?string $montant = null;

    #[ORM\Column(length: 30)]
    private string $methodePaiement = self::METHODE_ESPECES_LIVRAISON;

    #[ORM\Column(length: 20)]
    private string $statut = self::STATUT_EN_ATTENTE;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateTransaction = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $referenceTransaction = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentaire = null;

    public function __construct()
    {
        $this->dateTransaction = new \DateTime();
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

    public function getMontant(): ?string
    {
        return $this->montant;
    }

    public function setMontant(string $montant): static
    {
        $this->montant = $montant;
        return $this;
    }

    public function getMontantFloat(): float
    {
        return (float) $this->montant;
    }

    public function getMethodePaiement(): string
    {
        return $this->methodePaiement;
    }

    public function setMethodePaiement(string $methodePaiement): static
    {
        $this->methodePaiement = $methodePaiement;
        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getDateTransaction(): ?\DateTimeInterface
    {
        return $this->dateTransaction;
    }

    public function setDateTransaction(\DateTimeInterface $dateTransaction): static
    {
        $this->dateTransaction = $dateTransaction;
        return $this;
    }

    public function getReferenceTransaction(): ?string
    {
        return $this->referenceTransaction;
    }

    public function setReferenceTransaction(?string $referenceTransaction): static
    {
        $this->referenceTransaction = $referenceTransaction;
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

    public function getMethodePaiementLibelle(): string
    {
        return match($this->methodePaiement) {
            self::METHODE_ESPECES_LIVRAISON => 'Espèces à la livraison',
            self::METHODE_NITA => 'Nita',
            self::METHODE_AMANA => 'Amana',
            self::METHODE_CARTE_BANCAIRE => 'Carte bancaire',
            default => 'Inconnue'
        };
    }

    public function getStatutLibelle(): string
    {
        return match($this->statut) {
            self::STATUT_EN_ATTENTE => 'En attente',
            self::STATUT_PAYE => 'Payé',
            self::STATUT_ECHEC => 'Échec',
            self::STATUT_REMBOURSE => 'Remboursé',
            default => 'Inconnu'
        };
    }

    public static function getMethodesPaiementDisponibles(): array
    {
        return [
            self::METHODE_ESPECES_LIVRAISON => 'Espèces à la livraison',
            self::METHODE_NITA => 'Nita',
            self::METHODE_AMANA => 'Amana',
            self::METHODE_CARTE_BANCAIRE => 'Carte bancaire',
        ];
    }

    public static function getStatutsDisponibles(): array
    {
        return [
            self::STATUT_EN_ATTENTE => 'En attente',
            self::STATUT_PAYE => 'Payé',
            self::STATUT_ECHEC => 'Échec',
            self::STATUT_REMBOURSE => 'Remboursé',
        ];
    }
}