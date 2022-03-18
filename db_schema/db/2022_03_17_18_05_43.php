<?php

class Migration_2022_03_17_18_05_43 extends MpmMigration
{

	public function up(PDO &$pdo)
	{
		$pdo->exec("ALTER TABLE `ibf_topics` MODIFY COLUMN `moved_to` varchar(255) DEFAULT NULL;");
	}

	public function down(PDO &$pdo)
	{
		$pdo->exec("ALTER TABLE `ibf_topics` MODIFY COLUMN `moved_to` varchar(64) DEFAULT NULL;");
	}

}
