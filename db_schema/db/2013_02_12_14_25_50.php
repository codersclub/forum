<?php

class Migration_2013_02_12_14_25_50 extends MpmMigration
{

    public function up(PDO &$pdo)
    {
        $pdo->query("
		  ALTER TABLE `ibf_sessions` CHANGE `browser`
		  `browser` VARCHAR( 255 ) NOT NULL DEFAULT ''
		");
        return true;
    }

    public function down(PDO &$pdo)
    {
        $pdo->query("
		  ALTER TABLE `ibf_sessions` CHANGE `browser`
		  `browser` VARCHAR( 64 ) NULL DEFAULT NULL
		");
        return true;
    }
}
