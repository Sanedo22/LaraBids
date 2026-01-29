<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAuctionRequest extends FormRequest
{
    // Authorization
    public function authorize(): bool
    {
        return auth()->check();
    }

    // Validation rules
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'starting_price' => ['required', 'numeric', 'min:0.01', 'max:999999999'],
            'start_time' => ['required', 'date', 'after_or_equal:' . now()->subDay()->toDateTimeString()],
            'end_time' => ['required', 'date', 'after:start_time'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'document' => ['nullable', 'file', 'mimes:pdf,jpg,png,jpeg,doc,docx', 'max:5120'],
            'specifications' => ['nullable', 'array'],
        ];
    }

    // Custom messages
    public function messages(): array
    {
        return [
            'title.required' => 'Item title is required.',
            'title.min' => 'Title must be at least 3 characters.',
            'title.max' => 'Title must not exceed 255 characters.',
            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'Selected category is invalid.',
            'description.required' => 'Description is required.',
            'description.min' => 'Description must be at least 20 characters.',
            'description.max' => 'Description must not exceed 5000 characters.',
            'starting_price.required' => 'Starting price is required.',
            'starting_price.numeric' => 'Starting price must be a valid number.',
            'starting_price.min' => 'Starting price must be at least $0.01.',
            'starting_price.max' => 'Starting price is too high.',
            'start_time.required' => 'Auction start date and time is required.',
            'start_time.date' => 'Please enter a valid start date and time.',
            'start_time.after_or_equal' => 'Auction start time cannot be in the past.',
            'end_time.required' => 'Auction end date and time is required.',
            'end_time.date' => 'Please enter a valid end date and time.',
            'end_time.after' => 'End time must be after the start time.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'Image must be a JPEG, PNG, JPG, or GIF file.',
            'image.max' => 'Image size must not exceed 2MB.',
            'document.file' => 'The uploaded file is invalid.',
            'document.mimes' => 'Document must be a PDF, JPG, PNG, DOC, or DOCX file.',
            'document.max' => 'Document size must not exceed 5MB.',
        ];
    }
}
