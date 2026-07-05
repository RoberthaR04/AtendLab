<?php

class TiposAtendimentosController
{
    private PDO $pdo;

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';
        $this->pdo = $pdo;
    }

    public function listar(): void
    {
        header('Content-Type: application/json');

        $stmt = $this->pdo->query("
            SELECT *
            FROM tipos_atendimentos
            ORDER BY id DESC
        ");

        echo json_encode($stmt->fetchAll(), JSON_PRETTY_PRINT);
    }

    public function buscar(): void
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        $stmt = $this->pdo->prepare("
            SELECT *
            FROM tipos_atendimentos
            WHERE id = :id
        ");

        $stmt->execute([':id' => $id]);

        echo json_encode(
            $stmt->fetch(),
            JSON_PRETTY_PRINT
        );
    }

    public function criar(): void
    {
        $nome = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $duracao = filter_input(INPUT_POST,'duracao_min',FILTER_VALIDATE_INT);

        if ($nome === '') {

            http_response_code(400);

            echo json_encode([
                'erro' => 'Nome obrigatório'
            ]);

            return;
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO tipos_atendimentos
            (
                nome,
                descricao,
                duracao_min
            )
            VALUES
            (
                :nome,
                :descricao,
                :duracao
            )
        ");

        $stmt->execute([
            ':nome' => $nome,
            ':descricao' => $descricao,
            ':duracao' => $duracao ?: 30
        ]);

        echo json_encode([
            'mensagem' => 'Tipo criado com sucesso'
        ]);
    }

    public function atualizar(): void
    {
                $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (
            !$id ||
            empty($_POST['nome']) ||
            empty($_POST['descricao']) ||
            !isset($_POST['duracao_min'])
        ) {
            http_response_code(400);
            echo json_encode([
                "erro" => "ID, nome, descrição e duração são obrigatórios."
            ]);
            return;
        }

        $stmt = $this->pdo->prepare("
            UPDATE tipos_atendimentos
            SET
                nome = :nome,
                descricao = :descricao,
                duracao_min = :duracao
            WHERE id = :id
        ");

        $stmt->execute([
            ':nome' => $_POST['nome'],
            ':descricao' => $_POST['descricao'],
            ':duracao' => $_POST['duracao_min'],
            ':id' => $id
        ]);

        echo json_encode([
            'mensagem' => 'Tipo atualizado'
        ]);
    }

    public function inativar(): void
    {
        $id = filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);

        $stmt = $this->pdo->prepare("
            UPDATE tipos_atendimentos
            SET status = 'inativo'
            WHERE id = :id
        ");

        $stmt->execute([
            ':id' => $id
        ]);

        echo json_encode([
            'mensagem' => 'Tipo inativado'
        ]);
    }
}