<?php
session_start();
include "./../App/configuracao.php";
include "./../App/autoload.php";
require_once '../vendor/autoload.php';

$db = new Database;

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NOME ?></title>
    <link rel="stylesheet" type="text/css" href="<?= URL ?>/public/css/bootstrap.css" />
    <link rel="stylesheet" type="text/css" href="<?= URL ?>/public/css/login.css" />
    <link rel="stylesheet" type="text/css" href="<?= URL ?>/public/css/geral.css" />
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inria+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
</head>

<body>
    <?php
    $rotas = new Rota();
    ?>

</body>
<script src="<?= URL ?>/public/js/formatacao.js"></script>
<script src="<?= URL ?>/public/js/imagem.js"></script>
<script src="<?= URL ?>/public/js/senha.js"></script>
<script src="<?= URL ?>/public/js/consultarP.js"></script>
<script src="<?= URL ?>/public/js/solicitacao.js"></script>
<script src="<?= URL ?>/public/js/realizarS.js"></script>
<script src="<?= URL ?>/public/js/verSoli.js"></script>
<script src="<?= URL ?>/public/js/minhasS.js"></script>
<script src="<?= URL ?>/public/js/mascaraTelefone.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>

</html>