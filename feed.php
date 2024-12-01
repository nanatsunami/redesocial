<?php
session_start(); // Inicia a sessão

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario'])) {
    header('Location: index.php'); // Redireciona para a página de login se não estiver logado
    exit();
}

// Incluindo o arquivo de conexão com o banco de dados (PDO), funções de notificações, postagens e bloqueios
include('db.php');
include('notificacoes.php');
include('postagens.php');
include('bloqueio.php');  // Incluindo o arquivo de bloqueio

// ID do usuário logado
$id_usuario = $_SESSION['id_usuario'];  // Supondo que o ID do usuário esteja armazenado na sessão

$preferenciasGlobais = obterPreferenciasNotificacoes($pdo, $id_usuario);
$notificacoesNaoLidas = contarNotificacoesNaoLidas($pdo, $id_usuario);

// Contar notificações não lidas
$notificacoesNaoLidas = contarNotificacoesNaoLidas($pdo, $id_usuario);

// Processamento de ações do usuário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Deletar post
    if (isset($_POST['acao']) && $_POST['acao'] == 'deletar') {
        $post_id = $_POST['post_id'];
        $mensagem_sucesso = deletarPostagem($pdo, $id_usuario, $post_id) ? "Post deletado com sucesso!" : "Erro ao deletar o post.";
    }

    // Bloquear usuário
    elseif (isset($_POST['acao']) && $_POST['acao'] == 'bloquear') {
        $id_usuario_bloqueado = $_POST['id_usuario_bloqueado'];

        // Verifica se o usuário está tentando se bloquear
        if ($id_usuario_bloqueado == $id_usuario) {
            $mensagem_erro = "Você não pode bloquear a si mesmo.";
        } else {
            // Bloqueia o usuário utilizando a função do arquivo bloqueio.php
            if (bloquearUsuario($pdo, $id_usuario, $id_usuario_bloqueado)) {
                $mensagem_sucesso = "Usuário bloqueado com sucesso!";
            } else {
                $mensagem_erro = "Este usuário já está bloqueado.";
            }
        }
    }
    
    // Publicação de post
    elseif (!empty($_POST['texto'])) {
        $texto = $_POST['texto']; // Captura o texto do post
        $imagem = null;

        // Lógica para upload da imagem
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $imagem = fazerUploadImagem($_FILES['image']); // Função de upload, caso necessário
        }

        // Criar o post utilizando a função centralizada
        $mensagem_sucesso = criarPostagem($pdo, $id_usuario, $texto, $imagem) ? "Post publicado com sucesso!" : "Erro ao publicar o post.";
    }
}

// Carregar postagens do perfil de um usuário específico
if (isset($_GET['id_usuario'])) {
    $id_usuario_perfil = $_GET['id_usuario'];  // Obtém o ID do usuário da URL
} else {
    $id_usuario_perfil = $id_usuario;  // Caso não haja parâmetro, assume que é o perfil do usuário logado
}

$posts = carregarPostagens($pdo, $id_usuario, $id_usuario_perfil);


// Exibir posts
foreach ($posts as $post) {
    // Carregar comentários de cada post
    $comentarios = carregarComentarios($pdo, $id_usuario, $post['id']);
    
    // Carregar curtidas de cada post
    $curtidas = carregarCurtidas($pdo, $id_usuario, $post['id']);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Feed da Rede Social</title>
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

    <!-- Feed de Posts -->
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

            <!-- Formulário de Publicação -->
            <div class="feed-post-form"> 
                <form action="feed.php" method="POST" enctype="multipart/form-data">
                    <textarea name="texto" placeholder="O que você está pensando?" required></textarea>
                    <div class="botao-container">
                        <input type="file" name="image" id="image">
                        <button type="submit">Postar</button>
                    </div>
                </form>
            </div>

            <!-- Lista de Posts -->
            <div class="feed">
                <?php foreach ($posts as $post): ?>
                    <div class="post"> 
                        <?php if ($post['id_usuario'] == $id_usuario): ?>
                            <!-- Ícone de 3 bolinhas para o usuário dono do post -->
                            <div class="opcoes-post">
                                <button class="opcoes-btn" onclick="mostrarOpcoes(<?php echo $post['id']; ?>)">...</button>
                                <div id="opcoes-<?php echo $post['id']; ?>" class="opcoes-menu" style="display: none;">
                                    <form action="feed.php" method="POST">
                                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                        <button type="submit" name="acao" value="deletar">Deletar</button>
                                    </form>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Ícone de 3 bolinhas para posts de outros usuários -->
                            <div class="opcoes-post">
                                <button class="opcoes-btn" onclick="mostrarOpcoes(<?php echo $post['id']; ?>)">...</button>
                                <div id="opcoes-<?php echo $post['id']; ?>" class="opcoes-menu" style="display: none;">
                                    <form action="feed.php" method="POST">
                                        <input type="hidden" name="id_usuario_bloqueado" value="<?php echo $post['id_usuario']; ?>">
                                        <button type="submit" name="acao" value="bloquear">Bloquear Usuário</button>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($post['imagem'])): ?>
                            <img src="<?php echo htmlspecialchars($post['imagem']); ?>" alt="Post Image">
                        <?php endif; ?>
                        <p><?php echo htmlspecialchars($post['texto']); ?></p>
                        <small>
                            Postado por <a href="perfil.php?id_usuario=<?php echo $post['id_usuario']; ?>"><?php echo htmlspecialchars($post['nome_usuario']); ?></a>
                            em <?php echo $post['data_criacao']; ?>
                        </small>
                        <div class="interacao-feed"> 
                            <button>Curtir</button>
                            <button onclick="window.location.href='lista_comentarios.php?post_id=<?php echo $post['id']; ?>'">Comentários</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
    <script>
    function mostrarOpcoes(postId) {
        var menu = document.getElementById('opcoes-' + postId);
        // Alterna entre mostrar e esconder as opções
        if (menu.style.display === "none") {
            menu.style.display = "block";
        } else {
            menu.style.display = "none";
        }
    }
    
    function openComment(id) {
        alert('Abrir comentários para o post ID: ' + id);
    }
    </script>

</body>
</html>
