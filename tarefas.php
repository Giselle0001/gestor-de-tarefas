<?php
// tarefas.php — arquivo completo pronto para usar (contadores corrigidos)

// ajuste de erros para debug (se precisar, ative)
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'conexao.php'; // deve definir $conn (mysqli)

// ------------------------------------------------------------------
// AÇÕES (DELETE, UPDATE, CONCLUIR)
// ------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'delete') {
        $id_delete = intval($_POST['id'] ?? 0);
        if ($id_delete > 0) {
            $stmt = $conn->prepare("DELETE FROM tarefas WHERE id = ?");
            $stmt->bind_param("i", $id_delete);
            $stmt->execute();
            $stmt->close();
        }
        header("Location: tarefas.php");
        exit;
    }

    if ($action === 'conclude') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $hoje = date('Y-m-d');
            $stmt = $conn->prepare("UPDATE tarefas SET status = 'concluída', data_conclusao = ? WHERE id = ?");
            $stmt->bind_param("si", $hoje, $id);
            $stmt->execute();
            $stmt->close();
        }
        header("Location: tarefas.php?concluded=1");
        exit;
    }

    if ($action === 'update') {
        // campos do modal de edição
        $id = intval($_POST['update_id'] ?? 0);
        $titulo = $conn->real_escape_string($_POST['update_titulo'] ?? '');
        $descricao = $conn->real_escape_string($_POST['update_descricao'] ?? '');
        $prioridade = $conn->real_escape_string($_POST['update_prioridade'] ?? '');
        $status = $conn->real_escape_string($_POST['update_status'] ?? '');
        $venc = $_POST['update_venc'] ?? null;
        if ($venc === '') $venc = null;

        if ($id > 0) {
            $sql = "UPDATE tarefas 
                    SET titulo = ?, descricao = ?, prioridade = ?, status = ?, data_vencimento = ?
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $titulo, $descricao, $prioridade, $status, $venc, $id);
            $stmt->execute();
            $stmt->close();
        }

        header("Location: tarefas.php");
        exit;
    }
}

// ------------------------------------------------------------------
// CONTADORES GLOBAIS (correção): calcular a partir do conjunto total
// ------------------------------------------------------------------
$today = date('Y-m-d');

$counts_sql = "
    SELECT
        SUM(CASE WHEN LOWER(status) = 'pendente' THEN 1 ELSE 0 END) AS pendentes,
        SUM(CASE WHEN LOWER(status) IN ('concluída','concluida') THEN 1 ELSE 0 END) AS concluidas,
        SUM(CASE WHEN (LOWER(status) NOT IN ('concluída','concluida')) AND data_vencimento IS NOT NULL AND data_vencimento < ? THEN 1 ELSE 0 END) AS atrasadas,
        SUM(CASE WHEN LOWER(prioridade) = 'alta' THEN 1 ELSE 0 END) AS alta_prioridade,
        COUNT(*) AS total
    FROM tarefas
";
$stmt_counts = $conn->prepare($counts_sql);
$stmt_counts->bind_param("s", $today);
$stmt_counts->execute();
$res_counts = $stmt_counts->get_result();
$pendentes_count = $concluidas_count = $atrasadas_count = $alta_prioridade_count = $total_count = 0;
if ($row = $res_counts->fetch_assoc()) {
    $pendentes_count = (int)$row['pendentes'];
    $concluidas_count = (int)$row['concluidas'];
    $atrasadas_count = (int)$row['atrasadas'];
    $alta_prioridade_count = (int)$row['alta_prioridade'];
    $total_count = (int)$row['total'];
}
$stmt_counts->close();

// ------------------------------------------------------------------
// BUSCAR TAREFAS COM FILTRO (apenas para exibição)
// ------------------------------------------------------------------
$filtro_raw = $_GET['filtro'] ?? 'todas';
$filtro = mb_strtolower(trim($filtro_raw), 'UTF-8');

$where = [];
$params = []; // não usamos parâmetros dinâmicos além de $today, mas deixo preparado

if ($filtro === 'pendente') {
    $where[] = "LOWER(t.status) = 'pendente'";
} elseif ($filtro === 'concluida' || $filtro === 'concluída') {
    $where[] = "(LOWER(t.status) = 'concluída' OR LOWER(t.status) = 'concluida')";
} elseif ($filtro === 'atrasada') {
    $where[] = "LOWER(t.status) NOT IN ('concluída','concluida') AND t.data_vencimento IS NOT NULL AND t.data_vencimento < '$today'";
} elseif ($filtro === 'alta') {
    $where[] = "LOWER(t.prioridade) = 'alta'";
}

