<?php
// Iniciar a sessão
session_start();

// Incluir arquivo de conexão com o banco de dados
require 'db.php';

$mensagem = ''; // Variável para armazenar mensagens

// Verifica se o token foi enviado corretamente via GET
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Limpeza do token (se necessário) para garantir que apenas o valor do token é utilizado
    $token = trim($token); // Remover espaços extras, caso existam

    // Exibe o token recebido para depuração
    //echo "Token recebido: " . $token . "<br>";

    // Verifica se o token é válido e se o usuário não foi verificado ainda
    $query = "SELECT * FROM usuarios WHERE token_verificacao = ? AND verificado = 0";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$token]);
    $usuario = $stmt->fetch();

    // Depuração: Mostrar o resultado da consulta
    if ($usuario) {
        //echo "Usuário encontrado: " . $usuario['email'] . "<br>";

        // Agora, tenta atualizar a tabela para marcar como verificado
        $query = "UPDATE usuarios SET verificado = 1, token_verificacao = NULL WHERE email = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$usuario['email']]);

        // Depuração: Verifica se a atualização foi bem-sucedida
        //echo "Linhas afetadas na atualização: " . $stmt->rowCount() . "<br>";

        if ($stmt->rowCount() > 0) {
            // Mensagem de sucesso
            $mensagem = "Cadastro verificado com sucesso! Você pode agora fazer login.";

            // Armazena uma variável de sessão para indicar que a verificação foi realizada
            $_SESSION['verificado'] = true;

            // Não redireciona aqui, apenas exibe a mensagem de sucesso
            //header("Location: index.php"); // REMOVER esse redirecionamento!
            //exit();
        } else {
            $mensagem = "Falha ao marcar como verificado ou já estava verificado.";
        }
    } else {
        $mensagem = "Token inválido ou já ativado.";
    }
} else {
    $mensagem = "Token não fornecido.";
}

// Se a verificação já foi realizada, evitar mostrar novamente
if (isset($_SESSION['verificado']) && $_SESSION['verificado'] == true) {
    // Aqui, não há necessidade de redirecionar, apenas mostramos a mensagem
    //header("Location: index.php");
    //exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Verificação de Cadastro</title>
</head>
<body>
    <div class="container-verificacao">
        <h2>Verificação de Cadastro</h2>
        <p><?php echo $mensagem; ?></p>
        <a href="index.php" class="botao-login">Ir para a página de login</a>
    </div>
</body>
</html>
