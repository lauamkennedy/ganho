<?php
session_start();
require_once 'db.php';

// Se o usuário já estiver logado, redireciona
if (isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

$erro = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];

    // Exemplo simples de validação (pode ser substituído por consulta ao banco)
    if ($usuario === "admin" && $senha === "1234") {
        $_SESSION['usuario'] = $usuario;
        header("Location: index.php");
        exit();
    } else {
        $erro = "Usuário ou senha inválidos!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Login</h1>
    <form method="POST">
        <label>Usuário:</label>
        <input type="text" name="usuario" required>
        
        <label>Senha:</label>
        <input type="password" name="senha" required>
        
        <button type="submit">Entrar</button>
    </form>

    <?php if ($erro): ?>
        <p style="color:red; text-align:center;"><?= $erro ?></p>
    <?php endif; ?>
</body>
</html>
