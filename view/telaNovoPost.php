<?php
session_start();
include_once("../processamento/conexao.php");

// Require login and creator level (nivel == 2)
if (empty($_SESSION['usuarioId']) || !isset($_SESSION['usuarioNiveisAcessoId']) || (int)$_SESSION['usuarioNiveisAcessoId'] !== 2) {
    $_SESSION['postErro'] = 'Apenas criadores logados podem criar posts.';
    header('Location: ./telaLogin.php');
    exit;
}

$usuarioId = (int) $_SESSION['usuarioId'];

// Handle POST (create new post)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
  $id_categoria = isset($_POST['id_categoria']) ? (int) $_POST['id_categoria'] : 0;

  // keep old values in session so form can be repopulated after redirect-on-error
  $_SESSION['postOld'] = [
    'titulo' => $titulo,
    'id_categoria' => $id_categoria
  ];

  if ($titulo === '') {
    $_SESSION['postErro'] = 'Título é obrigatório.';
    header('Location: ./telaNovoPost.php');
    exit;
  }
  if ($id_categoria <= 0) {
    $_SESSION['postErro'] = 'Categoria é obrigatória.';
    header('Location: ./telaNovoPost.php');
    exit;
  }

    // process image upload (optional)
    $imgData = null;
    if (!empty($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['img']['tmp_name'];
        $fsize = filesize($tmp);
        if ($fsize > 5 * 1024 * 1024) { // limit 5MB
            $_SESSION['postErro'] = 'Imagem muito grande (máx 5MB).';
            header('Location: ./telaNovoPost.php');
            exit;
        }
        $mime = mime_content_type($tmp);
        $allowed = ['image/jpeg','image/png','image/gif'];
        if (!in_array($mime, $allowed)) {
            $_SESSION['postErro'] = 'Tipo de imagem não permitido. Use PNG/JPG/GIF.';
            header('Location: ./telaNovoPost.php');
            exit;
        }
        $imgData = file_get_contents($tmp);
    }

  // Insert into posts
  // Detect which foreign-key column exists in `posts` (prefer `id_categoria`, fallback to `id_classe`).
  $fk_column = null;
  try {
    $cols = $conn->query("SHOW COLUMNS FROM posts");
    if ($cols) {
      while ($col = $cols->fetch_assoc()) {
        if ($col['Field'] === 'id_categoria') { $fk_column = 'id_categoria'; break; }
        if ($col['Field'] === 'id_classe' && $fk_column === null) { $fk_column = 'id_classe'; }
      }
    }
  } catch (mysqli_sql_exception $e) {
    error_log('telaNovoPost.php - show columns posts failed: '.$e->getMessage());
  }

  if ($fk_column) {
    $sql = "INSERT INTO posts (img, titulo, id_usuario, {$fk_column}) VALUES (?, ?, ?, ?)";
  } else {
    // posts table doesn't have a category/class FK column; insert without it
    $sql = "INSERT INTO posts (img, titulo, id_usuario) VALUES (?, ?, ?)";
  }

  $stmt = $conn->prepare($sql);
  if (!$stmt) {
    $_SESSION['postErro'] = 'Erro interno. Tente novamente.';
    header('Location: ./telaNovoPost.php');
    exit;
  }

  // bind parameters: img (blob or null), titulo (s), id_usuario (i), [categoria/class id (i) optional]
  $imgParam = $imgData !== null ? $imgData : null;
  if ($fk_column) {
    $stmt->bind_param('ssii', $imgParam, $titulo, $usuarioId, $id_categoria);
  } else {
    $stmt->bind_param('ssi', $imgParam, $titulo, $usuarioId);
  }
    $ok = $stmt->execute();
    if (!$ok) {
        $_SESSION['postErro'] = 'Erro ao salvar post: ' . $stmt->error;
        $stmt->close();
        header('Location: ./telaNovoPost.php');
        exit;
    }
    $stmt->close();

    $_SESSION['postSucesso'] = 'Post criado com sucesso.';
  // clear old inputs on success
  if (isset($_SESSION['postOld'])) unset($_SESSION['postOld']);
  header('Location: ./telaHome.php');
    exit;
}

