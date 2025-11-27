-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 27/11/2025 às 01:04
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `verdivabd`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `codigos_redefinicao`
--

CREATE TABLE `codigos_redefinicao` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `codigo` varchar(255) NOT NULL,
  `expiracao` int(11) NOT NULL,
  `criado_em` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `depositos`
--

CREATE TABLE `depositos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `peso_gramas` int(11) NOT NULL,
  `quantidade_unidades` int(11) DEFAULT 1,
  `pontos_ganhos` int(11) NOT NULL,
  `maquina_id` varchar(20) NOT NULL,
  `localizacao` varchar(255) DEFAULT NULL,
  `transacao_id` varchar(50) NOT NULL,
  `status` enum('processed','pending','cancelled') DEFAULT 'processed',
  `criado_em` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `depositos`
--

INSERT INTO `depositos` (`id`, `usuario_id`, `material_id`, `peso_gramas`, `quantidade_unidades`, `pontos_ganhos`, `maquina_id`, `localizacao`, `transacao_id`, `status`, `criado_em`) VALUES
(1, 8, 5, 100, 1, 50, 'VRD002', 'Supermercado Eco - Entrada', 'TXN17642000838748', 'processed', '2025-11-26 20:34:43'),
(2, 8, 5, 100, 1, 50, 'VRD002', 'Supermercado Eco - Entrada', 'TXN17642000836251', 'processed', '2025-11-26 20:34:43'),
(3, 8, 2, 100, 1, 2, 'VRD002', 'Supermercado Eco - Entrada', 'TXN17642001008049', 'processed', '2025-11-26 20:35:00'),
(4, 8, 2, 100, 1, 2, 'VRD003', 'Estação Metro Verdiva - Hall Principal', 'TXN17642001002161', 'processed', '2025-11-26 20:35:00'),
(5, 8, 3, 100, 1, 1, 'VRD002', 'Supermercado Eco - Entrada', 'TXN17642001001050', 'processed', '2025-11-26 20:35:00'),
(6, 8, 3, 100, 1, 1, 'VRD003', 'Estação Metro Verdiva - Hall Principal', 'TXN17642001002467', 'processed', '2025-11-26 20:35:00'),
(7, 8, 5, 500, 5, 250, 'VRD002', 'Supermercado Eco - Entrada', 'TXN17642001399824', 'processed', '2025-11-26 20:35:39'),
(8, 8, 5, 500, 5, 250, 'VRD004', 'Universidade Sustentável - Biblioteca', 'TXN17642001391966', 'processed', '2025-11-26 20:35:39'),
(9, 8, 1, 1000, 10, 10, 'VRD004', 'Universidade Sustentável - Biblioteca', 'TXN17642001552639', 'processed', '2025-11-26 20:35:55'),
(10, 8, 1, 1000, 10, 10, 'VRD003', 'Estação Metro Verdiva - Hall Principal', 'TXN17642001556465', 'processed', '2025-11-26 20:35:55'),
(11, 8, 5, 200, 2, 100, 'VRD003', 'Estação Metro Verdiva - Hall Principal', 'TXN17642003395930', 'processed', '2025-11-26 20:38:59'),
(12, 8, 5, 200, 2, 100, 'VRD003', 'Estação Metro Verdiva - Hall Principal', 'TXN17642003395470', 'processed', '2025-11-26 20:38:59'),
(13, 8, 4, 200, 2, 6, 'VRD002', 'Supermercado Eco - Entrada', 'TXN17642003882119', 'processed', '2025-11-26 20:39:48'),
(14, 8, 4, 200, 2, 6, 'VRD003', 'Estação Metro Verdiva - Hall Principal', 'TXN17642003889824', 'processed', '2025-11-26 20:39:48'),
(15, 8, 5, 100, 1, 50, 'VRD001', 'Shopping Verde - Piso 2', 'TXN17642007922046', 'processed', '2025-11-26 20:46:32'),
(16, 8, 5, 100, 1, 50, 'VRD003', 'Estação Metro Verdiva - Hall Principal', 'TXN17642007928371', 'processed', '2025-11-26 20:46:32'),
(17, 8, 5, 100, 1, 50, 'VRD001', 'Shopping Verde - Piso 2', 'TXN17642010763036', 'processed', '2025-11-26 20:51:16'),
(18, 8, 5, 100, 1, 50, 'VRD002', 'Supermercado Eco - Entrada', 'TXN17642010766840', 'processed', '2025-11-26 20:51:16'),
(19, 8, 5, 100, 1, 50, 'VRD001', 'Shopping Verde - Piso 2', 'TXN17642010765006', 'processed', '2025-11-26 20:51:16'),
(20, 8, 5, 100, 1, 50, 'VRD003', 'Estação Metro Verdiva - Hall Principal', 'TXN17642010768405', 'processed', '2025-11-26 20:51:16'),
(21, 8, 5, 50, 1, 50, 'VRD003', 'Estação Metro Verdiva - Hall Principal', 'TXN17642011476460', 'processed', '2025-11-26 20:52:27'),
(22, 8, 5, 50, 1, 50, 'VRD003', 'Estação Metro Verdiva - Hall Principal', 'TXN17642011472989', 'processed', '2025-11-26 20:52:27'),
(23, 8, 4, 2, 1, 3, 'VRD003', 'Estação Metro Verdiva - Hall Principal', 'TXN17642011475358', 'processed', '2025-11-26 20:52:27'),
(24, 8, 4, 2, 1, 3, 'VRD001', 'Shopping Verde - Piso 2', 'TXN17642011473588', 'processed', '2025-11-26 20:52:27'),
(25, 8, 1, 100, 1, 1, 'VRD004', 'Universidade Sustentável - Biblioteca', 'TXN17642016159554', 'processed', '2025-11-26 21:00:15'),
(26, 8, 1, 100, 1, 1, 'VRD001', 'Shopping Verde - Piso 2', 'TXN17642016151332', 'processed', '2025-11-26 21:00:15'),
(27, 8, 4, 20000, 200, 600, 'VRD003', 'Estação Metro Verdiva - Hall Principal', 'TXN17642016154868', 'processed', '2025-11-26 21:00:15'),
(28, 8, 4, 20000, 200, 600, 'VRD003', 'Estação Metro Verdiva - Hall Principal', 'TXN17642016155430', 'processed', '2025-11-26 21:00:15');

--
-- Acionadores `depositos`
--
DELIMITER $$
CREATE TRIGGER `after_deposito_insert` AFTER INSERT ON `depositos` FOR EACH ROW BEGIN
    INSERT INTO pontos_usuario (usuario_id, saldo_pontos, total_acumulado, total_resgatado)
    VALUES (NEW.usuario_id, NEW.pontos_ganhos, NEW.pontos_ganhos, 0)
    ON DUPLICATE KEY UPDATE
        saldo_pontos = saldo_pontos + NEW.pontos_ganhos,
        total_acumulado = total_acumulado + NEW.pontos_ganhos;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `materiais`
--

CREATE TABLE `materiais` (
  `id` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `categoria` varchar(100) NOT NULL,
  `pontos_por_kg` decimal(10,2) DEFAULT 0.00,
  `pontos_por_unidade` decimal(10,2) DEFAULT NULL,
  `peso_minimo_gramas` int(11) DEFAULT 0,
  `status` enum('accepted','rejected','pending') DEFAULT 'accepted',
  `instrucoes` text DEFAULT NULL,
  `criado_em` datetime DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `materiais`
--

INSERT INTO `materiais` (`id`, `tipo`, `categoria`, `pontos_por_kg`, `pontos_por_unidade`, `peso_minimo_gramas`, `status`, `instrucoes`, `criado_em`, `atualizado_em`) VALUES
(1, 'papel', 'Papel e Papelão', 10.00, NULL, 100, 'accepted', 'Remova grampos e fitas adesivas', '2025-11-26 13:53:22', '2025-11-26 13:53:22'),
(2, 'vidro', 'Vidros e Cristais', 3.00, 2.00, 50, 'accepted', 'Remova tampas e rótulos', '2025-11-26 13:53:22', '2025-11-26 13:53:22'),
(3, 'plastico', 'Plásticos Recicláveis', 5.00, 1.00, 10, 'accepted', 'Lave antes de depositar', '2025-11-26 13:53:22', '2025-11-26 13:53:22'),
(4, 'metal', 'Metais', 15.00, 3.00, 50, 'accepted', 'Latas de alumínio e ferro', '2025-11-26 13:53:22', '2025-11-26 13:53:22'),
(5, 'eletronico', 'Eletrônicos', 25.00, 50.00, NULL, 'accepted', 'Celulares, baterias, cabos', '2025-11-26 13:53:22', '2025-11-26 13:53:22');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pontos_usuario`
--

