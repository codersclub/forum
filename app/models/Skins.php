<?php

namespace Models;

/**
 * Class Skins
 * Class for work with ibf_skins table
 * @package Models
 */
class Skins
{
    protected static function buildWhere($data){
        $params = [];
        $sql = '';
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $fields[]           = sprintf('%s = :%s', $key, $key);
                $params[':' . $key] = $value;
            }
            $sql = ' WHERE ' . implode(' AND ', $fields);
        }
        return [
            $sql,
            $params,
        ];
    }

    /**
     * Ищет запись по указанному фильтру полей
     * @param array $data Массив вида 'поле' => 'значение'
     * @return mixed
     */
    public static function find(array $data = [])
    {
        $sql    = 'SELECT * FROM ibf_skins';
        list($where, $params) = self::buildWhere($data);
        $sql .= $where . ' LIMIT 0,1';
        return \Ibf::app()->db->prepare($sql)
            ->execute($params)
            ->fetch();
    }

    /**
     * Ищет все записи по указанному фильтру полей
     * @param array $data Массив вида 'поле' => 'значение'
     * @return mixed
     */
    public static function findAll(array $data = []){
        $sql    = 'SELECT * FROM ibf_skins';
        list($where, $params) = self::buildWhere($data);
        $sql .= $where;
        return \Ibf::app()->db->prepare($sql)
            ->execute($params)
            ->fetchAll();
    }

    public static function count(array $data = []){
        $sql = 'SELECT COUNT(*) FROM ibf_skins';
        list($where, $params) = self::buildWhere($data);
        $sql .= $where;
        return \Ibf::app()->db->prepare($sql)
            ->execute($params)
            ->fetchColumn();
    }

    public static function add($data) {
        \Ibf::app()->db->insertRow("ibf_skins", $data);
    }
}
