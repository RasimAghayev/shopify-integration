<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ValueObjects;

use PHPUnit\Framework\TestCase;
use Src\Domain\Product\ValueObjects\ProductStatus;

final class ProductStatusTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_active_status(): void
    {
        $status = ProductStatus::ACTIVE;

        $this->assertEquals('active', $status->value);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_draft_status(): void
    {
        $status = ProductStatus::DRAFT;

        $this->assertEquals('draft', $status->value);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_archived_status(): void
    {
        $status = ProductStatus::ARCHIVED;

        $this->assertEquals('archived', $status->value);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_from_string(): void
    {
        $status = ProductStatus::fromString('active');

        $this->assertEquals(ProductStatus::ACTIVE, $status);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_from_uppercase_string(): void
    {
        $status = ProductStatus::fromString('ACTIVE');

        $this->assertEquals(ProductStatus::ACTIVE, $status);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_from_mixed_case_string(): void
    {
        $status = ProductStatus::fromString('Active');

        $this->assertEquals(ProductStatus::ACTIVE, $status);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_throws_exception_for_invalid_status(): void
    {
        $this->expectException(\ValueError::class);

        ProductStatus::fromString('invalid');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_checks_if_active(): void
    {
        $active = ProductStatus::ACTIVE;
        $draft = ProductStatus::DRAFT;

        $this->assertTrue($active->isActive());
        $this->assertFalse($draft->isActive());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_checks_if_draft(): void
    {
        $draft = ProductStatus::DRAFT;
        $active = ProductStatus::ACTIVE;

        $this->assertTrue($draft->isDraft());
        $this->assertFalse($active->isDraft());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_checks_if_archived(): void
    {
        $archived = ProductStatus::ARCHIVED;
        $active = ProductStatus::ACTIVE;

        $this->assertTrue($archived->isArchived());
        $this->assertFalse($active->isArchived());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_checks_if_editable(): void
    {
        $active = ProductStatus::ACTIVE;
        $draft = ProductStatus::DRAFT;
        $archived = ProductStatus::ARCHIVED;

        $this->assertTrue($active->isEditable());
        $this->assertTrue($draft->isEditable());
        $this->assertFalse($archived->isEditable());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_all_statuses(): void
    {
        $statuses = ProductStatus::all();

        $this->assertCount(3, $statuses);
        $this->assertContains(ProductStatus::ACTIVE, $statuses);
        $this->assertContains(ProductStatus::DRAFT, $statuses);
        $this->assertContains(ProductStatus::ARCHIVED, $statuses);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_converts_to_string(): void
    {
        $status = ProductStatus::ACTIVE;

        $this->assertEquals('active', (string) $status->value);
    }
}
