<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro Empresarial</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <h1 class="auth-title">Cadastro Empresarial</h1>
        <form class="auth-form">
            <label for="nome">Nome da Empresa</label>
            <input type="text" id="nome" class="auth-input" placeholder="Razão social" required>

            <label for="email">E-mail</label>
            <input type="email" id="email" class="auth-input" placeholder="contato@empresa.com" required>

            <label for="telefone">Telefone</label>
            <input type="tel" id="telefone" class="auth-input" placeholder="(00) 00000-0000">

            <label for="cnpj">CNPJ</label>
            <input type="text" id="cnpj" class="auth-input" placeholder="00.000.000/0001-00" required>

            <button type="submit" class="auth-button">Cadastrar</button>
        </form>
    </div>
</body>
</html>