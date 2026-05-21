CREATE TABLE IF NOT EXISTS `offers` (
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

INSERT IGNORE INTO `offers` (`title`, `description`, `price`, `target_tier`, `created_by`) VALUES
('Voucher Ramadan Ceria', 'Potongan biaya admin SmartBank sebesar 50% untuk transaksi Marketplace.', 25000.00, 'free', 4),
('Paket Premium Anniversary', 'Upgrade ke Premium 1 Tahun dengan harga spesial 500rb saja.', 500000.00, 'all', 4);
