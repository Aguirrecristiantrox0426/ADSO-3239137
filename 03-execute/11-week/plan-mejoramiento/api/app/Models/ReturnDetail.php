<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnDetail extends Model
{
    protected $fillable = [
        'return_id',
        'rental_detail_id',
        'returned_quantity',
    ];

    protected function casts(): array
    {
        return [
            'returned_quantity' => 'integer',
        ];
    }

    public function rentalReturn(): BelongsTo
    {
        return $this->belongsTo(RentalReturn::class, 'return_id');
    }

    public function rentalDetail(): BelongsTo
    {
        return $this->belongsTo(RentalDetail::class);
    }
}