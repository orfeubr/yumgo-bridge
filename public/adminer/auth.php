<?php
// Verificar se está autenticado no Laravel
session_start();

// Verificar se tem sessão do Laravel
$sessionName = 'laravel_session';
if (!isset($_COOKIE[$sessionName])) {
    http_response_code(401);
    die('Não autorizado. Faça login no painel admin primeiro.');
}

// Permitir acesso ao Adminer
header('Location: /adminer/index.php');
exit;
