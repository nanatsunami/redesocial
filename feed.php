<?php
session_start(); // Inicia a sessão

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario'])) {
    header('Location: index.php'); // Redireciona para a página de login se não estiver logado
    exit();
}

// Incluindo o arquivo de conexão com o banco de dados
include('db.php');

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

    // ID do usuário logado
    $id_usuario = $_SESSION['id_usuario'];  // Supondo que o ID do usuário esteja armazenado na sessão

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
            <button class="btn-notificacoes" aria-label="Notificações">
                <img src="img\bell.png" alt="Notificações" class="icone"> <!-- Ícone de notificação (bell.png) -->
            </button>
            <button class="btn-deslogar" aria-label="Deslogar" onclick="window.location.href='logout.php'">
                <img src="img\logout.png" alt="Deslogar" class="icone"> <!-- Ícone de deslogar (logout.png) -->
            </button>
        </div>
    </header>

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

</body>
</html>
