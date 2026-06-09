CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    ativo BOOLEAN DEFAULT TRUE
);

CREATE TABLE pessoas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(14),
    telefone VARCHAR(20),
    email VARCHAR(100),
    ativo BOOLEAN DEFAULT TRUE
);

CREATE TABLE tipos_atendimento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    ativo BOOLEAN DEFAULT TRUE
);

CREATE TABLE atendimentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pessoa_id INT NOT NULL,
    tipo_atendimento_id INT NOT NULL,
    usuario_id INT NOT NULL,
    descricao TEXT NOT NULL,
    status ENUM('aberto', 'em_andamento', 'concluido') DEFAULT 'aberto',
    observacao_final TEXT,
    data_atendimento DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (pessoa_id) REFERENCES pessoas(id),
    FOREIGN KEY (tipo_atendimento_id) REFERENCES tipos_atendimento(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);