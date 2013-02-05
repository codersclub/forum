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
|   > Admin Category functions
|   > Module written by Matt Mecham
|   > Date started: 1st march 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new ad_mod();

class ad_mod
{

	var $base_url;

	function ad_mod()
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

		switch ($IN['code'])
		{
			case 'add':
				$this->add_one();
				break;
			case 'add_two':
				$this->add_two();
				break;
			case 'add_final':
				$this->mod_form('add');
				break;
			case 'doadd':
				$this->add_mod();
				break;

			case 'edit':
				$this->mod_form('edit');
				break;

			case 'doedit':
				$this->do_edit();
				break;

			case 'remove':
				$this->do_delete();
				break;

			default:
				$this->show_list();
				break;
		}

	}

	//+---------------------------------------------------------------------------------
	//
	// DELETE MODERATOR
	//
	//+---------------------------------------------------------------------------------

	function do_delete()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		if ($IN['mid'] == "")
		{
			$ADMIN->error("You did not choose a valid moderator ID");
		}

		$stmt = $ibforums->db->query("SELECT * FROM ibf_moderators WHERE mid='" . $IN['mid'] . "'");
		$mod  = $stmt->fetch();

		if ($mod['is_group'])
		{
			$name = 'Group: ' . $mod['group_name'];
		} else
		{
			$name = $mod['member_name'];
		}

		$ibforums->db->exec("DELETE FROM ibf_moderators WHERE mid='" . $IN['mid'] . "'");

		$ADMIN->save_log("Removed Moderator '{$name}'");

		$ADMIN->done_screen("Moderator Removed", "Moderator Control", "act=mod");

	}

	//+---------------------------------------------------------------------------------
	//
	// EDIT MODERATOR
	//
	//+---------------------------------------------------------------------------------

	function do_edit()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		if ($IN['mid'] == "")
		{
			$ADMIN->error("You did not choose a valid moderator ID");
		}

		$stmt = $ibforums->db->query("SELECT member_name FROM ibf_moderators WHERE mid='" . $IN['mid'] . "'");
		$mod  = $stmt->fetch();

		//--------------------------------------
		// Build Mr Hash
		//--------------------------------------

		$mr_hash = array(
			'forum_id'     => $IN['forum_id'],
			'edit_post'    => $IN['edit_post'],
			'edit_topic'   => $IN['edit_topic'],
			'delete_post'  => $IN['delete_post'],
			'delete_topic' => $IN['delete_topic'],
			'view_ip'      => $IN['view_ip'],
			'open_topic'   => $IN['open_topic'],
			'close_topic'  => $IN['close_topic'],
			'mass_move'    => $IN['mass_move'],
			'mass_prune'   => $IN['mass_prune'],
			'move_topic'   => $IN['move_topic'],
			'pin_topic'    => $IN['pin_topic'],
			'unpin_topic'  => $IN['unpin_topic'],
			'post_q'       => $IN['post_q'],
			'topic_q'      => $IN['topic_q'],
			'allow_warn'   => $IN['allow_warn'],
			'split_merge'  => $IN['split_merge'],
			'edit_user'    => $IN['edit_user'],
			'can_mm'       => $IN['can_mm'],
			'can_attach'   => $IN['can_attach'],
			'can_pin_post' => $IN['can_pin_post'],
			'rules_edit'   => $IN['rules_edit'],
			'hide_topic'   => $IN['hide_topic'],
			'add_to_faq'   => $IN['add_to_faq'],
			'mirror_topic' => $IN['mirror_topic'],
		);

		$ibforums->db->updateRow("ibf_moderators", array_map([
		                                                     $ibforums->db,
		                                                     'quote'
		                                                     ], $mr_hash), "mid='" . $IN['mid'] . "'");

		$ADMIN->save_log("Edited Moderator '{$mod['member_name']}'");

		$ADMIN->done_screen("Moderator Edited", "Moderator Control", "act=mod");

	}

	//+---------------------------------------------------------------------------------
	//
	// ADD MODERATOR
	//
	//+---------------------------------------------------------------------------------

	function add_mod()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		if ($IN['fid'] == "")
		{
			$ADMIN->error("You did not choose any forums to add this member to");
		}

		//--------------------------------------
		// Build Mr Hash
		//--------------------------------------

		$mr_hash = array(
			'edit_post'    => $IN['edit_post'],
			'edit_topic'   => $IN['edit_topic'],
			'delete_post'  => $IN['delete_post'],
			'delete_topic' => $IN['delete_topic'],
			'view_ip'      => $IN['view_ip'],
			'open_topic'   => $IN['open_topic'],
			'close_topic'  => $IN['close_topic'],
			'mass_move'    => $IN['mass_move'],
			'mass_prune'   => $IN['mass_prune'],
			'move_topic'   => $IN['move_topic'],
			'pin_topic'    => $IN['pin_topic'],
			'unpin_topic'  => $IN['unpin_topic'],
			'post_q'       => $IN['post_q'],
			'topic_q'      => $IN['topic_q'],
			'allow_warn'   => $IN['allow_warn'],
			'split_merge'  => $IN['split_merge'],
			'edit_user'    => $IN['edit_user'],
			'can_mm'       => $IN['can_mm'],
			'can_attach'   => $IN['can_attach'],
			'can_pin_post' => $IN['can_pin_post'],
			'rules_edit'   => $IN['rules_edit'],
			'hide_topic'   => $IN['hide_topic'],
			'add_to_faq'   => $IN['add_to_faq'],
			'mirror_topic' => $IN['mirror_topic'],
		);

		$forum_ids = array();

		$stmt = $ibforums->db->query("SELECT id FROM ibf_forums WHERE id IN(" . $IN['fid'] . ")");

		while ($i = $stmt->fetch())
		{
			$forum_ids[$i['id']] = $i['id'];
		}

		//---------------------------------------

		if ($IN['mod_type'] == 'group')
		{

			if ($IN['gid'] == "")
			{
				$ADMIN->error("We could not match that group ID");
			}

			$stmt = $ibforums->db->query("SELECT g_id, g_title FROM ibf_groups WHERE g_id='" . $IN['gid'] . "'");

			if (!$group = $stmt->fetch())
			{
				$ADMIN->error("We could not match that group ID");
			}

			//---------------------------------------
			// Already using this group on this forum?
			//---------------------------------------

			$stmt = $ibforums->db->query("SELECT * FROM ibf_moderators WHERE forum_id IN(" . $IN['fid'] . ") and group_id={$IN['gid']}");

			while ($f = $stmt->fetch())
			{
				unset($forum_ids[$f['forum_id']]);
			}

			$mr_hash['member_name'] = '-1';
			$mr_hash['member_id']   = '-1';
			$mr_hash['group_id']    = $group['g_id'];
			$mr_hash['group_name']  = $group['g_title'];
			$mr_hash['is_group']    = 1;

			$ad_log = "Added Group '{$group['g_title']}' as a moderator";

		} else
		{

			if ($IN['mem'] == "")
			{
				$ADMIN->error("You did not choose a member to add as a moderator");
			}

			$stmt = $ibforums->db->query("SELECT id, name from ibf_members WHERE id='" . $IN['mem'] . "'");

			if (!$mem = $stmt->fetch())
			{
				$ADMIN->error("Could not match that member name so there.");
			}

			//---------------------------------------
			// Already using this member on this forum?
			//---------------------------------------

			$stmt = $ibforums->db->query("SELECT * FROM ibf_moderators WHERE forum_id IN(" . $IN['fid'] . ") and member_id={$IN['mem']}");

			while ($f = $stmt->fetch())
			{
				unset($forum_ids[$f['forum_id']]);
			}

			$mr_hash['member_name'] = $mem['name'];
			$mr_hash['member_id']   = $mem['id'];
			$mr_hash['is_group']    = 0;

			$ad_log = "Added Member '{$mem['name']}' as a moderator";

		}

		//--------------------------------------
		// Check for legal forums
		//--------------------------------------

		if (count($forum_ids) == 0)
		{
			$ADMIN->error("You did not select any forums that do not have this group or member already moderating.");
		}

		//--------------------------------------
		// Loopy loopy
		//--------------------------------------

		foreach ($forum_ids as $cartman)
		{
			$mr_hash['forum_id'] = $cartman;

			$ibforums->db->insertRow("ibf_moderators", $mr_hash);
		}

		$ADMIN->save_log($ad_log);

		$ADMIN->done_screen("Moderator Added", "Moderator Control", "act=mod");

	}

	//+---------------------------------------------------------------------------------
	//
	// ADD FINAL, display the add / edit form
	//
	//+---------------------------------------------------------------------------------

	function mod_form($type = 'add')
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$group = array();

		if ($type == 'add')
		{
			if ($IN['fid'] == "")
			{
				$ADMIN->error("You did not choose any forums to add this member to");
			}

			$mod   = array();
			$names = array();

			//--------------------------------------

			$stmt = $ibforums->db->query("SELECT name FROM ibf_forums WHERE id IN(" . $IN['fid'] . ")");

			while ($r = $stmt->fetch())
			{
				$names[] = $r['name'];
			}

			$thenames = implode(", ", $names);

			//--------------------------------------

			$button = "Add this moderator";

			$form_code = 'doadd';

			if ($IN['mod_type'] == 'group')
			{
				$stmt = $ibforums->db->query("SELECT g_id, g_title FROM ibf_groups WHERE g_id='" . $IN['mod_group'] . "'");

				if (!$group = $stmt->fetch())
				{
					$ADMIN->error("Could not find that group to add as a moderator");
				}

				$ADMIN->page_detail = "Adding <b>group: {$group['g_title']}</b> as a moderator to: $thenames";
				$ADMIN->page_title  = "Add a moderator group";
			} else
			{

				if ($IN['MEMBER_ID'] == "")
				{
					$ADMIN->error("Could not resolve the member id bucko");
				} else
				{
					$stmt = $ibforums->db->query("SELECT name, id FROM ibf_members WHERE id='" . $IN['MEMBER_ID'] . "'");

					if (!$mem = $stmt->fetch())
					{
						$ADMIN->error("That member ID does not resolve");
					}

					$member_id   = $mem['id'];
					$member_name = $mem['name'];
				}

				$ADMIN->page_detail = "Adding a $member_name as a moderator to: $thenames";
				$ADMIN->page_title  = "Add a moderator";

			}

		} else
		{
			if ($IN['mid'] == "")
			{
				$ADMIN->error("You must choose a valid moderator to edit.");
			}

			$button = "Edit this moderator";

			$form_code = "doedit";

			$ADMIN->page_title  = "Editing a moderator";
			$ADMIN->page_detail = "Please check the information carefully before submitting the form";

			$stmt = $ibforums->db->query("SELECT * from ibf_moderators WHERE mid='" . $IN['mid'] . "'");

			if (!$mod = $stmt->fetch())
			{
				$ADMIN->error("Could not retrieve that moderators record");
			}

			$member_id   = $mod['member_id'];
			$member_name = $mod['member_name'];
		}

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', $form_code),
		                                       2 => array('act', 'mod'),
		                                       3 => array('mid', $mod['mid']),
		                                       4 => array('fid', $IN['fid']),
		                                       5 => array('mem', $member_id),
		                                       6 => array('mod_type', $IN['mod_type']),
		                                       7 => array('gid', $group['g_id']),
		                                       8 => array('gname', $group['g_name']),
		                                  ));

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("General Settings");

		//+-------------------------------

		if ($type == 'edit')
		{
			$forums = array();

			$stmt = $ibforums->db->query("SELECT id, name FROM ibf_forums ORDER BY position");

			while ($r = $stmt->fetch())
			{
				$forums[] = array($r['id'], $r['name']);
			}

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Moderates forum...</b>",
			                                       $SKIN->form_dropdown("forum_id", $forums, $mod['forum_id'])
			                                  ));
		}

		//+-------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Can edit others posts/polls?</b>",
		                                       $SKIN->form_yes_no("edit_post", $mod['edit_post'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Can edit others topic titles?</b>",
		                                       $SKIN->form_yes_no("edit_topic", $mod['edit_topic'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Can delete others posts?</b>",
		                                       $SKIN->form_yes_no("delete_post", $mod['delete_post'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Can delete others topics/polls?</b>",
		                                       $SKIN->form_yes_no("delete_topic", $mod['delete_topic'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Can view posters IP addresses?</b>",
		                                       $SKIN->form_yes_no("view_ip", $mod['view_ip'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Can open locked topics?</b>",
		                                       $SKIN->form_yes_no("open_topic", $mod['open_topic'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Can close open topics?</b>",
		                                       $SKIN->form_yes_no("close_topic", $mod['close_topic'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Can move topics?</b>",
		                                       $SKIN->form_yes_no("move_topic", $mod['move_topic'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Can pin topics?</b>",
		                                       $SKIN->form_yes_no("pin_topic", $mod['pin_topic'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Can unpin topics?</b>",
		                                       $SKIN->form_yes_no("unpin_topic", $mod['unpin_topic'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Can split / merge topics?</b>",
		                                       $SKIN->form_yes_no("split_merge", $mod['split_merge'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Can hide topics?</b>",
		                                       $SKIN->form_yes_no("hide_topic", $mod['hide_topic'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Can attach links?</b>",
		                                       $SKIN->form_yes_no("can_attach", $mod['can_attach'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Can pin posts?</b>",
		                                       $SKIN->form_yes_no("can_pin_post", $mod['can_pin_post'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Can edit forum rules?</b>",
		                                       $SKIN->form_yes_no("rules_edit", $mod['rules_edit'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Can copy posts to FAQ?</b>",
		                                       $SKIN->form_yes_no("add_to_faq", $mod['add_to_faq'])
		                                  ));
		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Can mirror topics?</b>",
		                                       $SKIN->form_yes_no("mirror_topic", $mod['mirror_topic'])
		                                  ));

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Moderator Control Panel Settings");

		//+-------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Can mass move topics?</b>",
		                                       $SKIN->form_yes_no("mass_move", $mod['mass_move'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Can mass prune topics?</b>",
		                                       $SKIN->form_yes_no("mass_prune", $mod['mass_prune'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Can manage queued topics?</b>",
		                                       $SKIN->form_yes_no("topic_q", $mod['topic_q'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Can manage queued posts?</b>",
		                                       $SKIN->form_yes_no("post_q", $mod['post_q'])
		                                  ));

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Advanced Settings");

		//+-------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Can warn other users?</b>",
		                                       $SKIN->form_yes_no("allow_warn", $mod['allow_warn'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Can edit user avatars and signatures?</b>",
		                                       $SKIN->form_yes_no("edit_user", $mod['edit_user'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Can use topic multi-moderation?</b><br>" . $SKIN->js_help_link('mod_mmod', 'Important Information'),
		                                       $SKIN->form_yes_no("can_mm", $mod['can_mm'])
		                                  ));

		//+-------------------------------

		$ADMIN->html .= $SKIN->end_form($button);

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	//+---------------------------------------------------------------------------------
	//
	// ADD step one: Look up a member
	//
	//+---------------------------------------------------------------------------------

	function add_one()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		//-----------------------------
		// Grab and serialize the input
		//-----------------------------

		$fid      = "";
		$fidarray = array();

		foreach ($IN as $k => $v)
		{
			if (preg_match("/^add_(\d+)$/", $k, $match))
			{
				if ($IN[$match[0]])
				{
					$fidarray[] = $match[1];
				}
			}
		}

		if (count($fidarray) < 1)
		{
			$ADMIN->error("You must select a forum, or forums to add a moderator to. You can do this by checking the checkboxes to the left of the forum name");
		}

		$fid = implode(",", $fidarray);

		$ADMIN->page_title = "Add a moderator";

		$ADMIN->page_detail = "Please find a member or group to moderate the forums you previously selected.";

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'add_two'),
		                                       2 => array('act', 'mod'),
		                                       3 => array('fid', $fid),
		                                       4 => array('mod_type', $IN['mod_type']),
		                                  ));

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		//+-------------------------------

		if ($IN['mod_type'] == 'member')
		{

			$ADMIN->html .= $SKIN->start_table("Search for a member");

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Enter part or all of the usersname</b>",
			                                       $SKIN->form_input("USER_NAME")
			                                  ));

			$ADMIN->html .= $SKIN->end_form("Find Member");

			$ADMIN->html .= $SKIN->end_table();

		} else
		{
			// Get the group ID's and names

			$mem_group = array();

			$stmt = $ibforums->db->query("SELECT g_id, g_title FROM ibf_groups ORDER BY g_title");

			while ($r = $stmt->fetch())
			{
				$mem_group[] = array($r['g_id'], $r['g_title']);
			}

			$ADMIN->html .= $SKIN->start_table("Choose a group as a moderator");

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Select a group</b>",
			                                       $SKIN->form_dropdown("mod_group", $mem_group)
			                                  ));

			$ADMIN->html .= $SKIN->end_form("Add this group");

			$ADMIN->html .= $SKIN->end_table();

		}

		$ADMIN->output();

	}

	//+---------------------------------------------------------------------------------
	//
	// REFINE MEMBER SEARCH
	//
	//+---------------------------------------------------------------------------------

	function add_two()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		// Are we adding a group as a mod? If so, bounce straight to the mod perms form

		if ($IN['mod_type'] == 'group')
		{
			$this->mod_form();
			exit();
		}

		// Else continue as normal.

		if ($IN['USER_NAME'] == "")
		{
			$ADMIN->error("You didn't choose a member name to look for!");
		}

		$stmt = $ibforums->db->query("SELECT id, name FROM ibf_members WHERE name LIKE '" . $IN['USER_NAME'] . "%'");

		if (!$stmt->rowCount())
		{
			$ADMIN->error("Sorry, we could not find any members that matched the search string you entered");
		}

		$form_array = array();

		while ($r = $stmt->fetch())
		{
			$form_array[] = array($r['id'], $r['name']);
		}

		$ADMIN->page_title = "Add a moderator";

		$ADMIN->page_detail = "Please select the correct member name from the selection below to add as a moderator to the previously selected forums.";

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'add_final'),
		                                       2 => array('act', 'mod'),
		                                       3 => array('fid', $IN['fid']),
		                                  ));

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Search for a member");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Choose from the matches...</b>",
		                                       $SKIN->form_dropdown("MEMBER_ID", $form_array)
		                                  ));

		$ADMIN->html .= $SKIN->end_form("Choose Member");

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	//+---------------------------------------------------------------------------------
	//
	// SHOW LIST
	// Renders a complete listing of all the forums and categories w/mods.
	//
	//+---------------------------------------------------------------------------------

	function show_list()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$ADMIN->page_title  = "Moderator Control Overview";
		$ADMIN->page_detail = "This section allows you to edit, remove and add new moderators to your forums";

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'add'),
		                                       2 => array('act', 'mod'),
		                                  ));

		$SKIN->td_header[] = array("Add", "5%");
		$SKIN->td_header[] = array("Forum Name", "40%");
		$SKIN->td_header[] = array("Current Moderators", "55%");

		$ADMIN->html .= $SKIN->start_table("Your Categories and Forums");

		//------------------------------------

		$cats     = array();
		$forums   = array();
		$mods     = array();
		$children = array();

		$stmt = $ibforums->db->query("SELECT * from ibf_categories where id > 0 ORDER BY position ASC");
		while ($r = $stmt->fetch())
		{
			$cats[$r['id']] = $r;
		}

		$stmt = $ibforums->db->query("SELECT * from ibf_forums ORDER BY position ASC");
		while ($r = $stmt->fetch())
		{

			if ($r['parent_id'] > 0)
			{
				$children[$r['parent_id']][] = $r;
			} else
			{
				$forums[] = $r;
			}

		}

		$stmt = $ibforums->db->query("SELECT * from ibf_moderators");
		while ($r = $stmt->fetch())
		{
			$mods[] = $r;
		}

		//------------------------------------

		$last_cat_id = -1;

		foreach ($cats as $c)
		{

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       '&nbsp;',
			                                       "<a href='{$ADMIN->base_url}&act=cat&code=doeditform&c={$c['id']}'>" . $c['name'] . "</a>",
			                                       '&nbsp;',
			                                  ), 'pformstrip');
			$last_cat_id = $c['id'];

			foreach ($forums as $r)
			{

				if ($r['category'] == $last_cat_id)
				{
					$mod_string = "";

					foreach ($mods as $phpid => $data)
					{
						if ($data['forum_id'] == $r['id'])
						{
							if ($data['is_group'] == 1)
							{
								$mod_string .= "<tr>
											 <td width='60%'>Group: {$data['group_name']}</td>
											 <td width='20%'><a href='{$ADMIN->base_url}&act=mod&code=remove&mid={$data['mid']}'>Remove</a></td>
											 <td width='20%'><a href='{$ADMIN->base_url}&act=mod&code=edit&mid={$data['mid']}'>Edit</a></td>
										</tr>";
							} else
							{
								$mod_string .= "<tr>
											 <td width='60%'>{$data['member_name']}</td>
											 <td width='20%'><a href='{$ADMIN->base_url}&act=mod&code=remove&mid={$data['mid']}'>Remove</a></td>
											 <td width='20%'><a href='{$ADMIN->base_url}&act=mod&code=edit&mid={$data['mid']}'>Edit</a></td>
										</tr>";
							}
						}
					}

					if ($mod_string != "")
					{
						$these_mods = "<table cellpadding='3' cellspacing='0' width='100%' align='center'>" . $mod_string . "</table>";
					} else
					{
						$these_mods = "<center><i>Unmoderated</i></center>";
					}

					if ($r['subwrap'] == 1 and $r['sub_can_post'] != 1)
					{
						$ADMIN->html .= $SKIN->add_td_row(array(
						                                       "<center><input type='checkbox' name='add_{$r['id']}' value='1'></center>",
						                                       " - <b>" . $r['name'] . "</b>",
						                                       $these_mods
						                                  ),

							"subforum");
					} else
					{
						$css = $r['subwrap'] == 1
							? 'subforum'
							: '';

						$ADMIN->html .= $SKIN->add_td_row(array(
						                                       "<center><input type='checkbox' name='add_{$r['id']}' value='1'></center>",
						                                       "<b>" . $r['name'] . "</b>",
						                                       $these_mods
						                                  ),

							$css);
					}

					// Song * infinite subforums, 17.12.04
					$this->subforums_addtorow($children, $r['id'], 0, $mods);
					// Song * infinite subforums, 17.12.04

				}
			}
		}

		$ADMIN->html .= $SKIN->add_td_basic("<b>Type of moderator to add:</b> &nbsp;" . $SKIN->form_dropdown("mod_type", array(
		                                                                                                                      0 => array(
			                                                                                                                      'member',
			                                                                                                                      'Single Member'
		                                                                                                                      ),
		                                                                                                                      1 => array(
			                                                                                                                      'group',
			                                                                                                                      'Member Group'
		                                                                                                                      )
		                                                                                                                 )), "center");

		$ADMIN->html .= $SKIN->end_form("Add a moderator to the selected forums");

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	function subforums_addtorow($children, $id, $level, $mods)
	{
		global $ADMIN, $SKIN;
		$ibforums = Ibf::instance();

		if (!(isset($children[$id])) || (count($children[$id]) <= 0))
		{
			return;
		}

		foreach ($children[$id] as $idx => $rd)
		{

			$mod_string = "";

			foreach ($mods as $phpid => $data)
			{
				if ($data['forum_id'] == $rd['id'])
				{
					if ($data['is_group'] == 1)
					{
						$mod_string .= "<tr>
					<td width='60%'>Group: {$data['group_name']}</td>
					<td width='20%'><a href='{$ADMIN->base_url}&act=mod&code=remove&mid={$data['mid']}'>Remove</a></td>
					<td width='20%'><a href='{$ADMIN->base_url}&act=mod&code=edit&mid={$data['mid']}'>Edit</a></td>
					</tr>";
					} else
					{
						$mod_string .= "<tr>
					<td width='60%'>{$data['member_name']}</td>
					<td width='20%'><a href='{$ADMIN->base_url}&act=mod&code=remove&mid={$data['mid']}'>Remove</a></td>
					<td width='20%'><a href='{$ADMIN->base_url}&act=mod&code=edit&mid={$data['mid']}'>Edit</a></td>
					</tr>";
					}
				}
			}

			if ($mod_string != "")
			{
				$these_mods = "<table cellpadding='3' cellspacing='0' width='100%' align='center'>" . $mod_string . "</table>";
			} else
			{
				$these_mods = "<center><i>Unmoderated</i></center>";
			}

			$t_level_char = "+--";

			for ($i = 0; $i < $level; $i++)
			{
				$t_level_char .= "--";
			}

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<center><input type='checkbox' name='add_{$rd['id']}' value='1'></center>",
			                                       " " . $t_level_char . " <b>" . $rd['name'] . "</b>",
			                                       $these_mods
			                                  ), 'subforum');

			$this->subforums_addtorow($children, $rd['id'], $level + 1, $mods);
		}

	}

}

?>
