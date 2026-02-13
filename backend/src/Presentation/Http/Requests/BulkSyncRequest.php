<?php

declare(strict_types=1);

namespace Src\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class BulkSyncRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<string>>
     */
    public function rules(): array
    {
        return [
            'shopify_ids' => ['required', 'array', 'min:1', 'max:100'],
            'shopify_ids.*' => ['required', 'string', 'max:50'],
            'skip_duplicates' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'shopify_ids.required' => 'At least one Shopify product ID is required',
            'shopify_ids.max' => 'Cannot sync more than 100 products at once',
        ];
    }
}
