<!-- Modal de Seleção Meio a Meio -->
<div id="halfAndHalfModal" class="modal" style="display: none;">
    <div class="modal-overlay" onclick="closeHalfModal()"></div>

    <div class="modal-content">
        <div class="modal-header">
            <h2>🍕 Pizza Meio a Meio</h2>
            <button class="modal-close" onclick="closeHalfModal()">✕</button>
        </div>

        <div class="modal-body">
            <!-- Primeiro Sabor Selecionado -->
            <div class="selected-flavor">
                <h3>1ª Metade</h3>
                <div id="firstFlavorDisplay" class="flavor-display">
                    <!-- Preenchido via JS -->
                </div>
            </div>

            <!-- Segundo Sabor - SCROLL COM INGREDIENTES -->
            <div class="second-flavor-section">
                <h3>2ª Metade - Escolha o segundo sabor</h3>

                <input
                    type="text"
                    id="searchSecondFlavor"
                    class="search-input"
                    placeholder="🔍 Buscar por nome ou ingrediente..."
                    onkeyup="filterSecondFlavors(this.value)"
                />

                <!-- SCROLL DE SABORES -->
                <div id="secondFlavorsScroll" class="flavors-scroll">
                    <!-- Cards serão inseridos aqui -->
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button onclick="closeHalfModal()" class="btn-cancel">Cancelar</button>
            <button onclick="confirmHalfAndHalf()" id="btnConfirmHalf" class="btn-confirm" disabled>
                Adicionar ao Carrinho
            </button>
        </div>
    </div>
</div>

<style>
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(4px);
}

.modal-content {
    position: relative;
    background: white;
    border-radius: 16px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    display: flex;
    flex-direction: column;
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    border-bottom: 2px solid #f0f0f0;
    background: linear-gradient(135deg, #ff6b35 0%, #e85a25 100%);
    color: white;
}

.modal-header h2 {
    margin: 0;
    font-size: 20px;
}

.modal-close {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    font-size: 24px;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.modal-close:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: rotate(90deg);
}

.modal-body {
    flex: 1;
    overflow-y: auto;
    padding: 20px 24px;
}

.selected-flavor {
    margin-bottom: 24px;
    padding: 16px;
    background: #f9f9f9;
    border-radius: 12px;
}

.selected-flavor h3 {
    font-size: 14px;
    color: #666;
    margin-bottom: 8px;
}

.flavor-display {
    display: flex;
    align-items: center;
    gap: 12px;
}

.flavor-display img {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    object-fit: cover;
}

.flavor-display-info h4 {
    font-size: 16px;
    margin-bottom: 4px;
}

.flavor-display-info p {
    font-size: 13px;
    color: #666;
    line-height: 1.4;
}

.second-flavor-section h3 {
    font-size: 16px;
    margin-bottom: 12px;
}

.search-input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
    margin-bottom: 12px;
    transition: border-color 0.2s;
}

.search-input:focus {
    outline: none;
    border-color: #ff6b35;
}

/* SCROLL DE SABORES */
.flavors-scroll {
    max-height: 400px;
    overflow-y: auto;
    padding-right: 8px;
}

.flavors-scroll::-webkit-scrollbar {
    width: 6px;
}

.flavors-scroll::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.flavors-scroll::-webkit-scrollbar-thumb {
    background: #ff6b35;
    border-radius: 3px;
}

/* CARD DE SABOR NO SCROLL */
.flavor-scroll-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border: 2px solid #e0e0e0;
    border-radius: 12px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.2s;
    background: white;
}

.flavor-scroll-card:hover {
    border-color: #ff6b35;
    box-shadow: 0 4px 12px rgba(255, 107, 53, 0.15);
    transform: translateY(-2px);
}

.flavor-scroll-card.selected {
    border-color: #ff6b35;
    background: #fff5f2;
    box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
}

.flavor-scroll-card img {
    width: 70px;
    height: 70px;
    border-radius: 8px;
    object-fit: cover;
    flex-shrink: 0;
}

.flavor-scroll-info {
    flex: 1;
    min-width: 0;
}

.flavor-scroll-info h4 {
    font-size: 15px;
    font-weight: 600;
    margin-bottom: 4px;
    color: #333;
}

