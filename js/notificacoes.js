// Função para alternar a visibilidade do container de notificações
function toggleNotificacoes() {
    var container = document.getElementById("notificacoes-container");
    
    // Alterna entre mostrar e esconder
    if (container.style.display === "block") {
        container.style.display = "none";
    } else {
        container.style.display = "block";
    }
}
