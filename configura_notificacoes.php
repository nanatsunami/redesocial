<?php
session_start();

// Incluindo o arquivo de conexão com o banco de dados (PDO) e as configurações de notificação
include('db.php');
include('notificacoes.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

// Inicializa a variável de erro
$erro = "";

// Recupera as preferências de notificações do banco de dados
$preferencias = obterPreferenciasNotificacoes($pdo, $id_usuario);

// Obter o número de notificações não lidas
$notificacoesNaoLidas = contarNotificacoesNaoLidas($pdo, $id_usuario);

// Verifica se o formulário foi enviado para atualizar as preferências
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $notificacao_comentarios_globais = isset($_POST['notificacao_comentarios_globais']) ? 1 : 0;
    $notificacao_curtidas_globais = isset($_POST['notificacao_curtidas_globais']) ? 1 : 0;

    // Atualiza as preferências de notificações
    $linhasAfetadas = atualizarPreferenciasNotificacoes($pdo, $id_usuario, $notificacao_comentarios_globais, $notificacao_curtidas_globais);

    // Verifica se a atualização foi bem-sucedida
    if ($linhasAfetadas > 0) {
        $sucesso = "Preferências de notificação atualizadas com sucesso!";
    } else {
        $erro = "Falha ao atualizar as preferências de notificação.";
    }
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
    <!-- Título -->
    <div class="header-notificacoes">
        <h1>Configurações de Notificação</h1>
    </div>

    <!-- Mensagem de erro ou sucesso -->
    <?php if (!empty($erro)): ?>
        <p class="erro"><?php echo $erro; ?></p>
    <?php endif; ?>
    <?php if (!empty($sucesso)): ?>
        <p class="sucesso"><?php echo $sucesso; ?></p>
    <?php endif; ?>

    <!-- Formulário de preferências -->
    <div class="container-feed">
        <form action="" method="post">
            <div class="secao-notificacoes">
                <label class="checkbox-container">
                    <input type="checkbox" name="notificacao_comentarios_globais" <?php echo $preferencias['notificacao_comentarios_globais'] ? 'checked' : ''; ?>>
                    <span>Receber notificações de comentários</span>
                </label>
            </div>

            <div class="secao-curtidas">
                <label class="checkbox-container">
                    <input type="checkbox" name="notificacao_curtidas_globais" <?php echo $preferencias['notificacao_curtidas_globais'] ? 'checked' : ''; ?>>
                    <span>Receber notificações de curtidas</span>
                </label>
            </div>

            <div class="botao-salvar">
                <input type="submit" value="Salvar preferências">
            </div>
        </form>
    </div>            
</body>
</html>
