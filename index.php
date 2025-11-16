<?php
session_start();

if (!empty($_SESSION['usuarioId'])) {
    header('Location: ./view/telaHome.php');
    exit;
}

header('Location: ./view/telaLogin.php');
exit;
?>