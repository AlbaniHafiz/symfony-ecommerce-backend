<?php

namespace App\Entity;

use App\Entity\Trait\SoftDeleteable;
use App\Repository\CartItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CartItemRepository::class)]
#[ORM\Table(name: 'cart_item')]
class CartItem
{
    use SoftDeleteable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['cart:read', 'cart:detail'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'cartItems')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Le panier est obligatoire.')]
    private ?Cart $cart = null;

    #[ORM\ManyToOne(inversedBy: 'cartItems')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Le produit est obligatoire.')]
    #[Groups(['cart:read', 'cart:detail'])]
    private ?Product $product = null;

    #[ORM\ManyToOne(inversedBy: 'cartItems')]
    #[Groups(['cart:read', 'cart:detail'])]
    private ?Variant $variant = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'La quantité est obligatoire.')]
    #[Assert\Positive(message: 'La quantité doit être positive.')]
    #[Groups(['cart:read', 'cart:detail'])]
    private ?int $quantity = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'Le prix unitaire est obligatoire.')]
    #[Assert\PositiveOrZero(message: 'Le prix unitaire doit être positif.')]
    #[Groups(['cart:read', 'cart:detail'])]
    private ?string $unitPrice = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['cart:read', 'cart:detail'])]
    private ?string $totalPrice = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['cart:detail'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['cart:detail'])]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCart(): ?Cart
    {
        return $this->cart;
    }

    public function setCart(?Cart $cart): static
    {
        $this->cart = $cart;
        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;
        $this->updatePrice();
        return $this;
    }

    public function getVariant(): ?Variant
    {
        return $this->variant;
    }

    public function setVariant(?Variant $variant): static
    {
        $this->variant = $variant;
        $this->updatePrice();
        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;
        $this->calculateTotalPrice();
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function getUnitPrice(): ?string
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(string $unitPrice): static
    {
        $this->unitPrice = $unitPrice;
        $this->calculateTotalPrice();
        return $this;
    }

    public function getUnitPriceFloat(): float
    {
        return (float) $this->unitPrice;
    }

    public function getTotalPrice(): ?string
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(string $totalPrice): static
    {
        $this->totalPrice = $totalPrice;
        return $this;
    }

    public function getTotalPriceFloat(): float
    {
        return (float) $this->totalPrice;
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

    public function updatePrice(): void
    {
        if ($this->variant) {
            $this->unitPrice = (string) $this->variant->getFinalPrice();
        } elseif ($this->product) {
            $this->unitPrice = $this->product->getPrice();
        }
        
        $this->calculateTotalPrice();
    }

    public function calculateTotalPrice(): void
    {
        if ($this->unitPrice !== null && $this->quantity !== null) {
            $total = $this->getUnitPriceFloat() * $this->quantity;
            $this->totalPrice = (string) number_format($total, 2, '.', '');
        }
    }

    public function increaseQuantity(int $amount = 1): static
    {
        $this->quantity += $amount;
        $this->calculateTotalPrice();
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function decreaseQuantity(int $amount = 1): static
    {
        $this->quantity = max(0, $this->quantity - $amount);
        $this->calculateTotalPrice();
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function isInStock(): bool
    {
        if ($this->variant) {
            return $this->variant->isInStock() && $this->variant->getEffectiveStock() >= $this->quantity;
        }
        
        return $this->product?->isInStock() && $this->product->getStock() >= $this->quantity;
    }

    public function getAvailableStock(): int
    {
        if ($this->variant) {
            return $this->variant->getEffectiveStock();
        }
        
        return $this->product?->getStock() ?? 0;
    }

    public function getDisplayName(): string
    {
        $name = $this->product?->getName() ?? 'Produit supprimé';
        
        if ($this->variant) {
            $name .= ' - ' . $this->variant->getDisplayName();
        }
        
        return $name;
    }

    public function getProductImage(): ?string
    {
        // Try variant image first, then product featured image
        if ($this->variant && $this->variant->hasImage()) {
            return $this->variant->getImageUrl();
        }
        
        return $this->product?->getFeaturedImage()?->getImagePath();
    }

    public function getSeller(): ?Seller
    {
        return $this->product?->getSeller();
    }

    public function canBePurchased(): bool
    {
        return $this->product?->canBeSold() && $this->isInStock();
    }

    public function hasDiscount(): bool
    {
        return $this->product?->hasDiscount() ?? false;
    }

    public function getOriginalPrice(): ?float
    {
        if ($this->variant) {
            $basePrice = $this->product?->getCompareAtPriceFloat();
            if ($basePrice) {
                return $basePrice + $this->variant->getPriceAdjustmentFloat();
            }
        }
        
        return $this->product?->getCompareAtPriceFloat();
    }

    public function getSavingsAmount(): ?float
    {
        $originalPrice = $this->getOriginalPrice();
        if ($originalPrice && $originalPrice > $this->getUnitPriceFloat()) {
            return $originalPrice - $this->getUnitPriceFloat();
        }
        
        return null;
    }

    public function getSavingsPercentage(): ?int
    {
        $originalPrice = $this->getOriginalPrice();
        $savingsAmount = $this->getSavingsAmount();
        
        if ($originalPrice && $savingsAmount) {
            return (int) round(($savingsAmount / $originalPrice) * 100);
        }
        
        return null;
    }

    public function __toString(): string
    {
        return $this->getDisplayName() . ' (x' . $this->quantity . ')';
    }
}