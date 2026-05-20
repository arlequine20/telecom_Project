<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'sim_number', 'phone_number', 'balance', 'status', 
        'tariff_plan', 'customer_id', 'assigned_at', 'last_activity_at', 'data_balance'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'balance' => 'decimal:2',
        'data_balance' => 'decimal:2'
    ];

    public static function normalizePhoneNumber(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);
        return $digits ?: null;
    }

    public static function findByPhone(string $phone): ?self
    {
        $normalized = self::normalizePhoneNumber($phone);

        if (!$normalized) {
            return null;
        }

        $simCard = self::whereRaw(
            "REPLACE(REPLACE(REPLACE(REPLACE(phone_number, '+', ''), ' ', ''), '-', ''), '.', '') = ?",
            [$normalized]
        )->first();

        if ($simCard) {
            return $simCard;
        }

        $suffix = substr($normalized, -9);
        if (!$suffix) {
            return null;
        }

        return self::where('phone_number', 'like', "%{$suffix}")
            ->get()
            ->first(function ($sim) use ($suffix) {
                return substr(self::normalizePhoneNumber($sim->phone_number), -9) === $suffix;
            });
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function sentTransactions()
    {
        return $this->hasMany(Transaction::class, 'from_sim_id');
    }

    public function receivedTransactions()
    {
        return $this->hasMany(Transaction::class, 'to_sim_id');
    }

    public function isActive()
    {
        // Active if assigned and has recent activity
        if ($this->customer_id === null) {
            return false;
        }
        
        $lastActivity = $this->last_activity_at ?? $this->assigned_at;
        $daysInactive = $lastActivity ? $lastActivity->diffInDays(now()) : null;
        
        return $this->status === 'active' && (!$daysInactive || $daysInactive < 30);
    }

    public function hasSufficientBalance($amount)
    {
        return $this->balance >= $amount;
    }

    public function recordActivity()
    {
        $this->last_activity_at = now();
        $this->save();
        return $this;
    }

    public function addBalance($amount)
    {
        $this->balance += $amount;
        $this->recordActivity();
        return $this;
    }

    public function addDataBalance($amount)
    {
        $this->data_balance += $amount;
        $this->recordActivity();
        $this->save();
        return $this;
    }

    public function deductBalance($amount)
    {
        if ($this->hasSufficientBalance($amount)) {
            $this->balance -= $amount;
            $this->recordActivity();
            $this->save();
            return true;
        }
        return false;
    }
}