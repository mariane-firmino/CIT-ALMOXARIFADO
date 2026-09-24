-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Sep 24, 2026 at 05:26 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bd_cit_almoxarifado`
--

-- --------------------------------------------------------

--
-- Table structure for table `categoria`
--

CREATE TABLE `categoria` (
  `cate_id` int(5) NOT NULL,
  `cate_nome` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categoria`
--

INSERT INTO `categoria` (`cate_id`, `cate_nome`) VALUES
(1, 'Acessório multifuncional de viagem'),
(2, 'Acessório/controle'),
(3, 'Acessórios corporativos'),
(4, 'Acessórios de áudio'),
(5, 'Acessórios de fotografia e vídeo'),
(8, 'Acessórios e Componentes para Irrigação'),
(6, 'Acessórios para chaveiros'),
(7, 'Acessórios para Videogame'),
(9, 'Adaptador de energia universal'),
(10, 'Ativos passivos'),
(11, 'Atuadores'),
(12, 'Automação hidráulica'),
(13, 'Automação Residencial'),
(14, 'Baterias recarregáveis de íons de lítio'),
(15, 'Cabeamento Estruturado'),
(16, 'Cabos de transferência de dados'),
(17, 'Caixas de Proteção para Eletrônicos'),
(18, 'CAT II 1000V'),
(19, 'Componente de automação/eletrônica'),
(20, 'Componente eletrônico'),
(21, 'Componente eletrônico passivo'),
(22, 'Componente passivo de gerenciamento térmico'),
(23, 'Componentes de automação'),
(24, 'Componentes de Automação Hidráulica/Irrigação'),
(25, 'Componentes de Interconexão'),
(26, 'Componentes de movimento e proximidade'),
(27, 'Componentes de prototipagem'),
(28, 'Componentes de prototipagem eletrônica'),
(29, 'Componentes eletrônicos'),
(31, 'Componentes eletrônicos de automação'),
(32, 'Componentes eletrônicos de entrada'),
(33, 'Componentes eletrônicos de robótica'),
(34, 'Componentes eletrônicos de segurança eletrônica e automação'),
(35, 'Componentes eletrônicos de sinalização'),
(36, 'Componentes eletrônicos para prototipagem'),
(30, 'Componentes Eletrônicos Passivos/Ativos'),
(37, 'Componentes optoeletrônicos'),
(43, 'Computador de Placa Única'),
(38, 'Conectores DC'),
(39, 'Conexões e Acessórios para Irrigação/Jardinagem'),
(40, 'Conexões para Irrigação'),
(41, 'Conexões plásticas para mangueiras de baixa pressão'),
(42, 'Construção civil'),
(44, 'Desengraxante'),
(45, 'Displays'),
(46, 'Displays LCD Alfanuméricos'),
(47, 'Dispositivo de entrada de dados'),
(48, 'Elementos de Fixação'),
(49, 'Elementos de fixação mecânica'),
(50, 'Eletrônico'),
(51, 'Eletrônicos passivos'),
(52, 'Encapsulamentos de montagem através de orifício'),
(53, 'Ferragens para móveis'),
(54, 'Ferramenta elétrica de uso profissional e multiuso'),
(55, 'Ferramenta manual de crimpagem'),
(56, 'Ferramenta manual de uso geral'),
(57, 'Ferramentas de medição manual de precisão'),
(58, 'Ferramentas e materiais de construção'),
(59, 'Ferramentas elétricas de gravação'),
(60, 'Ferramentas manuais de aperto e desaperto'),
(61, 'Ferramentas Manuais de Fixação'),
(62, 'Ferramentas manuais de fixação de bancada'),
(63, 'Ferramentas manuais de precisão'),
(64, 'Fita de isolamento térmico'),
(65, 'Fonte Chaveada AC/DC'),
(66, 'Fonte de Alimentação'),
(67, 'Fontes Chaveadas Estabilizadas'),
(68, 'Fotoresistores'),
(69, 'Fotorresistor'),
(70, 'Hi-Speed USB 2.0'),
(71, 'Instrumentos meteorológicos de segurança'),
(72, 'Irrigação e Microaspersão'),
(73, 'Kit de Resistores'),
(74, 'Lubrificantes Spray'),
(75, 'Micro servo de engrenagens metálicas'),
(76, 'Microcontrolador'),
(77, 'Microcontroladores'),
(78, 'Microcontroladores embarcados de prototipagem rápida'),
(79, 'Microcontroladores Sem Fio'),
(80, 'Microinterruptor táctil'),
(81, 'Microirrigação'),
(82, 'Mini bombas dosadoras peristálticas'),
(83, 'Módulo de armazenamento de dados'),
(84, 'Módulo sensor'),
(85, 'Módulos de Comunicação'),
(86, 'Módulos de Display LED'),
(87, 'Motores de passo híbridos NEMA'),
(88, 'Motores elétricos de corrente contínua'),
(89, 'Notebooks corporativos'),
(90, 'Optoeletrônica'),
(91, 'Parafusos Estruturais'),
(92, 'Periféricos de entrada'),
(97, 'PLA (Ácido Polilático)'),
(93, 'Placa de desenvolvimento'),
(94, 'Placa Ilhada'),
(95, 'Placas de desenvolvimento (IoT)'),
(96, 'Placas de prototipagem universal'),
(98, 'Polímeros'),
(99, 'Protetores auditivos'),
(100, 'Prototipagem'),
(101, 'Prototipagem eletrônica'),
(102, 'Protótipo'),
(103, 'Reguladores de Tensão'),
(104, 'Resistores fixos de filme de carbono'),
(105, 'Robótica Educacional'),
(106, 'Segurança/acessórios'),
(107, 'Sensor'),
(108, 'Sensor barométrico digital de pressão atmosférica e temperatura'),
(109, 'Sensor de qualidade do ar e gases tóxicos'),
(110, 'Sensores'),
(111, 'Sensores de gases'),
(112, 'Sensores de gases inflamáveis'),
(113, 'Sensores de injeção eletrônica'),
(114, 'Sensores de luminosidade'),
(115, 'Sensores de luz infravermelha'),
(116, 'Sensores de monitoramento ambiental'),
(117, 'Sensores de movimento e presença'),
(118, 'Sensores de temperatura e umidade'),
(119, 'Sensores de temperatura resistivos'),
(120, 'Sensores de Umidade'),
(121, 'Sensores e módulos'),
(122, 'Sensores e módulos eletrônicos'),
(123, 'Sensores eletrônicos de ambiente'),
(124, 'Sensores meteorológicos'),
(125, 'Sensores ópticos'),
(126, 'Sensores Optoeletrônicos'),
(127, 'Sensores semicondutores de óxido metálico'),
(128, 'Suspensão'),
(130, 'Tinta Spray de Uso Geral'),
(129, 'Tipos de conectores'),
(131, 'Uso geral'),
(132, 'Uso geral e industrial'),
(133, 'Versão preliminar');

