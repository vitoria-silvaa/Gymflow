-- =====================================================================
-- GymCore / GymFlow - Banco de Dados 100% MySQL
-- Estrutura Completa com Esquema e Dados Iniciais (Seed)
-- =====================================================================

-- 1. APAGA o banco antigo de testes para não dar conflito de tipos (TEXT vs INT)
DROP DATABASE IF EXISTS gymcore_db;

-- 2. Cria o banco de dados do zero, totalmente limpo
CREATE DATABASE gymcore_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 3. Ativa o banco de dados para ser usado
USE gymcore_db;

-- =====================================================================
-- PARTE 1: DEFINIÇÃO DE TABELAS (SCHEMA)
-- =====================================================================

-- ---------- 1. MULTI-TENANT & REDES ----------------------------------

CREATE TABLE companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------- 2. FILIAIS --------------------------------------------------

CREATE TABLE filiais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    cnpj VARCHAR(18) NOT NULL,
    telefone VARCHAR(15) NOT NULL,
    responsavel VARCHAR(100) NOT NULL,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    latitude DECIMAL(10,8) DEFAULT NULL,
    longitude DECIMAL(11,8) DEFAULT NULL,
    CONSTRAINT uq_filiais_cnpj UNIQUE (company_id, cnpj),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

-- ---------- 3. USUÁRIOS E PERMISSÕES ---------------------------------

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(30) NOT NULL,
    aluno_id INT DEFAULT NULL
);

CREATE TABLE user_filiais (
    user_id INT NOT NULL,
    filial_id INT NOT NULL,
    PRIMARY KEY (user_id, filial_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (filial_id) REFERENCES filiais(id) ON DELETE CASCADE
);

-- ---------- 4. PLANOS DA ACADEMIA ------------------------------------

CREATE TABLE planos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    valor NUMERIC(10, 2) NOT NULL CHECK (valor >= 0),
    duracao VARCHAR(20) NOT NULL,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

-- ---------- 5. ALUNOS ---------------------------------------------------

CREATE TABLE alunos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filial_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    rg VARCHAR(20),
    sexo VARCHAR(15) NOT NULL,
    nascimento DATE NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefone VARCHAR(15) NOT NULL,
    endereco TEXT,
    status VARCHAR(20) NOT NULL DEFAULT 'Ativo',
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (filial_id) REFERENCES filiais(id) ON DELETE RESTRICT
);

-- Vinculando a chave estrangeira de usuários/alunos de forma segura para o MySQL
ALTER TABLE users
ADD CONSTRAINT fk_users_aluno
FOREIGN KEY (aluno_id) REFERENCES alunos(id)
ON DELETE SET NULL;

-- ---------- 6. MATRÍCULAS ----------------------------------------------

CREATE TABLE matriculas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    plano_id INT NOT NULL,
    inicio DATE NOT NULL,
    fim DATE NOT NULL,
    valor NUMERIC(10, 2) NOT NULL CHECK (valor >= 0),
    desconto NUMERIC(10, 2) NOT NULL DEFAULT 0.00 CHECK (desconto >= 0),
    ativa BOOLEAN NOT NULL DEFAULT TRUE,
    CHECK (fim >= inicio),
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE,
    FOREIGN KEY (plano_id) REFERENCES planos(id) ON DELETE RESTRICT
);

-- ---------- 7. CONTAS A RECEBER (FINANCEIRO) -------------------------

CREATE TABLE contas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    matricula_id INT NOT NULL,
    vencimento DATE NOT NULL,
    valor NUMERIC(10, 2) NOT NULL CHECK (valor >= 0),
    status VARCHAR(20) NOT NULL DEFAULT 'Aberto',
    forma_pagamento VARCHAR(30),
    pago_em TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE,
    FOREIGN KEY (matricula_id) REFERENCES matriculas(id) ON DELETE CASCADE
);

-- ---------- 7.1 PAGAMENTOS ONLINE / MATRÍCULAS ------------------------

