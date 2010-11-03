/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table exported
# ------------------------------------------------------------

CREATE TABLE `exported` (
  `paymentID` int(11) NOT NULL,
  `CallerTID` bigint(20) NOT NULL,
  `Prefix` varchar(64) default NULL,
  `FirstName` varchar(255) default NULL,
  `MiddleName` varchar(64) default NULL,
  `LastName` varchar(255) default NULL,
  `Suffix` varchar(64) default NULL,
  `Address1` varchar(255) default NULL,
  `Address2` varchar(255) default NULL,
  `City` varchar(255) default NULL,
  `State` char(2) default NULL,
  `Zip` char(5) default NULL,
  `Plus4` char(4) default NULL,
  `Email` varchar(255) default NULL,
  `Phone_Home` varchar(32) default NULL,
  `Phone_Work` varchar(32) default NULL,
  `Employer` varchar(255) default NULL,
  `Occupation` varchar(255) default NULL,
  `GiftDate` varchar(64) default NULL,
  `GiftAmount` float(6,2) NOT NULL,
  `Card_Name_on` varchar(255) NOT NULL,
  `CardNo` char(16) NOT NULL default '************0000',
  `Card_Exp_Month` varchar(16) default NULL,
  `Card_Exp_Year` varchar(16) default NULL,
  `More_in_XML` mediumtext,
  `export_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `export_timestamp` int(11) NOT NULL,
  `cron_status` smallint(6) NOT NULL default '0',
  `additional_info` mediumtext,
  PRIMARY KEY  (`paymentID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table log
# ------------------------------------------------------------

CREATE TABLE `log` (
  `logid` bigint(20) unsigned NOT NULL auto_increment,
  `log_message` mediumtext NOT NULL,
  `log_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`logid`),
  UNIQUE KEY `logid` (`logid`)
) ENGINE=InnoDB AUTO_INCREMENT=2560 DEFAULT CHARSET=latin1;



# Dump of table page_marker
# ------------------------------------------------------------

CREATE TABLE `page_marker` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `name` mediumtext NOT NULL,
  `value` int(11) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;






/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
