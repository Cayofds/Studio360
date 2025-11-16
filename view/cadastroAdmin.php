<?php
session_start();
include_once("../processamento/conexao.php");

$adminExists = false;
if (isset($conn)) {
    if ($stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM usuarios WHERE nivel = 0")) {
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $total);
        if (mysqli_stmt_fetch($stmt)) {
            $adminExists = ((int)$total) > 0;
        }
        mysqli_stmt_close($stmt);
    }
}

if ($adminExists) {
    if (empty($_SESSION['usuarioId']) || !isset($_SESSION['usuarioNiveisAcessoId']) || (int) $_SESSION['usuarioNiveisAcessoId'] !== 0) {
        $_SESSION['loginErro'] = "Acesso restrito a administradores.";
        header("Location: ./telaLogin.php");
        exit;
    }
}

$primeiroAdmin = !$adminExists;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Administrador</title>
    <link rel="stylesheet" href="../css/variaveis.css">
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/views/admin-register.css">
</head>
<body>
    <div class="auth-container">
        <h1 class="auth-title">Cadastro de Administrador</h1>
        <?php if ($primeiroAdmin): ?>
        <div class="auth-alert" style="background:#e6f7ff;border:1px solid #91d5ff;color:#004a75;padding:12px;border-radius:8px;margin-bottom:16px;">
            Este formulário está liberado porque nenhum administrador foi encontrado. Após criar o primeiro, somente admins logados poderão acessar esta página.
        </div>
        <?php endif; ?>
        <form class="auth-form register-form" method="post" action="../processamento/cadastro.php" enctype="multipart/form-data">
            <label for="usuario">Nome da conta</label>
            <input type="text" id="usuario" name="usuario" class="auth-input" placeholder="nome_de_usuário" required>

            <label for="nome">Nome</label>
            <input type="text" id="nome" name="nome" class="auth-input" placeholder="Nome completo" required>

            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" class="auth-input" placeholder="admin@exemplo.com" required>

            <label for="senha">Senha</label>
            <input type="password" id="senha" name="senha" class="auth-input" placeholder="Crie uma senha forte" required>

            <label for="senha_confirm">Confirmar senha</label>
            <input type="password" id="senha_confirm" name="senha_confirm" class="auth-input" placeholder="Confirme a senha" required>

            <label for="foto_perfil">Foto de perfil (opcional)</label>
            <input type="file" id="foto_perfil" name="foto_perfil" class="auth-input" accept="image/*">

            <input type="hidden" name="tipo_cadastro" value="admin">

            <button type="submit" class="auth-button">Cadastrar</button>
        </form>

        <div class="auth-footer" style="margin-top:12px;">
            <a class="btn-secondary" href="./telaCadastro.php">Voltar</a>
        </div>
    </div>
</body>
</html>
<script src="../scripts/script.js"></script>