CREATE TABLE pagamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matricula_id INT NOT NULL,
    mercado_pago_id VARCHAR(100) DEFAULT NULL,
    valor NUMERIC(10, 2) NOT NULL CHECK (valor >= 0),
    metodo VARCHAR(30) NOT NULL, -- 'pix', 'cartao', 'dinheiro', 'boleto'
    status VARCHAR(30) NOT NULL DEFAULT 'pendente', -- 'pendente', 'aprovado', 'recusado', 'cancelado'
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (matricula_id) REFERENCES matriculas(id) ON DELETE CASCADE
);

-- ---------- 8. CRM / LEADS ---------------------------------------------

CREATE TABLE leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filial_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    telefone VARCHAR(15) NOT NULL,
    objetivo VARCHAR(100),
    campanha VARCHAR(100),
    status VARCHAR(30) NOT NULL DEFAULT 'Novo',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (filial_id) REFERENCES filiais(id) ON DELETE CASCADE
);

-- ---------- 9. FLUXO DE CAIXA (CUSTOS) ---------------------------------

CREATE TABLE custos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filial_id INT NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    valor NUMERIC(10, 2) NOT NULL CHECK (valor >= 0),
    data DATE NOT NULL,
    FOREIGN KEY (filial_id) REFERENCES filiais(id) ON DELETE CASCADE
);

-- ---------- 10. BIBLIOTECA DE EXERCÍCIOS & TREINOS --------------------

CREATE TABLE exercicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    grupo VARCHAR(30) NOT NULL,
    midia TEXT,
    tipo_midia VARCHAR(10) NOT NULL DEFAULT 'imagem'
);

CREATE TABLE fichas_treino (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    professor_id INT NOT NULL,
    objetivo VARCHAR(50) NOT NULL,
    criada_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    versao INT NOT NULL DEFAULT 1,
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE,
    FOREIGN KEY (professor_id) REFERENCES users(id) ON DELETE RESTRICT
);

CREATE TABLE ficha_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ficha_id INT NOT NULL,
    exercicio_id INT NOT NULL,
    ordem INT NOT NULL DEFAULT 0,
    series INT NOT NULL CHECK (series > 0),
    repeticoes VARCHAR(30) NOT NULL,
    carga VARCHAR(20),
    intervalo VARCHAR(20),
    FOREIGN KEY (ficha_id) REFERENCES fichas_treino(id) ON DELETE CASCADE,
    FOREIGN KEY (exercicio_id) REFERENCES exercicios(id) ON DELETE RESTRICT
);

-- ---------- 11. AVALIAÇÕES FÍSICAS ----------------------------------

CREATE TABLE avaliacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    data DATE NOT NULL,
    peso NUMERIC(5, 2) NOT NULL,
    altura NUMERIC(3, 2) NOT NULL,
    gordura NUMERIC(4, 2) NOT NULL,
    massa_magra NUMERIC(5, 2) NOT NULL,
    braco NUMERIC(4, 1),
    peitoral NUMERIC(4, 1),
    abdomen NUMERIC(4, 1),
    cintura NUMERIC(4, 1),
    quadril NUMERIC(4, 1),
    coxa NUMERIC(4, 1),
    panturrilha NUMERIC(4, 1),
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE
);

-- ---------- 12. PORTARIA, TRANCAMENTOS & ADICIONAIS -----------------

CREATE TABLE checkins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    data TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ficha_id INT DEFAULT NULL,
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE,
    FOREIGN KEY (ficha_id) REFERENCES fichas_treino(id) ON DELETE SET NULL
);

CREATE TABLE trancamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    inicio DATE NOT NULL,
    fim DATE NOT NULL,
    justificativa TEXT,
    taxa NUMERIC(10, 2) NOT NULL DEFAULT 0.00 CHECK (taxa >= 0),
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE
);

-- ---------- 13. PREFERÊNCIAS DO USUÁRIO ------------------------------

