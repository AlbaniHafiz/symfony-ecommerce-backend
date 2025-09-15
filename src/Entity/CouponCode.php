<?php

namespace App\Entity;

use App\Entity\Trait\SoftDeleteable;
use App\Repository\CouponCodeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: CouponCodeRepository::class)]
#[ORM\Table(name: 'coupon_code')]
#[UniqueEntity(fields: ['code'], message: 'Ce code de coupon existe déjà.')]
class CouponCode
{
    use SoftDeleteable;

    public const TYPE_PERCENTAGE = 'percentage';
    public const TYPE_FIXED_AMOUNT = 'fixed_amount';
    public const TYPE_FREE_SHIPPING = 'free_shipping';

    public const USAGE_TYPE_UNLIMITED = 'unlimited';
    public const USAGE_TYPE_LIMITED = 'limited';
    public const USAGE_TYPE_ONCE_PER_CUSTOMER = 'once_per_customer';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['coupon:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank(message: 'Le code du coupon est obligatoire.')]
    #[Assert\Length(max: 50, maxMessage: 'Le code ne peut pas dépasser {{ limit }} caractères.')]
    #[Assert\Regex(pattern: '/^[A-Z0-9\-_]+$/', message: 'Le code ne peut contenir que des lettres majuscules, chiffres, tirets et underscores.')]
    #[Groups(['coupon:read'])]
    private ?string $code = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank(message: 'Le nom du coupon est obligatoire.')]
    #[Assert\Length(max: 200, maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.')]
    #[Groups(['coupon:read'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['coupon:read'])]
    private ?string $description = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::TYPE_PERCENTAGE, self::TYPE_FIXED_AMOUNT, self::TYPE_FREE_SHIPPING])]
    #[Groups(['coupon:read'])]
    private string $discountType = self::TYPE_PERCENTAGE;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'La valeur de remise est obligatoire.')]
    #[Assert\PositiveOrZero(message: 'La valeur de remise doit être positive.')]
    #[Groups(['coupon:read'])]
    private ?string $discountValue = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero(message: 'Le montant minimum de commande doit être positif.')]
    #[Groups(['coupon:read'])]
    private ?string $minimumOrderAmount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero(message: 'Le montant maximum de remise doit être positif.')]
    #[Groups(['coupon:read'])]
    private ?string $maximumDiscountAmount = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::USAGE_TYPE_UNLIMITED, self::USAGE_TYPE_LIMITED, self::USAGE_TYPE_ONCE_PER_CUSTOMER])]
    #[Groups(['coupon:read'])]
    private string $usageType = self::USAGE_TYPE_UNLIMITED;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: 'La limite d\'usage doit être positive.')]
    #[Groups(['coupon:read'])]
    private ?int $usageLimit = null;

    #[ORM\Column]
    #[Groups(['coupon:read'])]
    private int $usageCount = 0;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['coupon:read'])]
    private ?\DateTimeInterface $startsAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['coupon:read'])]
    private ?\DateTimeInterface $expiresAt = null;

    #[ORM\Column]
    #[Groups(['coupon:read'])]
    private bool $isActive = true;

    #[ORM\Column]
    private bool $isPublic = true;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $applicableProducts = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $applicableCategories = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $applicableSellers = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $excludedProducts = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $excludedCategories = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $excludedSellers = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $metadata = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['coupon:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['coupon:read'])]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'couponCode', targetEntity: CouponUsage::class)]
    private Collection $couponUsages;

    public function __construct()
    {
        $this->couponUsages = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->applicableProducts = [];
        $this->applicableCategories = [];
        $this->applicableSellers = [];
        $this->excludedProducts = [];
        $this->excludedCategories = [];
        $this->excludedSellers = [];
        $this->metadata = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = strtoupper($code);
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
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

    public function getDiscountType(): string
    {
        return $this->discountType;
    }

    public function setDiscountType(string $discountType): static
    {
        $this->discountType = $discountType;
        return $this;
    }

    public function getDiscountValue(): ?string
    {
        return $this->discountValue;
    }

    public function setDiscountValue(string $discountValue): static
    {
        $this->discountValue = $discountValue;
        return $this;
    }

    public function getDiscountValueFloat(): float
    {
        return (float) $this->discountValue;
    }

    public function getMinimumOrderAmount(): ?string
    {
        return $this->minimumOrderAmount;
    }

    public function setMinimumOrderAmount(?string $minimumOrderAmount): static
    {
        $this->minimumOrderAmount = $minimumOrderAmount;
        return $this;
    }

    public function getMinimumOrderAmountFloat(): ?float
    {
        return $this->minimumOrderAmount ? (float) $this->minimumOrderAmount : null;
    }

    public function getMaximumDiscountAmount(): ?string
    {
        return $this->maximumDiscountAmount;
    }

    public function setMaximumDiscountAmount(?string $maximumDiscountAmount): static
    {
        $this->maximumDiscountAmount = $maximumDiscountAmount;
        return $this;
    }

    public function getMaximumDiscountAmountFloat(): ?float
    {
        return $this->maximumDiscountAmount ? (float) $this->maximumDiscountAmount : null;
    }

    public function getUsageType(): string
    {
        return $this->usageType;
    }

    public function setUsageType(string $usageType): static
    {
        $this->usageType = $usageType;
        return $this;
    }

    public function getUsageLimit(): ?int
    {
        return $this->usageLimit;
    }

    public function setUsageLimit(?int $usageLimit): static
    {
        $this->usageLimit = $usageLimit;
        return $this;
    }

    public function getUsageCount(): int
    {
        return $this->usageCount;
    }

    public function setUsageCount(int $usageCount): static
    {
        $this->usageCount = $usageCount;
        return $this;
    }

    public function incrementUsageCount(): static
    {
        $this->usageCount++;
        return $this;
    }

    public function getStartsAt(): ?\DateTimeInterface
    {
        return $this->startsAt;
    }

    public function setStartsAt(?\DateTimeInterface $startsAt): static
    {
        $this->startsAt = $startsAt;
        return $this;
    }

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeInterface $expiresAt): static
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): static
    {
        $this->isPublic = $isPublic;
        return $this;
    }

    public function getApplicableProducts(): ?array
    {
        return $this->applicableProducts;
    }

    public function setApplicableProducts(?array $applicableProducts): static
    {
        $this->applicableProducts = $applicableProducts;
        return $this;
    }

    public function getApplicableCategories(): ?array
    {
        return $this->applicableCategories;
    }

    public function setApplicableCategories(?array $applicableCategories): static
    {
        $this->applicableCategories = $applicableCategories;
        return $this;
    }

    public function getApplicableSellers(): ?array
    {
        return $this->applicableSellers;
    }

    public function setApplicableSellers(?array $applicableSellers): static
    {
        $this->applicableSellers = $applicableSellers;
        return $this;
    }

    public function getExcludedProducts(): ?array
    {
        return $this->excludedProducts;
    }

    public function setExcludedProducts(?array $excludedProducts): static
    {
        $this->excludedProducts = $excludedProducts;
        return $this;
    }

    public function getExcludedCategories(): ?array
    {
        return $this->excludedCategories;
    }

    public function setExcludedCategories(?array $excludedCategories): static
    {
        $this->excludedCategories = $excludedCategories;
        return $this;
    }

    public function getExcludedSellers(): ?array
    {
        return $this->excludedSellers;
    }

    public function setExcludedSellers(?array $excludedSellers): static
    {
        $this->excludedSellers = $excludedSellers;
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

    /**
     * @return Collection<int, CouponUsage>
     */
    public function getCouponUsages(): Collection
    {
        return $this->couponUsages;
    }

    public function addCouponUsage(CouponUsage $couponUsage): static
    {
        if (!$this->couponUsages->contains($couponUsage)) {
            $this->couponUsages->add($couponUsage);
            $couponUsage->setCouponCode($this);
        }

        return $this;
    }

    public function removeCouponUsage(CouponUsage $couponUsage): static
    {
        if ($this->couponUsages->removeElement($couponUsage)) {
            // set the owning side to null (unless already changed)
            if ($couponUsage->getCouponCode() === $this) {
                $couponUsage->setCouponCode(null);
            }
        }

        return $this;
    }

    public function isValid(): bool
    {
        $now = new \DateTime();
        
        // Check if coupon is active
        if (!$this->isActive) {
            return false;
        }
        
        // Check start date
        if ($this->startsAt && $now < $this->startsAt) {
            return false;
        }
        
        // Check expiry date
        if ($this->expiresAt && $now > $this->expiresAt) {
            return false;
        }
        
        // Check usage limit
        if ($this->usageType === self::USAGE_TYPE_LIMITED && $this->usageLimit && $this->usageCount >= $this->usageLimit) {
            return false;
        }
        
        return true;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt && new \DateTime() > $this->expiresAt;
    }

    public function isUsageLimitReached(): bool
    {
        return $this->usageType === self::USAGE_TYPE_LIMITED && 
               $this->usageLimit && 
               $this->usageCount >= $this->usageLimit;
    }

    public function canBeUsedBy(User $user): bool
    {
        if (!$this->isValid()) {
            return false;
        }
        
        if ($this->usageType === self::USAGE_TYPE_ONCE_PER_CUSTOMER) {
            // Check if user has already used this coupon
            foreach ($this->couponUsages as $usage) {
                if ($usage->getUser() === $user) {
                    return false;
                }
            }
        }
        
        return true;
    }

    public function calculateDiscountAmount(float $orderAmount): float
    {
        switch ($this->discountType) {
            case self::TYPE_PERCENTAGE:
                $discount = $orderAmount * ($this->getDiscountValueFloat() / 100);
                break;
                
            case self::TYPE_FIXED_AMOUNT:
                $discount = $this->getDiscountValueFloat();
                break;
                
            case self::TYPE_FREE_SHIPPING:
                // This should be handled separately in shipping calculation
                return 0.0;
                
            default:
                return 0.0;
        }
        
        // Apply maximum discount limit if set
        if ($this->maximumDiscountAmount) {
            $discount = min($discount, $this->getMaximumDiscountAmountFloat());
        }
        
        // Ensure discount doesn't exceed order amount
        return min($discount, $orderAmount);
    }

    public function isApplicableToProduct(Product $product): bool
    {
        // Check if product is specifically excluded
        if ($this->excludedProducts && in_array($product->getId(), $this->excludedProducts)) {
            return false;
        }
        
        // Check if product's category is excluded
        if ($this->excludedCategories && $product->getCategory() && 
            in_array($product->getCategory()->getId(), $this->excludedCategories)) {
            return false;
        }
        
        // Check if product's seller is excluded
        if ($this->excludedSellers && $product->getSeller() && 
            in_array($product->getSeller()->getId(), $this->excludedSellers)) {
            return false;
        }
        
        // If there are specific applicable products, check if this product is included
        if ($this->applicableProducts && !empty($this->applicableProducts)) {
            return in_array($product->getId(), $this->applicableProducts);
        }
        
        // If there are specific applicable categories, check if this product's category is included
        if ($this->applicableCategories && !empty($this->applicableCategories)) {
            return $product->getCategory() && in_array($product->getCategory()->getId(), $this->applicableCategories);
        }
        
        // If there are specific applicable sellers, check if this product's seller is included
        if ($this->applicableSellers && !empty($this->applicableSellers)) {
            return $product->getSeller() && in_array($product->getSeller()->getId(), $this->applicableSellers);
        }
        
        // If no specific restrictions, coupon applies to all products
        return true;
    }

    public function getRemainingUsages(): ?int
    {
        if ($this->usageType !== self::USAGE_TYPE_LIMITED || !$this->usageLimit) {
            return null;
        }
        
        return max(0, $this->usageLimit - $this->usageCount);
    }

    public function getUsagePercentage(): ?float
    {
        if ($this->usageType !== self::USAGE_TYPE_LIMITED || !$this->usageLimit) {
            return null;
        }
        
        return ($this->usageCount / $this->usageLimit) * 100;
    }

    public function getDiscountTypeLabel(): string
    {
        return match($this->discountType) {
            self::TYPE_PERCENTAGE => 'Pourcentage',
            self::TYPE_FIXED_AMOUNT => 'Montant fixe',
            self::TYPE_FREE_SHIPPING => 'Livraison gratuite',
            default => 'Inconnu'
        };
    }

    public function getUsageTypeLabel(): string
    {
        return match($this->usageType) {
            self::USAGE_TYPE_UNLIMITED => 'Illimité',
            self::USAGE_TYPE_LIMITED => 'Limité',
            self::USAGE_TYPE_ONCE_PER_CUSTOMER => 'Une fois par client',
            default => 'Inconnu'
        };
    }

    public static function getDiscountTypes(): array
    {
        return [
            self::TYPE_PERCENTAGE => 'Pourcentage',
            self::TYPE_FIXED_AMOUNT => 'Montant fixe',
            self::TYPE_FREE_SHIPPING => 'Livraison gratuite',
        ];
    }

    public static function getUsageTypes(): array
    {
        return [
            self::USAGE_TYPE_UNLIMITED => 'Illimité',
            self::USAGE_TYPE_LIMITED => 'Limité',
            self::USAGE_TYPE_ONCE_PER_CUSTOMER => 'Une fois par client',
        ];
    }

    public function __toString(): string
    {
        return $this->code ?? '';
    }
}