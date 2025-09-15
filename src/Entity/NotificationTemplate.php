<?php

namespace App\Entity;

use App\Entity\Trait\SoftDeleteable;
use App\Repository\NotificationTemplateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: NotificationTemplateRepository::class)]
#[ORM\Table(name: 'notification_template')]
#[UniqueEntity(fields: ['code'], message: 'Ce code de template existe déjà.')]
class NotificationTemplate
{
    use SoftDeleteable;

    public const TYPE_EMAIL = 'email';
    public const TYPE_SMS = 'sms';
    public const TYPE_PUSH = 'push';
    public const TYPE_IN_APP = 'in_app';

    public const CATEGORY_ORDER = 'order';
    public const CATEGORY_PRODUCT = 'product';
    public const CATEGORY_SELLER = 'seller';
    public const CATEGORY_USER = 'user';
    public const CATEGORY_PAYMENT = 'payment';
    public const CATEGORY_SYSTEM = 'system';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['template:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank(message: 'Le code du template est obligatoire.')]
    #[Assert\Length(max: 100, maxMessage: 'Le code ne peut pas dépasser {{ limit }} caractères.')]
    #[Assert\Regex(pattern: '/^[a-z0-9_]+$/', message: 'Le code ne peut contenir que des lettres minuscules, chiffres et underscores.')]
    #[Groups(['template:read'])]
    private ?string $code = null;

    #[ORM\Column(length: 200)]
    #[Assert\NotBlank(message: 'Le nom du template est obligatoire.')]
    #[Assert\Length(max: 200, maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.')]
    #[Groups(['template:read'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['template:read'])]
    private ?string $description = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::TYPE_EMAIL, self::TYPE_SMS, self::TYPE_PUSH, self::TYPE_IN_APP])]
    #[Groups(['template:read'])]
    private string $type = self::TYPE_EMAIL;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(choices: [self::CATEGORY_ORDER, self::CATEGORY_PRODUCT, self::CATEGORY_SELLER, self::CATEGORY_USER, self::CATEGORY_PAYMENT, self::CATEGORY_SYSTEM])]
    #[Groups(['template:read'])]
    private string $category = self::CATEGORY_SYSTEM;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['template:read'])]
    private ?string $subject = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['template:read'])]
    private ?string $contentText = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['template:read'])]
    private ?string $contentHtml = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['template:read'])]
    private ?array $variables = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['template:read'])]
    private ?array $conditions = null;

    #[ORM\Column]
    #[Groups(['template:read'])]
    private bool $isActive = true;

    #[ORM\Column]
    private bool $isSystem = false;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $metadata = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['template:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['template:read'])]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'template', targetEntity: Notification::class)]
    private Collection $notifications;

    public function __construct()
    {
        $this->notifications = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->variables = [];
        $this->conditions = [];
        $this->metadata = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = strtolower($code);
        return $this;
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

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): static
    {
        $this->subject = $subject;
        return $this;
    }

    public function getContentText(): ?string
    {
        return $this->contentText;
    }

    public function setContentText(?string $contentText): static
    {
        $this->contentText = $contentText;
        return $this;
    }

    public function getContentHtml(): ?string
    {
        return $this->contentHtml;
    }

    public function setContentHtml(?string $contentHtml): static
    {
        $this->contentHtml = $contentHtml;
        return $this;
    }

    public function getVariables(): ?array
    {
        return $this->variables;
    }

    public function setVariables(?array $variables): static
    {
        $this->variables = $variables;
        return $this;
    }

    public function getConditions(): ?array
    {
        return $this->conditions;
    }

    public function setConditions(?array $conditions): static
    {
        $this->conditions = $conditions;
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

    public function isSystem(): bool
    {
        return $this->isSystem;
    }

    public function setIsSystem(bool $isSystem): static
    {
        $this->isSystem = $isSystem;
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

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setTemplate($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getTemplate() === $this) {
                $notification->setTemplate(null);
            }
        }

        return $this;
    }

    public function renderSubject(array $data = []): ?string
    {
        if (!$this->subject) {
            return null;
        }
        
        return $this->replaceVariables($this->subject, $data);
    }

    public function renderContentText(array $data = []): ?string
    {
        if (!$this->contentText) {
            return null;
        }
        
        return $this->replaceVariables($this->contentText, $data);
    }

    public function renderContentHtml(array $data = []): ?string
    {
        if (!$this->contentHtml) {
            return null;
        }
        
        return $this->replaceVariables($this->contentHtml, $data);
    }

    public function checkConditions(array $data = []): bool
    {
        if (!$this->conditions || empty($this->conditions)) {
            return true;
        }
        
        foreach ($this->conditions as $condition) {
            if (!$this->evaluateCondition($condition, $data)) {
                return false;
            }
        }
        
        return true;
    }

    private function replaceVariables(string $content, array $data): string
    {
        // Replace variables like {{ variable_name }} with actual values
        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_\.]+)\s*\}\}/', function($matches) use ($data) {
            $key = $matches[1];
            return $this->getNestedValue($data, $key) ?? $matches[0];
        }, $content);
    }

    private function getNestedValue(array $data, string $key)
    {
        if (str_contains($key, '.')) {
            $keys = explode('.', $key);
            $value = $data;
            
            foreach ($keys as $nestedKey) {
                if (!is_array($value) || !array_key_exists($nestedKey, $value)) {
                    return null;
                }
                $value = $value[$nestedKey];
            }
            
            return $value;
        }
        
        return $data[$key] ?? null;
    }

    private function evaluateCondition(array $condition, array $data): bool
    {
        $field = $condition['field'] ?? null;
        $operator = $condition['operator'] ?? '=';
        $value = $condition['value'] ?? null;
        
        if (!$field) {
            return false;
        }
        
        $fieldValue = $this->getNestedValue($data, $field);
        
        return match($operator) {
            '=' => $fieldValue == $value,
            '!=' => $fieldValue != $value,
            '>' => $fieldValue > $value,
            '<' => $fieldValue < $value,
            '>=' => $fieldValue >= $value,
            '<=' => $fieldValue <= $value,
            'in' => is_array($value) && in_array($fieldValue, $value),
            'not_in' => is_array($value) && !in_array($fieldValue, $value),
            'contains' => is_string($fieldValue) && str_contains($fieldValue, $value),
            'exists' => $fieldValue !== null,
            'not_exists' => $fieldValue === null,
            default => false
        };
    }

    public function getUsageCount(): int
    {
        return $this->notifications->count();
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            self::TYPE_EMAIL => 'Email',
            self::TYPE_SMS => 'SMS',
            self::TYPE_PUSH => 'Notification push',
            self::TYPE_IN_APP => 'Notification in-app',
            default => 'Inconnu'
        };
    }

    public function getCategoryLabel(): string
    {
        return match($this->category) {
            self::CATEGORY_ORDER => 'Commande',
            self::CATEGORY_PRODUCT => 'Produit',
            self::CATEGORY_SELLER => 'Vendeur',
            self::CATEGORY_USER => 'Utilisateur',
            self::CATEGORY_PAYMENT => 'Paiement',
            self::CATEGORY_SYSTEM => 'Système',
            default => 'Inconnu'
        };
    }

    public static function getTypes(): array
    {
        return [
            self::TYPE_EMAIL => 'Email',
            self::TYPE_SMS => 'SMS',
            self::TYPE_PUSH => 'Notification push',
            self::TYPE_IN_APP => 'Notification in-app',
        ];
    }

    public static function getCategories(): array
    {
        return [
            self::CATEGORY_ORDER => 'Commande',
            self::CATEGORY_PRODUCT => 'Produit',
            self::CATEGORY_SELLER => 'Vendeur',
            self::CATEGORY_USER => 'Utilisateur',
            self::CATEGORY_PAYMENT => 'Paiement',
            self::CATEGORY_SYSTEM => 'Système',
        ];
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}