CREATE TABLE preferencias_usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    notificacoes_email BOOLEAN NOT NULL DEFAULT TRUE,
    notificacoes_push BOOLEAN NOT NULL DEFAULT TRUE,
    tema VARCHAR(20) NOT NULL DEFAULT 'light',
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_preferencias_user UNIQUE (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ---------- 14. SITE / PORTFÓLIO E CUSTOMIZAÇÃO -----------------------

CREATE TABLE portfolio_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    app_name VARCHAR(100) NOT NULL DEFAULT 'GymCore',
    theme_mode VARCHAR(10) NOT NULL DEFAULT 'light',
    primary_color VARCHAR(20) NOT NULL DEFAULT '#10b981',
    secondary_color VARCHAR(20) NOT NULL DEFAULT '#1f2937',
    logo_url TEXT,
    hero_title VARCHAR(150),
    hero_subtitle TEXT,
    hero_cta VARCHAR(50),
    about_text TEXT,
    about_image TEXT,
    company_values TEXT,
    company_competencies TEXT,
    contact_title VARCHAR(150),
    contact_subtitle TEXT,
    contact_form_title VARCHAR(150),
    contact_email VARCHAR(100),
    contact_phone VARCHAR(20),
    contact_hours VARCHAR(100),
    contact_image TEXT,
    instagram_url VARCHAR(255) DEFAULT '',
    facebook_url VARCHAR(255) DEFAULT '',
    tiktok_url VARCHAR(255) DEFAULT '',
    whatsapp_url VARCHAR(255) DEFAULT '',
    feedback_title VARCHAR(150),
    feedback_subtitle TEXT,
    feedback_image TEXT,
    accepts_wellhub BOOLEAN NOT NULL DEFAULT FALSE,
    wellhub_icon VARCHAR(50) DEFAULT NULL,
    accepts_totalpass BOOLEAN NOT NULL DEFAULT FALSE,
    totalpass_icon VARCHAR(50) DEFAULT NULL,
    CONSTRAINT uq_portfolio_company UNIQUE (company_id),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

CREATE TABLE portfolio_modalities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filial_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image_url TEXT,
    FOREIGN KEY (filial_id) REFERENCES filiais(id) ON DELETE CASCADE
);

CREATE TABLE portfolio_filial_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    filial_id INT NOT NULL,
    image_url TEXT NOT NULL,
    CONSTRAINT uq_portfolio_filial_image UNIQUE (filial_id),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (filial_id) REFERENCES filiais(id) ON DELETE CASCADE
);

CREATE TABLE portfolio_slides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    image_url TEXT NOT NULL,
    ordem INT NOT NULL DEFAULT 0,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    CONSTRAINT uq_portfolio_slides_company_ordem UNIQUE (company_id, ordem),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

CREATE TABLE portfolio_feedbacks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL DEFAULT 'Visitante',
    nota TINYINT NOT NULL,
    mensagem TEXT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    CHECK (nota BETWEEN 1 AND 5),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

-- ---------- ÍNDICES DE PERFORMANCE -----------------------------------

CREATE INDEX idx_alunos_filial_status ON alunos(filial_id, status);
CREATE INDEX idx_contas_vencimento ON contas(status, vencimento);
CREATE INDEX idx_pagamentos_matricula ON pagamentos(matricula_id);
CREATE INDEX idx_pagamentos_status ON pagamentos(status);
CREATE INDEX idx_ficha_itens_vinculo ON ficha_itens(ficha_id);
CREATE INDEX idx_custos_data ON custos(data);
CREATE INDEX idx_leads_status ON leads(status);


-- =====================================================================
-- PARTE 2: CARGA DE DADOS INICIAIS (SEED / MOCK DATA)
-- =====================================================================

-- 1. COMPANIES
INSERT INTO companies (id, nome) VALUES 
(1, 'GymFlow Corporation'), 
(2, 'FitLife Group');

