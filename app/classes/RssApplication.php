<?php
/**
 * Created by JetBrains PhpStorm.
 * User: yuri
 * Date: 09.03.13
 * Time: 11:37
 * To change this template use File | Settings | File Templates.
 */
class RssApplication extends CoreApplication
{
	var $input = array();
	var $base_url = "";
	var $vars = "";

	public function __construct()
	{
		parent::__construct();
		$this->session = new session();
	}

	public function init()
	{
		parent::init();
		$this->input['act'] = 'yandex';
	}

	protected function loadMember()
	{
		$data = parent::loadMember();
		if ($data['id'])
		{
			$club = $this->db
				->prepare("SELECT read_perms FROM ibf_forums WHERE id=:id")
				->bindParam(':id', $this->vars['club'], PDO::PARAM_INT)
				->execute()
				->fetch();

			if ($club)
			{
				$data['club_perms'] = $club['read_perms'];
			}
		}

		// disable highlight to reduce power of parser
		$data['syntax'] = 'none';

		$data['rss'] = 1;

	}
}

