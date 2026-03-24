# Script para verificar configurações da impressora POS-58-Termica
# Execute com: powershell -ExecutionPolicy Bypass -File verificar-impressora.ps1

Write-Host "==================================================" -ForegroundColor Cyan
Write-Host "   VERIFICAÇÃO DE IMPRESSORA POS-58-Termica" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host ""

# Listar todas as impressoras
Write-Host "📋 Impressoras instaladas:" -ForegroundColor Yellow
Get-Printer | Select-Object Name, DriverName, PortName, PrinterStatus | Format-Table -AutoSize
Write-Host ""

# Detalhes da POS-58
$printerName = "POS-58-Termica"
Write-Host "🔍 Detalhes da impressora: $printerName" -ForegroundColor Yellow

try {
    $printer = Get-Printer -Name $printerName -ErrorAction Stop

    Write-Host "  Nome: $($printer.Name)" -ForegroundColor Green
    Write-Host "  Driver: $($printer.DriverName)" -ForegroundColor Green
    Write-Host "  Porta: $($printer.PortName)" -ForegroundColor Green
    Write-Host "  Status: $($printer.PrinterStatus)" -ForegroundColor Green
    Write-Host "  Compartilhada: $($printer.Shared)" -ForegroundColor Green
    Write-Host ""

    # Verificar configurações do driver
    Write-Host "⚙️  Configurações do Driver:" -ForegroundColor Yellow
    $printerConfig = Get-PrintConfiguration -PrinterName $printerName
    Write-Host "  Papel Padrão: $($printerConfig.PaperSize)" -ForegroundColor Green
    Write-Host "  Duplexação: $($printerConfig.DuplexingMode)" -ForegroundColor Green
    Write-Host "  Cor: $($printerConfig.Color)" -ForegroundColor Green
    Write-Host ""

    # Listar tamanhos de papel suportados
    Write-Host "📏 Tamanhos de papel disponíveis:" -ForegroundColor Yellow
    $driver = Get-PrinterDriver -Name $printer.DriverName

    # Tentar obter formulários disponíveis
    $forms = Get-PrinterProperty -PrinterName $printerName -ErrorAction SilentlyContinue
    if ($forms) {
        $forms | Format-Table -AutoSize
    } else {
        Write-Host "  (Não foi possível listar - use 'printmanagement.msc' para ver)" -ForegroundColor Gray
    }

} catch {
    Write-Host "❌ ERRO: Impressora '$printerName' não encontrada!" -ForegroundColor Red
    Write-Host "   Verifique se o nome está correto." -ForegroundColor Red
    Write-Host ""
    Write-Host "💡 Impressoras disponíveis:" -ForegroundColor Yellow
    Get-Printer | Select-Object Name | Format-Table
}

Write-Host ""
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host "   TESTE DE IMPRESSÃO" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Para testar a largura, crie um arquivo teste.txt com 48 caracteres:" -ForegroundColor Yellow
Write-Host "================================================" -ForegroundColor White
Write-Host "** TESTE DE LARGURA - 48 CARACTERES **" -ForegroundColor White
Write-Host "================================================" -ForegroundColor White
Write-Host ""
Write-Host "E imprima com:" -ForegroundColor Yellow
Write-Host 'Out-Printer -Name "POS-58-Termica" -InputObject (Get-Content teste.txt -Raw)' -ForegroundColor Cyan
Write-Host ""
