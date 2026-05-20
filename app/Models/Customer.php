<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'email', 'phone', 
        'address', 'national_id', 'date_of_birth', 'status'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function simCards()
    {
        return $this->hasMany(SimCard::class);
    }

    public function transactions()
    {
        return $this->hasManyThrough(Transaction::class, SimCard::class, 'customer_id', 'from_sim_id');
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getTotalBalanceAttribute()
    {
        return $this->simCards->sum('balance');
    }
}