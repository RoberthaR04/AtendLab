<?php

class PessoasController
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
            FROM pessoas
            ORDER BY id DESC
        ");

        echo json_encode(
            $stmt->fetchAll(PDO::FETCH_ASSOC),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
    }

    public function buscar(): void
    {
        header('Content-Type: application/json');

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido']);
            return;
        }

        $stmt = $this->pdo->prepare("
            SELECT *
            FROM pessoas
            WHERE id = :id
        ");

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $pessoa = $stmt->fetch();

        if (!$pessoa) {
            http_response_code(404);
            echo json_encode(['erro' => 'Pessoa não encontrada']);
            return;
        }

        echo json_encode(
            $pessoa,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
    }

    public function criar(): void
    {
        header('Content-Type: application/json');

        $nome = trim($_POST['nome'] ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (
            $nome === '' ||
            $documento === '' ||
            $email === ''
        ) {
            http_response_code(400);
            echo json_encode([
                'erro' => 'Nome, documento e email são obrigatórios.'
            ]);
            return;
        }

        $sql = "
            INSERT INTO pessoas
            (
                nome,
                documento,
                telefone,
                email,
                status
            )
            VALUES
            (
                :nome,
                :documento,
                :telefone,
                :email,
                'ativo'
            )
        ";

        try {

            $stmt = $this->pdo->prepare($sql);

            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':documento', $documento);
            $stmt->bindValue(':telefone', $telefone);
            $stmt->bindValue(':email', $email);

            $stmt->execute();

            echo json_encode([
                'mensagem' => 'Pessoa cadastrada com sucesso.',
                'id' => $this->pdo->lastInsertId()
            ]);

        } catch(PDOException $e){

            http_response_code(500);

            echo json_encode([
                'erro' => $e->getMessage()
            ]);
        }
    }

    public function atualizar(): void
    {
        header('Content-Type: application/json');

        $id = filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);

        $nome = trim($_POST['nome'] ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $email = trim($_POST['email'] ?? '');

        $stmt = $this->pdo->prepare("
            UPDATE pessoas
            SET
                nome = :nome,
                documento = :documento,
                telefone = :telefone,
                email = :email
            WHERE id = :id
        ");

        $stmt->execute([
            ':nome' => $nome,
            ':documento' => $documento,
            ':telefone' => $telefone,
            ':email' => $email,
            ':id' => $id
        ]);

        echo json_encode([
            'mensagem' => 'Pessoa atualizada com sucesso.'
        ]);
    }

    public function inativar(): void
    {
        header('Content-Type: application/json');

        $id = filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);

        $stmt = $this->pdo->prepare("
            UPDATE pessoas
            SET status = 'inativo'
            WHERE id = :id
        ");

        $stmt->bindValue(':id',$id,PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode([
            'mensagem' => 'Pessoa inativada com sucesso.'
        ]);
    }
}