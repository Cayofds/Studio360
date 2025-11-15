<?php
session_start();
include 'conexao.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepara a consulta para evitar SQL Injection
    $stmt = $con->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();

        // Verifica a senha
        if (password_verify($password, $hashed_password)) {
            // Login bem-sucedido
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            header("Location: ../view/dashboard.php");
            exit();
        } else {
            // Senha incorreta
            echo "Senha incorreta.";
        }
    } else {
        // Usuário não encontrado
        echo "Usuário não encontrado.";
    }

    $stmt->close();
}