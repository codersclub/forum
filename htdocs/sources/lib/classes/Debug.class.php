<?php

class Debug
{
	private $starttime = 0;
	private $recipients;
	private $sender;

	public function __construct()
	{
		global $INFO;

		$this->recipients = is_array($INFO['errors_receivers'])
			? $INFO['errors_receivers']
			: explode(',', $INFO['errors_receivers']);

		$this->sender = $INFO['auto_pm_from'];
		

		if ($INFO['enable_exceptions_handling'])
		{
			set_exception_handler([$this, 'onException']);//handling exceptions
		}
		if ($INFO['enable_errors_handling'])
		{
			set_error_handler([$this, 'onError']);
		}

	}

	function startTimer()
	{
		$mtime           = microtime();
		$mtime           = explode(' ', $mtime);
		$mtime           = $mtime[1] + $mtime[0];
		$this->starttime = $mtime;
	}

	function endTimer()
	{
		$mtime     = microtime();
		$mtime     = explode(' ', $mtime);
		$mtime     = $mtime[1] + $mtime[0];
		$endtime   = $mtime;
		$totaltime = round(($endtime - $this->starttime), 5);
		return $totaltime;
	}

	protected function allowPM()
	{
		return true;
	}

	protected function allowMail()
	{
		return false;
	}


	private function notifyError($text, $subject)
	{
		error_log($text);
		// Are we simply returning the error?

		$user = $this->member['id']
			? 'user ' . $this->member['id']
			: 'guest';

		$user .= " [" . $_SERVER["REMOTE_ADDR"] . "]";
		$subject  = str_replace(['%USER%'], [$user], $subject);

		$do_popup = 1;
		$do_mail  = 1;
		foreach ($INFO['errors_receivers'] as $receiver)
		{
			FUNC::instance()->sendpm($receiver, $text, $subject, $INFO['auto_pm_from'], $do_popup, $do_mail, 0);
		}
	}

	/**
	 * Exception handler
	 */
	public function onException($exception)
	{
		try
		{
			$the_error = "Error message: " . $exception->getMessage() . "\n";
			$the_error .= "Date: " . date('r');
			$out = str_replace('<#ERROR_DESCRIPTION#>', htmlspecialchars($the_error), $INFO['exception_error_page']);

			$the_error .= "\nTrace:\n" . $exception->getTraceAsString() . "\n";
			$the_error .= "\nREQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
			$the_error .= "REFERER: " . $_SERVER['HTTP_REFERER'] . "\n\n";

			//Prevent flood attack
			$file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ibf_last_exception_time';
			//last message was at least 10 minutes ago
			if (!file_exists($file) || (time() - 10) > (int)file_get_contents($file))
			{
				$this->notifyError($the_error, get_class($exception) . ' raised for %USER%');
				file_put_contents($file, (string)time());
			}
			echo($out);
			die();
		} catch (Exception $e)
		{
			die('Too many exceptions');
		}
	}

	public function onError($number, $string, $file, $line, $context)
	{
	   // Determine if this error is one of the enabled ones in php config (php.ini, .htaccess, etc)
		$error_is_enabled = (bool)($number & $INFO['error_reporting'] );

	    // -- FATAL ERROR
		// throw an Error Exception, to be handled by whatever Exception handling logic is available in this context
	    if( in_array($number, array(E_USER_ERROR, E_RECOVERABLE_ERROR)) && $error_is_enabled )
		{
		    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
	    }

		// -- NON-FATAL ERROR/WARNING/NOTICE
		// Log the error if it's enabled, otherwise just ignore it
	    elseif( $error_is_enabled )
		{
			$type = $this->functions->friendlyErrorType($number);
			$text = str_replace(
				['%TYPE%', '%MESSAGE%', '%FILE%', '%LINE%', '%CONTEXT%', '%TRACE%'],
				[$type, $string, $file, $line, var_export($context, TRUE)],
				$INFO['errors_text']
			);
			$this->notifyError($text, 'An ' . $type . ' has been caught in ' . basename($file) . ' on line ' . $line);
	        return false; // Make sure this ends up in $php_errormsg, if appropriate
		}
	}

}

