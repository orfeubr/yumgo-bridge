<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminerController extends Controller
{
    public function index(Request $request)
    {
        // Verificar se está autenticado
        if (!auth()->guard('platform')->check()) {
            return redirect('/admin/login');
        }

        // Auto-login no PostgreSQL
        $server = config('database.connections.pgsql.host');
        $username = config('database.connections.pgsql.username');
        $database = config('database.connections.pgsql.database');

        // Redirecionar para Adminer com parâmetros
        $url = '/adminer/index.php?' . http_build_query([
            'pgsql' => $server,
            'username' => $username,
            'db' => $database,
        ]);

        return redirect($url);
    }
}
