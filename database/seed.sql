-- =====================================================================
-- GymCore - Massa de Dados de Teste (Mock / Seed)
-- =====================================================================

-- Garante que estamos usando o banco correto               
USE gymcore_db;

-- Limpa os dados em ordem reversa de dependência das FKs para evitar conflitos ao re-executar
DELETE FROM portfolio_modalities;
DELETE FROM portfolio_config;
DELETE FROM trancamentos;
DELETE FROM checkins;
DELETE FROM avaliacoes;
DELETE FROM ficha_itens;
DELETE FROM fichas_treino;
DELETE FROM exercicios;
DELETE FROM custos;
DELETE FROM leads;
DELETE FROM contas;
DELETE FROM matriculas;
DELETE FROM planos;
DELETE FROM user_filiais;
DELETE FROM users;
DELETE FROM alunos;
DELETE FROM filiais;
DELETE FROM companies;

-- 1. COMPANIES
INSERT INTO companies (id, nome) VALUES 
(1, 'GymFlow Corporation'), 
(2, 'FitLife Group');

-- 2. FILIAIS
INSERT INTO filiais (id, company_id, nome, cnpj, telefone, responsavel, ativo) VALUES
(1, 1, 'GymFlow Central', '12.345.678/0001-90', '(11) 98765-4321', 'Jorge Silva', TRUE),
(2, 1, 'GymFlow Zona Sul', '12.345.678/0002-70', '(11) 98765-4322', 'Mariana Costa', TRUE),
(3, 2, 'FitLife Centro', '98.765.432/0001-10', '(21) 99999-8888', 'Carlos Santos', TRUE);

-- 3. ALUNOS
INSERT INTO alunos (id, filial_id, nome, cpf, rg, sexo, nascimento, email, telefone, endereco, status) VALUES
(1, 1, 'Ana Oliveira', '111.222.333-44', '12.345.678-9', 'Feminino', '1995-03-15', 'ana.oliveira@email.com', '(11) 91111-1111', 'Rua A, 123 - São Paulo', 'Ativo'),
(2, 1, 'Bruno Souza', '222.333.444-55', '98.765.432-1', 'Masculino', '1988-07-20', 'bruno.souza@email.com', '(11) 92222-2222', 'Av. B, 456 - São Paulo', 'Ativo'),
(3, 1, 'Camila Lima', '333.444.555-66', '45.678.901-2', 'Feminino', '2000-11-05', 'camila.lima@email.com', '(11) 93333-3333', 'Rua C, 789 - São Paulo', 'Inativo'),
(4, 3, 'Diego Rocha', '444.555.666-77', '34.567.890-3', 'Masculino', '1992-05-10', 'diego.rocha@email.com', '(21) 94444-4444', 'Rua D, 101 - Rio de Janeiro', 'Ativo');

-- 4. USERS
-- A senha padrão para todos é 'admin' criptografada em bcrypt ($2y$10$tM3N7C9vEa0Hq4r4Kz4Hbe.t.XhBq4W5l0Wp6p9v6tK2eK2eK2eK2)
INSERT INTO users (id, name, email, password, role, aluno_id) VALUES
(1, 'Administrador Principal', 'admin@gymflow.com', '$2y$10$tM3N7C9vEa0Hq4r4Kz4Hbe.t.XhBq4W5l0Wp6p9v6tK2eK2eK2eK2', 'Admin', NULL),
(2, 'Professor Marcelo', 'marcelo.treino@gymflow.com', '$2y$10$tM3N7C9vEa0Hq4r4Kz4Hbe.t.XhBq4W5l0Wp6p9v6tK2eK2eK2eK2', 'Professor', NULL),
(3, 'Professora Juliana', 'juliana.fit@gymflow.com', '$2y$10$tM3N7C9vEa0Hq4r4Kz4Hbe.t.XhBq4W5l0Wp6p9v6tK2eK2eK2eK2', 'Professor', NULL),
(4, 'Ana Oliveira', 'ana.oliveira@email.com', '$2y$10$tM3N7C9vEa0Hq4r4Kz4Hbe.t.XhBq4W5l0Wp6p9v6tK2eK2eK2eK2', 'Aluno', 1),
(5, 'Bruno Souza', 'bruno.souza@email.com', '$2y$10$tM3N7C9vEa0Hq4r4Kz4Hbe.t.XhBq4W5l0Wp6p9v6tK2eK2eK2eK2', 'Aluno', 2);

-- 5. USER_FILIAIS
INSERT INTO user_filiais (user_id, filial_id) VALUES
(1, 1), -- Admin na Filial 1
(1, 2), -- Admin na Filial 2
(2, 1), -- Professor Marcelo na Filial 1
(3, 3); -- Professora Juliana na Filial 3

-- 6. PLANOS
INSERT INTO planos (id, company_id, nome, categoria, valor, duracao) VALUES
(1, 1, 'Plano Mensal Gold', 'Musculação', 99.90, '1 Mês'),
(2, 1, 'Plano Semestral Platinum', 'Musculação + Aulas', 499.00, '6 Meses'),
(3, 1, 'Plano Anual Black', 'Livre Acesso', 899.00, '1 Ano'),
(4, 2, 'Plano Standard', 'Básico', 79.90, '1 Mês');

-- 7. MATRÍCULAS
INSERT INTO matriculas (id, aluno_id, plano_id, inicio, fim, valor, desconto, ativa) VALUES
(1, 1, 1, '2026-07-01', '2026-08-01', 99.90, 0.00, TRUE),   -- Ana ativa no Mensal Gold
(2, 2, 2, '2026-01-15', '2026-07-15', 499.00, 50.00, FALSE), -- Bruno inativo (vencido)
(3, 4, 4, '2026-06-01', '2026-07-01', 79.90, 0.00, TRUE);     -- Diego ativo no Standard

