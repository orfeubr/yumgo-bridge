<?php
// Limpa OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache limpo com sucesso!\n";
} else {
    echo "⚠️ OPcache não está habilitado\n";
}

// Remove o arquivo após execução
@unlink(__FILE__);
