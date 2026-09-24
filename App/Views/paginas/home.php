<?php
include "../App/Views/menu.php";

/*
 * Variáveis recebidas do Controller ($dados):
 *   notificacoesNaoLidas (int)
 *   resumo               ['aprovadas','pendentes','negadas']
 *   Coordenador: atividades, perfis, produtos, estoque
 *   Demais:      ultimaSolicitacao (array|null)
 */
$funcao       = (int) ($_SESSION['usuario_funcao'] ?? 0);
$coordenador  = ($funcao === 1);
$notificacoes = (int) $dados['notificacoesNaoLidas'];

// Escapa qualquer valor antes de imprimir (proteção contra XSS)
$esc = static fn($valor): string => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');

/* ---------- Cartão "Resumo das solicitações" ---------- */
$tituloResumo = $coordenador ? 'Resumo das solicitações' : 'Minhas solicitações';
$iconeResumo  = $coordenador ? 'bi-checklist' : 'bi-file-earmark-text';
$colunasResumo = [
    'Aprovadas' => $dados['resumo']['aprovadas'],
    'Pendentes' => $dados['resumo']['pendentes'],
    'Negadas'   => $dados['resumo']['negadas'],
];

/*
 * ---------- Cartões da segunda faixa ----------
 * Cada cartão é um array. Chaves opcionais:
 *   status   -> texto de destaque pequeno
 *   valor    -> número grande
 *   linhas   -> lista de [rótulo, valor]
 *   mensagem -> texto exibido quando não há linhas
 */
$cartoes = [];

if ($coordenador) {
    $atividades = $dados['atividades'];
    $estoque    = $dados['estoque'];

    $cartoes[] = [
        'icone' => 'bi-file-text',
        'titulo' => 'Resumo das atividades',
        'linhas' => [
            ['Solicitações em andamento', $atividades['emAndamento']],
            ['Pendências de devolução', $atividades['pendenciasDevolucao']],
            ['Solicitações concluídas', $atividades['concluidas']],
        ],
    ];
    $cartoes[] = [
        'icone' => 'bi-people-fill',
        'titulo' => 'Total de perfis',
        'valor' => $dados['perfis']['ativos'],
        'linhas' => [['Perfis removidos', $dados['perfis']['removidos']]],
    ];
    $cartoes[] = [
        'icone' => 'bi-box',
        'titulo' => 'Total de produtos',
        'valor' => $dados['produtos']['total'],
        'linhas' => [['Unidades em estoque', $dados['produtos']['unidades']]],
    ];
    $cartoes[] = [
        'icone' => 'bi-archive',
        'titulo' => 'Situação do estoque',
        'status' => ($estoque['baixo'] + $estoque['falta']) === 0 ? 'Estoque completo' : 'Requer atenção',
        'linhas' => [
            ['Estoque baixo', $estoque['baixo']],
            ['Em falta', $estoque['falta']],
        ],
    ];
} else {
    $ultima = $dados['ultimaSolicitacao'];

    $cartoes[] = [
        'icone' => 'bi-file-earmark-text',
        'titulo' => 'Última solicitação',
        'mensagem' => 'Você ainda não fez nenhuma solicitação.',
        'linhas' => $ultima ? [
            ['Produtos', $ultima['produtos']],
            ['Quantidade', $ultima['quantidade']],
            ['Status', $ultima['status']],
            ['Data da solicitação', date('d/m/Y', strtotime($ultima['data']))],
        ] : [],
    ];
}
?>
<main class="inicio">
    <header class="inicio__cabecalho">
        <div class="inicio__titulos">
            <div class="inicio__linha-titulo">
                <span class="inicio__marcador"></span>
                <h1 class="inicio__titulo">Início</h1>
            </div>
            <p class="inicio__subtitulo">Bem-vindo(a), <?= $esc($_SESSION['usuario_nome'] ?? '') ?>!</p>
        </div>
        <img class="inicio__logo" src="<?= URL ?>/img/logo-sacit.png" alt="SACIT">
    </header>

    <section class="inicio__painel">
        <div class="inicio__faixa-principal">
            <article class="inicio__cartao">
                <div class="inicio__cartao-cabecalho">
                    <i class="text-success bi bi-bell"></i>
                    <h2 class="inicio__cartao-titulo">Novas notificações</h2>
                </div>
                <p class="inicio__destaque inicio__destaque--centralizado"><?= $notificacoes ?></p>
                <p class="inicio__texto-apoio">
                    <?= $notificacoes === 0
                        ? 'Você não possui novas notificações.'
                        : ($notificacoes === 1 ? 'Você tem 1 notificação não lida.' : "Você tem $notificacoes notificações não lidas.") ?>
                </p>
            </article>

            <article class="inicio__cartao">
                <div class="inicio__cartao-cabecalho">
                    <i class="text-success bi <?= $esc($iconeResumo) ?>"></i>
                    <h2 class="inicio__cartao-titulo"><?= $esc($tituloResumo) ?></h2>
                </div>
                <div class="inicio__resumo">
                    <?php foreach ($colunasResumo as $rotulo => $numero) { ?>
                        <div class="inicio__resumo-coluna">
                            <p class="inicio__resumo-rotulo"><?= $esc($rotulo) ?></p>
                            <p class="inicio__resumo-numero"><?= (int) $numero ?></p>
                        </div>
                    <?php } ?>
                </div>
            </article>
        </div>

        <div class="inicio__faixa-secundaria">
            <?php foreach ($cartoes as $cartao) { ?>
                <article class="inicio__cartao">
                    <div class="inicio__cartao-cabecalho">
                        <i class="text-success bi <?= $esc($cartao['icone']) ?>"></i>
                        <h2 class="inicio__cartao-titulo"><?= $esc($cartao['titulo']) ?></h2>
                    </div>

                    <?php if (isset($cartao['status'])) { ?>
                        <p class="inicio__status"><?= $esc($cartao['status']) ?></p>
                    <?php } ?>

                    <?php if (isset($cartao['valor'])) { ?>
                        <p class="inicio__destaque"><?= (int) $cartao['valor'] ?></p>
                    <?php } ?>

                    <?php if (!empty($cartao['linhas'])) { ?>
                        <div class="inicio__linhas">
                            <?php foreach ($cartao['linhas'] as [$rotulo, $valor]) { ?>
                                <div class="inicio__linha">
                                    <span class="inicio__linha-rotulo"><?= $esc($rotulo) ?></span>
                                    <strong class="inicio__linha-valor"><?= $esc($valor) ?></strong>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } elseif (isset($cartao['mensagem'])) { ?>
                        <p class="inicio__texto-apoio"><?= $esc($cartao['mensagem']) ?></p>
                    <?php } ?>
                </article>
            <?php } ?>
        </div>
    </section>
</main>

<?php include "../App/Views/footer.php"; ?>