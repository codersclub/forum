-- MySQL dump 10.13  Distrib 5.1.35, for unknown-freebsd7.2 (x86_64)
--
-- Host: localhost    Database: invision
-- ------------------------------------------------------
-- Server version	5.1.35-log
/*

dump command:

mysqldump --default-character-set=cp1251 --no-data invision > db_struct.sql

*/
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES cp1251 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

SET NAMES cp1251;

USE `invision`;

--
-- Table structure for table `cc_module`
--


DROP TABLE IF EXISTS `cc_module`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cc_module` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module` varchar(64) NOT NULL DEFAULT '',
  `title` varchar(64) NOT NULL DEFAULT '',
  `icon` varchar(64) NOT NULL DEFAULT '',
  `active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `system` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `date` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `active` (`active`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=cp1251 PACK_KEYS=0;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `client_members`
--

DROP TABLE IF EXISTS `client_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_members` (
  `nickname` varchar(30) NOT NULL DEFAULT '',
  `email` varchar(50) NOT NULL DEFAULT '',
  `date` date NOT NULL DEFAULT '0000-00-00',
  `paid` smallint(6) NOT NULL DEFAULT '20',
  `comment` varchar(100) DEFAULT NULL,
  UNIQUE KEY `nickname` (`nickname`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_admin_foreign_visits`
--

DROP TABLE IF EXISTS `ibf_admin_foreign_visits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_admin_foreign_visits` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IP_ADDRESS` varchar(16) DEFAULT NULL,
  `content` text,
  PRIMARY KEY (`id`),
  KEY `dt` (`dt`)
) ENGINE=MyISAM AUTO_INCREMENT=3100 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_admin_logs`
--

DROP TABLE IF EXISTS `ibf_admin_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_admin_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `act` varchar(255) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `member_id` int(10) DEFAULT NULL,
  `ctime` int(10) DEFAULT NULL,
  `note` text,
  `ip_address` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9432 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_admin_sessions`
--

