<?php

/**
 * Wrapper to PDO class
 */
class IBPDO extends PDOWrapper
{
    const OPTION_IGNORE = 'IGNORE';
    const OPTION_DELAYED = 'DELAYED';

	use MixinTrait;

	function __construct()
	{
		$options                               = [];
		$options[PDO::ATTR_DEFAULT_FETCH_MODE] = PDO::FETCH_ASSOC; //return always assoc arrays
		$options[PDO::ATTR_ERRMODE]            = PDO::ERRMODE_EXCEPTION; //throw exceptions instead returning false

		$config = Config::get('database');
		if (!empty($config['charset']))
		{
			$options[PDO::MYSQL_ATTR_INIT_COMMAND] = sprintf('SET NAMES "%s"', $config['charset']);
		}
		if ($config['persistent'])
		{
			$options[PDO::ATTR_PERSISTENT] = (bool)$config['persistent'];
		}
		parent::__construct($config['dsn'], $config['user'], $config['password'], $options);
	}

	/**
	 * Helper to compile sql string for array of arguments
	 * @param mixed|array $params Array of arguments to sql. Also converted to use in PDOStatement::execute
	 * @return string String in format 'field1 = :field1,field2 = :field2,...'
	 */
	public static function compileKeyPairsString(&$params)
	{
		$keys   = [];
		$values = [];
		foreach ($params as $key => $item)
		{
			if (mb_substr($key, 0, 1) === ':')
			{
				$key = mb_substr($key, 1);
			}
			$keys[]             = "$key = :$key";
			if (is_bool($item)) {
			   $values[':' . $key] = intval($item);
			} else {
			    $values[':' . $key] = $item;
			}
		}
		$params = $values;
		return implode(',', $keys);
	}

	public static function placeholders($params)
	{
		return implode(',', array_fill(0, count($params), '?'));
	}

	/**
	 * Returns comma-separated fields for INSERT query
	 * @param mixed $values Array of values with fields as keys
	 * @return string
	 */
	public function compileInsertFields($values)
	{
		return '(' . implode(',', array_keys($values)) . ')';
	}

	/**
	 * Returns values string for INSERT query
	 * @param array $values
	 * @param string $type Return type. Can be one of 'fields', 'questions' and 'values'
	 */
	public function compileInsertValues($values, $type = 'values')
	{
		$result = '';
		switch ($type)
		{
			case 'questions':
				$result = self::placeholders($values);
				break;
			case 'values':
			    $values = array_map(
					function ($v) {
					    if (is_null($v)) {
					        return $this->quote($v, \PDO::PARAM_NULL);
					    } elseif (is_bool($v)) {
					        return $this->quote($v, \PDO::PARAM_BOOL);
					    } else {
					        return $this->quote($v, \PDO::PARAM_STR);
					    }
					},
					$values
				);
				$result = implode(',', $values);
				break;
			case 'fields':
				$result = implode(
					',',
					array_map(
						function ($item)
						{
							return ':' . $item;
						},
						$values
					)
				);
				break;
			default:
				throw new \PDOException('Wrong type for compileInsertValues');
		}
		return '(' . $result . ')';
	}

	public function quote($string, $parameter_type = PDO::PARAM_STR)
    {
        if ($string === false) {
            return parent::quote(0, $parameter_type);
        } elseif ($string === null) {
            return 'NULL';
        }
        return parent::quote($string, $parameter_type);
    }

    /**
	 * Adds a row into the table
	 * @param string $table Table name
	 * @param string $op Method
	 * @param array $values
	 * @param string $options
	 * @return null
	 */
	private function addRow($table, $op = 'INSERT', $values, $options = '', $prepare = false, $named = false)
	{
		assert(!empty($table));
		assert(!empty($values));

		if (!empty($options) && !in_array(mb_strtoupper($options), [self::OPTION_DELAYED, self::OPTION_IGNORE]))
		{
			$options = '';
		}
		$sql = $op . ' ' . $options . ' INTO ' . $table . ' ';
		if (!is_numeric(key($values)))
		{
			$sql .= $this->compileInsertFields($values);
		}
		$type = $prepare
			? $named
				? 'fields'
				: 'questions'
			: 'values';
		$sql .= ' VALUES ' . $this->compileInsertValues($values, $type);
		return $prepare
			? $this->prepare($sql)
			: $this->exec($sql);
	}

	/**
	 * Inserts a row into the table
	 * @param string $table Table name
	 * @param array $values
	 * @param string $options
	 * @return null
	 */
	public function insertRow($table, $values, $options = '', $prepare = false, $named = false)
	{
		$this->addRow($table, 'INSERT', $values, $options, $prepare, $named);
	}

	/**
	 * Replace row in the table
	 * @param string $table Table name
	 * @param array $values
	 * @param string $options
	 * @return null
	 */
	public function replaceRow($table, $values, $options = '', $prepare = false, $named = false)
	{
		$this->addRow($table, 'REPLACE', $values, $options, $prepare, $named);
	}

	/**
	 * Update table
	 * @todo Переделать
	 * @param string $table Table name
	 * @param array|string $values Prepared values for update
	 * @param string $where Prepared where part
	 */
	public function updateRow($table, $values, $where = NULL)
	{
		assert(!empty($table));
		assert(!empty($values));

		$set = [];
		foreach ($values as $key => $value)
		{
			$set[] = "$key = $value";
		}
		$sql = 'UPDATE ' . $table . ' SET ' . implode(',', $set);
		if ($where)
		{
			$sql .= ' WHERE ' . $where;
		}
		return $this->exec($sql);
	}

	protected function beforeQuery()
	{
		$this->raiseEvent('beforeQuery', new EventObject($this));
	}

	protected function afterQuery()
	{
		$this->raiseEvent('afterQuery', new EventObject($this));
	}

	public function query($statement)
	{
		$this->beforeQuery();
		$result = parent::query($statement);
		$this->afterQuery();
		return $result;
	}

	protected function beforeExec()
	{
		$this->raiseEvent('beforeExec', new EventObject($this));
	}

	protected function afterExec()
	{
		$this->raiseEvent('afterExec', new EventObject($this));
	}

	public function exec($statement)
	{
		$this->beforeExec();
		$result = parent::exec($statement);
		$this->afterExec();
		return $result;
	}

	protected function beforePrepare()
	{
		$this->raiseEvent('beforePrepare', new EventObject($this));
	}

	protected function afterPrepare()
	{
		$this->raiseEvent('afterPrepare', new EventObject($this));
	}

	public function prepare($statement, array $driver_options = null)
	{
		$this->beforePrepare();
		$result = parent::prepare($statement, $driver_options);
		$this->afterPrepare();
		return $result;
	}

}