/* INGREDIENTES VISÍVEIS */
.flavor-ingredients {
    font-size: 12px;
    color: #666;
    line-height: 1.4;
    margin-bottom: 6px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.flavor-ingredients strong {
    color: #ff6b35;
    font-weight: 600;
}

.flavor-scroll-price {
    font-size: 16px;
    font-weight: 700;
    color: #ff6b35;
}

.selected-badge {
    background: #ff6b35;
    color: white;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.modal-footer {
    padding: 16px 24px;
    border-top: 2px solid #f0f0f0;
    display: flex;
    gap: 12px;
}

.btn-cancel {
    flex: 1;
    padding: 12px;
    border: 2px solid #e0e0e0;
    background: white;
    color: #666;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-cancel:hover {
    border-color: #ccc;
    background: #f9f9f9;
}

.btn-confirm {
    flex: 2;
    padding: 12px;
    border: none;
    background: #ff6b35;
    color: white;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-confirm:hover:not(:disabled) {
    background: #e85a25;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
}

.btn-confirm:disabled {
    background: #ccc;
    cursor: not-allowed;
}
</style>

<script>
let allFlavors = [];
let selectedFirstFlavor = null;
let selectedSecondFlavor = null;

// ABRIR MODAL
function openHalfModal(firstFlavor) {
    selectedFirstFlavor = firstFlavor;
    selectedSecondFlavor = null;

    // Mostrar primeiro sabor
    document.getElementById('firstFlavorDisplay').innerHTML = `
        <img src="${firstFlavor.image || '/placeholder.png'}" alt="${firstFlavor.name}">
        <div class="flavor-display-info">
            <h4>${firstFlavor.name}</h4>
            <p><strong>Ingredientes:</strong> ${firstFlavor.ingredients || 'Não informado'}</p>
            <p style="color: #ff6b35; font-weight: 600; margin-top: 4px;">${firstFlavor.price_formatted || 'R$ ' + firstFlavor.price.toFixed(2)}</p>
        </div>
    `;

    // Carregar sabores para o segundo
    loadSecondFlavors();

    // Mostrar modal
    document.getElementById('halfAndHalfModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

// FECHAR MODAL
function closeHalfModal() {
    document.getElementById('halfAndHalfModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    selectedFirstFlavor = null;
    selectedSecondFlavor = null;
}

// CARREGAR SABORES PARA O SEGUNDO
async function loadSecondFlavors() {
    try {
        const response = await fetch('/api/v1/products/pizza/flavors?per_page=50');
        const data = await response.json();
        allFlavors = data.data;
        renderSecondFlavors(allFlavors);
    } catch (error) {
        console.error('Erro ao carregar sabores:', error);
    }
}

// RENDERIZAR CARDS NO SCROLL
function renderSecondFlavors(flavors) {
    const container = document.getElementById('secondFlavorsScroll');

    if (flavors.length === 0) {
        container.innerHTML = '<p style="text-align: center; color: #999; padding: 20px;">Nenhum sabor encontrado</p>';
        return;
    }

    container.innerHTML = flavors.map(flavor => `
        <div class="flavor-scroll-card ${selectedSecondFlavor?.id === flavor.id ? 'selected' : ''}"
             onclick="selectSecondFlavor(${flavor.id})">

            ${selectedSecondFlavor?.id === flavor.id ? '<span class="selected-badge">✓ Selecionado</span>' : ''}

            <img src="${flavor.image || '/placeholder.png'}" alt="${flavor.name}">

            <div class="flavor-scroll-info">
                <h4>${flavor.name}</h4>

                <!-- INGREDIENTES VISÍVEIS NO SCROLL -->
                <div class="flavor-ingredients">
                    <strong>🍕 Ingredientes:</strong> ${flavor.ingredients || flavor.ingredients_short || 'Não informado'}
                </div>

                <div class="flavor-scroll-price">${flavor.price_formatted || 'R$ ' + parseFloat(flavor.price).toFixed(2).replace('.', ',')}</div>
            </div>
        </div>
    `).join('');
}

// SELECIONAR SEGUNDO SABOR
function selectSecondFlavor(flavorId) {
    selectedSecondFlavor = allFlavors.find(f => f.id === flavorId);
    renderSecondFlavors(allFlavors);

    // Habilitar botão
    const btn = document.getElementById('btnConfirmHalf');
    btn.disabled = false;

    const maxPrice = Math.max(selectedFirstFlavor.price, selectedSecondFlavor.price);
    btn.textContent = `Adicionar - R$ ${maxPrice.toFixed(2).replace('.', ',')}`;
}

// FILTRAR SABORES
function filterSecondFlavors(query) {
    if (!query.trim()) {
        renderSecondFlavors(allFlavors);
        return;
    }

    const filtered = allFlavors.filter(f =>
        f.name.toLowerCase().includes(query.toLowerCase()) ||
        (f.ingredients && f.ingredients.toLowerCase().includes(query.toLowerCase()))
    );

    renderSecondFlavors(filtered);
}

// CONFIRMAR E ADICIONAR AO CARRINHO
function confirmHalfAndHalf() {
    if (!selectedSecondFlavor) return;

    // Adicionar ao carrinho
    const item = {
        id: selectedFirstFlavor.id,
        name: `Meio a Meio: ${selectedFirstFlavor.name} + ${selectedSecondFlavor.name}`,
        image: selectedFirstFlavor.image,
        price: Math.max(selectedFirstFlavor.price, selectedSecondFlavor.price),
        quantity: 1,
        half_and_half: {
            product_id: selectedSecondFlavor.id,
            name: selectedSecondFlavor.name,
            ingredients: selectedSecondFlavor.ingredients,
        },
        ingredients: `1ª metade: ${selectedFirstFlavor.ingredients} | 2ª metade: ${selectedSecondFlavor.ingredients}`,
    };

    addToCart(item);
    closeHalfModal();

    // Notificar
    showToast('Pizza meio a meio adicionada ao carrinho!');
}

// ADICIONAR AO CARRINHO (você já deve ter essa função)
function addToCart(item) {
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
    cart.push(item);
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartDisplay();
}

// MOSTRAR NOTIFICAÇÃO
function showToast(message) {
    // Implementar notificação (toast/alert)
    alert(message);
}
</script>
