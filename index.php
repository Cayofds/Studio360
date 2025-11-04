<?php
session_start();

if(isset($_SESSION['Logado']) && $_SESSION['Logado'])
    header("Location: ./view/home.php");
else
    header("Location: ./view/login.php")

?>