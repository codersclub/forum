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
|   > Admin "welcome" screen functions
|   > Module written by Matt Mecham
|   > Date started: 1st march 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new index_page();

class index_page
{

	var $mysql_version = "";

	function __construct()
	{
		global $IN, $INFO, $ADMIN, $MEMBER, $SKIN, $std, $ibforums;

		//---------------------------------------
		// Kill globals - globals bad, Homer good.
		//---------------------------------------

		$tmp_in = array_merge($_GET, $_POST, $_COOKIE);

		foreach ($tmp_in as $k => $v)
		{
			unset($$k);
		}

		//---------------------------------------

		$ADMIN->page_title  = "Welcome to the Invision Power Board Administration CP";
		$ADMIN->page_detail = "You can set up and customize your board from within this control panel.<br><br>Clicking on one of the links in the left menu pane will show you the relevant options for that administration category. Each option will contain further information on configuration, etc.";

		//---------------------------------
		// Get mySQL & PHP Version
		//---------------------------------

		$stmt = $ibforums->db->query("SELECT VERSION() AS version");

		if (!$row = $stmt->fetch())
		{
			$stmt = $ibforums->db->query("SHOW VARIABLES LIKE 'version'");
			$row  = $stmt->fetch();
		}

		$this->mysql_version = $row['version'];

		$phpv = phpversion();

		$ADMIN->page_detail .= "<br><br><b><a href='http://www.php.net' target='_blank'>PHP</a> VERSION:</b> $phpv, <b><a href='http://www.mysql.com' target='_blank'>MySQL</a> VERSION:</b> " . $this->mysql_version;

		$ADMIN->page_detail .= ", <a href='http://forum.sources.ru/admin.php?adsess={$IN['AD_SESS']}&act=mysql&code=processes'><b>SHOW PROCESSLIST</b></a>";

		//---------------------------------

		$stmt = $ibforums->db->query("SELECT * FROM ibf_stats");

		$row = $stmt->fetch();

		if ($row['TOTAL_REPLIES'] < 0)
		{
			$row['TOTAL_REPLIES'] = 0;
		}
		if ($row['TOTAL_TOPICS'] < 0)
		{
			$row['TOTAL_TOPICS'] = 0;
		}
		if ($row['MEM_COUNT'] < 0)
		{
			$row['MEM_COUNT'] = 0;
		}

		$stmt = $ibforums->db->query("SELECT COUNT(*) as reg
					FROM ibf_validating
					WHERE validate_type<>'lost_pass'");
		$reg  = $stmt->fetch();

		if ($reg['reg'] < 1)
		{
			$reg['reg'] = 0;
		}

		$stmt  = $ibforums->db->query("SELECT COUNT(*) as coppa
					FROM ibf_validating
					WHERE validate_type='coppa_user'");
		$coppa = $stmt->fetch();

		if ($coppa['coppa'] < 1)
		{
			$coppa['coppa'] = 0;
		}

		//-------------------------------------------------
		// Make sure the uploads path is correct
		//-------------------------------------------------

		$uploads_size = 0;

		if ($dh = opendir($INFO['upload_dir']))
		{
			while ($file = readdir($dh))
			{
				if (!preg_match("/^..?$|^index/i", $file))
				{
					$uploads_size += @filesize($INFO['upload_dir'] . "/" . $file);
				}
			}
			closedir($dh);
		}

		// This piece of code from Jesse's (jesse@jess.on.ca) contribution
		// to the PHP manual @ php.net

		if ($uploads_size >= 1048576)
		{
			$uploads_size = round($uploads_size / 1048576 * 100) / 100 . " mb";
		} else
		{
			if ($uploads_size >= 1024)
			{
				$uploads_size = round($uploads_size / 1024 * 100) / 100 . " k";
			} else
			{
				$uploads_size = $uploads_size . " bytes";
			}
		}

		//+-----------------------------------------------------------
		// BOARD OFFLINE?
		//+-----------------------------------------------------------

		if ($INFO['board_offline'])
		{

			$SKIN->td_header[] = array("&nbsp;", "100%");

			$ADMIN->html .= $SKIN->start_table("Offline Notice");

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "Your board is currently offline<br><br>&raquo; <a href='{$ADMIN->base_url}&act=op&code=board'>Turn Board Online</a>"
			                                  ));

			$ADMIN->html .= $SKIN->end_table();

			$ADMIN->html .= $SKIN->add_td_spacer();
		}

		$ADMIN->html .= $SKIN->start_form();

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "20%");

		$ADMIN->html .= $SKIN->start_table("Quick Clicks");

		$ADMIN->html .= "

