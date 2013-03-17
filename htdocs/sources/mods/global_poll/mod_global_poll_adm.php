<?php

/*
+--------------------------------------------------------------------------
|   Invision Power Board v1.2
|   ========================================
|   by Matthew Mecham
|   (c) 2001,2002 Invision Power Services
|   http://www.ibforums.com
|   ========================================
|   Web: http://www.ibforums.com
|   Email: phpboards@ibforums.com
|   Licence Info: phpib-licence@ibforums.com
+---------------------------------------------------------------------------
|
|   > Global Poll AdminModule
		$ibforums = Ibf::instance();
|   > Module written by Eike Falkenberg ('Koksi')
|   > Date started: 13th August 2003
|
|   > Module Version 1.2 b
|                                             >русский перевод> bizzznesmen
+--------------------------------------------------------------------------
*/

class AdminGlobalPoll
{

	//-------------------------------
	// the admin stuff:
	//-------------------------------
	function ad_settings()
	{

		global $ADMIN, $SKIN, $INFO, $std;
		$ibforums = Ibf::app();

		// letґs collect all polls and put them into a simple array
		$i             = 0;
		$pollArray[$i] = array(0, "нет глобального опроса");
		$i++;
		$stmt = $ibforums->db->query("SELECT pid,tid, poll_question FROM ibf_polls");
		while ($r = $stmt->fetch())
		{
			$pollArray[$i] = array($r['tid'], "[" . $r['tid'] . "] -> " . $r['poll_question']);
			$i++;
		}

		$ADMIN->html .= $SKIN->add_td_basic('Global Poll Mod', 'left', 'catrow2');

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Выберите опрос, который будет показан на главной станице форумов</b><br> <i>[ ID Опроса ] -> Вопрос опроса</i>",
		                                       $SKIN->form_dropdown('global_poll', $pollArray, $INFO['global_poll'])
		                                  ));

	}

	function save_config()
	{
		global $ADMIN, $master;
		$ibforums              = Ibf::app();
		$master['global_poll'] = $_POST['global_poll'];

		$ADMIN->rebuild_config($master);
		$ADMIN->save_log("Global Poll ID Updated, Back Up Written");
	}

}
