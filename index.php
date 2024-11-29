<?php
session_start(); // Inicia a sessão

// Inclua o arquivo de conexão com o banco de dados
include('db.php');

// Inicializa a variável de erro
$erro = "";

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verifica se as chaves existem no array $_POST
    if (isset($_POST['nome_usuario']) && isset($_POST['senha'])) {
        $nome_usuario = trim($_POST['nome_usuario']); // Remove espaços em branco
        $senha = trim($_POST['senha']); // Remove espaços em branco

        // Verifica se os campos não estão vazios
        if (!empty($nome_usuario) && !empty($senha)) {
            // Verifica as credenciais com o banco de dados
            $query = "SELECT * FROM usuarios WHERE nome_usuario = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$nome_usuario]);
            $usuario = $stmt->fetch();

            // Verifica se o usuário existe e se a senha está correta
            if ($usuario && password_verify($senha, $usuario['senha'])) {
                $_SESSION['usuario'] = $usuario['nome_usuario']; // Armazena o nome de usuário na sessão
                $_SESSION['id_usuario'] = $usuario['id']; // Armazena o id do usuário na sessão

                // Redireciona para a página do feed
                header('Location: feed.php');
                exit(); // Certifique-se de sair após o redirecionamento
            } else {
                $erro = "Credenciais inválidas. Tente novamente.";
            }
        } else {
            $erro = "Por favor, preencha todos os campos.";
        }
    } else {
        $erro = "Por favor, preencha todos os campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Login</title>
</head>
<body>
    <div class="container-cadastro">
        <h2>Login</h2>
        <?php if (!empty($erro)): ?> <!-- Verifica se $erro não está vazio -->
            <p class="erro"><?php echo $erro; ?></p>
        <?php endif; ?>
        <form action="" method="POST">
            <input type="text" name="nome_usuario" placeholder="Usuário" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <p class="redefine-senha"><a href="esqueceu_senha.php">Esqueceu sua senha?</a></p>
            <button type="submit" class="botao-login">Entrar</button>
        </form>
        <div class="signup">
            <p>Ainda não tem uma conta? <a href="cadastro.php">Cadastre-se</a></p>
        </div>
    </div>
</body>
</html>
