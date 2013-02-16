<?php

/**
 * @method mixed fetch ($fetch_style = null, $cursor_orientation = PDO::FETCH_ORI_NEXT, $cursor_offset = 0)
 * @method int rowCount ()
 * @method string fetchColumn ($column_number = 0)
 * @method array fetchAll ($fetch_style = null, $fetch_argument = null, array $ctor_args = 'array()')
 * @method mixed fetchObject ($class_name = "stdClass", array $ctor_args = null)
 * @method string errorCode ()
 * @method array errorInfo ()
 * @method bool setAttribute ($attribute, $value)
 * @method mixed getAttribute ($attribute)
 * @method int columnCount ()
 * @method array getColumnMeta ($column)
 * @method bool setFetchMode ($mode)
 * @method bool nextRowset ()
 * @method bool closeCursor ()
 */
class PDOStatementWrapper implements IteratorAggregate
{
	/**
	 * @var PDOStatement
	 */
	private $_statement;

	/**
	 * Magic __call. Tries to execute given method in PDOStatement
	 */
	function __call($name, $arguments)
	{
		if (method_exists($this->_statement, $name))
		{
			return call_user_func_array([$this->_statement, $name], $arguments);
		}
	}

	function __construct(PDOStatement $statement)
	{
		$this->_statement = $statement;
	}

	function getIterator()
	{
		return $this->_statement;
	}

	/**
	 * (PHP 5 &gt;= 5.1.0, PECL pdo &gt;= 0.1.0)<br/>
	 * Executes a prepared statement
	 * @link http://php.net/manual/en/pdostatement.execute.php
	 * @param array $input_parameters [optional] <p>
	 * An array of values with as many elements as there are bound
	 * parameters in the SQL statement being executed.
	 * All values are treated as <b>PDO::PARAM_STR</b>.
	 * </p>
	 * <p>
	 * You cannot bind multiple values to a single parameter; for example,
	 * you cannot bind two values to a single named parameter in an IN()
	 * clause.
	 * </p>
	 * <p>
	 * You cannot bind more values than specified; if more keys exist in
	 * <i>input_parameters</i> than in the SQL specified
	 * in the <b>PDO::prepare</b>, then the statement will
	 * fail and an error is emitted.
	 * </p>
	 * @return PDOStatementWrapper
	 */
	public function execute(array $input_parameters = null)
	{
		$this->_statement->execute($input_parameters);
		return $this;
	}

	/**
	 * (PHP 5 &gt;= 5.1.0, PECL pdo &gt;= 0.1.0)<br/>
	 * Binds a parameter to the specified variable name
	 * @link http://php.net/manual/en/pdostatement.bindparam.php
	 * @param mixed $parameter <p>
	 * Parameter identifier. For a prepared statement using named
	 * placeholders, this will be a parameter name of the form
	 * :name. For a prepared statement using
	 * question mark placeholders, this will be the 1-indexed position of
	 * the parameter.
	 * </p>
	 * @param mixed $variable <p>
	 * Name of the PHP variable to bind to the SQL statement parameter.
	 * </p>
	 * @param int $data_type [optional] <p>
	 * Explicit data type for the parameter using the PDO::PARAM_*
	 * constants.
	 * To return an INOUT parameter from a stored procedure,
	 * use the bitwise OR operator to set the PDO::PARAM_INPUT_OUTPUT bits
	 * for the <i>data_type</i> parameter.
	 * </p>
	 * @param int $length [optional] <p>
	 * Length of the data type. To indicate that a parameter is an OUT
	 * parameter from a stored procedure, you must explicitly set the
	 * length.
	 * </p>
	 * @param mixed $driver_options [optional] <p>
	 * </p>
	 * @return PDOStatementWrapper
	 */
	public function bindParam($parameter, &$variable, $data_type = PDO::PARAM_STR, $length = null, $driver_options = null)
	{
		$this->_statement->bindParam($parameter, $variable, $data_type, $length, $driver_options);
		return $this;
	}

	/**
	 * (PHP 5 &gt;= 5.1.0, PECL pdo &gt;= 0.1.0)<br/>
	 * Bind a column to a PHP variable
	 * @link http://php.net/manual/en/pdostatement.bindcolumn.php
	 * @param mixed $column <p>
	 * Number of the column (1-indexed) or name of the column in the result set.
	 * If using the column name, be aware that the name should match the
	 * case of the column, as returned by the driver.
	 * </p>
	 * @param mixed $param <p>
	 * Name of the PHP variable to which the column will be bound.
	 * </p>
	 * @param int $type [optional] <p>
	 * Data type of the parameter, specified by the PDO::PARAM_* constants.
	 * </p>
	 * @param int $maxlen [optional] <p>
	 * A hint for pre-allocation.
	 * </p>
	 * @param mixed $driverdata [optional] <p>
	 * Optional parameter(s) for the driver.
	 * </p>
	 * @return PDOStatementWrapper
	 */
	public function bindColumn($column, &$param, $type = null, $maxlen = null, $driverdata = null)
	{
		$this->_statement->bindColumn($column, $param, $type, $maxlen, $driverdata);
		return $this;
	}

	/**
	 * (PHP 5 &gt;= 5.1.0, PECL pdo &gt;= 1.0.0)<br/>
	 * Binds a value to a parameter
	 * @link http://php.net/manual/en/pdostatement.bindvalue.php
	 * @param mixed $parameter <p>
	 * Parameter identifier. For a prepared statement using named
	 * placeholders, this will be a parameter name of the form
	 * :name. For a prepared statement using
	 * question mark placeholders, this will be the 1-indexed position of
	 * the parameter.
	 * </p>
	 * @param mixed $value <p>
	 * The value to bind to the parameter.
	 * </p>
	 * @param int $data_type [optional] <p>
	 * Explicit data type for the parameter using the PDO::PARAM_*
	 * constants.
	 * </p>
	 * @return PDOStatementWrapper
	 */
	public function bindValue($parameter, $value, $data_type = PDO::PARAM_STR)
	{
		$this->_statement->bindValue($parameter, $value, $data_type);
		return $this;
	}
}
