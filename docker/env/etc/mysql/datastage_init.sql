#
# SQL Export
# Created by Querious (201042)
# Created: September 24, 2018 at 11:35:55 GMT-3
# Encoding: Unicode (UTF-8)
#


DROP DATABASE IF EXISTS `edge`;
CREATE DATABASE `edge` DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_bin;
USE `edge`;




SET @PREVIOUS_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS = 0;


DROP TABLE IF EXISTS `vendor`;
DROP TABLE IF EXISTS `dump_type`;
DROP TABLE IF EXISTS `dump_job`;
DROP TABLE IF EXISTS `dump_data`;


CREATE TABLE `dump_data` (
  `id_djob` int(11) NOT NULL DEFAULT '0',
  `id_vendor` int(11) NOT NULL DEFAULT '0',
  `vendor_art` int(11) NOT NULL DEFAULT '0',
  `vendor_desc` varchar(512) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `vendor_uxb` decimal(10,5) DEFAULT '0.00000',
  `vendor_prc` decimal(10,5) DEFAULT '0.00000',
  `ts` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_djob`,`vendor_art`,`id_vendor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


CREATE TABLE `dump_job` (
  `id_djob` int(11) NOT NULL AUTO_INCREMENT,
  `id_dtype` int(11) NOT NULL DEFAULT '0',
  `hash` varchar(128) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `url` varchar(2048) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `request` varchar(1024) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT '',
  `response` varchar(1024) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT '',
  `ts_begin` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ts_end` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_djob`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


CREATE TABLE `dump_type` (
  `id_dtype` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(512) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `processor` varchar(512) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`id_dtype`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


CREATE TABLE `vendor` (
  `id_vendor` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_vendor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;




SET FOREIGN_KEY_CHECKS = @PREVIOUS_FOREIGN_KEY_CHECKS;


DROP PROCEDURE IF EXISTS `dump_job_new`;


DELIMITER //
CREATE DEFINER=`root`@`%` PROCEDURE `dump_job_new`(p_id_dtype int, p_url varchar (2048), p_request varchar (1024), OUT o_id_djob int, OUT o_hash varchar(128), OUT o_result int)
    MODIFIES SQL DATA
    DETERMINISTIC
BEGIN
DECLARE v_dtype_exists INT;

-- Vars
SET v_dtype_exists := 0;
SET o_result := 1;
SET o_hash := ( SELECT CAST(UUID() AS CHAR) );

-- Validate
SELECT COUNT(1) 
  INTO v_dtype_exists 
  FROM dump_job 
  WHERE id_dtype = p_id_dtype > 0;

IF (o_result = 1 
     AND p_id_dtype IS NOT NULL 
     AND v_dtype_exists > 0) THEN
  SET o_result := 1;    
ELSE
  SET o_result := 0;
END IF;

IF (o_result = 1 
     AND p_url IS NOT NULL) THEN
  SET o_result := 1;   
ELSE
  SET o_result := 0;
END IF;

-- Ejecutar
IF (o_result = 0) THEN
  SET o_result := 0;
ELSE

  INSERT INTO dump_job ( id_dtype, hash, url, request ) 
    VALUES ( p_id_dtype, o_hash, p_url, p_request );

  SELECT id_djob INTO o_id_djob 
    FROM dump_job WHERE hash like o_hash;
  
  COMMIT;
  
END IF;

END;
//
DELIMITER ;




SET @PREVIOUS_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS = 0;


LOCK TABLES `dump_data` WRITE;
TRUNCATE `dump_data`;
ALTER TABLE `dump_data` DISABLE KEYS;
ALTER TABLE `dump_data` ENABLE KEYS;
UNLOCK TABLES;


LOCK TABLES `dump_job` WRITE;
TRUNCATE `dump_job`;
ALTER TABLE `dump_job` DISABLE KEYS;
INSERT INTO `dump_job` (`id_djob`, `id_dtype`, `hash`, `url`, `request`, `response`, `ts_begin`, `ts_end`) VALUES 
	(1,1,'jjj','jkjkjkj','kjk','kjk','2018-09-21 20:39:06',NULL),
	(2,0,NULL,'jjj','','','2018-09-21 20:39:07',NULL),
	(3,1,'a7b86931-bdde-11e8-9b6d-0242ac110002','http://www.google.com.ar/','{"a": "1"}','','2018-09-21 20:41:02',NULL),
	(4,1,'c9946378-bdde-11e8-9b6d-0242ac110002','http://www.google.com.ar/','{"a": "1"}','','2018-09-21 20:41:59',NULL),
	(5,1,'d5ab33b7-bdde-11e8-9b6d-0242ac110002','http://www.google.com.ar/','{"a": "1"}','','2018-09-21 20:42:19',NULL),
	(6,1,'e4d69a17-bdde-11e8-9b6d-0242ac110002','http://www.google.com.ar/','{"a": "1"}','','2018-09-21 20:42:45',NULL);
ALTER TABLE `dump_job` ENABLE KEYS;
UNLOCK TABLES;


LOCK TABLES `dump_type` WRITE;
TRUNCATE `dump_type`;
ALTER TABLE `dump_type` DISABLE KEYS;
INSERT INTO `dump_type` (`id_dtype`, `name`, `processor`) VALUES 
	(1,'masivos s.a.','datastage/masivos/run'),
	(2,'golomax','datastage/golomax/run');
ALTER TABLE `dump_type` ENABLE KEYS;
UNLOCK TABLES;


LOCK TABLES `vendor` WRITE;
TRUNCATE `vendor`;
ALTER TABLE `vendor` DISABLE KEYS;
ALTER TABLE `vendor` ENABLE KEYS;
UNLOCK TABLES;




SET FOREIGN_KEY_CHECKS = @PREVIOUS_FOREIGN_KEY_CHECKS;


