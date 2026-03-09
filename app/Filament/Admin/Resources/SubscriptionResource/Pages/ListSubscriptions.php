<?php

namespace App\Filament\Admin\Resources\SubscriptionResource\Pages;

use App\Filament\Admin\Resources\SubscriptionResource;
use App\Models\Subscription;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSubscriptions extends ListRecords
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SubscriptionResource\Widgets\SubscriptionStatsWidget::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Todas')
                ->badge(Subscription::count()),

            'active' => Tab::make('Ativas')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'active'))
                ->badge(Subscription::where('status', 'active')->count())
                ->badgeColor('success'),

            'trialing' => Tab::make('Trial')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'trialing'))
                ->badge(Subscription::where('status', 'trialing')->count())
                ->badgeColor('info'),

            'past_due' => Tab::make('Atrasadas')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'past_due'))
                ->badge(Subscription::where('status', 'past_due')->count())
                ->badgeColor('danger'),

            'canceled' => Tab::make('Canceladas')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'canceled'))
                ->badge(Subscription::where('status', 'canceled')->count())
                ->badgeColor('gray'),
        ];
    }
}
