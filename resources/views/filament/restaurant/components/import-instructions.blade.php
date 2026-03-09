<div class="prose dark:prose-invert max-w-none">
    <h3>📋 Como Importar Produtos</h3>

    <ol>
        <li><strong>Baixe o modelo</strong> clicando em "Baixar Modelo Excel" abaixo</li>
        <li><strong>Preencha a planilha</strong> com seus produtos seguindo o formato</li>
        <li><strong>Faça upload</strong> do arquivo preenchido usando o campo acima</li>
        <li><strong>Clique em "Importar"</strong> e aguarde o processamento</li>
    </ol>

    <h4>📝 Formato da Planilha</h4>

    <table class="min-w-full border border-gray-300 dark:border-gray-700">
        <thead>
            <tr class="bg-gray-50 dark:bg-gray-800">
                <th class="border px-2 py-1">Coluna</th>
                <th class="border px-2 py-1">Obrigatória?</th>
                <th class="border px-2 py-1">Formato</th>
                <th class="border px-2 py-1">Exemplo</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="border px-2 py-1"><strong>categoria</strong></td>
                <td class="border px-2 py-1">✅ Sim</td>
                <td class="border px-2 py-1">Texto</td>
                <td class="border px-2 py-1">Pizzas</td>
            </tr>
            <tr>
                <td class="border px-2 py-1"><strong>nome</strong></td>
                <td class="border px-2 py-1">✅ Sim</td>
                <td class="border px-2 py-1">Texto</td>
                <td class="border px-2 py-1">Pizza de Mussarela</td>
            </tr>
            <tr>
                <td class="border px-2 py-1"><strong>descricao</strong></td>
                <td class="border px-2 py-1">❌ Não</td>
                <td class="border px-2 py-1">Texto</td>
                <td class="border px-2 py-1">Molho, mussarela, orégano</td>
            </tr>
            <tr>
                <td class="border px-2 py-1"><strong>preco</strong></td>
                <td class="border px-2 py-1">✅ Sim</td>
                <td class="border px-2 py-1">Número (use vírgula)</td>
                <td class="border px-2 py-1">35,00</td>
            </tr>
            <tr>
                <td class="border px-2 py-1"><strong>variacoes</strong></td>
                <td class="border px-2 py-1">❌ Não</td>
                <td class="border px-2 py-1">Nome:Preço,Nome:Preço<br><small class="text-red-600">⚠️ Use aspas duplas!</small></td>
                <td class="border px-2 py-1">"P:30.00,M:35.00,G:45.00"</td>
            </tr>
            <tr>
                <td class="border px-2 py-1"><strong>adicionais</strong></td>
                <td class="border px-2 py-1">❌ Não</td>
                <td class="border px-2 py-1">Nome:Preço,Nome:Preço<br><small class="text-red-600">⚠️ Use aspas duplas!</small></td>
                <td class="border px-2 py-1">"Borda:5.00,Catupiry:3.00"</td>
            </tr>
            <tr>
                <td class="border px-2 py-1"><strong>foto_url</strong></td>
                <td class="border px-2 py-1">❌ Não</td>
                <td class="border px-2 py-1">URL completa</td>
                <td class="border px-2 py-1">https://exemplo.com/pizza.jpg</td>
            </tr>
            <tr>
                <td class="border px-2 py-1"><strong>ativo</strong></td>
                <td class="border px-2 py-1">❌ Não</td>
                <td class="border px-2 py-1">sim ou não</td>
                <td class="border px-2 py-1">sim</td>
            </tr>
        </tbody>
    </table>

    <h4>📸 Sobre as Fotos</h4>

    <ul>
        <li><strong>URL Obrigatória:</strong> A foto precisa estar hospedada online</li>
        <li><strong>Download Automático:</strong> O YumGo baixa e hospeda automaticamente</li>
        <li><strong>Thumbnail:</strong> Miniatura de 400x400px é gerada automaticamente</li>
        <li><strong>Formatos:</strong> JPG, PNG, GIF, WEBP</li>
    </ul>

    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 my-4">
        <p class="font-bold text-yellow-800">⚠️ URLs de Fotos - IMPORTANTE:</p>
        <div class="mt-2 text-sm text-yellow-700">
            <p class="font-semibold">URLs que FUNCIONAM:</p>
            <ul class="list-disc list-inside ml-2">
                <li>✅ Dropbox público</li>
                <li>✅ Google Drive (compartilhamento público)</li>
                <li>✅ Imgur (https://imgur.com)</li>
                <li>✅ Unsplash</li>
            </ul>
            <p class="font-semibold mt-2">URLs que NÃO FUNCIONAM:</p>
            <ul class="list-disc list-inside ml-2">
                <li>❌ iFood (bloqueado)</li>
                <li>❌ URLs privadas</li>
            </ul>
            <p class="mt-2 font-semibold">💡 Como pegar fotos do iFood:</p>
            <ol class="list-decimal list-inside ml-2">
                <li>Salve a imagem do iFood no seu computador</li>
                <li>Faça upload para Imgur ou Dropbox</li>
                <li>Use a URL pública gerada</li>
            </ol>
        </div>
    </div>

    <h4>⚠️ Avisos Importantes</h4>

    <ul>
        <li>Produtos duplicados (mesmo nome) serão criados novamente</li>
        <li>Categorias novas são criadas automaticamente</li>
        <li>Se houver erro em uma linha, as outras continuam sendo importadas</li>
        <li>Revise sempre o relatório após a importação</li>
    </ul>
</div>
