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
|   > Import functions
|   > Module written by Matt Mecham
|   > Date started: 22nd April 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

// Ensure we've not accessed this script directly:

$idx = new ad_modlogs();

class ad_modlogs
{
	var $base_url;

	function __construct()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

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

			case 'view':
				$this->view();
				break;

			case 'remove':
				$this->remove();
				break;

			//-------------------------
			default:
				$this->list_current();
				break;
		}

	}

	//---------------------------------------------
	// Remove archived files
	//---------------------------------------------

	function view()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$start = $IN['st']
			? $IN['st']
			: 0;

		$ADMIN->page_detail = "Viewing all actions by a moderator";
		$ADMIN->page_title  = "Moderator Logs Manager";

		if ($IN['search_string'] == "")
		{
			$stmt = $ibforums->db->query("SELECT COUNT(id) as count FROM ibf_moderator_logs WHERE member_id='" . $IN['mid'] . "'");
			$row  = $stmt->fetch();

			$row_count = $row['count'];

			$query = "&act=modlog&mid={$IN['mid']}&code=view";

			$stmt = $ibforums->db->query("SELECT m.*, f.id as forum_id, f.name
				    FROM ibf_moderator_logs m
				     LEFT JOIN ibf_forums f ON(f.id=m.forum_id)
				    WHERE m.member_id='" . $IN['mid'] . "'
				    ORDER BY m.ctime DESC
				    LIMIT $start, 20");

		} else
		{
			$IN['search_string'] = urldecode($IN['search_string']);

			if (($IN['search_type'] == 'topic_id') or ($IN['search_type'] == 'forum_id'))
			{
				$dbq = "m." . $IN['search_type'] . "='" . $IN['search_string'] . "'";
			} else
			{
				$dbq = "m." . $IN['search_type'] . " LIKE '%" . $IN['search_string'] . "%'";
			}

			$stmt = $ibforums->db->query("SELECT COUNT(m.id) as count FROM ibf_moderator_logs m WHERE $dbq");
			$row  = $stmt->fetch();

			$row_count = $row['count'];

			$query = "&act=modlog&code=view&search_type={$IN['search_type']}&search_string=" . urlencode($IN['search_string']);

			$stmt = $ibforums->db->query("SELECT m.*, f.id as forum_id, f.name
				    FROM ibf_moderator_logs m
					    LEFT JOIN ibf_forums f ON(f.id=m.forum_id)
				    WHERE $dbq
				    ORDER BY m.ctime DESC
				    LIMIT $start, 20");

		}

		$links = $std->build_pagelinks(array(
		                                    'TOTAL_POSS' => $row_count,
		                                    'PER_PAGE'   => 20,
		                                    'CUR_ST_VAL' => $start,
		                                    'L_SINGLE'   => "Single Page",
		                                    'L_MULTI'    => "Pages: ",
		                                    'BASE_URL'   => $ADMIN->base_url . $query,
		                               ));

		$ADMIN->page_detail = "You may view and remove actions performed by your moderators";
		$ADMIN->page_title  = "Moderator Logs Manager";

		//+-------------------------------

		$SKIN->td_header[] = array("Member Name", "15%");
		$SKIN->td_header[] = array("Action Perfomed", "15%");
		$SKIN->td_header[] = array("Forum", "15%");
		$SKIN->td_header[] = array("Topic Title", "25%");
		$SKIN->td_header[] = array("Time of action", "20%");
		$SKIN->td_header[] = array("IP address", "10%");

		$ADMIN->html .= $SKIN->start_table("Saved Moderator Logs");
		$ADMIN->html .= $SKIN->add_td_basic($links, 'center', 'catrow');

		if ($stmt->rowCount())
		{
			while ($row = $stmt->fetch())
			{

				$row['ctime'] = $ADMIN->get_date($row['ctime'], 'LONG');

				if ($row['topic_id'])
				{
					$topicid = "<br />Topic ID: " . $row['topic_id'];
				}

				$sess_id             = preg_replace("/^.+?s=(\w{32}).+?$/", "\\1", $row['http_referer']);
				$row['http_referer'] = preg_replace("/s=(\w){32}/", "", $row['http_referer']);

				$ADMIN->html .= $SKIN->add_td_row(array(
				                                       "<b>{$row['member_name']}</b>",
				                                       "<span style='font-weight:bold;color:red'>{$row['action']}</span>",
				                                       "<b>{$row['name']}</b>",
				                                       "{$row['topic_title']}" . $topicid,
				                                       "{$row['ctime']}",
				                                       "{$row['ip_address']}",
				                                  ));

			}
		} else
		{
			$ADMIN->html .= $SKIN->add_td_basic("<center>No results</center>");
		}

		$ADMIN->html .= $SKIN->add_td_basic($links, 'center', 'tdtop');

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------
		//+-------------------------------

		$ADMIN->output();

	}

	//---------------------------------------------
	// Remove archived files
	//---------------------------------------------

	function remove()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		if ($IN['mid'] == "")
		{
			$ADMIN->error("You did not select a member ID to remove by!");
		}

		$ibforums->db->exec("DELETE FROM ibf_moderator_logs WHERE member_id='" . $IN['mid'] . "'");

		$ADMIN->save_log("Removed Moderator Logs");

		$std->boink_it($ADMIN->base_url . "&act=modlog");
		exit();

	}

	//-------------------------------------------------------------
	// SHOW ALL LANGUAGE PACKS
	//-------------------------------------------------------------

	function list_current()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$form_array = array();

		$ADMIN->page_detail = "You may view and remove actions performed by your moderators";
		$ADMIN->page_title  = "Moderator Logs Manager";

		//+-------------------------------
		// VIEW LAST 5
		//+-------------------------------

		$stmt = $ibforums->db->query("SELECT m.*, f.id as forum_id, f.name
			    FROM ibf_moderator_logs m
		            LEFT JOIN ibf_forums f ON (f.id=m.forum_id)
		            ORDER BY m.ctime DESC
			    LIMIT 0, 5");

		$SKIN->td_header[] = array("Member Name", "15%");
		$SKIN->td_header[] = array("Action Perfomed", "15%");
		$SKIN->td_header[] = array("Forum", "15%");
		$SKIN->td_header[] = array("Topic Title", "25%");
		$SKIN->td_header[] = array("Time of action", "20%");
		$SKIN->td_header[] = array("IP address", "10%");

		$ADMIN->html .= $SKIN->start_table("Last 5 Moderation Actions");

		if ($stmt->rowCount())
		{
			while ($row = $stmt->fetch())
			{

				$row['ctime'] = $ADMIN->get_date($row['ctime'], 'LONG');

				$topicid = "";

				if ($row['topic_id'])
				{
					$topicid = "<br />Topic ID: " . $row['topic_id'];
				}

				$sess_id             = preg_replace("/^.+?s=(\w{32}).+?$/", "\\1", $row['http_referer']);
				$row['http_referer'] = preg_replace("/s=(\w){32}/", "", $row['http_referer']);

				$ADMIN->html .= $SKIN->add_td_row(array(
				                                       "<b>{$row['member_name']}</b>",
				                                       "<span style='font-weight:bold;color:red'>{$row['action']}</span>",
				                                       "<b>{$row['name']}</b>",
				                                       "{$row['topic_title']}" . $topicid,
				                                       "{$row['ctime']}",
				                                       "{$row['ip_address']}",
				                                  ));

			}
		} else
		{
			$ADMIN->html .= $SKIN->add_td_basic("<center>No results</center>");
		}

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------

		$SKIN->td_header[] = array("Member Name", "30%");
		$SKIN->td_header[] = array("Actions Perfomed", "20%");
		$SKIN->td_header[] = array("View all by member", "20%");
		$SKIN->td_header[] = array("Remove all by member", "30%");

		$ADMIN->html .= $SKIN->start_table("Saved Moderator Logs");

		$stmt = $ibforums->db->query("SELECT m.*, count(m.id) as act_count from ibf_moderator_logs m GROUP BY m.member_id ORDER BY act_count DESC");

		while ($r = $stmt->fetch())
		{

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>{$r['member_name']}</b>",
			                                       "<center>{$r['act_count']}</center>",
			                                       "<center><a href='" . $SKIN->base_url . "&act=modlog&code=view&mid={$r['member_id']}'>View</a></center>",
			                                       "<center><a href='" . $SKIN->base_url . "&act=modlog&code=remove&mid={$r['member_id']}'>Remove</a></center>",
			                                  ));
		}

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------
		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'view'),
		                                       2 => array('act', 'modlog'),
		                                  ));

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		$ADMIN->html .= $SKIN->start_table("Search Moderator Logs");

		$form_array = array(
			0 => array('topic_title', 'Topic Title'),
			1 => array('ip_address', 'IP Address'),
			2 => array('member_name', 'Member Name'),
			3 => array('topic_id', 'Topic ID'),
			4 => array('forum_id', 'Forum ID')
		);

		//+-------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Search for...</b>",
		                                       $SKIN->form_input("search_string")
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Search in...</b>",
		                                       $SKIN->form_dropdown("search_type", $form_array)
		                                  ));

		$ADMIN->html .= $SKIN->end_form("Search");

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------
		//+-------------------------------

		$ADMIN->output();

	}

}

?>
