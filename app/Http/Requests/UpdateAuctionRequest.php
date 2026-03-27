<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAuctionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $auction = $this->route('auction');
        
        if (!$auction instanceof \App\Models\Auction) {
            $id = $this->route('auction') ?? $this->route('id') ?? $this->id;
            $auction = \App\Models\Auction::find($id);
        }

        if (!$auction) {
            return false;
        }

        // Only allow owner to update
        return auth()->check() && auth()->id() === $auction->user_id;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $auction = $this->route('auction');
        if (!$auction instanceof \App\Models\Auction) {
            $id = $this->route('auction') ?? $this->route('id') ?? $this->id;
            $auction = \App\Models\Auction::find($id);
        }

        $hasBids = $auction ? $auction->bids()->exists() : false;

        $rules = [
            'title' => ['sometimes', 'string', 'min:3', 'max:100'],
            'description' => ['sometimes', 'string', 'min:20', 'max:5000'],
            'category_id' => ['sometimes', 'exists:categories,id'],
            'start_time' => ['sometimes', 'date'],
            'end_time' => ['sometimes', 'date', 'after:start_time'],
            'min_increment' => 'nullable|numeric|min:0.01',
            'specifications' => ['nullable', 'array'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'deleted_images' => ['nullable', 'array'],
            'deleted_images.*' => ['exists:auction_images,id'],
            'reserve_price' => 'nullable|numeric|gte:starting_price',
            'location' => ['nullable', 'string', 'max:255'],
        ];

        // Custom validation for total image count (existing - deleted + new <= 5)
        if ($this->has('images') || $this->has('deleted_images')) {
            $existingCount = $auction ? $auction->images()->count() : 0;
            $deletedCount = is_array($this->deleted_images) ? count($this->deleted_images) : 0;
            $newCount = is_array($this->images) ? count($this->images) : 0;
            
            if (($existingCount - $deletedCount + $newCount) > 5) {
                // We'll add a custom validator error after the rules return if possible, 
                // but for now, let's use a simpler rule or just rely on the service.
                // Actually, let's add a Closure rule for 'images'
                $rules['images'][] = function ($attribute, $value, $fail) use ($existingCount, $deletedCount, $newCount) {
                    if (($existingCount - $deletedCount + $newCount) > 5) {
                        $fail('The total number of images cannot exceed 5.');
                    }
                };
            }
        }

        // Only allow price/category change if no bids exist
        if (!$hasBids) {
            $rules['starting_price'] = ['sometimes', 'numeric', 'min:100.00', 'max:999999999'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'end_time.after' => 'The auction end time must be after the start time.',
            'starting_price.min' => 'Starting price must be at least ₹100.00.',
            'images.max' => 'You can only upload up to 5 images.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.max' => 'Each image must not exceed 2MB.',
        ];
    }
}
