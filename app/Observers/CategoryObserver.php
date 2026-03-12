<?php

namespace App\Observers;

use App\Models\Category;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CategoryObserver
{
    /**
     * Limpa cache do tenant quando categorias mudam
     */
    private function clearTenantCache(): void
    {
        if (tenancy()->initialized) {
            $tenantId = tenant('id');
            Cache::forget("tenant_{$tenantId}_common_data");
        }
    }
    /**
     * Handle the Category "saving" event.
     * Empurra outras categorias para baixo se necessário
     */
    public function saving(Category $category): void
    {
        // Se não está definindo ordem, usar 0
        if (is_null($category->order)) {
            $category->order = 0;
        }

        $desiredOrder = $category->order;

        // Verificar se já existe outra categoria com essa ordem
        $conflictQuery = Category::where('order', $desiredOrder);

        // Se está atualizando (não criando), excluir a própria categoria da busca
        if ($category->exists) {
            $conflictQuery->where('id', '!=', $category->id);
        }

        // Se há conflito, EMPURRAR todas as categorias >= desiredOrder para baixo
        if ($conflictQuery->exists()) {
            // Pegar todas as categorias com ordem >= ordem desejada
            $query = Category::where('order', '>=', $desiredOrder);

            if ($category->exists) {
                $query->where('id', '!=', $category->id);
            }

            // Incrementar +1 em todas (empurrar para baixo)
            // Fazer em ordem decrescente para evitar conflitos de unique
            $categoriesToPush = $query->orderBy('order', 'desc')->get();

            foreach ($categoriesToPush as $cat) {
                $cat->order++;
                $cat->saveQuietly(); // Salvar sem disparar observers novamente
            }

            // Notificar que empurrou outras categorias
            $pushedCount = $categoriesToPush->count();
            if ($pushedCount > 0) {
                defer(function () use ($pushedCount, $desiredOrder) {
                    Notification::make()
                        ->title('Posições ajustadas')
                        ->body("{$pushedCount} categoria(s) foram empurradas para baixo para liberar a posição {$desiredOrder}.")
                        ->success()
                        ->send();
                });
            }
        }
    }

    /**
     * Handle the Category "saved" event.
     */
    public function saved(Category $category): void
    {
        $this->clearTenantCache();
    }

    /**
     * Handle the Category "deleted" event.
     */
    public function deleted(Category $category): void
    {
        $this->clearTenantCache();
    }
}
