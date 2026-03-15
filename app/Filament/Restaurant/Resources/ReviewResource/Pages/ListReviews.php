<?php

namespace App\Filament\Restaurant\Resources\ReviewResource\Pages;

use App\Filament\Restaurant\Resources\ReviewResource;
use App\Models\Review;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListReviews extends ListRecords
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Reviews são criados pelos clientes via API, não pelo painel
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Todas')
                ->badge(Review::count()),

            'pending' => Tab::make('Sem Resposta')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('response'))
                ->badge(Review::whereNull('response')->count())
                ->badgeColor('warning'),

            'responded' => Tab::make('Respondidas')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('response'))
                ->badge(Review::whereNotNull('response')->count())
                ->badgeColor('success'),

            'public' => Tab::make('Públicas')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_public', true))
                ->badge(Review::where('is_public', true)->count()),

            'private' => Tab::make('Privadas')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_public', false))
                ->badge(Review::where('is_public', false)->count()),

            '5_stars' => Tab::make('5 Estrelas')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('rating', 5))
                ->badge(Review::where('rating', 5)->count())
                ->badgeColor('success'),

            '1_2_stars' => Tab::make('1-2 Estrelas')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('rating', [1, 2]))
                ->badge(Review::whereIn('rating', [1, 2])->count())
                ->badgeColor('danger'),
        ];
    }
}
