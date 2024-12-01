<?php
session_start();

// Incluindo os arquivos necessários
include('db.php');
include('usuario.php'); // Funções de usuário
include('notificacoes.php');

// ID do usuário logado
$id_usuario_logado = $_SESSION['id_usuario']; 

// Verifica se o usuário está logado
if (!isset($id_usuario_logado)) {
    header('Location: index.php');
    exit;
}

// Verifica se o usuário está tentando editar o próprio perfil
if (isset($_GET['id_usuario'])) {
    $id_usuario = $_GET['id_usuario'];
    // Se o id_usuario não corresponder ao usuário logado, redireciona para o perfil
    if ($id_usuario != $id_usuario_logado) {
        header('Location: perfil.php'); // Impede que um usuário edite o perfil de outro
        exit;
    }
} else {
    // Se não tiver o parâmetro id_usuario na URL, redireciona para o perfil
    $id_usuario = $id_usuario_logado;
}

// Processar a alteração do tema
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'alterar_tema') {
    $id_usuario = $_SESSION['id_usuario'];  // ID do usuário logado
    $id_tema = $_POST['id_tema'];  // ID do tema escolhido

    // Atualiza o tema no banco de dados
    $query = "UPDATE usuarios SET id_tema = :id_tema WHERE id = :id_usuario";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_tema', $id_tema);
    $stmt->bindParam(':id_usuario', $id_usuario);
    
    if ($stmt->execute()) {
        $mensagem_sucesso = "Tema alterado com sucesso!";
    } else {
        $mensagem_erro = "Erro ao alterar o tema. Tente novamente.";
    }
}


// Carregar o ID do tema do usuário
$query = "SELECT id_tema FROM usuarios WHERE id = :id_usuario";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':id_usuario', $id_usuario);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Carregar as cores do tema
$query = "SELECT * FROM temas WHERE id = :id_tema";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':id_tema', $usuario['id_tema']);
$stmt->execute();
$tema = $stmt->fetch(PDO::FETCH_ASSOC);

// Carregar o perfil do usuário
$usuario = carregarPerfil($pdo, $id_usuario); 

// Mensagens de sucesso e erro
$mensagem_sucesso = '';
$mensagem_erro = '';

