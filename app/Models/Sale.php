<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'barber_id',
        'client_id',
        'client_name',
        'amount',
        'payment_method',
        'sold_at',
        'notes',
    ];

    protected $casts = [
        'sold_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function barber()
    {
        return $this->belongsTo(Barber::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
