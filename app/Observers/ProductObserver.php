<?php

namespace App\Observers;

use App\Models\Product;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

class ProductObserver
{
    /**
     * Handle the Product "saving" event.
     * Empurra outros produtos para baixo se necessário (dentro da mesma categoria)
     */
    public function saving(Product $product): void
    {
        // Se não está definindo ordem, usar 0
        if (is_null($product->order)) {
            $product->order = 0;
        }

        // Se não tem categoria, não validar ordem
        if (!$product->category_id) {
            return;
        }

        $desiredOrder = $product->order;

        // Verificar se já existe outro produto com essa ordem NA MESMA CATEGORIA
        $conflictQuery = Product::where('category_id', $product->category_id)
                                ->where('order', $desiredOrder);

        // Se está atualizando (não criando), excluir o próprio produto da busca
        if ($product->exists) {
            $conflictQuery->where('id', '!=', $product->id);
        }

        // Se há conflito, EMPURRAR todos os produtos >= desiredOrder para baixo
        if ($conflictQuery->exists()) {
            // Pegar todos os produtos com ordem >= ordem desejada NA MESMA CATEGORIA
            $query = Product::where('category_id', $product->category_id)
                           ->where('order', '>=', $desiredOrder);

            if ($product->exists) {
                $query->where('id', '!=', $product->id);
            }

            // Incrementar +1 em todos (empurrar para baixo)
            // Fazer em ordem decrescente para evitar conflitos
            $productsToPush = $query->orderBy('order', 'desc')->get();

            foreach ($productsToPush as $prod) {
                $prod->order++;
                $prod->saveQuietly(); // Salvar sem disparar observers novamente
            }

            // Notificar que empurrou outros produtos
            $pushedCount = $productsToPush->count();
            if ($pushedCount > 0) {
                defer(function () use ($pushedCount, $desiredOrder) {
                    Notification::make()
                        ->title('Posições ajustadas')
                        ->body("{$pushedCount} produto(s) foram empurrados para baixo para liberar a posição {$desiredOrder}.")
                        ->success()
                        ->send();
                });
            }
        }
    }

    /**
     * Handle the Product "saved" event.
     */
    public function saved(Product $product): void
    {
        // Otimizar imagem principal
        if ($product->isDirty('image') && $product->image) {
            $this->optimizeImage($product->image);
        }

        // Otimizar galeria de imagens
        if ($product->isDirty('images') && is_array($product->images)) {
            foreach ($product->images as $imagePath) {
                $this->optimizeImage($imagePath);
            }
        }
    }

    /**
     * Otimizar imagem: redimensionar e comprimir
     */
    protected function optimizeImage(string $path): void
    {
        try {
            $fullPath = storage_path('app/public/' . $path);

            // Verificar se arquivo existe
            if (!file_exists($fullPath)) {
                return;
            }

            // Carregar imagem
            $image = Image::read($fullPath);

            // Redimensionar se for maior que 800x800 (mantém proporção)
            if ($image->width() > 800 || $image->height() > 800) {
                $image->scale(width: 800, height: 800);
            }

            // Salvar otimizada (qualidade 80%)
            $image->save($fullPath, quality: 80);

            \Log::info('Imagem otimizada', [
                'path' => $path,
                'size_before' => filesize($fullPath) / 1024 . 'KB',
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao otimizar imagem', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
