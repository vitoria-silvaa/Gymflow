-- =====================================================================
-- Migração: Criação da tabela de pagamentos para o fluxo de matrícula
-- =====================================================================

USE gymcore_db;

CREATE TABLE IF NOT EXISTS pagamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matricula_id INT NOT NULL,
    mercado_pago_id VARCHAR(100) DEFAULT NULL,
    valor NUMERIC(10, 2) NOT NULL CHECK (valor >= 0),
    metodo VARCHAR(30) NOT NULL, -- 'pix', 'cartao'
    status VARCHAR(30) NOT NULL DEFAULT 'pendente', -- 'pendente', 'aprovado', 'recusado', 'cancelado'
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (matricula_id) REFERENCES matriculas(id) ON DELETE CASCADE
);

CREATE INDEX idx_pagamentos_matricula ON pagamentos(matricula_id);
CREATE INDEX idx_pagamentos_status ON pagamentos(status);
