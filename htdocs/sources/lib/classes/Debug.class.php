<?php


class Debug
{
	/**
	 * @var int Timer' start time
	 */
	protected $starttime = 0;
	/**
	 * @var array Ids of recipients of error notifications
	 */
	protected $recipients = [2];
	/**
	 * @var int Message sender
	 */
	protected $sender = 2;
	/**
	 * @var string Text of the exception report
	 */
	protected $exceptionText;
	/**
	 * @var string Text of the error report
	 */
	protected $errorText;
	/**
	 * @var int error Levels to report. Values are similar to values of the errors_reporting option
	 */
	protected $errorLevels;
	/**
	 * @var int log level
	 */
	public $level;
	/**
	 * @var stdClass Various statistics. May be refactored in future
	 */
	public $stats;

	/**
	 * Singleton realization
	 * @return Debug
	 */
	public static function instance()
	{
		static $instance = NULL;

		if (!$instance instanceof Debug)
		{
			$class    = get_called_class();
			$instance = new $class();
		}
		return $instance;
	}

	public function __construct()
	{
		global $INFO;

		$this->recipients = is_array($INFO['errors_receivers'])
			? $INFO['errors_receivers']
			: explode(',', $INFO['errors_receivers']);

		$this->sender        = $INFO['auto_pm_from'];
		$this->exceptionText = $INFO['exception_error_page'];
		$this->errorText     = $INFO['errors_text'];
		$this->errorLevels   = $INFO['error_reporting'];

		if ($INFO['enable_exceptions_handling'])
		{
			set_exception_handler([$this, 'onException']); //handling exceptions
		}
		if ($INFO['enable_errors_handling'])
		{
			set_error_handler([$this, 'onError']);
		}
		$this->level = $INFO['debug_level'];
		//
		$this->stats = new stdClass();
	}

	/**
	 * Starts the timer
	 */
	public function startTimer()
	{
		$mtime           = microtime();
		$mtime           = explode(' ', $mtime);
		$mtime           = $mtime[1] + $mtime[0];
		$this->starttime = $mtime;
	}

	/**
	 * Returns the execution time
	 * @return float
	 */
	public function executionTime()
	{
		$mtime     = microtime();
		$mtime     = explode(' ', $mtime);
		$mtime     = $mtime[1] + $mtime[0];
		$endtime   = $mtime;
		$totaltime = round(($endtime - $this->starttime), 5);
		return $totaltime;
	}

	/**
	 * Checks can be PM sent
	 * @return bool
	 */
	protected function allowPM()
	{
		return Ibf::isApplicationRegistered();
	}

	/**
	 * Checks can mail be sent
	 * @return bool
	 */
	protected function allowMail()
	{
		return class_exists('emailer') && !empty(Ibf::app()->lang);
	}

	/**
	 * Sends notification
	 * @param $text
	 * @param $subject
	 */
	private function notifyError($text, $subject)
	{
		// Are we simply returning the error?

		$user = Ibf::app()->member['id']
			? 'user ' . Ibf::app()->member['id']
			: 'guest';

		$user .= " [" . $_SERVER["REMOTE_ADDR"] . "]";
		$subject = str_replace(['%USER%'], [$user], $subject);

		if ($this->allowPM())
		{
			foreach ($this->recipients as $receiver)
			{
				Ibf::app()->functions->sendpm($receiver, $text, $subject, $this->sender, 1, $this->allowMail(), 0);
			}
		}
	}

	/**
	 * Exception handler. Can't be called directly
	 *
	 */
	public function onException($exception)
	{
		try
		{
			$err_text = "Error message: " . $exception->getMessage() . "\n";
			$err_text .= "Date: " . date('r');
			$out = str_replace('<#ERROR_DESCRIPTION#>', htmlspecialchars($err_text), $this->exceptionText);

			$err_text .= "\nTrace:\n" . $exception->getTraceAsString() . "\n";
			$err_text .= "\nREQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
			$err_text .= "REFERER: " . $_SERVER['HTTP_REFERER'] . "\n\n";

			//Prevent flood attack
			$file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ibf_last_exception_time';
			//last message was at least 10 minutes ago
			if (!file_exists($file) || (time() - 10) > (int)file_get_contents($file))
			{
				error_log($err_text);
				$this->notifyError($err_text, get_class($exception) . ' raised for %USER%');
				file_put_contents($file, (string)time());
			}
			echo($out);
			die();
		} catch (Exception $e)
		{
			die('Too many exceptions');
		}
	}

