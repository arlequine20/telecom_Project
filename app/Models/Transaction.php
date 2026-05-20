<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REVERSAL_REQUESTED = 'reversal_requested';
    public const STATUS_REVERSED = 'reversed';
    public const STATUS_REVERSAL_DENIED = 'reversal_denied';

    protected $fillable = [
        'transaction_reference', 'from_sim_id', 'to_sim_id', 
        'amount', 'fee', 'status', 'description', 'approved_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'approved_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public const REVIEW_THRESHOLD = 100000;

    public static function requiresAdminReview(float $amount): bool
    {
        return $amount >= self::REVIEW_THRESHOLD;
    }

    public function isReversalRequest(): bool
    {
        return $this->status === self::STATUS_REVERSAL_REQUESTED;
    }

    public function isReversed(): bool
    {
        return $this->status === self::STATUS_REVERSED;
    }

    public function isReversalDenied(): bool
    {
        return $this->status === self::STATUS_REVERSAL_DENIED;
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($transaction) {
            $transaction->transaction_reference = 'TXN-' . Str::upper(Str::random(13));
        });
    }

    public function fromSim()
    {
        return $this->belongsTo(SimCard::class, 'from_sim_id');
    }

    public function toSim()
    {
        return $this->belongsTo(SimCard::class, 'to_sim_id');
    }

    public function approve()
    {
        $this->status = 'approved';
        $this->approved_at = now();
        
        // Deduct from sender
        $this->fromSim->balance -= ($this->amount + $this->fee);
        $this->fromSim->save();
        
        // Add to receiver
        $this->toSim->balance += $this->amount;
        $this->toSim->save();
        
        $this->save();
    }

    public function cancel()
    {
        $this->status = 'cancelled';
        $this->cancelled_at = now();
        $this->save();
    }
}