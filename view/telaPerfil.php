<?php
session_start();
include_once("../processamento/conexao.php");

if (empty($_SESSION['usuarioId'])) {
    $_SESSION['loginErro'] = 'Você precisa estar logado para ver o perfil.';
    header('Location: ./telaLogin.php');
    exit;
}

$usuarioId = (int) $_SESSION['usuarioId'];

// detect available columns on usuarios
$available = [];
try {
    $cols = $conn->query("SHOW COLUMNS FROM usuarios");
    if ($cols) {
        while ($c = $cols->fetch_assoc()) {
            $available[] = $c['Field'];
        }
    }
} catch (mysqli_sql_exception $e) {
    error_log('telaPerfil.php - show columns usuarios failed: '.$e->getMessage());
}

// fetch the user row
$user = null;
try {
    $sql = "SELECT * FROM usuarios WHERE id = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('i', $usuarioId);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        $stmt->close();
    }
} catch (mysqli_sql_exception $e) {
    error_log('telaPerfil.php - user fetch failed: '.$e->getMessage());
}

if (!$user) {
    echo "<p>Usuário não encontrado.</p>";
    exit;
}

// helper to safely get a column if present
function colVal($arr, $col) {
    if (!empty($arr[$col])) return $arr[$col];
    return null;
}

$nivel = isset($user['nivel']) ? (int)$user['nivel'] : null;
$nivelLabel = 'Desconhecido';
switch ($nivel) {
    case 0: $nivelLabel = 'Administrador'; break;
    case 1: $nivelLabel = 'Visitante'; break;
    case 2: $nivelLabel = 'Criador'; break;
    case 3: $nivelLabel = 'Empresarial'; break;
}

// detect profile image column (common names)
$imgCol = null;
$possibleImgCols = ['avatar','foto','foto_perfil','imagem','img','profile_image'];
foreach ($possibleImgCols as $c) {
    if (in_array($c, $available)) { $imgCol = $c; break; }
}

// prepare image data URI if available
$imgDataUri = null;
if ($imgCol && !empty($user[$imgCol])) {
    $imgData = $user[$imgCol];
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_buffer($finfo, $imgData);
        finfo_close($finfo);
        if ($mime) {
            $b64 = base64_encode($imgData);
            $imgDataUri = "data:{$mime};base64,{$b64}";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Perfil - <?php echo htmlspecialchars($user['usuario'] ?? ''); ?></title>
  <link rel="stylesheet" href="../css/views/register.css">
  <style>
    .profile-box { max-width:820px; margin:36px auto; display:flex; gap:24px; align-items:flex-start; }
    .pf-avatar { width:200px; height:200px; border-radius:12px; background:#222; display:flex; align-items:center; justify-content:center; overflow:hidden; }
    .pf-avatar img { width:100%; height:100%; object-fit:cover; }
    .pf-info { flex:1; color:var(--cor-texto); }
    .pf-info h2 { color:var(--cor-branco); margin-bottom:6px }
    .pf-row { margin-bottom:10px; color:var(--cor-texto-suave); }
    .pf-label { display:inline-block; width:140px; font-weight:600; color:var(--cor-white); }
    .actions { margin-top:18px; }
    .btn-secondary { margin-right:8px; }
  </style>
</head>
<body>
  <div class="container">
    <div class="profile-box">
      <div class="pf-avatar">
        <?php if ($imgDataUri): ?>
          <img src="<?php echo $imgDataUri; ?>" alt="Avatar">
        <?php else: ?>
          <div style="text-align:center; padding:10px; color:var(--cor-texto-suave)">
            Sem foto
          </div>
        <?php endif; ?>
      </div>

      <div class="pf-info">
        <h2><?php echo htmlspecialchars($user['usuario'] ?? 'Usuário'); ?></h2>
        <div class="pf-row"><strong>Nível:</strong> <?php echo $nivelLabel; ?></div>
        <?php if (in_array('nome_real', $available) && !empty($user['nome_real'])): ?>
          <div class="pf-row"><strong>Nome real:</strong> <?php echo htmlspecialchars($user['nome_real']); ?></div>
        <?php endif; ?>

        <?php if (!empty($user['email'])): ?>
          <div class="pf-row"><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></div>
        <?php endif; ?>

        <?php if ($nivel === 2): // criador specific fields ?>
            <?php if (in_array('portfolio', $available) && !empty($user['portfolio'])): ?>
              <div class="pf-row"><strong>Portfolio:</strong> <?php echo htmlspecialchars($user['portfolio']); ?></div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($nivel === 3): // empresarial specific fields ?>
            <?php if (in_array('nome_empresa', $available) && !empty($user['nome_empresa'])): ?>
              <div class="pf-row"><strong>Empresa:</strong> <?php echo htmlspecialchars($user['nome_empresa']); ?></div>
            <?php endif; ?>
            <?php if (in_array('cnpj', $available) && !empty($user['cnpj'])): ?>
              <div class="pf-row"><strong>CNPJ:</strong> <?php echo htmlspecialchars($user['cnpj']); ?></div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (in_array('telefone', $available) && !empty($user['telefone'])): ?>
          <div class="pf-row"><strong>Telefone:</strong> <?php echo htmlspecialchars($user['telefone']); ?></div>
        <?php endif; ?>

        <div class="actions">
          <a class="btn-secondary" href="./telaMeusPosts.php">Meus posts</a>
          <a class="auth-button" href="./telaHome.php">Voltar</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
