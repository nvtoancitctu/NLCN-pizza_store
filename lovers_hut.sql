-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th4 06, 2025 lúc 04:10 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `pizza_store`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `size` enum('S','M','L') NOT NULL DEFAULT 'S',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_at`) VALUES
(1, 'Classic Pizzas', '2024-10-04 03:58:57'),
(2, 'Specialty Pizzas', '2024-10-04 03:58:57'),
(3, 'Vegan Pizzas', '2024-10-04 03:58:57'),
(4, 'Deals', '2025-03-17 12:43:00'),
(5, 'Appetizers', '2025-03-17 12:53:48'),
(6, 'Fried Chicken', '2025-03-17 12:58:00'),
(7, 'Beverages', '2025-03-17 12:58:00'),
(8, 'Combo', '2025-03-19 08:03:08'),
(9, 'Pizza', '2025-03-19 12:53:50');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `product_id`, `created_at`) VALUES
(2, 1, 48, '2025-04-04 09:11:50'),
(3, 10, 43, '2025-04-06 07:13:30'),
(4, 10, 3, '2025-04-06 07:26:51'),
(5, 10, 2, '2025-04-06 07:26:52'),
(6, 2, 43, '2025-04-06 07:58:43'),
(7, 2, 42, '2025-04-06 07:58:44'),
(8, 2, 49, '2025-04-06 07:58:48'),
(9, 2, 2, '2025-04-06 07:59:04'),
(10, 3, 2, '2025-04-06 08:05:41'),
(11, 3, 4, '2025-04-06 08:05:43'),
(12, 3, 8, '2025-04-06 08:05:45'),
(13, 3, 11, '2025-04-06 08:05:48'),
(14, 3, 54, '2025-04-06 08:06:17'),
(15, 4, 2, '2025-04-06 08:11:20'),
(16, 4, 4, '2025-04-06 08:11:21'),
(17, 4, 49, '2025-04-06 08:11:24'),
(18, 4, 54, '2025-04-06 08:11:31'),
(19, 4, 60, '2025-04-06 08:12:14'),
(20, 8, 49, '2025-04-06 08:39:39'),
(21, 10, 42, '2025-04-06 08:45:45'),
(22, 10, 49, '2025-04-06 08:45:48'),
(23, 10, 54, '2025-04-06 08:45:51');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `order_id` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `response` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `responsed_at` datetime DEFAULT NULL,
  `rating` int(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `name`, `email`, `order_id`, `message`, `response`, `created_at`, `updated_at`, `responsed_at`, `rating`) VALUES
(1, 1, 'ADMIN', 'loverhut.pizzastore@gmail.com', 1, 'The pizza is delicious, and the combo is good too!', NULL, '2025-03-09 04:53:14', '2025-03-09 04:53:14', NULL, 4),
(2, 2, 'Huỳnh Đắc Vinh', 'dacvinhhh@gmail.com', 4, 'I love the packaging and the taste. Highly recommended!', NULL, '2025-04-06 00:53:14', '2025-04-06 00:53:14', NULL, 5),
(3, 4, 'Huỳnh Loan', 'chauloan2004@gmail.com', 7, 'The pizza was cold when it arrived.', NULL, '2025-04-06 01:55:14', '2025-04-06 01:55:14', NULL, 3),
(4, 3, 'Phan Trung Thuận', 'thuantk000@gmail.com', 6, 'I love the packaging and the taste. Highly recommended!', NULL, '2025-04-06 03:53:14', '2025-04-06 03:53:14', NULL, 5),
(5, 10, 'Nguyễn Văn Toàn', 'quykute007ct@gmail.com', 2, 'The pizza is delicious, and the combo is good too!', NULL, '2025-04-06 04:53:14', '2025-04-06 04:53:14', NULL, 5),
(6, 5, 'Nguyễn Ngọc Trâm', 'ngoctram077682@gmail.com', 8, 'The pizza was cold when it arrived.', NULL, '2025-04-06 04:53:14', '2025-04-06 04:53:14', NULL, 3),
(7, 10, 'Nguyễn Văn Toàn', 'quykute007ct@gmail.com', 3, 'The pizza is delicious, and the combo is good too!', NULL, '2025-04-06 04:55:14', '2025-04-06 04:55:14', NULL, 5),
(8, 6, 'Ngô Quang lâu', 'nvtoan.1706@gmail.com', 9, 'The pizza is delicious, and the combo is good too!', NULL, '2025-04-06 04:55:14', '2025-04-06 04:55:14', NULL, 5),
(9, 2, 'Huỳnh Đắc Vinh', 'dacvinhhh@gmail.com', 5, 'The pizza was cold when it arrived.', NULL, '2025-04-06 05:55:14', '2025-04-06 05:55:14', NULL, 3),
(10, 7, 'Trần Tấn Lộc', 'tanloc0979942603@gmail.com', 10, 'Great service, fast delivery!', NULL, '2025-04-07 04:55:14', '2025-04-07 04:55:14', NULL, 5),
(11, 9, 'Lê Quang Vinh', 'quangvinh.0126215641@gmail.com', 13, 'Great service, fast delivery!', NULL, '2025-04-07 05:55:14', '2025-04-07 05:55:14', NULL, 5);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(1, 1, 'Have a new order from User: #1 with Order: #1. Please check the order detail', 1, '2025-04-04 13:04:22'),
(2, 1, 'Your order (ID: 1) has been updated to status: completed.', 0, '2025-04-05 04:50:06'),
(3, 1, 'Customer: ADMIN has submitted feedback for Order ID: 1', 0, '2025-04-05 04:53:14'),
(4, 1, 'Have a new order from User: #10 with Order: #2. Please check the order detail', 0, '2025-04-06 07:16:33'),
(5, 1, 'Have a new order from User: #10 with Order: #3. Please check the order detail', 0, '2025-04-06 07:27:40'),
(6, 10, 'Your order (ID: 2) has been updated to status: completed.', 0, '2025-04-06 07:51:46'),
(7, 10, 'Your order (ID: 3) has been updated to status: completed.', 0, '2025-04-06 07:54:15'),
(8, 1, 'Have a new order from User: #2 with Order: #4. Please check the order detail', 0, '2025-04-06 08:00:41'),
(9, 1, 'Have a new order from User: #2 with Order: #5. Please check the order detail', 0, '2025-04-06 08:02:17'),
(10, 1, 'Have a new order from User: #3 with Order: #6. Please check the order detail', 0, '2025-04-06 08:07:19'),
(11, 1, 'Have a new order from User: #4 with Order: #7. Please check the order detail', 0, '2025-04-06 08:13:10'),
(12, 1, 'Have a new order from User: #8 with Order: #12. Please check the order detail', 0, '2025-04-06 08:40:40'),
(13, 1, 'Have a new order from User: #8 with Order: #13. Please check the order detail', 0, '2025-04-06 08:42:18'),
(14, 1, 'Have a new order from User: #9 with Order: #14. Please check the order detail', 0, '2025-04-06 08:43:27'),
(15, 1, 'Have a new order from User: #9 with Order: #15. Please check the order detail', 0, '2025-04-06 08:44:47'),
(16, 1, 'Have a new order from User: #10 with Order: #16. Please check the order detail', 0, '2025-04-06 08:47:35'),
(17, 8, 'Your order (ID: 13) has been updated to status: completed.', 0, '2025-04-06 09:42:39'),
(18, 9, 'Your order (ID: 15) has been updated to status: completed.', 0, '2025-04-06 09:43:09'),
(19, 10, 'Your order (ID: 16) has been updated to status: cancelled.', 0, '2025-04-06 09:43:30'),
(20, 9, 'Your order (ID: 15) has been updated to status: cancelled.', 0, '2025-04-06 09:43:47'),
(21, 1, 'Your order (ID: 1) has been updated to status: pending.', 0, '2025-04-06 10:07:42'),
(22, 1, 'Your order (ID: 1) has been updated to status: completed.', 0, '2025-04-06 10:07:50');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `voucher_id` int(11) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `address` text NOT NULL,
  `payment_method` enum('bank_transfer','cash_on_delivery') NOT NULL,
  `status` enum('pending','processing','completed','cancelled') DEFAULT 'pending',
  `status_at` datetime DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `images` varchar(255) DEFAULT NULL,
  `note` varchar(255) DEFAULT 'unfeedbacked',
  `shipping_link` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `voucher_id`, `total`, `address`, `payment_method`, `status`, `status_at`, `created_at`, `images`, `note`, `shipping_link`) VALUES
(1, 1, 1, 12.28, 'Phường An Khánh, Quận Ninh Kiều, Thành phố Cần Thơ, 94000, Việt Nam', 'bank_transfer', 'completed', '2025-04-06 17:07:50', '2025-03-04 13:04:22', 'OD1-20250304.jpg', 'feedbacked', 'https://www.google.com/maps?q=10.0241385,105.7659888'),
(2, 10, 5, 22.51, 'Phường Thuận Hưng, Quận Thốt Nốt, Thành phố Cần Thơ, Việt Nam', 'bank_transfer', 'completed', '2025-03-06 14:51:46', '2025-03-06 07:16:33', 'OD2-20250306.jpg', 'unfeedbacked', 'https://www.google.com/maps?q=10.2251141,105.5636816'),
(3, 10, NULL, 28.22, 'Phường Thuận Hưng, Quận Thốt Nốt, Thành phố Cần Thơ, Việt Nam', 'cash_on_delivery', 'completed', '2025-03-07 14:54:15', '2025-03-07 07:27:40', NULL, 'unfeedbacked', ''),
(4, 2, 5, 27.89, 'Hẻm 132, đường 3/2', 'bank_transfer', 'completed', '2025-04-04 15:00:40', '2025-04-04 08:00:40', 'OD4-20250404.jpg', 'unfeedbacked', ''),
(5, 2, NULL, 47.37, 'Hẻm 132, đường 3/2', 'cash_on_delivery', 'completed', '2025-04-04 15:02:17', '2025-04-04 08:02:17', NULL, 'unfeedbacked', ''),
(6, 3, 5, 34.90, 'KTX B, B5-206', 'bank_transfer', 'completed', '2025-04-04 15:07:19', '2025-04-04 08:07:19', 'OD6-20250404.jpg', 'unfeedbacked', ''),
(7, 4, NULL, 29.31, 'Trần Hoàng Na, nhà trọ Gia Phúc', 'cash_on_delivery', 'completed', '2025-04-04 21:43:10', '2025-04-04 14:13:10', NULL, 'unfeedbacked', ''),
(8, 5, NULL, 47.37, 'KTX B, B5-206', 'cash_on_delivery', 'completed', '2025-04-05 07:32:34', '2025-04-05 00:02:17', 'OD8-20250405.jpg', 'unfeedbacked', ''),
(9, 6, NULL, 29.31, 'KTX B, B5-206', 'cash_on_delivery', 'completed', '2025-04-05 08:32:34', '2025-04-05 01:02:34', 'OD9-20250405.jpg', 'unfeedbacked', ''),
(10, 7, NULL, 29.31, 'Trần Hoàng Na, nhà trọ Gia Phúc', 'cash_on_delivery', 'completed', '2025-04-05 15:13:10', '2025-04-05 08:13:10', 'OD10-20250405.jpg', 'unfeedbacked', ''),
(11, 7, NULL, 47.37, 'KTX B, B5-206', 'cash_on_delivery', 'processing', '2025-04-05 15:52:34', '2025-04-05 08:22:17', NULL, 'unfeedbacked', ''),
(12, 8, NULL, 22.77, '255, Tân phước 1, Thuận Hưng, Thốt Nốt, Cần Thơ', 'cash_on_delivery', 'processing', '2025-04-06 15:40:40', '2025-04-06 08:40:40', NULL, 'unfeedbacked', 'https://www.google.com/maps?q=10.2251433,105.5637022'),
(13, 8, 6, 31.09, '255, Tân phước 1, Thuận Hưng, Thốt Nốt, Cần Thơ', 'bank_transfer', 'completed', '2025-04-06 16:42:39', '2025-04-06 08:42:17', 'OD13-20250406.jpg', 'unfeedbacked', ''),
(14, 9, NULL, 20.01, 'Phường Thuận Hưng, Quận Thốt Nốt, Thành phố Cần Thơ, Việt Nam', 'cash_on_delivery', 'processing', '2025-04-06 15:43:27', '2025-04-06 08:43:27', NULL, 'unfeedbacked', 'https://www.google.com/maps?q=10.2251141,105.5636816'),
(15, 9, NULL, 26.28, 'Phường Thuận Hưng, Quận Thốt Nốt, Thành phố Cần Thơ, Việt Nam', 'bank_transfer', 'cancelled', '2025-04-06 16:43:47', '2025-04-06 08:44:47', 'OD15-20250406.jpg', 'unfeedbacked', ''),
(16, 10, NULL, 18.10, '255, Tân Phước 1, Thuận Hưng, Thốt Nốt, Thành phố Cần Thơ', 'cash_on_delivery', 'cancelled', '2025-04-06 16:43:30', '2025-04-06 08:47:35', NULL, 'unfeedbacked', 'https://www.google.com/maps?q=10.2251141,105.5636816');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `size` enum('S','M','L') NOT NULL DEFAULT 'S'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `size`) VALUES
(1, 1, 2, 1, 4.49, 'S'),
(2, 1, 48, 1, 9.36, 'S'),
(3, 2, 65, 1, 12.36, 'S'),
(4, 2, 18, 1, 8.00, 'L'),
(5, 2, 43, 2, 1.17, 'S'),
(6, 3, 3, 1, 9.75, 'S'),
(7, 3, 54, 1, 3.09, 'S'),
(8, 3, 55, 1, 3.09, 'S'),
(9, 3, 60, 1, 3.09, 'S'),
(10, 3, 61, 1, 3.02, 'S'),
(11, 3, 42, 1, 1.17, 'S'),
(12, 3, 44, 1, 1.17, 'S'),
(13, 3, 46, 2, 1.17, 'S'),
(14, 4, 66, 1, 15.46, 'S'),
(15, 4, 61, 1, 3.02, 'S'),
(16, 4, 18, 1, 5.33, 'S'),
(17, 4, 43, 4, 1.17, 'S'),
(18, 5, 11, 3, 5.84, 'S'),
(19, 5, 18, 2, 8.00, 'L'),
(20, 5, 65, 1, 12.36, 'S'),
(21, 6, 2, 1, 4.66, 'S'),
(22, 6, 3, 1, 9.75, 'S'),
(23, 6, 4, 1, 5.44, 'S'),
(24, 6, 18, 1, 5.33, 'S'),
(25, 6, 42, 1, 1.17, 'S'),
(26, 6, 44, 1, 1.17, 'S'),
(27, 6, 45, 1, 1.17, 'S'),
(28, 6, 46, 1, 1.17, 'S'),
(29, 6, 54, 1, 3.09, 'S'),
(30, 6, 62, 1, 3.08, 'S'),
(31, 7, 65, 1, 12.36, 'S'),
(32, 7, 54, 1, 4.64, 'L'),
(33, 7, 55, 1, 4.64, 'L'),
(34, 7, 60, 2, 3.09, 'S'),
(35, 8, 11, 3, 5.84, 'S'),
(36, 8, 18, 2, 8.00, 'L'),
(37, 8, 65, 1, 12.36, 'S'),
(38, 9, 65, 1, 12.36, 'S'),
(39, 9, 54, 1, 4.64, 'L'),
(40, 9, 55, 1, 4.64, 'L'),
(41, 9, 60, 2, 3.09, 'S'),
(42, 10, 65, 1, 12.36, 'S'),
(43, 10, 54, 1, 4.64, 'L'),
(44, 10, 55, 1, 4.64, 'L'),
(45, 10, 60, 2, 3.09, 'S'),
(46, 11, 11, 3, 5.84, 'S'),
(47, 11, 18, 2, 8.00, 'L'),
(48, 11, 65, 1, 12.36, 'S'),
(49, 12, 68, 1, 21.27, 'S'),
(50, 13, 2, 1, 6.99, 'L'),
(51, 13, 3, 2, 9.75, 'S'),
(52, 13, 54, 1, 3.71, 'M'),
(53, 13, 56, 2, 3.71, 'M'),
(54, 13, 44, 1, 1.17, 'S'),
(55, 13, 46, 1, 1.17, 'S'),
(56, 14, 18, 1, 5.33, 'S'),
(57, 14, 2, 1, 4.66, 'S'),
(58, 14, 42, 1, 1.17, 'S'),
(59, 14, 44, 1, 1.17, 'S'),
(60, 14, 54, 1, 3.09, 'S'),
(61, 14, 56, 1, 3.09, 'S'),
(62, 15, 68, 1, 21.27, 'S'),
(63, 15, 46, 3, 1.17, 'S'),
(64, 16, 65, 1, 12.36, 'S'),
(65, 16, 54, 1, 3.09, 'S'),
(66, 16, 47, 1, 1.15, 'S');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `note` varchar(255) DEFAULT NULL,
  `discount` decimal(10,2) DEFAULT NULL,
  `discount_end_time` timestamp NULL DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `category_id`, `price`, `stock_quantity`, `created_at`, `note`, `discount`, `discount_end_time`, `image`) VALUES
(1, 'Hawaiian Paradise', 'Hawaiian-flavored Pizza with ham, pineapples and mozzarella', 1, 4.66, 6, '2024-10-24 22:49:53', NULL, NULL, NULL, 'A01@@Pizza_hawaiian.webp'),
(2, 'Pepperoni', 'Classic pizza with spicy pepperoni.', 1, 4.66, 0, '2024-10-24 22:49:53', 'Best Seller', NULL, NULL, 'A02@@Pizza_xuc_xich.webp'),
(3, 'Pizza Lava', 'Bacon, Sausage, Beef, Ham, Pepperoni, Boneless Crispy Chicken, Mushrooms, Onions, Tomato Sauce, Cheddar Cheese Sauce', 1, 9.75, 4, '2025-03-17 11:04:59', 'New', NULL, NULL, 'A03@@Pizza_lava.webp'),
(4, 'Supreme', 'Beef, bacon, pepperoni, pineapples, capsicums, mushrooms, onions on tomato sauce and mozzarella cheese', 1, 5.44, 8, '2025-03-17 11:48:19', 'Best Seller', NULL, NULL, 'A04@@Pizza_thap_cam.webp'),
(5, 'Supreme Meat Lover\'s', 'Enjoy a tasty, protein-packed serving of bacon, sausage, beef, ham and pepperoni', 1, 5.44, 10, '2025-03-17 12:22:14', NULL, NULL, NULL, 'A05@@Pizza_thit_va_xuc_xich.webp'),
(6, 'Cheese Lover', 'Mozzarella cheese, honey, tomato sauce. Tastier with honey.', 1, 4.66, 10, '2025-04-04 12:50:27', NULL, 4.49, '2025-04-10 05:00:00', 'A06@@Pizza_pho_mai_cao_cap.webp'),
(7, 'Korean BBQ Spicy Beef', 'Beef, pineapples, cress, on Korean spicy sauce, ganished with cress and sesames', 2, 4.66, 10, '2024-10-25 05:49:53', NULL, NULL, NULL, 'B01@@Pizza_bo_BBQ.webp'),
(8, 'Cheesy Chicken Pizza', 'Crispy boneless chicken leg, mushrooms, onions on cheesy sauce', 2, 4.66, 10, '2024-10-25 05:49:53', 'Best Seller', NULL, NULL, 'B02@@Pizza_ga_pho_mai.webp'),
(9, 'Seafood Lover', 'Shrimp, Imitation Crab, Cherry Tomatoes, Sweet Corn, Pineapple, Dill, Mozzarella Cheese', 2, 5.44, 10, '2025-03-17 11:40:59', NULL, NULL, NULL, 'B03@@Pizza_hai_san_nhiet_doi.webp'),
(10, 'Fisherman\'s Tuna', 'Taste of the ocean with tuna, crab sticks, onions, pineapples with mozzarella', 2, 5.44, 9, '2025-03-17 11:45:47', NULL, NULL, NULL, 'B04@@Pizza_ca_ngu.webp'),
(11, 'Ocean Delight', 'Squids, crab sticks, pineapples,capsicums and mozzarella cheese', 2, 5.84, 5, '2025-03-17 11:53:11', 'Best Seller', NULL, NULL, 'B05@@Pizza_con_loc_hai_san.webp'),
(12, 'Chicken Deluxe', 'Chicken fillets, mushrooms, Pineapples, mozzarella, ganished with fresh carrots and Cress', 2, 4.66, 10, '2025-03-17 12:06:48', NULL, NULL, NULL, 'B06@@Pizza_ga_nuong_nam.webp'),
(13, 'Premium Honey Cheese', 'A perfect combination of asssorted cheeses - mozzarella, parmesan, cheddar and cream cheese on honey sauce', 2, 8.97, 10, '2025-03-17 12:09:04', NULL, NULL, NULL, 'B07@@Pizza_pho_mai_4_vi_mat_ong.webp'),
(14, 'Cheesy Bites Trio Shrimp', 'Shrimps with garlic butter, onions, red capsicums, pineapples, black olives, and mozzarella', 2, 11.71, 10, '2025-03-17 12:23:44', NULL, NULL, NULL, 'B08@@Pizza_Hai_san_vien_pho_mai_3_vi.webp'),
(15, 'Seafood Pesto', 'Shrimps, squids and mushrooms on a bed of signature Pesto sauce, topped with mozzarella cheese', 2, 5.84, 10, '2025-03-17 12:25:50', NULL, NULL, NULL, 'B09@@Pizza_hai_ssn_sot_pesto.webp'),
(16, 'Seafood Black Pepper', 'Shrimps, squids, crabsticks, pineapples, onions on black pepper sauce and  mozzarella cheese', 2, 5.44, 10, '2025-03-17 12:26:58', NULL, NULL, NULL, 'B10@@Pizza_hai_san_sot_tieu_den.webp'),
(17, 'Asian Beef And Reef Pizza', 'Shrimps, squids and beef on Korean spicy sauce, adding juiciness of pineapples, onions, topped with mozzarella', 2, 5.44, 10, '2025-03-17 12:28:37', NULL, NULL, NULL, 'B11@@pizza_bo_va_hai_san.webp'),
(18, 'Ham And Shrimp Pepper Pizza', 'Shrimps, ham on black pepper sauce with mushrooms, onions and mozzarella', 2, 5.44, 3, '2025-03-17 12:31:46', NULL, 5.33, '2025-04-10 05:00:00', 'B12@@Pizza_tom_thit_nuong_tieu.webp'),
(19, 'Shrimp Scampi', 'Shrimps, onions, capsicums with garlic butter sauce and mozzarella', 2, 5.44, 10, '2025-03-17 12:33:10', NULL, NULL, NULL, 'B13@@Pizza_tom_xot_bo_toi.webp'),
(20, 'Veggie Supreme', 'Black olives, cherry tomatoes, mushrooms, pineapples, sweet corns, onions with garlic butter, on top of mozzarella cheese', 3, 4.66, 10, '2025-03-17 05:12:13', NULL, NULL, NULL, 'C01@@Pizza_rau_cu.webp'),
(21, 'Bacon Onion Pizza', 'Lardon, Mozzarella Cheese, Onion, Sweet Chili Sauce, Cheesy Mayo Sauce, Parsley Fresh', 4, 1.92, 8, '2025-03-17 12:44:29', NULL, NULL, NULL, 'D01@@Bacon_Onion_Pizza.webp'),
(22, 'Beef & Corn Pizza', 'Beef, Pineapple, Sweet Corn, Mozzarella Cheese, Black Pepper Sauce & Parsley', 4, 1.92, 10, '2025-03-17 12:45:23', NULL, NULL, NULL, 'D02@@Beef_Corn_Pizza.webp'),
(23, 'Tuna & Crab Stick Pizza', 'Pesto Sauce, Mozza Cheese, Tuna, Crab Stick, Onion And Parsley', 4, 1.92, 10, '2025-03-17 12:46:48', NULL, NULL, NULL, 'D03@@Tuna_Crab_Stick_Pizza.webp'),
(24, 'Crab Stick Spaghetti', 'Spaghetti, Cheesy Mayo Sauce, Crab Stick, Carrot, Green Bean', 4, 1.92, 10, '2025-03-17 12:47:54', NULL, NULL, NULL, 'D04@@my_y_thanh_cua.webp'),
(25, 'Bacon Black Pepper Spaghetti', 'Spaghetti, Black Pepper Sauce, Bacon, Chicken Sausages, Zucchini', 4, 1.92, 10, '2025-03-17 12:50:07', NULL, NULL, NULL, 'D05@@my_y_thit_xong_khoi_xot_tieu_den.webp'),
(26, 'Garlic Bread', 'Garlic butter Bread', 5, 1.14, 8, '2025-03-19 07:26:00', NULL, NULL, NULL, 'E01@@banh_mi_bo_toi.webp'),
(27, 'Nachos', 'Nachos with cheesy dipping sauce or house-made tomato sauce', 5, 1.14, 10, '2025-03-19 07:28:11', NULL, NULL, NULL, 'E02@@banh_nachos.webp'),
(28, 'Crinkle-Cut French Fries', 'Crinkle cut fries', 5, 2.31, 10, '2025-03-19 07:29:13', NULL, NULL, NULL, 'E03@@khoai_tay_chien.webp'),
(29, 'Cheesy Pops', 'Cheesy Pops', 5, 2.70, 10, '2025-03-19 07:30:06', NULL, NULL, NULL, 'E04@@banh_cuon_pho_mai.webp'),
(30, 'Cheesy Rings', 'Crispy fried cheese ring', 5, 3.09, 10, '2025-03-19 07:31:02', NULL, NULL, NULL, 'E05@@pho_mai_chien_gion.webp'),
(31, 'Honey Mustard Fries', 'Baked fries with cheesy bacon and honey mustard sauce', 5, 3.09, 10, '2025-03-19 07:32:29', NULL, NULL, NULL, 'E06@@khoai_tay_chien_dut_lo.webp'),
(32, 'Baked Cheesy Corn With Bacon', 'Baked sweet corn with bacon and cheese', 5, 3.09, 10, '2025-03-19 07:33:16', NULL, NULL, NULL, 'E07@@bap_pho_mai_thit_xong_khoi.webp'),
(33, 'Creamy Chicken Soup', 'Creamy soup with chicken fillets, mushrooms and parsley', 5, 1.92, 10, '2025-03-19 07:35:26', NULL, NULL, NULL, 'E08@@sup_kem_ga_nam.webp'),
(34, 'Seafood Chowder', 'Seafood Chowder', 5, 2.69, 10, '2025-03-19 07:36:11', NULL, NULL, NULL, 'E09@@sup_hai_san.webp'),
(35, 'Tuna Bacon Salad', 'Mixed greens with tuna, bacon, French beans, cherry tomatoes, corn with vinegrette dressing and mayonaise', 5, 3.09, 10, '2025-03-19 07:39:15', NULL, NULL, NULL, 'E10@@salad_ca_ngu.webp'),
(36, 'Grill Shrimp and Peach Salad', 'Garden salads, peach, olive, capsicum, topped with grilled shrimps and croutons', 5, 3.48, 10, '2025-03-19 07:40:43', NULL, NULL, NULL, 'E11@@salad_tom_nuong_dao.webp'),
(37, 'Spaghetti Bolognese', 'Bolognese spaghetti', 5, 4.69, 10, '2025-03-19 07:42:59', NULL, NULL, NULL, 'E12@@mi_y_bo_bam.webp'),
(38, 'Creamy Shrimp & Sausage Spaghetti', 'Spaghetti in a delicious creamy sauce with shrimps, sausages and carrots.', 5, 4.70, 10, '2025-03-19 07:44:02', NULL, NULL, NULL, 'E13@@mi_y_tom_va_xuc_xich.webp'),
(39, 'Fiesta Seafood Rice', 'Fried rice with garlic, shrimps, squids, mushroom, beans and carrots', 5, 3.88, 10, '2025-03-19 07:45:42', NULL, NULL, NULL, 'E14@@com_chien_hai_san.webp'),
(40, 'BBQ Chicken Wings With Garlic Rice', 'Auromatic Garlic Rice With Delicious Chicken Wings', 5, 5.05, 10, '2025-03-19 07:46:31', NULL, NULL, NULL, 'E15@@com_chien_toi_ga_BBQ.webp'),
(41, 'Aquafina', 'Aquafina 500ml', 7, 0.78, 10, '2025-03-19 07:49:03', NULL, NULL, NULL, 'G01@@AQUAFINA_500ML.webp'),
(42, '7Up', 'Can 320ml', 7, 1.17, 1, '2025-03-19 07:52:00', NULL, NULL, NULL, 'G02@@7UP_CAN_320ML.webp'),
(43, 'Pesi', 'Can 320ml', 7, 1.17, 0, '2025-03-19 07:53:48', NULL, NULL, NULL, 'G03@@PEPSI_CAN_320ML.webp'),
(44, 'Pesi Lemon', 'Can 320ml', 7, 1.17, 1, '2025-03-19 07:54:49', NULL, NULL, NULL, 'G04@@PEPSI_LEMON_CAN_320ML.webp'),
(45, 'Pepsi No Calories', 'Can 320ml', 7, 1.17, 9, '2025-03-19 07:55:41', NULL, NULL, NULL, 'G05@@PEPSI_NO_CALO_CAN_320ML.webp'),
(46, 'Mirinda Orange', 'Can 320ml', 7, 1.17, 2, '2025-03-19 07:57:09', NULL, NULL, NULL, 'G06@@MIRINDA_ORANGE_CAN_320ML.webp'),
(47, 'Mirinda Soda', 'Can 320ml', 7, 1.15, 9, '2025-03-19 07:57:35', NULL, NULL, NULL, 'G07@@MIRINDA_SODA_CAN_320ML.webp'),
(48, 'COMBO \"SUONG SUONG\"', '1 Korean BBQ Spicy Beef/ Chicken Deluxe/ Hawaiian/ Pepperoni/ Cheese Lover’s/ Veggie Supreme Pizza (Regular); \r\n1 Crinkle Cut French Fries/ Cheesy Pops/ Creamy Chicken Soup; \r\n2 Glasses/Cans of Pepsi/ 7UP/ Mirinda/ Aquafina.', 8, 9.36, 7, '2025-03-19 08:06:12', NULL, NULL, NULL, 'COMBO_SS.webp'),
(49, 'COMBO \"VUA VAN\"', '1 Pizza (not applicable for Double Topping & Hut Signature) (Regular);  \r\n1 Tuna Bacon Salad/ Chicken Salad With Pesto Sauce/ Bacon Cabonara Spaghetti; \r\n1 Chicken 4 pieces/ Boneless Chicken (Optional Flavor); \r\n3 Glasses/Cans of Pepsi/ 7UP/ Mirinda/ Aquafina.', 8, 14.45, 7, '2025-03-19 08:08:48', 'Best Seller', NULL, NULL, 'COMBO_VV.webp'),
(50, 'COMBO \"NO NE\"', '1 Pizza (not applicable for Double Topping & Hut Signature) (Large); \r\n1 Tuna Bacon Salad/ Chicken Salad With Pesto Sauce/ Bacon Cabonara Spaghetti; \r\n1 Chicken 4 pieces/ Boneless Chicken (Optional Flavor); \r\n4 Glasses/Cans of Pepsi/ 7UP/ Mirinda/ Aquafina.', 8, 18.36, 10, '2025-03-19 08:11:01', NULL, NULL, NULL, 'COMBO_NN.webp'),
(51, 'COMBO \"PHU PHE\"', '1 Pizza (not applicable for Double Topping & Hut Signature) (L); \r\n1 Pizza (not applicable for Double Topping & Hut Signature) (R); \r\n1 Tuna Bacon Salad/ Chicken Salad With Pesto Sauce/ Bacon Cabonara Spaghetti; \r\n1 Chicken 6 pieces/ Boneless Chicken; \r\n6 Glasses/Cans of Pepsi/ 7UP/ Mirinda/ Aquafina.', 8, 26.58, 10, '2025-03-19 08:12:40', NULL, NULL, NULL, 'COMBO_PP.webp'),
(52, 'Korean BBQ Pulled Pork', 'Pulled pork, cress, seasame, pineapple, Bulgogi sauce', 2, 4.66, 10, '2025-03-19 23:43:37', NULL, NULL, NULL, 'B14@@pizza_heo_xe_bbq.webp'),
(53, 'F.C.Ws Gochujang', 'Sweet honey with the mildly spicy taste of Gochujang', 6, 3.09, 9, '2025-03-20 00:00:36', NULL, NULL, NULL, 'F01@@Chicken_Gochujang_4pcs.webp'),
(54, 'F.C.Ws Tomyum', 'The distinctive sour and spicy flavour of Tom Yum sauce', 6, 3.09, 2, '2025-03-20 00:03:22', 'New', NULL, NULL, 'F02@@Chicken_Tomyum_4pcs.webp'),
(55, 'F.C.Ws Salted Egg', 'Perfect mix of crispy skin and salted egg sauce', 6, 3.09, 6, '2025-03-20 00:04:59', 'New', NULL, NULL, 'F03@@Chicken_Salted_egg_4pcs.webp'),
(56, 'F.C.Ws Mekong', 'Fried chicken wings coated with Mekong-style sauce and crispy garlic', 6, 3.09, 7, '2025-03-20 00:09:38', NULL, NULL, NULL, 'F04@@Chicken_Mekong_4pcs.webp'),
(57, 'F.C.Ws Karaage', 'Karaage Fried Chicken Wings', 6, 3.09, 10, '2025-03-20 00:11:22', NULL, NULL, NULL, 'F05@@Chicken_Karaage_4pcs.webp'),
(58, 'F.C.Ws Korean', 'Fried chicken wings coated with famous Korean spicy sauce', 6, 3.09, 10, '2025-03-20 00:12:50', NULL, NULL, NULL, 'F06@@Chicken_Cay_pop_4pcs.webp'),
(59, 'F.B.C Gochujang', 'Sweet honey with the mildly spicy taste of Gochujang', 6, 3.07, 8, '2025-03-20 00:17:12', NULL, NULL, NULL, 'F07@@Chicken_Gochujang_BL.webp'),
(60, 'F.B.C Tomyum', 'The distinctive sour and spicy flavour of Tom Yum sauce', 6, 3.09, 7, '2025-03-20 00:19:27', 'New', NULL, NULL, 'F08@@Chicken_Tomyum_BL.webp'),
(61, 'F.B.C Salted Egg', 'Perfect mix of crispy skin and salted egg sauce', 6, 3.02, 8, '2025-03-20 00:20:42', 'New', NULL, NULL, 'F09@@Chicken_Salted_egg_BL.webp'),
(62, 'F.B.C Mekong', 'Fried boneless chicken leg coated with Mekong-style sauce and crispy garlic', 6, 3.08, 9, '2025-03-20 00:22:52', NULL, NULL, NULL, 'F10@@Chicken_Mekong_BL.webp'),
(63, 'F.B.C Korean', 'Fried boneless chicken leg coated with famous Korean spicy sauce', 6, 3.09, 10, '2025-03-20 00:23:48', NULL, NULL, NULL, 'F11@@Chicken_Cay_pop_BL.webp'),
(65, 'COMBO \"TU HAO DAT VIET\"', '01 Pizza (Regular): Seafood Lover/ Supreme Slices/ Pizza Lava;\r\n01 Pizza (Regular): Nice To Meat You/ Veggie Lover;\r\n02 Drink 320ml.', 8, 12.36, 6, '2025-03-19 08:12:40', 'New', NULL, NULL, 'COMBO_THDV.webp'),
(66, 'COMBO \"CON RONG CHAU TIEN\"', '01 Pizza (Regular): Nice To Meat You/ Veggie Lover/ Seafood Lover/ Supreme Slices/ Pizza Lava;\r\n01 Pizza (Large): Nice To Meat You/ Veggie Lover/ Seafood Lover/ Supreme Slices/ Pizza Lava;\r\n01 Crinkle Cut Fries.', 8, 15.46, 9, '2025-03-19 08:12:40', 'New', NULL, NULL, 'COMBO_CRCT.webp'),
(67, 'COMBO \"CO TIEC VUA HUNG\"', '01 Pizza (Regular): Nice To Meat You/ Veggie Lover/ Seafood Lover/ Supreme Slices/ Pizza Lava;\r\n01 Pizza (Large): Nice To Meat You/ Veggie Lover/ Seafood Lover/ Supreme Slices/ Pizza Lava;\r\n01 Tuna Bacon Salad;\r\n01 Cheesy Pops;\r\n04 Drink 320ml.', 8, 21.27, 10, '2025-03-19 08:12:40', 'New', NULL, NULL, 'COMBO_CTVH.webp'),
(68, 'COMBO \"VUI NGAY DAI LE\"', '01 Pizza (Large): Nice To Meat You/ Veggie Lover/ Seafood Lover/ Supreme Slices/ Pizza Lava;\r\n02 Pizza (Regular): Nice To Meat You/ Veggie Lover/ Seafood Lover/ Supreme Slices/ Pizza Lava;\r\n01 Tuna Bacon Salad;\r\n01 Chicken Wings BBQ (4Pcs);\r\n01 Spaghetti Bolognese;\r\n04 Drink 320ml.', 8, 21.27, 8, '2025-03-19 08:12:40', 'New', NULL, NULL, 'COMBO_VNDL.webp'),
(69, 'TEST 1', 'nien luan chuyen nganh cntt', 3, 0.00, 2, '2025-04-06 11:17:31', NULL, NULL, '2025-04-10 05:00:00', NULL),
(70, 'TEST 2', 'ct446', 4, 5.00, 0, '2025-04-06 11:17:31', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('admin','customer') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `blocked_until` datetime DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `address`, `role`, `created_at`, `blocked_until`, `avatar`) VALUES
(1, 'ADMIN', 'loverhut.pizzastore@gmail.com', '$2y$10$fek773vMvBhXcl80pyAlEOyEVwwiNxJsa6Al0zwFLehd0.grRnNge', '0932822075', 'Phường An Khánh, Quận Ninh Kiều, Thành phố Cần Thơ, 94000, Việt Nam', 'admin', '2024-10-07 13:24:13', NULL, NULL),
(2, 'Huỳnh Đắc Vinh', 'dacvinhh@gmail.com', '$2y$10$fek773vMvBhXcl80pyAlEOyEVwwiNxJsa6Al0zwFLehd0.grRnNge', '0945203902', 'Hẻm 132, đường 3/2', 'customer', '2025-03-26 10:24:48', '2025-04-07 15:54:53', NULL),
(3, 'Phan Trung Thuận', 'thuantk000@gmail.com', '$2y$10$fek773vMvBhXcl80pyAlEOyEVwwiNxJsa6Al0zwFLehd0.grRnNge', '0349443461', 'KTX B, B5-206', 'customer', '2025-02-26 05:48:12', NULL, NULL),
(4, 'Huỳnh Loan', 'chauloan2004@gmail.com', '$2y$10$fek773vMvBhXcl80pyAlEOyEVwwiNxJsa6Al0zwFLehd0.grRnNge', '0914265926', 'Trần Hoàng Na, nhà trọ Gia Phúc', 'customer', '2025-02-28 09:05:27', '2025-04-07 15:55:28', NULL),
(5, 'Nguyễn Ngọc Trâm', 'ngoctram077682@gmail.com', '$2y$10$fek773vMvBhXcl80pyAlEOyEVwwiNxJsa6Al0zwFLehd0.grRnNge', '0703844538', 'Hẻm 51, đường 3/2', '', '2025-02-28 09:05:56', NULL, NULL),
(6, 'Ngô Quang Lâu', 'nvtoan.1706@gmail.com', '$2y$10$l6UiaTv2Z/jRHHEKyaSxK.4IBVTVnaeKbWerS0.QfxciLDqH9Y.Zy', NULL, NULL, 'customer', '2025-03-26 10:49:34', NULL, NULL),
(7, 'Trần Tấn Lộc', 'tanloc0979942603@gmail.com', '$2y$10$fek773vMvBhXcl80pyAlEOyEVwwiNxJsa6Al0zwFLehd0.grRnNge', '0945203902', 'KTX B, ĐHCT', 'customer', '2025-03-24 06:22:36', '2025-04-09 15:56:02', NULL),
(8, 'Lê Tấn Phát', 'phaletan1@gmail.com', '$2y$10$fek773vMvBhXcl80pyAlEOyEVwwiNxJsa6Al0zwFLehd0.grRnNge', NULL, '255, Tân phước 1, Thuận Hưng, Thốt Nốt, Cần Thơ', 'customer', '2025-03-24 06:24:10', NULL, NULL),
(9, 'Lê Quang Vinh', 'quangvinh.0126215641@gmail.com', '$2y$10$fek773vMvBhXcl80pyAlEOyEVwwiNxJsa6Al0zwFLehd0.grRnNge', '0914265926', 'KDC An Phú, đường số 44', 'customer', '2025-03-24 06:26:38', '2025-04-09 15:57:01', NULL),
(10, 'Nguyễn Văn Toàn', 'quykute007ct@gmail.com', '$2y$10$VDegzydlyiRdEvtLIB9cfOuxJvb3UbvoRLXaZ5jB2iTTLf3HLEEWC', '', '255, Tân Phước 1, Thuận Hưng, Thốt Nốt, Thành phố Cần Thơ', '', '2025-03-31 11:35:31', NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user_voucher`
--

