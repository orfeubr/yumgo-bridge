# Instala driver Generic/Text Only para impressora POS58
# Isso resolve 99% dos problemas de driver bugado

param(
    [string]$PrinterName = "POS58-Generic",
    [string]$PortName = "USB001"
)

Write-Host "Instalando driver Generic/Text Only para $PrinterName..."

# Caminho correto do .inf (PowerShell precisa de $env:)
$infPath = "$env:windir\inf\ntprint.inf"

# Verifica se arquivo existe
if (-not (Test-Path $infPath)) {
    Write-Host "❌ Arquivo ntprint.inf não encontrado: $infPath"
    exit 1
}

Write-Host "Usando arquivo: $infPath"

# Comando para instalar impressora com driver genérico
try {
    $proc = Start-Process -FilePath "rundll32.exe" -ArgumentList "printui.dll,PrintUIEntry /if /b `"$PrinterName`" /f `"$infPath`" /r `"$PortName`" /m `"Generic / Text Only`"" -Wait -PassThru -WindowStyle Hidden

    if ($proc.ExitCode -eq 0) {
        Write-Host "✅ Driver genérico instalado: $PrinterName"
        Write-Host "   Porta: $PortName"
        Write-Host "   Driver: Generic / Text Only"
        exit 0
    } else {
        Write-Host "❌ Erro ao instalar driver. Exit code: $($proc.ExitCode)"
        exit 1
    }
} catch {
    Write-Host "❌ Erro ao instalar driver: $_"
    exit 1
}
