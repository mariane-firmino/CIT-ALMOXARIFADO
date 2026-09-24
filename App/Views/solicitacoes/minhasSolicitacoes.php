<?php
include "../App/Views/menu.php";

/**
 * Dados esperados do Controller (Solicitacoes::solicitacaoServidor):
 *  $dados['solicitacoes']  array de objetos (soli_id, soli_status, soli_data_solicitacao,
 *                          produtos, quantidade_total)
 *  $dados['resumo']        array ['Aprovada', 'Pendente', 'Negada', 'Devolvido', ...] => quantidade
 *  $dados['opcoesStatus']  array de status disponíveis no filtro
 *  $dados['filtros']       array [pesquisa, status, data]
 *  $dados['paginacao']     array [total, paginaAtual, totalPaginas, inicio, fim]
 *  $dados['sucesso']       string|null - mensagem de sucesso (flash)
 *  $dados['erro']          string|null - mensagem de erro (flash)
 */

$e = static fn($valor): string => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');

$solicitacoes = $dados['solicitacoes'];
$resumo       = $dados['resumo'];
$opcoesStatus = $dados['opcoesStatus'];
$filtros      = $dados['filtros'];
$paginacao    = $dados['paginacao'];

$classesStatus = [
    'Pendente'  => 'msol-status--pendente',
    'Aprovada'  => 'msol-status--aprovada',
    'Negada'    => 'msol-status--negada',
    'Devolvido' => 'msol-status--devolvido',
    'Cancelada' => 'msol-status--cancelada',
    'Em devolução' => 'msol-status--devolucao',
];

// Cartões de resumo
$cartoes = [
    ['valor' => $resumo['Aprovada'],  'rotulo' => 'Aprovados',  'texto' => 'Solicitações aprovadas', 'icone' => 'bi-check-circle text-success', 'cor' => 'msol-resumo-icone--aprovada'],
    ['valor' => $resumo['Pendente'],  'rotulo' => 'Pendentes',  'texto' => 'Aguardando análise',     'icone' => 'bi-exclamation-triangle text-alert',    'cor' => 'msol-resumo-icone--pendente'],
    ['valor' => $resumo['Negada'],    'rotulo' => 'Negadas',    'texto' => 'Solicitações negadas',   'icone' => 'bi-x-circle-fill text-danger',               'cor' => 'msol-resumo-icone--negada'],
    ['valor' => $resumo['Devolvido'], 'rotulo' => 'Devolvidos', 'texto' => 'Produtos devolvidos',    'icone' => 'bi-arrow-return-left text-primary',  'cor' => 'msol-resumo-icone--devolvido'],
];

// Links da paginação preservam os filtros ativos.
$urlBase       = URL . '/solicitacoes/solicitacaoServidor';
$filtrosAtivos = array_filter($filtros, static fn($valor) => $valor !== '');
$urlPagina     = static fn(int $pagina): string =>
$urlBase . '?' . http_build_query(array_merge($filtrosAtivos, ['pagina' => $pagina]));

