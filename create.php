<?php include 'conexao.php'; ?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Tarefa</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
    --turquoise: #63C7D1; /* você pode mudar o tom aqui */
}

        body {
            margin: 0;
            background: #F6F8FA;
            font-family: Arial, sans-serif;
            color: #333;
        }
header{ 
            padding: 22px; 
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
        
        .back-arrow {
            font-size: 26px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.2s;
        }

        .back-arrow:hover {
            opacity: 0.8;
        }
        
        h1 { 
            margin: 0 auto; 
            font-size: 28px; 
            font-weight: 600; 
        }

        .container {
            max-width: 700px;
            margin: 35px auto;
            background: white;
            padding: 35px;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        h2 {
            margin-top: 0;
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            color: #2D2D2D;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-size: 15px;
            font-weight: 600;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 1.6px solid #ccc;
            border-radius: 10px;
            font-size: 15px;
            margin-bottom: 18px;
            transition: 0.2s;
            outline: none;
            box-sizing: border-box;
        }

        input:focus, select:focus, textarea:focus {
            border-color: #63C7D1;
            box-shadow: 0 0 4px rgba(99,199,209,0.4);
        }

        textarea {
            height: 110px;
            resize: none;
        }

        .buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }

        .btn-save {
            background: #FF6F3C;
            color: white;
            padding: 14px 24px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: 0.2s;
        }

        .btn-save:hover {
            background: #E95725;
        }

        .btn-back {
            background: #ccc;
            color: #333;
            padding: 14px 24px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: 0.2s;
        }

        .btn-back:hover {
            background: #b8b8b8;
        }

        @media(max-width: 600px) {
            .buttons {
                flex-direction: column;
                gap: 12px;
            }
        }
    </style>

</head>
<body>

<header>
    <span class="back-arrow" onclick="window.location.href='index.php'">&#8592;</span>
    <h1>Criar Nova Tarefa</h1>
</header>

<div class="container">
    <h2>Preencha as informações da tarefa</h2>

    <form action="salvar_tarefa.php" method="POST">

        <label for="titulo">Título da Tarefa</label>
        <input type="text" id="titulo" name="titulo" placeholder="Ex: Estudar para prova" required>

        <label for="descricao">Descrição</label>
        <textarea id="descricao" name="descricao" placeholder="Detalhes da tarefa..."></textarea>

        <label for="categoria">Categoria</label>
        <select id="categoria" name="categoria_id" required>
            <option value="" disabled selected>Selecione</option>
            <option value="1">Trabalho</option>
            <option value="2">Estudos</option>
            <option value="3">Pessoal</option>
            <option value="4">Casa</option>
        </select>

        <label for="prioridade">Prioridade</label>
        <select id="prioridade" name="prioridade" required>
            <option value="" disabled selected>Selecione</option>
            <option value="Baixa">Baixa</option>
            <option value="Média">Média</option>
            <option value="Alta">Alta</option>
        </select>

        <label for="data">Data de Vencimento</label>
        <input type="date" id="data" name="data_vencimento" required>

        <div class="buttons">
            <button type="button" class="btn-back" onclick="window.location.href='index.php'">Voltar</button>
            <button type="submit" class="btn-save">Salvar Tarefa</button>
        </div>

    </form>
</div>

</body>
</html>
