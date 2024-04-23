-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sty 23, 2024 at 10:36 AM
-- Wersja serwera: 10.4.32-MariaDB
-- Wersja PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `database`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(512) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `email`, `password`) VALUES
(1, 'admin@gmail.com', '$2y$10$SGB0o83qQVmPyLKwi8H/e.3eu0.I7BbgutEpfQrLXGXWGbIZP5gRm');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `apphistory`
--

CREATE TABLE `apphistory` (
  `app_id` int(11) NOT NULL,
  `date` datetime DEFAULT NULL,
  `fac_id` int(11) DEFAULT NULL,
  `app_type` varchar(255) DEFAULT NULL,
  `app_price` int(11) DEFAULT NULL,
  `client_id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `apphistory`
--

INSERT INTO `apphistory` (`app_id`, `date`, `fac_id`, `app_type`, `app_price`, `client_id`, `emp_id`) VALUES
(1, '2024-01-19 00:23:15', 1, 'da', 123, 8, 2),
(2, '2024-01-09 00:23:15', 1, 'daw', 131, 8, 3),
(3, '2024-01-16 01:21:04', 1, 'ad', 1232, 8, 2),
(4, '2024-01-23 00:23:15', 1, 'ad', 131, 8, 4),
(5, '2024-01-03 00:04:00', 1, 'wizytaaaa', 213212, 8, 2),
(6, '2024-01-03 03:48:00', 1, 'yrtfb', 213, 9, 2),
(7, '2024-01-09 01:35:00', 1, 'ad', 123, 8, 2),
(8, '2024-01-02 03:26:00', 1, 'dadwa', 13, 8, 3),
(9, '2024-01-02 03:26:00', 1, 'dadwa', 13, 9, 3),
(10, '2024-01-02 03:26:00', 1, 'dadwa', 13, 8, 3),
(11, '2024-01-02 03:26:00', 1, 'dadwa', 13, 9, 3),
(12, '2024-01-02 03:26:00', 1, 'dadwa', 13, 9, 3),
(13, '2024-01-02 03:26:00', 1, 'dadwa', 13, 9, 3),
(14, '2024-01-02 03:26:00', 1, 'dadwa', 13, 8, 3),
(15, '2024-01-17 23:24:00', 1, 'wizyta', 1337, 8, 4);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `appointments`
--

CREATE TABLE `appointments` (
  `app_id` int(11) NOT NULL,
  `date` datetime DEFAULT NULL,
  `emp_id` int(11) DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `fac_id` int(11) DEFAULT NULL,
  `app_type` varchar(255) DEFAULT NULL,
  `price` decimal(11,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`app_id`, `date`, `emp_id`, `client_id`, `fac_id`, `app_type`, `price`) VALUES
(109, '0000-00-00 00:00:00', 2, NULL, NULL, 'dadwadasvjtfg', 123.00),
(156, '2024-01-24 06:35:00', 2, 10, 1, 'konsultacja', 100.02),
(157, '2024-01-25 06:35:00', 2, NULL, 1, 'konsultacja', 100.00),
(163, '2024-01-24 14:20:00', 3, NULL, 1, 'konsultacja', 120.99),
(164, '2024-01-26 14:30:00', 2, NULL, 1, 'konsultacja', 99.99),
(165, '2024-01-24 15:30:00', 3, NULL, 1, 'konsultacja', 120.00);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `clients`
--

CREATE TABLE `clients` (
  `name` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `client_id` int(11) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`name`, `lastname`, `client_id`, `email`, `password`) VALUES
('szefito', 'bandito', 8, 'k1@gmail.com', '$2y$10$ynEgvNfYmFPMwE8F2RoSG.CsgYuIIBk9G/S7esEOnj/udfRigDuSG'),
('nazwa', 'klienta', 9, 'klient2@gmail.com', '$2y$10$HJCwXzly/YQa1kDyAugnCuq7rUcaJomNt5GPKVnaPfX3H89mphE8C'),
('imie', 'nazwisko', 10, 'k3@gmail.com', '$2y$10$pEJQG8ihLegh5Sb/2vu0PevuuK.VbfMTo7P2lFQ8FdQB9ZbXzSJbu');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `employees`
--

CREATE TABLE `employees` (
  `name` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `specialization` varchar(255) DEFAULT NULL,
  `emp_id` int(11) NOT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`name`, `lastname`, `email`, `specialization`, `emp_id`, `password`) VALUES
('mac', 'kra', 'mail@gmail.com', 'p1', 2, '$2y$10$3A.GtUj3tdXr1m9K4Tb1W.yG0QsTFAMcxPeH34Zvct5jTcsrsh3Ri'),
('pra', 'cownik', 'p@gmail.com', 'szef', 3, '$2y$10$Y4qhcP1ceMYxoLFNkL4uN.SVObooZ63CSslGrZGtNdm9gq7MkqxrC'),
('prac2', 'ownik2', 'p2@gmail.com', 'szeff', 4, '$2y$10$BCCPl8X2CTxswrUMCvXlsu.mHBkNJsqBMh6Jj.5tZ00X9acxLdmRa');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `facilities`
--

CREATE TABLE `facilities` (
  `fac_id` int(11) NOT NULL,
  `address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `facilities`
--

INSERT INTO `facilities` (`fac_id`, `address`) VALUES
(1, 'Sucharskiego 11, Skierniewice, 96-100');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `favorites`
--

CREATE TABLE `favorites` (
  `client_id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`client_id`, `emp_id`) VALUES
(8, 2),
(10, 4),
(8, 3);

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indeksy dla tabeli `apphistory`
--
ALTER TABLE `apphistory`
  ADD PRIMARY KEY (`app_id`);

--
-- Indeksy dla tabeli `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`app_id`);

--
-- Indeksy dla tabeli `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`client_id`);

--
-- Indeksy dla tabeli `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`emp_id`);

--
-- Indeksy dla tabeli `facilities`
--
ALTER TABLE `facilities`
  ADD PRIMARY KEY (`fac_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `apphistory`
--
ALTER TABLE `apphistory`
  MODIFY `app_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `app_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=166;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `client_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `emp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `facilities`
--
ALTER TABLE `facilities`
  MODIFY `fac_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
