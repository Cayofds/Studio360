<?php
// Cria a conexão e exporta uma variável $conn compatível com outros arquivos
$conn = new mysqli("localhost", "root", "", "studio360");

// Verifica erro
if ($conn->connect_error) {
    // Em produção, registre o erro em vez de expor detalhes ao usuário
    die("Erro de conexão com o banco de dados.");
}

// Não echo nem fecho a conexão aqui — os scripts que incluírem este arquivo
// usarão $conn e poderão fechá-lo quando necessário.
?>