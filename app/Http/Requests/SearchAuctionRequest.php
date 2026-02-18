<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchAuctionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public endpoint, no authorization needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'keyword' => 'nullable|string|min:2|max:255',
            'q' => 'nullable|string|min:2|max:255', // Alias for keyword
            'category' => 'nullable|string|exists:categories,slug',
            'category_id' => 'nullable|integer|exists:categories,id',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:active,pending,closed,cancelled,draft,past,all',
            'sort' => 'nullable|string|in:latest,price_asc,price_desc,ending_soon',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'keyword.min' => 'Search keyword must be at least 2 characters.',
            'q.min' => 'Search query must be at least 2 characters.',
            'category.exists' => 'The selected category does not exist.',
            'category_id.exists' => 'The selected category ID does not exist.',
            'min_price.min' => 'Minimum price must be at least 0.',
            'max_price.min' => 'Maximum price must be at least 0.',
            'status.in' => 'Invalid status. Allowed values: active, pending, closed, cancelled, draft, past, all.',
            'sort.in' => 'Invalid sort option. Allowed values: latest, price_asc, price_desc, ending_soon.',
            'per_page.max' => 'Maximum results per page is 100.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // If 'keyword' is provided, use it as 'q' for consistency
        if ($this->has('keyword') && !$this->has('q')) {
            $this->merge(['q' => $this->input('keyword')]);
        }
    }
}
