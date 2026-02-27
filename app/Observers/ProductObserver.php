<?php

namespace App\Observers;

use App\Models\Product;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\Storage;

class ProductObserver
{
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
