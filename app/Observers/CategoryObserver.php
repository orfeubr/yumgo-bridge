<?php

namespace App\Observers;

use App\Models\Category;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class CategoryObserver
{
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
}
