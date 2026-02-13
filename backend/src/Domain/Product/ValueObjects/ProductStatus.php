<?php

declare(strict_types=1);

namespace Src\Domain\Product\ValueObjects;

enum ProductStatus: string
{
    case ACTIVE = 'active';
    case DRAFT = 'draft';
    case ARCHIVED = 'archived';

    public static function fromString(string $value): self
    {
        return self::from(strtolower(trim($value)));
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function isDraft(): bool
    {
        return $this === self::DRAFT;
    }

    public function isArchived(): bool
    {
        return $this === self::ARCHIVED;
    }

    public function isEditable(): bool
    {
        return $this !== self::ARCHIVED;
    }

    /**
     * @return array<self>
     */
    public static function all(): array
    {
        return self::cases();
    }
}
