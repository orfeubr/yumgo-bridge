{{--
    Loading Spinner YumGo - Jumping Dots
    Estilo moderno (Uber Eats/WhatsApp)
--}}

<div class="inline-flex items-center justify-center">
    <div style="display: inline-flex; gap: 8px; align-items: center; justify-content: center;">
        <div style="width: 12px; height: 12px; background-color: #EA1D2C; border-radius: 50%; animation: yumgo-bounce-1 1.4s infinite ease-in-out both; box-shadow: 0 2px 8px rgba(234, 29, 44, 0.3);"></div>
        <div style="width: 12px; height: 12px; background-color: #EA1D2C; border-radius: 50%; animation: yumgo-bounce-2 1.4s infinite ease-in-out both; box-shadow: 0 2px 8px rgba(234, 29, 44, 0.3);"></div>
        <div style="width: 12px; height: 12px; background-color: #EA1D2C; border-radius: 50%; animation: yumgo-bounce-3 1.4s infinite ease-in-out both; box-shadow: 0 2px 8px rgba(234, 29, 44, 0.3);"></div>
    </div>
</div>

<style>
@keyframes yumgo-bounce-1 {
    0%, 80%, 100% { transform: scale(0.8) translateY(0); opacity: 0.7; }
    40% { transform: scale(1.2) translateY(-12px); opacity: 1; }
}
@keyframes yumgo-bounce-2 {
    0%, 80%, 100% { transform: scale(0.8) translateY(0); opacity: 0.7; }
    40% { transform: scale(1.2) translateY(-12px); opacity: 1; }
}
@keyframes yumgo-bounce-3 {
    0%, 80%, 100% { transform: scale(0.8) translateY(0); opacity: 0.7; }
    40% { transform: scale(1.2) translateY(-12px); opacity: 1; }
}
/* Delays */
div[style*="yumgo-bounce-1"] { animation-delay: -0.32s !important; }
div[style*="yumgo-bounce-2"] { animation-delay: -0.16s !important; }
div[style*="yumgo-bounce-3"] { animation-delay: 0s !important; }
</style>
