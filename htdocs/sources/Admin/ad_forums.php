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

class ad_forums
{

	var $base_url;

	function ad_forums()
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
			case 'new':
				$this->new_form();
				break;
			case 'donew':
				$this->do_new();
				break;
			//+-------------------------
			case 'edit':
				$this->edit_form();
				break;
			case 'doedit':
				$this->do_edit();
				break;
			//+-------------------------
			case 'pedit':
				$this->perm_edit_form();
				break;
			case 'pdoedit':
				$this->perm_do_edit();
				break;
			//+-------------------------
			case 'reorder':
				$this->reorder_form();
				break;
			case 'doreorder':
				$this->do_reorder();
				break;
			//+-------------------------
			case 'delete':
				$this->delete_form();
				break;
			case 'dodelete':
				$this->do_delete();
				break;
			//+-------------------------
			case 'recount':
				$this->recount();
				break;
			//+-------------------------
			case 'empty':
				$this->empty_form();
				break;
			case 'doempty':
				$this->do_empty();
				break;
			//+-------------------------
			case 'frules':
				$this->show_rules();
				break;
			case 'dorules':
				$this->do_rules();
				break;
			//+-------------------------
			case 'newsp':
				$this->new_form();
				break;
			case 'donewsplash':
				$this->donew_splash();
				break;
			case 'donewsub':
				$this->add_sub();
				break;
			//+-------------------------
			case 'subedit':
				$this->subedit();
				break;
			case 'doeditsub':
				$this->doeditsub();
				break;

