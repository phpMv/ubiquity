-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le :  mar. 30 juil. 2019 à 18:38
-- Version du serveur :  10.1.38-MariaDB
-- Version de PHP :  7.3.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `projects`
--
CREATE DATABASE IF NOT EXISTS `projects` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `projects`;

-- --------------------------------------------------------

--
-- Structure de la table `developer`
--

CREATE TABLE `developer` (
  `id` int(11) NOT NULL,
  `identity` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `developer`
--

INSERT INTO `developer` (`id`, `identity`) VALUES
(6, 'Evan YOU'),
(7, 'Fabien Potencier'),
(13, 'jcH'),
(8, 'John Resig'),
(9, 'Kris Selden'),
(33, 'nobody'),
(12, 'ploc'),
(11, 'plouc'),
(35, 'pluc'),
(34, 'yazz'),
(10, 'Yehuda Katz');

-- --------------------------------------------------------

--
-- Structure de la table `project`
--

CREATE TABLE `project` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `startDate` date NOT NULL,
  `dueDate` date NOT NULL,
  `idOwner` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `project`
--

INSERT INTO `project` (`id`, `name`, `description`, `startDate`, `dueDate`, `idOwner`) VALUES
(1, 'Boards-EmberJS_4', 'Gestion de projet SCRUM avec EmberJS ', '2018-02-12', '2019-02-03', 6),
(2, 'phpMyBenchmarks', 'Benchmarks PHP', '2018-02-20', '2018-03-21', 9),
(3, 'Cloud_66_for_Rails', 'Build, deploy, and maintain your Rails apps on any cloud or server', '2017-07-22', '2017-08-01', NULL),
(4, 'Codecov', 'Group, merge, archive and compare coverage reports', '2017-10-09', '2018-03-13', NULL),
(5, 'ZenHub', 'Agile Task Boards, Epics, Estimates and Reports, all within GitHub\'s UI', '2016-11-14', '2018-03-13', NULL),
(6, 'EmberJS', '', '2018-03-01', '2018-03-01', 10),
(7, 'MongoDb', 'no SQL database', '2018-03-01', '2018-03-24', 8),
(15, 'Codacy', 'Automated code reviews to help developers ship better software, faster', '2018-03-20', '2018-03-31', 9),
(16, 'jQuery', 'jQuery javascript library', '0000-00-00', '0000-00-00', 8),
(17, 'Scripts', '', '0000-00-00', '0000-00-00', 13),
(18, 'essai', '', '0000-00-00', '0000-00-00', 7);

-- --------------------------------------------------------

--
-- Structure de la table `step`
--

CREATE TABLE `step` (
  `id` int(11) NOT NULL,
  `title` varchar(50) NOT NULL,
  `sequence` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `step`
--

INSERT INTO `step` (`id`, `title`, `sequence`) VALUES
(1, 'Todo', 1),
(4, 'Done', 3),
(5, 'In progress', 2);

-- --------------------------------------------------------

--
-- Structure de la table `story`
--

CREATE TABLE `story` (
  `id` int(11) NOT NULL,
  `code` varchar(10) NOT NULL,
  `descriptif` text,
  `tags` text,
  `step` varchar(50) DEFAULT NULL,
  `idDeveloper` int(11) DEFAULT NULL,
  `idProject` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `story`
--

INSERT INTO `story` (`id`, `code`, `descriptif`, `tags`, `step`, `idDeveloper`, `idProject`) VALUES
(1, 'BO1', 'Lister les projets', '1,2,3', 'In progress', NULL, 1),
(2, 'BO2', 'Lister les stories', '1,4', '', 8, 1);

-- --------------------------------------------------------

--
-- Structure de la table `tag`
--

CREATE TABLE `tag` (
  `id` int(11) NOT NULL,
  `title` varchar(30) NOT NULL,
  `color` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `tag`
--

INSERT INTO `tag` (`id`, `title`, `color`) VALUES
(1, 'UC', 'violet'),
(2, 'Bug', 'red'),
(3, 'Todo', 'blue'),
(4, 'In progress', 'teal'),
(5, 'Help wanted', 'green');

-- --------------------------------------------------------

--
-- Structure de la table `task`
--

CREATE TABLE `task` (
  `id` int(11) NOT NULL,
  `content` text NOT NULL,
  `idStory` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `task`
--

INSERT INTO `task` (`id`, `content`, `idStory`) VALUES
(1, 'Analyse', 1),
(2, 'Maquettage', 1),
(3, 'Lib Task (Analyse fonctionnelle)', 1),
(4, 'New task', 1);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `developer`
--
ALTER TABLE `developer`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `identity` (`identity`);

--
-- Index pour la table `project`
--
ALTER TABLE `project`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `projectName` (`name`),
  ADD KEY `idOwner` (`idOwner`);

--
-- Index pour la table `step`
--
ALTER TABLE `step`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `title` (`title`);

--
-- Index pour la table `story`
--
ALTER TABLE `story`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idDeveloper` (`idDeveloper`),
  ADD KEY `idProject` (`idProject`);

--
-- Index pour la table `tag`
--
ALTER TABLE `tag`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `title` (`title`);

--
-- Index pour la table `task`
--
ALTER TABLE `task`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idStory` (`idStory`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `developer`
--
ALTER TABLE `developer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT pour la table `project`
--
ALTER TABLE `project`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT pour la table `step`
--
ALTER TABLE `step`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `story`
--
ALTER TABLE `story`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `tag`
--
ALTER TABLE `tag`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `task`
--
ALTER TABLE `task`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `project`
--
ALTER TABLE `project`
  ADD CONSTRAINT `project_ibfk_1` FOREIGN KEY (`idOwner`) REFERENCES `developer` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `story`
--
ALTER TABLE `story`
  ADD CONSTRAINT `story_ibfk_1` FOREIGN KEY (`idDeveloper`) REFERENCES `developer` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `story_ibfk_2` FOREIGN KEY (`idProject`) REFERENCES `project` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `task`
--
ALTER TABLE `task`
  ADD CONSTRAINT `task_ibfk_1` FOREIGN KEY (`idStory`) REFERENCES `story` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
