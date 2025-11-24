<?php
include('conexao.php');

if ($conn) {
    echo "✅ Conectado com sucesso!";
} else {
    echo "❌ Falha na conexão.";
}
?>
