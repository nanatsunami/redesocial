<?php
session_start();

include('db.php');  // Conectar ao banco de dados
include('usuario.php');  // Arquivo de funções (como a função alterarTema)

// Variável para armazenar mensagem de sucesso ou erro
$_SESSION['mensagem'] = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = $_SESSION['id_usuario'];  // O ID do usuário deve estar na sessão
    $id_tema = $_POST['id_tema'];  // ID do tema escolhido

    // Chamar a função para alterar o tema
    if (alterarTema($pdo, $id_usuario, $id_tema)) {
        $_SESSION['mensagem'] = "Tema alterado com sucesso!";
    } else {
        $_SESSION['mensagem'] = "Erro ao alterar o tema. Por favor, tente novamente.";
    }
}

// Redireciona de volta para a página de edição de perfil
header('Location: edita_perfil.php');  // 
