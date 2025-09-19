<?php

namespace App\Entity;

use App\Entity\Trait\SoftDeleteable;
use App\Repository\VariantTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: VariantTypeRepository::class)]
#[ORM\Table(name: 'variant_type')]
#[UniqueEntity(fields: ['name'], message: 'Ce type de variante existe déjà.')]
class VariantType
{
    use SoftDeleteable;

    public const TYPE_TEXT = 'text';
    public const TYPE_COLOR = 'color';
    public const TYPE_IMAGE = 'image';
    public const TYPE_DROPDOWN = 'dropdown';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['variant:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank(message: 'Le nom du type de variante est obligatoire.')]
    #[Assert\Length(max: 100, maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.')]
    #[Groups(['variant:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank(message: 'Le slug est obligatoire.')]
    #[Assert\Regex(pattern: '/^[a-z0-9\-]+$/', message: 'Le slug ne peut contenir que des lettres minuscules, chiffres et tirets.')]
    #[Groups(['variant:read'])]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['variant:read'])]
    private ?string $description = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::TYPE_TEXT, self::TYPE_COLOR, self::TYPE_IMAGE, self::TYPE_DROPDOWN])]
    #[Groups(['variant:read'])]
    private string $displayType = self::TYPE_TEXT;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['variant:read'])]
    private ?array $options = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['variant:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['variant:read'])]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column]
    #[Groups(['variant:read'])]
    private bool $isActive = true;

    #[ORM\Column]
    #[Groups(['variant:read'])]
    private int $sortOrder = 0;

    #[ORM\OneToMany(mappedBy: 'variantType', targetEntity: Variant::class)]
    private Collection $variants;

    public function __construct()
    {
        $this->variants = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->options = [];
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

    public function getDisplayType(): string
    {
        return $this->displayType;
    }

    public function setDisplayType(string $displayType): static
    {
        $this->displayType = $displayType;
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
     * @return Collection<int, Variant>
     */
    public function getVariants(): Collection
    {
        return $this->variants;
    }

    public function addVariant(Variant $variant): static
    {
        if (!$this->variants->contains($variant)) {
            $this->variants->add($variant);
            $variant->setVariantType($this);
        }

        return $this;
    }

    public function removeVariant(Variant $variant): static
    {
        if ($this->variants->removeElement($variant)) {
            // set the owning side to null (unless already changed)
            if ($variant->getVariantType() === $this) {
                $variant->setVariantType(null);
            }
        }

        return $this;
    }

    public function getDisplayTypeLabel(): string
    {
        return match($this->displayType) {
            self::TYPE_TEXT => 'Texte',
            self::TYPE_COLOR => 'Couleur',
            self::TYPE_IMAGE => 'Image',
            self::TYPE_DROPDOWN => 'Liste déroulante',
            default => 'Inconnu'
        };
    }

    public static function getDisplayTypes(): array
    {
        return [
            self::TYPE_TEXT => 'Texte',
            self::TYPE_COLOR => 'Couleur',
            self::TYPE_IMAGE => 'Image',
            self::TYPE_DROPDOWN => 'Liste déroulante',
        ];
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}