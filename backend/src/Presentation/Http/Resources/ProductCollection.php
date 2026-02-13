<?php

declare(strict_types=1);

namespace Src\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

final class ProductCollection extends ResourceCollection
{
    /**
     * @param  array{data: array, total: int, page: int, per_page: int}  $resource
     */
    public function __construct(array $resource)
    {
        parent::__construct($resource['data']);

        $this->additional([
            'meta' => [
                'total' => $resource['total'],
                'page' => $resource['page'],
                'perPage' => $resource['per_page'],
                'lastPage' => (int) ceil($resource['total'] / $resource['per_page']),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => ProductResource::collection($this->collection),
        ];
    }
}
