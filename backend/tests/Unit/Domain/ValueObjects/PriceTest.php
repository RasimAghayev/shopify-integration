<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ValueObjects;

use PHPUnit\Framework\TestCase;
use Src\Domain\Product\Exceptions\CurrencyMismatchException;
use Src\Domain\Product\Exceptions\InvalidPriceException;
use Src\Domain\Product\ValueObjects\Currency;
use Src\Domain\Product\ValueObjects\Price;

final class PriceTest extends TestCase
{
    /** @test */
    public function it_creates_valid_price(): void
    {
        $price = new Price(1999, Currency::USD);

        $this->assertEquals(1999, $price->amount);
        $this->assertEquals(Currency::USD, $price->currency);
    }

    /** @test */
    public function it_converts_to_decimal(): void
    {
        $price = new Price(1999, Currency::USD);

        $this->assertEquals(19.99, $price->toDecimal());
    }

    /** @test */
    public function it_converts_zero_to_decimal(): void
    {
        $price = new Price(0, Currency::USD);

        $this->assertEquals(0.00, $price->toDecimal());
    }

    /** @test */
    public function it_throws_exception_for_negative_amount(): void
    {
        $this->expectException(InvalidPriceException::class);
        $this->expectExceptionMessage('Price cannot be negative');

        new Price(-100, Currency::USD);
    }

    /** @test */
    public function it_allows_zero_price(): void
    {
        $price = new Price(0, Currency::USD);

        $this->assertEquals(0, $price->amount);
    }

    /** @test */
    public function it_compares_less_than(): void
    {
        $price1 = new Price(1000, Currency::USD);
        $price2 = new Price(2000, Currency::USD);

        $this->assertTrue($price1->isLessThan($price2));
        $this->assertFalse($price2->isLessThan($price1));
    }

    /** @test */
    public function it_compares_greater_than(): void
    {
        $price1 = new Price(2000, Currency::USD);
        $price2 = new Price(1000, Currency::USD);

        $this->assertTrue($price1->isGreaterThan($price2));
        $this->assertFalse($price2->isGreaterThan($price1));
    }

    /** @test */
    public function it_compares_equality(): void
    {
        $price1 = new Price(1999, Currency::USD);
        $price2 = new Price(1999, Currency::USD);
        $price3 = new Price(2999, Currency::USD);

        $this->assertTrue($price1->equals($price2));
        $this->assertFalse($price1->equals($price3));
    }

    /** @test */
    public function it_throws_exception_when_comparing_different_currencies(): void
    {
        $this->expectException(CurrencyMismatchException::class);

        $priceUsd = new Price(1000, Currency::USD);
        $priceEur = new Price(1000, Currency::EUR);

        $priceUsd->isLessThan($priceEur);
    }

    /** @test */
    public function it_adds_prices_with_same_currency(): void
    {
        $price1 = new Price(1000, Currency::USD);
        $price2 = new Price(500, Currency::USD);

        $result = $price1->add($price2);

        $this->assertEquals(1500, $result->amount);
        $this->assertEquals(Currency::USD, $result->currency);
    }

    /** @test */
    public function it_throws_exception_when_adding_different_currencies(): void
    {
        $this->expectException(CurrencyMismatchException::class);

        $priceUsd = new Price(1000, Currency::USD);
        $priceEur = new Price(500, Currency::EUR);

        $priceUsd->add($priceEur);
    }

    /** @test */
    public function it_subtracts_prices_with_same_currency(): void
    {
        $price1 = new Price(1000, Currency::USD);
        $price2 = new Price(300, Currency::USD);

        $result = $price1->subtract($price2);

        $this->assertEquals(700, $result->amount);
    }

    /** @test */
    public function it_throws_exception_when_subtraction_results_in_negative(): void
    {
        $this->expectException(InvalidPriceException::class);

        $price1 = new Price(100, Currency::USD);
        $price2 = new Price(500, Currency::USD);

        $price1->subtract($price2);
    }

    /** @test */
    public function it_multiplies_price_by_quantity(): void
    {
        $price = new Price(1000, Currency::USD);

        $result = $price->multiply(3);

        $this->assertEquals(3000, $result->amount);
    }

    /** @test */
    public function it_formats_price_as_string(): void
    {
        $price = new Price(1999, Currency::USD);

        $this->assertEquals('$19.99', $price->format());
    }

    /** @test */
    public function it_formats_euro_price(): void
    {
        $price = new Price(1999, Currency::EUR);

        $this->assertEquals('19.99 EUR', $price->format());
    }

    /** @test */
    public function it_creates_from_decimal(): void
    {
        $price = Price::fromDecimal(19.99, Currency::USD);

        $this->assertEquals(1999, $price->amount);
    }

    /** @test */
    public function it_creates_zero_price(): void
    {
        $price = Price::zero(Currency::USD);

        $this->assertEquals(0, $price->amount);
        $this->assertEquals(Currency::USD, $price->currency);
    }
}
