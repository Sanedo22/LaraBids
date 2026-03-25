<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kyc extends Model
{
    protected $fillable = [
        'user_id',
        'full_name',
        'date_of_birth',
        'gender',
        'id_type',
        'id_number',
        'id_document',
        'selfie_image',
        'signature_image',
        'status',
        'admin_note',
        'is_resubmitted',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
