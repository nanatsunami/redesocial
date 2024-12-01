<?php
// Função para criar uma nova postagem
function criarPostagem($pdo, $id_usuario, $texto, $imagem) {
    $data_criacao = date('Y-m-d H:i:s');
    $query = "INSERT INTO publicacoes (id_usuario, texto, imagem, data_criacao) 
              VALUES (:id_usuario, :texto, :imagem, :data_criacao)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->bindParam(':texto', $texto);
    $stmt->bindParam(':imagem', $imagem);
    $stmt->bindParam(':data_criacao', $data_criacao);
    return $stmt->execute();
}

// Função para deletar uma postagem
function deletarPostagem($pdo, $id_usuario, $post_id) {
    // Primeiro, vamos buscar se a postagem tem imagem e excluí-la do servidor
    $queryImagemPost = "SELECT imagem FROM publicacoes WHERE id = :post_id";
    $stmtImagemPost = $pdo->prepare($queryImagemPost);
    $stmtImagemPost->bindParam(':post_id', $post_id);
    $stmtImagemPost->execute();
    $postImagem = $stmtImagemPost->fetch(PDO::FETCH_ASSOC);

    if (isset($postImagem['imagem']) && file_exists($postImagem['imagem'])) {
        unlink($postImagem['imagem']); // Apaga a imagem do servidor
    }

    // Agora, deletar o post
    $queryDeletarPost = "DELETE FROM publicacoes WHERE id = :post_id AND id_usuario = :id_usuario";
    $stmtDeletarPost = $pdo->prepare($queryDeletarPost);
    $stmtDeletarPost->bindParam(':post_id', $post_id);
    $stmtDeletarPost->bindParam(':id_usuario', $id_usuario);
    return $stmtDeletarPost->execute();
}

