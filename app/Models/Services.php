<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Services extends Model
{
    protected $fillable = [
        'name',
        'duration_minutes',
        'price',
        'description',
        'is_active',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
