<?php
include 'conexao.php';

// Captura os dados do formulário
$titulo = $_POST['titulo'];
$descricao = $_POST['descricao'];
$categoria_id = $_POST['categoria_id']; // <-- CORRETO
$prioridade = $_POST['prioridade'];
$data_vencimento = $_POST['data_vencimento'];

// Query CORRIGIDA — usando categoria_id (não categoria)
$sql = "INSERT INTO tarefas (titulo, descricao, categoria_id, prioridade, status, data_vencimento, criado_em)
        VALUES (?, ?, ?, ?, 'pendente', ?, NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssiss", $titulo, $descricao, $categoria_id, $prioridade, $data_vencimento);

if ($stmt->execute()) {
    header("Location: tarefas.php");
    exit();
} else {
    echo "Erro ao salvar tarefa: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>