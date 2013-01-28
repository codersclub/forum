<?php

class Core {
	/**
	 * @var IBPDO $db
	 */
	public $db;
	/**
	 * @var session
	 */
	public $session;
	/**
	 * @var FUNC
	 */
	public $functions;
	/**
	 *
	 */
	 public $input;
	 public $member;
	 public $skin;

	/**
	 *
	 * @staticvar info $instance
	 * @return \Core
	 */
	public static function instance()
	{
		static $instance = NULL;
		if ($instance === NULL)
		{
			$name = get_called_class();
			$instance = new $name();
		}
		return $instance;
	}

	public function __construct(){
		global $INFO;
		$this->vars = &$INFO;
		$this->session = new session();
		$this->functions = new FUNC();
		//todo uncomment this
		//set_exception_handler([$this, 'onException']);//handling exceptions
		if (!$this->initDB()){
			echo "<h1>Слишком много подключений к серверу. Пожалуйста подождите несколько минут и повторите попытку.</h1>";
			exit;
		}
	}

	public function init(){
		$this->input 	= $this->functions->parse_incoming();
		$this->member 	= $this->session->authorise();
		$this->skin     = $this->functions->load_skin();
	
	}

	/**
	 * Exception handler
	 */
	public function onException($exception)
	{
		global $sess;

		try {
			$the_error .= "Error message: " . $exception->getMessage() . "\n";
			$the_error .= "Date: " . date('r');

			// Are we simply returning the error?
			$out = str_replace('<#ERROR_DESCRIPTION#>', htmlspecialchars($the_error), $this->vars['exception_error_page']);

			//Prevent flood attack
			$file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ibf_last_exception_time';
			//last message was at least 10 minutes ago
			if (!file_exists($file) || (time() - 10) > (int)file_get_contents($file))
			{
				$user = $this->member['id']
					? 'user ' . $this->member['id']
					: 'guest';

				$user .= " [". $_SERVER["REMOTE_ADDR"] ."]";
				$the_error .= "\nTrace:\n" . $exception->getTraceAsString() . "\n";
				$the_error .= "\nREQUEST_URI: ".$_SERVER['REQUEST_URI']."\n";
				$the_error .= "REFERER: ".$_SERVER['HTTP_REFERER']."\n\n";

				foreach ($this->vars['errors_receivers'] as $receiver) {
					FUNC::instance()->sendpm(
						$receiver,
						$the_error,
						get_class($exception) . ' raised for ' . $user,
						$s->vars['auto_pm_from'],
						1,//popup
						1,//mail
						0
					);
				}
				file_put_contents($file, (string)time());
			}
			echo($out);
			die();
		}catch(Exception $e){
			die('Too many exceptions');
		}
	}

	/**
	 * Database initialization
	 */
	final protected function InitDB()
	{
		try {
			$this->db = new IBPDO($this->vars);
		} catch (PDOException $e) {
			//todo do something
			die($e->getMessage());
			return false;
		}
		return TRUE;
	}


}
