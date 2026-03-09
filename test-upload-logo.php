<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Criar uma imagem de teste
$image = imagecreatetruecolor(400, 100);
$bgColor = imagecolorallocate($image, 234, 29, 44); // #EA1D2C
$textColor = imagecolorallocate($image, 255, 255, 255);
imagefill($image, 0, 0, $bgColor);
imagestring($image, 5, 150, 40, 'YumGo', $textColor);

// Salvar no storage temporário
$tmpPath = storage_path('app/public/branding/test-logo.png');
imagepng($image, $tmpPath);
imagedestroy($image);

echo "✅ Imagem criada: $tmpPath\n";
echo "   Tamanho: " . filesize($tmpPath) . " bytes\n";
echo "   Existe: " . (file_exists($tmpPath) ? 'SIM' : 'NÃO') . "\n\n";

// Testar se consegue copiar para public
$publicPath = public_path('logo.png');
copy($tmpPath, $publicPath);
chmod($publicPath, 0644);

echo "✅ Logo copiada para: $publicPath\n";
echo "   Tamanho: " . filesize($publicPath) . " bytes\n";
echo "   Existe: " . (file_exists($publicPath) ? 'SIM' : 'NÃO') . "\n\n";

// Salvar no banco
App\Models\PlatformSetting::set('logo', 'branding/test-logo.png');

echo "✅ Path salvo no banco\n";
echo "   Valor: " . App\Models\PlatformSetting::get('logo') . "\n\n";

echo "🎉 Teste completo! Acesse https://yumgo.com.br/ para ver o logo.\n";
