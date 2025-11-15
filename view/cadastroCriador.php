<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Criador</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <h1 class="auth-title">Cadastro de Criador</h1>
        <form class="auth-form">
            <label for="nome">Nome</label>
            <input type="text" id="nome" class="auth-input" placeholder="Seu nome artístico" required>

            <label for="email">E-mail</label>
            <input type="email" id="email" class="auth-input" placeholder="seuemail@exemplo.com" required>

            <label for="senha">Senha</label>
            <input type="password" id="senha" class="auth-input" placeholder="Crie uma senha" required>

            <label for="telefone">Telefone</label>
            <input type="tel" id="telefone" class="auth-input" placeholder="(00) 00000-0000">

            <button type="submit" class="auth-button">Cadastrar</button>
        </form>
    </div>
</body>
</html>