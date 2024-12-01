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

        // Definir a codificação do email para UTF-8
        $mail->CharSet = 'UTF-8';  // Adicione esta linha

        // Remetente e destinatário
        $mail->setFrom('projetoredesocial9@gmail.com', 'Projeto Rede Social');
        $mail->addAddress($email); // Adicionar o e-mail do usuário

        // Definir a URL de verificação com o token como parâmetro
        // Aqui estamos apenas passando o valor do token, não a URL completa
        $urlVerificacao = "http://localhost/ProjetoRedeSocial/verifica_cadastro.php?token=" . $token;

        // O corpo do email
        $mail->Body    = 'Obrigado por se cadastrar!<br>Para completar o seu cadastro, clique no link abaixo para verificar sua conta:<br><a href="' . $urlVerificacao . '">Clique aqui para verificar sua conta</a>';
        $mail->AltBody = 'Obrigado por se cadastrar! Para completar o seu cadastro, copie e cole o seguinte link no seu navegador: ' . $urlVerificacao;

        // Envia o e-mail
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

        // Definir a codificação do email para UTF-8
        $mail->CharSet = 'UTF-8';  // Adicione esta linha

        // Remetente e destinatário
        $mail->setFrom('projetoredesocial9@gmail.com', 'Projeto Rede Social');
        $mail->addAddress($email); // Adicionar o e-mail do usuário

        // Define a URL de redefinição de senha com o token
        $urlRedefinicao = "http://localhost/ProjetoRedeSocial/redefine_senha.php?token=" . $token;


        // Conteúdo do e-mail
        $mail->isHTML(true);
        $mail->Subject = 'Redefinição de Senha';
        $mail->Body    = 'Você solicitou a redefinição de sua senha.<br>Para redefinir sua senha, clique no link abaixo:<br><a href="' . $urlRedefinicao . '">Clique aqui para redefinir sua senha</a>';
        $mail->AltBody = 'Você solicitou a redefinição de sua senha. Para redefinir sua senha, copie e cole o seguinte link no seu navegador: ' . $urlRedefinicao;

        // Envia o e-mail
        $mail->send();
        return true; // E-mail enviado com sucesso
    } catch (Exception $e) {
        // Log de erro ou tratamento adicional
        return false; // E-mail não pôde ser enviado
    }
}