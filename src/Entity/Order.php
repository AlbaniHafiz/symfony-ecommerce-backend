<?php

namespace App\Entity;

use App\Entity\Trait\SoftDeleteable;
use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    use SoftDeleteable;

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUNDED = 'refunded';

    public const PAYMENT_STATUS_PENDING = 'pending';
    public const PAYMENT_STATUS_PAID = 'paid';
    public const PAYMENT_STATUS_FAILED = 'failed';
    public const PAYMENT_STATUS_REFUNDED = 'refunded';
    public const PAYMENT_STATUS_PARTIALLY_REFUNDED = 'partially_refunded';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['order:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Groups(['order:read'])]
    private ?string $orderNumber = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'L\'utilisateur est obligatoire.')]
    #[Groups(['order:read'])]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[Groups(['order:read'])]
    private ?Seller $seller = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Le marketplace est obligatoire.')]
    #[Groups(['order:read'])]
    private ?Marketplace $marketplace = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::STATUS_PENDING, self::STATUS_CONFIRMED, self::STATUS_PROCESSING, self::STATUS_SHIPPED, self::STATUS_DELIVERED, self::STATUS_CANCELLED, self::STATUS_REFUNDED])]
    #[Groups(['order:read'])]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::PAYMENT_STATUS_PENDING, self::PAYMENT_STATUS_PAID, self::PAYMENT_STATUS_FAILED, self::PAYMENT_STATUS_REFUNDED, self::PAYMENT_STATUS_PARTIALLY_REFUNDED])]
    #[Groups(['order:read'])]
    private string $paymentStatus = self::PAYMENT_STATUS_PENDING;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['order:read'])]
    private ?string $subtotal = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['order:read'])]
    private ?string $taxAmount = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['order:read'])]
    private ?string $shippingAmount = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['order:read'])]
    private ?string $discountAmount = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['order:read'])]
    private ?string $commissionAmount = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['order:read'])]
    private ?string $total = '0.00';

    #[ORM\Column(length: 3)]
    #[Groups(['order:read'])]
    private string $currency = 'EUR';

    #[ORM\Column(type: Types::JSON)]
    #[Groups(['order:read'])]
    private array $billingAddress = [];

    #[ORM\Column(type: Types::JSON)]
    #[Groups(['order:read'])]
    private array $shippingAddress = [];

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['order:read'])]
    private ?string $paymentMethod = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $paymentTransactionId = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $paymentData = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['order:read'])]
    private ?string $shippingMethod = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['order:read'])]
    private ?string $trackingNumber = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['order:read'])]
    private ?string $notes = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $internalNotes = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['order:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['order:read'])]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['order:read'])]
    private ?\DateTimeInterface $confirmedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['order:read'])]
    private ?\DateTimeInterface $shippedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['order:read'])]
    private ?\DateTimeInterface $deliveredAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $cancelledAt = null;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: OrderItem::class, cascade: ['persist', 'remove'])]
    #[Groups(['order:read', 'order:detail'])]
    private Collection $orderItems;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: MarketplaceCommission::class)]
    private Collection $commissions;

    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
        $this->commissions = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->orderNumber = $this->generateOrderNumber();
        $this->billingAddress = [];
        $this->shippingAddress = [];
        $this->paymentData = [];
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
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

    public function getMarketplace(): ?Marketplace
    {
        return $this->marketplace;
    }

    public function setMarketplace(?Marketplace $marketplace): static
    {
        $this->marketplace = $marketplace;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        $this->updatedAt = new \DateTime();

        // Set specific timestamps based on status
        match($status) {
            self::STATUS_CONFIRMED => $this->confirmedAt = $this->confirmedAt ?? new \DateTime(),
            self::STATUS_SHIPPED => $this->shippedAt = $this->shippedAt ?? new \DateTime(),
            self::STATUS_DELIVERED => $this->deliveredAt = $this->deliveredAt ?? new \DateTime(),
            self::STATUS_CANCELLED => $this->cancelledAt = $this->cancelledAt ?? new \DateTime(),
            default => null
        };

        return $this;
    }

    public function getPaymentStatus(): string
    {
        return $this->paymentStatus;
    }

    public function setPaymentStatus(string $paymentStatus): static
    {
        $this->paymentStatus = $paymentStatus;
        return $this;
    }

    public function getSubtotal(): ?string
    {
        return $this->subtotal;
    }

    public function setSubtotal(string $subtotal): static
    {
        $this->subtotal = $subtotal;
        return $this;
    }

    public function getSubtotalFloat(): float
    {
        return (float) $this->subtotal;
    }

    public function getTaxAmount(): ?string
    {
        return $this->taxAmount;
    }

    public function setTaxAmount(string $taxAmount): static
    {
        $this->taxAmount = $taxAmount;
        return $this;
    }

    public function getTaxAmountFloat(): float
    {
        return (float) $this->taxAmount;
    }

    public function getShippingAmount(): ?string
    {
        return $this->shippingAmount;
    }

    public function setShippingAmount(string $shippingAmount): static
    {
        $this->shippingAmount = $shippingAmount;
        return $this;
    }

    public function getShippingAmountFloat(): float
    {
        return (float) $this->shippingAmount;
    }

    public function getDiscountAmount(): ?string
    {
        return $this->discountAmount;
    }

    public function setDiscountAmount(string $discountAmount): static
    {
        $this->discountAmount = $discountAmount;
        return $this;
    }

    public function getDiscountAmountFloat(): float
    {
        return (float) $this->discountAmount;
    }

    public function getCommissionAmount(): ?string
    {
        return $this->commissionAmount;
    }

    public function setCommissionAmount(string $commissionAmount): static
    {
        $this->commissionAmount = $commissionAmount;
        return $this;
    }

    public function getCommissionAmountFloat(): float
    {
        return (float) $this->commissionAmount;
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

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;
        return $this;
    }

    public function getBillingAddress(): array
    {
        return $this->billingAddress;
    }

    public function setBillingAddress(array $billingAddress): static
    {
        $this->billingAddress = $billingAddress;
        return $this;
    }

    public function getShippingAddress(): array
    {
        return $this->shippingAddress;
    }

    public function setShippingAddress(array $shippingAddress): static
    {
        $this->shippingAddress = $shippingAddress;
        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?string $paymentMethod): static
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    public function getPaymentTransactionId(): ?string
    {
        return $this->paymentTransactionId;
    }

    public function setPaymentTransactionId(?string $paymentTransactionId): static
    {
        $this->paymentTransactionId = $paymentTransactionId;
        return $this;
    }

    public function getPaymentData(): ?array
    {
        return $this->paymentData;
    }

    public function setPaymentData(?array $paymentData): static
    {
        $this->paymentData = $paymentData;
        return $this;
    }

    public function getShippingMethod(): ?string
    {
        return $this->shippingMethod;
    }

    public function setShippingMethod(?string $shippingMethod): static
    {
        $this->shippingMethod = $shippingMethod;
        return $this;
    }

    public function getTrackingNumber(): ?string
    {
        return $this->trackingNumber;
    }

    public function setTrackingNumber(?string $trackingNumber): static
    {
        $this->trackingNumber = $trackingNumber;
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

    public function getInternalNotes(): ?string
    {
        return $this->internalNotes;
    }

    public function setInternalNotes(?string $internalNotes): static
    {
        $this->internalNotes = $internalNotes;
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

    public function getConfirmedAt(): ?\DateTimeInterface
    {
        return $this->confirmedAt;
    }

    public function setConfirmedAt(?\DateTimeInterface $confirmedAt): static
    {
        $this->confirmedAt = $confirmedAt;
        return $this;
    }

    public function getShippedAt(): ?\DateTimeInterface
    {
        return $this->shippedAt;
    }

    public function setShippedAt(?\DateTimeInterface $shippedAt): static
    {
        $this->shippedAt = $shippedAt;
        return $this;
    }

    public function getDeliveredAt(): ?\DateTimeInterface
    {
        return $this->deliveredAt;
    }

    public function setDeliveredAt(?\DateTimeInterface $deliveredAt): static
    {
        $this->deliveredAt = $deliveredAt;
        return $this;
    }

    public function getCancelledAt(): ?\DateTimeInterface
    {
        return $this->cancelledAt;
    }

    public function setCancelledAt(?\DateTimeInterface $cancelledAt): static
    {
        $this->cancelledAt = $cancelledAt;
        return $this;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): static
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->setOrder($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            // set the owning side to null (unless already changed)
            if ($orderItem->getOrder() === $this) {
                $orderItem->setOrder(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MarketplaceCommission>
     */
    public function getCommissions(): Collection
    {
        return $this->commissions;
    }

    public function addCommission(MarketplaceCommission $commission): static
    {
        if (!$this->commissions->contains($commission)) {
            $this->commissions->add($commission);
            $commission->setOrder($this);
        }

        return $this;
    }

    public function removeCommission(MarketplaceCommission $commission): static
    {
        if ($this->commissions->removeElement($commission)) {
            // set the owning side to null (unless already changed)
            if ($commission->getOrder() === $this) {
                $commission->setOrder(null);
            }
        }

        return $this;
    }

    public function calculateTotals(): void
    {
        $subtotal = 0;
        
        foreach ($this->orderItems as $item) {
            $subtotal += $item->getUnitPriceFloat() * $item->getQuantity();
        }
        
        $this->subtotal = (string) number_format($subtotal, 2, '.', '');
        
        // Calculate total
        $total = $subtotal + $this->getTaxAmountFloat() + $this->getShippingAmountFloat() - $this->getDiscountAmountFloat();
        $this->total = (string) number_format($total, 2, '.', '');
    }

    public function calculateCommission(): void
    {
        if (!$this->seller || !$this->marketplace) {
            return;
        }

        $commissionRate = $this->seller->getCommissionRateFloat();
        $commission = $this->getSubtotalFloat() * ($commissionRate / 100);
        
        $this->commissionAmount = (string) number_format($commission, 2, '.', '');
    }

    public function getSellerAmount(): float
    {
        return $this->getSubtotalFloat() - $this->getCommissionAmountFloat();
    }

    public function getTotalItemCount(): int
    {
        $count = 0;
        foreach ($this->orderItems as $item) {
            $count += $item->getQuantity();
        }
        return $count;
    }

    public function isPaid(): bool
    {
        return $this->paymentStatus === self::PAYMENT_STATUS_PAID;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isDelivered(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED, self::STATUS_PROCESSING]);
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'En attente',
            self::STATUS_CONFIRMED => 'Confirmée',
            self::STATUS_PROCESSING => 'En préparation',
            self::STATUS_SHIPPED => 'Expédiée',
            self::STATUS_DELIVERED => 'Livrée',
            self::STATUS_CANCELLED => 'Annulée',
            self::STATUS_REFUNDED => 'Remboursée',
            default => 'Inconnu'
        };
    }

    public function getPaymentStatusLabel(): string
    {
        return match($this->paymentStatus) {
            self::PAYMENT_STATUS_PENDING => 'En attente',
            self::PAYMENT_STATUS_PAID => 'Payé',
            self::PAYMENT_STATUS_FAILED => 'Échec',
            self::PAYMENT_STATUS_REFUNDED => 'Remboursé',
            self::PAYMENT_STATUS_PARTIALLY_REFUNDED => 'Partiellement remboursé',
            default => 'Inconnu'
        };
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'En attente',
            self::STATUS_CONFIRMED => 'Confirmée',
            self::STATUS_PROCESSING => 'En préparation',
            self::STATUS_SHIPPED => 'Expédiée',
            self::STATUS_DELIVERED => 'Livrée',
            self::STATUS_CANCELLED => 'Annulée',
            self::STATUS_REFUNDED => 'Remboursée',
        ];
    }

    public static function getPaymentStatuses(): array
    {
        return [
            self::PAYMENT_STATUS_PENDING => 'En attente',
            self::PAYMENT_STATUS_PAID => 'Payé',
            self::PAYMENT_STATUS_FAILED => 'Échec',
            self::PAYMENT_STATUS_REFUNDED => 'Remboursé',
            self::PAYMENT_STATUS_PARTIALLY_REFUNDED => 'Partiellement remboursé',
        ];
    }

    private function generateOrderNumber(): string
    {
        return 'ORD-' . date('Y') . '-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
    }

    public function __toString(): string
    {
        return $this->orderNumber ?? '';
    }
}