function carregarPostagensFeed($pdo, $id_usuario_logado) {
    // Consulta para pegar todas as postagens que o usuário logado pode ver
    $query = "
        SELECT p.*, u.nome_usuario
        FROM publicacoes p
        INNER JOIN usuarios u ON p.id_usuario = u.id
        WHERE p.id_usuario NOT IN (
            SELECT id_usuario_bloqueado
            FROM bloqueios
            WHERE id_usuario = :id_usuario_logado
        )
        AND p.id_usuario NOT IN (
            SELECT id_usuario
            FROM bloqueios
            WHERE id_usuario_bloqueado = :id_usuario_logado
        )
        ORDER BY p.data_criacao DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_usuario_logado', $id_usuario_logado, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



// Função para carregar uma postagem específica pelo seu ID
function carregarPostagemPorId($pdo, $post_id) {
    $query = "SELECT p.*, u.nome_usuario
              FROM publicacoes p
              JOIN usuarios u ON p.id_usuario = u.id
              WHERE p.id = :post_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':post_id', $post_id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Função para carregar as postagens de um usuário específico (perfil)
function carregarPostagensPerfil($pdo, $id_usuario_perfil) {
    // Consulta para pegar as postagens do usuário específico
    $query = "
        SELECT p.*, u.nome_usuario
        FROM publicacoes p
        INNER JOIN usuarios u ON p.id_usuario = u.id
        WHERE p.id_usuario = :id_usuario_perfil
        ORDER BY p.data_criacao DESC
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_usuario_perfil', $id_usuario_perfil, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para contar o número de curtidas em uma postagem
function contarCurtidas($pdo, $post_id) {
    // Alterar 'id_post' para o nome correto da coluna, como 'id_publicacao'
    $sql = "SELECT COUNT(*) FROM curtidas WHERE id_publicacao = :post_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':post_id', $post_id);
    $stmt->execute();
    return $stmt->fetchColumn();
}

function adicionarOuRemoverCurtir($pdo, $id_usuario, $id_item, $tipo) {
    $tabela = 'curtidas'; // Usar a tabela única de curtidas
    if ($tipo == 'post') {
        $campo_id = 'id_publicacao'; // Para postagens
        $queryPost = "SELECT id_usuario FROM publicacoes WHERE id = :id_item"; // Para obter o autor da postagem
    } elseif ($tipo == 'comentario') {
        $campo_id = 'id_comentario'; // Para comentários
        $queryPost = "SELECT id_usuario FROM comentarios WHERE id = :id_item"; // Para obter o autor do comentário
    } else {
        return false; // Tipo inválido
    }

    // Verifica se o usuário já curtiu o item
    $sql = "SELECT COUNT(*) FROM $tabela WHERE id_usuario = :id_usuario AND $campo_id = :id_item";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->bindParam(':id_item', $id_item);
    $stmt->execute();

    if ($stmt->fetchColumn() > 0) {
        // Se já curtiu, remover a curtida
        $sql = "DELETE FROM $tabela WHERE id_usuario = :id_usuario AND $campo_id = :id_item";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->bindParam(':id_item', $id_item);
        $stmt->execute();
        return 'removido'; // Retorna 'removido' se a curtida foi retirada
    } else {
        // Se não curtiu, adicionar a curtida
        $sql = "INSERT INTO $tabela (id_usuario, $campo_id) VALUES (:id_usuario, :id_item)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->bindParam(':id_item', $id_item);
        $stmt->execute();

        // Recuperar o id da curtida que acabou de ser inserido
        $id_curtida = $pdo->lastInsertId();

        // Adiciona a notificação para o dono da postagem/comentário
        $stmtPost = $pdo->prepare($queryPost);
        $stmtPost->bindParam(':id_item', $id_item);
        $stmtPost->execute();
        $autor = $stmtPost->fetchColumn(); // ID do autor da postagem ou comentário

        // Não envia notificação para o próprio usuário
        if ($autor != $id_usuario) {
            // Monta a mensagem
            $mensagem = "O usuário " . getUsuarioNome($pdo, $id_usuario) . " curtiu seu " . ($tipo == 'post' ? 'post' : 'comentário') . ".";

            // Inserir a notificação
            $queryNotificacao = "INSERT INTO notificacoes (id_usuario, tipo, mensagem, id_publicacao, id_curtida) 
                                 VALUES (:id_usuario, :tipo, :mensagem, :id_publicacao, :id_curtida)";
            $stmtNotificacao = $pdo->prepare($queryNotificacao);
            $stmtNotificacao->bindParam(':id_usuario', $autor);
            $stmtNotificacao->bindParam(':tipo', $tipo);
            $stmtNotificacao->bindParam(':mensagem', $mensagem);
            $stmtNotificacao->bindParam(':id_publicacao', $id_item);  // Pode ser tanto id_publicacao ou id_comentario
            $stmtNotificacao->bindParam(':id_curtida', $id_curtida);  // Agora garante que o id_curtida é válido
            $stmtNotificacao->execute();
        }

        return 'adicionado'; // Retorna 'adicionado' se a curtida foi inserida
    }
}

function deletarComentario($pdo, $id_usuario, $comentario_id) {
    $queryDeletarComentario = "DELETE FROM comentarios WHERE id = :comentario_id AND id_usuario = :id_usuario";
    $stmtDeletarComentario = $pdo->prepare($queryDeletarComentario);
    $stmtDeletarComentario->bindParam(':comentario_id', $comentario_id);
    $stmtDeletarComentario->bindParam(':id_usuario', $id_usuario);
    return $stmtDeletarComentario->execute();
}

function comentarPostagem($pdo, $id_usuario, $post_id, $comentario) {
    $data_criacao = date('Y-m-d H:i:s');

    // Garantir que $id_usuario, $post_id, e $comentario são variáveis e do tipo correto
    $id_usuario = (int)$id_usuario; // Converte para inteiro
    $post_id = (int)$post_id; // Converte para inteiro
    $comentario = (string)$comentario; // Converte para string
    $data_criacao = (string)$data_criacao; // Converte para string

    // Preparação da consulta SQL
    $queryComentar = "INSERT INTO comentarios (id_usuario, id_publicacao, texto, data_criacao) 
                      VALUES (:id_usuario, :post_id, :texto, :data_criacao)";
    $stmtComentar = $pdo->prepare($queryComentar);

    // Agora passamos as variáveis corretamente com bindValue()
    $stmtComentar->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT); // Usar bindValue() ao invés de bindParam()
    $stmtComentar->bindValue(':post_id', $post_id, PDO::PARAM_INT);
    $stmtComentar->bindValue(':texto', $comentario, PDO::PARAM_STR);
    $stmtComentar->bindValue(':data_criacao', $data_criacao, PDO::PARAM_STR);

    // Executa a consulta
    $stmtComentar->execute();

    // Adiciona a notificação para o autor da postagem
    $queryAutorPost = "SELECT id_usuario FROM publicacoes WHERE id = :post_id";
    $stmtAutorPost = $pdo->prepare($queryAutorPost);
    $stmtAutorPost->bindValue(':post_id', $post_id, PDO::PARAM_INT);  // Garantindo o tipo correto
    $stmtAutorPost->execute();
    $autor = $stmtAutorPost->fetchColumn(); // ID do autor da postagem

    // Não envia notificação para o próprio usuário
    if ($autor != $id_usuario) {
        // Monta a mensagem
        $mensagem = "O usuário " . getUsuarioNome($pdo, $id_usuario) . " comentou em seu post.";

        // Inserir a notificação
        $queryNotificacao = "INSERT INTO notificacoes (id_usuario, tipo, mensagem, id_publicacao, id_comentario) 
                     VALUES (:id_usuario, :tipo, :mensagem, :id_publicacao, :id_comentario)";
        $stmtNotificacao = $pdo->prepare($queryNotificacao);
        $stmtNotificacao->bindValue(':id_usuario', $autor, PDO::PARAM_INT);
        $stmtNotificacao->bindValue(':tipo', $tipo = 'comentario', PDO::PARAM_STR);
        $stmtNotificacao->bindValue(':mensagem', $mensagem, PDO::PARAM_STR);
        $stmtNotificacao->bindValue(':id_publicacao', $post_id, PDO::PARAM_INT);
        $stmtNotificacao->bindValue(':id_comentario', $comentario_id, PDO::PARAM_INT);  // Use o $comentario_id gerado
        $stmtNotificacao->execute();
        $comentario_id = $pdo->lastInsertId();
    }
    return true; // Retorna true se o comentário foi inserido
}

function carregarComentarios($pdo, $id_usuario, $post_id) {
    $queryComentarios = "SELECT c.*, u.nome_usuario 
                         FROM comentarios c
                         JOIN usuarios u ON c.id_usuario = u.id
                         WHERE c.id_publicacao = :id_publicacao
                         AND c.id_usuario NOT IN (
                             SELECT id_usuario_bloqueador 
                             FROM bloqueios 
                             WHERE id_usuario_bloqueado = :id_usuario
                         )
                         AND c.id_usuario NOT IN (
                             SELECT id_usuario_bloqueado 
                             FROM bloqueios
                             WHERE id_usuario_bloqueador = :id_usuario
                         )
                         ORDER BY c.data_criacao DESC";
    $stmtComentarios = $pdo->prepare($queryComentarios);
    $stmtComentarios->bindParam(':id_publicacao', $post_id);
    $stmtComentarios->bindParam(':id_usuario', $id_usuario);
    $stmtComentarios->execute();
    return $stmtComentarios->fetchAll(PDO::FETCH_ASSOC);
}

// Função para carregar as curtidas de um post
function carregarCurtidas($pdo, $id_usuario, $id_publicacao) {
    // Consulta SQL para carregar as curtidas do post, exceto de usuários bloqueados
    $queryCurtidas = "SELECT cu.*, u.nome_usuario
                      FROM curtidas cu
                      JOIN usuarios u ON cu.id_usuario = u.id
                      WHERE cu.id_publicacao = :id_publicacao
                      AND cu.id_usuario NOT IN (
                          SELECT id_usuario_bloqueador 
                          FROM bloqueios 
                          WHERE id_usuario_bloqueado = :id_usuario
                      )
                      AND cu.id_usuario NOT IN (
                          SELECT id_usuario_bloqueado 
                          FROM bloqueios 
                          WHERE id_usuario_bloqueador = :id_usuario
                      )";
    
    $stmtCurtidas = $pdo->prepare($queryCurtidas);
    $stmtCurtidas->bindParam(':id_publicacao', $id_publicacao);
    $stmtCurtidas->bindParam(':id_usuario', $id_usuario);
    $stmtCurtidas->execute();
    
    // Retorna as curtidas encontradas
    return $stmtCurtidas->fetchAll(PDO::FETCH_ASSOC);
}

?>
