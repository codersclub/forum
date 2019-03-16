<?php

class Migration_2013_12_30_16_34_51 extends MpmMigration
{

    public function up(PDO &$pdo)
    {
        $pdo->exec('ALTER TABLE ibf_attachments_link DROP primary key');
        $pdo->exec('alter table ibf_attachments_link add primary key (item_id, item_type, attach_id)');
    }

    public function down(PDO &$pdo)
    {
        $pdo->exec('ALTER TABLE ibf_attachments_link DROP primary key');
        $pdo->exec('alter table ibf_attachments_link add primary key (`attach_id`,`item_type`,`item_id`)');
    }
}
