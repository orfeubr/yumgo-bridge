/**
 * CHECKOUT - FINALIZAR PEDIDO
 * Conecta botão de finalizar com a API
 */

// FINALIZAR PEDIDO
async function finalizarPedido() {
    // Pegar carrinho do localStorage
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');

    if (cart.length === 0) {
        alert('Seu carrinho está vazio!');
        return;
    }

    // Validar endereço
    const deliveryAddress = document.getElementById('delivery_address')?.value;
    if (!deliveryAddress || deliveryAddress.trim() === '') {
        alert('Por favor, informe o endereço de entrega!');
        document.getElementById('delivery_address')?.focus();
        return;
    }

    // Pegar método de pagamento
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value;
    if (!paymentMethod) {
        alert('Por favor, selecione o método de pagamento!');
        return;
    }

    // Mostrar loading no botão
    const btn = document.getElementById('btn-finalizar-pedido');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Processando...';

    try {
        // Pegar token de autenticação
        const token = localStorage.getItem('auth_token');
        if (!token) {
            throw new Error('Você precisa fazer login primeiro!');
        }

        // Preparar items para API
        const items = cart.map(item => {
            const apiItem = {
                product_id: item.id,
                quantity: item.quantity || 1,
                notes: item.notes || '',
            };

            // Se tem variação (tamanho)
            if (item.variation_id) {
                apiItem.variation_id = item.variation_id;
            }

            // Se tem adicionais
            if (item.addons && Array.isArray(item.addons)) {
                apiItem.addons = item.addons.map(a => a.id);
            }

            // Se é meio a meio
            if (item.half_and_half) {
                apiItem.half_and_half = {
                    product_id: item.half_and_half.product_id,
                };
            }

            return apiItem;
        });

        // Preparar payload completo
        const payload = {
            items: items,
            delivery_address: deliveryAddress,
            payment_method: paymentMethod,
            use_cashback: parseFloat(document.getElementById('use_cashback')?.value || 0),
            notes: document.getElementById('order_notes')?.value || '',
        };

        // Se for cartão, adicionar dados
        if (paymentMethod === 'credit_card' || paymentMethod === 'debit_card') {
            payload.card = {
                holderName: document.getElementById('card_holder_name')?.value,
                number: document.getElementById('card_number')?.value.replace(/\s/g, ''),
                expiryMonth: document.getElementById('card_month')?.value,
                expiryYear: document.getElementById('card_year')?.value,
                ccv: document.getElementById('card_cvv')?.value,
            };
        }

        console.log('Enviando pedido:', payload);

        // FAZER REQUEST PARA API
        const response = await fetch('/api/v1/orders', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Erro ao criar pedido');
        }

        console.log('Pedido criado:', data);

        // SUCESSO! Limpar carrinho
        localStorage.removeItem('cart');
        updateCartDisplay();

        // Se for PIX, redirecionar para QR Code
        if (data.payment && data.payment.qrcode_image) {
            window.location.href = `/pix-payment?order=${data.order.id}`;
        } else {
            // Outros métodos
            window.location.href = `/order-success?order=${data.order.id}&number=${data.order.order_number}`;
        }

    } catch (error) {
        console.error('Erro ao finalizar pedido:', error);
        alert(`Erro: ${error.message}`);

        // Restaurar botão
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// ATUALIZAR DISPLAY DO CARRINHO
function updateCartDisplay() {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const cartCount = document.getElementById('cart-count');
    const cartItems = document.getElementById('cart-items');
    const cartTotal = document.getElementById('cart-total');

    // Atualizar contador
    if (cartCount) {
        const totalItems = cart.reduce((sum, item) => sum + (item.quantity || 1), 0);
        cartCount.textContent = totalItems;
        cartCount.style.display = totalItems > 0 ? 'inline-block' : 'none';
    }

    // Atualizar lista de items
    if (cartItems) {
        if (cart.length === 0) {
            cartItems.innerHTML = '<p style="text-align: center; color: #999; padding: 40px;">Carrinho vazio</p>';
        } else {
            cartItems.innerHTML = cart.map((item, index) => `
                <div class="cart-item">
                    <img src="${item.image || '/placeholder.png'}" alt="${item.name}">
                    <div class="cart-item-info">
                        <h4>${item.name}</h4>
                        ${item.ingredients ? `<p class="item-ingredients">${item.ingredients}</p>` : ''}
                        <div class="item-quantity">
                            <button onclick="updateItemQuantity(${index}, -1)">-</button>
                            <span>${item.quantity || 1}</span>
                            <button onclick="updateItemQuantity(${index}, 1)">+</button>
                        </div>
                    </div>
                    <div class="cart-item-actions">
                        <div class="item-price">R$ ${((item.price || 0) * (item.quantity || 1)).toFixed(2)}</div>
                        <button onclick="removeFromCart(${index})" class="btn-remove">🗑️</button>
                    </div>
                </div>
            `).join('');
        }
    }

    // Atualizar total
    if (cartTotal) {
        const total = cart.reduce((sum, item) => sum + ((item.price || 0) * (item.quantity || 1)), 0);
        cartTotal.textContent = `R$ ${total.toFixed(2).replace('.', ',')}`;
    }

    // Habilitar/desabilitar botão de checkout
    const btnCheckout = document.getElementById('btn-finalizar-pedido');
    if (btnCheckout) {
        btnCheckout.disabled = cart.length === 0;
    }
}

// ATUALIZAR QUANTIDADE
function updateItemQuantity(index, delta) {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');

    if (cart[index]) {
        cart[index].quantity = (cart[index].quantity || 1) + delta;

        if (cart[index].quantity <= 0) {
            cart.splice(index, 1);
        }

        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartDisplay();
    }
}

// REMOVER DO CARRINHO
function removeFromCart(index) {
    if (confirm('Remover este item do carrinho?')) {
        const cart = JSON.parse(localStorage.getItem('cart') || '[]');
        cart.splice(index, 1);
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartDisplay();
    }
}

// APLICAR CUPOM
function applyCoupon() {
    const code = document.getElementById('coupon_code')?.value;
    if (!code) return;

    // TODO: Validar cupom via API
    alert('Funcionalidade de cupom em desenvolvimento');
}

// INICIALIZAR AO CARREGAR PÁGINA
document.addEventListener('DOMContentLoaded', function() {
    updateCartDisplay();

    // Adicionar evento ao botão de finalizar
    const btnCheckout = document.getElementById('btn-finalizar-pedido');
    if (btnCheckout) {
        btnCheckout.addEventListener('click', finalizarPedido);
    }
});

// CSS para spinner
const style = document.createElement('style');
style.textContent = `
    .spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255,255,255,.3);
        border-radius: 50%;
        border-top-color: white;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .cart-item {
        display: flex;
        gap: 12px;
        padding: 12px;
        border-bottom: 1px solid #f0f0f0;
    }

    .cart-item img {
        width: 60px;
        height: 60px;
        border-radius: 8px;
        object-fit: cover;
    }

    .cart-item-info {
        flex: 1;
    }

    .cart-item-info h4 {
        font-size: 14px;
        margin-bottom: 4px;
    }

    .item-ingredients {
        font-size: 12px;
        color: #666;
        margin-bottom: 8px;
    }

    .item-quantity {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .item-quantity button {
        width: 24px;
        height: 24px;
        border: 1px solid #ddd;
        background: white;
        border-radius: 4px;
        cursor: pointer;
    }

    .item-quantity button:hover {
        background: #f5f5f5;
    }

    .cart-item-actions {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 8px;
    }

    .item-price {
        font-size: 16px;
        font-weight: 700;
        color: #ff6b35;
    }

    .btn-remove {
        background: none;
        border: none;
        font-size: 18px;
        cursor: pointer;
        opacity: 0.5;
        transition: opacity 0.2s;
    }

    .btn-remove:hover {
        opacity: 1;
    }
`;
document.head.appendChild(style);