DROP TABLE IF EXISTS `ibf_admin_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_admin_sessions` (
  `ID` varchar(32) NOT NULL DEFAULT '',
  `IP_ADDRESS` varchar(32) NOT NULL DEFAULT '',
  `MEMBER_NAME` varchar(32) NOT NULL DEFAULT '',
  `MEMBER_ID` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `SESSION_KEY` varchar(32) NOT NULL DEFAULT '',
  `LOCATION` varchar(64) DEFAULT 'index',
  `LOG_IN_TIME` int(10) NOT NULL DEFAULT '0',
  `RUNNING_TIME` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_b_visitors`
--

DROP TABLE IF EXISTS `ibf_b_visitors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_b_visitors` (
  `id` char(32) NOT NULL DEFAULT '',
  `ip_address` char(16) NOT NULL DEFAULT '',
  `day` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `month` tinyint(2) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`ip_address`,`day`,`month`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_badtittles`
--

DROP TABLE IF EXISTS `ibf_badtittles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_badtittles` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `mid` int(8) unsigned NOT NULL DEFAULT '0',
  `title` tinytext NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `mid` (`mid`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_badwords`
--

DROP TABLE IF EXISTS `ibf_badwords`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_badwords` (
  `wid` int(3) NOT NULL AUTO_INCREMENT,
  `type` varchar(250) NOT NULL DEFAULT '',
  `swop` varchar(250) DEFAULT NULL,
  `m_exact` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`wid`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_boards_visibility`
--

DROP TABLE IF EXISTS `ibf_boards_visibility`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_boards_visibility` (
  `id` int(11) NOT NULL DEFAULT '0',
  `is_forum` tinyint(1) NOT NULL DEFAULT '0',
  `is_visible` tinyint(1) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_cache_store`
--

DROP TABLE IF EXISTS `ibf_cache_store`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_cache_store` (
  `cs_key` varchar(255) NOT NULL DEFAULT '',
  `cs_value` text NOT NULL,
  `cs_extra` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`cs_key`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_calendar_events`
--

DROP TABLE IF EXISTS `ibf_calendar_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_calendar_events` (
  `eventid` mediumint(8) NOT NULL AUTO_INCREMENT,
  `userid` mediumint(8) NOT NULL DEFAULT '0',
  `year` int(4) NOT NULL DEFAULT '2002',
  `month` int(2) NOT NULL DEFAULT '1',
  `mday` int(2) NOT NULL DEFAULT '1',
  `title` varchar(254) NOT NULL DEFAULT 'no title',
  `event_text` text NOT NULL,
  `read_perms` varchar(254) NOT NULL DEFAULT '*',
  `unix_stamp` int(10) NOT NULL DEFAULT '0',
  `priv_event` tinyint(1) NOT NULL DEFAULT '0',
  `show_emoticons` tinyint(1) NOT NULL DEFAULT '1',
  `rating` smallint(2) NOT NULL DEFAULT '1',
  `event_ranged` tinyint(1) NOT NULL DEFAULT '0',
  `event_repeat` tinyint(1) NOT NULL DEFAULT '0',
  `repeat_unit` char(2) NOT NULL DEFAULT '',
  `end_day` int(2) DEFAULT NULL,
  `end_month` int(2) DEFAULT NULL,
  `end_year` int(4) DEFAULT NULL,
  `end_unix_stamp` int(10) DEFAULT NULL,
  `event_bgcolor` varchar(32) NOT NULL DEFAULT '',
  `event_color` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`eventid`),
  KEY `unix_stamp` (`unix_stamp`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_categories`
--

DROP TABLE IF EXISTS `ibf_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_categories` (
  `id` smallint(5) NOT NULL DEFAULT '0',
  `position` tinyint(3) DEFAULT NULL,
  `state` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `name` varchar(128) NOT NULL DEFAULT '',
  `description` text,
  `image` varchar(128) DEFAULT NULL,
  `url` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `state` (`state`),
  KEY `position` (`position`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_check_members`
--

DROP TABLE IF EXISTS `ibf_check_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_check_members` (
  `mid` int(10) unsigned NOT NULL DEFAULT '0',
  `last_visit` int(10) unsigned NOT NULL DEFAULT '0',
  `sent` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`mid`),
  KEY `last_visit` (`last_visit`,`sent`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_cms_articles_watchdog`
--

DROP TABLE IF EXISTS `ibf_cms_articles_watchdog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_cms_articles_watchdog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) NOT NULL DEFAULT '0',
  `aid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `article_id` (`aid`)
) ENGINE=MyISAM AUTO_INCREMENT=51 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_cms_comments`
--

DROP TABLE IF EXISTS `ibf_cms_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_cms_comments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `comment` text NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `user_name` varchar(255) NOT NULL DEFAULT '',
  `submit_date` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_cms_comments_links`
--

DROP TABLE IF EXISTS `ibf_cms_comments_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_cms_comments_links` (
  `base` int(11) NOT NULL DEFAULT '0',
  `refs` int(11) NOT NULL DEFAULT '0',
  KEY `base` (`base`,`refs`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_cms_content`
--

DROP TABLE IF EXISTS `ibf_cms_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_cms_content` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `path` varchar(255) NOT NULL DEFAULT '',
  `rights` varchar(16) NOT NULL DEFAULT '000000000',
  `owner` int(8) unsigned NOT NULL DEFAULT '0',
  `ogroup` int(8) unsigned NOT NULL DEFAULT '0',
  `description` text,
  UNIQUE KEY `path` (`path`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_cms_groups`
--

DROP TABLE IF EXISTS `ibf_cms_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_cms_groups` (
  `gid` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `gname` varchar(15) NOT NULL DEFAULT '',
  `gdescription` text NOT NULL,
  `gedit_post` tinyint(1) NOT NULL DEFAULT '0',
  `gadd_post` tinyint(1) NOT NULL DEFAULT '1',
  `gapprove_post` tinyint(1) NOT NULL DEFAULT '0',
  `gdelete_post` tinyint(1) NOT NULL DEFAULT '0',
  `gmove_posts` tinyint(1) NOT NULL DEFAULT '0',
  `g_add_attach` tinyint(1) NOT NULL DEFAULT '1',
  `g_delete_attach` tinyint(1) NOT NULL DEFAULT '1',
  `g_max_attach_size` int(11) NOT NULL DEFAULT '0',
  `gview_comments` tinyint(1) NOT NULL DEFAULT '1',
  `gpost_comment` tinyint(1) NOT NULL DEFAULT '1',
  `gedit_comment` tinyint(1) NOT NULL DEFAULT '0',
  `gview_posts` tinyint(1) NOT NULL DEFAULT '1',
  `gdelete_comments` tinyint(1) NOT NULL DEFAULT '0',
  KEY `id` (`gid`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_cms_moderators`
--

DROP TABLE IF EXISTS `ibf_cms_moderators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_cms_moderators` (
  `mid` mediumint(8) NOT NULL AUTO_INCREMENT,
  `forum_id` int(5) NOT NULL DEFAULT '0',
  `member_name` varchar(32) NOT NULL DEFAULT '',
  `member_id` mediumint(8) NOT NULL DEFAULT '0',
  `edit_post` tinyint(4) NOT NULL DEFAULT '0',
  `delete_post` tinyint(4) NOT NULL DEFAULT '0',
  `approve_post` int(11) NOT NULL DEFAULT '0',
  `is_group` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `mid` (`mid`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_cms_subscriptions`
--

DROP TABLE IF EXISTS `ibf_cms_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_cms_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) DEFAULT '0',
  `article_version` int(11) NOT NULL DEFAULT '1',
  `category_id` int(11) DEFAULT '0',
  `member_id` int(11) DEFAULT '0',
  `type` enum('favorite','subscribe') DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_cms_uploads`
--

DROP TABLE IF EXISTS `ibf_cms_uploads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_cms_uploads` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `version_id` int(11) NOT NULL DEFAULT '1',
  `name` varchar(63) NOT NULL DEFAULT '',
  `short_desc` varchar(255) NOT NULL DEFAULT '',
  `article` text NOT NULL,
  `hits` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `author_name` varchar(255) NOT NULL DEFAULT '',
  `submit_date` int(11) NOT NULL DEFAULT '0',
  `icon_id` int(11) NOT NULL DEFAULT '0',
  `approved` int(11) unsigned DEFAULT NULL,
  `article_id` varchar(63) NOT NULL DEFAULT '',
  KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_cms_uploads_cat`
--

DROP TABLE IF EXISTS `ibf_cms_uploads_cat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_cms_uploads_cat` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `category_id` varchar(63) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `num` int(11) NOT NULL DEFAULT '0',
  `always_empty` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `visible` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `allow_posts` tinyint(1) NOT NULL DEFAULT '1',
  `one_article` tinyint(1) NOT NULL DEFAULT '1',
  `add_article_form` tinyint(1) NOT NULL DEFAULT '0',
  `redirect_url` varchar(255) DEFAULT NULL,
  `moderate` tinyint(1) NOT NULL DEFAULT '1',
  `ord` int(11) NOT NULL DEFAULT '0',
  `show_subcats` tinyint(1) NOT NULL DEFAULT '1',
  `allow_comments` tinyint(1) NOT NULL DEFAULT '1',
  `show_fullscreen` tinyint(1) DEFAULT '1',
  `show_smilies` tinyint(1) NOT NULL DEFAULT '1',
  `force_versioning` tinyint(1) NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=202 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_cms_uploads_cat_links`
--

DROP TABLE IF EXISTS `ibf_cms_uploads_cat_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_cms_uploads_cat_links` (
  `base` int(11) NOT NULL DEFAULT '0',
  `refs` int(11) NOT NULL DEFAULT '0',
  `current_version` int(11) NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_cms_uploads_file_links`
--

DROP TABLE IF EXISTS `ibf_cms_uploads_file_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_cms_uploads_file_links` (
  `base` int(11) NOT NULL DEFAULT '0',
  `refs` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_cms_uploads_files`
--

DROP TABLE IF EXISTS `ibf_cms_uploads_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_cms_uploads_files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(63) NOT NULL DEFAULT '',
  `path` varchar(255) NOT NULL DEFAULT '',
  `mime` varchar(63) NOT NULL DEFAULT '',
  `hits` int(11) NOT NULL DEFAULT '0',
  KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_cms_views`
--

DROP TABLE IF EXISTS `ibf_cms_views`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_cms_views` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT '0',
  `b_order` int(7) DEFAULT NULL,
  `bname` varchar(63) DEFAULT NULL,
  `bcaption` varchar(63) DEFAULT NULL,
  `bdescription` varchar(63) DEFAULT NULL,
  `break` tinyint(3) DEFAULT NULL,
  `visible` tinyint(1) DEFAULT '1',
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1003 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_contacts`
--

DROP TABLE IF EXISTS `ibf_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_contacts` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `contact_id` mediumint(8) NOT NULL DEFAULT '0',
  `member_id` mediumint(8) NOT NULL DEFAULT '0',
  `contact_name` varchar(32) NOT NULL DEFAULT '',
  `allow_msg` tinyint(1) DEFAULT NULL,
  `show_online` tinyint(1) NOT NULL DEFAULT '0',
  `contact_desc` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`,`contact_id`),
  KEY `show_online` (`show_online`)
) ENGINE=MyISAM AUTO_INCREMENT=3259 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_css`
--

DROP TABLE IF EXISTS `ibf_css`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_css` (
  `cssid` int(10) NOT NULL AUTO_INCREMENT,
  `css_name` varchar(128) NOT NULL DEFAULT '',
  `css_text` text,
  `css_comments` text,
  `updated` int(10) DEFAULT '0',
  `random` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`cssid`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_email_logs`
--

DROP TABLE IF EXISTS `ibf_email_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_email_logs` (
  `email_id` int(10) NOT NULL AUTO_INCREMENT,
  `email_subject` varchar(255) NOT NULL DEFAULT '',
  `email_content` text NOT NULL,
  `email_date` int(10) NOT NULL DEFAULT '0',
  `from_member_id` mediumint(8) NOT NULL DEFAULT '0',
  `from_email_address` varchar(250) NOT NULL DEFAULT '',
  `from_ip_address` varchar(16) NOT NULL DEFAULT '127.0.0.1',
  `to_member_id` mediumint(8) NOT NULL DEFAULT '0',
  `to_email_address` varchar(250) NOT NULL DEFAULT '',
  `topic_id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`email_id`),
  KEY `from_member_id` (`from_member_id`),
  KEY `email_date` (`email_date`)
) ENGINE=MyISAM AUTO_INCREMENT=1061 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_emoticons`
--

DROP TABLE IF EXISTS `ibf_emoticons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_emoticons` (
  `id` smallint(3) NOT NULL AUTO_INCREMENT,
  `typed` varchar(32) NOT NULL DEFAULT '',
  `image` varchar(128) NOT NULL DEFAULT '',
  `clickable` smallint(2) NOT NULL DEFAULT '1',
  `skid` tinyint(2) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `skid` (`skid`),
  KEY `clickable` (`clickable`)
) ENGINE=MyISAM AUTO_INCREMENT=365 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_emoticons_skins`
--

DROP TABLE IF EXISTS `ibf_emoticons_skins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_emoticons_skins` (
  `id` tinyint(2) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_faq`
--

DROP TABLE IF EXISTS `ibf_faq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_faq` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL DEFAULT '',
  `text` text,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_favorites`
--

DROP TABLE IF EXISTS `ibf_favorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_favorites` (
  `mid` int(11) NOT NULL DEFAULT '0',
  `tid` int(11) NOT NULL DEFAULT '0',
  KEY `mid` (`mid`),
  KEY `tid` (`tid`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_forum_perms`
--

DROP TABLE IF EXISTS `ibf_forum_perms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_forum_perms` (
  `perm_id` int(10) NOT NULL AUTO_INCREMENT,
  `perm_name` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`perm_id`)
) ENGINE=MyISAM AUTO_INCREMENT=34 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_forum_tracker`
--

DROP TABLE IF EXISTS `ibf_forum_tracker`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_forum_tracker` (
  `frid` mediumint(8) NOT NULL AUTO_INCREMENT,
  `member_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `forum_id` smallint(5) NOT NULL DEFAULT '0',
  `start_date` int(10) DEFAULT NULL,
  `last_sent` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`frid`),
  KEY `member_id` (`member_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3088 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_forums`
--

DROP TABLE IF EXISTS `ibf_forums`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_forums` (
  `id` smallint(5) NOT NULL DEFAULT '0',
  `topics` mediumint(6) DEFAULT NULL,
  `posts` mediumint(6) DEFAULT NULL,
  `last_post` int(10) DEFAULT NULL,
  `last_poster_id` mediumint(8) NOT NULL DEFAULT '0',
  `last_poster_name` varchar(32) DEFAULT NULL,
  `name` varchar(128) NOT NULL DEFAULT '',
  `icon` varchar(128) DEFAULT NULL COMMENT 'forum icon by sunny',
  `description` text,
  `position` tinyint(2) DEFAULT NULL,
  `use_ibc` tinyint(1) DEFAULT NULL,
  `use_html` tinyint(1) DEFAULT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `start_perms` varchar(255) NOT NULL DEFAULT '',
  `reply_perms` varchar(255) NOT NULL DEFAULT '',
  `read_perms` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(32) DEFAULT NULL,
  `category` tinyint(2) NOT NULL DEFAULT '0',
  `last_title` varchar(128) DEFAULT NULL,
  `last_id` int(10) DEFAULT NULL,
  `sort_key` varchar(32) DEFAULT NULL,
  `sort_order` varchar(32) DEFAULT NULL,
  `prune` tinyint(3) DEFAULT NULL,
  `show_rules` tinyint(1) DEFAULT NULL,
  `upload_perms` varchar(255) DEFAULT NULL,
  `preview_posts` tinyint(1) DEFAULT NULL,
  `allow_poll` tinyint(1) NOT NULL DEFAULT '1',
  `allow_pollbump` tinyint(1) NOT NULL DEFAULT '0',
  `inc_postcount` tinyint(1) NOT NULL DEFAULT '1',
  `skin_id` int(10) DEFAULT NULL,
  `parent_id` mediumint(5) DEFAULT '-1',
  `subwrap` tinyint(1) DEFAULT '0',
  `sub_can_post` tinyint(1) DEFAULT '1',
  `quick_reply` tinyint(1) DEFAULT '0',
  `redirect_url` varchar(250) DEFAULT '',
  `redirect_on` tinyint(1) NOT NULL DEFAULT '0',
  `redirect_hits` int(10) NOT NULL DEFAULT '0',
  `redirect_loc` varchar(250) DEFAULT '',
  `rules_title` varchar(255) NOT NULL DEFAULT '',
  `rules_text` text NOT NULL,
  `has_mod_posts` tinyint(1) NOT NULL DEFAULT '0',
  `topic_mm_id` varchar(250) NOT NULL DEFAULT '',
  `forum_highlight` tinyint(1) NOT NULL DEFAULT '0',
  `highlight_fid` smallint(6) NOT NULL DEFAULT '-1',
  `red_border` tinyint(1) NOT NULL DEFAULT '0',
  `siu_thumb` tinyint(1) NOT NULL DEFAULT '0',
  `days_off` tinyint(4) NOT NULL DEFAULT '5',
  `decided_button` tinyint(1) NOT NULL DEFAULT '0',
  `faq_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `position` (`position`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_forums_order`
--

DROP TABLE IF EXISTS `ibf_forums_order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_forums_order` (
  `id` smallint(4) unsigned NOT NULL DEFAULT '0',
  `pid` smallint(4) unsigned NOT NULL DEFAULT '0',
  KEY `id` (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_g_visitors`
--

DROP TABLE IF EXISTS `ibf_g_visitors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_g_visitors` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `ip_address` varchar(16) NOT NULL DEFAULT '',
  `day` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `month` tinyint(2) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`ip_address`,`day`,`month`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_groups`
--

DROP TABLE IF EXISTS `ibf_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_groups` (
  `g_id` int(3) unsigned NOT NULL AUTO_INCREMENT,
  `g_view_board` tinyint(1) DEFAULT NULL,
  `g_mem_info` tinyint(1) DEFAULT NULL,
  `g_other_topics` tinyint(1) DEFAULT NULL,
  `g_use_search` tinyint(1) DEFAULT NULL,
  `g_email_friend` tinyint(1) DEFAULT NULL,
  `g_invite_friend` tinyint(1) DEFAULT NULL,
  `g_edit_profile` tinyint(1) DEFAULT NULL,
  `g_post_new_topics` tinyint(1) DEFAULT NULL,
  `g_reply_own_topics` tinyint(1) DEFAULT NULL,
  `g_reply_other_topics` tinyint(1) DEFAULT NULL,
  `g_edit_posts` tinyint(1) DEFAULT NULL,
  `g_delete_own_posts` tinyint(1) DEFAULT NULL,
  `g_open_close_posts` tinyint(1) DEFAULT NULL,
  `g_delete_own_topics` tinyint(1) DEFAULT NULL,
  `g_post_polls` tinyint(1) DEFAULT NULL,
  `g_vote_polls` tinyint(1) DEFAULT NULL,
  `g_use_pm` tinyint(1) DEFAULT NULL,
  `g_is_supmod` tinyint(1) DEFAULT NULL,
  `g_access_cp` tinyint(1) DEFAULT NULL,
  `g_title` varchar(32) NOT NULL DEFAULT '',
  `g_can_remove` tinyint(1) DEFAULT NULL,
  `g_append_edit` tinyint(1) DEFAULT NULL,
  `g_access_offline` tinyint(1) DEFAULT NULL,
  `g_avoid_q` tinyint(1) DEFAULT NULL,
  `g_avoid_flood` tinyint(1) DEFAULT NULL,
  `g_icon` varchar(64) DEFAULT NULL,
  `g_attach_max` bigint(20) DEFAULT NULL,
  `g_avatar_upload` tinyint(1) DEFAULT '0',
  `g_calendar_post` tinyint(1) DEFAULT '0',
  `prefix` varchar(250) DEFAULT NULL,
  `suffix` varchar(250) DEFAULT NULL,
  `g_max_messages` int(5) DEFAULT '50',
  `g_max_mass_pm` int(5) DEFAULT '0',
  `g_post_flood` mediumint(6) NOT NULL DEFAULT '20',
  `g_search_flood` mediumint(6) DEFAULT '20',
  `g_edit_cutoff` int(10) DEFAULT '0',
  `g_promotion` varchar(10) DEFAULT '-1&-1',
  `g_hide_from_list` tinyint(1) DEFAULT '0',
  `g_post_closed` tinyint(1) DEFAULT '0',
  `g_perm_id` varchar(255) NOT NULL DEFAULT '',
  `g_photo_max_vars` varchar(200) DEFAULT '',
  `g_dohtml` tinyint(1) NOT NULL DEFAULT '0',
  `g_edit_topic` tinyint(1) NOT NULL DEFAULT '0',
  `g_email_limit` varchar(15) NOT NULL DEFAULT '10:15',
  `g_fine_edit` tinyint(1) NOT NULL DEFAULT '0',
  `g_allow_inventoryedit` tinyint(1) NOT NULL DEFAULT '0',
  `g_discount` int(3) NOT NULL DEFAULT '0',
  `g_use_signature` tinyint(1) DEFAULT '1',
  `g_use_avatar` varchar(50) DEFAULT NULL,
  `g_days_ago` smallint(5) DEFAULT NULL,
  `g_delay_delete_posts` tinyint(1) NOT NULL DEFAULT '0',
  `g_use_decided` tinyint(1) NOT NULL DEFAULT '0',
  `g_art_view` tinyint(1) DEFAULT NULL,
  `g_art_edit` tinyint(1) DEFAULT NULL,
  `g_art_add` tinyint(1) DEFAULT NULL,
  `g_art_approve` tinyint(1) DEFAULT NULL,
  `g_art_delete` tinyint(1) DEFAULT NULL,
  `g_art_move` tinyint(1) DEFAULT NULL,
  `g_art_attach` tinyint(1) DEFAULT NULL,
  `g_art_del_attach` tinyint(1) DEFAULT NULL,
  `g_art_attach_max` int(11) DEFAULT NULL,
  `g_art_add_comment` tinyint(1) DEFAULT NULL,
  `g_art_edit_comment` tinyint(1) DEFAULT NULL,
  `g_art_delete_comments` tinyint(1) DEFAULT NULL,
  `g_art_view_comments` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`g_id`),
  KEY `g_hide_from_list` (`g_hide_from_list`),
  KEY `g_title` (`g_title`)
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_ip_table`
--

DROP TABLE IF EXISTS `ibf_ip_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_ip_table` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `mid` int(8) unsigned NOT NULL DEFAULT '0',
  `fid` int(8) unsigned NOT NULL DEFAULT '0',
  `ok1` char(3) NOT NULL DEFAULT '0',
  `ok2` char(3) NOT NULL DEFAULT '0',
  `ok3` char(3) NOT NULL DEFAULT '0',
  `ok4` char(3) NOT NULL DEFAULT '0',
  `comment` char(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mid` (`mid`),
  KEY `ip_data` (`fid`,`ok1`,`ok2`,`ok3`,`ok4`)
) ENGINE=MyISAM AUTO_INCREMENT=698 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_languages`
--

DROP TABLE IF EXISTS `ibf_languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_languages` (
  `lid` mediumint(8) NOT NULL AUTO_INCREMENT,
  `ldir` varchar(64) NOT NULL DEFAULT '',
  `lname` varchar(250) NOT NULL DEFAULT '',
  `lauthor` varchar(250) DEFAULT NULL,
  `lemail` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`lid`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_log_forums`
--

DROP TABLE IF EXISTS `ibf_log_forums`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_log_forums` (
  `mid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `fid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `logTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`mid`,`fid`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_log_topics`
--

DROP TABLE IF EXISTS `ibf_log_topics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_log_topics` (
  `mid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `tid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `fid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `logTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`tid`,`mid`,`fid`),
  KEY `logTime` (`logTime`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_m_visitors`
--

DROP TABLE IF EXISTS `ibf_m_visitors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_m_visitors` (
  `mid` int(10) unsigned NOT NULL DEFAULT '0',
  `day` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `month` tinyint(2) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`mid`,`day`,`month`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_macro`
--

DROP TABLE IF EXISTS `ibf_macro`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_macro` (
  `macro_id` smallint(3) NOT NULL AUTO_INCREMENT,
  `macro_value` varchar(200) DEFAULT NULL,
  `macro_replace` text,
  `can_remove` tinyint(1) DEFAULT '0',
  `macro_set` smallint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`macro_id`),
  KEY `macro_set` (`macro_set`)
) ENGINE=MyISAM AUTO_INCREMENT=1664 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_macro_name`
--

DROP TABLE IF EXISTS `ibf_macro_name`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_macro_name` (
  `set_id` smallint(3) NOT NULL DEFAULT '0',
  `set_name` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`set_id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_member_extra`
--

DROP TABLE IF EXISTS `ibf_member_extra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_member_extra` (
  `id` int(11) NOT NULL DEFAULT '0',
  `notes` text,
  `links` text,
  `bio` text,
  `ta_size` char(3) DEFAULT NULL,
  `photo_type` varchar(10) DEFAULT '',
  `photo_location` varchar(255) DEFAULT '',
  `photo_dimensions` varchar(200) DEFAULT '',
  `country` varchar(2) NOT NULL DEFAULT '',
  `region` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `geo_lat` float NOT NULL,
  `geo_lon` float NOT NULL,
  `real_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `country` (`country`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_members`
--

DROP TABLE IF EXISTS `ibf_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_members` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `mgroup` smallint(3) NOT NULL DEFAULT '0',
  `old_group` smallint(3) NOT NULL DEFAULT '0',
  `password` varchar(255) NOT NULL DEFAULT '',
  `gender` enum('','m','f') NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `joined` int(10) NOT NULL DEFAULT '0',
  `ip_address` varchar(16) NOT NULL DEFAULT '',
  `avatar` varchar(128) DEFAULT NULL,
  `avatar_size` varchar(9) DEFAULT NULL,
  `posts` mediumint(7) DEFAULT '0',
  `aim_name` varchar(40) DEFAULT NULL,
  `icq_number` varchar(40) DEFAULT NULL,
  `location` varchar(128) DEFAULT NULL,
  `signature` text,
  `website` varchar(70) DEFAULT NULL,
  `yahoo` varchar(32) DEFAULT NULL,
  `title` varchar(64) DEFAULT NULL,
  `allow_admin_mails` tinyint(1) DEFAULT NULL,
  `time_offset` varchar(10) DEFAULT NULL,
  `interests` text,
  `hide_email` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `email_pm` tinyint(1) DEFAULT NULL,
  `email_full` tinyint(1) DEFAULT NULL,
  `skin` smallint(5) DEFAULT NULL,
  `warn_level` int(10) DEFAULT NULL,
  `warn_lastwarn` int(10) NOT NULL DEFAULT '0',
  `language` varchar(32) DEFAULT NULL,
  `msnname` varchar(64) DEFAULT NULL,
  `last_post` int(10) DEFAULT NULL,
  `restrict_post` varchar(100) NOT NULL DEFAULT '0',
  `view_sigs` tinyint(1) DEFAULT '1',
  `view_img` tinyint(1) DEFAULT '1',
  `view_avs` tinyint(1) DEFAULT '1',
  `view_pop` tinyint(1) DEFAULT '1',
  `bday_day` int(2) DEFAULT NULL,
  `bday_month` int(2) DEFAULT NULL,
  `bday_year` int(4) DEFAULT NULL,
  `new_msg` tinyint(2) DEFAULT NULL,
  `msg_from_id` mediumint(8) DEFAULT NULL,
  `msg_msg_id` int(10) DEFAULT NULL,
  `msg_total` smallint(5) DEFAULT NULL,
  `vdirs` text,
  `show_popup` tinyint(1) DEFAULT NULL,
  `last_visit` int(10) DEFAULT '0',
  `last_activity` int(10) DEFAULT '0',
  `dst_in_use` tinyint(1) DEFAULT '0',
  `view_prefs` varchar(64) DEFAULT '-1&-1',
  `coppa_user` tinyint(1) DEFAULT '0',
  `mod_posts` varchar(100) NOT NULL DEFAULT '0',
  `auto_track` tinyint(1) DEFAULT '0',
  `org_perm_id` varchar(255) DEFAULT '',
  `org_supmod` tinyint(1) DEFAULT '0',
  `integ_msg` varchar(250) DEFAULT '',
  `temp_ban` varchar(100) DEFAULT NULL,
  `rep` int(10) DEFAULT NULL,
  `ratting` int(10) NOT NULL DEFAULT '0',
  `allow_rep` tinyint(1) NOT NULL DEFAULT '1',
  `allow_anon` tinyint(1) NOT NULL DEFAULT '1',
  `board_layout` text,
  `favorites` text NOT NULL,
  `show_wp` tinyint(1) NOT NULL DEFAULT '1',
  `cb_forumlist` tinyint(1) NOT NULL DEFAULT '1',
  `quick_search` tinyint(1) NOT NULL DEFAULT '1',
  `highlight_topic` tinyint(1) NOT NULL DEFAULT '0',
  `close_category` tinyint(1) NOT NULL DEFAULT '0',
  `points` int(10) NOT NULL DEFAULT '0',
  `fined` int(11) NOT NULL DEFAULT '0',
  `deposited` int(9) NOT NULL DEFAULT '0',
  `auto_collect` tinyint(1) NOT NULL DEFAULT '0',
  `last_collect` varchar(255) NOT NULL DEFAULT '0',
  `extra_intrest` int(3) NOT NULL DEFAULT '0',
  `use_highlight` tinyint(1) NOT NULL DEFAULT '1',
  `use_dot` tinyint(1) NOT NULL DEFAULT '1',
  `quick_reply` tinyint(1) NOT NULL DEFAULT '1',
  `is_new_warn_exixts` tinyint(1) NOT NULL DEFAULT '0',
  `board_read` int(10) NOT NULL DEFAULT '0',
  `forums_read` text NOT NULL,
  `show_history` tinyint(1) NOT NULL DEFAULT '1',
  `sskin_id` tinyint(2) NOT NULL DEFAULT '1',
  `show_status` tinyint(1) NOT NULL DEFAULT '1',
  `show_icons` tinyint(1) NOT NULL DEFAULT '1',
  `show_ratting` tinyint(1) NOT NULL DEFAULT '1',
  `css_method` enum('inline','external') NOT NULL DEFAULT 'external',
  `forum_icon` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'forum icon by sunny',
  `show_filter` tinyint(1) NOT NULL DEFAULT '0',
  `disable_mail` tinyint(1) NOT NULL DEFAULT '0',
  `disable_mail_reason` varchar(255) DEFAULT NULL,
  `disable_group` tinyint(1) NOT NULL DEFAULT '0',
  `syntax` enum('client','server','none') NOT NULL DEFAULT 'client',
  `syntax_use_wrap` tinyint(1) DEFAULT NULL,
  `syntax_use_line_colouring` tinyint(1) DEFAULT NULL,
  `syntax_use_line_numbering` tinyint(1) DEFAULT NULL,
  `syntax_lines_count` int(4) DEFAULT NULL,
  `show_new` tinyint(1) NOT NULL DEFAULT '0',
  `profile_delete_time` int(10) unsigned NOT NULL DEFAULT '0',
  `search_days` tinyint(1) NOT NULL DEFAULT '5',
  `dsite_group` smallint(3) DEFAULT NULL,
  `post_wrap_size` int(10) NOT NULL DEFAULT '0',
  `openid_url`  varchar(255),
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `mgroup` (`mgroup`),
  KEY `bday_day` (`bday_day`),
  KEY `bday_month` (`bday_month`),
  KEY `ip_address` (`ip_address`),
  KEY `email` (`email`),
  KEY `profile_delete_time` (`profile_delete_time`),
  KEY `fined` (`fined`),
  KEY `joined` (`joined`),
  KEY `posts` (`posts`),
  KEY `sskin_id` (`sskin_id`),
  KEY `last_visit` (`last_visit`),
  KEY `gender` (`gender`),
  UNIQUE KEY (`openid_url`)
) ENGINE=MyISAM AUTO_INCREMENT=139226 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_messages`
--

DROP TABLE IF EXISTS `ibf_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_messages` (
  `msg_id` int(10) NOT NULL AUTO_INCREMENT,
  `msg_date` int(10) DEFAULT NULL,
  `read_state` tinyint(1) DEFAULT NULL,
  `title` varchar(128) DEFAULT NULL,
  `message` text,
  `from_id` mediumint(8) NOT NULL DEFAULT '0',
  `vid` varchar(32) DEFAULT NULL,
  `member_id` mediumint(8) NOT NULL DEFAULT '0',
  `recipient_id` mediumint(8) NOT NULL DEFAULT '0',
  `attach_type` tinyint(128) DEFAULT NULL,
  `attach_file` tinyint(128) DEFAULT NULL,
  `cc_users` text,
  `tracking` tinyint(1) DEFAULT '0',
  `read_date` int(10) DEFAULT NULL,
  PRIMARY KEY (`msg_id`),
  KEY `member_id` (`member_id`),
  KEY `vid` (`vid`),
  KEY `from_id` (`from_id`),
  KEY `idx_sender_date` (`from_id`,`msg_date`)
) ENGINE=MyISAM AUTO_INCREMENT=500568 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_moderator_logs`
--

DROP TABLE IF EXISTS `ibf_moderator_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_moderator_logs` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `forum_id` int(5) DEFAULT '0',
  `topic_id` int(10) NOT NULL DEFAULT '0',
  `post_id` int(10) DEFAULT NULL,
  `member_id` mediumint(8) NOT NULL DEFAULT '0',
  `member_name` varchar(32) NOT NULL DEFAULT '',
  `ip_address` varchar(16) NOT NULL DEFAULT '0',
  `http_referer` varchar(255) DEFAULT NULL,
  `ctime` int(10) DEFAULT NULL,
  `topic_title` varchar(128) DEFAULT NULL,
  `action` varchar(128) DEFAULT NULL,
  `query_string` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`)
) ENGINE=MyISAM AUTO_INCREMENT=110586 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_moderators`
--

DROP TABLE IF EXISTS `ibf_moderators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_moderators` (
  `mid` mediumint(8) NOT NULL AUTO_INCREMENT,
  `forum_id` int(5) NOT NULL DEFAULT '0',
  `member_name` varchar(32) NOT NULL DEFAULT '',
  `member_id` mediumint(8) NOT NULL DEFAULT '0',
  `edit_post` tinyint(1) DEFAULT NULL,
  `edit_topic` tinyint(1) DEFAULT NULL,
  `delete_post` tinyint(1) DEFAULT NULL,
  `delete_topic` tinyint(1) DEFAULT NULL,
  `view_ip` tinyint(1) DEFAULT NULL,
  `open_topic` tinyint(1) DEFAULT NULL,
  `close_topic` tinyint(1) DEFAULT NULL,
  `mass_move` tinyint(1) DEFAULT NULL,
  `mass_prune` tinyint(1) DEFAULT NULL,
  `move_topic` tinyint(1) DEFAULT NULL,
  `pin_topic` tinyint(1) DEFAULT NULL,
  `unpin_topic` tinyint(1) DEFAULT NULL,
  `post_q` tinyint(1) DEFAULT NULL,
  `topic_q` tinyint(1) DEFAULT NULL,
  `allow_warn` tinyint(1) DEFAULT NULL,
  `edit_user` tinyint(1) NOT NULL DEFAULT '0',
  `is_group` tinyint(1) DEFAULT '0',
  `group_id` smallint(3) DEFAULT NULL,
  `group_name` varchar(200) DEFAULT NULL,
  `split_merge` tinyint(1) DEFAULT '0',
  `can_mm` tinyint(1) NOT NULL DEFAULT '0',
  `can_attach` tinyint(1) NOT NULL DEFAULT '1',
  `time_deleted_link` int(10) DEFAULT NULL,
  `can_pin_post` tinyint(1) NOT NULL DEFAULT '1',
  `rules_edit` tinyint(1) NOT NULL DEFAULT '1',
  `multimod_edit` tinyint(1) NOT NULL DEFAULT '0',
  `hide_topic` tinyint(1) NOT NULL DEFAULT '1',
  `add_to_faq` tinyint(1) NOT NULL DEFAULT '0',
  `mirror_topic` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`mid`),
  KEY `forum_id` (`forum_id`),
  KEY `group_id` (`group_id`),
  KEY `member_id` (`member_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1078 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_pfields_content`
--

DROP TABLE IF EXISTS `ibf_pfields_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_pfields_content` (
  `member_id` mediumint(8) NOT NULL DEFAULT '0',
  `updated` int(10) DEFAULT '0',
  `field_6` text,
  `field_1` text,
  `field_2` text,
  `field_3` text,
  PRIMARY KEY (`member_id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_pfields_data`
--

DROP TABLE IF EXISTS `ibf_pfields_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_pfields_data` (
  `fid` smallint(5) NOT NULL AUTO_INCREMENT,
  `ftitle` varchar(200) NOT NULL DEFAULT '',
  `fdesc` varchar(250) DEFAULT '',
  `fcontent` text,
  `ftype` varchar(250) DEFAULT 'text',
  `freq` tinyint(1) DEFAULT '0',
  `fhide` tinyint(1) DEFAULT '0',
  `fmaxinput` smallint(6) DEFAULT '250',
  `fedit` tinyint(1) DEFAULT '1',
  `forder` smallint(6) DEFAULT '1',
  `fshowreg` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`fid`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_polls`
--

DROP TABLE IF EXISTS `ibf_polls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_polls` (
  `pid` mediumint(8) NOT NULL AUTO_INCREMENT,
  `tid` int(10) NOT NULL DEFAULT '0',
  `start_date` int(10) DEFAULT NULL,
  `choices` text,
  `voices` varchar(255) NOT NULL DEFAULT '',
  `starter_id` mediumint(8) NOT NULL DEFAULT '0',
  `votes` smallint(5) NOT NULL DEFAULT '0',
  `forum_id` smallint(5) NOT NULL DEFAULT '0',
  `poll_question` varchar(255) DEFAULT NULL,
  `is_multi_poll` tinyint(1) NOT NULL DEFAULT '0',
  `multi_poll_min` tinyint(2) NOT NULL DEFAULT '0',
  `multi_poll_max` tinyint(2) NOT NULL DEFAULT '0',
  `is_weighted_poll` tinyint(1) NOT NULL DEFAULT '0',
  `weighted_poll_places` tinyint(2) NOT NULL DEFAULT '0',
  `state` enum('open','closed') NOT NULL DEFAULT 'open',
  `live_before` int(10) DEFAULT NULL,
  PRIMARY KEY (`pid`),
  UNIQUE KEY `tid` (`tid`)
) ENGINE=MyISAM AUTO_INCREMENT=3880 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_post_attachments`
--

DROP TABLE IF EXISTS `ibf_post_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_post_attachments` (
  `attach_id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) DEFAULT NULL,
  `member_id` int(11) DEFAULT NULL,
  `real_filename` varchar(64) DEFAULT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `type` varchar(128) DEFAULT NULL,
  `size` int(11) DEFAULT NULL,
  `hits` int(11) DEFAULT '0',
  PRIMARY KEY (`attach_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8346 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_post_edit_history`
--

DROP TABLE IF EXISTS `ibf_post_edit_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_post_edit_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) DEFAULT NULL,
  `old_text` text,
  `editor_id` mediumint(8) DEFAULT NULL,
  `editor_name` varchar(32) DEFAULT NULL,
  `edit_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15893 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_posts`
--

DROP TABLE IF EXISTS `ibf_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_posts` (
  `pid` int(10) NOT NULL AUTO_INCREMENT,
  `append_edit` tinyint(1) NOT NULL DEFAULT '0',
  `edit_time` int(10) NOT NULL DEFAULT '0',
  `author_id` mediumint(8) NOT NULL DEFAULT '0',
  `author_name` varchar(32) DEFAULT NULL,
  `use_sig` tinyint(1) NOT NULL DEFAULT '0',
  `use_emo` tinyint(1) NOT NULL DEFAULT '0',
  `ip_address` varchar(16) NOT NULL DEFAULT '',
  `post_date` int(10) NOT NULL DEFAULT '0',
  `icon_id` smallint(3) DEFAULT NULL,
  `post` text,
  `queued` tinyint(1) NOT NULL DEFAULT '0',
  `topic_id` int(10) NOT NULL DEFAULT '0',
  `forum_id` smallint(5) NOT NULL DEFAULT '0',
  `attach_id` varchar(64) DEFAULT NULL,
  `attach_hits` int(10) DEFAULT NULL,
  `attach_type` varchar(128) DEFAULT NULL,
  `attach_file` varchar(255) DEFAULT NULL,
  `attach_size` varchar(10) DEFAULT NULL,
  `post_title` varchar(255) DEFAULT NULL,
  `new_topic` tinyint(1) NOT NULL DEFAULT '0',
  `edit_name` varchar(255) DEFAULT NULL,
  `has_modcomment` tinyint(1) NOT NULL DEFAULT '0',
  `delete_after` int(10) unsigned NOT NULL DEFAULT '0',
  `added_to_faq` tinyint(1) NOT NULL DEFAULT '0',
  `indexed` tinyint(1) NOT NULL DEFAULT '0',
  `attach_exists` tinyint(1) DEFAULT NULL,
  `decline_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`pid`),
  KEY `author_id` (`author_id`),
  KEY `ip_address` (`ip_address`),
  KEY `topic_id` (`topic_id`),
  KEY `forumizer` (`forum_id`,`edit_time`),
  KEY `use_sig` (`forum_id`,`use_sig`),
  KEY `forum_id` (`forum_id`,`post_date`),
  KEY `delete_after` (`delete_after`),
  KEY `indexed` (`indexed`),
  KEY `posts_queued_posts` (`topic_id`,`queued`)
) ENGINE=MyISAM AUTO_INCREMENT=2861755 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_preview_user`
--

DROP TABLE IF EXISTS `ibf_preview_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_preview_user` (
  `mid` int(8) unsigned NOT NULL DEFAULT '0',
  `fid` int(4) unsigned NOT NULL DEFAULT '0',
  `mod_posts` char(100) DEFAULT NULL,
  `restrict_posts` char(100) DEFAULT NULL,
  `temp_ban` char(100) DEFAULT NULL,
  KEY `idx` (`mid`,`fid`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_quiz`
--

DROP TABLE IF EXISTS `ibf_quiz`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_quiz` (
  `mid` int(15) NOT NULL AUTO_INCREMENT,
  `quiz_id` int(9) NOT NULL DEFAULT '0',
  `question` text NOT NULL,
  `answer` text NOT NULL,
  `type` enum('single','multiq','dropdown','radio','checkbox','opinion') NOT NULL DEFAULT 'single',
  PRIMARY KEY (`mid`)
) ENGINE=MyISAM AUTO_INCREMENT=59 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_quiz_info`
--

DROP TABLE IF EXISTS `ibf_quiz_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_quiz_info` (
  `q_id` int(9) NOT NULL AUTO_INCREMENT,
  `quizname` varchar(255) NOT NULL DEFAULT 'None',
  `quizdesc` varchar(70) NOT NULL DEFAULT '',
  `starter_id` mediumint(8) NOT NULL DEFAULT '0',
  `starter_name` varchar(32) NOT NULL DEFAULT '',
  `views` int(11) NOT NULL DEFAULT '0',
  `icon_id` tinyint(2) NOT NULL DEFAULT '0',
  `pinned` tinyint(1) NOT NULL DEFAULT '0',
  `approved` tinyint(1) NOT NULL DEFAULT '1',
  `club` tinyint(1) NOT NULL DEFAULT '0',
  `post` text NOT NULL,
  `percent_needed` int(3) NOT NULL DEFAULT '0',
  `amount_won` int(9) NOT NULL DEFAULT '0',
  `started_on` int(14) NOT NULL DEFAULT '0',
  `run_for` int(15) NOT NULL DEFAULT '0',
  `let_only` int(9) NOT NULL DEFAULT '0',
  `quiz_status` enum('OPEN','CLOSED') NOT NULL DEFAULT 'OPEN',
  `timeout` int(9) NOT NULL DEFAULT '0',
  `pending` tinyint(1) NOT NULL DEFAULT '1',
  `quiz_items` text NOT NULL,
  PRIMARY KEY (`q_id`),
  KEY `starter_id` (`starter_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_quiz_winners`
--

DROP TABLE IF EXISTS `ibf_quiz_winners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_quiz_winners` (
  `quiz_id` int(9) NOT NULL DEFAULT '0',
  `memberid` int(9) NOT NULL DEFAULT '0',
  `ip_address` varchar(16) NOT NULL DEFAULT '',
  `amount_right` int(9) NOT NULL DEFAULT '0',
  `time` int(10) NOT NULL DEFAULT '0',
  `time_took` int(9) NOT NULL DEFAULT '0',
  `answers` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_reg_antispam`
--

DROP TABLE IF EXISTS `ibf_reg_antispam`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_reg_antispam` (
  `regid` varchar(32) NOT NULL DEFAULT '',
  `regcode` varchar(8) NOT NULL DEFAULT '',
  `ip_address` varchar(32) DEFAULT NULL,
  `ctime` int(10) DEFAULT NULL,
  PRIMARY KEY (`regid`),
  KEY `ctime` (`ctime`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_reputation`
--

DROP TABLE IF EXISTS `ibf_reputation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_reputation` (
  `msg_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `msg_date` int(10) DEFAULT NULL,
  `message` text,
  `from_id` varchar(32) DEFAULT NULL,
  `member_id` varchar(32) NOT NULL DEFAULT '0',
  `forum_id` smallint(5) NOT NULL DEFAULT '0',
  `topic_id` bigint(20) NOT NULL DEFAULT '0',
  `post` bigint(20) NOT NULL DEFAULT '0',
  `CODE` char(2) NOT NULL DEFAULT '',
  `vis` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`msg_id`),
  KEY `stat` (`from_id`,`vis`),
  KEY `total` (`member_id`,`CODE`)
) ENGINE=MyISAM AUTO_INCREMENT=98848 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_search`
--

DROP TABLE IF EXISTS `ibf_search`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_search` (
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `fid` int(10) unsigned NOT NULL DEFAULT '0',
  `word_id` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `pid` (`pid`),
  KEY `tid` (`tid`),
  KEY `fid` (`fid`),
  KEY `word_id` (`word_id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_search_forums`
--

DROP TABLE IF EXISTS `ibf_search_forums`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_search_forums` (
  `mid` int(10) unsigned NOT NULL DEFAULT '0',
  `fid` smallint(5) unsigned NOT NULL DEFAULT '0',
  KEY `mid` (`mid`,`fid`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_search_results`
--

DROP TABLE IF EXISTS `ibf_search_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_search_results` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `topic_id` text NOT NULL,
  `search_date` int(12) NOT NULL DEFAULT '0',
  `topic_max` int(3) NOT NULL DEFAULT '0',
  `sort_key` varchar(32) NOT NULL DEFAULT 'last_post',
  `sort_order` varchar(4) NOT NULL DEFAULT 'desc',
  `member_id` mediumint(10) DEFAULT '0',
  `ip_address` varchar(16) NOT NULL DEFAULT '',
  `post_id` longtext,
  `post_max` int(10) NOT NULL DEFAULT '0',
  `query_cache` text,
  PRIMARY KEY (`id`),
  KEY `search_date` (`search_date`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_search_words`
--

DROP TABLE IF EXISTS `ibf_search_words`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_search_words` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `word` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `word` (`word`)
) ENGINE=MyISAM AUTO_INCREMENT=2229688 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_sessions`
--

DROP TABLE IF EXISTS `ibf_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_sessions` (
  `id` varchar(32) NOT NULL DEFAULT '0',
  `member_name` varchar(64) DEFAULT NULL,
  `member_id` mediumint(8) NOT NULL DEFAULT '0',
  `ip_address` varchar(16) DEFAULT NULL,
  `browser` varchar(64) DEFAULT NULL,
  `running_time` int(10) DEFAULT NULL,
  `login_type` tinyint(1) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `member_group` smallint(3) DEFAULT NULL,
  `in_forum` varchar(128) DEFAULT NULL,
  `in_topic` varchar(128) DEFAULT NULL,
  `last_post` int(10) DEFAULT NULL,
  `org_perm_id` varchar(255) DEFAULT '',
  `r_location` varchar(40) DEFAULT NULL,
  `r_in_forum` smallint(5) NOT NULL DEFAULT '0',
  `r_in_topic` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`),
  KEY `r_in_forum` (`r_in_forum`),
  KEY `r_in_topic` (`r_in_topic`),
  KEY `ip_address` (`ip_address`),
  KEY `login_type` (`login_type`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_skin_templates`
--

DROP TABLE IF EXISTS `ibf_skin_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_skin_templates` (
  `suid` int(10) NOT NULL AUTO_INCREMENT,
  `set_id` int(10) NOT NULL DEFAULT '0',
  `group_name` varchar(255) NOT NULL DEFAULT '',
  `section_content` mediumtext,
  `func_name` varchar(255) DEFAULT NULL,
  `func_data` text,
  `updated` int(10) DEFAULT NULL,
  `can_remove` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`suid`)
) ENGINE=MyISAM AUTO_INCREMENT=98519 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_skins`
--

DROP TABLE IF EXISTS `ibf_skins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_skins` (
  `uid` int(10) NOT NULL AUTO_INCREMENT,
  `sname` varchar(100) NOT NULL DEFAULT '',
  `sid` int(10) NOT NULL DEFAULT '0',
  `set_id` int(5) NOT NULL DEFAULT '0',
  `tmpl_id` int(10) NOT NULL DEFAULT '0',
  `macro_id` int(10) NOT NULL DEFAULT '1',
  `css_id` int(10) NOT NULL DEFAULT '1',
  `img_dir` varchar(200) DEFAULT '1',
  `tbl_width` varchar(250) DEFAULT NULL,
  `tbl_border` varchar(250) DEFAULT NULL,
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  `default_set` tinyint(1) NOT NULL DEFAULT '0',
  `css_method` varchar(100) DEFAULT 'inline',
  `white_background` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `tmpl_id` (`tmpl_id`),
  KEY `css_id` (`css_id`),
  KEY `sid` (`sid`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_spider_logs`
--

DROP TABLE IF EXISTS `ibf_spider_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_spider_logs` (
  `sid` int(10) NOT NULL AUTO_INCREMENT,
  `bot` varchar(255) NOT NULL DEFAULT '',
  `query_string` text NOT NULL,
  `entry_date` int(10) NOT NULL DEFAULT '0',
  `ip_address` varchar(16) NOT NULL DEFAULT '',
  PRIMARY KEY (`sid`),
  KEY `bot` (`bot`)
) ENGINE=MyISAM AUTO_INCREMENT=1127514 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_stats`
--

DROP TABLE IF EXISTS `ibf_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_stats` (
  `TOTAL_REPLIES` int(10) NOT NULL DEFAULT '0',
  `TOTAL_TOPICS` int(10) NOT NULL DEFAULT '0',
  `LAST_MEM_NAME` varchar(32) DEFAULT NULL,
  `LAST_MEM_ID` mediumint(8) NOT NULL DEFAULT '0',
  `MOST_DATE` int(10) DEFAULT NULL,
  `MOST_COUNT` int(10) DEFAULT '0',
  `MEM_COUNT` mediumint(8) NOT NULL DEFAULT '0',
  `members` int(11) DEFAULT NULL,
  `guests` int(11) DEFAULT NULL,
  `bots` int(11) DEFAULT NULL,
  `record` int(10) unsigned DEFAULT NULL,
  `record_date` int(10) unsigned DEFAULT NULL,
  `h_members` int(11) DEFAULT NULL,
  `h_guests` int(11) DEFAULT NULL,
  `h_bots` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_store_category`
--

DROP TABLE IF EXISTS `ibf_store_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_store_category` (
  `catid` int(9) NOT NULL AUTO_INCREMENT,
  `cat_name` varchar(255) NOT NULL DEFAULT 'none',
  `cat_desc` text NOT NULL,
  PRIMARY KEY (`catid`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_store_inventory`
--

DROP TABLE IF EXISTS `ibf_store_inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_store_inventory` (
  `i_id` int(9) NOT NULL AUTO_INCREMENT,
  `owner_id` int(9) NOT NULL DEFAULT '0',
  `item_id` int(9) NOT NULL DEFAULT '0',
  `price_payed` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`i_id`)
) ENGINE=MyISAM AUTO_INCREMENT=187 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_store_logs`
--

DROP TABLE IF EXISTS `ibf_store_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_store_logs` (
  `logid` int(9) NOT NULL AUTO_INCREMENT,
  `fromid` int(11) NOT NULL DEFAULT '0',
  `message` text NOT NULL,
  `reason` text NOT NULL,
  `username` varchar(255) NOT NULL DEFAULT '0',
  `toid` int(11) NOT NULL DEFAULT '0',
  `toname` varchar(32) NOT NULL DEFAULT '',
  `type` varchar(255) NOT NULL DEFAULT 'single',
  `sum` int(11) NOT NULL DEFAULT '0',
  `time` int(9) NOT NULL DEFAULT '0',
  PRIMARY KEY (`logid`),
  KEY `fromid` (`fromid`),
  KEY `toid` (`toid`),
  KEY `type` (`type`)
) ENGINE=MyISAM AUTO_INCREMENT=1738 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_store_modlogs`
--

DROP TABLE IF EXISTS `ibf_store_modlogs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_store_modlogs` (
  `id` int(15) NOT NULL AUTO_INCREMENT,
  `fromid` int(11) NOT NULL DEFAULT '0',
  `username` varchar(255) NOT NULL DEFAULT '0',
  `toid` int(11) NOT NULL DEFAULT '0',
  `toname` varchar(64) NOT NULL DEFAULT '',
  `sum` int(11) NOT NULL DEFAULT '0',
  `reson` text NOT NULL,
  `user_reson` text NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'Unknown',
  `time` int(9) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fromid` (`fromid`),
  KEY `toid` (`toid`)
) ENGINE=MyISAM AUTO_INCREMENT=316 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_store_shopstock`
--

DROP TABLE IF EXISTS `ibf_store_shopstock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_store_shopstock` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `item_name` varchar(255) NOT NULL DEFAULT 'None',
  `icon` varchar(255) NOT NULL DEFAULT 'none.gif',
  `item_desc` text NOT NULL,
  `sell_price` int(9) NOT NULL DEFAULT '0',
  `module` varchar(255) NOT NULL DEFAULT 'item_unusuabl',
  `stock` int(9) NOT NULL DEFAULT '0',
  `category` varchar(100) NOT NULL DEFAULT 'shop',
  `avalible` tinyint(1) NOT NULL DEFAULT '1',
  `extra_one` varchar(255) NOT NULL DEFAULT '0',
  `extra_two` varchar(255) NOT NULL DEFAULT '0',
  `extra_three` text NOT NULL,
  `soldout_time` int(9) NOT NULL DEFAULT '0',
  `restock_amount` varchar(255) NOT NULL DEFAULT '0',
  `restock_wait` varchar(255) NOT NULL DEFAULT '0',
  `item_limit` int(9) NOT NULL DEFAULT '0',
  `restock_type` varchar(255) NOT NULL DEFAULT '0_m',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=38 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_syntax_access`
--

DROP TABLE IF EXISTS `ibf_syntax_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_syntax_access` (
  `syntax_id` int(11) NOT NULL DEFAULT '0',
  `member_id` mediumint(8) NOT NULL DEFAULT '0',
  PRIMARY KEY (`syntax_id`,`member_id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_syntax_list`
--

DROP TABLE IF EXISTS `ibf_syntax_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_syntax_list` (
  `id` int(11) NOT NULL DEFAULT '0',
  `syntax` varchar(10) NOT NULL DEFAULT '',
  `description` varchar(63) DEFAULT NULL,
  `back_color` varchar(15) DEFAULT NULL,
  `fore_color` varchar(15) DEFAULT NULL,
  `tab_length` int(2) NOT NULL DEFAULT '4',
  `example` text NOT NULL,
  `syntax_description` varchar(20) DEFAULT NULL,
  `version` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `syntax` (`syntax`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_syntax_rules`
--

DROP TABLE IF EXISTS `ibf_syntax_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_syntax_rules` (
  `syntax_id` int(11) NOT NULL DEFAULT '0',
  `record` int(2) NOT NULL DEFAULT '0',
  `reg_exp` text NOT NULL,
  `description` varchar(63) DEFAULT NULL,
  `tag_0` varchar(255) DEFAULT NULL,
  `tag_1` varchar(255) DEFAULT NULL,
  `tag_2` varchar(255) DEFAULT NULL,
  `tag_3` varchar(255) DEFAULT NULL,
  `tag_4` varchar(255) DEFAULT NULL,
  `tag_5` varchar(255) DEFAULT NULL,
  `tag_6` varchar(255) DEFAULT NULL,
  `tag_7` varchar(255) DEFAULT NULL,
  `tag_8` varchar(255) DEFAULT NULL,
  `tag_9` varchar(255) DEFAULT NULL,
  `action_0` varchar(10) DEFAULT NULL,
  `action_1` varchar(10) DEFAULT NULL,
  `action_2` varchar(10) DEFAULT NULL,
  `action_3` varchar(10) DEFAULT NULL,
  `action_4` varchar(10) DEFAULT NULL,
  `action_5` varchar(10) DEFAULT NULL,
  `action_6` varchar(10) DEFAULT NULL,
  `action_7` varchar(10) DEFAULT NULL,
  `action_8` varchar(10) DEFAULT NULL,
  `action_9` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`syntax_id`,`record`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_templates`
--

DROP TABLE IF EXISTS `ibf_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_templates` (
  `tmid` int(10) NOT NULL AUTO_INCREMENT,
  `template` mediumtext,
  `name` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`tmid`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_titles`
--

DROP TABLE IF EXISTS `ibf_titles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_titles` (
  `id` smallint(5) NOT NULL AUTO_INCREMENT,
  `posts` int(10) DEFAULT NULL,
  `title` varchar(128) DEFAULT NULL,
  `pips` varchar(128) DEFAULT NULL,
  `max_pms_per_hour` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `posts` (`posts`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_tmpl_names`
--

DROP TABLE IF EXISTS `ibf_tmpl_names`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_tmpl_names` (
  `skid` int(10) NOT NULL AUTO_INCREMENT,
  `skname` varchar(60) NOT NULL DEFAULT 'Invision Board',
  `author` varchar(250) DEFAULT '',
  `email` varchar(250) DEFAULT '',
  `url` varchar(250) DEFAULT '',
  PRIMARY KEY (`skid`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_topic_mmod`
--

DROP TABLE IF EXISTS `ibf_topic_mmod`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_topic_mmod` (
  `mm_id` smallint(5) NOT NULL AUTO_INCREMENT,
  `mm_title` varchar(250) NOT NULL DEFAULT '',
  `mm_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `topic_state` varchar(10) NOT NULL DEFAULT 'leave',
  `topic_pin` varchar(10) NOT NULL DEFAULT 'leave',
  `topic_move` smallint(5) NOT NULL DEFAULT '0',
  `topic_move_link` tinyint(1) NOT NULL DEFAULT '0',
  `topic_title_st` varchar(250) NOT NULL DEFAULT '',
  `topic_title_end` varchar(250) NOT NULL DEFAULT '',
  `topic_reply` tinyint(1) NOT NULL DEFAULT '0',
  `topic_reply_content` text NOT NULL,
  `topic_reply_postcount` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`mm_id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_topiclinks`
--

DROP TABLE IF EXISTS `ibf_topiclinks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_topiclinks` (
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `link` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `tid` (`tid`),
  KEY `link` (`link`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_topics`
--

DROP TABLE IF EXISTS `ibf_topics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_topics` (
  `tid` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `state` varchar(8) DEFAULT NULL,
  `posts` int(10) DEFAULT NULL,
  `starter_id` mediumint(8) NOT NULL DEFAULT '0',
  `start_date` int(10) DEFAULT NULL,
  `last_poster_id` mediumint(8) NOT NULL DEFAULT '0',
  `last_post` int(10) NOT NULL DEFAULT '0',
  `icon_id` tinyint(2) DEFAULT NULL,
  `starter_name` varchar(32) DEFAULT NULL,
  `last_poster_name` varchar(32) DEFAULT NULL,
  `poll_state` varchar(8) DEFAULT NULL,
  `last_vote` int(10) DEFAULT NULL,
  `views` int(10) DEFAULT NULL,
  `forum_id` smallint(5) NOT NULL DEFAULT '0',
  `approved` tinyint(1) NOT NULL DEFAULT '1',
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  `decided` tinyint(1) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `author_mode` tinyint(1) DEFAULT NULL,
  `pinned` tinyint(1) DEFAULT NULL,
  `moved_to` varchar(64) DEFAULT NULL,
  `OLD_ID_TOPIC` bigint(20) DEFAULT NULL,
  `why_close` tinytext,
  `link_time` int(10) DEFAULT NULL,
  `pinned_post` int(10) DEFAULT NULL,
  `club` tinyint(1) NOT NULL DEFAULT '0',
  `pinned_date` int(10) DEFAULT NULL,
  `indexed` tinyint(1) NOT NULL DEFAULT '0',
  `has_mirror` tinyint(1) DEFAULT NULL,
  `mirrored_topic_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`tid`),
  KEY `last_post` (`last_post`),
  KEY `del_index` (`state`,`moved_to`),
  KEY `link_time` (`link_time`),
  KEY `forum_id` (`forum_id`,`last_post`),
  KEY `deleted` (`deleted`),
  KEY `indexed` (`indexed`),
  KEY `approved` (`approved`),
  KEY `pinned` (`pinned`),
  KEY `mirrored_topic_id_index` (`mirrored_topic_id`)
) ENGINE=MyISAM AUTO_INCREMENT=328948 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_topicsinfo`
--

DROP TABLE IF EXISTS `ibf_topicsinfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_topicsinfo` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `name` tinytext NOT NULL,
  `link` tinytext NOT NULL,
  `date` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `tid` (`tid`),
  KEY `date` (`date`)
) ENGINE=MyISAM AUTO_INCREMENT=117 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_tracker`
--

DROP TABLE IF EXISTS `ibf_tracker`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_tracker` (
  `trid` mediumint(8) NOT NULL AUTO_INCREMENT,
  `member_id` mediumint(8) NOT NULL DEFAULT '0',
  `topic_id` bigint(20) NOT NULL DEFAULT '0',
  `start_date` int(10) DEFAULT NULL,
  `last_sent` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`trid`),
  KEY `member_id` (`member_id`),
  KEY `data` (`topic_id`,`member_id`)
) ENGINE=MyISAM AUTO_INCREMENT=95511 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_users_stat`
--

DROP TABLE IF EXISTS `ibf_users_stat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_users_stat` (
  `day` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `month` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `members` int(8) unsigned NOT NULL DEFAULT '0',
  `guests` int(8) unsigned NOT NULL DEFAULT '0',
  `bots` int(8) unsigned NOT NULL DEFAULT '0',
  KEY `day` (`day`),
  KEY `month` (`month`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_validating`
--

DROP TABLE IF EXISTS `ibf_validating`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_validating` (
  `vid` varchar(32) NOT NULL DEFAULT '',
  `member_id` mediumint(8) NOT NULL DEFAULT '0',
  `real_group` smallint(3) NOT NULL DEFAULT '0',
  `temp_group` smallint(3) NOT NULL DEFAULT '0',
  `entry_date` int(10) NOT NULL DEFAULT '0',
  `coppa_user` tinyint(1) NOT NULL DEFAULT '0',
  `lost_pass` tinyint(1) NOT NULL DEFAULT '0',
  `new_reg` tinyint(1) NOT NULL DEFAULT '0',
  `email_chg` tinyint(1) NOT NULL DEFAULT '0',
  `ip_address` varchar(16) NOT NULL DEFAULT '0',
  PRIMARY KEY (`vid`),
  KEY `member_id` (`member_id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_voters`
--

DROP TABLE IF EXISTS `ibf_voters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_voters` (
  `vid` int(10) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(16) NOT NULL DEFAULT '',
  `vote_date` int(10) NOT NULL DEFAULT '0',
  `tid` int(10) NOT NULL DEFAULT '0',
  `member_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `forum_id` smallint(5) NOT NULL DEFAULT '0',
  `votes` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`vid`),
  KEY `tid` (`tid`,`member_id`)
) ENGINE=MyISAM AUTO_INCREMENT=200178 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_warn_logs`
--

DROP TABLE IF EXISTS `ibf_warn_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_warn_logs` (
  `wlog_id` int(10) NOT NULL AUTO_INCREMENT,
  `wlog_mid` mediumint(8) NOT NULL DEFAULT '0',
  `wlog_notes` text NOT NULL,
  `wlog_contact` varchar(250) NOT NULL DEFAULT 'none',
  `wlog_contact_content` text NOT NULL,
  `wlog_date` int(10) NOT NULL DEFAULT '0',
  `wlog_type` varchar(6) NOT NULL DEFAULT 'pos',
  `wlog_addedby` mediumint(8) NOT NULL DEFAULT '0',
  `pid` int(10) DEFAULT NULL,
  PRIMARY KEY (`wlog_id`),
  KEY `pid` (`pid`),
  KEY `wlog_mid` (`wlog_mid`,`wlog_type`)
) ENGINE=MyISAM AUTO_INCREMENT=10340 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ibf_warnings`
--

DROP TABLE IF EXISTS `ibf_warnings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ibf_warnings` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `mid` int(8) unsigned NOT NULL DEFAULT '0',
  `level` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `RestrictDate` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `RestrictDate` (`RestrictDate`,`mid`)
) ENGINE=MyISAM AUTO_INCREMENT=6243 DEFAULT CHARSET=cp1251;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ip_table`
--

DROP TABLE IF EXISTS `ip_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ip_table` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `mid` int(8) unsigned NOT NULL DEFAULT '0',
  `ok1` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ok2` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ok3` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ok4` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `mid` (`mid`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;

--
-- Table structure for table `ibf_attachments_link`
--

CREATE TABLE `ibf_attachments_link` (
  `attach_id` int(11) NOT NULL,
  `item_type` enum('post','private_message','topic_draft') NOT NULL DEFAULT 'post',
  `item_id` int(11) NOT NULL,
  PRIMARY KEY (`attach_id`,`item_type`,`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;

/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2011-03-25 16:04:17
