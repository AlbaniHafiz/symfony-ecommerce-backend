<?php

namespace App\Entity;

use App\Entity\Trait\SoftDeleteable;
use App\Repository\MarketplaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: MarketplaceRepository::class)]
#[ORM\Table(name: 'marketplace')]
class Marketplace
{
    use SoftDeleteable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['marketplace:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: 'Le nom du marketplace est obligatoire.')]
    #[Assert\Length(max: 150, maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.')]
    #[Groups(['marketplace:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank(message: 'Le slug est obligatoire.')]
    #[Assert\Regex(pattern: '/^[a-z0-9\-]+$/', message: 'Le slug ne peut contenir que des lettres minuscules, chiffres et tirets.')]
    #[Groups(['marketplace:read'])]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['marketplace:read'])]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['marketplace:read'])]
    private ?string $logo = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['marketplace:read'])]
    private ?string $banner = null;

    #[ORM\Column(length: 7, nullable: true)]
    #[Assert\Regex(pattern: '/^#[0-9a-fA-F]{6}$/', message: 'La couleur primaire doit être au format hexadécimal (#000000).')]
    #[Groups(['marketplace:read'])]
    private ?string $primaryColor = null;

    #[ORM\Column(length: 7, nullable: true)]
    #[Assert\Regex(pattern: '/^#[0-9a-fA-F]{6}$/', message: 'La couleur secondaire doit être au format hexadécimal (#000000).')]
    #[Groups(['marketplace:read'])]
    private ?string $secondaryColor = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Email(message: 'L\'email de contact doit être valide.')]
    #[Groups(['marketplace:read'])]
    private ?string $contactEmail = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Regex(
        pattern: '/^[0-9+\-\s\(\)]+$/',
        message: 'Le numéro de téléphone n\'est pas valide.'
    )]
    #[Groups(['marketplace:read'])]
    private ?string $contactPhone = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['marketplace:read'])]
    private ?string $address = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['marketplace:read'])]
    private ?string $city = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['marketplace:read'])]
    private ?string $postalCode = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['marketplace:read'])]
    private ?string $country = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['marketplace:read'])]
    private ?string $website = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['marketplace:read'])]
    private ?array $socialMedia = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    #[Assert\NotBlank(message: 'Le taux de commission par défaut est obligatoire.')]
    #[Assert\Range(min: 0, max: 100, notInRangeMessage: 'Le taux de commission doit être entre {{ min }} et {{ max }}.')]
    #[Groups(['marketplace:read'])]
    private ?string $defaultCommissionRate = '5.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\PositiveOrZero(message: 'Les frais de transaction doivent être positifs ou nuls.')]
    #[Groups(['marketplace:read'])]
    private ?string $transactionFee = '0.00';

    #[ORM\Column(length: 3)]
    #[Assert\NotBlank(message: 'La devise est obligatoire.')]
    #[Assert\Length(exactly: 3, exactMessage: 'La devise doit faire exactement {{ limit }} caractères.')]
    #[Groups(['marketplace:read'])]
    private string $currency = 'EUR';

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: 'La langue par défaut est obligatoire.')]
    #[Groups(['marketplace:read'])]
    private string $defaultLanguage = 'fr';

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le fuseau horaire est obligatoire.')]
    #[Groups(['marketplace:read'])]
    private string $timezone = 'Europe/Paris';

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['marketplace:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['marketplace:read'])]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column]
    #[Groups(['marketplace:read'])]
    private bool $isActive = true;

    #[ORM\Column]
    private bool $allowSellerRegistration = true;

    #[ORM\Column]
    private bool $requireSellerApproval = true;

    #[ORM\Column]
    private bool $allowGuestCheckout = true;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $paymentMethods = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $shippingMethods = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $settings = null;

    #[ORM\OneToMany(mappedBy: 'marketplace', targetEntity: Seller::class)]
    private Collection $sellers;

    #[ORM\OneToMany(mappedBy: 'marketplace', targetEntity: Order::class)]
    private Collection $orders;

    #[ORM\OneToMany(mappedBy: 'marketplace', targetEntity: MarketplaceCommission::class)]
    private Collection $commissions;

    #[ORM\OneToMany(mappedBy: 'marketplace', targetEntity: MarketplaceSettings::class)]
    private Collection $marketplaceSettings;

    public function __construct()
    {
        $this->sellers = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->commissions = new ArrayCollection();
        $this->marketplaceSettings = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->socialMedia = [];
        $this->paymentMethods = [];
        $this->shippingMethods = [];
        $this->settings = [];
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPrimaryColor(): ?string
    {
        return $this->primaryColor;
    }

    public function setPrimaryColor(?string $primaryColor): static
    {
        $this->primaryColor = $primaryColor;
        return $this;
    }

    public function getSecondaryColor(): ?string
    {
        return $this->secondaryColor;
    }

    public function setSecondaryColor(?string $secondaryColor): static
    {
        $this->secondaryColor = $secondaryColor;
        return $this;
    }

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(?string $contactEmail): static
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

    public function setAddress(?string $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;
        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): static
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;
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

    public function getSocialMedia(): ?array
    {
        return $this->socialMedia;
    }

    public function setSocialMedia(?array $socialMedia): static
    {
        $this->socialMedia = $socialMedia;
        return $this;
    }

    public function getDefaultCommissionRate(): ?string
    {
        return $this->defaultCommissionRate;
    }

    public function setDefaultCommissionRate(string $defaultCommissionRate): static
    {
        $this->defaultCommissionRate = $defaultCommissionRate;
        return $this;
    }

    public function getDefaultCommissionRateFloat(): float
    {
        return (float) $this->defaultCommissionRate;
    }

    public function getTransactionFee(): ?string
    {
        return $this->transactionFee;
    }

    public function setTransactionFee(string $transactionFee): static
    {
        $this->transactionFee = $transactionFee;
        return $this;
    }

    public function getTransactionFeeFloat(): float
    {
        return (float) $this->transactionFee;
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

    public function getDefaultLanguage(): string
    {
        return $this->defaultLanguage;
    }

    public function setDefaultLanguage(string $defaultLanguage): static
    {
        $this->defaultLanguage = $defaultLanguage;
        return $this;
    }

    public function getTimezone(): string
    {
        return $this->timezone;
    }

    public function setTimezone(string $timezone): static
    {
        $this->timezone = $timezone;
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

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function isAllowSellerRegistration(): bool
    {
        return $this->allowSellerRegistration;
    }

    public function setAllowSellerRegistration(bool $allowSellerRegistration): static
    {
        $this->allowSellerRegistration = $allowSellerRegistration;
        return $this;
    }

    public function isRequireSellerApproval(): bool
    {
        return $this->requireSellerApproval;
    }

    public function setRequireSellerApproval(bool $requireSellerApproval): static
    {
        $this->requireSellerApproval = $requireSellerApproval;
        return $this;
    }

    public function isAllowGuestCheckout(): bool
    {
        return $this->allowGuestCheckout;
    }

    public function setAllowGuestCheckout(bool $allowGuestCheckout): static
    {
        $this->allowGuestCheckout = $allowGuestCheckout;
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

    public function getSettings(): ?array
    {
        return $this->settings;
    }

    public function setSettings(?array $settings): static
    {
        $this->settings = $settings;
        return $this;
    }

    /**
     * @return Collection<int, Seller>
     */
    public function getSellers(): Collection
    {
        return $this->sellers;
    }

    public function addSeller(Seller $seller): static
    {
        if (!$this->sellers->contains($seller)) {
            $this->sellers->add($seller);
            $seller->setMarketplace($this);
        }

        return $this;
    }

    public function removeSeller(Seller $seller): static
    {
        if ($this->sellers->removeElement($seller)) {
            // set the owning side to null (unless already changed)
            if ($seller->getMarketplace() === $this) {
                $seller->setMarketplace(null);
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
            $order->setMarketplace($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getMarketplace() === $this) {
                $order->setMarketplace(null);
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
            $commission->setMarketplace($this);
        }

        return $this;
    }

    public function removeCommission(MarketplaceCommission $commission): static
    {
        if ($this->commissions->removeElement($commission)) {
            // set the owning side to null (unless already changed)
            if ($commission->getMarketplace() === $this) {
                $commission->setMarketplace(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MarketplaceSettings>
     */
    public function getMarketplaceSettings(): Collection
    {
        return $this->marketplaceSettings;
    }

    public function addMarketplaceSetting(MarketplaceSettings $marketplaceSetting): static
    {
        if (!$this->marketplaceSettings->contains($marketplaceSetting)) {
            $this->marketplaceSettings->add($marketplaceSetting);
            $marketplaceSetting->setMarketplace($this);
        }

        return $this;
    }

    public function removeMarketplaceSetting(MarketplaceSettings $marketplaceSetting): static
    {
        if ($this->marketplaceSettings->removeElement($marketplaceSetting)) {
            // set the owning side to null (unless already changed)
            if ($marketplaceSetting->getMarketplace() === $this) {
                $marketplaceSetting->setMarketplace(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}