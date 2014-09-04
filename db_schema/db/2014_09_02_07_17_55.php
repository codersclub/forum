<?php

class Migration_2014_09_02_07_17_55 extends MpmMigration
{

    public function up(PDO &$pdo)
    {
        $pdo->exec('DROP TABLE IF EXISTS ibf_templates');
        $pdo->exec('DROP TABLE IF EXISTS ibf_skin_templates');
        $pdo->exec('DROP TABLE IF EXISTS ibf_tmpl_names');

        $pdo->exec('ALTER TABLE ibf_skins DROP COLUMN tmpl_id');
        $pdo->exec('ALTER TABLE ibf_skins DROP COLUMN white_background');
        $pdo->exec('ALTER TABLE ibf_skins DROP COLUMN tbl_width');
        $pdo->exec('ALTER TABLE ibf_skins DROP COLUMN tbl_border');
        $pdo->exec('ALTER TABLE ibf_skins DROP COLUMN default_set');
        $pdo->exec('ALTER TABLE ibf_skins DROP COLUMN set_id');
        $pdo->prepare('ALTER TABLE ibf_skins ADD COLUMN template_class VARCHAR(40) NOT NULL DEFAULT :def AFTER macro_id')
            ->execute([':def' => '\Templates\Invision']);

        $pdo->exec('ALTER TABLE ibf_skins MODIFY COLUMN css_id VARCHAR(255) DEFAULT NULL');
        $pdo->exec('UPDATE ibf_skins SET css_id = CONCAT("css_", css_id, ".scss")');
    }

    public function down(PDO &$pdo)
    {
        $pdo->exec('ALTER TABLE ibf_skins DROP COLUMN template_class');
        $pdo->exec('ALTER TABLE ibf_skins ADD COLUMN tmpl_id INT(10) NOT NULL DEFAULT 0');
        $pdo->exec('ALTER TABLE ibf_skins ADD COLUMN tbl_width varchar(250) DEFAULT NULL');
        $pdo->exec('ALTER TABLE ibf_skins ADD COLUMN tbl_border varchar(250) DEFAULT NULL');
        $pdo->exec('ALTER TABLE ibf_skins ADD COLUMN white_background varchar(30) DEFAULT NULL');
        $pdo->exec('UPDATE ibf_skins SET tmpl_id = 1');
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS `ibf_templates` (
              `tmid` int(10) NOT NULL AUTO_INCREMENT,
              `template` longtext,
              `name` varchar(128) DEFAULT NULL,
              PRIMARY KEY (`tmid`)
            ) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8'
        );
        if (file_exists(__DIR__ . '/../../htdocs/Skin/s1/wrapper.tpl.php')) {
            ob_start();
            require __DIR__ . '/../../htdocs/Skin/s1/wrapper.tpl.php';
            $template = ob_get_clean();
        } else {
            $template = '';
        }
        $pdo->prepare('INSERT INTO ibf_templates (template, name) VALUES (:template, :name)')
            ->execute(
                [
                    ':template' => $template,
                    ':name'     => 'Invision Board Standard'
                ]
            );
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS ibf_tmpl_names (
                  skid int(10) NOT NULL AUTO_INCREMENT,
                  skname varchar(60) NOT NULL DEFAULT 'Invision Board',
                  author varchar(250) DEFAULT '',
                  email varchar(250) DEFAULT '',
                  url varchar(250) DEFAULT '',
                  PRIMARY KEY (skid)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8"
        );
        $pdo->exec(
            "INSERT INTO ibf_tmpl_names (skname, author, email, url) VALUES
          ('Invision Power Board Template Set','Invision Power Board','skins@invisionboard.com','http://www.invisionboard.com')
          "
        );
        $pdo->exec('ALTER TABLE ibf_skins ADD COLUMN set_id int(5) NOT NULL DEFAULT "0"');
        $pdo->exec('UPDATE ibf_skins SET set_id = 1');
        $pdo->exec('ALTER TABLE ibf_skins MODIFY COLUMN css_id int(10) NOT NULL DEFAULT "1"');
        $pdo->exec('UPDATE ibf_skins SET css_id=1 WHERE sid = 0');
        $pdo->exec('UPDATE ibf_skins SET css_id=8 WHERE sid = 8');
        $pdo->exec('UPDATE ibf_skins SET css_id=1 WHERE sid = 2');
        $pdo->exec('UPDATE ibf_skins SET css_id=5 WHERE sid = 4');
        $pdo->exec('UPDATE ibf_skins SET css_id=5 WHERE sid = 5');
        $pdo->exec('UPDATE ibf_skins SET css_id=6 WHERE sid = 6');
        $pdo->exec('UPDATE ibf_skins SET css_id=6 WHERE sid = 7');
        $pdo->exec('UPDATE ibf_skins SET css_id=11 WHERE sid = 10');
        $pdo->exec('UPDATE ibf_skins SET css_id=10 WHERE sid = 9');
        $pdo->exec('UPDATE ibf_skins SET css_id=14 WHERE sid = 11');

        $pdo->exec('ALTER TABLE ibf_skins ADD COLUMN default_set tinyint(1) NOT NULL DEFAULT 0');
        $pdo->exec('UPDATE ibf_skins SET default_set = 1 WHERE sid = 0');
    }
}
