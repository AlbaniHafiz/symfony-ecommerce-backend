<?php

namespace App\Entity;

use App\Entity\Trait\SoftDeleteable;
use App\Repository\CartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CartRepository::class)]
#[ORM\Table(name: 'cart')]
class Cart
{
    use SoftDeleteable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['cart:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'carts')]
    #[Assert\NotNull(message: 'L\'utilisateur est obligatoire.')]
    #[Groups(['cart:read'])]
    private ?User $user = null;

    #[ORM\Column(length: 100, unique: true, nullable: true)]
    #[Groups(['cart:read'])]
    private ?string $sessionId = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['cart:read'])]
    private ?string $subtotal = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['cart:read'])]
    private ?string $taxAmount = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['cart:read'])]
    private ?string $shippingAmount = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['cart:read'])]
    private ?string $discountAmount = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['cart:read'])]
    private ?string $total = '0.00';

    #[ORM\Column(length: 3)]
    #[Groups(['cart:read'])]
    private string $currency = 'EUR';

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $appliedCoupons = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $metadata = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['cart:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['cart:read'])]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastAccessedAt = null;

    #[ORM\Column]
    #[Groups(['cart:read'])]
    private bool $isActive = true;

    #[ORM\OneToMany(mappedBy: 'cart', targetEntity: CartItem::class, cascade: ['persist', 'remove'])]
    #[Groups(['cart:read', 'cart:detail'])]
    private Collection $cartItems;

    public function __construct()
    {
        $this->cartItems = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->lastAccessedAt = new \DateTime();
        $this->appliedCoupons = [];
        $this->metadata = [];
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function setSessionId(?string $sessionId): static
    {
        $this->sessionId = $sessionId;
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

    public function getAppliedCoupons(): ?array
    {
        return $this->appliedCoupons;
    }

    public function setAppliedCoupons(?array $appliedCoupons): static
    {
        $this->appliedCoupons = $appliedCoupons;
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

    public function getLastAccessedAt(): ?\DateTimeInterface
    {
        return $this->lastAccessedAt;
    }

    public function setLastAccessedAt(?\DateTimeInterface $lastAccessedAt): static
    {
        $this->lastAccessedAt = $lastAccessedAt;
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
            $cartItem->setCart($this);
        }

        return $this;
    }

    public function removeCartItem(CartItem $cartItem): static
    {
        if ($this->cartItems->removeElement($cartItem)) {
            // set the owning side to null (unless already changed)
            if ($cartItem->getCart() === $this) {
                $cartItem->setCart(null);
            }
        }

        return $this;
    }

    public function updateLastAccessedAt(): static
    {
        $this->lastAccessedAt = new \DateTime();
        return $this;
    }

    public function calculateTotals(): void
    {
        $subtotal = 0;
        
        foreach ($this->cartItems as $item) {
            $subtotal += $item->getUnitPriceFloat() * $item->getQuantity();
        }
        
        $this->subtotal = (string) number_format($subtotal, 2, '.', '');
        
        // Calculate total
        $total = $subtotal + $this->getTaxAmountFloat() + $this->getShippingAmountFloat() - $this->getDiscountAmountFloat();
        $this->total = (string) number_format($total, 2, '.', '');
        
        $this->updatedAt = new \DateTime();
    }

    public function getTotalItemCount(): int
    {
        $count = 0;
        foreach ($this->cartItems as $item) {
            $count += $item->getQuantity();
        }
        return $count;
    }

    public function getUniqueProductCount(): int
    {
        return $this->cartItems->count();
    }

    public function isEmpty(): bool
    {
        return $this->cartItems->isEmpty();
    }

    public function hasProduct(Product $product, ?Variant $variant = null): bool
    {
        foreach ($this->cartItems as $item) {
            if ($item->getProduct() === $product && $item->getVariant() === $variant) {
                return true;
            }
        }
        return false;
    }

    public function findCartItem(Product $product, ?Variant $variant = null): ?CartItem
    {
        foreach ($this->cartItems as $item) {
            if ($item->getProduct() === $product && $item->getVariant() === $variant) {
                return $item;
            }
        }
        return null;
    }

    public function getItemsBySeller(): array
    {
        $itemsBySeller = [];
        
        foreach ($this->cartItems as $item) {
            $seller = $item->getProduct()?->getSeller();
            $sellerId = $seller?->getId() ?? 'unknown';
            
            if (!isset($itemsBySeller[$sellerId])) {
                $itemsBySeller[$sellerId] = [
                    'seller' => $seller,
                    'items' => [],
                    'subtotal' => 0.0,
                ];
            }
            
            $itemsBySeller[$sellerId]['items'][] = $item;
            $itemsBySeller[$sellerId]['subtotal'] += $item->getUnitPriceFloat() * $item->getQuantity();
        }
        
        return $itemsBySeller;
    }

    public function getSellers(): array
    {
        $sellers = [];
        
        foreach ($this->cartItems as $item) {
            $seller = $item->getProduct()?->getSeller();
            if ($seller && !in_array($seller, $sellers, true)) {
                $sellers[] = $seller;
            }
        }
        
        return $sellers;
    }

    public function hasMultipleSellers(): bool
    {
        return count($this->getSellers()) > 1;
    }

    public function canApplyCoupon(CouponCode $coupon): bool
    {
        // Basic validation - can be extended based on coupon rules
        if (!$coupon->isActive() || !$coupon->isValid()) {
            return false;
        }
        
        // Check if already applied
        foreach ($this->appliedCoupons ?? [] as $appliedCoupon) {
            if ($appliedCoupon['code'] === $coupon->getCode()) {
                return false;
            }
        }
        
        // Check minimum order amount
        if ($coupon->getMinimumOrderAmount() && $this->getSubtotalFloat() < $coupon->getMinimumOrderAmountFloat()) {
            return false;
        }
        
        return true;
    }

    public function applyCoupon(CouponCode $coupon): bool
    {
        if (!$this->canApplyCoupon($coupon)) {
            return false;
        }
        
        $this->appliedCoupons = $this->appliedCoupons ?? [];
        $this->appliedCoupons[] = [
            'id' => $coupon->getId(),
            'code' => $coupon->getCode(),
            'type' => $coupon->getDiscountType(),
            'value' => $coupon->getDiscountValue(),
            'appliedAt' => (new \DateTime())->format('c'),
        ];
        
        return true;
    }

    public function removeCoupon(string $couponCode): bool
    {
        if (!$this->appliedCoupons) {
            return false;
        }
        
        $this->appliedCoupons = array_filter($this->appliedCoupons, function($coupon) use ($couponCode) {
            return $coupon['code'] !== $couponCode;
        });
        
        return true;
    }

    public function clearCoupons(): static
    {
        $this->appliedCoupons = [];
        return $this;
    }

    public function clear(): static
    {
        $this->cartItems->clear();
        $this->clearCoupons();
        $this->calculateTotals();
        return $this;
    }

    public function isExpired(int $expirationHours = 24): bool
    {
        if (!$this->lastAccessedAt) {
            return false;
        }
        
        $expirationTime = (clone $this->lastAccessedAt)->add(new \DateInterval('PT' . $expirationHours . 'H'));
        return new \DateTime() > $expirationTime;
    }

    public function __toString(): string
    {
        return 'Cart #' . $this->id . ' (' . $this->getTotalItemCount() . ' items)';
    }
}