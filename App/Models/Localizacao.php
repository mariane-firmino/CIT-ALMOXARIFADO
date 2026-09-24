<?php

/**
 * Model: Localizacao
 * Premissa: tabela localizacao (loca_id, loca_nome).
 */
class Localizacao
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    public function listar(): array
    {
        $this->db->query("SELECT loca_id, loca_nome FROM localizacao ORDER BY loca_nome ASC");

        return $this->db->resultados();
    }

    public function localizacaoExiste(int $id): bool
    {
        $sql = 'SELECT 1
            FROM localizacao
            WHERE loca_id = :id
            LIMIT 1';

        $this->db->query($sql);

        $this->db->bind(':id', $id, PDO::PARAM_INT);

        $resultado = $this->db->resultado();

        return $resultado !== false;
    }
}
