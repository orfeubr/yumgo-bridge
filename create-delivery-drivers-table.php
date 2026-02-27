<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Inicializar tenant
$tenant = \Stancl\Tenancy\Database\Models\Tenant::find('144c5973-f985-4309-8f9a-c404dd11feae');
tenancy()->initialize($tenant);

// Criar tabela
DB::statement("
CREATE TABLE IF NOT EXISTS delivery_drivers (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(255) UNIQUE NOT NULL,
    email VARCHAR(255),
    cpf VARCHAR(255),
    vehicle_type VARCHAR(255),
    vehicle_plate VARCHAR(255),
    photo VARCHAR(255),
    is_active BOOLEAN DEFAULT true,
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
)
");

echo "✅ Tabela delivery_drivers criada com sucesso!\n";
