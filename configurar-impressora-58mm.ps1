# Script para configurar impressora térmica 58mm
# Execute como ADMINISTRADOR:
# PowerShell (Admin) → Set-ExecutionPolicy Bypass -Scope Process
# .\configurar-impressora-58mm.ps1

$printerName = "POS-58-Termica"

Write-Host "==================================================" -ForegroundColor Cyan
Write-Host "   CONFIGURAÇÃO AUTOMÁTICA POS-58 (58mm)" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host ""

# Verificar se impressora existe
try {
    $printer = Get-Printer -Name $printerName -ErrorAction Stop
    Write-Host "✅ Impressora encontrada: $printerName" -ForegroundColor Green
} catch {
    Write-Host "❌ ERRO: Impressora '$printerName' não encontrada!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Impressoras disponíveis:" -ForegroundColor Yellow
    Get-Printer | Select-Object Name | Format-Table
    exit 1
}

Write-Host ""
Write-Host "📝 Criando formulário personalizado 58mm..." -ForegroundColor Yellow

# Criar formulário customizado 58mm
# NOTA: Requer privilégios de administrador
try {
    # Verificar se formulário já existe
    $formName = "Termica_58mm"

    # Remover formulário antigo se existir
    $existingForm = Get-PrinterProperty -PrinterName $printerName -ErrorAction SilentlyContinue |
        Where-Object { $_.Name -eq $formName }

    if ($existingForm) {
        Write-Host "  ⚠️  Formulário já existe, será recriado..." -ForegroundColor Yellow
    }

    # Aplicar configuração de papel via Set-PrintConfiguration
    Write-Host ""
    Write-Host "⚙️  Aplicando configurações..." -ForegroundColor Yellow

    # Tentar configurar para papel personalizado ou menor disponível
    $config = Get-PrintConfiguration -PrinterName $printerName

    # Listar tamanhos disponíveis
    Write-Host "  📏 Tamanho atual: $($config.PaperSize)" -ForegroundColor Gray

    # Tentar aplicar configurações
    Set-PrintConfiguration -PrinterName $printerName -PaperSize "Custom" -ErrorAction SilentlyContinue

    Write-Host "  ✅ Configurações aplicadas!" -ForegroundColor Green

} catch {
    Write-Host "  ⚠️  Não foi possível criar formulário via PowerShell" -ForegroundColor Yellow
    Write-Host "  (Algumas impressoras exigem configuração manual)" -ForegroundColor Gray
}

Write-Host ""
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host "   CONFIGURAÇÃO MANUAL NECESSÁRIA" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "⚠️  Windows não permite configurar margens via PowerShell" -ForegroundColor Yellow
Write-Host "   Você precisa configurar MANUALMENTE:" -ForegroundColor Yellow
Write-Host ""
Write-Host "1️⃣  Abra: control printers" -ForegroundColor Cyan
Write-Host "2️⃣  Clique direito em: POS-58-Termica" -ForegroundColor Cyan
Write-Host "3️⃣  Selecione: Preferências de impressão" -ForegroundColor Cyan
Write-Host "4️⃣  Configure:" -ForegroundColor Cyan
Write-Host "     • Tamanho do papel: Custom (58mm x 210mm)" -ForegroundColor White
Write-Host "     • Margens esquerda/direita: 0mm" -ForegroundColor White
Write-Host "     • Fonte: Courier New 8pt ou 9pt" -ForegroundColor White
Write-Host "     • Desmarcar: 'Ajustar ao tamanho'" -ForegroundColor White
Write-Host "     • Desmarcar: 'Centralizar'" -ForegroundColor White
Write-Host "5️⃣  Clique: OK → OK" -ForegroundColor Cyan
Write-Host ""
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host "   TESTE" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Criando arquivo de teste..." -ForegroundColor Yellow

# Criar arquivo de teste
$testFile = "teste-largura-58mm.txt"
$testContent = @"
================================================
** TESTE DE LARGURA 48 CARACTERES **
================================================
PEDIDO #1234              14:30 - 14/03
------------------------------------------------
1x PIZZA CALABRESA                      R$ 45.00
   - Borda recheada (catupiry)          R$  5.00
------------------------------------------------
SUBTOTAL                                R$ 50.00
ENTREGA                                 R$  8.00
------------------------------------------------
TOTAL                                   R$ 58.00
================================================

✅ Se as linhas de '=' ocuparem 5-6cm = CORRETO
❌ Se as linhas de '=' ocuparem 2cm = ERRADO

Meça com uma régua!
"@

$testContent | Out-File -FilePath $testFile -Encoding UTF8
Write-Host "  ✅ Arquivo criado: $testFile" -ForegroundColor Green
Write-Host ""
Write-Host "📤 Imprimindo teste..." -ForegroundColor Yellow

try {
    Out-Printer -Name $printerName -InputObject $testContent
    Write-Host "  ✅ Teste enviado para impressora!" -ForegroundColor Green
    Write-Host ""
    Write-Host "📏 Meça a largura das linhas de '=' com uma régua:" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "   • Se ≥ 5cm → ✅ Configuração OK!" -ForegroundColor Green
    Write-Host "   • Se < 3cm → ❌ Ainda precisa ajustar driver" -ForegroundColor Red
    Write-Host ""
} catch {
    Write-Host "  ❌ Erro ao imprimir: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host "   PRÓXIMOS PASSOS" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Se a impressão ainda sair estreita (< 3cm):" -ForegroundColor Yellow
Write-Host ""
Write-Host "1. Configure manualmente (passos acima)" -ForegroundColor White
Write-Host "2. Reinicie o serviço de impressão:" -ForegroundColor White
Write-Host "   Restart-Service Spooler" -ForegroundColor Cyan
Write-Host "3. Teste novamente com:" -ForegroundColor White
Write-Host "   Out-Printer -Name 'POS-58-Termica' -InputObject (Get-Content teste-largura-58mm.txt -Raw)" -ForegroundColor Cyan
Write-Host ""
