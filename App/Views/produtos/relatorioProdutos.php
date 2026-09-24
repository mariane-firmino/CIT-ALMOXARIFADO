<?php
/**
 * View (documento para o Dompdf): produtos/relatorioProdutos
 * NÃO inclui menu/footer do site: é um HTML completo, capturado pelo Controller.
 *
 * Espera em $dados: categoriaNome (?string), produtos (array), resumo (objeto: total,
 * disponiveis, baixo, esgotados, unidades), emitidoEm, emissor, instituicao, sistema, logo.
 */

$e = fn($valor) => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');

$emissorNome   = $dados['emissor']->usua_nome ?? '—';
$emissorFuncao = $dados['emissor']->func_nome ?? '';

$resumo         = $dados['resumo'];
$escopo         = $dados['categoriaNome'] ?? 'Todas as categorias';
$agrupar        = $dados['categoriaNome'] === null; // "todas" => agrupa por categoria
$secao          = 1;
$categoriaAtual = null;
$n              = 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de produtos</title>
    <style>
        @page { margin: 118px 42px 72px 42px; }

        .rel-corpo {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9pt;
            line-height: 1.45;
            color: #1c1c1c;
        }

        /* ---------- Cabeçalho e rodapé (repetem em todas as páginas) ---------- */
        .rel-cabecalho {
            position: fixed;
            top: -100px;
            left: 0;
            right: 0;
            height: 82px;
            border-bottom: 2px solid #0a7f70;
        }
        .rel-cabecalho-tabela { width: 100%; border-collapse: collapse; }
        .rel-cabecalho-logo   { width: 70px; vertical-align: middle; }
        .rel-cabecalho-texto  { vertical-align: middle; padding-left: 10px; }
        .rel-cabecalho-lado   { width: 150px; vertical-align: middle; text-align: right; font-size: 8pt; color: #444; }
        .rel-instituicao { font-size: 11pt; font-weight: bold; color: #0a7f70; }
        .rel-sistema     { font-size: 8.5pt; color: #444; }

        .rel-rodape {
            position: fixed;
            bottom: -52px;
            left: 0;
            right: 0;
            height: 30px;
            padding-top: 6px;
            border-top: 1px solid #9aa5a1;
            font-size: 7.5pt;
            color: #555;
        }
        .rel-rodape-texto { width: 68%; }

        /* ---------- Título e identificação ---------- */
        .rel-titulo-bloco { text-align: center; margin: 4px 0 16px 0; }
        .rel-titulo    { font-size: 14pt; font-weight: bold; letter-spacing: 1px; color: #13231d; }
        .rel-subtitulo { font-size: 10pt; color: #444; margin-top: 3px; }

        .rel-ident { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .rel-ident-rotulo {
            width: 16%;
            padding: 5px 8px;
            background: #eef4f2;
            border: 1px solid #c9d6d1;
            font-weight: bold;
            font-size: 8pt;
            color: #333;
        }
        .rel-ident-valor { width: 34%; padding: 5px 8px; border: 1px solid #c9d6d1; font-size: 8.5pt; }

        /* ---------- Seções ---------- */
        .rel-secao {
            margin: 20px 0 8px 0;
            padding-bottom: 3px;
            border-bottom: 1px solid #0a7f70;
            font-size: 10.5pt;
            font-weight: bold;
            color: #0a7f70;
            page-break-after: avoid;
        }
        .rel-nota  { margin: 0 0 8px 0; font-size: 8pt; color: #555; }
        .rel-vazio { padding: 10px; border: 1px dashed #b8c5c0; text-align: center; color: #666; font-size: 8.5pt; }

        /* ---------- Tabelas de dados ---------- */
        .rel-tabela { width: 100%; border-collapse: collapse; }
        .rel-th {
            padding: 5px 6px;
            background: #0a7f70;
            border: 1px solid #0a7f70;
            color: #ffffff;
            font-size: 7.5pt;
            text-align: left;
        }
        .rel-td {
            padding: 4px 6px;
            border: 1px solid #c9d6d1;
            font-size: 7.5pt;
            vertical-align: top;
        }
        .rel-grupo {
            padding: 4px 6px;
            background: #dfeae6;
            border: 1px solid #c9d6d1;
            font-size: 8pt;
            font-weight: bold;
            color: #0a5f54;
        }
        .rel-linha { page-break-inside: avoid; }
        .rel-linha-par .rel-td { background: #f5f8f7; }
        .rel-centro { text-align: center; }
        .rel-destaque { font-weight: bold; }
        .rel-muted { color: #666; font-size: 7pt; }

        /* Resumo (indicador | valor) */
        .rel-resumo-rotulo { width: 70%; padding: 5px 8px; border: 1px solid #c9d6d1; font-size: 8.5pt; }
        .rel-resumo-valor  { width: 30%; padding: 5px 8px; border: 1px solid #c9d6d1; font-size: 8.5pt; font-weight: bold; text-align: right; }

        /* ---------- Assinaturas ---------- */
        .rel-assinaturas { width: 100%; border-collapse: collapse; margin-top: 46px; page-break-inside: avoid; }
        .rel-assinatura  { width: 50%; padding: 0 22px; text-align: center; font-size: 8pt; }
        .rel-linha-assinatura { border-top: 1px solid #333; padding-top: 4px; }
        .rel-fecho { margin-top: 26px; font-size: 7.5pt; color: #666; text-align: center; }
    </style>
</head>
<body class="rel-corpo">

    <!-- Cabeçalho fixo -->
    <div class="rel-cabecalho">
        <table class="rel-cabecalho-tabela">
            <tr>
                <td class="rel-cabecalho-logo">
                    <?php if (!empty($dados['logo'])): ?>
                        <img src="<?= $e($dados['logo']) ?>" height="48" alt="">
                    <?php endif; ?>
                </td>
                <td class="rel-cabecalho-texto">
                    <div class="rel-instituicao"><?= $e($dados['instituicao']) ?></div>
                    <div class="rel-sistema"><?= $e($dados['sistema']) ?></div>
                </td>
                <td class="rel-cabecalho-lado">
                    Relatório de produtos<br>
                    <strong><?= $e($dados['emitidoEm']) ?></strong>
                </td>
            </tr>
        </table>
    </div>

    <!-- Rodapé fixo (a numeração de páginas é desenhada pelo Controller depois do render) -->
    <div class="rel-rodape">
        <div class="rel-rodape-texto">
            <?= $e($dados['sistema']) ?> · Emitido em <?= $e($dados['emitidoEm']) ?> por <?= $e($emissorNome) ?>
        </div>
    </div>

    <!-- Título -->
    <div class="rel-titulo-bloco">
        <div class="rel-titulo">RELATÓRIO DE PRODUTOS EM ESTOQUE</div>
        <div class="rel-subtitulo"><?= $e($escopo) ?></div>
    </div>

    <!-- Identificação -->
    <table class="rel-ident">
        <tr>
            <td class="rel-ident-rotulo">Categoria</td>
            <td class="rel-ident-valor"><?= $e($escopo) ?></td>
            <td class="rel-ident-rotulo">Emitido em</td>
            <td class="rel-ident-valor"><?= $e($dados['emitidoEm']) ?></td>
        </tr>
        <tr>
            <td class="rel-ident-rotulo">Emitido por</td>
            <td class="rel-ident-valor" colspan="3">
                <?= $e($emissorNome) ?><?= $emissorFuncao !== '' ? ' (' . $e($emissorFuncao) . ')' : '' ?>
            </td>
        </tr>
    </table>

    <!-- Resumo -->
    <div class="rel-secao"><?= $secao++ ?>. Resumo</div>
    <table class="rel-tabela">
        <tr><td class="rel-resumo-rotulo">Produtos listados</td><td class="rel-resumo-valor"><?= (int) $resumo->total ?></td></tr>
        <tr><td class="rel-resumo-rotulo">&nbsp;&nbsp;Disponíveis</td><td class="rel-resumo-valor"><?= (int) $resumo->disponiveis ?></td></tr>
        <tr><td class="rel-resumo-rotulo">&nbsp;&nbsp;Com estoque baixo</td><td class="rel-resumo-valor"><?= (int) $resumo->baixo ?></td></tr>
        <tr><td class="rel-resumo-rotulo">&nbsp;&nbsp;Esgotados</td><td class="rel-resumo-valor"><?= (int) $resumo->esgotados ?></td></tr>
        <tr><td class="rel-resumo-rotulo">Total de unidades em estoque (soma das quantidades)</td><td class="rel-resumo-valor"><?= (int) $resumo->unidades ?></td></tr>
    </table>

    <!-- Relação de produtos -->
    <div class="rel-secao"><?= $secao++ ?>. Relação de produtos</div>
    <p class="rel-nota">
        <?= $agrupar ? 'Agrupados por categoria e ordenados por nome.' : 'Ordenados por nome.' ?>
        Quantidade e situação são as vigentes no momento da emissão do relatório.
    </p>

    <?php if (empty($dados['produtos'])): ?>
        <div class="rel-vazio">Nenhum produto encontrado.</div>
    <?php else: ?>
        <table class="rel-tabela">
            <thead>
                <tr>
                    <th class="rel-th" style="width: 8%;">Cód.</th>
                    <th class="rel-th" style="width: 36%;">Produto</th>
                    <th class="rel-th" style="width: 22%;">Localização</th>
                    <th class="rel-th rel-centro" style="width: 10%;">Estoque</th>
                    <th class="rel-th rel-centro" style="width: 10%;">Mínimo</th>
                    <th class="rel-th" style="width: 14%;">Situação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dados['produtos'] as $produto): ?>

                    <?php if ($agrupar && $produto->cate_nome !== $categoriaAtual): ?>
                        <?php $categoriaAtual = $produto->cate_nome; $n = 0; ?>
                        <tr>
                            <td class="rel-grupo" colspan="6"><?= $e($categoriaAtual) ?></td>
                        </tr>
                    <?php endif; ?>

                    <tr class="rel-linha <?= $n++ % 2 ? 'rel-linha-par' : '' ?>">
                        <td class="rel-td"><?= (int) $produto->prod_id ?></td>
                        <td class="rel-td"><?= $e($produto->prod_nome) ?></td>
                        <td class="rel-td"><?= $e($produto->loca_nome ?: '—') ?></td>
                        <td class="rel-td rel-centro"><?= (int) $produto->prod_quantidade ?></td>
                        <td class="rel-td rel-centro"><?= (int) $produto->prod_estoque_minimo ?></td>
                        <td class="rel-td <?= $produto->prod_status !== 'Disponível' ? 'rel-destaque' : '' ?>">
                            <?= $e($produto->prod_status ?: '—') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Assinaturas -->
    <table class="rel-assinaturas">
        <tr>
            <td class="rel-assinatura">
                <div class="rel-linha-assinatura">
                    <?= $e($emissorNome) ?><br>
                    <span class="rel-muted"><?= $e($emissorFuncao !== '' ? $emissorFuncao : 'Responsável pela emissão') ?></span>
                </div>
            </td>
            <td class="rel-assinatura">
                <div class="rel-linha-assinatura">
                    Ciência da Coordenação<br>
                    <span class="rel-muted">Data: ____ / ____ / ________</span>
                </div>
            </td>
        </tr>
    </table>

    <div class="rel-fecho">
        Documento gerado eletronicamente pelo <?= $e($dados['sistema']) ?> em <?= $e($dados['emitidoEm']) ?>.
        Reflete os registros do sistema na data e hora de emissão.
    </div>

</body>
</html>