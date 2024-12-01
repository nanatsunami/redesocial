<?php
$status = isset($_GET['status']) ? $_GET['status'] : '';

if ($status == 'sucesso') {
    $mensagem = 'E-mail de redefinição de senha enviado com sucesso! Por favor, verifique sua caixa de entrada.';
} elseif ($status == 'erro') {
    $mensagem = 'Ocorreu um erro ao enviar o e-mail. Tente novamente mais tarde.';
} elseif ($status == 'email_nao_encontrado') {
    $mensagem = 'E-mail não encontrado. Verifique o endereço digitado e tente novamente.';
} elseif ($status == 'token_expirado') {
    $mensagem = 'O token de redefinição de senha expirou. Solicite um novo link de redefinição de senha.';
} else {
    $mensagem = 'Ocorreu um erro desconhecido. Tente novamente mais tarde.';
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Sucesso</title>
</head>
<body>
    <div class="container-redefinir">
        <h2>Esqueceu sua senha?</h2>
        <p><?php echo $mensagem; ?></p>
        <a href="index.php" class="botao-redefinir">Voltar para o Login</a>
    </div>
</body>
</html>
