/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Database structure for bookstore
DROP DATABASE IF EXISTS `bookstore`;
CREATE DATABASE IF NOT EXISTS `bookstore` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `bookstore`;

-- Structure for taulu bookstore.Asiakas
DROP TABLE IF EXISTS `Asiakas`;
CREATE TABLE IF NOT EXISTS `Asiakas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nimi` varchar(50) NOT NULL DEFAULT '0',
  `osoite` varchar(50) NOT NULL DEFAULT '0',
  `postinumero` varchar(50) NOT NULL DEFAULT '0',
  `postitoimipaikka` varchar(50) NOT NULL DEFAULT '0',
  `email` varchar(50) NOT NULL DEFAULT '0',
  `salasana` varchar(80) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Structure for taulu bookstore.Hallinta
DROP TABLE IF EXISTS `Hallinta`;
CREATE TABLE IF NOT EXISTS `Hallinta` (
  `tunnus` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `arvo` varchar(80) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`tunnus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Structure for taulu bookstore.Julkaisija
DROP TABLE IF EXISTS `Julkaisija`;
CREATE TABLE IF NOT EXISTS `Julkaisija` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `julkaisija` varchar(50) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Structure for taulu bookstore.Kansityyppi
DROP TABLE IF EXISTS `Kansityyppi`;
CREATE TABLE IF NOT EXISTS `Kansityyppi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tyyppi` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Structure for taulu bookstore.Kirjailija
DROP TABLE IF EXISTS `Kirjailija`;
CREATE TABLE IF NOT EXISTS `Kirjailija` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kirjailija` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Structure for taulu bookstore.Teos
DROP TABLE IF EXISTS `Teos`;
CREATE TABLE IF NOT EXISTS `Teos` (
  `isbn` bigint(20) NOT NULL,
  `teos` varchar(50) NOT NULL,
  `kirjailija_id` int(11) NOT NULL,
  `julkaisija_id` int(11) NOT NULL,
  `kansityyppi_id` int(11) NOT NULL,
  `kuvaus` varchar(500) NOT NULL,
  `varastosaldo` int(11) NOT NULL,
  `hinta` double NOT NULL,
  PRIMARY KEY (`isbn`),
  KEY `FK_Teos_Julkaisija` (`julkaisija_id`),
  KEY `FK_Teos_Kansityyppi` (`kansityyppi_id`),
  KEY `FK_Teos_Kirjailija` (`kirjailija_id`),
  CONSTRAINT `FK_Teos_Julkaisija` FOREIGN KEY (`julkaisija_id`) REFERENCES `Julkaisija` (`id`),
  CONSTRAINT `FK_Teos_Kansityyppi` FOREIGN KEY (`kansityyppi_id`) REFERENCES `Kansityyppi` (`id`),
  CONSTRAINT `FK_Teos_Kirjailija` FOREIGN KEY (`kirjailija_id`) REFERENCES `Kirjailija` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Structure for taulu bookstore.Tila
DROP TABLE IF EXISTS `Tila`;
CREATE TABLE IF NOT EXISTS `Tila` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tyyppi` varchar(50) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Structure for taulu bookstore.Tilaus
DROP TABLE IF EXISTS `Tilaus`;
CREATE TABLE IF NOT EXISTS `Tilaus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nimi` varchar(50) NOT NULL DEFAULT '0',
  `osoite` varchar(50) NOT NULL DEFAULT '0',
  `postinumero` varchar(50) NOT NULL DEFAULT '0',
  `postitoimipaikka` varchar(50) NOT NULL DEFAULT '0',
  `tila_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_Tilaus_Tila` (`tila_id`),
  CONSTRAINT `FK_Tilaus_Tila` FOREIGN KEY (`tila_id`) REFERENCES `Tila` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Structure for taulu bookstore.Tuotetilaus
DROP TABLE IF EXISTS `Tuotetilaus`;
CREATE TABLE IF NOT EXISTS `Tuotetilaus` (
  `tilaus_id` int(11) DEFAULT NULL,
  `teos_isbn` bigint(20) DEFAULT NULL,
  `kpl` int(11) DEFAULT NULL,
  KEY `FK__Teos` (`teos_isbn`),
  KEY `FK__Tilaus` (`tilaus_id`),
  CONSTRAINT `FK__Teos` FOREIGN KEY (`teos_isbn`) REFERENCES `Teos` (`isbn`) ON DELETE CASCADE,
  CONSTRAINT `FK__Tilaus` FOREIGN KEY (`tilaus_id`) REFERENCES `Tilaus` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