CREATE TABLE `user_voucher` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `voucher_id` int(11) NOT NULL,
  `received_at` datetime DEFAULT current_timestamp(),
  `used_at` datetime DEFAULT NULL,
  `status` enum('unused','used','expired') DEFAULT 'unused'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `user_voucher`
--

INSERT INTO `user_voucher` (`id`, `user_id`, `voucher_id`, `received_at`, `used_at`, `status`) VALUES
(11, 2, 4, '2025-03-31 18:35:31', NULL, 'unused'),
(12, 3, 4, '2025-03-31 18:35:31', NULL, 'unused'),
(13, 4, 4, '2025-03-31 18:35:31', NULL, 'unused'),
(14, 5, 4, '2025-03-31 18:35:31', NULL, 'unused'),
(15, 6, 4, '2025-03-31 18:35:31', NULL, 'unused'),
(16, 7, 4, '2025-03-31 18:35:31', NULL, 'unused'),
(17, 8, 4, '2025-03-31 18:35:31', NULL, 'unused'),
(18, 9, 4, '2025-03-31 18:35:31', NULL, 'unused'),
(19, 10, 4, '2025-03-31 18:35:31', NULL, 'unused'),
(21, 1, 1, '2025-04-03 11:19:51', '2025-04-04 20:04:22', 'used'),
(22, 1, 2, '2025-04-03 11:19:59', NULL, 'unused'),
(23, 1, 1, '2025-04-04 22:00:15', NULL, 'unused'),
(24, 10, 2, '2025-04-06 14:12:51', NULL, 'unused'),
(25, 10, 3, '2025-04-06 14:13:02', NULL, 'unused'),
(26, 10, 5, '2025-04-06 14:13:06', '2025-04-06 14:16:33', 'used'),
(27, 2, 3, '2025-04-06 14:59:58', NULL, 'unused'),
(28, 2, 5, '2025-04-06 15:00:01', '2025-04-06 15:00:40', 'used'),
(29, 2, 2, '2025-04-06 15:00:04', NULL, 'unused'),
(30, 2, 6, '2025-04-06 15:00:08', NULL, 'unused'),
(31, 3, 2, '2025-04-06 15:06:42', NULL, 'unused'),
(32, 3, 3, '2025-04-06 15:06:45', NULL, 'unused'),
(33, 3, 5, '2025-04-06 15:06:52', '2025-04-06 15:07:19', 'used'),
(34, 4, 2, '2025-04-06 15:11:08', NULL, 'unused'),
(35, 4, 3, '2025-04-06 15:11:10', NULL, 'unused'),
(36, 4, 6, '2025-04-06 15:11:13', NULL, 'unused'),
(37, 8, 2, '2025-04-06 15:40:54', NULL, 'unused'),
(38, 8, 5, '2025-04-06 15:40:57', NULL, 'unused'),
(39, 8, 6, '2025-04-06 15:41:04', '2025-04-06 15:42:18', 'used'),
(40, 9, 2, '2025-04-06 15:42:46', NULL, 'unused');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `vouchers`
--

