<?php

class Migration_2014_05_05_06_42_28 extends MpmMigration
{

	public function up(PDO &$pdo)
	{
		$pdo->exec('ALTER TABLE ibf_members DROP COLUMN css_method');
		$pdo->exec('ALTER TABLE ibf_skins DROP COLUMN css_method');
	}

	public function down(PDO &$pdo)
	{
		$pdo->exec("ALTER TABLE ibf_members ADD COLUMN css_method enum('inline','external') NOT NULL DEFAULT 'external'");
		$pdo->exec("ALTER TABLE ibf_skins ADD COLUMN css_method varchar(100) DEFAULT 'inline'");
		$pdo->exec("UPDATE ibf_skins SET css_method='external'");
	}

}
