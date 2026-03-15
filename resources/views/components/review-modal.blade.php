<!-- Modal de Avaliação -->
<div x-show="showReviewModal"
     @click.self="showReviewModal = false"
     x-cloak
     x-transition.opacity
     class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[9999] flex items-center justify-center p-4">

    <div @click.stop
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-full md:translate-y-0 md:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 md:scale-100"
         class="bg-white w-full md:w-full md:max-w-md rounded-t-3xl md:rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto">

        <!-- Header -->
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-900">Avaliar Pedido</h3>
            <button @click="showReviewModal = false" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Conteúdo -->
        <div class="p-6 space-y-6">
            <!-- Avaliação Geral -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-3">
                    Como foi sua experiência?
                </label>
                <div class="flex gap-2 justify-center">
                    <template x-for="star in 5" :key="star">
                        <button @click="reviewData.rating = star"
                                type="button"
                                class="text-4xl transition-all hover:scale-110"
                                :class="star <= reviewData.rating ? 'text-yellow-400' : 'text-gray-300'">
                            ★
                        </button>
                    </template>
                </div>
                <p x-show="reviewData.rating > 0"
                   x-text="['Péssimo', 'Ruim', 'Regular', 'Bom', 'Excelente'][reviewData.rating - 1]"
                   class="text-center text-sm text-gray-600 mt-2"></p>
            </div>

            <!-- Avaliações Detalhadas (Opcional) -->
            <div class="space-y-4">
                <p class="text-sm font-semibold text-gray-700">Avalie também (opcional):</p>

                <!-- Comida -->
                <div>
                    <label class="block text-xs text-gray-600 mb-1">🍽️ Comida</label>
                    <div class="flex gap-1">
                        <template x-for="star in 5" :key="star">
                            <button @click="reviewData.food_rating = star"
                                    type="button"
                                    class="text-2xl"
                                    :class="star <= (reviewData.food_rating || 0) ? 'text-yellow-400' : 'text-gray-300'">
                                ★
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Entrega -->
                <div>
                    <label class="block text-xs text-gray-600 mb-1">🚚 Entrega</label>
                    <div class="flex gap-1">
                        <template x-for="star in 5" :key="star">
                            <button @click="reviewData.delivery_rating = star"
                                    type="button"
                                    class="text-2xl"
                                    :class="star <= (reviewData.delivery_rating || 0) ? 'text-yellow-400' : 'text-gray-300'">
                                ★
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Atendimento -->
                <div>
                    <label class="block text-xs text-gray-600 mb-1">😊 Atendimento</label>
                    <div class="flex gap-1">
                        <template x-for="star in 5" :key="star">
                            <button @click="reviewData.service_rating = star"
                                    type="button"
                                    class="text-2xl"
                                    :class="star <= (reviewData.service_rating || 0) ? 'text-yellow-400' : 'text-gray-300'">
                                ★
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Comentário -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Conte-nos mais (opcional)
                </label>
                <textarea x-model="reviewData.comment"
                          rows="4"
                          maxlength="1000"
                          placeholder="O que você achou do pedido?"
                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent resize-none"></textarea>
                <p class="text-xs text-gray-500 mt-1" x-text="(reviewData.comment || '').length + '/1000'"></p>
            </div>

            <!-- Publicar -->
            <div class="flex items-center gap-2">
                <input type="checkbox"
                       x-model="reviewData.is_public"
                       id="is_public"
                       class="w-4 h-4 text-red-600 rounded focus:ring-red-500">
                <label for="is_public" class="text-sm text-gray-700">
                    Permitir que outros clientes vejam minha avaliação
                </label>
            </div>

            <!-- Botões -->
            <div class="flex gap-3">
                <button @click="showReviewModal = false"
                        type="button"
                        class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition">
                    Cancelar
                </button>
                <button @click="submitReview()"
                        :disabled="!reviewData.rating"
                        type="button"
                        class="flex-1 px-6 py-3 bg-red-600 text-white rounded-xl font-semibold hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Enviar Avaliação
                </button>
            </div>
        </div>
    </div>
</div>
