<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlaceBidRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // User must be logged in to bid
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $auction = $this->route('auction');
        $minBid = ($auction->current_price > 0) ? $auction->current_price + 0.01 : $auction->starting_price;

        return [
            'amount' => [
                'required',
                'numeric',
                'min:' . $minBid,
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Please enter a bid amount.',
            'amount.numeric' => 'The bid amount must be a number.',
            'amount.min' => 'Your bid must be higher than the current price.',
        ];
    }
}