-- 2. FILIAIS
INSERT INTO filiais (id, company_id, nome, cnpj, telefone, responsavel, ativo, latitude, longitude) VALUES
(1, 1, 'GymFlow Central', '12.345.678/0001-90', '(11) 98765-4321', 'Jorge Silva', TRUE, -23.55052000, -46.63330800),
(2, 1, 'GymFlow Zona Sul', '12.345.678/0002-70', '(11) 98765-4322', 'Mariana Costa', TRUE, -23.65000000, -46.65000000),
(3, 1, 'GymFlow Zona Leste', '12.345.678/0003-50', '(11) 98765-4323', 'Maria Fernanda', TRUE, -23.54000000, -46.47000000);

-- 3. ALUNOS
INSERT INTO alunos (id, filial_id, nome, cpf, rg, sexo, nascimento, email, telefone, endereco, status) VALUES
(1, 1, 'Ana Oliveira', '111.222.333-44', '12.345.678-9', 'Feminino', '1995-03-15', 'ana.oliveira@email.com', '(11) 91111-1111', 'Rua das Flores, 123 - São Paulo', 'Ativo'),
(2, 1, 'Bruno Souza', '222.333.444-55', '98.765.432-1', 'Masculino', '1988-07-20', 'bruno.souza@email.com', '(11) 92222-2222', 'Av. Paulista, 456 - São Paulo', 'Ativo'),
(3, 1, 'Camila Lima', '333.444.555-66', '45.678.901-2', 'Feminino', '2000-11-05', 'camila.lima@email.com', '(11) 93333-3333', 'Rua Augusta, 789 - São Paulo', 'Inativo'),
(4, 3, 'Diego Rocha', '444.555.666-77', '34.567.890-3', 'Masculino', '1992-05-10', 'diego.rocha@email.com', '(11) 94444-4444', 'Rua Ipiranga, 101 - São Paulo', 'Ativo'),
(5, 2, 'Eduardo Moreira', '555.666.777-88', '23.456.789-4', 'Masculino', '1997-09-12', 'eduardo.m@email.com', '(11) 95555-4444', 'Av. Faria Lima, 2000 - São Paulo', 'Ativo'),
(6, 1, 'Fernanda Alencar', '666.777.888-99', '56.789.012-5', 'Feminino', '1999-04-28', 'fernanda.a@email.com', '(11) 96666-3333', 'Rua Oscar Freire, 350 - São Paulo', 'Ativo');

-- 4. USERS
-- Senha padrão para todos: 'admin' | Hash bcrypt: $2y$10$nmdtL/W7IDi8gbKjf3sYOO1CWKPfsuMZYtNWGaBJ3hFMCTS00NW5.
INSERT INTO users (id, name, email, password, role, aluno_id) VALUES
(1, 'Administrador Principal', 'admin@gymflow.com', '$2y$10$nmdtL/W7IDi8gbKjf3sYOO1CWKPfsuMZYtNWGaBJ3hFMCTS00NW5.', 'Admin', NULL),
(2, 'Professor Marcelo', 'marcelo.treino@gymflow.com', '$2y$10$nmdtL/W7IDi8gbKjf3sYOO1CWKPfsuMZYtNWGaBJ3hFMCTS00NW5.', 'Professor', NULL),
(3, 'Professora Juliana', 'juliana.fit@gymflow.com', '$2y$10$nmdtL/W7IDi8gbKjf3sYOO1CWKPfsuMZYtNWGaBJ3hFMCTS00NW5.', 'Professor', NULL),
(4, 'Ana Oliveira', 'ana.oliveira@email.com', '$2y$10$nmdtL/W7IDi8gbKjf3sYOO1CWKPfsuMZYtNWGaBJ3hFMCTS00NW5.', 'Aluno', 1),
(5, 'Bruno Souza', 'bruno.souza@email.com', '$2y$10$nmdtL/W7IDi8gbKjf3sYOO1CWKPfsuMZYtNWGaBJ3hFMCTS00NW5.', 'Aluno', 2),
(6, 'Recepção Central', 'recepcao@gymflow.com', '$2y$10$nmdtL/W7IDi8gbKjf3sYOO1CWKPfsuMZYtNWGaBJ3hFMCTS00NW5.', 'Recepcao', NULL);

