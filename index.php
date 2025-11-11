<?php
session_start();

// if (isset($_SESSION['Logado']) && $_SESSION['Logado'] == true) {
    header("Location: ./view/home.php");
//     exit;
// } else {
//     header("Location: ./view/login.php");
//     exit;
// }
?>