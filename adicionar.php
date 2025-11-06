<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
?>

<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valor = $_POST['valor'];
    $data = $_POST['data'];
    $tipo = $_POST['tipo'] ?? 'ganho';

    // Validar se é terça a sábado
    $dia_semana = date('w', strtotime($data));
    if ($dia_semana < 2 || $dia_semana > 6) {
        echo "Só é permitido registrar entre terça e sábado.";
        exit;
    }

    if ($tipo === 'ganho') {
        $stmt = $pdo->prepare("INSERT INTO ganhos (valor, data) VALUES (?, ?)");
    } else {
        $stmt = $pdo->prepare("INSERT INTO gastos (valor, data) VALUES (?, ?)");
    }

    $stmt->execute([$valor, $data]);
}

header("Location: index.php");
exit;
