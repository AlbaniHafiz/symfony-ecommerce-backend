<?php

namespace App\Entity;

use App\Entity\Trait\SoftDeleteable;
use App\Repository\NotificationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ORM\Table(name: 'notification')]
class Notification
{
    use SoftDeleteable;

    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_READ = 'read';

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['notification:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'notifications')]
    #[Groups(['notification:read'])]
    private ?NotificationTemplate $template = null;

    #[ORM\ManyToOne(inversedBy: 'notifications')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'L\'utilisateur est obligatoire.')]
    #[Groups(['notification:read'])]
    private ?User $user = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::STATUS_PENDING, self::STATUS_SENT, self::STATUS_FAILED, self::STATUS_READ])]
    #[Groups(['notification:read'])]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::PRIORITY_LOW, self::PRIORITY_NORMAL, self::PRIORITY_HIGH, self::PRIORITY_URGENT])]
    #[Groups(['notification:read'])]
    private string $priority = self::PRIORITY_NORMAL;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [NotificationTemplate::TYPE_EMAIL, NotificationTemplate::TYPE_SMS, NotificationTemplate::TYPE_PUSH, NotificationTemplate::TYPE_IN_APP])]
    #[Groups(['notification:read'])]
    private string $type = NotificationTemplate::TYPE_IN_APP;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(choices: [NotificationTemplate::CATEGORY_ORDER, NotificationTemplate::CATEGORY_PRODUCT, NotificationTemplate::CATEGORY_SELLER, NotificationTemplate::CATEGORY_USER, NotificationTemplate::CATEGORY_PAYMENT, NotificationTemplate::CATEGORY_SYSTEM])]
    #[Groups(['notification:read'])]
    private string $category = NotificationTemplate::CATEGORY_SYSTEM;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['notification:read'])]
    private ?string $subject = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Le contenu est obligatoire.')]
    #[Groups(['notification:read'])]
    private ?string $content = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['notification:read'])]
    private ?string $contentHtml = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['notification:read'])]
    private ?string $actionUrl = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['notification:read'])]
    private ?string $actionText = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['notification:read'])]
    private ?string $recipientEmail = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['notification:read'])]
    private ?string $recipientPhone = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $templateData = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $deliveryAttempts = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $errorMessage = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $metadata = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['notification:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['notification:read'])]
    private ?\DateTimeInterface $scheduledAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['notification:read'])]
    private ?\DateTimeInterface $sentAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['notification:read'])]
    private ?\DateTimeInterface $readAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $expiresAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->templateData = [];
        $this->deliveryAttempts = [];
        $this->metadata = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTemplate(): ?NotificationTemplate
    {
        return $this->template;
    }

    public function setTemplate(?NotificationTemplate $template): static
    {
        $this->template = $template;
        
        if ($template) {
            $this->type = $template->getType();
            $this->category = $template->getCategory();
        }
        
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        
        if ($user) {
            $this->recipientEmail = $user->getEmail();
            $this->recipientPhone = $user->getPhone();
        }
        
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        
        match($status) {
            self::STATUS_SENT => $this->sentAt = $this->sentAt ?? new \DateTime(),
            self::STATUS_READ => $this->readAt = $this->readAt ?? new \DateTime(),
            default => null
        };
        
        return $this;
    }

    public function getPriority(): string
    {
        return $this->priority;
    }

    public function setPriority(string $priority): static
    {
        $this->priority = $priority;
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;
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

    public function getActionUrl(): ?string
    {
        return $this->actionUrl;
    }

    public function setActionUrl(?string $actionUrl): static
    {
        $this->actionUrl = $actionUrl;
        return $this;
    }

    public function getActionText(): ?string
    {
        return $this->actionText;
    }

    public function setActionText(?string $actionText): static
    {
        $this->actionText = $actionText;
        return $this;
    }

    public function getRecipientEmail(): ?string
    {
        return $this->recipientEmail;
    }

    public function setRecipientEmail(?string $recipientEmail): static
    {
        $this->recipientEmail = $recipientEmail;
        return $this;
    }

    public function getRecipientPhone(): ?string
    {
        return $this->recipientPhone;
    }

    public function setRecipientPhone(?string $recipientPhone): static
    {
        $this->recipientPhone = $recipientPhone;
        return $this;
    }

    public function getTemplateData(): ?array
    {
        return $this->templateData;
    }

    public function setTemplateData(?array $templateData): static
    {
        $this->templateData = $templateData;
        return $this;
    }

    public function getDeliveryAttempts(): ?array
    {
        return $this->deliveryAttempts;
    }

    public function setDeliveryAttempts(?array $deliveryAttempts): static
    {
        $this->deliveryAttempts = $deliveryAttempts;
        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): static
    {
        $this->errorMessage = $errorMessage;
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

    public function getScheduledAt(): ?\DateTimeInterface
    {
        return $this->scheduledAt;
    }

    public function setScheduledAt(?\DateTimeInterface $scheduledAt): static
    {
        $this->scheduledAt = $scheduledAt;
        return $this;
    }

    public function getSentAt(): ?\DateTimeInterface
    {
        return $this->sentAt;
    }

    public function setSentAt(?\DateTimeInterface $sentAt): static
    {
        $this->sentAt = $sentAt;
        return $this;
    }

    public function getReadAt(): ?\DateTimeInterface
    {
        return $this->readAt;
    }

    public function setReadAt(?\DateTimeInterface $readAt): static
    {
        $this->readAt = $readAt;
        return $this;
    }

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeInterface $expiresAt): static
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function addDeliveryAttempt(string $status, ?string $error = null): static
    {
        $attempts = $this->deliveryAttempts ?? [];
        $attempts[] = [
            'status' => $status,
            'error' => $error,
            'attempted_at' => (new \DateTime())->format('c'),
        ];
        
        $this->deliveryAttempts = $attempts;
        return $this;
    }

    public function getDeliveryAttemptCount(): int
    {
        return count($this->deliveryAttempts ?? []);
    }

    public function getLastDeliveryAttempt(): ?array
    {
        $attempts = $this->deliveryAttempts ?? [];
        return end($attempts) ?: null;
    }

    public function send(): static
    {
        $this->setStatus(self::STATUS_SENT);
        $this->addDeliveryAttempt('sent');
        return $this;
    }

    public function fail(string $error): static
    {
        $this->setStatus(self::STATUS_FAILED);
        $this->setErrorMessage($error);
        $this->addDeliveryAttempt('failed', $error);
        return $this;
    }

    public function markAsRead(): static
    {
        $this->setStatus(self::STATUS_READ);
        return $this;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isRead(): bool
    {
        return $this->status === self::STATUS_READ;
    }

    public function isScheduled(): bool
    {
        return $this->scheduledAt && $this->scheduledAt > new \DateTime();
    }

    public function isExpired(): bool
    {
        return $this->expiresAt && $this->expiresAt < new \DateTime();
    }

    public function isDue(): bool
    {
        if (!$this->scheduledAt) {
            return true;
        }
        
        return $this->scheduledAt <= new \DateTime();
    }

    public function canBeResent(): bool
    {
        return $this->isFailed() && !$this->isExpired();
    }

    public function hasAction(): bool
    {
        return $this->actionUrl !== null;
    }

    public function getDeliveryTime(): ?\DateInterval
    {
        if ($this->sentAt) {
            return $this->sentAt->diff($this->createdAt);
        }
        
        return null;
    }

    public function getReadTime(): ?\DateInterval
    {
        if ($this->readAt && $this->sentAt) {
            return $this->readAt->diff($this->sentAt);
        }
        
        return null;
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'En attente',
            self::STATUS_SENT => 'Envoyé',
            self::STATUS_FAILED => 'Échec',
            self::STATUS_READ => 'Lu',
            default => 'Inconnu'
        };
    }

    public function getPriorityLabel(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'Faible',
            self::PRIORITY_NORMAL => 'Normale',
            self::PRIORITY_HIGH => 'Élevée',
            self::PRIORITY_URGENT => 'Urgente',
            default => 'Inconnu'
        };
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            NotificationTemplate::TYPE_EMAIL => 'Email',
            NotificationTemplate::TYPE_SMS => 'SMS',
            NotificationTemplate::TYPE_PUSH => 'Push',
            NotificationTemplate::TYPE_IN_APP => 'In-App',
            default => 'Inconnu'
        };
    }

    public function getCategoryLabel(): string
    {
        return match($this->category) {
            NotificationTemplate::CATEGORY_ORDER => 'Commande',
            NotificationTemplate::CATEGORY_PRODUCT => 'Produit',
            NotificationTemplate::CATEGORY_SELLER => 'Vendeur',
            NotificationTemplate::CATEGORY_USER => 'Utilisateur',
            NotificationTemplate::CATEGORY_PAYMENT => 'Paiement',
            NotificationTemplate::CATEGORY_SYSTEM => 'Système',
            default => 'Inconnu'
        };
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'En attente',
            self::STATUS_SENT => 'Envoyé',
            self::STATUS_FAILED => 'Échec',
            self::STATUS_READ => 'Lu',
        ];
    }

    public static function getPriorities(): array
    {
        return [
            self::PRIORITY_LOW => 'Faible',
            self::PRIORITY_NORMAL => 'Normale',
            self::PRIORITY_HIGH => 'Élevée',
            self::PRIORITY_URGENT => 'Urgente',
        ];
    }

    public function __toString(): string
    {
        return $this->subject ?? $this->content ?? 'Notification #' . $this->id;
    }
}