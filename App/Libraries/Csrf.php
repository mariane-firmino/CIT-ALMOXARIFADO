<?php

/**
 * Helper: Csrf  (sugestão de lugar: App/Helpers ou App/Libraries, junto de Sessao)
 * Requer session_start() já executado.
 */
class Csrf
{
    /** Devolve o token da sessão (cria se ainda não existir). */
    public static function token(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf'];
    }

    /** Confere o token enviado no POST. Token vazio nunca é válido. */
    public static function valido(): bool
    {
        $esperado = $_SESSION['csrf'] ?? '';
        $enviado  = $_POST['csrf'] ?? '';

        return $esperado !== '' && is_string($enviado) && hash_equals($esperado, $enviado);
    }
}
