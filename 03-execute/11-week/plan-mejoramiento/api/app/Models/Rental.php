<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rental extends Model
{
    protected $fillable = [
        'customer_id',
        'advisor_id',
        'rental_date',
        'due_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'rental_date' => 'date',
            'due_date' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function advisor(): BelongsTo
    {
        return $this->belongsTo(Advisor::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(RentalDetail::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(RentalReturn::class);
    }
}