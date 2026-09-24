<?php

class EmailTemplate
{
    public static function padrao($titulo, $nomeDestinatario, $corpoHtml)
    {
        $nome = htmlspecialchars($nomeDestinatario, ENT_QUOTES, 'UTF-8');

        return "
        <div style='font-family: Arial, sans-serif; background-color: #f4f6f6; padding: 30px;'>
            <div style='max-width: 650px; margin: auto; background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.10);'>
                <div style='background-color: #075248; padding: 25px; text-align: center;'>
                    <h1 style='color: #ffffff; margin: 0; font-size: 24px;'>CIT Almoxarifado</h1>
                </div>
                <div style='padding: 30px;'>
                    <p>Olá, <strong>{$nome}</strong>!</p>
                    {$corpoHtml}
                </div>
                <div style='background-color: #f1f1f1; padding: 20px; text-align: center; color: #666666; font-size: 13px;'>
                    <p>Este é um e-mail automático. Por favor, não responda.</p>
                    <strong>CIT Almoxarifado</strong>
                </div>
            </div>
        </div>
        ";
    }
}