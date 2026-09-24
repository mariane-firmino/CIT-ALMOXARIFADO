<?php
/**
 * View: historicos/consultarHistorico
 *
 * A permissão é decidida no Controller: $dados['visao'] é 'geral' (coordenador)
 * ou 'pessoal' (demais usuários, só o próprio histórico).
 *
 * Em $dados: visao, resumo, historico, filtros, statusValidos, total, paginaAtual,
 * totalPaginas, limite, mensagem  e, só na visão geral: funcoes, periodo,
 * tiposRelatorio, meses, anos, mesPadrao, anoPadrao.
 */

// Escapa qualquer saída dinâmica (evita XSS). Se já tiver um helper global, use-o no lugar.
$e = fn($valor) => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');

$geral        = ($dados['visao'] === 'geral');
$filtros      = $dados['filtros'];
$resumo       = $dados['resumo'];
$paginaAtual  = $dados['paginaAtual'];
$totalPaginas = $dados['totalPaginas'];
$total        = $dados['total'];

// Cards-resumo: um único bloco de HTML repetido pelo loop
if ($geral) {
    $periodo = $dados['periodo'];
    $cards = [
        ['cor' => 'verde',  'icone' => 'bi-graph-up-arrow text-success', 'rotulo' => 'Total de Registros',   'valor' => (int) $resumo->registros, 'texto' => 'Registros encontrados'],
        ['cor' => 'azul',   'icone' => 'bi-person-circle text-primary',   'rotulo' => 'Usuários Envolvidos',  'valor' => (int) $resumo->usuarios,  'texto' => 'Usuários diferentes'],
        ['cor' => 'azul',   'icone' => 'bi-briefcase text-primary',  'rotulo' => 'Produtos Movimentados', 'valor' => (int) $resumo->produtos, 'texto' => 'Produtos diferentes'],
        ['cor' => 'roxo',   'icone' => 'bi-box-seam text-purple',  'rotulo' => 'Período Consultado',   'valor' => $periodo['inicio'],       'texto' => $periodo['fim'] !== '' ? 'até ' . $periodo['fim'] : 'Sem registros', 'pequeno' => true],
    ];
} else {
    $cards = [
        ['cor' => 'verde',    'icone' => 'bi-graph-up-arrow text-success',      'rotulo' => 'Total de Solicitações', 'valor' => (int) $resumo->total,     'texto' => 'Todas as solicitações'],
        ['cor' => 'verde',    'icone' => 'bi-patch-check-fill text-success', 'rotulo' => 'Aprovadas',             'valor' => (int) $resumo->aprovadas, 'texto' => 'Solicitações aprovadas'],
        ['cor' => 'vermelho', 'icone' => 'bi-x-circle text-danger',               'rotulo' => 'Negadas',               'valor' => (int) $resumo->negadas,   'texto' => 'Solicitações negadas'],
        ['cor' => 'roxo',     'icone' => 'bi-arrow-left-right text-purple',  'rotulo' => 'Em Devolução',          'valor' => (int) $resumo->devolucao, 'texto' => 'Aguardando devolução'],
    ];
}

$classesStatus = [
    'Pendente'     => 'hist-status-pendente',
    'Aprovada'     => 'hist-status-aprovada',
    'Negada'       => 'hist-status-negada',
    'Em devolução' => 'hist-status-devolucao',
];

// Monta a URL da tela mantendo os filtros (e trocando o que for passado em $troca)
$url = function (array $troca = []) use ($filtros): string {
    $query = array_merge(
        [
            'pesquisa' => $filtros['pesquisa'],
            'funcao'   => $filtros['funcao'],
            'status'   => $filtros['status'],
            'de'       => $filtros['de'],
            'ate'      => $filtros['ate'],
        ],
        $troca
    );
    $query = array_filter($query, fn($valor) => $valor !== '' && $valor !== null && $valor !== 0);

    return URL . '/historicos/consultarHistorico' . ($query ? '?' . http_build_query($query) : '');
};

