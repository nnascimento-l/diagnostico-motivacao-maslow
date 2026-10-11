<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/conexao.php';

function responder(int $codigo, array $dados): void
{
    http_response_code($codigo);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

function invalido(string $msg): void
{
    responder(400, ['ok' => false, 'erro' => $msg]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(405, ['ok' => false, 'erro' => 'Método não permitido.']);
}

$corpo = file_get_contents('php://input');
if ($corpo === false || strlen($corpo) > 20000) {
    invalido('Dados inválidos.');
}
$d = json_decode($corpo, true);
if (!is_array($d)) {
    invalido('Dados inválidos.');
}

// 1) Identificação
$nome  = trim((string)($d['nome'] ?? ''));
$email = trim((string)($d['email'] ?? ''));
$setor = trim((string)($d['setor'] ?? ''));
if ($nome === '' || mb_strlen($nome) > 150) {
    invalido('Informe o nome completo.');
}
if (mb_strlen($setor) > 100) {
    invalido('Setor muito longo.');
}
if ($email !== '' && (mb_strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL))) {
    invalido('E-mail inválido.');
}

// 2) Respostas: o servidor confere tudo e recalcula os totais (não confia no navegador)
$pares = explode(' ', PARES);
$resp  = $d['respostas'] ?? null;
if (!is_array($resp) || count($resp) !== count($pares)) {
    invalido('Responda todas as perguntas.');
}

$totais = ['V' => 0, 'W' => 0, 'X' => 0, 'Y' => 0, 'Z' => 0];
$linhas = [];
foreach ($pares as $i => $par) {
    $r = $resp[$i] ?? null;
    if (!is_array($r) || count($r) !== 2 || !is_int($r[0] ?? null) || !is_int($r[1] ?? null)) {
        invalido('Resposta inválida na pergunta ' . ($i + 1) . '.');
    }
    $a = $r[0];
    $b = $r[1];
    if ($a < 0 || $a > 3 || $b < 0 || $b > 3 || $a + $b !== 3) {
        invalido('Na pergunta ' . ($i + 1) . ', a soma dos pontos precisa ser 3.');
    }
    $totais[$par[0]] += $a;
    $totais[$par[1]] += $b;
    $linhas[] = [$i + 1, $par[0], $a, $par[1], $b];
}

// 3) Gravação (tudo ou nada, dentro de uma transação)
try {
    $pdo = pdo();
    $pdo->beginTransaction();

    $st = $pdo->prepare(
        'INSERT INTO respondentes (nome, email, setor, total_v, total_w, total_x, total_y, total_z)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $st->execute([
        $nome,
        $email !== '' ? $email : null,
        $setor !== '' ? $setor : null,
        $totais['V'], $totais['W'], $totais['X'], $totais['Y'], $totais['Z'],
    ]);
    $id = (int)$pdo->lastInsertId();

    $st2 = $pdo->prepare(
        'INSERT INTO respostas (respondente_id, pergunta, elemento_a, pontos_a, elemento_b, pontos_b)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    foreach ($linhas as $l) {
        $st2->execute([$id, $l[0], $l[1], $l[2], $l[3], $l[4]]);
    }

    $pdo->commit();
    responder(200, ['ok' => true, 'id' => $id]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erro ao salvar diagnostico: ' . $e->getMessage());
    responder(500, ['ok' => false, 'erro' => 'Não foi possível salvar as respostas. Tente novamente.']);
}
