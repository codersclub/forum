<?php

/*
+--------------------------------------------------------------------------
|   Download manager
|   ========================================
|   by Parmeet Singh (Improved By Ryan Ong and Brandon Farber)
|
|	Extended by bfarber for use with Download System
|     (c) 2001,2002,2003 IBForums
|	{c} 2003 Bfarber
|   	http://www.phpwiz.net
|	http://bfarber.com
|   ========================================
|   Web: http://www.phpwiz.net
|   Email: parmeet@emirates.net.ae
|   IBFORUMS: Licence Info: phpib-licence@ibforums.com
+---------------------------------------------------------------------------
|   ========================================
|   Web: http://bfarber.net
|   Email: bfarber@bfarber.com
+---------------------------------------------------------------------------
|
|
|   > Admin Forum function
|   > Module written by Parmeet
|   > Extended by bfarber for use with Download System
|   > Date started: 23th July 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new ad_downloads();

class ad_downloads
{

	var $base_url;

	function __construct()
	{
		global $IN, $root_path, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		switch ($IN['code'])
		{
			case 'showaddcat':
				$this->show_cat('add');
				break;

			case 'showdelcat':
				$this->show_cat('del');
				break;

			case 'showeditcat':
				$this->show_cat('edit');
				break;

			case 'showeditcat1':
				$this->show_cat('edit1');
				break;

			case 'doaddcat':
				$this->do_cat('add');
				break;

			case 'dodelcat':
				$this->do_cat('del');
				break;

			case 'doeditcat':
				$this->do_cat('edit');
				break;

			case 'switch':
				$this->switch_download();
				break;

			case 'settings':
				$this->show_edit_vars();
				break;

			case 'do_switch':
				$this->save_config(array('d_section_close'));
				break;

			case 'editvars':
				$this->edit_vars();
				break;

			case 'reorder':
				$this->reorder_form();
				break;

			case 'doreorder':
				$this->do_reorder();
				break;

			default:
				$this->main_screen();
				break;
		}

	}

	//-------------------------------------------------------------
	//
	// Save config. Does the hard work, so you don't have to.
	//
	//--------------------------------------------------------------

	function save_config($new)
	{
		global $IN, $root_path, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_VARS;
		$ibforums = Ibf::app();

		$master = array();

		if (is_array($new))
		{
			if (count($new) > 0)
			{
				foreach ($new as $field)
				{

					// Handle special..

					if ($field == 'img_ext' or $field == 'avatar_ext')
					{
						$HTTP_POST_VARS[$field] = preg_replace("/[\.\s]/", "", $HTTP_POST_VARS[$field]);
						$HTTP_POST_VARS[$field] = preg_replace("/,/", '|', $HTTP_POST_VARS[$field]);
					} else
					{
						if ($field == 'coppa_address')
						{
							$HTTP_POST_VARS[$field] = nl2br($HTTP_POST_VARS[$field]);
						}
					}

					$HTTP_POST_VARS[$field] = preg_replace("/'/", "&#39;", stripslashes($HTTP_POST_VARS[$field]));

					$master[$field] = stripslashes($HTTP_POST_VARS[$field]);
				}

				$ADMIN->rebuild_config($master);
			}
		}

		$ADMIN->save_log("Board Downloads System Updated, Back Up Written");

		$ADMIN->done_screen("Download Configuration Updated", "Download Administration Home", "act=downloads");

	}

	function show_cat($type = "add")
	{
		global $IN, $root_path, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		if ($type == "add")
		{
			$a_array   = array();
			$a_array[] = array(1, "Yes");
			$a_array[] = array(0, "No");

			$s_array   = array();
			$s_array[] = array(1, "Yes");
			$s_array[] = array(0, "No");

			$abc_array   = array();
			$abc_array[] = array(1, "Yes");
			$abc_array[] = array(0, "No");

			$sn_array   = array();
			$sn_array[] = array(1, "Yes");
			$sn_array[] = array(0, "No");

			$as_array   = array();
			$as_array[] = array(1, "Yes");
			$as_array[] = array(0, "No");

			$stmt      = $ibforums->db->query("SELECT * from ibf_files_cats WHERE sub=0");
			$e_array   = array();
			$e_array[] = array(0, "None");
			while ($row = $stmt->fetch())
			{
				$e_array[] = array($row['cid'], $row['cname']);

			}

			$stmt       = $ibforums->db->query("SELECT * FROM ibf_forums");
			$tt_array   = array();
			$tt_array[] = array(0, "<b>None</b>");
			while ($row = $stmt->fetch())
			{
				$tt_array[] = array($row['id'], "--{$row['name']}");

			}

			$ADMIN->page_title = "Add a download category!";

			$ADMIN->page_detail = "This page helps you add categories for your download system.";

			//+-------------------------------

			$ADMIN->html .= $SKIN->start_form(array(
			                                       1 => array('code', 'doaddcat'),
			                                       2 => array('act', 'downloads'),
			                                  ));

			$ADMIN->html .= $SKIN->start_table("Add Category");

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Name</b>",
			                                       $SKIN->form_input("cname", "", "text"),
			                                  ));

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Description (optional)</b>",
			                                       $SKIN->form_input("cdesc", "", "text"),
			                                  ));

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Do you want screenshots to automatically display on the category view page for this category?</b><br>--Note: If you have set this globally to Yes or No, this setting is ignored.",
			                                       $SKIN->form_dropdown("dis_screen_cat", $s_array, "text"),
			                                  ));

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Do you want screenshots to automatically display on the download page for this category?</b><br>--Note: If you have set this globally to Yes or No, this setting is ignored.",
			                                       $SKIN->form_dropdown("dis_screen", $s_array, "text"),
			                                  ));

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Do you want screenshots to be required in this category?</b><br>--Note: If you have set this globally to Yes or No, this setting is ignored.",
			                                       $SKIN->form_dropdown("screen_req", $abc_array, "text"),
			                                  ));

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Do you want to accept files in this category before being available for download?</b><br>--Note: If you have set this globally to Yes or No, this setting is ignored.",
			                                       $SKIN->form_dropdown("authorize", $as_array, "text"),
			                                  ));

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>In which forum do you wish for files submitted to this category to automautically create a topic?</b><br />--Note: If this has a global setting other than \"Per-Category\" this setting will be ignored.",
			                                       $SKIN->form_dropdown("fordaforum", $tt_array, "text"),
			                                  ));

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Which groups should have access to this category?</b><br>(NOTE: You can use \"*\" for all groups, or a comma-separated list for more control)",
			                                       $SKIN->form_input("group_perm", "", "text"),
			                                  ));

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Do you want to show category-specific notes for this category?</b>",
			                                       $SKIN->form_dropdown("show_notes", $sn_array, "text"),
			                                  ));

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Enter your category-specific notes:</b>",
			                                       $SKIN->form_textarea("cnotes", ""),
			                                  ));

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Do you want this category to be open?</b>",
			                                       $SKIN->form_dropdown("copen", $a_array, "text"),
			                                  ));

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Choose the category of which this category should be a sub category of.</b><br>If you do not want this category to be sub category then choose 'None'",
			                                       $SKIN->form_dropdown("sub", $e_array, "text"),
			                                  ));

			$ADMIN->html .= $SKIN->end_form("Add Category");

			$ADMIN->html .= $SKIN->end_table();

			$ADMIN->output();
		} elseif ($type == "del")
		{

			$ADMIN->page_title = "Deleting categories!";

			$ADMIN->page_detail = "This page helps you delete categories.";

			$stmt    = $ibforums->db->query("SELECT * from ibf_files_cats");
			$d_array = array();
			$w_array = array();

			while ($row = $stmt->fetch())
			{
				$d_array[] = array($row['cid'], $row['cname']);
				$w_array[] = array($row['cid'], $row['cname']);
			}
			//+-------------------------------

			$ADMIN->html .= $SKIN->start_form(array(
			                                       1 => array('code', 'dodelcat'),
			                                       2 => array('act', 'downloads'),
			                                  ));

			$ADMIN->html .= $SKIN->start_table("Delete Category");

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Choose which category to delete</b>",
			                                       $SKIN->form_dropdown("del", $d_array, "text"),
			                                  ));

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>To which category would you like to transfer the scripts belonging to this category</b>",
			                                       $SKIN->form_dropdown("trans", $w_array, "text"),
			                                  ));

			$ADMIN->html .= $SKIN->end_form("Delete Category");

			$ADMIN->html .= $SKIN->end_table();

			$ADMIN->output();
		} elseif ($type == "edit")
		{

			$ADMIN->page_title = "Editing download categories!";

			$ADMIN->page_detail = "This page helps you edit caegories for your download system.";

			$stmt    = $ibforums->db->query("SELECT * from ibf_files_cats");
			$e_array = array();

			while ($row = $stmt->fetch())
			{
				$e_array[] = array($row['cid'], $row['cname']);
			}
			//+-------------------------------

			$ADMIN->html .= $SKIN->start_form(array(
			                                       1 => array('code', 'showeditcat1'),
			                                       2 => array('act', 'downloads'),
			                                  ));

			$ADMIN->html .= $SKIN->start_table("Edit Categories");

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Choose which category to edit</b>",
			                                       $SKIN->form_dropdown("cid", $e_array, "text"),
			                                  ));

			$ADMIN->html .= $SKIN->end_form("Edit Category");

			$ADMIN->html .= $SKIN->end_table();

			$ADMIN->output();

		} elseif ($type == "edit1")
		{

			if ($IN['cid'] == "")
			{
				$ADMIN->error("No category was specified...");
			}

			$stmt      = $ibforums->db->query("SELECT * from ibf_files_cats WHERE sub=0");
			$e_array   = array();
			$e_array[] = array(0, "None");
			while ($row = $stmt->fetch())
			{
				$e_array[] = array($row['cid'], $row['cname']);

			}

			$stmt       = $ibforums->db->query("SELECT * FROM ibf_forums");
			$tt_array   = array();
			$tt_array[] = array(0, "<b>None</b>");
			while ($row = $stmt->fetch())
			{
				$tt_array[] = array($row['id'], "--{$row['name']}");

			}

			$ADMIN->page_title = "Editing download categories!";

			$ADMIN->page_detail = "This page helps you edit caegory for your download module.";

			$stmt = $ibforums->db->query("SELECT * FROM ibf_files_cats WHERE cid = " . $IN['cid']);

			$row = $stmt->fetch();

			$auth       = $row['authorize'] == 1
				? 1
				: 0;
			$dis        = $row['dis_screen'] == 1
				? 1
				: 0;
			$notes      = $row['show_notes'] == 1
				? 1
				: 0;
			$screen_req = $row['screen_req'] == 1
				? 1
				: 0;

			$open = $row['copen'] == 1
				? 1
				: 0;
			//+-------------------------------

			$ADMIN->html .= $SKIN->start_form(array(
			                                       1 => array('code', 'doeditcat'),
			                                       2 => array('act', 'downloads'),
			                                  ));
			$a   = array();
			$a[] = array('cid', $IN['cid']);

			$ADMIN->html .= $SKIN->form_hidden($a);

			$ADMIN->html .= $SKIN->start_table("Edit Categories");

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Name</b>",
			                                       $SKIN->form_input("cname", $row['cname'], "text"),
			                                  ));

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Description (optional)</b>",
			                                       $SKIN->form_input("cdesc", $row['cdesc'], "text"),
			                                  ));

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Do you want screenshots to automatically display in the category view page for this category?</b><br>--Note: If you have set this globally to Yes or No, this setting is ignored.",
			                                       $SKIN->form_yes_no("dis_screen_cat", $dis),
			                                  ));

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Do you want screenshots to automatically display on the download page for this category?</b><br>--Note: If you have set this globally to Yes or No, this setting is ignored.",
			                                       $SKIN->form_yes_no("dis_screen", $dis),
			                                  ));

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Do you want screenshots to be required in this category?</b><br>--Note: If you have set this globally to Yes or No, this setting is ignored.",
			                                       $SKIN->form_yes_no("screen_req", $screen_req),
			                                  ));

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Do you want to accept files in this category before being available to the public?</b><br>--Note: If you have set this globally to Yes or No, this setting is ignored.",
			                                       $SKIN->form_yes_no("authorize", $auth),
			                                  ));

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>In which forum do you wish for files submitted to this category to automautically create a topic?</b><br />--Note: If this has a global setting other than \"Per-Category\" this setting will be ignored.",
			                                       $SKIN->form_dropdown("fordaforum", $tt_array, $row['fordaforum']),
			                                  ));

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Which groups should have access to this category?</b><br>(NOTE: You can use \"*\" for all groups, or a comma-separated list for more control)",
			                                       $SKIN->form_input("group_perm", $row['group_perm']),
			                                  ));

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Do you want to show category-specific notes for this category?",
			                                       $SKIN->form_yes_no("show_notes", $notes),
			                                  ));

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Enter your category-specific notes:</b>",
			                                       $SKIN->form_textarea("cnotes", $row['cnotes']),
			                                  ));

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Choose the category of which this category should be a sub category of.</b><br>If you do not want this category to be sub category then choose 'None'",
			                                       $SKIN->form_dropdown("sub", $e_array, $row['sub']),
			                                  ));

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<b>Do you want this category to be open?</b>",
			                                       $SKIN->form_yes_no("copen", $open),
			                                  ));

			$ADMIN->html .= $SKIN->end_form("Edit Category");

			$ADMIN->html .= $SKIN->end_table();

			$ADMIN->output();

		}
	}

	//+---------------------------------------------------------------------------------
	//
	// RE-ORDER CATEGORY
	//
	//+---------------------------------------------------------------------------------
	function reorder_form()
	{
		global $IN, $root_path, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$ADMIN->page_title  = "Category Re-Order";
		$ADMIN->page_detail = "To re-order the categories, simply choose the position number from the drop down box next to each category title.  When you are satisfied with the ordering, simply hit the submit button at the bottom of the form.";

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'doreorder'),
		                                       2 => array('act', 'downloads'),
		                                  ));

		$SKIN->td_header[] = array("&nbsp;", "10%");
		$SKIN->td_header[] = array("Category Name", "90%");

		$ADMIN->html .= $SKIN->start_table("Your Categories");

		$cats = array();

		$stmt = $ibforums->db->query("SELECT * from ibf_files_cats WHERE cid > 0 ORDER BY position ASC");
		while ($r = $stmt->fetch())
		{
			$cats[] = $r;
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
			                                       $SKIN->form_dropdown('POS_' . $c['cid'], $form_array, $c['position']),
			                                       $c['cname'],
			                                  ), 'catrow');
			$last_cat_id = $c['cid'];

			if ($r['category'] == $last_cat_id)
			{
				$ADMIN->html .= $SKIN->add_td_row(array(
				                                       '&nbsp;',
				                                       "<b>" . $r['cname'] . "</b><br>" . $r['cdesc'],
				                                       $r['posts'],
				                                       $r['topics'],
				                                  ));
			}
		}

		$ADMIN->html .= $SKIN->end_form("Adjust Category Ordering");

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	//+---------------------------------------------------------------------------------

	function do_reorder()
	{
		global $IN, $root_path, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$stmt = $ibforums->db->query("SELECT cid from ibf_files_cats");

		while ($r = $stmt->fetch())
		{
			$order_query = $ibforums->db->exec("UPDATE ibf_files_cats SET position='" . $IN['POS_' . $r['cid']] . "' WHERE cid='" . $r['cid'] . "'");
		}

		$ADMIN->done_screen("Category Ordering Adjusted", "Category Control", "act=cat");

	}

	function do_cat($type = "add")
	{

		global $IN, $root_path, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		if ($type == "add")
		{
			if ($IN['cname'] == "")
			{
				$ADMIN->error("The name field was not filled in...");
			}

			$stmt = $ibforums->db->query("SELECT MAX(cid) as mid FROM ibf_files_cats");
			$row  = $stmt->fetch();

			$last_id = $row['mid'] + 1;
			if ($IN['sub'] == 0 || $IN['sub'] == "")
			{
				$stmt = $ibforums->db->query("SELECT cname FROM ibf_files_cats WHERE cname = '" . $IN['cname'] . "' AND sub = 0");
			} else
			{
				$stmt = $ibforums->db->query("SELECT cname FROM ibf_files_cats WHERE cname = '" . $IN['cname'] . "' AND sub = '" . $IN['sub'] . "'");
			}

			if ($stmt->rowCount() > 0)
			{
				$ADMIN->error("Duplicate category name...");
			}

			$stmt = $ibforums->db->query("SELECT sub FROM ibf_files_cats WHERE cid = '" . $IN['sub'] . "'");

			if ($stmt->rowCount() > 0)
			{
				$ADMIN->error("The ID specified for the category does not exists or that category is already a sub category...");
			}

			$ibforums->db->exec("INSERT INTO ibf_files_cats ( cid , sub , cname , cdesc , copen, dis_screen, dis_screen_cat, screen_req, authorize, fordaforum, show_notes, cnotes, group_perm ) VALUES( '$last_id' , '" . $IN['sub'] . "' , '" . $IN['cname'] . "' , '" . $IN['cdesc'] . "' , '" . $IN['copen'] . "', '" . $IN['dis_screen'] . "', '" . $IN['dis_screen_cat'] . "', '" . $IN['screen_req'] . "', '" . $IN['authorize'] . "', '" . $IN['fordaforum'] . "', '" . $IN['show_notes'] . "', '" . $IN['cnotes'] . "', '" . $IN['group_perm'] . "'  )");

			$ADMIN->done_screen("Category created!", "Download Admin", "act=downloads");
		}
		if ($type == "edit")
		{
			if ($IN['cid'] == "")
			{
				$ADMIN->error("No category specified...");
			} elseif ($IN['cname'] == "")
			{
				$ADMIN->error("No name specified...");
			}

			$ibforums->db->exec("UPDATE ibf_files_cats SET cname = '" . $IN['cname'] . "' , sub = '" . $IN['sub'] . "' , cdesc = '" . $IN['cdesc'] . "' , copen = '" . $IN['copen'] . "', dis_screen = '" . $IN['dis_screen'] . "', dis_screen_cat = '" . $IN['dis_screen_cat'] . "', screen_req = '" . $IN['screen_req'] . "', authorize = '" . $IN['authorize'] . "', fordaforum = '" . $IN['fordaforum'] . "', show_notes = '" . $IN['show_notes'] . "', cnotes = '" . $IN['cnotes'] . "', group_perm= '" . $IN['group_perm'] . "'  WHERE cid = " . $IN['cid']);

			$ADMIN->done_screen("Category edited!", "Download Admin", "act=downloads");
		}
		if ($type == "del")
		{
			if ($IN['del'] == "")
			{
				$ADMIN->error("No category specified...");
			}
			if ($IN['trans'] == "")
			{
				$ADMIN->error("The category to which the files in this category must be transfered isnt specified...");
			}
			$ibforums->db->exec("UPDATE ibf_files SET cat = '" . $IN['trans'] . "' WHERE cat = '" . $IN['del'] . "'");
			$ibforums->db->exec("DELETE FROM ibf_files_cats WHERE cid = '" . $IN['del'] . "'");

			$ADMIN->done_screen("Category deleted and files have been tranfered!", "Download Admin", "act=downloads");
		}
	}

	function switch_download()
	{
		global $IN, $root_path, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();
		if (!$INFO['d_section_close'])
		{
			$status = "On";
		} else
		{
			$status = "Off";
		}
		$ADMIN->page_title  = "Download Settings (Downloads offline/online)";
		$ADMIN->page_detail = "Downloads are " . $status;
		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'do_switch'),
		                                       2 => array('act', 'downloads'),
		                                  ));

		$ADMIN->html .= $SKIN->start_table("Settings");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Do you want to set the download section offline?</b>",
		                                       $SKIN->form_yes_no("d_section_close", $INFO['d_section_close'])
		                                  ));
		$ADMIN->html .= $SKIN->end_form("Edit downloads");

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();
	}

	function show_edit_vars()
	{
		global $IN, $root_path, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums  = Ibf::app();
		$o_array   = array();
		$o_array[] = array(1, "Yes");
		$o_array[] = array(0, "No");

		$ADMIN->page_title = "Editing download section's configuration!";

		$ADMIN->page_detail = "This page lets you edit the configuration for the download section.";

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'editvars'),
		                                       2 => array('act', 'downloads'),
		                                  ));

		$ADMIN->html .= $SKIN->start_table("Edit configuration");
		$max_size = ini_get('upload_max_filesize');

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>What is the path to the download directory?</b>",
		                                       $SKIN->form_input("d_download_dir", $INFO['d_download_dir'], "text"),
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>What is the path to the screenshots directory?</b>",
		                                       $SKIN->form_input("d_screen_dir", $INFO['d_screen_dir'], "text"),
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>What is the url to the download directory?</b>",
		                                       $SKIN->form_input("d_download_url", $INFO['d_download_url'], "text"),
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>What is the url to the screenshots directory?</b>",
		                                       $SKIN->form_input("d_screen_url", $INFO['d_screen_url'], "text"),
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Do you want to upload files to the current server?</b>",
		                                       $SKIN->form_yes_no("d_upload", $INFO['d_upload']),
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Do you want to have linking downloads?<br>(Download files from external servers... Please do not leech)</b>",
		                                       $SKIN->form_yes_no("d_linking", $INFO['d_linking'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>What must be the maximum size of files that can be uploaded if uploads are allowed?</b><br />Your php.ini upload_max_filesize is set to " . $max_size . "<br />In KB(KiloBytes)",
		                                       $SKIN->form_input("d_max_dwnld_size", $INFO['d_max_dwnld_size'], "text"),
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Do you want to allow uploading of screenshots?</b>",
		                                       $SKIN->form_yes_no("d_screenshot_allowed", $INFO['d_screenshot_allowed'])
		                                  ));
		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Do you want to allow linked screenshots?</b>",
		                                       $SKIN->form_yes_no("d_screenshotl_allowed", $INFO['d_screenshotl_allowed'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>What must be the maximum size of screenshots that can be uploaded?</b><br>In KB(KiloBytes)",
		                                       $SKIN->form_input("d_screen_max_dwnld_size", $INFO['d_screen_max_dwnld_size'], "text"),
		                                  ));

		// Reconvert array into a text string
		$dext = "";
		foreach ($INFO['d_allowable_ext'] as $value)
		{
			$dext .= $value . "|";
		}
		$dext = mb_substr($dext, 0, -1);
		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>What are the allowable file extensions for files?</b><br>Must be seperated by '|' eg \".txt|.zip\"",
		                                       $SKIN->form_input("d_allowable_ext", $dext, "text"),
		                                  ));
		// Reconvert array into a text string
		$sext = "";
		foreach ($INFO['d_screenshot_ext'] as $value)
		{
			$sext .= $value . "|";
		}
		$sext = mb_substr($sext, 0, -1);
		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>What are the allowable file extensions for screenshots?</b><br>Must be seperated by '|' eg \".gif|.jpeg\"",
		                                       $SKIN->form_input("d_screenshot_ext", $sext, "text"),
		                                  ));

		// Reconvert array into a text string

		if ($INFO['d_files_perpage'] == "")
		{
			$pages = "10|20|30|40|50";
		} else
		{
			$pages = "";
			foreach ($INFO['d_files_perpage'] as $value)
			{
				$pages .= $value . "|";
			}
			$pages = mb_substr($pages, 0, -1);
		}
		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>What options do you want in the number of files per page dropdown menu?</b><br />Must be separated by '|' eg \"10|20|30\"",
		                                       $SKIN->form_input("d_files_perpage", $pages, "text"),
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>What is the default number of files to show per page?</b>",
		                                       $SKIN->form_input("d_perpage", $INFO['d_perpage'], "text"),
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Do you want Force Download Active?(This increases file security greatly and also renames the files correctly, however doesn't work on all servers.)</b>",
		                                       $SKIN->form_yes_no("d_force", $INFO['d_force']),
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>What is the Bandwidth limit?(How fast somebody can download in Kb/s. Force Download must be on. For no limit put 0.)</b>",
		                                       $SKIN->form_input("d_speed", $INFO['d_speed'], "text"),
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Do you want the script to generate thumbnails for you?</b><br /> ",
		                                       $SKIN->form_checkbox('d_show_thumb', $INFO['d_show_thumb']) . "Show Thumb? <br /> Size " . $SKIN->form_simple_input('d_thumb_w', $INFO['d_thumb_w']) . " x " . $SKIN->form_simple_input('d_thumb_h', $INFO['d_thumb_h'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Do you want to automautically check links to see if they are broken when displaying a file?</b><br />(NOTE: If your host doensn't allow fopen resource to operate outside of your root directory, this won't work)",
		                                       $SKIN->form_yes_no("d_link_check", $INFO['d_link_check'])
		                                  ));
		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Do you want to pull file sizes automatically from links?</b><br />(NOTE: If you get slow page loads on large linked files, turn this OFF)",
		                                       $SKIN->form_yes_no("d_link_check1", $INFO['d_link_check1'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Do you want to use the commenting system?</b><br />(NOTE: It's recommended not to use this in combination with auto-created topics, but instead use one or the other.)",
		                                       $SKIN->form_yes_no("d_use_comments", $INFO['d_use_comments'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Do you wish to add \"[category name]\" to the topic title?</b><br />This only applies if you set the script to automautically create a topic on new submissions.",
		                                       $SKIN->form_yes_no("d_cat_add", $INFO['d_cat_add']),
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Do you wish for administrator-submitted files to auto-accept (this overrides any acceptance configuration currently set)?</b>",
		                                       $SKIN->form_yes_no("d_admin_auto", $INFO['d_admin_auto']),
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Do you want to show the number of files a member has submitted in topics?</b>",
		                                       $SKIN->form_yes_no("d_topic", $INFO['d_topic']),
		                                  ));
		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Do you want to show the number of downloads a member has downloaded in topics?</b>",
		                                       $SKIN->form_yes_no("d_downloads", $INFO['d_downloads']),
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Do you want to show the Top Submitters Box?</b>",
		                                       $SKIN->form_yes_no("d_show_top_up", $INFO['d_show_top_up']),
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>How many top submitters would you like to spotlight?</b>",
		                                       $SKIN->form_input("d_how_many_top", $INFO['d_how_many_top']),
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Do you want to show Global Notes?</b><br />Note: You may choose no and still display per-category notes, or you may say yes and show both global and per-category notes.",
		                                       $SKIN->form_yes_no("d_show_global_notes", $INFO['d_show_global_notes']),
		                                  ));

		$return_nl = str_replace("<br />", "\n", $INFO['d_global_notes']);
		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Enter your global notes:</b>",
		                                       $SKIN->form_textarea("d_global_notes", $return_nl),
		                                  ));

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->end_table();
		$ADMIN->html .= $SKIN->start_table("Global vs Per-Category Settings");

		//-----------------------------------------------------------------------------------------------------------

		$ds_array   = array();
		$ds_array[] = array(0, "No");
		$ds_array[] = array(1, "Yes");
		$ds_array[] = array(2, "Per-Category");

		$as_array   = array();
		$as_array[] = array(0, "No");
		$as_array[] = array(1, "Yes");
		$as_array[] = array(2, "Per-Category");

		$ss_array   = array();
		$ss_array[] = array(0, "No");
		$ss_array[] = array(1, "Yes");
		$ss_array[] = array(2, "Per-Category");

		$stmt      = $ibforums->db->query("SELECT * FROM ibf_forums");
		$e_array   = array();
		$e_array[] = array(0, "<b>None</b>");
		$e_array[] = array('percat', "<b>Per-Category</b>");
		while ($row = $stmt->fetch())
		{
			$e_array[] = array($row['id'], "--{$row['name']}");

		}

		$ADMIN->html .= $SKIN->add_td_basic('If you would like the setting to be for all categories, choose a global answer (eg. yes, no, or a specific topic).<br>&nbsp;&nbsp;If you would like to configure these settings on a per-category basis, choose Per-Category.', 'left', 'catrow2');
		$ibforums = Ibf::app();

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Do you want to accept files before being available for downloading?",
		                                       $SKIN->form_dropdown("d_authorize", $as_array, $INFO['d_authorize']),
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Would you like to display screenshots automautically on the category view page?</b>",
		                                       $SKIN->form_dropdown("d_dis_screen_cat", $ds_array, $INFO['d_dis_screen_cat']),
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Would you like to display screenshots automautically on the download page?</b>",
		                                       $SKIN->form_dropdown("d_dis_screen", $ds_array, $INFO['d_dis_screen']),
		                                  ));
		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>If you allow screenshots, do you want to make it required?</b>",
		                                       $SKIN->form_dropdown("d_screenshot_required", $ss_array, $INFO['d_screenshot_required'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>If you want a topic to be created for each download submitted then choose the forum name.</b><br>If you do not want a topic created, choose none, or if you'd like to configure this on a per category basis, choose \"Per-Category\"",
		                                       $SKIN->form_dropdown("d_create_topic", $e_array, $INFO['d_create_topic']),
		                                  ));
		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->html .= $SKIN->end_form("Edit downloads");

		$ADMIN->output();
	}

	function edit_vars()
	{
		global $IN, $root_path, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_VARS;
		$ibforums = Ibf::app();

		if ($IN['d_max_dwnld_size'] == "")
		{
			$ADMIN->error("You did not specify the maximum upload size...");
		} elseif ($IN['d_download_dir'] == "")
		{
			$ADMIN->error("You did not specify the download directory...");
		} elseif ($IN['d_download_url'] == "")
		{
			$ADMIN->error("You did not specify the download url...");
		} elseif ($IN['d_allowable_ext'] == "")
		{
			$ADMIN->error("You did not specify the allowable file extensions...");
		} elseif ($IN['d_files_perpage'] == "")
		{
			$ADMIN->error("You did not specify what options to show for the how many files per page dropdown menus...");
		}

		$fix_notes = nl2br($HTTP_POST_VARS['d_global_notes']);
		$content   = "<?php\n";
		$allow1    = preg_replace("/&#124;/", "|", stripslashes($HTTP_POST_VARS['d_allowable_ext']));
		$allow1    = explode('|', $allow1);
		$allowable = "";
		foreach ($allow1 as $value)
		{
			$allowable .= "'" . mb_strtolower($value) . "', ";
		}
		$allowable = mb_substr($allowable, 0, -2);
		$content .= "\$INFO['d_allowable_ext']         = array($allowable);\n";
		$screen     = preg_replace("/&#124;/", "|", stripslashes($HTTP_POST_VARS['d_screenshot_ext']));
		$screen     = explode('|', $screen);
		$screenshot = "";
		foreach ($screen as $value)
		{
			$screenshot .= "'" . $value . "', ";
		}
		$screenshot = mb_substr($screenshot, 0, -2);
		$content .= "\$INFO['d_screenshot_ext']        	= array({$screenshot});\n";

		$filesperpage = preg_replace("/&#124;/", "|", stripslashes($HTTP_POST_VARS['d_files_perpage']));
		$filesperpage = explode('|', $filesperpage);
		$theoptions   = "";
		foreach ($filesperpage as $value)
		{
			$theoptions .= "'" . $value . "', ";
		}
		$theoptions = mb_substr($theoptions, 0, -2);
		$content .= "\$INFO['d_files_perpage']        	= array({$theoptions});\n";
		$content .= "\$INFO['d_max_dwnld_size']        	= {$IN['d_max_dwnld_size']};\n";
		$content .= "\$INFO['d_screen_max_dwnld_size'] 	= {$IN['d_screen_max_dwnld_size']};\n";
		$content .= "\$INFO['d_download_dir']          	= '{$IN['d_download_dir']}';\n";
		$content .= "\$INFO['d_download_url']          	= '{$IN['d_download_url']}';\n";
		$content .= "\$INFO['d_screen_dir']            	= '{$IN['d_screen_dir']}';\n";
		$content .= "\$INFO['d_screen_url']            	= '{$IN['d_screen_url']}';\n";
		$content .= "\$INFO['d_screenshot_allowed']     = '{$IN['d_screenshot_allowed']}';\n";
		$content .= "\$INFO['d_screenshotl_allowed']     = '{$IN['d_screenshotl_allowed']}';\n";
		$content .= "\$INFO['d_screenshot_required']    = '{$IN['d_screenshot_required']}';\n";
		$content .= "\$INFO['d_authorize']		      = {$IN['d_authorize']};\n";
		$content .= "\$INFO['d_speed']               	= '{$IN['d_speed']}';\n";
		$content .= "\$INFO['d_force']               	= {$IN['d_force']};\n";
		$content .= "\$INFO['d_linking']               	= {$IN['d_linking']};\n";
		$content .= "\$INFO['d_link_check']               	= '{$IN['d_link_check']}';\n";
		$content .= "\$INFO['d_link_check1']               	= '{$IN['d_link_check1']}';\n";
		$content .= "\$INFO['d_use_comments']		= {$IN['d_use_comments']};\n";
		$content .= "\$INFO['d_upload']               	= {$IN['d_upload']};\n";
		$content .= "\$INFO['d_perpage']		      = '{$IN['d_perpage']}';\n";
		$content .= "\$INFO['d_create_topic']		= '{$IN['d_create_topic']}';\n";
		$content .= "\$INFO['d_topic']                 	= {$IN['d_topic']};\n";
		$content .= "\$INFO['d_downloads']			= {$IN['d_downloads']};\n";
		$content .= "\$INFO['d_dis_screen_cat']		= {$IN['d_dis_screen_cat']};\n";
		$content .= "\$INFO['d_dis_screen']			= {$IN['d_dis_screen']};\n";
		$content .= "\$INFO['d_cat_add']			= {$IN['d_cat_add']};\n";
		$content .= "\$INFO['d_admin_auto']			= {$IN['d_admin_auto']};\n";
		$content .= "\$INFO['d_show_thumb']              = '{$IN['d_show_thumb']}';\n";
		$content .= "\$INFO['d_thumb_w']               	= '{$IN['d_thumb_w']}';\n";
		$content .= "\$INFO['d_thumb_h']               	= '{$IN['d_thumb_h']}';\n";
		$content .= "\$INFO['d_how_many_top']      = '{$IN['d_how_many_top']}';\n";
		$content .= "\$INFO['d_show_top_up']      = '{$IN['d_show_top_up']}';\n";
		$content .= "\$INFO['d_show_global_notes']      = '{$IN['d_show_global_notes']}';\n";
		$content .= "\$INFO['d_global_notes']           = '{$fix_notes}';\n";

		$content .= "?" . ">\n";

		$open = fopen($root_path . "downloads_config.php", "w");
		fwrite($open, $content);
		fclose($open);

		$ADMIN->done_screen("Settings have been edited successfully", "Download Admin", "act=downloads");
	}

	function main_screen()
	{
		global $IN, $root_path, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$stmt = $ibforums->db->query("SELECT cid FROM ibf_files_cats");
		$cats = $stmt->rowCount();

		$stmt    = $ibforums->db->query("SELECT id FROM ibf_files");
		$scripts = $stmt->rowCount();

		$stmt = $ibforums->db->query("SELECT DISTINCT author FROM ibf_files");

		$authors = "";

		while ($row = $stmt->fetch())
		{
			if ($authors == "")
			{
				$authors .= $row['author'];
			} else
			{
				$authors .= " , " . $row['author'];
			}
		}

		$stmt  = $ibforums->db->query("SELECT SUM( downloads ) as down FROM ibf_files");
		$downs = $stmt->fetch();

		$stmt = $ibforums->db->query("SELECT sname FROM ibf_files ORDER BY downloads DESC LIMIT 0,1");
		$maxd = $stmt->fetch();

		$stmt = $ibforums->db->query("SELECT sname FROM ibf_files ORDER BY views DESC LIMIT 0,1");
		$maxv = $stmt->fetch();

		$ADMIN->page_title = "Welcome to the downloads module's Control Panel!";

		$ADMIN->page_detail = "This page lets you control the download section.";

		//+-------------------------------
		$SKIN->td_header[] = array("&nbsp;", "50%");
		$SKIN->td_header[] = array("&nbsp;", "50%");
		$ADMIN->html .= $SKIN->start_table("Download Module Admin Page");

		$ADMIN->html .= $SKIN->add_td_row(array("<b>Number of categories</b>", $cats));

		$ADMIN->html .= $SKIN->add_td_row(array("<b>Number of files</b>", $scripts));

		$ADMIN->html .= $SKIN->add_td_row(array("<b>Number of downloads</b>", $downs['down']));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Individual authors</b>",
		                                       $authors
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>File with highest number of downloads</b>",
		                                       $maxd['sname']
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>File with highest number of views</b>",
		                                       $maxv['sname']
		                                  ));

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}
}

?>
