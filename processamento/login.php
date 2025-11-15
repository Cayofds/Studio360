<?php
session_start();
include_once("conexao.php");
$usuario = filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_STRING);
$senha = filter_input(INPUT_POST, 'senha', FILTER_SANITIZE_STRING);

if ((!empty($usuario)) AND (!empty($senha))) {
    $senha = md5($senha);
    $result_usuario = "SELECT * FROM usuarios WHERE usuario='$usuario' AND senha='$senha' LIMIT 1";
    $resultado_usuario = mysqli_query($conn, $result_usuario);
    if ($resultado_usuario && mysqli_num_rows($resultado_usuario) > 0) {
        $row_usuario = mysqli_fetch_assoc($resultado_usuario);
        $_SESSION['usuarioId'] = $row_usuario['id'];
        $_SESSION['usuarioNome'] = $row_usuario['nome'];
        $_SESSION['usuarioNiveisAcessoId'] = $row_usuario['niveis_acesso_id'];
        $_SESSION['usuarioEmail'] = $row_usuario['email'];
        if ($_SESSION['usuarioNiveisAcessoId'] == "1") {
            header("Location: ../admin.php");
        } elseif ($_SESSION['usuarioNiveisAcessoId'] == "2") {
            header("Location: ../colaborador.php");
        } else {
            header("Location: ../login.php");
        }
    } else {
        $_SESSION['loginErro'] = "Usuário ou senha inválidos";
        header("Location: ../login.php");
    }
} else {
    $_SESSION['loginErro'] = "Usuário ou senha inválidos";
    header("Location: ../login.php");
}