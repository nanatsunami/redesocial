<?php
// Incluir arquivo de conexão com o banco de dados
require 'db.php';

// Verifica se o token foi passado na URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Verifique a consulta no banco de dados
    // A consulta só será feita após a verificação do token
    $stmt = $pdo->prepare("SELECT * FROM redefinicao_senha WHERE token = ?");
    $stmt->execute([$token]);
    $resetRequest = $stmt->fetch();

    // Verifique se o token foi encontrado no banco de dados
    if ($resetRequest) {
        // Verifique a data e hora do campo vencimento
        $vencimento = $resetRequest['vencimento'];

        // Converte as datas para objetos DateTime para comparação
        $dataVencimento = new DateTime($vencimento);
        $dataAtual = new DateTime();

        // Verifique se a data de vencimento é maior que a data atual
        if ($dataVencimento > $dataAtual) {
            // Se o token for válido, o formulário de redefinição de senha será exibido
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $newPassword = $_POST['new_password'];

                // Verifica se a nova senha foi preenchida
                if (!empty($newPassword)) {
                    // Atualiza a senha do usuário
                    $userId = $resetRequest['id_usuario'];
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                    // Atualiza a senha na tabela de usuários
                    $stmt = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
                    $stmt->execute([$hashedPassword, $userId]);

                    // Remova o token após a redefinição da senha
                    $stmt = $pdo->prepare("DELETE FROM redefinicao_senha WHERE token = ?");
                    $stmt->execute([$token]);

                    // Redireciona para a página de sucesso com a mensagem
                    header("Location: sucesso_redefinicao.php?status=sucesso");
                    exit();
                } else {
                    $erro = "Por favor, insira uma nova senha.";
                }
            }
        } else {
            // Caso o token seja inválido ou expirado
            header("Location: sucesso_envio.php?status=token_expirado");
            exit();
        }
    } else {
        // Caso não encontre o token no banco
        header("Location: sucesso_envio.php?status=token_nao_encontrado");
        exit();
    }
} else {
    // Se o token não for passado na URL
    echo "Token não fornecido.";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <div class="container-redefinir">
        <h2>Redefinir Senha</h2>

        <!-- Mostrar mensagem de erro, se existir -->
        <?php if (isset($erro) && !empty($erro)) : ?>
            <p class="erro"><?= $erro; ?></p>
        <?php endif; ?>

        <!-- Formulário de redefinição de senha -->
            <form method="POST" action="redefine_senha.php?token=<?php echo isset($_GET['token']) ? htmlspecialchars($_GET['token']) : ''; ?>">
            <label for="new_password">Nova Senha:</label>
            <input type="password" name="new_password" id="new_password" required>
            <button type="submit" class="botao-redefinir">Redefinir Senha</button>
        </form>
    </div>

</body>
</html>