$temFiltro = $filtros['pesquisa'] !== '' || $filtros['funcao'] !== 0 || $filtros['status'] !== ''
    || $filtros['de'] !== '' || $filtros['ate'] !== '';

// Janela de páginas (2 antes e 2 depois da atual)
$primeiraJanela = max(1, $paginaAtual - 2);
$ultimaJanela   = min($totalPaginas, $paginaAtual + 2);

$inicio = $total > 0 ? (($paginaAtual - 1) * $dados['limite']) + 1 : 0;
$fim    = min($paginaAtual * $dados['limite'], $total);

$colunas = $geral ? 7 : 5;
?>
<?php include "../App/Views/menu.php"; ?>

<main class="hist-container">

    <header class="hist-cabecalho">
        <div class="hist-titulo-grupo">
            <div class="hist-titulo-linha">
                <span class="hist-titulo-marca"></span>
                <h1 class="hist-titulo">Consultar Histórico</h1>
            </div>
            <p class="hist-subtitulo">
                <?= $geral
                    ? 'Consulte todo o histórico de movimentações realizadas no sistema.'
                    : 'Visualize o seu histórico completo.' ?>
            </p>
        </div>

        <img src="<?= URL ?>/img/logo-sacit.png" alt="SACIT" class="hist-logo">
    </header>

    <?php if (!empty($dados['mensagem'])): ?>
        <div class="hist-alerta hist-alerta-<?= $e($dados['mensagem']['tipo']) ?>" role="status">
            <?= $e($dados['mensagem']['texto']) ?>
        </div>
    <?php endif; ?>

    <section class="hist-resumo" aria-label="Resumo do histórico">
        <?php foreach ($cards as $card): ?>
            <article class="hist-resumo-card">
                <div class="hist-resumo-topo">
                    <div class="hist-resumo-icone hist-resumo-icone-<?= $card['cor'] ?>">
                        <i class="bi <?= $card['icone'] ?>"></i>
                    </div>
                    <p class="hist-resumo-rotulo"><?= $e($card['rotulo']) ?></p>
                </div>

                <p class="hist-resumo-valor <?= !empty($card['pequeno']) ? 'hist-resumo-valor-pequeno' : '' ?>">
                    <?= $e($card['valor']) ?>
                </p>
                <span class="hist-resumo-texto"><?= $e($card['texto']) ?></span>
            </article>
        <?php endforeach; ?>
    </section>

    <?php if ($geral): ?>
        <!-- Relatório mensal (PDF): só o coordenador vê -->
        <form class="hist-relatorio" action="<?= URL ?>/historicos/relatorio" method="GET" target="_blank">
            <div class="hist-relatorio-cabeca">
                <h2 class="hist-relatorio-titulo">Relatório mensal</h2>
                <p class="hist-relatorio-texto">Escolha o mês e o que deve constar. O PDF abre em uma nova aba, pronto para imprimir.</p>
            </div>

            <div class="hist-grupo">
                <label class="hist-rotulo" for="hist-mes">Mês</label>
                <select id="hist-mes" name="mes" class="hist-select">
                    <?php foreach ($dados['meses'] as $numero => $nomeMes): ?>
                        <option value="<?= (int) $numero ?>" <?= $numero === $dados['mesPadrao'] ? 'selected' : '' ?>>
                            <?= $e($nomeMes) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="hist-grupo">
                <label class="hist-rotulo" for="hist-ano">Ano</label>
                <select id="hist-ano" name="ano" class="hist-select">
                    <?php foreach ($dados['anos'] as $ano): ?>
                        <option value="<?= (int) $ano ?>" <?= $ano === $dados['anoPadrao'] ? 'selected' : '' ?>>
                            <?= (int) $ano ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="hist-grupo hist-grupo-largo">
                <label class="hist-rotulo" for="hist-tipo">O que incluir</label>
                <select id="hist-tipo" name="tipo" class="hist-select">
                    <?php foreach ($dados['tiposRelatorio'] as $valorTipo => $rotuloTipo): ?>
                        <option value="<?= $e($valorTipo) ?>"><?= $e($rotuloTipo) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="hist-btn hist-btn-principal">Visualizar / Imprimir</button>
            <button type="submit" name="acao" value="baixar" class="hist-btn hist-btn-contorno">Baixar PDF</button>
        </form>
    <?php endif; ?>

    <section class="hist-painel">
        <h2 class="hist-painel-titulo">
            <?= $geral ? 'Histórico de Solicitações' : 'Minhas Solicitações' ?>
        </h2>

        <form action="<?= URL ?>/historicos/consultarHistorico" method="GET" class="hist-filtros">

            <div class="hist-busca">
                <i class="bi bi-search"></i>
                <input
                    type="search"
                    name="pesquisa"
                    class="hist-busca-campo"
                    value="<?= $e($filtros['pesquisa']) ?>"
                    placeholder="<?= $geral ? 'Buscar por usuário, produto ou nº da solicitação...' : 'Buscar por nome do produto ou nº da solicitação...' ?>"
                    aria-label="Buscar no histórico">
            </div>

            <?php if ($geral): ?>
                <select name="funcao" class="hist-select hist-select-filtro" aria-label="Filtrar por tipo de usuário">
                    <option value="">Tipo de usuário</option>
                    <?php foreach ($dados['funcoes'] as $funcao): ?>
                        <option
                            value="<?= (int) $funcao->func_id ?>"
                            <?= $filtros['funcao'] === (int) $funcao->func_id ? 'selected' : '' ?>>
                            <?= $e($funcao->func_nome) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php else: ?>
                <select name="status" class="hist-select hist-select-filtro" aria-label="Filtrar por status">
                    <option value="">Todos os status</option>
                    <?php foreach ($dados['statusValidos'] as $opcaoStatus): ?>
                        <option value="<?= $e($opcaoStatus) ?>" <?= $filtros['status'] === $opcaoStatus ? 'selected' : '' ?>>
                            <?= $e($opcaoStatus) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>

            <div class="hist-grupo hist-grupo-data">
                <label class="hist-rotulo" for="hist-de">De</label>
                <input type="date" id="hist-de" name="de" class="hist-select" value="<?= $e($filtros['de']) ?>">
            </div>

            <div class="hist-grupo hist-grupo-data">
                <label class="hist-rotulo" for="hist-ate">Até</label>
                <input type="date" id="hist-ate" name="ate" class="hist-select" value="<?= $e($filtros['ate']) ?>">
            </div>

            <button type="submit" class="hist-btn hist-btn-principal">Pesquisar</button>

            <?php if ($temFiltro): ?>
                <a href="<?= URL ?>/historicos/consultarHistorico" class="hist-btn hist-btn-contorno">Limpar</a>
            <?php endif; ?>
        </form>

        <div class="hist-tabela-wrapper" role="region" aria-label="Tabela do histórico" tabindex="0">
            <table class="hist-tabela">
                <caption class="hist-somente-leitor">Histórico de solicitações</caption>
                <thead>
                    <tr>
                        <?php if ($geral): ?>
                            <th scope="col" class="hist-th">Data/Hora</th>
                            <th scope="col" class="hist-th">Usuário</th>
                            <th scope="col" class="hist-th">Tipo de Usuário</th>
                            <th scope="col" class="hist-th">Produto(s)</th>
                            <th scope="col" class="hist-th hist-col-centro">Quantidade</th>
                            <th scope="col" class="hist-th">Status</th>
                            <th scope="col" class="hist-th">Detalhes</th>
                        <?php else: ?>
                            <th scope="col" class="hist-th">Código</th>
                            <th scope="col" class="hist-th">Produto(s)</th>
                            <th scope="col" class="hist-th hist-col-centro">Quantidade</th>
                            <th scope="col" class="hist-th">Data</th>
                            <th scope="col" class="hist-th">Status</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>

                    <?php if (empty($dados['historico'])): ?>
                        <tr>
                            <td colspan="<?= $colunas ?>" class="hist-td hist-vazio">
                                Nenhum registro encontrado com esses filtros.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($dados['historico'] as $registro): ?>
                        <?php
                        $idRegistro   = (int) $registro->soli_id;
                        $classeStatus = $classesStatus[$registro->soli_status] ?? 'hist-status-devolucao';
                        $timestamp    = strtotime($registro->soli_data_solicitacao);
                        ?>
                        <tr class="hist-linha">
                            <?php if ($geral): ?>
                                <td class="hist-td">
                                    <?= $e(date('d/m/Y', $timestamp)) ?>
                                    <span class="hist-td-secundario"><?= $e(date('H:i', $timestamp)) ?></span>
                                </td>
                                <td class="hist-td">
                                    <span class="hist-usuario-nome"><?= $e($registro->usua_nome) ?></span>
                                    <span class="hist-usuario-email"><?= $e($registro->usua_email) ?></span>
                                </td>
                                <td class="hist-td"><?= $e($registro->func_nome ?: '—') ?></td>
                                <td class="hist-td hist-produtos"><?= $e($registro->produtos ?: 'Sem itens') ?></td>
                                <td class="hist-td hist-col-centro"><?= (int) $registro->quantidade_total ?></td>
                                <td class="hist-td">
                                    <span class="hist-status <?= $classeStatus ?>"><?= $e($registro->soli_status) ?></span>
                                </td>
                                <td class="hist-td">
                                    <a href="<?= URL ?>/solicitacoes/verSolicitacao/<?= $idRegistro ?>" class="hist-btn-acao">Ver</a>
                                </td>
                            <?php else: ?>
                                <td class="hist-td"><?= $idRegistro ?></td>
                                <td class="hist-td hist-produtos"><?= $e($registro->produtos ?: 'Sem itens') ?></td>
                                <td class="hist-td hist-col-centro"><?= (int) $registro->quantidade_total ?></td>
                                <td class="hist-td">
                                    <?= $e(date('d/m/Y', $timestamp)) ?>
                                    <span class="hist-td-secundario"><?= $e(date('H:i', $timestamp)) ?></span>
                                </td>
                                <td class="hist-td">
                                    <span class="hist-status <?= $classeStatus ?>"><?= $e($registro->soli_status) ?></span>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>
        </div>

        <?php if ($total > 0): ?>
            <div class="hist-rodape-tabela">
                <p class="hist-rodape-info">
                    Mostrando <?= $inicio ?> a <?= $fim ?> de <?= $total ?> registros
                </p>

                <?php if ($totalPaginas > 1): ?>
                    <nav class="hist-paginacao" aria-label="Paginação">

                        <?php if ($paginaAtual > 1): ?>
                            <a class="hist-pag-link" href="<?= $e($url(['pagina' => $paginaAtual - 1])) ?>" aria-label="Página anterior">&#8249;</a>
                        <?php endif; ?>

                        <?php for ($i = $primeiraJanela; $i <= $ultimaJanela; $i++): ?>
                            <a
                                class="hist-pag-link <?= $i === $paginaAtual ? 'hist-pag-link-ativa' : '' ?>"
                                href="<?= $e($url(['pagina' => $i])) ?>"
                                <?= $i === $paginaAtual ? 'aria-current="page"' : '' ?>>
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($paginaAtual < $totalPaginas): ?>
                            <a class="hist-pag-link" href="<?= $e($url(['pagina' => $paginaAtual + 1])) ?>" aria-label="Próxima página">&#8250;</a>
                        <?php endif; ?>

                    </nav>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </section>
</main>

<?php include "../App/Views/footer.php"; ?>