-- 5. USER_FILIAIS
INSERT INTO user_filiais (user_id, filial_id) VALUES
(1, 1),
(1, 2),
(1, 3),
(2, 1),
(3, 3),
(6, 1);

-- 6. PLANOS
INSERT INTO planos (id, company_id, nome, categoria, valor, duracao) VALUES
(1, 1, 'Plano Mensal Gold', 'Musculação', 99.90, '1 Mês'),
(2, 1, 'Plano Semestral Platinum', 'Musculação + Aulas', 499.00, '6 Meses'),
(3, 1, 'Plano Anual Black', 'Livre Acesso', 899.00, '1 Ano'),
(4, 2, 'Plano Standard', 'Básico', 79.90, '1 Mês');

-- 7. MATRÍCULAS
INSERT INTO matriculas (id, aluno_id, plano_id, inicio, fim, valor, desconto, ativa) VALUES
(1, 1, 1, '2026-07-01', '2026-08-01', 99.90, 0.00, TRUE),
(2, 2, 2, '2026-01-15', '2026-07-15', 499.00, 50.00, FALSE),
(3, 4, 3, '2026-06-01', '2027-06-01', 899.00, 0.00, TRUE),
(4, 5, 1, '2026-08-10', '2026-09-10', 99.90, 0.00, TRUE),
(5, 6, 2, '2026-09-01', '2027-03-01', 499.00, 0.00, TRUE);

-- 7.1 PAGAMENTOS
INSERT INTO pagamentos (id, matricula_id, mercado_pago_id, valor, metodo, status) VALUES
(1, 1, 'MP-1002931', 99.90, 'cartao', 'aprovado'),
(2, 2, 'MP-1002932', 449.00, 'pix', 'aprovado'),
(3, 3, 'MP-1002933', 899.00, 'cartao', 'aprovado'),
(4, 4, 'MP-1002934', 99.90, 'pix', 'pendente');

-- 8. CONTAS A RECEBER (MÓDULO FINANCEIRO - JORGE)
INSERT INTO contas (id, aluno_id, matricula_id, vencimento, valor, status, forma_pagamento, pago_em) VALUES
(1, 1, 1, '2026-07-01', 99.90, 'Pago', 'Cartão de Crédito', '2026-07-01 10:00:00'),
(2, 2, 2, '2026-01-15', 449.00, 'Pago', 'Dinheiro', '2026-01-15 14:30:00'),
(3, 4, 3, '2026-06-01', 899.00, 'Pago', 'Cartão de Crédito', '2026-06-01 09:15:00'),
(4, 5, 4, '2026-09-10', 99.90, 'Aberto', NULL, NULL),
(5, 6, 5, '2026-10-01', 499.00, 'Aberto', NULL, NULL),
(6, 1, 1, '2026-10-05', 99.90, 'Aberto', NULL, NULL);

-- 9. LEADS (MÓDULO CRM - JORGE) - Distribuídos em todas as etapas do Kanban
INSERT INTO leads (id, filial_id, nome, telefone, objetivo, campanha, status, criado_em) VALUES
(1, 1, 'Carlos Eduardo', '(11) 95555-5555', 'Hipertrofia Muscular', 'Instagram Ads', 'Novo', '2026-09-20 08:30:00'),
(2, 1, 'Fernanda Mello', '(11) 96666-6666', 'Emagrecimento', 'Indicação Amigo', 'Contato Agendado', '2026-09-19 11:00:00'),
(3, 2, 'Lucas Alcantara', '(11) 97777-1111', 'Condicionamento Físico', 'Google Search', 'Experimental', '2026-09-18 16:20:00'),
(4, 2, 'Gabriela Santos', '(11) 97777-7777', 'Saúde e Bem-estar', 'Fachada da Unidade', 'Convertido', '2026-09-17 15:45:00'),
(5, 1, 'Roberto Antunes', '(11) 98888-2222', 'Ganho de Força', 'Outdoor', 'Perdido', '2026-09-15 10:10:00'),
(6, 3, 'Juliana Paes', '(11) 99999-3333', 'Definição Abdominal', 'TikTok Ads', 'Novo', '2026-09-21 14:00:00');

