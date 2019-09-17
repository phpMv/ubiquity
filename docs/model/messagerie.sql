-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le :  ven. 22 fév. 2019 à 04:20
-- Version du serveur :  10.1.30-MariaDB
-- Version de PHP :  7.2.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `messagerie`
--
CREATE DATABASE IF NOT EXISTS `messagerie` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `messagerie`;

-- --------------------------------------------------------

--
-- Structure de la table `connection`
--

CREATE TABLE `connection` (
  `id` int(11) NOT NULL,
  `idUser` int(11) NOT NULL,
  `dateCo` datetime NOT NULL,
  `url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `connection`
--

INSERT INTO `connection` (`id`, `idUser`, `dateCo`, `url`) VALUES
(3, 1, '2018-06-04 02:52:12', 'groupes/2'),
(7, 1, '2018-06-04 04:25:13', 'organizations/display/2'),
(8, 1, '2018-06-05 17:00:23', 'organizations/display/2'),
(27, 102, '2018-06-06 20:03:24', 'organizations/display/4'),
(30, 102, '2018-06-06 21:01:51', 'organizations/display/4'),
(31, 102, '2018-06-07 00:47:55', 'groupes'),
(41, 102, '2018-06-10 14:22:16', 'groupes'),
(43, 102, '2018-06-12 01:56:10', 'organizations/display/4'),
(44, 102, '2018-06-12 11:19:15', 'organizations/display/4'),
(45, 102, '2018-06-12 16:23:08', 'connections'),
(46, 102, '2018-06-14 23:34:05', 'Groupes'),
(47, 102, '2018-06-14 23:36:52', 'organizations/display/4'),
(48, 102, '2018-06-15 00:55:58', 'Organizations'),
(49, 102, '2018-06-15 01:05:20', 'connections'),
(50, 102, '2018-06-17 02:57:36', 'connections'),
(51, 102, '2018-06-23 02:47:09', 'connections'),
(52, 1, '2018-06-23 03:24:29', 'organizations/display/2'),
(53, 8, '2019-01-02 12:20:00', 'aucune/');

-- --------------------------------------------------------

--
-- Structure de la table `groupe`
--

CREATE TABLE `groupe` (
  `id` int(11) NOT NULL,
  `name` varchar(65) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `aliases` mediumtext,
  `idOrganization` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `groupe`
--

INSERT INTO `groupe` (`id`, `name`, `email`, `aliases`, `idOrganization`) VALUES
(1, 'Personnels', 'personnels', 'all;', 1),
(2, 'Auditeurs', 'autiteurs', 'etu;stagiaires;', 1),
(3, 'Personnels', 'liste.personnels', NULL, 2),
(4, 'Etudiants', 'liste.etudiants', 'liste.etu;', 2),
(5, 'Enseignants', 'liste.enseignants', 'liste.ens;', 2),
(6, 'Vacataires', 'liste.vacataires', 'liste.vac;', 2);

-- --------------------------------------------------------

--
-- Structure de la table `groupeusers`
--

CREATE TABLE `groupeusers` (
  `idGroupe` int(11) NOT NULL,
  `idUser` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `groupeusers`
--

INSERT INTO `groupeusers` (`idGroupe`, `idUser`) VALUES
(1, 5),
(1, 14),
(1, 15),
(1, 20),
(1, 37),
(1, 40),
(1, 48),
(1, 66),
(1, 69),
(1, 71),
(1, 96),
(2, 1),
(2, 8),
(2, 15),
(2, 34),
(2, 45),
(2, 72),
(2, 75),
(2, 84),
(2, 88),
(2, 89),
(2, 98),
(3, 15),
(3, 19),
(3, 20),
(3, 29),
(3, 45),
(3, 46),
(3, 60),
(3, 62),
(3, 77),
(3, 84),
(3, 89),
(3, 93),
(4, 11),
(4, 17),
(4, 50),
(4, 54),
(4, 57),
(4, 74),
(4, 86),
(4, 88),
(5, 22),
(5, 25),
(5, 34),
(5, 49),
(5, 81),
(5, 83),
(5, 94),
(6, 10),
(6, 11),
(6, 12),
(6, 46),
(6, 47),
(6, 53),
(6, 68),
(6, 74),
(6, 84);

-- --------------------------------------------------------

--
-- Structure de la table `organization`
--

CREATE TABLE `organization` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `domain` varchar(255) NOT NULL,
  `aliases` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `organization`
--

INSERT INTO `organization` (`id`, `name`, `domain`, `aliases`) VALUES
(1, 'Conservatoire National des Arts et Métiers', 'lecnam.net', 'cnam-basse-normandie.fr;cnam.fr'),
(2, 'Université de Caen-Normandie', 'unicaen.fr', NULL),
(3, 'IUT Campus III', 'iutc3.unicaen.fr', 'unicaen.fr'),
(4, 'IUT Lisieux', 'iut.lisieux.unicaen.fr', 'unicaen.fr');

-- --------------------------------------------------------

--
-- Structure de la table `organizationsettings`
--

CREATE TABLE `organizationsettings` (
  `idSettings` int(11) NOT NULL,
  `idOrganization` int(11) NOT NULL,
  `value` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `organizationsettings`
--

INSERT INTO `organizationsettings` (`idSettings`, `idOrganization`, `value`) VALUES
(1, 1, '{{firstname}}{{lastname}}'),
(1, 2, '{{firstname}}.{{lastname}}'),
(2, 4, 'no value');

-- --------------------------------------------------------

--
-- Structure de la table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `name` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `settings`
--

INSERT INTO `settings` (`id`, `name`) VALUES
(6, 'bop'),
(1, 'emailMask'),
(2, 'test'),
(5, 'truc2');

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `firstname` varchar(65) NOT NULL,
  `lastname` varchar(65) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `suspended` tinyint(1) DEFAULT '0',
  `idOrganization` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `firstname`, `lastname`, `email`, `password`, `suspended`, `idOrganization`) VALUES
(1, 'Benjamin', 'Shermans', 'benjamin.sherman@gmail.com', 'OWC09RSW6AE', 0, 2),
(2, 'Acton', 'Carrillo', 'acton.carrillo@gmail.com', 'HIO59BHB8HB', 0, 2),
(3, 'Zorita', 'Rodriguez', 'zorita.rodriguez', 'GNW04ZAO6HP', 0, 2),
(4, 'Henry', 'Beasley', 'henry.beasley', 'UZK64PCR2UN', 0, 2),
(5, 'Kelsey', 'Weber', 'kelsey.weber', 'CGR68MCJ3SN', 0, 2),
(6, 'Olympia', 'Huber', 'olympia.huber', 'HNP83HAE0FM', 0, 4),
(7, 'Carolyn', 'Pace', 'carolyn.pace', 'YIX45RKH5MR', 0, 2),
(8, 'Levi', 'Bishop', 'levi.bishop', 'IKY43FHZ6VT', 1, 2),
(9, 'Wyatt', 'Higgins', 'wyatt.higgins', 'ZIO64ZJU9HY', 0, 1),
(10, 'Lionel', 'Mccray', 'lionel.mccray', 'VKW78FSB6PJ', 0, 2),
(11, 'Jeremy', 'Bryan', 'jeremy.bryan', 'TTV64OAQ9AN', 0, 2),
(12, 'Ava', 'Pollard', 'ava.pollard', 'ZKV02QCQ5GZ', 0, 2),
(13, 'Jane', 'Leon', 'jane.leon', 'AQD96ABI2WQ', 0, 1),
(14, 'Baxter', 'Wise', 'baxter.wise', 'PJG36JAP3GU', 0, 2),
(15, 'Cyrus', 'Rosario', 'cyrus.rosario', 'ZDU33RYL2AK', 0, 2),
(16, 'Amos', 'Travis', 'amos.travis', 'UIP43SJH2IK', 0, 4),
(17, 'Whitney', 'Hale', 'whitney.hale', 'PCA69ZZG9HD', 0, 2),
(18, 'Fletcher', 'Fischer', 'fletcher.fischer', 'BJM28BRO9SX', 0, 2),
(19, 'Rhiannon', 'Dickerson', 'rhiannon.dickerson', 'ZUM07JRG0JH', 0, 2),
(20, 'Maggy', 'Weber', 'maggy.weber', 'MWW53SWA2WH', 0, 1),
(21, 'Kyle', 'Craig', 'kyle.craig', 'KAD56XAM2KY', 0, 2),
(22, 'Burton', 'Sanford', 'burton.sanford', 'LYO83OLV8TF', 0, 2),
(23, 'Cooper', 'Callahan', 'cooper.callahan', 'WKF09LDB4AF', 0, 2),
(24, 'Urielle', 'Moreno', 'urielle.moreno', 'DTB04DDU0KV', 0, 2),
(25, 'Aristotle', 'Reese', 'aristotle.reese', 'QPN11PVQ7TR', 0, 2),
(26, 'Camille', 'Blevins', 'camille.blevins', 'CLQ63RXB3VB', 0, 2),
(27, 'Colleen', 'Blevins', 'colleen.blevins', 'EOO51HIZ0PG', 0, 2),
(28, 'Martina', 'Holder', 'martina.holder', 'QZW21CRI9ZY', 0, 4),
(29, 'Allistair', 'Leon', 'allistair.leon', 'ZAW47BFF3DM', 0, 1),
(30, 'Driscoll', 'Dickson', 'driscoll.dickson', 'YNN51MQQ4II', 0, 2),
(31, 'Magee', 'Marquez', 'magee.marquez', 'SHX59YVP7XU', 0, 2),
(32, 'Angelica', 'Serrano', 'angelica.serrano', 'XRJ73PFL2WQ', 0, 2),
(33, 'Nomlanga', 'Bowen', 'nomlanga.bowen', 'SSH13DSE6TU', 0, 2),
(34, 'Gil', 'Bright', 'gil.bright', 'BEH66TUK0UL', 1, 2),
(35, 'Alvin', 'Hatfield', 'alvin.hatfield', 'MBO67IAK8UM', 0, 1),
(36, 'Curran', 'Knowles', 'curran.knowles', 'QNW26QIE9RW', 0, 2),
(37, 'Charissa', 'David', 'charissa.david', 'RTM13TXT9AK', 0, 2),
(38, 'Lev', 'Kennedy', 'lev.kennedy', 'EYG45KQT2IU', 0, 2),
(39, 'Lynn', 'Jacobs', 'lynn.jacobs', 'ZHW67JUR3DI', 0, 2),
(40, 'Lois', 'Wiley', 'lois.wiley', 'SIU35PZI0BT', 0, 2),
(41, 'Deborah', 'Wheeler', 'deborah.wheeler', 'WUD38KWN1LI', 0, 2),
(42, 'Renee', 'Olson', 'renee.olson', 'BXT76DJI2KA', 0, 2),
(43, 'Philip', 'English', 'philip.english', 'OLM46WUL5QC', 0, 2),
(44, 'Kevin', 'Johns', 'kevin.johns', 'IKJ83UQO4LP', 0, 2),
(45, 'Jane', 'Holden', 'jane.holden', 'BVG22IMJ7UO', 0, 2),
(46, 'Kendall', 'Collier', 'kendall.collier', 'PPQ01KRW4QU', 0, 1),
(47, 'Solomon', 'Tucker', 'solomon.tucker', 'LLI65CKR1FM', 0, 2),
(48, 'Richard', 'Higgins', 'richard.higgins', 'QXP09FYD8IJ', 0, 2),
(49, 'Carly', 'David', 'carly.david', 'ORG15ORK7NR', 0, 2),
(50, 'Ursa', 'Barry', 'ursa.barry', 'SDJ66QPG1VS', 0, 2),
(51, 'Steven', 'Norman', 'steven.norman', 'HVH32HVT8MR', 0, 1),
(52, 'Joan', 'Hatfield', 'joan.hatfield', 'RNF84ENW1FC', 0, 1),
(53, 'Simon', 'Pacheco', 'simon.pacheco', 'JTD92HBV6LY', 0, 2),
(54, 'Price', 'Sears', 'price.sears', 'ARD22CYJ7DJ', 0, 2),
(55, 'Melodie', 'Burton', 'melodie.burton', 'MJB89OEN9YD', 0, 2),
(56, 'Amela', 'Burks', 'amela.burks', 'COY70COZ0HP', 0, 2),
(57, 'Melvin', 'Jacobs', 'melvin.jacobs', 'ERJ13FFZ9IS', 0, 2),
(58, 'Ivory', 'Morin', 'ivory.morin', 'VCA67DEG0LI', 0, 2),
(59, 'Quentin', 'Clements', 'quentin.clements', 'BCU26BTI1ZC', 0, 2),
(60, 'Colton', 'Mcintyre', 'colton.mcintyre', 'DPM10ODN4MK', 0, 2),
(61, 'Talon', 'Boyle', 'talon.boyle', 'EAC10BKA9FZ', 0, 2),
(62, 'Kyra', 'Rocha', 'kyra.rocha', 'VJW60ULA7YW', 0, 2),
(63, 'Stella', 'Cole', 'stella.cole', 'RJH68PRO4SW', 0, 2),
(64, 'Brock', 'Lucas', 'brock.lucas', 'GZI54FAF2QV', 0, 2),
(65, 'Lila', 'Lewis', 'lila.lewis', 'PMM40BGE7EZ', 0, 2),
(66, 'Hu', 'Key', 'hu.key', 'MHN02DRZ2QK', 0, 2),
(67, 'Kuame', 'James', 'kuame.james', 'PAN51UII5EK', 0, 2),
(68, 'Xenos', 'Padilla', 'xenos.padilla', 'RSO17VKK9PN', 0, 2),
(69, 'Sade', 'Owens', 'sade.owens', 'XIH02LWO2MI', 0, 2),
(70, 'Ivor', 'Logan', 'ivor.logan', 'BJQ09KDN8WK', 0, 1),
(71, 'Eleanor', 'Cabrera', 'eleanor.cabrera', 'ECW85CUY3ZR', 0, 2),
(72, 'Clare', 'Macdonald', 'clare.macdonald', 'VPQ45ENN0NH', 0, 2),
(73, 'Malcolm', 'Burke', 'malcolm.burke', 'PLO48UGZ5XA', 0, 2),
(74, 'Kitra', 'Delaney', 'kitra.delaney', 'SQU50ZAG7OI', 0, 2),
(75, 'Barrett', 'Holcomb', 'barrett.holcomb', 'SBA21QWP2YR', 0, 2),
(76, 'Haley', 'Reed', 'haley.reed', 'GPK80XRK7JZ', 0, 2),
(77, 'Grant', 'Townsend', 'grant.townsend', 'YAL32HDT0UA', 0, 2),
(78, 'Derek', 'Hays', 'derek.hays', 'OVD66OAJ2UH', 0, 2),
(79, 'Keiko', 'Benson', 'keiko.benson', 'HPV72ZLP6MQ', 0, 2),
(80, 'Mara', 'Benjamin', 'mara.benjamin', 'XCQ79LJC5LQ', 0, 2),
(81, 'Hyacinth', 'Finley', 'hyacinth.finley', 'UIV27LYU6SW', 0, 1),
(82, 'Ramona', 'Solomon', 'ramona.solomon', 'MYJ31VYH0GV', 0, 2),
(83, 'Ezra', 'Anderson', 'ezra.anderson', 'NKN68ETH4OM', 0, 2),
(84, 'Alana', 'Lambert', 'alana.lambert', 'IXT00JND7YK', 0, 2),
(85, 'Lillian', 'Wright', 'lillian.wright', 'LBJ92OFT4IT', 0, 2),
(86, 'Brenna', 'Trevino', 'brenna.trevino', 'QJO38DEX1TM', 0, 2),
(87, 'Madeson', 'Larsen', 'madeson.larsen', 'QFL74NXO4UR', 0, 1),
(88, 'Kenyon', 'Hinton', 'kenyon.hinton', 'OJN19NDN7HR', 0, 2),
(89, 'Vera', 'Powers', 'vera.powers', 'VIR06MOZ2JV', 0, 2),
(90, 'Natalie', 'Brown', 'natalie.brown', 'YKD61DCY5IF', 0, 2),
(91, 'Claudia', 'Savage', 'claudia.savage', 'KFN84UVA1SG', 0, 2),
(92, 'Lucas', 'Bush', 'lucas.bush', 'ZFS09NFU7DO', 0, 2),
(93, 'Kenyon', 'Neal', 'kenyon.neal', 'OWG74JRY9KV', 0, 2),
(94, 'Tyrone', 'Hurley', 'tyrone.hurley', 'GHE80GQD6EU', 0, 2),
(95, 'Maris', 'Mosley', 'maris.mosley', 'NEX48LLK6CD', 0, 1),
(96, 'Elaine', 'Norton', 'elaine.norton', 'STY09EPG0GD', 0, 2),
(97, 'Vernon', 'Tanner', 'vernon.tanner', 'VMZ45SGA2NV', 0, 2),
(98, 'Brennan', 'Shaw', 'brennan.shaw', 'XMG63KHO3JY', 0, 2),
(99, 'Victoria', 'Whitehead', 'victoria.whitehead', 'LAF73KHK8FZ', 1, 2),
(100, 'Allistair', 'Johnson', 'allistair.johnson', 'RUN58DYH4RN', 0, 2),
(102, 'Jean-Christophe', 'HERON', 'myaddressmailgmail.com', '0000', 1, 4);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `connection`
--
ALTER TABLE `connection`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_co_fk` (`idUser`);

--
-- Index pour la table `groupe`
--
ALTER TABLE `groupe`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name_orga_UNIQUE` (`name`,`idOrganization`) USING BTREE,
  ADD UNIQUE KEY `email_orga_UNIQUE` (`email`,`idOrganization`) USING BTREE,
  ADD KEY `fk_groupe_organization1_idx` (`idOrganization`);

--
-- Index pour la table `groupeusers`
--
ALTER TABLE `groupeusers`
  ADD PRIMARY KEY (`idGroupe`,`idUser`),
  ADD KEY `fk_groupe_has_user_user1_idx` (`idUser`),
  ADD KEY `fk_groupe_has_user_groupe_idx` (`idGroupe`);

--
-- Index pour la table `organization`
--
ALTER TABLE `organization`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name_UNIQUE` (`name`),
  ADD UNIQUE KEY `domain_UNIQUE` (`domain`);

--
-- Index pour la table `organizationsettings`
--
ALTER TABLE `organizationsettings`
  ADD PRIMARY KEY (`idSettings`,`idOrganization`),
  ADD KEY `fk_settings_has_organization_organization1_idx` (`idOrganization`),
  ADD KEY `fk_settings_has_organization_settings1_idx` (`idSettings`);

--
-- Index pour la table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name_UNIQUE` (`name`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_organization1_idx` (`idOrganization`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `connection`
--
ALTER TABLE `connection`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT pour la table `groupe`
--
ALTER TABLE `groupe`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT pour la table `organization`
--
ALTER TABLE `organization`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `connection`
--
ALTER TABLE `connection`
  ADD CONSTRAINT `connection_ibfk_1` FOREIGN KEY (`idUser`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `groupe`
--
ALTER TABLE `groupe`
  ADD CONSTRAINT `fk_groupe_organization1` FOREIGN KEY (`idOrganization`) REFERENCES `organization` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `groupeusers`
--
ALTER TABLE `groupeusers`
  ADD CONSTRAINT `fk_groupe_has_user_groupe` FOREIGN KEY (`idGroupe`) REFERENCES `groupe` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_groupe_has_user_user1` FOREIGN KEY (`idUser`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `organizationsettings`
--
ALTER TABLE `organizationsettings`
  ADD CONSTRAINT `fk_settings_has_organization_organization1` FOREIGN KEY (`idOrganization`) REFERENCES `organization` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_settings_has_organization_settings1` FOREIGN KEY (`idSettings`) REFERENCES `settings` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `fk_user_organization1` FOREIGN KEY (`idOrganization`) REFERENCES `organization` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
