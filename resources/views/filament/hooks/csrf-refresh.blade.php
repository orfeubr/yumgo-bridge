<script>
    // Auto-refresh CSRF token a cada 10 minutos
    setInterval(function() {
        fetch('/sanctum/csrf-cookie')
            .then(() => console.log('✅ CSRF token renovado'))
            .catch(err => console.error('❌ Erro ao renovar CSRF token:', err));
    }, 600000); // 10 minutos

    // Interceptar erro 419 (Page Expired) e recarregar automaticamente
    window.addEventListener('livewire:exception', (event) => {
        if (event.detail?.status === 419) {
            console.log('🔄 Sessão expirada, recarregando página...');
            window.location.reload();
        }
    });

    // Interceptar erros AJAX 419
    document.addEventListener('DOMContentLoaded', function() {
        // Override do fetch para interceptar 419
        const originalFetch = window.fetch;
        window.fetch = function(...args) {
            return originalFetch.apply(this, args)
                .then(response => {
                    if (response.status === 419) {
                        console.log('🔄 Sessão expirada, recarregando página...');
                        window.location.reload();
                    }
                    return response;
                });
        };
    });
</script>
