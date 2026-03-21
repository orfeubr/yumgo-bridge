<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Waiter;
use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TableOrderController extends Controller
{
    /**
     * Acesso via QR Code da mesa
     */
    public function tableAccess(string $token)
    {
        $table = Table::where('qr_token', $token)
            ->where('is_active', true)
            ->firstOrFail();

        // Salvar na sessão
        Session::put('order_source', 'table');
        Session::put('table_id', $table->id);
        Session::put('table_number', $table->number);

        // Atualizar status da mesa
        if ($table->status === 'available') {
            $table->update(['status' => 'occupied']);
        }

        // Redirecionar para seleção de garçom
        return view('tenant.table-waiter-select', [
            'table' => $table,
            'waiters' => Waiter::where('is_active', true)->get(),
        ]);
    }

    /**
     * Seleciona garçom e vai para o cardápio
     */
    public function selectWaiter(Request $request)
    {
        $request->validate([
            'waiter_id' => 'required|exists:waiters,id',
        ]);

        Session::put('waiter_id', $request->waiter_id);

        // Redirecionar para cardápio
        return redirect('/');
    }

    /**
     * Acesso via QR Code do balcão
     */
    public function counterAccess()
    {
        Session::put('order_source', 'counter');
        Session::forget(['table_id', 'waiter_id']);

        // Redirecionar direto para cardápio
        return redirect('/');
    }

    /**
     * Gera QR Code de uma mesa (para o painel admin)
     */
    public function generateTableQR(Table $table)
    {
        $qr = QrCode::size(400)
            ->margin(2)
            ->generate($table->qr_url);

        return view('restaurant.table-qr-code', [
            'table' => $table,
            'qr' => $qr,
        ]);
    }

    /**
     * Gera QR Code do balcão (link fixo)
     */
    public function generateCounterQR()
    {
        $url = url('/balcao');

        $qr = QrCode::size(400)
            ->margin(2)
            ->generate($url);

        return view('restaurant.counter-qr-code', [
            'url' => $url,
            'qr' => $qr,
        ]);
    }
}
