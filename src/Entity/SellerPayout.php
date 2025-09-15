<?php

namespace App\Entity;

use App\Entity\Trait\SoftDeleteable;
use App\Repository\SellerPayoutRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SellerPayoutRepository::class)]
#[ORM\Table(name: 'seller_payout')]
class SellerPayout
{
    use SoftDeleteable;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    public const METHOD_BANK_TRANSFER = 'bank_transfer';
    public const METHOD_PAYPAL = 'paypal';
    public const METHOD_STRIPE = 'stripe';
    public const METHOD_CHECK = 'check';
    public const METHOD_CASH = 'cash';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['payout:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'payouts')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Le vendeur est obligatoire.')]
    #[Groups(['payout:read'])]
    private ?Seller $seller = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Groups(['payout:read'])]
    private ?string $payoutNumber = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'Le montant est obligatoire.')]
    #[Assert\Positive(message: 'Le montant doit être positif.')]
    #[Groups(['payout:read'])]
    private ?string $amount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero(message: 'Les frais doivent être positifs.')]
    #[Groups(['payout:read'])]
    private ?string $fees = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['payout:read'])]
    private ?string $netAmount = null;

    #[ORM\Column(length: 3)]
    #[Groups(['payout:read'])]
    private string $currency = 'EUR';

    #[ORM\Column(length: 30)]
    #[Assert\Choice(choices: [self::METHOD_BANK_TRANSFER, self::METHOD_PAYPAL, self::METHOD_STRIPE, self::METHOD_CHECK, self::METHOD_CASH])]
    #[Groups(['payout:read'])]
    private string $paymentMethod = self::METHOD_BANK_TRANSFER;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::STATUS_PENDING, self::STATUS_PROCESSING, self::STATUS_COMPLETED, self::STATUS_FAILED, self::STATUS_CANCELLED])]
    #[Groups(['payout:read'])]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['payout:read'])]
    private ?string $transactionId = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $paymentDetails = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $bankDetails = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['payout:read'])]
    private ?string $notes = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $failureReason = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $metadata = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['payout:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['payout:read'])]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['payout:read'])]
    private ?\DateTimeInterface $scheduledAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['payout:read'])]
    private ?\DateTimeInterface $processedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['payout:read'])]
    private ?\DateTimeInterface $completedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->payoutNumber = $this->generatePayoutNumber();
        $this->paymentDetails = [];
        $this->bankDetails = [];
        $this->metadata = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSeller(): ?Seller
    {
        return $this->seller;
    }

    public function setSeller(?Seller $seller): static
    {
        $this->seller = $seller;
        
        if ($seller && $seller->getBankDetails()) {
            $this->bankDetails = $seller->getBankDetails();
        }
        
        return $this;
    }

    public function getPayoutNumber(): ?string
    {
        return $this->payoutNumber;
    }

    public function setPayoutNumber(string $payoutNumber): static
    {
        $this->payoutNumber = $payoutNumber;
        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;
        $this->calculateNetAmount();
        return $this;
    }

    public function getAmountFloat(): float
    {
        return (float) $this->amount;
    }

    public function getFees(): ?string
    {
        return $this->fees;
    }

    public function setFees(?string $fees): static
    {
        $this->fees = $fees;
        $this->calculateNetAmount();
        return $this;
    }

    public function getFeesFloat(): float
    {
        return (float) ($this->fees ?? '0.00');
    }

    public function getNetAmount(): ?string
    {
        return $this->netAmount;
    }

    public function setNetAmount(string $netAmount): static
    {
        $this->netAmount = $netAmount;
        return $this;
    }

    public function getNetAmountFloat(): float
    {
        return (float) $this->netAmount;
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

    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(string $paymentMethod): static
    {
        $this->paymentMethod = $paymentMethod;
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

        match($status) {
            self::STATUS_PROCESSING => $this->processedAt = $this->processedAt ?? new \DateTime(),
            self::STATUS_COMPLETED => $this->completedAt = $this->completedAt ?? new \DateTime(),
            default => null
        };

        return $this;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function setTransactionId(?string $transactionId): static
    {
        $this->transactionId = $transactionId;
        return $this;
    }

    public function getPaymentDetails(): ?array
    {
        return $this->paymentDetails;
    }

    public function setPaymentDetails(?array $paymentDetails): static
    {
        $this->paymentDetails = $paymentDetails;
        return $this;
    }

    public function getBankDetails(): ?array
    {
        return $this->bankDetails;
    }

    public function setBankDetails(?array $bankDetails): static
    {
        $this->bankDetails = $bankDetails;
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

    public function getFailureReason(): ?string
    {
        return $this->failureReason;
    }

    public function setFailureReason(?string $failureReason): static
    {
        $this->failureReason = $failureReason;
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

    public function getScheduledAt(): ?\DateTimeInterface
    {
        return $this->scheduledAt;
    }

    public function setScheduledAt(?\DateTimeInterface $scheduledAt): static
    {
        $this->scheduledAt = $scheduledAt;
        return $this;
    }

    public function getProcessedAt(): ?\DateTimeInterface
    {
        return $this->processedAt;
    }

    public function setProcessedAt(?\DateTimeInterface $processedAt): static
    {
        $this->processedAt = $processedAt;
        return $this;
    }

    public function getCompletedAt(): ?\DateTimeInterface
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeInterface $completedAt): static
    {
        $this->completedAt = $completedAt;
        return $this;
    }

    public function calculateNetAmount(): void
    {
        if ($this->amount) {
            $netAmount = $this->getAmountFloat() - $this->getFeesFloat();
            $this->netAmount = (string) number_format($netAmount, 2, '.', '');
        }
    }

    public function process(): static
    {
        $this->setStatus(self::STATUS_PROCESSING);
        return $this;
    }

    public function complete(?string $transactionId = null): static
    {
        $this->setStatus(self::STATUS_COMPLETED);
        if ($transactionId) {
            $this->setTransactionId($transactionId);
        }
        return $this;
    }

    public function fail(string $reason): static
    {
        $this->setStatus(self::STATUS_FAILED);
        $this->setFailureReason($reason);
        return $this;
    }

    public function cancel(): static
    {
        $this->setStatus(self::STATUS_CANCELLED);
        return $this;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    public function canBeRetried(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function getFeePercentage(): float
    {
        if ($this->getAmountFloat() > 0) {
            return ($this->getFeesFloat() / $this->getAmountFloat()) * 100;
        }
        
        return 0.0;
    }

    public function getProcessingTime(): ?\DateInterval
    {
        if ($this->processedAt && $this->completedAt) {
            return $this->completedAt->diff($this->processedAt);
        }
        
        return null;
    }

    public function getTotalTime(): ?\DateInterval
    {
        if ($this->completedAt) {
            return $this->completedAt->diff($this->createdAt);
        }
        
        return null;
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'En attente',
            self::STATUS_PROCESSING => 'En cours',
            self::STATUS_COMPLETED => 'Terminé',
            self::STATUS_FAILED => 'Échec',
            self::STATUS_CANCELLED => 'Annulé',
            default => 'Inconnu'
        };
    }

    public function getPaymentMethodLabel(): string
    {
        return match($this->paymentMethod) {
            self::METHOD_BANK_TRANSFER => 'Virement bancaire',
            self::METHOD_PAYPAL => 'PayPal',
            self::METHOD_STRIPE => 'Stripe',
            self::METHOD_CHECK => 'Chèque',
            self::METHOD_CASH => 'Espèces',
            default => 'Inconnu'
        };
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'En attente',
            self::STATUS_PROCESSING => 'En cours',
            self::STATUS_COMPLETED => 'Terminé',
            self::STATUS_FAILED => 'Échec',
            self::STATUS_CANCELLED => 'Annulé',
        ];
    }

    public static function getPaymentMethods(): array
    {
        return [
            self::METHOD_BANK_TRANSFER => 'Virement bancaire',
            self::METHOD_PAYPAL => 'PayPal',
            self::METHOD_STRIPE => 'Stripe',
            self::METHOD_CHECK => 'Chèque',
            self::METHOD_CASH => 'Espèces',
        ];
    }

    private function generatePayoutNumber(): string
    {
        return 'PAY-' . date('Y') . '-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
    }

    public function __toString(): string
    {
        return $this->payoutNumber ?? '';
    }
}