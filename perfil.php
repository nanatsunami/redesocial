<?php
session_start();

// Incluindo os arquivos necessários
include('db.php');
include('notificacoes.php');
include('postagens.php'); // Funções de postagens
include('usuario.php'); // Incluindo o arquivo com as funções de usuário

// ID do usuário logado
$id_usuario_logado = $_SESSION['id_usuario']; // ID do usuário logado

// Verifica se o usuário está logado
if (!isset($id_usuario_logado)) {
    header('Location: index.php'); // Redireciona para a página de login se não estiver logado
    exit;
}

// Definir a visibilidade do perfil (logado ou de outro usuário)
if (isset($_GET['id_usuario'])) {
    $id_usuario = $_GET['id_usuario']; // ID do usuário a ser visualizado (de outro usuário)
    $is_perfil_visivel = true; // O perfil de outro usuário está sendo visualizado
} else {
    $id_usuario = $_SESSION['id_usuario']; // O perfil do usuário logado
    $is_perfil_visivel = false; // O perfil do usuário logado está sendo visualizado
}

// Carregar o perfil do usuário
$usuario = carregarPerfil($pdo, $id_usuario);

// Verificação de erro no retorno de $usuario
if (empty($usuario)) {
    die("Erro ao carregar o perfil do usuário.");
}

// Carregar as postagens do perfil de um usuário específico
$posts = carregarPostagensPerfil($pdo, $id_usuario); // Usando a função ajustada

// Agora que $id_usuario está definido, podemos carregar as notificações
$preferenciasGlobais = obterPreferenciasNotificacoes($pdo, $id_usuario);
$notificacoesNaoLidas = contarNotificacoesNaoLidas($pdo, $id_usuario);

// Verificação para curtir um post
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao'])) {
    if ($_POST['acao'] == 'curtir') {
        $post_id = $_POST['post_id'];
        $resultado = adicionarOuRemoverCurtir($pdo, $id_usuario_logado, $post_id, 'post');
    }
}

// Lógica de deletar post
$mensagem_sucesso = ''; // Inicializando a variável para mensagens de sucesso
$mensagem_erro = ''; // Inicializando a variável de erro

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'deletar') {
    $post_id = $_POST['post_id'];

    if (deletarPostagem($pdo, $id_usuario_logado, $post_id)) {
        $mensagem_sucesso = "Postagem deletada com sucesso!"; // Mensagem de sucesso
    } else {
        $mensagem_erro = "Erro ao deletar o post."; // Mensagem de erro
    }
}
// Carregar o ID do tema do usuário
$query = "SELECT id_tema FROM usuarios WHERE id = :id_usuario";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':id_usuario', $id_usuario);
$stmt->execute();
$usuario_tema = $stmt->fetch(PDO::FETCH_ASSOC);  // Use uma variável diferente para os dados do tema

// Carregar as cores do tema
$query = "SELECT * FROM temas WHERE id = :id_tema";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':id_tema', $usuario_tema['id_tema']);
$stmt->execute();
$tema = $stmt->fetch(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Perfil de <?php echo htmlspecialchars($usuario['nome_usuario']); ?></title>
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

    <!-- Perfil do Usuário -->
    <main class="perfil-main">
        <div class="container-feed">
            <div class="perfil-info">
                <div class="nome-usuario">
                    <h1><?php echo htmlspecialchars($usuario['nome_usuario']); ?></h1>
                    <?php if (!$is_perfil_visivel): ?>
                        <button onclick="window.location.href='edita_perfil.php'">Editar Perfil</button>
                    <?php endif; ?>
                </div>

                <div class="informacoes-usuario">
                    <p><strong>Data de cadastro:</strong> <?php echo date('d/m/Y', strtotime($usuario['data_criacao'])); ?></p>
                </div>
            </div>

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

            <div class="feed">
            <?php foreach ($posts as $post): ?>
                <div class="post">
                    <!-- Se o post for do usuário logado, mostrar opções de deletar -->
                    <?php if ($post['id_usuario'] == $id_usuario_logado): ?>
                        <div class="opcoes-post">
                            <button class="opcoes-btn" onclick="mostrarOpcoes(<?php echo $post['id']; ?>)">...</button>
                            <div id="opcoes-<?php echo $post['id']; ?>" class="opcoes-menu" style="display: none;">
                                <form action="perfil.php" method="POST">
                                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                    <button type="submit" name="acao" value="deletar">Deletar</button>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Se o post for de outro usuário, mostrar opção de bloquear -->
                        <div class="opcoes-post">
                            <button class="opcoes-btn" onclick="mostrarOpcoes(<?php echo $post['id']; ?>)">...</button>
                            <div id="opcoes-<?php echo $post['id']; ?>" class="opcoes-menu" style="display: none;">
                                <form action="perfil.php" method="POST">
                                    <input type="hidden" name="id_usuario_bloqueado" value="<?php echo $post['id_usuario']; ?>">
                                    <button type="submit" name="acao" value="bloquear">Bloquear</button>
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
                        <?php
                            $numero_curtidas = contarCurtidas($pdo, $post['id']);
                        ?>
                        <form action="perfil.php" method="POST">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                            <button type="submit" name="acao" value="curtir">
                                <?php echo ($numero_curtidas > 0) ? $numero_curtidas : "Curtir"; ?>
                            </button>
                        </form>
                        <button onclick="window.location.href='lista_comentarios.php?post_id=<?php echo $post['id']; ?>'">Comentários</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>


        </div> <!-- Fechando a container-feed -->
    </main>

    <script>
        function mostrarOpcoes(postId) {
            var menu = document.getElementById('opcoes-' + postId);
            if (menu.style.display === "none") {
                menu.style.display = "block";
            } else {
                menu.style.display = "none";
            }
        }
    </script>
</body>
</html>
