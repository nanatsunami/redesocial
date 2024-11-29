<?php
// Conexão com o banco de dados
$host = 'localhost';
$db = 'rede_social';
$user = 'admin';
$pass = 'senha';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// Inicializa a variável de erro
$erro = "";

// Verifica se os dados foram enviados
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $nome_usuario = trim($_POST['nome_usuario']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    // Validação de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Email inválido.";
    } else {
        // Verifica se o email já está cadastrado
        $query = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $erro = "Este email já está cadastrado.";
        } else {
            // Verifica se o nome de usuário já está cadastrado
            $query = "SELECT * FROM usuarios WHERE nome_usuario = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$nome_usuario]);

            if ($stmt->rowCount() > 0) {
                $erro = "Este nome de usuário já está em uso.";
            } else {
                // Insere os dados no banco de dados
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                $query = "INSERT INTO usuarios (nome, nome_usuario, email, senha, verificado) VALUES (?, ?, ?, ?, 0)";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$nome, $nome_usuario, $email, $senhaHash]);

                // Verifica se a inserção foi bem-sucedida
                if ($stmt->rowCount() > 0) {
                    // Obtém o ID do usuário recém-criado
                    $id_usuario = $pdo->lastInsertId();

                    // Insere as preferências de notificações para esse usuário (tudo ativado por padrão)
                    $queryPreferencias = "INSERT INTO preferencias_notificacoes (id_usuario, notificacao_comentarios_globais, notificacao_curtidas_globais)
                                          VALUES (:id_usuario, 1, 1)"; // 1 significa que as notificações estão ativadas
                    $stmtPreferencias = $pdo->prepare($queryPreferencias);
                    $stmtPreferencias->bindParam(':id_usuario', $id_usuario);
                    $stmtPreferencias->execute();

                    // Gera um token de verificação
                    $token = bin2hex(random_bytes(16));

                    // Insere o token no banco de dados
                    $query = "UPDATE usuarios SET token_verificacao = ? WHERE email = ?";
                    $stmt = $pdo->prepare($query);
                    $stmt->execute([$token, $email]);

                    // Verifica se a atualização foi bem-sucedida
                    if ($stmt->rowCount() > 0) {
                        // Envia um email com o token de verificação
                        require 'envia_email.php'; // Inclua o arquivo de envio de e-mail
                        if (sendVerificationEmail($email, $token)) {
                            // Redireciona para a página de sucesso
                            header('Location: sucesso.php');
                            exit();
                        } else {
                            $erro = "Falha ao enviar o e-mail de verificação.";
                        }
                    } else {
                        $erro = "Falha ao atualizar o token de verificação.";
                    }
                } else {
                    $erro = "Falha ao cadastrar o usuário.";
                }
            }
        }
    }
}

?>

<!-- Formulário de cadastro -->
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Cadastro</title>
</head>
<body>
    <div class="container-cadastro">
        <h1>Cadastro</h1>

        <!-- Mensagem de erro -->
        <?php if (!empty($erro)): ?> <!-- Verifica se $erro não está vazio -->
            <p class="erro"><?php echo $erro; ?></p>
        <?php endif; ?>

        <form action="" method="post">
            <input type="text" id="nome" name="nome" placeholder="Insira seu nome" required>

            <input type="text" id="nome_usuario" name="nome_usuario" placeholder="Insira seu usuário" required>
            
            <input type="email" id="email" name="email" placeholder="Insira seu email" required>
            
            <input type="password" id="senha" name="senha" placeholder="Insira sua senha" required>
            
            <input type="submit" class="botao-cadastro" value="Cadastrar">
        </form>
        <div class="login">
            <p>Já tem uma conta? <a href="index.php">Faça login</a></p>
        </div>
    </div>
</body>
</html>
