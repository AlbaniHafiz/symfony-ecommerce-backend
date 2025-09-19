<?php

namespace App\Entity;

use App\Entity\Trait\SoftDeleteable;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'product')]
class Product
{
    use SoftDeleteable;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_SUSPENDED = 'suspended';

    public const CONDITION_NEW = 'new';
    public const CONDITION_USED = 'used';
    public const CONDITION_REFURBISHED = 'refurbished';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['product:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Le vendeur est obligatoire.')]
    #[Groups(['product:read'])]
    private ?Seller $seller = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'La catégorie est obligatoire.')]
    #[Groups(['product:read'])]
    private ?Category $category = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[Groups(['product:read'])]
    private ?SubCategory $subCategory = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[Groups(['product:read'])]
    private ?Brand $brand = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank(message: 'Le nom du produit est obligatoire.')]
    #[Assert\Length(max: 200, maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.')]
    #[Groups(['product:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 150, unique: true)]
    #[Assert\NotBlank(message: 'Le slug est obligatoire.')]
    #[Assert\Regex(pattern: '/^[a-z0-9\-]+$/', message: 'Le slug ne peut contenir que des lettres minuscules, chiffres et tirets.')]
    #[Groups(['product:read'])]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['product:read', 'product:detail'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['product:detail'])]
    private ?string $shortDescription = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['product:read'])]
    private ?string $sku = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['product:read'])]
    private ?string $barcode = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'Le prix est obligatoire.')]
    #[Assert\PositiveOrZero(message: 'Le prix doit être positif.')]
    #[Groups(['product:read'])]
    private ?string $price = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero(message: 'Le prix de comparaison doit être positif.')]
    #[Groups(['product:read'])]
    private ?string $compareAtPrice = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero(message: 'Le prix de coût doit être positif.')]
    private ?string $costPrice = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Le stock est obligatoire.')]
    #[Assert\PositiveOrZero(message: 'Le stock doit être positif ou nul.')]
    #[Groups(['product:read'])]
    private ?int $stock = 0;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: 'Le stock d\'alerte doit être positif ou nul.')]
    #[Groups(['product:read'])]
    private ?int $lowStockThreshold = null;

    #[ORM\Column]
    private bool $trackQuantity = true;

    #[ORM\Column]
    private bool $continueSellingWhenOutOfStock = false;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero(message: 'Le poids doit être positif.')]
    #[Groups(['product:read'])]
    private ?string $weight = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::CONDITION_NEW, self::CONDITION_USED, self::CONDITION_REFURBISHED])]
    #[Groups(['product:read'])]
    private string $condition = self::CONDITION_NEW;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::STATUS_DRAFT, self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED, self::STATUS_SUSPENDED])]
    #[Groups(['product:read'])]
    private string $status = self::STATUS_DRAFT;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $rejectionReason = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 2)]
    #[Assert\Range(min: 0, max: 5, notInRangeMessage: 'La note doit être entre {{ min }} et {{ max }}.')]
    #[Groups(['product:read'])]
    private ?string $rating = '0.00';

    #[ORM\Column]
    #[Groups(['product:read'])]
    private int $totalReviews = 0;

    #[ORM\Column]
    #[Groups(['product:read'])]
    private int $totalSales = 0;

    #[ORM\Column]
    #[Groups(['product:read'])]
    private int $viewCount = 0;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['product:detail'])]
    private ?array $specifications = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['product:detail'])]
    private ?array $features = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $seoData = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $metadata = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['product:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['product:read'])]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $approvedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $publishedAt = null;

    #[ORM\Column]
    #[Groups(['product:read'])]
    private bool $isActive = true;

    #[ORM\Column]
    #[Groups(['product:read'])]
    private bool $isFeatured = false;

    #[ORM\Column]
    private bool $isDigital = false;

    #[ORM\Column]
    private bool $requiresShipping = true;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ProductImage::class, cascade: ['persist', 'remove'])]
    #[Groups(['product:read', 'product:detail'])]
    private Collection $images;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Variant::class, cascade: ['persist', 'remove'])]
    #[Groups(['product:detail'])]
    private Collection $variants;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Review::class)]
    private Collection $reviews;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: OrderItem::class)]
    private Collection $orderItems;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: CartItem::class)]
    private Collection $cartItems;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->variants = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->orderItems = new ArrayCollection();
        $this->cartItems = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->specifications = [];
        $this->features = [];
        $this->seoData = [];
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
        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function getSubCategory(): ?SubCategory
    {
        return $this->subCategory;
    }

    public function setSubCategory(?SubCategory $subCategory): static
    {
        $this->subCategory = $subCategory;
        return $this;
    }

    public function getBrand(): ?Brand
    {
        return $this->brand;
    }

    public function setBrand(?Brand $brand): static
    {
        $this->brand = $brand;
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;
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

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(?string $shortDescription): static
    {
        $this->shortDescription = $shortDescription;
        return $this;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(?string $sku): static
    {
        $this->sku = $sku;
        return $this;
    }

    public function getBarcode(): ?string
    {
        return $this->barcode;
    }

    public function setBarcode(?string $barcode): static
    {
        $this->barcode = $barcode;
        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;
        return $this;
    }

    public function getPriceFloat(): float
    {
        return (float) $this->price;
    }

    public function getCompareAtPrice(): ?string
    {
        return $this->compareAtPrice;
    }

    public function setCompareAtPrice(?string $compareAtPrice): static
    {
        $this->compareAtPrice = $compareAtPrice;
        return $this;
    }

    public function getCompareAtPriceFloat(): ?float
    {
        return $this->compareAtPrice ? (float) $this->compareAtPrice : null;
    }

    public function getCostPrice(): ?string
    {
        return $this->costPrice;
    }

    public function setCostPrice(?string $costPrice): static
    {
        $this->costPrice = $costPrice;
        return $this;
    }

    public function getCostPriceFloat(): ?float
    {
        return $this->costPrice ? (float) $this->costPrice : null;
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

    public function getLowStockThreshold(): ?int
    {
        return $this->lowStockThreshold;
    }

    public function setLowStockThreshold(?int $lowStockThreshold): static
    {
        $this->lowStockThreshold = $lowStockThreshold;
        return $this;
    }

    public function isTrackQuantity(): bool
    {
        return $this->trackQuantity;
    }

    public function setTrackQuantity(bool $trackQuantity): static
    {
        $this->trackQuantity = $trackQuantity;
        return $this;
    }

    public function isContinueSellingWhenOutOfStock(): bool
    {
        return $this->continueSellingWhenOutOfStock;
    }

    public function setContinueSellingWhenOutOfStock(bool $continueSellingWhenOutOfStock): static
    {
        $this->continueSellingWhenOutOfStock = $continueSellingWhenOutOfStock;
        return $this;
    }

    public function getWeight(): ?string
    {
        return $this->weight;
    }

    public function setWeight(?string $weight): static
    {
        $this->weight = $weight;
        return $this;
    }

    public function getWeightFloat(): ?float
    {
        return $this->weight ? (float) $this->weight : null;
    }

    public function getCondition(): string
    {
        return $this->condition;
    }

    public function setCondition(string $condition): static
    {
        $this->condition = $condition;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        
        if ($status === self::STATUS_APPROVED && $this->approvedAt === null) {
            $this->approvedAt = new \DateTime();
            if ($this->publishedAt === null) {
                $this->publishedAt = new \DateTime();
            }
        }
        
        return $this;
    }

    public function getRejectionReason(): ?string
    {
        return $this->rejectionReason;
    }

    public function setRejectionReason(?string $rejectionReason): static
    {
        $this->rejectionReason = $rejectionReason;
        return $this;
    }

    public function getRating(): ?string
    {
        return $this->rating;
    }

    public function setRating(string $rating): static
    {
        $this->rating = $rating;
        return $this;
    }

    public function getRatingFloat(): float
    {
        return (float) $this->rating;
    }

    public function getTotalReviews(): int
    {
        return $this->totalReviews;
    }

    public function setTotalReviews(int $totalReviews): static
    {
        $this->totalReviews = $totalReviews;
        return $this;
    }

    public function getTotalSales(): int
    {
        return $this->totalSales;
    }

    public function setTotalSales(int $totalSales): static
    {
        $this->totalSales = $totalSales;
        return $this;
    }

    public function getViewCount(): int
    {
        return $this->viewCount;
    }

    public function setViewCount(int $viewCount): static
    {
        $this->viewCount = $viewCount;
        return $this;
    }

    public function incrementViewCount(): static
    {
        $this->viewCount++;
        return $this;
    }

    public function getSpecifications(): ?array
    {
        return $this->specifications;
    }

    public function setSpecifications(?array $specifications): static
    {
        $this->specifications = $specifications;
        return $this;
    }

    public function getFeatures(): ?array
    {
        return $this->features;
    }

    public function setFeatures(?array $features): static
    {
        $this->features = $features;
        return $this;
    }

    public function getSeoData(): ?array
    {
        return $this->seoData;
    }

    public function setSeoData(?array $seoData): static
    {
        $this->seoData = $seoData;
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

    public function getApprovedAt(): ?\DateTimeInterface
    {
        return $this->approvedAt;
    }

    public function setApprovedAt(?\DateTimeInterface $approvedAt): static
    {
        $this->approvedAt = $approvedAt;
        return $this;
    }

    public function getPublishedAt(): ?\DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeInterface $publishedAt): static
    {
        $this->publishedAt = $publishedAt;
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

    public function isFeatured(): bool
    {
        return $this->isFeatured;
    }

    public function setIsFeatured(bool $isFeatured): static
    {
        $this->isFeatured = $isFeatured;
        return $this;
    }

    public function isDigital(): bool
    {
        return $this->isDigital;
    }

    public function setIsDigital(bool $isDigital): static
    {
        $this->isDigital = $isDigital;
        return $this;
    }

    public function isRequiresShipping(): bool
    {
        return $this->requiresShipping;
    }

    public function setRequiresShipping(bool $requiresShipping): static
    {
        $this->requiresShipping = $requiresShipping;
        return $this;
    }

    /**
     * @return Collection<int, ProductImage>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(ProductImage $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setProduct($this);
        }

        return $this;
    }

    public function removeImage(ProductImage $image): static
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getProduct() === $this) {
                $image->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Variant>
     */
    public function getVariants(): Collection
    {
        return $this->variants;
    }

    public function addVariant(Variant $variant): static
    {
        if (!$this->variants->contains($variant)) {
            $this->variants->add($variant);
            $variant->setProduct($this);
        }

        return $this;
    }

    public function removeVariant(Variant $variant): static
    {
        if ($this->variants->removeElement($variant)) {
            // set the owning side to null (unless already changed)
            if ($variant->getProduct() === $this) {
                $variant->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setProduct($this);
        }

        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getProduct() === $this) {
                $review->setProduct(null);
            }
        }

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
            $orderItem->setProduct($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            // set the owning side to null (unless already changed)
            if ($orderItem->getProduct() === $this) {
                $orderItem->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CartItem>
     */
    public function getCartItems(): Collection
    {
        return $this->cartItems;
    }

    public function addCartItem(CartItem $cartItem): static
    {
        if (!$this->cartItems->contains($cartItem)) {
            $this->cartItems->add($cartItem);
            $cartItem->setProduct($this);
        }

        return $this;
    }

    public function removeCartItem(CartItem $cartItem): static
    {
        if ($this->cartItems->removeElement($cartItem)) {
            // set the owning side to null (unless already changed)
            if ($cartItem->getProduct() === $this) {
                $cartItem->setProduct(null);
            }
        }

        return $this;
    }

    public function isInStock(): bool
    {
        if (!$this->trackQuantity) {
            return true;
        }
        
        return $this->stock > 0 || $this->continueSellingWhenOutOfStock;
    }

    public function isLowStock(): bool
    {
        if (!$this->trackQuantity || $this->lowStockThreshold === null) {
            return false;
        }
        
        return $this->stock <= $this->lowStockThreshold;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    public function canBeSold(): bool
    {
        return $this->isActive() && 
               $this->isApproved() && 
               $this->seller?->canSell() &&
               $this->isInStock();
    }

    public function hasDiscount(): bool
    {
        return $this->compareAtPrice !== null && 
               $this->getCompareAtPriceFloat() > $this->getPriceFloat();
    }

    public function getDiscountPercentage(): ?int
    {
        if (!$this->hasDiscount()) {
            return null;
        }
        
        $discount = ($this->getCompareAtPriceFloat() - $this->getPriceFloat()) / $this->getCompareAtPriceFloat() * 100;
        return (int) round($discount);
    }

    public function getFeaturedImage(): ?ProductImage
    {
        foreach ($this->images as $image) {
            if ($image->isFeatured()) {
                return $image;
            }
        }
        
        return $this->images->first() ?: null;
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'Brouillon',
            self::STATUS_PENDING => 'En attente',
            self::STATUS_APPROVED => 'Approuvé',
            self::STATUS_REJECTED => 'Rejeté',
            self::STATUS_SUSPENDED => 'Suspendu',
            default => 'Inconnu'
        };
    }

    public function getConditionLabel(): string
    {
        return match($this->condition) {
            self::CONDITION_NEW => 'Neuf',
            self::CONDITION_USED => 'Occasion',
            self::CONDITION_REFURBISHED => 'Reconditionné',
            default => 'Inconnu'
        };
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Brouillon',
            self::STATUS_PENDING => 'En attente',
            self::STATUS_APPROVED => 'Approuvé',
            self::STATUS_REJECTED => 'Rejeté',
            self::STATUS_SUSPENDED => 'Suspendu',
        ];
    }

    public static function getConditions(): array
    {
        return [
            self::CONDITION_NEW => 'Neuf',
            self::CONDITION_USED => 'Occasion',
            self::CONDITION_REFURBISHED => 'Reconditionné',
        ];
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}