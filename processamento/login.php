<?php
session_start();
include_once("conexao.php");

$usuario = trim(filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_STRING));
$senha = trim(filter_input(INPUT_POST, 'senha', FILTER_SANITIZE_STRING));
// campo opcional para distinguir áreas de login (ex.: 'view' vs 'root')
$area = trim(filter_input(INPUT_POST, 'area', FILTER_SANITIZE_STRING));

// rota de retorno em caso de erro — por padrão volta para a página de login dentro de /view
$login_return = ($area === 'view') ? '../view/telaLogin.php' : '../view/telaLogin.php';

if (!empty($usuario) && !empty($senha)) {
    // buscar usuário por usuário ou email (não incluímos a senha na cláusula WHERE)
    // selecionamos todas as colunas e tratamos os nomes dinamicamente para evitar erros de coluna
    $sql = "SELECT * FROM usuarios WHERE usuario = ? OR email = ? LIMIT 1";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ss", $usuario, $usuario);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = $result ? mysqli_fetch_assoc($result) : null;
        mysqli_stmt_close($stmt);

        if ($row) {
            // senha armazenada (nome da coluna esperado: 'senha')
            $stored_hash = isset($row['senha']) ? $row['senha'] : '';
            $password_ok = false;

            // tenta verificar com password_verify (hash moderno)
            if (function_exists('password_verify') && password_verify($senha, $stored_hash)) {
                $password_ok = true;
            } elseif (md5($senha) === $stored_hash) {
                // fallback para MD5 legado — atualiza para password_hash
                $password_ok = true;
                if (function_exists('password_hash')) {
                    $new_hash = password_hash($senha, PASSWORD_DEFAULT);
                    $up = mysqli_prepare($conn, "UPDATE usuarios SET senha = ? WHERE id = ?");
                    if ($up) {
                        mysqli_stmt_bind_param($up, "si", $new_hash, $row['id']);
                        mysqli_stmt_execute($up);
                        mysqli_stmt_close($up);
                    }
                }
            }

            if ($password_ok) {
                // popular sessão — determine o nível (coluna 'nivel') e escolha nome conforme regra:
                    $_SESSION['usuarioId'] = isset($row['id']) ? $row['id'] : null;
                    // determine access level: prefer 'nivel' column (int)
                    $access = null;
                    if (isset($row['nivel'])) {
                        $access = (int) $row['nivel'];
                    }
                    $_SESSION['usuarioNiveisAcessoId'] = $access;
                    // nome_real só deve ser usado quando o usuário for criador (nivel == 2)
                    if ($access === 2 && !empty($row['nome_real'])) {
                        $_SESSION['usuarioNome'] = $row['nome_real'];
                    } else {
                        $_SESSION['usuarioNome'] = isset($row['usuario']) ? $row['usuario'] : '';
                    }
                    $_SESSION['usuarioEmail'] = isset($row['email']) ? $row['email'] : '';

                    // redirecionar para a home da aplicação após login bem-sucedido
                    header('Location: ../view/telaHome.php');
                    exit;
            }
        }
    }

    $_SESSION['loginErro'] = "Usuário ou senha inválidos";
    header("Location: $login_return");
    exit;
} else {
    $_SESSION['loginErro'] = "Usuário ou senha inválidos";
    header("Location: $login_return");
    exit;
}