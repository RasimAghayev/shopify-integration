<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ValueObjects;

use PHPUnit\Framework\TestCase;
use Src\Domain\Product\Exceptions\InvalidSkuException;
use Src\Domain\Product\ValueObjects\Sku;

final class SkuTest extends TestCase
{
    /** @test */
    public function it_creates_valid_sku(): void
    {
        $sku = new Sku('TEST-001');

        $this->assertEquals('TEST-001', $sku->value);
    }

    /** @test */
    public function it_creates_sku_with_alphanumeric_characters(): void
    {
        $sku = new Sku('ABC123-XYZ-789');

        $this->assertEquals('ABC123-XYZ-789', $sku->value);
    }

    /** @test */
    public function it_throws_exception_for_empty_sku(): void
    {
        $this->expectException(InvalidSkuException::class);
        $this->expectExceptionMessage('SKU cannot be empty');

        new Sku('');
    }

    /** @test */
    public function it_throws_exception_for_whitespace_only_sku(): void
    {
        $this->expectException(InvalidSkuException::class);

        new Sku('   ');
    }

    /** @test */
    public function it_throws_exception_for_sku_with_spaces(): void
    {
        $this->expectException(InvalidSkuException::class);
        $this->expectExceptionMessage('SKU contains invalid characters');

        new Sku('TEST 001');
    }

    /** @test */
    public function it_throws_exception_for_sku_exceeding_max_length(): void
    {
        $this->expectException(InvalidSkuException::class);
        $this->expectExceptionMessage('SKU cannot exceed 50 characters');

        new Sku(str_repeat('A', 51));
    }

    /** @test */
    public function it_allows_sku_at_max_length(): void
    {
        $sku = new Sku(str_repeat('A', 50));

        $this->assertEquals(50, strlen($sku->value));
    }

    /** @test */
    public function it_throws_exception_for_special_characters(): void
    {
        $this->expectException(InvalidSkuException::class);

        new Sku('TEST@001!');
    }

    /** @test */
    public function it_allows_underscores_in_sku(): void
    {
        $sku = new Sku('TEST_001_VARIANT');

        $this->assertEquals('TEST_001_VARIANT', $sku->value);
    }

    /** @test */
    public function it_compares_equality_correctly(): void
    {
        $sku1 = new Sku('TEST-001');
        $sku2 = new Sku('TEST-001');
        $sku3 = new Sku('TEST-002');

        $this->assertTrue($sku1->equals($sku2));
        $this->assertFalse($sku1->equals($sku3));
    }

    /** @test */
    public function it_converts_to_string(): void
    {
        $sku = new Sku('TEST-001');

        $this->assertEquals('TEST-001', (string) $sku);
    }

    /** @test */
    public function it_trims_whitespace_from_sku(): void
    {
        $sku = new Sku('  TEST-001  ');

        $this->assertEquals('TEST-001', $sku->value);
    }

    /** @test */
    public function it_converts_to_uppercase(): void
    {
        $sku = new Sku('test-001');

        $this->assertEquals('TEST-001', $sku->value);
    }
}
