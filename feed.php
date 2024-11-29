<?php
session_start(); // Inicia a sessão

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario'])) {
    header('Location: index.php'); // Redireciona para a página de login se não estiver logado
    exit();
}

// Incluindo o arquivo de conexão com o banco de dados (PDO)
include('db.php');

// ID do usuário logado
$id_usuario = $_SESSION['id_usuario'];  // Supondo que o ID do usuário esteja armazenado na sessão

// Verificar preferências globais de notificações
$queryGlobais = "SELECT notificacao_comentarios_globais, notificacao_curtidas_globais FROM config_notificacoes WHERE id_usuario = :id_usuario";
$stmtGlobais = $pdo->prepare($queryGlobais);
$stmtGlobais->bindParam(':id_usuario', $id_usuario);
$stmtGlobais->execute();
$preferenciasGlobais = $stmtGlobais->fetch(PDO::FETCH_ASSOC);

// Verificar se o usuário tem preferências definidas, caso contrário, define como padrão
if ($preferenciasGlobais === false) {
    // Se não houver preferências no banco, define valores padrão
    $preferenciasGlobais = [
        'notificacao_comentarios_globais' => 1, // Notificação de comentários ativada por padrão
        'notificacao_curtidas_globais' => 1    // Notificação de curtidas ativada por padrão
    ];
}

// Verificar se o usuário quer ser notificado sobre comentários globais
if ($preferenciasGlobais['notificacao_comentarios_globais']) {
    // Buscar notificações sobre comentários
    $queryComentarios = "SELECT * FROM comentarios WHERE id_usuario != :id_usuario";
    $stmtComentarios = $pdo->prepare($queryComentarios);
    $stmtComentarios->bindParam(':id_usuario', $id_usuario);
    $stmtComentarios->execute();

    while ($comentario = $stmtComentarios->fetch(PDO::FETCH_ASSOC)) {
        // Criar a notificação de comentário
        $mensagemComentario = "Novo comentário no seu post: {$comentario['texto']}";
        // Inserir a notificação na tabela de notificações
        $insertNotificacao = "INSERT INTO notificacoes (id_usuario, tipo, mensagem) VALUES (:id_usuario, 'comentario', :mensagemComentario)";
        $stmtNotificacao = $pdo->prepare($insertNotificacao);
        $stmtNotificacao->bindParam(':id_usuario', $id_usuario);
        $stmtNotificacao->bindParam(':mensagemComentario', $mensagemComentario);
        $stmtNotificacao->execute();
    }
}

// Verificar se o usuário quer ser notificado sobre curtidas globais
if ($preferenciasGlobais['notificacao_curtidas_globais']) {
    // Buscar notificações sobre curtidas
    $queryCurtidas = "SELECT * FROM curtidas WHERE id_usuario != :id_usuario";
    $stmtCurtidas = $pdo->prepare($queryCurtidas);
    $stmtCurtidas->bindParam(':id_usuario', $id_usuario);
    $stmtCurtidas->execute();

    while ($curtida = $stmtCurtidas->fetch(PDO::FETCH_ASSOC)) {
        // Criar a notificação de curtida
        $mensagemCurtida = "Alguém curtiu seu post!";
        // Inserir a notificação na tabela de notificações
        $insertNotificacao = "INSERT INTO notificacoes (id_usuario, tipo, mensagem) VALUES (:id_usuario, 'curtida', :mensagemCurtida)";
        $stmtNotificacao = $pdo->prepare($insertNotificacao);
        $stmtNotificacao->bindParam(':id_usuario', $id_usuario);
        $stmtNotificacao->bindParam(':mensagemCurtida', $mensagemCurtida);
        $stmtNotificacao->execute();
    }
}

