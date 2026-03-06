# Script de Instalacao - ESC/POS Virtual Printer Emulator
# Para: elize
# Data: 06/03/2026

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  ESC/POS Virtual Printer Emulator" -ForegroundColor Cyan
Write-Host "  Instalacao Automatica" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# 1. Criar pasta de instalacao
$installDir = "C:\Users\elize\YumGo\PrinterEmulator"
Write-Host "[1/4] Criando pasta de instalacao..." -ForegroundColor Yellow
if (-not (Test-Path $installDir)) {
    New-Item -ItemType Directory -Path $installDir -Force | Out-Null
    Write-Host "[OK] Pasta criada: $installDir" -ForegroundColor Green
} else {
    Write-Host "[OK] Pasta ja existe: $installDir" -ForegroundColor Green
}

# 2. Baixar executável da release
Write-Host "`n[2/4] Baixando emulador do GitHub..." -ForegroundColor Yellow
$downloadUrl = "https://github.com/Garletz/escpos-virtual-printer-emulator/releases/download/windobe/escpos_emulator.exe"
$exePath = "$installDir\escpos_emulator.exe"

try {
    Invoke-WebRequest -Uri $downloadUrl -OutFile $exePath -UseBasicParsing
    Write-Host "[OK] Download concluído!" -ForegroundColor Green
} catch {
    Write-Host "[ERRO] Erro ao baixar: $_" -ForegroundColor Red
    Write-Host "`nTente baixar manualmente de:" -ForegroundColor Yellow
    Write-Host "https://github.com/Garletz/escpos-virtual-printer-emulator/releases" -ForegroundColor Cyan
    pause
    exit
}

# 3. Criar atalho na área de trabalho
Write-Host "`n[3/4] Criando atalho na área de trabalho..." -ForegroundColor Yellow
$desktopPath = [Environment]::GetFolderPath("Desktop")
$shortcutPath = "$desktopPath\Impressora Virtual ESC-POS.lnk"

$WScriptShell = New-Object -ComObject WScript.Shell
$shortcut = $WScriptShell.CreateShortcut($shortcutPath)
$shortcut.TargetPath = $exePath
$shortcut.WorkingDirectory = $installDir
$shortcut.Description = "Emulador de Impressora Térmica ESC/POS"
$shortcut.IconLocation = "$exePath,0"
$shortcut.Save()

Write-Host "[OK] Atalho criado na área de trabalho!" -ForegroundColor Green

# 4. Executar o emulador
Write-Host "`n[4/4] Iniciando emulador..." -ForegroundColor Yellow
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  IMPORTANTE - PRÓXIMOS PASSOS:" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "1. O emulador vai abrir em uma janela" -ForegroundColor White
Write-Host "2. Clique na aba 'Settings'" -ForegroundColor White
Write-Host "3. Clique em '[*] Install Windows Printer'" -ForegroundColor White
Write-Host "4. Aceite a solicitação de administrador" -ForegroundColor White
Write-Host "5. A impressora 'ESC/POS Emulator' será instalada!" -ForegroundColor White
Write-Host ""
Write-Host "Depois disso, ela vai aparecer no YumGo Bridge! [OK]" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

Start-Process -FilePath $exePath -WorkingDirectory $installDir

Write-Host "`n[OK] INSTALAÇÃO CONCLUÍDA!" -ForegroundColor Green
Write-Host "Arquivo instalado em: $installDir" -ForegroundColor Cyan
Write-Host ""
pause
