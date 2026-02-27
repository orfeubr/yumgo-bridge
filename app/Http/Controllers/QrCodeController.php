<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeController extends Controller
{
    /**
     * Exibir QR Code do cardápio
     */
    public function show()
    {
        $tenant = tenant();
        $url = 'https://' . request()->getHost();

        return view('tenant.qrcode', compact('tenant', 'url'));
    }

    /**
     * Baixar QR Code em PNG
     */
    public function download()
    {
        $tenant = tenant();
        $url = 'https://' . request()->getHost();

        $qrCode = QrCode::format('png')
            ->size(500)
            ->margin(2)
            ->errorCorrection('H')
            ->generate($url);

        return response($qrCode)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="qrcode-' . $tenant->slug . '.png"');
    }

    /**
     * Gerar PDF para imprimir
     */
    public function pdf()
    {
        $tenant = tenant();
        $url = 'https://' . request()->getHost();

        return view('tenant.qrcode-print', compact('tenant', 'url'));
    }
}