CREATE TABLE `pontos_usuario` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `saldo_pontos` int(11) DEFAULT 0,
  `total_acumulado` int(11) DEFAULT 0,
  `total_resgatado` int(11) DEFAULT 0,
  `atualizado_em` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `pontos_usuario`
--

INSERT INTO `pontos_usuario` (`id`, `usuario_id`, `saldo_pontos`, `total_acumulado`, `total_resgatado`, `atualizado_em`) VALUES
(1, 7, 0, 0, 0, '2025-11-26 13:53:22'),
(2, 6, 0, 0, 0, '2025-11-26 13:53:22'),
(4, 8, 2446, 2446, 0, '2025-11-26 21:00:15');

-- --------------------------------------------------------

--
-- Estrutura para tabela `recompensas`
--

CREATE TABLE `recompensas` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `pontos_necessarios` int(11) NOT NULL,
  `valor_reais` decimal(10,2) NOT NULL,
  `categoria` varchar(50) NOT NULL,
  `parceiro` varchar(255) DEFAULT NULL,
  `validade_dias` int(11) DEFAULT 30,
  `disponivel` tinyint(1) DEFAULT 1,
  `termos` text DEFAULT NULL,
  `instrucoes` text DEFAULT NULL,
  `criado_em` datetime DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `recompensas`
