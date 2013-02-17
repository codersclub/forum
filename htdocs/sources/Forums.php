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
  |   > Forum topic index module
  |   > Module written by Matt Mecham
  |   > Date started: 14th February 2002
  |
  |	> Module Version Number: 1.0.0
  +--------------------------------------------------------------------------
 */

$idx = new Forums;

class Forums
{

	var $output = "";
	var $base_url = "";
	var $html = "";
	var $forum = array();
	var $forums = array();
	var $forums_list = array();
	var $children = array();
	var $mods = array(); // Moderators
	var $gmods = array(); // Group Moderators
	var $nav_extra = array();
	var $read_array = array();
	var $read_mark = array();
	var $dots = array();
	var $queued = array();
	var $see = array();
	var $ids = "";
	var $board_html = "";
	var $sub_output = "";
	var $pinned_print = 0;
	var $new_posts = 0;
	var $parser = "";
	var $mode_id = 0;
	var $forums_id = array();
	var $main_id = 0;
	var $favs = array();
	var $mod = 0;

	//+----------------------------------------------------------------
	//
	// Check the member Moderator Rights for some forum
	//
	//+----------------------------------------------------------------
	function is_moderator($forum_id = 0)
	{
		$ibforums = Ibf::instance();
		if ($ibforums->member['id'] and
		    ($ibforums->member['g_is_supmod'] or
		     //vot: check for single moderator
		     $this->mods[$forum_id][$ibforums->member['id']] or
		     //vot: check for group moderators
		     $this->gmods[$forum_id][$ibforums->member['mgroup']])
		)
		{
			$this->mod = 1;
			return 1;
		} else
		{
			return 0;
		}
	}