// Processar o formulário de alteração de senha
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'alterar_senha') {
    $nova_senha = $_POST['nova_senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    // Verifica se as senhas são iguais
    if ($nova_senha == $confirmar_senha) {
        // Chama a função para alterar a senha no banco
        if (alterarSenha($pdo, $id_usuario, $nova_senha)) {
            $mensagem_sucesso = "Senha alterada com sucesso!";
        } else {
            $mensagem_erro = "Erro ao alterar a senha. Tente novamente.";
        }
    } else {
        $mensagem_erro = "As senhas não coincidem. Tente novamente.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'alterar_tema') {
    $id_usuario = $_SESSION['id_usuario'];  // ID do usuário logado
    $id_tema = $_POST['id_tema'];  // ID do tema escolhido

    // Atualiza o tema no banco de dados
    $query = "UPDATE usuarios SET id_tema = :id_tema WHERE id = :id_usuario";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_tema', $id_tema);
    $stmt->bindParam(':id_usuario', $id_usuario);
    
    if ($stmt->execute()) {
        // Redireciona para a página de edição do perfil para carregar o novo tema
        header('Location: edita_perfil.php');
        exit;  // Garante que o código não será executado após o redirecionamento
    } else {
        $mensagem_erro = "Erro ao alterar o tema. Tente novamente.";
    }
}


// Processar o desbloqueio de um usuário
if (isset($_POST['acao']) && $_POST['acao'] == 'desbloquear_usuario' && isset($_POST['id_usuario_bloqueado'])) {
    $id_usuario_bloqueado = $_POST['id_usuario_bloqueado'];  // ID do usuário bloqueado
    var_dump($id_usuario_bloqueado); // Verifique o ID que está sendo enviado no POST
    
    // Agora, tente desbloquear o usuário
    if (desbloquearUsuario($pdo, $id_usuario, $id_usuario_bloqueado)) {
        $mensagem_sucesso = "Usuário desbloqueado com sucesso!";
    } else {
        $mensagem_erro = "Erro ao desbloquear o usuário.";
    }
}

// Processar o bloqueio de um usuário
if (isset($_POST['acao']) && $_POST['acao'] == 'bloquear_usuario' && isset($_POST['id_usuario_bloqueado'])) {
    $id_usuario_bloqueado = $_POST['id_usuario_bloqueado'];
    
    // Chama a função para bloquear o usuário no banco
    if (bloquearUsuario($pdo, $id_usuario, $id_usuario_bloqueado)) {
        $mensagem_sucesso = "Usuário bloqueado com sucesso!";
    } else {
        $mensagem_erro = "Erro ao bloquear o usuário.";
    }
}

// Carregar os dados do perfil do usuário
$perfil = carregarPerfil($pdo, $id_usuario);
$usuarios_bloqueados = obterUsuariosBloqueados($pdo, $id_usuario);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Editar Perfil - <?php echo htmlspecialchars($usuario['nome_usuario']); ?></title>
</head>
<body class="theme-<?php echo strtolower($tema['nome']); ?>">
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
            </button>
            <button class="btn-deslogar" aria-label="Deslogar" onclick="window.location.href='logout.php'">
                <img src="img\logout.png" alt="Deslogar" class="icone">
            </button>
        </div>
    </header>

    <!-- Página de Edição de Perfil -->
    <main class="perfil-main">
        <div class="container-feed">
            <div class="perfil-info">
                <h1>Editar Perfil</h1>
                
                <!-- Mensagens de sucesso/erro -->
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

                <!-- Formulário de Edição -->
                <form action="edita_perfil.php?id_usuario=<?php echo $id_usuario; ?>" method="POST">
                    <!-- Nome de usuário não editável -->
                    <div class="campo">
                        <label for="nome_usuario">Nome de Usuário:</label>
                        <input type="text" id="nome_usuario" name="nome_usuario" value="<?php echo htmlspecialchars($usuario['nome_usuario']); ?>" disabled>
                    </div>

                    <!-- Alteração de senha -->
                    <div class="campo">
                        <label for="nova_senha">Nova Senha:</label>
                        <input type="password" id="nova_senha" name="nova_senha" required>
                    </div>

                    <div class="campo">
                        <label for="confirmar_senha">Confirmar Nova Senha:</label>
                        <input type="password" id="confirmar_senha" name="confirmar_senha" required>
                    </div>

                    <button type="submit" name="acao" value="alterar_senha">Alterar Senha</button>
                </form>

                <h2>Usuários Bloqueados</h2>
                <?php if (count($usuarios_bloqueados) > 0): ?>
                    <ul>
                        <?php foreach ($usuarios_bloqueados as $usuario_bloqueado): ?>
                            <li>
                                <?php echo htmlspecialchars($usuario_bloqueado['nome_usuario']); ?>
                                
                                <?php
                                    // Verifique se o usuário está bloqueado
                                    $bloqueado = verificarSeBloqueado($pdo, $id_usuario, $usuario_bloqueado['id']);
                                    
                                    if ($bloqueado):  // Se estiver bloqueado, mostre o botão de desbloquear
                                ?>
                                    <!-- Formulário para desbloquear -->
                                    <form action="edita_perfil.php?id_usuario=<?php echo $id_usuario; ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="id_usuario_bloqueado" value="<?php echo $usuario_bloqueado['id']; ?>">
                                        <button type="submit" name="acao" value="desbloquear_usuario">Desbloquear</button>
                                    </form>
                                <?php else: ?>
                                    <!-- Se não estiver bloqueado, mostre o botão de bloquear -->
                                    <form action="edita_perfil.php?id_usuario=<?php echo $id_usuario; ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="id_usuario_bloqueado" value="<?php echo $usuario_bloqueado['id']; ?>">
                                        <button type="submit" name="acao" value="bloquear_usuario">Bloquear</button>
                                    </form>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Você não tem usuários bloqueados.</p>
                <?php endif; ?>

                <h2>Alterar Tema</h2>
                <form action="edita_perfil.php" method="POST" onsubmit="changeTheme(document.getElementById('id_tema').value); return false;">
                    <label for="id_tema">Escolha o Tema:</label>
                    <select name="id_tema" id="id_tema">
                        <option value="1">Roxo</option>
                        <option value="2">Rosa</option>
                        <option value="3">Verde</option>
                        <option value="4">Azul</option>
                    </select>
                    <button type="submit" name="acao" value="alterar_tema">Salvar Tema</button>
                </form>
            </div>
        </div>
    </main>
    <script>
    <script>
    function changeTheme(themeId) {
        let themeClass = '';

        // Definindo a classe do tema baseado no ID
        switch (themeId) {
            case '4': // Tema Roxo
                themeClass = 'theme-roxo';
                break;
            case '5': // Tema Rosa
                themeClass = 'theme-rosa';
                break;
            case '6': // Tema Verde
                themeClass = 'theme-verde';
                break;
            case '7': // Tema Azul
                themeClass = 'theme-azul';
                break;
            default:
                themeClass = 'theme-roxo'; // Fallback para roxo
        }

        // Alterando a classe do body para aplicar o tema
        document.body.className = themeClass;

        // Salvar o tema selecionado no localStorage para persistir entre recarregamentos
        localStorage.setItem('theme', themeClass);
    }

    // Carregar o tema salvo no localStorage quando a página for carregada
    window.onload = function() {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            document.body.className = savedTheme;
        }
    };
    function lightenColor(hex, percent) {
        const color = hex.replace("#", "");
        const r = parseInt(color.substring(0, 2), 16) + Math.round(2.55 * percent);
        const g = parseInt(color.substring(2, 4), 16) + Math.round(2.55 * percent);
        const b = parseInt(color.substring(4, 6), 16) + Math.round(2.55 * percent);

        return `rgb(${Math.min(r, 255)}, ${Math.min(g, 255)}, ${Math.min(b, 255)})`;
    }
    </script>


</body>
</html>
