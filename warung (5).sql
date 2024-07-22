-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 22, 2024 at 07:22 PM
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
-- Database: `warung`
--

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `idmenu` int(11) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `harga` int(11) NOT NULL,
  `stok` int(11) NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `kategori` enum('makanan','minuman') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`idmenu`, `nama`, `harga`, `stok`, `gambar`, `kategori`) VALUES
(1, 'Nasi Goreng', 20000, 44, 'images/nasgor.jpeg', 'makanan'),
(2, 'Ayam Goreng Spesial', 25000, 36, 'images/ayam.jpeg', 'makanan'),
(3, 'Bebek Goreng', 30000, 43, 'images/bebek.jpeg', 'makanan'),
(4, 'Rica Rica Ayam', 22000, 48, 'images/rica.jpeg', 'makanan'),
(5, 'Nasi Kebuli', 35000, 30, 'images/kebuli.jpeg', 'makanan'),
(6, 'Es Good Day', 8000, 48, 'images/gooday.jpg', 'minuman'),
(7, 'Es Teh', 5000, 40, 'images/teh.jpg', 'minuman'),
(8, 'Teh Anget', 4000, 0, 'images/anget.jpg', 'minuman'),
(9, 'Es Jeruk', 6000, 30, 'images/marimas.jpg', 'minuman'),
(10, 'Lemon Tea', 7000, 29, 'images/teal.jpg', 'minuman');

-- --------------------------------------------------------

--
-- Table structure for table `penjualan`
--

CREATE TABLE `penjualan` (
  `id` int(11) NOT NULL,
  `idmenu` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  `nama_pembeli` varchar(100) NOT NULL,
  `jumlah_dibayarkan` decimal(10,2) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `items` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penjualan`
--

INSERT INTO `penjualan` (`id`, `idmenu`, `quantity`, `total`, `tanggal`, `nama_pembeli`, `jumlah_dibayarkan`, `status`, `items`) VALUES
(1, 2, 2, 50000.00, '2024-07-01 17:01:56', 'andi', 50000.00, 'Processed', NULL),
(2, 2, 2, 50000.00, '2024-07-16 03:36:55', 'adi', 70000.00, 'Processed', NULL),
(3, 1, 1, 20000.00, '2024-07-16 03:36:55', 'adi', 70000.00, 'Processed', NULL),
(4, 2, 2, 50000.00, '2024-07-16 04:30:08', 'Samuel', 57000.00, 'Processed', NULL),
(5, 10, 1, 7000.00, '2024-07-16 04:30:08', 'Samuel', 57000.00, 'Processed', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','manager','customer') NOT NULL DEFAULT 'customer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin', '21232f297a57a5a743894a0e4a801fc3', 'admin'),
(2, 'manager', '202cb962ac59075b964b07152d234b70', 'manager'),
(3, 'user', 'ee11cbb19052e40b07aac0ca060c23ee', 'customer'),
(4, 'user1', '202cb962ac59075b964b07152d234b70', 'customer'),
(5, 'user2', '202cb962ac59075b964b07152d234b70', 'customer');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`idmenu`);

--
-- Indexes for table `penjualan`
--
ALTER TABLE `penjualan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `idmenu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `penjualan`
--
ALTER TABLE `penjualan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
