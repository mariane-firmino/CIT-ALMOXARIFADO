<?php

class Perfil
{
    private $db;
    private const STATUS_EDITAVEIS = [
        'Ativo',
        'Inativo'
    ];
    public const FUNC_COORDENADOR = 1;
    public const FUNC_ESTAGIARIO  = 2;
    public const FUNC_SERVIDOR    = 3;
    public const STATUS_INICIAL = 'Ativo';

    public function __construct()
    {
        $this->db = new Database();
    }

    public function resumoPerfis()
    {
        $this->db->query(
            "SELECT COUNT(*) AS total,
                    COALESCE(SUM(usua_status = 'Ativo'), 0)    AS ativos,
                    COALESCE(SUM(usua_status = 'Inativo'), 0)  AS inativos,
                    COALESCE(SUM(usua_status = 'Removido'), 0) AS removidos
            FROM usuario"
        );

        return $this->db->resultado();
    }

    /**
     * Opções do filtro de função.
     */
    public function listarFuncoes()
    {
        $this->db->query('SELECT func_id, func_nome FROM funcao ORDER BY func_nome ASC');

        return $this->db->resultados();
    }

    /**
     * Usuários da página atual (removidos não aparecem na lista).
     *
     * @param array $filtros ['pesquisa' => string, 'funcao' => int, 'status' => 'Ativo'|'Inativo']
     */
    public function listarPerfis(array $filtros, int $limite, int $offset)
    {
        [$where, $params] = $this->montarFiltros($filtros);

        // limite e offset são inteiros já convertidos, então podem ir direto no SQL
        $limite = max(1, $limite);
        $offset = max(0, $offset);

        $this->db->query(
            "SELECT u.usua_id, u.usua_nome, u.usua_email, u.func_id, f.func_nome,
                    u.usua_status AS status, CAST(u.usua_foto AS CHAR(300)) AS foto
               FROM usuario u
               JOIN funcao f ON f.func_id = u.func_id
              WHERE {$where}
              ORDER BY u.usua_nome ASC
              LIMIT {$limite} OFFSET {$offset}"
        );

        foreach ($params as $nome => $valor) {
            $this->db->bind($nome, $valor);
        }

        return $this->db->resultados();
    }

    /**
     * Total de usuários que batem com os filtros (para a paginação).
     */
    public function contarPerfis(array $filtros): int
    {
        [$where, $params] = $this->montarFiltros($filtros);

        $this->db->query(
            "SELECT COUNT(*) AS total
               FROM usuario u
               JOIN funcao f ON f.func_id = u.func_id
              WHERE {$where}"
        );

        foreach ($params as $nome => $valor) {
            $this->db->bind($nome, $valor);
        }

        return (int) $this->db->resultado()->total;
    }

    /**
     * Busca um usuário (nome e status) para validar antes de alterar.
     */
    public function buscarPorId(int $id)
    {
        $this->db->query(
            'SELECT usua_id, usua_nome, usua_status AS status
               FROM usuario
              WHERE usua_id = :id'
        );
        $this->db->bind(':id', $id);

        return $this->db->resultado();
    }

    public function buscarUsuariosAtivos(array $funcoes, ?int $excluirId = null): array
    {
        if (empty($funcoes)) {
            return [];
        }

        $placeholders = [];

        foreach ($funcoes as $indice => $funcao) {
            $placeholders[] = ':funcao' . $indice;
        }

        $sql = "
        SELECT
            u.usua_id,
            u.usua_nome,
            u.usua_email,
            u.func_id
        FROM usuario AS u
        WHERE u.usua_status = 'Ativo'
            AND u.usua_removido = 0
            AND u.func_id IN (" . implode(', ', $placeholders) . ")
        ";

        if ($excluirId !== null) {
            $sql .= " AND u.usua_id <> :excluirId";
        }

        $this->db->query($sql);

        foreach ($funcoes as $indice => $funcao) {
            $this->db->bind(
                ':funcao' . $indice,
                (int) $funcao
            );
        }

        if ($excluirId !== null) {
            $this->db->bind(':excluirId', $excluirId);
        }

        return $this->db->resultados();
    }