$sql = "SELECT 
            t.id, t.titulo, t.descricao, t.prioridade, t.status, 
            t.data_vencimento, t.data_conclusao, t.categoria_id,
            COALESCE(c.nome, 'Sem categoria') AS categoria
        FROM tarefas t
        LEFT JOIN categorias c ON t.categoria_id = c.id";

if (!empty($where)) {
    $sql .= " WHERE " . implode(' AND ', $where);
}

$sql .= " ORDER BY t.id DESC";

$result = $conn->query($sql);

// montar array de tarefas para renderizar
$tarefas = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $tarefas[] = $row;
    }
}

$just_concluded = isset($_GET['concluded']) && $_GET['concluded'] == '1';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Minhas Tarefas</title>
    <style>
       :root {
    --primary: #5a8fd8;
    --danger:  #d9534f;
    --muted:   #f0f4fa;
}

* { box-sizing: border-box; } /* bom incluir globalmente */

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: var(--muted);
    color: #222;
}

header {
    padding: 22px;
    font-size: 22px;
    font-weight: 700;
    border-radius: 0 0 14px 14px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.12);
    background: linear-gradient(135deg,#2e4c6d,var(--primary));
    color: #fff;
    display: flex;
    align-items: center;
    gap: 12px;
}

.back-arrow {
    font-size: 28px;
    cursor: pointer;
    transition: 0.2s;
}
.back-arrow:hover { opacity: 0.8; }

h1 {
    margin: 0 auto;
    font-size: 28px;
    font-weight: 600;
}

.container {
    max-width: 1000px;
    margin: 30px auto;
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.12);
}

/* Top actions */
.top-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 22px;
    gap: 10px;
    flex-wrap: wrap;
}

.search input {
    padding: 10px 12px;
    width: 260px;
    border-radius: 6px;
    border: 1px solid #bbb;
    font-size: 14px;
}

.btn {
    padding: 10px 18px;
    background: var(--primary);
    color: #fff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 15px;
}
.btn:hover { filter: brightness(.95); }

/* Filters row */
.filters {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    align-items: center;
    flex-wrap: wrap;
}

/* Aplica-se a botões e links de filtro */
.filters button,
.filters a,
.filters .filter-button {
    padding: 8px 14px;
    border: none;
    border-radius: 6px;
    background: #e3e8f0;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
    color: inherit;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: background .15s ease, color .15s ease, transform .06s;
}

/* estado ativo (botões e links) */
.filters button.active,
.filter-button.active,
.filters .filter-button.active {
    background: var(--primary);
    color: #fff;
    transform: translateY(-2px);
}

/* badge (contador) */
.badge {
    background: var(--danger);
    color: #fff;
    padding: 3px 8px;
    border-radius: 999px;
    font-weight: 700;
    margin-left: 6px;
    font-size: 13px;
}

/* Task list */
.task-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.task-item {
    background: #f8faff;
    padding: 18px;
    border-radius: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-left: 6px solid var(--primary);
}

.task-item.completed {
    background: #EAF7EF;
    text-decoration: line-through;
    opacity: 0.9;
    border-left-color: #2f9b4a;
}

.task-info h3 {
    margin: 0;
    font-size: 18px;
    font-weight: bold;
}

.task-info p {
    margin: 4px 0 0 0;
    font-size: 14px;
    color: #555;
}

/* ações das tarefas */
.task-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

.task-actions button,
.task-actions input[type="submit"] {
    padding: 8px 12px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-size: 13px;
}

.edit-btn {
    background: var(--primary);
    color: #fff;
}
.edit-btn:hover { background: #4a7bc0; }

.delete-btn {
    background: var(--danger);
    color: #fff;
}
.delete-btn:hover { background: #c64543; }

.conclude-btn {
    background: green;
    color: #fff;
}
.conclude-btn:hover { filter: brightness(.9); }

/* modal edit */
.modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.45);
    justify-content: center;
    align-items: center;
    z-index: 9999;
}
.modal.show { display: flex; }

.modal-content {
    width: 420px;
    max-width: 95%;
    background: #fff;
    padding: 18px;
    border-radius: 10px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.2);
}

.modal-content h2 { margin-top: 0; font-size: 18px; text-align: center; }

