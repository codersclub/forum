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
|   > Show online users
|   > Module written by Matt Mecham
|   > Date started: 12th March 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new Online;

class Online
{

	var $output = "";
	var $page_title = "";
	var $nav = array();
	var $html = "";
	var $first = 0;
	var $perpage = 25;

	var $forums = array();
	var $cats = array();
	var $sessions = array();
	var $where = array();

	var $seen_name = array();

	function Online()
	{
		global $ibforums, $std, $print;

		// Are we allowed to see the online list?

		$ibforums->input['st'] = intval($ibforums->input['st']);

		if ($ibforums->vars['allow_online_list'] != 1)
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_permission'));
		}

		// vot: Disable negative 'st' value
		if ($ibforums->input['st'] < 0)
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_permission'));
		}

		if ($ibforums->input['CODE'] == "")
		{
			$ibforums->input['CODE'] = 'listall';
		}

		//--------------------------------------------
		// Require the HTML and language modules
		//--------------------------------------------

		$ibforums->lang = $std->load_words($ibforums->lang, 'lang_online', $ibforums->lang_id);

		$this->html = $std->load_template('skin_online');

		$this->base_url = $ibforums->base_url;

		//--------------------------------------------
		// Build up our language hash
		//--------------------------------------------

		foreach ($ibforums->lang as $k => $v)
		{
			if (preg_match("/^WHERE_(\w+)$/", $k, $match))
			{
				$this->where[$match[1]] = $ibforums->lang[$k];
			}
		}

		unset($match);

		//--------------------------------------------
		// What to do?
		//--------------------------------------------

		switch ($ibforums->input['CODE'])
		{
			case 'listall':
				$this->list_all();
				break;
			case '02':
				$this->list_forum();
				break;
			default:
				$this->list_all();
				break;
		}

		// If we have any HTML to print, do so...

		$print->add_output("$this->output");
		$print->do_output(array('TITLE' => $this->page_title, 'JS' => 0, 'NAV' => $this->nav));

	}

	/*****************************************************/
	// list_all
	// ------------------
	// List all online users
	/*****************************************************/

	function list_all()
	{
		global $ibforums, $std;

		$this->first = 0;

		if (!empty($ibforums->input['st']))
		{
			$this->first = $ibforums->input['st'];
		}

		$show_mem   = array('reg', 'guest', 'all');
		$sort_order = array('desc', 'asc');
		$sort_key   = array('click', 'name');

		$show_mem_value   = $ibforums->input['show_mem']
			? $ibforums->input['show_mem']
			: 'all';
		$sort_order_value = $ibforums->input['sort_order']
			? $ibforums->input['sort_order']
			: 'desc';
		$sort_key_value   = $ibforums->input['sort_key']
			? $ibforums->input['sort_key']
			: 'click';

		$show_mem_html   = "";
		$sort_order_html = "";
		$sort_key_html   = "";

		$oo = "<option ";
		$oc = "</option>\n";

		foreach ($show_mem as $k)
		{
			$s = "";

			if ($show_mem_value == $k)
			{
				$s = ' selected="selected" ';
			}

			$show_mem_html .= $oo . 'value="' . $k . '"' . $s . '>' . $ibforums->lang['s_show_mem_' . $k] . $oc;
		}

		foreach ($sort_order as $k)
		{
			$s = "";

			if ($sort_order_value == $k)
			{
				$s = ' selected="selected" ';
			}

			$sort_order_html .= $oo . 'value="' . $k . '"' . $s . '>' . $ibforums->lang['s_sort_order_' . $k] . $oc;
		}

		foreach ($sort_key as $k)
		{
			$s = "";

			if ($sort_key_value == $k)
			{
				$s = ' selected="selected" ';
			}

			$sort_key_html .= $oo . 'value="' . $k . '"' . $s . '>' . $ibforums->lang['s_sort_key_' . $k] . $oc;
		}

		$last_cat_id = -1;

		$stmt = $ibforums->db->query("SELECT f.id, f.name, f.read_perms, f.password, c.id as cat_id, c.name as cat_name from ibf_forums f, ibf_categories c where c.id=f.category ORDER BY c.position, f.position");

		while ($i = $stmt->fetch())
		{
			if ($last_cat_id != $i['cat_id'])
			{
				// Print the category

				$last_cat_id = $i['cat_id'];

				$this->cats[$i['cat_id']] = $this->cats['cat_name'];

			}

			$this->forums[$i['id']] = array(
				'name'       => $i['name'],
				'read_perms' => $i['read_perms'],
				'password'   => $i['password'],
			);

		}

		$cut_off = ($ibforums->vars['au_cutoff'] != "")
			? $ibforums->vars['au_cutoff'] * 60
			: 900;

		$t_time = time() - $cut_off;

		$db_order = $sort_order_value == 'asc'
			? 'asc'
			: 'desc';
		$db_key   = $sort_key_value == 'click'
			? 'running_time'
			: 'member_name';

		switch ($show_mem_value)
		{
			case 'reg':
				$db_mem = " AND (s.member_group <> {$ibforums->vars['guest_group']} OR s.id LIKE '%session' ) ";
				break;
			case 'guest':
				$db_mem = " AND s.member_group = {$ibforums->vars['guest_group']} ";
				break;
			default:
				$db_mem = "";
				break;
		}

		$stmt = $ibforums->db->query("SELECT COUNT(id) as total_sessions FROM ibf_sessions s WHERE login_type <> 1 AND running_time > $t_time" . $db_mem);
		$max  = $stmt->fetch();

		$links = $std->build_pagelinks(array(
		                                    'TOTAL_POSS' => $max['total_sessions'],
		                                    'PER_PAGE'   => 25,
		                                    'CUR_ST_VAL' => $this->first,
		                                    'L_SINGLE'   => "",
		                                    'L_MULTI'    => $ibforums->lang['pages'],
		                                    'BASE_URL'   => $this->base_url . "&amp;act=Online&amp;CODE=listall&amp;sort_key=$sort_key_value&amp;sort_order=$sort_order_value&amp;show_mem=$show_mem_value"
		                               ));

		$this->output = $this->html->Page_header($links);

		// Grab all the current sessions.

		$final     = array();
		$tid_array = array();

		$topics = array();

		$stmt = $ibforums->db->query("SELECT s.id, s.in_forum, s.in_topic, s.member_name, s.member_id, s.ip_address, s.running_time,
				   s.location, s.login_type, s.member_group, g.prefix, g.suffix, IFNULL(c.id,0) as friend
			    FROM (ibf_sessions s, ibf_groups g )
			    LEFT JOIN ibf_contacts c ON (c.member_id='" . $ibforums->member['id'] . "' and c.contact_id=s.member_id and c.show_online=1)
			    WHERE s.running_time > $t_time AND s.member_group=g.g_id $db_mem
			    ORDER BY friend DESC, $db_key $db_order,s.running_time DESC LIMIT " . $this->first . ",25");

		while ($r = $stmt->fetch())
		{
			$final[] = $r;

			if ($r['in_topic'] != "")
			{
				$tid_array[] = $r['in_topic'];
			}
		}

		if (count($tid_array) > 0)
		{
			$tid_string = implode(",", $tid_array);

			$tid_string = str_replace(",,", ",", $tid_string);

			$stmt = $ibforums->db->query("SELECT tid, title, club FROM ibf_topics WHERE tid IN ($tid_string)");

			while ($t = $stmt->fetch())
			{
				if ($t['club'] and $std->check_perms($ibforums->member['club_perms']) == FALSE)
				{
					continue;
				}

				$topics[$t['tid']] = $t['title'];
			}
		}

		foreach ($final as $idx => $sess)
		{

			//----------------------------------------------------
			// Is this a member, and have we seen them before?
			// Proxy servers, etc can confuse the session handler,
			// creating duplicate session IDs for the same user when
			// their IP address changes.
			//----------------------------------------------------

			$inv = '';

			if (strstr($sess['id'], '_session'))
			{
				$sess['is_bot'] = 1;

				if ($ibforums->vars['spider_anon'])
				{
					if ($ibforums->member['mgroup'] == $ibforums->vars['admin_group'])
					{
						$inv = '*';
					} else
					{
						continue;
					}
				}
			} else {
				if ($sess['login_type'] == 1)
				{
					if (($ibforums->member['mgroup'] == $ibforums->vars['admin_group']) and ($ibforums->vars['disable_admin_anon'] != 1))
					{
						$inv = '*';
					} else
					{
						continue;
					}
				}
			}

			//----------------------------------------------------
			// ICheck for dupes
			//----------------------------------------------------

			if (!empty($sess['member_name']))
			{
				if (isset($this->seen_name[$sess['member_name']]))
				{
					continue;
				} else
				{
					$this->seen_name[$sess['member_name']] = 1;
				}
			}

			//----------------------------------------------------
			// Figure out location
			//----------------------------------------------------

			$line = "";

			if (isset($sess['location']))
			{
				$location = $sess['location'] . '|';
				$in_forum = $sess['in_forum'] . ',';
				$in_topic = $sess['in_topic'] . ',';

				do
				{
					$sess['location'] = substr($location, 0, strpos($location, '|'));
					$sess['in_forum'] = substr($in_forum, 0, strpos($in_forum, ','));
					$sess['in_topic'] = substr($in_topic, 0, strpos($in_topic, ','));

					$location = substr($location, strpos($location, '|') + 1);
					$in_forum = substr($in_forum, strpos($in_forum, ',') + 1);
					$in_topic = substr($in_topic, strpos($in_topic, ',') + 1);

					list($act, $pid) = explode(",", $sess['location']);
					$fid = $sess['in_forum'];
					$tid = $sess['in_topic'];

					if (isset($act))
					{
						$line .= isset($this->where[$act])
							? $this->where[$act]
							: $ibforums->lang['board_index'];
					}

					if ($fid != "" and ($act == 'SF' or $act == 'ST' or $act == 'Post'))
					{
						$pass = 0;

						if ($std->check_perms($this->forums[$fid]['read_perms']) == TRUE)
						{
							$pass = 1;
						}

						if ($pass == 1)
						{
							// Check cookie (monster)

							if ($this->forums[$fid]['password'] != "")
							{
								if (!$c_pass = $std->my_getcookie('iBForum' . $fid))
								{
									$pass = 0;
								}

								if ($c_pass == $this->forums[$fid]['password'])
								{
									$pass = 1;
								} else
								{
									$pass = 0;
								}
							} else
							{
								$pass = 1;
							}
						}

						if ($pass == 1 and $tid > 0 and !$topics[$tid])
						{
							$tid = 0;
						}

						if ($pass == 1)
						{
							if (($tid > 0) and ($act != 'Post'))
							{
								$line .= " <a href='{$this->base_url}&act=ST&f=$fid&t=$tid'>{$topics[$tid]}</a>";
							} else
							{
								$line .= " <a href='{$this->base_url}&act=SF&f=$fid'>{$this->forums[ $fid ]['name']}</a>";
							}
						} else
						{
							$line .= " {$ibforums->lang['board_index']}";
						}
					}

					$line .= '<br>';
				} while (strstr($location, '|'));
			} else
			{
				$line .= " {$ibforums->lang['board_index']}";
			}

			$sess['where_line'] = $line;

			if (($ibforums->member['mgroup'] == $ibforums->vars['admin_group']) and ($ibforums->vars['disable_online_ip'] != 1))
			{
				$sess['ip_address'] = "( <a href='{$ibforums->base_url}&act=modcp&CODE=ip&incoming={$sess['ip_address']}' target='_blank'>{$sess['ip_address']}</a> )";
			} else
			{
				$sess['ip_address'] = "";
			}

			if (($sess['member_id']))
			{
				$sess['member_name'] = "<a href='{$this->base_url}showuser={$sess['member_id']}'>{$sess['prefix']}{$sess['member_name']}{$sess['suffix']}</a>$inv {$sess['ip_address']}";
			}

			$sess['running_time'] = $std->get_date($sess['running_time']);

			$this->output .= $this->do_html_row($sess);

		}

		$this->output .= $this->html->Page_end($show_mem_html, $sort_order_html, $sort_key_html, $links);

		$this->page_title = $ibforums->lang['page_title'];
		$this->nav        = array($ibforums->lang['page_title']);

	}

	function do_html_row($sess)
	{
		global $ibforums;

		if ($sess['member_name'] and $sess['member_id'])
		{
			$sess['msg_icon'] = "<a href='{$this->base_url}act=Msg&amp;CODE=04&amp;MID={$sess['member_id']}'><{P_MSG}></a>";
		} else
		{
			if (!$sess['is_bot'])
			{
				$sess['member_name'] = $sess['prefix'] . $ibforums->lang['guest'] . $sess['suffix'] . " " . $sess['ip_address'];
				$sess['msg_icon']    = '&nbsp;';
			} else
			{
				$sess['member_name'] .= ' ' . $sess['ip_address'];
			}
		}

		return $this->html->show_row($sess);
	}

	function list_forum()
	{
	}

}
