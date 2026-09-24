<?php
include "../App/Views/menu.php";

/**
 * Dados esperados do Controller (Solicitacoes::verSolicitacao):
 *  $dados['solicitacao']  objeto (soli_id, codigo, usua_nome, soli_data_solicitacao, soli_status,
 *                         soli_data_retirada, soli_data_devolucao, soli_observacao)
 *  $dados['itens']        array de objetos (prod_nome, item_quantidade)
 *  $dados['podeAnalisar'] bool - true quando a solicitação ainda está pendente
 *  $dados['erro']         string|null - mensagem de erro (flash)
 */

$e = static fn($valor): string => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');

$solicitacao  = $dados['solicitacao'];
$itens        = $dados['itens'];
$podeAnalisar = $dados['podeAnalisar'];
$erro         = $dados['erro'] ?? null;

$formatarData = static fn(?string $data, string $formato): string =>
$data ? date($formato, strtotime($data)) : '—';

$podeConfirmarDevolucao = $dados['podeConfirmarDevolucao'] ?? false;

$classesStatus = [
    'Pendente'     => 'vsol-status--pendente',
    'Aprovada'     => 'vsol-status--aprovada',
    'Negada'       => 'vsol-status--negada',
    'Em devolução' => 'vsol-status--devolucao',
    'Devolvido'    => 'vsol-status--devolvido',
    'Cancelada'    => 'vsol-status--cancelada',
];
$classeStatus = $classesStatus[$solicitacao->soli_status] ?? 'vsol-status--neutro'; // antes caía em "pendente"
$classeStatus = $classesStatus[$solicitacao->soli_status] ?? 'vsol-status--pendente';
?>
<main class="vsol-pagina">

    <header class="vsol-cabecalho">
        <div class="vsol-cabecalho-texto">
            <div class="vsol-titulo-linha">
                <span class="vsol-titulo-marca"></span>
                <h1 class="vsol-titulo">Analisar solicitação</h1>
            </div>
            <p class="vsol-subtitulo">Gerencie e analise as solicitações dos usuários.</p>
        </div>
        <img class="vsol-logo" src="<?= URL ?>/img/logo-sacit.png" alt="SACIT">
    </header>

    <nav class="page-breadcrumb" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?= URL ?>/solicitacoes/analisarSolicitacao">Analisar Solicitação</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                Detalhes da Solicitação
            </li>
        </ol>
    </nav>

    <?php if (!empty($erro)): ?>
        <div class="vsol-alerta vsol-alerta--erro" role="alert"><?= $e($erro) ?></div>
    <?php endif; ?>

    <section class="vsol-painel">

        <header class="vsol-painel-cabecalho">
            <h2 class="vsol-painel-titulo">Detalhe da solicitação</h2>
            <p class="vsol-codigo">
                <span class="vsol-codigo-rotulo">Código:</span>
                <strong class="vsol-codigo-valor">#<?= $e($solicitacao->codigo) ?></strong>
            </p>
        </header>

        <div class="vsol-painel-corpo">

            <!-- Resumo -->
            <div class="vsol-resumo">
                <div class="vsol-resumo-item">
                    <span class="vsol-resumo-rotulo">Solicitante</span>
                    <span class="vsol-resumo-valor"><?= $e($solicitacao->usua_nome) ?></span>
                </div>

                <div class="vsol-resumo-item">
                    <span class="vsol-resumo-rotulo">Data e hora</span>
                    <span class="vsol-resumo-valor">
                        <?= $e($formatarData($solicitacao->soli_data_solicitacao, 'd/m/Y H:i')) ?>
                    </span>
                </div>

                <div class="vsol-resumo-item">
                    <span class="vsol-resumo-rotulo">Situação</span>
                    <span class="vsol-status <?= $classeStatus ?>"><?= $e($solicitacao->soli_status) ?></span>
                </div>
            </div>

            <!-- Produtos -->
            <div class="vsol-secao">
                <h3 class="vsol-secao-titulo">Produtos solicitados</h3>

                <div class="vsol-tabela-rolagem">
                    <table class="vsol-tabela">
                        <thead>
                            <tr>
                                <th class="vsol-tabela-titulo" scope="col">Produto</th>
                                <th class="vsol-tabela-titulo vsol-tabela-titulo--centro" scope="col">Quantidade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($itens)): ?>
                                <tr>
                                    <td class="vsol-tabela-celula vsol-tabela-celula--vazia" colspan="2">
                                        Nenhum item nesta solicitação.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($itens as $item): ?>
                                    <tr>
                                        <td class="vsol-tabela-celula"><?= $e($item->prod_nome) ?></td>
                                        <td class="vsol-tabela-celula vsol-tabela-celula--centro">
                                            <?= (int) $item->item_quantidade ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($podeAnalisar): ?>

                <!-- Formulário de análise -->
                <form class="vsol-formulario js-vsol-formulario"
                    action="<?= URL ?>/solicitacoes/processarSolicitacao"
                    method="POST">

                    <input type="hidden" name="soli_id" value="<?= (int) $solicitacao->soli_id ?>">

                    <div class="vsol-campos">
                        <div class="vsol-campo">
                            <label class="vsol-rotulo" for="vsol-data-retirada">Data para retirada</label>
                            <input
                                class="vsol-entrada js-vsol-retirada"
                                id="vsol-data-retirada"
                                type="date"
                                name="data_retirada"
                                min="<?= date('Y-m-d') ?>"
                                required>
                        </div>

                        <div class="vsol-campo">
                            <label class="vsol-rotulo" for="vsol-data-devolucao">Data para devolução</label>
                            <input
                                class="vsol-entrada js-vsol-devolucao"
                                id="vsol-data-devolucao"
                                type="date"
                                name="data_devolucao"
                                min="<?= date('Y-m-d') ?>"
                                required>
                        </div>
                    </div>

                    <div class="vsol-campo">
                        <label class="vsol-rotulo" for="vsol-observacao">Observação</label>
                        <textarea
                            class="vsol-entrada vsol-entrada--texto js-vsol-observacao"
                            id="vsol-observacao"
                            name="observacao"
                            rows="5"
                            maxlength="500"
                            placeholder="Digite uma observação (obrigatória ao negar)..."></textarea>
                    </div>

                    <div class="vsol-acoes">
                        <!-- formnovalidate: negar não exige as datas -->
                        <button
                            class="vsol-botao vsol-botao--perigo js-vsol-acao"
                            type="submit"
                            name="acao"
                            value="Negada"
                            formnovalidate
                            data-confirmacao="Deseja realmente negar esta solicitação?">
                            Negar
                        </button>

                        <button
                            class="vsol-botao vsol-botao--sucesso js-vsol-acao"
                            type="submit"
                            name="acao"
                            value="Aprovada"
                            data-confirmacao="Deseja realmente aprovar esta solicitação?">
                            Aprovar
                        </button>
                    </div>
                </form>

            <?php else: ?>

                <!-- Solicitação já analisada: somente leitura -->
                <div class="vsol-decisao">
                    <h3 class="vsol-secao-titulo">Resultado da análise</h3>

                    <div class="vsol-resumo">
                        <div class="vsol-resumo-item">
                            <span class="vsol-resumo-rotulo">Retirada</span>
                            <span class="vsol-resumo-valor"><?= $e($formatarData($solicitacao->soli_dth_retirada, 'd/m/Y')) ?></span>
                        </div>
                        <div class="vsol-resumo-item">
                            <span class="vsol-resumo-rotulo">Devolução</span>
                            <span class="vsol-resumo-valor"><?= $e($formatarData($solicitacao->soli_dth_devolucao, 'd/m/Y')) ?></span>
                        </div>
                    </div>

                    <div class="vsol-resumo-item">
                        <span class="vsol-resumo-rotulo">Observação</span>
                        <span class="vsol-resumo-valor">
                            <?= !empty($solicitacao->soli_observacao) ? nl2br($e($solicitacao->soli_observacao)) : '—' ?>
                        </span>
                    </div>
                </div>

            <?php endif; ?>
            <?php if ($podeConfirmarDevolucao): ?>
                <form class="vsol-formulario"
                    action="<?= URL ?>/solicitacoes/confirmarDevolucao"
                    method="POST"
                    onsubmit="return confirm('Confirmar o recebimento dos itens desta solicitação?');">
                    <input type="hidden" name="soli_id" value="<?= (int) $solicitacao->soli_id ?>">
                    <div class="vsol-acoes">
                        <button class="vsol-botao vsol-botao--sucesso" type="submit">Confirmar devolução</button>
                    </div>
                </form>
            <?php endif; ?>

            <a class="vsol-voltar" href="<?= URL ?>/solicitacoes/analisarSolicitacao">Voltar para a lista</a>
        </div>
    </section>
</main>

<script src="<?= URL ?>/js/ver-solicitacao.js" defer></script>
<?php include "../App/Views/footer.php"; ?>