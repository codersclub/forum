<?php

class Migration_2022_09_30_15_54_20 extends MpmMigration
{

	public function up(PDO &$pdo)
	{
		$pdo->exec("
			ALTER TABLE `ibf_forums_order`
			CHANGE `id` `id` INT UNSIGNED NOT NULL DEFAULT '0',
			CHANGE `pid` `pid` INT UNSIGNED NOT NULL DEFAULT '0';
		");
	}

	public function down(PDO &$pdo)
	{
		$pdo->exec("
			ALTER TABLE `ibf_forums_order`
			CHANGE `id` `id` SMALLINT(4) UNSIGNED NOT NULL DEFAULT '0',
			CHANGE `pid` `pid` SMALLINT(4) UNSIGNED NOT NULL DEFAULT '0';
		");
	}

}
