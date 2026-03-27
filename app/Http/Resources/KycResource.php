<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class KycResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'user_id'       => $this->user_id,
            'user'          => [
                'id'       => $this->user->id,
                'username' => $this->user->username,
                'email'    => $this->user->email,
            ],
            'full_name'     => $this->full_name,
            'date_of_birth' => $this->date_of_birth,
            'address'       => $this->address,
            'id_type'       => $this->id_type,
            'id_type_label' => match($this->id_type) {
                'aadhaar' => 'Aadhaar Card',
                'pan' => 'PAN Card',
                'passport' => 'Passport',
                'driving_license' => 'Driving License',
                default => ucfirst(str_replace('_', ' ', $this->id_type))
            },
            'id_number'     => $this->id_number,
            'id_document'   => $this->id_document ? asset('storage/' . $this->id_document) : null,
            'selfie_image'  => $this->selfie_image ? asset('storage/' . $this->selfie_image) : null,
            'gender'        => $this->gender,
            'signature'     => $this->signature ? asset('storage/' . $this->signature) : null,
            'status'        => $this->status,
            'is_resubmitted'=> (bool)$this->is_resubmitted,
            'admin_note'    => $this->admin_note,
            'created_at'    => $this->created_at->format('M d, Y H:i'),
            'updated_at'    => $this->updated_at->format('M d, Y H:i'),
        ];
    }
}

