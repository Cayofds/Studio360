<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro Empresarial</title>
    <link rel="stylesheet" href="../css/views/register.css">
</head>
<body>
    <div class="auth-container">
        <h1 class="auth-title">Cadastro Empresarial</h1>
    <form class="auth-form register-form" method="post" action="../processamento/cadastro.php" enctype="multipart/form-data">
            <label for="usuario">Nome da conta</label>
            <input type="text" id="usuario" name="usuario" class="auth-input" placeholder="nome_de_usuário" required>

            <label for="nome_empresa">Nome da Empresa</label>
            <input type="text" id="nome_empresa" name="nome_empresa" class="auth-input" placeholder="Razão social" required>

            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" class="auth-input" placeholder="contato@empresa.com" required>

            <label for="telefone">Telefone</label>
            <input type="tel" id="telefone" name="telefone" class="auth-input" placeholder="(00) 00000-0000">

            <label for="foto">Foto de perfil (opcional)</label>
            <input type="file" id="foto" name="foto" accept="image/*" class="auth-input">

            <label for="cnpj">CNPJ</label>
            <input type="text" id="cnpj" name="cnpj" class="auth-input" placeholder="00.000.000/0001-00" required>

            <label for="senha">Senha</label>
            <input type="password" id="senha" name="senha" class="auth-input" placeholder="Crie uma senha" required>

            <label for="senha_confirm">Confirmar senha</label>
            <input type="password" id="senha_confirm" name="senha_confirm" class="auth-input" placeholder="Confirme a senha" required>

            <input type="hidden" name="tipo_cadastro" value="empresa">

            <button type="submit" class="auth-button">Cadastrar</button>
        </form>

        <div class="auth-footer" style="margin-top:12px;">
            <a class="btn-secondary" href="./telaCadastro.php">Voltar</a>
        </div>
    </div>
</body>
</html>
<script src="../scripts/script.js"></script>