<?php

/**
 * @method bool beginTransaction ()
 * @method bool commit ()
 * @method bool rollBack ()
 * @method bool inTransaction ()
 * @method bool setAttribute ($attribute, $value)
 * @method int exec ($statement)
 * @method string lastInsertId ($name = null)
 * @method mixed errorCode ()
 * @method array errorInfo ()
 * @method int getAttribute ($attribute)
 * @method string quote ($string, $parameter_type = PDO::PARAM_STR)
 */
class PDOWrapper
{
	/**
	 * @var PDO
	 */
	private $_pdo;

	function __call($name, $arguments)
	{
		if (method_exists($this->_pdo, $name))
		{
			return call_user_func_array([$this->_pdo, $name], $arguments);
		}
	}

	/**
	 * @param $dsn string Connection info
	 * @param $username string Username
	 * @param $passwd string User password
	 * @param $options array PDO options
	 */
	public function __construct($dsn, $username, $passwd, $options)
	{
		$this->_pdo = new PDO($dsn, $username, $passwd, $options);
	}

	/**
	 * (PHP 5 &gt;= 5.1.0, PECL pdo &gt;= 0.1.0)<br/>
	 * Prepares a statement for execution and returns a statement object
	 * @link http://php.net/manual/en/pdo.prepare.php
	 * @param string $statement <p>
	 * This must be a valid SQL statement for the target database server.
	 * </p>
	 * @param array $driver_options [optional] <p>
	 * This array holds one or more key=&gt;value pairs to set
	 * attribute values for the PDOStatement object that this method
	 * returns. You would most commonly use this to set the
	 * PDO::ATTR_CURSOR value to
	 * PDO::CURSOR_SCROLL to request a scrollable cursor.
	 * Some drivers have driver specific options that may be set at
	 * prepare-time.
	 * </p>
	 * @return PDOStatementWrapper If the database server successfully prepares the statement,
	 * <b>PDO::prepare</b> returns a
	 * <b>PDOStatement</b> object.
	 * If the database server cannot successfully prepare the statement,
	 * <b>PDO::prepare</b> returns false or emits
	 * <b>PDOException</b> (depending on error handling).
	 * </p>
	 * <p>
	 * Emulated prepared statements does not communicate with the database server
	 * so <b>PDO::prepare</b> does not check the statement.
	 */
	public function prepare($statement, array $driver_options = null)
	{

		//wtf? We can set null as default value of array arg, but we can't pass it
		$stmt = $driver_options === null
			? $this->_pdo->prepare($statement)
			: $this->_pdo->prepare($statement, $driver_options);
		if ($stmt instanceof PDOStatement)
		{
			return new PDOStatementWrapper($stmt);
		}
	}

	/**
	 * (PHP 5 &gt;= 5.1.0, PECL pdo &gt;= 0.2.0)<br/>
	 * Executes an SQL statement, returning a result set as a PDOStatement object
	 * @link http://php.net/manual/en/pdo.query.php
	 * @param string $statement <p>
	 * The SQL statement to prepare and execute.
	 * </p>
	 * <p>
	 * Data inside the query should be properly escaped.
	 * </p>
	 * @return PDOStatementWrapper <b>PDO::query</b> returns a PDOStatement object, or false
	 * on failure.
	 */
	public function query($statement)
	{
		$stmt = $this->_pdo->query($statement);
		if ($stmt instanceof PDOStatement)
		{
			return new PDOStatementWrapper($stmt);
		}
	}

	/**
	 * (PHP 5 &gt;= 5.1.3, PECL pdo &gt;= 1.0.3)<br/>
	 * Return an array of available PDO drivers
	 * @link http://php.net/manual/en/pdo.getavailabledrivers.php
	 * @return array <b>PDO::getAvailableDrivers</b> returns an array of PDO driver names. If
	 * no drivers are available, it returns an empty array.
	 */
	public static function getAvailableDrivers()
	{
		return PDO::getAvailableDrivers();
	}
}
