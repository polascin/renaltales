<?php

declare(strict_types=1);

namespace RenalTales\Entities;

use Doctrine\ORM\Mapping as ORM;
use DateTime;
use DateTimeImmutable;

/**
 * Base Entity
 *
 * Abstract base class for all entities in the system.
 * Provides common functionality like timestamps and primary key handling.
 *
 * @package RenalTales\Entities
 * @version 2025.3.1.dev
 * @author Ľubomír Polaščín
 */
#[ORM\MappedSuperclass]
#[ORM\HasLifecycleCallbacks]
abstract class BaseEntity
{
    /**
     * @var int|null Primary key identifier
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected ?int $id = null;

    /**
     * @var DateTimeImmutable Entity creation timestamp
     */
    #[ORM\Column(type: 'datetime_immutable')]
    protected DateTimeImmutable $createdAt;

    /**
     * @var DateTime Entity last update timestamp
     */
    #[ORM\Column(type: 'datetime')]
    protected DateTime $updatedAt;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTime();
    }

    /**
     * Get the entity ID
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the creation timestamp
     *
     * @return DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Get the last update timestamp
     *
     * @return DateTime
     */
    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    /**
     * Set the last update timestamp
     *
     * @param DateTime $updatedAt
     * @return self
     */
    public function setUpdatedAt(DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Pre-persist lifecycle callback
     * Called before entity is persisted to database
     *
     * @return void
     */
    #[ORM\PrePersist]
    public function prePersist(): void
    {
        if (!isset($this->createdAt)) {
            $this->createdAt = new DateTimeImmutable();
        }
        $this->updatedAt = new DateTime();
    }

    /**
     * Pre-update lifecycle callback
     * Called before entity is updated in database
     *
     * @return void
     */
    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new DateTime();
    }

    /**
     * Convert entity to array representation
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Convert entity to JSON representation
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }

    /**
     * Check if entity is new (not persisted yet)
     *
     * @return bool
     */
    public function isNew(): bool
    {
        return $this->id === null;
    }

    /**
     * Magic method for debugging
     *
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return $this->toArray();
    }
}
