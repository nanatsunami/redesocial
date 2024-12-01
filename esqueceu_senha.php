<?php
session_start();
require 'db.php'; 
require 'envia_email.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Verifique se o e-mail existe no banco de dados
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Gera um token único
        $token = bin2hex(random_bytes(16));

        // Define a data de expiração (1 hora a partir de agora)
        $vencimento = date('Y-m-d H:i:s', strtotime('+1 hour'));  // A data de vencimento será 1 hora após a criação

        // Armazene o token no banco de dados usando o user_id
        $stmt = $pdo->prepare("INSERT INTO redefinicao_senha (id_usuario, token, criacao, vencimento) VALUES (?, ?, NOW(), ?)");
        $stmt->execute([$user['id'], $token, $vencimento]);


        // Envie o e-mail de redefinição de senha
        if (sendPasswordResetEmail($email, $token)) {
            // Redireciona para a página de sucesso
            header("Location: sucesso_envio.php?status=sucesso");
            exit(); // Finaliza o script após o redirecionamento
        } else {
            // Redireciona para a página de erro
            header("Location: sucesso_envio.php?status=erro");
            exit();
        }
    } else {
        // E-mail não encontrado, redireciona para a página de erro
        header("Location: sucesso_envio.php?status=email_nao_encontrado");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Esqueceu sua senha?</title>
</head>
<body>
    <div class="container-redefinir">
        <h2>Redefinir Senha</h2>
        <form action="esqueceu_senha.php" method="POST">
            <label for="email">Digite seu e-mail:</label>
            <input type="email" name="email" id="email" required>
            <br>
            <!-- Alterando para o botão com a classe botao-redefinir -->
            <button type="submit" class="botao-redefinir">Enviar E-mail de Redefinição</button>
        </form>
    </div>
</body>
</html>
