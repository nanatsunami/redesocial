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

// Função para registrar uma notificação de comentário
function criarNotificacaoComentario($pdo, $id_usuario, $id_comentario) {
    // Verifica se o comentário já tem uma notificação
    $stmtVerificar = $pdo->prepare("SELECT COUNT(*) FROM notificacoes WHERE id_usuario = :id_usuario AND id_comentario = :id_comentario");
    $stmtVerificar->bindParam(':id_usuario', $id_usuario);
    $stmtVerificar->bindParam(':id_comentario', $id_comentario);
    $stmtVerificar->execute();
    if ($stmtVerificar->fetchColumn() > 0) {
        return false; // Se a notificação já existir, não cria outra
    }

    // Insere uma nova notificação de comentário
    $stmt = $pdo->prepare("INSERT INTO notificacoes (id_usuario, tipo, mensagem, data_criacao, id_comentario) 
                           VALUES (:id_usuario, 'comentario', 'Seu post recebeu um comentário!', NOW(), :id_comentario)");
    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->bindParam(':id_comentario', $id_comentario);
    return $stmt->execute();
}

function criarNotificacaoCurtida($pdo, $id_usuario, $id_curtido) {
    // Verifica se a curtida já foi registrada antes (caso queira evitar duplicatas)
    $stmtVerificar = $pdo->prepare("SELECT COUNT(*) FROM notificacoes WHERE id_usuario = :id_usuario AND id_curtida = :id_curtido");
    $stmtVerificar->bindParam(':id_usuario', $id_usuario);
    $stmtVerificar->bindParam(':id_curtido', $id_curtido);
    $stmtVerificar->execute();
    if ($stmtVerificar->fetchColumn() > 0) {
        return false; // Se a notificação já existir, não cria outra
    }

    // Insere uma nova notificação de curtida
    $stmt = $pdo->prepare("INSERT INTO notificacoes (id_usuario, tipo, mensagem, data_criacao, id_curtida) 
                           VALUES (:id_usuario, 'curtida', 'Seu post foi curtido!', NOW(), :id_curtido)");
    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->bindParam(':id_curtido', $id_curtido);
    return $stmt->execute();
}

function contarNotificacoesNaoLidas($pdo, $id_usuario) {
    $queryNotificacoesNaoLidas = "SELECT COUNT(*) FROM notificacoes WHERE id_usuario = :id_usuario AND lida = 0";
    $stmtNotificacoesNaoLidas = $pdo->prepare($queryNotificacoesNaoLidas);
    $stmtNotificacoesNaoLidas->bindParam(':id_usuario', $id_usuario);
    $stmtNotificacoesNaoLidas->execute();
    return $stmtNotificacoesNaoLidas->fetchColumn();
}

function getNotificacoesComentarios($pdo, $id_usuario) {
    // Consulta para obter as notificações de comentários do usuário
    $query = "SELECT * FROM notificacoes WHERE id_usuario = :id_usuario AND tipo = 'comentario' ORDER BY data_criacao DESC";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);  // Retorna todas as notificações de comentário
}

function getNotificacoesCurtidas($pdo, $id_usuario) {
    // Consulta para obter as notificações de curtidas com mais informações (como o título do post, por exemplo)
    $query = "
        SELECT n.*, p.texto AS texto, u.nome AS nome_usuario
        FROM notificacoes n
        LEFT JOIN publicacoes p ON n.id_curtida = p.id  -- Se a curtida está associada à publicação
        LEFT JOIN usuarios u ON n.id_usuario = u.id
        WHERE n.id_usuario = :id_usuario AND n.tipo = 'curtida'
        ORDER BY n.data_criacao DESC
    ";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);  // Retorna todas as notificações de curtida
}


?>
