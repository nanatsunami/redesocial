<?php
// Inicia a sessão para obter o ID do usuário logado
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php'); // Redireciona para a página de login, caso não esteja logado
    exit;
}

// ID do usuário logado
$id_usuario = $_SESSION['id_usuario']; 

// Conexão com o banco de dados
include 'db.php';

// Função para buscar notificações de comentários
function getNotificacoesComentarios($pdo, $id_usuario) {
    $stmt = $pdo->prepare("SELECT n.id, n.tipo, n.mensagem, n.data_criacao
                           FROM notificacoes n
                           JOIN comentarios c ON n.id_comentario = c.id
                           WHERE n.id_usuario = :id_usuario AND n.tipo = 'comentario'
                           ORDER BY n.data_criacao DESC");
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para buscar notificações de curtidas
function getNotificacoesCurtidas($pdo, $id_usuario) {
    $stmt = $pdo->prepare("SELECT n.id, n.tipo, n.mensagem, n.data_criacao
                           FROM notificacoes n
                           JOIN curtidas c ON n.id_curtida = c.id
                           WHERE n.id_usuario = :id_usuario AND n.tipo = 'curtida'
                           ORDER BY n.data_criacao DESC");
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Recupera as notificações de comentários e curtidas
$comentarios = getNotificacoesComentarios($pdo, $id_usuario);
$curtidas = getNotificacoesCurtidas($pdo, $id_usuario);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificações</title>
    <link rel="stylesheet" href="css\style.css"> 
</head>
<body>

    <!-- Cabeçalho fixo -->
    <header class="header-fixo">
        <div class="logo">
            <a href="feed.php" class="btn-rede-social">Rede Social</a>
        </div>
        <div class="header-botoes">
            <button class="foto-perfil" aria-label="Foto de Perfil" onclick="window.location.href='perfil.php'">
                <img src="img\cog.png" alt="Foto de Perfil" class="icone">
            </button>
            <button class="btn-notificacoes" aria-label="Notificações" onclick="window.location.href='notificacoes.php'">
            <img src="img\bell.png" alt="Notificações" class="icone">
            </button>
            <button class="btn-deslogar" aria-label="Deslogar" onclick="window.location.href='logout.php'">
                <img src="img\logout.png" alt="Deslogar" class="icone">
            </button>
        </div>
    </header>

    <!-- Título da Página -->
    <div class="header-notificacoes">
        <h1>Notificações</h1>
        <!-- Botão para as configurações -->
        <button class="btn-configuracoes-notif" aria-label="Configurações de Notificação" onclick="window.location.href='configura_notificacoes.php'">
            <img src="img/cog.png" alt="Configurações" class="icone">
        </button>
    </div>

    <div class="container-feed">
        <!-- Seção de Comentários -->
        <section id="comentarios">
            <h2>Comentários</h2>
            <?php if (count($comentarios) > 0): ?>
                <ul>
                    <?php foreach ($comentarios as $notificacao): ?>
                        <li>
                            <p><strong><?php echo htmlspecialchars($notificacao['mensagem']); ?></strong></p>
                            <p>Data: <?php echo date('d/m/Y H:i:s', strtotime($notificacao['data_criacao'])); ?></p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Você não tem notificações de comentários no momento.</p>
            <?php endif; ?>
        </section>

        <!-- Seção de Curtidas -->
        <section id="curtidas">
            <h2>Curtidas</h2>
            <?php if (count($curtidas) > 0): ?>
                <ul>
                    <?php foreach ($curtidas as $notificacao): ?>
                        <li>
                            <p><strong><?php echo htmlspecialchars($notificacao['mensagem']); ?></strong></p>
                            <p>Data: <?php echo date('d/m/Y H:i:s', strtotime($notificacao['data_criacao'])); ?></p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Você não tem notificações de curtidas no momento.</p>
            <?php endif; ?>
        </section>
    </div>

</body>
</html>
