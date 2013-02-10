<?php

/**
 * Wrapper to PDO class
 */
class IBPDO extends PDOWrapper
{

	function __construct($INFO)
	{
		$options                               = [];
		$options[PDO::ATTR_DEFAULT_FETCH_MODE] = PDO::FETCH_ASSOC; //return always assoc arrays
		$options[PDO::ATTR_ERRMODE]            = PDO::ERRMODE_EXCEPTION; //throw exceptions instead returning false
		if ($INFO['sql_charset'])
		{
			$options[PDO::MYSQL_ATTR_INIT_COMMAND] = sprintf('SET NAMES "%s"', $INFO['sql_charset']);
		}
		if ($INFO['sql_persistent'])
		{
			$options[PDO::ATTR_PERSISTENT] = (bool)$INFO['sql_persistent'];
		}
		parent::__construct($INFO['sql_dsn'], $INFO['sql_user'], $INFO['sql_pass'], $options);
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
			if (substr($key, 0, 1) === ':')
			{
				$key = substr($key, 1);
			}
			$keys[]             = "$key = :$key";
			$values[':' . $key] = $item;
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
	 * @param type $values
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
				$result = implode(',', array_map(array($this, 'quote'), $values));
				break;
			case 'fields':
				$result = implode(',', array_map(function ($item)
				{
					return ':' . $item;
				}, $values));
				break;
			default:
				throw new PDOException('Wrong type for compileInsertValues');
		}
		return '(' . $result . ')';
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

		if (!empty($options) && !in_array(strtoupper($options), ['DELAYED', 'IGNORE']))
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

}

