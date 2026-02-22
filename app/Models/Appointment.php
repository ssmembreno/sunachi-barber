<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use SoftDeletes;

   protected $fillable = [
        'start_at',
        'end_at',
        'status',
        'source',
        'notes',
        'client_notes',
        'price',
        'cancelled_at',
        'cancel_reason',
        'client_id',
        'created_by',
        'barber_id',
        'meta',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'meta' => 'array',
    ];

    public function barber()
    {
        return $this->belongsTo(User::class, 'barber_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function clientUser()
    {
        return $this->belongsTo(User::class, 'client_user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(AppointmentItem::class);
    }
}
