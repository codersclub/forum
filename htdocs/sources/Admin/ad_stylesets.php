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
|   > Help Control functions
|   > Module written by Matt Mecham
|   > Date started: 2nd April 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new ad_settings();

class ad_settings
{

	var $base_url;

	function ad_settings()
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
			case 'add':
				$this->do_form('add');
				break;

			case 'edit':
				$this->do_form('edit');
				break;

			case 'doadd':
				$this->save_skin('add');
				break;

			case 'doedit':
				$this->save_skin('edit');
				break;

			case 'remove':
				$this->remove();
				break;

			//-------------------------
			case 'memskins':
				$this->mem_skins();
				break;

			default:
				$this->list_sets();
				break;
		}

	}

	//----------------------------------------------
	// I must get around to centralising this...
	//----------------------------------------------

	function convert_tags($t = "")
	{
		if ($t == "")
		{
			return "";
		}

		$t = preg_replace("/{?\\\$ibforums->base_url}?/", "{ibf.script_url}", $t);
		$t = preg_replace("/{?\\\$ibforums->session_id}?/", "{ibf.session_id}", $t);
		$t = preg_replace("/{?\\\$ibforums->skin\['?(\w+)'?\]}?/", "{ibf.skin.\\1}", $t);
		$t = preg_replace("/{?\\\$ibforums->lang\['?(\w+)'?\]}?/", "{ibf.lang.\\1}", $t);
		$t = preg_replace("/{?\\\$ibforums->vars\['?(\w+)'?\]}?/", "{ibf.vars.\\1}", $t);
		$t = preg_replace("/{?\\\$ibforums->member\['?(\w+)'?\]}?/", "{ibf.member.\\1}", $t);

		return $t;

	}

	//----------------------------------------------

	function mem_skins()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$stmt = $ibforums->db->query("SELECT sid FROM ibf_skins WHERE uid='" . $IN['oid'] . "'");
		$old  = $stmt->fetch();

		if ($IN['nid'] == 'n')
		{
			$ibforums->db->exec("UPDATE ibf_members SET skin=NULL WHERE skin='" . $old['sid'] . "'");
		} else
		{
			$stmt = $ibforums->db->query("SELECT sid FROM ibf_skins WHERE uid='" . $IN['nid'] . "'");
			$new  = $stmt->fetch();
			$ibforums->db->exec("UPDATE ibf_members SET skin='" . $new['sid'] . "' WHERE skin='" . $old['sid'] . "'");
		}

		$ADMIN->done_screen("Member Skin Choices Updated", "Manage Skin Sets", "act=sets");

	}

	//-------------------------------------------------------------
	// REMOVE WRAPPERS
	//-------------------------------------------------------------

	function remove()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		//+-------------------------------

		if ($IN['id'] == "")
		{
			$ADMIN->error("You must specify an existing skin set ID, go back and try again");
		}

		//+-------------------------------

		if (FALSE === $skin = \Models\Skins::find(['uid' => $IN['id']]))
		{
			$ADMIN->error("Could not query that skin set information from the DB");
		}

		//+-------------------------------

		if ($skin['sid'] == Config::get('app.default_skin'))
		{
			$ADMIN->error("You can not remove this skin set as it is set as the default. Set another skin as default and try again");
		}

		//+-------------------------------

		// Update the members skins..

		$ibforums->db->exec("UPDATE ibf_members SET skin='' WHERE skin='" . $skin['sid'] . "'");

		// Update the forums DB

		$stmt = $ibforums->db->query("SELECT id FROM ibf_forums WHERE skin_id='" . $skin['sid'] . "'");

		if ($stmt->rowCount())
		{
			$arr = array();

			while ($i = $stmt->fetch())
			{
				$arr['forum_skin_' . $i['id']] = '';
			}

			$ibforums->db->exec("UPDATE ibf_forums SET skin_id='' WHERE skin_id='" . $skin['sid'] . "'");

			// Remove it from the config file..

			$ADMIN->rebuild_config($arr);

		}

		// Remove skin from the DB

		$ibforums->db->exec("DELETE FROM ibf_skins WHERE uid='" . $IN['id'] . "'");

		$std->boink_it($SKIN->base_url . "&act=sets");

		exit();

	}

	//-------------------------------------------------------------
	// ADD / EDIT SKIN SETS
	//-------------------------------------------------------------

	function save_skin($type = 'add')
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		//+-------------------------------

		if ($type == 'edit')
		{
			if ($IN['id'] == "")
			{
				$ADMIN->error("You must specify an existing skin set ID, go back and try again");
			}
		}

		if ($IN['sname'] == "")
		{
			$ADMIN->error("You must specify a name for this skin pack ID");
		}

		$barney = array(
			'sname'            => $std->txt_stripslashes($_POST['sname']),
            //лечим идиотию с заменой символов для всех входящих данных
			'template_class'   => html_entity_decode($IN['template_class']),
			'img_dir'          => $IN['img_dir'],
			'css_id'           => $IN['css'],
			'hidden'           => $IN['hidden'],
			'macro_id'         => $IN['macro_id'],
		);

		if ($type == 'add')
		{

			$stmt = $ibforums->db->query("SELECT MAX(sid) as new_id FROM ibf_skins");

			$row = $stmt->fetch();

			$barney['sid'] = $row['new_id'] + 1;

			\Models\Skins::add($barney);

			$std->boink_it($SKIN->base_url . "&act=sets");

			exit();

		} else
		{
			$db_string = array_map([$ibforums->db, 'quote'], $barney);
			$ibforums->db->updateRow("ibf_skins", $db_string, "uid='" . $IN['id'] . "'");

			$ADMIN->done_screen("Skin Set Updated", "Manage Skin Sets", "act=sets");
		}

	}

	//-------------------------------------------------------------
	// ADD / EDIT SETS
	//-------------------------------------------------------------

	function do_form($type = 'add')
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $ibforums;

		//+-------------------------------

		$css       = array();
		$macros    = array();

		//+-------------------------------

		if ($IN['id'] == "")
		{
			$ADMIN->error("You must specify an existing wrapper ID, go back and try again");
		}

		//+-------------------------------

		if (FALSE !== $row = \Models\Skins::find(['uid' => $IN['id']]))
		{
			$ADMIN->error("Could not query the information from the database");
		}

		//+-------------------------------

		$stmt = $ibforums->db->query("SELECT * FROM ibf_macro_name");

		while ($img = $stmt->fetch())
		{
			$macros[] = array($img['set_id'], $img['set_name']);
		}

		//+-------------------------------

		foreach (scandir(app_path('/assets/stylesheets/skins/')) as $item)
		{
			$matches = [];
			if (preg_match('/^[a-z0-9_]+\.scss$/i', $item, $matches))
			{
				$css[] = [$matches[0], $matches[0]];
			}
		}

		//+-------------------------------

		if ($type == 'add')
		{
			$code               = 'doadd';
			$button             = 'Create Skin Set';
			$row['sname']       = $row['sname'] . ".2";
		} else
		{
			$code   = 'doedit';
			$button = 'Edit Skin Set';
		}

		$dirs = array();

		$dh = opendir('./style_images');
		while ($file = readdir($dh))
		{
			if (($file != ".") && ($file != ".."))
			{
				if (is_dir('./style_images/' . $file))
				{
					$dirs[] = array($file, $file);
				}
			}
		}
		closedir($dh);

		//+-------------------------------

		$ADMIN->page_detail = "You may mix n' match which skin resources you wish to apply to this skin set.";
		$ADMIN->page_title  = "Manage Skin Sets";

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', $code),
		                                       2 => array('act', 'sets'),
		                                       3 => array('id', $IN['id']),
		                                  ), "theAdminForm", "onSubmit=\"return no_specialchars('sets')\"");

		//+-------------------------------

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table($button);

		$ADMIN->html .= $SKIN->js_no_specialchars();

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Skin Set Title</b>",
		                                       $SKIN->form_input('sname', $row['sname']),
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Skin template class name</b>",
		                                       $SKIN->form_input('template_class', $row['template_class']),
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Use Macro Set:</b>",
		                                       $SKIN->form_dropdown('macro_id', $macros, $row['macro_id']),
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Use Image Directory:</b>",
		                                       $SKIN->form_dropdown('img_dir', $dirs, $row['img_dir']),
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Use Stylesheet:</b>",
		                                       $SKIN->form_dropdown('css', $css, $row['css_id']),
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Hide from Members?</b><br>Useful if you want to make a forum only skin",
		                                       $SKIN->form_yes_no('hidden', $row['hidden']),
		                                  ));

		$ADMIN->html .= $SKIN->end_form($button);

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------
		//+-------------------------------

		$ADMIN->output();

	}

	//-------------------------------------------------------------
	// SHOW WRAPPERS
	//-------------------------------------------------------------

	function list_sets()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$form_array = array();

		$forums      = array();
		$forum_skins = array();

		$ADMIN->page_detail = "You may add/edit and remove skin sets.<br><br>Skin sets are groups of style resources. You can mix n' match board wrappers, image and macro sets, stylesheets and skin templates. If you wish to edit any of the resources, please choose the resource you wish to manage from the menu bar on the left.";
		$ADMIN->page_title  = "Manage Skin Sets";

		//+-------------------------------

		$stmt = $ibforums->db->query("SELECT id, name FROM ibf_forums");

		while ($f = $stmt->fetch())
		{
			$forums[$f['id']] = $f['name'];

			if ($INFO['forum_skin_' . $f['id']] != "")
			{
				$forum_skins[$INFO['forum_skin_' . $f['id']]][] = $f['name'];
			}
		}

		$stmt = $ibforums->db->prepare("select ibf_skins.*, count(ibf_members.id) as mcount
			    from ibf_skins
			    left join ibf_members on(ibf_members.skin=ibf_skins.sid)
			    where (ibf_members.skin is not null or ibf_skins.sid = :defsid) group by ibf_skins.sid
			    order by ibf_skins.sname")->execute([':defsid' => Config::get('app.default_skin', 0)]);

		$used_ids = array();

		if ($stmt->rowCount())
		{

			$SKIN->td_header[] = array("Title", "40%");
			$SKIN->td_header[] = array("No. Members", "20%");
			$SKIN->td_header[] = array("Edit", "10%");
			$SKIN->td_header[] = array("Remove", "10%");
			$SKIN->td_header[] = array("Hidden", "5%");
			$SKIN->td_header[] = array("Default", "5%");

			//+-------------------------------

			$ADMIN->html .= $SKIN->start_table("Current Skins Used by Members");

			while ($r = $stmt->fetch())
			{
				$extra = "";

				if (is_array($forum_skins[$r['sid']]))
				{
					if (count($forum_skins[$r['sid']]) > 0)
					{
						$extra = "<br>(Used in forums: " . implode(",", $forum_skins[$r['sid']]) . " )";
					}
				}

				$default = '&nbsp;';
				$hidden  = '&nbsp;';

				if ($r['hidden'] == 1)
				{
					$hidden = "<span style='color:red;font-weight:bold'>X</span>";
				}

				if ($r['sid'] == Config::get('app.default_skin'))
				{
					$default = "<span style='color:red;font-weight:bold'>X</span>";
				}

				$editlist = "<br /><b>Edit:</b> <a href='{$SKIN->base_url}&act=image&code=edit&id={$r['macro_id']}'>Macros</a>";

				$ADMIN->html .= $SKIN->add_td_row(array(
				                                       "<b>" . $std->txt_stripslashes($r['sname']) . "</b>$extra" . $editlist,
				                                       "<center>" . $r['mcount'] . "</center>",
				                                       "<center><a href='" . $SKIN->base_url . "&act=sets&code=edit&id={$r['uid']}'>Edit</a></center>",
				                                       "<center><a href='" . $SKIN->base_url . "&act=sets&code=remove&id={$r['uid']}'>Remove</a></center>",
				                                       "<center>$hidden</center>",
				                                       "<center>$default</center>",
				                                  ));

				$used_ids[] = $r['uid'];

				$form_array[] = array($r['uid'], $r['sname']);

			}

			$ADMIN->html .= $SKIN->end_table();
		}

		if (count($used_ids) < 1)
		{
			$stmt = $ibforums->db->query("SELECT * FROM ibf_skins");

			$left_one = $stmt->rowCount();
		} else
		{
			if (count($used_ids) > 0)
			{
				$stmt = $ibforums->db->query("SELECT * FROM ibf_skins WHERE uid NOT IN(" . implode(",", $used_ids) . ")");

				$left_two = $stmt->rowCount();
			}
		}

		if ($left_one > 0 or $left_two > 0)
		{
			$SKIN->td_header[] = array("Title", "60%");
			$SKIN->td_header[] = array("Edit", "10%");
			$SKIN->td_header[] = array("Remove", "10%");
			$SKIN->td_header[] = array("Hidden", "5%");
			$SKIN->td_header[] = array("Default", "5%");

			$ADMIN->html .= $SKIN->start_table("Skin Sets not used by Members");

			while ($r = $stmt->fetch())
			{

				$extra = "";

				if (is_array($forum_skins[$r['sid']]))
				{
					if (count($forum_skins[$r['sid']]) > 0)
					{
						$extra = "<br>(Used in forums: " . implode(",", $forum_skins[$r['sid']]) . " )";
					}
				}

				$default = '&nbsp;';
				$hidden  = '&nbsp;';

				if ($r['hidden'] == 1)
				{
					$hidden = "<span style='color:red;font-weight:bold'>X</span>";
				}

				if ($r['sid'] == Config::get('app.default_skin'))
				{
					$default = "<span style='color:red;font-weight:bold'>X</span>";
				}

				$editlist = "<br /><b>Edit:</b> <a href='{$SKIN->base_url}&act=image&code=edit&id={$r['macro_id']}'>Macros</a>";

				$ADMIN->html .= $SKIN->js_checkdelete();

				$ADMIN->html .= $SKIN->add_td_row(array(
				                                       "<b>" . $std->txt_stripslashes($r['sname']) . "</b>$extra" . $editlist,
				                                       "<center><a href='" . $SKIN->base_url . "&act=sets&code=edit&id={$r['uid']}'>Edit</a></center>",
				                                       "<center><a href='javascript:checkdelete(\"act=sets&code=remove&id={$r['uid']}\")'>Remove</a></center>",
				                                       "<center>$hidden</center>",
				                                       "<center>$default</center>",
				                                  ));

				$form_array[] = array($r['uid'], $r['sname']);

			}

			$ADMIN->html .= $SKIN->end_table();
		}

		//+-------------------------------
		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'add'),
		                                       2 => array('act', 'sets'),
		                                  ));

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		$ADMIN->html .= $SKIN->start_table("Create New Skin Set");

		//+-------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Base new skin set on...</b>",
		                                       $SKIN->form_dropdown("id", $form_array)
		                                  ));

		$ADMIN->html .= $SKIN->end_form("Create new Skin Set");

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------
		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'memskins'),
		                                       2 => array('act', 'sets'),
		                                  ));

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		$ADMIN->html .= $SKIN->start_table("Swop members skin choice");

		//+-------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Where members use skin...</b>",
		                                       $SKIN->form_dropdown("oid", $form_array)
		                                  ));

		$form_array[] = array('n', 'None (Will use whatever is set as default)');

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>update to use skin...</b>",
		                                       $SKIN->form_dropdown("nid", $form_array)
		                                  ));

		$ADMIN->html .= $SKIN->end_form("Update members skin choice");

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------
		//+-------------------------------

		$ADMIN->output();

	}

}

?>
