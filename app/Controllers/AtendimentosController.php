<?php

class AtendimentosController
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

        $sql = "
            SELECT
                a.*,
                p.nome AS pessoa,
                t.nome AS tipo_atendimento,
                u.nome AS atendente
            FROM atendimentos a
            INNER JOIN pessoas p
                ON p.id = a.pessoa_id
            INNER JOIN tipos_atendimentos t
                ON t.id = a.tipo_atendimento_id
            INNER JOIN usuarios u
                ON u.id = a.atendente_id
            ORDER BY a.id DESC
        ";

        $stmt = $this->pdo->query($sql);

        echo json_encode(
            $stmt->fetchAll(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
    }

    public function buscar(): void
    {
        $id = filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);

        $stmt = $this->pdo->prepare("
            SELECT *
            FROM atendimentos
            WHERE id = :id
        ");

        $stmt->execute([
            ':id' => $id
        ]);

        echo json_encode(
            $stmt->fetch(),
            JSON_PRETTY_PRINT
        );
    }

    public function criar(): void
    {
        $pessoa_id = filter_input(INPUT_POST,'pessoa_id',FILTER_VALIDATE_INT);
        $tipo_id = filter_input(INPUT_POST,'tipo_atendimento_id',FILTER_VALIDATE_INT);
        $atendente_id = filter_input(INPUT_POST,'atendente_id',FILTER_VALIDATE_INT);

        if (!$pessoa_id || !$tipo_id || !$atendente_id) {

            http_response_code(400);

            echo json_encode([
                'erro' => 'IDs obrigatórios'
            ]);

            return;
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO atendimentos
            (
                pessoa_id,
                tipo_atendimento_id,
                atendente_id,
                data_atendimento,
                hora_inicio,
                modalidade,
                observacoes,
                status
            )
            VALUES
            (
                :pessoa,
                :tipo,
                :atendente,
                :data,
                :hora,
                :modalidade,
                :obs,
                'agendado'
            )
        ");

        $stmt->execute([
            ':pessoa' => $pessoa_id,
            ':tipo' => $tipo_id,
            ':atendente' => $atendente_id,
            ':data' => $_POST['data_atendimento'],
            ':hora' => $_POST['hora_inicio'],
            ':modalidade' => $_POST['modalidade'] ?? 'presencial',
            ':obs' => $_POST['observacoes'] ?? ''
        ]);

        echo json_encode([
            'mensagem' => 'Atendimento criado',
            'id' => $this->pdo->lastInsertId()
        ]);
    }

    public function alterarStatus(): void
    {
        $id = filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);

        $status = $_POST['status'] ?? '';

        $permitidos = [
            'agendado',
            'em_andamento',
            'concluido',
            'cancelado',
            'nao_compareceu'
        ];

        if (!in_array($status,$permitidos,true)) {

            http_response_code(400);

            echo json_encode([
                'erro' => 'Status inválido'
            ]);

            return;
        }

        $stmt = $this->pdo->prepare("
            UPDATE atendimentos
            SET
                status = :status,
                resultado = :resultado,
                hora_fim = :hora_fim
            WHERE id = :id
        ");

        $stmt->execute([
            ':status' => $status,
            ':resultado' => $_POST['resultado'] ?? null,
            ':hora_fim' => $_POST['hora_fim'] ?? null,
            ':id' => $id
        ]);

        echo json_encode([
            'mensagem' => 'Status atualizado'
        ]);
    }
}