--

INSERT INTO `recompensas` (`id`, `nome`, `descricao`, `pontos_necessarios`, `valor_reais`, `categoria`, `parceiro`, `validade_dias`, `disponivel`, `termos`, `instrucoes`, `criado_em`, `atualizado_em`) VALUES
(1, 'Vale Transporte Público', 'Créditos para ônibus e metrô', 500, 5.00, 'transporte', 'TransVerde', 60, 1, 'Válido em toda rede de transporte', 'Use o código no app TransVerde', '2025-11-26 13:53:22', '2025-11-26 13:53:22'),
(2, 'Kit Plantio Urbano', 'Kit com sementes e vaso biodegradável', 800, 8.00, 'jardinagem', 'Jardim Verde', 90, 1, 'Retirada em loja parceira', 'Apresente o código na loja mais próxima', '2025-11-26 13:53:22', '2025-11-26 13:53:22'),
(3, 'Desconto Supermercado Verde', 'Desconto de R$ 10,00 em compras acima de R$ 50,00', 1000, 10.00, 'alimentacao', 'Supermercado Verde', 30, 1, 'Válido apenas para compras presenciais', 'Apresente o código no caixa', '2025-11-26 13:53:22', '2025-11-26 13:53:22'),
(4, 'Desconto Cinema Sustentável', 'Ingresso com 50% de desconto', 1500, 15.00, 'entretenimento', 'CineVerde', 20, 1, 'Válido de segunda a quinta-feira', 'Use o código na compra online ou bilheteria', '2025-11-26 13:53:22', '2025-11-26 13:53:22'),
(5, 'Desconto Loja Eco Fashion', 'Desconto de R$ 25,00 em roupas sustentáveis', 2500, 25.00, 'moda', 'Eco Fashion Store', 45, 1, 'Válido para toda linha eco', 'Apresente o código na loja ou site', '2025-11-26 13:53:22', '2025-11-26 13:53:22'),
(6, 'Curso Online Sustentabilidade', 'Acesso gratuito ao curso completo', 3000, 30.00, 'educacao', 'EcoEducação', 120, 1, 'Certificado incluso', 'Acesse o site e use o código para liberação', '2025-11-26 13:53:22', '2025-11-26 13:53:22');

