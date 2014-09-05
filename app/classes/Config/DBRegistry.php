<?php
/**
 * @file
 */

namespace Config;

class DBRegistry extends Registry
{
    public function __construct()
    {
        //
    }

    protected function getConfigs($name)
    {
        if (!isset($this->configs[$name])) {
            $stmt = \Ibf::app()->db->prepare('SELECT * FROM ibf_variables WHERE name = :name')
                ->bindValue(':name', strtolower($name))
                ->execute();
            if ($stmt->rowCount() > 0) {
                $value = @unserialize($stmt->fetch(\PDO::FETCH_ASSOC)['data']);
            }
            if (!isset($value) || $value === false) {
                trigger_error(sprintf('Access to undefined property %s', $name), E_USER_NOTICE);
                $value = [];
            }
            $this->configs[$name] = $value;
        }
        return $this->configs[$name];
    }

    protected function isConfigExists($name)
    {
        return 0 < \Ibf::app()->db->prepare('SELECT count(*) FROM ibf_variables WHERE name = :name')
            ->bindParam(':name', $name)
            ->execute()
            ->fetchColumn();
    }

    public function commitChanges($name = null)
    {
        $insertStmt = \Ibf::app()->db->prepare('INSERT INTO ibf_variables (name, data) VALUES (:name, :data)');
        $updateStmt = \Ibf::app()->db->prepare('UPDATE ibf_variables SET data = :data WHERE name = :name');
        $write      = function ($name) use ($insertStmt, $updateStmt) {
            ${$this->isConfigExists($name)
                ? 'updateStmt'
                : 'insertStmt'}->bindValue(':name', strtolower($name))
                ->bindValue(':data', serialize($this->configs[$name]))
                ->execute();
        };

        if ($name !== null) {
            if (isset($this->configs[$name])) {
                $write($name);
            }
        } else {
            foreach ($this->configs as $name => $dummy) {
                $write($name);
            }
        }
    }

}
