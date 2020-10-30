-- phpMyAdmin SQL Dump
-- version 4.8.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 06, 2020 at 04:33 PM
-- Server version: 10.1.32-MariaDB
-- PHP Version: 7.2.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `digital_marketplace`
--

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `ID` int(11) NOT NULL,
  `category_name` text COLLATE utf8_unicode_ci NOT NULL,
  `color` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`ID`, `category_name`, `color`) VALUES
(1, 'Cameras', 'teal'),
(2, 'Lens', 'blue'),
(3, 'Video cameras', 'purple'),
(4, 'Drones', 'yellow'),
(5, 'Accessories', 'red');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `ID` int(11) NOT NULL,
  `user_ID` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `ad_ID` text COLLATE utf8_unicode_ci NOT NULL,
  `purchase_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `ID` int(11) NOT NULL,
  `email` text CHARACTER SET utf8 NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` text CHARACTER SET utf8 NOT NULL,
  `surname` text COLLATE utf8_unicode_ci NOT NULL,
  `address` text CHARACTER SET utf8 NOT NULL,
  `postcode` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `phone` int(11) NOT NULL,
  `user_ip` text COLLATE utf8_unicode_ci NOT NULL,
  `login_attempts` int(11) NOT NULL,
  `status` text COLLATE utf8_unicode_ci NOT NULL,
  `image` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`ID`, `email`, `password`, `name`, `surname`, `address`, `postcode`, `phone`, `user_ip`, `login_attempts`, `status`, `image`) VALUES
