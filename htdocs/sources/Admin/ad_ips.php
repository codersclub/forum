<?php

/*
+--------------------------------------------------------------------------
|   Invision Power Board v1.2
|   ========================================
|   by Matthew Mecham
|   (c) 2001 - 2003 Invision Power Services
|   http://www.invisionpower.com
|   ========================================
|   Web: http://www.invisionboard.com
|   Email: matt@invisionpower.com
|   Licence Info: http://www.invisionboard.com/?license
+---------------------------------------------------------------------------
|
|   > IPS Remote Call thingy
|   > Module written by Matt Mecham
|   > Date started: 17th October 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

// Ensure we've not accessed this script directly:

$idx = new ad_ips();

class ad_ips
{

	var $base_url;

	var $colours = array();

	var $url = "http://www.invisionboard.com/acp/";

	var $version = "1.1";

	function ad_ips()
	{
		global $IN;

		//---------------------------------------
		// Kill globals - globals bad, Homer good.
		//---------------------------------------

		$tmp_in = array_merge($_GET, $_POST, $_COOKIE);

		foreach ($tmp_in as $k => $v)
		{
			unset($$k);
		}

		//---------------------------------------

		switch ($IN['code'])
		{

			case 'news':
				$this->news();
				break;

			case 'updates':
				$this->updates();
				break;

			case 'docs':
				$this->docs();
				break;

			case 'support':
				$this->support();
				break;

			case 'host':
				$this->host();
				break;

			case 'purchase':
				$this->purchase();
				break;

			//-------------------------
			default:
				exit();
				break;
		}

	}

	function news()
	{
		@header("Location: " . $this->url . "?news");
		exit();
	}

	function updates()
	{
		//@header("Location: ".$this->url."?updates&version=".$this->version);
		@header("Location: " . $this->url . "?updates");
		exit();
	}

	function docs()
	{
		@header("Location: " . $this->url . "?docs");
		exit();
	}

	function support()
	{
		@header("Location: " . $this->url . "?support");
		exit();
	}

	function host()
	{
		@header("Location: " . $this->url . "?host");
		exit();
	}

	function purchase()
	{
		@header("Location: " . $this->url . "?purchase");
		exit();
	}

}
