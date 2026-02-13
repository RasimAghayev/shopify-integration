<?php

declare(strict_types=1);

namespace Src\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SyncProductRequest extends FormRequest
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
            'shopify_id' => ['required', 'string', 'max:50'],
            'force_update' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'shopify_id.required' => 'Shopify product ID is required',
            'shopify_id.max' => 'Shopify product ID cannot exceed 50 characters',
        ];
    }
}
