<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'sku',
        'description',
        'buying_price',
        'selling_price',
        'quantity',
        'reorder_level',
        'unit',
        'image',
        'expiry_date',
        'business_type',
        'is_active'
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'buying_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->reorder_level && $this->quantity > 0;
    }

    public function isOutOfStock(): bool
    {
        return $this->quantity <= 0;
    }

    public function isExpiringSoon(): bool
    {
        if (!$this->expiry_date) return false;
        return $this->expiry_date->lte(Carbon::now()->addDays(30)) && $this->expiry_date->gte(Carbon::now());
    }

    public function isExpired(): bool
    {
        if (!$this->expiry_date) return false;
        return $this->expiry_date->lt(Carbon::now());
    }

    public function getStockValueAttribute(): float
    {
        return $this->quantity * $this->buying_price;
    }

    public function getPotentialRevenueAttribute(): float
    {
        return $this->quantity * $this->selling_price;
    }

    public function getProfitMarginAttribute(): float
    {
        if ($this->selling_price == 0) return 0;
        return (($this->selling_price - $this->buying_price) / $this->selling_price) * 100;
    }
}
