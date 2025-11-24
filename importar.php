<?php
include "conexao.php";

if (!empty($_FILES['arquivo']['tmp_name'])) {

    $arquivo = fopen($_FILES['arquivo']['tmp_name'], "r");

    // Ignora a primeira linha (cabeçalho)
    fgetcsv($arquivo);

    while (($linha = fgetcsv($arquivo, 1000, ",")) !== false) {

        $titulo = $linha[1];
        $categoria = $linha[2];
        $prioridade = $linha[3];
        $status = $linha[4];
        $vencimento = $linha[5];

        // Criar categoria se não existir
        $cat_id = null;
        if ($categoria != "") {
            $sqlCat = "SELECT id FROM categorias WHERE nome='$categoria' LIMIT 1";
            $res = $conn->query($sqlCat);

            if ($res->num_rows > 0) {
                $cat_id = $res->fetch_assoc()['id'];
            } else {
                $conn->query("INSERT INTO categorias (nome) VALUES ('$categoria')");
                $cat_id = $conn->insert_id;
            }
        }

        // Inserir tarefa
        $sql = "INSERT INTO tarefas (titulo, categoria_id, prioridade, status, data_vencimento)
                VALUES ('$titulo', '$cat_id', '$prioridade', '$status', '$vencimento')";
        $conn->query($sql);
    }

    fclose($arquivo);

    header("Location: tarefas.php?msg=importado");
    exit;
}
?>