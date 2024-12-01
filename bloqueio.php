<?php
// Função para bloquear um usuário
function bloquearUsuario($pdo, $id_usuario, $id_usuario_bloqueado) {
    // Verifica se o usuário já está bloqueado
    $queryVerificarBloqueio = "SELECT * FROM bloqueios WHERE id_usuario_bloqueador = :id_usuario AND id_usuario_bloqueado = :id_usuario_bloqueado";
    $stmtVerificarBloqueio = $pdo->prepare($queryVerificarBloqueio);
    $stmtVerificarBloqueio->bindParam(':id_usuario', $id_usuario);
    $stmtVerificarBloqueio->bindParam(':id_usuario_bloqueado', $id_usuario_bloqueado);
    $stmtVerificarBloqueio->execute();

    // Se o usuário não estiver bloqueado, realiza o bloqueio
    if ($stmtVerificarBloqueio->rowCount() == 0) {
        $queryBloquearUsuario = "INSERT INTO bloqueios (id_usuario_bloqueador, id_usuario_bloqueado) VALUES (:id_usuario_bloqueador, :id_usuario_bloqueado)";
        $stmtBloquearUsuario = $pdo->prepare($queryBloquearUsuario);
        $stmtBloquearUsuario->bindParam(':id_usuario_bloqueador', $id_usuario);
        $stmtBloquearUsuario->bindParam(':id_usuario_bloqueado', $id_usuario_bloqueado);
        return $stmtBloquearUsuario->execute();
    } else {
        return false; // Usuário já está bloqueado
    }
}


?>
