<?php
session_start();
include_once("../processamento/conexao.php");

if (empty($_SESSION['usuarioId']) || !isset($_SESSION['usuarioNiveisAcessoId']) || (int) $_SESSION['usuarioNiveisAcessoId'] !== 0) {
    $_SESSION['loginErro'] = "Acesso negado";
    header("Location: ./telaLogin.php");
    exit;
}

$nivelAlvo = 3;
$usuarios = [];
$erroCarregamento = null;
$colunasDisponiveis = [];

if ($conn) {
    try {
        $cols = $conn->query("SHOW COLUMNS FROM usuarios");
        if ($cols) {
            while ($col = $cols->fetch_assoc()) {
                $colunasDisponiveis[] = $col['Field'];
            }
        }
    } catch (mysqli_sql_exception $e) {
        $erroCarregamento = "Não foi possível ler o esquema da tabela.";
    }

    $selectCols = ['id', 'usuario', 'email'];
    if (in_array('nome_real', $colunasDisponiveis, true)) { $selectCols[] = 'nome_real'; }
    if (in_array('foto_perfil', $colunasDisponiveis, true)) { $selectCols[] = 'foto_perfil'; }
    if (in_array('cnpj', $colunasDisponiveis, true)) { $selectCols[] = 'cnpj'; }

    $selectCols = array_unique($selectCols);
    $ordem = in_array('nome_real', $colunasDisponiveis, true) ? 'nome_real' : 'usuario';
    $sql = "SELECT " . implode(', ', $selectCols) . " FROM usuarios WHERE nivel = ? ORDER BY {$ordem}, id";

    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $nivelAlvo);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $usuarios[] = $row;
            }
        }
        mysqli_stmt_close($stmt);
    } else {
        $erroCarregamento = "Não foi possível carregar as empresas.";
    }
} else {
    $erroCarregamento = "Conexão com o banco não encontrada.";
}

function formatDisplayName(array $row): string {
    if (!empty($row['nome_real'])) {
        return $row['nome_real'];
    }
    if (!empty($row['usuario'])) {
        return $row['usuario'];
    }
    return '—';
}

function resolveProfileImage(?string $raw): string {
    if (empty($raw)) {
        return '../img/teste-Perfil.png';
    }
    $value = (string) $raw;
    if (ctype_print($value) && preg_match('/\.(png|jpe?g|gif|webp)$/i', trim($value))) {
        return '../uploads/' . basename(trim($value));
    }
    $mime = 'image/jpeg';
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $detected = finfo_buffer($finfo, $value);
            if ($detected) {
                $mime = $detected;
            }
            finfo_close($finfo);
        }
    }
    return 'data:' . $mime . ';base64,' . base64_encode($value);
}

$total = count($usuarios);
$exibirCnpj = in_array('cnpj', $colunasDisponiveis, true);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empresas cadastradas</title>
    <link rel="stylesheet" href="../css/variaveis.css">
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/views/visualizar-lista.css">
</head>
<body>
    <header class="list-header">
        <div class="container">
            <div>
                <p class="label">Painel administrativo</p>
                <h1>Empresas cadastradas</h1>
                <p>Visualize todas as contas empresariais aptas a contratar e divulgar oportunidades.</p>
            </div>
            <a class="btn-link" href="./telaAdmin.php">Voltar ao painel</a>
        </div>
    </header>

    <main class="list-main">
        <div class="container">
            <div class="stats-row">
                <div class="stat-chip">
                    <span>Total de empresas</span>
                    <strong><?php echo $total; ?></strong>
                </div>
            </div>

            <div class="table-wrapper">
                <?php if ($erroCarregamento): ?>
                    <p class="empty-state"><?php echo htmlspecialchars($erroCarregamento, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php elseif (empty($usuarios)): ?>
                    <p class="empty-state">Nenhuma empresa cadastrada no momento.</p>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Perfil</th>
                                <th>E-mail</th>
                                <?php if ($exibirCnpj): ?><th>CNPJ</th><?php endif; ?>
                                <th>ID</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td>
                                        <div class="avatar-cell">
                                            <img src="<?php echo htmlspecialchars(resolveProfileImage($usuario['foto_perfil'] ?? null), ENT_QUOTES, 'UTF-8'); ?>" alt="Logo da empresa">
                                            <div>
                                                <strong><?php echo htmlspecialchars(formatDisplayName($usuario), ENT_QUOTES, 'UTF-8'); ?></strong>
                                                <small><?php echo htmlspecialchars($usuario['usuario'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($usuario['email'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <?php if ($exibirCnpj): ?>
                                        <td><?php echo htmlspecialchars($usuario['cnpj'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <?php endif; ?>
                                    <td>#<?php echo htmlspecialchars($usuario['id'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>