<?php
// Tela de escolha de tipo de cadastro: Criador, Empresa, Visitante
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Cadastro - Escolha</title>
		<link rel="stylesheet" href="../css/views/auth.css">
		<link rel="stylesheet" href="../css/views/choice.css">
</head>
<body>
	<div class="auth-container">
		<h1 class="auth-title">Escolha o tipo de cadastro</h1>

		<div class="choices">
			<div class="choice-card">
				<div class="choice-title">Criador</div>
				<div class="choice-desc">Cadastre-se como criador para publicar trabalhos, portfolios e interagir com clientes.</div>
				<div class="choice-actions">
					<a class="auth-button" href="cadastroCriador.php">Cadastrar como Criador</a>
				</div>
			</div>

			<div class="choice-card">
				<div class="choice-title">Empresa</div>
				<div class="choice-desc">Cadastre sua empresa para divulgar serviços, contratar profissionais e gerenciar portfólios empresariais.</div>
				<div class="choice-actions">
					<a class="auth-button" href="cadastroEmpresa.php">Cadastrar Empresa</a>
				</div>
			</div>

			<div class="choice-card">
				<div class="choice-title">Visitante</div>
				<div class="choice-desc">Cadastro simples para visitantes que desejam comentar, favoritar ou entrar em contato com criadores.</div>
				<div class="choice-actions">
					<a class="auth-button" href="cadastroVisitante.php">Cadastrar como Visitante</a>
				</div>
			</div>
		</div>

		<div class="auth-footer">
			<a href="telaLogin.php">Já tenho conta — Entrar</a>
		</div>
	</div>
</body>
</html>