// Janela de 5 páginas ao redor da atual.
$paginaInicial = max(1, $paginacao['paginaAtual'] - 2);
$paginaFinal   = min($paginacao['totalPaginas'], $paginacao['paginaAtual'] + 2);
?>
<main class="msol-pagina">

    <header class="msol-cabecalho">
        <div class="msol-cabecalho-texto">
            <div class="msol-titulo-linha">
                <span class="msol-titulo-marca"></span>
                <h1 class="msol-titulo">Minhas solicitações</h1>
            </div>
            <p class="msol-subtitulo">Acompanhe suas solicitações.</p>
        </div>
        <img class="msol-logo" src="<?= URL ?>/img/logo-sacit.png" alt="SACIT">
    </header>

    <?php if (!empty($dados['sucesso'])): ?>
        <div class="msol-alerta msol-alerta--sucesso" role="status"><?= $e($dados['sucesso']) ?></div>
    <?php endif; ?>
    <?php if (!empty($dados['erro'])): ?>
        <div class="msol-alerta msol-alerta--erro" role="alert"><?= $e($dados['erro']) ?></div>
    <?php endif; ?>

    <!-- Resumo -->
    <section class="msol-resumo" aria-label="Resumo das solicitações">
        <?php foreach ($cartoes as $cartao): ?>
            <article class="msol-resumo-cartao">
                <div class="msol-resumo-topo">
                    <div class="msol-resumo-icone <?= $cartao['cor'] ?>">
                        <i class="bi <?= $cartao['icone'] ?>"></i>
                    </div>
                    <p class="msol-resumo-rotulo"><?= $e($cartao['rotulo']) ?></p>
                </div>
                <p class="msol-resumo-numero"><?= (int) $cartao['valor'] ?></p>
                <span class="msol-resumo-texto"><?= $e($cartao['texto']) ?></span>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="msol-painel">
        <h2 class="msol-painel-titulo">Pesquisar solicitação</h2>

        <!-- Filtros (GET) -->
        <form class="msol-filtros" action="<?= $urlBase ?>" method="GET">
            <div class="msol-busca">
                <i class="bi bi-search"></i>
                <input
                    class="msol-busca-campo"
                    type="search"
                    name="pesquisa"
                    value="<?= $e($filtros['pesquisa']) ?>"
                    placeholder="Buscar por produto ou nº da solicitação..."
                    aria-label="Buscar solicitações">
            </div>

            <select class="msol-select js-msol-filtro-auto" name="status" aria-label="Filtrar por status">
                <option value="">Todos os status</option>
                <?php foreach ($opcoesStatus as $status): ?>
                    <option value="<?= $e($status) ?>" <?= $filtros['status'] === $status ? 'selected' : '' ?>>
                        <?= $e($status) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input
                class="msol-data js-msol-filtro-auto"
                type="date"
                name="data"
                value="<?= $e($filtros['data']) ?>"
                aria-label="Filtrar por data da solicitação">

            <button class="msol-botao msol-botao--primario" type="submit">Pesquisar</button>
        </form>

        <?php if (empty($solicitacoes)): ?>

            <div class="msol-vazio">
                <p class="msol-vazio-titulo">Nenhuma solicitação encontrada</p>
                <p class="msol-vazio-texto">Tente outro termo de busca ou remova algum filtro.</p>
                <a class="msol-botao msol-botao--contorno" href="<?= $urlBase ?>">Limpar filtros</a>
            </div>

        <?php else: ?>

            <div class="msol-tabela-rolagem">
                <table class="msol-tabela">
                    <thead>
                        <tr>
                            <th class="msol-tabela-titulo" scope="col">Código</th>
                            <th class="msol-tabela-titulo" scope="col">Produto(s)</th>
                            <th class="msol-tabela-titulo msol-tabela-titulo--centro" scope="col">Quantidade</th>
                            <th class="msol-tabela-titulo msol-tabela-titulo--centro" scope="col">Data</th>
                            <th class="msol-tabela-titulo" scope="col">Status</th>
                            <th class="msol-tabela-titulo" scope="col">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($solicitacoes as $solicitacao): ?>
                            <?php
                            $classeStatus = $classesStatus[$solicitacao->soli_status] ?? 'msol-status--neutro';
                            $pendente     = $solicitacao->soli_status === 'Pendente';
                            ?>
                            <tr>
                                <td class="msol-tabela-celula">#<?= (int) $solicitacao->soli_id ?></td>

                                <td class="msol-tabela-celula"><?= $e($solicitacao->produtos ?: '—') ?></td>

                                <td class="msol-tabela-celula msol-tabela-celula--centro">
                                    <?= (int) $solicitacao->quantidade_total ?>
                                </td>

                                <td class="msol-tabela-celula msol-tabela-celula--centro">
                                    <?= $e(date('d/m/Y', strtotime($solicitacao->soli_data_solicitacao))) ?>
                                </td>

                                <td class="msol-tabela-celula">
                                    <span class="msol-status <?= $classeStatus ?>"><?= $e($solicitacao->soli_status) ?></span>
                                </td>

                                <td class="msol-tabela-celula">
                                    <div class="msol-acoes">
                                        <a class="msol-botao msol-botao--pequeno msol-botao--primario"
                                            href="<?= URL ?>/solicitacoes/detalharSolicitacao/<?= (int) $solicitacao->soli_id ?>">
                                            Detalhes
                                        </a>

                                        <?php if ($pendente): ?>
                                            <!-- POST: cancelar altera dados, então não pode ser um link comum -->
                                            <form class="msol-acoes-formulario js-msol-cancelar"
                                                action="<?= URL ?>/solicitacoes/cancelar"
                                                method="POST"
                                                data-confirmacao="Deseja realmente cancelar a solicitação #<?= (int) $solicitacao->soli_id ?>?">
                                                <input type="hidden" name="soli_id" value="<?= (int) $solicitacao->soli_id ?>">
                                                <button class="msol-botao msol-botao--pequeno msol-botao--perigo" type="submit">
                                                    Cancelar
                                                </button>
                                                <?php if ($solicitacao->soli_status === 'Aprovada'): ?>
                                                    <form class="msol-acoes-formulario" action="<?= URL ?>/solicitacoes/devolver" method="POST"
                                                        onsubmit="return confirm('Confirmar a devolução da solicitação #<?= (int) $solicitacao->soli_id ?>?');">
                                                        <input type="hidden" name="soli_id" value="<?= (int) $solicitacao->soli_id ?>">
                                                        <button class="msol-botao msol-botao--pequeno msol-botao--sucesso" type="submit">Devolvido</button>
                                                    </form>
                                                <?php endif; ?>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <footer class="msol-rodape">
                <p class="msol-rodape-info">
                    Mostrando <?= $paginacao['inicio'] ?> a <?= $paginacao['fim'] ?>
                    de <?= $paginacao['total'] ?> solicitações
                </p>

                <?php if ($paginacao['totalPaginas'] > 1): ?>
                    <nav class="msol-paginacao" aria-label="Paginação">
                        <?php if ($paginacao['paginaAtual'] > 1): ?>
                            <a class="msol-pagina-link" href="<?= $e($urlPagina($paginacao['paginaAtual'] - 1)) ?>" aria-label="Página anterior">&#8249;</a>
                        <?php endif; ?>

                        <?php for ($i = $paginaInicial; $i <= $paginaFinal; $i++): ?>
                            <a class="msol-pagina-link <?= $i === $paginacao['paginaAtual'] ? 'msol-pagina-link--ativa' : '' ?>"
                                href="<?= $e($urlPagina($i)) ?>"
                                <?= $i === $paginacao['paginaAtual'] ? 'aria-current="page"' : '' ?>>
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($paginacao['paginaAtual'] < $paginacao['totalPaginas']): ?>
                            <a class="msol-pagina-link" href="<?= $e($urlPagina($paginacao['paginaAtual'] + 1)) ?>" aria-label="Próxima página">&#8250;</a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            </footer>

        <?php endif; ?>
    </section>
</main>

<script src="<?= URL ?>/js/solicitacao-servidor.js" defer></script>
<?php include "../App/Views/footer.php"; ?>