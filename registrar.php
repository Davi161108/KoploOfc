<?php
require_once 'config.php';

$dados = json_decode(file_get_contents('php://input'), true);

$nome  = trim($dados['nome'] ?? '');
$email = strtolower(trim($dados['email'] ?? ''));
$senha = $dados['senha'] ?? '';

if (!$nome || !$email || strlen($senha) < 4) {
    http_response_code(400);
    echo json_encode(["erro" => "Campos incompletos ou senha curta"]);
    exit;
}

// NUNCA guarde senha em texto puro — usa hash do PHP
$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $nome, $email, $senhaHash);

if ($stmt->execute()) {
    echo json_encode(["sucesso" => true, "usuario" => ["nome" => $nome, "email" => $email]]);
} else {
    http_response_code(409); // e-mail duplicado
    echo json_encode(["erro" => "E-mail já cadastrado"]);
}
?>