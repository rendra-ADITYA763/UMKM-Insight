-- SQL Script for UMKM Insight Database
-- Group: UMKM Insight (RPL 2)

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- 1. Table `users`
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('client','operator','admin') NOT NULL DEFAULT 'client',
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `nama_bisnis` varchar(100) DEFAULT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `smartbank_id` varchar(50) DEFAULT NULL,
  `tier` enum('free','premium') NOT NULL DEFAULT 'free',
  `tier_expiry` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Table `notifications`
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `type` enum('auto','admin','offer') NOT NULL DEFAULT 'auto',
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Table `complaints`
CREATE TABLE `complaints` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `status` enum('open','resolved') NOT NULL DEFAULT 'open',
  `operator_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Table `products`
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `harga_jual` decimal(15,2) NOT NULL,
  `stok` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Table `transaction_cache`
CREATE TABLE `transaction_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `external_id` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `source` varchar(50) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `transaction_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `transaction_cache_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transaction_cache_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Table `tier_requests`
CREATE TABLE `tier_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `target_tier` enum('premium') NOT NULL DEFAULT 'premium',
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `processed_at` timestamp NULL DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `tier_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Table `offers`
CREATE TABLE `offers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `target_tier` enum('all','free','premium') NOT NULL DEFAULT 'all',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `offers_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- Password for all is: password (hashed with BCRYPT)
INSERT INTO `users` (`id`, `username`, `password`, `role`, `nama_lengkap`, `email`, `nama_bisnis`, `kategori`, `smartbank_id`, `tier`, `tier_expiry`) VALUES
(1, 'budi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client', 'Budi Santoso', 'budi@tokoberkah.com', 'Toko Berkah Utama', 'Makanan & Minuman', 'SB-UMKM-001', 'free', NULL),
(2, 'sari', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client', 'Sari Dewi', 'sari@rajutanku.com', 'Rajutan Sari', 'Fashion & Kerajinan', 'SB-UMKM-002', 'premium', '2026-12-31'),
(3, 'op_jaya', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'operator', 'Jaya Operasional', 'jaya@umkminsight.id', NULL, NULL, NULL, 'free', NULL),
(4, 'admin_super', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Admin System', 'admin@umkminsight.id', NULL, NULL, NULL, 'free', NULL);

-- Seed Products
INSERT INTO `products` (`id`, `user_id`, `nama_produk`, `kategori`, `harga_jual`, `stok`) VALUES
(1, 1, 'Kripik Tempe Barokah', 'Makanan', 15000.00, 150),
(2, 1, 'Sambal Bawang Pedas', 'Makanan', 25000.00, 80),
(3, 1, 'Emping Melinjo', 'Makanan', 35000.00, 45),
(4, 2, 'Tas Rajut Ethnic', 'Fashion', 250000.00, 12),
(5, 2, 'Syal Wol Musim Dingin', 'Fashion', 125000.00, 20);

-- Seed Transactions for Analytics
INSERT INTO `transaction_cache` (`user_id`, `product_id`, `external_id`, `type`, `source`, `amount`, `status`, `transaction_date`) VALUES
(1, 1, 'TX-001', 'Income', 'Marketplace', 250000.00, 'Success', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 2, 'TX-002', 'Income', 'POS', 125000.00, 'Success', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1, NULL, 'TX-003', 'Expense', 'Stok', 50000.00, 'Success', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(1, 1, 'TX-004', 'Income', 'Marketplace', 450000.00, 'Success', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(1, NULL, 'TX-005', 'Expense', 'Operasional', 20000.00, 'Success', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1, 3, 'TX-006', 'Income', 'POS', 300000.00, 'Success', DATE_SUB(NOW(), INTERVAL 10 DAY)),
(2, 4, 'TX-S-004', 'Income', 'Marketplace', 1500000.00, 'Success', DATE_SUB(NOW(), INTERVAL 15 DAY)),
(2, 5, 'TX-S-005', 'Income', 'POS', 450000.00, 'Success', DATE_SUB(NOW(), INTERVAL 7 DAY));

-- Seed Tier Requests
INSERT INTO `tier_requests` (`user_id`, `status`, `requested_at`) VALUES
(1, 'pending', NOW());

-- Seed Offers
INSERT INTO `offers` (`title`, `description`, `price`, `target_tier`, `created_by`) VALUES
('Voucher Ramadan Ceria', 'Potongan biaya admin SmartBank sebesar 50% untuk transaksi Marketplace.', 25000.00, 'free', 4),
('Paket Premium Anniversary', 'Upgrade ke Premium 1 Tahun dengan harga spesial 500rb saja.', 500000.00, 'all', 4);

COMMIT;
