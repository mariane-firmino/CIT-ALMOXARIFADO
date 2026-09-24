<?php

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Controller: Historicos
 *
 * Rotas:
 *   GET /historicos/consultarHistorico  -> coordenador: histórico geral + emissão de relatório
 *                                          demais usuários: só o próprio histórico
 *   GET /historicos/relatorio           -> PDF do relatório mensal (somente coordenador)
 *                                          ?mes=9&ano=2026&tipo=completo|solicitacoes|produtos[&acao=baixar]
 *
 * Premissas: classe base Controller com $this->model() e $this->view(); sessão iniciada;
 * o autoload do Composer (vendor/autoload.php) já carregado, para o Dompdf.
 */
class Historicos extends Controller
{
    private const POR_PAGINA = 10;
    private const FUNCAO_COORDENADOR = 1;

    /* Identificação no cabeçalho do relatório: AJUSTE aos nomes oficiais */
    private const INSTITUICAO = 'Instituto Federal de Rondônia';
    private const SISTEMA     = 'SACIT · Almoxarifado do CIT';

    private const TIPOS_RELATORIO = [
        'completo'     => 'Completo: tudo o que foi feito no mês',
        'solicitacoes' => 'Somente as solicitações do mês',
        'produtos'     => 'Somente os produtos cadastrados no mês',
    ];

    private const MESES = [
        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
        5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
        9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
    ];

    private $historicoModel;

    public function __construct()
    {
        $this->historicoModel = $this->model('Historico');
    }

    /* ==========================================================
       Tela: Consultar Histórico
       ========================================================== */
    public function consultarHistorico()
    {
        $this->exigirLogin();

        $geral     = $this->ehCoordenador();
        $usuarioId = $geral ? null : (int) $_SESSION['usuario_id'];
        $filtros   = $this->lerFiltros($_GET, $geral);

        $total        = $this->historicoModel->contar($filtros, $usuarioId);
        $totalPaginas = max(1, (int) ceil($total / self::POR_PAGINA));
        $paginaAtual  = min(max(1, (int) ($_GET['pagina'] ?? 1)), $totalPaginas);
        $offset       = ($paginaAtual - 1) * self::POR_PAGINA;

        $dados = [
            'visao'        => $geral ? 'geral' : 'pessoal',
            'resumo'       => $geral
                ? $this->historicoModel->resumoGeral($filtros)
                : $this->historicoModel->resumoPessoal($usuarioId),
            'historico'    => $this->historicoModel->listar($filtros, $usuarioId, self::POR_PAGINA, $offset),
            'filtros'      => $filtros,
            'statusValidos' => Historico::STATUS_VALIDOS,
            'total'        => $total,
            'paginaAtual'  => $paginaAtual,
            'totalPaginas' => $totalPaginas,
            'limite'       => self::POR_PAGINA,
            'mensagem'     => $this->pegarMensagem(),
        ];

        if ($geral) {
            $resumo = $dados['resumo'];
            $dados['funcoes']        = $this->historicoModel->listarFuncoes();
            $dados['periodo']        = $this->periodoConsultado($filtros, $resumo);
            $dados['tiposRelatorio'] = self::TIPOS_RELATORIO;
            $dados['meses']          = self::MESES;
            $dados['anos']           = $this->anosDoRelatorio();
            $dados['mesPadrao']      = (int) date('n');
            $dados['anoPadrao']      = (int) date('Y');
        }

        $this->view('historicos/consultarHistorico', $dados);
    }

