<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/../conexao.php';

function h($s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// Evita que o Excel interprete um texto como fórmula (ex.: nome começando com "=").
function csvSeguro($s): string
{
    $s = (string)$s;
    return ($s !== '' && strpos('=+-@', $s[0]) !== false) ? "'" . $s : $s;
}

function principal(array $r): string
{
    $t = ['V' => (int)$r['total_v'], 'W' => (int)$r['total_w'], 'X' => (int)$r['total_x'],
          'Y' => (int)$r['total_y'], 'Z' => (int)$r['total_z']];
    $max = max($t);
    $nomes = [];
    foreach ($t as $letra => $pts) {
        if ($pts === $max) {
            $nomes[] = ELEMENTOS[$letra];
        }
    }
    return implode(' e ', $nomes);
}

// Cada elemento aparece em 12 perguntas, então o máximo possível por elemento é 36 pontos.
function pct($pontos): float
{
    return round(((float)$pontos) / 36 * 100, 1);
}

function fmtPct($pontos): string
{
    return number_format(pct($pontos), 1, ',', '.') . '%';
}

$css = <<<'CSS'
:root{--ink:#14213d;--muted:#566174;--line:#d6dce4;--brand:#0e6f78;--soft:#f1f4f6}
*{box-sizing:border-box}
body{margin:0;font:16px/1.55 system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;color:var(--ink);background:#fff}
header{background:var(--ink);color:#fff;padding:14px 20px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
header a{color:#fff}
main{max-width:1180px;margin:0 auto;padding:24px 20px 56px}
h1{font-size:1.4rem;margin:0}h2{font-size:1.15rem;margin:32px 0 10px}
.btn{display:inline-block;padding:9px 18px;border-radius:999px;background:var(--brand);color:#fff;border:0;text-decoration:none;font:inherit;font-weight:600;cursor:pointer}
.scroll{overflow-x:auto}
table{width:100%;border-collapse:collapse;font-size:.92rem}
th,td{padding:8px 10px;border-bottom:1px solid var(--line);text-align:left;white-space:nowrap}
th{background:var(--soft);color:var(--muted)}
td.n,th.n{text-align:right}
.bar{display:inline-block;width:90px;height:8px;background:var(--soft);border-radius:4px;vertical-align:middle;margin-left:8px}
.bar i{display:block;height:100%;background:var(--brand);border-radius:4px}
.login{max-width:340px;margin:12vh auto;padding:0 20px}
.login input{width:100%;padding:11px 12px;border:1.5px solid var(--line);border-radius:8px;font:inherit;margin:6px 0 14px}
.erro{color:#b3261e;margin:0 0 10px}
.vazio{color:var(--muted)}
a{color:var(--brand)}header a{color:#fff}
.chartbox{background:#fff;border:1px solid #cfd5dd;border-radius:6px;padding:8px}
.chartbox svg{display:block;width:100%;height:auto}
.meta{color:var(--muted);margin:4px 0 20px}.meta b{color:var(--ink)}
.dest{font-size:1.3rem;font-weight:700;color:var(--brand);margin:0 0 18px}
.sw{display:inline-block;width:11px;height:11px;border-radius:3px;margin-right:9px;-webkit-print-color-adjust:exact;print-color-adjust:exact}
.acoes{display:flex;gap:10px;flex-wrap:wrap;margin-top:20px}
@media print{header,.acoes{display:none!important}main{padding:0}}
CSS;

// ---- Sair ----
if (isset($_GET['sair'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: index.php');
    exit;
}

// ---- Login ----
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (hash_equals((string)$config['admin_senha'], (string)($_POST['senha'] ?? ''))) {
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        header('Location: index.php');
        exit;
    }
    $erro = 'Senha incorreta.';
}

if (empty($_SESSION['admin'])) {
    ?><!DOCTYPE html>
<html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Administração - Diagnóstico</title><style><?= $css ?></style></head>
<body><form class="login" method="post">
<h1>Área do administrador</h1>
<p class="vazio">Informe a senha para ver os resultados.</p>
<?php if ($erro !== ''): ?><p class="erro"><?= h($erro) ?></p><?php endif; ?>
<label for="senha">Senha</label>
<input type="password" id="senha" name="senha" autofocus required>
<button class="btn" type="submit">Entrar</button>
</form></body></html>
<?php
    exit;
}

// ---- Dados ----
$pdo = pdo();
$rows = $pdo->query('SELECT * FROM respondentes ORDER BY criado_em DESC, id DESC')->fetchAll();

// ---- Exportar CSV (abre direto no Excel) ----
if (isset($_GET['csv'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="diagnostico.csv"');
    echo "\xEF\xBB\xBF";
    $o = fopen('php://output', 'w');
    fputcsv($o, ['ID', 'Data', 'Nome', 'Setor', 'E-mail', 'Fisiológicas', 'Segurança', 'Sociais', 'Auto-estima', 'Realização', 'Principal motivação'], ';', '"', '\\');
    foreach ($rows as $r) {
        fputcsv($o, [
            $r['id'], $r['criado_em'], csvSeguro($r['nome']), csvSeguro($r['setor']), csvSeguro($r['email']),
            $r['total_v'], $r['total_w'], $r['total_x'], $r['total_y'], $r['total_z'], principal($r),
        ], ';', '"', '\\');
    }
    fclose($o);
    exit;
}

// ---- Detalhe de um respondente (com gráfico individual) ----
if (isset($_GET['id'])) {
    $st = $pdo->prepare('SELECT * FROM respondentes WHERE id = ?');
    $st->execute([(int)$_GET['id']]);
    $r = $st->fetch();
    if (!$r) {
        http_response_code(404);
        exit('Respondente não encontrado.');
    }
    $cores = ['#d38340', '#37789a', '#c96560', '#a36aae', '#1f7050'];
    $dadosGraf = [];
    $i = 0;
    foreach (ELEMENTOS as $letra => $nomeEl) {
        $pts = (int)$r['total_' . strtolower($letra)];
        $dadosGraf[] = ['n' => $nomeEl, 's' => $nomeEl, 'pts' => $pts, 'p' => pct($pts), 'cor' => $cores[$i++]];
    }
    $flags = JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
    ?><!DOCTYPE html>
<html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Diagnóstico - <?= h($r['nome']) ?></title><style><?= $css ?></style></head>
<body>
<header><h1>Diagnóstico Corporativo · Resultado individual</h1>
<span><a href="index.php">&larr; Voltar à lista</a> &nbsp; <a href="?sair=1">Sair</a></span></header>
<main>
<h1><?= h($r['nome']) ?></h1>
<p class="meta">
<?php if ($r['setor'] !== null): ?>Setor: <b><?= h($r['setor']) ?></b> &nbsp; <?php endif; ?>
<?php if ($r['email'] !== null): ?>E-mail: <b><?= h($r['email']) ?></b> &nbsp; <?php endif; ?>
Data: <b><?= h(date('d/m/Y H:i', strtotime((string)$r['criado_em']))) ?></b></p>
<p class="dest">Principal motivação: <?= h(principal($r)) ?></p>
<div class="scroll"><table>
<thead><tr><th>Elemento / Pilar</th><th class="n">Pontos</th><th class="n">Máximo</th><th class="n">Percentual</th></tr></thead><tbody>
<?php foreach ($dadosGraf as $d): ?>
<tr><td><span class="sw" style="background:<?= h($d['cor']) ?>"></span><?= h($d['n']) ?></td>
<td class="n"><?= (int)$d['pts'] ?></td><td class="n">36</td><td class="n"><b><?= number_format($d['p'], 1, ',', '.') ?>%</b></td></tr>
<?php endforeach; ?>
</tbody></table></div>
<h2>Gráfico</h2>
<div class="chartbox" id="grafico"></div>
<div class="acoes"><button class="btn" onclick="window.print()">Imprimir / Baixar PDF</button>
<a class="btn" href="index.php">Voltar à lista</a></div>
</main>
<script src="../../grafico.js"></script>
<script>
document.getElementById('grafico').innerHTML = graficoSVG(<?= json_encode($r['nome'], $flags) ?>, <?= json_encode($dadosGraf, $flags) ?>);
</script>
</body></html>
<?php
    exit;
}

$media = $pdo->query(
    'SELECT COUNT(*) AS n, AVG(total_v) AS v, AVG(total_w) AS w, AVG(total_x) AS x, AVG(total_y) AS y, AVG(total_z) AS z
     FROM respondentes'
)->fetch();
$setores = $pdo->query(
    "SELECT COALESCE(setor, '(sem setor)') AS setor, COUNT(*) AS n,
            AVG(total_v) AS v, AVG(total_w) AS w, AVG(total_x) AS x, AVG(total_y) AS y, AVG(total_z) AS z
     FROM respondentes GROUP BY COALESCE(setor, '(sem setor)') ORDER BY n DESC"
)->fetchAll();
?><!DOCTYPE html>
<html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Administração - Diagnóstico</title><style><?= $css ?></style></head>
<body>
<header><h1>Diagnóstico Corporativo · Resultados</h1>
<span><a class="btn" href="?csv=1">Exportar para Excel (CSV)</a> &nbsp; <a href="?sair=1">Sair</a></span></header>
<main>
<?php if ((int)$media['n'] === 0): ?>
<p class="vazio">Ainda não há respostas. Preencha o questionário em <a href="../../index.html">index.html</a> e volte aqui.</p>
<?php else: ?>
<h2>Média geral (<?= (int)$media['n'] ?> respondente<?= (int)$media['n'] === 1 ? '' : 's' ?>)</h2>
<div class="scroll"><table>
<thead><tr><th>Elemento</th><th class="n">Média de pontos</th><th class="n">Percentual</th><th></th></tr></thead><tbody>
<?php foreach (['V' => 'v', 'W' => 'w', 'X' => 'x', 'Y' => 'y', 'Z' => 'z'] as $letra => $col): ?>
<tr><td><?= h(ELEMENTOS[$letra]) ?></td>
<td class="n"><?= number_format((float)$media[$col], 1, ',', '.') ?></td>
<td class="n"><b><?= fmtPct($media[$col]) ?></b></td>
<td><span class="bar"><i style="width:<?= min(100, pct($media[$col])) ?>%"></i></span></td></tr>
<?php endforeach; ?>
</tbody></table></div>
<h2>Gráfico da média geral</h2>
<div class="chartbox" id="graficoMedia"></div>

<h2>Por setor (percentual)</h2>
<div class="scroll"><table>
<thead><tr><th>Setor</th><th class="n">Respondentes</th>
<?php foreach (ELEMENTOS as $nomeEl): ?><th class="n"><?= h($nomeEl) ?></th><?php endforeach; ?></tr></thead><tbody>
<?php foreach ($setores as $s): ?>
<tr><td><?= h($s['setor']) ?></td><td class="n"><?= (int)$s['n'] ?></td>
<?php foreach (['v', 'w', 'x', 'y', 'z'] as $col): ?><td class="n"><?= fmtPct($s[$col]) ?></td><?php endforeach; ?></tr>
<?php endforeach; ?>
</tbody></table></div>

<h2>Respondentes</h2>
<div class="scroll"><table>
<thead><tr><th class="n">#</th><th>Data</th><th>Nome</th><th>Setor</th><th>E-mail</th>
<?php foreach (ELEMENTOS as $nomeEl): ?><th class="n"><?= h($nomeEl) ?></th><?php endforeach; ?>
<th>Principal motivação</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $r): ?>
<tr><td class="n"><?= (int)$r['id'] ?></td>
<td><?= h(date('d/m/Y H:i', strtotime((string)$r['criado_em']))) ?></td>
<td><a href="?id=<?= (int)$r['id'] ?>"><?= h($r['nome']) ?></a></td><td><?= h($r['setor']) ?></td><td><?= h($r['email']) ?></td>
<td class="n"><?= (int)$r['total_v'] ?></td><td class="n"><?= (int)$r['total_w'] ?></td><td class="n"><?= (int)$r['total_x'] ?></td>
<td class="n"><?= (int)$r['total_y'] ?></td><td class="n"><?= (int)$r['total_z'] ?></td>
<td><b><?= h(principal($r)) ?></b></td>
<td><a href="?id=<?= (int)$r['id'] ?>">Ver gráfico</a></td></tr>
<?php endforeach; ?>
</tbody></table></div>
<?php
$dadosMedia = [];
foreach (['V' => 'v', 'W' => 'w', 'X' => 'x', 'Y' => 'y', 'Z' => 'z'] as $letra => $col) {
    $dadosMedia[] = ['n' => ELEMENTOS[$letra], 's' => ELEMENTOS[$letra], 'p' => pct($media[$col])];
}
$flags = JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
?>
<script src="../../grafico.js"></script>
<script>
document.getElementById('graficoMedia').innerHTML = graficoSVG(<?= json_encode('Média geral (' . (int)$media['n'] . ' resp.)', $flags) ?>, <?= json_encode($dadosMedia, $flags) ?>);
</script>
<?php endif; ?>
</main></body></html>
