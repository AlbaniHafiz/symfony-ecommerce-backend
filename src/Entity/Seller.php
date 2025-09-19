<?php

namespace App\Entity;

use App\Entity\Trait\SoftDeleteable;
use App\Repository\SellerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SellerRepository::class)]
#[ORM\Table(name: 'seller')]
class Seller
{
    use SoftDeleteable;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_INACTIVE = 'inactive';

    public const VERIFICATION_NONE = 'none';
    public const VERIFICATION_PENDING = 'pending';
    public const VERIFICATION_VERIFIED = 'verified';
    public const VERIFICATION_REJECTED = 'rejected';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['seller:read'])]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'seller', targetEntity: User::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'L\'utilisateur est obligatoire.')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'sellers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Le marketplace est obligatoire.')]
    #[Groups(['seller:read'])]
    private ?Marketplace $marketplace = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: 'Le nom de l\'entreprise est obligatoire.')]
    #[Assert\Length(max: 150, maxMessage: 'Le nom de l\'entreprise ne peut pas dépasser {{ limit }} caractères.')]
    #[Groups(['seller:read', 'product:read'])]
    private ?string $companyName = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank(message: 'Le slug est obligatoire.')]
    #[Assert\Regex(pattern: '/^[a-z0-9\-]+$/', message: 'Le slug ne peut contenir que des lettres minuscules, chiffres et tirets.')]
    #[Groups(['seller:read', 'product:read'])]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['seller:read'])]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['seller:read', 'product:read'])]
    private ?string $logo = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['seller:read'])]
    private ?string $banner = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['seller:read'])]
    private ?string $businessRegistrationNumber = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['seller:read'])]
    private ?string $taxNumber = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'L\'email de contact est obligatoire.')]
    #[Assert\Email(message: 'L\'email de contact doit être valide.')]
    #[Groups(['seller:read'])]
    private ?string $contactEmail = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Regex(
        pattern: '/^[0-9+\-\s\(\)]+$/',
        message: 'Le numéro de téléphone n\'est pas valide.'
    )]
    #[Groups(['seller:read'])]
    private ?string $contactPhone = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'L\'adresse est obligatoire.')]
    #[Groups(['seller:read'])]
    private ?string $address = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'La ville est obligatoire.')]
    #[Groups(['seller:read'])]
    private ?string $city = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'Le code postal est obligatoire.')]
    #[Groups(['seller:read'])]
    private ?string $postalCode = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le pays est obligatoire.')]
    #[Groups(['seller:read'])]
    private ?string $country = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED, self::STATUS_SUSPENDED, self::STATUS_INACTIVE])]
    #[Groups(['seller:read'])]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::VERIFICATION_NONE, self::VERIFICATION_PENDING, self::VERIFICATION_VERIFIED, self::VERIFICATION_REJECTED])]
    #[Groups(['seller:read'])]
    private string $verificationStatus = self::VERIFICATION_NONE;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $rejectionReason = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: 'Le taux de commission doit être entre {{ min }} et {{ max }}.')]
    #[Groups(['seller:read'])]
    private ?string $commissionRate = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\PositiveOrZero(message: 'Le solde doit être positif ou nul.')]
    #[Groups(['seller:read'])]
    private ?string $balance = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\PositiveOrZero(message: 'Le solde en attente doit être positif ou nul.')]
    #[Groups(['seller:read'])]
    private ?string $pendingBalance = '0.00';

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $bankDetails = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $paymentMethods = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $shippingMethods = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $businessHours = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $socialMedia = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['seller:read'])]
    private ?string $website = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 2)]
    #[Assert\Range(min: 0, max: 5, notInRangeMessage: 'La note doit être entre {{ min }} et {{ max }}.')]
    #[Groups(['seller:read', 'product:read'])]
    private ?string $rating = '0.00';

    #[ORM\Column]
    #[Groups(['seller:read'])]
    private int $totalReviews = 0;

    #[ORM\Column]
    #[Groups(['seller:read'])]
    private int $totalSales = 0;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['seller:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['seller:read'])]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $approvedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $verifiedAt = null;

    #[ORM\Column]
    #[Groups(['seller:read'])]
    private bool $isActive = true;

    #[ORM\Column]
    private bool $isVacationMode = false;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $vacationMessage = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $vacationStartDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $vacationEndDate = null;

    #[ORM\OneToMany(mappedBy: 'seller', targetEntity: Product::class)]
    private Collection $products;

    #[ORM\OneToMany(mappedBy: 'seller', targetEntity: Order::class)]
    private Collection $orders;

    #[ORM\OneToMany(mappedBy: 'seller', targetEntity: SellerReview::class)]
    private Collection $reviews;

    #[ORM\OneToMany(mappedBy: 'seller', targetEntity: MarketplaceCommission::class)]
    private Collection $commissions;

    #[ORM\OneToMany(mappedBy: 'seller', targetEntity: SellerPayout::class)]
    private Collection $payouts;

    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->commissions = new ArrayCollection();
        $this->payouts = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->bankDetails = [];
        $this->paymentMethods = [];
        $this->shippingMethods = [];
        $this->businessHours = [];
        $this->socialMedia = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;
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

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): static
    {
        $this->companyName = $companyName;
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

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;
        return $this;
    }

    public function getBanner(): ?string
    {
        return $this->banner;
    }

    public function setBanner(?string $banner): static
    {
        $this->banner = $banner;
        return $this;
    }

    public function getBusinessRegistrationNumber(): ?string
    {
        return $this->businessRegistrationNumber;
    }

    public function setBusinessRegistrationNumber(?string $businessRegistrationNumber): static
    {
        $this->businessRegistrationNumber = $businessRegistrationNumber;
        return $this;
    }

    public function getTaxNumber(): ?string
    {
        return $this->taxNumber;
    }

    public function setTaxNumber(?string $taxNumber): static
    {
        $this->taxNumber = $taxNumber;
        return $this;
    }

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(string $contactEmail): static
    {
        $this->contactEmail = $contactEmail;
        return $this;
    }

    public function getContactPhone(): ?string
    {
        return $this->contactPhone;
    }

    public function setContactPhone(?string $contactPhone): static
    {
        $this->contactPhone = $contactPhone;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;
        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;
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
        }
        
        return $this;
    }

    public function getVerificationStatus(): string
    {
        return $this->verificationStatus;
    }

    public function setVerificationStatus(string $verificationStatus): static
    {
        $this->verificationStatus = $verificationStatus;
        
        if ($verificationStatus === self::VERIFICATION_VERIFIED && $this->verifiedAt === null) {
            $this->verifiedAt = new \DateTime();
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
        return (float) ($this->commissionRate ?? $this->marketplace?->getDefaultCommissionRate() ?? '5.00');
    }

    public function getBalance(): ?string
    {
        return $this->balance;
    }

    public function setBalance(string $balance): static
    {
        $this->balance = $balance;
        return $this;
    }

    public function getBalanceFloat(): float
    {
        return (float) $this->balance;
    }

    public function getPendingBalance(): ?string
    {
        return $this->pendingBalance;
    }

    public function setPendingBalance(string $pendingBalance): static
    {
        $this->pendingBalance = $pendingBalance;
        return $this;
    }

    public function getPendingBalanceFloat(): float
    {
        return (float) $this->pendingBalance;
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

    public function getPaymentMethods(): ?array
    {
        return $this->paymentMethods;
    }

    public function setPaymentMethods(?array $paymentMethods): static
    {
        $this->paymentMethods = $paymentMethods;
        return $this;
    }

    public function getShippingMethods(): ?array
    {
        return $this->shippingMethods;
    }

    public function setShippingMethods(?array $shippingMethods): static
    {
        $this->shippingMethods = $shippingMethods;
        return $this;
    }

    public function getBusinessHours(): ?array
    {
        return $this->businessHours;
    }

    public function setBusinessHours(?array $businessHours): static
    {
        $this->businessHours = $businessHours;
        return $this;
    }

    public function getSocialMedia(): ?array
    {
        return $this->socialMedia;
    }

    public function setSocialMedia(?array $socialMedia): static
    {
        $this->socialMedia = $socialMedia;
        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): static
    {
        $this->website = $website;
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

    public function getVerifiedAt(): ?\DateTimeInterface
    {
        return $this->verifiedAt;
    }

    public function setVerifiedAt(?\DateTimeInterface $verifiedAt): static
    {
        $this->verifiedAt = $verifiedAt;
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

    public function isVacationMode(): bool
    {
        return $this->isVacationMode;
    }

    public function setIsVacationMode(bool $isVacationMode): static
    {
        $this->isVacationMode = $isVacationMode;
        return $this;
    }

    public function getVacationMessage(): ?string
    {
        return $this->vacationMessage;
    }

    public function setVacationMessage(?string $vacationMessage): static
    {
        $this->vacationMessage = $vacationMessage;
        return $this;
    }

    public function getVacationStartDate(): ?\DateTimeInterface
    {
        return $this->vacationStartDate;
    }

    public function setVacationStartDate(?\DateTimeInterface $vacationStartDate): static
    {
        $this->vacationStartDate = $vacationStartDate;
        return $this;
    }

    public function getVacationEndDate(): ?\DateTimeInterface
    {
        return $this->vacationEndDate;
    }

    public function setVacationEndDate(?\DateTimeInterface $vacationEndDate): static
    {
        $this->vacationEndDate = $vacationEndDate;
        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setSeller($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getSeller() === $this) {
                $product->setSeller(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setSeller($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getSeller() === $this) {
                $order->setSeller(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SellerReview>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(SellerReview $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setSeller($this);
        }

        return $this;
    }

    public function removeReview(SellerReview $review): static
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getSeller() === $this) {
                $review->setSeller(null);
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
            $commission->setSeller($this);
        }

        return $this;
    }

    public function removeCommission(MarketplaceCommission $commission): static
    {
        if ($this->commissions->removeElement($commission)) {
            // set the owning side to null (unless already changed)
            if ($commission->getSeller() === $this) {
                $commission->setSeller(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SellerPayout>
     */
    public function getPayouts(): Collection
    {
        return $this->payouts;
    }

    public function addPayout(SellerPayout $payout): static
    {
        if (!$this->payouts->contains($payout)) {
            $this->payouts->add($payout);
            $payout->setSeller($this);
        }

        return $this;
    }

    public function removePayout(SellerPayout $payout): static
    {
        if ($this->payouts->removeElement($payout)) {
            // set the owning side to null (unless already changed)
            if ($payout->getSeller() === $this) {
                $payout->setSeller(null);
            }
        }

        return $this;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isVerified(): bool
    {
        return $this->verificationStatus === self::VERIFICATION_VERIFIED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    public function canSell(): bool
    {
        return $this->isActive() && 
               $this->isApproved() && 
               !$this->isSuspended() && 
               !$this->isVacationMode();
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'En attente',
            self::STATUS_APPROVED => 'Approuvé',
            self::STATUS_REJECTED => 'Rejeté',
            self::STATUS_SUSPENDED => 'Suspendu',
            self::STATUS_INACTIVE => 'Inactif',
            default => 'Inconnu'
        };
    }

    public function getVerificationStatusLabel(): string
    {
        return match($this->verificationStatus) {
            self::VERIFICATION_NONE => 'Non vérifié',
            self::VERIFICATION_PENDING => 'En attente de vérification',
            self::VERIFICATION_VERIFIED => 'Vérifié',
            self::VERIFICATION_REJECTED => 'Vérification rejetée',
            default => 'Inconnu'
        };
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'En attente',
            self::STATUS_APPROVED => 'Approuvé',
            self::STATUS_REJECTED => 'Rejeté',
            self::STATUS_SUSPENDED => 'Suspendu',
            self::STATUS_INACTIVE => 'Inactif',
        ];
    }

    public static function getVerificationStatuses(): array
    {
        return [
            self::VERIFICATION_NONE => 'Non vérifié',
            self::VERIFICATION_PENDING => 'En attente de vérification',
            self::VERIFICATION_VERIFIED => 'Vérifié',
            self::VERIFICATION_REJECTED => 'Vérification rejetée',
        ];
    }

    public function __toString(): string
    {
        return $this->companyName ?? '';
    }
}