// Read classes for select (optional)
$categorias = [];
// Try to read categories for the select. Prefer `categorias` table, but fall
// back to `classes` for compatibility. If neither exists we continue with an
// empty list and show a numeric input (still required).
try {
  // categorias table uses columns `id` and `descricao` (descricao holds the category name)
  $res = $conn->query("SELECT id, descricao FROM categorias ORDER BY descricao ASC");
    if ($res && $res->num_rows > 0) {
        while ($r = $res->fetch_assoc()) {
            $categorias[] = $r;
        }
    } else {
        // Fallback to `classes` if `categorias` is not present or empty
    $res2 = $conn->query("SELECT id, nome FROM classes ORDER BY nome ASC");
        if ($res2) {
            while ($r = $res2->fetch_assoc()) {
                $categorias[] = $r;
            }
        }
    }
} catch (mysqli_sql_exception $e) {
    // Table may not exist; avoid fatal error and log for debugging.
    error_log('telaNovoPost.php - categorias/classes query failed: ' . $e->getMessage());
    $categorias = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Novo Post</title>
  <link rel="stylesheet" href="../css/views/register.css">
  <style>
    .preview { max-width:220px; max-height:180px; display:block; margin-top:8px; }
    .form-row { margin-bottom:12px; }
  </style>
</head>
<body>
  <div class="auth-container">
    <h1 class="auth-title">Criar novo post</h1>

    <?php if (!empty($_SESSION['postErro'])): ?>
      <div class="auth-footer" style="color:#b91c1c; margin-bottom:10px"><?php echo htmlspecialchars($_SESSION['postErro']); unset($_SESSION['postErro']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['postSucesso'])): ?>
      <div class="auth-footer" style="color:#065f46; margin-bottom:10px"><?php echo htmlspecialchars($_SESSION['postSucesso']); unset($_SESSION['postSucesso']); ?></div>
    <?php endif; ?>

    <?php
      // populate old values if present
      $oldTitulo = '';
      $oldCategoria = 0;
      if (!empty($_SESSION['postOld']) && is_array($_SESSION['postOld'])) {
          $oldTitulo = isset($_SESSION['postOld']['titulo']) ? $_SESSION['postOld']['titulo'] : '';
          $oldCategoria = isset($_SESSION['postOld']['id_categoria']) ? (int)$_SESSION['postOld']['id_categoria'] : (isset($_SESSION['postOld']['id_categoria']) ? (int)$_SESSION['postOld']['id_categoria'] : (isset($_SESSION['postOld']['categoria']) ? (int)$_SESSION['postOld']['categoria'] : 0));
          // remove after using
          unset($_SESSION['postOld']);
      }
    ?>

    <form method="post" enctype="multipart/form-data">
      <div class="form-row">
        <label for="titulo">Título</label>
        <input type="text" id="titulo" name="titulo" class="auth-input" required value="<?php echo htmlspecialchars($oldTitulo ?? ''); ?>">
      </div>

      <div class="form-row">
        <label for="id_categoria">Categoria</label>
        <?php if (!empty($categorias)): ?>
          <select id="id_categoria" name="id_categoria" class="auth-input" required>
            <option value="0" disabled>— escolha —</option>
            <?php foreach ($categorias as $c): ?>
              <?php $label = isset($c['descricao']) ? $c['descricao'] : (isset($c['nome']) ? $c['nome'] : ''); ?>
              <option value="<?php echo (int)$c['id']; ?>" <?php echo ((int)$c['id'] === (int)$oldCategoria) ? 'selected' : ''; ?>><?php echo htmlspecialchars($label); ?></option>
            <?php endforeach; ?>
          </select>
        <?php else: ?>
          <input type="number" id="id_categoria" name="id_categoria" class="auth-input" placeholder="ID da categoria (obrigatório)" required value="<?php echo htmlspecialchars($oldCategoria ?? ''); ?>">
          <div style="font-size:0.9rem; color:#555; margin-top:6px">Nenhuma categoria disponível para selecionar — informe o ID da categoria manualmente.</div>
        <?php endif; ?>
      </div>

      <div class="form-row">
        <label for="img">Imagem (PNG/JPG/GIF, opcional)</label>
        <input type="file" id="img" name="img" accept="image/*" class="auth-input">
        <img id="preview" class="preview" style="display:none" src="#" alt="preview">
      </div>

      <div class="form-row">
        <button type="submit" class="auth-button">Publicar</button>
        <a class="btn-secondary" href="./telaHome.php" style="margin-left:8px">Voltar</a>
      </div>
    </form>
  </div>

<script>
document.getElementById('img').addEventListener('change', function(e){
  const f = e.target.files[0];
  const preview = document.getElementById('preview');
  if (!f) { preview.style.display='none'; return; }
  const url = URL.createObjectURL(f);
  preview.src = url; preview.style.display = 'block';
});
</script>
</body>
</html>
