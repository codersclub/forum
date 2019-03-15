<?php

class Migration_2017_04_25_19_32_25 extends MpmMigration
{

    public function up(PDO &$pdo)
    {
        $pdo->exec("alter table ibf_members modify `syntax` enum('client','server','none','prism-coy','prism-twilight') NOT NULL DEFAULT 'client';");
    }

    public function down(PDO &$pdo)
    {
        $pdo->exec("alter table ibf_members modify `syntax` enum('client','server','none') NOT NULL DEFAULT 'client';");
    }

}

?>
