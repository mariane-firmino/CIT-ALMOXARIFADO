<?php
/**
 * View: produtos/etiquetaProduto
 * Página independente (sem menu/rodapé) só com a etiqueta; abre já na impressão.
 *
 * Espera em $dados['produto']: prod_id, prod_nome.
 */
$e = fn($valor) => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');

$produto = $dados['produto'];
$urlQr   = URL . '/produtos/qrcode/' . (int) $produto->prod_id;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Etiqueta - <?= $e($produto->prod_nome) ?></title>

    <style>
        .etiq-pagina {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            color: #000;
        }

        .etiq-etiqueta {
            display: flex;
            align-items: center;
            gap: 4mm;
            width: 80mm;
            box-sizing: border-box;
            padding: 4mm;
            border: 1px dashed #999;
        }

        .etiq-qr {
            width: 30mm;
            height: 30mm;
            flex-shrink: 0;
            image-rendering: pixelated;
        }

        .etiq-nome {
            margin: 0 0 2mm;
            font-size: 12pt;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .etiq-codigo {
            margin: 0;
            font-size: 10pt;
        }

        @media print {
            .etiq-pagina {
                padding: 0;
            }

            .etiq-etiqueta {
                border: none;
            }
        }
    </style>
</head>
<body class="etiq-pagina">

    <div class="etiq-etiqueta">
        <img src="<?= $e($urlQr) ?>" alt="QR Code" class="etiq-qr">
        <div>
            <p class="etiq-nome"><?= $e($produto->prod_nome) ?></p>
            <p class="etiq-codigo">Código: <?= (int) $produto->prod_id ?></p>
        </div>
    </div>

    <script>
        // Espera o QR carregar antes de abrir a impressão
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>