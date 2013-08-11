<?php

class CoreApplication
{
	/**
	 * @var IBPDO $db
	 */
	public $db;
	/**
	 * @var session
	 */
	public $session;
	/**
	 * @var functions
	 */
	public $functions;
	/**
	 *
	 */
	public $input;
	/**
	 * @var Member
	 */
	public $member;

	/**
	 *
	 */
	public $skin;

	/**
	 *
	 */
	public $lang_id;
	public $lang = "";

	public function __construct()
	{
		global $INFO;
		$this->vars      = & $INFO;
		$this->functions = new functions();

		if (!$this->initDB())
		{
			echo "<h1>Слишком много подключений к серверу. Пожалуйста подождите несколько минут и повторите попытку.</h1>";
			exit;
		}
	}

	public function init()
	{
		$this->input  = $this->loadInputData();
		$this->member = $this->loadMember();
		$this->lang   = $this->loadLanguage();
		$this->skin   = $this->loadSkin();
	}

	/**
	 * Incoming data loader
	 * @return array
	 */
	protected function loadInputData()
	{
		return $this->functions->parse_incoming();
	}

	/**
	 * Member init
	 * @return array
	 */
	protected function loadMember()
	{
		return $this->session->authorise();
	}

	/**
	 * Skin init
	 * @return mixed
	 */
	protected function loadSkin()
	{
		return $this->functions->load_skin();
	}

	protected function loadLanguage()
	{
		if (!$this->vars['default_language'])
		{
			$this->vars['default_language'] = 'en';
		}

		$this->lang_id = $this->member['language']
			? $this->member['language']
			: $this->vars['default_language'];

		if (($this->lang_id != $this->vars['default_language']) and (!is_dir(ROOT_PATH . "lang/" . $this->lang_id)))
		{
			$this->lang_id = $this->vars['default_language'];
		}
		return $this->functions->load_words($this->lang, 'lang_global', $this->lang_id);

	}

	/**
	 * Database initialization
	 */
	final protected function InitDB()
	{
		try
		{
			$this->db = new IBPDO($this->vars);
		} catch (PDOException $e)
		{
			//todo do something
			die($e->getMessage());
			return false;
		}
		return TRUE;
	}

}
