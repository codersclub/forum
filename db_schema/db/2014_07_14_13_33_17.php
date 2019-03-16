<?php

class Migration_2014_07_14_13_33_17 extends MpmMigration
{

    public function up(PDO &$pdo)
    {
        $pdo->exec('CREATE TABLE ibf_variables (name varchar(40) not null, data mediumtext not null, PRIMARY KEY (name)) ENGINE=MyISAM DEFAULT CHARSET=utf8');
    }

    public function down(PDO &$pdo)
    {
        $pdo->exec('DROP TABLE ibf_variables');
    }
}