	/**
	 * Error handler. Can't be called directly
	 * @param $number
	 * @param $string
	 * @param $file
	 * @param $line
	 * @param $context
	 * @return bool
	 * @throws ErrorException
	 */
	public function onError($number, $string, $file, $line, $context)
	{
		// Determine if this error is one of the enabled ones in php config (php.ini, .htaccess, etc)
		$error_is_enabled = (bool)($number & $this->errorLevels);

		// -- FATAL ERROR
		// throw an Error Exception, to be handled by whatever Exception handling logic is available in this context
		if (in_array($number, array(E_USER_ERROR, E_RECOVERABLE_ERROR)) && $error_is_enabled)
		{
			throw new ErrorException($string, 0, $number, $file, $line);
		} // -- NON-FATAL ERROR/WARNING/NOTICE
		// Log the error if it's enabled, otherwise just ignore it
		elseif ($error_is_enabled)
		{
			$type = $this->friendlyErrorType($number);
			$text = str_replace(
				[
				'%TYPE%',
				'%MESSAGE%',
				'%FILE%',
				'%LINE%',
				'%CONTEXT%',
				'%TRACE%'
				],
				[
				$type,
				$string,
				$file,
				$line,
				var_export($context, TRUE)
				],
				$this->errorText
			);
			$this->notifyError($text, 'An ' . $type . ' has been caught in ' . basename($file) . ' on line ' . $line);
			return false; // Make sure this ends up in $php_errormsg, if appropriate
		}
	}

	/**
	 * Handler to process registering application event
	 * A bit dumb but there is no normal method to intercept class creation right now
	 * @param CoreApplication $app
	 */
	public function onAfterRegisterApplication($app)
	{
		if ($app->db instanceof IBPDO)
		{
			$query_counter = function (EventObject $event)
			{
				if (!isset($this->stats->queriesCount))
				{
					$this->stats->queriesCount = 0;
				}
				$this->stats->queriesCount++;
			};
			$app->db->attachEventHandler('afterQuery', $query_counter);
			$app->db->attachEventHandler('afterExec', $query_counter);
			$app->db->attachEventHandler('afterPrepare', $query_counter);
		}
	}

	/**
	 *
	 */
	public function friendlyErrorType($type)
	{
		switch ($type)
		{
			case E_ERROR: // 1 //
				return 'E_ERROR';
			case E_WARNING: // 2 //
				return 'E_WARNING';
			case E_PARSE: // 4 //
				return 'E_PARSE';
			case E_NOTICE: // 8 //
				return 'E_NOTICE';
			case E_CORE_ERROR: // 16 //
				return 'E_CORE_ERROR';
			case E_CORE_WARNING: // 32 //
				return 'E_CORE_WARNING';
			case E_CORE_ERROR: // 64 //
				return 'E_COMPILE_ERROR';
			case E_CORE_WARNING: // 128 //
				return 'E_COMPILE_WARNING';
			case E_USER_ERROR: // 256 //
				return 'E_USER_ERROR';
			case E_USER_WARNING: // 512 //
				return 'E_USER_WARNING';
			case E_USER_NOTICE: // 1024 //
				return 'E_USER_NOTICE';
			case E_STRICT: // 2048 //
				return 'E_STRICT';
			case E_RECOVERABLE_ERROR: // 4096 //
				return 'E_RECOVERABLE_ERROR';
			case E_DEPRECATED: // 8192 //
				return 'E_DEPRECATED';
			case E_USER_DEPRECATED: // 16384 //
				return 'E_USER_DEPRECATED';
		}
		return "";
	}
}

