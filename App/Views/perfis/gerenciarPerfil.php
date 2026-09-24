<?php include "../App/Views/menu.php"; ?>
<?php
/* A permissão (somente administrador) é verificada no controller. */
$e = fn($valor) => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');

/* Cartões de resumo: [rótulo, valor, descrição, ícone Bootstrap, cor] */
$resumo = [
    ['Total de Perfis',  $dados['total'],     'Usuários cadastrados', 'bi-people',      'verde'],
    ['Perfis Ativos',    $dados['ativos'],    'Usuários ativos',      'bi-person-plus', 'azul'],
    ['Perfis Inativos',  $dados['inativos'],  'Usuários inativos',    'bi-person-dash', 'amarelo'],
    ['Perfis Removidos', $dados['removidos'], 'Usuários removidos',   'bi-trash3-fill', 'vermelho'],
];

/* Link de paginação que preserva pesquisa, função e status */
$urlPagina = function (int $pagina) use ($e): string {
    $filtros = array_diff_key($_GET, ['url' => true, 'pagina' => true]);
    return '?' . $e(http_build_query($filtros + ['pagina' => $pagina]));
};

/* Filtros atuais, enviados junto com o botão Ativar/Inativar para voltar à mesma tela */
$retorno = http_build_query(array_diff_key($_GET, ['url' => true]));

/* Tipos de perfil no botão "Novo perfil": slug => [texto, ícone Bootstrap] */
$tiposDePerfil = [
    'servidor'    => ['Servidor',    'bi-person-badge'],
    'estagiario'  => ['Estagiário',  'bi-mortarboard'],
    'coordenador' => ['Coordenador', 'bi-person-gear'],
];

$statusSelecionado = $_GET['status'] ?? '';
$idLogado          = (int) ($_SESSION['usuario_id'] ?? 0);
?>