.modal-content label { display: block; margin-top: 8px; font-weight: 600; font-size: 13px; }

/* Aqui garantimos box-sizing também nos inputs do modal (alinhamento) */
.modal-content input[type="text"],
.modal-content textarea,
.modal-content select,
.modal-content input[type="date"] {
    width: 100%;
    padding: 10px;
    margin-top: 6px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
    box-sizing: border-box;
}

.modal-actions {
    display: flex;
    justify-content: space-between;
    margin-top: 12px;
}

.modal-actions button {
    padding: 9px 14px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
}

.modal-actions .cancel { background: #bbb; color: #111; }
.modal-actions .save   { background: var(--primary); color: #fff; }

/* Responsivo */
@media (max-width: 720px) {
    .search input { width: 140px; }
    header { padding: 16px; }
    h1 { font-size: 20px; }
    .modal-content { width: 92%; }
}
    </style>
</head>
<body>

<header>
    <span class="back-arrow" onclick="window.location.href='index.php'">&#8592;</span>
    <h1>Minhas Tarefas</h1>
</header>

<div class="container">

    <div class="top-actions">
        <div class="search">
            <input id="taskSearch" type="text" placeholder="Buscar tarefa..." oninput="applySearch()">
        </div>
        <button class="btn" onclick="window.location.href='create.php'">+ Criar Tarefa</button>
    </div>

    <div class="filters">
        <!-- botões com badges (contagens sempre globais agora) -->
        <a id="linkTodas" class="filter-button" href="tarefas.php?filtro=todas">Todas
            <span class="badge"><?= $total_count ?></span>
        </a>

        <a id="linkPendentes" class="filter-button" href="tarefas.php?filtro=pendente">Pendentes
            <span class="badge"><?= $pendentes_count ?></span>
        </a>

        <a id="linkConcluidas" class="filter-button" href="tarefas.php?filtro=concluida">Concluídas
            <span class="badge"><?= $concluidas_count ?></span>
        </a>

        <a id="linkAtrasadas" class="filter-button" href="tarefas.php?filtro=atrasada">Atrasadas
            <span id="badgeAtrasadas" class="badge"><?= $atrasadas_count ?></span>
        </a>

        <a id="linkAlta" class="filter-button" href="tarefas.php?filtro=alta">Alta Prioridade
            <span class="badge"><?= $alta_prioridade_count ?></span>
        </a>
    </div>

    <div id="task-list" class="task-list" aria-live="polite">
        <?php if (!empty($tarefas)): ?>
            <?php foreach ($tarefas as $tarefa):
                $status_attr = htmlspecialchars($tarefa['status'], ENT_QUOTES, 'UTF-8');
                $venc = htmlspecialchars($tarefa['data_vencimento'], ENT_QUOTES, 'UTF-8');
                $completed_class = (mb_strtolower($tarefa['status'],'UTF-8') === 'concluída' || mb_strtolower($tarefa['status'],'UTF-8') === 'concluida') ? 'completed' : '';
                $json = htmlspecialchars(json_encode($tarefa, JSON_HEX_APOS|JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8');
            ?>
                <div class="task-item <?= $completed_class ?>" data-status="<?= $status_attr ?>" data-vencimento="<?= $venc ?>">
                    <div class="task-info">
                        <h3><?= htmlspecialchars($tarefa['titulo']) ?></h3>
                        <p>Categoria: <?= htmlspecialchars($tarefa['categoria']) ?> — Prioridade: <?= htmlspecialchars($tarefa['prioridade']) ?></p>

                        <?php if (!empty($tarefa['data_vencimento'])): ?>
                            <p style="margin-top:6px;color:#777;font-size:13px;">Vencimento: <?= htmlspecialchars($tarefa['data_vencimento']) ?></p>
                        <?php endif; ?>

                        <?php if (!empty($tarefa['data_conclusao'])): ?>
                            <p style="margin-top:6px;color:#2f9b4a;font-size:13px;">Concluída em: <?= htmlspecialchars($tarefa['data_conclusao']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="task-actions">
                        <?php if (!(mb_strtolower($tarefa['status'],'UTF-8') === 'concluída' || mb_strtolower($tarefa['status'],'UTF-8') === 'concluida')): ?>
                            <form method="POST" style="display:inline;margin:0;">
                                <input type="hidden" name="action" value="conclude">
                                <input type="hidden" name="id" value="<?= (int)$tarefa['id'] ?>">
                                <button type="submit" class="conclude-btn" onclick="return confirm('Marcar esta tarefa como concluída?')">Concluir</button>
                            </form>
                        <?php endif; ?>

                        <button class="edit-btn" data-task="<?= $json ?>" onclick="openEditModalFromBtn(this)">Editar</button>

                        <form method="POST" style="display:inline;margin:0;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int)$tarefa['id'] ?>">
                            <button type="submit" class="delete-btn" onclick="return confirm('Deseja realmente excluir esta tarefa?')">Excluir</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Nenhuma tarefa encontrada.</p>
        <?php endif; ?>
    </div>

</div>

<!-- MODAL DE EDIÇÃO -->
<div id="editModal" class="modal" aria-hidden="true">
    <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <h2 id="modalTitle">Editar Tarefa</h2>

        <form id="editForm" method="POST" onsubmit="return submitEditForm(event)">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="update_id" id="edit_id">

            <label for="edit_titulo">Título:</label>
            <input type="text" id="edit_titulo" name="update_titulo" required>

            <label for="edit_descricao">Descrição:</label>
            <textarea id="edit_descricao" name="update_descricao" rows="3"></textarea>

            <label for="edit_prioridade">Prioridade:</label>
            <select id="edit_prioridade" name="update_prioridade">
                <option value="Baixa">Baixa</option>
                <option value="Média">Média</option>
                <option value="Alta">Alta</option>
            </select>

            <label for="edit_status">Status:</label>
            <select id="edit_status" name="update_status">
                <option value="pendente">Pendente</option>
                <option value="concluída">Concluída</option>
            </select>

            <label for="edit_vencimento">Data de vencimento:</label>
            <input type="date" id="edit_vencimento" name="update_venc">

            <div class="modal-actions">
                <button type="button" class="cancel" onclick="closeModal()">Cancelar</button>
                <button type="submit" class="save">Salvar</button>
            </div>
        </form>
    </div>
</div>

<script>
/* ===== filtros / busca (mantive suas funções) ===== */
function filterShowAll() {
    document.querySelectorAll('#task-list .task-item').forEach(it => it.style.display = 'flex');
}
function applySearch() {
    const q = document.getElementById('taskSearch').value.trim().toLowerCase();
    document.querySelectorAll('#task-list .task-item').forEach(it => {
        const text = it.querySelector('.task-info').innerText.toLowerCase();
        if (!q || text.includes(q)) it.style.display = 'flex';
        else it.style.display = 'none';
    });
}

/* ===== Modal / Edit ===== */
function openEditModalFromBtn(btn){
    try {
        const json = btn.getAttribute('data-task');
        const task = JSON.parse(json);
        openEditModal(task);
    } catch(e){
        console.error('Erro ao abrir modal (parse):', e);
        alert('Não foi possível abrir a edição dessa tarefa.');
    }
}
function openEditModal(task){
    document.getElementById('edit_id').value = task.id || '';
    document.getElementById('edit_titulo').value = task.titulo || '';
    document.getElementById('edit_descricao').value = task.descricao || '';
    document.getElementById('edit_prioridade').value = task.prioridade || 'Baixa';
    document.getElementById('edit_status').value = task.status || 'pendente';
    document.getElementById('edit_vencimento').value = task.data_vencimento || '';

    document.getElementById('editModal').classList.add('show');
    document.getElementById('editModal').setAttribute('aria-hidden', 'false');
}
function closeModal(){
    document.getElementById('editModal').classList.remove('show');
    document.getElementById('editModal').setAttribute('aria-hidden', 'true');
}
function submitEditForm(e){
    return true;
}

/* aviso simples quando concluiu */
<?php if ($just_concluded): ?>
alert('Tarefa marcada como concluída!');
<?php endif; ?>

/* ===== manter botão ativo (visual) conforme filtro na URL ===== */
(function(){
    const params = new URLSearchParams(window.location.search);
    const filtro = (params.get('filtro') || 'todas').toLowerCase();

    const map = {
        'todas': 'linkTodas',
        'pendente': 'linkPendentes',
        'concluida': 'linkConcluidas',
        'concluída': 'linkConcluidas',
        'atrasada': 'linkAtrasadas',
        'alta': 'linkAlta'
    };

    const id = map[filtro];
    if (id) {
        document.querySelectorAll('.filter-button').forEach(el => el.classList.remove('active'));
        const el = document.getElementById(id);
        if (el) el.classList.add('active');
    }
})();
</script>

</body>
</html>
