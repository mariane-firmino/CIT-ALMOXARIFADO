<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Email
{
    public static function enviar($destinatario, $nomeDestinatario, $assunto, $mensagem)
    {
        $mail = new PHPMailer(true);

        try {

            // Configurações SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'EMAIL';
            $mail->Password   = 'SENHA_DO_EMAIL';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Remetente
            $mail->setFrom(
                'mariane.firmino@estudante.ifro.edu.br',
                'CIT Almoxarifado'
            );

            // Destinatário
            $mail->addAddress($destinatario, $nomeDestinatario);

            // Configuração da mensagem
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';

            $mail->Subject = $assunto;
            $mail->Body    = $mensagem;

            $mail->AltBody = strip_tags($mensagem);

            $mail->send();

            return true;
        } catch (Exception $e) {

            error_log('Erro PHPMailer: ' . $mail->ErrorInfo);

            return false;
        }
    }
}
