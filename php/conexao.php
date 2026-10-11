<?php
declare(strict_types=1);

$config = require __DIR__ . '/config.php';

function pdo(): PDO
{
    global $config;
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host={$config['host']};dbname={$config['banco']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['usuario'], $config['senha'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

// Elementos do teste (mesma ordem usada no JavaScript do index.html).
const ELEMENTOS = [
    'V' => 'Fisiológicas',
    'W' => 'Segurança',
    'X' => 'Sociais',
    'Y' => 'Auto-estima',
    'Z' => 'Realização',
];

// Elementos de cada pergunta: primeira letra = resposta A, segunda = resposta B.
const PARES = 'VZ WZ VY VX WY XZ YW XV YZ ZW YV ZV VW WX XW YZ WV XY YX ZY VX ZX VZ YW VY YX WZ XW ZX VW';
