<?php

class Migration_2013_07_18_09_59_32 extends MpmMigration
{

	public function up(PDO &$pdo)
	{
		$pdo->exec('ALTER TABLE ibf_members DROP COLUMN favorites');
	}

	public function down(PDO &$pdo)
	{
		$pdo->exec('ALTER TABLE ibf_members ADD COLUMN favorites text NOT NULL');
	}

}

?>
