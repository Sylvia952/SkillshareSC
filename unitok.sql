-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : ven. 07 nov. 2025 à 08:18
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `unitok`
--

-- --------------------------------------------------------

--
-- Structure de la table `education`
--

CREATE TABLE `education` (
  `id` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `contenu` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `auteur` varchar(100) DEFAULT NULL,
  `date_publication` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `education`
--

INSERT INTO `education` (`id`, `titre`, `contenu`, `image`, `auteur`, `date_publication`) VALUES
(1, 'L’importance de l’éducation sexuelle', 'L’éducation sexuelle aide les jeunes à comprendre leur corps...', 'assets/img/education1.jpg', 'Dr. Sonia K.', '2025-11-06 09:14:24'),
(2, 'Prévention des IST à l’université', 'Les infections sexuellement transmissibles peuvent être évitées grâce...', 'assets/img/education2.jpg', 'Service Santé', '2025-11-06 09:14:24'),
(3, 'Santé mentale et vie sociale', 'Apprendre à équilibrer vie académique et personnelle...', 'assets/img/education3.jpg', 'Campus Life Team', '2025-11-06 09:14:24');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `matricule` int(11) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `filiere` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mdp` varchar(255) NOT NULL,
  `username` varchar(50) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `matricule`, `last_name`, `first_name`, `filiere`, `email`, `mdp`, `username`, `profile_picture`, `bio`, `created_at`) VALUES
(3, 0, '0', '', '', 'sylviahoundjo9@gmail.com', '$2y$10$.1ohJOPBu3IvK1l1fcJSO.rIDoPuDmjygz4tZCTd9AsMsSTT1PkyC', '', NULL, NULL, '2025-11-06 08:47:00'),
(4, 2147483647, 'KODJO', 'Jean-Eude', 'SIL', 'jean9@gmail.com', '$2y$10$Fh6TeJdcg0RoMDD3hiWCXuBUj51UWZEt79ujnvCiLT.jA/bcmT9ci', 'Jean100', 'uploads/profiles/profile_690d23ebbd0f5.jpg', NULL, '2025-11-06 22:40:44'),
(5, 2147483647, 'HOUNDJO', 'AHOUEFA DJENOUGNUIE SYLVIA', 'SSRI', 'dhash933@gmail.com', '$2y$10$MiLqDf5Kj2zhOSZzRNynjO.LlJnvkOn/dq8Ng9neYMi47j.sD1agq', 'Dhash9', 'uploads/profiles/profile_690d9af3baebe.jpg', NULL, '2025-11-07 07:08:36');

-- --------------------------------------------------------

--
-- Structure de la table `videos`
--

CREATE TABLE `videos` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `fichier` varchar(255) NOT NULL,
  `date_publication` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `videos`
--

INSERT INTO `videos` (`id`, `user_id`, `titre`, `description`, `fichier`, `date_publication`) VALUES
(6, 3, 'me', 'pub', 'assets/videos/video_690c60c772961.mp4', '2025-11-06 08:48:07');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `education`
--
ALTER TABLE `education`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `education`
--
ALTER TABLE `education`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `videos`
--
ALTER TABLE `videos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `videos`
--
ALTER TABLE `videos`
  ADD CONSTRAINT `videos_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
