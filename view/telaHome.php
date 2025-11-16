<?php session_start(); include_once("../processamento/conexao.php"); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Studio 360</title>
  <link rel="stylesheet" href="../css/views/home.css">

</head>
<body>
  <!-- session started and DB connection moved to file top to avoid headers already sent -->
  <div id="topo"></div>
  <header>
    <div class="container">
      <h1 class="logo">Studio 360</h1>
      <nav>
        <ul class="nav-links">
          <li><a href="#topo">Home</a></li>
          <?php if (!empty($_SESSION['usuarioNiveisAcessoId']) && $_SESSION['usuarioNiveisAcessoId'] === 2): ?>
            <li><a href="./telaNovoPost.php">Novo post</a></li>
            <li><a href="./telaMeusPosts.php">Meus posts</a></li>
          <?php endif; ?>
          <?php if (!empty($_SESSION['usuarioNiveisAcessoId']) && (int)$_SESSION['usuarioNiveisAcessoId'] === 0): ?>
            <li><a href="./telaAdmin.php">Painel Admin</a></li>
            <li><a href="./cadastroAdmin.php">Adicionar Admin</a></li>
          <?php endif; ?>
          <?php if (!empty($_SESSION['usuarioId'])): ?>
            <li><span>Olá, <?php echo "<a href=\"./telaPerfil.php\">". htmlspecialchars($_SESSION['usuarioNome'] ?? $_SESSION['usuarioEmail'] ?? 'Usuário', ENT_QUOTES, 'UTF-8') . "</a>"; ?></span></li>
            <li><a style="color: #00ff00" href="../processamento/logout.php">Sair</a></li>
          <?php else: ?>
            <li><a href="./telaLogin.php">Login</a></li>
          <?php endif; ?>
        </ul>
      </nav>
    </div>
  </header>

  <section class="hero">
    <div class="hero-content">
      <h2>Ideias que giram o mundo.</h2>
      <p>Design, código, fotos e criatividade — tudo em um só lugar.</p>
      <?php if (!empty($_SESSION['usuarioId']) && isset($_SESSION['usuarioNiveisAcessoId'])): ?>
        <?php if ((int)$_SESSION['usuarioNiveisAcessoId'] === 0): ?>
          <div class="hero-actions">
            <a href="./telaAdmin.php" class="btn">Ir para o painel admin</a>
            <a href="./cadastroAdmin.php" class="btn btn-secondary">Adicionar novo admin</a>
          </div>
        <?php elseif ((int)$_SESSION['usuarioNiveisAcessoId'] === 2 || (int)$_SESSION['usuarioNiveisAcessoId'] === 3): ?>
          <a href="./telaNovoPost.php" class="btn">Fazer um novo Post</a>
        <?php else: ?>
          <a href="#" class="btn">Conheça os outros Trabalhos</a>
        <?php endif; ?>
      <?php else: ?>
        <a href="./telaCadastro.php" class="btn">Cadastre-se gratuitamente</a>
      <?php endif; ?>
    </div>
  </section>

  <?php if (!empty($_SESSION['usuarioId']) && isset($_SESSION['usuarioNiveisAcessoId']) && (int)$_SESSION['usuarioNiveisAcessoId'] === 0): ?>
    <section class="admin-section">
      <div class="container">
        <div class="admin-section__header">
          <div>
            <p class="label">Painel rápido</p>
            <h2>Ferramentas administrativas</h2>
            <p>Gerencie usuários, novos administradores e conteúdos sem sair da página inicial.</p>
          </div>
          <a href="./telaAdmin.php" class="btn">Abrir painel completo</a>
        </div>
        <div class="admin-grid">
          <a class="admin-card" href="./telaAdmin.php">
            <h3>Painel Admin</h3>
            <p>Visão geral com métricas e atalhos de gerenciamento.</p>
          </a>
          <a class="admin-card" href="./cadastroAdmin.php">
            <h3>Adicionar Admin</h3>
            <p>Cadastra rapidamente um novo administrador com foto e credenciais.</p>
          </a>
          <a class="admin-card" href="./visualizarAdmin.php">
            <h3>Meu Perfil</h3>
            <p>Atualize suas informações administrativas e foto de perfil.</p>
          </a>
          <a class="admin-card" href="./telaHome.php#posts">
            <h3>Ver postagens</h3>
            <p>Analise as publicações recentes de toda a comunidade.</p>
          </a>
        </div>
      </div>
    </section>
  <?php endif; ?>
  <section class="janela">
    <h2 id="posts">Trabalhos dos Usuários</h2>
    <div class="cards-container">
      <?php
      try {
        // Detect FK columns
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
          error_log('telaHome.php - show columns failed: '.$e->getMessage());
        }

        // ==========================================
        //  PRIMEIRA TENTATIVA COM JOIN DIRETO
        // ==========================================
        $fetched = false;

        try {
          $joins = '';
          $categoryExpr = "NULL AS categoria";

          if ($has_id_categoria) {
            $joins .= " LEFT JOIN categorias c ON p.id_categoria = c.id";
            $categoryExpr = "c.descricao AS categoria";
          }
          if ($has_id_classe) {
            $joins .= " LEFT JOIN classes cl ON p.id_classe = cl.id";

            if ($categoryExpr === "NULL AS categoria") {
              $categoryExpr = "cl.nome AS categoria";
            } else {
              $categoryExpr = "COALESCE(c.descricao, cl.nome) AS categoria";
            }
          }

          // Agora também buscamos a foto_perfil
          $sql = "SELECT 
                    p.id, 
                    p.titulo, 
                    p.img, 
                    {$categoryExpr}, 
                    u.usuario AS autor,
                    u.foto_perfil
                  FROM posts p
                  {$joins}
                  LEFT JOIN usuarios u ON p.id_usuario = u.id
                  ORDER BY p.id DESC 
                  LIMIT 20";

          $res = $conn->query($sql);

          if ($res && $res->num_rows > 0) {
            $fetched = true;

            while ($row = $res->fetch_assoc()) {

              $title = htmlspecialchars($row['titulo'] ?? '');
              $categoria = htmlspecialchars($row['categoria'] ?? '');
              $autor = htmlspecialchars($row['autor'] ?? 'Usuário');

              // Foto do usuário
              $perfilSrc = "img/teste-Perfil.png";
              if (!empty($row['foto_perfil'])) {
                $perfilSrc = "data:image/jpeg;base64," . base64_encode($row['foto_perfil']);
              }

              // Fundo do post
              $bgStyle = "background-image: url('https://picsum.photos/600/400?random=" . ((int)$row['id'] % 100) . "');";

              if (!empty($row['img'])) {
                $imgData = $row['img'];
                $mime = false;

                if (function_exists('finfo_open')) {
                  $finfo = finfo_open(FILEINFO_MIME_TYPE);
                  $mime = finfo_buffer($finfo, $imgData);
                  finfo_close($finfo);
                }

                if ($mime) {
                  $b64 = base64_encode($imgData);
                  $bgStyle = "background-image: url('data:{$mime};base64,{$b64}'); background-size: cover; background-position: center;";
                }
              }
              ?>

              <div class="card" style="<?= $bgStyle ?>">
                <div class="card-content">
                  <h2 class="card-title"><?= $title ?></h2>
                  <p class="card-category">Categoria: <?= $categoria ?: '—' ?></p>

                  <div class="profile">
                    <img src="<?= $perfilSrc ?>" class="foto-usuario-post" alt="Foto do usuário">
                    <span class="profile-name"><?= $autor ?></span>
                  </div>
                </div>
              </div>

              <?php
            }
          }
        } catch (mysqli_sql_exception $e) {
          error_log('telaHome.php - main query failed: '.$e->getMessage());
          $fetched = false;
        }

        // ==========================================
        //  FALLBACK SE JOIN FALHAR
        // ==========================================
        if (!$fetched) {
          echo "<p>Erro ao carregar categorias, mas posso te ajudar a ajustar se quiser.</p>";
        }

      } catch (mysqli_sql_exception $e) {
        error_log('telaHome.php - fatal error: '.$e->getMessage());
        echo '<p>Erro ao carregar posts.</p>';
      }
      ?>
    </div>
</section>


  <?php if (!empty($_SESSION['usuarioId']) && isset($_SESSION['usuarioNiveisAcessoId']) && (int)$_SESSION['usuarioNiveisAcessoId'] === 2): ?>
    <a href="./telaNovoPost.php" class="fab" title="Novo post">+</a>
  <?php endif; ?>
</body>
</html>