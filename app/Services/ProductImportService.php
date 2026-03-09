<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\ProductAddon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProductImportService
{
    protected array $report = [
        'success' => 0,
        'errors' => [],
        'warnings' => [],
        'categories_created' => 0,
        'products_created' => 0,
        'variations_created' => 0,
        'addons_created' => 0,
    ];

    protected string $tenantId;

    public function __construct(string $tenantId)
    {
        $this->tenantId = $tenantId;
    }

    /**
     * Importa produtos de um arquivo Excel/CSV
     */
    public function import(string $filePath): array
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Remove header
            $header = array_shift($rows);

            // Valida header
            $this->validateHeader($header);

            // Processa cada linha
            foreach ($rows as $index => $row) {
                $lineNumber = $index + 2; // +2 porque tiramos header e Excel começa em 1

                // Pula linhas vazias
                if (empty(array_filter($row))) {
                    continue;
                }

                try {
                    $this->importProduct($row, $lineNumber);
                    $this->report['success']++;
                } catch (\Exception $e) {
                    $this->report['errors'][] = "Linha {$lineNumber}: {$e->getMessage()}";
                }
            }

            return $this->report;
        } catch (\Exception $e) {
            throw new \Exception("Erro ao ler arquivo: {$e->getMessage()}");
        }
    }

    /**
     * Valida se o header está correto
     */
    protected function validateHeader(array $header): void
    {
        $requiredColumns = ['categoria', 'nome', 'preco'];
        $headerLower = array_map('strtolower', array_map('trim', $header));

        foreach ($requiredColumns as $required) {
            if (!in_array($required, $headerLower)) {
                throw new \Exception("Coluna obrigatória '{$required}' não encontrada no arquivo.");
            }
        }
    }

    /**
     * Importa um produto individual
     */
    protected function importProduct(array $row, int $lineNumber): void
    {
        // Mapeia colunas (ajustar conforme template)
        $data = [
            'categoria' => trim($row[0] ?? ''),
            'nome' => trim($row[1] ?? ''),
            'descricao' => trim($row[2] ?? ''),
            'preco' => $this->parsePrice($row[3] ?? 0),
            'variacoes' => trim($row[4] ?? ''),
            'adicionais' => trim($row[5] ?? ''),
            'foto_url' => trim($row[6] ?? ''),
            'ativo' => strtolower(trim($row[7] ?? 'sim')) === 'sim',
        ];

        // Validações
        if (empty($data['categoria'])) {
            throw new \Exception("Categoria é obrigatória");
        }

        if (empty($data['nome'])) {
            throw new \Exception("Nome do produto é obrigatório");
        }

        if ($data['preco'] <= 0) {
            throw new \Exception("Preço deve ser maior que zero");
        }

        // Busca ou cria categoria
        $category = $this->getOrCreateCategory($data['categoria']);

        // Gera slug único
        $slug = Str::slug($data['nome']);
        $originalSlug = $slug;
        $counter = 1;

        while (Product::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Cria produto
        $product = Product::create([
            'category_id' => $category->id,
            'name' => $data['nome'],
            'description' => $data['descricao'],
            'price' => $data['preco'],
            'is_active' => $data['ativo'],
            'slug' => $slug,
        ]);

        $this->report['products_created']++;

        // Download e salva foto
        if (!empty($data['foto_url'])) {
            try {
                $this->downloadAndSaveImage($product, $data['foto_url']);
            } catch (\Exception $e) {
                $this->report['warnings'][] = "Linha {$lineNumber}: Erro ao baixar foto - {$e->getMessage()}";
            }
        }

        // Cria variações (ex: "P:30.00,M:35.00,G:45.00")
        if (!empty($data['variacoes'])) {
            $this->createVariations($product, $data['variacoes'], $lineNumber);
        }

        // Cria adicionais (ex: "Borda:5.00,Catupiry:3.00")
        if (!empty($data['adicionais'])) {
            $this->createAddons($product, $data['adicionais'], $lineNumber);
        }
    }

    /**
     * Busca ou cria uma categoria
     */
    protected function getOrCreateCategory(string $name): Category
    {
        $category = Category::where('name', $name)->first();

        if (!$category) {
            $category = Category::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'is_active' => true,
            ]);

            $this->report['categories_created']++;
        }

        return $category;
    }

    /**
     * Download e salva imagem do produto
     */
    protected function downloadAndSaveImage(Product $product, string $url): void
    {
        // Valida URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \Exception("URL inválida: {$url}");
        }

        // Download da imagem
        $response = Http::timeout(30)->get($url);

        if (!$response->successful()) {
            throw new \Exception("Erro ao baixar imagem (HTTP {$response->status()})");
        }

        $imageContent = $response->body();
        $extension = $this->getImageExtension($response->header('Content-Type'));

        // Nome do arquivo
        $filename = Str::slug($product->name) . '-' . time() . '.' . $extension;
        $path = "products/{$this->tenantId}/{$filename}";

        // Salva imagem original
        Storage::disk('public')->put($path, $imageContent);

        // Gera thumbnail (400x400)
        $thumbnailPath = "products/{$this->tenantId}/thumbs/{$filename}";
        $this->createThumbnail(Storage::disk('public')->path($path), $thumbnailPath);

        // Atualiza produto
        $product->update([
            'image' => $path,
            'thumbnail' => $thumbnailPath,
        ]);
    }

    /**
     * Cria thumbnail da imagem
     */
    protected function createThumbnail(string $originalPath, string $thumbnailPath): void
    {
        $image = Image::read($originalPath);

        // Resize mantendo proporção (fit cover)
        $image->cover(400, 400);

        // Salva
        Storage::disk('public')->put(
            $thumbnailPath,
            $image->encode()
        );
    }

    /**
     * Detecta extensão da imagem pelo Content-Type
     */
    protected function getImageExtension(?string $contentType): string
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];

        return $map[$contentType] ?? 'jpg';
    }

    /**
     * Cria variações do produto
     * Formato: "P:30.00,M:35.00,G:45.00"
     */
    protected function createVariations(Product $product, string $variationsStr, int $lineNumber): void
    {
        $variations = explode(',', $variationsStr);

        foreach ($variations as $variation) {
            $parts = explode(':', trim($variation));

            if (count($parts) !== 2) {
                $this->report['warnings'][] = "Linha {$lineNumber}: Formato inválido de variação '{$variation}'";
                continue;
            }

            $name = trim($parts[0]);
            $price = $this->parsePrice($parts[1]);

            if (empty($name) || $price <= 0) {
                $this->report['warnings'][] = "Linha {$lineNumber}: Variação inválida '{$variation}'";
                continue;
            }

            ProductVariation::create([
                'product_id' => $product->id,
                'name' => $name,
                'price' => $price,
                'is_active' => true,
            ]);

            $this->report['variations_created']++;
        }
    }

    /**
     * Cria adicionais do produto
     * Formato: "Borda:5.00,Catupiry:3.00"
     */
    protected function createAddons(Product $product, string $addonsStr, int $lineNumber): void
    {
        $addons = explode(',', $addonsStr);

        foreach ($addons as $addon) {
            $parts = explode(':', trim($addon));

            if (count($parts) !== 2) {
                $this->report['warnings'][] = "Linha {$lineNumber}: Formato inválido de adicional '{$addon}'";
                continue;
            }

            $name = trim($parts[0]);
            $price = $this->parsePrice($parts[1]);

            if (empty($name) || $price < 0) {
                $this->report['warnings'][] = "Linha {$lineNumber}: Adicional inválido '{$addon}'";
                continue;
            }

            ProductAddon::create([
                'product_id' => $product->id,
                'name' => $name,
                'price' => $price,
                'is_active' => true,
            ]);

            $this->report['addons_created']++;
        }
    }

    /**
     * Converte preço de string para float
     */
    protected function parsePrice($value): float
    {
        // Remove espaços
        $value = trim($value);

        // Remove R$, pontos de milhar
        $value = str_replace(['R$', '.'], '', $value);

        // Troca vírgula por ponto
        $value = str_replace(',', '.', $value);

        return (float) $value;
    }

    /**
     * Retorna o relatório de importação
     */
    public function getReport(): array
    {
        return $this->report;
    }
}