CREATE TABLE `vouchers` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `min_order_value` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expiration_date` datetime DEFAULT NULL,
  `status` enum('active','expired','used') DEFAULT 'active',
  `quantity` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `vouchers`
--

INSERT INTO `vouchers` (`id`, `code`, `description`, `discount_amount`, `min_order_value`, `created_at`, `expiration_date`, `status`, `quantity`) VALUES
(1, 'WELCOME20', 'Get 20% off on your first order', 0.20, 0.00, '2025-03-09 10:30:08', '2025-04-09 23:59:59', 'active', 1),
(2, 'FREESHIP', 'Get a $5 discount on shipping for orders over $50', 1.50, 50.00, '2025-03-09 10:30:08', '2025-04-09 23:59:59', 'active', 1),
(3, 'BIGDEAL50', 'Flat $50 off on orders above $200', 50.00, 200.00, '2025-03-09 10:30:08', '2025-04-09 23:59:59', 'active', 1),
(4, 'WEEKEND10', 'Exclusive Weekend Deal $10 Off', 10.00, 80.00, '2025-03-09 10:45:08', '2025-04-09 23:59:59', 'active', 1),
(5, 'LOYAL7', 'Loyalty Bonus $7 Off', 0.07, 20.00, '2025-03-09 10:45:08', '2025-04-09 23:59:59', 'active', 1),
(6, 'FLASH25', 'Flash Sale 25% Off Today Only!', 0.25, 0.00, '2025-03-09 10:45:08', '2025-04-09 23:59:59', 'active', 1);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_feedback_user` (`user_id`),
  ADD KEY `fk_feedback_order` (`order_id`);

--
-- Chỉ mục cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_orders_voucher` (`voucher_id`);

--
-- Chỉ mục cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Chỉ mục cho bảng `user_voucher`
--
ALTER TABLE `user_voucher`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `voucher_id` (`voucher_id`);

--
-- Chỉ mục cho bảng `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=368;

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT cho bảng `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT cho bảng `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT cho bảng `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `user_voucher`
--
ALTER TABLE `user_voucher`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT cho bảng `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `fk_feedback_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_feedback_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_voucher` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `user_voucher`
--
ALTER TABLE `user_voucher`
  ADD CONSTRAINT `user_voucher_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_voucher_ibfk_2` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
