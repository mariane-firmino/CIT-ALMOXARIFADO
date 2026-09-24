<?php
include "../App/Views/menu.php";

/**
 * Dados esperados do Controller (Solicitacoes::analisarSolicitacao):
 *  $dados['solicitacoes']  array de objetos (soli_id, soli_status, soli_data_solicitacao,
 *                          usua_nome, usua_email, produtos, quantidade_total)
 *  $dados['resumo']        array ['total', 'Pendente', 'Aprovada', 'Negada', 'Em devolução']
 *  $dados['opcoesStatus']  array [valor => rótulo da aba]
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
    'Pendente'     => 'lsol-status--pendente',
    'Aprovada'     => 'lsol-status--aprovada',
    'Negada'       => 'lsol-status--negada',
    'Em devolução' => 'lsol-status--devolucao',
    'Devolvido' => 'lsol-status--devolvido',
];

// Cartões de resumo (ícone, rótulo, descrição e cor de cada um).
$cartoes = [
    ['valor' => $resumo['total'],        'rotulo' => 'Total de solicitações', 'texto' => 'Todas as solicitações',    'icone' => 'bi-clipboard2-check text-success',      'cor' => 'lsol-resumo-icone--total'],
    ['valor' => $resumo['Em devolução'], 'rotulo' => 'Em devolução',          'texto' => 'Aguardando devolução',     'icone' => 'bi-arrow-return-left text-primary',  'cor' => 'lsol-resumo-icone--devolucao'],
    ['valor' => $resumo['Pendente'],     'rotulo' => 'Pendentes',             'texto' => 'Aguardando análise',       'icone' => 'bi-exclamation-triangle text-warning',    'cor' => 'lsol-resumo-icone--pendente'],
    ['valor' => $resumo['Aprovada'],     'rotulo' => 'Aprovadas',             'texto' => 'Solicitações aprovadas',   'icone' => 'bi-check-circle text-success', 'cor' => 'lsol-resumo-icone--aprovada'],
    ['valor' => $resumo['Negada'],       'rotulo' => 'Negadas',               'texto' => 'Solicitações negadas',     'icone' => 'bi-x-circle-fill text-danger',               'cor' => 'lsol-resumo-icone--negada'],
];

// URLs que preservam os filtros ativos (abas e paginação).
$urlBase       = URL . '/solicitacoes/analisarSolicitacao';
$filtrosAtivos = array_filter($filtros, static fn($valor) => $valor !== '');
$montarUrl     = static fn(array $parametros): string =>
$urlBase . ($parametros ? '?' . http_build_query($parametros) : '');

// Janela de 5 páginas ao redor da atual.
$paginaInicial = max(1, $paginacao['paginaAtual'] - 2);
$paginaFinal   = min($paginacao['totalPaginas'], $paginacao['paginaAtual'] + 2);
?>
<main class="lsol-pagina">

    <header class="lsol-cabecalho">
        <div class="lsol-cabecalho-texto">
            <div class="lsol-titulo-linha">
                <span class="lsol-titulo-marca"></span>
                <h1 class="lsol-titulo">Analisar solicitações</h1>
            </div>
            <p class="lsol-subtitulo">Gerencie e analise as solicitações dos usuários.</p>
        </div>
        <img class="lsol-logo" src="<?= URL ?>/img/logo-sacit.png" alt="SACIT">
    </header>

    <?php if (!empty($dados['sucesso'])): ?>
        <div class="lsol-alerta lsol-alerta--sucesso" role="status"><?= $e($dados['sucesso']) ?></div>
    <?php endif; ?>
    <?php if (!empty($dados['erro'])): ?>
        <div class="lsol-alerta lsol-alerta--erro" role="alert"><?= $e($dados['erro']) ?></div>
    <?php endif; ?>

    <!-- Resumo -->
    <section class="lsol-resumo" aria-label="Resumo das solicitações">
        <?php foreach ($cartoes as $cartao): ?>
            <article class="lsol-resumo-cartao">
                <div class="lsol-resumo-topo">
                    <div class="lsol-resumo-icone <?= $cartao['cor'] ?>">
                        <i class="bi <?= $cartao['icone'] ?>"></i>
                    </div>
                    <p class="lsol-resumo-rotulo"><?= $e($cartao['rotulo']) ?></p>
                </div>
                <p class="lsol-resumo-numero"><?= str_pad((string) (int) $cartao['valor'], 2, '0', STR_PAD_LEFT) ?></p>
                <span class="lsol-resumo-texto"><?= $e($cartao['texto']) ?></span>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="lsol-painel">

        <!-- Filtros (GET) -->
        <form class="lsol-filtros" action="<?= $urlBase ?>" method="GET">
            <div class="lsol-busca">
                <i class="bi bi-search"></i>
                <input
                    class="lsol-busca-campo"
                    type="search"
                    name="pesquisa"
                    value="<?= $e($filtros['pesquisa']) ?>"
                    placeholder="Buscar por usuário, produto ou nº da solicitação..."
                    aria-label="Buscar solicitações">
            </div>

            <select class="lsol-select js-lsol-filtro-auto" name="status" aria-label="Filtrar por status">
                <option value="">Todos os status</option>
                <?php foreach ($opcoesStatus as $valor => $rotulo): ?>
                    <option value="<?= $e($valor) ?>" <?= $filtros['status'] === $valor ? 'selected' : '' ?>>
                        <?= $e($valor) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input
                class="lsol-data js-lsol-filtro-auto"
                type="date"
                name="data"
                value="<?= $e($filtros['data']) ?>"
                aria-label="Filtrar por data da solicitação">

            <button class="lsol-botao lsol-botao--primario" type="submit">Pesquisar</button>
        </form>

        <!-- Abas por status -->
        <nav class="lsol-abas" aria-label="Filtrar por status">
            <a class="lsol-aba <?= $filtros['status'] === '' ? 'lsol-aba--ativa' : '' ?>"
                href="<?= $e($montarUrl(array_diff_key($filtrosAtivos, ['status' => '']))) ?>">
                Todas
            </a>
            <?php foreach ($opcoesStatus as $valor => $rotulo): ?>
                <a class="lsol-aba <?= $filtros['status'] === $valor ? 'lsol-aba--ativa' : '' ?>"
                    href="<?= $e($montarUrl(array_merge($filtrosAtivos, ['status' => $valor]))) ?>">
                    <?= $e($rotulo) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <?php if (empty($solicitacoes)): ?>

            <div class="lsol-vazio">
                <p class="lsol-vazio-titulo">Nenhuma solicitação encontrada</p>
                <p class="lsol-vazio-texto">Tente outro termo de busca ou remova algum filtro.</p>
                <a class="lsol-botao lsol-botao--contorno" href="<?= $urlBase ?>">Limpar filtros</a>
            </div>

        <?php else: ?>

            <div class="lsol-tabela-rolagem">
                <table class="lsol-tabela">
                    <thead>
                        <tr>
                            <th class="lsol-tabela-titulo" scope="col">Código</th>
                            <th class="lsol-tabela-titulo" scope="col">Usuário</th>
                            <th class="lsol-tabela-titulo" scope="col">Produto(s)</th>
                            <th class="lsol-tabela-titulo lsol-tabela-titulo--centro" scope="col">Quantidade</th>
                            <th class="lsol-tabela-titulo lsol-tabela-titulo--centro" scope="col">Data</th>
                            <th class="lsol-tabela-titulo" scope="col">Status</th>
                            <th class="lsol-tabela-titulo lsol-tabela-titulo--direita" scope="col">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($solicitacoes as $solicitacao): ?>
                            <?php
                            $classeStatus = $classesStatus[$solicitacao->soli_status] ?? 'lsol-status--neutro';
                            $pendente     = $solicitacao->soli_status === 'Pendente';
                            ?>
                            <tr>
                                <td class="lsol-tabela-celula">#<?= (int) $solicitacao->soli_id ?></td>

                                <td class="lsol-tabela-celula">
                                    <?= $e($solicitacao->usua_nome) ?>
                                    <span class="lsol-tabela-apoio"><?= $e($solicitacao->usua_email) ?></span>
                                </td>

                                <td class="lsol-tabela-celula"><?= $e($solicitacao->produtos ?: '—') ?></td>

                                <td class="lsol-tabela-celula lsol-tabela-celula--centro">
                                    <?= (int) $solicitacao->quantidade_total ?>
                                </td>

                                <td class="lsol-tabela-celula lsol-tabela-celula--centro">
                                    <?= $e(date('d/m/Y', strtotime($solicitacao->soli_data_solicitacao))) ?>
                                </td>

                                <td class="lsol-tabela-celula">
                                    <span class="lsol-status <?= $classeStatus ?>"><?= $e($solicitacao->soli_status) ?></span>
                                </td>

                                <td class="lsol-tabela-celula lsol-tabela-celula--direita">
                                    <?php
                                    $pendente     = $solicitacao->soli_status === 'Pendente';
                                    $emDevolucao  = $solicitacao->soli_status === 'Em devolução';
                                    $rotuloAcao   = $pendente ? 'Analisar' : ($emDevolucao ? 'Confirmar devolução' : 'Ver detalhes');
                                    $destaque     = ($pendente || $emDevolucao) ? 'lsol-botao--primario' : 'lsol-botao--contorno';
                                    ?>
                                    <a class="lsol-botao lsol-botao--pequeno <?= $destaque ?>"
                                        href="<?= URL ?>/solicitacoes/verSolicitacao/<?= (int) $solicitacao->soli_id ?>">
                                        <?= $rotuloAcao ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <footer class="lsol-rodape">
                <p class="lsol-rodape-info">
                    Mostrando <?= $paginacao['inicio'] ?> a <?= $paginacao['fim'] ?>
                    de <?= $paginacao['total'] ?> solicitações
                </p>

                <?php if ($paginacao['totalPaginas'] > 1): ?>
                    <nav class="lsol-paginacao" aria-label="Paginação">
                        <?php if ($paginacao['paginaAtual'] > 1): ?>
                            <a class="lsol-pagina-link"
                                href="<?= $e($montarUrl(array_merge($filtrosAtivos, ['pagina' => $paginacao['paginaAtual'] - 1]))) ?>"
                                aria-label="Página anterior">&#8249;</a>
                        <?php endif; ?>

                        <?php for ($i = $paginaInicial; $i <= $paginaFinal; $i++): ?>
                            <a class="lsol-pagina-link <?= $i === $paginacao['paginaAtual'] ? 'lsol-pagina-link--ativa' : '' ?>"
                                href="<?= $e($montarUrl(array_merge($filtrosAtivos, ['pagina' => $i]))) ?>"
                                <?= $i === $paginacao['paginaAtual'] ? 'aria-current="page"' : '' ?>>
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($paginacao['paginaAtual'] < $paginacao['totalPaginas']): ?>
                            <a class="lsol-pagina-link"
                                href="<?= $e($montarUrl(array_merge($filtrosAtivos, ['pagina' => $paginacao['paginaAtual'] + 1]))) ?>"
                                aria-label="Próxima página">&#8250;</a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            </footer>

        <?php endif; ?>
    </section>
</main>

<script src="<?= URL ?>/js/analisar-solicitacao.js" defer></script>
<?php include "../App/Views/footer.php"; ?>