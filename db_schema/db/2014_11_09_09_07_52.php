<?php

class Migration_2014_11_09_09_07_52 extends MpmMigration
{

    public function up(PDO &$pdo)
    {
        $stmt = $pdo->prepare('insert into ibf_emoticons (typed, image, skid) values (:type, :image, :skid)');
        foreach ($pdo->query('select id from ibf_emoticons_skins') as $row) {
               $stmt->execute([':type' => ':sarcasm:', ':image' => 'sarcasm.gif', ':skid' => $row['id']]);
        }
    }

    public function down(PDO &$pdo)
    {
        $pdo->exec("delete from ibf_emoticons where typed = ':sarcasm:'");
    }
}
