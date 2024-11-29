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

$mensagem = ''; // Variável para armazenar mensagens

// Verifica se o token foi enviado
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Verifica se o token é válido
    $query = "SELECT * FROM usuarios WHERE token_verificacao = ? AND verificado = 0";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$token]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        // Atualiza o status de verificação do usuário
        $query = "UPDATE usuarios SET verificado = 1, token_verificacao = NULL WHERE email = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$usuario['email']]);

        // Mensagem de sucesso
        $mensagem = "Cadastro verificado com sucesso! Você pode agora fazer login.";
    } else {
        $mensagem = "Token inválido ou já ativado.";
    }
} else {
    $mensagem = "Token não fornecido.";
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
    <h1>Verificação de Cadastro</h1>
    <p><?php echo $mensagem; ?></p>
    <a href="index.php">Ir para a página de login</a>
</body>
</html>