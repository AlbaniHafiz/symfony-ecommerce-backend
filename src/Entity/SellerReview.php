<?php

namespace App\Entity;

use App\Entity\Trait\SoftDeleteable;
use App\Repository\SellerReviewRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SellerReviewRepository::class)]
#[ORM\Table(name: 'seller_review')]
class SellerReview
{
    use SoftDeleteable;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['seller_review:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Le vendeur est obligatoire.')]
    #[Groups(['seller_review:read'])]
    private ?Seller $seller = null;

    #[ORM\ManyToOne(inversedBy: 'sellerReviews')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'L\'utilisateur est obligatoire.')]
    #[Groups(['seller_review:read'])]
    private ?User $user = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'La note de communication est obligatoire.')]
    #[Assert\Range(min: 1, max: 5, notInRangeMessage: 'La note de communication doit être entre {{ min }} et {{ max }}.')]
    #[Groups(['seller_review:read'])]
    private ?int $communicationRating = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'La note de rapidité est obligatoire.')]
    #[Assert\Range(min: 1, max: 5, notInRangeMessage: 'La note de rapidité doit être entre {{ min }} et {{ max }}.')]
    #[Groups(['seller_review:read'])]
    private ?int $shippingSpeedRating = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'La note de service est obligatoire.')]
    #[Assert\Range(min: 1, max: 5, notInRangeMessage: 'La note de service doit être entre {{ min }} et {{ max }}.')]
    #[Groups(['seller_review:read'])]
    private ?int $serviceRating = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 2)]
    #[Groups(['seller_review:read'])]
    private ?string $overallRating = null;

    #[ORM\Column(length: 200, nullable: true)]
    #[Assert\Length(max: 200, maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères.')]
    #[Groups(['seller_review:read'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['seller_review:read'])]
    private ?string $comment = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED])]
    #[Groups(['seller_review:read'])]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $moderationNotes = null;

    #[ORM\Column]
    #[Groups(['seller_review:read'])]
    private bool $isVerifiedPurchase = false;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['seller_review:read'])]
    private ?string $orderNumber = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['seller_review:read'])]
    private ?int $helpfulCount = 0;

    #[ORM\Column(nullable: true)]
    #[Groups(['seller_review:read'])]
    private ?int $unhelpfulCount = 0;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $metadata = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['seller_review:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['seller_review:read'])]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $moderatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->metadata = [];
        $this->calculateOverallRating();
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

    public function getCommunicationRating(): ?int
    {
        return $this->communicationRating;
    }

    public function setCommunicationRating(int $communicationRating): static
    {
        $this->communicationRating = $communicationRating;
        $this->calculateOverallRating();
        return $this;
    }

    public function getShippingSpeedRating(): ?int
    {
        return $this->shippingSpeedRating;
    }

    public function setShippingSpeedRating(int $shippingSpeedRating): static
    {
        $this->shippingSpeedRating = $shippingSpeedRating;
        $this->calculateOverallRating();
        return $this;
    }

    public function getServiceRating(): ?int
    {
        return $this->serviceRating;
    }

    public function setServiceRating(int $serviceRating): static
    {
        $this->serviceRating = $serviceRating;
        $this->calculateOverallRating();
        return $this;
    }

    public function getOverallRating(): ?string
    {
        return $this->overallRating;
    }

    public function setOverallRating(string $overallRating): static
    {
        $this->overallRating = $overallRating;
        return $this;
    }

    public function getOverallRatingFloat(): float
    {
        return (float) $this->overallRating;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        
        if (in_array($status, [self::STATUS_APPROVED, self::STATUS_REJECTED])) {
            $this->moderatedAt = new \DateTime();
        }
        
        return $this;
    }

    public function getModerationNotes(): ?string
    {
        return $this->moderationNotes;
    }

    public function setModerationNotes(?string $moderationNotes): static
    {
        $this->moderationNotes = $moderationNotes;
        return $this;
    }

    public function isVerifiedPurchase(): bool
    {
        return $this->isVerifiedPurchase;
    }

    public function setIsVerifiedPurchase(bool $isVerifiedPurchase): static
    {
        $this->isVerifiedPurchase = $isVerifiedPurchase;
        return $this;
    }

    public function getOrderNumber(): ?string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(?string $orderNumber): static
    {
        $this->orderNumber = $orderNumber;
        return $this;
    }

    public function getHelpfulCount(): ?int
    {
        return $this->helpfulCount;
    }

    public function setHelpfulCount(?int $helpfulCount): static
    {
        $this->helpfulCount = $helpfulCount;
        return $this;
    }

    public function getUnhelpfulCount(): ?int
    {
        return $this->unhelpfulCount;
    }

    public function setUnhelpfulCount(?int $unhelpfulCount): static
    {
        $this->unhelpfulCount = $unhelpfulCount;
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

    public function getModeratedAt(): ?\DateTimeInterface
    {
        return $this->moderatedAt;
    }

    public function setModeratedAt(?\DateTimeInterface $moderatedAt): static
    {
        $this->moderatedAt = $moderatedAt;
        return $this;
    }

    public function calculateOverallRating(): void
    {
        if ($this->communicationRating && $this->shippingSpeedRating && $this->serviceRating) {
            $average = ($this->communicationRating + $this->shippingSpeedRating + $this->serviceRating) / 3;
            $this->overallRating = (string) number_format($average, 2, '.', '');
        }
    }

    public function incrementHelpfulCount(): static
    {
        $this->helpfulCount = ($this->helpfulCount ?? 0) + 1;
        return $this;
    }

    public function incrementUnhelpfulCount(): static
    {
        $this->unhelpfulCount = ($this->unhelpfulCount ?? 0) + 1;
        return $this;
    }

    public function getTotalVotes(): int
    {
        return ($this->helpfulCount ?? 0) + ($this->unhelpfulCount ?? 0);
    }

    public function getHelpfulPercentage(): float
    {
        $totalVotes = $this->getTotalVotes();
        if ($totalVotes === 0) {
            return 0.0;
        }
        
        return (($this->helpfulCount ?? 0) / $totalVotes) * 100;
    }

    public function approve(): static
    {
        return $this->setStatus(self::STATUS_APPROVED);
    }

    public function reject(?string $reason = null): static
    {
        $this->setStatus(self::STATUS_REJECTED);
        if ($reason) {
            $this->setModerationNotes($reason);
        }
        return $this;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'En attente',
            self::STATUS_APPROVED => 'Approuvé',
            self::STATUS_REJECTED => 'Rejeté',
            default => 'Inconnu'
        };
    }

    public function getRatingBreakdown(): array
    {
        return [
            'communication' => $this->communicationRating,
            'shipping_speed' => $this->shippingSpeedRating,
            'service' => $this->serviceRating,
            'overall' => $this->getOverallRatingFloat(),
        ];
    }

    public function getRatingStars(string $type = 'overall'): string
    {
        $rating = match($type) {
            'communication' => $this->communicationRating,
            'shipping_speed' => $this->shippingSpeedRating,
            'service' => $this->serviceRating,
            'overall' => (int) round($this->getOverallRatingFloat()),
            default => 0
        };
        
        return str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
    }

    public function getAuthorName(): string
    {
        return $this->user?->getFirstName() ?? 'Utilisateur anonyme';
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'En attente',
            self::STATUS_APPROVED => 'Approuvé',
            self::STATUS_REJECTED => 'Rejeté',
        ];
    }

    public function __toString(): string
    {
        return $this->title ?? ('Avis de ' . $this->getAuthorName() . ' pour ' . $this->seller?->getCompanyName());
    }
}