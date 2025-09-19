<?php

namespace App\Entity;

use App\Entity\Trait\SoftDeleteable;
use App\Repository\OrderItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
#[ORM\Table(name: 'order_item')]
class OrderItem
{
    use SoftDeleteable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['order:read', 'order:detail'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'La commande est obligatoire.')]
    private ?Order $order = null;

    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Le produit est obligatoire.')]
    #[Groups(['order:read', 'order:detail'])]
    private ?Product $product = null;

    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    #[Groups(['order:read', 'order:detail'])]
    private ?Variant $variant = null;

    #[ORM\Column(length: 200)]
    #[Groups(['order:read', 'order:detail'])]
    private ?string $productName = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['order:read', 'order:detail'])]
    private ?string $productSku = null;

    #[ORM\Column(length: 200, nullable: true)]
    #[Groups(['order:read', 'order:detail'])]
    private ?string $variantName = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'Le prix unitaire est obligatoire.')]
    #[Assert\PositiveOrZero(message: 'Le prix unitaire doit être positif.')]
    #[Groups(['order:read', 'order:detail'])]
    private ?string $unitPrice = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'La quantité est obligatoire.')]
    #[Assert\Positive(message: 'La quantité doit être positive.')]
    #[Groups(['order:read', 'order:detail'])]
    private ?int $quantity = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['order:read', 'order:detail'])]
    private ?string $totalPrice = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Groups(['order:read', 'order:detail'])]
    private ?string $discountAmount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $commissionAmount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $commissionRate = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['order:detail'])]
    private ?array $productData = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['order:detail'])]
    private ?array $variantData = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['order:detail'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['order:detail'])]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->productData = [];
        $this->variantData = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): static
    {
        $this->order = $order;
        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;
        
        // Auto-populate product data when product is set
        if ($product) {
            $this->productName = $product->getName();
            $this->productSku = $product->getSku();
            $this->productData = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'slug' => $product->getSlug(),
                'sku' => $product->getSku(),
                'description' => $product->getShortDescription(),
                'category' => $product->getCategory()?->getName(),
                'brand' => $product->getBrand()?->getName(),
                'seller' => $product->getSeller()?->getCompanyName(),
                'image' => $product->getFeaturedImage()?->getImagePath(),
            ];
        }
        
        return $this;
    }

    public function getVariant(): ?Variant
    {
        return $this->variant;
    }

    public function setVariant(?Variant $variant): static
    {
        $this->variant = $variant;
        
        // Auto-populate variant data when variant is set
        if ($variant) {
            $this->variantName = $variant->getDisplayName();
            $this->variantData = [
                'id' => $variant->getId(),
                'type' => $variant->getVariantType()?->getName(),
                'value' => $variant->getValue(),
                'sku' => $variant->getSku(),
                'colorCode' => $variant->getColorCode(),
                'imagePath' => $variant->getImagePath(),
            ];
        }
        
        return $this;
    }

    public function getProductName(): ?string
    {
        return $this->productName;
    }

    public function setProductName(string $productName): static
    {
        $this->productName = $productName;
        return $this;
    }

    public function getProductSku(): ?string
    {
        return $this->productSku;
    }

    public function setProductSku(?string $productSku): static
    {
        $this->productSku = $productSku;
        return $this;
    }

    public function getVariantName(): ?string
    {
        return $this->variantName;
    }

    public function setVariantName(?string $variantName): static
    {
        $this->variantName = $variantName;
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

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;
        $this->calculateTotalPrice();
        return $this;
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

    public function getDiscountAmount(): ?string
    {
        return $this->discountAmount;
    }

    public function setDiscountAmount(?string $discountAmount): static
    {
        $this->discountAmount = $discountAmount;
        return $this;
    }

    public function getDiscountAmountFloat(): float
    {
        return (float) ($this->discountAmount ?? '0.00');
    }

    public function getCommissionAmount(): ?string
    {
        return $this->commissionAmount;
    }

    public function setCommissionAmount(?string $commissionAmount): static
    {
        $this->commissionAmount = $commissionAmount;
        return $this;
    }

    public function getCommissionAmountFloat(): float
    {
        return (float) ($this->commissionAmount ?? '0.00');
    }

    public function getCommissionRate(): ?string
    {
        return $this->commissionRate;
    }

    public function setCommissionRate(?string $commissionRate): static
    {
        $this->commissionRate = $commissionRate;
        return $this;
    }

    public function getCommissionRateFloat(): float
    {
        return (float) ($this->commissionRate ?? '0.00');
    }

    public function getProductData(): ?array
    {
        return $this->productData;
    }

    public function setProductData(?array $productData): static
    {
        $this->productData = $productData;
        return $this;
    }

    public function getVariantData(): ?array
    {
        return $this->variantData;
    }

    public function setVariantData(?array $variantData): static
    {
        $this->variantData = $variantData;
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

    public function calculateTotalPrice(): void
    {
        if ($this->unitPrice !== null && $this->quantity !== null) {
            $total = $this->getUnitPriceFloat() * $this->quantity;
            $this->totalPrice = (string) number_format($total, 2, '.', '');
        }
    }

    public function calculateCommission(?float $rate = null): void
    {
        $commissionRate = $rate ?? $this->getCommissionRateFloat();
        
        if ($commissionRate > 0) {
            $commission = $this->getTotalPriceFloat() * ($commissionRate / 100);
            $this->commissionAmount = (string) number_format($commission, 2, '.', '');
        }
    }

    public function getSellerAmount(): float
    {
        return $this->getTotalPriceFloat() - $this->getCommissionAmountFloat() - $this->getDiscountAmountFloat();
    }

    public function getDisplayName(): string
    {
        $name = $this->productName;
        
        if ($this->variantName) {
            $name .= ' - ' . $this->variantName;
        }
        
        return $name;
    }

    public function getProductImage(): ?string
    {
        // Try variant image first, then product image
        if ($this->variantData && isset($this->variantData['imagePath'])) {
            return $this->variantData['imagePath'];
        }
        
        if ($this->productData && isset($this->productData['image'])) {
            return $this->productData['image'];
        }
        
        return null;
    }

    public function hasDiscount(): bool
    {
        return $this->getDiscountAmountFloat() > 0;
    }

    public function __toString(): string
    {
        return $this->getDisplayName();
    }
}