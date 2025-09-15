<?php

namespace App\Entity;

use App\Entity\Trait\SoftDeleteable;
use App\Repository\PosterRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PosterRepository::class)]
#[ORM\Table(name: 'poster')]
class Poster
{
    use SoftDeleteable;

    public const TYPE_BANNER = 'banner';
    public const TYPE_SLIDE = 'slide';
    public const TYPE_POPUP = 'popup';
    public const TYPE_SIDEBAR = 'sidebar';
    public const TYPE_PROMOTION = 'promotion';

    public const POSITION_TOP = 'top';
    public const POSITION_MIDDLE = 'middle';
    public const POSITION_BOTTOM = 'bottom';
    public const POSITION_LEFT = 'left';
    public const POSITION_RIGHT = 'right';
    public const POSITION_CENTER = 'center';

    public const TARGET_BLANK = '_blank';
    public const TARGET_SELF = '_self';
    public const TARGET_PARENT = '_parent';
    public const TARGET_TOP = '_top';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['poster:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(max: 200, maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères.')]
    #[Groups(['poster:read'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['poster:read'])]
    private ?string $description = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::TYPE_BANNER, self::TYPE_SLIDE, self::TYPE_POPUP, self::TYPE_SIDEBAR, self::TYPE_PROMOTION])]
    #[Groups(['poster:read'])]
    private string $type = self::TYPE_BANNER;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::POSITION_TOP, self::POSITION_MIDDLE, self::POSITION_BOTTOM, self::POSITION_LEFT, self::POSITION_RIGHT, self::POSITION_CENTER])]
    #[Groups(['poster:read'])]
    private string $position = self::POSITION_TOP;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'L\'image est obligatoire.')]
    #[Groups(['poster:read'])]
    private ?string $imagePath = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['poster:read'])]
    private ?string $mobileImagePath = null;

    #[ORM\Column(length: 200, nullable: true)]
    #[Groups(['poster:read'])]
    private ?string $altText = null;

    #[ORM\Column(length: 500, nullable: true)]
    #[Assert\Url(message: 'L\'URL de lien doit être valide.')]
    #[Groups(['poster:read'])]
    private ?string $linkUrl = null;

    #[ORM\Column(length: 10)]
    #[Assert\Choice(choices: [self::TARGET_BLANK, self::TARGET_SELF, self::TARGET_PARENT, self::TARGET_TOP])]
    #[Groups(['poster:read'])]
    private string $linkTarget = self::TARGET_BLANK;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['poster:read'])]
    private ?string $buttonText = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: 'La largeur doit être positive.')]
    #[Groups(['poster:read'])]
    private ?int $width = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: 'La hauteur doit être positive.')]
    #[Groups(['poster:read'])]
    private ?int $height = null;

    #[ORM\Column]
    #[Groups(['poster:read'])]
    private int $sortOrder = 0;

    #[ORM\Column]
    #[Groups(['poster:read'])]
    private int $viewCount = 0;

    #[ORM\Column]
    #[Groups(['poster:read'])]
    private int $clickCount = 0;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['poster:read'])]
    private ?\DateTimeInterface $startsAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['poster:read'])]
    private ?\DateTimeInterface $endsAt = null;

    #[ORM\Column]
    #[Groups(['poster:read'])]
    private bool $isActive = true;

    #[ORM\Column]
    #[Groups(['poster:read'])]
    private bool $isFeatured = false;

    #[ORM\Column]
    private bool $showOnMobile = true;

    #[ORM\Column]
    private bool $showOnDesktop = true;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $targetAudience = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $displayRules = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $cssProperties = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $metadata = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['poster:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['poster:read'])]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->targetAudience = [];
        $this->displayRules = [];
        $this->cssProperties = [];
        $this->metadata = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
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

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getPosition(): string
    {
        return $this->position;
    }

    public function setPosition(string $position): static
    {
        $this->position = $position;
        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(string $imagePath): static
    {
        $this->imagePath = $imagePath;
        return $this;
    }

    public function getMobileImagePath(): ?string
    {
        return $this->mobileImagePath;
    }

    public function setMobileImagePath(?string $mobileImagePath): static
    {
        $this->mobileImagePath = $mobileImagePath;
        return $this;
    }

    public function getAltText(): ?string
    {
        return $this->altText;
    }

    public function setAltText(?string $altText): static
    {
        $this->altText = $altText;
        return $this;
    }

    public function getLinkUrl(): ?string
    {
        return $this->linkUrl;
    }

    public function setLinkUrl(?string $linkUrl): static
    {
        $this->linkUrl = $linkUrl;
        return $this;
    }

    public function getLinkTarget(): string
    {
        return $this->linkTarget;
    }

    public function setLinkTarget(string $linkTarget): static
    {
        $this->linkTarget = $linkTarget;
        return $this;
    }

    public function getButtonText(): ?string
    {
        return $this->buttonText;
    }

    public function setButtonText(?string $buttonText): static
    {
        $this->buttonText = $buttonText;
        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(?int $width): static
    {
        $this->width = $width;
        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): static
    {
        $this->height = $height;
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

    public function getViewCount(): int
    {
        return $this->viewCount;
    }

    public function setViewCount(int $viewCount): static
    {
        $this->viewCount = $viewCount;
        return $this;
    }

    public function getClickCount(): int
    {
        return $this->clickCount;
    }

    public function setClickCount(int $clickCount): static
    {
        $this->clickCount = $clickCount;
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

    public function getEndsAt(): ?\DateTimeInterface
    {
        return $this->endsAt;
    }

    public function setEndsAt(?\DateTimeInterface $endsAt): static
    {
        $this->endsAt = $endsAt;
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

    public function isShowOnMobile(): bool
    {
        return $this->showOnMobile;
    }

    public function setShowOnMobile(bool $showOnMobile): static
    {
        $this->showOnMobile = $showOnMobile;
        return $this;
    }

    public function isShowOnDesktop(): bool
    {
        return $this->showOnDesktop;
    }

    public function setShowOnDesktop(bool $showOnDesktop): static
    {
        $this->showOnDesktop = $showOnDesktop;
        return $this;
    }

    public function getTargetAudience(): ?array
    {
        return $this->targetAudience;
    }

    public function setTargetAudience(?array $targetAudience): static
    {
        $this->targetAudience = $targetAudience;
        return $this;
    }

    public function getDisplayRules(): ?array
    {
        return $this->displayRules;
    }

    public function setDisplayRules(?array $displayRules): static
    {
        $this->displayRules = $displayRules;
        return $this;
    }

    public function getCssProperties(): ?array
    {
        return $this->cssProperties;
    }

    public function setCssProperties(?array $cssProperties): static
    {
        $this->cssProperties = $cssProperties;
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

    public function incrementViewCount(): static
    {
        $this->viewCount++;
        return $this;
    }

    public function incrementClickCount(): static
    {
        $this->clickCount++;
        return $this;
    }

    public function isCurrentlyActive(): bool
    {
        if (!$this->isActive) {
            return false;
        }

        $now = new \DateTime();

        if ($this->startsAt && $now < $this->startsAt) {
            return false;
        }

        if ($this->endsAt && $now > $this->endsAt) {
            return false;
        }

        return true;
    }

    public function isExpired(): bool
    {
        return $this->endsAt && new \DateTime() > $this->endsAt;
    }

    public function isScheduled(): bool
    {
        return $this->startsAt && new \DateTime() < $this->startsAt;
    }

    public function hasLink(): bool
    {
        return !empty($this->linkUrl);
    }

    public function hasButton(): bool
    {
        return !empty($this->buttonText);
    }

    public function getImageUrl(?bool $isMobile = null): string
    {
        if ($isMobile && $this->mobileImagePath) {
            return '/uploads/posters/' . $this->mobileImagePath;
        }
        
        return '/uploads/posters/' . $this->imagePath;
    }

    public function getDimensions(): ?string
    {
        if ($this->width && $this->height) {
            return $this->width . 'x' . $this->height;
        }
        
        return null;
    }

    public function getClickThroughRate(): float
    {
        if ($this->viewCount === 0) {
            return 0.0;
        }
        
        return ($this->clickCount / $this->viewCount) * 100;
    }

    public function shouldShowForDevice(bool $isMobile): bool
    {
        if ($isMobile) {
            return $this->showOnMobile;
        }
        
        return $this->showOnDesktop;
    }

    public function matchesTargetAudience(array $userAttributes): bool
    {
        if (!$this->targetAudience || empty($this->targetAudience)) {
            return true;
        }
        
        foreach ($this->targetAudience as $rule) {
            $attribute = $rule['attribute'] ?? null;
            $operator = $rule['operator'] ?? '=';
            $value = $rule['value'] ?? null;
            
            if (!$attribute || !isset($userAttributes[$attribute])) {
                continue;
            }
            
            $userValue = $userAttributes[$attribute];
            
            $matches = match($operator) {
                '=' => $userValue == $value,
                '!=' => $userValue != $value,
                '>' => $userValue > $value,
                '<' => $userValue < $value,
                '>=' => $userValue >= $value,
                '<=' => $userValue <= $value,
                'in' => is_array($value) && in_array($userValue, $value),
                'not_in' => is_array($value) && !in_array($userValue, $value),
                'contains' => is_string($userValue) && str_contains($userValue, $value),
                default => false
            };
            
            if (!$matches) {
                return false;
            }
        }
        
        return true;
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            self::TYPE_BANNER => 'Bannière',
            self::TYPE_SLIDE => 'Slide',
            self::TYPE_POPUP => 'Pop-up',
            self::TYPE_SIDEBAR => 'Sidebar',
            self::TYPE_PROMOTION => 'Promotion',
            default => 'Inconnu'
        };
    }

    public function getPositionLabel(): string
    {
        return match($this->position) {
            self::POSITION_TOP => 'Haut',
            self::POSITION_MIDDLE => 'Milieu',
            self::POSITION_BOTTOM => 'Bas',
            self::POSITION_LEFT => 'Gauche',
            self::POSITION_RIGHT => 'Droite',
            self::POSITION_CENTER => 'Centre',
            default => 'Inconnu'
        };
    }

    public static function getTypes(): array
    {
        return [
            self::TYPE_BANNER => 'Bannière',
            self::TYPE_SLIDE => 'Slide',
            self::TYPE_POPUP => 'Pop-up',
            self::TYPE_SIDEBAR => 'Sidebar',
            self::TYPE_PROMOTION => 'Promotion',
        ];
    }

    public static function getPositions(): array
    {
        return [
            self::POSITION_TOP => 'Haut',
            self::POSITION_MIDDLE => 'Milieu',
            self::POSITION_BOTTOM => 'Bas',
            self::POSITION_LEFT => 'Gauche',
            self::POSITION_RIGHT => 'Droite',
            self::POSITION_CENTER => 'Centre',
        ];
    }

    public static function getLinkTargets(): array
    {
        return [
            self::TARGET_BLANK => 'Nouvelle fenêtre',
            self::TARGET_SELF => 'Même fenêtre',
            self::TARGET_PARENT => 'Fenêtre parent',
            self::TARGET_TOP => 'Fenêtre principale',
        ];
    }

    public function __toString(): string
    {
        return $this->title ?? '';
    }
}