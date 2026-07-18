-- 1. APAGA o banco antigo de testes para não dar conflito de tipos (TEXT vs INT)
DROP DATABASE IF EXISTS gymcore_db;

-- 2. Cria o banco de dados do zero, totalmente limpo
CREATE DATABASE gymcore_db;

-- 3. Ativa o banco de dados para ser usado
USE gymcore_db;

-- =====================================================================
-- GymCore - Banco de Dados 100% MySQL (Otimizado para XAMPP e PHP)
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
    valor NUMERIC(10,2) NOT NULL CHECK (valor >= 0),
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
    FOREIGN KEY (filial_id) REFERENCES filiais(id) ON DELETE RESTRICT
);

-- Vinculando a chave estrangeira de usuários/alunos de forma segura para o MySQL
ALTER TABLE users ADD CONSTRAINT fk_users_aluno FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE SET NULL;

-- ---------- 6. MATRÍCULAS ----------------------------------------------
CREATE TABLE matriculas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    plano_id INT NOT NULL,
    inicio DATE NOT NULL,
    fim DATE NOT NULL,
    valor NUMERIC(10,2) NOT NULL CHECK (valor >= 0),
    desconto NUMERIC(10,2) NOT NULL DEFAULT 0.00 CHECK (desconto >= 0),
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
    valor NUMERIC(10,2) NOT NULL CHECK (valor >= 0),
    status VARCHAR(20) NOT NULL DEFAULT 'Aberto', 
    forma_pagamento VARCHAR(30),                  
    pago_em TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE,
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
    valor NUMERIC(10,2) NOT NULL CHECK (valor >= 0),
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
    peso NUMERIC(5,2) NOT NULL,
    altura NUMERIC(3,2) NOT NULL,
    gordura NUMERIC(4,2) NOT NULL,
    massa_magra NUMERIC(5,2) NOT NULL,
    braco NUMERIC(4,1),
    peitoral NUMERIC(4,1),
    abdomen NUMERIC(4,1),
    cintura NUMERIC(4,1),
    quadril NUMERIC(4,1),
    coxa NUMERIC(4,1),
    panturrilha NUMERIC(4,1),
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
    taxa NUMERIC(10,2) NOT NULL DEFAULT 0.00,
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE
);

-- ---------- 13. PORTFÓLIO & BRANDING UNIFICADO (WHITE LABEL) --------
CREATE TABLE portfolio_config (
    company_id INT PRIMARY KEY,
    app_name VARCHAR(50) NOT NULL DEFAULT 'GymCore',
    theme_mode VARCHAR(10) NOT NULL DEFAULT 'light',
    primary_color VARCHAR(20) NOT NULL DEFAULT '#00ff00',
    secondary_color VARCHAR(20) NOT NULL DEFAULT '#000000',
    logo_url TEXT,
    hero_title VARCHAR(150) NOT NULL,
    hero_subtitle TEXT NOT NULL,
    hero_cta VARCHAR(50) NOT NULL,
    about_text TEXT NOT NULL,
    about_image TEXT,
    company_values TEXT,
    company_competencies TEXT,
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

-- ---------- ÍNDICES DE PERFORMANCE ---------
CREATE INDEX idx_alunos_filial_status ON alunos(filial_id, status);
CREATE INDEX idx_contas_vencimento ON contas(status, vencimento);
CREATE INDEX idx_ficha_itens_vinculo ON ficha_itens(ficha_id);