    /* ==========================================================
       Relatório mensal em PDF (Dompdf)
       ========================================================== */
    public function relatorio()
    {
        // Avisos do PHP impressos na tela entram no arquivo e corrompem o PDF.
        // Eles continuam indo para o log de erros.
        ini_set('display_errors', '0');

        $this->exigirLogin();

        if (!$this->ehCoordenador()) {
            $this->redirecionar('historicos/consultarHistorico');
        }

        $mes  = (int) ($_GET['mes'] ?? 0);
        $ano  = (int) ($_GET['ano'] ?? 0);
        $tipo = is_string($_GET['tipo'] ?? '') ? ($_GET['tipo'] ?? '') : '';

        $inicio = ($mes >= 1 && $mes <= 12 && $ano >= 2000)
            ? DateTimeImmutable::createFromFormat('!Y-n-j', "{$ano}-{$mes}-1")
            : false;

        $primeiroDiaDoMesAtual = new DateTimeImmutable('first day of this month 00:00:00');

        if (!$inicio || !isset(self::TIPOS_RELATORIO[$tipo]) || $inicio > $primeiroDiaDoMesAtual) {
            $this->guardarMensagem('erro', 'Escolha um mês (que não seja futuro) e um tipo de relatório válidos.');
            $this->redirecionar('historicos/consultarHistorico');
        }

        // Intervalo [início do mês, início do mês seguinte)
        $fim         = $inicio->modify('first day of next month');
        $inicioSql   = $inicio->format('Y-m-d H:i:s');
        $fimSql      = $fim->format('Y-m-d H:i:s');
        $ultimoDia   = $fim->modify('-1 day');

        $mostrarSolicitacoes = in_array($tipo, ['completo', 'solicitacoes'], true);
        $mostrarProdutos     = in_array($tipo, ['completo', 'produtos'], true);
        $mostrarEventos      = ($tipo === 'completo');

        $dados = [
            'tipo'         => $tipo,
            'tituloTipo'   => self::TIPOS_RELATORIO[$tipo],
            'referencia'   => self::MESES[$mes] . ' de ' . $ano,
            'periodo'      => $inicio->format('d/m/Y') . ' a ' . $ultimoDia->format('d/m/Y'),
            'emitidoEm'    => date('d/m/Y \à\s H:i'),
            'emissor'      => $this->historicoModel->buscarEmissor((int) $_SESSION['usuario_id']),
            'instituicao'  => self::INSTITUICAO,
            'sistema'      => self::SISTEMA,
            'logo'         => $this->logoEmBase64(),
            'mostrarSolicitacoes' => $mostrarSolicitacoes,
            'mostrarProdutos'     => $mostrarProdutos,
            'mostrarEventos'      => $mostrarEventos,
            'resumoSolicitacoes'  => $mostrarSolicitacoes ? $this->historicoModel->resumoSolicitacoes($inicioSql, $fimSql) : null,
            'solicitacoes'        => $mostrarSolicitacoes ? $this->historicoModel->solicitacoesDoPeriodo($inicioSql, $fimSql) : [],
            'produtos'            => $mostrarProdutos ? $this->historicoModel->produtosCadastradosNoPeriodo($inicioSql, $fimSql) : [],
            'eventos'             => $mostrarEventos ? $this->historicoModel->eventosDoPeriodo($inicioSql, $fimSql) : [],
        ];

        // A view é um documento HTML completo (sem menu/rodapé do site): captura e entrega ao Dompdf
        ob_start();
        $this->view('historicos/relatorio', $dados);
        $html = ob_get_clean();

        $arquivo = 'relatorio-' . $tipo . '-' . $inicio->format('Y-m') . '.pdf';
        $baixar  = (($_GET['acao'] ?? '') === 'baixar');

        $this->emitirPdf($html, $arquivo, $baixar);
    }

    /* ==========================================================
       Auxiliares
       ========================================================== */

    private function emitirPdf(string $html, string $arquivo, bool $baixar): void
    {
        $pdf = '';

        try {
            $opcoes = new Options();
            $opcoes->set('defaultFont', 'DejaVu Sans');   // cobre acentos e símbolos
            $opcoes->set('isRemoteEnabled', false);       // nada de buscar recursos externos
            $opcoes->set('isHtml5ParserEnabled', true);

            $dompdf = new Dompdf($opcoes);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // Numeração "Página X de Y" (só é possível depois do render)
            $canvas = $dompdf->getCanvas();
            $fonte  = $dompdf->getFontMetrics()->getFont('DejaVu Sans', 'normal');
            $canvas->page_text(
                $canvas->get_width() - 108,
                $canvas->get_height() - 42,
                'Página {PAGE_NUM} de {PAGE_COUNT}',
                $fonte,
                8,
                [0.35, 0.35, 0.35]
            );

            $pdf = $dompdf->output();
        } catch (Throwable $erro) {
            error_log('[relatorio] falha ao gerar o PDF: ' . $erro->getMessage());
        }

        // Todo PDF válido começa com "%PDF". Se não começou, não entrega um arquivo quebrado.
        if (strncmp($pdf, '%PDF', 4) !== 0) {
            $this->guardarMensagem('erro', 'Não foi possível gerar o relatório. Tente novamente.');
            $this->redirecionar('historicos/consultarHistorico');
        }

        // Descarta qualquer saída acumulada antes (avisos, espaços, BOM):
        // um único caractere antes do "%PDF" faz o navegador dizer "não foi possível abrir o arquivo".
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        if (headers_sent($origemArquivo, $origemLinha)) {
            // Algum arquivo já imprimiu algo: o log diz qual e em que linha
            error_log("[relatorio] saída antes do PDF em {$origemArquivo}:{$origemLinha}");
        }

        // inline abre no navegador (para imprimir); attachment baixa o arquivo
        header('Content-Type: application/pdf');
        header('Content-Length: ' . strlen($pdf));
        header('Content-Disposition: ' . ($baixar ? 'attachment' : 'inline') . '; filename="' . $arquivo . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');

        echo $pdf;
        exit;
    }

    /** Logo embutido no PDF (data URI), sem precisar liberar acesso remoto no Dompdf. */
    private function logoEmBase64(): ?string
    {
        $caminho = dirname(__DIR__, 2) . '/public/img/logo-sacit.png'; // AJUSTE se a pasta pública for outra

        return is_file($caminho)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($caminho))
            : null;
    }

