<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Advisor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'full_name',
        'identification_number',
        'email',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(RentalReturn::class);
    }
}
