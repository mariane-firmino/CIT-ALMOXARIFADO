<?php
include "../App/Views/menu.php";

/**
 * Dados esperados do Controller (Solicitacoes::detalharSolicitacao):
 *  $dados['solicitacao']  objeto (soli_id, soli_status, soli_data_solicitacao, soli_dth_retirada,
 *                         soli_dth_devolucao, soli_observacao, coord_nome)
 *  $dados['itens']        array de objetos (prod_nome, item_quantidade)
 *  $dados['podeDevolver'] bool - true quando a solicitação está aprovada
 *  $dados['sucesso']      string|null
 *  $dados['erro']         string|null
 */

$e = static fn($valor): string => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');

$solicitacao  = $dados['solicitacao'];
$itens        = $dados['itens'];
$podeDevolver = $dados['podeDevolver'];

$formatarData = static fn(?string $data, string $formato): string =>
$data ? date($formato, strtotime($data)) : '—';

$classesStatus = [
    'Pendente'     => 'vsol-status--pendente',
    'Aprovada'     => 'vsol-status--aprovada',
    'Negada'       => 'vsol-status--negada',
    'Em devolução' => 'vsol-status--devolucao',
    'Devolvido'    => 'vsol-status--devolvido',
    'Cancelada'    => 'vsol-status--cancelada',
];
$classeStatus = $classesStatus[$solicitacao->soli_status] ?? 'vsol-status--neutro';

// Datas definidas pelo coordenador só existem depois da aprovação.
$temDatas = in_array($solicitacao->soli_status, ['Aprovada', 'Em devolução', 'Devolvido'], true);
?>
<main class="vsol-pagina">

    <header class="vsol-cabecalho">
        <div class="vsol-cabecalho-texto">
            <div class="vsol-titulo-linha">
                <span class="vsol-titulo-marca"></span>
                <h1 class="vsol-titulo">Detalhes da solicitação</h1>
            </div>
            <p class="vsol-subtitulo">Acompanhe o andamento da sua solicitação.</p>
        </div>
        <img class="vsol-logo" src="<?= URL ?>/img/logo-sacit.png" alt="SACIT">
    </header>

    <nav class="page-breadcrumb" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?= URL ?>/solicitacoes/solicitacaoServidor">Minhas Solicitações</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                Ver Detalhes
            </li>
        </ol>
    </nav>

    <?php if (!empty($dados['sucesso'])): ?>
        <div class="vsol-alerta vsol-alerta--sucesso" role="status"><?= $e($dados['sucesso']) ?></div>
    <?php endif; ?>
    <?php if (!empty($dados['erro'])): ?>
        <div class="vsol-alerta vsol-alerta--erro" role="alert"><?= $e($dados['erro']) ?></div>
    <?php endif; ?>

    <section class="vsol-painel">

        <header class="vsol-painel-cabecalho">
            <h2 class="vsol-painel-titulo">Solicitação</h2>
            <p class="vsol-codigo">
                <span class="vsol-codigo-rotulo">Código:</span>
                <strong class="vsol-codigo-valor">#<?= (int) $solicitacao->soli_id ?></strong>
            </p>
        </header>

        <div class="vsol-painel-corpo">

            <div class="vsol-resumo">
                <div class="vsol-resumo-item">
                    <span class="vsol-resumo-rotulo">Data e hora</span>
                    <span class="vsol-resumo-valor"><?= $e($formatarData($solicitacao->soli_data_solicitacao, 'd/m/Y H:i')) ?></span>
                </div>
                <div class="vsol-resumo-item">
                    <span class="vsol-resumo-rotulo">Situação</span>
                    <span class="vsol-status <?= $classeStatus ?>"><?= $e($solicitacao->soli_status) ?></span>
                </div>
                <?php if (!empty($solicitacao->coord_nome)): ?>
                    <div class="vsol-resumo-item">
                        <span class="vsol-resumo-rotulo">Analisada por</span>
                        <span class="vsol-resumo-valor"><?= $e($solicitacao->coord_nome) ?></span>
                    </div>
                <?php endif; ?>
            </div>

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
                                        <td class="vsol-tabela-celula vsol-tabela-celula--centro"><?= (int) $item->item_quantidade ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($temDatas): ?>
                <div class="vsol-decisao">
                    <h3 class="vsol-secao-titulo">Prazos definidos pelo coordenador</h3>
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
                </div>
            <?php elseif ($solicitacao->soli_status === 'Pendente'): ?>
                <p class="vsol-subtitulo">Aguardando análise do coordenador.</p>
            <?php endif; ?>

            <?php if (!empty($solicitacao->soli_observacao)): ?>
                <div class="vsol-resumo-item">
                    <span class="vsol-resumo-rotulo">Observação do coordenador</span>
                    <span class="vsol-resumo-valor"><?= nl2br($e($solicitacao->soli_observacao)) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($podeDevolver): ?>
                <form class="vsol-formulario"
                    action="<?= URL ?>/solicitacoes/devolver"
                    method="POST"
                    onsubmit="return confirm('Confirmar que você devolveu os itens desta solicitação?');">
                    <input type="hidden" name="soli_id" value="<?= (int) $solicitacao->soli_id ?>">
                    <div class="vsol-acoes">
                        <button class="vsol-botao vsol-botao--sucesso" type="submit">Devolvido</button>
                    </div>
                </form>
            <?php elseif ($solicitacao->soli_status === 'Em devolução'): ?>
                <p class="vsol-subtitulo">Devolução informada. Aguardando a confirmação do coordenador.</p>
            <?php endif; ?>

            <a class="vsol-voltar" href="<?= URL ?>/solicitacoes/solicitacaoServidor">Voltar para minhas solicitações</a>
        </div>
    </section>
</main>
<?php include "../App/Views/footer.php"; ?>