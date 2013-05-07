<?php

class Migration_2013_05_07_05_27_26 extends MpmMigration
{

	public function up(PDO &$pdo)
	{
                $pdo->query("ALTER TABLE `ibf_post_attachments` ADD key(post_id);");		
	}

	public function down(PDO &$pdo)
	{
	         $pdo->query(" ALTER TABLE `ibf_post_attachments` DROP key `post_id` ;");	
	}

}

