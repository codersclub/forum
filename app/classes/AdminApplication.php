<?php
/**
 * Created by JetBrains PhpStorm.
 * User: yuri
 * Date: 09.03.13
 * Time: 11:50
 * To change this template use File | Settings | File Templates.
 */
class AdminApplication extends CoreApplication{

	var $vars       = "";
	var $version    = '1.2';
	var $acpversion = '12005';
	var $base_url   = '';

	public function init()
	{
		global $INFO;
		$this->vars['TEAM_ICON_URL']   = $INFO['html_url'] . '/team_icons';
		$this->vars['AVATARS_URL']     = $INFO['html_url'] . '/avatars';
		$this->vars['EMOTICONS_URL']   = $INFO['html_url'] . '/emoticons';
		$this->vars['mime_img']        = $INFO['html_url'] . '/mime_types';

		$this->base_url = $INFO['board_url']."/index.".$INFO['php_ext'].'?';
	}
}
