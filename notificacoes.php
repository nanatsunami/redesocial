<?php
function obterPreferenciasNotificacoes($pdo, $id_usuario) {
    $queryGlobais = "SELECT notificacao_comentarios_globais, notificacao_curtidas_globais FROM config_notificacoes WHERE id_usuario = :id_usuario";
    $stmtGlobais = $pdo->prepare($queryGlobais);
    $stmtGlobais->bindParam(':id_usuario', $id_usuario);
    $stmtGlobais->execute();
    $preferenciasGlobais = $stmtGlobais->fetch(PDO::FETCH_ASSOC);

    // Define preferências padrão caso não existam
    if ($preferenciasGlobais === false) {
        $preferenciasGlobais = [
            'notificacao_comentarios_globais' => 1,
            'notificacao_curtidas_globais' => 1
        ];
    }
    return $preferenciasGlobais;
}

// Função para atualizar as preferências de notificações
function atualizarPreferenciasNotificacoes($pdo, $id_usuario, $notificacao_comentarios_globais, $notificacao_curtidas_globais) {
    $query = "UPDATE config_notificacoes SET 
              notificacao_comentarios_globais = ?, 
              notificacao_curtidas_globais = ? 
              WHERE id_usuario = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$notificacao_comentarios_globais, $notificacao_curtidas_globais, $id_usuario]);

    return $stmt->rowCount(); // Retorna o número de linhas afetadas
}

// Função para marcar notificações como lidas
function marcarNotificacoesComoLidas($pdo, $id_usuario) {
    $query = "UPDATE notificacoes SET lida = 1 WHERE id_usuario = :id_usuario AND lida = 0";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
}

// Função para buscar notificações de comentários
function getNotificacoesComentarios($pdo, $id_usuario) {
    $stmt = $pdo->prepare("SELECT n.id, n.tipo, n.mensagem, n.data_criacao
                           FROM notificacoes n
                           JOIN comentarios c ON n.id_comentario = c.id
                           WHERE n.id_usuario = :id_usuario AND n.tipo = 'comentario'
                           ORDER BY n.data_criacao DESC");
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para buscar notificações de curtidas
function getNotificacoesCurtidas($pdo, $id_usuario) {
    $stmt = $pdo->prepare("SELECT n.id, n.tipo, n.mensagem, n.data_criacao
                           FROM notificacoes n
                           JOIN curtidas c ON n.id_curtida = c.id
                           WHERE n.id_usuario = :id_usuario AND n.tipo = 'curtida'
                           ORDER BY n.data_criacao DESC");
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function contarNotificacoesNaoLidas($pdo, $id_usuario) {
    $queryNotificacoesNaoLidas = "SELECT COUNT(*) FROM notificacoes WHERE id_usuario = :id_usuario AND lida = 0";
    $stmtNotificacoesNaoLidas = $pdo->prepare($queryNotificacoesNaoLidas);
    $stmtNotificacoesNaoLidas->bindParam(':id_usuario', $id_usuario);
    $stmtNotificacoesNaoLidas->execute();
    return $stmtNotificacoesNaoLidas->fetchColumn();
}


?>