-- --------------------------------------------------------

--
-- Table structure for table `funcao`
--

CREATE TABLE `funcao` (
  `func_id` int(5) NOT NULL,
  `func_nome` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `funcao`
--

INSERT INTO `funcao` (`func_id`, `func_nome`) VALUES
(1, 'Coordenador'),
(2, 'Estagiário'),
(3, 'Servidor');

-- --------------------------------------------------------

--
-- Table structure for table `item_solicitacao`
--

CREATE TABLE `item_solicitacao` (
  `item_id` int(11) NOT NULL,
  `soli_id` int(11) NOT NULL,
  `prod_id` int(11) NOT NULL,
  `item_quantidade` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item_solicitacao`
--

INSERT INTO `item_solicitacao` (`item_id`, `soli_id`, `prod_id`, `item_quantidade`) VALUES
(11, 12, 2, 1),
(12, 12, 12, 1),
(13, 12, 13, 1),
(14, 12, 14, 1),
(15, 13, 12, 2),
(16, 14, 2, 4),
(17, 15, 2, 4),
(18, 16, 2, 8),
(19, 17, 2, 1),
(20, 18, 14, 2),
(21, 19, 14, 2);

-- --------------------------------------------------------

--
-- Table structure for table `localizacao`
--

CREATE TABLE `localizacao` (
  `loca_id` int(5) NOT NULL,
  `loca_nome` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `localizacao`
--

INSERT INTO `localizacao` (`loca_id`, `loca_nome`) VALUES
(2, 'Armário 1 (sala de aula)'),
(6, 'Armário 3'),
(1, 'CX Lote 1'),
(3, 'Depósito'),
(5, 'Gaveta do CIT'),
(4, 'Sala CIT');

-- --------------------------------------------------------

--
-- Table structure for table `notificacao`
--

CREATE TABLE `notificacao` (
  `noti_id` int(11) NOT NULL,
  `noti_titulo` varchar(100) NOT NULL,
  `noti_mensagem` text NOT NULL,
  `noti_tipo` enum('Solicitação','Aprovação','Negado','Estoque','Usuário','Produto','Sistema') NOT NULL,
  `noti_data` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notificacao`
--

INSERT INTO `notificacao` (`noti_id`, `noti_titulo`, `noti_mensagem`, `noti_tipo`, `noti_data`) VALUES
(1, 'Novo cadastro', 'Novo usuário cadastrado: testee', 'Usuário', '2026-09-17 22:22:55'),
(2, 'Novo cadastro', 'Novo usuário cadastrado: Mariane Cardoso Firmino Melo', 'Usuário', '2026-09-17 22:30:46'),
(3, 'Usuário alterado', 'Status do usuário alterado: Mariane Cardoso Firmino Melo () agora está Inativo.', 'Usuário', '2026-09-19 10:35:28'),
(4, 'Seu status foi alterado', 'Seu status no sistema foi alterado para Inativo.', 'Usuário', '2026-09-19 10:36:12'),
(5, 'Usuário alterado', 'Status do usuário alterado: Anne () agora está Ativo.', 'Usuário', '2026-09-19 21:05:29'),
(6, 'Seu status foi alterado', 'Seu status no sistema foi alterado para Ativo.', 'Usuário', '2026-09-19 21:05:58'),
(7, 'Usuário alterado', 'Status do usuário alterado: Anne () agora está Ativo.', 'Usuário', '2026-09-19 21:05:58'),
(8, 'Seu status foi alterado', 'Seu status no sistema foi alterado para Ativo.', 'Usuário', '2026-09-19 21:06:23'),
(9, 'Nova solicitação', 'Nova solicitação #11 realizada por darly.', 'Solicitação', '2026-09-20 07:22:55'),
(10, 'Novo cadastro', 'Novo usuário cadastrado: Alguem aí', 'Usuário', '2026-09-20 22:03:06'),
(11, 'Novo produto disponível', 'Um novo produto foi cadastrado: Travesseiro Fofinho.', 'Produto', '2026-09-20 22:16:11'),
(12, 'Novo cadastro', 'Novo usuário cadastrado: Darliane', 'Usuário', '2026-09-21 16:49:08'),
(13, 'Seu status foi alterado', 'Seu status no sistema foi alterado para Ativo.', 'Usuário', '2026-09-21 16:50:29'),
(14, 'Novo produto disponível', 'Um novo produto foi cadastrado: Energéticos.', 'Produto', '2026-09-21 17:05:32'),
(15, 'Nova solicitação', 'Nova solicitação #12 realizada por Darliane.', 'Solicitação', '2026-09-21 17:07:55'),
(16, 'Solicitação aprovada', 'Sua solicitação #12 foi aprovada.', 'Aprovação', '2026-09-21 17:24:18'),
(17, 'Nova solicitação', 'Nova solicitação #13 realizada por Darliane.', 'Solicitação', '2026-09-23 12:20:01'),
(18, 'Solicitação negada', 'Sua solicitação #13 foi negada.', 'Negado', '2026-09-23 12:20:50'),
(19, 'Novo cadastro', 'Novo usuário cadastrado: Neemias Ferreira Hitotuzi', 'Usuário', '2026-09-23 16:26:50'),
(20, 'Seu status foi alterado', 'Seu status no sistema foi alterado para Ativo.', 'Usuário', '2026-09-23 16:29:46'),
(21, 'Novo cadastro', 'Novo usuário cadastrado: teste', 'Usuário', '2026-09-23 16:39:01'),
(22, 'Usuário alterado', 'Status do usuário alterado: teste () agora está Ativo.', 'Usuário', '2026-09-23 16:40:29'),
(23, 'Seu status foi alterado', 'Seu status no sistema foi alterado para Ativo.', 'Usuário', '2026-09-23 16:40:29'),
(24, 'Nova solicitação', 'Nova solicitação #14 realizada por teste.', 'Solicitação', '2026-09-23 16:41:38'),
(25, 'Nova solicitação', 'Nova solicitação #15 realizada por teste.', 'Solicitação', '2026-09-23 16:42:29'),
(26, 'Solicitação aprovada', 'Sua solicitação #14 foi aprovada.', 'Aprovação', '2026-09-23 16:43:24'),
(27, 'Novo produto disponível', 'Um novo produto foi cadastrado: Lua.', 'Produto', '2026-09-23 16:59:06'),
(28, 'Devolução informada', 'teste informou a devolução da solicitação #14.', 'Solicitação', '2026-09-23 17:28:00'),
(29, 'Devolução confirmada', 'A devolução da sua solicitação #14 foi confirmada.', 'Aprovação', '2026-09-23 17:30:15'),
(30, 'Nova solicitação', 'Nova solicitação #16 realizada por teste.', 'Solicitação', '2026-09-23 17:32:57'),
(31, 'Solicitação aprovada', 'Sua solicitação #16 foi aprovada.', 'Aprovação', '2026-09-23 17:34:24'),
(32, 'Nova solicitação', 'Nova solicitação #17 realizada por teste.', 'Solicitação', '2026-09-23 17:39:39'),
(33, 'Solicitação aprovada', 'Sua solicitação #17 foi aprovada.', 'Aprovação', '2026-09-23 17:40:34'),
(34, 'Novo cadastro', 'Novo usuário cadastrado: rafel', 'Usuário', '2026-09-23 17:51:35'),
(35, 'Novo usuário cadastrado', 'Um novo usuário foi cadastrado no sistema: rafel (Estagiário).', 'Usuário', '2026-09-23 17:51:35'),
(36, 'Novo produto disponível', 'Um novo produto foi cadastrado: capoeira.', 'Produto', '2026-09-23 18:01:29'),
(37, 'Novo cadastro', 'Novo usuário cadastrado: primeiro teste', 'Usuário', '2026-09-23 19:38:57'),
(38, 'Novo cadastro', 'Novo usuário cadastrado: teste segundo', 'Usuário', '2026-09-23 19:39:52'),
(39, 'Novo cadastro', 'Novo usuário cadastrado: Teste tres', 'Usuário', '2026-09-23 19:54:14'),
(40, 'Novo cadastro', 'Novo usuário cadastrado: Teste quatro', 'Usuário', '2026-09-23 19:56:01'),
(41, 'Novo cadastro', 'Novo usuário cadastrado: Teste cinco', 'Usuário', '2026-09-23 20:02:00'),
(42, 'Novo cadastro', 'Novo usuário cadastrado: Mariane Firmino', 'Usuário', '2026-09-23 20:08:27'),
(43, 'Usuário alterado', 'Status do usuário alterado: Mariane Firmino () agora está Ativo.', 'Usuário', '2026-09-23 20:10:18'),
(44, 'Seu status foi alterado', 'Seu status no sistema foi alterado para Ativo.', 'Usuário', '2026-09-23 20:10:18'),
(45, 'Novo cadastro', 'Novo usuário cadastrado: coordenador primeiro', 'Usuário', '2026-09-23 20:56:03'),
(46, 'Novo usuário cadastrado', 'Um novo usuário foi cadastrado no sistema: coordenador primeiro (Coordenador).', 'Usuário', '2026-09-23 20:56:03'),
(47, 'Novo cadastro', 'Novo usuário cadastrado: estagiario teste', 'Usuário', '2026-09-23 20:56:44'),
(48, 'Novo usuário cadastrado', 'Um novo usuário foi cadastrado no sistema: estagiario teste (Estagiário).', 'Usuário', '2026-09-23 20:56:44'),
(49, 'Usuário alterado', 'Status do usuário alterado: primeiro teste () agora está Ativo.', 'Usuário', '2026-09-23 21:41:01'),
(50, 'Seu status foi alterado', 'Seu status no sistema foi alterado para Ativo.', 'Usuário', '2026-09-23 21:41:01'),
(51, 'Nova solicitação', 'Nova solicitação #18 realizada por primeiro teste.', 'Solicitação', '2026-09-23 22:51:48'),
(52, 'Nova solicitação', 'Nova solicitação #19 realizada por Darliane.', 'Solicitação', '2026-09-23 22:53:58'),
(53, 'Devolução informada', 'Darliane informou a devolução da solicitação #12.', 'Solicitação', '2026-09-23 23:00:28'),
(54, 'Devolução confirmada', 'A devolução da sua solicitação #12 foi confirmada.', 'Aprovação', '2026-09-23 23:03:16'),
(55, 'Novo cadastro', 'Novo usuário cadastrado: teste siete', 'Usuário', '2026-09-23 23:13:28'),
(56, 'Novo cadastro aguardando ativação', 'O usuário teste siete realizou um novo cadastro e aguarda ativação.', 'Usuário', '2026-09-23 23:13:28');

-- --------------------------------------------------------

--
-- Table structure for table `notificacao_usuario`
--

CREATE TABLE `notificacao_usuario` (
  `noti_id` int(11) NOT NULL,
  `usua_id` int(5) NOT NULL,
  `noti_status` enum('Lida','Não lida') NOT NULL DEFAULT 'Não lida',
  `noti_data_leitura` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notificacao_usuario`
--

INSERT INTO `notificacao_usuario` (`noti_id`, `usua_id`, `noti_status`, `noti_data_leitura`) VALUES
(1, 9, 'Lida', '2026-09-19 12:12:59'),
(2, 18, 'Não lida', NULL),
(3, 9, 'Não lida', NULL),
(3, 18, 'Não lida', NULL),
(4, 18, 'Não lida', NULL),
(9, 9, 'Não lida', NULL),
(9, 18, 'Não lida', NULL),
(11, 6, 'Não lida', NULL),
(11, 9, 'Não lida', NULL),
(12, 9, 'Lida', '2026-09-23 12:17:04'),
(13, 21, 'Não lida', NULL),
(14, 6, 'Não lida', NULL),
(14, 9, 'Lida', '2026-09-23 12:15:35'),
(14, 21, 'Não lida', NULL),
(15, 9, 'Lida', '2026-09-23 12:11:22'),
(18, 21, 'Não lida', NULL),
(29, 23, 'Não lida', NULL),
(31, 23, 'Não lida', NULL),
(33, 23, 'Não lida', NULL),
(34, 9, 'Não lida', NULL),
(45, 22, 'Não lida', NULL),
(45, 31, 'Não lida', NULL),
(45, 33, 'Não lida', NULL),
(47, 22, 'Não lida', NULL),
(47, 31, 'Não lida', NULL),
(47, 33, 'Não lida', NULL),
(54, 21, 'Não lida', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `produto`
--

CREATE TABLE `produto` (
  `prod_id` int(5) NOT NULL,
  `prod_nome` varchar(100) NOT NULL,
  `prod_descricao` text NOT NULL,
  `prod_foto` varchar(300) NOT NULL,
  `prod_quantidade` int(5) NOT NULL,
  `prod_status` varchar(50) DEFAULT NULL,
  `prod_estoque_minimo` int(3) NOT NULL DEFAULT 5,
  `prod_qrcode` blob DEFAULT NULL,
  `prod_data_cadastro` datetime NOT NULL DEFAULT current_timestamp(),
  `loca_id` int(5) NOT NULL,
  `cate_id` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produto`
--

INSERT INTO `produto` (`prod_id`, `prod_nome`, `prod_descricao`, `prod_foto`, `prod_quantidade`, `prod_status`, `prod_estoque_minimo`, `prod_qrcode`, `prod_data_cadastro`, `loca_id`, `cate_id`) VALUES
(2, 'Caneca de teste', 'Teste para verificar se tá tudo certo.', 'a8d23b9694c23f35c557ae17b87e20cd.webp', 1, 'Disponível', 5, NULL, '2026-09-20 12:10:43', 4, 131),
(12, 'Saco de dinheiro', 'Melhor produtooooo.', '2018a73a4ec794baf89522ac373bc793.jpg', 4, 'Estoque baixo', 10, 0x89504e470d0a1a0a0000000d4948445200000140000001400103000000f75e37e900000006504c5445040204fcfefc49bec75e000000097048597300000ec400000ec401952b0e1b0000029e494441546881ed9a3b6ec33010445770e1d247c8517434f2683a8a8fe03245c0cdcecc52969314a982002b028123f1b119ec9f32ffe5b2133cc13f018dabe1e96e362efe8827feacee1fdc5c0a830b90ad2dfe7ebb63eb1a20e40db0c713764b83a1d5dbd6ecf20e3983c753e888d33ad64eb0a5b5c5dfedce2d1227982005f41e72de9c3f16667682d30b2dc2d21bfe05cf9721e74fee5a0c4cfb826490f3cbcff7605f0c9c4bef5c4637d2ccf6ddba609852a4fe90ece69b316aaf7de050fc30b05b6550d1c953391011ab62eb8190c5272f0daa2402a877e17e26d029eeb340aa089a5d1fd4517a319477fa24ea474351591a8c9c8f6cc6c2d118bcbdc3f600c62a0e42c03be351f28c55f24281dd0b83aa85cc2e2a89b8d095a5b5a19aac0c1afa8db5d3fd36cbdc661715dbf612a44a82b2af4586c514a7de15a795e22a83d96fa48eae14c78e0df6857148af0c66ef3a90e936a39cfb136bcbd5bd32a85a48ddc7b4b63de11d2a809aa0bc90a9df568780cfb62387af954169a56c062f4465043ec7212ff65810cc9268ecef52396edbc1cc6a82f96e8625f9a4d9f7eea3263875e4de0c4baa8c66acf2c2e0a2349661c9f2260c6b3399d95e0154044d973b68ce78c7c3823ae720a9aa17065d5783aa18e7b1c3e48377897541dea3726e06bf63054067d4d4957d6d65706852362f31b29aa41736cd87bc32c81b42c66948e6e25f5afb5e1954efda19caada5b5b12c400c477c2f0d52ab6dceec07b73ca7aeaa0a5a6530d792bdab7a345787bff8ac07ca82a9156f3622b729e10d89ab63bd322887e3972e4fe5f27ba08ce84b65f03037cb7b314427eaf83c5d1dd404b1ab556b0a590aecbb995506a77d7dbc74f8593c5506e5858c4e9a2ed2e8ecf00143653063f8d0884c5ff85a76b214f7502a14047fb74ef004ff13f809c80ae6db33bbe9f60000000049454e44ae426082, '2026-09-20 12:10:43', 4, 131),
(13, 'Travesseiro Fofinho', 'Queria dormir e pensei em um travesseiro para adicionar.\r\n(\\_/)\r\n(*.*)\r\n(><)', '1eba4faa8ad79886017a9a8eb45c9a99.webp', 10, 'Disponível', 2, 0x89504e470d0a1a0a0000000d4948445200000140000001400103000000f75e37e900000006504c5445040204fcfefc49bec75e000000097048597300000ec400000ec401952b0e1b000002a8494441546881ed9a318eac301044db2298902370148e868fc65138c2841b8cdcdf5d556658ed041badbed490a08147527297abdb63fecbcb6ef006ff04345c5b79d9ec5e8b7fc5cd5f0f8f5f2fbc2c89c11232ed9b9eb5c99fb6061fbfc87b6ab013cbbe993d9e5dce6610b013cf1010b7ed063bd197d91ee2cd28bbaee3ea37384029d791e7b2ab26dd6f705421d4ebebcb42c0aee37cacfeb15c7381a7873f28e0f7db27b34f05ea8af23b6c43f9d96661e520c6ebb420ab309e1d6b7df3b3beeece551283256c29a442ddb529cc1b0960e9045393e706c3a494006c82801104baaaf10aaae605fb32835d43ce0abeebc80daf854585aa79417a78444555e11997900e7a154ac7a4e0c4768ce63d7235ab90bd88a7060d9b7d8b9bd77815bdc8148e8e0a3dde512123283f2a3e887074f286e8ed3533c8eec346a3f16e3bc4db69f639412600ed7420f819aa902d6c62304ceaa02df5ddac213f1ad65e34ad5f331bb7b4609877ac286c6a9555882080db352aa404c7dc6cc216a72a6c1304541ef0c420db8e1ae072aaaa941d156a6bcd0c36e6478e1537d5a45276f7703ff3634e5057c46b797877ed661fda9494a074943b892897a1746ef03a67b58de6ed3a172b7c9719c419cf1e7517fd067b1163bf8fc9c799007282b025ce131d5b3f92d17bf2b18cb1624e30f67cadaf283f2e2c6d71fada1383dcfaab3afcc2295ad1509a1a97c4204f75b6c27f27606149dc185163f2e199c173dc1acfd09cadf5f470e481921ae411182f9d10b2fc30915d60e469415d9c3f2b27e1ebe5479b9211a46a9b8f62bc9eaaf2d0f9fbb174325003b3c6d1bdcec534565485a606c7ffa44c5b3f5a7b8aab877e836ce65d87cee7dcecf1bc416332ba1c14ea9c437d6d6a7054a1730c8d430ca549a95a12832a388dc8caf8ff18057c5d12404ef077d70ddee0ff04fe0308d53d2a0854db790000000049454e44ae426082, '2026-09-20 22:16:11', 6, 14),
(14, 'Energéticos', 'Energéticos de vários sabores. Solicite um e ganhe aleatoriamente.', 'b5227d2591d749f355b70b2fbb8501b3.webp', 10, 'Disponível', 3, 0x89504e470d0a1a0a0000000d4948445200000140000001400103000000f75e37e900000006504c5445040204fcfefc49bec75e000000097048597300000ec400000ec401952b0e1b000002a3494441546881ed9a318ec3300c0429b8489927f8297e9afdb43ce59e90d245601eb94bc90970c5558703d66a04cbe3140b91e25231ffe5b00bbcc03f010d636d2fbb7f2d9b4d7b4efebac5db2d16733461b0a54c8f95bc1d933f6dd99aeff944dea5c1d06a0e3088391629604ccf1410d37a81abe7368b77b9cd6cf5d471f10b1ca0637fe5144f19930e55e5414621a6dc5f9602868e91abfca7701503470ebf51c0cfe9a7642f05d688e7117e869445a2bf96051985b99615c0e0eff5b53fba8e9260739e6d8cbb63cae48d0a600e02b96a736dd0433933a4eb3ce262e1b05baaea7ca70c1ec6d30c726ee04347164847a6a8545517acca08af962d5f454cd6d7d8668fd25113ec762ca330fd461d6a88427a1197060d877dc69de333e8386546af081da5822258f9a8946392da5240e6708bd05406e93eac1b8dd376ccf123a8937ab2d704590144c59826e4c04987cf1085b4b0c220cd7c1f072b4647f84551b9df69dc64c1f41b334f3a670590c198533ebd57009260d954eca82c231185c70401939b7b05a009667783ae8c6b50b5aaec36ba45b26009c8f05b474cb29a64e65206e9e2d36f600d55b6572aff741f92207534f6ecb722da5b535a1b84395b9cee6365f2f6d6c5b5b3025005fba1967ea38e387e8dcec7590148822519cfb60d3c5bd41585f3d9569404d1f958499480ac00dceb6b570671e66fe5f0db79e3e3debb224d19442b68a58bc7fdcf5ce2a69ce87cb832f8d66ecd717b0e1dd97cfd34b97a60b71d5dced38494aa2e0cd6b071f755150076db874d91044b401f7222fcbaaaa8079a30988d6718b01d3ad2e8575bb122541aecb7cd7b9f786dd833fab8d290067bf2ae72893dfb91d1e5c19aeaa2b0ee39188c260d320a590badc8da6561199a5f6fa582205839bc5a64adff7f8c02bede2a004df077e3022ff03f81df8bd4691b8d140fa40000000049454e44ae426082, '2026-09-21 17:05:32', 4, 9),
(16, 'capoeira', 'Instrumento musical de capoeira', '4da7976c91b150fc33a1fec43a143bf8.jpg', 5, 'Disponível', 2, 0x89504e470d0a1a0a0000000d4948445200000140000001400103000000f75e37e900000006504c5445040204fcfefc49bec75e000000097048597300000ec400000ec401952b0e1b000002ab494441546881ed9a316e84500c44ffd71629390247e16870b43d0a47d892628563cfd8809214a9a24803d2ea4bf0b6b1be87197f9afdf26a3778837f02365cb3ff06b3a53db658ecfde14f97fec6c32e0cf628d37376a28dcfe6c40b8fb620c89b34e8b51a03b4972f7bf347278f65bec1d96c1bd609db6c9dbc765ec7c96ef004edd5a62596113787d86037985d8825d4a945015955fba95dc5c0d4f0d8515eceafcb77b11703f38a57dcda66b45f836491a8c7b220bb30ee85481dfc90ff7645efca206529fb6e7f84782fe100dc0f50ab161307e11f21d7f48f16466085a21baa2a0c966a473917f05e471aa43d242aaaaa0bee14717f545d18661b598446e09975d4043b8d63eeb6b444c62edc43ca516359303616624765d720fc15e75585795a4faba008a61e75433803e8f711d51aacb72dca20d347b3b48a67ec701edbac345c14a403888d157a045fede2cde5c1082b0c22cc7b056b308466c4de8bd0ba0d0c6eb220e41a46209c74b55ff981ab5590042f3175bc8c3cca08e45444168c7b113beadec621235c763fa645b220fd231d0089e849baec3d954b18cc141ff63a35bc45557f88299260d611ea04f13e8e347228ad0df6cb9c95097f6424a903b1c32a48821c7944df45de601669542e4c3e4eab2009b20b9796e12c9dd139f918cfb1a22218effcb5e666481f9c2e1e53d71a2b8a82d023d6116719c6f34280383d2c91d204115ad33f42c35da4ca353d30f93065f0326ec5171da5e8d070f8812e0df2088c609e10b2fd70963842c865c1bca855ccaefcf7f835a64882acda9ce12c5399556b7ebc8e579c26188367e3972e74009827968d44874a8379daccc907c76773fa6a9478aa7655065bcd9f1942383e63c2bfc1ac631dfcd439479e254a83ec427aa1b99f47f3637d7a675d182c0de788acd7f7632ce0fbe20034c1df5d377883ff09fc04360a829ec381ebdd0000000049454e44ae426082, '2026-09-23 18:01:29', 3, 5);

-- --------------------------------------------------------

--
-- Table structure for table `setor`
--

CREATE TABLE `setor` (
  `seto_id` int(5) NOT NULL,
  `seto_nome` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `setor`
--

INSERT INTO `setor` (`seto_id`, `seto_nome`) VALUES
(2, 'CIT'),
(3, 'DAPE');

-- --------------------------------------------------------

--
-- Table structure for table `solicitacao`
--

CREATE TABLE `solicitacao` (
  `soli_id` int(5) NOT NULL,
  `soli_dth_retirada` timestamp NULL DEFAULT NULL,
  `soli_dth_devolucao` timestamp NULL DEFAULT NULL,
  `soli_observacao` text NOT NULL,
  `soli_status` varchar(50) NOT NULL,
  `usua_id_solicitante` int(5) NOT NULL,
  `usua_id_coord` int(5) DEFAULT NULL,
  `soli_data_solicitacao` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `solicitacao`
--

INSERT INTO `solicitacao` (`soli_id`, `soli_dth_retirada`, `soli_dth_devolucao`, `soli_observacao`, `soli_status`, `usua_id_solicitante`, `usua_id_coord`, `soli_data_solicitacao`) VALUES
(12, '2026-09-22 04:00:00', '2026-09-25 04:00:00', 'Estou fazendo testess.', 'Devolvido', 21, 9, '2026-09-21 17:07:55'),
(13, NULL, NULL, 'pobre não pode', 'Negada', 21, 9, '2026-09-23 12:20:01'),
(14, '2026-09-23 04:00:00', '2026-09-30 04:00:00', 'alguja coisa', 'Devolvido', 23, 9, '2026-09-23 16:41:38'),
(15, NULL, NULL, '', 'Pendente', 23, NULL, '2026-09-23 16:42:29'),
(16, '2026-09-23 04:00:00', '2026-09-30 04:00:00', 'teste pra baixo estopque', 'Aprovada', 23, 22, '2026-09-23 17:32:57'),
(17, '2026-10-02 04:00:00', '2026-10-22 04:00:00', '', 'Aprovada', 23, 22, '2026-09-23 17:39:39'),
(18, NULL, NULL, '', 'Pendente', 25, NULL, '2026-09-23 22:51:48'),
(19, NULL, NULL, '', 'Cancelada', 21, NULL, '2026-09-23 22:53:58');

--
-- Triggers `solicitacao`
--
DELIMITER $$
CREATE TRIGGER `trg_solicitacao_baixa_estoque` AFTER UPDATE ON `solicitacao` FOR EACH ROW BEGIN
    DECLARE v_produtos    INT DEFAULT 0;
    DECLARE v_atualizados INT DEFAULT 0;

    IF NEW.soli_status = 'Aprovada'
       AND OLD.soli_status NOT IN ('Aprovada', 'Em devolução') THEN

        SELECT COUNT(*) INTO v_produtos
        FROM (
            SELECT prod_id
            FROM item_solicitacao
            WHERE soli_id = NEW.soli_id
            GROUP BY prod_id
            HAVING SUM(item_quantidade) > 0
        ) AS pedidos;

        UPDATE produto p
        INNER JOIN (
            SELECT prod_id, SUM(item_quantidade) AS qtd
            FROM item_solicitacao
            WHERE soli_id = NEW.soli_id
            GROUP BY prod_id
            HAVING SUM(item_quantidade) > 0
        ) AS pedido ON pedido.prod_id = p.prod_id
        SET p.prod_quantidade = p.prod_quantidade - pedido.qtd
        WHERE p.prod_quantidade >= pedido.qtd;

        SET v_atualizados = ROW_COUNT();

        IF v_atualizados < v_produtos THEN
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Estoque insuficiente para aprovar esta solicitação.';
        END IF;

        UPDATE produto p
        INNER JOIN (
            SELECT DISTINCT prod_id
            FROM item_solicitacao
            WHERE soli_id = NEW.soli_id
        ) AS pedido ON pedido.prod_id = p.prod_id
        SET p.prod_status = CASE
            WHEN p.prod_quantidade <= 0                     THEN 'Esgotado'
            WHEN p.prod_quantidade <= p.prod_estoque_minimo THEN 'Estoque baixo'
            ELSE 'Disponível'
        END;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_solicitacao_devolucao_estoque` AFTER UPDATE ON `solicitacao` FOR EACH ROW BEGIN
    IF OLD.soli_status = 'Em devolução' AND NEW.soli_status = 'Devolvido' THEN

        UPDATE produto AS p
        INNER JOIN item_solicitacao AS i ON i.prod_id = p.prod_id
        SET p.prod_quantidade = p.prod_quantidade + i.item_quantidade,
            p.prod_status     = IF(p.prod_status = 'Esgotado', 'Disponível', p.prod_status)
        WHERE i.soli_id = NEW.soli_id;

    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `telefone`
--

CREATE TABLE `telefone` (
  `tele_id` int(5) NOT NULL,
  `tele_numero` varchar(15) NOT NULL,
  `usua_id` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `telefone`
--

INSERT INTO `telefone` (`tele_id`, `tele_numero`, `usua_id`) VALUES
(3, '69 99926-2045', 6),
(5, '(69) 9926-2045', 8),
(6, '69999262045', 9),
(7, '', 10),
(8, '(66) 8956-4785', 12),
(9, '69 99922-3526', 13),
(10, '69 3625-3625', 14),
(12, '36 9865-6598', 16),
(13, '(98) 56321-4789', 17),
(14, '(69) 99926-2045', 18),
(15, '(69) 58231-4777', 19),
(16, '(99) 99999-5585', 20),
(17, '(96) 5857-4123', 21),
(18, '(95) 99118-3012', 22),
(19, '99999999999', 23),
(20, '(69) 99397-9550', 24),
(21, '(59) 56123-1516', 25),
(22, '(41) 55613-2465', 26),
(23, '(15) 61986-5132', 27),
(24, '(15) 65841-3641', 28),
(25, '(16) 54139-8645', 30),
(26, '69999262045', 31),
(27, '(15) 68746-5215', 33),
(28, '15348653416', 34),
(29, '(16) 36416-5163', 35);

-- --------------------------------------------------------

--
-- Table structure for table `token_senha`
--

CREATE TABLE `token_senha` (
  `toke_id` int(11) NOT NULL,
  `toke_token` varchar(64) NOT NULL,
  `usua_id` int(5) NOT NULL,
  `toke_expira` datetime NOT NULL,
  `toke_usado` tinyint(1) NOT NULL DEFAULT 0,
  `toke_criado` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `token_senha`
--

INSERT INTO `token_senha` (`toke_id`, `toke_token`, `usua_id`, `toke_expira`, `toke_usado`, `toke_criado`) VALUES
(1, 'e830efe14d6a093352109c06a634ade7ba9182d7d80ded73efc09938bd225244', 31, '2026-09-24 03:10:49', 1, '2026-09-23 20:10:49'),
(2, 'd8efbd120bebed620ed4a2f921f9b5678ec2abc0a26d5de4bb188ba509ec80c0', 31, '2026-09-24 03:16:02', 1, '2026-09-23 20:16:02');

-- --------------------------------------------------------

--
-- Table structure for table `turma`
--

CREATE TABLE `turma` (
  `turm_id` int(5) NOT NULL,
  `turm_ano` int(2) NOT NULL,
  `turm_curso` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `turma`
--

INSERT INTO `turma` (`turm_id`, `turm_ano`, `turm_curso`) VALUES
(1, 1, 'Técnico em Informática'),
(2, 2, 'Técnico em Informática'),
(3, 3, 'Técnico em Informática');

-- --------------------------------------------------------

--
-- Table structure for table `usuario`
--

CREATE TABLE `usuario` (
  `usua_id` int(5) NOT NULL,
  `usua_nome` varchar(100) NOT NULL,
  `usua_email` varchar(100) NOT NULL,
  `usua_foto` blob DEFAULT NULL,
  `usua_siap` int(6) DEFAULT NULL,
  `usua_status` varchar(20) NOT NULL DEFAULT 'Inativo',
  `usua_matricula` varchar(13) DEFAULT NULL,
  `usua_senha` varchar(225) NOT NULL,
  `func_id` int(5) DEFAULT NULL,
  `seto_id` int(5) DEFAULT NULL,
  `turm_id` int(5) DEFAULT NULL,
  `usua_ultimo_login` datetime DEFAULT NULL,
  `usua_removido` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usuario`
--

INSERT INTO `usuario` (`usua_id`, `usua_nome`, `usua_email`, `usua_foto`, `usua_siap`, `usua_status`, `usua_matricula`, `usua_senha`, `func_id`, `seto_id`, `turm_id`, `usua_ultimo_login`, `usua_removido`) VALUES
(6, 'Mariane Cardoso', 'mariane@estudante.ifro.edu.br', NULL, 123456, 'Ativo', NULL, '$2y$10$BSGPVr/ZKBP7LhM7lJGGmucwcebnksMt9Qyox4baO5CJVxsIPg9vO', 2, 2, NULL, NULL, 0),
(9, 'Mariane Cardoso F M', 'mariane@gmail.com', 0x75395f316136636231386661376130353638382e6a7067, 362541, 'Ativo', NULL, '$2y$10$5q6t9VXlVHndOOumR8jniewLWxDCYiGImfijjhmAzsnUcLQAlWIuu', 1, 2, NULL, '2026-09-23 20:09:00', 0),
(18, 'Mariane Cardoso Firmino Melo', 'marianecfm2024@gmail.com', NULL, 359628, 'Inativo', NULL, '$2y$10$R/dG.tcQ3uc/6eXfZmYAmuSruT8rK8u35bPFW8zt.Yp9pxSlM64Pu', 1, 2, NULL, NULL, 0),
(21, 'Darliane', 'darliane@gmail.com', NULL, 2967412, 'Ativo', NULL, '$2y$10$I3uEnlZDo3E5dOWLe2TxoudonlYH1N9mJ9jvUcxlvsOM5UdZFcZn.', 3, 2, NULL, '2026-09-23 22:53:29', 0),
(22, 'Neemias Ferreira Hitotuzi', 'neemias.hitotuzi@ifro.edu.br', NULL, 12345, 'Ativo', NULL, '$2y$10$KeA6RYLH4oUjetl3h6aeiekCnUx4z6l7j/SQRRGRxyiNWswVjCgxK', 1, 2, NULL, '2026-09-23 16:52:49', 0),
(23, 'eu', 'dar@gmail.com', 0x7532335f333331653331653362663635623138622e6a7067, 456789, 'Ativo', NULL, '$2y$10$LD2lw.Coac8GLK68XQAxHeytoOnWj56qFNyh2Bww83r4ZRgYt80IK', 3, 3, NULL, '2026-09-23 16:59:28', 0),
(24, 'rafel', 'rafel@gmail.com', NULL, NULL, 'Ativo', '1234567890123', '$2y$10$b2jPzCEibkjC7C/cR.lc8.wo90cWo5.HyJbZkTYNjrOCfQrmnZoii', 2, NULL, 1, NULL, 0),
(25, 'primeiro teste', 'primeiro@gmail.com', NULL, 2561354, 'Ativo', NULL, '$2y$10$04.uUkeVuosKL1984aJ.Deu8t2UYrUSsYMH9haDtBI00QQINRHVIW', 3, 3, NULL, '2026-09-23 22:38:08', 0),
(26, 'teste segundo', 'segundo@gmail.com', NULL, NULL, 'Inativo', '1564864121316', '$2y$10$4uVXvahvr7g04G4yZ3n7bupZubhzJ0FM7/FDWKARdparhxU7jdxD6', 2, NULL, 2, NULL, 0),
(27, 'Teste tres', 'tres@gmail.com', NULL, 1653128, 'Inativo', NULL, '$2y$10$HsbOZdMKv3LgZbIp33BTZ.ryR1l5jb.kaKW.SYcoXWdSL/AQlCfTe', 1, 3, NULL, NULL, 0),
(28, 'Teste quatro', 'quatro@gmail.com', NULL, 1564896, 'Inativo', NULL, '$2y$10$/1HslRR.0JoUbGbNqdU1COqF9q8aqCogoesYkTjQ9ZruBm2pfhAdi', 1, 2, NULL, NULL, 0),
(30, 'Teste cinco', 'cinco@gmail.com', NULL, 6513120, 'Inativo', NULL, '$2y$10$dAXiM.v0aoRAmj8DasSza.91oDNyxaiKeTeihk1JV1qYj386Na0y6', 3, 2, NULL, NULL, 0),
(31, 'Mariane Firmino Melo', 'cardosomariane451@gmail.com', 0x7533315f613538356433386536613262346566632e6a7067, 1555641, 'Ativo', NULL, '$2y$10$8DqdBgRbd5hY/uZpkNDiv.zmPXovYGdJRmyFqw2Rl1XdKLIA8PVmy', 1, 2, NULL, '2026-09-23 20:24:56', 0),
(33, 'coordenador primeiro', 'coord@gmail.com', NULL, 156498, 'Ativo', NULL, '$2y$10$kmnYEygv1YSYPoXU1LpfvO1bCuJiP7yCybZVoFH2RmToV4THgmqYK', 1, 2, NULL, NULL, 0),
(34, 'estagiario teste', 'estagiario@gmail.com', 0x7533345f643535323764623630396437636366612e6a7067, NULL, 'Ativo', '1322845631853', '$2y$10$N2BsFY.VV4j/Ok9z4JqTY.CnBBJ8iOCgK/mtYnH3cgvf6QsbOh7rG', 2, NULL, 2, '2026-09-23 23:18:08', 0),
(35, 'teste siete', 'sete@gmail.com', NULL, 1963516, 'Inativo', NULL, '$2y$10$aJEj/91PArn6GK/BDOb7Susy0iOrD4Lbtnsd/Cuu6vixWhRCmvfYi', 3, 3, NULL, NULL, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`cate_id`),
  ADD KEY `ix_categoria_nome` (`cate_nome`) USING BTREE;

--
-- Indexes for table `funcao`
--
ALTER TABLE `funcao`
  ADD PRIMARY KEY (`func_id`),
  ADD KEY `ix_funcao_nome` (`func_nome`);

--
-- Indexes for table `item_solicitacao`
--
ALTER TABLE `item_solicitacao`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `fk_item_solicitacao` (`soli_id`),
  ADD KEY `fk_item_produto` (`prod_id`);

--
-- Indexes for table `localizacao`
--
ALTER TABLE `localizacao`
  ADD PRIMARY KEY (`loca_id`),
  ADD KEY `ix_localizacao_nome` (`loca_nome`);

--
-- Indexes for table `notificacao`
--
ALTER TABLE `notificacao`
  ADD PRIMARY KEY (`noti_id`);

--
-- Indexes for table `notificacao_usuario`
--
ALTER TABLE `notificacao_usuario`
  ADD PRIMARY KEY (`noti_id`,`usua_id`),
  ADD KEY `fk_noti_usuario_usuario` (`usua_id`);

--
-- Indexes for table `produto`
--
ALTER TABLE `produto`
  ADD PRIMARY KEY (`prod_id`),
  ADD KEY `ix_produto_nome` (`prod_nome`),
  ADD KEY `ix_produto_quantidade` (`prod_quantidade`),
  ADD KEY `fk_produto_localizacao` (`loca_id`),
  ADD KEY `fk_produto_categoria` (`cate_id`),
  ADD KEY `ix_produto_data_cadastro` (`prod_data_cadastro`);

--
-- Indexes for table `setor`
--
ALTER TABLE `setor`
  ADD PRIMARY KEY (`seto_id`),
  ADD KEY `ix_setor_nome` (`seto_nome`);

--
-- Indexes for table `solicitacao`
--
ALTER TABLE `solicitacao`
  ADD PRIMARY KEY (`soli_id`),
  ADD KEY `ix_solicitacao_dth_retirada` (`soli_dth_retirada`),
  ADD KEY `ix_solicitacao_dth_devolucao` (`soli_dth_devolucao`),
  ADD KEY `fk_solicitacao_usuario_solicitante` (`usua_id_solicitante`),
  ADD KEY `fk_solicitacao_usuario_coord` (`usua_id_coord`),
  ADD KEY `ix_solicitacao_data_solicitacao` (`soli_data_solicitacao`);

--
-- Indexes for table `telefone`
--
ALTER TABLE `telefone`
  ADD PRIMARY KEY (`tele_id`),
  ADD KEY `ix_telefone_numero` (`tele_numero`),
  ADD KEY `fk_telefone_usuario` (`usua_id`);

--
-- Indexes for table `token_senha`
--
ALTER TABLE `token_senha`
  ADD PRIMARY KEY (`toke_id`),
  ADD KEY `ix_token_valor` (`toke_token`),
  ADD KEY `fk_token_usuario` (`usua_id`);

--
-- Indexes for table `turma`
--
ALTER TABLE `turma`
  ADD PRIMARY KEY (`turm_id`);

--
-- Indexes for table `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`usua_id`),
  ADD UNIQUE KEY `uk_usuario_siap` (`usua_siap`),
  ADD UNIQUE KEY `uk_usuario_matricula` (`usua_matricula`),
  ADD KEY `ix_usuario_nome` (`usua_nome`),
  ADD KEY `fk_usuario_setor` (`seto_id`),
  ADD KEY `fk_usuario_funcao` (`func_id`),
  ADD KEY `ix_usuario_email` (`usua_email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categoria`
--
ALTER TABLE `categoria`
  MODIFY `cate_id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=134;

--
-- AUTO_INCREMENT for table `funcao`
--
ALTER TABLE `funcao`
  MODIFY `func_id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `item_solicitacao`
--
ALTER TABLE `item_solicitacao`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `localizacao`
--
ALTER TABLE `localizacao`
  MODIFY `loca_id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notificacao`
--
ALTER TABLE `notificacao`
  MODIFY `noti_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `produto`
--
ALTER TABLE `produto`
  MODIFY `prod_id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `setor`
--
ALTER TABLE `setor`
  MODIFY `seto_id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `solicitacao`
--
ALTER TABLE `solicitacao`
  MODIFY `soli_id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `telefone`
--
ALTER TABLE `telefone`
  MODIFY `tele_id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `token_senha`
--
ALTER TABLE `token_senha`
  MODIFY `toke_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `turma`
--
ALTER TABLE `turma`
  MODIFY `turm_id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `usuario`
--
ALTER TABLE `usuario`
  MODIFY `usua_id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `item_solicitacao`
--
ALTER TABLE `item_solicitacao`
  ADD CONSTRAINT `fk_item_produto` FOREIGN KEY (`prod_id`) REFERENCES `produto` (`prod_id`),
  ADD CONSTRAINT `fk_item_solicitacao` FOREIGN KEY (`soli_id`) REFERENCES `solicitacao` (`soli_id`);

--
-- Constraints for table `notificacao_usuario`
--
ALTER TABLE `notificacao_usuario`
  ADD CONSTRAINT `fk_noti_usuario_notificacao` FOREIGN KEY (`noti_id`) REFERENCES `notificacao` (`noti_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_noti_usuario_usuario` FOREIGN KEY (`usua_id`) REFERENCES `usuario` (`usua_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `token_senha`
--
ALTER TABLE `token_senha`
  ADD CONSTRAINT `fk_token_usuario` FOREIGN KEY (`usua_id`) REFERENCES `usuario` (`usua_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
