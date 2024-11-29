<?php
// Conectar ao banco de dados
include 'db.php';

session_start(); // Inicia a sessão

if (isset($_SESSION['id_usuario'])) {
    $id_usuario = $_SESSION['id_usuario'];
    // Agora você pode usar $id_usuario para buscar informações do usuário ou suas postagens
} else {
    // Se o ID do usuário não estiver na sessão, o usuário não está logado
    header('Location: index.php');
    exit();
}


// Consultar informações do usuário
$sql = "SELECT * FROM usuarios WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id_usuario);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Consultar publicações do usuário
$sql_publicacoes = "SELECT p.*, u.nome_usuario, u.foto_perfil FROM publicacoes p
                    JOIN usuarios u ON p.id_usuario = u.id
                    WHERE p.id_usuario = :id_usuario
                    ORDER BY p.data_criacao DESC";
$stmt_publicacoes = $pdo->prepare($sql_publicacoes);
$stmt_publicacoes->bindParam(':id_usuario', $id_usuario);
$stmt_publicacoes->execute();
$publicacoes = $stmt_publicacoes->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Perfil do Usuário</title>
</head>
<body>

<!-- Fundo -->
<div class="background"></div>

<!-- Caixa de perfil -->
<div class="container-perfil">
    <!-- Foto de perfil -->
    <div class="foto-perfil-container">
        <img class="foto-perfil" src="uploads/<?php echo $usuario['foto_perfil']; ?>" alt="Foto de Perfil">
    </div>
    <div class="nome-usuario"><?php echo $usuario['nome_usuario']; ?></div>

    <!-- Publicações do usuário -->
    <div class="posts-container">
        <?php foreach ($publicacoes as $post): ?>
            <div class="post">
                <div class="post-header">
                    <img src="uploads/<?php echo $post['foto_perfil']; ?>" alt="Foto de Perfil" class="post-photo">
                    <div class="nome-usuario"><?php echo $post['nome_usuario']; ?></div>
                </div>
                <div class="post-content">
                    <img src="uploads/<?php echo $post['imagem']; ?>" alt="Imagem da Publicação" class="post-image">
                    <p><?php echo $post['descricao']; ?></p>
                </div>
                <div class="post-acoes">
                    <!-- Botões de curtir e comentar -->
                    <button>Curtir</button>
                    <button>Comentários</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>
