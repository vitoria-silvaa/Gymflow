CREATE TABLE caixas_fisicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filial_id INT NOT NULL,
    aberto_por INT NOT NULL,
    aberto_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    saldo_inicial DECIMAL(10, 2) NOT NULL,
    fechado_por INT NULL,
    fechado_em DATETIME NULL,
    saldo_esperado DECIMAL(10, 2) NULL,
    saldo_contado DECIMAL(10, 2) NULL,
    diferenca DECIMAL(10, 2) NULL,
    conferido_por INT NULL,
    conferido_em DATETIME NULL,
    observacao_conferencia VARCHAR(255) NULL,
    filial_aberta INT GENERATED ALWAYS AS (
        CASE
            WHEN fechado_em IS NULL THEN filial_id
            ELSE NULL
        END
    ) STORED,
    CONSTRAINT uq_caixa_aberto_filial UNIQUE (filial_aberta),
    CONSTRAINT fk_caixa_filial FOREIGN KEY (filial_id) REFERENCES filiais(id) ON DELETE RESTRICT,
    CONSTRAINT fk_caixa_aberto_por FOREIGN KEY (aberto_por) REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT fk_caixa_fechado_por FOREIGN KEY (fechado_por) REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT fk_caixa_conferido_por FOREIGN KEY (conferido_por) REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT chk_caixa_saldo_inicial CHECK (saldo_inicial >= 0),
    CONSTRAINT chk_caixa_saldo_contado CHECK (saldo_contado >= 0),
    INDEX idx_caixa_filial_data (filial_id, aberto_em)
) ENGINE = InnoDB;
CREATE TABLE caixa_movimentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    caixa_id INT NOT NULL,
    conta_id INT NULL,
    usuario_id INT NOT NULL,
    tipo ENUM('recebimento', 'sangria') NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_caixa_movimento_conta UNIQUE (conta_id),
    CONSTRAINT fk_movimento_caixa FOREIGN KEY (caixa_id) REFERENCES caixas_fisicos(id) ON DELETE RESTRICT,
    CONSTRAINT fk_movimento_conta FOREIGN KEY (conta_id) REFERENCES contas(id) ON DELETE RESTRICT,
    CONSTRAINT fk_movimento_usuario FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT chk_movimento_valor CHECK (valor > 0),
    CONSTRAINT chk_movimento_tipo CHECK (
        (
            tipo = 'recebimento'
            AND conta_id IS NOT NULL
        )
        OR (
            tipo = 'sangria'
            AND conta_id IS NULL
        )
    ),
    INDEX idx_movimento_caixa_data (caixa_id, criado_em)
) ENGINE = InnoDB;