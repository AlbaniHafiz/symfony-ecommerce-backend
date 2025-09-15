<?php

namespace App\Entity;

use App\Entity\Trait\SoftDeleteable;
use App\Repository\ActivityLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ActivityLogRepository::class)]
#[ORM\Table(name: 'activity_log')]
class ActivityLog
{
    use SoftDeleteable;

    public const LEVEL_INFO = 'info';
    public const LEVEL_WARNING = 'warning';
    public const LEVEL_ERROR = 'error';
    public const LEVEL_CRITICAL = 'critical';

    public const ACTION_CREATE = 'create';
    public const ACTION_UPDATE = 'update';
    public const ACTION_DELETE = 'delete';
    public const ACTION_LOGIN = 'login';
    public const ACTION_LOGOUT = 'logout';
    public const ACTION_VIEW = 'view';
    public const ACTION_EXPORT = 'export';
    public const ACTION_IMPORT = 'import';
    public const ACTION_APPROVE = 'approve';
    public const ACTION_REJECT = 'reject';
    public const ACTION_SUSPEND = 'suspend';
    public const ACTION_ACTIVATE = 'activate';
    public const ACTION_DEACTIVATE = 'deactivate';
    public const ACTION_PURCHASE = 'purchase';
    public const ACTION_REFUND = 'refund';
    public const ACTION_SHIP = 'ship';
    public const ACTION_DELIVER = 'deliver';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['activity:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['activity:read'])]
    private ?User $user = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'L\'action est obligatoire.')]
    #[Groups(['activity:read'])]
    private ?string $action = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['activity:read'])]
    private ?string $entityType = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['activity:read'])]
    private ?int $entityId = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La description est obligatoire.')]
    #[Groups(['activity:read'])]
    private ?string $description = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::LEVEL_INFO, self::LEVEL_WARNING, self::LEVEL_ERROR, self::LEVEL_CRITICAL])]
    #[Groups(['activity:read'])]
    private string $level = self::LEVEL_INFO;

    #[ORM\Column(length: 45, nullable: true)]
    #[Groups(['activity:read'])]
    private ?string $ipAddress = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['activity:read'])]
    private ?string $url = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['activity:read'])]
    private ?string $method = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $oldValues = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $newValues = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $context = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $metadata = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['activity:read'])]
    private ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->oldValues = [];
        $this->newValues = [];
        $this->context = [];
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

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): static
    {
        $this->action = $action;
        return $this;
    }

    public function getEntityType(): ?string
    {
        return $this->entityType;
    }

    public function setEntityType(?string $entityType): static
    {
        $this->entityType = $entityType;
        return $this;
    }

    public function getEntityId(): ?int
    {
        return $this->entityId;
    }

    public function setEntityId(?int $entityId): static
    {
        $this->entityId = $entityId;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    public function setLevel(string $level): static
    {
        $this->level = $level;
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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;
        return $this;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(?string $method): static
    {
        $this->method = $method;
        return $this;
    }

    public function getOldValues(): ?array
    {
        return $this->oldValues;
    }

    public function setOldValues(?array $oldValues): static
    {
        $this->oldValues = $oldValues;
        return $this;
    }

    public function getNewValues(): ?array
    {
        return $this->newValues;
    }

    public function setNewValues(?array $newValues): static
    {
        $this->newValues = $newValues;
        return $this;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }

    public function setContext(?array $context): static
    {
        $this->context = $context;
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

    public function addContext(string $key, $value): static
    {
        $context = $this->context ?? [];
        $context[$key] = $value;
        $this->context = $context;
        return $this;
    }

    public function addMetadata(string $key, $value): static
    {
        $metadata = $this->metadata ?? [];
        $metadata[$key] = $value;
        $this->metadata = $metadata;
        return $this;
    }

    public function getChangedFields(): array
    {
        if (!$this->oldValues || !$this->newValues) {
            return [];
        }
        
        $changed = [];
        foreach ($this->newValues as $field => $newValue) {
            $oldValue = $this->oldValues[$field] ?? null;
            if ($oldValue !== $newValue) {
                $changed[$field] = [
                    'old' => $oldValue,
                    'new' => $newValue
                ];
            }
        }
        
        return $changed;
    }

    public function hasChanges(): bool
    {
        return !empty($this->getChangedFields());
    }

    public function isSystemAction(): bool
    {
        return $this->user === null;
    }

    public function getUserName(): string
    {
        return $this->user?->getFullName() ?? 'Système';
    }

    public function getEntityName(): string
    {
        if (!$this->entityType || !$this->entityId) {
            return 'N/A';
        }
        
        return $this->entityType . ' #' . $this->entityId;
    }

    public function getFormattedDescription(): string
    {
        $userName = $this->getUserName();
        $entityName = $this->getEntityName();
        
        if ($entityName !== 'N/A') {
            return sprintf('%s a %s %s', $userName, $this->getActionLabel(), $entityName);
        }
        
        return sprintf('%s : %s', $userName, $this->description);
    }

    public function getBrowserInfo(): ?array
    {
        if (!$this->userAgent) {
            return null;
        }
        
        // Simple user agent parsing - could be enhanced with a proper library
        $info = [
            'browser' => 'Inconnu',
            'os' => 'Inconnu',
            'device' => 'Desktop',
        ];
        
        if (preg_match('/Mobile|Android|iPhone|iPad/', $this->userAgent)) {
            $info['device'] = 'Mobile';
        } elseif (preg_match('/Tablet/', $this->userAgent)) {
            $info['device'] = 'Tablette';
        }
        
        if (preg_match('/Chrome/', $this->userAgent)) {
            $info['browser'] = 'Chrome';
        } elseif (preg_match('/Firefox/', $this->userAgent)) {
            $info['browser'] = 'Firefox';
        } elseif (preg_match('/Safari/', $this->userAgent)) {
            $info['browser'] = 'Safari';
        } elseif (preg_match('/Edge/', $this->userAgent)) {
            $info['browser'] = 'Edge';
        }
        
        if (preg_match('/Windows/', $this->userAgent)) {
            $info['os'] = 'Windows';
        } elseif (preg_match('/Mac/', $this->userAgent)) {
            $info['os'] = 'macOS';
        } elseif (preg_match('/Linux/', $this->userAgent)) {
            $info['os'] = 'Linux';
        } elseif (preg_match('/Android/', $this->userAgent)) {
            $info['os'] = 'Android';
        } elseif (preg_match('/iOS/', $this->userAgent)) {
            $info['os'] = 'iOS';
        }
        
        return $info;
    }

    public function getActionLabel(): string
    {
        return match($this->action) {
            self::ACTION_CREATE => 'créé',
            self::ACTION_UPDATE => 'modifié',
            self::ACTION_DELETE => 'supprimé',
            self::ACTION_LOGIN => 'connecté',
            self::ACTION_LOGOUT => 'déconnecté',
            self::ACTION_VIEW => 'consulté',
            self::ACTION_EXPORT => 'exporté',
            self::ACTION_IMPORT => 'importé',
            self::ACTION_APPROVE => 'approuvé',
            self::ACTION_REJECT => 'rejeté',
            self::ACTION_SUSPEND => 'suspendu',
            self::ACTION_ACTIVATE => 'activé',
            self::ACTION_DEACTIVATE => 'désactivé',
            self::ACTION_PURCHASE => 'acheté',
            self::ACTION_REFUND => 'remboursé',
            self::ACTION_SHIP => 'expédié',
            self::ACTION_DELIVER => 'livré',
            default => $this->action
        };
    }

    public function getLevelLabel(): string
    {
        return match($this->level) {
            self::LEVEL_INFO => 'Information',
            self::LEVEL_WARNING => 'Avertissement',
            self::LEVEL_ERROR => 'Erreur',
            self::LEVEL_CRITICAL => 'Critique',
            default => 'Inconnu'
        };
    }

    public function getLevelColor(): string
    {
        return match($this->level) {
            self::LEVEL_INFO => 'blue',
            self::LEVEL_WARNING => 'orange',
            self::LEVEL_ERROR => 'red',
            self::LEVEL_CRITICAL => 'purple',
            default => 'gray'
        };
    }

    public static function getActions(): array
    {
        return [
            self::ACTION_CREATE => 'Créer',
            self::ACTION_UPDATE => 'Modifier',
            self::ACTION_DELETE => 'Supprimer',
            self::ACTION_LOGIN => 'Connexion',
            self::ACTION_LOGOUT => 'Déconnexion',
            self::ACTION_VIEW => 'Consulter',
            self::ACTION_EXPORT => 'Exporter',
            self::ACTION_IMPORT => 'Importer',
            self::ACTION_APPROVE => 'Approuver',
            self::ACTION_REJECT => 'Rejeter',
            self::ACTION_SUSPEND => 'Suspendre',
            self::ACTION_ACTIVATE => 'Activer',
            self::ACTION_DEACTIVATE => 'Désactiver',
            self::ACTION_PURCHASE => 'Acheter',
            self::ACTION_REFUND => 'Rembourser',
            self::ACTION_SHIP => 'Expédier',
            self::ACTION_DELIVER => 'Livrer',
        ];
    }

    public static function getLevels(): array
    {
        return [
            self::LEVEL_INFO => 'Information',
            self::LEVEL_WARNING => 'Avertissement',
            self::LEVEL_ERROR => 'Erreur',
            self::LEVEL_CRITICAL => 'Critique',
        ];
    }

    public function __toString(): string
    {
        return $this->getFormattedDescription();
    }
}