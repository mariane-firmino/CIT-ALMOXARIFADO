<?php
include "../App/Views/menu.php";

// Ideal: vir do Controller (ex.: $equipe = $this->model->listarEquipe();)
$equipe = $equipe ?? [
    [
        'nome'      => 'Darliane Oliveira',
        'funcao'    => 'Front-end / UX',
        'descricao' => '"Fui a responsável principal por criar o visual do sistema do CIT. Busquei as melhores formas de manter um visual profissional, mas que fosse fácil de utilizar. As cores foram pensadas para tons que fossem agradaveis aos olhos, além de ser a palheta do IFRO (verde).',
        'foto'      => 'darliane.jpeg',
    ],
    [
        'nome'      => 'Mariane Firmino',
        'funcao'    => 'Back-end / Dados',
        'descricao' => '"Sou a responsável por fazer o sistema funcionar, depois que o visual foi pronto me responsabilizei por deixá-lo funcional. Foi uma tarefa bem complicada, principalmente por ter muitas funcionalidades, como: gerar QrCode e relatório. Foi algo novo para mim, mas obtive bons resultados. Fico feliz por poder ter participado desse projeto e ter concluído-o."',
        'foto'      => 'mariane.jpeg',
    ],
];
?>
<link rel="stylesheet" href="<?= URL ?>/public/css/sobre.css">

<div class="page">
    <main class="sobre-pagina">

        <header class="sobre-cabecalho">
            <div class="sobre-cabecalho-titulo">
                <span class="sobre-cabecalho-marca"></span>
                <div>
                    <h1 class="sobre-titulo">Sobre Nós</h1>
                    <p class="sobre-subtitulo">Conheça as desenvolvedoras e o motivo por trás do sistema.</p>
                </div>
            </div>
            <img src="<?= URL ?>/public/img/logo-sacit.png" alt="Logo do SACIT" class="sobre-logo">
        </header>

        <section class="sobre-apresentacao">
            <p class="sobre-apresentacao-texto">
                O Sistema de Almoxarifado CIT será responsável pela otimização do funcionamento de almoxarifado oferecido pelo CIT(Centro de Inovação Tecnológica). Através dele, um usuário pode fazer a solicitação de produtos que estão no estoque, para serem usados de forma educativa. Essas solicitações serão analisadas pelo coordenador que será responsável pela separação e disponibilidade dos itens.
            </p>
            <p class="sobre-apresentacao-texto">
                Além disso, o sistema armazenará dados dos usuários, possibilitando a identificação de quem realizou cada solicitação; dos produtos, permitindo o controle eficiente do estoque; e das próprias solicitações, viabilizando o acompanhamento das datas de retirada e devolução.
            </p>
        </section>

        <section class="sobre-equipe" aria-label="Equipe de desenvolvimento">
            <?php foreach ($equipe as $membro): ?>
                <article class="sobre-membro">
                    <div class="sobre-membro-foto">
                        <img src="<?= URL ?>/public/img/<?= htmlspecialchars($membro['foto']) ?>"
                            alt="Foto de <?= htmlspecialchars($membro['nome']) ?>"
                            class="sobre-membro-imagem">
                    </div>
                    <h2 class="sobre-membro-nome"><?= htmlspecialchars($membro['nome']) ?></h2>
                    <p class="sobre-membro-funcao"><?= htmlspecialchars($membro['funcao']) ?></p>
                    <p class="sobre-membro-descricao"><?= htmlspecialchars($membro['descricao']) ?></p>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="sobre-secao">
            <h2 class="sobre-secao-titulo">Por que criamos o SACIT?</h2>
            <p class="sobre-secao-texto">
                Essa ideia foi proprosta para o controle de dados de forma organizada e que reduza erros. Com isso, impede problemas relacionados à ausência de materiais sem justificativa; reduz problemas para identificar atrasos na devolução de produtos; agiliza o processo de contabilização de produtos no estoque, facilitando na hora de solicitar mais caso necessário; permite que o usuário receba sua solicitação mais rapidamente; e mostra a disponibilidade de materiais que são oferecidos.
            </p>
            <p class="sobre-secao-texto">
                O sistema também serve como uma base de estudos para futuras melhorias, trazendo
                flexibilidade para novas funcionalidades e relatórios mais completos.
            </p>
        </section>

        <section class="sobre-secao">
            <h2 class="sobre-secao-titulo">Como foi o desenvolvimento</h2>
            <p class="sobre-secao-texto">
                O desenvolvimento foi realizado por etapas, durante o estágio foi realizado o levantamento de dados de todos os produtos presentes no CIT. Esses produtos foram devidamente registrados em um documento word.
                Ademais, começou a parte de prototipação no figma, aplicativo utilizado para criar as telas do sistema (parte visual) e após terminado foi iniciado a parte de programação no VSCode.
            </p>
            <p class="sobre-secao-texto">
                O sistema foi organizado em MVC, uma estrutura que foi de extrema importacia para o desenvolvimento do software. Foi utilizada a linguagem PHP, juntamente com suas bibliotecas. E, para o banco de dados, foi utilizado o phpMyAdmin.
            </p>
        </section>

    </main>
</div>

<?php
include "../App/Views/footer.php";
?>