-- 10. CUSTOS (MÓDULO FLUXO DE CAIXA - JORGE)
INSERT INTO custos (id, filial_id, descricao, categoria, valor, data) VALUES
(1, 1, 'Aluguel do imóvel da Unidade Central', 'Infraestrutura', 3500.00, '2026-09-05'),
(2, 1, 'Manutenção preventiva das esteiras', 'Equipamentos', 450.00, '2026-09-10'),
(3, 3, 'Energia Elétrica (Enel)', 'Contas de Consumo', 850.00, '2026-09-08'),
(4, 1, 'Internet Fibra Óptica 1Gbps', 'Contas de Consumo', 250.00, '2026-09-12'),
(5, 1, 'Folha de Pagamento - Equipe de Limpeza', 'Pessoal', 1800.00, '2026-09-05'),
(6, 2, 'Anúncios Patrocinados no Instagram', 'Marketing', 400.00, '2026-09-15');

-- 11. EXERCÍCIOS
INSERT INTO exercicios (id, nome, grupo, midia, tipo_midia) VALUES
(1, 'Supino Reto com Barra', 'Peito', 'https://example.com/supino.gif', 'imagem'),
(2, 'Agachamento Livre', 'Quadríceps', 'https://example.com/agachamento.gif', 'imagem'),
(3, 'Puxada Aberta no Pulley', 'Costas', 'https://example.com/puxada.gif', 'imagem'),
(4, 'Rosca Direta com Barra W', 'Bíceps', 'https://example.com/rosca.gif', 'imagem'),
(5, 'Tríceps Corda no Cross', 'Tríceps', 'https://example.com/triceps.gif', 'imagem'),
(6, 'Elevação Lateral com Halteres', 'Ombros', 'https://example.com/elevacao.gif', 'imagem'),
(7, 'Prancha Abdominal', 'Abdômen', 'https://example.com/prancha.gif', 'imagem');

-- 12. FICHAS_TREINO
INSERT INTO fichas_treino (id, aluno_id, professor_id, objetivo, criada_em, versao) VALUES
(1, 1, 2, 'Hipertrofia e Tonificação', '2026-07-02 10:00:00', 1),
(2, 2, 2, 'Resistência Muscular e Força', '2026-01-20 09:00:00', 1);

-- 13. FICHA_ITENS
INSERT INTO ficha_itens (id, ficha_id, exercicio_id, ordem, series, repeticoes, carga, intervalo) VALUES
(1, 1, 1, 1, 4, '10 a 12', '20kg cada lado', '60s'),
(2, 1, 4, 2, 3, '12', '10kg', '45s'),
(3, 1, 5, 3, 3, '15', '25kg', '45s'),
(4, 2, 2, 1, 4, '15', 'Sem peso adicional', '30s'),
(5, 2, 3, 2, 4, '10', '40kg', '60s');

-- 14. AVALIAÇÕES FÍSICAS
INSERT INTO avaliacoes (id, aluno_id, data, peso, altura, gordura, massa_magra, braco, peitoral, abdomen, cintura, quadril, coxa, panturrilha) VALUES
(1, 1, '2026-07-02', 65.50, 1.68, 22.40, 50.80, 28.5, 90.0, 78.0, 72.0, 95.0, 52.0, 34.0),
(2, 2, '2026-01-16', 82.00, 1.80, 18.50, 66.80, 35.0, 102.0, 88.0, 84.0, 100.0, 58.0, 38.0);

-- 15. CHECK-INS
INSERT INTO checkins (id, aluno_id, data, ficha_id) VALUES
(1, 1, '2026-09-20 07:15:00', 1),
(2, 1, '2026-09-21 07:20:00', 1),
(3, 2, '2026-09-20 18:30:00', 2),
(4, 4, '2026-09-21 19:00:00', NULL);

-- 16. TRANCAMENTOS
INSERT INTO trancamentos (id, aluno_id, inicio, fim, justificativa, taxa) VALUES
(1, 3, '2026-07-01', '2026-08-01', 'Viagem a trabalho', 30.00);

