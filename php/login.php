<?php
require_once 'config.php';

$dados = json_decode(file_get_contents('php://input'), true);
$email = strtolower(trim($dados['email'] ?? ''));
$senha = $dados['senha'] ?? '';

$stmt = $conn->prepare("SELECT id, nome, email, senha FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

if (!$usuario || !password_verify($senha, $usuario['senha'])) {
    http_response_code(401);
    echo json_encode(["erro" => "E-mail ou senha incorretos"]);
    exit;
}

// NUNCA devolver a senha para o app
echo json_encode([
    "sucesso" => true,
    "usuario" => ["id" => $usuario['id'], "nome" => $usuario['nome'], "email" => $usuario['email']]
]);
?>