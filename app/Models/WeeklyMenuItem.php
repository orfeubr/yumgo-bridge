<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyMenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'weekly_menu_id',
        'product_id',
        'day_of_week',
        'special_price',
        'order',
        'is_available',
    ];

    protected $casts = [
        'special_price' => 'decimal:2',
        'order' => 'integer',
        'is_available' => 'boolean',
    ];

    public function weeklyMenu(): BelongsTo
    {
        return $this->belongsTo(WeeklyMenu::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getEffectivePrice(): float
    {
        return $this->special_price ?? $this->product->price;
    }

    public function getDayLabel(): string
    {
        $days = WeeklyMenu::getDaysOfWeek();
        return $days[$this->day_of_week] ?? $this->day_of_week;
    }
}