// Lógica para processar o formulário de post
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtém o texto do post
    $texto = $_POST['post_text'];

    // Lógica para upload da imagem (caso tenha sido enviada)
    $imagem = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // Defina o diretório para onde a imagem será salva
        $diretorio = 'uploads/';  // Certifique-se de que esse diretório existe e tem permissões adequadas
        $imagem_nome = basename($_FILES['image']['name']);
        $imagem_destino = $diretorio . $imagem_nome;

        // Move a imagem para o diretório de uploads
        if (move_uploaded_file($_FILES['image']['tmp_name'], $imagem_destino)) {
            $imagem = $imagem_destino;  // Armazena o caminho da imagem
        } else {
            echo "Erro ao fazer upload da imagem.";
        }
    }

    // Obtém a data e hora atuais
    $data_criacao = date('Y-m-d H:i:s');

    // Insere o post no banco de dados
    $query = "INSERT INTO publicacoes (id_usuario, texto, imagem, data_criacao) 
              VALUES (:id_usuario, :texto, :imagem, :data_criacao)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->bindParam(':texto', $texto);
    $stmt->bindParam(':imagem', $imagem);
    $stmt->bindParam(':data_criacao', $data_criacao);

    if ($stmt->execute()) {
        // Sucesso, pode redirecionar ou exibir uma mensagem
        echo "Post publicado com sucesso!";
    } else {
        // Em caso de erro
        echo "Erro ao publicar o post.";
    }
}

// Lógica para buscar posts
$query = "SELECT * FROM publicacoes ORDER BY data_criacao DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$posts = $stmt->fetchAll();
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
                <img src="img\cog.png" alt="Foto de Perfil" class="icone">
            </button>
            <button class="btn-notificacoes" aria-label="Notificações" onclick="toggleNotificacoes()">
            <img src="img\bell.png" alt="Notificações" class="icone">
            </button>
            <button class="btn-deslogar" aria-label="Deslogar" onclick="window.location.href='logout.php'">
                <img src="img\logout.png" alt="Deslogar" class="icone"> <!-- Ícone de deslogar (logout.png) -->
            </button>
        </div>
    </header>

    <!-- Container para as Notificações -->
    <div id="notificacoes-container" class="notificacoes-container">
        <div class="notificacoes-conteudo">
            <h2>Notificações</h2>
            <span class="fechar" onclick="toggleNotificacoes()">&times;</span>
            <div id="lista-notificacoes">
                <?php 
                // Código para buscar as notificações
                $queryNotificacoes = "SELECT * FROM notificacoes WHERE id_usuario = :id_usuario ORDER BY data_criacao DESC";
                $stmtNotificacoes = $pdo->prepare($queryNotificacoes);
                $stmtNotificacoes->bindParam(':id_usuario', $id_usuario);
                $stmtNotificacoes->execute();
                $notificacoes = $stmtNotificacoes->fetchAll();

                foreach ($notificacoes as $notificacao): ?>
                    <div class="notificacao-item">
                        <p><?php echo htmlspecialchars($notificacao['mensagem']); ?></p>
                        <small><?php echo $notificacao['data_criacao']; ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>


    <!-- Feed de Posts -->
    <main class="main-feed"> 
        <div class="container-feed">
            <div class="feed-post-form"> 
                <form action="feed.php" method="POST" enctype="multipart/form-data">
                    <textarea name="post_text" placeholder="O que você está pensando?" required></textarea>
                    <input type="file" name="image">
                    <button type="submit">Postar</button>
                </form>
            </div>

            <div class="feed-posts">
                <?php foreach ($posts as $post): ?>
                    <div class="feed-post"> 
                        <img src="<?php echo htmlspecialchars($post['imagem']); ?>" alt="Post Image">
                        <p><?php echo htmlspecialchars($post['texto']); ?></p>
                        <small>Postado por <?php echo htmlspecialchars($post['id_usuario']); ?> em <?php echo $post['data_criacao']; ?></small>
                        <div class="interacao-feed"> 
                            <button>Curtir</button>
                            <button onclick="openComment(<?php echo $post['id']; ?>)">Comentar</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <script>
        function openComment(id) {
            alert('Abrir comentários para o post ID: ' + id);
        }
    </script>
    <script src="js\notificacoes.js"></script>

</body>
</html>