    /** Anos disponíveis no relatório: do primeiro registro até o atual, do mais novo ao mais antigo. */
    private function anosDoRelatorio(): array
    {
        $atual   = (int) date('Y');
        $primeiro = $this->historicoModel->primeiroAno() ?? $atual;

        return range($atual, min($primeiro, $atual));
    }

    /**
     * Texto do card "Período consultado": o filtro, se houver; senão, do primeiro ao último registro.
     * Retorna ['inicio' => string, 'fim' => string] ou ['inicio' => '—', 'fim' => ''] sem registros.
     */
    private function periodoConsultado(array $filtros, $resumo): array
    {
        $formatar = static fn(?string $data): string => $data ? date('d/m/Y', strtotime($data)) : '';

        $inicio = $filtros['de']  !== '' ? $formatar($filtros['de'])  : $formatar($resumo->primeira ?? null);
        $fim    = $filtros['ate'] !== '' ? $formatar($filtros['ate']) : $formatar($resumo->ultima ?? null);

        return ($inicio === '' && $fim === '')
            ? ['inicio' => '—', 'fim' => '']
            : ['inicio' => $inicio ?: '—', 'fim' => $fim ?: '—'];
    }

    /** Limpa e valida os filtros vindos da URL. */
    private function lerFiltros(array $origem, bool $geral): array
    {
        $texto = static fn($valor): string => is_string($valor) ? trim($valor) : '';
        $data  = static function ($valor): string {
            $valor  = is_string($valor) ? $valor : '';
            $objeto = DateTimeImmutable::createFromFormat('!Y-m-d', $valor);

            return ($objeto && $objeto->format('Y-m-d') === $valor) ? $valor : '';
        };

        $status = $texto($origem['status'] ?? '');
        if ($geral || !in_array($status, Historico::STATUS_VALIDOS, true)) {
            $status = ''; // o filtro de status só existe na visão pessoal
        }

        $de  = $data($origem['de'] ?? '');
        $ate = $data($origem['ate'] ?? '');
        if ($de !== '' && $ate !== '' && $de > $ate) {
            [$de, $ate] = [$ate, $de]; // período invertido: troca em vez de dar tela vazia
        }

        return [
            'pesquisa' => $texto($origem['pesquisa'] ?? ''),
            'funcao'   => $geral ? (int) ($origem['funcao'] ?? 0) : 0,
            'status'   => $status,
            'de'       => $de,
            'ate'      => $ate,
        ];
    }

    private function ehCoordenador(): bool
    {
        return (int) ($_SESSION['usuario_funcao'] ?? 0) === self::FUNCAO_COORDENADOR;
    }

    private function exigirLogin(): void
    {
        if (empty($_SESSION['usuario_id'])) {
            $this->redirecionar('login');
        }
    }

    private function redirecionar(string $rota): void
    {
        header('Location: ' . URL . '/' . $rota);
        exit;
    }

    /* ---- mensagem de retorno (aparece uma vez e some) ---- */

    private function guardarMensagem(string $tipo, string $texto): void
    {
        $_SESSION['historicos_mensagem'] = ['tipo' => $tipo, 'texto' => $texto];
    }

    private function pegarMensagem(): ?array
    {
        $mensagem = $_SESSION['historicos_mensagem'] ?? null;
        unset($_SESSION['historicos_mensagem']);

        return $mensagem;
    }
}