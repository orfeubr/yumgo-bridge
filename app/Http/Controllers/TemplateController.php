<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TemplateController extends Controller
{
    /**
     * Gera e retorna template Excel para importação de produtos
     */
    public function products(): Response
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Título da planilha
        $sheet->setTitle('Produtos YumGo');

        // Headers
        $headers = [
            'A1' => 'categoria',
            'B1' => 'nome',
            'C1' => 'descricao',
            'D1' => 'preco',
            'E1' => 'variacoes',
            'F1' => 'adicionais',
            'G1' => 'foto_url',
            'H1' => 'ativo',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Estilo do header
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'EA1D2C'], // Vermelho YumGo
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Largura das colunas
        $sheet->getColumnDimension('A')->setWidth(15); // categoria
        $sheet->getColumnDimension('B')->setWidth(30); // nome
        $sheet->getColumnDimension('C')->setWidth(40); // descricao
        $sheet->getColumnDimension('D')->setWidth(12); // preco
        $sheet->getColumnDimension('E')->setWidth(25); // variacoes
        $sheet->getColumnDimension('F')->setWidth(25); // adicionais
        $sheet->getColumnDimension('G')->setWidth(50); // foto_url
        $sheet->getColumnDimension('H')->setWidth(10); // ativo

        // Exemplos (3 linhas) - Note: Aspas duplas são adicionadas automaticamente pelo Excel ao salvar como CSV
        $examples = [
            ['Pizzas', 'Pizza Mussarela', 'Molho de tomate, mussarela, orégano', '35,00', 'P:30.00,M:35.00,G:45.00', 'Borda:5.00,Catupiry:3.00', 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=800', 'sim'],
            ['Bebidas', 'Coca-Cola 2L', 'Refrigerante sabor cola', '10,00', '', '', 'https://images.unsplash.com/photo-1600271886742-f049cd451bba?w=800', 'sim'],
            ['Marmitex', 'Marmitex Executivo', 'Arroz, feijão, carne, salada', '25,00', 'P:20.00,M:25.00,G:30.00', 'Frango:0.00,Carne:5.00,Peixe:8.00', '', 'sim'],
        ];

        $row = 2;
        foreach ($examples as $example) {
            $col = 'A';
            foreach ($example as $value) {
                $sheet->setCellValue($col . $row, $value);
                $col++;
            }
            $row++;
        }

        // Estilo dos exemplos (cinza claro)
        $sheet->getStyle('A2:H4')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F3F4F6'],
            ],
        ]);

        // Adiciona nota explicativa
        $sheet->setCellValue('A6', '💡 INSTRUÇÕES:');
        $sheet->setCellValue('A7', '1. As linhas 2-4 são EXEMPLOS. Você pode apagá-las.');
        $sheet->setCellValue('A8', '2. Preencha seus produtos a partir da linha 5 (ou substitua os exemplos).');
        $sheet->setCellValue('A9', '3. Colunas obrigatórias: categoria, nome, preco');
        $sheet->setCellValue('A10', '4. Formato de variações: Nome:Preço,Nome:Preço (ex: P:30.00,M:35.00)');
        $sheet->setCellValue('A11', '5. Formato de adicionais: Nome:Preço,Nome:Preço (ex: Borda:5.00)');
        $sheet->setCellValue('A12', '6. ⚠️ Se exportar para CSV: Use aspas duplas em variações/adicionais!');
        $sheet->setCellValue('A13', '7. Fotos: URL pública (Imgur, Dropbox) - iFood não funciona');
        $sheet->setCellValue('A14', '8. Ativo: escreva "sim" ou "não"');

        $sheet->getStyle('A6:A14')->applyFromArray([
            'font' => [
                'italic' => true,
                'color' => ['rgb' => '6B7280'],
                'size' => 10,
            ],
        ]);

        $sheet->mergeCells('A6:H6');
        $sheet->mergeCells('A7:H7');
        $sheet->mergeCells('A8:H8');
        $sheet->mergeCells('A9:H9');
        $sheet->mergeCells('A10:H10');
        $sheet->mergeCells('A11:H11');
        $sheet->mergeCells('A12:H12');
        $sheet->mergeCells('A13:H13');
        $sheet->mergeCells('A14:H14');

        // Gera arquivo
        $writer = new Xlsx($spreadsheet);
        $filename = 'modelo-importacao-produtos-yumgo.xlsx';

        // Output
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}
