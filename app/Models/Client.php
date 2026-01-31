<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Services;

class Client extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'notes',
        'user_id',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
