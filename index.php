<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
?>
<a href="logout.php" style="float:right; margin:10px; color:red; text-decoration:none; font-weight:bold;">Sair</a>

<?php
include 'db.php';

// Função para formatar intervalo da semana (terça a sábado)
function semanaToPeriodo($ano, $semana_num) {
    $dto = new DateTime();
    $dto->setISODate($ano, $semana_num, 2); // segunda-feira da semana
    $dto->modify('+1 day'); // terça
    $inicio = $dto->format('d/m/Y');
    $dto->modify('+4 days'); // sábado
    $fim = $dto->format('d/m/Y');
    return "$inicio - $fim";
}

// Inserção via POST (ganho ou gasto)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['valor'], $_POST['data'], $_POST['tipo'])) {
    $valor = floatval($_POST['valor']);
    $data = $_POST['data'];
    $tipo = $_POST['tipo'];

    if ($tipo === 'ganho') {
        $stmt = $pdo->prepare("INSERT INTO ganhos (valor, data) VALUES (?, ?)");
        $stmt->execute([$valor, $data]);
    } elseif ($tipo === 'gasto') {
        $stmt = $pdo->prepare("INSERT INTO gastos (valor, data) VALUES (?, ?)");
        $stmt->execute([$valor, $data]);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <title>Controle de Ganhos e Gastos Semanais</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        body { font-family: Arial, sans-serif; background: #f9f9f9; padding: 20px; background-image: url('bg.jpg');}
        form { margin-bottom: 20px; background: white; padding: 15px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);}
        label { margin-right: 10px; }
        input[type=number], input[type=date] { margin-right: 10px; padding: 5px; }
        button { padding: 5px 12px; cursor: pointer; }
        .gasto-btn { background-color: #dc3545; color: white; border: none; }
        .container { max-width: 700px; margin: auto; }
        h1 { text-align: center; margin-bottom: 30px; color: white; }

        /* Estilos do accordion */
        .accordion {
            max-width: 700px;
            margin: 0 auto 40px auto;
        }
        .accordion-item {
            background: white;
            margin-bottom: 10px;
            border-radius: 8px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .accordion-header {
            cursor: pointer;
            padding: 15px 20px;
            font-weight: bold;
            background-color: #007bff;
            color: white;
            user-select: none;
        }
        .accordion-header:hover {
            background-color: #0056b3;
        }
        .accordion-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            padding: 0 20px;
            background: #f9f9f9;
        }
        .accordion-content.open {
            padding: 15px 20px;
            max-height: 500px; /* valor alto para abrir */
        }
        .saldo-positivo {
            color: green;
            font-weight: bold;
        }
        .saldo-negativo {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Controle de Ganhos e Gastos Semanais (Terça a Sábado)</h1>

    <!-- Formulário Ganho -->
    <form method="post">
        <label for="valor_ganho">Valor Ganho (R$):</label>
        <input type="number" step="0.01" name="valor" id="valor_ganho" required>
        <label for="data_ganho">Data:</label>
        <input type="date" name="data" id="data_ganho" required>
        <input type="hidden" name="tipo" value="ganho">
        <button type="submit">Adicionar Ganho</button>
    </form>

    <!-- Formulário Gasto -->
    <form method="post">
        <label for="valor_gasto">Valor Gasto (R$):</label>
        <input type="number" step="0.01" name="valor" id="valor_gasto" required>
        <label for="data_gasto">Data:</label>
        <input type="date" name="data" id="data_gasto" required>
        <input type="hidden" name="tipo" value="gasto">
        <button type="submit" class="gasto-btn">Adicionar Gasto</button>
    </form>

<?php
// Consultas agrupadas por semana e ano, dias entre terça (3) e sábado (7) no MySQL DAYOFWEEK
$query_ganhos = "
    SELECT
        YEAR(data) as ano,
        WEEK(data, 3) as semana,
        SUM(valor) as total_ganhos
    FROM ganhos
    WHERE DAYOFWEEK(data) BETWEEN 3 AND 7
    GROUP BY ano, semana
    ORDER BY ano DESC, semana DESC
";

$query_gastos = "
    SELECT
        YEAR(data) as ano,
        WEEK(data, 3) as semana,
        SUM(valor) as total_gastos
    FROM gastos
    WHERE DAYOFWEEK(data) BETWEEN 3 AND 7
    GROUP BY ano, semana
    ORDER BY ano DESC, semana DESC
";

$ganhos_semana = [];
foreach ($pdo->query($query_ganhos) as $row) {
    $ganhos_semana[$row['ano']][$row['semana']] = $row['total_ganhos'];
}

$gastos_semana = [];
foreach ($pdo->query($query_gastos) as $row) {
    $gastos_semana[$row['ano']][$row['semana']] = $row['total_gastos'];
}

// Pegar todas as semanas (união de ganhos e gastos)
$semanas = [];
foreach ($ganhos_semana as $ano => $semanas_ano) {
    foreach ($semanas_ano as $semana_num => $valor) {
        $semanas[$ano][$semana_num] = true;
    }
}
foreach ($gastos_semana as $ano => $semanas_ano) {
    foreach ($semanas_ano as $semana_num => $valor) {
        $semanas[$ano][$semana_num] = true;
    }
}
?>

    <div class="accordion">
        <?php
        if (empty($semanas)) {
            echo "<p>Nenhum dado de ganhos ou gastos para as semanas.</p>";
        } else {
            krsort($semanas);
            foreach ($semanas as $ano => $semana_nums) {
                krsort($semana_nums);
                foreach ($semana_nums as $semana_num => $_) {
                    $total_ganhos = $ganhos_semana[$ano][$semana_num] ?? 0;
                    $total_gastos = $gastos_semana[$ano][$semana_num] ?? 0;
                    $lucro_liquido = $total_ganhos - $total_gastos;
                    $ajudante = $lucro_liquido * 0.30;
                    $saldo_final = $lucro_liquido - $ajudante;

                    $periodo = semanaToPeriodo($ano, $semana_num);
        ?>
                    <div class="accordion-item">
                        <div class="accordion-header">Semana <?= $semana_num ?> (<?= $periodo ?>)</div>
                        <div class="accordion-content">
                            <p><strong>Ganhos:</strong> R$ <?= number_format($total_ganhos, 2, ',', '.') ?></p>
                            <p><strong>Gastos:</strong> R$ <?= number_format($total_gastos, 2, ',', '.') ?></p>
                            <p><strong>Ajudante (30% do lucro líquido):</strong> R$ <?= number_format($ajudante, 2, ',', '.') ?></p>
                            <h3 class="<?= $saldo_final >= 0 ? 'saldo-positivo' : 'saldo-negativo' ?>">
                                Saldo Final: R$ <?= number_format($saldo_final, 2, ',', '.') ?>
                            </h3>
                        </div>
                    </div>
        <?php
                }
            }
        }
        ?>
    </div>

</div>

<script>
    // JS para abrir/fechar o conteúdo do accordion ao clicar
    document.querySelectorAll('.accordion-header').forEach(header => {
        header.addEventListener('click', () => {
            const content = header.nextElementSibling;
            content.classList.toggle('open');
        });
    });
</script>

</body>
</html>
