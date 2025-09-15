<?php

namespace App\Entity;

use App\Entity\Trait\SoftDeleteable;
use App\Repository\MarketplaceCommissionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: MarketplaceCommissionRepository::class)]
#[ORM\Table(name: 'marketplace_commission')]
class MarketplaceCommission
{
    use SoftDeleteable;

    public const STATUS_PENDING = 'pending';
    public const STATUS_CALCULATED = 'calculated';
    public const STATUS_COLLECTED = 'collected';
    public const STATUS_DISPUTED = 'disputed';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['commission:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'commissions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Le marketplace est obligatoire.')]
    #[Groups(['commission:read'])]
    private ?Marketplace $marketplace = null;

    #[ORM\ManyToOne(inversedBy: 'commissions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Le vendeur est obligatoire.')]
    #[Groups(['commission:read'])]
    private ?Seller $seller = null;

    #[ORM\ManyToOne(inversedBy: 'commissions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'La commande est obligatoire.')]
    #[Groups(['commission:read'])]
    private ?Order $order = null;

    #[ORM\Column(length: 50)]
    #[Groups(['commission:read'])]
    private ?string $orderNumber = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'Le montant de la commande est obligatoire.')]
    #[Assert\PositiveOrZero(message: 'Le montant de la commande doit être positif.')]
    #[Groups(['commission:read'])]
    private ?string $orderAmount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    #[Assert\NotBlank(message: 'Le taux de commission est obligatoire.')]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: 'Le taux de commission doit être entre {{ min }} et {{ max }}.')]
    #[Groups(['commission:read'])]
    private ?string $commissionRate = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'Le montant de la commission est obligatoire.')]
    #[Assert\PositiveOrZero(message: 'Le montant de la commission doit être positif.')]
    #[Groups(['commission:read'])]
    private ?string $commissionAmount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero(message: 'Les frais de transaction doivent être positifs.')]
    #[Groups(['commission:read'])]
    private ?string $transactionFee = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['commission:read'])]
    private ?string $netCommission = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['commission:read'])]
    private ?string $sellerAmount = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::STATUS_PENDING, self::STATUS_CALCULATED, self::STATUS_COLLECTED, self::STATUS_DISPUTED])]
    #[Groups(['commission:read'])]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(length: 3)]
    #[Groups(['commission:read'])]
    private string $currency = 'EUR';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['commission:read'])]
    private ?string $notes = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $metadata = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['commission:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['commission:read'])]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['commission:read'])]
    private ?\DateTimeInterface $calculatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['commission:read'])]
    private ?\DateTimeInterface $collectedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->metadata = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMarketplace(): ?Marketplace
    {
        return $this->marketplace;
    }

    public function setMarketplace(?Marketplace $marketplace): static
    {
        $this->marketplace = $marketplace;
        return $this;
    }

    public function getSeller(): ?Seller
    {
        return $this->seller;
    }

    public function setSeller(?Seller $seller): static
    {
        $this->seller = $seller;
        return $this;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): static
    {
        $this->order = $order;
        
        if ($order) {
            $this->orderNumber = $order->getOrderNumber();
            $this->orderAmount = $order->getSubtotal();
            $this->currency = $order->getCurrency();
        }
        
        return $this;
    }

    public function getOrderNumber(): ?string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(string $orderNumber): static
    {
        $this->orderNumber = $orderNumber;
        return $this;
    }

    public function getOrderAmount(): ?string
    {
        return $this->orderAmount;
    }

    public function setOrderAmount(string $orderAmount): static
    {
        $this->orderAmount = $orderAmount;
        $this->calculateCommission();
        return $this;
    }

    public function getOrderAmountFloat(): float
    {
        return (float) $this->orderAmount;
    }

    public function getCommissionRate(): ?string
    {
        return $this->commissionRate;
    }

    public function setCommissionRate(string $commissionRate): static
    {
        $this->commissionRate = $commissionRate;
        $this->calculateCommission();
        return $this;
    }

    public function getCommissionRateFloat(): float
    {
        return (float) $this->commissionRate;
    }

    public function getCommissionAmount(): ?string
    {
        return $this->commissionAmount;
    }

    public function setCommissionAmount(string $commissionAmount): static
    {
        $this->commissionAmount = $commissionAmount;
        $this->calculateNetAmounts();
        return $this;
    }

    public function getCommissionAmountFloat(): float
    {
        return (float) $this->commissionAmount;
    }

    public function getTransactionFee(): ?string
    {
        return $this->transactionFee;
    }

    public function setTransactionFee(?string $transactionFee): static
    {
        $this->transactionFee = $transactionFee;
        $this->calculateNetAmounts();
        return $this;
    }

    public function getTransactionFeeFloat(): float
    {
        return (float) ($this->transactionFee ?? '0.00');
    }

    public function getNetCommission(): ?string
    {
        return $this->netCommission;
    }

    public function setNetCommission(string $netCommission): static
    {
        $this->netCommission = $netCommission;
        return $this;
    }

    public function getNetCommissionFloat(): float
    {
        return (float) $this->netCommission;
    }

    public function getSellerAmount(): ?string
    {
        return $this->sellerAmount;
    }

    public function setSellerAmount(string $sellerAmount): static
    {
        $this->sellerAmount = $sellerAmount;
        return $this;
    }

    public function getSellerAmountFloat(): float
    {
        return (float) $this->sellerAmount;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        $this->updatedAt = new \DateTime();

        match($status) {
            self::STATUS_CALCULATED => $this->calculatedAt = $this->calculatedAt ?? new \DateTime(),
            self::STATUS_COLLECTED => $this->collectedAt = $this->collectedAt ?? new \DateTime(),
            default => null
        };

        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): static
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getCalculatedAt(): ?\DateTimeInterface
    {
        return $this->calculatedAt;
    }

    public function setCalculatedAt(?\DateTimeInterface $calculatedAt): static
    {
        $this->calculatedAt = $calculatedAt;
        return $this;
    }

    public function getCollectedAt(): ?\DateTimeInterface
    {
        return $this->collectedAt;
    }

    public function setCollectedAt(?\DateTimeInterface $collectedAt): static
    {
        $this->collectedAt = $collectedAt;
        return $this;
    }

    public function calculateCommission(): void
    {
        if ($this->orderAmount && $this->commissionRate) {
            $commission = $this->getOrderAmountFloat() * ($this->getCommissionRateFloat() / 100);
            $this->commissionAmount = (string) number_format($commission, 2, '.', '');
            $this->calculateNetAmounts();
        }
    }

    public function calculateNetAmounts(): void
    {
        if ($this->commissionAmount) {
            // Net commission = commission - transaction fee
            $netCommission = $this->getCommissionAmountFloat() - $this->getTransactionFeeFloat();
            $this->netCommission = (string) number_format($netCommission, 2, '.', '');
            
            // Seller amount = order amount - commission
            $sellerAmount = $this->getOrderAmountFloat() - $this->getCommissionAmountFloat();
            $this->sellerAmount = (string) number_format($sellerAmount, 2, '.', '');
        }
    }

    public function calculate(): static
    {
        $this->calculateCommission();
        $this->setStatus(self::STATUS_CALCULATED);
        return $this;
    }

    public function collect(): static
    {
        $this->setStatus(self::STATUS_COLLECTED);
        return $this;
    }

    public function dispute(): static
    {
        $this->setStatus(self::STATUS_DISPUTED);
        return $this;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isCalculated(): bool
    {
        return $this->status === self::STATUS_CALCULATED;
    }

    public function isCollected(): bool
    {
        return $this->status === self::STATUS_COLLECTED;
    }

    public function isDisputed(): bool
    {
        return $this->status === self::STATUS_DISPUTED;
    }

    public function getCommissionPercentage(): float
    {
        if ($this->getOrderAmountFloat() > 0) {
            return ($this->getCommissionAmountFloat() / $this->getOrderAmountFloat()) * 100;
        }
        
        return 0.0;
    }

    public function getMarketplaceRevenue(): float
    {
        return $this->getNetCommissionFloat();
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'En attente',
            self::STATUS_CALCULATED => 'Calculé',
            self::STATUS_COLLECTED => 'Collecté',
            self::STATUS_DISPUTED => 'Contesté',
            default => 'Inconnu'
        };
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'En attente',
            self::STATUS_CALCULATED => 'Calculé',
            self::STATUS_COLLECTED => 'Collecté',
            self::STATUS_DISPUTED => 'Contesté',
        ];
    }

    public function __toString(): string
    {
        return 'Commission #' . $this->id . ' - ' . $this->orderNumber;
    }
}