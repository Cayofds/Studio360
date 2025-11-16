<?php
session_start();
include_once("../processamento/conexao.php");

if (empty($_SESSION['usuarioId']) || !isset($_SESSION['usuarioNiveisAcessoId']) || (int)$_SESSION['usuarioNiveisAcessoId'] !== 0) {
    $_SESSION['loginErro'] = "Acesso restrito aos administradores.";
    header("Location: ./telaLogin.php");
    exit;
}

$stats = [
    'totalUsuarios' => 0,
    'totalCriadores' => 0,
    'totalEmpresas' => 0,
    'totalPosts' => 0
];

function fetch_count($conn, $sql, $types = '', $params = []) {
    $total = 0;
    if (!$conn) {
        return $total;
    }
    if ($stmt = mysqli_prepare($conn, $sql)) {
        if ($types && $params) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $total);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
    }
    return (int)$total;
}

$stats['totalUsuarios'] = fetch_count($conn, "SELECT COUNT(*) FROM usuarios");
$stats['totalCriadores'] = fetch_count($conn, "SELECT COUNT(*) FROM usuarios WHERE nivel = 2");
$stats['totalEmpresas'] = fetch_count($conn, "SELECT COUNT(*) FROM usuarios WHERE nivel = 3");
$stats['totalPosts'] = fetch_count($conn, "SELECT COUNT(*) FROM posts");

$adminName = $_SESSION['usuarioNome'] ?? $_SESSION['usuarioEmail'] ?? 'Administrador';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Administrador • Studio 360</title>
    <link rel="stylesheet" href="../css/variaveis.css">
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/views/admin-panel.css">
</head>
<body>
<header class="admin-header">
    <div class="container">
        <div class="brand">
            <h1>Studio 360</h1>
            <p>Painel Administrativo</p>
        </div>
        <nav>
            <ul>
                <li><a href="./telaHome.php">Home</a></li>
                <li><a href="./telaNovoPost.php">Novo Post</a></li>
                <li><a href="./cadastroAdmin.php">Adicionar Admin</a></li>
                <li><a href="./visualizarAdmin.php">Administradores</a></li>
                <li><a href="./visualizarVisitante.php">Visitantes</a></li>
                <li><a href="./visualizarCriador.php">Criadores</a></li>
                <li><a href="./visualizarEmpresa.php">Empresas</a></li>
                <li><a class="logout" href="../processamento/logout.php">Sair</a></li>
            </ul>
        </nav>
    </div>
</header>

<main>
    <section class="admin-hero">
        <div class="container">
            <p class="label">Olá, <?php echo htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8'); ?></p>
            <h2>Controle total do Studio 360</h2>
            <p>Centralize tarefas operacionais, crie novos administradores, acompanhe métricas e entre rapidamente em outras áreas da plataforma.</p>
            <div class="hero-actions">
                <a class="btn" href="./cadastroAdmin.php">Adicionar administrador</a>
                <a class="btn btn-outline" href="./visualizarAdmin.php">Ver meus dados</a>
            </div>
        </div>
    </section>

    <section class="admin-stats container">
        <div class="stat-card">
            <span>Total de usuários</span>
            <strong><?php echo $stats['totalUsuarios']; ?></strong>
        </div>
        <div class="stat-card">
            <span>Criadores</span>
            <strong><?php echo $stats['totalCriadores']; ?></strong>
        </div>
        <div class="stat-card">
            <span>Empresas</span>
            <strong><?php echo $stats['totalEmpresas']; ?></strong>
        </div>
        <div class="stat-card">
            <span>Postagens</span>
            <strong><?php echo $stats['totalPosts']; ?></strong>
        </div>
    </section>

    <section class="admin-shortcuts">
        <div class="container">
            <h3>Atalhos rápidos</h3>
            <div class="shortcut-grid">
                <a class="shortcut-card" href="./cadastroAdmin.php">
                    <h4>Cadastrar admin</h4>
                    <p>Crie contas administrativas adicionais com foto e dados completos.</p>
                </a>
                <a class="shortcut-card" href="./visualizarAdmin.php">
                    <h4>Visualizar admins</h4>
                    <p>Veja todas as contas com privilégios administrativos.</p>
                </a>
                <a class="shortcut-card" href="./visualizarVisitante.php">
                    <h4>Visitantes</h4>
                    <p>Consulte a lista completa de perfis visitantes.</p>
                </a>
                <a class="shortcut-card" href="./visualizarCriador.php">
                    <h4>Criadores</h4>
                    <p>Acompanhe quem está produzindo conteúdo na plataforma.</p>
                </a>
                <a class="shortcut-card" href="./visualizarEmpresa.php">
                    <h4>Empresas</h4>
                    <p>Verifique as contas empresariais registradas.</p>
                </a>
                <a class="shortcut-card" href="./telaHome.php">
                    <h4>Trabalhos recentes</h4>
                    <p>Acompanhe tudo que está sendo publicado na plataforma.</p>
                </a>
                <a class="shortcut-card" href="./telaNovoPost.php">
                    <h4>Novo post</h4>
                    <p>Publique conteúdos oficiais ou comunicados importantes.</p>
                </a>
                <a class="shortcut-card" href="./telaMeusPosts.php">
                    <h4>Meus posts</h4>
                    <p>Gerencie os conteúdos publicados pela sua conta.</p>
                </a>
                <a class="shortcut-card" href="./telaPerfil.php">
                    <h4>Perfil completo</h4>
                    <p>Veja como os outros usuários enxergam suas informações.</p>
                </a>
            </div>
        </div>
    </section>

    <section class="admin-support">
        <div class="container">
            <div>
                <p class="label">Precisa de ajuda?</p>
                <h3>Recursos de apoio</h3>
                <p>Acesse os documentos do projeto e o seed SQL para criar rapidamente um ambiente funcional.</p>
            </div>
            <div class="support-links">
                <a href="../docs/seed_admin.sql" class="btn" download>Baixar seed Admin</a>
                <a href="../studio360.sql" class="btn btn-outline" download>Baixar banco completo</a>
            </div>
        </div>
    </section>
</main>
</body>
</html>
