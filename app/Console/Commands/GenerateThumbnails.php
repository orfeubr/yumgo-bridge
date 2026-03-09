<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class GenerateThumbnails extends Command
{
    protected $signature = 'products:generate-thumbnails {tenant-id}';
    protected $description = 'Gera thumbnails para todos os produtos de um tenant';

    public function handle()
    {
        $tenantId = $this->argument('tenant-id');
        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            $this->error("Tenant {$tenantId} não encontrado!");
            return 1;
        }

        tenancy()->initialize($tenant);

        $this->info("Gerando thumbnails para: {$tenant->name}");
        $this->info('');

        $products = Product::whereNotNull('image')->get();
        $bar = $this->output->createProgressBar($products->count());

        $gerados = 0;
        $erros = 0;

        foreach ($products as $product) {
            try {
                // Pula se já tem thumbnail
                if ($product->thumbnail && Storage::disk('public')->exists($product->thumbnail)) {
                    $bar->advance();
                    continue;
                }

                // Verifica se imagem original existe
                if (!Storage::disk('public')->exists($product->image)) {
                    $this->newLine();
                    $this->warn("⚠️  {$product->name} - imagem original não encontrada");
                    $erros++;
                    $bar->advance();
                    continue;
                }

                // Gera thumbnail
                $imagePath = Storage::disk('public')->path($product->image);
                $pathInfo = pathinfo($product->image);
                $thumbnailPath = $pathInfo['dirname'] . '/thumbs/' . $pathInfo['basename'];

                // Cria diretório se não existir
                $thumbDir = dirname(Storage::disk('public')->path($thumbnailPath));
                if (!is_dir($thumbDir)) {
                    mkdir($thumbDir, 0755, true);
                }

                // Redimensiona
                $image = Image::read($imagePath);
                $image->cover(400, 400);

                // Salva
                Storage::disk('public')->put($thumbnailPath, $image->encode());

                // Atualiza produto
                $product->update(['thumbnail' => $thumbnailPath]);

                $gerados++;
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("❌ {$product->name} - Erro: " . $e->getMessage());
                $erros++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Thumbnails gerados: {$gerados}");
        if ($erros > 0) {
            $this->warn("⚠️  Erros: {$erros}");
        }

        return 0;
    }
}
