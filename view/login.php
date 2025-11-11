<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Studio360 - Login</title>
</head>
<body>
    <form action="../processamento/login.php" method="post">
        <div class="login-container">
            <h2>Login</h2>
            <form action="login.php" method="POST">
                <label for="usuario">Usuário</label>
                <input type="text" id="usuario" name="usuario" required>

                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required>

                <button type="submit">Entrar</button>
            </form>

            <p>Não tem uma conta? <a href="#">Cadastre-se</a></p>
        </div>
    </form>
</body>
</html>