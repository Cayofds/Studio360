<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Studio360 - Login</title>
    <link rel="stylesheet" href="../css/views/auth.css">
</head>
<body>
    <div class="auth-container">
        <h2 class="auth-title">Login</h2>
        <form class="auth-form" action="../processamento/login.php" method="post">
            <label for="usuario">Usuário</label>
            <input type="text" id="usuario" name="usuario" class="auth-input" required>

            <label for="senha">Senha</label>
            <input type="password" id="senha" name="senha" class="auth-input" required>

            <button type="submit" class="auth-button">Entrar</button>
        </form>

        <p class="auth-footer">
            Não tem uma conta? <a href="./cadastro.php">Cadastre-se</a>
        </p>
    </div>
</body>
</html>
