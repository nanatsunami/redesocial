<?php
session_start(); // Inicia a sessão

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario'])) {
    header('Location: index.php'); // Redireciona para a página de login se não estiver logado
    exit();
}

// Incluindo o arquivo de conexão com o banco de dados (PDO), funções de notificações e postagens
include('db.php');
include('notificacoes.php');
include('postagens.php'); 
include('usuario.php');

// ID do usuário logado
$id_usuario = $_SESSION['id_usuario'];  // Supondo que o ID do usuário esteja armazenado na sessão

$preferenciasGlobais = obterPreferenciasNotificacoes($pdo, $id_usuario);
$notificacoesNaoLidas = contarNotificacoesNaoLidas($pdo, $id_usuario);

// Recebe o ID da postagem para exibir os comentários
$post_id = $_GET['post_id'] ?? null;
if (!$post_id) {
    echo "Post não encontrado.";
    exit;
}

// Carregar os comentários da postagem
$comentarios = carregarComentarios($pdo, $id_usuario, $post_id);

// Carregar as curtidas da postagem
$curtidas = carregarCurtidas($pdo, $id_usuario, $post_id);

// Carregar as informações do post
$post = carregarPostagemPorId($pdo, $post_id);

// Processamento de novo comentário
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comentario'])) {
    $comentario_texto = $_POST['comentario'];
    if (comentarPostagem($pdo, $id_usuario, $post_id, $comentario_texto)) {
        $mensagem_sucesso = "Comentário adicionado com sucesso!";
        // Recarregar os comentários após adicionar o novo
        header("Location: lista_comentarios.php?post_id=$post_id");
        exit(); // Evitar que o código continue a execução após o redirecionamento
    } else {
        $mensagem_erro = "Erro ao adicionar o comentário.";
    }
}

// Processamento de curtidas
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao'])) {
    if ($_POST['acao'] == 'curtir') {
        // Verifica se a ação é de curtir um post ou um comentário
        if (isset($_POST['post_id']) && !empty($_POST['post_id'])) {
            // Curtir post
            $post_id = $_POST['post_id'];
            adicionarOuRemoverCurtir($pdo, $id_usuario, $post_id, 'post');
        } elseif (isset($_POST['comentario_id']) && !empty($_POST['comentario_id'])) {
            // Curtir comentário
            $comentario_id = $_POST['comentario_id'];
            adicionarOuRemoverCurtir($pdo, $id_usuario, $comentario_id, 'comentario');
        } else {
            echo "Erro: ID da postagem ou do comentário não encontrado.";
        }
    }
}

// Processa a ação de deletar a postagem
if (isset($_POST['acao']) && $_POST['acao'] == 'deletar') {
    $post_id = $_POST['post_id'];
    if (deletarPostagem($pdo, $id_usuario, $post_id)) {
        $mensagem_sucesso = "Postagem deletada com sucesso!";
        header("Location: feed.php"); // Redireciona para o feed
        exit();
    } else {
        $mensagem_erro = "Erro ao deletar a postagem.";
    }
}

