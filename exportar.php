<?php
include "conexao.php";

// Impede qualquer lixo antes do download
if (ob_get_level()) {
    ob_end_clean();
}

header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=tarefas_exportadas.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Consulta ao banco
$sql = "SELECT t.*, c.nome AS categoria
        FROM tarefas t
        LEFT JOIN categorias c ON t.categoria_id = c.id
        ORDER BY t.id DESC";

$result = $conn->query($sql);

// Início do HTML que o Excel entende
echo '
<html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style>
    table {
        border-collapse: collapse;
        font-family: Arial;
        width: 100%;
    }
    th {
        background: #4EC5C1;
        color: #fff;
        padding: 10px;
        border: 1px solid #ccc;
        font-size: 14px;
    }
    td {
        padding: 8px;
        border: 1px solid #ccc;
        font-size: 13px;
    }
    tr:nth-child(even) {
        background: #f2f2f2;
    }
</style>

<table>
    <tr>
        <th>ID</th>
        <th>Título</th>
        <th>Categoria</th>
        <th>Prioridade</th>
        <th>Status</th>
        <th>Vencimento</th>
    </tr>
';

// Preenche a tabela
while ($row = $result->fetch_assoc()) {
    echo "
    <tr>
        <td>{$row['id']}</td>
        <td>{$row['titulo']}</td>
        <td>{$row['categoria']}</td>
        <td>{$row['prioridade']}</td>
        <td>{$row['status']}</td>
        <td>{$row['data_vencimento']}</td>
    </tr>";
}

echo "</table></html>";
exit;
?>