<?php
session_start();

require_once '../processamento/conexao.php';

$recoveryStep = $_SESSION['recovery_step'] ?? 'request';
$recoveryMessage = '';
$recoveryMessageType = 'neutral';
$generatedCode = $_SESSION['recovery_code'] ?? null;
$storedUser = $_SESSION['recovery_user'] ?? '';
$storedUserId = $_SESSION['recovery_user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_action'])) {
    $action = $_POST['reset_action'];

    if ($action === 'generate') {
        $username = trim($_POST['usuario_recuperacao'] ?? '');

        if ($username === '') {
            $recoveryMessage = 'Informe o usuário ou e-mail associado à conta.';
            $recoveryMessageType = 'error';
            $recoveryStep = 'request';
        } else {
            $stmt = $conn->prepare('SELECT id, usuario, email FROM usuarios WHERE usuario = ? OR email = ? LIMIT 1');
            if ($stmt) {
                $stmt->bind_param('ss', $username, $username);
                $stmt->execute();
                $result = $stmt->get_result();
                $userRow = $result ? $result->fetch_assoc() : null;
                $stmt->close();

                if ($userRow) {
                    $generatedCode = random_int(100000, 999999);
                    $_SESSION['recovery_code'] = $generatedCode;
                    $_SESSION['recovery_step'] = 'verify';
                    $_SESSION['recovery_user'] = $username;
                    $_SESSION['recovery_user_id'] = $userRow['id'];

                    $recoveryStep = 'verify';
                    $recoveryMessage = 'Guarde este número e digite no campo abaixo para liberar a alteração.';
                    $recoveryMessageType = 'success';
                    $storedUser = $username;
                    $storedUserId = $userRow['id'];
                } else {
                    $recoveryMessage = 'Usuário ou e-mail não encontrado.';
                    $recoveryMessageType = 'error';
                    $recoveryStep = 'request';
                }
            } else {
                $recoveryMessage = 'Falha ao consultar o banco. Tente novamente mais tarde.';
                $recoveryMessageType = 'error';
                $recoveryStep = 'request';
            }
        }
    }

    if ($action === 'verify') {
        $typedCode = trim($_POST['codigo_digitado'] ?? '');
        $sessionCode = $_SESSION['recovery_code'] ?? '';

        if ($typedCode !== '' && $typedCode === (string) $sessionCode) {
            $_SESSION['recovery_step'] = 'password';
            $recoveryStep = 'password';
            $recoveryMessage = 'Código confirmado! Agora escolha uma nova senha.';
            $recoveryMessageType = 'success';
        } else {
            $recoveryStep = 'verify';
            $recoveryMessage = 'Código incorreto. Verifique o número exibido e tente novamente.';
            $recoveryMessageType = 'error';
        }
    }

    if ($action === 'update') {
        $newPassword = trim($_POST['nova_senha'] ?? '');
        $confirmPassword = trim($_POST['confirmar_senha'] ?? '');
        $storedUserId = $_SESSION['recovery_user_id'] ?? null;

        if ($newPassword === '' || $confirmPassword === '') {
            $recoveryMessage = 'Preencha e confirme a nova senha.';
            $recoveryMessageType = 'error';
            $recoveryStep = 'password';
        } elseif ($newPassword !== $confirmPassword) {
            $recoveryMessage = 'As senhas não coincidem. Tente novamente.';
            $recoveryMessageType = 'error';
            $recoveryStep = 'password';
        } elseif (strlen($newPassword) < 6) {
            $recoveryMessage = 'Use pelo menos 6 caracteres para a nova senha.';
            $recoveryMessageType = 'error';
            $recoveryStep = 'password';
        } elseif (!$storedUserId) {
            $recoveryMessage = 'Sessão expirada. Gere um código novamente.';
            $recoveryMessageType = 'error';
            $recoveryStep = 'request';
            unset($_SESSION['recovery_code'], $_SESSION['recovery_step'], $_SESSION['recovery_user'], $_SESSION['recovery_user_id']);
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('UPDATE usuarios SET senha = ? WHERE id = ?');

            if ($stmt) {
                $stmt->bind_param('si', $hashedPassword, $storedUserId);
                $stmt->execute();
                $stmt->close();

                $recoveryMessage = 'Senha redefinida com sucesso! Utilize a nova senha para acessar o sistema.';
                $recoveryMessageType = 'success';
                $recoveryStep = 'request';
                unset($_SESSION['recovery_code'], $_SESSION['recovery_step'], $_SESSION['recovery_user'], $_SESSION['recovery_user_id']);
                $storedUser = '';
                $storedUserId = null;
            } else {
                $recoveryMessage = 'Não foi possível atualizar a senha. Tente novamente mais tarde.';
                $recoveryMessageType = 'error';
                $recoveryStep = 'password';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Studio360 - Recuperar Senha</title>
    <link rel="stylesheet" href="../css/views/auth.css">
</head>
<body>
    <div class="auth-container">
        <h2 class="auth-title">Recuperar Senha</h2>

        <?php if ($recoveryMessage !== ''): ?>
            <p class="recover-message recover-<?php echo htmlspecialchars($recoveryMessageType); ?>">
                <?php echo htmlspecialchars($recoveryMessage); ?>
            </p>
        <?php endif; ?>

        <?php if ($recoveryStep === 'request'): ?>
            <form method="post" class="auth-form">
                <input type="hidden" name="reset_action" value="generate">
                <label for="usuario_recuperacao">Usuário ou e-mail</label>
                <input type="text" id="usuario_recuperacao" name="usuario_recuperacao" class="auth-input" value="<?php echo htmlspecialchars($storedUser); ?>" required>
                <button type="submit" class="auth-button">Gerar código</button>
            </form>
        <?php elseif ($recoveryStep === 'verify'): ?>
            <div class="recover-card">
                <p class="recover-info">
                    Código gerado: <span class="recover-highlight"><?php echo htmlspecialchars((string) ($_SESSION['recovery_code'] ?? '')); ?></span>
                </p>
                <form method="post" class="auth-form">
                    <input type="hidden" name="reset_action" value="verify">
                    <label for="codigo_digitado">Digite o código exibido</label>
                    <input type="text" id="codigo_digitado" name="codigo_digitado" class="auth-input" maxlength="6" required>
                    <button type="submit" class="auth-button">Validar código</button>
                </form>
            </div>
        <?php elseif ($recoveryStep === 'password'): ?>
            <form method="post" class="auth-form">
                <input type="hidden" name="reset_action" value="update">
                <label for="nova_senha">Nova senha</label>
                <input type="password" id="nova_senha" name="nova_senha" class="auth-input" required>
                <label for="confirmar_senha">Confirmar nova senha</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha" class="auth-input" required>
                <button type="submit" class="auth-button">Salvar nova senha</button>
            </form>
        <?php endif; ?>

        <p class="auth-footer">
            Lembrou a senha? <a href="./telaLogin.php">Voltar para o login</a>
        </p>
    </div>
</body>
</html>