    private function emailExiste(string $email): bool
    {
        $this->db->query("
        SELECT usua_id
        FROM usuario
        WHERE usua_email = :email
        AND usua_removido = 0
        LIMIT 1
        ");

        $this->db->bind(':email', trim($email));

        return (bool) $this->db->resultado();
    }

    private function siapExiste(string $siap): bool
    {
        $this->db->query("
        SELECT usua_id
        FROM usuario
        WHERE usua_siap = :siap
        AND usua_removido = 0
        LIMIT 1
        ");

        $this->db->bind(':siap', trim($siap));

        return (bool) $this->db->resultado();
    }


    /**
     * Define o status como 'Ativo' ou 'Inativo'.
     * Não mexe em usuários removidos. É idempotente: clicar duas vezes
     * não faz o status "piscar" entre um valor e outro.
     */
    public function alterarStatus(int $id, string $novoStatus): bool
    {
        if (!in_array($novoStatus, self::STATUS_EDITAVEIS, true)) {
            return false;
        }

        $this->db->query(
            "UPDATE usuario
                SET usua_status = :status
              WHERE usua_id = :id
                AND usua_status <> 'Removido'"
        );
        $this->db->bind(':status', $novoStatus);
        $this->db->bind(':id', $id);

        return $this->db->executa();
    }

    /**
     * Monta o WHERE e os parâmetros a partir dos filtros da tela.
     * Cada parâmetro tem nome único: com prepares nativos do PDO,
     * repetir o mesmo :nome na query dá erro.
     */
    private function montarFiltros(array $filtros): array
    {
        $where  = ["u.usua_status <> 'Removido'"];
        $params = [];

        if (!empty($filtros['pesquisa'])) {
            $termo = '%' . addcslashes($filtros['pesquisa'], '%_\\') . '%';
            $where[] = '(u.usua_nome LIKE :pesquisa_nome OR u.usua_email LIKE :pesquisa_email)';
            $params[':pesquisa_nome']  = $termo;
            $params[':pesquisa_email'] = $termo;
        }

        if (!empty($filtros['funcao'])) {
            $where[] = 'u.func_id = :funcao';
            $params[':funcao'] = (int) $filtros['funcao'];
        }

        if (!empty($filtros['status']) && in_array($filtros['status'], self::STATUS_EDITAVEIS, true)) {
            $where[] = 'u.usua_status = :status';
            $params[':status'] = $filtros['status'];
        }

        return [implode(' AND ', $where), $params];
    }

    public function buscarPerfil(int $id, int $funcao)
    {
        // Subquery: pega apenas 1 telefone (evita linhas duplicadas no JOIN)
        $telefone = '(SELECT t.tele_numero
                        FROM telefone t
                       WHERE t.usua_id = u.usua_id
                    ORDER BY t.tele_id
                       LIMIT 1) AS telefone';                          // AJUSTE

        if ($funcao === 2) {
            // Aluno
            $sql = "SELECT u.usua_id, u.usua_nome, u.usua_email, u.usua_matricula, u.usua_foto,
                           tu.turm_curso, tu.turm_ano, f.func_nome,
                           $telefone
                      FROM usuario u
                INNER JOIN funcao f ON f.func_id = u.func_id          -- AJUSTE
                 LEFT JOIN turma tu ON tu.turm_id = u.turm_id       -- AJUSTE
                     WHERE u.usua_id = :id
                     LIMIT 1";
        } else {
            // Servidor (funções 1 e 3)
            $sql = "SELECT u.usua_id, u.usua_nome, u.usua_email, u.usua_siap, u.usua_foto,
                           s.seto_nome, f.func_nome,
                           $telefone
                      FROM usuario u
                INNER JOIN funcao f ON f.func_id = u.func_id          -- AJUSTE
                 LEFT JOIN setor s ON s.seto_id = u.seto_id          -- AJUSTE
                     WHERE u.usua_id = :id
                     LIMIT 1";
        }

        $this->db->query($sql);
        $this->db->bind(':id', $id);

        return $this->db->resultado(); // objeto ou false
    }

    /**
     * Verifica se o e-mail já pertence a OUTRO usuário.
     */
    public function emailEmUso(string $email, int $idIgnorar): bool
    {
        $this->db->query('SELECT usua_id FROM usuario WHERE usua_email = :email AND usua_id <> :id LIMIT 1');
        $this->db->bind(':email', $email);
        $this->db->bind(':id', $idIgnorar);

        return (bool) $this->db->resultado();
    }

    /**
     * Atualiza nome, e-mail, telefone e (opcionalmente) a foto.
     * $dados: ['nome' => ..., 'email' => ..., 'telefone' => ..., 'foto' => string|null]
     */
    public function atualizarPerfil(int $id, array $dados): bool
    {
        $sql = 'UPDATE usuario SET usua_nome = :nome, usua_email = :email';
        if (!empty($dados['foto'])) {
            $sql .= ', usua_foto = :foto';
        }
        $sql .= ' WHERE usua_id = :id';

        $this->db->query($sql);
        $this->db->bind(':nome', $dados['nome']);
        $this->db->bind(':email', $dados['email']);
        if (!empty($dados['foto'])) {
            $this->db->bind(':foto', $dados['foto']);
        }
        $this->db->bind(':id', $id);

        if (!$this->db->executa()) {
            return false;
        }

        return $this->salvarTelefone($id, $dados['telefone']);
    }

    /**
     * Atualiza o telefone se já existir; senão, insere.
     * (Consulta antes de decidir, pois o rowCount do UPDATE é 0 quando o valor não muda.)
     */
    private function salvarTelefone(int $id, string $telefone): bool
    {
        $this->db->query('SELECT tele_id FROM telefone WHERE usua_id = :id ORDER BY tele_id LIMIT 1'); // AJUSTE
        $this->db->bind(':id', $id);
        $existente = $this->db->resultado();

        if ($existente) {
            $this->db->query('UPDATE telefone SET tele_numero = :numero WHERE tele_id = :tele_id');
            $this->db->bind(':numero', $telefone);
            $this->db->bind(':tele_id', $existente->tele_id);
        } else {
            $this->db->query('INSERT INTO telefone (usua_id, tele_numero) VALUES (:id, :numero)');
            $this->db->bind(':id', $id);
            $this->db->bind(':numero', $telefone);
        }

        return $this->db->executa();
    }

    public function buscarHashSenha(int $id): ?string
    {
        $this->db->query('SELECT usua_senha FROM usuarios WHERE usua_id = :id LIMIT 1'); // AJUSTE
        $this->db->bind(':id', $id);
        $usuario = $this->db->resultado();

        return $usuario ? $usuario->usua_senha : null;
    }

    /**
     * Grava o novo hash. Nunca passe a senha em texto puro para cá.
     */
    public function atualizarSenha(int $id, string $hash): bool
    {
        $this->db->query('UPDATE usuarios SET usua_senha = :hash WHERE usua_id = :id'); // AJUSTE
        $this->db->bind(':hash', $hash);
        $this->db->bind(':id', $id);

        return $this->db->executa();
    }


    /* ================================= CRIAR NOVO PERFIL ======================================*/
    public const CAMPOS = [
        'nome' => [
            'rotulo' => 'Nome completo',
            'tipo' => 'text',
            'placeholder' => 'Nome completo',
            'autocomplete' => 'name',
        ],
        'email' => [
            'rotulo' => 'E-mail institucional',
            'tipo' => 'email',
            'placeholder' => 'nome@ifro.edu.br',
            'autocomplete' => 'email',
        ],
        'telefone' => [
            'rotulo' => 'Telefone',
            'tipo' => 'tel',
            'placeholder' => '(99) 99999-9999',
            'autocomplete' => 'tel',
        ],
        'setor' => [
            'rotulo' => 'Setor',
            'tipo' => 'select',
            'placeholder' => 'Selecione o setor',
        ],
        'siape' => [
            'rotulo' => 'SIAPE',
            'tipo' => 'text',
            'placeholder' => '0000000',
            'inputmode' => 'numeric',
            'maxlength' => 7,
        ],
        'matricula' => [
            'rotulo' => 'Matrícula',
            'tipo' => 'text',
            'placeholder' => '0000000000000',
            'inputmode' => 'numeric',
            'maxlength' => 13,
        ],
        'curso' => [
            'rotulo' => 'Curso',
            'tipo' => 'select',
            'placeholder' => 'Selecione o curso',
        ],
        'ano' => [
            'rotulo' => 'Ano',
            'tipo' => 'select',
            'placeholder' => 'Selecione o ano',
        ],
        'senha' => [
            'rotulo' => 'Senha',
            'tipo' => 'password',
            'placeholder' => 'Mínimo de 8 caracteres',
            'autocomplete' => 'new-password',
        ],
        'confirmar_senha' => [
            'rotulo' => 'Confirmar senha',
            'tipo' => 'password',
            'placeholder' => 'Repita a senha',
            'autocomplete' => 'new-password',
        ],
    ];

    public const PERFIS = [
        'servidor' => [
            'rotulo'  => 'Servidor',
            'icone'   => 'bi-person-badge',
            'func_id' => self::FUNC_SERVIDOR,
            'campos'  => ['nome', 'setor', 'email', 'siape', 'senha', 'telefone', 'confirmar_senha'],
        ],
        'estagiario' => [
            'rotulo'  => 'Estagiário',
            'icone'   => 'bi-mortarboard',
            'func_id' => self::FUNC_ESTAGIARIO,
            'campos'  => ['nome', 'email', 'matricula', 'curso', 'ano', 'telefone', 'senha', 'confirmar_senha'],
        ],
        'coordenador' => [
            'rotulo'  => 'Coordenador',
            'icone'   => 'bi-person-gear',
            'func_id' => self::FUNC_COORDENADOR,
            'campos'  => ['nome', 'setor', 'email', 'siape', 'senha', 'telefone', 'confirmar_senha'],
        ],
    ];

    /** Campo do formulário => coluna que precisa ser única (whitelist do SQL dinâmico). */
    private const COLUNAS_UNICAS = [
        'email'     => 'usua_email',
        'siape'     => 'usua_siap',
        'matricula' => 'usua_matricula',
    ];

    private const MENSAGENS_UNICAS = [
        'email'     => 'Este e-mail já está cadastrado.',
        'siape'     => 'Este SIAPE já está cadastrado.',
        'matricula' => 'Esta matrícula já está cadastrada.',
    ];

    /** @var array<string,array> cache das opções dos selects durante a requisição */
    private array $cacheOpcoes = [];

    /* ------------------------------------------------------------------
     * Metadados dos perfis
     * ----------------------------------------------------------------*/

    public static function perfilValido(string $perfil): bool
    {
        return isset(self::PERFIS[$perfil]);
    }

    /** Formato usado pelas abas da view: ['slug' => [rótulo, ícone]]. */
    public static function tiposDePerfil(): array
    {
        $tipos = [];
        foreach (self::PERFIS as $slug => $perfil) {
            $tipos[$slug] = [$perfil['rotulo'], $perfil['icone']];
        }
        return $tipos;
    }

    /** Campos do perfil na ordem de exibição, com as opções dos selects já preenchidas. */
    public function camposDoPerfil(string $perfil): array
    {
        $campos = [];
        foreach (self::PERFIS[$perfil]['campos'] as $nome) {
            $campo = self::CAMPOS[$nome];
            if ($campo['tipo'] === 'select') {
                $campo['opcoes'] = $this->opcoes($nome);
            }
            $campos[$nome] = $campo;
        }
        return $campos;
    }

    /** Opções de setor, curso e ano lidas das tabelas setor e turma. */
    private function opcoes(string $campo): array
    {
        if (isset($this->cacheOpcoes[$campo])) {
            return $this->cacheOpcoes[$campo];
        }

        switch ($campo) {
            case 'setor': // [seto_id => seto_nome]

                $this->db->query(
                    'SELECT seto_id, seto_nome
         FROM setor
         ORDER BY seto_nome'
                );

                $setores = $this->db->resultados();

                $opcoes = [];

                foreach ($setores as $setor) {
                    $opcoes[$setor->seto_id] = $setor->seto_nome;
                }

                break;


            case 'curso': // [nome do curso => nome do curso]

                $this->db->query(
                    'SELECT DISTINCT turm_curso
                    FROM turma
                    ORDER BY turm_curso'
                );

                $cursos = $this->db->resultados();

                $opcoes = [];

                foreach ($cursos as $curso) {
                    $opcoes[$curso->turm_curso] = $curso->turm_curso;
                }

                break;


            case 'ano': // [1 => "1º ano", ...]

                $this->db->query(
                    'SELECT DISTINCT turm_ano
                    FROM turma
                    ORDER BY turm_ano'
                );

                $anos = $this->db->resultados();

                $opcoes = [];

                foreach ($anos as $ano) {
                    $opcoes[$ano->turm_ano] = $ano->turm_ano . 'º ano';
                }

                break;

            default:
                $opcoes = [];
        }

        return $this->cacheOpcoes[$campo] = $opcoes;
    }
 
    /* ------------------------------------------------------------------
     * Normalização e validação
     * ----------------------------------------------------------------*/

    /** Mantém APENAS os campos do perfil (evita mass assignment) e limpa os valores. */
    public function normalizar(string $perfil, array $entrada): array
    {
        $dados = [];
        foreach (self::PERFIS[$perfil]['campos'] as $nome) {
            $valor = is_string($entrada[$nome] ?? null) ? $entrada[$nome] : '';

            $dados[$nome] = match ($nome) {
                'senha', 'confirmar_senha'     => $valor,                          // senha não recebe trim
                'telefone', 'siape', 'matricula' => preg_replace('/\D+/', '', $valor), // só dígitos
                'email'                        => mb_strtolower(trim($valor)),
                default                        => trim($valor),
            };
        }
        return $dados;
    }

    /** @return array<string,string> erros por campo (vazio = válido) */
    public function validar(string $perfil, array $dados): array
    {
        $erros = [];

        foreach (self::PERFIS[$perfil]['campos'] as $nome) {
            $valor = $dados[$nome] ?? '';

            if ($valor === '') {
                $erros[$nome] = 'Preencha este campo.';
                continue;
            }

            switch ($nome) {
                case 'nome':      // usua_nome é VARCHAR(100)
                    if (mb_strlen($valor) < 3 || mb_strlen($valor) > 100) {
                        $erros[$nome] = 'Informe o nome completo (3 a 100 caracteres).';
                    }
                    break;

                case 'email':     // usua_email é VARCHAR(100)
                    if (!filter_var($valor, FILTER_VALIDATE_EMAIL) || mb_strlen($valor) > 100) {
                        $erros[$nome] = 'Informe um e-mail válido.';
                    }
                    break;

                case 'telefone':
                    if (!preg_match('/^\d{10,11}$/', $valor)) {
                        $erros[$nome] = 'Informe DDD e número. Ex.: (69) 99999-9999.';
                    }
                    break;

                case 'siape':     // seus dados têm SIAPEs de 5 e 6 dígitos; aceita 5 a 7
                    if (!preg_match('/^\d{5,7}$/', $valor)) {
                        $erros[$nome] = 'O SIAPE tem de 5 a 7 dígitos.';
                    }
                    break;

                case 'matricula':
                    if (!preg_match('/^\d{13}$/', $valor)) {
                        $erros[$nome] = 'A matrícula tem 13 dígitos.';
                    }
                    break;

                case 'setor':
                case 'curso':
                case 'ano':
                    if (!array_key_exists($valor, $this->opcoes($nome))) {
                        $erros[$nome] = 'Escolha uma das opções da lista.';
                    }
                    break;

                case 'senha':
                    if (strlen($valor) < 8) {
                        $erros[$nome] = 'A senha precisa ter pelo menos 8 caracteres.';
                    }
                    break;

                case 'confirmar_senha':
                    if (!hash_equals((string) ($dados['senha'] ?? ''), $valor)) {
                        $erros[$nome] = 'As senhas não conferem.';
                    }
                    break;
            }
        }

        // Estagiário: a combinação curso + ano precisa existir na tabela turma
        if (
            isset($dados['curso'], $dados['ano']) && empty($erros['curso']) && empty($erros['ano'])
            && $this->idTurma($dados['curso'], (int) $dados['ano']) === null
        ) {
            $erros['ano'] = 'Não existe turma cadastrada para esse curso e ano.';
        }

        // Unicidade (só consulta o banco se o formato já estiver correto)
        foreach (array_keys(self::COLUNAS_UNICAS) as $campo) {
            if (isset($dados[$campo]) && empty($erros[$campo]) && $this->existe($campo, $dados[$campo])) {
                $erros[$campo] = self::MENSAGENS_UNICAS[$campo];
            }
        }

        return $erros;
    }
 
    /* ------------------------------------------------------------------
     * Consultas auxiliares
     * ----------------------------------------------------------------*/

    /** Confere se já existe usuário (inclusive removido) com esse e-mail/SIAPE/matrícula. */
    private function existe(string $campo, $valor): bool
    {
        $camposPermitidos = ['email', 'siap'];

        if (!in_array($campo, $camposPermitidos, true)) {
            return false;
        }

        $coluna = $campo === 'email'
            ? 'usua_email'
            : 'usua_siap';

        $this->db->query("
        SELECT usua_id
        FROM usuario
        WHERE {$coluna} = :valor
        AND usua_removido = 0
        LIMIT 1
        ");

        $this->db->bind(':valor', trim($valor));

        return (bool) $this->db->resultado();
    }



    private function idTurma(string $curso, int $ano): ?int
    {
        $this->db->query(
            'SELECT turm_id
            FROM turma
            WHERE turm_curso = :curso
            AND turm_ano = :ano
            LIMIT 1'
        );

        $this->db->bind(':curso', $curso);
        $this->db->bind(':ano', $ano);

        $turma = $this->db->resultado();

        $id = $turma ? (int) $turma->turm_id : null;

        return $id === false ? null : (int) $id;
    }

    /* ------------------------------------------------------------------
     * Persistência
     * ----------------------------------------------------------------*/

    /**
     * Grava usuário + telefone + notificação para os coordenadores, tudo em uma transação.
     * Espera dados já normalizados e validados.
     *
     * @param int $idAutor  usua_id de quem está cadastrando (não recebe a notificação)
     * @throws PDOException (SQLSTATE 23000 = UNIQUE violado por envio simultâneo)
     */
    public function criar(string $perfil, array $dados, int $idAutor): int
    {
        $this->db->iniciarTransacao();

        try {

            // Descobre a turma antes de cadastrar o usuário
            $turmId = null;

            if (isset($dados['curso'], $dados['ano'])) {
                $turmId = $this->idTurma(
                    $dados['curso'],
                    (int) $dados['ano']
                );
            }

            // Cadastra o usuário
            $this->db->query(
                'INSERT INTO usuario (
                usua_nome,
                usua_email,
                usua_siap,
                usua_status,
                usua_matricula,
                usua_senha,
                func_id,
                seto_id,
                turm_id
            ) VALUES (
                :nome,
                :email,
                :siap,
                :status,
                :matricula,
                :senha,
                :func_id,
                :seto_id,
                :turm_id
            )'
            );

            $this->db->bind(':nome', $dados['nome']);
            $this->db->bind(':email', $dados['email']);
            $this->db->bind(
                ':siap',
                isset($dados['siape']) && $dados['siape'] !== ''
                    ? (int) $dados['siape']
                    : null
            );
            $this->db->bind(':status', self::STATUS_INICIAL);
            $this->db->bind(
                ':matricula',
                $dados['matricula'] ?? null
            );
            $this->db->bind(
                ':senha',
                password_hash($dados['senha'], PASSWORD_DEFAULT)
            );
            $this->db->bind(
                ':func_id',
                self::PERFIS[$perfil]['func_id']
            );
            $this->db->bind(
                ':seto_id',
                isset($dados['setor']) && $dados['setor'] !== ''
                    ? (int) $dados['setor']
                    : null
            );
            $this->db->bind(':turm_id', $turmId);

            $this->db->executa();

            // ID do usuário recém-cadastrado
            $idUsuario = (int) $this->db->ultimoIdInserido();

            // Cadastra o telefone
            $this->db->query(
                'INSERT INTO telefone (
                tele_numero,
                usua_id
            ) VALUES (
                :numero,
                :usua
            )'
            );

            $this->db->bind(
                ':numero',
                $this->formatarTelefone($dados['telefone'])
            );

            $this->db->bind(':usua', $idUsuario);

            $this->db->executa();

            // Confirma o cadastro
            $this->db->confirmarTransacao();
        } catch (Throwable $ex) {

            // Desfaz tudo caso alguma operação falhe
            $this->db->desfazerTransacao();

            throw $ex;
        }

        /*
     * A notificação fica FORA da transação.
     * Assim, se o envio de notificação/e-mail falhar,
     * o cadastro do usuário não será desfeito.
     */
        try {
            $this->notificarCoordenadores(
                $dados['nome'],
                $idAutor
            );
        } catch (Throwable $ex) {
            error_log(
                '[Usuario::criar] Falha ao enviar notificação: '
                    . $ex->getMessage()
            );
        }

        return $idUsuario;
    }