(1, 'test01@email.com', '$2y$10$QcOBAHm5fberPTlpr4/z5.FdzXfPhsMakGsKOIjHVf6iGbJNEOcPi', 'User01', 'Surname01', 'Address str 01', '8052', 784123456, '::1', 0, 'active', 'images/users/1593939093_606d3314945417.5a28f86c7511b.png'),
(2, 'test02@email.com', '$2y$10$dZXm3Dj7ri/4c3X45zaX4udDXXvcOpoloEvtfudGJEmyn0ksvd7FG', 'User02', 'Surname02', 'Address str 02', '8304', 784123456, '::1', 0, 'active', 'images/users/1593939018_Untitled.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `user_ads`
--

CREATE TABLE `user_ads` (
  `ID` int(11) NOT NULL,
  `title` text CHARACTER SET utf8 NOT NULL,
  `category_ID` text CHARACTER SET utf8 NOT NULL,
  `user_ID` int(11) NOT NULL,
  `publish_date` datetime DEFAULT NULL,
  `condition` text COLLATE utf8_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8 NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` text CHARACTER SET utf8 NOT NULL,
  `image` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `user_ads`
--

INSERT INTO `user_ads` (`ID`, `title`, `category_ID`, `user_ID`, `publish_date`, `condition`, `description`, `price`, `status`, `image`) VALUES
(1, 'Canon EF 50mm f/1.8 STM', '2', 1, '2020-07-05 11:15:03', 'new', 'Das Objektiv hat eine kompakte und leichte Bauform mit einem Gewicht von lediglich 160 Gramm. Es passt problemlos in die Kameratasche, ohne sich erheblich auf das Gesamtgewicht der AusrÃ¼stung auszuwirken. Das neue Design des Objektivs passt sowohl zu den Einsteiger- als auch den semi-professionellen EOS Kameras und Ã¼berzeugt mit einem Objektivbajonett aus Metall fÃ¼r eine robuste Verbindung mit dem KameragehÃ¤use. ZusÃ¤tzlich gestattet das Objektiv mit seinem manuellen Fokussierring die Freiheit, die SchÃ¤rfeebene exakt zu steuern.', '105.00', 'published', 'images/product_images/1593940503_lens01.jpg'),
(2, 'Canon EOS Kiss X7i (700D) 18.0 MP', '2', 1, '0000-00-00 00:00:00', 'used', 'Step into DSLR photography and let your creativity grow. Produce superb photos and video with an 18-megapixel sensor and enjoy shooting with an easy to use Vari-angle Clear View LCD II Touch screen.', '210.00', 'draft', 'images/product_images/1593940933_lens02.jpeg'),
(3, 'Canon PowerShot SX70 HS', '1', 1, '2020-07-05 11:25:17', 'used', 'The PowerShot SX70 HS is the top model in Canon&#39;s Bridge series with a DSLR look. It is equipped with the latest technology and helps you to capture the special moments in breathtaking pictures. The premium DSLR-look camera with 65x optical zoom and versatile advanced features. RAW recording, 4K video, BluetoothÂ® and WLAN as well as live image viewing on the mobile device - this is what versatile recording options look like. The all-round camera that looks and feels like a DSLR while offering the flexibility and weight of a compact camera. You can shoot anything from distant details to macros without changing lenses. Motifs far away can be captured without loss of quality. In contrast, the macro mode offers a close-up limit of 0 cm and captures details that are invisible to the naked eye. Recordings in RAW or compact RAW format offer high flexibility in post-production.', '479.00', 'published', 'images/product_images/1593941117_camera01.jpg'),
(4, 'Canon EOS 800D Kit', '1', 2, '2020-07-05 11:27:53', 'new', '\r\nThe latest generation CMOS sensor in APS-C format can capture even more detail with 24.2 megapixels\r\n\r\nThe EOS 800D enables fast working with continuous shooting at up to 6 frames per second and with the bright optical viewfinder, which always allows delay-free viewing. When you use the camera&#39;s rotate and tilt display to compose images, the world&#39;s fastest Live View AF system* provides precise focus in just 0.03 seconds.', '680.00', 'published', 'images/product_images/1593941273_camera02.jpg'),
(5, 'AF Micro-NIKKOR 60mm f/2.8D Lens', '2', 2, '2020-07-05 11:29:31', 'used', 'The Nikon AF 60mm f/2.8D macro lens provides an exceptional range of applications and high-quality performance. Close-range correction system ensures high resolution from infinity to close-ups down to 8 3/4&#34; (22Cm)(1: 1 reproduction ratio). A-M (automatic-manual) switch enables quick changing from autofocus to manual focus.Note! Af not supported by D40, D60, D3000 & D5000 cameras. The most compact Nikon Micro-NIKKOR lens for portrait, copy work, and field close-up applications.Close range correction (CRC) system provides high performance at both near and far focusing distances.Continuous focusing from infinity to life-size (1: 1).', '456.00', 'published', 'images/product_images/1593941371_lens03.jpg'),
(6, 'Nikon D7500 Body (21.51Mpx, APS-C / DX)', '1', 2, '0000-00-00 00:00:00', 'used', '\r\nOutstanding in low light. With sensitivity settings from ISO 100 to ISO 51,200: The fast image processing engine EXPEED 5 ensures remarkable image quality over the entire ISO range. Even the finest noise is significantly reduced. Even at high ISO sensitivity, detail enlargements do not lose quality. The &#34;Hi 5&#34; setting corresponds to the incredible ISO value of 1,640,000 and makes the most of the available residual light. ', '620.00', 'draft', 'images/product_images/1593941463_camera03.jpg'),
(7, 'Lowepro Nova 170 AW II (Shoulder bags)', '5', 2, '2020-07-05 11:32:41', 'new', 'Store, transport and protect your DSLR with 17-85mm lens attached, 1-2 additional lenses, flash and accessories with the black Nova 170 AW II camera bag from Lowepro', '62.00', 'published', 'images/product_images/1593941561_accessories01.jpg'),
(8, 'Dji Mavic 2 Pro (4K)', '4', 1, '2020-07-05 11:34:53', 'new', 'The DJI Mavic 2 Pro drone comes with a 1&#34; CMOS sensor for higher image quality with excellent light and color performance. The new L1D-20c camera was developed by Hasselblad in collaboration with DJI and comes with a lens with adjustable aperture.', '1259.00', 'published', 'images/product_images/1593941693_drone.jpg'),
(9, 'Nikon D7500 Kit', '1', 1, '2020-07-05 11:37:05', 'used', 'The fast, versatile DSLR for passionate photographers features a wide ISO range and impressive video capabilities.', '1128.00', 'published', 'images/product_images/1593941825_camera04.jpg'),
(10, 'Sony NP-FM500H (Rechargeable battery)', '5', 1, '2020-07-05 11:38:49', 'new', 'Sony NPF M500H - Camera battery Li-Ion 1650 mAh - for a (Alpha) DSLR-A100, A500, A550, A560, A580, SLT-A57, A58, A65, A77, a77 II (Alpha 77 II) (NPFM500)', '64.00', 'published', 'images/product_images/1593941929_accessories02.jpg'),
(11, 'Tascam AK-DR11C (accessory kit)', '5', 1, '0000-00-00 00:00:00', 'new', 'AK-DR11C package for connection to a DSLR or video camera', '43.10', 'draft', 'images/product_images/1593941991_accessories03.jpg'),
(12, 'Panasonic HC-W580EP-K Video camera', '3', 2, '2020-07-05 11:45:15', 'used', 'Panasonic HC-W580 Camcorder (2.51 MP, 50 x Zoom, FHD, Wi-Fi, 3 inch LCD) - Black.', '289.00', 'published', 'images/product_images/1593942315_video.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `user_ads`
--
ALTER TABLE `user_ads`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_ads`
--
ALTER TABLE `user_ads`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
