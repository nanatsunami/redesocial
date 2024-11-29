<?php

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendVerificationEmail($email, $token) {
    $mail = new PHPMailer(true);

    try {
        // Configurações do servidor
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'projetoredesocial9@gmail.com';
        $mail->Password   = 'bwsr sgzd orwe dfqs';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Remetente e destinatário
        $mail->setFrom('projetoredesocial9@gmail.com', 'Projeto Rede Social');
        $mail->addAddress($email); // Adicionar o e-mail do usuário

        // Conteúdo do e-mail
        $mail->isHTML(true);
        $mail->Subject = 'Verificação de Cadastro';
        $mail->Body    = 'Obrigado por se cadastrar!<br>Seu token de verificação é: <b>' . $token . '</b>';
        $mail->AltBody = 'Obrigado por se cadastrar! Seu token de verificação é: ' . $token;

        $mail->send();
        return true; // E-mail enviado com sucesso
    } catch (Exception $e) {
        // Log de erro ou tratamento adicional
        error_log("Erro ao enviar e-mail: " . $mail->ErrorInfo); // Log do erro
        return false; // E-mail não pôde ser enviado
    }
}

function sendPasswordResetEmail($email, $token) {
    $mail = new PHPMailer(true);

    try {
        // Configurações do servidor
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'projetoredesocial9@gmail.com';
        $mail->Password   = 'bwsr sgzd orwe dfqs';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Remetente e destinatário
        $mail->setFrom('projetoredesocial9@gmail.com', 'Projeto Rede Social');
        $mail->addAddress($email); // Adicionar o e-mail do usuário

        // Conteúdo do e-mail
        $mail->isHTML(true);
        $mail->Subject = 'Redefinição de Senha';
        $mail->Body    = 'Você solicitou a redefinição de sua senha.<br>Para redefinir sua senha, clique no seguinte link: <a href="https://seusite.com/redefinir_senha.php?token=' . $token . '">Redefinir Senha</a>';
        $mail->AltBody = 'Você solicitou a redefinição de sua senha. Para redefinir sua senha, copie e cole o seguinte link no seu navegador: https://seusite.com/redefinir_senha.php?token=' . $token;

        $mail->send();
        return true; // E-mail enviado com sucesso
    } catch (Exception $e) {
        // Log de erro ou tratamento adicional
        return false; // E-mail não pôde ser enviado
    }
}