    /** Mesmo padrão das notificações "Novo cadastro" que já existem no banco. */
    private function notificarCoordenadores(string $nomeNovo, int $idAutor): void
    {
        // 1. Cria a notificação
        $this->db->query(
            "INSERT INTO notificacao (
            noti_titulo,
            noti_mensagem,
            noti_tipo
        ) VALUES (
            :titulo,
            :mensagem,
            :tipo
        )"
        );

        $this->db->bind(':titulo', 'Novo cadastro');
        $this->db->bind(
            ':mensagem',
            'Novo usuário cadastrado: ' . $nomeNovo
        );
        $this->db->bind(':tipo', 'Usuário');

        $this->db->executa();

        $idNotificacao = (int) $this->db->ultimoIdInserido();

        // 2. Vincula a notificação aos coordenadores ativos
        $this->db->query(
            "INSERT INTO notificacao_usuario (
            noti_id,
            usua_id
        )
        SELECT
            :noti,
            usua_id
        FROM usuario
        WHERE func_id = :coordenador
            AND usua_status = 'Ativo'
            AND usua_removido = 0
            AND usua_id <> :autor"
        );

        $this->db->bind(':noti', $idNotificacao);
        $this->db->bind(':coordenador', self::FUNC_COORDENADOR);
        $this->db->bind(':autor', $idAutor);

        $this->db->executa();
    }

    /** tele_numero é VARCHAR(15): "(69) 99999-9999" (15) ou "(69) 9999-9999" (14). */
    private function formatarTelefone(string $digitos): string
    {
        return strlen($digitos) === 11
            ? sprintf('(%s) %s-%s', substr($digitos, 0, 2), substr($digitos, 2, 5), substr($digitos, 7))
            : sprintf('(%s) %s-%s', substr($digitos, 0, 2), substr($digitos, 2, 4), substr($digitos, 6));
    }
}