-- 17. PREFERÊNCIAS DO USUÁRIO
INSERT INTO preferencias_usuario (user_id, notificacoes_email, notificacoes_push, tema) VALUES
(1, TRUE, TRUE, 'light'),
(4, TRUE, FALSE, 'dark');

-- 18. PORTFOLIO_CONFIG
INSERT INTO portfolio_config (
    company_id,
    app_name,
    theme_mode,
    primary_color,
    secondary_color,
    logo_url,
    hero_title,
    hero_subtitle,
    hero_cta,
    about_text,
    about_image,
    company_values,
    company_competencies,
    contact_title,
    contact_subtitle,
    contact_form_title,
    contact_email,
    contact_phone,
    contact_hours,
    contact_image,
    instagram_url,
    facebook_url,
    tiktok_url,
    whatsapp_url,
    feedback_title,
    feedback_subtitle,
    feedback_image,
    accepts_wellhub,
    wellhub_icon,
    accepts_totalpass,
    totalpass_icon
) VALUES (
    1,
    'GymFlow Ecosystem',
    'dark',
    '#10b981',
    '#111827',
    'https://example.com/logo.png',
    'Transforme seu corpo e sua mente na GymFlow',
    'O melhor ecossistema inteligente de academias para gerenciar seus treinos, evolução e metas.',
    'Matricule-se Já',
    'Focados em entregar alta performance, infraestrutura moderna, conforto e tecnologia de ponta.',
    'https://example.com/about.jpg',
    'Foco, Disciplina, Excelência, Saúde e Resultado',
    'Musculação Avançada, Acompanhamento com Personal, Fichas Digitais',
    'Fale com a Nossa Equipe',
    'Estamos prontos para atender você e tirar todas as suas dúvidas.',
    'Envie uma Mensagem',
    'contato@gymflow.com',
    '(11) 98765-4321',
    'Segunda a sexta: 06h às 23h | Sábado: 08h às 18h | Domingo: 08h às 14h',
    NULL,
    'https://instagram.com/gymflow',
    'https://facebook.com/gymflow',
    'https://tiktok.com/@gymflow',
    'https://wa.me/5511987654321',
    'Como foi sua experiência na GymFlow?',
    'Envie sua avaliação e nos ajude a aprimorar nosso espaço e atendimento.',
    NULL,
    TRUE,
    'wellhub',
    TRUE,
    'totalpass'
);

-- 19. PORTFOLIO_MODALITIES
INSERT INTO portfolio_modalities (id, filial_id, name, description, image_url) VALUES
(1, 1, 'CrossFit & Funcional', 'Treinamento funcional de alta intensidade para resistência e força.', 'https://example.com/crossfit.jpg'),
(2, 1, 'Pilates & Flexibilidade', 'Aulas focadas em postura, alinhamento corporal e fortalecimento do core.', 'https://example.com/pilates.jpg'),
(3, 2, 'Muay Thai & Lutas', 'Arte marcial dinâmica com alto gasto calórico e condicionamento.', 'https://example.com/muaythai.jpg'),
(4, 1, 'Spinning Indoor', 'Aulas aeróbicas intensas com ritmo musical e alta energia.', 'https://example.com/spinning.jpg');

-- 20. PORTFOLIO_FEEDBACKS
INSERT INTO portfolio_feedbacks (company_id, nome, nota, mensagem, criado_em, ativo) VALUES
(1, 'Ana Paula Silva', 5, 'Excelente estrutura! Aparelhos novinhos e professores muito atenciosos.', '2026-09-10 10:00:00', TRUE),
(2, 'Ricardo Silveira', 4, 'Ambiente limpo, organizado e climatizado. Muito satisfeito!', '2026-09-12 14:30:00', TRUE),
(3, 'Mariana Costa', 5, 'As aulas coletivas são incríveis e a ficha de treino no app facilita tudo.', '2026-09-15 09:15:00', TRUE);
