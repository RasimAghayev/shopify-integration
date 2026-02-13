<?php

declare(strict_types=1);

namespace Src\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateInventoryRequest extends FormRequest
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
            'sku' => ['required', 'string', 'max:50'],
            'quantity' => ['required', 'integer', 'min:0'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sku.required' => 'Product SKU is required',
            'quantity.required' => 'Quantity is required',
            'quantity.min' => 'Quantity cannot be negative',
        ];
    }
}
