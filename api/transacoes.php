<?php
require_once 'config.php';

// Lê o método HTTP (GET = listar, POST = criar, PUT = editar, DELETE = apagar)
$metodo = $_SERVER['REQUEST_METHOD'];

// Lê o corpo da requisição (o que o app manda)
$dados = json_decode(file_get_contents('php://input'), true);
if (!is_array($dados)) {
    $dados = [];
}

// O app sempre manda o usuario_id junto (cada usuário vê só as próprias transações)
$usuarioId = $dados['usuario_id'] ?? ($_GET['usuario_id'] ?? null);

if (!$usuarioId || !is_numeric($usuarioId)) {
    http_response_code(400);
    echo json_encode(["erro" => "usuario_id é obrigatório"]);
    exit;
}

switch ($metodo) {

    case 'GET':
        // Lista as transações do usuário (mais novas primeiro)
        $stmt = $conn->prepare("SELECT id, descricao, valor, categoria, criado_em FROM transacoes WHERE usuario_id = ? ORDER BY criado_em DESC");
        $stmt->bind_param("i", $usuarioId);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $transacoes = [];
        while ($linha = $resultado->fetch_assoc()) {
            $transacoes[] = [
                'id'          => (int)$linha['id'],
                'description' => $linha['descricao'],
                'amount'      => (float)$linha['valor'],
                'category'    => $linha['categoria'],
                'date'        => $linha['criado_em']
            ];
        }
        echo json_encode(["transacoes" => $transacoes]);
        break;

    case 'POST':
        // Cria uma transação nova
        $descricao = trim($dados['description'] ?? '');
        $valor     = $dados['amount'] ?? 0;
        $categoria = $dados['category'] ?? 'Outros';

        if (!$descricao || !is_numeric($valor)) {
            http_response_code(400);
            echo json_encode(["erro" => "Descrição e valor são obrigatórios"]);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO transacoes (usuario_id, descricao, valor, categoria) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isds", $usuarioId, $descricao, $valor, $categoria);
        $stmt->execute();

        echo json_encode(["sucesso" => true, "id" => $stmt->insert_id]);
        break;

    case 'PUT':
        // Edita uma transação existente (só se for do próprio usuário)
        $id        = $dados['id'] ?? null;
        $descricao = trim($dados['description'] ?? '');
        $valor     = $dados['amount'] ?? 0;
        $categoria = $dados['category'] ?? 'Outros';

        if (!$id || !$descricao || !is_numeric($valor)) {
            http_response_code(400);
            echo json_encode(["erro" => "Dados incompletos para editar"]);
            exit;
        }

        $stmt = $conn->prepare("UPDATE transacoes SET descricao = ?, valor = ?, categoria = ? WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param("sdsii", $descricao, $valor, $categoria, $id, $usuarioId);
        $stmt->execute();

        echo json_encode(["sucesso" => true]);
        break;

    case 'DELETE':
        // Apaga uma transação (só se for do próprio usuário)
        $id = $dados['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(["erro" => "id é obrigatório"]);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM transacoes WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param("ii", $id, $usuarioId);
        $stmt->execute();

        echo json_encode(["sucesso" => true]);
        break;

    default:
        http_response_code(405);
        echo json_encode(["erro" => "Método não permitido"]);
}
?>