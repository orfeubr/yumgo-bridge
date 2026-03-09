/**
 * MODAL DE PIZZA MEIO A MEIO
 * Com scroll e ingredientes visíveis
 */

let modalFirstFlavor = null;
let modalSecondFlavor = null;
let modalAllFlavors = [];

// ABRIR MODAL
function abrirModalMeioAMeio(firstFlavor) {
    modalFirstFlavor = firstFlavor;
    modalSecondFlavor = null;

    // Criar modal se não existir
    if (!document.getElementById('modal-meio-a-meio')) {
        criarModalHTML();
    }

    // Exibir primeiro sabor (compacto)
    document.getElementById('modal-first-flavor').innerHTML = `
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <img src="${firstFlavor.image || '/placeholder.png'}"
                 alt="${firstFlavor.name}"
                 style="width: 50px; height: 50px; border-radius: 0.5rem; object-fit: cover; flex-shrink: 0;">
            <div style="flex: 1; min-width: 0;">
                <h4 style="margin: 0 0 0.25rem 0; font-size: 0.9375rem; font-weight: 600; color: #1f2937;">${firstFlavor.name}</h4>
                <p style="margin: 0; font-size: 0.75rem; color: #6b7280; line-height: 1.3; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical;">
                    ${firstFlavor.ingredients || 'Não informado'}
                </p>
                <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem; color: #EA1D2C; font-weight: 700;">
                    R$ ${parseFloat(firstFlavor.price).toFixed(2).replace('.', ',')}
                </p>
            </div>
        </div>
    `;

    // Carregar sabores
    carregarSaboresParaModal();

    // Mostrar modal
    document.getElementById('modal-meio-a-meio').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

// FECHAR MODAL
function fecharModalMeioAMeio() {
    document.getElementById('modal-meio-a-meio').style.display = 'none';
    document.body.style.overflow = 'auto';
    modalFirstFlavor = null;
    modalSecondFlavor = null;
}

// CARREGAR SABORES
async function carregarSaboresParaModal() {
    const container = document.getElementById('modal-flavors-scroll');
    container.innerHTML = '<p style="text-align: center; padding: 20px;">Carregando...</p>';

    try {
        const response = await fetch('/api/v1/products/pizza/flavors?per_page=100');
        const data = await response.json();
        modalAllFlavors = data.data || [];

        renderizarSaboresModal(modalAllFlavors);
    } catch (error) {
        console.error('Erro ao carregar sabores:', error);
        container.innerHTML = '<p style="text-align: center; padding: 20px; color: #999;">Erro ao carregar sabores</p>';
    }
}

// RENDERIZAR SABORES NO SCROLL
function renderizarSaboresModal(flavors) {
    const container = document.getElementById('modal-flavors-scroll');

    if (flavors.length === 0) {
        container.innerHTML = '<p style="text-align: center; padding: 20px; color: #999;">Nenhum sabor encontrado</p>';
        return;
    }

    container.innerHTML = flavors.map(flavor => `
        <div class="modal-flavor-card ${modalSecondFlavor?.id === flavor.id ? 'selected' : ''}"
             onclick="selecionarSegundoSabor(${flavor.id})">

            ${modalSecondFlavor?.id === flavor.id ?
                '<div class="selected-badge">✓</div>' : ''}

            <img src="${flavor.image || '/placeholder.png'}"
                 alt="${flavor.name}">

            <div class="modal-flavor-info">
                <h4>${flavor.name}</h4>

                <p class="modal-flavor-ingredients">
                    ${flavor.ingredients || flavor.ingredients_short || 'Sem descrição'}
                </p>

                <p class="modal-flavor-price">
                    R$ ${parseFloat(flavor.price).toFixed(2).replace('.', ',')}
                </p>
            </div>
        </div>
    `).join('');
}

// SELECIONAR SEGUNDO SABOR
function selecionarSegundoSabor(flavorId) {
    modalSecondFlavor = modalAllFlavors.find(f => f.id === flavorId);
    renderizarSaboresModal(modalAllFlavors);

    // Atualizar botão
    const btn = document.getElementById('btn-confirmar-meio');
    btn.disabled = false;

    const maxPrice = Math.max(
        parseFloat(modalFirstFlavor.price),
        parseFloat(modalSecondFlavor.price)
    );

    btn.textContent = `Adicionar - R$ ${maxPrice.toFixed(2).replace('.', ',')}`;
}

// BUSCAR SABORES
function buscarSaboresModal() {
    const query = document.getElementById('modal-search').value.toLowerCase();

    if (!query.trim()) {
        renderizarSaboresModal(modalAllFlavors);
        return;
    }

    const filtered = modalAllFlavors.filter(f =>
        f.name.toLowerCase().includes(query) ||
        (f.ingredients && f.ingredients.toLowerCase().includes(query))
    );

    renderizarSaboresModal(filtered);
}

// CONFIRMAR MEIO A MEIO
function confirmarMeioAMeio() {
    if (!modalSecondFlavor) return;

    const maxPrice = Math.max(
        parseFloat(modalFirstFlavor.price),
        parseFloat(modalSecondFlavor.price)
    );

    const item = {
        id: modalFirstFlavor.id,
        name: `Meio a Meio: ${modalFirstFlavor.name} + ${modalSecondFlavor.name}`,
        image: modalFirstFlavor.image,
        price: maxPrice,
        quantity: 1,
        ingredients: `1ª metade: ${modalFirstFlavor.ingredients || modalFirstFlavor.name}`,
        half_and_half: {
            product_id: modalSecondFlavor.id,
            name: modalSecondFlavor.name,
            ingredients: modalSecondFlavor.ingredients || modalSecondFlavor.name
        }
    };

    addToCart(item);
    fecharModalMeioAMeio();
}

// CRIAR HTML DO MODAL (Clean & Compacto)
function criarModalHTML() {
    const modal = document.createElement('div');
    modal.id = 'modal-meio-a-meio';
    modal.innerHTML = `
        <div class="modal-overlay" onclick="fecharModalMeioAMeio()"></div>
        <div class="modal-container">
            <!-- Header Compacto -->
            <div class="modal-header">
                <h2>🍕 Pizza Meio a Meio</h2>
                <button onclick="fecharModalMeioAMeio()" class="modal-close">✕</button>
            </div>

            <div class="modal-body">
                <!-- 1ª Metade (Compacto) -->
                <div class="modal-section-compact">
                    <p class="section-label">1ª Metade</p>
                    <div id="modal-first-flavor" class="flavor-compact"></div>
                </div>

                <!-- 2ª Metade -->
                <div class="modal-section-compact">
                    <p class="section-label">2ª Metade</p>

                    <input
                        type="text"
                        id="modal-search"
                        class="modal-search"
                        placeholder="🔍 Buscar sabor..."
                        oninput="buscarSaboresModal()"
                    >

                    <!-- SCROLL COMPACTO -->
                    <div id="modal-flavors-scroll" class="modal-flavors-scroll">
                        <!-- Cards aqui -->
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button onclick="fecharModalMeioAMeio()" class="btn-cancel">Cancelar</button>
                <button onclick="confirmarMeioAMeio()" id="btn-confirmar-meio" class="btn-confirm" disabled>
                    Selecione o 2º sabor
                </button>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    // CSS do modal (Clean & Compacto)
    const style = document.createElement('style');
    style.textContent = `
        #modal-meio-a-meio {
            position: fixed;
            inset: 0;
            z-index: 99999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        @media (min-width: 768px) {
            #modal-meio-a-meio { padding: 1rem; }
        }

        .modal-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(8px);
        }

        .modal-container {
            position: relative;
            background: white;
            width: 100%;
            max-width: 540px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            border-radius: 0;
        }

        @media (min-width: 768px) {
            .modal-container { border-radius: 1rem; }
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            background: #EA1D2C;
            color: white;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 1.125rem;
            font-weight: 700;
        }

        .modal-close {
            background: rgba(255, 255, 255, 0.15);
            border: none;
            color: white;
            font-size: 1.5rem;
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-close:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        .modal-body {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
        }

        .modal-section-compact {
            margin-bottom: 1rem;
        }

        .section-label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #6b7280;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }

        .flavor-compact {
            padding: 0.75rem;
            background: #f9fafb;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
        }

        .modal-search {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            margin-bottom: 0.75rem;
            transition: border-color 0.2s;
        }

        .modal-search:focus {
            outline: none;
            border-color: #EA1D2C;
            ring: 2px;
            ring-color: rgba(234, 29, 44, 0.1);
        }

        .modal-flavors-scroll {
            max-height: 320px;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        .modal-flavors-scroll::-webkit-scrollbar {
            width: 4px;
        }

        .modal-flavors-scroll::-webkit-scrollbar-track {
            background: #f3f4f6;
            border-radius: 2px;
        }

        .modal-flavors-scroll::-webkit-scrollbar-thumb {
            background: #EA1D2C;
            border-radius: 2px;
        }

        .modal-flavor-card {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            margin-bottom: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
            background: white;
            position: relative;
        }

        .modal-flavor-card:hover {
            border-color: #EA1D2C;
            box-shadow: 0 2px 8px rgba(234, 29, 44, 0.1);
            transform: translateY(-1px);
        }

        .modal-flavor-card.selected {
            border-color: #EA1D2C;
            background: #fef2f2;
            box-shadow: 0 0 0 2px rgba(234, 29, 44, 0.1);
        }

        .modal-flavor-card img {
            width: 60px;
            height: 60px;
            border-radius: 0.5rem;
            object-fit: cover;
            flex-shrink: 0;
        }

        .modal-flavor-info {
            flex: 1;
            min-width: 0;
        }

        .modal-flavor-info h4 {
            font-size: 0.9375rem;
            font-weight: 600;
            margin: 0 0 0.25rem 0;
            color: #1f2937;
        }

        .modal-flavor-ingredients {
            font-size: 0.75rem;
            color: #6b7280;
            line-height: 1.3;
            margin: 0.25rem 0;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .modal-flavor-ingredients strong {
            color: #EA1D2C;
            font-weight: 600;
        }

        .modal-flavor-price {
            font-size: 0.9375rem;
            font-weight: 700;
            color: #EA1D2C;
            margin: 0;
        }

        .selected-badge {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: #EA1D2C;
            color: white;
            padding: 0.25rem 0.625rem;
            border-radius: 0.75rem;
            font-size: 0.6875rem;
            font-weight: 600;
        }

        .modal-footer {
            padding: 1rem;
            border-top: 1px solid #e5e7eb;
            display: flex;
            gap: 0.75rem;
        }

        .btn-cancel {
            flex: 1;
            padding: 0.875rem;
            border: 1px solid #d1d5db;
            background: white;
            color: #6b7280;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-cancel:hover {
            border-color: #9ca3af;
            background: #f9fafb;
        }

        .btn-confirm {
            flex: 2;
            padding: 0.875rem;
            border: none;
            background: #EA1D2C;
            color: white;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-confirm:hover:not(:disabled) {
            background: #d11a26;
            box-shadow: 0 4px 12px rgba(234, 29, 44, 0.25);
        }

        .btn-confirm:active:not(:disabled) {
            transform: scale(0.98);
        }

        .btn-confirm:disabled {
            background: #d1d5db;
            cursor: not-allowed;
        }
    `;

    document.head.appendChild(style);
}
