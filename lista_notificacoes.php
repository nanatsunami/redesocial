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
include 'notificacoes.php';
include 'bloqueio.php';

// Marcar notificações como lidas
marcarNotificacoesComoLidas($pdo, $id_usuario);

// Recupera as notificações de comentários e curtidas
$comentarios = getNotificacoesComentarios($pdo, $id_usuario);
$curtidas = getNotificacoesCurtidas($pdo, $id_usuario);

// Função para contar notificações não lidas
$notificacoesNaoLidas = contarNotificacoesNaoLidas($pdo, $id_usuario);

// Verifica se o id_usuario_bloqueado foi passado na URL para bloqueio
if (isset($_GET['id_usuario_bloqueado'])) {
    $id_usuario_bloqueado = $_GET['id_usuario_bloqueado'];

    // Verifica se o id_usuario_bloqueado é diferente do id_usuario logado
    if ($id_usuario_bloqueado != $id_usuario) {
        // Chama a função para bloquear o usuário
        $bloqueadoComSucesso = bloquearUsuario($pdo, $id_usuario, $id_usuario_bloqueado);
        
        if ($bloqueadoComSucesso) {
            // Redireciona de volta para a página de notificações após o bloqueio
            header('Location: lista_notificacoes.php');
            exit;
        } else {
            echo "Erro: O usuário já está bloqueado.";
        }
    } else {
        echo "Você não pode se bloquear.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificações</title>
    <link rel="stylesheet" href="css/style.css"> 
</head>
<body>
    <!-- Cabeçalho fixo -->
    <header class="header-fixo">
        <div class="logo">
            <a href="feed.php" class="btn-rede-social">Rede Social</a>
        </div>
        <div class="header-botoes">
            <button class="foto-perfil" aria-label="Foto de Perfil" onclick="window.location.href='perfil.php'">
                <img src="img/profile.png" alt="Foto de Perfil" class="icone">
            </button>
            <button class="btn-notificacoes" aria-label="Notificações" onclick="window.location.href='lista_notificacoes.php'">
                <img src="img/bell.png" alt="Notificações" class="icone">
                <?php if ($notificacoesNaoLidas > 0): ?>
                    <span class="pontinho-vermelho"></span>
                <?php endif; ?>
            </button>
            <button class="btn-deslogar" aria-label="Deslogar" onclick="window.location.href='logout.php'">
                <img src="img/logout.png" alt="Deslogar" class="icone">
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
                            <!-- Adicionando o link de bloqueio -->
                            <a href="lista_notificacoes.php?id_usuario_bloqueado=<?php echo $notificacao['id_usuario']; ?>" class="link-bloquear">Bloquear este usuário</a>
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
                            <!-- Adicionando o link de bloqueio -->
                            <a href="lista_notificacoes.php?id_usuario_bloqueado=<?php echo $notificacao['id_usuario']; ?>" class="link-bloquear">Bloquear este usuário</a>
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
