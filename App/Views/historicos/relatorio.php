<?php
/**
 * View (documento para o Dompdf): historicos/relatorio
 * NÃO inclui header/menu/footer do site: é um HTML completo, capturado pelo Controller.
 *
 * Dompdf entende CSS 2.1 (sem flex/grid), então o layout usa tabelas e blocos simples.
 * Espera em $dados: tipo, tituloTipo, referencia, periodo, emitidoEm, emissor, instituicao,
 * sistema, logo, mostrarSolicitacoes/Produtos/Eventos, resumoSolicitacoes,
 * solicitacoes, produtos, eventos.
 */

$e = fn($valor) => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');

$dataHora = static fn($valor): string => $valor ? date('d/m/Y H:i', strtotime($valor)) : '—';
$soData   = static fn($valor): string => $valor ? date('d/m/Y', strtotime($valor)) : '—';

$emissorNome   = $dados['emissor']->usua_nome ?? '—';
$emissorFuncao = $dados['emissor']->func_nome ?? '';

$resumo = $dados['resumoSolicitacoes'];
$secao  = 1; // numeração das seções
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório mensal · <?= $e($dados['referencia']) ?></title>
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
        .rel-linha { page-break-inside: avoid; }
        .rel-linha-par .rel-td { background: #f5f8f7; }
        .rel-centro { text-align: center; }
        .rel-direita { text-align: right; }
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
                        <img src="<?= $dados['logo'] ?>" height="48" alt="">
                    <?php endif; ?>
                </td>
                <td class="rel-cabecalho-texto">
                    <div class="rel-instituicao"><?= $e($dados['instituicao']) ?></div>
                    <div class="rel-sistema"><?= $e($dados['sistema']) ?></div>
                </td>
                <td class="rel-cabecalho-lado">
                    Relatório mensal<br>
                    <strong><?= $e($dados['referencia']) ?></strong>
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
        <div class="rel-titulo">RELATÓRIO MENSAL DE ATIVIDADES</div>
        <div class="rel-subtitulo"><?= $e($dados['tituloTipo']) ?></div>
    </div>

    <!-- Identificação -->
    <table class="rel-ident">
        <tr>
            <td class="rel-ident-rotulo">Referência</td>
            <td class="rel-ident-valor"><?= $e($dados['referencia']) ?></td>
            <td class="rel-ident-rotulo">Período</td>
            <td class="rel-ident-valor"><?= $e($dados['periodo']) ?></td>
        </tr>
        <tr>
            <td class="rel-ident-rotulo">Emitido em</td>
            <td class="rel-ident-valor"><?= $e($dados['emitidoEm']) ?></td>
            <td class="rel-ident-rotulo">Emitido por</td>
            <td class="rel-ident-valor">
                <?= $e($emissorNome) ?><?= $emissorFuncao !== '' ? ' (' . $e($emissorFuncao) . ')' : '' ?>
            </td>
        </tr>
    </table>

    <!-- Resumo do período -->
    <div class="rel-secao"><?= $secao++ ?>. Resumo do período</div>
    <table class="rel-tabela">
        <?php if ($dados['mostrarSolicitacoes'] && $resumo): ?>
            <tr><td class="rel-resumo-rotulo">Solicitações registradas</td><td class="rel-resumo-valor"><?= (int) $resumo->total ?></td></tr>
            <tr><td class="rel-resumo-rotulo">&nbsp;&nbsp;Aprovadas</td><td class="rel-resumo-valor"><?= (int) $resumo->aprovadas ?></td></tr>
            <tr><td class="rel-resumo-rotulo">&nbsp;&nbsp;Negadas</td><td class="rel-resumo-valor"><?= (int) $resumo->negadas ?></td></tr>
            <tr><td class="rel-resumo-rotulo">&nbsp;&nbsp;Pendentes</td><td class="rel-resumo-valor"><?= (int) $resumo->pendentes ?></td></tr>
            <tr><td class="rel-resumo-rotulo">&nbsp;&nbsp;Em devolução</td><td class="rel-resumo-valor"><?= (int) $resumo->em_devolucao ?></td></tr>
            <tr><td class="rel-resumo-rotulo">Unidades solicitadas (soma dos itens)</td><td class="rel-resumo-valor"><?= (int) $resumo->itens ?></td></tr>
            <tr><td class="rel-resumo-rotulo">Usuários solicitantes distintos</td><td class="rel-resumo-valor"><?= (int) $resumo->solicitantes ?></td></tr>
        <?php endif; ?>
        <?php if ($dados['mostrarProdutos']): ?>
            <tr><td class="rel-resumo-rotulo">Produtos cadastrados no período</td><td class="rel-resumo-valor"><?= count($dados['produtos']) ?></td></tr>
        <?php endif; ?>
        <?php if ($dados['mostrarEventos']): ?>
            <tr><td class="rel-resumo-rotulo">Eventos administrativos registrados</td><td class="rel-resumo-valor"><?= count($dados['eventos']) ?></td></tr>
        <?php endif; ?>
    </table>

    <!-- Solicitações -->
    <?php if ($dados['mostrarSolicitacoes']): ?>
        <div class="rel-secao"><?= $secao++ ?>. Solicitações do período</div>
        <p class="rel-nota">Ordenadas da mais antiga para a mais recente. "Analisado por" fica em branco enquanto a solicitação está pendente.</p>

        <?php if (empty($dados['solicitacoes'])): ?>
            <div class="rel-vazio">Nenhuma solicitação registrada no período.</div>
        <?php else: ?>
            <table class="rel-tabela">
                <thead>
                    <tr>
                        <th class="rel-th" style="width: 6%;">Nº</th>
                        <th class="rel-th" style="width: 13%;">Data/hora</th>
                        <th class="rel-th" style="width: 19%;">Solicitante</th>
                        <th class="rel-th" style="width: 28%;">Itens solicitados</th>
                        <th class="rel-th rel-centro" style="width: 6%;">Qtd.</th>
                        <th class="rel-th" style="width: 12%;">Situação</th>
                        <th class="rel-th" style="width: 16%;">Analisado por</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dados['solicitacoes'] as $i => $solicitacao): ?>
                        <tr class="rel-linha <?= $i % 2 ? 'rel-linha-par' : '' ?>">
                            <td class="rel-td"><?= (int) $solicitacao->soli_id ?></td>
                            <td class="rel-td"><?= $e($dataHora($solicitacao->soli_data_solicitacao)) ?></td>
                            <td class="rel-td">
                                <?= $e($solicitacao->usua_nome) ?>
                                <?php if (!empty($solicitacao->func_nome)): ?>
                                    <br><span class="rel-muted"><?= $e($solicitacao->func_nome) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="rel-td">
                                <?php if (!empty($solicitacao->itens)): ?>
                                    <?= implode('<br>', array_map($e, explode('; ', $solicitacao->itens))) ?>
                                <?php else: ?>
                                    <span class="rel-muted">Sem itens</span>
                                <?php endif; ?>
                            </td>
                            <td class="rel-td rel-centro"><?= (int) $solicitacao->quantidade_total ?></td>
                            <td class="rel-td"><?= $e($solicitacao->soli_status) ?></td>
                            <td class="rel-td"><?= $e($solicitacao->analisado_por ?: '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Produtos cadastrados -->
    <?php if ($dados['mostrarProdutos']): ?>
        <div class="rel-secao"><?= $secao++ ?>. Produtos cadastrados no período</div>
        <p class="rel-nota">Quantidade e situação são as vigentes no momento da emissão do relatório.</p>

        <?php if (empty($dados['produtos'])): ?>
            <div class="rel-vazio">Nenhum produto cadastrado no período.</div>
        <?php else: ?>
            <table class="rel-tabela">
                <thead>
                    <tr>
                        <th class="rel-th" style="width: 6%;">Cód.</th>
                        <th class="rel-th" style="width: 11%;">Cadastro</th>
                        <th class="rel-th" style="width: 24%;">Produto</th>
                        <th class="rel-th" style="width: 18%;">Categoria</th>
                        <th class="rel-th" style="width: 15%;">Localização</th>
                        <th class="rel-th rel-centro" style="width: 6%;">Qtd.</th>
                        <th class="rel-th rel-centro" style="width: 6%;">Mín.</th>
                        <th class="rel-th" style="width: 14%;">Situação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dados['produtos'] as $i => $produto): ?>
                        <tr class="rel-linha <?= $i % 2 ? 'rel-linha-par' : '' ?>">
                            <td class="rel-td"><?= (int) $produto->prod_id ?></td>
                            <td class="rel-td"><?= $e($soData($produto->prod_data_cadastro)) ?></td>
                            <td class="rel-td"><?= $e($produto->prod_nome) ?></td>
                            <td class="rel-td"><?= $e($produto->cate_nome ?: '—') ?></td>
                            <td class="rel-td"><?= $e($produto->loca_nome ?: '—') ?></td>
                            <td class="rel-td rel-centro"><?= (int) $produto->prod_quantidade ?></td>
                            <td class="rel-td rel-centro"><?= (int) $produto->prod_estoque_minimo ?></td>
                            <td class="rel-td"><?= $e($produto->prod_status ?: '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Registro de eventos -->
    <?php if ($dados['mostrarEventos']): ?>
        <div class="rel-secao"><?= $secao++ ?>. Registro de eventos administrativos</div>
        <p class="rel-nota">Cadastros e alterações de usuários, produtos, estoque e sistema, conforme registrado nas notificações. As solicitações estão detalhadas acima.</p>

        <?php if (empty($dados['eventos'])): ?>
            <div class="rel-vazio">Nenhum evento administrativo registrado no período.</div>
        <?php else: ?>
            <table class="rel-tabela">
                <thead>
                    <tr>
                        <th class="rel-th" style="width: 15%;">Data/hora</th>
                        <th class="rel-th" style="width: 12%;">Tipo</th>
                        <th class="rel-th" style="width: 73%;">Descrição</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dados['eventos'] as $i => $evento): ?>
                        <tr class="rel-linha <?= $i % 2 ? 'rel-linha-par' : '' ?>">
                            <td class="rel-td"><?= $e($dataHora($evento->noti_data)) ?></td>
                            <td class="rel-td"><?= $e($evento->noti_tipo) ?></td>
                            <td class="rel-td">
                                <strong><?= $e($evento->noti_titulo) ?></strong><br>
                                <?= $e($evento->noti_mensagem) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
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