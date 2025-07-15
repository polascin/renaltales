<?php

declare(strict_types=1);

namespace RenalTales\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Language Entity
 *
 * Represents a language in the system using Doctrine ORM.
 * Contains language code, name, and status information.
 *
 * @package RenalTales\Entities
 * @version 2025.3.1.dev
 * @author Ľubomír Polaščín
 */
#[ORM\Entity(repositoryClass: 'RenalTales\Repositories\DoctrineLanguageRepository')]
#[ORM\Table(name: 'languages')]
#[ORM\UniqueConstraint(name: 'languages_code_unique', columns: ['code'])]
#[ORM\Index(name: 'languages_active_idx', columns: ['active'])]
class Language extends BaseEntity
{
    /**
     * @var string Language code (ISO 639-1 or custom)
     */
    #[ORM\Column(type: 'string', length: 10, unique: true)]
    private string $code;

    /**
     * @var string Language name (in English)
     */
    #[ORM\Column(type: 'string', length: 100)]
    private string $name;

    /**
     * @var string Language native name (in the language itself)
     */
    #[ORM\Column(type: 'string', length: 100)]
    private string $nativeName;

    /**
     * @var bool Whether the language is active
     */
    #[ORM\Column(type: 'boolean')]
    private bool $active;

    /**
     * @var bool Whether the language is the default language
     */
    #[ORM\Column(type: 'boolean')]
    private bool $isDefault;

    /**
     * @var string|null Language direction (ltr or rtl)
     */
    #[ORM\Column(type: 'string', length: 3, nullable: true)]
    private ?string $direction = 'ltr';

    /**
     * @var string|null Language region/country code
     */
    #[ORM\Column(type: 'string', length: 5, nullable: true)]
    private ?string $region = null;

    /**
     * @var int Sort order for displaying languages
     */
    #[ORM\Column(type: 'integer')]
    private int $sortOrder = 0;

    /**
     * Constructor
     *
     * @param string $code Language code
     * @param string $name Language name
     * @param string $nativeName Language native name
     * @param bool $active Whether language is active
     * @param bool $isDefault Whether language is default
     */
    public function __construct(
        string $code,
        string $name,
        string $nativeName,
        bool $active = true,
        bool $isDefault = false
    ) {
        parent::__construct();
        $this->code = $code;
        $this->name = $name;
        $this->nativeName = $nativeName;
        $this->active = $active;
        $this->isDefault = $isDefault;
    }

    /**
     * Get language code
     *
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Set language code
     *
     * @param string $code
     * @return self
     */
    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Get language name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set language name
     *
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get native language name
     *
     * @return string
     */
    public function getNativeName(): string
    {
        return $this->nativeName;
    }

    /**
     * Set native language name
     *
     * @param string $nativeName
     * @return self
     */
    public function setNativeName(string $nativeName): self
    {
        $this->nativeName = $nativeName;
        return $this;
    }

    /**
     * Check if language is active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * Set language active status
     *
     * @param bool $active
     * @return self
     */
    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }

    /**
     * Check if language is default
     *
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    /**
     * Set language as default
     *
     * @param bool $isDefault
     * @return self
     */
    public function setIsDefault(bool $isDefault): self
    {
        $this->isDefault = $isDefault;
        return $this;
    }

    /**
     * Get language direction
     *
     * @return string|null
     */
    public function getDirection(): ?string
    {
        return $this->direction;
    }

    /**
     * Set language direction
     *
     * @param string|null $direction
     * @return self
     */
    public function setDirection(?string $direction): self
    {
        $this->direction = $direction;
        return $this;
    }

    /**
     * Get language region
     *
     * @return string|null
     */
    public function getRegion(): ?string
    {
        return $this->region;
    }

    /**
     * Set language region
     *
     * @param string|null $region
     * @return self
     */
    public function setRegion(?string $region): self
    {
        $this->region = $region;
        return $this;
    }

    /**
     * Get sort order
     *
     * @return int
     */
    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    /**
     * Set sort order
     *
     * @param int $sortOrder
     * @return self
     */
    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    /**
     * Convert entity to array representation
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'code' => $this->code,
            'name' => $this->name,
            'native_name' => $this->nativeName,
            'active' => $this->active,
            'is_default' => $this->isDefault,
            'direction' => $this->direction,
            'region' => $this->region,
            'sort_order' => $this->sortOrder,
        ]);
    }

    /**
     * String representation of the language
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->nativeName . ' (' . $this->code . ')';
    }
}
