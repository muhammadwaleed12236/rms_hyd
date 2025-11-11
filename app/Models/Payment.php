<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'method',
        'amount',
        'transaction_id',
    ];

    /**
     * Cast amount to float for proper summing and arithmetic.
     */
    protected $casts = [
        'amount' => 'float',
    ];

    /**
     * Each Payment belongs to a single Sale.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}
