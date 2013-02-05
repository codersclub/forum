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

$idx = new ad_cat();

class ad_cat
{

	var $base_url;

	function ad_cat()
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
				$this->show_cats();
				break;
			case 'doeditform':
				$this->edit_form();
				break;
			case 'doedit':
				$this->do_edit();
				break;
			//+-------------------------
			case 'remove':
				$this->remove_form();
				break;
			case 'doremove':
				$this->do_remove();
				break;
			//+-------------------------
			case 'reorder':
				$this->reorder_form(); //Get it? No... ok.
				break;
			case 'doreorder':
				$this->do_reorder();
				break;

			default:
				$this->show_cats();
				break;
		}

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

		$ADMIN->page_title  = "Category Re Order";
		$ADMIN->page_detail = "To re-order the categories, simply choose the position number from the drop down box next to each category title, when you are satisfied with the ordering, simply hit the submit button at the bottom of the form";

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'doreorder'),
		                                       2 => array('act', 'cat'),
		                                  ));

		$SKIN->td_header[] = array("&nbsp;", "10%");
		$SKIN->td_header[] = array("Forum Name", "60%");
		$SKIN->td_header[] = array("Posts", "15%");
		$SKIN->td_header[] = array("Topics", "15%");

		$ADMIN->html .= $SKIN->start_table("Your Categories and Forums");

		$cats   = array();
		$forums = array();

		$stmt = $ibforums->db->query("SELECT * from ibf_categories WHERE id > 0 ORDER BY position ASC");
		while ($r = $stmt->fetch())
		{
			$cats[] = $r;
		}

		$stmt = $ibforums->db->query("SELECT * from ibf_forums ORDER BY position ASC");
		while ($r = $stmt->fetch())
		{
			$forums[] = $r;
		}

		// Build up the drop down box

		$form_array = array();

		for ($i = 1; $i <= count($cats); $i++)
		{
			$form_array[] = array($i, $i);
		}

		$last_cat_id = -1;

		foreach ($cats as $c)
		{

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       $SKIN->form_dropdown('POS_' . $c['id'], $form_array, $c['position']),
			                                       $c['name'],
			                                       '&nbsp;',
			                                       '&nbsp;',
			                                  ), 'pformstrip');
			$last_cat_id = $c['id'];

			foreach ($forums as $r)
			{

				if ($r['category'] == $last_cat_id)
				{
					$ADMIN->html .= $SKIN->add_td_row(array(
					                                       '&nbsp;',
					                                       "<b>" . $r['name'] . "</b>",
					                                       $r['posts'],
					                                       $r['topics'],
					                                  ));
				}
			}
		}

		$ADMIN->html .= $SKIN->end_form("Adjust Category Ordering");

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	//+---------------------------------------------------------------------------------

	function do_reorder()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$stmt = $ibforums->db->query("SELECT id from ibf_categories");

		while ($r = $stmt->fetch())
		{
			$order_query = $ibforums->db->exec("UPDATE ibf_categories SET position='" . $IN['POS_' . $r['id']] . "' WHERE id='" . $r['id'] . "'");
		}

		$ADMIN->save_log("Re-ordered categories");

		$ADMIN->done_screen("Category Ordering Adjusted", "Category Control", "act=cat");

	}

	//+---------------------------------------------------------------------------------
	//
	// REMOVE CATEGORY
	//
	//+---------------------------------------------------------------------------------

	function remove_form()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$form_array = array();

		if ($IN['c'] == "")
		{
			$ADMIN->error("Could not determine the category ID to update.");
		}

		$stmt = $ibforums->db->query("SELECT id, name FROM ibf_categories WHERE id > 0 ");

		//+-------------------------------
		// Make sure we have more than 1
		// category..
		//+-------------------------------

		if ($stmt->rowCount() < 2)
		{
			$ADMIN->error("Can not remove this category, please create another before attempting to remove this one");
		}

		while ($r = $stmt->fetch())
		{
			if ($r['id'] == $IN['c'])
			{
				continue;
			}

			$form_array[] = array($r['id'], $r['name']);
		}

		//+-------------------------------
		// Get the details for this category...
		//+-------------------------------

		$stmt = $ibforums->db->query("SELECT * FROM ibf_categories WHERE id='" . $IN['c'] . "'");
		$cat  = $stmt->fetch();

		//+-------------------------------

		$ADMIN->page_title = "Removing category '{$cat['name']}'";

		$ADMIN->page_detail = "Before we remove this category, we need to determine what to do with any forums you may have left in this category.";

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'doremove'),
		                                       2 => array('act', 'cat'),
		                                       3 => array('c', $IN['c']),
		                                       4 => array('name', $cat['name']),
		                                  ));

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Required");

		$ADMIN->html .= $SKIN->add_td_row(array("<b>Category to remove: </b>", $cat['name']));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Move all <i>existing forums in this category</i> to which category?</b>",
		                                       $SKIN->form_dropdown("MOVE_ID", $form_array)
		                                  ));

		$ADMIN->html .= $SKIN->end_form("Move forums and delete this category");

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	//+---------------------------------------------------------------------------------

	function do_remove()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		if ($IN['c'] == "")
		{
			$ADMIN->error("Could not determine the source category ID.");
		}

		if ($IN['MOVE_ID'] == "")
		{
			$ADMIN->error("Could not determine the destination category ID.");
		}

		$ibforums->db->exec("UPDATE ibf_forums SET category='" . $IN['MOVE_ID'] . "' WHERE category='" . $IN['c'] . "'");

		$ibforums->db->exec("DELETE FROM ibf_categories WHERE id='" . $IN['c'] . "'");

		$ADMIN->save_log("Removed category '{$IN['name']}'");

		$ADMIN->done_screen("Category Removed", "Category Control", "act=cat");

	}

	//+---------------------------------------------------------------------------------
	//
	// EDIT CATEGORY
	//
	//+---------------------------------------------------------------------------------

	function edit_form()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$subcats = array();

		$stmt = $ibforums->db->query("SELECT id, name FROM ibf_categories WHERE id > 0");

		while ($r = $stmt->fetch())
		{
			if ($r['id'] == $IN['c'])
			{
				continue;
			}

			$subcats[] = array($r['id'], $r['name']);
		}

		$stmt = $ibforums->db->query("SELECT * FROM ibf_categories WHERE id='" . $IN['c'] . "'");
		$cat  = $stmt->fetch();

		$ADMIN->page_title = "Edit a category";

		$ADMIN->page_detail = "This section will allow you to edit a new category.";

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'doedit'),
		                                       2 => array('VIEW', '*'),
		                                       3 => array('act', 'cat'),
		                                       4 => array('c', $IN['c']),
		                                  ));

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "30%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Required");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Category Name</b>",
		                                       $SKIN->form_input("CAT_NAME", $cat['name'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Category State</b><br />Unless disabled, categories will show in search selection box",
		                                       $SKIN->form_dropdown("CAT_STATE", array(
		                                                                              0 => array(1, 'Visible'),
		                                                                              1 => array(0, 'Disable'),
		                                                                              2 => array(
			                                                                              2,
			                                                                              'Hidden from forumhome list - can access via URL and forum jump'
		                                                                              ),
		                                                                              3 => array(
			                                                                              3,
			                                                                              'Hidden from forumhome list - can access via URL - removed from forum jump'
		                                                                              ),
		                                                                         ), $cat['state'])
		                                  ));

		/*$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------

		$SKIN->td_header[] = array( "&nbsp;"  , "30%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );

		$ADMIN->html .= $SKIN->start_table( "Optional" );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Category Sponsor Image</b><br>(Example: http://www.domain.com/image.gif)" ,
												  $SKIN->form_input("IMAGE", $cat['image'])
									     )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Category Sponsor Link</b><br>(Example: http://www.domain.com/)" ,
												  $SKIN->form_input("URL", $cat['url'])
									     )      );*/

		$ADMIN->html .= $SKIN->end_form("Amend this category");

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	//+---------------------------------------------------------------------------------

	function do_edit()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$IN['CAT_NAME'] = trim($IN['CAT_NAME']);

		if ($IN['CAT_NAME'] == "")
		{
			$ADMIN->error("You must enter a category title");
		}

		if ($IN['c'] == "")
		{
			$ADMIN->error("Could not determine the category ID to update.");
		}

		$data = [
			'state'       => $IN['CAT_STATE'],
			'name'        => $IN['CAT_NAME'],
			'description' => $IN['CAT_DESC'],
			'image'       => $IN['IMAGE'],
			'url'         => $IN['URL'],
		];

		$ibforums->db->updateRow("ibf_categories", array_map([$ibforums->db, 'quote'], $data), "id='" . $IN['c'] . "'");

		$ADMIN->save_log("Edited Category '{$IN['CAT_NAME']}'");

		$ADMIN->done_screen("Category '{$IN['CAT_NAME']}' Edited", "Category Control", "act=cat");

	}

	//+---------------------------------------------------------------------------------
	//
	// SHOW CATS
	// Renders a complete listing of all the forums and categories.
	//
	//+---------------------------------------------------------------------------------

	function show_cats()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$ADMIN->page_title  = "Category and Forums Overview";
		$ADMIN->page_detail = "<img src='{$SKIN->img_url}/acp_rules.gif' border='0'> <b>Forum Rules</b> This allows you to add/edit or remove rules for this forum
							   <br /><img src='{$SKIN->img_url}/acp_edit.gif' border='0'> <b>Skin Options</b> This allows you to add/edit or remove a skin for this forum
							   <br /><img src='{$SKIN->img_url}/acp_resync.gif' border='0'> <b>Resynchronise</b> This allows you to recount the forum posts, topics and last post information";

		$cats        = array();
		$forums      = array();
		$children    = array();
		$this->skins = array();

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
			}

		}

		$stmt = $ibforums->db->query("SELECT uid, sname, sid FROM ibf_skins");
		while ($s = $stmt->fetch())
		{
			$this->skins[$s['sid']] = $s['sname'];
		}

		$SKIN->td_header[] = array("{none}", "40%");
		$SKIN->td_header[] = array("{none}", "20%");
		$SKIN->td_header[] = array("{none}", "15%");
		$SKIN->td_header[] = array("{none}", "20%");

		$ADMIN->html .= $SKIN->start_table("Your Categories and Forums");

		$last_cat_id = -1;

		foreach ($cats as $c)
		{

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<a href='{$INFO['board_url']}/index.php?c={$c['id']}' target='_blank'>" . $c['name'] . "</a>",
			                                       "<center><a href='{$ADMIN->base_url}&act=cat&code=doeditform&c={$c['id']}'>Edit</a></center>",
			                                       '&nbsp;',
			                                       "<center><a href='{$ADMIN->base_url}&act=cat&code=remove&c={$c['id']}'>Delete</a></center>",
			                                  ), 'pformstrip');
			$last_cat_id = $c['id'];

			foreach ($forums as $r)
			{

				if ($r['category'] == $last_cat_id)
				{

					if ($r['skin_id'])
					{
						$skin_stuff = "<br>[ " . "Using Skin Set: " . $this->skins[$r['skin_id']] . " ]";
					} else
					{
						$skin_stuff = "";
					}

					$redirect = ($r['redirect_on'] == 1)
						? ' (Redirect Forum)'
						: '';

					if ($r['subwrap'] == 1)
					{

						//						if ($r['sub_can_post'])
						//						{
						$ADMIN->html .= $SKIN->add_td_row(array(
						                                       " - <b>" . $r['name'] . "</b>$redirect $skin_stuff",
						                                       "<center><b><a href='{$ADMIN->base_url}&act=forum&code=subedit&f={$r['id']}'>Settings</a></b>" . " | <a href='{$ADMIN->base_url}&act=forum&code=pedit&f={$r['id']}'>Permissions</a></center>",
						                                       "<center><a href='{$ADMIN->base_url}&act=forum&code=frules&f={$r['id']}'><img src='{$SKIN->img_url}/acp_rules.gif' border='0' title='Forum Rules'></a>&nbsp;&nbsp;" . "<a href='{$ADMIN->base_url}&act=forum&code=skinedit&f={$r['id']}'><img src='{$SKIN->img_url}/acp_edit.gif' border='0' title='Skin Options'></a>&nbsp;&nbsp;" . "<a href='{$ADMIN->base_url}&act=forum&code=recount&f={$r['id']}'><img src='{$SKIN->img_url}/acp_resync.gif' border='0' title='Resynchronise'></a></center>",
						                                       "<center><a href='{$ADMIN->base_url}&act=forum&code=subdelete&f={$r['id']}'>Delete</a>" . " | <b><a href='{$ADMIN->base_url}&act=forum&code=empty&f={$r['id']}'>Empty Forum</a></b></center>",
						                                  ), 'subforum');
						//						}
						//						else
						//						{
						//							$ADMIN->html .= $SKIN->add_td_row( array(
						//																	   " - <b>".$r['name']."</b>$redirect $skin_stuff",
						//																	   "<a href='{$ADMIN->base_url}&act=forum&code=subedit&f={$r['id']}'>Edit</a>",
						//																	   "<a href='{$ADMIN->base_url}&act=forum&code=skinedit&f={$r['id']}'>Skin Options</a>",
						//																	   "<a href='{$ADMIN->base_url}&act=forum&code=subdelete&f={$r['id']}'>Delete</a>",
						//															 )   , 'subforum' );
						//						}
					} else
					{
						$ADMIN->html .= $SKIN->add_td_row(array(
						                                       "<b>" . $r['name'] . "</b>$redirect $skin_stuff<br>",
						                                       "<center><b><a href='{$ADMIN->base_url}&act=forum&code=edit&f={$r['id']}'>Settings</a></b>" . " | <a href='{$ADMIN->base_url}&act=forum&code=pedit&f={$r['id']}'>Permissions</a></center>",
						                                       "<center><a href='{$ADMIN->base_url}&act=forum&code=frules&f={$r['id']}'><img src='{$SKIN->img_url}/acp_rules.gif' border='0' title='Forum Rules'></a>&nbsp;&nbsp;" . "<a href='{$ADMIN->base_url}&act=forum&code=skinedit&f={$r['id']}'><img src='{$SKIN->img_url}/acp_edit.gif' border='0' title='Skin Options'></a>&nbsp;&nbsp;" . "<a href='{$ADMIN->base_url}&act=forum&code=recount&f={$r['id']}'><img src='{$SKIN->img_url}/acp_resync.gif' border='0' title='Resynchronise'></a></center>",
						                                       "<center><a href='{$ADMIN->base_url}&act=forum&code=delete&f={$r['id']}'>Delete</a>" . " | <b><a href='{$ADMIN->base_url}&act=forum&code=empty&f={$r['id']}'>Empty Forum</a></b></center>",
						                                  ));
					}

					// Song * infinite subforums, 17.12.04

					$this->subforums_addtorow($children, $r['id'], 0);

					// Song * infinite subforums, 17.12.04
				}
			}
		}

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	//+---------------------------------------------------------------------------------
	//
	// NEW CATEGORY
	//
	//+---------------------------------------------------------------------------------

	function new_form()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$cat_name = "";

		if ($_GET['name'] != "")
		{
			$cat_name = $std->txt_stripslashes(urldecode($_GET['name']));
		}

		$ADMIN->page_title = "Add a new category";

		$ADMIN->page_detail = "This section will allow you to add a new category to your forums.";

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'donew'),
		                                       2 => array('act', 'cat'),
		                                  ));

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "30%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Required");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Category Name</b>",
		                                       $SKIN->form_input("CAT_NAME", $cat_name)
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Category State</b><br />Unless disabled, categories will show in search selection box",
		                                       $SKIN->form_dropdown("CAT_STATE", array(
		                                                                              0 => array(1, 'Visible'),
		                                                                              1 => array(0, 'Disable'),
		                                                                              2 => array(
			                                                                              2,
			                                                                              'Hidden from forumhome list - can access via URL and forum jump'
		                                                                              ),
		                                                                              3 => array(
			                                                                              3,
			                                                                              'Hidden from forumhome list - can access via URL - removed from forum jump'
		                                                                              ),
		                                                                         ), "1")
		                                  ));

		/*$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------

		$SKIN->td_header[] = array( "&nbsp;"  , "30%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );

		$ADMIN->html .= $SKIN->start_table( "Optional" );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Category Sponsor Image</b><br>(Example: http://www.domain.com/image.gif)" ,
												  $SKIN->form_input("IMAGE")
									     )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Category Sponsor Link</b><br>(Example: http://www.domain.com/)" ,
												  $SKIN->form_input("URL")
									     )      );*/

		$ADMIN->html .= $SKIN->end_form("Create this category");

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	//+---------------------------------------------------------------------------------

	function do_new()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::instance();

		$IN['CAT_NAME'] = trim($IN['CAT_NAME']);

		if ($IN['CAT_NAME'] == "")
		{
			$ADMIN->error("You must enter a category title");
		}

		// Get the new cat id. We could use auto_incrememnt, but we need the ID to use as the default
		// category position...

		$stmt = $ibforums->db->query("SELECT MAX(id) as top_cat FROM ibf_categories"); //ooh, top cat - he's the leader of our gang..
		$row  = $stmt->fetch();

		if ($row['top_cat'] < 1)
		{
			$row['top_cat'] = 0;
		}

		$row['top_cat']++;

		$data = [
			'id'          => $row['top_cat'],
			'position'    => $row['top_cat'],
			'state'       => $IN['CAT_STATE'],
			'name'        => $IN['CAT_NAME'],
			'description' => $IN['CAT_DESC'],
			'image'       => $IN['IMAGE'],
			'url'         => $IN['URL'],
		];

		$ibforums->db->insertRow("ibf_categories", $data);

		$ADMIN->save_log("Added Category '{$IN['CAT_NAME']}'");

		$ADMIN->done_screen("Category {$IN['CAT_NAME']} created", "Category Control", "act=cat");

	}

	// Song * endless forums, 19.12.04

	function delete_forum_link($children, $row)
	{
		global $ADMIN;
		$ibforums = Ibf::instance();

		return (!count($children[$row['id']]) > 0)
			? "<center><a href='{$ADMIN->base_url}&act=forum&code=delete&f={$row['id']}'>Delete</a> | "
			: "<center>";

	}

	function subforums_addtorow($children, $id, $level)
	{
		global $ADMIN, $SKIN;
		$ibforums = Ibf::instance();

		if (!isset($children[$id]) || count($children[$id]) <= 0)
		{
			return;
		}

		foreach ($children[$id] as $idx => $rd)
		{

			if ($rd['skin_id'])
			{
				$skin_stuff = "<br>[ " . "Using Skin Set: " . $this->skins[$rd['skin_id']] . " ]";
			} else
			{
				$skin_stuff = "";
			}

			$redirect = ($rd['redirect_on'] == 1)
				? ' (Redirect Forum)'
				: '';

			$t_level_char = "+--";
			for ($i = 0; $i < $level; $i++)
			{
				$t_level_char .= "--";
			}

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       " " . $t_level_char . " <b>" . $rd['name'] . "</b>$redirect $skin_stuff<br>",
			                                       "<center><b><a href='{$ADMIN->base_url}&act=forum&code=edit&f={$rd['id']}'>Settings</a></b>" . " | <a href='{$ADMIN->base_url}&act=forum&code=pedit&f={$rd['id']}'>Permissions</a></center>",
			                                       "<center><a href='{$ADMIN->base_url}&act=forum&code=frules&f={$rd['id']}'><img src='{$SKIN->img_url}/acp_rules.gif' border='0' title='Forum Rules'></a>&nbsp;&nbsp;" . "<a href='{$ADMIN->base_url}&act=forum&code=skinedit&f={$rd['id']}'><img src='{$SKIN->img_url}/acp_edit.gif' border='0' title='Skin Options'></a>&nbsp;&nbsp;" . "<a href='{$ADMIN->base_url}&act=forum&code=recount&f={$rd['id']}'><img src='{$SKIN->img_url}/acp_resync.gif' border='0' title='Resynchronise'></a></center>",
			                                       $this->delete_forum_link($children, $rd) . "<b><a href='{$ADMIN->base_url}&act=forum&code=empty&f={$rd['id']}'>Empty Forum</a></b></center>",
			                                  ), 'subforum');

			$this->subforums_addtorow($children, $rd['id'], $level + 1);
		}

		// Song * endless forums, 19.12.04

	}

}

?>
