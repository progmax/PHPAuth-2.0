-- PHPAuth 2.0 SQL Structure Dump

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `phpauth2.0`
--

-- --------------------------------------------------------

--
-- Table structure for table `activations`
--

CREATE TABLE IF NOT EXISTS `activations` (
  `uid` int(11) NOT NULL,
  `activekey` varchar(20) CHARACTER SET utf8_bin NOT NULL,
  `expiredate` datetime NOT NULL,
  KEY `activekey` (`activekey`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `attempts`
--

CREATE TABLE IF NOT EXISTS `attempts` (
  `ip` varchar(15) CHARACTER SET utf8_bin NOT NULL,
  `count` int(11) NOT NULL,
  `expiredate` datetime NOT NULL,
  KEY `ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `log`
--

CREATE TABLE IF NOT EXISTS `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) CHARACTER SET urf8_bin NOT NULL DEFAULT 'UNKNOWN' COMMENT 'Username or UID',
  `action` varchar(100) CHARACTER SET utf8_bin NOT NULL,
  `info` varchar(1000) CHARACTER SET utf8_bin NOT NULL DEFAULT 'None provided',
  `ip` varchar(15) CHARACTER SET utf8_bin NOT NULL DEFAULT '0.0.0.0',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `resets`
--

CREATE TABLE IF NOT EXISTS `resets` (
  `uid` int(11) NOT NULL,
  `resetkey` varchar(20) CHARACTER SET utf8_bin NOT NULL,
  `expiredate` datetime NOT NULL,
  KEY `uid` (`uid`),
  KEY `resetkey` (`resetkey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `hash` varchar(40) CHARACTER SET utf8_bin NOT NULL,
  `expiredate` datetime NOT NULL,
  `ip` varchar(15) CHARACTER SET utf8_bin NOT NULL,
  `agent` varchar(200) CHARACTER SET utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hash` (`hash`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) CHARACTER SET utf8_bin NOT NULL,
  `password` varchar(128) CHARACTER SET utf8_bin NOT NULL,
  `email` varchar(100) CHARACTER SET utf8_bin NOT NULL,
  `isactive` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
