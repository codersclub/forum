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
|   > Admin Forum functions
|   > Module written by Matt Mecham
|   > Date started: 1st march 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new ad_forums();

$root_path = "";

class ad_forums
{

	var $base_url;
	var $modules = "";

	function ad_forums()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $ibforums;

		$this->img_url  = $INFO['html_url'] . '/sys-img';
		$this->base_url = $INFO['board_url'] . "/admin." . $INFO['php_ext'] . "?adsess=" . $IN['AD_SESS'];

		//--------------------------------------------
		// Get the sync module
		//--------------------------------------------

		if (USE_MODULES == 1)
		{
			require ROOT_PATH . "modules/ipb_member_sync.php";

			$this->modules = new ipb_member_sync();
		}

		//---------------------------------------
		// Kill globals - globals bad, Homer good.
		//---------------------------------------

		$tmp_in = array_merge($_GET, $_POST, $_COOKIE);

		foreach ($tmp_in as $k => $v)
		{
			unset($$k);
		}

		$ADMIN->nav[] = array('act=mem&code=edit', 'Edit Member Search Form');

		//---------------------------------------

		switch ($IN['code'])
		{
			case 'delete_members':
				$this->do_delete_members();
				break;
			case 'unblock':
				$this->do_block_email(0);
				break;
			case 'block':
				$this->do_block_email(1);
				break;
			case 'stepone':
				$this->do_advanced_search(1);
				break;
			case 'doform':
				$this->do_edit_form();
				break;
			case 'doedit':
				$this->do_edit();
				break;
			case 'advancedsearch':
				$this->do_advanced_search();
				break;
			//---------------------
			case 'unsuspend':
				$this->unsuspend();
				break;
			//---------------------
			case 'add':
				$this->add_form();
				break;
			case 'doadd':
				$this->do_add();
				break;
			//---------------------
			case 'del':
				$this->delete_form();
				break;
			case 'delete2':
				$this->delete_lookup_form();
				break;
			case 'dodelete':
				$this->dodelete();
				break;
			case 'prune':
				$this->prune_confirm();
				break;
			case 'doprune':
				$this->doprune();
				break;
			//---------------------
			case 'title':
				$this->titles();
				break;
			case 'rank_edit':
				$this->rank_setup('edit');
				break;
			case 'rank_add':
				$this->rank_setup('add');
				break;
			case 'do_add_rank':
				$this->add_rank();
				break;
			case 'do_rank_edit':
				$this->edit_rank();
				break;
			case 'rank_delete':
				$this->delete_rank();
				break;
			//---------------------
			case 'ban':
				$this->ban_control();
				break;
			case 'doban':
				$this->update_ban();
				break;
			//---------------------
			case 'mod':
				$this->view_mod();
				break;
			case 'domod':
				$this->domod();
				break;
			//---------------------
			case 'changename':
				$this->change_name_start();
				break;
			case 'dochangename':
				$this->change_name_complete();
				break;
			//---------------------
			case 'mail':
				$this->bulk_mail_form();
				break;
			case 'domail':
				$this->do_bulk_mail();
				break;

			case 'banmember':
				$this->temp_ban_start();
				break;

			case 'dobanmember':
				$this->temp_ban_complete();
				break;
			//---------------------
			default:
				$this->search_form();
				break;
		}

	}

	//+---------------------------------------------------------------------------------
	//
	// MASS EMAIL PEOPLE!
	//
	//+---------------------------------------------------------------------------------

	function do_bulk_mail()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		// Get the ID's of the groups we're emailing.

		$ids = array();

		foreach ($IN as $key => $value)
		{
			if (preg_match("/^sg_(\d+)$/", $key, $match))
			{
				if ($IN[$match[0]])
				{
					$ids[] = $match[1];
				}
			}
		}

		if (count($ids) < 1)
		{
			$this->bulk_mail_form(1, 'Errors', 'You must choose at least one group to send the email to');
			exit();
		}

		if ($IN['title'] == "")
		{
			$this->bulk_mail_form(1, 'Errors', 'You must set a subject for this email');
			exit();
		}

		if ($IN['email_contents'] == "")
		{
			$this->bulk_mail_form(1, 'Errors', 'You must include some text for the email body');
			exit();
		}

		$group_str = implode(",", $ids);

		// Sort out the rest of the DB stuff

		$where = ""; // Where? who knows? who cares?

		if ($IN['posts'] > 0)
		{
			$where .= " AND posts < " . $IN['posts'];
		}

		if ($IN['days'] > 0)
		{
			$time = time() - ($IN['days'] * 60 * 60 * 24);
			$where .= " AND last_activity < '$time'";
		}

		if ($IN["honour_user_setting"] == 1)
		{
			$where .= "AND allow_admin_mails=1";
		}

		//+---------------------------------------
		// Get a grip, er count
		//+---------------------------------------

		$stmt = $ibforums->db->query("SELECT COUNT(id) AS total
			    FROM ibf_members
			    WHERE mgroup IN($group_str)" . $where);

		$rows = $stmt->fetch();

		if ($rows['total'] < 1)
		{
			$this->bulk_mail_form(1, 'Errors', 'Please expand your criteria as no members could be found to email using the supplied information');
			exit();
		}

		//+---------------------------------------
		// Regex up stuff
		//+---------------------------------------

		$stmt  = $ibforums->db->query("SELECT * FROM ibf_stats");
		$stats = $stmt->fetch();

		$contents = $std->txt_stripslashes($_POST['email_contents']);

		$contents = str_replace("{board_name}", str_replace("&#39;", "'", $INFO['board_name']), $contents);
		$contents = str_replace("{board_url}", $INFO['board_url'] . "/index." . $INFO['php_ext'], $contents);
		$contents = str_replace("{reg_total}", $stats['MEM_COUNT'], $contents);
		$contents = str_replace("{total_posts}", $stats['TOTAL_TOPICS'] + $stats['TOTAL_REPLIES'], $contents);
		$contents = str_replace("{busy_count}", $stats['MOST_COUNT'], $contents);
		$contents = str_replace("{busy_time}", $std->get_date($stats['MOST_DATE'], 'SHORT'), $contents);

		//+---------------------------------------
		// Are we previewing? Why am I asking you?
		//+---------------------------------------

		if ($IN['preview'] != "")
		{
			$this->bulk_mail_form(1, 'Preview', "<b>" . $std->txt_stripslashes($_POST['title']) . "</b><br><br>" . $contents . "<br><br><b>Members to mail:</b> " . $rows['total']);
			exit();
		}

		//+---------------------------------------
		// We're still here? GROOVY, send da mail
		//+---------------------------------------

		@set_time_limit(1200);

		require_once ROOT_PATH . "/sources/lib/emailer.php";

		$this->email = new emailer();

		$this->email->bcc = array();

		$stmt = $ibforums->db->query("SELECT email
			    FROM ibf_members
			    WHERE mgroup IN($group_str)" . $where);

		while ($r = $stmt->fetch())
		{
			if ($r['email'] != "")
			{
				$this->email->bcc[] = $r['email'];
			}
		}

		$this->email->message = str_replace("\r\n", "\n", $contents);
		$this->email->subject = $std->txt_stripslashes($_POST['title']);

		if ($IN['email_admin'] == 1)
		{
			$this->email->to = $INFO['email_in'];
		} else
		{
			$this->email->to = "";
		}

		$this->email->send_mail();

		$ADMIN->save_log("Mass emailed members ($where)");

		$ADMIN->done_screen("Bulk Email sent", "Member Control", "act=mem");

	}

	function bulk_mail_form($preview = 0, $title = 'Preview', $content = "")
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$ADMIN->page_title = "Bulk Email Members";

		$ADMIN->page_detail = "You may bulk email your members by configuring the form below. Click the 'Quick Help' link for more information";

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'domail'),
		                                       2 => array('act', 'mem'),
		                                  ));

		if ($_POST['email_contents'] == "")
		{
			$_POST['email_contents'] = "\n\n\n-------------------------------------\n{board_name} Statistics:\n" . "-------------------------------------\nRegistered Users: {reg_total}\nTotal Posts: {total_posts}\n" . "Busiest Time: {busy_count} users were online on {busy_time}\n\n" . "-------------------------------------\nHandy Links\n" . "-------------------------------------\nBoard Address: {board_url}\nLog In: {board_url}?act=Login&CODE=00\n" . "Lost Password Recovery: {board_url}?act=Reg&CODE=10\n\n" . "-------------------------------------\nHow to unsubscribe\n" . "-------------------------------------\nVisit your email preferences ({board_url}?act=UserCP&CODE=02) and ensure " . "that the box for 'Send me any updates sent by the board administrator' is unchecked and submit the form";
		}

		if ($preview == 1)
		{
			$SKIN->td_header[] = array("&nbsp;", "100%");

			$ADMIN->html .= $SKIN->start_table($title);

			$ADMIN->html .= $SKIN->add_td_row(array(nl2br($content)));

			$ADMIN->html .= $SKIN->end_table();
		}

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Bulk Email Members: Content");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Honour 'Allow Admin Emails' user setting?</b><br>It is strongly recommended that you do!",
		                                       $SKIN->form_yes_no("honour_user_setting", isset($IN["honour_user_setting"])
			                                       ? $IN["honour_user_setting"]
			                                       : 1)
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Email Subject</b>",
		                                       $SKIN->form_input("title", $std->txt_stripslashes($_POST['title']))
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Email contents</b><br>" . $SKIN->js_help_link('m_bulkemail'),
		                                       $SKIN->form_textarea("email_contents", $std->txt_stripslashes($_POST['email_contents']), 60, 15)
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Send this email to the admin incoming address?</b><br>Enable this if you get a mail error upon submit - required when using SMTP.",
		                                       $SKIN->form_yes_no("email_admin", isset($IN['email_admin'])
			                                       ? $IN['email_admin']
			                                       : 1)
		                                  ));

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Bulk Email Members: Settings");

		$stmt = $ibforums->db->query("SELECT g_id, g_title
			    FROM ibf_groups
			    WHERE g_id <> " . $INFO['guest_group'] . "
			    ORDER BY g_title");

		while ($r = $stmt->fetch())
		{
			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Send to group <span style='color:red'>{$r['g_title']}</span>?</b>",
			                                       $SKIN->form_yes_no("sg_{$r['g_id']}", isset($IN['sg_' . $r['g_id']])
				                                       ? $IN['sg_' . $r['g_id']]
				                                       : 1)
			                                  ));
		}

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Where user has less than [x] posts</b><br>Leave blank to email regardless of post count",
		                                       $SKIN->form_input("post", $IN['post'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Where user has NOT been online for more than [x] days</b><br>Leave blank to email regardless of last visit",
		                                       $SKIN->form_input("days", $IN['days'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_basic('<input type="submit" name="preview" id="button" value="Preview">', 'center');

		$ADMIN->html .= $SKIN->end_form("Proceed");

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	//+---------------------------------------------------------------------------------
	//
	// TEMP BANNING
	//
	//+---------------------------------------------------------------------------------

	function temp_ban_start()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$ADMIN->page_title = "Account Suspension";

		$ADMIN->page_detail = "Automated temporary member suspension. Simply choose the duration of the suspension and submit the form below";

		$contents = "{membername},\nYour member account at {$INFO['board_name']} has been temporarily suspended.\n\nYour account will not be functional until {date_end} (depending on your timezone). This is an automated process and you do not need to do anything to expediate the unsuspension process.\n\nBoard Address: {$INFO['board_url']}/index.php";

		if ($IN['mid'] == "")
		{
			$ADMIN->error("You must specify a valid member id, please go back and try again");
		}

		$stmt = $ibforums->db->query("SELECT *
			    FROM ibf_members
			    WHERE id='" . $IN['mid'] . "'");

		if (!$member = $stmt->fetch())
		{
			$ADMIN->error("We could not match that ID in the members database");
		}

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'dobanmember'),
		                                       2 => array('act', 'mem'),
		                                       3 => array('mid', $IN['mid']),
		                                  ));

		$ban = $std->hdl_ban_line($member['temp_ban']);

		$units = array(0 => array('h', 'Hours'), 1 => array('d', 'Days'));

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Member Account Suspension", "Note: If this member is already suspended, any new setting will restart the ban");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<strong>Suspend {$member['name']} for...</strong>",
		                                       $SKIN->form_input('timespan', $ban['timespan'], "text", "", '5') . '&nbsp;' . $SKIN->form_dropdown('units', $units, $ban['units']),
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Email notification to this member?</b><br>(If so, you may edit the email below)",
		                                       $SKIN->form_yes_no("send_email", 0)
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Email contents</b><br>(Tags: {membername} = member's name, {date_end} = ban end)",
		                                       $SKIN->form_textarea("email_contents", $contents)
		                                  ), "", 'top');

		$ADMIN->html .= $SKIN->end_form("Suspend This Account");

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();
	}

	//---------------------------------------------------------------

	function unsuspend()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		if ($IN['mid'] == "")
		{
			$ADMIN->error("You must specify a valid member id, please go back and try again");
		}

		if ($IN['mid'] == 'all')
		{
			$ibforums->db->exec("UPDATE ibf_members
				    SET temp_ban=''");

			$ADMIN->save_log("Unsuspended all member accounts");

			$ADMIN->done_screen("All Accounts Unsuspended", "Member Control", "act=mem");
		} else
		{
			$mid = intval($IN['mid']);

			$ibforums->db->exec("UPDATE ibf_members
				    SET temp_ban=''
				    WHERE id=$mid");

			$stmt = $ibforums->db->query("SELECT name
				    FROM ibf_members
				    WHERE id=$mid");

			$member = $stmt->fetch();

			$ADMIN->save_log("Unsuspended {$member['name']}");

			$ADMIN->done_screen("{$member['name']} Unsuspended", "Member Control", "act=mem");
		}

	}

	//---------------------------------------------------------------

	function temp_ban_complete()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$ADMIN->page_title = "Account Suspension";

		$ADMIN->page_detail = "Automated temporary member suspension. Confirmation and information";

		$IN['mid'] = intval($IN['mid']);

		if ($IN['mid'] == "")
		{
			$ADMIN->error("You must specify a valid member id, please go back and try again");
		}

		$stmt = $ibforums->db->query("SELECT *
			    FROM ibf_members
			    WHERE id='" . $IN['mid'] . "'");

		if (!$member = $stmt->fetch())
		{
			$ADMIN->error("We could not match that ID in the members database");
		}

		//+-------------------------------
		// Work out end date
		//+-------------------------------

		$IN['timespan'] = intval($IN['timespan']);

		if ($IN['timespan'] == "")
		{
			$new_ban = "";
		} else
		{
			$new_ban = $std->hdl_ban_line(array('timespan' => intval($IN['timespan']), 'unit' => $IN['units']));
		}

		$show_ban = $std->hdl_ban_line($new_ban);

		//+-------------------------------
		// Update and show confirmation
		//+-------------------------------

		$ibforums->db->exec("UPDATE ibf_members
			    SET temp_ban='$new_ban'
			    WHERE id={$IN['mid']}");

		// I say, did we choose to email 'dis member?

		if ($IN['send_email'] == 1)
		{
			// By golly, we did!

			require_once ROOT_PATH . "/sources/lib/emailer.php";

			$this->email = new emailer();

			$msg = trim($std->txt_stripslashes($_POST['email_contents']));

			$msg = str_replace("{membername}", $member['name'], $msg);
			$msg = str_replace("{date_end}", $ADMIN->get_date($show_ban['date_end'], 'LONG'), $msg);

			$this->email->message = $this->email->clean_message($msg);
			$this->email->subject = "Account Suspension Notification";
			$this->email->to      = $member['email'];
			$this->email->send_mail();

			$skin_extra = $SKIN->add_td_row(array(
			                                     "<strong>Email Sent</strong>",
			                                     "<strong>Account Suspension Notification</strong><br /><br />" . str_replace("\n", "<br />", $msg)
			                                ), "", 'top');
		}

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Member Suspension: Result");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<strong>{$member['name']} is suspended until...</strong>",
		                                       $ADMIN->get_date($show_ban['date_end'], 'LONG'),
		                                  ));

		$ADMIN->html .= $skin_extra;

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->nav[] = array(
			"act=mem&code=stepone&USER_NAME={$member['name']}",
			"Admin Options for {$member['name']}"
		);

		$ADMIN->save_log("Suspended {$member['name']} until " . $ADMIN->get_date($show_ban['date_end'], 'SHORT'));

		$ADMIN->output();
	}

	//+---------------------------------------------------------------------------------
	//
	// CHANGE MEMBER NAME
	//
	//+---------------------------------------------------------------------------------

	function change_name_complete()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$IN['new_name'] = str_replace('|', '&#124;', $IN['new_name']);

		if ($IN['mid'] == "")
		{
			$ADMIN->error("You must specify a valid member id, please go back and try again");
		}

		if ($IN['new_name'] == "")
		{
			$this->change_name_start("You must enter a new name for this member");
			exit();
		}

		$stmt = $ibforums->db->query("SELECT name, email
			    FROM ibf_members
			    WHERE id='" . $IN['mid'] . "'");

		if (!$member = $stmt->fetch())
		{
			$ADMIN->error("We could not match that ID in the members database");
		}

		$mid = $IN['mid']; // Save me poor ol' carpels

		if ($IN['new_name'] == $member['name'])
		{
			$this->change_name_start("The new name is the same as the old name, that is illogical captain");
			exit();
		}

		// Check to ensure that his member name hasn't already been taken.

		$new_name = trim($IN['new_name']);

		$stmt = $ibforums->db->query("SELECT id
			    FROM ibf_members
			    WHERE LOWER(name)='" . strtolower($new_name) . "'");

		if ($stmt->rowCount())
		{
			$this->change_name_start("The name '$new_name' already exists, please choose another");
			exit();
		}

		// If one gets here, one can assume that the new name is correct for one, er...one.
		// So, lets do the converteroo

		$ibforums->db->exec("UPDATE ibf_members
			    SET name='$new_name'
			    WHERE id='$mid'");
		$ibforums->db->exec("UPDATE ibf_contacts
			    SET contact_name='$new_name'
			    WHERE contact_id='$mid'");
		$ibforums->db->exec("UPDATE ibf_forums
			    SET last_poster_name='$new_name'
			    WHERE last_poster_id='$mid'");
		$ibforums->db->exec("UPDATE ibf_moderator_logs
			    SET member_name='$new_name'
			    WHERE member_id='$mid'");
		$ibforums->db->exec("UPDATE ibf_moderators
			    SET member_name='$new_name'
			    WHERE member_id='$mid'");
		$ibforums->db->exec("UPDATE ibf_posts
			    SET author_name='$new_name'
			    WHERE author_id='$mid'");
		$ibforums->db->exec("UPDATE ibf_sessions
			    SET member_name='$new_name'
			    WHERE member_id='$mid'");
		$ibforums->db->exec("UPDATE ibf_topics
			    SET starter_name='$new_name'
			    WHERE starter_id='$mid'");
		$ibforums->db->exec("UPDATE ibf_topics
			    SET last_poster_name='$new_name'
			    WHERE last_poster_id='$mid'");

		// I say, did we choose to email 'dis member?

		if ($IN['send_email'] == 1)
		{
			// By golly, we did!

			require_once ROOT_PATH . "/sources/lib/emailer.php";

			$this->email = new emailer();

			$msg = trim($_POST['email_contents']);

			$msg = str_replace("{old_name}", $member['name'], $msg);
			$msg = str_replace("{new_name}", $new_name, $msg);

			$this->email->message = $this->email->clean_message($msg);
			$this->email->subject = "Member Name Change Notification";
			$this->email->to      = $member['email'];
			$this->email->send_mail();
		}

		$stmt                   = $ibforums->db->query("SELECT id, name
			    FROM ibf_members
			    WHERE mgroup <> '" . $INFO['auth_group'] . "'
			    ORDER BY id DESC
			    LIMIT 0,1");
		$r                      = $stmt->fetch();
		$stats['LAST_MEM_NAME'] = $r['name'];
		$stats['LAST_MEM_ID']   = $r['id'];

		$ibforums->db->updateRow("ibf_stats", array_map([$ibforums->db, 'quote'], $stats));

		$ADMIN->save_log("Changed Member Name '{$member['name']}' to '$new_name'");

		if (USE_MODULES == 1)
		{
			$this->modules->register_class($this);
			$this->modules->on_name_change($mid, $new_name);
		}

		$ADMIN->done_screen("Member Name Changed", "Member Control", "act=mem");
	}

	//===========================================================================

	function change_name_start($message = "")
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$ADMIN->page_title = "Change Member Name";

		$ADMIN->page_detail = "You may enter a new name for this member.";

		if ($IN['mid'] == "")
		{
			$ADMIN->error("You must specify a valid member id, please go back and try again");
		}

		$stmt = $ibforums->db->query("SELECT name
			    FROM ibf_members
			    WHERE id='" . $IN['mid'] . "'");

		if (!$member = $stmt->fetch())
		{
			$ADMIN->error("We could not match that ID in the members database");
		}

		$contents = "{old_name},\nAn administrator has changed your member name on {$INFO['board_name']}.\n\nYour new name is: {new_name}\n\nPlease remember this as you will need to use this new name when you log in next time.\nBoard Address: {$INFO['board_url']}/index.php";

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'dochangename'),
		                                       2 => array('act', 'mem'),
		                                       3 => array('mid', $IN['mid']),
		                                  ));

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Change Member Name");

		if ($message != "")
		{
			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Error Message:</b>",
			                                       "<b><span style='color:red'>$message</span></b>",
			                                  ));
		}

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Current Member's Name</b>",
		                                       $member['name'],
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>New Members Name</b>",
		                                       $SKIN->form_input("new_name", $IN['new_name'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Email notification to this member?</b><br>(If so, you may edit the email below)",
		                                       $SKIN->form_yes_no("send_email", 1)
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Email contents</b><br>(Tags: {old_name} = current name, {new_name} = new name)",
		                                       $SKIN->form_textarea("email_contents", $contents)
		                                  ));

		$ADMIN->html .= $SKIN->end_form("Change this members name");

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();
	}

	//+---------------------------------------------------------------------------------

	//+---------------------------------------------------------------------------------
	//
	// Moderation control...
	//
	//+---------------------------------------------------------------------------------

	function domod()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$ids = array();

		foreach ($IN as $k => $v)
		{
			if (preg_match("/^mid_(\d+)$/", $k, $match))
			{
				if ($IN[$match[0]])
				{
					$ids[] = $match[1];
				}
			}
		}

		//-------------------

		if (count($ids) < 1)
		{
			$ADMIN->error("You did not select any members to approve or delete");
		}

		//-------------------

		if ($IN['type'] == 'approve')
		{

			//-------------------------------------------

			require_once ROOT_PATH . "sources/lib/emailer.php";

			$email = new emailer();

			$email->get_template("complete_reg");

			$email->build_message("");

			$email->subject = "Account validated at " . $INFO['board_name'];

			//-------------------------------------------

			$stmt = $ibforums->db->query("SELECT m.id, m.email, m.mgroup, v.*
				     FROM ibf_validating v
					 LEFT JOIN ibf_members m ON (v.member_id=m.id)
				     WHERE m.id IN(" . implode(",", $ids) . ")");

			while ($row = $stmt->fetch())
			{
				if ($row['mgroup'] != $INFO['auth_group'])
				{
					continue;
				}

				if ($row['real_group'] == "")
				{
					$row['real_group'] = $INFO['member_group'];
				}

				$update = $ibforums->db->exec("UPDATE ibf_members
						      SET mgroup='" . $row['real_group'] . "',
						          old_group='" . $row['real_group'] . "'
						      WHERE id='" . $row['id'] . "'");

				$email->to = $row['email'];

				$email->send_mail();
			}

			$ibforums->db->exec("DELETE FROM ibf_validating
				    WHERE member_id IN(" . implode(",", $ids) . ")");

			$stmt = $ibforums->db->query("SELECT id, name
				    FROM ibf_members
				    WHERE mgroup <> " . $INFO['auth_group'] . "
				    ORDER BY id DESC
				    LIMIT 0,1");
			$r    = $stmt->fetch();

			$ibforums->db->exec("UPDATE ibf_stats
				    SET MEM_COUNT=MEM_COUNT+" . count($ids) . ",
					LAST_MEM_NAME='{$r['name']}',
					LAST_MEM_ID='{$r['id']}'");

			$ADMIN->save_log("Approved Queued Registrations");

			$ADMIN->done_screen(count($ids) . " Members Approved", "Manage Registrations", "act=mem&code=mod");

		} else
		{
			$ibforums->db->exec("DELETE FROM ibf_members
				    WHERE id IN(" . implode(",", $ids) . ")");

			$ibforums->db->exec("DELETE FROM ibf_member_extra
				    WHERE id IN(" . implode(",", $ids) . ")");

			// Delete member messages...

			$ibforums->db->exec("DELETE FROM ibf_messages
                                    WHERE member_id IN(" . implode(",", $ids) . ")");
			$ibforums->db->exec("DELETE FROM ibf_contacts
                                    WHERE member_id IN(" . implode(",", $ids) . ")
                                       OR contact_id IN(" . implode(",", $ids) . ")");

			$ibforums->db->exec("DELETE FROM ibf_validating
                                    WHERE member_id IN(" . implode(",", $ids) . ")");

			$ibforums->db->exec("DELETE FROM ibf_pfields_content
                                    WHERE member_id IN(" . implode(",", $ids) . ")");

			$ibforums->db->exec("DELETE FROM ibf_warn_logs
                                    WHERE wlog_mid IN(" . implode(",", $ids) . ")");

			// Convert their posts and topics into guest postings..

			$ibforums->db->exec("UPDATE ibf_posts
                                    SET author_id='0'
                                    WHERE author_id IN(" . implode(",", $ids) . ")");

			$ibforums->db->exec("UPDATE ibf_topics
                                    SET starter_id='0'
                                    WHERE starter_id IN(" . implode(",", $ids) . ")");

			if (USE_MODULES == 1)
			{
				$this->modules->register_class($this);
				$this->modules->on_delete($ids);
			}

			$ADMIN->save_log("Denied Queued Registrations");

			$ADMIN->done_screen(count($ids) . " Members Removed", "Manage Registrations", "act=mem&code=mod");
		}

	}

	//---------------------------------------------

	function view_mod()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$ADMIN->page_title = "Manage User Registration/Email Change Queues";

		$ADMIN->page_detail = "This section allows you to allow or deny registrations where you have requested that an administrator previews new accounts before allowing full membership. It will also allow you to complete or deny new email address changes.<br><br>This form will also allow you to complete the registrations for those who did not receive an email.";

		$stmt = $ibforums->db->query("SELECT COUNT(vid) AS mcount
			    FROM ibf_validating
			    WHERE validate_type<>'lost_pass'");

		$row = $stmt->fetch();

		$cnt = $row['mcount'] < 1
			? 0
			: $row['mcount'];

		$st = intval($IN['st']);

		$ord = $IN['ord'] == 'asc'
			? 'asc'
			: 'desc';

		$new_ord = $ord == 'asc'
			? 'desc'
			: 'asc';

		switch ($IN['sort'])
		{
			case 'mem':
				$col = 'm.name';
				break;
			case 'email':
				$col = 'm.email';
				break;
			case 'sent':
				$col = 'v.entry_date';
				break;
			case 'posts':
				$col = 'm.posts';
				break;
			case 'reg':
				$col = 'm.joined';
				break;
			default:
				$col = 'v.entry_date';
				break;
		}

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'domod'),
		                                       2 => array('act', 'mem'),
		                                  ));

		$SKIN->td_header[] = array(
			"<a href='{$SKIN->base_url}&act=mem&code=mod&st=$st&sort=mem&ord=$new_ord'>Member Name</a>",
			"20%"
		);
		$SKIN->td_header[] = array("Where?", "20%");
		$SKIN->td_header[] = array(
			"<a href='{$SKIN->base_url}&act=mem&code=mod&st=$st&sort=email&ord=$new_ord'>Email Address</a>",
			"15%"
		);
		$SKIN->td_header[] = array(
			"<a href='{$SKIN->base_url}&act=mem&code=mod&st=$st&sort=sent&ord=$new_ord'>Email Sent</a>",
			"10%"
		);
		$SKIN->td_header[] = array(
			"<a href='{$SKIN->base_url}&act=mem&code=mod&st=$st&sort=posts&ord=$new_ord'>Posts</a>",
			"10%"
		);
		$SKIN->td_header[] = array(
			"<a href='{$SKIN->base_url}&act=mem&code=mod&st=$st&sort=reg&ord=$new_ord'>Reg. On</a>",
			"10%"
		);
		$SKIN->td_header[] = array("Age", "10%");
		$SKIN->td_header[] = array("&nbsp;", "5%");

		$ADMIN->html .= $SKIN->start_table("Users awaiting authorisation");

		$links = $std->build_pagelinks(array(
		                                    'TOTAL_POSS' => $cnt,
		                                    'PER_PAGE'   => 75,
		                                    'CUR_ST_VAL' => $st,
		                                    'L_SINGLE'   => "Single Page",
		                                    'L_MULTI'    => "Multiple Pages",
		                                    'BASE_URL'   => $SKIN->base_url . "&act=mem&code=mod",
		                               ));

		$ADMIN->html .= $SKIN->add_td_basic("<b>$cnt users require registration or email change validation</b>", "center", "catrow2");

		if ($cnt > 0)
		{
			$stmt = $ibforums->db->query("SELECT m.name, m.id, m.email, m.posts, m.joined, v.*
				    FROM ibf_validating v
				    LEFT JOIN ibf_members m ON (v.member_id=m.id)
				    WHERE v.validate_type<>'lost_pass'
				    ORDER BY $col $ord
				    LIMIT $st,75");

			while ($r = $stmt->fetch())
			{

				if ($r['coppa_user'] == 1)
				{
					$coppa = ' ( COPPA Request )';
				} else
				{
					$coppa = "";
				}

				$where = ($r['lost_pass']
					? 'Lost Password'
					: ($r['new_reg']
						? "Registering"
						: ($r['email_chg']
							? "Email Change"
							: 'N/A')));

				//$age = floor( ( time() - $r['entry_date'] ) / 86400 );

				$hours = floor((time() - $r['entry_date']) / 3600);

				$days = intval($hours / 24);

				$rhours = intval($hours - ($days * 24));

				if ($r['name'] == "")
				{
					$r['name'] = "<em>Deleted Member</em>";
				}

				$ADMIN->html .= $SKIN->add_td_row(array(
				                                       "<b>" . $r['name'] . "</b>$coppa",
				                                       "<center>$where</center>",
				                                       $r['email'],
				                                       "<center>" . $std->get_date($r['entry_date'], 'JOINED') . "</center>",
				                                       "<center>{$r['posts']}</center>",
				                                       "<center>" . $std->get_date($r['joined'], 'JOINED') . "</center>",
				                                       "<center><strong><span style='color:red'>$days d</span>, $rhours h</center>",
				                                       "<center><input type='checkbox' name='mid_{$r['member_id']}' value='1'></center>"
				                                  ));
			}
			$ADMIN->html .= $SKIN->add_td_basic("$links", "left", "catrow2");

			$ADMIN->html .= $SKIN->add_td_basic("<select name='type' id='dropdown'><option value='approve'>Approve these Accounts</option><option value='delete'>DELETE these accounts</option></select>", "center", "catrow2");

		}

		$ADMIN->html .= $SKIN->end_form("Go!");

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	//+---------------------------------------------------------------------------------
	//
	// Ban control...
	//
	//+---------------------------------------------------------------------------------

	function ban_control()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$ADMIN->page_title = "Ban Control";

		$ADMIN->page_detail = "This section allows you to modify, delete or add IP addresses, email addresses and reserved names to the ban filters.";

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'doban'),
		                                       2 => array('act', 'mem'),
		                                  ));

		$ip_list    = "";
		$name_list  = "";
		$email_list = "";

		if ($INFO['ban_ip'] != "")
		{
			$ip_list = preg_replace("/\|/", "\n", $INFO['ban_ip']);
		}

		//+-------------------------------

		if ($INFO['ban_email'] != "")
		{
			$email_list = preg_replace("/\|/", "\n", $INFO['ban_email']);
		}

		//+-------------------------------

		if ($INFO['ban_names'] != "")
		{
			$name_list = preg_replace("/\|/", "\n", $INFO['ban_names']);
		}

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Ban Control");

		$ADMIN->html .= $SKIN->add_td_basic("Banned IP Addresses (one per line - use * as a wildcard)", "center", "pformstrip");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Banned IP Address</b><br>(Example: 212.45.45.23)<br>(Example: 212.45.45.*)",
		                                       $SKIN->form_textarea('ban_ip', $ip_list, 60, 40)
		                                  ));

		$ADMIN->html .= $SKIN->add_td_basic("Banned Email Addresses (one per line - use * as a wildcard)", "center", "pformstrip");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Banned Email Address</b><br>(Example: name@domain.com)<br>(Example: *@domain.com)",
		                                       $SKIN->form_textarea('ban_email', $email_list, 60, 20)
		                                  ));

		$ADMIN->html .= $SKIN->add_td_basic("Banned / Reserved Names (one per line)", "center", "pformstrip");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Banned Names</b>",
		                                       $SKIN->form_textarea('ban_names', $name_list, 60, 20)
		                                  ));

		$ADMIN->html .= $SKIN->end_form("Update the ban filters");

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	function update_ban()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		// Get the incoming..

		$new = array();

		$new['ban_ip']    = $this->_do_banline($_POST['ban_ip']);
		$new['ban_email'] = $this->_do_banline($_POST['ban_email']);
		$new['ban_names'] = $this->_do_banline($_POST['ban_names']);

		$ADMIN->rebuild_config($new);

		$ADMIN->save_log("Updated Ban Filters");

		$ADMIN->done_screen("Ban Filters Updated", "Ban Control", "act=mem&code=ban");

	}

	function _do_banline($raw)
	{
		global $std;
		$ibforums = Ibf::instance();

		$ban = trim($std->txt_stripslashes($raw));

		$ban = str_replace('|', "&#124;", $ban);

		$ban = preg_replace("/\n/", '|', str_replace("\n\n", "\n", str_replace("\r", "\n", $ban)));

		$ban = preg_replace("/\|{1,}\s{1,}?/s", "|", $ban);

		$ban = preg_replace("/^\|/", "", $ban);

		$ban = preg_replace("/\|$/", "", $ban);

		$ban = str_replace("'", '&#39;', $ban);

		return $ban;
	}

	//+---------------------------------------------------------------------------------
	//
	// MEMBER RANKS...
	//
	//+---------------------------------------------------------------------------------

	function titles()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$ADMIN->page_title = "Member Ranking Set Up";

		$ADMIN->page_detail = "This section allows you to modify, delete or add extra ranks.<br>If you wish to display pips below the members name, enter the number of pips. If you wish to use a custom image, simply enter the image name in the pips box. Note, these custom images must reside in the 'html/team_icons' directory of your installation";

		//+-------------------------------

		$SKIN->td_header[] = array("Title", "23%");
		$SKIN->td_header[] = array("Min Posts", "7%");
		$SKIN->td_header[] = array("Max PMs per hour", "10%");
		$SKIN->td_header[] = array("Pips", "20%");
		$SKIN->td_header[] = array("&nbsp;", "20%");
		$SKIN->td_header[] = array("&nbsp;", "20%");

		//+-------------------------------

		$stmt = $ibforums->db->query("SELECT macro_id, img_dir
			    FROM ibf_skins
			    WHERE default_set=1");

		$mid = $stmt->fetch();

		$stmt = $ibforums->db->query("SELECT macro_replace AS A_STAR
			    FROM ibf_macro
			    WHERE macro_set={$mid['macro_id']}
				AND macro_value='A_STAR'");

		$row = $stmt->fetch();

		//vot    	$row['A_STAR'] = str_replace( "<#IMG_DIR#>", $mid['img_dir'], $row['A_STAR'] );
		$row['A_STAR'] = str_replace("<#IMG_DIR#>", $SKIN->img_url, $row['A_STAR']); //vot

		$ADMIN->html .= $SKIN->start_table("Member Titles/Ranks");

		$stmt = $ibforums->db->query("SELECT *
			    FROM ibf_titles
			    ORDER BY posts");

		while ($r = $stmt->fetch())
		{
			$img = "";

			if (preg_match("/^\d+$/", $r['pips']))
			{
				for ($i = 1; $i <= $r['pips']; $i++)
				{
					$img .= $row['A_STAR'];

				}
			} else
			{
				$img = "<img src='html/team_icons/{$r['pips']}' border='0'>";
			}

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>" . $r['title'] . "</b>",
			                                       $r['posts'],
			                                       intval($r['max_pms_per_hour']),
			                                       $img,
			                                       "<a href='{$SKIN->base_url}&act=mem&code=rank_edit&id={$r['id']}'>Edit</a>",
			                                       "<a href='{$SKIN->base_url}&act=mem&code=rank_delete&id={$r['id']}'>Delete</a>",
			                                  ));

		}

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'do_add_rank'),
		                                       2 => array('act', 'mem'),
		                                  ));

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Add a Member Rank");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Rank Title</b>",
		                                       $SKIN->form_input("title")
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Minimum number of posts needed</b>",
		                                       $SKIN->form_input("posts")
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Number of pips</b><br>(Or pip image)",
		                                       $SKIN->form_input("pips")
		                                  ));

		$ADMIN->html .= $SKIN->end_form("Add this rank");

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();
	}

	//+---------------------------------------------------------------------------------

	function add_rank()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		//+-------------------------------
		// check for input
		//+-------------------------------

		foreach (array('posts', 'title', 'pips') as $field)
		{
			if ($IN[$field] == "")
			{
				$ADMIN->error("You must complete the form fully");
			}
		}

		//+-------------------------------
		// Add it to the DB
		//+-------------------------------

		$data = [
			'posts' => intval(trim($IN['posts'])),
			'title' => trim($IN['title']),
			'pips'  => trim($IN['pips']),
		];

		$ibforums->db->insertRow("ibf_titles", $data);

		$ADMIN->done_screen("Rank Added", "Member Ranking Control", "act=mem&code=title");

	}

	//+---------------------------------------------------------------------------------

	function delete_rank()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		//+-------------------------------
		// check for input
		//+-------------------------------

		if ($IN['id'] == "")
		{
			$ADMIN->error("We could not match that ID");
		}

		$ibforums->db->exec("DELETE FROM ibf_titles
			    WHERE id='" . $IN['id'] . "'");

		$ADMIN->save_log("Removed Rank Setting");

		$ADMIN->done_screen("Rank Removed", "Member Ranking Control", "act=mem&code=title");

	}

	//+---------------------------------------------------------------------------------

	function edit_rank()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		//+-------------------------------
		// check for input
		//+-------------------------------

		if ($IN['id'] == "")
		{
			$ADMIN->error("We could not match that ID");
		}

		//+-------------------------------

		foreach (array('posts', 'title', 'pips') as $field)
		{
			if ($IN[$field] == "")
			{
				$ADMIN->error("You must complete the form fully");
			}
		}

		//+-------------------------------
		// Add it to the DB
		//+-------------------------------

		$data = [
			'posts'            => intval(trim($IN['posts'])),
			'title'            => $ibforums->db->quote(trim($IN['title'])),
			'pips'             => $ibforums->db->quote(trim($IN['pips'])),
			'max_pms_per_hour' => intval(trim($IN['max_pms_per_hour'])),
		];

		$ibforums->db->updateRow("ibf_titles", $data, "id='" . $IN['id'] . "'");

		$ADMIN->save_log("Edited Rank Setting");

		$ADMIN->done_screen("Rank Edited", "Member Ranking Control", "act=mem&code=title");

	}

	//+---------------------------------------------------------------------------------

	function rank_setup($mode = 'edit')
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$ADMIN->page_title = "Member Rank Set Up";

		$ADMIN->page_detail = "If you wish to display pips below the members name, enter the number of pips. If you wish to use a custom image, simply enter the image name in the pips box. Note, these custom images must reside in the 'html/team_icons' directory of your installation";

		if ($mode == 'edit')
		{
			$form_code = 'do_rank_edit';

			if ($IN['id'] == "")
			{
				$ADMIN->error("No rank ID was set, please try again");
			}

			$stmt = $ibforums->db->query("SELECT *
				    FROM ibf_titles
				    WHERE id='" . $IN['id'] . "'");
			$rank = $stmt->fetch();

			$button = "Complete Edit";
		} else
		{
			$form_code = 'do_add_rank';
			$rank      = array('posts' => "", 'title' => "", 'pips' => "");
			$button    = "Add this rank";
		}

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', $form_code),
		                                       2 => array('act', 'mem'),
		                                       3 => array('id', $rank['id']),
		                                  ));

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Member Ranks");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Rank Title</b>",
		                                       $SKIN->form_input("title", $rank['title'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Minimum number of posts needed</b>",
		                                       $SKIN->form_input("posts", $rank['posts'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Number of pips</b><br>(Or pip image)",
		                                       $SKIN->form_input("pips", $rank['pips'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Count of PMs per hour</b><br>(Or pip image)",
		                                       $SKIN->form_input("max_pms_per_hour", $rank['max_pms_per_hour'])
		                                  ));

		$ADMIN->html .= $SKIN->end_form($button);

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	//+---------------------------------------------------------------------------------

	//+---------------------------------------------------------------------------------
	//
	// DELETE MEMBER SET UP
	//
	//+---------------------------------------------------------------------------------

	function delete_form()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$ADMIN->page_title = "Member Account Deletion";

		$ADMIN->page_detail = "Search for a member to delete by enter part or all of the username, or configure the prune form.";

		$mem_group[0] = array('0', 'Any member group');

		$stmt = $ibforums->db->query("SELECT g_id, g_title
			    FROM ibf_groups
			    ORDER BY g_title");

		while ($r = $stmt->fetch())
		{
			$mem_group[] = array($r['g_id'], $r['g_title']);
		}

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'delete2'),
		                                       2 => array('act', 'mem'),
		                                  ));

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Member Lookup");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Enter part or all of the usersname</b>",
		                                       $SKIN->form_input("USER_NAME")
		                                  ));

		$ADMIN->html .= $SKIN->end_form("Find Member Account");

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------
		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'prune'),
		                                       2 => array('act', 'mem'),
		                                  ));

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("<u>or</u> remove members where...");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>The members last visit was over [x] days ago.</b><br>([x] = number entered)<br>(Leave blank to omit from query)",
		                                       $SKIN->form_input("last_visit", '60')
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>The members last post was over [x] days ago.</b><br>([x] = number entered)<br>(Leave blank to omit from query)",
		                                       $SKIN->form_input("last_post", '60')
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b><u>and</u> where the member has less than [x] posts</b><br>([x] = number entered)<br>(Leave blank to omit from query)",
		                                       $SKIN->form_input("posts", '100')
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b><u>and</u> where the member joined [x] days ago</b><br>([x] = number entered)<br>(Leave blank to omit from query)",
		                                       $SKIN->form_input("joined", '365')
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b><u>and</u> the member group is...</b>",
		                                       $SKIN->form_dropdown("mgroup", $mem_group, 0)
		                                  ));

		$ADMIN->html .= $SKIN->end_form("Prune members");

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	//+---------------------------------------------------------------------------------

	function prune_confirm()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		//-----------------------------
		// Make sure we have *something*
		//------------------------------

		$blanks = 0;

		foreach (array('posts', 'last_post', 'joined') as $field)
		{
			if ($IN[$field] == "")
			{
				$blanks++;
			}
		}

		if ($blanks == 4)
		{
			$ADMIN->error("You must specify at least one field to use in the pruning query");
		}

		$time_now = time();

		$query = "SELECT COUNT(id) as mcount FROM ibf_members WHERE";

		$add_query = array();

		if ($IN['joined'] > 0)
		{
			$j           = $time_now - ($IN['joined'] * 60 * 60 * 24);
			$add_query[] = " joined < $j ";
		}

		if ($IN['last_visit'] > 0)
		{
			$lv          = $time_now - ($IN['last_visit'] * 60 * 60 * 24);
			$add_query[] = " last_visit < $lv ";
		}

		if ($IN['last_post'] > 0)
		{
			$l           = $time_now - ($IN['last_post'] * 60 * 60 * 24);
			$add_query[] = " last_post < $l ";
		}

		if ($IN['posts'] > 0)
		{
			$add_query[] = " posts < " . $IN['posts'] . " ";
		}

		if ($IN['mgroup'] > 0)
		{
			$add_query[] = " mgroup='" . $IN['mgroup'] . "' ";
		}

		$add_query[] = ' id > 0';

		$additional_query = implode("AND", $add_query);

		$this_query = trim($query . $additional_query);

		$pass_query = addslashes(urlencode($additional_query));

		//--------------------------------
		// Run the query
		//--------------------------------

		$stmt = $ibforums->db->query($this_query);

		$count = $stmt->fetch();

		if ($count['mcount'] < 1)
		{
			$ADMIN->error("We did not find any members matching the prune criteria. Please go back and try again");
		}

		if ($count['mcount'] < 101)
		{
			$stmt = $ibforums->db->query("SELECT id, name
				    FROM ibf_members
				    WHERE $additional_query");

			$member_arr = array();

			while ($mem = $stmt->fetch())
			{
				$member_arr[] = $std->make_profile_link($mem['name'], $mem['id']);
			}
		}

		$ADMIN->page_title = "Member Pruning";

		$ADMIN->page_detail = "Please confirm your action.";

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'doprune'),
		                                       2 => array('act', 'mem'),
		                                       3 => array('query', $pass_query),
		                                  ));

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Member Prune Confirmation");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Number of members to prune</b>",
		                                       $count['mcount']
		                                  ));

		if (count($member_arr) > 0)
		{
			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Members to prune</b>",
			                                       implode(', ', $member_arr)
			                                  ));
		}

		$ADMIN->html .= $SKIN->end_form("Complete Member Pruning");

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	//+---------------------------------------------------------------------------------

	function doprune()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		//-----------------------------
		// Make sure we have *something*
		//------------------------------

		$query = trim(urldecode($std->txt_stripslashes($IN['query'])));

		$query = str_replace(array("&lt;", "&gt;", "&quot;"), array("<", ">", "'"), $query);

		if (!$query)
		{
			$ADMIN->error("Prune query error, no query to use");
		}

		//-----------------------------
		// Get the member ids...
		//------------------------------

		$ids = array();

		$stmt = $ibforums->db->query("SELECT id
			    FROM ibf_members
			    WHERE " . $query);

		if ($stmt->rowCount())
		{
			while ($i = $stmt->fetch())
			{
				$ids[] = $i['id'];
			}

		} else
		{
			$ADMIN->error("Could not find any members that matched the prune criteria");
		}

		$id_string = implode(",", $ids);

		$id_count = count($ids);

		// Song * delete members

		$std->delete_members(" IN(" . $id_string . ")");

		// Song * delete members

		// Blow me melon farmer

		if ($IN['song'])
		{
			$query = str_replace(array("'", "email="), array("", ""), $query);

			$ADMIN->save_log("Delete members with email address $query");
		} else
		{
			$ADMIN->save_log("Removed $id_count members via the prune form");
		}

		if (USE_MODULES == 1)
		{
			$this->modules->register_class($this);
			$this->modules->on_delete($ids);
		}

		$ADMIN->done_screen("Member Account(s) Deleted", "Member Control", "act=mem&code=edit");
	}

	//+---------------------------------------------------------------------------------

	function delete_lookup_form()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		if ($IN['USER_NAME'] == "")
		{
			$ADMIN->error("You didn't choose a member name to look for!");
		}

		$stmt = $ibforums->db->query("SELECT id, name
			    FROM ibf_members
			    WHERE name LIKE '" . $IN['USER_NAME'] . "%'");

		if (!$stmt->rowCount())
		{
			$ADMIN->error("Sorry, we could not find any members that matched the search string you entered");
		}

		$form_array = array();

		while ($r = $stmt->fetch())
		{
			$form_array[] = array($r['id'], $r['name']);
		}

		$ADMIN->page_title = "Delete a member";

		$ADMIN->page_detail = "Please choose which member to delete.";

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'dodelete'),
		                                       2 => array('act', 'mem'),
		                                  ));

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Member Lookup results");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Choose from the matches...</b>",
		                                       $SKIN->form_dropdown("MEMBER_ID", $form_array)
		                                  ));

		$ADMIN->html .= $SKIN->end_form("Delete Member");

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();
	}

	//+---------------------------------------------------------------------------------

	//+---------------------------------------------------------------------------------
	//
	// DO DELETE
	//
	//+---------------------------------------------------------------------------------

	function dodelete()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		if ($IN['MEMBER_ID'] == "")
		{
			$ADMIN->error("Could not resolve member id");
		}

		//+-------------------------------

		$stmt = $ibforums->db->query("SELECT *
			    FROM ibf_members
			    WHERE id='" . $IN['MEMBER_ID'] . "'");
		$mem  = $stmt->fetch();

		//+-------------------------------

		if ($mem['id'] == "")
		{
			$ADMIN->error("Could not resolve member id");
		}

		// Song * delete members

		$std->delete_members("='" . $IN['MEMBER_ID'] . "'");

		// Song * delete members

		// Blow me melon farmer

		$ADMIN->save_log("Deleted Member '{$mem['name']}'");

		if (USE_MODULES == 1)
		{
			$this->modules->register_class($this);
			$this->modules->on_delete($IN['MEMBER_ID']);
		}

		$ADMIN->done_screen("Member Account Deleted", "Member Control", "act=mem&code=edit");

	}

	//+---------------------------------------------------------------------------------
	//
	// ADD MEMBER FORM
	//
	//+---------------------------------------------------------------------------------

	function add_form()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$ADMIN->page_title = "Pre Register a member";

		$ADMIN->page_detail = "You may pre-register members using this form.";

		$stmt = $ibforums->db->query("SELECT g_id, g_title
                            FROM ibf_groups
                            ORDER BY g_title");

		while ($r = $stmt->fetch())
		{
			if ($INFO['admin_group'] == $r['g_id'])
			{
				if ($MEMBER['mgroup'] != $INFO['admin_group'])
				{
					continue;
				}
			}
			$mem_group[] = array($r['g_id'], $r['g_title']);
		}

		//+-------------------------------

		$custom_output = "";
		$field_data    = array();

		$stmt = $ibforums->db->query("SELECT *
			    FROM ibf_pfields_content
                            WHERE member_id='" . $IN['MEMBER_ID'] . "'");

		while ($content = $stmt->fetch())
		{
			foreach ($content as $k => $v)
			{
				if (preg_match("/^field_(\d+)$/", $k, $match))
				{
					// Temporarily DISABLE Old Gender (field_1/6)
					//if(!($k=='field_1' || $k=='field_6')) {
					$field_data[$match[1]] = $v;
					//}
				}
			}
		}

		$stmt = $ibforums->db->query("SELECT *
			    FROM ibf_pfields_data
                            WHERE fshowreg=1
                            ORDER BY forder");

		while ($row = $stmt->fetch())
		{
			$form_element = "";

			if ($row['ftype'] == 'drop')
			{
				$carray = explode('|', trim($row['fcontent']));

				$d_content = array();

				foreach ($carray as $entry)
				{
					$value = explode('=', $entry);

					$ov = trim($value[0]);
					$td = trim($value[1]);

					if ($ov and $td)
					{
						$d_content[] = array($ov, $td);
					}
				}

				$form_element = $SKIN->form_dropdown('field_' . $row['fid'], $d_content, "");

			} else
			{
				if ($row['ftype'] == 'area')
				{
					$form_element = $SKIN->form_textarea('field_' . $row['fid'], "");
				} else
				{
					$form_element = $SKIN->form_input('field_' . $row['fid'], "");
				}
			}

			$custom_out .= $SKIN->add_td_row(array("<b>{$row['ftitle']}</b><br>{$row['desc']}", $form_element));

		}

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'doadd'),
		                                       2 => array('act', 'mem'),
		                                  ));

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Member Registration");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Member Name</b>",
		                                       $SKIN->form_input("name")
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Password</b>",
		                                       $SKIN->form_input("password", "", 'password')
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Email Address</b>",
		                                       $SKIN->form_input("email")
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Member Group</b>",
		                                       $SKIN->form_dropdown("mgroup", $mem_group, $mem['mgroup'])
		                                  ));

		if ($custom_out != "")
		{
			$ADMIN->html .= $custom_out;
		}

		$ADMIN->html .= $SKIN->end_form("Register Member");

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	function do_add()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		foreach (array('name', 'password', 'email', 'mgroup') as $field)
		{
			if ($IN[$field] == "")
			{
				$ADMIN->error("You must complete the form fully!");
			}
		}

		//----------------------------------
		// Do we already have such a member?
		//----------------------------------

		$stmt = $ibforums->db->query("SELECT id
			    FROM ibf_members
			    WHERE LOWER(name)='" . $IN['name'] . "'");

		if ($stmt->rowCount())
		{
			$ADMIN->error("We already have a member by that name, please select another");
		}

		//----------------------------------
		// Custom profile field stuff
		//----------------------------------

		$custom_fields = array();

		$stmt = $ibforums->db->query("SELECT * FROM ibf_pfields_data");

		$have_custom = $stmt->rowCount();

		while ($row = $stmt->fetch())
		{
			// vot: Temporarily disable OLD GENDER Fields!!!!!!
			//if( ($row['fid'] != 1) && ($row['fid'] != 6) ) {
			$custom_fields['field_' . $row['fid']] = $IN['field_' . $row['fid']];
			//}
		}

		//+--------------------------------------------
		//| Find the highest member id, and increment it
		//| auto_increment not used for guest id 0 val.
		//+--------------------------------------------

		$data = [
			'name'        => trim($IN['name']),
			'password'    => md5(trim($IN['password'])),
			'email'       => trim(strtolower($IN['email'])),
			'mgroup'      => $IN['mgroup'],
			'old_group'   => $IN['mgroup'],
			'joined'      => time(),
			'posts'       => 0,
			'ip_address'  => $IN['ip_address'],
			'time_offset' => 0,
			'view_sigs'   => 1,
			'view_avs'    => 1,
			'view_pop'    => 1,
			'view_img'    => 1,
			'vdirs'       => "in:Inbox|sent:Sent Items",
		];

		$ibforums->db->insertRow("ibf_members", $data);

		$member_id = $ibforums->db->lastInsertId();

		//+--------------------------------------------
		//| Insert into the custom profile fields DB
		//+--------------------------------------------

		if (count($custom_fields) > 0)
		{

			$custom_fields['member_id'] = $member_id;
			$ibforums->db->insertRow("ibf_pfields_content", $custom_fields);

		}

		//+--------------------------------------------

		$ibforums->db->exec("UPDATE ibf_stats SET " . "MEM_COUNT=MEM_COUNT+1, " . "LAST_MEM_NAME='" . trim($IN['name']) . "', " . "LAST_MEM_ID='" . $member_id . "'");

		$ADMIN->save_log("Created new member account for '{$IN['name']}'");

		$ADMIN->done_screen("Member Account Created", "Member Control", "act=mem&code=edit");

	}

	//+---------------------------------------------------------------------------------
	//
	// SEARCH FORM, SEARCH FOR MEMBER
	//
	//+---------------------------------------------------------------------------------

	function search_form()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$ADMIN->page_title = "Edit a member";

		$ADMIN->page_detail = "Search for a member.";

		$mem_group = array(0 => array('all', 'Any Group'));

		$stmt = $ibforums->db->query("SELECT g_id, g_title
			    FROM ibf_groups
			    ORDER BY g_title");

		while ($r = $stmt->fetch())
		{
			$mem_group[] = array($r['g_id'], $r['g_title']);
		}

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'stepone'),
		                                       2 => array('act', 'mem'),
		                                  ));

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Member Quick Search");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Enter part or all of the usersname</b>",
		                                       $SKIN->form_input("USER_NAME")
		                                  ));

		$ADMIN->html .= $SKIN->end_form("Find Member");

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'advancedsearch'),
		                                       2 => array('act', 'mem'),
		                                  ));

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Member Advanced Search", "Please complete at least one section, leave fields blank to omit from the query");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Member Name contains...</b>",
		                                       $SKIN->form_input("name")
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Email Address contains...</b>",
		                                       $SKIN->form_input("email")
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>IP Address contains...</b>",
		                                       $SKIN->form_input("ip_address")
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>AIM name contains...</b>",
		                                       $SKIN->form_input("aim_name")
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>ICQ Number contains...</b>",
		                                       $SKIN->form_input("icq_number")
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Yahoo! Identity contains...</b>",
		                                       $SKIN->form_input("yahoo")
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Signature contains...</b>",
		                                       $SKIN->form_input("signature")
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Last post from...</b>",
		                                       $SKIN->form_simple_input("last_post") . '... days ago to now'
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Last active from...</b>",
		                                       $SKIN->form_simple_input("last_activity") . '... days ago to now'
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Is in group...</b>",
		                                       $SKIN->form_dropdown("mgroup", $mem_group)
		                                  ));

		$ADMIN->html .= $SKIN->end_form("Query Member Database");

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	//+---------------------------------------------------------------------------------

	function do_advanced_search($basic = 0)
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$page_query = "";
		$un_all     = "";

		if ($IN['showsusp'] == 1)
		{
			$rq     = "temp_ban <> '' and temp_ban is not null";
			$un_all = "<a href='{$SKIN->base_url}&act=mem&code=unsuspend&mid=all'>Unsuspend All Accounts</a>&nbsp;&middot&nbsp;";
		} else
		{
			if ($basic == 0)
			{
				$query = array();

				foreach (array(
					         'name',
					         'email',
					         'ip_address',
					         'aim_name',
					         'icq_number',
					         'yahoo',
					         'signature',
					         'last_post',
					         'last_activity',
					         'mgroup'
				         ) as $bit)
				{
					$IN[$bit] = urldecode(trim($IN[$bit]));

					$page_query .= '&' . $bit . '=' . urlencode($IN[$bit]);

					if ($IN[$bit] != "")
					{
						if ($bit == 'last_post' or $bit == 'last_activity')
						{
							$dateline = time() - ($IN[$bit] * 60 * 60 * 24);
							$query[]  = 'm.' . $bit . ' > ' . "'$dateline'";
						} else
						{
							if ($bit == 'mgroup')
							{
								if ($IN['mgroup'] != 'all')
								{
									$query[] = "m.mgroup=" . $IN['mgroup'];
								}
							} else
							{
								$query[] = 'm.' . $bit . " LIKE '%" . $IN[$bit] . "%'";
							}
						}
					}
				}

				if (count($query) < 1)
				{
					$ADMIN->error("Please complete at least one field before submitting the search form");
				}

				$rq = implode(" AND ", $query);
			} else
			{
				// Basic username search

				if ($IN['decode'])
				{
					$IN['USER_NAME'] = trim(urldecode($IN['USER_NAME']));
				} else
				{
					$IN['USER_NAME'] = trim($IN['USER_NAME']);
				}

				if ($IN['USER_NAME'] == "")
				{
					$ADMIN->error("You didn't choose a member name to look for!");
				}

				$page_query = "&decode=1&USER_NAME=" . urlencode($IN['USER_NAME']);

				$rq = "name LIKE '" . $IN['USER_NAME'] . "%'";
			}
		}

		$st = intval($IN['st']);

		if ($st < 1)
		{
			$st = 0;
		}

		$query = "SELECT m.id, m.email, m.name, m.mgroup, m.ip_address, m.posts, m.temp_ban, g.g_title
		          FROM ibf_members m
		           LEFT JOIN ibf_groups g ON (g.g_id=m.mgroup)
		          WHERE $rq
			  ORDER BY m.name
			  LIMIT $st,50";

		//+-------------------------------
		// Get the number of results
		//+-------------------------------

		$stmt = $ibforums->db->query("SELECT COUNT(m.id) AS count
			    FROM ibf_members m
			    WHERE $rq");

		$count = $stmt->fetch();

		if ($count['count'] < 1)
		{
			if ($IN['showsusp'])
			{
				$ADMIN->error("There are currently no suspended member accounts");
			} else
			{
				$ADMIN->error("Your search query did not return any matches from the member database. Please go back and try again");
			}
		}

		$ADMIN->page_title = "Your Member Search Results";

		$ADMIN->page_detail = "Your search results.";

		//+-------------------------------

		$pages = $std->build_pagelinks(array(
		                                    'TOTAL_POSS' => $count['count'],
		                                    'PER_PAGE'   => 50,
		                                    'CUR_ST_VAL' => $IN['st'],
		                                    'L_SINGLE'   => $un_all . "Single Page",
		                                    'L_MULTI'    => $un_all . "Multi Page",
		                                    'BASE_URL'   => $SKIN->base_url . "&act=mem&showsusp={$IN['showsusp']}&code={$IN['code']}" . $page_query,
		                               ));

		//+-------------------------------

		$SKIN->td_header[] = array("IP Address ", "15%");
		$SKIN->td_header[] = array("Group", "10%");
		$SKIN->td_header[] = array("Posts", "10%");
		$SKIN->td_header[] = array("Email", "15%");
		$SKIN->td_header[] = array("Edit", "16%");
		$SKIN->td_header[] = array("Change", "16%");
		$SKIN->td_header[] = array("Ban", "17%");

		//+-------------------------------

		$title = $IN['showsusp'] == 1
			? 'Suspended Accounts'
			: 'Search Results';

		$ADMIN->html .= $SKIN->start_table("{$count['count']} " . $title);

		//+-------------------------------
		// Run the query
		//+-------------------------------

		$stmt = $ibforums->db->query($query);

		while ($r = $stmt->fetch())
		{
			$tban     = "";
			$sus_link = "";

			if ($r['temp_ban'] != "")
			{
				$s_ban = $std->hdl_ban_line($r['temp_ban']);

				$sus_link = " - <a href='{$SKIN->base_url}&act=mem&code=unsuspend&mid={$r['id']}'>Unsuspend</a>";

				$tban = '&nbsp;&nbsp;<span style="font-size:10px">(Suspended until ' . $ADMIN->get_date($s_ban['date_end'], 'LONG') . $sus_link . ')</span>';
			}

			$ADMIN->html .= $SKIN->add_td_basic("<img src='{$this->img_url}/item.gif' border='0' alt='-'>&nbsp;<a style='font-size:12px' title='View this members profile' href='{$INFO['board_url']}/index.{$INFO['php_ext']}?act=Profile&MID={$r['id']}' target='blank'>{$r['name']}</a> $tban", "left", "pformstrip");
			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "{$r['ip_address']}",
			                                       $r['g_title'],
			                                       "<center>" . $r['posts'] . "</center>",
			                                       "<center>" . $r['email'] . "</center>",
			                                       "<center><strong><a href='{$SKIN->base_url}&act=mem&code=doform&MEMBER_ID={$r['id']}' title='Edit this members account'>Edit Details</a></strong></center>",
			                                       "<center><a href='{$SKIN->base_url}&act=mem&code=changename&mid={$r['id']}' title='Change this members name'>Change Name</a></center>",
			                                       "<center><a href='{$SKIN->base_url}&act=mem&code=banmember&mid={$r['id']}' title='Suspend Member'>Suspend Account</a></span></center>"
			                                  ));

		}

		$ADMIN->html .= $SKIN->add_td_basic($pages, 'right', 'pformstrip');

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	//+---------------------------------------------------------------------------------
	//
	// DO EDIT FORM
	//
	//+---------------------------------------------------------------------------------

	function do_edit_form()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $ibforums;

		require ROOT_PATH . "sources/lib/post_parser.php";

		$parser = new post_parser();

		if ($IN['MEMBER_ID'] == "")
		{
			$ADMIN->error("Could not resolve member id");
		}

		//+-------------------------------

		$stmt = $ibforums->db->query("SELECT *
			    FROM ibf_members
                            WHERE id='" . $IN['MEMBER_ID'] . "'");
		$mem  = $stmt->fetch();

		//+-------------------------------

		if ($mem['id'] == "")
		{
			$ADMIN->error("Could not resolve member id");
		}

		//+-------------------------------

		$mem_group  = array();
		$show_fixed = FALSE;

		$units = array(0 => array('h', 'Hours'), 1 => array('d', 'Days'));

		$stmt = $ibforums->db->query("SELECT g_id, g_title
                            FROM ibf_groups
                            ORDER BY g_title");

		while ($r = $stmt->fetch())
		{
			// Ensure only root admins can promote to root admin grou...
			// oh screw it, I can't be bothered explaining stuff tonight

			if ($INFO['admin_group'] == $r['g_id'])
			{
				if ($MEMBER['mgroup'] != $INFO['admin_group'])
				{
					continue;
				}
			}

			$mem_group[] = array($r['g_id'], $r['g_title']);
		}

		// is this a non root editing a root?

		if ($MEMBER['mgroup'] != $INFO['admin_group'])
		{
			if ($mem['mgroup'] == $INFO['admin_group'])
			{
				$show_fixed = TRUE;
			}
		}

		//+-------------------------------

		$lang_array = array();

		$stmt = $ibforums->db->query("SELECT ldir, lname
                            FROM ibf_languages");

		while ($l = $stmt->fetch())
		{
			$lang_array[] = array($l['ldir'], $l['lname']);
		}

		//+-------------------------------

		$stmt = $ibforums->db->query("SELECT uid, sid, sname, default_set, hidden
                            FROM ibf_skins");

		$skin_array = array();

		$def_skin = "";

		if ($stmt->rowCount())
		{
			while ($s = $stmt->fetch())
			{
				if ($s['default_set'] == 1)
				{
					$def_skin = $s['sid'];
				}

				if ($s['hidden'] == 1)
				{
					$hidden = " *(Hidden)";
				} else
				{
					$hidden = "";
				}

				$skin_array[] = array($s['sid'], $s['sname'] . $hidden);

			}
		}

		//+-------------------------------

		if ($INFO['default_language'] == "")
		{
			$INFO['default_language'] = 'en';
		}

		//-----------------------------------------------
		// Custom profile fields stuff
		//-----------------------------------------------

		$custom_output = "";
		$field_data    = array();

		$stmt = $ibforums->db->query("SELECT *
                            FROM ibf_pfields_content
                            WHERE member_id='" . $IN['MEMBER_ID'] . "'");

		while ($content = $stmt->fetch())
		{
			foreach ($content as $k => $v)
			{
				if (preg_match("/^field_(\d+)$/", $k, $match))
				{
					// Temporarily DISABLE Old Gender (field_1/6)
					//if(!($k=='field_1' || $k=='field_6')) {
					$field_data[$match[1]] = $v;
					//}
				}
			}
		}

		$stmt = $ibforums->db->query("SELECT *
                            FROM ibf_pfields_data
                            ORDER BY forder");

		while ($row = $stmt->fetch())
		{
			$form_element = "";

			if ($row['fid'] != '1' && $row['fid'] != '6')
			{
				if ($row['ftype'] == 'drop')
				{
					$carray = explode('|', trim($row['fcontent']));

					$d_content = array();

					foreach ($carray as $entry)
					{
						$value = explode('=', $entry);

						$ov = trim($value[0]);
						$td = trim($value[1]);

						if ($ov != "" and $td != "")
						{
							$d_content[] = array($ov, $td);
						}
					}

					if (!isset($field_data[$row['fid']]))
					{
						$field_data[$row['fid']] = "0";
					}

					$form_element = $SKIN->form_dropdown('field_' . $row['fid'], $d_content, $field_data[$row['fid']]);

				} else
				{
					if ($row['ftype'] == 'area')
					{
						$form_element = $SKIN->form_textarea('field_' . $row['fid'], $field_data[$row['fid']]);
					} else
					{
						$form_element = $SKIN->form_input('field_' . $row['fid'], $field_data[$row['fid']]);
					}
				}
				$custom_out .= $SKIN->add_td_row(array("<b>{$row['ftitle']}</b><br>{$row['desc']}", $form_element));
			}

		}

		//+-------------------------------
		//| Perms masks section
		//+-------------------------------

		$perm_masks = array();

		$stmt = $ibforums->db->query("SELECT *
			    FROM ibf_forum_perms");

		while ($r = $stmt->fetch())
		{
			$perm_masks[] = array($r['perm_id'], $r['perm_name']);
		}

		//+-------------------------------

		$ADMIN->page_title = "Edit member: " . $mem['name'] . " (ID: " . $mem['id'] . ")";

		$ADMIN->page_detail = "You may alter the members settings from here.";

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'doedit'),
		                                       2 => array('act', 'mem'),
		                                       3 => array('mid', $mem['id']),
		                                       4 => array('curpass', $mem['password']),
		                                  ));

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Member Security Settings");

		$ADMIN->html .= $SKIN->add_td_row(array("<b>IP address when registered</b>", $mem['ip_address']));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Remove member's photo</b>",
		                                       $SKIN->form_checkbox("remove_photo", 0)
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Warn Level</b>",
		                                       $SKIN->form_input("warn_level", $mem['warn_level'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Member Title</b>",
		                                       $SKIN->form_input("title", $mem['title'])
		                                  ));

		if ($show_fixed != TRUE)
		{
			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Member Group</b>",
			                                       $SKIN->form_dropdown("mgroup", $mem_group, $mem['mgroup'])
			                                  ));
		} else
		{
			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Member Group</b>",
			                                       $SKIN->form_hidden(array(
			                                                               1 => array(
				                                                               'mgroup',
				                                                               $mem['mgroup']
			                                                               )
			                                                          )) . "<b>Root Admin</b> (Can't Change)",
			                                  ));
		}

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Запретить автоматическую смену группы?</b><br>(например, автоматический переход в Клуб)",
		                                       $SKIN->form_yes_no("disable_group", $mem['disable_group'])
		                                  ));

		//+-------------------------------
		// Sort out perm id stuff
		//+-------------------------------

		$ADMIN->html .= "<script type='text/javascript'>

			var show   = '';
		";

		foreach ($perm_masks as $id => $d)
		{
			$ADMIN->html .= " 		perms_$d[0] = '$d[1]';\n";
		}

		$ADMIN->html .= "

		 	function saveit(f)
		 	{
		 		show = '';
		 		for (var i = 0 ; i < f.options.length; i++)
				{
					if (f.options[i].selected)
					{
						tid  = f.options[i].value;
						show += '\\n' + eval('perms_'+tid);
					}
				}

				if ( show != '' )
				{
					document.forms[0].override.checked = true;
				}
				else
				{
					document.forms[0].override.checked = false;
				}
			}

			function show_me()
			{
				if (show == '')
				{
					show = 'No change detected\\nClick on the multi-select box to activate';
				}

				alert('Selected Permission Masks\\n---------------------------------\\n' + show);
			}

		</script>";

		$arr = explode(",", $mem['org_perm_id']);

		$ch_ch = ($mem['org_perm_id'])
			? 'checked'
			: '';

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Override group forum permission mask with...</b><br>You may choose more than one",
		                                       "<input type='checkbox' name='override' value='1' $ch_ch> <b>Override Group Permission Mask with...</b><br>" . $SKIN->form_multiselect("permid[]", $perm_masks, $arr, 5, 'onfocus="saveit(this)" onchange="saveit(this)"') . "<br><input style='margin-top:5px' id='editbutton' type='button' onclick='show_me();' value='Show me selected masks'>"
		                                  ), "subforum");

		//-----------------------------------------------------------------------------------------------
		// Mod posts bit
		//-----------------------------------------------------------------------------------------------

		$mod_tick = 0;
		$mod_arr  = array();

		if ($mem['mod_posts'] == 1)
		{
			$mod_tick = 'checked';
		} elseif ($mem['mod_posts'] > 0)
		{
			$mod_arr = $std->hdl_ban_line($mem['mod_posts']);

			$hours = ceil(($mod_arr['date_end'] - time()) / 3600);

			if ($hours > 24 and (($hours / 24) == ceil($hours / 24)))
			{
				$mod_arr['units']    = 'd';
				$mod_arr['timespan'] = $hours / 24;
			} else
			{
				$mod_arr['units']    = 'h';
				$mod_arr['timespan'] = $hours;
			}

			$mod_extra = "<br /><span style='color:red'>Restriction in progress - remaining time has been recalculated</span>";
		}

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Require moderator preview of all posts by this member?</b><br>If yes, all posts by this member will be put into the moderation queue. Untick box and clear number box to remove.",
		                                       "<input type='checkbox' name='mod_indef' value='1' $mod_tick> Moderator Preview indefinitely
							  <br /><b>or for</b> " . $SKIN->form_input('mod_timespan', $mod_arr['timespan'], "text", "", '5') . '&nbsp;' . $SKIN->form_dropdown('mod_units', $units, $mod_arr['units']) . $mod_extra
		                                  ));

		$post_tick = 0;
		$post_arr  = array();

		if ($mem['restrict_post'] == 1)
		{
			$post_tick = 'checked';
		} else
		{
			if ($mem['restrict_post'] > 0)
			{
				$post_arr = $std->hdl_ban_line($mem['restrict_post']);

				$hours = ceil(($post_arr['date_end'] - time()) / 3600);

				if ($hours > 24 and (($hours / 24) == ceil($hours / 24)))
				{
					$post_arr['units']    = 'd';
					$post_arr['timespan'] = $hours / 24;
				} else
				{
					$post_arr['units']    = 'h';
					$post_arr['timespan'] = $hours;
				}

				$post_extra = "<br /><span style='color:red'>Restriction in progress - remaining time has been recalculated</span>";
			}
		}

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Restrict {$mem['name']} from posting?</b><br>Untick box and clear number box to remove restriction.",
		                                       "<input type='checkbox' name='post_indef' value='1' $post_tick> Restrict posting indefinitely
							  <br /><b>or for</b> " . $SKIN->form_input('post_timespan', $post_arr['timespan'], "text", "", '5') . '&nbsp;' . $SKIN->form_dropdown('post_units', $units, $post_arr['units']) . $post_extra
		                                  ), "subforum");

		//-----------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Password Control");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>New Password</b><br>(Leave this blank if you do not wish to reset password!)",
		                                       $SKIN->form_input("password")
		                                  ));

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------+

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		//+-------------------------------+

		$ADMIN->html .= $SKIN->start_table("Board Settings");

		//+-------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Language Choice</b>",
		                                       $SKIN->form_dropdown("language", $lang_array, $mem['language'] != ""
			                                       ? $mem['language']
			                                       : $INFO['default_language'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Skin Choice</b>",
		                                       $SKIN->form_dropdown("skin", $skin_array, $mem['skin'] != ""
			                                       ? $mem['skin']
			                                       : $def_skin)
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Hide this members email address?</b>",
		                                       $SKIN->form_yes_no("hide_email", $mem['hide_email'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Email a PM reminder?</b>",
		                                       $SKIN->form_yes_no("email_pm", $mem['email_pm'])
		                                  ));

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------+

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		//+-------------------------------+

		$ADMIN->html .= $SKIN->start_table("Contact Information");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Email Address</b>",
		                                       $SKIN->form_input("email", $mem['email'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>AIM Identity</b>",
		                                       $SKIN->form_input("aim_name", $mem['aim_name'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>ICQ Number</b>",
		                                       $SKIN->form_input("icq_number", $mem['icq_number'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Yahoo Identity</b>",
		                                       $SKIN->form_input("yahoo", $mem['yahoo'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>MSN Identity</b>",
		                                       $SKIN->form_input("msnname", $mem['msnname'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Website Address</b>",
		                                       $SKIN->form_input("website", $mem['website'])
		                                  ));

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------+

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		//+-------------------------------+

		$ADMIN->html .= $SKIN->start_table("Other Information");

		//+-------------------------------

		$mem['signature'] = $parser->unconvert($mem['signature']);

		// New Gender
		$genders = array(
			array('', 'Unknown'),
			array('m', 'Male'),
			array('f', 'Female'),
		);
		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Gender</b>",
		                                       $SKIN->form_dropdown('gender', $genders, $mem['gender']),
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Avatar</b>",
		                                       $SKIN->form_input("avatar", $mem['avatar'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Avatar Size</b>",
		                                       $SKIN->form_input("avatar_size", $mem['avatar_size'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Post Count</b>",
		                                       $SKIN->form_input("posts", $mem['posts'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Members Points</b>",
		                                       $SKIN->form_input("points", $mem['points'])
		                                  ));
		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Location</b>",
		                                       $SKIN->form_input("location", $mem['location'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Interests</b>",
		                                       $SKIN->form_textarea("interests", str_replace('<br>', "\n", $mem['interests']))
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Signature</b>",
		                                       $SKIN->form_textarea("signature", $mem['signature'])
		                                  ));

		// Song

		//Reputation
		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------+

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		//+-------------------------------+

		$ADMIN->html .= $SKIN->start_table("Reputation Control");

		//+-------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow this member to change others' Reputation?</b>",
		                                       $SKIN->form_yes_no("rep_rep", $mem['allow_rep'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow anonymous voting?</b><br>Will only work if board overall anonymous voting is allowed",
		                                       $SKIN->form_yes_no("rep_anon", $mem['allow_anon'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Recount this member's Reputation?",
		                                       $SKIN->form_yes_no("rep_recount", 0)
		                                  ));

		$ADMIN->html .= $SKIN->end_table();
		//Reputation

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		$ADMIN->html .= $SKIN->start_table("Контроль подписок и электронной почты");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Удалить все подписки пользователя на темы и форумы</b>?",
		                                       $SKIN->form_checkbox("subscribe_delete", 0)
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Запретить пользователю работу с электронной почтой</b>?",
		                                       $SKIN->form_yes_no("disable_mail", $mem['disable_mail'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Причина блокировки</b>",
		                                       $SKIN->form_textarea("disable_mail_reason", $mem['disable_mail_reason'])
		                                  ));

		//+-------------------------------

		if ($custom_out != "")
		{

			$ADMIN->html .= $SKIN->end_table();

			$SKIN->td_header[] = array("&nbsp;", "40%");
			$SKIN->td_header[] = array("&nbsp;", "60%");

			//+-------------------------------+

			$ADMIN->html .= $SKIN->start_table("Custom Profile Fields");

			$ADMIN->html .= $custom_out;

		}

		//+-------------------------------

		$ADMIN->html .= $SKIN->end_form("Edit this member");

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	//+---------------------------------------------------------------------------------

	function do_edit()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $ibforums;

		$stmt = $ibforums->db->query("SELECT name, mgroup, password
			    FROM ibf_members
			    WHERE id='" . $IN['mid'] . "'");

		$memb = $stmt->fetch();

		require ROOT_PATH . "sources/lib/post_parser.php";

		$parser = new post_parser();

		$IN['signature'] = $parser->convert(array(

		                                         'TEXT'      => $IN['signature'],
		                                         'SMILIES'   => 0,
		                                         'CODE'      => $INFO['sig_allow_ibc'],
		                                         'HTML'      => $INFO['sig_allow_html'],
		                                         'SIGNATURE' => 1

		                                    ));

		if ($IN['override'] == 1)
		{
			$permid = implode(",", $_POST['permid']);
		} else
		{
			$permid = "";
		}

		$restrict_post = 0;
		$mod_queue     = 0;

		if ($IN['mod_indef'] == 1)
		{
			$mod_queue = 1;
		} elseif ($IN['mod_timespan'] > 0)
		{
			$mod_queue = $std->hdl_ban_line(array(
			                                     'timespan' => intval($IN['mod_timespan']),
			                                     'unit'     => $IN['mod_units']
			                                ));
		}

		if ($IN['post_indef'] == 1)
		{
			$restrict_post = 1;

		} elseif ($IN['post_timespan'] > 0)
		{
			$restrict_post = $std->hdl_ban_line(array(
			                                         'timespan' => intval($IN['post_timespan']),
			                                         'unit'     => $IN['post_units']
			                                    ));
		}

		$member = array(
			'restrict_post'       => $restrict_post,
			'mgroup'              => $IN['mgroup'],
			'title'               => $IN['title'],
			'language'            => $IN['language'],
			'skin'                => $IN['skin'],
			'gender'              => $IN['gender'],
			'hide_email'          => $IN['hide_email'],
			'email_pm'            => $IN['email_pm'],
			'email'               => $IN['email'],
			'aim_name'            => $IN['aim_name'],
			'icq_number'          => $IN['icq_number'],
			'yahoo'               => $IN['yahoo'],
			'msnname'             => $IN['msnname'],
			'website'             => $IN['website'],
			'avatar'              => $IN['avatar'],
			'avatar_size'         => $IN['avatar_size'],
			'posts'               => $IN['posts'],
			'points'              => $IN['points'],
			'location'            => $IN['location'],
			'interests'           => $IN['interests'],
			'signature'           => $IN['signature'],
			'mod_posts'           => $mod_queue,
			'org_perm_id'         => $permid,
			'warn_level'          => $IN['warn_level'],
			'allow_anon'          => $IN['rep_anon'],
			'allow_rep'           => $IN['rep_rep'],
			'disable_mail'        => $IN['disable_mail'],
			'disable_mail_reason' => $IN['disable_mail_reason'],
			'disable_group'       => $IN['disable_group'],
		);
		if ($IN['password'] != "")
		{
			$member['password'] = md5($IN['password']);
		}

		if (!$IN['warn_level'])
		{
			$member['old_group'] = $IN['mgroup'];
		}

		$ibforums->db->updateRow("ibf_members", array_map([
		                                                  $ibforums->db,
		                                                  'quote'
		                                                  ], $member), "id='" . $IN['mid'] . "'");

		// Song * reputation

		if ($IN['rep_recount'])
		{
			$std->rep_recount($IN['mid']);
		}

		// Song * reputation

		//----------------------------------
		// Remove photo?
		//----------------------------------

		if ($IN['remove_photo'])
		{
			$stmt = $ibforums->db->query("SELECT id
			     FROM ibf_member_extra
			     WHERE id={$IN['mid']}");

			if ($stmt->rowCount())
			{
				$ibforums->db->exec("UPDATE ibf_member_extra SET
						photo_location='',
						photo_type='',
						photo_dimensions=''
					    WHERE id={$IN['mid']}");
			} else
			{
				$ibforums->db->exec("INSERT INTO ibf_member_extra SET
						photo_location='',
						photo_type='',
						photo_dimensions='',
						id={$IN['mid']}");
			}

			foreach (array('swf', 'jpg', 'jpeg', 'gif', 'png') as $ext)
			{
				if (@file_exists($INFO['upload_dir'] . "/photo-" . $IN['mid'] . "." . $ext))
				{
					@unlink($INFO['upload_dir'] . "/photo-" . $IN['mid'] . "." . $ext);
				}
			}
		}

		if ($IN['subscribe_delete'])
		{

			$ibforums->db->exec("DELETE FROM ibf_tracker
				    WHERE member_id={$IN['mid']}");

			$ibforums->db->exec("DELETE FROM ibf_forum_tracker
				    WHERE member_id={$IN['mid']}");

		}

		//----------------------------------
		// Custom profile field stuff
		//----------------------------------

		$custom_fields = array();

		$stmt = $ibforums->db->query("SELECT *
			    FROM ibf_pfields_data");

		while ($row = $stmt->fetch())
		{
			$custom_fields['field_' . $row['fid']] = str_replace('<br>', "\n", $IN['field_' . $row['fid']]);
		}

		if (count($custom_fields) > 0)
		{
			// Do we already have an entry in the content table?

			$stmt = $ibforums->db->query("SELECT member_id
                                    FROM ibf_pfields_content
                                    WHERE member_id='" . $IN['mid'] . "'");
			$test = $stmt->fetch();

			if ($test['member_id'])
			{
				// We have it, so simply update
				$ibforums->db->updateRow("ibf_pfields_content", array_map([
				                                                          $ibforums->db,
				                                                          'quote'
				                                                          ], $custom_fields), "member_id='" . $IN['mid'] . "'");
			} else
			{
				$custom_fields['member_id'] = $IN['mid'];

				$ibforums->db->insertRow("ibf_pfields_content", $custom_fields);
			}
		}

		if (USE_MODULES == 1)
		{
			$this->modules->register_class($this);

			if (md5($IN['password']) != $memb['password'])
			{
				$this->modules->on_pass_change($IN['mid'], $IN['password']);
			}

			if ($memb['mgroup'] != $IN['mgroup'])
			{
				$this->modules->on_group_change($IN['mid'], $IN['mgroup']);
			}

			$mem_array = array(
				'title'      => $IN['title'],
				'aim_name'   => $IN['aim_name'],
				'icq_number' => $IN['icq_number'],
				'yahoo'      => $IN['yahoo'],
				'msnname'    => $IN['msnname'],
				'website'    => $IN['website'],
				'location'   => $IN['location'],
				'gender'     => $IN['gender'],
				'interests'  => $IN['interests'],
				'id'         => $IN['mid']

			);

			$this->modules->on_profile_update($mem_array, $custom_fields);
		}

		$ADMIN->save_log("Edited Member '{$memb['name']}' account");

		$ADMIN->done_screen("Member Edited", "Member Control", "act=mem&code=edit");

	}

	function do_block_email($action)
	{
		global $ADMIN, $IN;
		$ibforums = Ibf::instance();

		$email = trim($IN['email']);

		if (!$email)
		{
			$ADMIN->error("Email address is blank.");
		}

		$stmt = $ibforums->db->query("SELECT id
		    FROM ibf_members
		    WHERE email='" . addslashes($email) . "'");

		if (!$stmt->rowCount())
		{
			$ADMIN->error("Cannot find email address specified.");
		}

		$reason = ($action and $IN['reason'] and $IN['reason'] != "Enter reason here")
			? ",disable_mail_reason='" . addslashes($IN['reason']) . "'"
			: "";

		if (!$action)
		{
			$reason = ",disable_mail_reason=NULL";
		}

		$ibforums->db->exec("UPDATE ibf_members
		    SET disable_mail='" . $action . "'" . $reason . "
		    WHERE email='" . addslashes($email) . "'");

		$reason = ($action and $IN['reason'] and $IN['reason'] != "Enter reason here")
			? " with reason: " . $IN['reason']
			: "";

		$locked = ($action)
			? "locked"
			: "unlocked";

		$ADMIN->save_log($locked . " email address $email" . $reason);

		$ADMIN->done_screen("Email address $email has been " . $locked . $reason, "Return to ACP Home", "act=index");

	}

	function do_delete_members()
	{
		global $IN, $ADMIN, $std, $SKIN;
		$ibforums = Ibf::instance();

		$email = trim($IN['email']);

		if (!$email)
		{
			$ADMIN->error("Email address is blank.");
		}

		$stmt = $ibforums->db->query("SELECT m.id, m.name, m.email, m.posts, g.g_title,
		     IF(m.disable_mail=0,'no','yes') as disable_mail,
		     IF(m.temp_ban IS NULL,'no','yes') as temp_ban,
		     IF(m.mod_posts=0,'no','yes') as mod_posts,
		     IF(m.restrict_post=0,'yes','no') as restrict_posts,
		     IF(m.last_activity=0,'not active',DATE_FORMAT(FROM_UNIXTIME(m.last_activity),'%d.%m.%Y')) as last_time,
		     DATE_FORMAT(FROM_UNIXTIME(m.joined),'%d.%m.%Y') as joined, IFNULL(m.warn_level,0) as warn_level
		    FROM ibf_members m, ibf_groups g
		    WHERE g.g_id=m.mgroup and m.email='" . addslashes($email) . "'");

		if (!$stmt->rowCount())
		{
			$ADMIN->error("Cannot find email address specified.");
		}

		$member_arr = array();

		while ($mem = $stmt->fetch())
		{
			$info = " ({$mem['email']}), posts: <b>{$mem['posts']}</b>, joined: <b>{$mem['joined']}</b>, last forum activity: <b>{$mem['last_time']}</b>";

			$info .= "<br>";

			$info .= "group: <b>{$mem['g_title']}</b>, mail disable: <b>{$mem['disable_mail']}</b>, warnings: <b>{$mem['warn_level']}</b>,<br>";

			$info .= " personal ban: <b>{$mem['temp_ban']}</b>, profile premoderation: <b>{$mem['mod_posts']}</b>,";

			$info .= " can post: <b>{$mem['restrict_posts']}</b>";

			$member_arr[] = $std->make_profile_link($mem['name'], $mem['id'], $info);
		}

		$ADMIN->page_title = "Member Pruning";

		$ADMIN->page_detail = "Please confirm your action.";

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'doprune'),
		                                       2 => array('act', 'mem'),
		                                       3 => array('song', '1'),
		                                       4 => array('query', 'email="' . $email . '"'),
		                                  ));

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "20%");
		$SKIN->td_header[] = array("&nbsp;", "80%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Member Prune Confirmation");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Members to prune</b>",
		                                       implode('<br><br>', $member_arr)
		                                  ));

		$ADMIN->html .= $SKIN->end_form("Complete Member Pruning");

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

}