					<script language='javascript'>
					<!--
					  function delete_members() {

						if (document.forms[0].deletemembers.value == \"\") {
							alert(\"You must enter a member email address!\");
						} else
						{
							window.parent.body.location = '{$SKIN->base_url}' + '&act=mem&code=delete_members&email=' + escape(document.forms[0].deletemembers.value);
						}
					  }

					  function unblock_email() {

						if (document.forms[0].emailunblock.value == \"\") {
							alert(\"You must enter a email!\");
						} else {
							window.parent.body.location = '{$SKIN->base_url}' + '&act=mem&code=unblock&email=' + escape(document.forms[0].emailunblock.value);
						}
					  }

					  function block_email() {

						if (document.forms[0].emailblock.value == \"\") {
							alert(\"You must enter a email!\");
						} else {
							window.parent.body.location = '{$SKIN->base_url}' + '&act=mem&code=block&email=' + escape(document.forms[0].emailblock.value) + '&reason=' + escape(document.forms[0].emailblockreason.value);
						}
					  }

					  function edit_member() {

						if (document.forms[0].username.value == \"\") {
							alert(\"You must enter a username!\");
						} else {
							window.parent.body.location = '{$SKIN->base_url}' + '&act=mem&code=stepone&USER_NAME=' + document.forms[0].username.value;
						}
					  }

					  function new_cat() {

						if (document.forms[0].cat_name.value == \"\") {
							alert(\"You must enter a category name!\");
						} else {
							window.parent.body.location = '{$SKIN->base_url}' + '&act=cat&code=new&name=' + escape(document.forms[0].cat_name.value);
						}
					  }

					  function new_forum() {

						if (document.forms[0].forum_name.value == \"\") {
							alert(\"You must enter a forum name!\");
						} else {
							window.parent.body.location = '{$SKIN->base_url}' + '&act=forum&code=new&name=' + escape(document.forms[0].forum_name.value);
						}
					  }
					//-->

					</script>
					<form name='DOIT' action=''>

		";

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "Block email address:",
		                                       "<input type='text' style='width:100%' id='textinput' name='emailblock' value='Enter email here'><br><br>
							   <textarea name='emailblockreason' rows='5' style='width:100%' class='textinput'>Enter reason here</textarea>",
		                                       "<input type='button' value='Block email' id='button' onClick='block_email()'>"
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "Unblock email address:",
		                                       "<input type='text' style='width:100%' id='textinput' name='emailunblock' value='Enter email here' onfocus='this.value=\"\"'>",
		                                       "<input type='button' value='Unblock email' id='button' onClick='unblock_email()'>"
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "Delete members with email address:",
		                                       "<input type='text' style='width:100%' id='textinput' name='deletemembers' value='Enter email here' onfocus='this.value=\"\"'>",
		                                       "<input type='button' value='Delete members' id='button' onClick='delete_members()'>"
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "Edit Member:",
		                                       "<input type='text' style='width:100%' id='textinput' name='username' value='Enter name here' onfocus='this.value=\"\"'>",
		                                       "<input type='button' value='Find Member' id='button' onClick='edit_member()'>"
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "Add New Category:",
		                                       "<input type='text' style='width:100%' name='cat_name' id='textinput' value='Category title here' onfocus='this.value=\"\"'>",
		                                       "<input type='button' value='Add Category' id='button' onClick='new_cat()'>"
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "Add New Forum:",
		                                       "<input type='text' style='width:100%' name='forum_name' id='textinput' value='Forum title here' onfocus='this.value=\"\"'>",
		                                       "<input type='button' value='Add Forum' id='button' onClick='new_forum()'>"
		                                  ));

		$ADMIN->html .= "</form>";

		$ADMIN->html .= $SKIN->end_table();

		//+-----------------------------------------------------------
		// ADMINS USING CP
		//+-----------------------------------------------------------

		$SKIN->td_header[] = array("Name", "20%");
		$SKIN->td_header[] = array("IP Address", "20%");
		$SKIN->td_header[] = array("Log In", "20%");
		$SKIN->td_header[] = array("Last Click", "20%");
		$SKIN->td_header[] = array("Location", "20%");

		$ADMIN->html .= $SKIN->start_table("Administrators using the CP");

		$t_time = time() - 60 * 10;

		$stmt = $ibforums->db->query("SELECT MEMBER_NAME, LOCATION, LOG_IN_TIME, RUNNING_TIME, IP_ADDRESS FROM ibf_admin_sessions WHERE RUNNING_TIME > $t_time and ip_address <> ''");

		$time_now = time();

		$seen_name = array();

		while ($r = $stmt->fetch())
		{
			if ($seen_name[$r['MEMBER_NAME']] == 1)
			{
				continue;
			} else
			{
				$seen_name[$r['MEMBER_NAME']] = 1;
			}

			$log_in = $time_now - $r['LOG_IN_TIME'];
			$click  = $time_now - $r['RUNNING_TIME'];

			if (($log_in / 60) < 1)
			{
				$log_in = sprintf("%0d", $log_in) . " seconds ago";
			} else
			{
				$log_in = sprintf("%0d", ($log_in / 60)) . " minutes ago";
			}

			if (($click / 60) < 1)
			{
				$click = sprintf("%0d", $click) . " seconds ago";
			} else
			{
				$click = sprintf("%0d", ($click / 60)) . " minutes ago";
			}

			$ADMIN->html .= $SKIN->add_td_row(array(

			                                       $r['MEMBER_NAME'],
			                                       "<center><a href='javascript:alert(\"Host Name: " . @gethostbyaddr($r['IP_ADDRESS']) . "\")' title='Get host name'>" . $r['IP_ADDRESS'] . "</a></center>",
			                                       "<center>" . $log_in . "</center>",
			                                       "<center>" . $click . "</center>",
			                                       "<center>" . $r['LOCATION'] . "</center>",

			                                  ));
		}

		$ADMIN->html .= $SKIN->end_table();

		//+-----------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_spacer();

		//+-----------------------------------------------------------

		$SKIN->td_header[] = array("&nbsp;", "100%");

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('act', 'mysql'),
		                                       2 => array('code', 'runsql'),
		                                  ));

		$ADMIN->html .= $SKIN->start_table("Run Query");

		$ADMIN->html .= $SKIN->add_td_row(array("<center>" . $SKIN->form_textarea("query", $sql) . "</center>"));

		$ADMIN->html .= $SKIN->end_form("Run a New Query");

		$ADMIN->html .= $SKIN->end_table();

		if ($MEMBER['mgroup'] == $INFO['admin_group'])
		{
			//+-----------------------------------------------------------
			// LAST 20 Admin Actions
			//+-----------------------------------------------------------

			$SKIN->td_header[] = array("Member Name", "20%");
			$SKIN->td_header[] = array("Action Performed", "40%");
			$SKIN->td_header[] = array("Time of action", "20%");
			$SKIN->td_header[] = array("IP address", "20%");

			$ADMIN->html .= $SKIN->start_table("Last 20 Admin Actions");

			$stmt = $ibforums->db->query("SELECT m.*, mem.id, mem.name FROM ibf_admin_logs m, ibf_members mem
						WHERE  m.member_id=mem.id ORDER BY m.ctime DESC LIMIT 20");

			if ($stmt->rowCount())
			{
				while ($rowb = $stmt->fetch())
				{
					$rowb['ctime'] = $ADMIN->get_date($rowb['ctime']);

					$ADMIN->html .= $SKIN->add_td_row(array(
					                                       "<b>{$rowb['name']}</b>",
					                                       "{$rowb['note']}",
					                                       "{$rowb['ctime']}",
					                                       "{$rowb['ip_address']}",
					                                  ));

				}
			} else
			{
				$ADMIN->html .= $SKIN->add_td_basic("<center>No results</center>");
			}

			$ADMIN->html .= $SKIN->end_table();

			//+-----------------------------------------------------------

			$ADMIN->html .= $SKIN->add_td_spacer();
		}

		//+-----------------------------------------------------------
		// Bots stuff
		//+-----------------------------------------------------------

		if ($INFO['spider_sense'])
		{
			$SKIN->td_header[] = array("Search Bot", "20%");
			$SKIN->td_header[] = array("Date", "25%");
			$SKIN->td_header[] = array("Query", "20%");
			$SKIN->td_header[] = array("Query", "35%");

			$ADMIN->html .= $SKIN->start_table("Last 10 Search Engine Spiders Hits");

			$stmt = $ibforums->db->query("SELECT * FROM ibf_spider_logs ORDER BY entry_date DESC LIMIT 0,10");

			while ($r = $stmt->fetch())
			{
				$ADMIN->html .= $SKIN->add_td_row(array(
				                                       "<strong>" . $INFO['sp_' . $r['bot']] . "</strong>",
				                                       $ADMIN->get_date($r['entry_date'], 'SHORT'),
				                                       $r['ip_address'] . '&nbsp;',
				                                       $r['query_string'] . '&nbsp;'
				                                  ));
			}

			$ADMIN->html .= $SKIN->end_table();

			$ADMIN->html .= $SKIN->add_td_spacer();
		}

		//+-----------------------------------------------------------

		$SKIN->td_header[] = array("Definition", "25%");
		$SKIN->td_header[] = array("Value", "25%");
		$SKIN->td_header[] = array("Definition", "25%");
		$SKIN->td_header[] = array("Value", "25%");

		$ADMIN->html .= $SKIN->start_table("System Overview");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "Total Unique Topics",
		                                       $row['TOTAL_TOPICS'],
		                                       "Total Replies to topics",
		                                       $row['TOTAL_REPLIES']
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "Total Members",
		                                       $row['MEM_COUNT'],
		                                       "Public Upload Folder Size",
		                                       $uploads_size
		                                  ));

		$ADMIN->html .= $SKIN->end_table();

		//+-----------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_spacer();

		//+-----------------------------------------------------------

		$ADMIN->output();

	}

}


