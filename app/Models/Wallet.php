<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'total_spend',
        'data_balance',
        'data_unit',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'total_spend' => 'decimal:2',
        'data_balance' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function addBalance($amount)
    {
        $this->balance += $amount;
        $this->save();
        return $this;
    }

    public function deductBalance($amount)
    {
        if ($this->balance >= $amount) {
            $this->balance -= $amount;
            $this->total_spend += $amount;
            $this->save();
            return true;
        }
        return false;
    }

    public function addDataBalance($amount)
    {
        $this->data_balance += $amount;
        $this->save();
        return $this;
    }

    public function deductDataBalance($amount)
    {
        if ($this->data_balance >= $amount) {
            $this->data_balance -= $amount;
            $this->save();
            return true;
        }
        return false;
    }
}
