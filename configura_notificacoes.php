<?php
session_start();

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

// Verifica se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

// Inicializa a variável de erro
$erro = "";

// Verifica se o formulário foi enviado para atualizar as preferências
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $notificacao_comentarios_globais = isset($_POST['notificacao_comentarios_globais']) ? 1 : 0;
    $notificacao_curtidas_globais = isset($_POST['notificacao_curtidas_globais']) ? 1 : 0;

    // Atualiza as preferências de notificações
    $query = "UPDATE preferencias_notificacoes SET 
              notificacao_comentarios_globais = ?, 
              notificacao_curtidas_globais = ?
              WHERE id_usuario = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$notificacao_comentarios_globais, $notificacao_curtidas_globais, $id_usuario]);

    // Verifica se a atualização foi bem-sucedida
    if ($stmt->rowCount() > 0) {
        $erro = "Preferências de notificação atualizadas com sucesso!";
    } else {
        $erro = "Falha ao atualizar as preferências de notificação.";
    }
}

// Recupera as preferências de notificações do banco de dados
$query = "SELECT * FROM preferencias_notificacoes WHERE id_usuario = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$id_usuario]);
$preferencias = $stmt->fetch(PDO::FETCH_ASSOC);

// Se não encontrar as preferências, cria as preferências padrão
if (!$preferencias) {
    $query = "INSERT INTO preferencias_notificacoes (id_usuario, notificacao_comentarios_globais, notificacao_curtidas_globais)
              VALUES (?, 1, 1)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id_usuario]);
    $preferencias = ['notificacao_comentarios_globais' => 1, 'notificacao_curtidas_globais' => 1];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Configurações de Notificação</title>
</head>
<body>
    <div class="container">
        <h1>Configurações de Notificação</h1>

        <!-- Mensagem de erro ou sucesso -->
        <?php if (!empty($erro)): ?>
            <p class="erro"><?php echo $erro; ?></p>
        <?php endif; ?>

        <form action="" method="post">
            <label>
                <input type="checkbox" name="notificacao_comentarios_globais" <?php echo $preferencias['notificacao_comentarios_globais'] ? 'checked' : ''; ?>>
                Receber notificações de comentários globais
            </label>
            <br>
            <label>
                <input type="checkbox" name="notificacao_curtidas_globais" <?php echo $preferencias['notificacao_curtidas_globais'] ? 'checked' : ''; ?>>
                Receber notificações de curtidas globais
            </label>
            <br>
            <input type="submit" value="Salvar preferências">
        </form>
    </div>
</body>
</html>