			case 'subdelete':
				$this->subdeleteform();
				break;
			case 'dosubdelete':
				$this->dosubdelete();
				break;
			//+-------------------------
			case 'skinedit':
				$this->skin_edit();
				break;
			case 'doskinedit':
				$this->do_skin_edit();
				break;
			//+-------------------------
			default:
				$this->new_form();
				break;
		}

	}

	//+---------------------------------------------------------------------------------
	//
	// Edit forum skins
	//
	//+---------------------------------------------------------------------------------

	function skin_edit()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		if ($IN['f'] == "")
		{
			$ADMIN->error("Could not determine the forum ID to empty.");
		}

		$stmt = $ibforums->db->query("SELECT id, name, skin_id FROM ibf_forums WHERE id='" . $IN['f'] . "'");

		//+-------------------------------
		// Make sure we have a legal forum
		//+-------------------------------

		if (!$stmt->rowCount())
		{
			$ADMIN->error("Could not resolve that forum ID");
		}

		$forum = $stmt->fetch();

		if (($forum['skin_id'] == "") or ($forum['skin_id'] == -1))
		{
			$forum['skin_id'] = 'n';
		}

		$form_array = array();

		$form_array[] = array('n', '-- NONE --');

		$stmt = $ibforums->db->query("SELECT sid, sname FROM ibf_skins");

		while ($r = $stmt->fetch())
		{
			$form_array[] = array($r['sid'], $r['sname']);
		}

		//+-------------------------------

		$ADMIN->page_title  = "Forum Skin Options";
		$ADMIN->page_detail = "You may choose to either add or remove a skin set to this forum. The skin choice will override the users choice.";

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'doskinedit'),
		                                       2 => array('act', 'forum'),
		                                       3 => array('f', $IN['f']),
		                                  ));

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		$ADMIN->html .= $SKIN->start_table("Skin choices for forum: {$forum['name']}");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Apply which skin?</b>",
		                                       $SKIN->form_dropdown("fsid", $form_array, $forum['skin_id'])
		                                  ));

		$ADMIN->html .= $SKIN->end_form("Edit forum skin options");

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	function do_skin_edit()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		if ($IN['f'] == "")
		{
			$ADMIN->error("Could not determine the forum ID to empty.");
		}

		$stmt = $ibforums->db->query("SELECT id, name, skin_id FROM ibf_forums WHERE id='" . $IN['f'] . "'");

		$forum = $stmt->fetch();

		//+-------------------------------
		// Make sure we have a legal forum
		//+-------------------------------

		if (!$stmt->rowCount())
		{
			$ADMIN->error("Could not resolve that forum ID");
		}

		if ($IN['fsid'] == 'n')
		{
			$ibforums->db->exec("UPDATE ibf_forums SET skin_id='-1' WHERE id='" . $IN['f'] . "'");
			$ADMIN->rebuild_config(array('forum_skin_' . $IN['f'] => ""));
		} else
		{
			$ibforums->db->exec("UPDATE ibf_forums SET skin_id='" . $IN['fsid'] . "' WHERE id='" . $IN['f'] . "'");
			$ADMIN->rebuild_config(array('forum_skin_' . $IN['f'] => $IN['fsid']));
		}

		$ADMIN->save_log("Edited a skin setting for forum '{$forum['name']}'");

		$std->boink_it($ADMIN->base_url . "&act=cat");
		exit();

	}

	//+---------------------------------------------------------------------------------
	//
	// Sub Cat Delete Form
	//
	//+---------------------------------------------------------------------------------

	function subdeleteform()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$form_array = array();

		if ($IN['f'] == "")
		{
			$ADMIN->error("Could not determine the forum ID to delete.");
		}

		$cats = array();

		$name = "";

		$last_cat_id = -1;

		$stmt = $ibforums->db->query("SELECT c.id, c.name, f.id as forum_id, f.subwrap, f.name as forum_name, f.parent_id, f.category FROM ibf_categories c, ibf_forums f WHERE c.id > 0 ORDER BY c.position, f.position");

		while ($r = $stmt->fetch())
		{

			if ($last_cat_id != $r['id'])
			{
				$cats[] = array("c_" . $r['id'], "Category: " . $r['name']);

				$last_cat_id = $r['id'];
			}

			if ($r['parent_id'] > 0)
			{
				continue;
			}

			if ($r['forum_id'] == $IN['f'])
			{
				$name = $r['forum_name'];
				continue;
			}

			if ($r['subwrap'] != 1)
			{
				continue;
			}

			if ($r['category'] == $r['id'])
			{

				$cats[] = array("f_" . $r['forum_id'], "Sub Category Forum: " . $r['forum_name']);
			}

		}

		//+-------------------------------
		// Make sure we have more than 1
		// forum..
		//+-------------------------------

		if ($stmt->rowCount() < 2)
		{
			$ADMIN->error("Can not remove this forum, please create another category or sub cat forum before attempting to remove this one");
		}

		//+-------------------------------

		$ADMIN->page_title = "Removing Sub Category forum '$name'";

		$ADMIN->page_detail = "Before we remove this forum, we need to determine what to do with any sub forums you have in the sub category.";

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'dosubdelete'),
		                                       2 => array('act', 'forum'),
		                                       3 => array('f', $IN['f']),
		                                       4 => array('name', $name),
		                                  ));

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Required");

		$ADMIN->html .= $SKIN->add_td_row(array("<b>Forum to remove: </b>", $name));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Move all <i>existing forums</i> to which parent?</b>",
		                                       $SKIN->form_dropdown("MOVE_ID", $cats)
		                                  ));

		$ADMIN->html .= $SKIN->end_form("Move sub forums and remove this forum");

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	function dosubdelete()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		if ($IN['f'] == "")
		{
			$ADMIN->error("Could not determine the source forum ID.");
		}

		if ($IN['MOVE_ID'] == "")
		{
			$ADMIN->error("Could not determine the destination parent ID.");
		}

		$cat    = -1;
		$parent = -1;

		if (preg_match("/^c_(\d+)$/", $IN['MOVE_ID'], $match))
		{
			$cat = $match[1];
		} else
		{
			$parent = preg_replace("/^f_/", "", $IN['MOVE_ID']);
		}

		// Move sub forums...

		$ibforums->db->exec("UPDATE ibf_forums SET category='$cat', parent_id='$parent' WHERE parent_id='" . $IN['f'] . "'");

		$ibforums->db->exec("DELETE FROM ibf_forums WHERE id='" . $IN['f'] . "'");

		// Delete any moderators, if any..

		$ibforums->db->exec("DELETE FROM ibf_moderators WHERE forum_id='" . $IN['f'] . "'");

		$ADMIN->save_log("Removed sub-forum '{$IN['name']}'");

		$ADMIN->done_screen("Forum Removed", "Forum Control", "act=cat");

	}

	//+---------------------------------------------------------------------------------
	//
	// Show forum rules
	//
	//+---------------------------------------------------------------------------------

	function show_rules()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		if ($IN['f'] == "")
		{
			$ADMIN->error("Could not determine the forum ID to empty.");
		}

		$stmt = $ibforums->db->query("SELECT id, name, show_rules, rules_title, rules_text FROM ibf_forums WHERE id='" . $IN['f'] . "'");

		//+-------------------------------
		// Make sure we have a legal forum
		//+-------------------------------

		if (!$stmt->rowCount())
		{
			$ADMIN->error("Could not resolve that forum ID");
		}

		$forum = $stmt->fetch();

		//+-------------------------------

		$ADMIN->page_title  = "Forum Rules";
		$ADMIN->page_detail = "You may edit, add, remove or change the state of the forum rules display";

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'dorules'),
		                                       2 => array('act', 'forum'),
		                                       3 => array('f', $IN['f']),
		                                  ));

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		$ADMIN->html .= $SKIN->start_table("Forum Rules set up");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Display method</b>",
		                                       $SKIN->form_dropdown("show_rules", array(
		                                                                               0 => array('0', 'Don\'t Show'),
		                                                                               1 => array(
			                                                                               '1',
			                                                                               'Show Link Only'
		                                                                               ),
		                                                                               2 => array('2', 'Show full text')
		                                                                          ), $forum['show_rules'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Rules Title</b>",
		                                       $SKIN->form_input("title", $std->txt_stripslashes(str_replace("'", '&#039;', $forum['rules_title'])))
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Rules Text</b><br>(HTML Editing Mode)",
		                                       $SKIN->form_textarea("body", $std->txt_stripslashes($forum['rules_text']), 65, 20)
		                                  ));

		$ADMIN->html .= $SKIN->end_form("Edit forum rules");

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	function do_rules()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		if ($IN['f'] == "")
		{
			$ADMIN->error("Could not determine the forum ID to empty.");
		}

		$rules = array(
			'rules_title' => $ADMIN->make_safe($std->txt_stripslashes($_POST['title'])),
			'rules_text'  => $ADMIN->make_safe($std->txt_stripslashes($_POST['body'])),
			'show_rules'  => $IN['show_rules']
		);

		$ibforums->db->updateRow("ibf_forums", array_map([$ibforums->db, 'quote'], $rules), "id='" . $IN['f'] . "'");

		$ADMIN->done_screen("Forum Rules Updated", "Forum Control", "act=cat");

	}

	//+---------------------------------------------------------------------------------
	//
	// RECOUNT FORUM: Recounts topics and posts
	//
	//+---------------------------------------------------------------------------------

	function recount($f_override = "")
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		if ($f_override != "")
		{
			// Internal call, remap

			$IN['f'] = $f_override;
		}

		$stmt  = $ibforums->db->query("SELECT name FROM ibf_forums WHERE id='" . $IN['f'] . "'");
		$forum = $stmt->fetch();

		if ($IN['f'] == "")
		{
			$ADMIN->error("Could not determine the forum ID to resync.");
		}

		// Get the topics..

		$stmt   = $ibforums->db->query("SELECT COUNT(tid) as count FROM ibf_topics WHERE approved=1 and forum_id='" . $IN['f'] . "'");
		$topics = $stmt->fetch();

		// Get the posts..

		$stmt  = $ibforums->db->query("SELECT COUNT(pid) as count FROM ibf_posts WHERE queued <> 1 and forum_id='" . $IN['f'] . "'");
		$posts = $stmt->fetch();

		// Get the forum last poster..

		$stmt      = $ibforums->db->query("SELECT tid, title, last_poster_id, last_poster_name, last_post FROM ibf_topics WHERE approved=1 and forum_id='" . $IN['f'] . "' ORDER BY last_post DESC LIMIT 0,1");
		$last_post = $stmt->fetch();

		// Reset this forums stats

		$postc = $posts['count'] - $topics['count'];

		if ($postc < 0)
		{
			$postc = 0;
		}

		$data = [
			'last_poster_id'   => $last_post['last_poster_id'],
			'last_poster_name' => $last_post['last_poster_name'],
			'last_post'        => $last_post['last_post'],
			'last_title'       => $last_post['title'],
			'last_id'          => $last_post['tid'],
			'topics'           => $topics['count'],
			'posts'            => $postc
		];

		$ibforums->db->updateRow("ibf_forums", array_map([$ibforums->db, 'quote'], $data), "id='" . $IN['f'] . "'");

		// Override? then return..

		if ($f_override != "")
		{
			return TRUE;
		}

		$ADMIN->save_log("Recounted posts in forum '{$forum['name']}'");

		$ADMIN->done_screen("Forum Resynchronised", "Forum Control", "act=cat");

	}

	//+---------------------------------------------------------------------------------
	//
	// EMPTY FORUM: Removes all topics and posts, etc.
	//
	//+---------------------------------------------------------------------------------

	function empty_form()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$form_array = array();

		if ($IN['f'] == "")
		{
			$ADMIN->error("Could not determine the forum ID to empty.");
		}

		$stmt = $ibforums->db->query("SELECT id, name FROM ibf_forums WHERE id='" . $IN['f'] . "'");

		//+-------------------------------
		// Make sure we have a legal forum
		//+-------------------------------

		if (!$stmt->rowCount())
		{
			$ADMIN->error("Could not resolve that forum ID");
		}

		$forum = $stmt->fetch();

		//+-------------------------------

		$ADMIN->page_title = "Empty Forum '{$forum['name']}'";

		$ADMIN->page_detail = "This WILL DELETE ALL TOPICS, POSTS AND POLLS.<br>The forum itself will not be deleted - please ensure you wish to carry out this action before continuing.";

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'doempty'),
		                                       2 => array('act', 'forum'),
		                                       3 => array('f', $IN['f']),
		                                       4 => array('name', $forum['name']),
		                                  ));

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Empty Forum '{$forum['name']}");

		$ADMIN->html .= $SKIN->add_td_row(array("<b>Forum to empty: </b>", $forum['name']));

		$ADMIN->html .= $SKIN->end_form("Empty this forum");

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	//+---------------------------------------------------------------------------------

	function do_empty()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		if ($IN['f'] == "")
		{
			$ADMIN->error("Could not determine the source forum ID.");
		}

		// Check to make sure its a valid forum.

		$stmt = $ibforums->db->query("SELECT id, posts, topics FROM ibf_forums WHERE id='" . $IN['f'] . "'");

		if (!$forum = $stmt->fetch())
		{
			$ADMIN->error("Could not get the forum details for the forum to empty");
		}

		// Delete topics...

		$ibforums->db->exec("DELETE FROM ibf_topics WHERE forum_id='" . $IN['f'] . "'");

		// Move posts...

		$ibforums->db->exec("DELETE FROM ibf_posts WHERE forum_id='" . $IN['f'] . "'");

		// Move polls...

		$ibforums->db->exec("DELETE FROM ibf_polls WHERE forum_id='" . $IN['f'] . "'");

		// Move voters...

		$ibforums->db->exec("DELETE FROM ibf_voters WHERE forum_id='" . $IN['f'] . "'");

		// Clean up the stats

		$ibforums->db->exec("UPDATE ibf_forums SET posts='0', topics='0', last_post='', last_poster_id='', last_poster_name='', last_title='', last_id='' WHERE id='" . $IN['f'] . "'");

		$ibforums->db->exec("UPDATE ibf_stats SET TOTAL_TOPICS=TOTAL_TOPICS-" . $forum['topics'] . ", TOTAL_REPLIES=TOTAL_REPLIES-" . $forum['posts']);

		$ADMIN->save_log("Emptied forum '{$IN['name']}' of all posts");

		$ADMIN->done_screen("Forum Emptied", "Forum Control", "act=cat");

	}

	//+---------------------------------------------------------------------------------
	//
	// RE-ORDER CATEGORY
	//
	//+---------------------------------------------------------------------------------

	function reorder_form()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$ADMIN->page_title  = "Forum Re-Order";
		$ADMIN->page_detail = "To re-order the forums, simply choose the position number from the drop down box next to each forum title, when you are satisfied with the ordering, simply hit the submit button at the bottom of the form";

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'doreorder'),
		                                       2 => array('act', 'forum'),
		                                  ));

		$SKIN->td_header[] = array("&nbsp;", "10%");
		$SKIN->td_header[] = array("Forum Name", "60%");
		$SKIN->td_header[] = array("Posts", "15%");
		$SKIN->td_header[] = array("Topics", "15%");

		$ADMIN->html .= $SKIN->start_table("Your Categories and Forums");

		$cats         = array();
		$forums       = array();
		$forum_in_cat = array();
		$children     = array();

		$stmt = $ibforums->db->query("SELECT * from ibf_categories WHERE id > 0 ORDER BY position ASC");
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
				$forum_in_cat[$r['category']]++;
			}

		}

		$i = 1;

		$last_cat_id = -1;

		foreach ($cats as $c)
		{

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       '&nbsp;',
			                                       $c['name'],
			                                       '&nbsp;',
			                                       '&nbsp;',
			                                  ), 'pformstrip');
			$last_cat_id = $c['id'];

			foreach ($forums as $r)
			{

				if ($r['category'] == $last_cat_id)
				{

					$form_array = array();

					for ($c = 1; $c <= $forum_in_cat[$r['category']]; $c++)
					{
						$i++;

						$form_array[] = array($c, $c);
					}

					if ($r['subwrap'] == 1)
					{

						$ADMIN->html .= $SKIN->add_td_row(array(
						                                       $SKIN->form_dropdown('POS_' . $r['id'], $form_array, $r['position']),
						                                       $r['name'],
						                                       '&nbsp;',
						                                       '&nbsp;',
						                                  ), 'catrow2');
					} else
					{

						$ADMIN->html .= $SKIN->add_td_row(array(
						                                       $SKIN->form_dropdown('POS_' . $r['id'], $form_array, $r['position']),
						                                       "<b>" . $r['name'] . "</b>",
						                                       $r['posts'],
						                                       $r['topics'],
						                                  ));
					}

					// Song * infinite subforums, 17.12.04

					$this->subforums_addtorow($children, $r['id'], 0);

					// Song * infinite subforums, 17.12.04

				}
			}
		}

		$ADMIN->html .= $SKIN->end_form("Adjust Forum Ordering");

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	//+---------------------------------------------------------------------------------

	function do_reorder()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$stmt = $ibforums->db->query("SELECT id from ibf_forums");

		while ($r = $stmt->fetch())
		{
			$order_query = $ibforums->db->exec("UPDATE ibf_forums SET position='" . $IN['POS_' . $r['id']] . "' WHERE id='" . $r['id'] . "'");
		}

		$ADMIN->save_log("Reordered Forums");

		$ADMIN->done_screen("Forum Ordering Adjusted", "Forum Control", "act=cat");

	}

	//+---------------------------------------------------------------------------------
	//
	// REMOVE FORUM
	//
	//+---------------------------------------------------------------------------------

	function delete_form()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		if (!$IN['f'])
		{
			$ADMIN->error("Could not determine the forum ID to delete.");
		}

		// Song * infinite subforums, 17.12.04

		$stmt = $ibforums->db->query("SELECT f.id, f.name, f.parent_id FROM ibf_forums f, ibf_categories c
			    WHERE c.id=f.category ORDER BY c.position, f.position");

		//+-------------------------------
		// Make sure we have more than 1
		// forum..
		//+-------------------------------

		if ($stmt->rowCount() < 2)
		{
			$ADMIN->error("Can not remove this forum, please create another before attempting to remove this one");
		}

		$forums      = array();
		$children    = array();
		$forums_list = array();
		$result      = array();

		while ($r = $stmt->fetch())
		{
			if ($r['id'] != $IN['f'])
			{
				$std->fill_array($r, $forums, $children, $forums_list);
			} else
			{
				$name = $r['name'];
			}
		}

		foreach ($forums as $row)
		{
			if (!isset($result[$row['id']]))
			{
				$result[$row['id']] = $row;

				$result = $std->subforums_addtorow($result, $children, $row['id'], 1);
			}
		}

		foreach ($result as $idx => $row)
		{
			$result[$idx] = array($idx, $row['name']);
		}

		// Song * infinite subforums, 17.12.04

		//+-------------------------------

		$ADMIN->page_title = "Removing forum '$name'";

		$ADMIN->page_detail = "Before we remove this forum, we need to determine what to do with any topics and posts you may have left in this forum.";

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'dodelete'),
		                                       2 => array('act', 'forum'),
		                                       3 => array('f', $IN['f']),
		                                  ));

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Required");

		$ADMIN->html .= $SKIN->add_td_row(array("<b>Forum to remove: </b>", $name));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Move all <i>existing topics and posts in this forum</i> to which forum?</b>",
		                                       $SKIN->form_dropdown("MOVE_ID", $result)
		                                  ));

		$ADMIN->html .= $SKIN->end_form("Move topics and delete this forum");

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	//+---------------------------------------------------------------------------------

	function do_delete()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$stmt  = $ibforums->db->query("SELECT * FROM ibf_forums WHERE id='" . $IN['f'] . "'");
		$forum = $stmt->fetch();

		if (!$IN['f'])
		{
			$ADMIN->error("Could not determine the source forum ID.");
		}

		if (!$IN['MOVE_ID'])
		{
			$ADMIN->error("Could not determine the destination forum ID.");
		}

		// Move topics...

		$ibforums->db->exec("UPDATE ibf_topics SET forum_id='" . $IN['MOVE_ID'] . "' WHERE forum_id='" . $IN['f'] . "'");

		// Move posts...

		$ibforums->db->exec("UPDATE ibf_posts SET forum_id='" . $IN['MOVE_ID'] . "' WHERE forum_id='" . $IN['f'] . "'");

		// Move polls...

		$ibforums->db->exec("UPDATE ibf_polls SET forum_id='" . $IN['MOVE_ID'] . "' WHERE forum_id='" . $IN['f'] . "'");

		// Move voters...

		$ibforums->db->exec("UPDATE ibf_voters SET forum_id='" . $IN['MOVE_ID'] . "' WHERE forum_id='" . $IN['f'] . "'");

		// Delete the forum

		$ibforums->db->exec("DELETE FROM ibf_forums WHERE id='" . $IN['f'] . "'");

		// Delete any moderators, if any..

		$ibforums->db->exec("DELETE FROM ibf_moderators WHERE forum_id='" . $IN['f'] . "'");

		// Song * forums filter, 19.12.04

		$ibforums->db->exec("DELETE FROM ibf_forums_order WHERE id='" . $IN['f'] . "'");

		// Song * forums filter, 19.12.04

		// Song * add to faq, 02.05.05

		$ibforums->db->exec("UPDATE ibf_forums SET faq_id=0 WHERE faq_id='" . $IN['f'] . "'");

		// Song * add to faq, 02.05.05

		// Song * NEW system

		$ibforums->db->exec("UPDATE ibf_log_forums SET fid='" . $IN['MOVE_ID'] . "' WHERE fid='" . $IN['f'] . "'");

		$ibforums->db->exec("UPDATE ibf_log_topics SET fid='" . $IN['MOVE_ID'] . "' WHERE fid='" . $IN['f'] . "'");

		// Song * NEW system

		$this->recount($IN['MOVE_ID']);

		// Have we moved this forum from a sub cat forum?
		// If so, are there any forums left in this sub cat forum?

		if ($forum['parent_id'] > 0)
		{
			$stmt = $ibforums->db->query("SELECT id FROM ibf_forums WHERE parent_id='{$forum['parent_id']}'");

			if (!$stmt->rowCount())
			{
				// No, there are no more forums that have a parent id the same as the one we've just moved it from
				// So, make that forum a normal forum then!

				$ibforums->db->exec("UPDATE ibf_forums SET subwrap=0 WHERE id='{$forum['parent_id']}'");
			}
		}

		$ADMIN->save_log("Removed forum '{$forum['name']}'");

		$ADMIN->done_screen("Forum Removed", "Forum Control", "act=cat");

	}

	//+---------------------------------------------------------------------------------
	//
	// NEW FORUM
	//
	//+---------------------------------------------------------------------------------

	function new_form()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$f_name = "";

		if ($_GET['name'] != "")
		{
			$f_name = $std->txt_stripslashes(urldecode($_GET['name']));
		}

		// Song * infinite subforums, 17.12.04

		$cats = array();
		$seen = array();

		$last_cat_id = -1;

		$stmt = $ibforums->db->query("SELECT id, name from ibf_categories WHERE id > 0 ORDER BY position");
		while ($r = $stmt->fetch())
		{
			$category[$r['id']] = $r;
		}

		$stmt = $ibforums->db->query("SELECT id as forum_id, subwrap, name as forum_name, subwrap, parent_id, category from ibf_forums ORDER BY position");
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

		foreach ($category as $c)
		{
			$cats[]      = array("c_" . $c['id'], "Category: " . $c['name']);
			$last_cat_id = $c['id'];

			foreach ($forums as $r)
			{
				if ($r['category'] == $last_cat_id)
				{
					$cats[] = array("f_" . $r['forum_id'], "-- " . $r['forum_name']);

					$cats = array_merge($cats, $this->subforums_dropdown($r['forum_id'], $children));
				}
			}
		}

		// Song * infinite subforums, 17.12.04

		$ADMIN->page_title = "Add a new Forum";

		$ADMIN->page_detail = "This section will allow you to add a new forum to an existing category. Please ensure you select the correct category to insert
							   the new forum into. If you do make a mistake, clicking on \"Edit Settings\" will allow you to make any changes after the forum has
							   been created.";

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'donew'),
		                                       2 => array('act', 'forum'),
		                                  ));

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Basic Settings");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Add to which parent?</b><br>",
		                                       $SKIN->form_dropdown("CATEGORY", $cats)
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Forum State</b>",
		                                       $SKIN->form_dropdown("FORUM_STATUS", array(
		                                                                                 0 => array(1, 'Active'),
		                                                                                 1 => array(
			                                                                                 0,
			                                                                                 'Read Only Archive'
		                                                                                 ),
		                                                                            ), "1")
		                                  ));

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		$ADMIN->html .= $SKIN->start_table("Forum Settings");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Forum Name</b>",
		                                       $SKIN->form_input("FORUM_NAME", $f_name)
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Forum Icon</b>",
		                                       $SKIN->form_input("FORUM_ICON", $forum['icon'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Forum Description</b><br>You may use HTML - linebreaks are converted 'Auto-Magically'",
		                                       $SKIN->form_textarea("FORUM_DESC")
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Use syntax highlight ?</b> ((c) Song)",
		                                       $SKIN->form_yes_no("forum_highlight", 0)
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Show full image?</b> ((c) Song)<br>(if no link will be shown)",
		                                       $SKIN->form_yes_no("siu_thumb", 0)
		                                  ));

		//+-------------------------------

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		$ADMIN->html .= $SKIN->start_table("Root Forum Option: allow posting in this forum?");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow new topics and posts in this forum?</b><br>If yes, any sub-forums will be displayed above the normal topic list, if there are no sub-forums to show, it will display the topic list as normal<br><b>If 'no' you can skip the rest of this form as the settings will have no effect and this forum will act like a category.</b>",
		                                       $SKIN->form_yes_no("sub_can_post", 1) . "<br><b>NOTE</b> This option will have no effect if you use another forum as a parent for this new forum",
		                                  ));

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		$ADMIN->html .= $SKIN->start_table("Forum Redirect Options");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>URL to redirect this forum to</b>",
		                                       $SKIN->form_input("redirect_url")
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Target to redirect to?</b><br>Leave blank or use '_self' to load in same browser window",
		                                       $SKIN->form_input("redirect_loc")
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Switch on the URL redirect?</b><br>If 'yes' you can skip to the forum permission as the settings will have no effect and this forum will act like as a redirect link. Current posts will not be accessible when on.",
		                                       $SKIN->form_yes_no("redirect_on", 0)
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Redirect clicks to date</b>",
		                                       $SKIN->form_input("redirect_hits", 0)
		                                  ));

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		$ADMIN->html .= $SKIN->start_table("Postable Forum Options");

		//+-------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow use of the [doHTML] tag?</b><br />This will allow HTML to be posted and executed",
		                                       $SKIN->form_yes_no("FORUM_HTML", 0)
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow IBF CODE to be posted?</b>",
		                                       $SKIN->form_yes_no("FORUM_IBC", 1)
		                                  ));

		//-----------

		// Song * quote

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Turn on the Quick Reply Box?</b>",
		                                       $SKIN->form_yes_no("quick_reply", 1)
		                                  ));
		// Song

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow Polls in this forum (when allowed)?</b>",
		                                       $SKIN->form_yes_no("allow_poll", 1)
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow votes to bump a topic?</b>",
		                                       $SKIN->form_yes_no("allow_pollbump", 0)
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Posts in this forum increase member's cumulative post count?</b>",
		                                       $SKIN->form_yes_no("inc_postcount", 1)
		                                  ));

		//-----------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Moderate postings?</b><br>(Requires a moderator to manually add posts/topics to the forum)",
		                                       $SKIN->form_dropdown("MODERATE", array(
		                                                                             0 => array(0, 'No'),
		                                                                             1 => array(
			                                                                             1,
			                                                                             'Moderate all new topics and all replies'
		                                                                             ),
		                                                                             2 => array(
			                                                                             2,
			                                                                             'Moderate new topics but don\'t moderate replies'
		                                                                             ),
		                                                                             3 => array(
			                                                                             3,
			                                                                             'Moderate replies but don\'t moderate new topics'
		                                                                             ),
		                                                                        ), 0)
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Require password access?<br>Enter the password here</b><br>(Leave this box empty if you do not require this)",
		                                       $SKIN->form_input("FORUM_PROTECT")
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Default date cut off for topic display</b>",
		                                       $SKIN->form_dropdown("PRUNE_DAYS", array(
		                                                                               0  => array(1, 'Today'),
		                                                                               1  => array(5, 'Last 5 days'),
		                                                                               2  => array(7, 'Last 7 days'),
		                                                                               3  => array(10, 'Last 10 days'),
		                                                                               4  => array(15, 'Last 15 days'),
		                                                                               5  => array(20, 'Last 20 days'),
		                                                                               6  => array(25, 'Last 25 days'),
		                                                                               7  => array(30, 'Last 30 days'),
		                                                                               8  => array(60, 'Last 60 days'),
		                                                                               9  => array(90, 'Last 90 days'),
		                                                                               10 => array(100, 'Show All'),
		                                                                          ), "30")
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Default sort key</b>",
		                                       $SKIN->form_dropdown("SORT_KEY", array(
		                                                                             0 => array(
			                                                                             'last_post',
			                                                                             'Date of the last post'
		                                                                             ),
		                                                                             1 => array('title', 'Topic Title'),
		                                                                             2 => array(
			                                                                             'starter_name',
			                                                                             'Topic Starters Name'
		                                                                             ),
		                                                                             3 => array('posts', 'Topic Posts'),
		                                                                             4 => array('views', 'Topic Views'),
		                                                                             5 => array(
			                                                                             'start_date',
			                                                                             'Date topic started'
		                                                                             ),
		                                                                             6 => array(
			                                                                             'last_poster_name',
			                                                                             'Name of the last poster'
		                                                                             ),
		                                                                        ), "last_post")
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Default sort order</b>",
		                                       $SKIN->form_dropdown("SORT_ORDER", array(
		                                                                               0 => array(
			                                                                               'Z-A',
			                                                                               'Descending (Z - A, 0 - 10)'
		                                                                               ),
		                                                                               1 => array(
			                                                                               'A-Z',
			                                                                               'Ascending (A - Z, 10 - 0)'
		                                                                               ),
		                                                                          ), "Z-A")
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>How many days delayed messages will be keep</b>",
		                                       $SKIN->form_input("days_off", 5)
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow decided topics in this forum?</b>",
		                                       $SKIN->form_yes_no("decided_button", 1)
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Id of forum-FAQ for this forum<br>(Leave this box empty or 0 value if the forum has not FAQ-forum)",
		                                       $SKIN->form_input("faq_forum", 0)
		                                  ));

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------

		$SKIN->td_header[] = array("Name", "40%");
		$SKIN->td_header[] = array("Read", "15%");
		$SKIN->td_header[] = array("Reply", "15%");
		$SKIN->td_header[] = array("Start", "15%");
		$SKIN->td_header[] = array("Upload", "15%");

		$ADMIN->html .= $SKIN->start_table("Permission Access Levels");

		$ADMIN->html .= $SKIN->build_group_perms($forum['read_perms'], $forum['start_perms'], $forum['reply_perms'], $forum['upload_perms']);

		$ADMIN->html .= $SKIN->end_form("Create this forum");

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	//------------------------------------------------------------------------------------------------
	//------------------------------------------------------------------------------------------------

	function do_new()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$IN['FORUM_NAME'] = trim($IN['FORUM_NAME']);

		if ($IN['FORUM_NAME'] == "")
		{
			$ADMIN->error("You must enter a forum title");
		}

		// Get the new forum id. We could use auto_incrememnt, but we need the ID to use as the default
		// forum position...

		$stmt = $ibforums->db->query("SELECT MAX(id) as top_forum FROM ibf_forums");
		$row  = $stmt->fetch();

		if ($row['top_forum'] < 1)
		{
			$row['top_forum'] = 0;
		}

		$row['top_forum']++;

		$perms = $ADMIN->compile_forum_perms();

		$cat    = -1;
		$parent = -1;

		if (preg_match("/^c_(\d+)$/", $IN['CATEGORY'], $match))
		{
			$cat = $match[1];
		} else
		{
			$parent = preg_replace("/^f_/", "", $IN['CATEGORY']);

			$stmt = $ibforums->db->query("SELECT category FROM ibf_forums WHERE id='$parent'");

			if ($forum_result = $stmt->fetch())
			{
				$cat = $forum_result['category'];
			}
		}

		$data = [
			'id'               => $row['top_forum'],
			'position'         => 1,
			'topics'           => 0,
			'posts'            => 0,
			'last_post'        => "",
			'last_poster_id'   => "",
			'last_poster_name' => "",
			'name'             => $IN['FORUM_NAME'],
			'icon'             => $IN['FORUM_ICON'],
			'description'      => $std->my_nl2br($std->txt_stripslashes($_POST['FORUM_DESC'])),
			'use_ibc'          => $IN['FORUM_IBC'],
			'use_html'         => $IN['FORUM_HTML'],
			'status'           => $IN['FORUM_STATUS'],
			'start_perms'      => $perms['START'],
			'reply_perms'      => $perms['REPLY'],
			'read_perms'       => $perms['READ'],
			'upload_perms'     => $perms['UPLOAD'],
			'password'         => $IN['FORUM_PROTECT'],
			'category'         => $cat,
			'last_id'          => "",
			'last_title'       => "",
			'sort_key'         => $IN['SORT_KEY'],
			'sort_order'       => $IN['SORT_ORDER'],
			'prune'            => $IN['PRUNE_DAYS'],
			'show_rules'       => 0,
			'preview_posts'    => $IN['MODERATE'],
			'allow_poll'       => $IN['allow_poll'],
			'allow_pollbump'   => $IN['allow_pollbump'],
			'inc_postcount'    => $IN['inc_postcount'],
			'parent_id'        => $parent,
			'sub_can_post'     => $IN['sub_can_post'],
			'quick_reply'      => $IN['quick_reply'],
			'redirect_on'      => $IN['redirect_on'],
			'redirect_hits'    => $IN['redirect_hits'],
			'redirect_url'     => $IN['redirect_url'],
			'redirect_loc'     => $IN['redirect_loc'],
			'forum_highlight'  => $IN['forum_highlight'],
			'siu_thumb'        => $IN['siu_thumb'],
			'days_off'         => $IN['days_off'],
			'decided_button'   => $IN['decided_button'],
			'faq_id'           => $IN['faq_forum'],
		];

		$ibforums->db->insertRow("ibf_forums", $data);

		if ($parent != -1)
		{
			$ibforums->db->exec("UPDATE ibf_forums SET subwrap=1 WHERE id='$parent'");
		}

		// Song * forums filer, 19.12.04

		$std->update_forum_order_cache($row['top_forum'], $parent);

		// Song * forums filer, 19.12.04

		$ADMIN->save_log("Forum '{$IN['FORUM_NAME']}' created");

		$ADMIN->done_screen("Forum {$IN['FORUM_NAME']} created", "Forum Control", "act=cat");

	}

	//------------------------------------------------------------------------------------------------
	//------------------------------------------------------------------------------------------------

	//+---------------------------------------------------------------------------------
	//
	// EDIT FORUM
	//
	//+---------------------------------------------------------------------------------

	function edit_form()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		if ($IN['f'] == "")
		{
			$ADMIN->error("You didn't choose a forum to edit, duh!");
		}

		// Song * infinite subforums, 17.12.04

		$cats = array();
		$seen = array();

		$last_cat_id = -1;

		$stmt = $ibforums->db->query("SELECT
				id,
				name
			    FROM ibf_categories
			    WHERE id > 0
			    ORDER BY position");

		while ($r = $stmt->fetch())
		{
			$category[$r['id']] = $r;
		}

		$stmt = $ibforums->db->query("SELECT
				id as forum_id,
				subwrap,
				name as forum_name,
				subwrap,
				parent_id,
				category
			    FROM ibf_forums
			    ORDER BY position");
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

		foreach ($category as $c)
		{
			$cats[]      = array("c_" . $c['id'], "Category: " . $c['name']);
			$last_cat_id = $c['id'];

			foreach ($forums as $r)
			{
				if ($r['category'] == $last_cat_id)
				{
					$cats[] = array("f_" . $r['forum_id'], "-- " . $r['forum_name']);

					$cats = array_merge($cats, $this->subforums_dropdown($r['forum_id'], $children));
				}
			}
		}

		// Song * infinite subforums, 17.12.04

		$stmt  = $ibforums->db->query("SELECT *
			    FROM ibf_forums
			    WHERE id='" . $IN['f'] . "'");
		$forum = $stmt->fetch();

		if ($forum['id'] == "")
		{
			$ADMIN->error("Could not retrieve the forum data based on ID {$IN['f']}");
		}

		//-------------------------------------

		$real_parent = "";

		if ($forum['parent_id'] < 1)
		{
			$real_parent = 'c_' . $forum['category'];
		} else
		{
			$real_parent = 'f_' . $forum['parent_id'];
		}

		//-------------------------------------

		$ADMIN->page_title = "Edit a Forum";

		$ADMIN->page_detail = "This section will allow you to edit an existing forum. If you wish to adjust the forum permissions (who has the ability to
							   start, reply and read topics) click on 'Edit Permissions on the Forums and Categories overview.";

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'doedit'),
		                                       2 => array('act', 'forum'),
		                                       3 => array('f', $IN['f']),
		                                       4 => array('name', $forum['name']),
		                                  ));

		//+-------------------------------

		$SKIN->td_header[] = array("{none}", "40%");
		$SKIN->td_header[] = array("{none}", "60%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Basic Settings");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Add to which parent?</b><br>",
		                                       $SKIN->form_dropdown("CATEGORY", $cats, $real_parent)
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Forum State</b>",
		                                       $SKIN->form_dropdown("FORUM_STATUS", array(
		                                                                                 0 => array(1, 'Active'),
		                                                                                 1 => array(
			                                                                                 0,
			                                                                                 'Read Only Archive'
		                                                                                 ),
		                                                                            ), $forum['status'])
		                                  ));

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------

		$SKIN->td_header[] = array("{none}", "40%");
		$SKIN->td_header[] = array("{none}", "60%");

		$ADMIN->html .= $SKIN->start_table("Forum Settings");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Forum Name</b>",
		                                       $SKIN->form_input("FORUM_NAME", $forum['name'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Forum Icon</b>",
		                                       $SKIN->form_input("FORUM_ICON", $forum['icon'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Forum Description</b><br>You may use HTML - linebreaks 'Auto-Magically' converted to &lt;br&gt;",
		                                       $SKIN->form_textarea("FORUM_DESC", $std->my_br2nl($forum['description']))
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Use syntax highlight</b> ? ((c) Song)",
		                                       $SKIN->form_yes_no("forum_highlight", $forum['forum_highlight'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Show full image?</b> ((c) Song)<br>(if no link will be shown)",
		                                       $SKIN->form_yes_no("siu_thumb", $forum['siu_thumb'])
		                                  ));
		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------

		$SKIN->td_header[] = array("{none}", "40%");
		$SKIN->td_header[] = array("{none}", "60%");

		$ADMIN->html .= $SKIN->start_table("Forum Redirect Options");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>URL to redirect this forum to</b>",
		                                       $SKIN->form_input("redirect_url", $forum['redirect_url'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Target to redirect to?</b><br>Leave blank or use '_self' to load in same browser window",
		                                       $SKIN->form_input("redirect_loc", $forum['redirect_loc'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Switch on the URL redirect?</b><br>If 'yes' you can skip the rest of this form as the settings will have no effect and this forum will act like as a redirect link. Current posts will not be accessible when on.",
		                                       $SKIN->form_yes_no("redirect_on", $forum['redirect_on'], array(
		                                                                                                     'yes' => " onclick=\"ShowHide('canpost', 'canpostoff');\" ",
		                                                                                                     'no'  => " onclick=\"ShowHide('canpost', 'canpostoff');\" "
		                                                                                                ))
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Redirect clicks to date</b>",
		                                       $SKIN->form_input("redirect_hits", $forum['redirect_hits'])
		                                  ));

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------

		if ($forum['parent_id'] > 0)
		{
			$extra = "<span id='normal' style='color:red'><br><b>NOTE</b>: This forum is <b>not</b> a root forum, this option will have no effect unless you change the parent to a category</span>";
		}

		//+-------------------------------

		$SKIN->td_header[] = array("{none}", "40%");
		$SKIN->td_header[] = array("{none}", "60%");

		$cp2_show = $forum['redirect_on'] == 1
			? 'show'
			: 'none';
		$cp_show  = $forum['redirect_on'] == 1
			? 'none'
			: 'show';

		$ADMIN->html .= "\n<div id='canpost' style='display:$cp_show'>\n";

		$ADMIN->html .= $SKIN->start_table("Root Forum Option: Allow posting in this forum?");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow new topics and posts in this forum?</b><br>If yes, any sub-forums will be displayed above the normal topic list, if there are no sub-forums to show, it will display the topic list as normal<br><b>If 'no' you can skip the rest of this form as the settings will have no effect and this forum will act like a category.</b>",
		                                       $SKIN->form_yes_no("sub_can_post", $forum['sub_can_post'], array(
		                                                                                                       'yes' => " onclick=\"ShowHide('main_div', 'maindivoff');\" ",
		                                                                                                       'no'  => " onclick=\"ShowHide('main_div', 'maindivoff');\" "
		                                                                                                  )) . $extra
		                                  ));

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------

		$md_show  = $forum['sub_can_post'] == 1
			? 'show'
			: 'none';
		$md2_show = $forum['sub_can_post'] == 1
			? 'none'
			: 'show';

		$SKIN->td_header[] = array("{none}", "40%");
		$SKIN->td_header[] = array("{none}", "60%");

		$ADMIN->html .= "\n<div id='main_div' style='display:$md_show'>\n";

		$ADMIN->html .= $SKIN->start_table("Postable Forum Settings");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow use of the [doHTML] tag?</b><br />This will allow HTML to be posted and executed",
		                                       $SKIN->form_yes_no("FORUM_HTML", $forum['use_html'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow IBF CODE to be posted?</b>",
		                                       $SKIN->form_yes_no("FORUM_IBC", $forum['use_ibc'])
		                                  ));

		//-----------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Turn on the Quick Reply Box?</b>",
		                                       $SKIN->form_yes_no("quick_reply", $forum['quick_reply'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow Polls in this forum (when allowed)?</b>",
		                                       $SKIN->form_yes_no("allow_poll", $forum['allow_poll'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow votes to bump a topic?</b>",
		                                       $SKIN->form_yes_no("allow_pollbump", $forum['allow_pollbump'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Posts in this forum increase member's cumulative post count?</b>",
		                                       $SKIN->form_yes_no("inc_postcount", $forum['inc_postcount'])
		                                  ));

		//-----------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Moderate postings?</b><br>(Requires a moderator to manually add posts/topics to the forum)",
		                                       $SKIN->form_dropdown("MODERATE", array(
		                                                                             0 => array(0, 'No'),
		                                                                             1 => array(
			                                                                             1,
			                                                                             'Moderate all new topics and all replies'
		                                                                             ),
		                                                                             2 => array(
			                                                                             2,
			                                                                             'Moderate new topics but don\'t moderate replies'
		                                                                             ),
		                                                                             3 => array(
			                                                                             3,
			                                                                             'Moderate replies but don\'t moderate new topics'
		                                                                             ),
		                                                                        ), $forum['preview_posts'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Require password access?<br>Enter the password here</b><br>(Leave this box empty if you do not require this)",
		                                       $SKIN->form_input("FORUM_PROTECT", $forum['password'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Default date cut off for topic display</b>",
		                                       $SKIN->form_dropdown("PRUNE_DAYS", array(
		                                                                               0  => array(1, 'Today'),
		                                                                               1  => array(5, 'Last 5 days'),
		                                                                               2  => array(7, 'Last 7 days'),
		                                                                               3  => array(10, 'Last 10 days'),
		                                                                               4  => array(15, 'Last 15 days'),
		                                                                               5  => array(20, 'Last 20 days'),
		                                                                               6  => array(25, 'Last 25 days'),
		                                                                               7  => array(30, 'Last 30 days'),
		                                                                               8  => array(60, 'Last 60 days'),
		                                                                               9  => array(90, 'Last 90 days'),
		                                                                               10 => array(100, 'Show All'),
		                                                                          ), $forum['prune'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Default sort key</b>",
		                                       $SKIN->form_dropdown("SORT_KEY", array(
		                                                                             0 => array(
			                                                                             'last_post',
			                                                                             'Date of the last post'
		                                                                             ),
		                                                                             1 => array('title', 'Topic Title'),
		                                                                             2 => array(
			                                                                             'starter_name',
			                                                                             'Topic Starters Name'
		                                                                             ),
		                                                                             3 => array('posts', 'Topic Posts'),
		                                                                             4 => array('views', 'Topic Views'),
		                                                                             5 => array(
			                                                                             'start_date',
			                                                                             'Date topic started'
		                                                                             ),
		                                                                             6 => array(
			                                                                             'last_poster_name',
			                                                                             'Name of the last poster'
		                                                                             ),
		                                                                        ), $forum['sort_key'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Default sort order</b>",
		                                       $SKIN->form_dropdown("SORT_ORDER", array(
		                                                                               0 => array(
			                                                                               'Z-A',
			                                                                               'Descending (Z - A, 0 - 10)'
		                                                                               ),
		                                                                               1 => array(
			                                                                               'A-Z',
			                                                                               'Ascending (A - Z, 10 - 0)'
		                                                                               ),
		                                                                          ), $forum['sort_order'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>How many days delayed messages will be keep</b>",
		                                       $SKIN->form_input("days_off", $forum['days_off'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow decided topics in this forum?</b>",
		                                       $SKIN->form_yes_no("decided_button", $forum['decided_button'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Id of forum-FAQ for this forum<br>(Leave this box empty or 0 value if the forum has not FAQ-forum)",
		                                       $SKIN->form_input("faq_forum", $forum['faq_id'])
		                                  ));

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->html .= "\n<!--END MAIN DIV--></div>\n
		                   <div id='maindivoff' class='offdiv' style='display:$md2_show'>
		                     <div class='tableborder'>
						       <div class='maintitle'><a href=\"javascript:ShowHide('main_div', 'maindivoff');\"><img src='{$SKIN->img_url}/plus.gif'></a>&nbsp;<a href=\"javascript:ShowHide('main_div', 'maindivoff');\">Postable Forum Settings</a></div>
						     </div>
		                 </div><br />\n";

		$ADMIN->html .= "\n<!--END CAN POST DIV--></div>\n
		                   <div id='canpostoff' style='display:$cp2_show'>
		                     <div class='tableborder'>
						       <div class='maintitle'><a href=\"javascript:ShowHide('canpost', 'canpostoff');\"><img src='{$SKIN->img_url}/plus.gif'></a>&nbsp;<a href=\"javascript:ShowHide('main_div', 'maindivoff');\">Postable Forum Settings</a></div>
						     </div>
		                 </div><br />\n";

		$ADMIN->html .= $SKIN->end_form_standalone("Edit this forum");

		$ADMIN->nav[] = array('act=cat', 'Manage Forums');

		$ADMIN->output();

	}

	//+---------------------------------------------------------------------------------

	function do_edit()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$IN['FORUM_NAME'] = trim($IN['FORUM_NAME']);

		if ($IN['FORUM_NAME'] == "")
		{
			$ADMIN->error("You must enter a forum title");
		}

		$stmt = $ibforums->db->query("SELECT * from ibf_forums WHERE id='" . $IN['f'] . "'");

		$old_details = $stmt->fetch();

		$cat    = -1;
		$parent = -1;

		if (preg_match("/^c_(\d+)$/", $IN['CATEGORY'], $match))
		{
			$cat = $match[1];
		} else
		{
			$parent = preg_replace("/^f_/", "", $IN['CATEGORY']);

			$stmt = $ibforums->db->query("SELECT category FROM ibf_forums WHERE id='$parent'");

			if ($forum_result = $stmt->fetch())
			{
				$cat = $forum_result['category'];
			}
		}

		$data = [

			'name'            => $IN['FORUM_NAME'],
			'icon'            => $IN['FORUM_ICON'],
			'description'     => $std->my_nl2br($std->txt_stripslashes($_POST['FORUM_DESC'])),
			'use_ibc'         => $IN['FORUM_IBC'],
			'use_html'        => $IN['FORUM_HTML'],
			'status'          => $IN['FORUM_STATUS'],
			'password'        => $IN['FORUM_PROTECT'],
			'category'        => $cat,
			'sort_key'        => $IN['SORT_KEY'],
			'sort_order'      => $IN['SORT_ORDER'],
			'prune'           => $IN['PRUNE_DAYS'],
			'preview_posts'   => $IN['MODERATE'],
			'allow_poll'      => $IN['allow_poll'],
			'allow_pollbump'  => $IN['allow_pollbump'],
			'inc_postcount'   => $IN['inc_postcount'],
			'parent_id'       => $parent,
			'sub_can_post'    => $IN['sub_can_post'],
			'quick_reply'     => $IN['quick_reply'],
			'redirect_on'     => $IN['redirect_on'],
			'redirect_hits'   => $IN['redirect_hits'],
			'redirect_url'    => $IN['redirect_url'],
			'redirect_loc'    => $IN['redirect_loc'],
			'forum_highlight' => $IN['forum_highlight'],
			'siu_thumb'       => $IN['siu_thumb'],
			'days_off'        => $IN['days_off'],
			'decided_button'  => $IN['decided_button'],
			'faq_id'          => $IN['faq_forum'],

		];

		$ibforums->db->updateRow("ibf_forums", array_map([$ibforums->db, 'quote'], $data), "id='" . $IN['f'] . "'");

		// Update the parent if need be

		if ($parent != -1)
		{
			$ibforums->db->exec("UPDATE ibf_forums SET subwrap=1 WHERE id='$parent'");
		}

		// Have we moved this forum from a sub cat forum?
		// If so, are there any forums left in this sub cat forum?

		if (($old_details['parent_id'] > 0) and ($old_details['parent_id'] != $parent))
		{
			$stmt = $ibforums->db->query("SELECT id FROM ibf_forums WHERE parent_id='{$old_details['parent_id']}'");

			if (!$stmt->rowCount())
			{
				// No, there are no more forums that have a parent id the same as the one we've just moved it from
				// So, make that forum a normal forum then!

				$ibforums->db->exec("UPDATE ibf_forums SET subwrap=0 WHERE id='{$old_details['parent_id']}'");
			}
		}

		// Song * forums filer, 19.12.04

		$ibforums->db->exec("DELETE FROM ibf_forums_order WHERE id='" . $IN['f'] . "'");

		$std->update_forum_order_cache($IN['f'], $parent);

		// Song * forums filer, 19.12.04

		$ADMIN->save_log("Forum '{$IN['name']}' edited");

		$ADMIN->done_screen("Forum {$IN['name']} Edited", "Forum Control", "act=cat");

	}

	//+---------------------------------------------------------------------------------
	//
	// Sub Cat Edit Form
	//
	//+---------------------------------------------------------------------------------

	function subedit()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$cats = array();

		$last_cat_id = -1;

		$stmt = $ibforums->db->query("SELECT * from ibf_categories WHERE id > 0 ORDER BY position");

		while ($r = $stmt->fetch())
		{
			$cats[] = array($r['id'], "Category: " . $r['name']);
		}

		$stmt = $ibforums->db->query("SELECT * from ibf_forums WHERE subwrap='1' AND id='" . $IN['f'] . "'");

		if (!$forum = $stmt->fetch())
		{
			$ADMIN->error("Could not find that sub category forum in the database");
		}

		if ($forum['password'] == '-1')
		{
			$forum['password'] = "";
		}

		$ADMIN->page_title = "Editing a Sub Category Forum";

		$ADMIN->page_detail = "This section will allow you edit a sub category forum.";

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'doeditsub'),
		                                       2 => array('act', 'forum'),
		                                       3 => array('f', $IN['f']),
		                                  ));

		//+-------------------------------

		$SKIN->td_header[] = array("{none}", "40%");
		$SKIN->td_header[] = array("{none}", "60%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Basic Settings");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Add to which parent?</b><br>",
		                                       $SKIN->form_dropdown("CATEGORY", $cats, $forum['category'])
		                                  ));

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------

		$SKIN->td_header[] = array("{none}", "40%");
		$SKIN->td_header[] = array("{none}", "60%");

		$ADMIN->html .= $SKIN->start_table("Forum Settings");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Forum Name</b>",
		                                       $SKIN->form_input("name", $forum['name'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Forum Icon</b>",
		                                       $SKIN->form_input("FORUM_ICON", $forum['icon'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Forum Description</b>",
		                                       $SKIN->form_textarea("desc", $forum['description'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Forum State</b>",
		                                       $SKIN->form_dropdown("FORUM_STATUS", array(
		                                                                                 0 => array(1, 'Active'),
		                                                                                 1 => array(
			                                                                                 0,
			                                                                                 'Read Only Archive'
		                                                                                 ),
		                                                                            ), $forum['status'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Use syntax highlight</b> ? ((c) Song)",
		                                       $SKIN->form_yes_no("forum_highlight", $forum['forum_highlight'])
		                                  ));
		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Show full image?</b> ((c) Song)<br>(if no link will be shown)",
		                                       $SKIN->form_yes_no("siu_thumb", $forum['siu_thumb'])
		                                  ));
		//+-------------------------------

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------

		$SKIN->td_header[] = array("{none}", "40%");
		$SKIN->td_header[] = array("{none}", "60%");

		$ADMIN->html .= $SKIN->start_table("Forum Redirect Options");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>URL to redirect this forum to</b>",
		                                       $SKIN->form_input("redirect_url", $forum['redirect_url'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Target to redirect to?</b><br>Leave blank or use '_self' to load in same browser window",
		                                       $SKIN->form_input("redirect_loc", $forum['redirect_loc'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Switch on the URL redirect?</b><br>If 'yes' you can skip the rest of this form as the settings will have no effect and this forum will act like as a redirect link. Current posts will not be accessible when on.",
		                                       $SKIN->form_yes_no("redirect_on", $forum['redirect_on'], array(
		                                                                                                     'yes' => " onclick=\"ShowHide('canpost', 'canpostoff');\" ",
		                                                                                                     'no'  => " onclick=\"ShowHide('canpost', 'canpostoff');\" "
		                                                                                                ))
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Redirect clicks to date</b>",
		                                       $SKIN->form_input("redirect_hits", $forum['redirect_hits'])
		                                  ));

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------

		$SKIN->td_header[] = array("{none}", "40%");
		$SKIN->td_header[] = array("{none}", "60%");

		$cp2_show = $forum['redirect_on'] == 1
			? 'show'
			: 'none';
		$cp_show  = $forum['redirect_on'] == 1
			? 'none'
			: 'show';

		$ADMIN->html .= "\n<div id='canpost' style='display:$cp_show'>\n";

		$ADMIN->html .= $SKIN->start_table("Allow posting in this forum?");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow new topics and posts in this forum?</b><br>If yes, the forums in this sub-forum will be displayed above the normal topic list. <b>If 'no' you can skip the rest of this form as the settings will have no effect</b>",
		                                       $SKIN->form_yes_no("sub_can_post", $forum['sub_can_post'], array(
		                                                                                                       'yes' => " onclick=\"ShowHide('main_div', 'maindivoff');\" ",
		                                                                                                       'no'  => " onclick=\"ShowHide('main_div', 'maindivoff');\" "
		                                                                                                  )) . $extra
		                                  ));

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------

		$md_show  = $forum['sub_can_post'] == 1
			? 'show'
			: 'none';
		$md2_show = $forum['sub_can_post'] == 1
			? 'none'
			: 'show';

		$SKIN->td_header[] = array("{none}", "40%");
		$SKIN->td_header[] = array("{none}", "60%");

		$ADMIN->html .= "\n<div id='main_div' style='display:$md_show'>\n";

		$ADMIN->html .= $SKIN->start_table("Postable Forum Settings");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow use of the [doHTML] tag?</b><br />This will allow HTML to be posted and executed",
		                                       $SKIN->form_yes_no("FORUM_HTML", $forum['use_html'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow IBF CODE to be posted?</b>",
		                                       $SKIN->form_yes_no("FORUM_IBC", $forum['use_ibc'])
		                                  ));

		//-----------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Turn on the Quick Reply Box?</b>",
		                                       $SKIN->form_yes_no("quick_reply", $forum['quick_reply'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow Polls in this forum (when allowed)?</b>",
		                                       $SKIN->form_yes_no("allow_poll", $forum['allow_poll'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow votes to bump a topic?</b>",
		                                       $SKIN->form_yes_no("allow_pollbump", $forum['allow_pollbump'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Posts in this forum increase member's cumulative post count?</b>",
		                                       $SKIN->form_yes_no("inc_postcount", $forum['inc_postcount'])
		                                  ));

		//-----------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Moderate postings?</b><br>(Requires a moderator to manually add posts/topics to the forum)",
		                                       $SKIN->form_dropdown("MODERATE", array(
		                                                                             0 => array(0, 'No'),
		                                                                             1 => array(
			                                                                             1,
			                                                                             'Moderate all new topics and all replies'
		                                                                             ),
		                                                                             2 => array(
			                                                                             2,
			                                                                             'Moderate new topics but don\'t moderate replies'
		                                                                             ),
		                                                                             3 => array(
			                                                                             3,
			                                                                             'Moderate replies but don\'t moderate new topics'
		                                                                             ),
		                                                                        ), $forum['preview_posts'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Require password access?<br>Enter the password here</b><br>(Leave this box empty if you do not require this)",
		                                       $SKIN->form_input("FORUM_PROTECT", $forum['password'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Default date cut off for topic display</b>",
		                                       $SKIN->form_dropdown("PRUNE_DAYS", array(
		                                                                               0  => array(1, 'Today'),
		                                                                               1  => array(5, 'Last 5 days'),
		                                                                               2  => array(7, 'Last 7 days'),
		                                                                               3  => array(10, 'Last 10 days'),
		                                                                               4  => array(15, 'Last 15 days'),
		                                                                               5  => array(20, 'Last 20 days'),
		                                                                               6  => array(25, 'Last 25 days'),
		                                                                               7  => array(30, 'Last 30 days'),
		                                                                               8  => array(60, 'Last 60 days'),
		                                                                               9  => array(90, 'Last 90 days'),
		                                                                               10 => array(100, 'Show All'),
		                                                                          ), $forum['prune'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Default sort key</b>",
		                                       $SKIN->form_dropdown("SORT_KEY", array(
		                                                                             0 => array(
			                                                                             'last_post',
			                                                                             'Date of the last post'
		                                                                             ),
		                                                                             1 => array('title', 'Topic Title'),
		                                                                             2 => array(
			                                                                             'starter_name',
			                                                                             'Topic Starters Name'
		                                                                             ),
		                                                                             3 => array('posts', 'Topic Posts'),
		                                                                             4 => array('views', 'Topic Views'),
		                                                                             5 => array(
			                                                                             'start_date',
			                                                                             'Date topic started'
		                                                                             ),
		                                                                             6 => array(
			                                                                             'last_poster_name',
			                                                                             'Name of the last poster'
		                                                                             ),
		                                                                        ), $forum['sort_key'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Default sort order</b>",
		                                       $SKIN->form_dropdown("SORT_ORDER", array(
		                                                                               0 => array(
			                                                                               'Z-A',
			                                                                               'Descending (Z - A, 0 - 10)'
		                                                                               ),
		                                                                               1 => array(
			                                                                               'A-Z',
			                                                                               'Ascending (A - Z, 10 - 0)'
		                                                                               ),
		                                                                          ), $forum['sort_order'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>How many days delayed messages will be keep</b>",
		                                       $SKIN->form_input("days_off", $forum['days_off'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow decided topics in this forum?</b>",
		                                       $SKIN->form_yes_no("decided_button", $forum['decided_button'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Id of forum-FAQ for this forum<br>(Leave this box empty or 0 value if the forum has not FAQ-forum)",
		                                       $SKIN->form_input("faq_forum", $forum['faq_id'])
		                                  ));

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->html .= "\n<!--END MAIN DIV--></div>\n
		                   <div id='maindivoff' class='offdiv' style='display:$md2_show'>
		                     <div class='tableborder'>
						       <div class='maintitle'><a href=\"javascript:ShowHide('main_div', 'maindivoff');\"><img src='{$SKIN->img_url}/plus.gif'></a>&nbsp;<a href=\"javascript:ShowHide('main_div', 'maindivoff');\">Postable Forum Settings</a></div>
						     </div>
						 <br />
		                 </div>\n";

		$ADMIN->html .= "\n<!--END CAN POST DIV--></div>\n
		                   <div id='canpostoff' style='display:$cp2_show'>
		                     <div class='tableborder'>
						       <div class='maintitle'><a href=\"javascript:ShowHide('canpost', 'canpostoff');\"><img src='{$SKIN->img_url}/plus.gif'></a>&nbsp;<a href=\"javascript:ShowHide('main_div', 'maindivoff');\">Postable Forum Settings</a></div>
						     </div>
		                 </div><br />\n";

		$ADMIN->html .= $SKIN->end_form_standalone("Edit this forum");

		$ADMIN->nav[] = array('act=cat', 'Manage Forums');

		$ADMIN->output();

	}

	function doeditsub()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$IN['FORUM_NAME'] = trim($IN['name']);

		if ($IN['FORUM_NAME'] == "")
		{
			$ADMIN->error("You must enter a forum title");
		}

		if ($IN['f'] == "")
		{
			$ADMIN->error("No forum id was chosen, please go back and try again");
		}

		// Get the new forum id. We could use auto_incrememnt, but we need the ID to use as the default
		// forum position...

		$data = [
			'name'            => $IN['FORUM_NAME'],
			'icon'            => $IN['FORUM_ICON'],
			'description'     => $std->my_nl2br($std->txt_stripslashes($_POST['desc'])),
			'category'        => $IN['CATEGORY'],
			'subwrap'         => 1,
			'sub_can_post'    => $IN['sub_can_post'],
			'use_ibc'         => $IN['FORUM_IBC'],
			'use_html'        => $IN['FORUM_HTML'],
			'status'          => $IN['FORUM_STATUS'],
			'password'        => $IN['FORUM_PROTECT'],
			'sort_key'        => $IN['SORT_KEY'],
			'sort_order'      => $IN['SORT_ORDER'],
			'prune'           => $IN['PRUNE_DAYS'],
			'preview_posts'   => $IN['MODERATE'],
			'allow_poll'      => $IN['allow_poll'],
			'allow_pollbump'  => $IN['allow_pollbump'],
			'inc_postcount'   => $IN['inc_postcount'],
			'quick_reply'     => $IN['quick_reply'],
			'redirect_on'     => $IN['redirect_on'],
			'redirect_hits'   => $IN['redirect_hits'],
			'redirect_url'    => $IN['redirect_url'],
			'redirect_loc'    => $IN['redirect_loc'],
			'forum_highlight' => $IN[forum_highlight],
			'siu_thumb'       => $IN['siu_thumb'],
			'days_off'        => $IN['days_off'],
			'decided_button'  => $IN['decided_button'],
			'faq_id'          => $IN['faq_forum'],
		];

		$ibforums->db->updateRow("ibf_forums", array_map([$ibforums->db, 'quote'], $data), "id='" . $IN['f'] . "'");

		$ADMIN->save_log("Edited Sub Forum '{$IN['FORUM_NAME']}'");

		$ADMIN->done_screen("Forum Edited", "Forum Control", "act=cat");

	}

	//+---------------------------------------------------------------------------------
	//
	// EDIT FORUM
	//
	//+---------------------------------------------------------------------------------

	function perm_edit_form()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		if ($IN['f'] == "")
		{
			$ADMIN->error("You didn't choose a forum to edit, duh!");
		}

		$cats = array();

		$stmt = $ibforums->db->query("SELECT id,name FROM ibf_categories ORDER BY position");

		while ($r = $stmt->fetch())
		{
			$cats[] = array($r['CAT_ID'], $r['CAT_NAME']);
		}

		$stmt  = $ibforums->db->query("SELECT * FROM ibf_forums WHERE id='" . $IN['f'] . "'");
		$forum = $stmt->fetch();

		if ($forum['id'] == "")
		{
			$ADMIN->error("Could not retrieve the forum data based on ID {$IN['f']}");
		}

		$ADMIN->page_title = "Edit permissions for " . $forum['name'];

		$ADMIN->page_detail = "<b>Forum access permissions</b><br>(Check box for access, uncheck to not allow access)<br>If you deny read access for a permission mask, they will not see the forum";

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'pdoedit'),
		                                       2 => array('act', 'forum'),
		                                       3 => array('f', $IN['f']),
		                                       4 => array('name', $forum['name']),
		                                  ));

		$SKIN->td_header[] = array("Name", "40%");
		$SKIN->td_header[] = array("Read", "15%");
		$SKIN->td_header[] = array("Reply", "15%");
		$SKIN->td_header[] = array("Start", "15%");
		$SKIN->td_header[] = array("Upload", "15%");

		$ADMIN->html .= $SKIN->start_table("Permission Access Levels");

		$ADMIN->html .= $SKIN->build_group_perms($forum['read_perms'], $forum['start_perms'], $forum['reply_perms'], $forum['upload_perms']);

		$ADMIN->html .= $SKIN->end_form("Edit this forum");

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	function perm_do_edit()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$perms = $ADMIN->compile_forum_perms();

		$data = [

			'start_perms'  => $perms['START'],
			'reply_perms'  => $perms['REPLY'],
			'read_perms'   => $perms['READ'],
			'upload_perms' => $perms['UPLOAD'],
		];

		$ibforums->db->updateRow("ibf_forums", array_map([$ibforums->db, 'quote'], $data), "id='" . $IN['f'] . "'");

		$ADMIN->save_log("Forum access permission edited in '{$IN['name']}'");

		$ADMIN->done_screen("Forum Access Permissions Edited", "Forum Control", "act=cat");

	}

	function subforums_addtorow($children, $id, $level)
	{
		global $ADMIN, $SKIN;
		$ibforums = Ibf::instance();

		if (!(isset($children[$id])) || (count($children[$id]) <= 0))
		{
			return;
		}

		foreach ($children[$id] as $idx => $rd)
		{
			$form_array = array();

			for ($c = 1; $c <= count($children[$id]); $c++)
			{
				$i++;

				$form_array[] = array($c, $c);
			}

			$t_level_char = "+--";
			for ($i = 0; $i < $level; $i++)
			{
				$t_level_char .= "--";
			}

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       $SKIN->form_dropdown('POS_' . $rd['id'], $form_array, $rd['position']),
			                                       "<b>" . $t_level_char . " " . $rd['name'] . "</b>",
			                                       $rd['posts'],
			                                       $rd['topics'],
			                                  ), 'subforum');

			$this->subforums_addtorow($children, $rd['id'], $level + 1);
		}

	}

	function subforums_dropdown($id, &$children, $level = '----')
	{
		if (!(isset($children[$id])) || (count($children[$id]) <= 0))
		{
			// vot		return;		// It's Song's mistake!
			return array(); // vot   this is right!
		}

		//echo "\$id=$id<br>\n";
		//echo "\$children=$children<br>\n";

		foreach ($children[$id] as $idx => $r)
		{
			//echo "&nbsp;&nbsp; \$idx = $idx<br>\n";
			//	    foreach($r as $k => $v)
			//	    {
			//echo "&nbsp;&nbsp;&nbsp;&nbsp; $k => $v<br>\n";
			//	    }
			$tmp_array[] = array("f_" . $r['forum_id'], $level . " " . $r['forum_name']);
			$tmp_array   = array_merge($tmp_array, $this->subforums_dropdown($r['forum_id'], $children, $level . '--'));
		}
		//echo "--------<br>\n";
		return $tmp_array;

	}

}

?>
