ALTER TABLE matriculas
ADD COLUMN origem VARCHAR(20) NULL,
    ADD COLUMN status_matricula VARCHAR(20) NULL,
    ADD COLUMN criado_em DATETIME NULL,
    ADD COLUMN filial_id INT NULL,
    ADD COLUMN criado_por INT NULL,
    ADD COLUMN lead_id INT NULL,
    ADD CONSTRAINT fk_matriculas_filial FOREIGN KEY (filial_id) REFERENCES filiais(id) ON DELETE RESTRICT,
    ADD CONSTRAINT fk_matriculas_usuario FOREIGN KEY (criado_por) REFERENCES users(id) ON DELETE
SET NULL,
    ADD CONSTRAINT fk_matriculas_lead FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE
SET NULL,
    ADD CONSTRAINT uq_matriculas_lead UNIQUE (lead_id);
ALTER TABLE pagamentos
ADD COLUMN conta_id INT NULL,
    ADD COLUMN recebido_por INT NULL,
    ADD COLUMN confirmado_em DATETIME NULL,
    ADD COLUMN referencia VARCHAR(100) NULL,
    ADD CONSTRAINT fk_pagamentos_conta FOREIGN KEY (conta_id) REFERENCES contas(id) ON DELETE
SET NULL,
    ADD CONSTRAINT fk_pagamentos_usuario FOREIGN KEY (recebido_por) REFERENCES users(id) ON DELETE
SET NULL,
    ADD CONSTRAINT uq_pagamentos_conta UNIQUE (conta_id);