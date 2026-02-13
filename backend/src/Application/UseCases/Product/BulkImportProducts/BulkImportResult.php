<?php

declare(strict_types=1);

namespace Src\Application\UseCases\Product\BulkImportProducts;

final readonly class BulkImportResult
{
    /**
     * @param  array<array{shopifyId: string, error: string}>  $errors
     */
    public function __construct(
        public int $successCount,
        public int $failedCount,
        public int $skippedCount,
        public array $errors = [],
    ) {}

    public function totalProcessed(): int
    {
        return $this->successCount + $this->failedCount + $this->skippedCount;
    }

    public function hasErrors(): bool
    {
        return $this->failedCount > 0;
    }

    public function successRate(): float
    {
        $total = $this->totalProcessed();
        if ($total === 0) {
            return 0.0;
        }

        return ($this->successCount / $total) * 100;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'successCount' => $this->successCount,
            'failedCount' => $this->failedCount,
            'skippedCount' => $this->skippedCount,
            'totalProcessed' => $this->totalProcessed(),
            'successRate' => round($this->successRate(), 2),
            'errors' => $this->errors,
        ];
    }
}