<main class="cit-conteudo">
    <header class="cit-cabecalho">
        <div class="cit-cabecalho__grupo">
            <div class="cit-cabecalho__linha">
                <span class="cit-cabecalho__marca"></span>
                <h1 class="cit-cabecalho__titulo">Gerenciar Perfis</h1>
            </div>
            <p class="cit-cabecalho__subtitulo">Gerencie os usuários do sistema.</p>
        </div>
        <img src="<?= URL ?>/img/logo-sacit.png" alt="SACIT" class="cit-cabecalho__logo">
    </header>

    <?php if (!empty($dados['mensagem'])): ?>
        <div class="cit-aviso cit-aviso--<?= $e($dados['mensagem']['tipo']) ?>" role="status">
            <?= $e($dados['mensagem']['texto']) ?>
        </div>
    <?php endif; ?>

    <section class="cit-resumo-grade" aria-label="Resumo dos perfis">
        <?php foreach ($resumo as [$rotulo, $valor, $descricao, $icone, $cor]): ?>
            <article class="cit-resumo">
                <div class="cit-resumo__topo">
                    <span class="cit-resumo__icone cit-resumo__icone--<?= $cor ?>">
                        <i class="bi <?= $icone ?>" aria-hidden="true"></i>
                    </span>
                    <p class="cit-resumo__rotulo"><?= $e($rotulo) ?></p>
                </div>
                <p class="cit-resumo__valor"><?= $e($valor) ?></p>
                <p class="cit-resumo__descricao"><?= $e($descricao) ?></p>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="cit-painel">
        <h2 class="cit-painel__titulo">Lista de usuários</h2>

        <div class="cit-painel__barra">
            <form class="cit-filtros" action="<?= URL ?>/perfis/gerenciarPerfis" method="GET">
                <label class="cit-filtros__busca">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input
                        class="cit-filtros__campo"
                        type="search"
                        name="pesquisa"
                        value="<?= $e($_GET['pesquisa'] ?? '') ?>"
                        placeholder="Buscar usuário"
                        aria-label="Buscar usuário">
                </label>

                <select class="cit-filtros__select" name="funcao" aria-label="Filtrar por função" onchange="this.form.submit()">
                    <option value="">Todas as funções</option>
                    <?php foreach ($dados['funcoes'] as $opcaoFuncao): ?>
                        <option
                            value="<?= $e($opcaoFuncao->func_id) ?>"
                            <?= (string) ($_GET['funcao'] ?? '') === (string) $opcaoFuncao->func_id ? 'selected' : '' ?>>
                            <?= $e($opcaoFuncao->func_nome) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select class="cit-filtros__select" name="status" aria-label="Filtrar por status" onchange="this.form.submit()">
                    <option value="">Todos os status</option>
                    <?php foreach (['Ativo', 'Inativo'] as $opcaoStatus): ?>
                        <option value="<?= $opcaoStatus ?>" <?= $statusSelecionado === $opcaoStatus ? 'selected' : '' ?>>
                            <?= $opcaoStatus ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <details class="cit-suspenso cit-painel__novo">
                <summary class="cit-botao cit-botao--primario cit-suspenso__gatilho">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i>
                    Novo perfil
                    <i class="bi bi-chevron-down cit-suspenso__seta" aria-hidden="true"></i>
                </summary>

                <ul class="cit-suspenso__lista">
                    <?php foreach ($tiposDePerfil as $slug => [$textoPerfil, $iconePerfil]): ?>
                        <li>
                            <a class="cit-suspenso__item" href="<?= URL ?>/perfis/cadastrar?perfil=<?= $slug ?>">
                                <i class="bi <?= $iconePerfil ?>" aria-hidden="true"></i>
                                <?= $e($textoPerfil) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </details>
        </div>

        <?php if (empty($dados['usuarios'])): ?>
            <p class="cit-painel__vazio">Nenhum usuário encontrado.</p>
        <?php else: ?>
            <div class="cit-tabela__envoltorio">
                <table class="cit-tabela">
                    <thead class="cit-tabela__topo">
                        <tr>
                            <th scope="col" class="cit-tabela__cabecalho">Nome</th>
                            <th scope="col" class="cit-tabela__cabecalho">E-mail</th>
                            <th scope="col" class="cit-tabela__cabecalho">Função</th>
                            <th scope="col" class="cit-tabela__cabecalho">Status</th>
                            <th scope="col" class="cit-tabela__cabecalho">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="cit-tabela__corpo">
                        <?php foreach ($dados['usuarios'] as $usuario):
                            $chaveStatus = preg_replace('/[^a-z]/', '', strtolower((string) $usuario->status));
                            $estaAtivo   = ($chaveStatus === 'ativo');
                            $ehVoce      = ((int) $usuario->usua_id === $idLogado);

                            // Foto (se existir) ou iniciais do nome como alternativa
                            $urlFoto  = !empty($usuario->foto)
                                ? URL . '/img/usuarios/' . rawurlencode((string) $usuario->foto)
                                : null;
                            $palavras = preg_split('/\s+/', trim((string) $usuario->usua_nome));
                            $iniciais = mb_strtoupper(
                                mb_substr($palavras[0] ?? '', 0, 1) .
                                    (count($palavras) > 1 ? mb_substr(end($palavras), 0, 1) : '')
                            );
                        ?>
                            <tr class="cit-tabela__linha">
                                <td class="cit-tabela__celula cit-tabela__celula--nome" data-label="Nome">
                                    <span class="cit-usuario">
                                        <?php if ($urlFoto): ?>
                                            <img
                                                src="<?= $e($urlFoto) ?>"
                                                alt="Foto de <?= $e($usuario->usua_nome) ?>"
                                                class="cit-usuario__foto"
                                                width="40" height="40"
                                                loading="lazy">
                                        <?php else: ?>
                                            <span class="cit-usuario__foto cit-usuario__foto--iniciais" aria-hidden="true">
                                                <?= $e($iniciais) ?>
                                            </span>
                                        <?php endif; ?>
                                        <span class="cit-usuario__nome"><?= $e($usuario->usua_nome) ?></span>
                                    </span>
                                </td>
                                <!-- ...as outras células continuam iguais... -->
                                <td class="cit-tabela__celula" data-label="E-mail">
                                    <?= $e($usuario->usua_email) ?>
                                </td>
                                <td class="cit-tabela__celula" data-label="Função">
                                    <?= $e($usuario->func_nome) ?>
                                </td>
                                <td class="cit-tabela__celula" data-label="Status">
                                    <span class="cit-status cit-status--<?= $chaveStatus ?>">
                                        <?= $e($usuario->status) ?>
                                    </span>
                                </td>
                                <td class="cit-tabela__celula cit-tabela__celula--acao" data-label="Ação">
                                    <form class="cit-tabela__form"
                                        action="<?= URL ?>/perfis/alterarStatus/<?= (int) $usuario->usua_id ?>"
                                        method="POST">
                                        <input type="hidden" name="csrf" value="<?= $e($dados['csrf']) ?>">
                                        <input type="hidden" name="novo_status" value="<?= $estaAtivo ? 'Inativo' : 'Ativo' ?>">
                                        <input type="hidden" name="retorno" value="<?= $e($retorno) ?>">

                                        <?php if ($estaAtivo): ?>
                                            <button type="submit"
                                                class="cit-botao cit-botao--contorno cit-botao--pequeno cit-tabela__botao-status"
                                                <?= $ehVoce ? 'disabled title="Você não pode inativar o seu próprio perfil"' : 'onclick="return confirm(\'Inativar este usuário?\');"' ?>>
                                                <i class="bi bi-person-dash" aria-hidden="true"></i>
                                                Inativar
                                            </button>
                                        <?php else: ?>
                                            <button type="submit"
                                                class="cit-botao cit-botao--primario cit-botao--pequeno cit-tabela__botao-status">
                                                <i class="bi bi-person-check" aria-hidden="true"></i>
                                                Ativar
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="cit-painel__rodape">
                <p class="cit-painel__info">
                    Mostrando <?= count($dados['usuarios']) ?> de <?= (int) $dados['totalUsuarios'] ?> usuários
                </p>

                <?php if ($dados['totalPaginas'] > 1): ?>
                    <nav class="cit-paginacao" aria-label="Paginação">
                        <?php if ($dados['paginaAtual'] > 1): ?>
                            <a class="cit-paginacao__item" href="<?= $urlPagina($dados['paginaAtual'] - 1) ?>" aria-label="Página anterior">&#8249;</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $dados['totalPaginas']; $i++):
                            $paginaAtiva = ($dados['paginaAtual'] == $i);
                        ?>
                            <a class="cit-paginacao__item<?= $paginaAtiva ? ' cit-paginacao__item--ativo' : '' ?>"
                                href="<?= $urlPagina($i) ?>"
                                <?= $paginaAtiva ? 'aria-current="page"' : '' ?>>
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($dados['paginaAtual'] < $dados['totalPaginas']): ?>
                            <a class="cit-paginacao__item" href="<?= $urlPagina($dados['paginaAtual'] + 1) ?>" aria-label="Próxima página">&#8250;</a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>
</main>
<script>
    // Fecha o menu suspenso ao clicar fora dele ou apertar Esc
    document.addEventListener('click', function(evento) {
        document.querySelectorAll('.cit-suspenso[open]').forEach(function(menu) {
            if (!menu.contains(evento.target)) {
                menu.removeAttribute('open');
            }
        });
    });

    document.addEventListener('keydown', function(evento) {
        if (evento.key !== 'Escape') {
            return;
        }
        document.querySelectorAll('.cit-suspenso[open]').forEach(function(menu) {
            menu.removeAttribute('open');
            menu.querySelector('.cit-suspenso__gatilho').focus();
        });
    });
</script>
<?php include "../App/Views/footer.php"; ?>