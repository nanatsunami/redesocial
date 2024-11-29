<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $newPassword = $_POST['new_password'];

    // Verifique se o token é válido
    $stmt = $pdo->prepare("SELECT * FROM redefinicao_senha WHERE token = ?");
    $stmt->execute([$token]);
    $resetRequest = $stmt->fetch();

    if ($resetRequest) {
        // Atualize a senha do usuário
        $userId = $resetRequest['id_usuario'];
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Atualize a senha na tabela de usuários
        $stmt = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $id_usuario]);

        // Remova o token após a redefinição da senha
        $stmt = $pdo->prepare("DELETE FROM redefinicao_senha WHERE token = ?");
        $stmt->execute([$token]);

        echo "Senha redefinida com sucesso!";
    } else {
        echo "Token inválido.";
    }
} else {
    echo "Método de requisição inválido.";
}
?>