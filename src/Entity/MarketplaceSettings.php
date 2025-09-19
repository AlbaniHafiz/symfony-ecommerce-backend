<?php

namespace App\Entity;

use App\Entity\Trait\SoftDeleteable;
use App\Repository\MarketplaceSettingsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: MarketplaceSettingsRepository::class)]
#[ORM\Table(name: 'marketplace_settings')]
#[UniqueEntity(fields: ['marketplace', 'settingKey'], message: 'Cette clé de paramètre existe déjà pour ce marketplace.')]
class MarketplaceSettings
{
    use SoftDeleteable;

    public const TYPE_STRING = 'string';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_FLOAT = 'float';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_JSON = 'json';
    public const TYPE_ARRAY = 'array';

    public const CATEGORY_GENERAL = 'general';
    public const CATEGORY_PAYMENT = 'payment';
    public const CATEGORY_SHIPPING = 'shipping';
    public const CATEGORY_COMMISSION = 'commission';
    public const CATEGORY_NOTIFICATION = 'notification';
    public const CATEGORY_SECURITY = 'security';
    public const CATEGORY_APPEARANCE = 'appearance';
    public const CATEGORY_SEO = 'seo';
    public const CATEGORY_INTEGRATION = 'integration';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['settings:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'marketplaceSettings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Le marketplace est obligatoire.')]
    #[Groups(['settings:read'])]
    private ?Marketplace $marketplace = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'La clé de paramètre est obligatoire.')]
    #[Assert\Length(max: 100, maxMessage: 'La clé ne peut pas dépasser {{ limit }} caractères.')]
    #[Assert\Regex(pattern: '/^[a-z0-9_\.]+$/', message: 'La clé ne peut contenir que des lettres minuscules, chiffres, underscores et points.')]
    #[Groups(['settings:read'])]
    private ?string $settingKey = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank(message: 'Le nom du paramètre est obligatoire.')]
    #[Assert\Length(max: 200, maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.')]
    #[Groups(['settings:read'])]
    private ?string $settingName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['settings:read'])]
    private ?string $description = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::TYPE_STRING, self::TYPE_INTEGER, self::TYPE_FLOAT, self::TYPE_BOOLEAN, self::TYPE_JSON, self::TYPE_ARRAY])]
    #[Groups(['settings:read'])]
    private string $valueType = self::TYPE_STRING;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['settings:read'])]
    private ?string $settingValue = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['settings:read'])]
    private ?string $defaultValue = null;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(choices: [self::CATEGORY_GENERAL, self::CATEGORY_PAYMENT, self::CATEGORY_SHIPPING, self::CATEGORY_COMMISSION, self::CATEGORY_NOTIFICATION, self::CATEGORY_SECURITY, self::CATEGORY_APPEARANCE, self::CATEGORY_SEO, self::CATEGORY_INTEGRATION])]
    #[Groups(['settings:read'])]
    private string $category = self::CATEGORY_GENERAL;

    #[ORM\Column]
    #[Groups(['settings:read'])]
    private bool $isRequired = false;

    #[ORM\Column]
    #[Groups(['settings:read'])]
    private bool $isPublic = false;

    #[ORM\Column]
    #[Groups(['settings:read'])]
    private bool $isEditable = true;

    #[ORM\Column]
    #[Groups(['settings:read'])]
    private int $sortOrder = 0;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['settings:read'])]
    private ?array $validationRules = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['settings:read'])]
    private ?array $options = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $metadata = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['settings:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['settings:read'])]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->validationRules = [];
        $this->options = [];
        $this->metadata = [];
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSettingKey(): ?string
    {
        return $this->settingKey;
    }

    public function setSettingKey(string $settingKey): static
    {
        $this->settingKey = strtolower($settingKey);
        return $this;
    }

    public function getSettingName(): ?string
    {
        return $this->settingName;
    }

    public function setSettingName(string $settingName): static
    {
        $this->settingName = $settingName;
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

    public function getValueType(): string
    {
        return $this->valueType;
    }

    public function setValueType(string $valueType): static
    {
        $this->valueType = $valueType;
        return $this;
    }

    public function getSettingValue(): ?string
    {
        return $this->settingValue;
    }

    public function setSettingValue(?string $settingValue): static
    {
        $this->settingValue = $settingValue;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function getDefaultValue(): ?string
    {
        return $this->defaultValue;
    }

    public function setDefaultValue(?string $defaultValue): static
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function setIsRequired(bool $isRequired): static
    {
        $this->isRequired = $isRequired;
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

    public function isEditable(): bool
    {
        return $this->isEditable;
    }

    public function setIsEditable(bool $isEditable): static
    {
        $this->isEditable = $isEditable;
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

    public function getValidationRules(): ?array
    {
        return $this->validationRules;
    }

    public function setValidationRules(?array $validationRules): static
    {
        $this->validationRules = $validationRules;
        return $this;
    }

    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function setOptions(?array $options): static
    {
        $this->options = $options;
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

    public function getValue()
    {
        $value = $this->settingValue ?? $this->defaultValue;
        
        if ($value === null) {
            return null;
        }
        
        return match($this->valueType) {
            self::TYPE_STRING => (string) $value,
            self::TYPE_INTEGER => (int) $value,
            self::TYPE_FLOAT => (float) $value,
            self::TYPE_BOOLEAN => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            self::TYPE_JSON => json_decode($value, true),
            self::TYPE_ARRAY => is_string($value) ? explode(',', $value) : $value,
            default => $value
        };
    }

    public function setValue($value): static
    {
        $this->settingValue = match($this->valueType) {
            self::TYPE_STRING => (string) $value,
            self::TYPE_INTEGER => (string) (int) $value,
            self::TYPE_FLOAT => (string) (float) $value,
            self::TYPE_BOOLEAN => $value ? '1' : '0',
            self::TYPE_JSON => is_string($value) ? $value : json_encode($value),
            self::TYPE_ARRAY => is_array($value) ? implode(',', $value) : (string) $value,
            default => (string) $value
        };
        
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function resetToDefault(): static
    {
        $this->settingValue = $this->defaultValue;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function hasValue(): bool
    {
        return $this->settingValue !== null;
    }

    public function isUsingDefault(): bool
    {
        return $this->settingValue === null || $this->settingValue === $this->defaultValue;
    }

    public function validateValue($value): bool
    {
        if (!$this->validationRules || empty($this->validationRules)) {
            return true;
        }
        
        foreach ($this->validationRules as $rule => $params) {
            if (!$this->applyValidationRule($rule, $value, $params)) {
                return false;
            }
        }
        
        return true;
    }

    private function applyValidationRule(string $rule, $value, $params): bool
    {
        return match($rule) {
            'required' => $value !== null && $value !== '',
            'min_length' => is_string($value) && strlen($value) >= $params,
            'max_length' => is_string($value) && strlen($value) <= $params,
            'min_value' => is_numeric($value) && $value >= $params,
            'max_value' => is_numeric($value) && $value <= $params,
            'regex' => is_string($value) && preg_match($params, $value),
            'in' => is_array($params) && in_array($value, $params),
            'email' => is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'url' => is_string($value) && filter_var($value, FILTER_VALIDATE_URL) !== false,
            'numeric' => is_numeric($value),
            'boolean' => is_bool($value) || in_array($value, ['0', '1', 'true', 'false']),
            default => true
        };
    }

    public function getValueTypeLabel(): string
    {
        return match($this->valueType) {
            self::TYPE_STRING => 'Texte',
            self::TYPE_INTEGER => 'Entier',
            self::TYPE_FLOAT => 'Décimal',
            self::TYPE_BOOLEAN => 'Booléen',
            self::TYPE_JSON => 'JSON',
            self::TYPE_ARRAY => 'Tableau',
            default => 'Inconnu'
        };
    }

    public function getCategoryLabel(): string
    {
        return match($this->category) {
            self::CATEGORY_GENERAL => 'Général',  
            self::CATEGORY_PAYMENT => 'Paiement',
            self::CATEGORY_SHIPPING => 'Livraison',
            self::CATEGORY_COMMISSION => 'Commission',
            self::CATEGORY_NOTIFICATION => 'Notification',
            self::CATEGORY_SECURITY => 'Sécurité',
            self::CATEGORY_APPEARANCE => 'Apparence',
            self::CATEGORY_SEO => 'SEO',
            self::CATEGORY_INTEGRATION => 'Intégration',
            default => 'Inconnu'
        };
    }

    public static function getValueTypes(): array
    {
        return [
            self::TYPE_STRING => 'Texte',
            self::TYPE_INTEGER => 'Entier',
            self::TYPE_FLOAT => 'Décimal',
            self::TYPE_BOOLEAN => 'Booléen',
            self::TYPE_JSON => 'JSON',
            self::TYPE_ARRAY => 'Tableau',
        ];
    }

    public static function getCategories(): array
    {
        return [
            self::CATEGORY_GENERAL => 'Général',
            self::CATEGORY_PAYMENT => 'Paiement',
            self::CATEGORY_SHIPPING => 'Livraison',
            self::CATEGORY_COMMISSION => 'Commission',
            self::CATEGORY_NOTIFICATION => 'Notification',
            self::CATEGORY_SECURITY => 'Sécurité',
            self::CATEGORY_APPEARANCE => 'Apparence',
            self::CATEGORY_SEO => 'SEO',
            self::CATEGORY_INTEGRATION => 'Intégration',
        ];
    }

    public function __toString(): string
    {
        return $this->settingName ?? $this->settingKey ?? '';
    }
}