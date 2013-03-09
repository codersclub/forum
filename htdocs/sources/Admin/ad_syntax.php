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

$idx = new ad_syntax();

class ad_syntax
{
	var $base_url;

	function ad_syntax()
	{
		global $IN;

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
			case 'select':
			case 'create':
			case 'edit':
				$this->syntax_edit();
				break;
			case 'access':
			case 'grant':
				$this->syntax_access();
				break;
			case 'forums';
			case 'delete':
			default:
				$this->syntax_start();
				break;
		}
	}

	//+---------------------------------------------------------------------------------
	//
	// SHOW LIST
	// Renders a complete listing of all the forums and categories w/mods.
	//
	//+---------------------------------------------------------------------------------

	function syntax_start()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		// vot debug
		//foreach($IN as $k=>$v)
		//{
		//echo $k."=>".$v."<br>\n";
		//}
		//echo "-------------------------<br>\n";

		if ($IN['code'] == 'delete')
		{
			$n           = 0;
			$syntax_list = array();

			$stmt = $ibforums->db->query("select id, syntax from ibf_syntax_list");
			while ($row = $stmt->fetch())
			{
				$syntax_list[$n++] = array('syntax' => $row['syntax'], 'id' => $row['id']);
			}

			foreach ($syntax_list as $syntax)
			{
				if ($IN['syntax_' . $syntax['syntax']] == '1')
				{
					$id = $syntax['id'];
					$ibforums->db->exec("delete from ibf_syntax_list where id = " . $id);
					$ibforums->db->exec("delete from ibf_syntax_rules where syntax_id = '" . $id . "'");
					$ibforums->db->exec("delete from ibf_syntax_access where syntax_id = '" . $id . "'");
				}
			}
		}

		if ($IN['code'] == 'forums')
		{
			$n          = 0;
			$forum_list = array();

			$stmt = $ibforums->db->query("select id, parent_id, redirect_on from ibf_forums");

			while ($row = $stmt->fetch())
			{
				$forum_list[$n++] = array(
					'id'          => $row['id'],
					'parent_id'   => $row['parent_id'],
					'redirect_on' => $row['redirect_on']
				);
			}

			foreach ($forum_list as $forum)
			{
				$forum_id  = $forum['id'];
				$syntax_id = $IN['forum_' . $forum_id];

				if ($forum['redirect_on'])
				{
					$off = 0;
				} else
				{
					$off = 1;

					if ($syntax_id == 'null')
					{
						$off       = 0;
						$syntax_id = -1;
					}

					if ($off and $syntax_id == -1)
					{
						if ($IN['forum_' . $forum['parent_id']])
						{
							$syntax_id = $IN['forum_' . $forum['parent_id']];
						}
					}
				}
				//echo "\$forum_id=".$forum_id.", \$syntax_id=".$syntax_id.", \$IN[ 'forum_'.\$forum['parent_id'] ]=".$IN[ 'forum_'.$forum['parent_id'] ]." <br>\n";

				$sql = "update ibf_forums set highlight_fid = " . $syntax_id . ", forum_highlight = " . $off . " where id = " . $forum_id;
				//echo "\$sql=".$sql." <br>\n";
				//echo "-------------------------<br>\n";
				$stmt = $ibforums->db->query($sql);
			}
		}

		$ADMIN->page_title  = "Syntax Highlight. Edit and Permissions";
		$ADMIN->page_detail = "This section allows you to edit, remove and add new syntax highlight configurations. Also it allows you to grant or deny configuration's permissions for moderators";

		// -------------------------------------------------------------------------------------------
		// Select syntax form
		// -------------------------------------------------------------------------------------------
		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'select'),
		                                       2 => array('act', 'syntax'),
		                                  ));

		$ADMIN->html .= $SKIN->start_table("Edit or Creation");

		$SKIN->td_header[] = array('', "50%");
		$SKIN->td_header[] = array('', "50%");

		$syntax_list = array();
		$n           = 0;
		$stmt        = $ibforums->db->query("select * from ibf_syntax_list");

		while ($row = $stmt->fetch())
		{
			$syntax_list[$n++] = array($row['id'], $row['syntax'] . " - " . $row['description']);
		}
		$syntax_list[$n++] = array('new', 'Create New');

		$select_question = "Select one configuration or create new";
		$dropdown        = $SKIN->form_dropdown('syntax_list', $syntax_list);

		$ADMIN->html .= $SKIN->add_td_row(array($select_question, $dropdown), $css);

		$ADMIN->html .= $SKIN->end_form("Edit");
		$ADMIN->html .= $SKIN->end_table();

		// -------------------------------------------------------------------------------------------
		// Delete syntax form
		// -------------------------------------------------------------------------------------------
		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'delete'),
		                                       2 => array('act', 'syntax'),
		                                  ));

		$SKIN->td_header[] = array("Code", "10%");
		$SKIN->td_header[] = array("Configuration Desription", "85%");
		$SKIN->td_header[] = array("Select", "5%");

		$ADMIN->html .= $SKIN->start_table("Syntax Highlight Configurations (Delete)");

		$stmt = $ibforums->db->query("select * from ibf_syntax_list");

		while ($row = $stmt->fetch())
		{
			$code        = "<center>" . $row['syntax'] . "</center>";
			$description = "<b>" . $row['description'] . "</b>";
			$checkbox    = "<center><input type='checkbox' name='syntax_{$row['syntax']}' value='1'></center>";

			$ADMIN->html .= $SKIN->add_td_row(array($code, $description, $checkbox), $css);
		}

		$ADMIN->html .= $SKIN->end_form("Delete selected");
		$ADMIN->html .= $SKIN->end_table();

		// -------------------------------------------------------------------------------------------
		// Syntax permissions form
		// -------------------------------------------------------------------------------------------
		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'access'),
		                                       2 => array('act', 'syntax'),
		                                  ));

		$SKIN->td_header[] = array('', "50%");
		$SKIN->td_header[] = array('', "50%");

		$ADMIN->html .= $SKIN->start_table("Permissions");

		$members_list = array();
		$n            = 0;
		$stmt         = $ibforums->db->query("select m.id, m.name
			from ibf_members m, ibf_moderators c
			where m.id = c.member_id
			group by m.id
			order by m.name asc");
		while ($row = $stmt->fetch())
		{
			$members_list[$n++] = array($row['id'], $row['name']);
		}

		$select_question = "Select administrator or moderator from list";
		$dropdown        = $SKIN->form_dropdown('members_list', $members_list);

		$ADMIN->html .= $SKIN->add_td_row(array($select_question, $dropdown), $css);

		$ADMIN->html .= $SKIN->end_form("Select Member");
		$ADMIN->html .= $SKIN->end_table();

		// -------------------------------------------------------------------------------------------
		// Forums syntax set form
		// -------------------------------------------------------------------------------------------
		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'forums'),
		                                       2 => array('act', 'syntax'),
		                                  ));

		$SKIN->td_header[] = array('Forums List', "50%");
		$SKIN->td_header[] = array('Select syntax highlight configuration', "50%");

		$ADMIN->html .= $SKIN->start_table("Forums Syntax Highlight");

		$this->syntax_list    = array();
		$this->syntax_list[0] = array(-1, "Parent forum value");
		$this->syntax_list[1] = array('null', "No syntax highlight");
		$n                    = 2;

		$stmt = $ibforums->db->query("SELECT * FROM ibf_syntax_list");

		while ($row = $stmt->fetch())
		{
			$this->syntax_list[$n++] = array($row['id'], $row['syntax'] . " - " . $row['description']);
		}

		// Song * new forums list, 04.01.05

		$cats     = array();
		$forums   = array();
		$children = array();

		$stmt = $ibforums->db->query("SELECT * FROM ibf_categories WHERE id > 0 ORDER BY position ASC");

		while ($r = $stmt->fetch())
		{
			$cats[$r['id']] = $r;
		}

		$stmt = $ibforums->db->query("SELECT * FROM ibf_forums ORDER BY position ASC");

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

		$last_cat_id = -1;

		foreach ($cats as $c)
		{
			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       "<a href='{$INFO['board_url']}/index.php?c={$c['id']}' target='_blank'>" . $c['name'] . "</a>",
			                                       " ",

			                                  ), 'pformstrip');
			$last_cat_id = $c['id'];

			foreach ($forums as $r)
			{
				if ($r['category'] == $last_cat_id)
				{
					$forum_id   = $r['id'];
					$forum_name = $r['name'];
					$syntax_id  = $r['highlight_fid'];
					$off        = $r['forum_highlight'];

					if (!$syntax_id)
					{
						$syntax_id = 'null';
					}
					if (!$off)
					{
						$syntax_id = 'null';
					}

					$redirect = ($r['redirect_on'] == 1)
						? ' (Redirect Forum)'
						: '';

					$dropdown = $SKIN->form_dropdown('forum_' . $forum_id, $this->syntax_list, $syntax_id);

					if ($r['subwrap'])
					{
						$ADMIN->html .= $SKIN->add_td_row(array(

						                                       " - <b>" . $r['name'] . "</b>$redirect",
						                                       $dropdown

						                                  ), 'subforum');
					} else
					{
						$ADMIN->html .= $SKIN->add_td_row(array(

						                                       "<b>" . $r['name'] . "</b>$redirect<br>",
						                                       $dropdown

						                                  ));
					}

					// Song * infinite subforums, 04.01.05
					$this->subforums_addtorow($children, $r['id'], 0);

					// Song * infinite subforums, 04.01.05

				}
			}
		}

		// Song * new forums list, 04.01.05

		$ADMIN->html .= $SKIN->end_form("Apply");
		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();
	}

	// Song * endless forums, 04.01.05

	function subforums_addtorow($children, $id, $level)
	{
		global $ADMIN, $SKIN;
		$ibforums = Ibf::app();

		if (!isset($children[$id]) || count($children[$id]) <= 0)
		{
			return;
		}

		foreach ($children[$id] as $idx => $rd)
		{
			$forum_id   = $rd['id'];
			$forum_name = $rd['name'];
			$syntax_id  = $rd['highlight_fid'];
			$off        = $rd['forum_highlight'];

			if (!$syntax_id)
			{
				$syntax_id = 'null';
			}
			if (!$off)
			{
				$syntax_id = 'null';
			}

			$redirect = ($rd['redirect_on'] == 1)
				? ' (Redirect Forum)'
				: '';

			$dropdown = $SKIN->form_dropdown('forum_' . $forum_id, $this->syntax_list, $syntax_id);

			$t_level_char = "+--";

			for ($i = 0; $i < $level; $i++)
			{
				$t_level_char .= "--";
			}

			$ADMIN->html .= $SKIN->add_td_row(array(
			                                       " " . $t_level_char . " <b>" . $rd['name'] . "</b>$redirect",
			                                       $dropdown

			                                  ), 'subforum');

			$this->subforums_addtorow($children, $rd['id'], $level + 1);
		}

		// Song * endless forums, 04.01.05

	}

	function syntax_access()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$member_id = '';

		if ($IN['code'] == 'grant')
		{
			$member_id = $IN['member_id'];
			$ibforums->db->exec("delete from ibf_syntax_access where member_id = " . $member_id);

			$n           = 0;
			$syntax_list = array();

			$stmt = $ibforums->db->query("select id, syntax from ibf_syntax_list");
			while ($row = $stmt->fetch())
			{
				$syntax_list[$n++] = array('syntax' => $row['syntax'], 'id' => $row['id']);
			}

			foreach ($syntax_list as $syntax)
			{
				if ($IN['syntax_' . $syntax['syntax']] == '1')
				{
					$ibforums->db->exec("insert into ibf_syntax_access (syntax_id, member_id) values (" . $syntax['id'] . ", " . $member_id . ")");
				}
			}
		}

		if ($IN['code'] == 'access')
		{
			$member_id = $IN['members_list'];
		}

		$ADMIN->page_title  = "Syntax Highlight. Permissions";
		$ADMIN->page_detail = "Select syntax highlight configurations to grant access for selected member";

		// -------------------------------------------------------------------------------------------
		// Access syntax form
		// -------------------------------------------------------------------------------------------
		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'grant'),
		                                       2 => array('act', 'syntax'),
		                                       3 => array('member_id', $member_id)
		                                  ));

		$SKIN->td_header[] = array("Code", "10%");
		$SKIN->td_header[] = array("Configuration Desription", "85%");
		$SKIN->td_header[] = array("Access", "5%");

		$ADMIN->html .= $SKIN->start_table("Syntax Highlight Configurations (Grant Access)");

		$stmt = $ibforums->db->query("select l.id, l.syntax, l.description, a.member_id from ibf_syntax_list l
			    left join ibf_syntax_access a on a.syntax_id = l.id and a.member_id = " . $member_id);
		while ($row = $stmt->fetch())
		{
			$code        = "<center>" . $row['syntax'] . "</center>";
			$description = "<b>" . $row['description'] . "</b>";
			$checkbox    = "";

			if ($row['member_id'] == $member_id)
			{
				$checkbox = "<center><input type='checkbox' name='syntax_{$row['syntax']}' value='1' checked></center>";
			} else
			{
				$checkbox = "<center><input type='checkbox' name='syntax_{$row['syntax']}' value='1'></center>";
			}

			$ADMIN->html .= $SKIN->add_td_row(array($code, $description, $checkbox), $css);
		}

		$ADMIN->html .= $SKIN->end_form("Apply Permissions");
		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();
	}

	function syntax_edit()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$ADMIN->page_title  = "Syntax Highlight. Permissions";
		$ADMIN->page_detail = "Select syntax highlight configurations to grant access for selected member";

		// -------------------------------------------------------------------------------------------
		// Access syntax form
		// -------------------------------------------------------------------------------------------
		$syntax_id          = $IN['syntax_list'];
		$description        = '';
		$syntax             = '';
		$syntax_description = '';
		$back_color         = '';
		$fore_color         = '';
		$tab_length         = 4;
		$example            = 'Hello world';

		if ($IN ['code'] == 'create')
		{
			$syntax             = $IN['syntax'];
			$description        = $IN['description'];
			$syntax_description = $IN['syntax_description'];
			$back_color         = $IN['back_color'];
			$fore_color         = $IN['fore_color'];
			$tab_length         = $IN['tab_length'];
			$example            = $IN['example'];

			$stmt = $ibforums->db->query("select max(id) as syntax_id from ibf_syntax_list");
			if ($row = $stmt->fetch())
			{
				$syntax_id = $row['syntax_id'] + 1;

				$ibforums->db->exec("insert into ibf_syntax_list (id, syntax, syntax_description, description, back_color, fore_color, tab_length, example)
					     values (" . $syntax_id . ", '" . $syntax . "', '" . $syntax_description . "', '" . $description . "', '" . $back_color . "', '" . $fore_color . "', " . $tab_length . ", '" . $example . "')");
			}
		}

		if ($IN ['code'] == 'edit')
		{
			$syntax_id          = $IN['syntax_id'];
			$syntax             = $IN['syntax'];
			$description        = $IN['description'];
			$syntax_description = $IN['syntax_description'];
			$back_color         = $IN['back_color'];
			$fore_color         = $IN['fore_color'];
			$tab_length         = $IN['tab_length'];
			$example            = $IN['example'];

			$ibforums->db->exec("update ibf_syntax_list set
					syntax = '" . $syntax . "',
					syntax_description = '" . $syntax_description . "',
					description = '" . $description . "',
					back_color = '" . $back_color . "',
					fore_color = '" . $fore_color . "',
					tab_length = " . $tab_length . ",
					example = '" . $example . "'
					where id = " . $syntax_id);
		}

		if ($syntax_id != 'new')
		{
			$stmt = $ibforums->db->query("select * from ibf_syntax_list where id = " . $syntax_id);
			if ($row = $stmt->fetch())
			{
				$syntax             = $row['syntax'];
				$syntax_description = $row['syntax_description'];
				$description        = $row['description'];
				$back_color         = $row['back_color'];
				$fore_color         = $row['fore_color'];
				$tab_length         = $row['tab_length'];
				$example            = $row['example'];
			}

			$ADMIN->html .= $SKIN->start_form(array(
			                                       1 => array('code', 'edit'),
			                                       2 => array('act', 'syntax'),
			                                       3 => array('syntax_id', $syntax_id),
			                                  ));

			$SKIN->td_header[] = array("", "50%");
			$SKIN->td_header[] = array("", "50%");

			$ADMIN->html .= $SKIN->start_table("Edit Highlight Configuration (" . $description . ")");
		} else
		{
			$ADMIN->html .= $SKIN->start_form(array(
			                                       1 => array('code', 'create'),
			                                       2 => array('act', 'syntax'),
			                                  ));

			$SKIN->td_header[] = array("", "50%");
			$SKIN->td_header[] = array("", "50%");

			$ADMIN->html .= $SKIN->start_table("Create New Highlight Configuration");
		}

		$syntax             = $SKIN->form_input("syntax", $syntax);
		$syntax_description = $SKIN->form_input("syntax_description", $syntax_description);
		$description        = $SKIN->form_input("description", $description);
		$back_color         = $SKIN->form_input("back_color", $back_color);
		$fore_color         = $SKIN->form_input("fore_color", $fore_color);
		$tab_length         = $SKIN->form_input("tab_length", $tab_length);

		$example = str_replace("<br>", "\r\n", $example);

		$example = $SKIN->form_textarea("example", $example);

		$ADMIN->html .= $SKIN->add_td_row(array("Code", $syntax), $css);
		$ADMIN->html .= $SKIN->add_td_row(array("Syntax description", $syntax_description), $css);
		$ADMIN->html .= $SKIN->add_td_row(array("Description", $description), $css);
		$ADMIN->html .= $SKIN->add_td_row(array("Back Color", $back_color), $css);
		$ADMIN->html .= $SKIN->add_td_row(array("Fore Color", $fore_color), $css);
		$ADMIN->html .= $SKIN->add_td_row(array("Tab length in spaces", $tab_length), $css);
		$ADMIN->html .= $SKIN->add_td_row(array("Code example", $example), $css);

		if ($syntax_id != 'new')
		{
			$ADMIN->html .= $SKIN->end_form("Edit");
		} else
		{
			$ADMIN->html .= $SKIN->end_form("Create");
		}

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();
	}
}

?>
