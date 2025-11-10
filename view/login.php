<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/login.css">
    <title>Studio360 - Login</title>
</head>
<body>
    <form action="../processamento/login.php" method="post">
        <div class="login-box">
            <h2>Login</h2>
            <div class="user-box">
                <input type="text" name="username" required="">
                <label>Usuário</label>
            </div>
            <div class="user-box">
                <input type="senha" name="senha" required="">
                <label>Senha</label>
            </div>
            <button type="submit">Entrar</button>

            <p class="signup-link">
                Não tem uma conta? <a href="cadastro.html">Cadastre-se</a>
            </p>
    </form>
</body>
</html>