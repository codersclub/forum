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
|   > Email Logs Stuff
|   > Module written by Matt Mecham
|   > Date started: 11nd September 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

// Ensure we've not accessed this script directly:

$idx = new ad_emaillogs();

class ad_emaillogs
{

	var $base_url;
	var $colours = array();

	function ad_emaillogs()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		//---------------------------------------
		// Kill globals - globals bad, Homer good.
		//---------------------------------------

		$tmp_in = array_merge($_GET, $_POST, $_COOKIE);

		foreach ($tmp_in as $k => $v)
		{
			unset($$k);
		}

		//---------------------------------------

		// Make sure we're a root admin, or else!

		if ($MEMBER['mgroup'] != $INFO['admin_group'])
		{
			$ADMIN->error("Sorry, these functions are for the root admin group only");
		}

		switch ($IN['code'])
		{

			case 'list':
				$this->list_current();
				break;

			case 'remove':
				$this->remove_entries();
				break;

			case 'viewemail':
				$this->view_email();
				break;

			//-------------------------
			default:
				$this->list_current();
				break;
		}

	}

	//---------------------------------------------
	// View a single email.
	//---------------------------------------------

	function view_email()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		if ($IN['id'] == "")
		{
			$ADMIN->error("Could not resolve the email ID, please try again");
		}

		$id = intval($IN['id']);

		$stmt = $ibforums->db->query("SELECT email.*, m.id, m.name, mem.id as to_id, mem.name as to_name
			    FROM ibf_email_logs email
					 LEFT JOIN ibf_members m ON (m.id=email.from_member_id)
					 LEFT JOIN ibf_members mem ON (mem.id=email.to_member_id)
			    WHERE email.email_id=$id");

		if (!$row = $stmt->fetch())
		{
			$ADMIN->error("Could not resolve the email ID, please try again ($id)");
		}

		$SKIN->td_header[] = array("&nbsp;", "100%");

		$ADMIN->html .= $SKIN->start_table($row['email_subject']);

		$row['email_date'] = $ADMIN->get_date($row['email_date'], 'LONG');

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<strong>From:</strong> {$row['name']} &lt;{$row['from_email_address']}&gt;
													<br /><strong>To:</strong> {$row['to_name']} &lt;{$row['to_email_address']}&gt;
													<br /><strong>Sent:</strong> {$row['email_date']}
													<br /><strong>From IP:</strong> {$row['from_ip_address']}
													<br /><strong>Subject:</strong> {$row['email_subject']}
													<hr>
													<br />{$row['email_content']}
												    "
		                                  ));

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->print_popup();
	}

	//---------------------------------------------
	// Remove row(s)
	//---------------------------------------------

	function remove_entries()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		if ($IN['type'] == 'all')
		{
			$ibforums->db->exec("DELETE FROM ibf_email_logs");
		} else
		{
			$ids = array();

			foreach ($IN as $k => $v)
			{
				if (preg_match("/^id_(\d+)$/", $k, $match))
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
				$ADMIN->error("You did not select any email log entries to approve or delete");
			}

			$ibforums->db->exec("DELETE FROM ibf_email_logs WHERE email_id IN (" . implode(',', $ids) . ")");
		}

		$ADMIN->save_log("Removed " . count($ids) . " email log entries");

		$std->boink_it($ADMIN->base_url . "&act=emaillog");
		exit();

	}

	//-------------------------------------------------------------
	// SHOW ALL LANGUAGE PACKS
	//-------------------------------------------------------------

	function list_current()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$ADMIN->html .= $SKIN->js_pop_win();

		$form_array = array();

		$start = intval($IN['st']);

		$ADMIN->page_detail = "Stored email logs";
		$ADMIN->page_title  = "Email Logs Manager";

		//+-------------------------------
		// Check URL parameters
		//+-------------------------------

		$url_query = array();
		$db_query  = array();

		if ($IN['type'] != "")
		{
			$ADMIN->page_title .= " (Search Results)";

			switch ($IN['type'])
			{
				case 'fromid':
					$url_query[] = 'type=fromid';
					$url_query[] = 'id=' . intval($IN['id']);
					$db_query[]  = 'email.from_member_id=' . intval($IN['id']);
					break;
				case 'toid':
					$url_query[] = 'type=toid';
					$url_query[] = 'id=' . intval($IN['id']);
					$db_query[]  = 'email.to_member_id=' . intval($IN['id']);
					break;
				case 'subject':
					$string = urldecode($IN['string']);
					if ($string == "")
					{
						$ADMIN->error("You must enter something to search by");
					}
					$url_query[] = 'type=' . $IN['type'];
					$url_query[] = 'string=' . urlencode($string);
					$db_query[]  = $IN['match'] == 'loose'
						? "email.email_subject LIKE '%{$string}%'"
						: "email.email_subject='{$string}'";
					break;
				case 'content':
					$string = urldecode($IN['string']);
					if ($string == "")
					{
						$ADMIN->error("You must enter something to search by");
					}
					$url_query[] = 'type=' . $IN['type'];
					$url_query[] = 'string=' . urlencode($string);
					$db_query[]  = $IN['match'] == 'loose'
						? "email.email_content LIKE '%{$string}%'"
						: "email.email_content='{$string}'";
					break;
				case 'email_from':
					$string = urldecode($IN['string']);
					if ($string == "")
					{
						$ADMIN->error("You must enter something to search by");
					}
					$url_query[] = 'type=' . $IN['type'];
					$url_query[] = 'string=' . urlencode($string);
					$db_query[]  = $IN['match'] == 'loose'
						? "email.from_email_address LIKE '%{$string}%'"
						: "email.from_email_address='{$string}'";
					break;
				case 'email_to':
					$string = urldecode($IN['string']);
					if ($string == "")
					{
						$ADMIN->error("You must enter something to search by");
					}
					$url_query[] = 'type=' . $IN['type'];
					$url_query[] = 'string=' . urlencode($string);
					$db_query[]  = $IN['match'] == 'loose'
						? "email.to_email_address LIKE '%{$string}%'"
						: "email.to_email_address='{$string}'";
					break;
				case 'name_from':
					$string = urldecode($IN['string']);
					if ($string == "")
					{
						$ADMIN->error("You must enter something to search by");
					}

					if ($IN['match'] == 'loose')
					{
						$stmt = $ibforums->db->query("SELECT id,name FROM ibf_members WHERE name LIKE '%{$string}%'");

						if (!$stmt->rowCount())
						{
							$ADMIN->error("No matches found in the email logs");
						}

						$ids = array();

						while ($r = $stmt->fetch())
						{
							$ids[] = $r['id'];
						}

						$db_query[] = 'email.from_member_id IN(' . implode(',', $ids) . ')';
					} else
					{
						$stmt = $ibforums->db->query("SELECT id,name FROM ibf_members WHERE name='{$string}'");

						if (!$stmt->rowCount())
						{
							$ADMIN->error("No matches found in the email logs");
						}

						$r = $stmt->fetch();

						$db_query[] = 'email.from_member_id IN(' . $r['id'] . ')';
					}

					$url_query[] = 'type=' . $IN['type'];
					$url_query[] = 'string=' . urlencode($string);
					break;
				case 'name_to':
					$string = urldecode($IN['string']);
					if ($string == "")
					{
						$ADMIN->error("You must enter something to search by");
					}

					if ($IN['match'] == 'loose')
					{
						$stmt = $ibforums->db->query("SELECT id,name FROM ibf_members WHERE name LIKE '%{$string}%'");

						if (!$stmt->rowCount())
						{
							$ADMIN->error("No matches found in the email logs");
						}

						$ids = array();

						while ($r = $stmt->fetch())
						{
							$ids[] = $r['id'];
						}

						$db_query[] = 'email.to_member_id IN(' . implode(',', $ids) . ')';
					} else
					{
						$stmt = $ibforums->db->query("SELECT id,name FROM ibf_members WHERE name='{$string}'");

						if (!$stmt->rowCount())
						{
							$ADMIN->error("No matches found in the email logs");
						}

						$r = $stmt->fetch();

						$db_query[] = 'email.to_member_id IN(' . $r['id'] . ')';
					}

					$url_query[] = 'type=' . $IN['type'];
					$url_query[] = 'string=' . urlencode($string);
					break;
				default:
					//
					break;
			}
		}

		//+-------------------------------
		// LIST 'EM
		//+-------------------------------

		$dbe = "";
		$url = "";

		if (count($db_query) > 0)
		{
			$dbe = ' WHERE ' . implode(' AND ', $db_query);
		}

		if (count($url_query) > 0)
		{
			$url = '&' . implode('&', $url_query);
		}

		$stmt = $ibforums->db->query("SELECT count(email.email_id) as cnt FROM ibf_email_logs email" . $dbe);

		$count = $stmt->fetch();

		$links = $std->build_pagelinks(array(
		                                    'TOTAL_POSS' => $count['cnt'],
		                                    'PER_PAGE'   => 25,
		                                    'CUR_ST_VAL' => $start,
		                                    'L_SINGLE'   => "Single Page",
		                                    'L_MULTI'    => "Pages: ",
		                                    'BASE_URL'   => $ADMIN->base_url . '&act=emaillog' . $url,
		                               ));

		$stmt = $ibforums->db->query("SELECT email.*, m.id, m.name, mem.id as to_id, mem.name as to_name
			    FROM ibf_email_logs email
					 LEFT JOIN ibf_members m ON (m.id=email.from_member_id)
					 LEFT JOIN ibf_members mem ON (mem.id=email.to_member_id) $dbe
			    ORDER BY email_date DESC LIMIT $start,25");

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'remove'),
		                                       2 => array('act', 'emaillog'),
		                                  ));

		$SKIN->td_header[] = array("&nbsp;", "5%");
		$SKIN->td_header[] = array("From Member", "20%");
		$SKIN->td_header[] = array("Subject", "30%");
		$SKIN->td_header[] = array("To Member", "20%");
		$SKIN->td_header[] = array("Sent Time", "25%");

		$ADMIN->html .= $SKIN->start_table("Logged Emails");

		if ($stmt->rowCount())
		{
			while ($row = $stmt->fetch())
			{

				$row['email_date'] = $ADMIN->get_date($row['email_date'], 'SHORT');

				$ADMIN->html .= $SKIN->add_td_row(array(
				                                       "<center><input type='checkbox' class='checkbox' name='id_{$row['email_id']}' value='1' /></center>",
				                                       "<a href='{$ADMIN->base_url}&act=emaillog&code=list&type=fromid&id={$row['id']}' title='Show all from this member'><img src='{$SKIN->img_url}/acp_search.gif' border='0' alt='..by id'></a>&nbsp;<b><a href='{$INFO['board_url']}/index.{$INFO['php_ext']}?act=Profile&MID={$row['id']}' title='Members profile (new window)' target='blank'>{$row['name']}</a></b>",
				                                       "<a href='javascript:pop_win(\"&act=emaillog&code=viewemail&id={$row['email_id']}\",400,400)' title='Read email'>{$row['email_subject']}</a>",
				                                       "<a href='{$ADMIN->base_url}&act=emaillog&code=list&type=toid&id={$row['to_id']}' title='Show all sent to this member'><img src='{$SKIN->img_url}/acp_search.gif' border='0' alt='..by id'></a>&nbsp;<a href='{$INFO['board_url']}/index.{$INFO['php_ext']}?act=Profile&MID={$row['to_id']}'  title='Members profile (new window)' target='blank'>{$row['to_name']}</a>",
				                                       "{$row['email_date']}",
				                                  ));

			}
		} else
		{
			$ADMIN->html .= $SKIN->add_td_basic("<center>No results</center>");
		}

		$ADMIN->html .= $SKIN->add_td_basic('<div style="float:left;width:auto"><input type="submit" value="Remove Checked" id="button" />&nbsp;<input type="checkbox" id="checkbox" name="type" value="all" />&nbsp;Remove all?</div><div align="right">' . $links . '</div></form>', 'left', 'pformstrip');

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------
		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'list'),
		                                       2 => array('act', 'emaillog'),
		                                  ));

		$SKIN->td_header[] = array("&nbsp;", "100%");

		$ADMIN->html .= $SKIN->start_table("Search Email Logs");

		$form_array = array(
			0 => array('subject', 'Email Subject'),
			1 => array('content', 'Email Body'),
			2 => array('email_from', 'From Email Address'),
			3 => array('email_to', 'To Email Address'),
			4 => array('name_from', 'From Member Name'),
			5 => array('name_to', 'To Member Name'),
		);

		$type_array = array(
			0 => array('exact', 'is exactly'),
			1 => array('loose', 'contains'),
		);

		//+-------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Search where</b> &nbsp;" . $SKIN->form_dropdown("type", $form_array) . " " . $SKIN->form_dropdown("match", $type_array) . " " . $SKIN->form_input("string"),

		                                  ));

		$ADMIN->html .= $SKIN->end_form("Search");

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------
		//+-------------------------------

		$ADMIN->nav[] = array('act=emaillog', 'Email Logs (Show all)');

		$ADMIN->output();

	}

}

?>
