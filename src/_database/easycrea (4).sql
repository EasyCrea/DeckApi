-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 16 oct. 2024 à 16:16
-- Version du serveur : 10.4.28-MariaDB
-- Version de PHP : 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `easycrea`
--

-- --------------------------------------------------------

--
-- Structure de la table `administrateur`
--

CREATE TABLE `administrateur` (
  `id_administrateur` int(11) NOT NULL,
  `ad_email_admin` varchar(255) NOT NULL,
  `mdp_admin` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `administrateur`
--

INSERT INTO `administrateur` (`id_administrateur`, `ad_email_admin`, `mdp_admin`) VALUES
(1, 'eliot.pouplier@gmail.com', 'admin'),
(2, 'admin@gmail.com', '$2y$10$1kosz26S1zzhacHjfgHCkuGWn9x9pFf1//TDr6nbyVILahYCqO996');

-- --------------------------------------------------------

--
-- Structure de la table `carte`
--

CREATE TABLE `carte` (
  `id_carte` int(11) NOT NULL,
  `id_deck` int(11) NOT NULL,
  `texte_carte` text NOT NULL,
  `valeurs_choix1` varchar(255) NOT NULL,
  `valeurs_choix2` varchar(255) NOT NULL,
  `date_soumission` date NOT NULL,
  `ordre_soumission` int(11) NOT NULL,
  `id_createur` int(11) DEFAULT NULL,
  `id_administrateur` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `carte`
--

INSERT INTO `carte` (`id_carte`, `id_deck`, `texte_carte`, `valeurs_choix1`, `valeurs_choix2`, `date_soumission`, `ordre_soumission`, `id_createur`, `id_administrateur`) VALUES
(5, 5, 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam interdum urna at enim condimentum, et semper quam tristique. In nec facilisis urna, ac aliquam est. Duis non eros sem. Nulla tempor mi in massa pretium finibus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.', '-6,5', '2,-1', '2024-10-15', 1, NULL, NULL),
(6, 5, 'Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', '-3,4', '1,0', '2024-10-15', 2, NULL, NULL),
(18, 5, 'toto', '1,1', '1,1', '2024-10-15', 4, 2, NULL),
(20, 5, 'tonton', '3,3', '3,3', '2024-10-16', 5, 1, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `carte_aleatoire`
--

CREATE TABLE `carte_aleatoire` (
  `id_carte_aleatoire` int(11) NOT NULL,
  `id_deck` int(11) NOT NULL,
  `id_createur` int(11) NOT NULL,
  `id_carte` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `carte_aleatoire`
--

INSERT INTO `carte_aleatoire` (`id_carte_aleatoire`, `id_deck`, `id_createur`, `id_carte`) VALUES
(1, 5, 1, 6),
(2, 5, 2, 5),
(3, 5, 3, 5),
(4, 5, 4, 18);

-- --------------------------------------------------------

--
-- Structure de la table `createur`
--

CREATE TABLE `createur` (
  `id_createur` int(11) NOT NULL,
  `nom_createur` varchar(255) NOT NULL,
  `ad_email_createur` varchar(255) NOT NULL,
  `mdp_createur` varchar(255) NOT NULL,
  `genre` enum('Homme','Femme','Autre') NOT NULL,
  `ddn` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `createur`
--

INSERT INTO `createur` (`id_createur`, `nom_createur`, `ad_email_createur`, `mdp_createur`, `genre`, `ddn`) VALUES
(1, 'Eliot Pouplier', 'eliot.pouplier@gmail.com', '$2y$10$EdVKg1ijEp6ZeNnL4AhdBerrOYY93fdUOgv5bqXX/hJxSiGvxs8Wu', 'Homme', '2004-05-09'),
(2, 'test', 'test@gmail.com', '$2y$10$TeFNPA0daUF3tznkTlN6Cu1rzuy6N3C4FgCaaAacEB1HD68PZZZwa', 'Homme', '2024-10-02'),
(3, 'tata', 'tata@gmail.com', '$2y$10$g5YoerSydSQH9x/uNyex6e0jyiENPjLcVYmxYC/PCNRxG3OC03Cnq', 'Homme', '2024-10-16'),
(4, 'titi', 'titi@gmail.com', '$2y$10$FIcBdJo1hZVidDXNCNJn.uBTHny/iJU31C3nIRhOEwhErS7ktr3Wu', 'Homme', '2024-10-17');

-- --------------------------------------------------------

--
-- Structure de la table `deck`
--

CREATE TABLE `deck` (
  `id_deck` int(11) NOT NULL,
  `titre_deck` varchar(255) NOT NULL,
  `date_debut_deck` date NOT NULL,
  `date_fin_deck` date NOT NULL,
  `nb_cartes` int(11) NOT NULL DEFAULT 0,
  `nb_jaime` int(11) NOT NULL DEFAULT 0,
  `live` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `deck`
--

INSERT INTO `deck` (`id_deck`, `titre_deck`, `date_debut_deck`, `date_fin_deck`, `nb_cartes`, `nb_jaime`, `live`) VALUES
(5, 'Deck n°1', '2024-10-15', '2024-10-17', 5, 0, 1);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `administrateur`
--
ALTER TABLE `administrateur`
  ADD PRIMARY KEY (`id_administrateur`),
  ADD UNIQUE KEY `ad_email_admin` (`ad_email_admin`);

--
-- Index pour la table `carte`
--
ALTER TABLE `carte`
  ADD PRIMARY KEY (`id_carte`),
  ADD KEY `id_deck` (`id_deck`),
  ADD KEY `carte_ibfk_2` (`id_createur`),
  ADD KEY `carte_ibfk_3` (`id_administrateur`);

--
-- Index pour la table `carte_aleatoire`
--
ALTER TABLE `carte_aleatoire`
  ADD PRIMARY KEY (`id_carte_aleatoire`),
  ADD UNIQUE KEY `unique_deck_creator` (`id_deck`,`id_createur`),
  ADD KEY `id_carte` (`id_carte`);

--
-- Index pour la table `createur`
--
ALTER TABLE `createur`
  ADD PRIMARY KEY (`id_createur`),
  ADD UNIQUE KEY `ad_email_createur` (`ad_email_createur`);

--
-- Index pour la table `deck`
--
ALTER TABLE `deck`
  ADD PRIMARY KEY (`id_deck`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `administrateur`
--
ALTER TABLE `administrateur`
  MODIFY `id_administrateur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `carte`
--
ALTER TABLE `carte`
  MODIFY `id_carte` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT pour la table `carte_aleatoire`
--
ALTER TABLE `carte_aleatoire`
  MODIFY `id_carte_aleatoire` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `createur`
--
ALTER TABLE `createur`
  MODIFY `id_createur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `deck`
--
ALTER TABLE `deck`
  MODIFY `id_deck` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `carte`
--
ALTER TABLE `carte`
  ADD CONSTRAINT `carte_ibfk_1` FOREIGN KEY (`id_deck`) REFERENCES `deck` (`id_deck`) ON DELETE CASCADE,
  ADD CONSTRAINT `carte_ibfk_2` FOREIGN KEY (`id_createur`) REFERENCES `createur` (`id_createur`) ON DELETE SET NULL,
  ADD CONSTRAINT `carte_ibfk_3` FOREIGN KEY (`id_administrateur`) REFERENCES `administrateur` (`id_administrateur`) ON DELETE SET NULL;

--
-- Contraintes pour la table `carte_aleatoire`
--
ALTER TABLE `carte_aleatoire`
  ADD CONSTRAINT `carte_aleatoire_ibfk_1` FOREIGN KEY (`id_deck`) REFERENCES `deck` (`id_deck`) ON DELETE CASCADE,
  ADD CONSTRAINT `carte_aleatoire_ibfk_2` FOREIGN KEY (`id_carte`) REFERENCES `carte` (`id_carte`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
