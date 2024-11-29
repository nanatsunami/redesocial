<?php
session_start();
require 'db.php'; 
require 'envia_email.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Verifique se o e-mail existe no banco de dados
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Gera um token único
        $token = bin2hex(random_bytes(16));

        // Define a data de expiração (por exemplo, 1 hora a partir de agora)
        $expiresAt = date('d-m-y H:i:s', strtotime('+1 hour'));

        // Armazene o token no banco de dados usando o user_id
        $stmt = $pdo->prepare("INSERT INTO redefinicao_senha (id_usuario, token, created_at, expires_at) VALUES (?, ?, NOW(), ?)");
        $stmt->execute([$user['id'], $token, $expiresAt]);

        // Envie o e-mail de redefinição de senha
        if (sendPasswordResetEmail($email, $token)) {
            echo "E-mail de redefinição de senha enviado com sucesso!";
        } else {
            echo "Erro ao enviar o e-mail. Tente novamente.";
        }
    } else {
        echo "E-mail não encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Redefinir Senha</title>
</head>
<body>
    <h2>Redefinir Senha</h2>
    <form action="redefinir_senha.php" method="POST">
        <label for="email">Digite seu e-mail:</label>
        <input type="email" name="email" id="email" required>
        <br>
        <button type="submit">Enviar E-mail de Redefinição</button>
    </form>
</body>
</html>