<?php
// PERMITE QUE QUALQUER ORIGEM (app, web, Expo) fale com a API
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Responde automaticamente às requisições "preflight" do navegador
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
    
// DADOS QUE O INFINITYFREE TE DÁ EM: Control Panel > MySQL Databases
$host = 'sql105.infinityfree.com';   // ex.: sql123.infinityfree.com
$user = 'if0_42679161';               // seu usuário do MySQL
$pass = 'Aguiarrr07';        // a senha que você definiu ao criar o banco
$db   = 'if0_42679161_koplo';         // nome exato do banco

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["erro" => "Falha na conexão: " . $conn->connect_error]));
}

// Garante que tudo que o app mandar vira JSON
header('Content-Type: application/json; charset=utf-8');
?>