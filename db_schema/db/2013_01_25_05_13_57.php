<?php

class Migration_2013_01_25_05_13_57 extends MpmMigration
{

    public function up(PDO &$pdo)
    {
        $pdo->query('DROP TABLE ibf_css');
        return true;
    }

    public function down(PDO &$pdo)
    {
        //recreating table
        $sql = <<<EOF
CREATE TABLE ibf_css (
  cssid int(10) NOT NULL AUTO_INCREMENT,
  css_name varchar(128) NOT NULL DEFAULT '',
  css_comments text,
  updated int(10) DEFAULT '0',
  random varchar(20) DEFAULT NULL,
  PRIMARY KEY (cssid)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=cp1251;
EOF;
        $st = $pdo->prepare($sql);
        if ($st->execute()) {
            $sql = "INSERT INTO `ibf_css` VALUES (:id, :name, :comment, :updated, :random)";
            $st = $pdo->prepare($sql);

            $data = [
                [1, 'IPB Default CSS', '', 1187986617, '2015834940'],
                [5, 'New Year', '', 1187986688, '95420863'],
                [6, 'Winter', '', 1187986724, '871942890'],
                [8, 'Xpression Final', '', 1187986743, '1295422977'],
                [10, 'Text Skin', '', 1187986708, '1489726579'],
                [11, 'Mastilior', '', 1187986662, '1142841886'],
                [13, 'common', '', 1164892127, '1878934738'],
                [14, 'Black Label', '', 1187986639, '1916448335'],
            ];
            foreach ($data as $row) {
                $st->execute([
                    ':id'        => $row[0],
                    ':name'      => $row[1],
                    ':comment'   => $row[2],
                    ':updated'   => $row[3],
                    ':random'    => $row[4],
                ]);
            }
        }
    }
}
