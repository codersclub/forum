<?php

class Migration_2013_02_12_16_32_24 extends MpmMigration
{

	public function up(PDO &$pdo)
	{
		$pdo->query("
		  ALTER TABLE `ibf_validating` ADD
		  `validate_type` varchar(64) NOT NULL DEFAULT '' AFTER `member_id`
		");
	}

	public function down(PDO &$pdo)
	{
		$pdo->query("
		  ALTER TABLE `ibf_validating` DROP `validate_type`
		");
	}

}

?>