<?php

/**
 * Model: Categoria
 * Usado para preencher o filtro de categorias da tela Controlar Produto.
 */
class Categoria
{
    private $db;

    public function __construct()
    {
        $this->db = new Database;
    }

    public function listar(): array
    {
        $this->db->query("SELECT cate_id, cate_nome FROM categoria ORDER BY cate_nome ASC");

        return $this->db->resultados();
    }

    public function categoriaExiste(int $id): bool
{
    $sql = 'SELECT 1
            FROM categoria
            WHERE cate_id = :id
            LIMIT 1';

    $this->db->query($sql);

    $this->db->bind(':id', $id, PDO::PARAM_INT);

    $resultado = $this->db->resultado();

    return $resultado !== false;
}
}