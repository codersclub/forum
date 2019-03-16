<?php

class Migration_2013_02_12_16_23_07 extends MpmMigration
{

    public function up(PDO &$pdo)
    {
        $pdo->query("
		  ALTER TABLE `ibf_validating` ADD
		  `data` TEXT NOT NULL DEFAULT '' AFTER `member_id`
		");
    }

    public function down(PDO &$pdo)
    {
        $pdo->query("
		  ALTER TABLE `ibf_validating` DROP `data`		
		");
    }
}
