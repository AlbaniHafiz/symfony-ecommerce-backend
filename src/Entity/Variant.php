<?php

namespace App\Entity;

use App\Entity\Trait\SoftDeleteable;
use App\Repository\VariantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: VariantRepository::class)]
#[ORM\Table(name: 'variant')]
class Variant
{
    use SoftDeleteable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['variant:read', 'product:detail'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'variants')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Le produit est obligatoire.')]
    private ?Product $product = null;

    #[ORM\ManyToOne(inversedBy: 'variants')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Le type de variante est obligatoire.')]
    #[Groups(['variant:read', 'product:detail'])]
    private ?VariantType $variantType = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'La valeur de la variante est obligatoire.')]
    #[Assert\Length(max: 100, maxMessage: 'La valeur ne peut pas dépasser {{ limit }} caractères.')]
    #[Groups(['variant:read', 'product:detail'])]
    private ?string $value = null;

    #[ORM\Column(length: 7, nullable: true)]
    #[Assert\Regex(pattern: '/^#[0-9a-fA-F]{6}$/', message: 'La couleur doit être au format hexadécimal (#000000).')]
    #[Groups(['variant:read', 'product:detail'])]
    private ?string $colorCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['variant:read', 'product:detail'])]
    private ?string $imagePath = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['variant:read', 'product:detail'])]
    private ?string $sku = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero(message: 'Le prix supplémentaire doit être positif.')]
    #[Groups(['variant:read', 'product:detail'])]
    private ?string $priceAdjustment = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: 'Le stock doit être positif ou nul.')]
    #[Groups(['variant:read', 'product:detail'])]
    private ?int $stock = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero(message: 'Le poids doit être positif.')]
    #[Groups(['variant:read', 'product:detail'])]
    private ?string $weight = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['variant:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['variant:read'])]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column]
    #[Groups(['variant:read', 'product:detail'])]
    private bool $isActive = true;

    #[ORM\Column]
    #[Groups(['variant:read', 'product:detail'])]
    private int $sortOrder = 0;

    #[ORM\OneToMany(mappedBy: 'variant', targetEntity: OrderItem::class)]
    private Collection $orderItems;

    #[ORM\OneToMany(mappedBy: 'variant', targetEntity: CartItem::class)]
    private Collection $cartItems;

    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
        $this->cartItems = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;
        return $this;
    }

    public function getVariantType(): ?VariantType
    {
        return $this->variantType;
    }

    public function setVariantType(?VariantType $variantType): static
    {
        $this->variantType = $variantType;
        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;
        return $this;
    }

    public function getColorCode(): ?string
    {
        return $this->colorCode;
    }

    public function setColorCode(?string $colorCode): static
    {
        $this->colorCode = $colorCode;
        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(?string $imagePath): static
    {
        $this->imagePath = $imagePath;
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

    public function getPriceAdjustment(): ?string
    {
        return $this->priceAdjustment;
    }

    public function setPriceAdjustment(?string $priceAdjustment): static
    {
        $this->priceAdjustment = $priceAdjustment;
        return $this;
    }

    public function getPriceAdjustmentFloat(): float
    {
        return (float) ($this->priceAdjustment ?? '0.00');
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(?int $stock): static
    {
        $this->stock = $stock;
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

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;
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
            $orderItem->setVariant($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            // set the owning side to null (unless already changed)
            if ($orderItem->getVariant() === $this) {
                $orderItem->setVariant(null);
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
            $cartItem->setVariant($this);
        }

        return $this;
    }

    public function removeCartItem(CartItem $cartItem): static
    {
        if ($this->cartItems->removeElement($cartItem)) {
            // set the owning side to null (unless already changed)
            if ($cartItem->getVariant() === $this) {
                $cartItem->setVariant(null);
            }
        }

        return $this;
    }

    public function getFinalPrice(): float
    {
        $basePrice = $this->product?->getPriceFloat() ?? 0.0;
        return $basePrice + $this->getPriceAdjustmentFloat();
    }

    public function getEffectiveStock(): int
    {
        // If variant has specific stock, use it; otherwise use product stock
        return $this->stock ?? $this->product?->getStock() ?? 0;
    }

    public function isInStock(): bool
    {
        if (!$this->product?->isTrackQuantity()) {
            return true;
        }
        
        return $this->getEffectiveStock() > 0 || $this->product?->isContinueSellingWhenOutOfStock();
    }

    public function getDisplayName(): string
    {
        return $this->variantType?->getName() . ': ' . $this->value;
    }

    public function hasImage(): bool
    {
        return $this->imagePath !== null;
    }

    public function hasColor(): bool
    {
        return $this->colorCode !== null;
    }

    public function getImageUrl(): ?string
    {
        if (!$this->imagePath) {
            return null;
        }
        
        // This method should be adapted based on your file storage strategy
        return '/uploads/variants/' . $this->imagePath;
    }

    public function __toString(): string
    {
        return $this->getDisplayName();
    }
}