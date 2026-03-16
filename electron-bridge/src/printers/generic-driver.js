const { exec } = require('child_process');
const log = require('electron-log');
const path = require('path');

/**
 * Instala driver Generic/Text Only automaticamente
 * Resolve problemas com drivers bugados (POS-58, etc)
 */
async function installGenericDriver(printerName = 'POS58-Generic', portName = 'USB001') {
    return new Promise((resolve, reject) => {
        log.info(`🔧 Instalando driver Generic/Text Only para ${printerName}...`);

        // Caminho do script PowerShell
        const scriptPath = path.join(__dirname, '../../scripts/install-generic-driver.ps1');

        // Comando PowerShell
        const psCmd = `powershell.exe -ExecutionPolicy Bypass -File "${scriptPath}" -PrinterName "${printerName}" -PortName "${portName}"`;

        exec(psCmd, (error, stdout, stderr) => {
            if (error) {
                log.error(`❌ Erro ao instalar driver genérico: ${error.message}`);
                log.error(stderr);
                reject(error);
                return;
            }

            log.info(stdout);
            log.info(`✅ Driver genérico instalado: ${printerName}`);
            resolve({
                printerName,
                portName,
                driver: 'Generic / Text Only'
            });
        });
    });
}

/**
 * Verifica se impressora genérica já existe
 */
async function hasGenericPrinter(printerName = 'POS58-Generic') {
    return new Promise((resolve) => {
        const psCmd = `powershell -Command "Get-Printer -Name '${printerName}' -ErrorAction SilentlyContinue | Select-Object Name"`;

        exec(psCmd, (error, stdout) => {
            if (error || !stdout.trim()) {
                resolve(false);
                return;
            }
            resolve(true);
        });
    });
}

/**
 * Fallback automático: instala driver genérico se impressão falhar
 */
async function autoInstallGenericFallback(originalPrinterPort = 'USB001') {
    log.info('🔧 Tentando fallback com driver Generic/Text Only...');

    try {
        // Verifica se já existe
        const exists = await hasGenericPrinter('POS58-Generic');

        if (exists) {
            log.info('✅ Driver genérico já instalado!');
            return 'POS58-Generic';
        }

        // Instala
        await installGenericDriver('POS58-Generic', originalPrinterPort);
        return 'POS58-Generic';

    } catch (error) {
        log.error(`❌ Fallback genérico falhou: ${error.message}`);
        throw error;
    }
}

module.exports = {
    installGenericDriver,
    hasGenericPrinter,
    autoInstallGenericFallback
};