-- --------------------------------------------------------

--
-- Estrutura para tabela `resgates`
--

CREATE TABLE `resgates` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `recompensa_id` int(11) NOT NULL,
  `pontos_utilizados` int(11) NOT NULL,
  `valor_reais` decimal(10,2) NOT NULL,
  `codigo_desconto` varchar(50) NOT NULL,
  `transacao_id` varchar(50) NOT NULL,
  `valido_ate` date NOT NULL,
  `status` enum('active','used','expired','cancelled') DEFAULT 'active',
  `usado_em` datetime DEFAULT NULL,
  `criado_em` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Acionadores `resgates`
--
DELIMITER $$
CREATE TRIGGER `after_resgate_insert` AFTER INSERT ON `resgates` FOR EACH ROW BEGIN
    UPDATE pontos_usuario
    SET saldo_pontos = saldo_pontos - NEW.pontos_utilizados,
        total_resgatado = total_resgatado + NEW.pontos_utilizados
    WHERE usuario_id = NEW.usuario_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `cpf` varchar(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `data_cadastro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `cpf`, `email`, `senha`, `telefone`, `data_cadastro`) VALUES
(6, '44885055890', 'natsumiayame00@gmail.com', '$2y$10$Hq3pxH2czIBq9oHqll.C/OG07TWmqQmmzSptoaRHTNVw2ebYIo/9e', '11888888888', '2025-11-14 07:29:31'),
(7, '15635362589', 'natalia.xavier.silva16@gmail.com', '$2y$10$mZIi1GoC0kJY.7Rc5L6OLOkz0aegbeoMcFmTjt85Eiib2UyEIll9a', '11888888888', '2025-11-17 12:23:31'),
(8, '25414523650', 'test@test.com', '$2y$10$sGvkqLHQJo5zjnC0hm8hKedbdakdQbcxIH/HP6PezWSqiNP6aVatm', '11855252555', '2025-11-26 14:30:27');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `codigos_redefinicao`
--
ALTER TABLE `codigos_redefinicao`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `depositos`
--
ALTER TABLE `depositos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transacao_id` (`transacao_id`),
  ADD KEY `material_id` (`material_id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_data` (`criado_em`),
  ADD KEY `idx_transacao` (`transacao_id`);

--
-- Índices de tabela `materiais`
--
ALTER TABLE `materiais`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tipo` (`tipo`);

--
-- Índices de tabela `pontos_usuario`
--
ALTER TABLE `pontos_usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_usuario` (`usuario_id`);

--
-- Índices de tabela `recompensas`
--
ALTER TABLE `recompensas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pontos` (`pontos_necessarios`),
  ADD KEY `idx_categoria` (`categoria`);

--
-- Índices de tabela `resgates`
--
ALTER TABLE `resgates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_desconto` (`codigo_desconto`),
  ADD UNIQUE KEY `transacao_id` (`transacao_id`),
  ADD KEY `recompensa_id` (`recompensa_id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_codigo` (`codigo_desconto`),
  ADD KEY `idx_status` (`status`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cpf` (`cpf`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `codigos_redefinicao`
--
ALTER TABLE `codigos_redefinicao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `depositos`
--
ALTER TABLE `depositos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de tabela `materiais`
--
ALTER TABLE `materiais`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `pontos_usuario`
--
ALTER TABLE `pontos_usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de tabela `recompensas`
--
ALTER TABLE `recompensas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `resgates`
--
ALTER TABLE `resgates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `depositos`
--
ALTER TABLE `depositos`
  ADD CONSTRAINT `depositos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `depositos_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `materiais` (`id`);

--
-- Restrições para tabelas `pontos_usuario`
--
ALTER TABLE `pontos_usuario`
  ADD CONSTRAINT `pontos_usuario_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `resgates`
--
ALTER TABLE `resgates`
  ADD CONSTRAINT `resgates_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `resgates_ibfk_2` FOREIGN KEY (`recompensa_id`) REFERENCES `recompensas` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
