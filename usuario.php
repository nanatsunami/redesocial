<?php
include('db.php');

function carregarPerfil($pdo, $id_usuario) {
    $query = "SELECT nome_usuario, email, data_criacao, id_tema FROM usuarios WHERE id = :id_usuario";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->execute();
    
    // Verifique se a consulta foi bem-sucedida e se há dados retornados
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


// Função para editar o perfil (nome e email)
function editarPerfil($pdo, $id_usuario, $novo_nome, $novo_email) {
    $sql = "UPDATE usuarios SET nome = :nome, email = :email WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':nome', $novo_nome);
    $stmt->bindParam(':email', $novo_email);
    $stmt->bindParam(':id', $id_usuario);
    return $stmt->execute();
}

// Função para alterar a senha do usuário
function alterarSenha($pdo, $id_usuario, $nova_senha) {
    // Criptografar a nova senha com o algoritmo de hash padrão
    $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

    $sql = "UPDATE usuarios SET senha = :senha WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':senha', $senha_hash);
    $stmt->bindParam(':id', $id_usuario);
    return $stmt->execute();
}

// Função para obter os usuários bloqueados
function obterUsuariosBloqueados($pdo, $id_usuario) {
    // SQL para obter os usuários bloqueados
    $query = "SELECT u.id, u.nome_usuario 
              FROM usuarios u 
              INNER JOIN bloqueios b ON u.id = b.id_usuario_bloqueado 
              WHERE b.id_usuario_bloqueador = :id_usuario";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para verificar se um usuário está bloqueado
function verificarSeBloqueado($pdo, $id_usuario_bloqueador, $id_usuario_bloqueado) {
    $query = "SELECT * FROM bloqueios WHERE id_usuario_bloqueador = :id_usuario_bloqueador AND id_usuario_bloqueado = :id_usuario_bloqueado";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_usuario_bloqueador', $id_usuario_bloqueador);
    $stmt->bindParam(':id_usuario_bloqueado', $id_usuario_bloqueado);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Função para bloquear um usuário
function bloquearUsuario($pdo, $id_usuario_bloqueador, $id_usuario_bloqueado) {
    // Verifica se já existe o bloqueio
    if (!verificarSeBloqueado($pdo, $id_usuario_bloqueador, $id_usuario_bloqueado)) {
        $query = "INSERT INTO bloqueios (id_usuario_bloqueador, id_usuario_bloqueado) VALUES (:id_usuario_bloqueador, :id_usuario_bloqueado)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id_usuario_bloqueador', $id_usuario_bloqueador);
        $stmt->bindParam(':id_usuario_bloqueado', $id_usuario_bloqueado);
        return $stmt->execute();
    }
    return false; // Usuário já está bloqueado
}

// Função para desbloquear um usuário
function desbloquearUsuario($pdo, $id_usuario_bloqueador, $id_usuario_bloqueado) {
    // SQL para remover o bloqueio
    $query = "DELETE FROM bloqueios WHERE id_usuario_bloqueador = :id_usuario_bloqueador AND id_usuario_bloqueado = :id_usuario_bloqueado";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_usuario_bloqueador', $id_usuario_bloqueador);
    $stmt->bindParam(':id_usuario_bloqueado', $id_usuario_bloqueado);

    // Executar a query e retornar o resultado
    return $stmt->execute();
}



// Função para obter as informações do tema com base no id
function obterTema($pdo, $id_tema) {
    $sql = "SELECT * FROM temas WHERE id = :id_tema";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id_tema', $id_tema, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Função para carregar o tema do usuário
function carregarTema($pdo, $id_usuario) {
    // Consulta SQL para carregar o tema do usuário
    $sql = "SELECT t.* 
            FROM usuarios u 
            JOIN temas t ON u.id_tema = t.id  -- Corrigido para 'id_tema' em vez de 'cor_perfil'
            WHERE u.id = :id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id_usuario);
    $stmt->execute();
    
    // Verifica se o usuário foi encontrado e retorna o tema
    return $stmt->fetch(PDO::FETCH_ASSOC);  // Retorna o tema como um array associativo
}

// Função para alterar o tema do usuário
function alterarTema($pdo, $id_usuario, $id_tema) {
    // Verificar se o id_tema existe na tabela de temas
    $sql = "SELECT 1 FROM temas WHERE id = :id_tema";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_tema', $id_tema);
    $stmt->execute();
    
    // Se o tema não for encontrado, retorna false
    if ($stmt->rowCount() == 0) {
        return false;  // Tema não encontrado
    }

    // Se o tema for válido, altera o tema do usuário
    $sql = "UPDATE usuarios SET id_tema = :id_tema WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_tema', $id_tema);  // Vincula o ID do tema
    $stmt->bindParam(':id', $id_usuario);  // Vincula o ID do usuário
    
    // Executa a query e verifica se foi bem-sucedida
    if ($stmt->execute()) {
        return true;  // Sucesso na atualização
    } else {
        // Caso haja um erro, capture e imprima a mensagem de erro SQL
        print_r($stmt->errorInfo());
        return false;  // Erro na execução
    }
}
// Função para buscar o nome de um usuário baseado no ID
function getUsuarioNome($pdo, $id_usuario) {
    $query = "SELECT nome_usuario FROM usuarios WHERE id = :id_usuario";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    
    // Recupera o resultado e retorna o nome do usuário
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    return $resultado['nome_usuario'] ?? ''; // Retorna o nome ou string vazia se não encontrado
}

