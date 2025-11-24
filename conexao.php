<?php
$host = "sql201.infinityfree.com";
$user = "if0_40240395";
$pass = "XK7pgX2WjwWq";
$db = "if0_40240395_gestor_tarefas";

$conn = new mysqli($host, $user, $pass, $db, 3306);

if ($conn->connect_error) {
    die("Erro ao conectar ao banco de dados: " . $conn->connect_error);
}

// Opcional: definir charset UTF-8
$conn->set_charset("utf8");
?>