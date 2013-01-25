<?php

class Migration_2013_01_25_14_11_32 extends MpmMigration
{

	public function up(PDO &$pdo)
	{
		$pdo->query('DROP TABLE ibf_cms_articles_watchdog');
		$pdo->query('DROP TABLE ibf_cms_comments');
		$pdo->query('DROP TABLE ibf_cms_comments_links');
		$pdo->query('DROP TABLE ibf_cms_content');
		$pdo->query('DROP TABLE ibf_cms_groups');
		$pdo->query('DROP TABLE ibf_cms_moderators');
		$pdo->query('DROP TABLE ibf_cms_subscriptions');
		$pdo->query('DROP TABLE ibf_cms_uploads');
		$pdo->query('DROP TABLE ibf_cms_uploads_cat');
		$pdo->query('DROP TABLE ibf_cms_uploads_cat_links');
		$pdo->query('DROP TABLE ibf_cms_uploads_file_links');
		$pdo->query('DROP TABLE ibf_cms_uploads_files');
		$pdo->query('DROP TABLE ibf_cms_views');

		return TRUE;
	}

	public function down(PDO &$pdo)
	{
		$pdo->query("CREATE TABLE ibf_cms_articles_watchdog (id int(11) NOT NULL AUTO_INCREMENT, mid int(11) NOT NULL DEFAULT '0', aid int(11) NOT NULL DEFAULT '0', PRIMARY KEY (id), KEY article_id (aid)) ENGINE=MyISAM AUTO_INCREMENT=51 DEFAULT CHARSET=cp1251;");
		$pdo->query("CREATE TABLE ibf_cms_comments (id int(11) unsigned NOT NULL AUTO_INCREMENT,comment text NOT NULL,user_id int(11) NOT NULL DEFAULT '0',udkkker_name varchar(255) NOT NULL DEFAULT '',submit_date int(11) NOT NULL DEFAULT '0',UNIQUE KEY id (id)) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=cp1251;");
		$pdo->query("CREATE TABLE ibf_cms_comments_links (base int(11) NOT NULL DEFAULT '0',refs int(11) NOT NULL DEFAULT '0',KEY base (base,refs)) ENGINE=MyISAM DEFAULT CHARSET=cp1251;");
		$pdo->query("CREATE TABLE ibf_cms_content (id int(8) unsigned NOT NULL AUTO_INCREMENT,path varchar(255) NOT NULL DEFAULT '',rights varchar(16) NOT NULL DEFAULT '000000000',owner int(8) unsigned NOT NULL DEFAULT '0',ogroup int(8) unsigned NOT NULL DEFAULT '0',description text,UNIQUE KEY path (path),KEY id (id)) ENGINE=MyISAM DEFAULT CHARSET=cp1251;");
		$pdo->query("CREATE TABLE ibf_cms_groups (gid int(8) unsigned NOT NULL AUTO_INCREMENT,gname varchar(15) NOT NULL DEFAULT '',gdescription text NOT NULL,gedit_post tinyint(1) NOT NULL DEFAULT '0',gadd_post tinyint(1) NOT NULL DEFAULT '1',gapprove_post tinyint(1) NOT NULL DEFAULT '0',gdelete_post tinyint(1) NOT NULL DEFAULT '0',gmove_posts tinyint(1) NOT NULL DEFAULT '0',g_add_attach tinyint(1) NOT NULL DEFAULT '1',g_delete_attach tinyint(1) NOT NULL DEFAULT '1',g_max_attach_size int(11) NOT NULL DEFAULT '0',gview_comments tinyint(1) NOT NULL DEFAULT '1',gpost_comment tinyint(1) NOT NULL DEFAULT '1',gedit_comment tinyint(1) NOT NULL DEFAULT '0',gview_posts tinyint(1) NOT NULL DEFAULT '1',gdelete_comments tinyint(1) NOT NULL DEFAULT '0',KEY id (gid)) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=cp1251;");
		$pdo->query("CREATE TABLE ibf_cms_moderators (mid mediumint(8) NOT NULL AUTO_INCREMENT,forum_id int(5) NOT NULL DEFAULT '0',member_name varchar(32) NOT NULL DEFAULT '',member_id mediumint(8) NOT NULL DEFAULT '0',edit_post tinyint(4) NOT NULL DEFAULT '0',delete_post tinyint(4) NOT NULL DEFAULT '0',approve_post int(11) NOT NULL DEFAULT '0',is_group int(11) NOT NULL DEFAULT '0',UNIQUE KEY mid (mid)) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=cp1251;");
		$pdo->query("CREATE TABLE ibf_cms_subscriptions (id int(11) NOT NULL AUTO_INCREMENT,article_id int(11) DEFAULT '0',article_version int(11) NOT NULL DEFAULT '1',category_id int(11) DEFAULT '0',member_id int(11) DEFAULT '0',type enum('favorite','subscribe') DEFAULT NULL,PRIMARY KEY (id)) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=cp1251;");
		$pdo->query("CREATE TABLE ibf_cms_uploads (id int(11) unsigned NOT NULL AUTO_INCREMENT,version_id int(11) NOT NULL DEFAULT '1',name varchar(63) NOT NULL DEFAULT '',short_desc varchar(255) NOT NULL DEFAULT '',article text NOT NULL,hits int(11) NOT NULL DEFAULT '0',user_id int(11) NOT NULL DEFAULT '0',author_name varchar(255) NOT NULL DEFAULT '',submit_date int(11) NOT NULL DEFAULT '0',icon_id int(11) NOT NULL DEFAULT '0',approved int(11) unsigned DEFAULT NULL,article_id varchar(63) NOT NULL DEFAULT '',KEY id (id)) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=cp1251;");
		$pdo->query("CREATE TABLE ibf_cms_uploads_cat (id int(10) unsigned NOT NULL AUTO_INCREMENT,parent_id int(10) unsigned NOT NULL DEFAULT '0',name varchar(255) NOT NULL DEFAULT '',category_id varchar(63) NOT NULL DEFAULT '',description text NOT NULL,num int(11) NOT NULL DEFAULT '0',always_empty tinyint(1) unsigned NOT NULL DEFAULT '0',visible tinyint(1) unsigned NOT NULL DEFAULT '0',allow_posts tinyint(1) NOT NULL DEFAULT '1',one_article tinyint(1) NOT NULL DEFAULT '1',add_article_form tinyint(1) NOT NULL DEFAULT '0',redirect_url varchar(255) DEFAULT NULL,moderate tinyint(1) NOT NULL DEFAULT '1',ord int(11) NOT NULL DEFAULT '0',show_subcats tinyint(1) NOT NULL DEFAULT '1',allow_comments tinyint(1) NOT NULL DEFAULT '1',show_fullscreen tinyint(1) DEFAULT '1',show_smilies tinyint(1) NOT NULL DEFAULT '1',force_versioning tinyint(1) NOT NULL DEFAULT '0',UNIQUE KEY id (id)) ENGINE=MyISAM AUTO_INCREMENT=202 DEFAULT CHARSET=cp1251;");
		$pdo->query("CREATE TABLE ibf_cms_uploads_cat_links (base int(11) NOT NULL DEFAULT '0',refs int(11) NOT NULL DEFAULT '0',current_version int(11) NOT NULL DEFAULT '1') ENGINE=MyISAM DEFAULT CHARSET=cp1251;");
		$pdo->query("CREATE TABLE ibf_cms_uploads_file_links (base int(11) NOT NULL DEFAULT '0',refs int(11) NOT NULL DEFAULT '0') ENGINE=MyISAM DEFAULT CHARSET=cp1251;");
		$pdo->query("CREATE TABLE ibf_cms_uploads_files (id int(10) unsigned NOT NULL AUTO_INCREMENT,name varchar(63) NOT NULL DEFAULT '',path varchar(255) NOT NULL DEFAULT '',mime varchar(63) NOT NULL DEFAULT '',hits int(11) NOT NULL DEFAULT '0',KEY id (id)) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=cp1251;");
		$pdo->query("CREATE TABLE ibf_cms_views (id int(11) NOT NULL AUTO_INCREMENT,pid int(11) DEFAULT '0',b_order int(7) DEFAULT NULL,bname varchar(63) DEFAULT NULL,bcaption varchar(63) DEFAULT NULL,bdescription varchar(63) DEFAULT NULL,break tinyint(3) DEFAULT NULL,visible tinyint(1) DEFAULT '1',UNIQUE KEY id (id)) ENGINE=MyISAM AUTO_INCREMENT=1003 DEFAULT CHARSET=cp1251;");
		return TRUE;
	}

}

