<?php
session_start();
include_once("conexao.php");

// Helper: redirect back to the view cadastro with a message
function redirect_with_error($message, $back = '../view/telaCadastro.php') {
    $_SESSION['cadastroErro'] = $message;
    header("Location: $back");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_error('Requisição inválida.');
}

// basic POST cleanup
$tipo = isset($_POST['tipo_cadastro']) ? trim($_POST['tipo_cadastro']) : '';
$usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$senha = isset($_POST['senha']) ? $_POST['senha'] : '';
$senha_confirm = isset($_POST['senha_confirm']) ? $_POST['senha_confirm'] : '';
$telefone = isset($_POST['telefone']) ? trim($_POST['telefone']) : null;
$cnpj = isset($_POST['cnpj']) ? trim($_POST['cnpj']) : null;
$nome_real = isset($_POST['nome_real']) ? trim($_POST['nome_real']) : null;
$nome_empresa = isset($_POST['nome_empresa']) ? trim($_POST['nome_empresa']) : null;
$nome = isset($_POST['nome']) ? trim($_POST['nome']) : null; // admin name

// handle uploaded profile photo (optional) -- accept both 'foto' and 'foto_perfil'
$fotoData = null;
$uploadFields = ['foto', 'foto_perfil'];
foreach ($uploadFields as $fieldName) {
    if (!empty($_FILES[$fieldName]) && isset($_FILES[$fieldName]['error']) && $_FILES[$fieldName]['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES[$fieldName]['tmp_name'];
        // basic size check (max 5MB)
        if (is_file($tmp) && filesize($tmp) <= 5 * 1024 * 1024) {
            $fotoData = file_get_contents($tmp);
        }
        break;
    }
}

// server-side validation
if (empty($usuario) || empty($email) || empty($senha) || empty($senha_confirm)) {
    redirect_with_error('Por favor preencha todos os campos obrigatórios.');
}
if (strlen($senha) < 6) {
    redirect_with_error('A senha deve ter ao menos 6 caracteres.');
}
if ($senha !== $senha_confirm) {
    redirect_with_error('As senhas não coincidem.');
}

// tipo mapping
$map = [
    'admin' => 0,
    'visitante' => 1,
    'criador' => 2,
    'empresa' => 3,
];
if (!isset($map[$tipo])) {
    // default to visitante
    $nivel = 1;
} else {
    $nivel = $map[$tipo];
}

// check if usuarios table exists and columns — build an allowed columns list
$columns = [];
$res = $conn->query("SHOW COLUMNS FROM usuarios");
if ($res) {
    while ($col = $res->fetch_assoc()) {
        $columns[] = $col['Field'];
    }
} else {
    redirect_with_error('Erro ao ler esquema do banco.');
}

// uniqueness checks: usuario and email
if (in_array('usuario', $columns)) {
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE usuario = ? LIMIT 1");
    $stmt->bind_param('s', $usuario);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        redirect_with_error('Nome de conta já em uso. Escolha outro.');
    }
    $stmt->close();
}
if (in_array('email', $columns)) {
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        redirect_with_error('E-mail já cadastrado.');
    }
    $stmt->close();
}

// prepare fields to insert only if columns exist
$insertCols = [];
$placeholders = [];
$types = '';
$values = [];

// common fields
if (in_array('usuario', $columns)) { $insertCols[] = 'usuario'; $placeholders[] = '?'; $types .= 's'; $values[] = $usuario; }
if (in_array('email', $columns)) { $insertCols[] = 'email'; $placeholders[] = '?'; $types .= 's'; $values[] = $email; }
if (in_array('senha', $columns)) { $insertCols[] = 'senha'; $placeholders[] = '?'; $types .= 's'; $values[] = password_hash($senha, PASSWORD_DEFAULT); }
if (in_array('nivel', $columns)) { $insertCols[] = 'nivel'; $placeholders[] = '?'; $types .= 'i'; $values[] = $nivel; }

// optional / type-specific
if ($tipo === 'criador') {
    // prefer 'nome_real' for creators; if not present, skip (visitor/account name is stored in 'usuario')
    if (in_array('nome_real', $columns)) { $insertCols[] = 'nome_real'; $placeholders[] = '?'; $types .= 's'; $values[] = $nome_real ?? ''; }
    if ($telefone !== null && in_array('telefone', $columns)) { $insertCols[] = 'telefone'; $placeholders[] = '?'; $types .= 's'; $values[] = $telefone; }
}
if ($tipo === 'empresa') {
    if (in_array('nome_empresa', $columns)) { $insertCols[] = 'nome_empresa'; $placeholders[] = '?'; $types .= 's'; $values[] = $nome_empresa ?? ''; }
    elseif (in_array('nome_real', $columns)) { $insertCols[] = 'nome_real'; $placeholders[] = '?'; $types .= 's'; $values[] = $nome_empresa ?? ''; }
    if ($cnpj !== null && in_array('cnpj', $columns)) { $insertCols[] = 'cnpj'; $placeholders[] = '?'; $types .= 's'; $values[] = $cnpj; }
    if ($telefone !== null && in_array('telefone', $columns)) { $insertCols[] = 'telefone'; $placeholders[] = '?'; $types .= 's'; $values[] = $telefone; }
}
if ($tipo === 'visitante') {
    // visitors don't use nome_real; account name is in 'usuario' column already handled above
}
if ($tipo === 'admin') {
    // admin should use 'nome_real' if available
    if (in_array('nome_real', $columns)) { $insertCols[] = 'nome_real'; $placeholders[] = '?'; $types .= 's'; $values[] = $nome ?? $usuario; }
}

// If a photo was uploaded, map it to an available image column in usuarios
$possibleImgCols = ['foto', 'avatar','foto_perfil','imagem','img','profile_image'];
if ($fotoData !== null) {
    foreach ($possibleImgCols as $c) {
        if (in_array($c, $columns)) {
            $insertCols[] = $c;
            $placeholders[] = '?';
            $types .= 's';
            $values[] = $fotoData;
            break;
        }
    }
}

// final check
if (empty($insertCols)) {
    redirect_with_error('Nenhuma coluna disponível para inserir usuário.');
}

$sql = "INSERT INTO usuarios (" . implode(',', $insertCols) . ") VALUES (" . implode(',', $placeholders) . ")";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    redirect_with_error('Erro interno ao preparar cadastro.');
}

// bind params dynamically
$bind_names = [];
$bind_names[] = $types;
for ($i = 0; $i < count($values); $i++) {
    $bind_names[] = &$values[$i];
}
call_user_func_array(array($stmt, 'bind_param'), $bind_names);

$ok = $stmt->execute();
if (!$ok) {
    // possible DB error
    $stmt->close();
    redirect_with_error('Erro ao criar conta. Tente novamente.');
}

$stmt->close();

// sucesso — redirecionar para login com mensagem
$_SESSION['cadastroSucesso'] = 'Conta criada com sucesso. Faça login.';
header('Location: ../view/telaLogin.php');
exit;
