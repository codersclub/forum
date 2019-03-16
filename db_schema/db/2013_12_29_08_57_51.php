<?php

class Migration_2013_12_29_08_57_51 extends MpmMigration
{

    public function up(PDO &$pdo)
    {
        $pdo->exec('ALTER TABLE ibf_posts ADD KEY topic_id_id_idx  (topic_id, pid)');
    }

    public function down(PDO &$pdo)
    {
        $pdo->exec('ALTER TABLE ibf_posts DROP KEY topic_id_id_idx;');
    }
}
