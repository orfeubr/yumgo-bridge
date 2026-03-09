<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        if ($product->image && !$product->thumbnail) {
            $this->generateThumbnail($product);
        }
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        // Se imagem foi alterada, regerar thumbnail
        if ($product->isDirty('image') && $product->image) {
            $this->generateThumbnail($product);
        }
    }

    /**
     * Gera thumbnail para o produto
     */
    protected function generateThumbnail(Product $product): void
    {
        try {
            // Verifica se imagem original existe
            if (!Storage::disk('public')->exists($product->image)) {
                return;
            }

            $imagePath = Storage::disk('public')->path($product->image);
            $pathInfo = pathinfo($product->image);

            // Caminho do thumbnail
            $thumbnailPath = $pathInfo['dirname'] . '/thumbs/' . $pathInfo['basename'];

            // Cria diretório se não existir
            $thumbDir = dirname(Storage::disk('public')->path($thumbnailPath));
            if (!is_dir($thumbDir)) {
                mkdir($thumbDir, 0755, true);
            }

            // Gera thumbnail (400x400)
            $image = Image::read($imagePath);
            $image->cover(400, 400);

            // Salva
            Storage::disk('public')->put($thumbnailPath, $image->encode());

            // Atualiza produto SEM disparar eventos novamente
            $product->updateQuietly(['thumbnail' => $thumbnailPath]);

        } catch (\Exception $e) {
            \Log::error('Erro ao gerar thumbnail', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
