<x-filament-panels::page.simple>
    <style>
        body {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%) !important;
        }

        .fi-simple-layout {
            background: transparent !important;
        }

        .fi-simple-main {
            padding: 2rem;
        }

        .fi-simple-page {
            background: white !important;
            border-radius: 1.5rem !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
            padding: 3rem !important;
            max-width: 28rem;
            margin: 0 auto;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .login-emoji {
            font-size: 4rem;
            margin-bottom: 1rem;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .login-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .fi-btn-primary {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%) !important;
            border: none !important;
            border-radius: 0.75rem !important;
            font-weight: 600 !important;
            padding: 0.875rem 1.5rem !important;
            transition: all 0.3s ease !important;
            width: 100%;
        }

        .fi-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255, 107, 53, 0.4) !important;
        }

        .fi-input {
            border-radius: 0.75rem !important;
            border: 2px solid #e5e7eb !important;
            transition: all 0.3s ease !important;
        }

        .fi-input:focus {
            border-color: #ff6b35 !important;
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1) !important;
        }

        .fi-fo-field-wrp-label {
            font-weight: 600 !important;
            color: #374151 !important;
        }
    </style>

    <div class="login-header">
        <div class="login-emoji">🍕</div>
        <h1 class="login-title">Bem-vindo!</h1>
        <p class="login-subtitle">Acesse o painel do seu restaurante</p>
    </div>

    @if (filament()->hasLogin())
        <x-filament-panels::form wire:submit="authenticate">
            {{ $this->form }}

            <x-filament-panels::form.actions
                :actions="$this->getCachedFormActions()"
                :full-width="$this->hasFullWidthFormActions()"
            />
        </x-filament-panels::form>
    @endif
</x-filament-panels::page.simple>
