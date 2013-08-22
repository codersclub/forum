<?php

class Migration_2013_08_20_15_18_35 extends MpmMigration
{

    const UP    = 'utf8';
    const DOWN  = 'cp1251';
 
    /**
    * Возвращает имя таблицы - оно реально нужно для SQL запроса.
    * Я хз, как работает система, но чисто теоретически оно должно вернуть что надо, судя по сырцам движка миграций.
    * Ну или парсить регуляркой DSL в конфигах, сами поставили, молодцы =)))
    *
    * @return mixed
    * @throws Exception
    */
    protected function getTableName()
    {
        error_reporting(E_ALL);
        if (!isset($GLOBALS['db_config']) || !isset($GLOBALS['db_config']->name)) {
            throw new \Exception('Базы данных нету в этих глобалсах (привет 2000-е года!). ' .
                'Надо сделать с этим что-то срочно! Очень! Внезапно!');
        }
        $tableName = $GLOBALS['db_config']->name; // База данных реально нужна для хардкорных запросов
        return $tableName;
    }
 
    /**
    * Сама конвертация. Магия.
    *
    * @param PDO $db
    * @param $from
    * @param $to
    */
    protected function convert(\PDO $db, $from, $to)
    {
        $tableName = $this->getTableName();
        $db->query('SET NAMES ' . $from);
        $tables = $db->query("SELECT
           CONCAT('ALTER TABLE `', t.`TABLE_SCHEMA` ,  '`.`', t.`TABLE_NAME` ,  '` CONVERT TO CHARACTER SET " . $to .
            " COLLATE " . $to . "_general_ci;')
           AS sqlcode
           FROM  `information_schema`.`TABLES` t
           WHERE 1
           AND t.`TABLE_SCHEMA` =  '" . $tableName . "'
           ORDER BY 1"
        );
        $queries = 0;
        foreach ($tables as $table) {
            $query = end($table);
 
            $log = 'query [' . $queries++ . ']:';
            echo $log . str_repeat(' ', 16 - strlen($log)) . $query . "\n";
            $db->query($query);
        }
    }
 
 
    /**
    * @param PDO $db
    */
    public function up(PDO &$db)
    {
        $this->convert($db, self::DOWN, self::UP);
		$db->query('alter database ' . $this->getTableName() . ' default charset ' . self::UP);
		$db->query("update ibf_templates SET template = REPLACE(template, 'windows-1251', 'UTF-8')");
    }
 
    /**
    * @param PDO $db
    */
    public function down(PDO &$db)
    {
        $this->convert($db, self::DOWN, self::UP);
		$db->query('alter database ' . $this->getTableName() . ' default charset ' . self::DOWN);
		$db->query("update ibf_templates SET template = REPLACE(template, 'UTF-8', 'windows-1251')");
    }

}
