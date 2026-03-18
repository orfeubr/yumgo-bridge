<?php

$file = '/var/www/restaurante/resources/views/marketplace/index.blade.php';
$content = file_get_contents($file);

// Padrão antigo (com quebras de linha)
$oldPattern = <<<'PATTERN'
                                <div class="relative h-40 overflow-hidden bg-gray-100">
                                    @if($restaurant->logo)
                                        <img src="{{ $restaurant->logo_url }}" alt="{{ $restaurant->name }}"
                                             class="w-full h-full object-cover"
                                             onerror="this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&h=400&fit=crop';">
                                    @else
                                        <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&h=400&fit=crop"
                                             alt="Food" class="w-full h-full object-cover">
                                    @endif
PATTERN;

// Novo componente (início)
$newPattern = <<<'PATTERN'
                                <x-restaurant-avatar :restaurant="$restaurant" size="md">
PATTERN;

// Substituir
$content = str_replace($oldPattern, $newPattern, $content);

// Substituir fechamentos </div> antigos por </x-restaurant-avatar>
// Procurar padrões específicos de fechamento
$content = preg_replace(
    '/(@endif\s*<\/div>\s*<!-- Content -->)/',
    '@endif</x-restaurant-avatar>$1<!-- Content -->',
    $content
);

// Salvar
file_put_contents($file, $content);

echo "✅ Arquivo atualizado!\n";
echo "Substituições feitas: " . substr_count($content, '<x-restaurant-avatar') . " componentes\n";
