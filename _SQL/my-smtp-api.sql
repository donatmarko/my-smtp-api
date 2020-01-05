SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `apikeys` (
  `id` int(11) NOT NULL,
  `apikey` varchar(64) NOT NULL,
  `name` varchar(40) NOT NULL COMMENT 'Name of API key user',
  `email` varchar(40) NOT NULL COMMENT 'Email of API key user',
  `ip` varchar(32) NOT NULL COMMENT 'IP restriction regex. Empty = not restricted',
  `notes` mediumtext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE `log` (
  `id` int(11) NOT NULL,
  `datetime` datetime NOT NULL,
  `apikey` varchar(64) NOT NULL,
  `mail` text NOT NULL,
  `response` text NOT NULL,
  `http_referer` varchar(64) NOT NULL,
  `http_user_agent` varchar(64) NOT NULL,
  `remote_addr` varchar(64) NOT NULL,
  `remote_host` varchar(64) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;


ALTER TABLE `apikeys`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `log`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `apikeys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
