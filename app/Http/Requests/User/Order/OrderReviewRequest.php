<?php

namespace App\Http\Requests\User\Order;

use App\Models\User\Order\OrderItem;
use App\Models\User\Order\OrderReview;
use Illuminate\Foundation\Http\FormRequest;

class OrderReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'order_item_id' => [
                'required',
                'exists:order_items,id', // Ensure order item exists
            ],
            'rating' => ['required', 'integer', 'between:1,5'], // Rating must be between 1 and 5
            'review' => ['required', 'string', 'min:10'], // Review text, at least 10 characters
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'order_item_id.required' => 'The order item is required.',
            'order_item_id.exists'   => 'The selected order item is invalid.',
            'rating.required'        => 'A rating is required.',
            'rating.between'         => 'The rating must be between 1 and 5.',
            'review.required'        => 'The review cannot be empty.',
            'review.min'             => 'The review must be at least 10 characters long.',
        ];
    }
}
