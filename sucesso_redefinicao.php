<?php
// Verifique o status via GET
$status = $_GET['status'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/style.css">
    <title>Redefinição de Senha</title>
</head>
<body>
    <div class="container-redefinir">
        <h2>Redefinição de Senha</h2>
            <?php if ($status === 'sucesso'): ?>
                <p>Senha redefinida com sucesso! <a href="index.php">Clique aqui para voltar à página inicial</a></p>
            <?php else: ?>
                <p>Ocorreu um erro ao tentar redefinir sua senha. <a href="esqueceu_senha.php">Tentar novamente</a></p>
            <?php endif; ?>
     </div>
</body>
</html>