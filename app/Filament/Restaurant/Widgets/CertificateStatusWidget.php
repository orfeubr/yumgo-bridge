<?php

namespace App\Filament\Restaurant\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class CertificateStatusWidget extends Widget
{
    protected static string $view = 'filament.restaurant.widgets.certificate-status-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public function getViewData(): array
    {
        $tenant = tenant();

        $hasCertificate = !empty($tenant->certificate_a1);
        $hasCsc = !empty($tenant->csc_id) && !empty($tenant->csc_token);
        $hasCompanyData = !empty($tenant->cnpj) && !empty($tenant->razao_social);

        // Status geral
        $isFullyConfigured = $hasCertificate && $hasCsc && $hasCompanyData;

        // Verificar validade do certificado (se tiver)
        $certificateStatus = 'unknown';
        $daysUntilExpiry = null;

        if ($hasCertificate) {
            try {
                $certContent = base64_decode($tenant->certificate_a1);
                $certData = [];

                if (openssl_pkcs12_read($certContent, $certData, $tenant->certificate_password ?? '')) {
                    $certInfo = openssl_x509_parse($certData['cert']);

                    if (isset($certInfo['validTo_time_t'])) {
                        $expiryDate = Carbon::createFromTimestamp($certInfo['validTo_time_t']);
                        $now = Carbon::now();

                        $daysUntilExpiry = $now->diffInDays($expiryDate, false);

                        if ($daysUntilExpiry < 0) {
                            $certificateStatus = 'expired';
                        } elseif ($daysUntilExpiry <= 30) {
                            $certificateStatus = 'expiring_soon';
                        } else {
                            $certificateStatus = 'valid';
                        }
                    }
                }
            } catch (\Exception $e) {
                $certificateStatus = 'error';
            }
        }

        // Ambiente atual
        $environment = $tenant->nfce_environment ?? 'homologacao';

        return [
            'isFullyConfigured' => $isFullyConfigured,
            'hasCertificate' => $hasCertificate,
            'hasCsc' => $hasCsc,
            'hasCompanyData' => $hasCompanyData,
            'certificateStatus' => $certificateStatus,
            'daysUntilExpiry' => $daysUntilExpiry,
            'environment' => $environment,
        ];
    }
}
