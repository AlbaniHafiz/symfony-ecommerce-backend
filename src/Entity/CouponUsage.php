<?php

namespace App\Entity;

use App\Entity\Trait\SoftDeleteable;
use App\Repository\CouponUsageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CouponUsageRepository::class)]
#[ORM\Table(name: 'coupon_usage')]
class CouponUsage
{
    use SoftDeleteable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['coupon_usage:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'couponUsages')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Le code de coupon est obligatoire.')]
    #[Groups(['coupon_usage:read'])]
    private ?CouponCode $couponCode = null;

    #[ORM\ManyToOne(inversedBy: 'couponUsages')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'L\'utilisateur est obligatoire.')]
    #[Groups(['coupon_usage:read'])]
    private ?User $user = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['coupon_usage:read'])]
    private ?string $orderNumber = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'Le montant de la commande est obligatoire.')]
    #[Assert\PositiveOrZero(message: 'Le montant de la commande doit être positif.')]
    #[Groups(['coupon_usage:read'])]
    private ?string $orderAmount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'Le montant de la remise est obligatoire.')]
    #[Assert\PositiveOrZero(message: 'Le montant de la remise doit être positif.')]
    #[Groups(['coupon_usage:read'])]
    private ?string $discountAmount = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['coupon_usage:read'])]
    private ?string $sessionId = null;

    #[ORM\Column(length: 45, nullable: true)]
    #[Groups(['coupon_usage:read'])]
    private ?string $ipAddress = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $metadata = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['coupon_usage:read'])]
    private ?\DateTimeInterface $usedAt = null;

    public function __construct()
    {
        $this->usedAt = new \DateTime();
        $this->metadata = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCouponCode(): ?CouponCode
    {
        return $this->couponCode;
    }

    public function setCouponCode(?CouponCode $couponCode): static
    {
        $this->couponCode = $couponCode;
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

    public function getOrderNumber(): ?string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(?string $orderNumber): static
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
        return $this;
    }

    public function getOrderAmountFloat(): float
    {
        return (float) $this->orderAmount;
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

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function setSessionId(?string $sessionId): static
    {
        $this->sessionId = $sessionId;
        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): static
    {
        $this->userAgent = $userAgent;
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

    public function getUsedAt(): ?\DateTimeInterface
    {
        return $this->usedAt;
    }

    public function setUsedAt(\DateTimeInterface $usedAt): static
    {
        $this->usedAt = $usedAt;
        return $this;
    }

    public function getDiscountPercentage(): float
    {
        if ($this->getOrderAmountFloat() > 0) {
            return ($this->getDiscountAmountFloat() / $this->getOrderAmountFloat()) * 100;
        }
        
        return 0.0;
    }

    public function getSavingsAmount(): float
    {
        return $this->getDiscountAmountFloat();
    }

    public function getFinalAmount(): float
    {
        return $this->getOrderAmountFloat() - $this->getDiscountAmountFloat();
    }

    public function __toString(): string
    {
        return ($this->couponCode?->getCode() ?? 'Coupon') . ' - ' . ($this->user?->getFullName() ?? 'Utilisateur inconnu');
    }
}