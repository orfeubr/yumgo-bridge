<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class WeeklyMenu extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'date',
        'ends_at' => 'date',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(WeeklyMenuItem::class)->orderBy('order');
    }

    public function itemsForDay(string $dayOfWeek): HasMany
    {
        return $this->items()->where('day_of_week', $dayOfWeek)->where('is_available', true);
    }

    public function itemsForToday(): HasMany
    {
        $today = strtolower(Carbon::now()->englishDayOfWeek);
        return $this->itemsForDay($today);
    }

    public static function getActive()
    {
        return self::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->first();
    }

    public static function getDaysOfWeek(): array
    {
        return [
            'monday' => '🔵 Segunda-feira',
            'tuesday' => '🟢 Terça-feira',
            'wednesday' => '🟡 Quarta-feira',
            'thursday' => '🟠 Quinta-feira',
            'friday' => '🔴 Sexta-feira',
            'saturday' => '🟣 Sábado',
            'sunday' => '⚪ Domingo',
        ];
    }

    public static function getCurrentDayOfWeek(): string
    {
        return strtolower(Carbon::now()->englishDayOfWeek);
    }
}
