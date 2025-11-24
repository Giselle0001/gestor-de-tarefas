<?php include 'conexao.php';

$filtro = $_GET['filtro'] ?? 'todas';

$sql = "SELECT t.*, c.nome AS categoria 
        FROM tarefas t
        LEFT JOIN categorias c ON t.categoria_id = c.id";

if ($filtro === 'pendente') {
    $sql .= " WHERE t.status = 'pendente'";
}

$sql .= " ORDER BY t.id DESC";

$result = $conn->query($sql);

$tarefas = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tarefas[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Gestor de Tarefas</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        /* Ativando scroll suave via CSS (mais confiável para âncoras) */
        html { scroll-behavior: smooth; }

        :root{
            --turquoise:#63C7D1;
            --orange:#FF6F3C;
            --muted:#F6F8FA;
            --card-bg:#ffffff;
            --accent-dark:#4BA9B0;
            --pink-filter:#F8A1A4;
            --text:#252525;
            --glass: rgba(255,255,255,0.7);
        }

        *{box-sizing:border-box}
        
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f0f4fa;
            
            
            color:var(--text);
            -webkit-font-smoothing:antialiased;
            -moz-osx-font-smoothing:grayscale;
            overflow: hidden;
            
        }
        
        header{ padding: 22px; 
            font-size: 22px; 
            font-weight: 700; 
            border-radius: 0 0 14px 14px; 
            box-shadow: 0 3px 10px rgba(0,0,0,0.12); 
            background: linear-gradient(135deg,var(--turquoise)); 
            color: #fff; 
            display: flex; 
            align-items: center; 
            gap: 12px; 
        } 
        
        h1 { 
            margin: 0 auto; 
            font-size: 28px; 
            font-weight: 600; 
        }

        .container{
            max-width:1100px;
            margin:26px auto;
            padding:0 18px;
        }

        /* QUICK ACTIONS (visíveis e CENTRADAS logo após o header) */
        .quick-actions-wrap{
            display:flex;
            justify-content:center;
            margin:16px 0;
            gap:12px;
            position: sticky;
            top:14px;
            z-index:4;
            padding:8px;
        }

        .quick-actions {
            display:flex;
            gap:10px;
            background: rgba(255,255,255,0.92);
            padding:10px;
            border-radius:12px;
            box-shadow:0 6px 18px rgba(16,24,40,0.06);
            align-items:center;
        }

        .qa-button{
            display:inline-flex;
            align-items:center;
            gap:10px;
            padding:10px 16px;
            border-radius:10px;
            border:none;
            cursor:pointer;
            font-weight:600;
            font-size:14px;
            background:var(--card-bg);
            color:var(--text);
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            transition: transform .12s ease, box-shadow .12s ease, background .12s;
            text-decoration: none; /* para <a> ficar sem sublinhado */
        }

        .qa-button svg{width:18px;height:18px; flex-shrink:0; opacity:0.95}

        .qa-button:hover{ transform: translateY(-4px); box-shadow:0 8px 22px rgba(0,0,0,0.10) }
        .qa-primary{
            background: linear-gradient(135deg,var(--turquoise),var(--accent-dark));
            color:white;
        }
        .qa-secondary{
            background: #fffefc;
            border: 1px solid #f0f0f0;
        }
        .qa-ghost{
            background:transparent;
            border:1px dashed rgba(0,0,0,0.06);
        }

        .qa-button:focus{ outline:3px solid rgba(99,199,209,0.22); outline-offset:2px }

        /* CONTENT */
        .welcome-box{
            background:var(--card-bg);
            padding:22px;
            border-radius:12px;
            box-shadow:0 6px 18px rgba(16,24,40,0.04);
            text-align:center;
            margin-bottom:22px;
        }

        .welcome-box h2{ margin:0; font-size:20px; font-weight:700; color:#222 }
        .welcome-box p{ margin-top:8px; color:#505050; font-size:14px }

        /* STATS */
        .stats{
            display:flex;
            justify-content:center;
            gap:18px;
            margin-bottom:20px;
            flex-wrap:wrap;
        }

        .stat-card{
            background:var(--card-bg);
            width:200px;
            padding:16px;
            border-radius:12px;
            text-align:center;
            box-shadow:0 6px 20px rgba(16,24,40,0.04);
        }
        .stat-card h3{ margin:0; font-size:28px; color:var(--turquoise); font-weight:700 }
        .stat-card p{ margin-top:8px; color:#555; font-weight:600 }

        /* TIPS */
        .tips-box{
            background: linear-gradient(90deg,var(--orange),#E25A2F);
            color:white;
            padding:14px;
            border-radius:10px;
            text-align:center;
            margin:20px auto;
            max-width:720px;
            box-shadow:0 6px 18px rgba(0,0,0,0.08);
        }
        .tips-box p{ margin:0; font-weight:600
        }
        
        /* TASK GRID */
        .task-list{
            display:grid;
            grid-template-columns:repeat(auto-fill,minmax(300px,1fr));
            gap:22px;
            margin-bottom:80px;
        }

        /* Aqui definimos um scroll-margin-top suficiente para que o header não cubra o conteúdo
           Ajuste o valor se seu header tiver altura diferente. */
        #task-list { scroll-margin-top: 110px; }

        .task-item{
            background:var(--card-bg);
            padding:18px;
            border-radius:12px;
            box-shadow:0 6px 20px rgba(16,24,40,0.04);
            transition: transform .18s cubic-bezier(.2,.9,.3,1), box-shadow .18s;
        }
        .task-item:hover{ transform:translateY(-6px); box-shadow:0 12px 30px rgba(16,24,40,0.08) }

        .task-item.completed{ background:#EAF7EF; text-decoration:line-through; opacity:0.9 }

        .task-item h3{ margin:0 0 8px; font-size:18px }
        .task-item p{ margin:6px 0; color:#444; font-size:13.5px }

        .actions{ display:flex; gap:8px; justify-content:flex-end; margin-top:14px }
        .small-btn{
            border:none; background:var(--orange); color:white; padding:8px 12px; border-radius:8px; cursor:pointer; font-weight:600;
        }
        .small-btn:hover{ background:#e64f20 }

        /* FLOAT BUTTON */
        .add-task-button{
            position:fixed;
            bottom:22px;
            right:22px;
            width:56px;
            height:56px;
            border-radius:50%;
            background:var(--turquoise);
            color:white;
            border:none;
            font-size:26px;
            cursor:pointer;
            box-shadow:0 8px 24px rgba(16,24,40,0.14);
        }
        .add-task-button:hover{ background:var(--accent-dark) }

        @media (max-width:720px){
            .quick-actions{ flex-direction:column; gap:8px; padding:12px }
            .quick-actions-wrap{ padding:6px; top:8px }
            .stats{ gap:12px }
            .stat-card{ width:140px; padding:12px }
            .task-list{ grid-template-columns:repeat(1,1fr) }
            /* ajuste para mobile se header for menor */
            #task-list { scroll-margin-top: 80px; }
        }
    </style>
</head>
<body>

    <header>
        <h1>Gestor de Tarefas</h1>
    </header>

    <div class="container">
        <div class="quick-actions-wrap" aria-hidden="false">
            <div class="quick-actions" role="toolbar" aria-label="Ações rápidas">
                <!-- Criar -->
                <button class="qa-button qa-primary" onclick="location.href='create.php'">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M12 5v14M5 12h14" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Criar Tarefa
                </button>

                <!-- Ver Minhas agora é um link âncora confiável -->
                <a class="qa-button qa-secondary" href="tarefas.php">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M3 7h18M3 12h18M3 17h18" stroke="#3B3B3B" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Ver Minhas Tarefas
                </a>

                <!-- Importar -->
                <form action="importar.php" method="POST" enctype="multipart/form-data" style="display:inline;">
    <label id="btnImportar" class="qa-button qa-ghost" style="cursor:pointer;">
        <input type="file" name="arquivo" accept=".csv" style="display:none" onchange="this.form.submit()">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M12 3v12M8 7l4-4 4 4" stroke="#3B3B3B" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M5 21h14" stroke="#3B3B3B" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Importar
    </label>
</form>


                <form action="exportar.php" method="POST" style="display:inline;">
    <button type="submit" class="qa-button qa-ghost" style="cursor:pointer;">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M12 21V9M8 17l4 4 4-4" stroke="#3B3B3B" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M5 3h14" stroke="#3B3B3B" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Exportar
    </button>
</form>

            </div>
        </div>
    </div>

    <div class="container">
        <div class="welcome-box" role="region" aria-label="Boas vindas">
            <h2>Bem-vindo ao seu Gestor de Tarefas 👋</h2>
            <p>Organize suas atividades com eficiência: crie, priorize e conclua tarefas. Use os botões acima para ações rápidas.</p>
        </div>

        <div class="stats" aria-hidden="false">
            <!-- NOTE: Substitua os números estáticos com PHP se preferir -->
            <div class="stat-card">
                <h3><?php 
                    // exemplo: contar pendentes via PHP, caso tenha essa lógica
                    $pendentes = 0;
                    foreach($tarefas as $t){ if($t['status']=='pendente') $pendentes++; }
                    echo $pendentes;
                ?></h3>
                <p>Pendentes</p>
            </div>

            <div class="stat-card">
                <h3><?php
                    $concluidas = 0;
                    foreach($tarefas as $t){ if($t['status']=='concluída') $concluidas++; }
                    echo $concluidas;
                ?></h3>
                <p>Concluídas</p>
            </div>

            <div class="stat-card">
                <h3><?php
                    $atrasadas = 0;
                    $today = date('Y-m-d');
                    foreach($tarefas as $t){
                        if(!empty($t['data_vencimento']) && $t['status'] != 'concluída' && $t['data_vencimento'] < $today) $atrasadas++;
                    }
                    echo $atrasadas;
                ?></h3>
                <p>Atrasadas</p>
            </div>
        </div>

        <div class="tips-box" role="note">
            <p><strong>Dica:</strong> Clique em <em>Criar Tarefa</em> para adicionar rapidamente — ou use o botão “+” no canto para um atalho.</p>
        </div>
    </div>

    <!-- FLOAT ADD BUTTON (mantido) -->
    <button class="add-task-button" title="Criar nova tarefa" onclick="location.href='create.php'">+</button>

    <script>
        // Mantive a função filterTasks (útil para os botões de filtro)
        function filterTasks(status){
            const items = document.querySelectorAll('#task-list .task-item');
            items.forEach(it=>{
                if(status==='todas'){ it.style.display='block'; return; }
                if(it.dataset.status === status) it.style.display='block';
                else it.style.display='none';
            });
            // não forço scroll aqui: o usuário pode clicar em "Ver Minhas Tarefas" (âncora) quando quiser
        }
        
     
document.getElementById("btnImportar").addEventListener("click", function () {
    alert("Só são permitidos arquivos CSV com no máximo 2MB.");
});
</script>


</body>
</html>