// Processa a ação de deletar um comentário
if (isset($_POST['acao']) && $_POST['acao'] == 'deletar_comentario') {
    $comentario_id = $_POST['comentario_id'];
    if (deletarComentario($pdo, $id_usuario, $comentario_id)) {
        $mensagem_sucesso = "Comentário deletado com sucesso!";
        // Redirecionar para recarregar a página com os comentários atualizados
        header("Location: lista_comentarios.php?post_id=$post_id");
        exit();
    } else {
        $mensagem_erro = "Erro ao deletar o comentário.";
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Comentários - <?php echo htmlspecialchars($post['texto']); ?></title>
</head>
<body>
    <!-- Cabeçalho fixo -->
    <header class="header-fixo">
        <div class="logo">
            <a href="feed.php" class="btn-rede-social">Rede Social</a>
        </div>
        <div class="header-botoes">
            <button class="foto-perfil" aria-label="Foto de Perfil" onclick="window.location.href='perfil.php'">
                <img src="img\profile.png" alt="Foto de Perfil" class="icone">
            </button>
            <button class="btn-notificacoes" aria-label="Notificações" onclick="window.location.href='lista_notificacoes.php'">
                <img src="img\bell.png" alt="Notificações" class="icone">
                <?php if ($notificacoesNaoLidas > 0): ?>
                    <span class="pontinho-vermelho"></span>
                <?php endif; ?>
            </button>
            <button class="btn-deslogar" aria-label="Deslogar" onclick="window.location.href='logout.php'">
                <img src="img\logout.png" alt="Deslogar" class="icone">
            </button>
        </div>
    </header>

    <!-- Página de Comentários do Post -->
    <main class="main-feed"> 
        <div class="container-feed">

            <!-- Mensagens de Sucesso/Erro -->
            <div class="mensagem-status">
                <?php if (!empty($mensagem_sucesso)): ?>
                    <div class="sucesso">
                        <p><?php echo htmlspecialchars($mensagem_sucesso); ?></p>
                    </div>
                <?php endif; ?>
                <?php if (!empty($mensagem_erro)): ?>
                    <div class="erro">
                        <p><?php echo htmlspecialchars($mensagem_erro); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Exibindo o post -->
            <div class="post">
                <?php if ($post['id_usuario'] == $id_usuario): ?>
                    <!-- Ícone de 3 bolinhas para o usuário dono do post -->
                    <div class="opcoes-post">
                        <button class="opcoes-btn" onclick="mostrarOpcoes(<?php echo $post['id']; ?>)">...</button>
                        <div id="opcoes-<?php echo $post['id']; ?>" class="opcoes-menu" style="display: none;">
                            <form action="lista_comentarios.php?post_id=<?php echo $post['id']; ?>" method="POST">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <button type="submit" name="acao" value="deletar">Deletar</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
                <p><?php echo htmlspecialchars($post['texto']); ?></p>
                <small>
                    Postado por <a href="perfil.php?id_usuario=<?php echo $post['id_usuario']; ?>"><?php echo htmlspecialchars($post['nome_usuario']); ?></a>
                    em <?php echo $post['data_criacao']; ?>
                </small>
                <div class="interacao-feed"> 
                    <form action="lista_comentarios.php?post_id=<?php echo $post_id; ?>" method="POST">
                        <button type="submit" name="acao" value="curtir">
                            <?php echo contarCurtidas($pdo, $post_id) . " curtidas"; ?>
                        </button>
                        <input type="hidden" name="post_id" value="<?php echo $post_id; ?>" />
                    </form>
                    <button onclick="window.location.href='lista_comentarios.php?post_id=<?php echo $post_id; ?>'">Comentários</button>
                </div>
            </div>

            <!-- Formulário de Comentário -->
            <div class="feed-post-form"> 
                <form action="lista_comentarios.php?post_id=<?php echo $post_id; ?>" method="POST">
                    <textarea name="comentario" placeholder="Adicione um comentário" required></textarea>
                    <button type="submit">Comentar</button>
                </form>
            </div>

            <!-- Exibindo os comentários -->
            <?php foreach ($comentarios as $comentario): ?>
                <div class="comentario">
                    <?php if ($comentario['id_usuario'] == $id_usuario): ?>
                        <!-- Ícone de 3 bolinhas para o usuário dono do comentário -->
                        <div class="opcoes-comentario">
                            <button class="opcoes-btn" onclick="mostrarOpcoesComentario(<?php echo $comentario['id']; ?>)">...</button>
                            <div id="opcoes-comentario-<?php echo $comentario['id']; ?>" class="opcoes-menu" style="display: none;">
                                <form action="lista_comentarios.php?post_id=<?php echo $post_id; ?>" method="POST">
                                    <input type="hidden" name="comentario_id" value="<?php echo $comentario['id']; ?>">
                                    <button type="submit" name="acao" value="deletar_comentario">Deletar</button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                    <p><?php echo htmlspecialchars($comentario['texto']); ?></p>
                    <small>
                        Comentado por <a href="perfil.php?id_usuario=<?php echo $comentario['id_usuario']; ?>"><?php echo htmlspecialchars($comentario['nome_usuario']); ?></a>
                        em <?php echo $comentario['data_criacao']; ?>
                    </small>
                    <div class="interacao-comentario">
                        <form action="lista_comentarios.php?post_id=<?php echo $post_id; ?>" method="POST">
                            <button type="submit" name="acao" value="curtir">
                                <?php echo contarCurtidas($pdo, $comentario['id']) . " curtidas"; ?>
                            </button>
                            <input type="hidden" name="comentario_id" value="<?php echo $comentario['id']; ?>" />
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>
