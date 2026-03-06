<?php

namespace App\Filament\Admin\Widgets;

use App\Filament\Admin\Resources\TenantResource;
use App\Models\Tenant;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestTenantsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Restaurantes Recentes')
            ->query(
                Tenant::query()
                    ->with('plan')
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email copiado!'),

                Tables\Columns\TextColumn::make('plan.name')
                    ->label('Plano')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Starter' => 'info',
                        'Pro' => 'success',
                        'Enterprise' => 'warning',
                        default => 'gray',
                    })
                    ->default('Sem plano'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'trial' => 'warning',
                        'active' => 'success',
                        'inactive' => 'gray',
                        'suspended' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'trial' => 'Trial',
                        'active' => 'Ativo',
                        'inactive' => 'Inativo',
                        'suspended' => 'Suspenso',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Ver')
                    ->url(fn (Tenant $record): string => TenantResource::getUrl('edit', ['record' => $record]))
                    ->icon('heroicon-m-eye')
                    ->color('primary'),
            ]);
    }
}
