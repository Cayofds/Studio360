<?php
session_start();
include_once("../processamento/conexao.php");

// Require login
if (empty($_SESSION['usuarioId'])) {
    $_SESSION['postErro'] = 'Você precisa estar logado para ver seus posts.';
    header('Location: ./telaLogin.php');
    exit;
}

$usuarioId = (int) $_SESSION['usuarioId'];
$displayName = htmlspecialchars($_SESSION['usuarioNome'] ?? $_SESSION['usuarioEmail'] ?? 'Você', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Meus Posts - Studio 360</title>
  <link rel="stylesheet" href="../css/views/home.css">
</head>
<body>
    <div id="topo"></div>
    <header>
        <div class="container">
        <h1 class="logo">Studio 360</h1>
        <nav>
            <ul class="nav-links">
            <li><a href="./telaHome.php">Home</a></li>
            <li><a href="./telaNovoPost.php">Novo post</a></li>
            <li><span>Olá, <?php echo $displayName; ?></span></li>
            <li><a style="color: var(--cor-verde)" href="../processamento/logout.php">Sair</a></li>
            </ul>
        </nav>
        </div>
    </header>

    <section class="janela">
        <h2>Meus Trabalhos</h2>
        <div class="cards-container">
        <?php
        try {
            // detect category FK columns
            $has_id_categoria = false;
            $has_id_classe = false;
            try {
            $cols = $conn->query("SHOW COLUMNS FROM posts");
            if ($cols) {
                while ($c = $cols->fetch_assoc()) {
                if ($c['Field'] === 'id_categoria') $has_id_categoria = true;
                if ($c['Field'] === 'id_classe') $has_id_classe = true;
                }
            }
            } catch (mysqli_sql_exception $e) {
            error_log('telaMeusPosts.php - show columns posts failed: '.$e->getMessage());
            }

            // JOINS
            $joins = '';
            $categoryExpr = "NULL AS categoria";
            if ($has_id_categoria) {
            $joins .= " LEFT JOIN categorias c ON p.id_categoria = c.id";
            $categoryExpr = "c.descricao AS categoria";
            }
            if ($has_id_classe) {
            $joins .= " LEFT JOIN classes cl ON p.id_classe = cl.id";
            if ($categoryExpr === "NULL AS categoria") $categoryExpr = "cl.nome AS categoria";
            else $categoryExpr = "COALESCE(c.descricao, cl.nome) AS categoria";
            }

            // consulta final
            $sql = "SELECT 
                    p.id, 
                    p.titulo, 
                    p.img, 
                    {$categoryExpr}, 
                    u.usuario AS nome_usuario, 
                    u.foto_perfil
                    FROM posts p
                    {$joins}
                    LEFT JOIN usuarios u ON p.id_usuario = u.id
                    WHERE p.id_usuario = {$usuarioId}
                    ORDER BY p.id DESC 
                    LIMIT 50";

            $res = $conn->query($sql);

            if ($res && $res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {

                // título e categoria
                $title = htmlspecialchars($row['titulo'] ?? '');
                $categoria = htmlspecialchars($row['categoria'] ?? '');

                // nome do usuário
                $displayName = htmlspecialchars($row['nome_usuario'] ?? 'Usuário');

                // imagem de perfil
                $perfilSrc = "img/teste-Perfil.png";
                if (!empty($row['foto_perfil'])) {
                    $perfilSrc = "data:image/jpeg;base64," . base64_encode($row['foto_perfil']);
                }

                // imagem de fundo do post
                $bgStyle = "background-image: url('https://picsum.photos/600/400?random=" . ((int)$row['id'] % 100) . "');";
                if (!empty($row['img'])) {
                $mime = false;
                if (function_exists('finfo_open')) {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_buffer($finfo, $row['img']);
                    finfo_close($finfo);
                }
                if ($mime) {
                    $b64 = base64_encode($row['img']);
                    $bgStyle = "background-image: url('data:{$mime};base64,{$b64}'); background-size: cover; background-position: center;";
                }
                }
                ?>

                <div class="card" style="<?= $bgStyle ?>">
                <div class="card-content">
                    <h2 class="card-title"><?= $title ?></h2>
                    <p class="card-category">Categoria: <?= $categoria ?: '—' ?></p>

                    <div class="profile">
                        <img src="<?= $perfilSrc ?>" class="foto-usuario-post" alt="Usuário">
                        <span class="profile-name"><?= $displayName ?></span>
                    </div>
                </div>
                </div>

                <?php
            }
            } else {
            echo '<p>Você ainda não publicou nenhum post.</p>';
            }

        } catch (mysqli_sql_exception $e) {
            error_log('telaMeusPosts.php - posts query failed: '.$e->getMessage());
            echo '<p>Erro ao carregar seus posts.</p>';
        }
        ?>
        </div>
    </section>
</body>
</html>
