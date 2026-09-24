<?php

class User
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /* =================================== LOGIN ===================================*/
    public function checarEmail($email)
    {
        $this->db->query("SELECT usua_email FROM usuario WHERE usua_email = :e");
        $this->db->bind(":e", $email);

        if ($this->db->resultado()) :
            return true;
        else :
            return false;
        endif;
    }

    public function atualizarUltimoLogin($id)
    {
        $this->db->query(
            "UPDATE usuario
        SET usua_ultimo_login = NOW()
        WHERE usua_id = :id"
        );

        $this->db->bind(':id', $id);

        return $this->db->executa();
    }

    public function checarLogin($email, $senha)
    {
        $this->db->query("
        SELECT
            u.*,
            t.turm_curso,
            t.turm_ano,
            te.tele_numero,
            s.seto_nome
        FROM usuario u
        LEFT JOIN turma t
            ON u.turm_id = t.turm_id
        LEFT JOIN telefone te
            ON u.usua_id = te.usua_id
        LEFT JOIN setor s
            ON u.seto_id = s.seto_id
        WHERE u.usua_email = :e
        AND u.usua_removido = 0
        ");

        $this->db->bind(":e", $email);

        $resultado = $this->db->resultado();

        // E-mail não encontrado
        if (!$resultado) {
            return false;
        }

        // Senha incorreta
        if (!password_verify($senha, $resultado->usua_senha)) {
            return false;
        }

        // Retorna o usuário mesmo que esteja Inativo.
        // O Controller será responsável por verificar o status.
        return $resultado;
    }

    public function checarSiap($siap)
    {
        $this->db->query("
        SELECT usua_id
        FROM usuario
        WHERE usua_siap = :siap
        ");

        $this->db->bind(':siap', $siap);

        if ($this->db->resultado()) {
            return true;
        }

        return false;
    }
    /* ================================= FIM LOGIN ================================== */

    public function armazenar($dados)
    {
        $this->db->query("INSERT INTO usuario(usua_nome, usua_email, usua_siap, usua_matricula, usua_senha, func_id, seto_id, turm_id) VALUES (:nome, :email, :siap, :matricula, :senha, :funcao, :setor, :turma)");

        $this->db->bind('nome', $dados['nome']);
        $this->db->bind('email', $dados['email']);
        $this->db->bind('siap', $dados['siap']);
        $this->db->bind('matricula', $dados['matricula']);
        $this->db->bind('senha', $dados['senha']);
        $this->db->bind('funcao', $dados['funcao']);
        $this->db->bind('setor', $dados['setor']);
        $this->db->bind('turma', $dados['turma']);

        if (!$this->db->executa()) {
            return false;
        }

        $this->db->query("INSERT INTO telefone(tele_numero, usua_id) VALUES (:celular, :id_usuario)");
        $this->db->bind("celular", $dados['celular']);
        $this->db->bind("id_usuario", $this->db->ultimoIdInserido());


        if ($this->db->executa()) :
            return true;
        else :
            return false;
        endif;
    }

    public function buscarPorEmail($email)
    {
        $this->db->query("SELECT usua_id, usua_nome, usua_email FROM usuario WHERE usua_email = :email AND usua_removido = 0");
        $this->db->bind(':email', $email);
        return $this->db->resultado(); // retorna 1 linha ou false
    }

    public function salvarTokenSenha($usuaId, $token, $expira)
    {
        // invalida tokens antigos desse usuário antes de criar um novo
        $this->db->query("UPDATE token_senha SET toke_usado = 1 WHERE usua_id = :usuario AND toke_usado = 0");
        $this->db->bind(':usuario', $usuaId);
        $this->db->executa();

        $this->db->query(
            "INSERT INTO token_senha (toke_token, usua_id, toke_expira)
        VALUES (:token, :usuario, :expira)"
        );
        $this->db->bind(':token', $token);
        $this->db->bind(':usuario', $usuaId);
        $this->db->bind(':expira', $expira);

        return $this->db->executa();
    }

    public function buscarTokenValido($token)
    {
        $this->db->query(
            "SELECT ts.toke_id, ts.usua_id, u.usua_nome, u.usua_email
        FROM token_senha ts
        INNER JOIN usuario u ON u.usua_id = ts.usua_id
        WHERE ts.toke_token = :token
        AND ts.toke_usado = 0
        AND ts.toke_expira > NOW()"
        );
        $this->db->bind(':token', $token);
        return $this->db->resultado();
    }

    public function marcarTokenUsado($tokenId)
    {
        $this->db->query("UPDATE token_senha SET toke_usado = 1 WHERE toke_id = :id");
        $this->db->bind(':id', $tokenId);
        return $this->db->executa();
    }

    public function atualizarSenha($usuaId, $novaSenhaHash)
    {
        $this->db->query("UPDATE usuario SET usua_senha = :senha WHERE usua_id = :id");
        $this->db->bind(':senha', $novaSenhaHash);
        $this->db->bind(':id', $usuaId);
        return $this->db->executa();
    }
}
