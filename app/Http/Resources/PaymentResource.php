<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'txnid' => $this->txnid,
            'amount' => (float)$this->amount,
            'fee' => $this->commission_amount ?? ($this->amount * 0.05),
            'status' => $this->status,
            'method' => 'ONLINE',
            'user' => [
                'id' => $this->user_id,
                'name' => $this->user->name ?? 'N/A',
                'email' => $this->user->email ?? 'N/A',
            ],
            'auction' => [
                'id' => $this->auction_id,
                'title' => $this->auction->title ?? 'N/A',
                'seller' => ($this->auction && $this->auction->user) ? $this->auction->user->name : 'N/A',
            ],
            'additional_data' => $this->additional_data,
            'created_at' => $this->created_at->format('M d, Y H:i'),
            'updated_at' => $this->updated_at->format('M d, Y H:i'),
        ];
    }
}