-- 8. CONTAS
INSERT INTO contas (id, aluno_id, matricula_id, vencimento, valor, status, forma_pagamento, pago_em) VALUES
(1, 1, 1, '2026-07-01', 99.90, 'Pago', 'Cartão de Crédito', '2026-07-01 10:00:00'),
(2, 2, 2, '2026-01-15', 449.00, 'Pago', 'Dinheiro', '2026-01-15 14:30:00'),
(3, 4, 3, '2026-06-01', 79.90, 'Aberto', NULL, NULL);

-- 9. LEADS
INSERT INTO leads (id, filial_id, nome, telefone, objetivo, campanha, status, criado_em) VALUES
(1, 1, 'Carlos Eduardo', '(11) 95555-5555', 'Hipertrofia', 'Instagram Ads', 'Novo', '2026-07-15 08:30:00'),
(2, 1, 'Fernanda Mello', '(11) 96666-6666', 'Emagrecimento', 'Indicação', 'Em Atendimento', '2026-07-14 11:00:00'),
(3, 2, 'Gabriela Santos', '(11) 97777-7777', 'Condicionamento Físico', 'Google Search', 'Convertido', '2026-07-12 15:45:00');

-- 10. CUSTOS
INSERT INTO custos (id, filial_id, descricao, categoria, valor, data) VALUES
(1, 1, 'Aluguel do imóvel', 'Infraestrutura', 3500.00, '2026-07-05'),
(2, 1, 'Manutenção de esteiras', 'Equipamentos', 450.00, '2026-07-10'),
(3, 3, 'Energia Elétrica', 'Contas de Consumo', 850.00, '2026-07-08');

-- 11. EXERCICIOS
INSERT INTO exercicios (id, nome, grupo, midia, tipo_midia) VALUES
(1, 'Supino Reto', 'Peito', 'https://example.com/supino.gif', 'imagem'),
(2, 'Agachamento Livre', 'Pernas', 'https://example.com/agachamento.gif', 'imagem'),
(3, 'Puxada no Pulley', 'Costas', 'https://example.com/puxada.gif', 'imagem'),
(4, 'Rosca Direta', 'Bíceps', 'https://example.com/rosca.gif', 'imagem'),
(5, 'Tríceps Corda', 'Tríceps', 'https://example.com/triceps.gif', 'imagem');

-- 12. FICHAS_TREINO
INSERT INTO fichas_treino (id, aluno_id, professor_id, objetivo, criada_em, versao) VALUES
(1, 1, 2, 'Hipertrofia Muscular', '2026-07-02 10:00:00', 1),
(2, 2, 2, 'Resistência Muscular', '2026-01-20 09:00:00', 1);

-- 13. FICHA_ITENS
INSERT INTO ficha_itens (id, ficha_id, exercicio_id, ordem, series, repeticoes, carga, intervalo) VALUES
(1, 1, 1, 1, 4, '10 a 12', '20kg cada lado', '60s'),
(2, 1, 4, 2, 3, '12', '10kg', '45s'),
(3, 2, 2, 1, 4, '15', 'Sem peso adicional', '30s');

-- 14. AVALIACOES
INSERT INTO avaliacoes (id, aluno_id, data, peso, altura, gordura, massa_magra, braco, peitoral, abdomen, cintura, quadril, coxa, panturrilha) VALUES
(1, 1, '2026-07-02', 65.50, 1.68, 22.40, 50.80, 28.5, 90.0, 78.0, 72.0, 95.0, 52.0, 34.0),
(2, 2, '2026-01-16', 82.00, 1.80, 18.50, 66.80, 35.0, 102.0, 88.0, 84.0, 100.0, 58.0, 38.0);

-- 15. CHECKINS
INSERT INTO checkins (id, aluno_id, data, ficha_id) VALUES
(1, 1, '2026-07-15 07:15:00', 1),
(2, 1, '2026-07-16 07:20:00', 1),
(3, 2, '2026-07-10 18:30:00', 2);

-- 16. TRANCAMENTOS
INSERT INTO trancamentos (id, aluno_id, inicio, fim, justificativa, taxa) VALUES
(1, 3, '2026-07-01', '2026-08-01', 'Viagem a trabalho', 30.00);

-- 17. PORTFOLIO_CONFIG
INSERT INTO portfolio_config (company_id, app_name, theme_mode, primary_color, secondary_color, logo_url, hero_title, hero_subtitle, hero_cta, about_text, about_image, company_values, company_competencies) VALUES
(1, 'GymFlow Ecosystem', 'dark', '#ff0055', '#1a1a1a', 'https://example.com/logo.png', 'Transforme seu corpo e sua mente', 'O melhor ecossistema de academias para gerenciar seus treinos e metas.', 'Matricule-se Já', 'Focados em entregar alta performance com conforto e tecnologia.', 'https://example.com/about.jpg', 'Foco, Disciplina, Resultado', 'Musculação Avançada, Acompanhamento Nutricional');

-- 18. PORTFOLIO_MODALITIES
INSERT INTO portfolio_modalities (id, filial_id, name, description, image_url) VALUES
(1, 1, 'CrossFit', 'Treinamento funcional de alta intensidade.', 'https://example.com/crossfit.jpg'),
(2, 1, 'Pilates', 'Aulas focadas em postura, flexibilidade e core.', 'https://example.com/pilates.jpg'),
(3, 2, 'Muay Thai', 'Arte marcial tailandesa de alta queima calórica.', 'https://example.com/muaythai.jpg');
