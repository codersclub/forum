<?php

class Migration_2015_01_11_12_19_50 extends MpmMigration
{

    public function up(PDO &$pdo)
    {
        $pdo->exec('DROP TABLE IF EXISTS ibf_check_members');
    }

    public function down(PDO &$pdo)
    {
        $pdo->exec(<<<EOL
CREATE TABLE IF NOT EXISTS ibf_check_members (
  `mid` int(10) unsigned NOT NULL DEFAULT '0',
  `last_visit` int(10) unsigned NOT NULL DEFAULT '0',
  `sent` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`mid`),
  KEY `last_visit` (`last_visit`,`sent`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
EOL
        );
    }
}
