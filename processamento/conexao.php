<?php
// Cria a conexão
$con = new mysqli("localhost", "root", "", "seu_banco");

// Verifica erro
if ($con->connect_error) {
    die("Erro de conexão: " . $con->connect_error);
}

echo "Conectado com sucesso!";

$con->close();
?>