	function __construct()
	{
		global $print, $skin_universal;
		$ibforums = Ibf::instance();

		require ROOT_PATH . "sources/lib/post_parser.php";

		$this->parser = new post_parser();

		$this->parser->prepareIcons();

		//+------------------------------------------
		// Are we doing anything with "site jump?"
		//+------------------------------------------
		// Song * show only new topics, 17.01.05
		$ibforums->input['view'] = ($ibforums->member['show_new'] and $ibforums->input['view'] != "all")
			? "new"
			: $ibforums->input['view'];

		switch ($ibforums->input['f'])
		{
			case 'sj_home':
				$ibforums->functions->boink_it($ibforums->base_url . "act=idx");
				break;
			case 'sj_search':
				$ibforums->functions->boink_it($ibforums->base_url . "act=Search");
				break;
			case 'sj_help':
				$ibforums->functions->boink_it($ibforums->base_url . "act=Help");
				break;
			default:
				$ibforums->input['f'] = intval($ibforums->input['f']);
				break;
		}

		$ibforums->lang = $ibforums->functions->load_words($ibforums->lang, 'lang_forum', $ibforums->lang_id);

		$this->html = $ibforums->functions->load_template('skin_forum');

		//+------------------------------------------
		// Get the forum info based on the forum ID,
		// and get the category name, ID, etc.
		//+------------------------------------------

		$ibforums->input['f'] = intval($ibforums->input['f']);

		// Song * forum filter, 19.11.2004
		// parent forum id
		$pid = intval($ibforums->input['pid']);

		// for filter mode
		if ($ibforums->member['show_filter'])
		{
			// if mode eq "all forums" or "all visible forums"
			if ($ibforums->input['f'] == -1 or $ibforums->input['f'] == -2)
			{
				// remember selected mode
				$this->mode_id = $ibforums->input['f'];

				// a level from that we will begin
				if ($pid)
				{
					$ibforums->input['f'] = $pid;
				}
			}
		}

		// Song * forum filter, 19.11.2004
		$this->forum = $ibforums->db->query("SELECT f.*, c.id as cat_id, c.name as cat_name
		    FROM ibf_forums f
        	    LEFT JOIN ibf_categories c ON (c.id=f.category)
        	    WHERE f.id='" . $ibforums->input['f'] . "'")->fetch();

		// for forums that have access to post to root level
		if ($this->forum['sub_can_post'] and $this->forum['parent_id'] == -1 and !$this->mode_id)
		{
			$this->mode_id = -3;
		}

		// for forums that have not access to post to root level
		if (!$this->forum['sub_can_post'] and $ibforums->member['show_filter'] and !$pid)
		{
			$this->mode_id = -1;
		}

		//----------------------------------------
		// Error out if we can not find the forum
		//----------------------------------------

		if (!$this->forum['id'])
		{
			$ibforums->functions->Error(array('LEVEL' => 1, 'MSG' => 'is_broken_link'));
		}

		//----------------------------------------
		// Is it a redirect forum?
		//----------------------------------------

		if ($this->forum['redirect_on'] and $this->forum['redirect_url'])
		{
			// Update hits:
			$ibforums->db->query("UPDATE ibf_forums SET redirect_hits=redirect_hits+1 WHERE id='" . $this->forum['id'] . "'");

			// Boink!
			$ibforums->functions->boink_it($this->forum['redirect_url']);
		}

		// Song * user ban patch
		$ibforums->functions->user_ban_check($this->forum);

		//----------------------------------------
		// If this is a sub forum, we need to get
		// the cat details, and parent details
		//----------------------------------------
		// Song * endless forums, 20.12.04
		$this->base_url = $ibforums->base_url;

		// id of most upper forum
		$this->main_id = $this->forum['id'];

		if ($this->forum['parent_id'] > 0)
		{
			$stmt = $ibforums->db->query("SELECT f.id as forum_id, f.name as forum_name, f.parent_id, f.read_perms, c.id, c.name
			    FROM ibf_forums_order fo, ibf_forums f, ibf_categories c
			    WHERE fo.id='" . $this->forum['id'] . "' and f.id=fo.pid and c.id=f.category");

			while ($row = $stmt->fetch())
			{
				if ($row['forum_id'] == $this->forum['parent_id'])
				{
					$this->forum['cat_id']   = $row['id'];
					$this->forum['cat_name'] = $row['name'];
				}

				$this->main_id = $row['forum_id'];

				$this->nav_extra[] = "<a href='" . $this->base_url . "showforum={$this->main_id}'>{$row['forum_name']}</a>";
			}

			$this->nav_extra = array_reverse($this->nav_extra);
		}

		// add category and current forum
		array_unshift($this->nav_extra, "<a href='" . $this->base_url . "act=SC&amp;c={$this->forum['cat_id']}'>{$this->forum['cat_name']}</a>");

		array_push($this->nav_extra, "<a href='" . $this->base_url . "showforum={$this->forum['id']}'>{$this->forum['name']}</a>");

		// /Song * endless forums, 20.12.04
		//--------------------------------------------------------------------------------
		// quick jump
		$this->forum['FORUM_JUMP'] = $ibforums->functions->build_forum_jump_topics();

		// Are we viewing the forum, or viewing the forum rules?
		if ($ibforums->input['act'] == 'SR')
		{
			$this->show_rules();
		} else
		{

			// Song * forum filter + endless forums, 20.12.04
			// build structure of forums + combobox for filter
			$this->forums_id = $ibforums->functions->forums_array($this->main_id, $this->forum, $this->forums, $this->children, $this->forums_list);

			// user board layout, fill only for filter, for mode "all visible forums"
			if (count($this->forums_id) > 0 and $ibforums->member['show_filter'] and $this->mode_id == -1)
			{
				$list = explode(",", $ibforums->member['board_layout']);

				foreach ($list as $l)
				{
					if (substr($l, 1, 1) == "f")
					{
						$this->see[substr($l, 2)] = substr($l, 0, 1);
					}
				}
			}

			// /Song * forum filter + endless forums, 20.12.04
			// moderators of current forum
			$this->forum['moderators'] = $this->get_moderators();

			if ($ibforums->member['g_is_supmod'])
			{
				$this->mod = 1;
			}

			if ($this->forum['subwrap'] == 1)
			{
				if (!$ibforums->member['id'] or !$ibforums->member['show_filter'])
				{
					$this->show_subforums();
				}

				if ($this->forum['sub_can_post'] or $ibforums->member['show_filter'])
				{
					$this->show_forum();
				} else
				{
					// No forum to show, just use the HTML in $this->sub_output
					// or there will be no HTML to use in the str_replace!

					$this->output     = $this->sub_output;
					$this->sub_output = "";
				}
			} else
			{
				$this->show_forum();
			}
		}

		//+----------------------------------------------------------------
		// Print it
		//+----------------------------------------------------------------

		if ($this->sub_output)
		{
			$this->output = str_replace("<!--IBF.SUBFORUMS-->", $this->sub_output, $this->output);
		}

		if ($ibforums->member['id'])
		{
			$this->output = str_replace("<!--IBF.SUB_FORUM_LINK-->", $this->html->show_sub_link($this->forum['id']), $this->output);
		}

		$print->add_output($this->output);

		// Song * RSS, 31.01.05
		$sub = ($ibforums->member['show_filter'])
			? "&view=sub"
			: "";

		$print->do_output(array(
		                       'TITLE' => $this->forum['name'] . " -> " . $ibforums->vars['board_name'],
		                       'JS'    => ($this->mod)
			                       ? "rows_js.js?{$ibforums->vars['client_script_version']}"
			                       : "",
		                       'NAV'   => $this->nav_extra,
		                       'RSS'   => $skin_universal->rss("?f={$this->forum['id']}{$sub}"),
		                  ));
	}

	// Song * forum filter + endless forums, 29.12.04

	function add_to_array(&$result, $id)
	{

		if (!isset($this->see[$id]) or $this->see[$id] == 1)
		{
			$result[] = $id;
		}
	}

	function add_sub_ids($result, &$ids, $children, $id)
	{

		if (isset($children[$id]) and count($children[$id]) > 0)
		{
			foreach ($children[$id] as $child)
			{
				$ids[] = $child['id'];

				$this->add_to_array($result, $child['id']);

				$result = $this->add_sub_ids($result, $ids, $children, $child['id']);
			}
		}

		return $result;
	}

	// ******************************************************************
	// * function returns ids of forums that begins from the level of current forum
	// ******************************************************************

	function get_tree()
	{
		$ibforums = Ibf::instance();

		// for non-filter mode
		if (!$ibforums->member['id'] or !$ibforums->member['show_filter'] or !count($this->forums_id) or $this->mode_id == -3)
		{
			$this->mode_id = "";
			return "='" . $this->forum['id'] . "'";
		}

		// array for all forums
		$all = array();

		// array for visible forums
		$ids = array();

		$id = $this->forum['id'];

		// add current forum
		$all[] = $id;
		$this->add_to_array($ids, $id);

		// add children of current forum
		$ids = $this->add_sub_ids($ids, $all, $this->children, $id);

		// count of returned forums
		$count = count($ids);

		if ($count == 1)
		{
			// get first
			$ids = $ids[0];

			// safe query if will no forums returned
			if (!$ids)
			{
				$ids = $this->forum['id'];
			}

			$ids = "='" . $ids . "'";

			$this->mode_id = "";
		} else
		{
			// fill array if it's blank (it may because forums can be disabled in board layout)
			// it will happen also if mode = -2 (all forums)
			if (!$count)
			{
				$ids = $all;
			}

			// combine string of ids
			$ids = implode(",", $ids);

			if (!$ids)
			{
				// safe query if array is blank
				$ids = "='" . $this->forum['id'] . "'";

				$this->mode_id = "";
			} else
			{
				$ids = " IN (" . $ids . ")";
			}
		}

		return $ids;
	}

	// /Song * forum filter + endless forums, 29.12.04
	//--------------------------------------------
	function get_moderators()
	{
		$ibforums = Ibf::instance();

		// get ids from that we will collect moderators
		$this->ids = $this->get_tree();

		// querying moderators of current forum and parent forums
		$stmt = $ibforums->db->query("SELECT mid, member_id as mod_id, member_name as mod_name,
			is_group, group_id, group_name,
			forum_id, post_q, topic_q, hide_topic
		    FROM ibf_moderators WHERE forum_id" . $this->ids);

		$modlist = "";

		while ($mod = $stmt->fetch())
		{
			if ($mod['is_group'] == 1) // Group Moderators
			{
				if (!isset($modlist[$mod['group_name']]))
				{
					$modlist[$mod['group_name']] = -$mod['group_id'];
				}
				$this->gmods[$mod['forum_id']][$mod['group_id']] = array(
					'group_name' => $mod['group_name'],
					'post_q'     => $mod['post_q'],
					'topic_q'    => $mod['topic_q'],
					'hide_topic' => $mod['hide_topic'],
				);
				//vot: check for group moderators
				if ($mod['group_id'] == $ibforums->member['mgroup'])
				{
					$this->mod = 1; //$mod['mod_id'];
				}
			} else // Single moderators
			{
				if (!isset($modlist[$mod['group_name']]))
				{
					$modlist[$mod['mod_name']] = $mod['mod_id'];
				}
				$this->mods[$mod['forum_id']][$mod['mod_id']] = array(
					'mod_name'   => $mod['mod_name'],
					'post_q'     => $mod['post_q'],
					'topic_q'    => $mod['topic_q'],
					'hide_topic' => $mod['hide_topic'],
				);
				if ($mod['mod_id'] == $ibforums->member['id'])
				{
					$this->mod = 1; //$mod['mod_id'];
				}
			}
		}

		$result = "";
		if (is_array($modlist))
		{
			foreach ($modlist as $key => $value)
			{
				//		echo $key."=>".$value."<br>";

				if ($value < 0)
				{
					//			$result .= "<a href='{$ibforums->base_url}act=Members&amp;max_results=30&amp;filter={$mod['group_id']}&amp;sort_order=asc&amp;sort_key=name&amp;st=0&amp;b=1'>{$mod['group_name']}</a>, ";
					$result .= "<a href='{$ibforums->base_url}act=Members&amp;max_results=30&amp;filter={$value}&amp;sort_order=asc&amp;sort_key=name&amp;st=0&amp;b=1'>{$key}</a>, ";
				} else
				{
					//			$result .= "<a href='{$ibforums->base_url}showuser={$mod['mod_id']}'>{$mod['mod_name']}</a>, ";
					$result .= "<a href='{$ibforums->base_url}showuser={$value}'>{$key}</a>, ";
				}
			}
		}

		if ($result)
		{
			$result = preg_replace("!,\s+$!", "", $result);
		} else
		{
			$result = $ibforums->lang['no_moderators'];
		}

		return $result;
	}

	//+----------------------------------------------------------------
	// Display any sub forums
	//+----------------------------------------------------------------

	function show_subforums()
	{

		$ibforums = Ibf::instance();

		$ibforums->lang = $ibforums->functions->load_words($ibforums->lang, 'lang_boards', $ibforums->lang_id);

		$this->board_html = $ibforums->functions->load_template('skin_boards');

		$temp_html = "";

		if (count($this->children[$this->forum['id']]) > 0)
		{
			foreach ($this->children[$this->forum['id']] as $row)
			{
				$temp_html .= $this->process_forum($row['id'], $row);
			}
		}

		if ($temp_html)
		{
			if ($ibforums->member['id'])
			{
				$f_id = $ibforums->input['showforum'];

				if ($f_id)
				{
					$f_id = "<div align='center'><a href='{$ibforums->base_url}act=Login&amp;CODE=04&amp;f=" . $f_id . "&amp;i=1'>{$ibforums->lang['mark_as_read']}</a></div>";
				}
			}

			$this->sub_output .= $this->board_html->subheader($f_id);
			$this->sub_output .= $temp_html;
			$this->sub_output .= $this->board_html->end_this_cat();
			$this->sub_output .= "<br>";
		} else
		{
			return $this->sub_output;
		}

		unset($temp_html);

		$this->sub_output .= $this->board_html->end_all_cats();
	}

	function process_forum($forum_id = "", $forum_data = array())
	{
		$ibforums = Ibf::instance();

		//--------------------------------------
		// Check permissions...
		//--------------------------------------

		if ($ibforums->functions->check_perms($forum_data['read_perms']) != TRUE)
		{
			return "";
		}

		//--------------------------------------
		// Redirect only forum?
		//--------------------------------------

		if ($forum_data['redirect_on'])
		{
			// Simply return with the redirect information

			if ($forum_data['redirect_loc'] != "")
			{
				$forum_data['redirect_target'] = " target='" . $forum_data['redirect_loc'] . "' ";
			}

			$forum_data['redirect_hits'] = $ibforums->functions->do_number_format($forum_data['redirect_hits']);

			$forum_data['colspan'] = 'colspan="2" ';

			return $this->board_html->forum_redirect_row($forum_data);
		}

		$forum_data['img_new_post'] = $ibforums->functions->forum_new_posts($forum_data, count($this->children[$forum_data['id']]) > 0, "", $this->mods);

		if ($forum_data['img_new_post'] == '<{C_ON}>')
		{
			$forum_data['img_new_post'] = $this->board_html->forum_img_with_link($forum_data['img_new_post'], $forum_data['id']);
		}

		$forum_data['last_post'] = $ibforums->functions->get_date($forum_data['last_post']);

		$forum_data['last_topic'] = $ibforums->lang['f_none'];

		if (isset($forum_data['last_title']) and $forum_data['last_id'])
		{
			$forum_data['last_title'] = strip_tags($forum_data['last_title']);
			$forum_data['last_title'] = str_replace("&#33;", "!", $forum_data['last_title']);
			$forum_data['last_title'] = str_replace("&quot;", "\"", $forum_data['last_title']);

			if (strlen($forum_data['last_title']) > 30)
			{
				$forum_data['last_title'] = substr($forum_data['last_title'], 0, 27) . "...";
				$forum_data['last_title'] = preg_replace('/&(#(\d+;?)?)?\.\.\.$/', '...', $forum_data['last_title']);
			} else
			{
				$forum_data['last_title'] = preg_replace('/&(#(\d+?)?)?$/', '', $forum_data['last_title']);
			}

			if ($forum_data['password'] != "")
			{
				$forum_data['last_topic'] = $ibforums->lang['f_protected'];
			} else
			{
				$forum_data['last_unread'] = $this->board_html->forumrow_lastunread_link($forum_data['id'], $forum_data['last_id']);

				$forum_data['last_topic'] = "<a href='{$ibforums->base_url}showtopic={$forum_data['last_id']}&amp;view=getlastpost' title='{$ibforums->lang['tt_gounread']}'>{$forum_data['last_title']}</a>";
			}
		}

		if (isset($forum_data['last_poster_name']))
		{
			$forum_data['last_poster'] = $forum_data['last_poster_id']
				? "<a href='{$ibforums->base_url}showuser={$forum_data['last_poster_id']}'>{$forum_data['last_poster_name']}</a>"
				: $forum_data['last_poster_name'];
		} else
		{
			$forum_data['last_poster'] = $ibforums->lang['f_none'];
		}

		$forum_data['posts']  = $ibforums->functions->do_number_format($forum_data['posts']);
		$forum_data['topics'] = $ibforums->functions->do_number_format($forum_data['topics']);

		$forum_data['tree'] = '  <td colspan="2" class="row4" align="center">' . $forum_data['img_new_post'] . '</td>';

		return $this->board_html->ForumRow($forum_data);
	}

	//+----------------------------------------------------------------
	//
	// Show the forum rules on a separate page
	//
	//+----------------------------------------------------------------

	function show_rules()
	{
		global $print, $skin_universal;
		$ibforums = Ibf::instance();

		//+--------------------------------------------
		// Do we have permission to view these rules?
		//+--------------------------------------------

		$bad_entry = $this->check_access();

		if ($bad_entry)
		{
			$ibforums->functions->Error(array('LEVEL' => 1, 'MSG' => 'no_view_topic'));
		}

		if ($this->forum['rules_title'])
		{
			$rules['fid'] = $ibforums->input['f'];

			$rules['title'] = $this->parser->prepare(array(
			                                              'TEXT'      => $this->forum['rules_title'],
			                                              'SMILIES'   => 1,
			                                              'CODE'      => 1,
			                                              'SIGNATURE' => 0,
			                                              'HTML'      => 0,
			                                         ));

			$rules['body'] = $this->parser->prepare(array(
			                                             'TEXT'      => $this->forum['rules_text'],
			                                             'SMILIES'   => 1,
			                                             'CODE'      => 1,
			                                             'SIGNATURE' => 0,
			                                             'HTML'      => 0,
			                                        ));

			$rules['body'] = str_replace(";&lt;br&gt;", "<br>", $rules['body']);

			if ($this->forum['red_border'])
			{
				$rules['body'] .= "</td></tr></table>";
				$rules['body'] = "<table bgColor=#ffffff style='border:2px solid red;'><tr><td>" . $rules['body'];
			}

			$this->output .= $this->html->show_rules($rules);

			$print->add_output("$this->output");

			$print->do_output(array(
			                       'TITLE' => $ibforums->vars['board_name'] . " -&gt; " . $this->forum['name'],
			                       'JS'    => 0,
			                       'NAV'   => $this->nav_extra,
			                       'RSS'   => $skin_universal->rss("?f={$this->forum['id']}"),
			                  ));
		} else
		{
			$ibforums->functions->Error(array('LEVEL' => 1, 'MSG' => 'no_view_topic'));
		}
	}

	//+----------------------------------------------------------------
	//
	// Authenicate the log in for a password protected forum
	//
	//+----------------------------------------------------------------

	function authenticate_user()
	{
		global $print;
		$ibforums = Ibf::instance();

		if ($ibforums->input['f_password'] == "")
		{
			$ibforums->functions->Error(array('LEVEL' => 1, 'MSG' => 'pass_blank'));
		}

		if ($ibforums->input['f_password'] != $this->forum['password'])
		{
			$ibforums->functions->Error(array('LEVEL' => 1, 'MSG' => 'wrong_pass'));
		}

		$ibforums->functions->my_setcookie("iBForum" . $this->forum['id'], $ibforums->input['f_password']);

		$print->redirect_screen($ibforums->lang['logged_in'], "showforum=" . $this->forum['id'], 'html');
	}

	//+----------------------------------------------------------------------------------
	function check_access()
	{
		$ibforums = Ibf::instance();
		$return   = 1;

		if ($ibforums->functions->check_perms($this->forum['read_perms']) == TRUE)
		{
			$return = 0;
		}

		// Do we have permission to even see the password page?

		if ($return == 0)
		{
			if ($this->forum['password'])
			{
				if ($_COOKIE[$ibforums->vars['cookie_id'] . 'iBForum' . $this->forum['id']] == $this->forum['password'])
				{
					$return = 0;
				} else
				{
					$this->forum_login();
				}
			}
		}

		return $return;
	}

	//+----------------------------------------------------------------------------------

	function forum_login()
	{
		global $print, $skin_universal;
		$ibforums = Ibf::instance();

		if (empty($ibforums->member['id']))
		{
			$ibforums->functions->Error(array('LEVEL' => 1, 'MSG' => 'no_guests'));
		}

		$this->output = $this->html->Forum_log_in($this->forum['id']);

		$print->add_output("$this->output");

		$print->do_output(array(
		                       'TITLE' => $ibforums->vars['board_name'] . " -> " . $this->forum['name'],
		                       'JS'    => 0,
		                       'NAV'   => $this->nav_extra,
		                       'RSS'   => $skin_universal->rss("?f={$this->forum['id']}"),
		                  ));
	}

	//+----------------------------------------------------------------
	//
	// Forum view check for authentication
	//
	//+----------------------------------------------------------------

	function show_forum()
	{

		// are we checking for user authentication via the log in form
		// for a private forum w/password protection?

		$ibforums->input['L'] == 1
			? $this->authenticate_user()
			: $this->render_forum();
	}

	//+----------------------------------------------------------------
	//
	// Main render forum engine
	//
	//+----------------------------------------------------------------

	function render_forum()
	{
		global $print;
		$ibforums = Ibf::instance();

		$bad_entry = $this->check_access();

		if ($bad_entry == 1 or !$this->ids)
		{
			if ($this->forum['subwrap'] == 1)
			{
				// Dont' show an error as we may have sub forums up top
				// Instead, copy the sub forum ouput to the main output
				// and return gracefully

				$this->output     = $this->sub_output;
				$this->sub_output = "";

				return TRUE;
			} else
			{
				$ibforums->functions->Error(array('LEVEL' => 1, 'MSG' => 'no_permission'));
			}
		}

		$prune_value = $ibforums->functions->select_var(array(
		                                                     1 => $ibforums->input['prune_day'],
		                                                     2 => $this->forum['prune'],
		                                                     3 => '100'
		                                                ));

		$sort_key = $ibforums->functions->select_var(array(
		                                                  1 => $ibforums->input['sort_key'],
		                                                  2 => $this->forum['sort_key'],
		                                                  3 => 'last_post'
		                                             ));

		$sort_by = $ibforums->functions->select_var(array(
		                                                 1 => $ibforums->input['sort_by'],
		                                                 2 => $this->forum['sort_order'],
		                                                 3 => 'Z-A'
		                                            ));

		$First = $ibforums->functions->select_var(array(
		                                               1 => intval($ibforums->input['st']),
		                                               2 => 0
		                                          ));

		// Figure out sort order, day cut off, etc

		$Prune = $prune_value != 100
			? (time() - ($prune_value * 60 * 60 * 24))
			: -1;

		$sort_keys = array(
			'last_post'        => 'sort_by_date',
			'title'            => 'sort_by_topic',
			'starter_name'     => 'sort_by_poster',
			'posts'            => 'sort_by_replies',
			'views'            => 'sort_by_views',
			'start_date'       => 'sort_by_start',
			'last_poster_name' => 'sort_by_last_poster',
		);

		$prune_by_day = array(
			'1'   => 'show_today',
			'5'   => 'show_5_days',
			'7'   => 'show_7_days',
			'10'  => 'show_10_days',
			'15'  => 'show_15_days',
			'20'  => 'show_20_days',
			'25'  => 'show_25_days',
			'30'  => 'show_30_days',
			'60'  => 'show_60_days',
			'90'  => 'show_90_days',
			'100' => 'show_all',
		);

		$sort_by_keys = array(
			'Z-A' => 'descending_order',
			'A-Z' => 'ascending_order',
		);

		//+----------------------------------------------------------------
		// check for any form funny business by wanna-be hackers
		//+----------------------------------------------------------------
		// Song * secure patch

		if ((!isset($sort_keys[$sort_key])) or (!isset($prune_by_day[$prune_value])) or (!isset($sort_by_keys[$sort_by])))
		{
			$ibforums->functions->Error(array('LEVEL' => 5, 'MSG' => 'incorrect_use'));
		}

		// /Song * secure patch

		$r_sort_by = $sort_by == 'A-Z'
			? 'ASC'
			: 'DESC';

		//+----------------------------------------------------------------
		// Query the database to see how many topics there are in the forum
		//+----------------------------------------------------------------
		// Song * forum filter, 19.11.2004

		$stmt = $ibforums->db->query("SELECT COUNT(tid) as max FROM ibf_topics WHERE forum_id{$this->ids}
				and approved=1 and (pinned=1 or last_post > $Prune) and deleted=0");

		$total_possible = $stmt->fetch();

		//+----------------------------------------------------------------
		// Generate the forum page span links
		//+----------------------------------------------------------------

		$this->forum['SHOW_PAGES'] = $ibforums->functions->build_pagelinks(array(
		                                                                        'TOTAL_POSS' => $total_possible['max'],
		                                                                        'PER_PAGE'   => $ibforums->vars['display_max_topics'],
		                                                                        'CUR_ST_VAL' => $ibforums->input['st'],
		                                                                        'L_SINGLE'   => $ibforums->lang['single_page_forum'],
		                                                                        'BASE_URL'   => $this->base_url . "showforum=" . $this->forum['id'] . "&amp;view={$ibforums->input['view']}&amp;prune_day=$prune_value&amp;sort_by=$sort_by&amp;sort_key=$sort_key",
		                                                                   ));
		// Song * show only new topics, 17.01.05

		if ($ibforums->member['id'])
		{
			if ($ibforums->input['view'] == 'new')
			{
				$this->forum['show_all_topics'] = " (<a href='{$this->base_url}showforum={$this->forum['id']}&amp;view=all&amp;prune_day=$prune_value&amp;sort_by=$sort_by&amp;sort_key=$sort_key&amp;st={$ibforums->input['st']}'>{$ibforums->lang['show_all_topics']}</a>)";
			} else
			{
				$this->forum['show_all_topics'] = " (<a href='{$this->base_url}showforum={$this->forum['id']}&amp;view=new&amp;prune_day=$prune_value&amp;sort_by=$sort_by&amp;sort_key=$sort_key&amp;st={$ibforums->input['st']}'>{$ibforums->lang['show_only_new']}</a>)";
			}
		}

		//+----------------------------------------------------------------
		// Do we have any rules to show?
		//+----------------------------------------------------------------
		// Song * IBF forum rules

		if ($this->forum['show_rules'])
		{
			if ($this->forum['rules_title'])
			{
				$this->forum['rules_title'] = trim($this->parser->prepare(array(
				                                                               'TEXT'      => $this->forum['rules_title'],
				                                                               'SMILIES'   => 1,
				                                                               'CODE'      => 1,
				                                                               'SIGNATURE' => 0,
				                                                               'HTML'      => 0,
				                                                          )));

				$this->forum['rules_text'] = trim($this->parser->prepare(array(
				                                                              'TEXT'      => $this->forum['rules_text'],
				                                                              'SMILIES'   => 1,
				                                                              'CODE'      => 1,
				                                                              'SIGNATURE' => 0,
				                                                              'HTML'      => 0,
				                                                         )));

				$this->forum['rules_text'] = str_replace(";&lt;br&gt;", "<br>", $this->forum['rules_text']);

				$this->output .= $ibforums->functions->print_forum_rules($this->forum);
			}
		}

		//+----------------------------------------------------------------
		// Generate the poll button
		//+----------------------------------------------------------------

		$topic = "<a href='" . $this->base_url . "act=Post&amp;CODE=00&amp;f=" . $this->forum['id'] . "'><{A_POST}></a> &middot; ";
		$topic = ($this->allow_topic())
			? $topic
			: "";

		$poll = "<a href='" . $this->base_url . "act=Post&amp;CODE=10&amp;f=" . $this->forum['id'] . "'><{A_POLL}></a>";
		$poll = ($topic and $this->forum['allow_poll'])
			? $poll
			: "";

		// Song * forum filter, 19.11.2004

		if ($ibforums->member['id'] and $ibforums->member['show_filter'])
		{
			// draw combobox
			$this->forum['filter'] = $ibforums->functions->forum_filter($this->forum, $this->forums_id, $this->mode_id, $this->main_id);

			if (!$this->forum['sub_can_post'])
			{
				$this->forum['TOPIC_BUTTON'] = "";
				$this->forum['POLL_BUTTON']  = "";
			} else
			{
				$this->forum['TOPIC_BUTTON'] = $topic;
				$this->forum['POLL_BUTTON']  = $poll;
			}
		} else
		{
			$this->forum['TOPIC_BUTTON'] = $topic;
			$this->forum['POLL_BUTTON']  = $poll;
		}

		// /Song * forum filter, 19.11.2004
		//+----------------------------------------------------------------
		// Start printing the page
		//+----------------------------------------------------------------

		if ($ibforums->member['id'])
		{

			// Song * quick search
			$this->forum['quick_search'] = ($ibforums->member['quick_search'])
				? $this->html->quick_search($this->forum)
				: "";

			$this->forum['mark_read'] = $this->html->mark_forum_read($this->forum);
		}

		// Song * moderator checkbox, 09.04.2005

		if ($this->mod)
		{
			$this->forum['last_column'] = $this->html->last_mod_column();

			$this->forum['modform_open']  = $this->html->modform_open($this->forum);
			$this->forum['modform_close'] = $this->html->modform_close();
		} else
		{
			$this->forum['last_column'] = $this->html->last_column();
		}

		$this->output .= $this->html->PageTop($this->forum);

		//+----------------------------------------------------------------
		// Do we have any topics to show?
		//+----------------------------------------------------------------

		if ($total_possible['max'] < 1)
		{
			$this->output .= $this->html->show_no_matches();
		}

		$total_topics_printed = 0;

		$time = time();

		$query = "SELECT *, IF (pinned=1,pinned_date,{$time}) as my_pinned FROM ibf_topics
			   WHERE forum_id{$this->ids} and (pinned=1 or last_post > $Prune)";

		//+----------------------------------------------------------------
		// Do we have permission to view other posters topics?
		//+----------------------------------------------------------------

		if (!$ibforums->member['g_other_topics'])
		{
			$query .= " and starter_id='" . $ibforums->member['id'] . "'";
		}

		//+----------------------------------------------------------------
		// Finish off the query
		//+----------------------------------------------------------------

		$First = $First
			? $First
			: 0;

		if ($this->forum['has_mod_posts'] && $this->is_moderator($this->forum['id']))
		{
			$mod_order = ' (SELECT queued FROM ibf_posts WHERE queued = 1 AND topic_id = ibf_topics.tid LIMIT 1) DESC,  ';
		} else
		{
			$mod_order = '';
		}

		$query .= " and deleted=0 ORDER BY $mod_order my_pinned, $sort_key $r_sort_by LIMIT $First," . $ibforums->vars['display_max_topics'];

		$stmt = $ibforums->db->query($query);

		//+----------------------------------------------------------------
		// Grab the rest of the topics and print them
		//+----------------------------------------------------------------

		$tids      = array();
		$topiclist = array();

		while ($topic = $stmt->fetch())
		{
			// Song * club tool
			if ($topic['club'] and $ibforums->functions->check_perms($ibforums->member['club_perms']) == FALSE)
			{
				continue;
			}

			// Song * premoderation, 16.03.05

			if (!$topic['approved'] and !$ibforums->functions->premod_rights($topic['starter_id'], $this->mods[$this->forum['id']][$ibforums->member['id']]['topic_q'], $topic['app']))
			{
				continue;
			}

			// Song * hidden topics, 11.04.05

			if ($topic['hidden'] and !($ibforums->member['g_is_supmod'] or $this->mods[$this->forum['id']][$ibforums->member['id']]['hide_topic']))
			{
				continue;
			}

			$tids[] = $topic['tid'];
			$total_topics_printed++;

			$topiclist[$total_topics_printed] = $topic;
		}

		if ($ibforums->member['id'] and count($tids) > 0)
		{
			$tids = implode(",", $tids);

			$this->get_read_topics($tids); // Song * NEW

			$stmt = $ibforums->db->query("SELECT pid,topic_id,forum_id,author_id,queued FROM ibf_posts WHERE topic_id IN ({$tids})");

			while ($row = $stmt->fetch())
			{
				if ($ibforums->vars['show_user_posted'] and
				    $ibforums->member['use_dot'] and
				    $row['author_id'] == $ibforums->member['id'] and
				    !$this->dots[$row['topic_id']]
				)
				{
					$this->dots[$row['topic_id']] = $row['pid'];
				}

				if ($row['queued'] and !$this->queued[$row['topic_id']])
				{
					$this->queued[$row['topic_id']] = $row['pid'];
				}
			}
		}

		$this->new_posts = 0;

		//		$this->favs = explode(",", $ibforums->member['favorites']);
		$this->favs = $ibforums->member['favorites'];

		$this->total_topics = $total_topics_printed;

		for ($i = 1; $i <= $total_topics_printed; $i++)
		{
			$this->output .= $this->render_entry($topiclist[$i]);
		}

		//+----------------------------------------------------------------
		// Finish off the rest of the page
		//+----------------------------------------------------------------

		$ibforums->lang['showing_text'] = preg_replace("/<#MATCHED_TOPICS#>/", $this->total_topics, $ibforums->lang['showing_text']);
		$ibforums->lang['showing_text'] = preg_replace("/<#TOTAL_TOPICS#>/", $total_possible['max'], $ibforums->lang['showing_text']);

		$sort_key_html  = "<select name='sort_key'  class='forminput'>\n";
		$prune_day_html = "<select name='prune_day' class='forminput'>\n";
		$sort_by_html   = "<select name='sort_by'   class='forminput'>\n";

		foreach ($sort_by_keys as $k => $v)
		{
			$sort_by_html .= $k == $sort_by
				? "<option value='$k' selected='selected'>" . $ibforums->lang[$sort_by_keys[$k]] . "</option>\n"
				: "<option value='$k'>" . $ibforums->lang[$sort_by_keys[$k]] . "</option>\n";
		}

		foreach ($sort_keys as $k => $v)
		{
			$sort_key_html .= $k == $sort_key
				? "<option value='$k' selected='selected'>" . $ibforums->lang[$sort_keys[$k]] . "</option>\n"
				: "<option value='$k'>" . $ibforums->lang[$sort_keys[$k]] . "</option>\n";
		}
		foreach ($prune_by_day as $k => $v)
		{
			$prune_day_html .= $k == $prune_value
				? "<option value='$k' selected='selected'>" . $ibforums->lang[$prune_by_day[$k]] . "</option>\n"
				: "<option value='$k'>" . $ibforums->lang[$prune_by_day[$k]] . "</option>\n";
		}

		$ibforums->lang['sort_text'] = preg_replace("!<#SORT_KEY_HTML#>!", "$sort_key_html</select>", $ibforums->lang['sort_text']);
		$ibforums->lang['sort_text'] = preg_replace("!<#ORDER_HTML#>!", "$sort_by_html</select>", $ibforums->lang['sort_text']);
		$ibforums->lang['sort_text'] = preg_replace("!<#PRUNE_HTML#>!", "$prune_day_html</select>", $ibforums->lang['sort_text']);

		$this->output .= $this->html->TableEnd($this->forum);

		//+----------------------------------------------------------------
		// If all the new topics have been read in this forum..
		//+----------------------------------------------------------------
		// Song * NEW
		$ibforums->functions->song_set_forumread($this->forum['id']);
		// Song * NEW
		//+----------------------------------------------------------------
		// Process users active in this forum
		//+----------------------------------------------------------------

		if ($ibforums->vars['no_au_forum'] != 1)
		{
			//+-----------------------------------------
			// Get the users
			//+-----------------------------------------

			$cut_off = ($ibforums->vars['au_cutoff'] != "")
				? $ibforums->vars['au_cutoff'] * 60
				: 900;

			$time = time() - $cut_off;

			$stmt = $ibforums->db->query("SELECT s.member_id, s.member_name, s.login_type, s.location, s.org_perm_id,
					g.suffix, g.prefix, g.g_perm_id
				    FROM ibf_sessions s
				    LEFT JOIN ibf_groups g ON (g.g_id=s.member_group)
				    WHERE s.r_in_forum{$this->ids} and s.running_time > $time");

			//+-----------------------------------------
			// Cache all printed members so we don't double print them
			//+-----------------------------------------

			$cached = array();
			$active = array('guests' => 0, 'anon' => 0, 'members' => 0, 'names' => "");

			while ($result = $stmt->fetch())
			{
				if ($result['org_perm_id'])
				{
					$result['g_perm_id'] = ($result['g_perm_id'])
						? $result['g_perm_id'] . "," . $result['org_perm_id']
						: $result['org_perm_id'];
				}

				if ($this->forum['read_perms'] != '*')
				{
					if ($result['g_perm_id'])
					{
						if (!preg_match("/(^|,)(" . str_replace(",", '|', $result['g_perm_id']) . ")(,|$)/", $this->forum['read_perms']))
						{
							continue;
						}
					} else
					{
						continue;
					}
				}

				if (!$result['member_id'])
				{
					$active['guests']++;
				} else
				{
					if (empty($cached[$result['member_id']]))
					{
						$cached[$result['member_id']] = 1;

						if ($result['login_type'] == 1)
						{
							if (($ibforums->member['mgroup'] == $ibforums->vars['admin_group']) and ($ibforums->vars['disable_admin_anon'] != 1))
							{
								$active['names'] .= "<a href='{$ibforums->base_url}showuser={$result['member_id']}'>{$result['prefix']}{$result['member_name']}{$result['suffix']}</a>*, ";
								$active['anon']++;
							} else
							{
								$active['anon']++;
							}
						} else
						{
							$active['members']++;
							$active['names'] .= "<a href='{$ibforums->base_url}showuser={$result['member_id']}'>{$result['prefix']}{$result['member_name']}{$result['suffix']}</a>, ";
						}
					}
				}
			}

			$active['names'] = preg_replace("/,\s+$/", "", $active['names']);

			$ibforums->lang['active_users_title']   = sprintf($ibforums->lang['active_users_title'], ($active['members'] + $active['guests'] + $active['anon']));
			$ibforums->lang['active_users_detail']  = sprintf($ibforums->lang['active_users_detail'], $active['guests'], $active['anon']);
			$ibforums->lang['active_users_members'] = sprintf($ibforums->lang['active_users_members'], $active['members']);

			$this->output = str_replace("<!--IBF.FORUM_ACTIVE-->", $this->html->forum_active_users($active), $this->output);
		}

		return TRUE;
	}

	//+----------------------------------------------------------------
	//
	// Crunches the data into pwetty html
	//
	//+----------------------------------------------------------------

	function render_entry($topic)
	{
		$ibforums           = Ibf::instance();
		$topic['last_text'] = $ibforums->lang['last_post_by'];

		$topic['last_poster'] = ($topic['last_poster_id'])
			? "<a href='{$this->base_url}showuser={$topic['last_poster_id']}'>{$topic['last_poster_name']}</a>"
			: "-" . $topic['last_poster_name'] . "-";

		$topic['starter'] = ($topic['starter_id'])
			? "<a href='{$this->base_url}showuser={$topic['starter_id']}'>{$topic['starter_name']}</a>"
			: "-" . $topic['starter_name'] . "-";

		if ($topic['starter_id'] and $topic['starter_id'] == $ibforums->member['id'])
		{
			$topic['starter'] = "<b>" . $topic['starter'] . "</b>";
		}

		if ($topic['poll_state'])
		{
			$topic['prefix'] = "<span class='voteprefix'>{$ibforums->vars['pre_polls']}</span> ";
		}

		$topic['topic_icon'] = $topic['icon_id']
			? '<img src="' . $ibforums->vars['img_url'] . '/icon' . $topic['icon_id'] . '.gif" border="0" alt="">'
			: '&nbsp;';

		$topic['start_date'] = $ibforums->functions->get_date($topic['start_date']);

		$pages = 1;

		if ($topic['posts'])
		{
			if ((($topic['posts'] + 1) % $ibforums->vars['display_max_posts']) == 0)
			{
				$pages = ($topic['posts'] + 1) / $ibforums->vars['display_max_posts'];
			} else
			{
				$number = (($topic['posts'] + 1) / $ibforums->vars['display_max_posts']);
				$pages  = ceil($number);
			}
		}

		if ($pages > 1)
		{
			$topic['PAGES'] = "<span class='small'>({$ibforums->lang['topic_sp_pages']} ";

			// Song * new pages, 14.02.05

			for ($i = 0; $i < $pages; ++$i)
			{
				$real_no = $i * $ibforums->vars['display_max_posts'];

				$page_no = $i + 1;

				if ($page_no == 4)
				{
					$tripledot = $pages == $page_no
						? ""
						: "... ";

					$topic['PAGES'] .= $tripledot;

					break;
				} else
				{
					$topic['PAGES'] .= "<a href='{$this->base_url}showtopic={$topic['tid']}&amp;st=$real_no'>$page_no</a> ";
				}
			}

			if ($pages - $page_no > 2)
			{
				for ($i = $pages - 2; $i < $pages; ++$i)
				{
					$real_no = $i * $ibforums->vars['display_max_posts'];

					$page_no = $i + 1;

					$topic['PAGES'] .= "<a href='{$this->base_url}showtopic={$topic['tid']}&amp;st=$real_no'>$page_no</a> ";
				}
			} elseif ($page_no > 3)
			{
				$topic['PAGES'] .= "<a href='{$this->base_url}showtopic={$topic['tid']}&amp;st=" . ($pages - 1) * $ibforums->vars['display_max_posts'] . "'>$pages</a>";
			}

			// Song * new pages, 14.02.05

			$topic['PAGES'] .= " <a href='{$this->base_url}showtopic={$topic['tid']}&amp;view=getlastpost'>#</a>";
			// Song * link "all"
			if ($topic['posts'] < $ibforums->vars['max_show_all_posts'])
			{
				$topic['PAGES'] .= " <a href='{$this->base_url}showtopic={$topic['tid']}&amp;view=showall'>" . $ibforums->lang['all_posts'] . "</a>";
			}

			// Song * link "all"
			$topic['PAGES'] .= ")</span>";
		}

		//------------------------------------------------
		// Format some numbers
		//------------------------------------------------

		if ($topic['posts'] < 0)
		{
			$topic['posts'] = 0;
		}

		$topic['posts'] = $ibforums->functions->do_number_format($topic['posts']);
		$topic['views'] = $ibforums->functions->do_number_format($topic['views']);

		if (!$topic['posts'])
		{
			$topic['posts'] = "<b>{$topic['posts']}</b>";
		}

		// Song * moderator checkbox, 09.04.2005
		// ********************** DO NOT DELETE THREE FOLLOWING LINES ! *************************************
		// save old ids for moderator checkbox
		$topic['old_tid']      = $topic['tid'];
		$topic['old_forum_id'] = $topic['forum_id'];

		// **************************************************************************************************
		// Song * moderator checkbox, 09.04.2005

		if ($topic['state'] == 'link' || $topic['state'] == 'mirror')
		{
			$this->new_posts--;

			// if only new
			if ($ibforums->input['view'] == "new")
			{
				$this->total_topics--;

				return "";
			}

			$topic['go_new_post'] = "";

			$t_array = explode("&", $topic['moved_to']);
			if ($topic['state'] == 'link')
			{
				$prefix         = $ibforums->vars['pre_moved'];
				$suffix         = $ibforums->lang['move_label'];
				$topic['views'] = '--';
				$topic['posts'] = '--';
				$topic['tid']   = $t_array[0];

				$topic['prefix'] = "<span class='movedprefix'>{$prefix}</span> ";
				if ($t_array[2] != '' && $t_array[3] != '' && $t_array[4] != '')
				{
					$topic['description'] = $suffix;

					if ($t_array[5] != '')
					{
						$topic['description'] .= $t_array[5] . " -> ";
					}

					$topic['description'] .= "<a href='{$ibforums->base_url}showforum={$t_array[1]}'>{$t_array[2]}</a> by <a href='{$ibforums->base_url}showuser={$t_array[3]}'>{$t_array[4]}</a>";
				}
			} else
			{
				//$prefix = $ibforums->lang['mirror_prefix'];
				//$suffix = $ibforums->lang['mirror_label'];

				$topic['prefix']      = ''; //"<span class='mirrorprefix'>{$prefix}</span>";
				$topic['forum_title'] = " title='{$ibforums->lang['mirror_title']} {$t_array[2]}'";
				/*
				  $topic['description'] .= $topic['description'] ? '<br>' : '';
				  $topic['description'] .= "<span class='mirrorhint'>$suffix</span>{$t_array[5]} ";

				  $topic['description'] .= "<a href='{$ibforums->base_url}showforum={$t_array[1]}'>{$t_array[2]}</a> </a>";
				 */
			}
		}
		if ($topic['state'] != 'link')
		{
			//------------------------------------------------
			// Last time stuff...
			//------------------------------------------------
			// Song * NEW
			if (!$ibforums->member['id'])
			{
				$last_time = '';
			} else
			{
				if (!isset($this->read_mark[$topic['forum_id']]))
				{
					$this->read_mark[$topic['forum_id']] = $ibforums->forums_read[$topic['forum_id']];

					$this->read_mark[$topic['forum_id']] = ($ibforums->member['board_read'] > $this->read_mark[$topic['forum_id']])
						? $ibforums->member['board_read']
						: $this->read_mark[$topic['forum_id']];

					$this->read_mark[$topic['forum_id']] = ($this->read_mark[$topic['forum_id']] < (time() - 60 * 60 * 24 * 30))
						? (time() - 60 * 60 * 24 * 30)
						: $this->read_mark[$topic['forum_id']];
				}

				if ($this->read_array[$topic['tid']])
				{
					$last_time = $this->read_array[$topic['tid']];
				} else
				{
					$last_time = -1;

					if ($topic['last_post'] < $this->read_mark[$topic['forum_id']])
					{
						$last_time = $this->read_mark[$topic['forum_id']];
					}
				}
			}
			// Song * NEW
			if ($last_time && ($topic['last_post'] > $last_time))
			{
				$topic['go_new_post'] = "<a href='{$this->base_url}showtopic={$topic['tid']}&amp;view=getnewpost'><{NEW_POST}></a>";

				$this->new_posts++;
			} else
			{
				$topic['go_new_post'] = "";

				// if only new
				if ($ibforums->input['view'] == "new")
				{
					$this->total_topics--;

					return "";
				}
			}
		}

		// Topic icon
		$topic['folder_img'] = $ibforums->functions->folder_icon($topic, $this->dots[$topic['tid']], $this->read_array[$topic['tid']], $this->read_mark[$topic['forum_id']]);

		$topic['last_post'] = $ibforums->functions->get_date($topic['last_post'], 'SHORT');

		if ($topic['state'] != 'link')
		{
			$topic['posts'] = $this->html->who_link($topic['tid'], $topic['posts']);
		}

		//+----------------------------------------------------------------

		$class   = ($topic['hidden'])
			? "darkrow3"
			: "row4";
		$p_start = "";
		$p_end   = "";

		$topic['queued_link'] = "";

		$q = 0;

		if ($ibforums->member['id'] and ($ibforums->member['g_is_supmod'] or
		                                 ($this->mods[$this->forum['id']][$ibforums->member['id']] and
		                                  $this->mods[$this->forum['id']][$ibforums->member['id']]['post_q']))
		                                and $topic['approved']
		)
		{
			if ($this->queued[$topic['tid']])
			{
				$q = 1;
			}
		}

		if ($topic['pinned'])
		{
			$class = "pinned_topic";

			$topic['title'] = "<b>" . $topic['title'] . "</b>";
		}

		// Moderator checkbox, 09.04.2005
		//	$this->mods[ $topic['old_forum_id'] ][ $ibforums->member['id'] ]

		if ($this->is_moderator($topic['old_forum_id']))
		{
			$topic['mod_checkbox'] = $this->html->mod_checkbox($class, $topic['old_tid']);
		} else
		{
			$topic['colspan'] = " colspan='2'";
		}

		// Song * favorite topics

		if (in_array($topic['tid'], $this->favs))
		{
			$class = "darkrow2";
		}

		// Song * premoderation links

		if ($q or (!$topic['approved'] and $topic['app']))
		{
			$class = "darkrow2";

			$topic['queued_link'] = "";
			$topic_link           = "<br>" . $ibforums->lang['waiting_topics'];
			$topic_link .= "<a href='{$ibforums->base_url}act=modcp&amp;CODE=domodtopics&amp;f={$this->forum['id']}&amp;TID_{$topic['tid']}=approve'>{$ibforums->lang['modcp_accept']}</a> � ";
			$topic_link .= "<a href='{$ibforums->base_url}act=modcp&amp;CODE=domodtopics&amp;f={$this->forum['id']}&amp;TID_{$topic['tid']}=remove'>{$ibforums->lang['modcp_reject']}</a>";

			if (!$topic['approved'])
			{
				$topic['queued_link'] = $topic_link;
			} else
			{
				if ($q)
				{
					$topic['queued_link'] = "<br><a href='{$this->base_url}showtopic={$topic['tid']}&amp;view=findpost&amp;p={$this->queued[$topic['tid']]}'>" . $ibforums->lang['waiting_messages'] . "</a>";
				}
			}
		}

		// Song * premoderation links
		// Song * club tool

		if ($topic['club'])
		{
			$topic['prefix'] = "<span class='clubprefix'>{$ibforums->vars['pre_club']}</span> ";
		}

		// Song * club tool
		// Song * forum filter, 06.01.05

		if ($this->mode_id)
		{
			$title = $this->forums_list[$topic['forum_id']];

			if ($title)
			{
				$topic['forum_title'] = " title='" . $ibforums->lang['in_forum'] . ": $title'";
			}
		}

		// Song * forum filter, 06.01.05
		// color of cell
		$topic['color'] = $class;

		// Song * decided topics, 20.04.05

		if ($topic['decided'])
		{
			$topic['topic_icon'] = "<{B_DECIDED}>";
		}

		// Song * decided topics, 20.04.05

		if ($topic['pinned'])
		{
			if (!$topic['prefix'])
			{
				$topic['prefix'] = "<span class='pinnedprefix'>{$ibforums->vars['pre_pinned']}</span> ";
			}

			$topic['topic_icon'] = "<{B_PIN}>";

			if (!$this->pinned_print)
			{
				// we've a pinned topic, but we've not printed the pinned
				// starter row, so..
				$this->pinned_print = 1;
			}

			return $p_start . $this->html->render_pinned_row($topic);
		} else
		{
			// This is not a pinned topic, so lets check to see if we've
			// printed the footer yet.

			if ($this->pinned_print)
			{
				$this->pinned_print = 0;
			}

			return $p_end . $this->html->RenderRow($topic);
		}
	}

	//+----------------------------------------------------------------
	//
	// Returns the last action date
	//
	//+----------------------------------------------------------------

	function get_last_date($topic)
	{
		return Ibf::instance()->functions->get_date($topic['last_post'], 'SHORT');
	}

	// Song * NEW

	function get_read_topics($tids)
	{
		$ibforums = Ibf::instance();

		$stmt = $ibforums->db->query("SELECT fid,tid,logTime FROM ibf_log_topics WHERE mid='" . $ibforums->member['id'] . "' and
			tid IN ({$tids})");

		if ($stmt->rowCount())
		{
			while ($read = $stmt->fetch())
			{
				$fid = $read['fid'];
				$tid = $read['tid'];

				if (!$this->read_mark[$fid])
				{
					$this->read_mark[$fid] = $ibforums->forums_read[$fid];

					$this->read_mark[$fid] = ($ibforums->member['board_read'] > $this->read_mark[$fid])
						? $ibforums->member['board_read']
						: $this->read_mark[$fid];

					$this->read_mark[$fid] = ($this->read_mark[$fid] < (time() - 60 * 60 * 24 * 30))
						? (time() - 60 * 60 * 24 * 30)
						: $this->read_mark[$fid];
				}

				$this->read_array[$tid] = ($this->read_mark[$fid] > $read['logTime'])
					? $this->read_mark[$fid]
					: $read['logTime'];
			}
		}
	}

	// Song * new topic rights

	function allow_topic()
	{
		$ibforums = Ibf::instance();

		if (!$this->forum['sub_can_post'] or
		    !$ibforums->member['g_post_new_topics'] or
		    $ibforums->functions->check_perms($this->forum['start_perms']) == FALSE
		)
		{
			return 0;
		}

		return 1;
	}

}

