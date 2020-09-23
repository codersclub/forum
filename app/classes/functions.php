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
  |   > Multi function library
  |   > Module written by Matt Mecham
  |   > Date started: 14th February 2002
  |
  |	> Module Version Number: 1.0.0
  +--------------------------------------------------------------------------
 */
use Views\View;

class functions
{

	var $time_formats = array();
	var $offset = "";
	var $offset_set = 0;
	var $num_format = "";
	var $allow_unicode = 0;
	var $get_magic_quotes = 0;

	//----------------------------------------
	// Set up some standards to save CPU later
	function __construct()
	{
		global $INFO;

		$this->num_format = ($INFO['number_format'] == 'space')
			? ' '
			: $INFO['number_format'];
	}

	function get_board_visibility($board_id, $is_forum)
	{
		global $sess;
		$ibforums = Ibf::app();

		if (!$sess->member['id'])
		{
			return 1;
		}

		$visible  = $visible
			? 1
			: 0;
		$is_forum = $is_forum
			? 1
			: 0;

		$query  = "SELECT is_visible
		    FROM ibf_boards_visibility
		    WHERE
		    	id={$board_id} AND
		    	is_forum={$is_forum} AND
	   	    	user_id={$sess->member['id']}";
		$stmt   = $ibforums->db->query($query);
		$result = $stmt->fetch();
		if (!$result)
		{
			$result = $is_forum
				? 0
				: 1;
			$query  = "INSERT INTO ibf_boards_visibility
			VALUES ({$board_id},{$is_forum},{$result},{$sess->member['id']})";
			$ibforums->db->exec($query);
		} else
		{
			$result = $result['is_visible'];
		}
		return $result;
	}

	function set_board_visibility($board_id, $is_forum, $visible)
	{
		global $sess;
		$ibforums = Ibf::app();
		$visible  = $visible
			? 1
			: 0;
		$is_forum = $is_forum
			? 1
			: 0;

		if (!$sess->member['id'])
		{
			return;
		}

		$vis = $this->get_board_visibility($board_id, $is_forum);
		if ($vis == $visible)
		{
			return;
		}

		$query = "UPDATE ibf_boards_visibility
		    SET is_visible={$visible}
		    WHERE
			id={$board_id} AND
			is_forum={$is_forum} AND
			user_id={$sess->member['id']}";
		$ibforums->db->exec($query);
	}

	//---------------------------------------------------------------
	// POST functions
	//---------------------------------------------------------------
	function get_mod_tags_regexp($gm_on = 0, $mm_on = 1)
	{
		$tags = 'ex|mod';
		if ($gm_on)
		{
			$tags .= '|gm';
		}
		if ($mm_on)
		{
			$tags .= '|mm';
		}
		return '#(?!\[CODE[^\]]*\])\[(' . $tags . ')\](.*?)\[/\1\](?!\[/CODE\])#i';
	}

	function mod_tag_exists($post, $gm_on = 0, $mm_on = 1)
	{
		$re = self::get_mod_tags_regexp($gm_on, $mm_on);
		return (bool)preg_match($re, $post);
	}

	function delayed_time($post, $days_off, $to_do = 0, $moderator = array(), $is_new_post = false)
	{
		global $ibforums;

		if (!$days_off and !$ibforums->vars['default_days_off'])
		{
			return 0;
		}

		$member_has_rights =
			!empty($moderator['delete_post'])
			|| !empty($ibforums->member['g_is_supmod'])
			|| !empty($ibforums->member['g_delay_delete_posts']);

		$to_do = $to_do || ($ibforums->input['offtop'] and $days_off and ($member_has_rights));

		if ($this->mod_tag_exists($post) && $is_new_post)
		{
			$to_do = $to_do || (trim(self::cut_mod_tags($post)) == '');
		}

		$result = ($to_do)
			? time() + ((!$days_off)
				? $ibforums->vars['default_days_off']
				: $days_off) * 60 * 60 * 24
			: 0;

		return $result;
	}

	function cut_mod_tags(&$post)
	{
		$re = self::get_mod_tags_regexp();
		return preg_replace($re, '', $post);
	}

	/**
	 * возвращает строке "уд. 5 дн."
	 *
	 */
	function get_autodelete_message($days, $delete_waiting_message, $delete_through_message)
	{
		$days = $days - time();
		if ($days > 0)
		{
			$days = $days / 86400;
			if ($days > 1)
			{
				$days = round($days);
				$days = sprintf($delete_through_message, $days);
			} else
			{
				$days = $delete_waiting_message;
			}
		} else
		{
			$days = $delete_waiting_message;
		}
		return $days;
	}

	function regex_moderator_message($matches)
	{
		global $ibforums;
		$message = $matches[1];

		if (!$message or !$ibforums->member['id'])
		{
			return "";
		}
		if (!($ibforums->member['is_mod'] or $ibforums->member['g_is_supmod']))
		{
			return "";
		}

		return "[MM]{$message}[/MM]";
	}

	function regex_global_moderator_message($matches)
	{
		global $ibforums;
		$message = $matches[1];

		if (!$ibforums->member['g_is_supmod'] or !$message)
		{
			return "";
		}

		return "[GM]{$message}[/GM]";
	}

	function do_post($post = "")
	{

		if (!$post)
		{
			return "";
		}

		$post = preg_replace_callback("#\[mm\](.+?)\[/mm\]#is", [$this, 'regex_moderator_message'], $post);
		$post = preg_replace_callback("#\[gm\](.+?)\[/gm\]#is", [$this, 'regex_global_moderator_message'], $post);

		return $post;
	}

	function premod_rights($mid, $queued = 0, &$resolve = 0)
	{
		global $ibforums;

		$resolve = 0;

		if (!$ibforums->member['g_is_supmod'])
		{
			if (!$ibforums->member['is_mod'] and
			    $mid != $ibforums->member['id']
			)
			{
				return FALSE;
			}

			if ($ibforums->member['is_mod'])
			{
				if ($queued)
				{
					$resolve = 1;
				} elseif ($mid != $ibforums->member['id'])
				{
					return FALSE;
				}
			}
		} else
		{
			$resolve = 1;
		}

		return TRUE;
	}

	function user_reply_flood($start_date = 0)
	{
		global $ibforums;

		if ($ibforums->member['g_days_ago'] and
		    $start_date and
		    $start_date < (time() - intval($ibforums->member['g_days_ago']) * 60 * 60 * 24)
		)
		{
			return TRUE;
		}

		return FALSE;
	}

	function user_ban_check($forum = array())
	{
		$ibforums = Ibf::app();

		if (!$forum['id'] or $ibforums->member['is_mod'])
		{
			return;
		}

		$stmt = $ibforums->db->prepare("SELECT temp_ban
		    FROM ibf_preview_user
		    WHERE
			mid=:mid
			and (fid=:fid or fid=:pfid)");
		$stmt->execute([
		               ':mid'  => $ibforums->member['id'],
		               ':fid'  => $forum['id'],
		               ':pfid' => $forum['parent_id'],
		               ]);

		if ($stmt->rowCount())
		{
			$ban = $stmt->fetch();
			if ($ban['temp_ban'])
			{
				if ($ban['temp_ban'] == 1)
				{
					$this->Error(array(
					                  'LEVEL' => 1,
					                  'MSG'   => 'no_view_forum_always'
					             ));
				} else
				{
					// Game over man! :))
					$ban_arr = $this->hdl_ban_line($ban['temp_ban']);

					if (time() >= $ban_arr['date_end'])
					{
						// You're free :((
						$stmt = $ibforums->db->prepare("UPDATE ibf_preview_user
						    SET temp_ban=NULL
						    WHERE
								mid=:mid
								and (fid=:fid or fid=:pfid)");
						$stmt->execute([
						               ':mid'  => $ibforums->member['id'],
						               ':fid'  => $forum['id'],
						               ':pfid' => $forum['parent_id'],
						               ]);

						// delete row of user if there is nothing remaining for him
						$ibforums->db->exec("DELETE
						    FROM ibf_preview_user
						    WHERE
							IsNULL(mod_posts) and
							IsNULL(restrict_posts) and
							IsNULL(temp_ban)");

						// Gi-gi-gi! Hui tebe! :)
					} else
					{
						$this->Error(array(
						                  'LEVEL' => 1,
						                  'MSG'   => 'no_view_forum',
						                  'EXTRA' => $this->get_date($ban_arr['date_end'])
						             ));
					}
				}
			}
		}
	}

	function ip_control($forum, $ip)
	{
		$ibforums = Ibf::app();

		if (!$forum['id'] or
		    !$ip or
		    $ibforums->member['g_avoid_q']
		)
		{
			return 0;
		}

		$ip = explode(".", $ip);

		if (!count($ip))
		{
			return 0;
		}

		$stmt = $ibforums->db->query("SELECT id
		    FROM ibf_ip_table
		    WHERE
			(ok1='" . $ip[0] . "' or ok1='*') and
			(ok2='" . $ip[1] . "' or ok2='*') and
			(ok3='" . $ip[2] . "' or ok3='*') and
			(ok4='" . $ip[3] . "' or ok4='*') and
			(fid='0' or fid='" . $forum['id'] . "' or
			 fid='" . $forum['parent_id'] . "')
		    LIMIT 1");

		return $stmt->rowCount();
	}

	/**
	 * Recount member reputation
	 * @todo move to the user model
	 * @param int $mid
	 * @return mixed
	 */
	function rep_recount($mid = 0)
	{
		if (!$mid)
		{
			return;
		}

		$ibforums = Ibf::app();

		//todo shrink to 1-2 queries
		$stmt = $ibforums->db->prepare("SELECT
			COUNT(r.msg_id) AS cnt
		    FROM
			ibf_reputation r,
			ibf_forums f
		    WHERE
			r.forum_id=f.id and
			f.inc_postcount=:counter and
			r.member_id=:mid AND
			r.code=:code");
		//first reputation
		$stmt->execute([
		               ':counter' => 1,
		               ':mid'     => $mid,
		               ':code'    => '01'
		               ]);
		$plus = $stmt->fetchColumn();
		$stmt->closeCursor();

		$stmt->execute([
		               ':counter' => 1,
		               ':mid'     => $mid,
		               ':code'    => '02',
		               ]);
		$minus = $stmt->fetchColumn();
		$stmt->closeCursor();

		//second reputation
		$stmt->execute([
		               ':counter' => 0,
		               ':mid'     => $mid,
		               ':code'    => '01'
		               ]);
		$plus2 = $stmt->fetchColumn();
		$stmt->closeCursor();

		$stmt->execute([
		               ':counter' => 0,
		               ':mid'     => $mid,
		               ':code'    => '02',
		               ]);
		$minus2 = $stmt->fetchColumn();
		$stmt->closeCursor();

		//update
		$stmt = $ibforums->db->prepare("UPDATE ibf_members
		    SET rep=:rep, ratting=:rat
		    WHERE id =:id");
		$stmt->execute([
		               ':rep' => $plus - $minus,
		               ':rat' => $plus2 - $minus2,
		               ':id'  => $mid,
		               ]);
	}

	/**
	 * Finds forum's parent
	 * @param int $id Order id
	 * @return array|int
	 */
	function select_parent($id)
	{
		return Ibf::app()->db->query("SELECT
			IF (parent_id = -1, 0, parent_id) as parent_id
		    FROM ibf_forums
		    WHERE id='" . $id . "'")->fetch();
	}

	/**
	 * Update forums order (?)
	 * @todo need normal description
	 * @param int $id
	 * @param int $pid
	 */
	function do_update($id, $pid)
	{
		$stmt = Ibf::app()->db->prepare("INSERT INTO ibf_forums_order
		    VALUES (?, ?)");
		$stmt->execute([$id, $pid]);
	}

	/**
	 * Update order cache
	 * @param int $id
	 * @param int $pid
	 * @return null
	 */
	function update_forum_order_cache($id, $pid)
	{
		//todo how many queries does it execute?
		if (!$id)
		{
			return;
		}

		$this->do_update($id, $pid);

		while ($row = $this->select_parent($pid))
		{
			$pid = $row['parent_id'];
			$this->do_update($id, $pid);
		}
	}

	function subforums_addtorow($result, $children, $id, $level)
	{

		if (isset($children[$id]) and count($children[$id]) > 0)
		{
			foreach ($children[$id] as $idx => $child)
			{
				if (!isset($result[$child['id']]))
				{
					$prefix = "";

					// visuality depth
					for ($i = 0; $i < $level; $i++)
					{
						$prefix .= "---";
					}

					$child['name'] = $prefix . " " . $child['name'];

					$result[$child['id']] = $child;

					$result = $this->subforums_addtorow($result, $children, $child['id'], $level + 1);
				}
			}
		}

		return $result;
	}

	function fill_array($row, &$forums, &$children, &$total_list)
	{

		if ($row['parent_id'] > 0)
		{
			$children[$row['parent_id']][$row['id']] = $row;
		} else
		{
			$forums[$row['id']] = $row;
		}

		$total_list[$row['id']] = $row['name'];
	}

	function check_forum($row = array())
	{
		global $ibforums;

		// check rights
		if ($this->check_perms($row['read_perms']) != TRUE)
		{
			return FALSE;
		}

		// check "passworded" forums
		if ($row['password'] and $_COOKIE[$ibforums->vars['cookie_id'] . "iBForum" . $row['id']] != $row['password'])
		{
			return FALSE;
		}

		return TRUE;
	}

	function forums_array($id, $current, &$forums, &$children, &$forums_list = array())
	{
		$ibforums = Ibf::app();

		$result = array();

		if ($id)
		{
			// querying upper forum
			if ($id != $current['id'])
			{
				//todo move to the forums model
				$main = $ibforums->db->query("SELECT *
				    FROM ibf_forums
				    WHERE id='" . $id . "'")->fetch();
			} else
			{
				$main = $current;
			}

			// have we access to it ?
			if ($main and $this->check_forum($main))
			{
				// querying parent forums
				$stmt = $ibforums->db->query("SELECT f.*
				    FROM
					ibf_forums f,
					ibf_forums_order fo
				    WHERE
					fo.pid='" . $id . "' and
					f.id=fo.id
				    ORDER BY f.position");

				if ($stmt->rowCount() > 0)
				{
					// at first include upper forum
					$this->fill_array($main, $forums, $children, $forums_list);

					// collect parent forums
					while ($row = $stmt->fetch())
					{
						if ($this->check_forum($row))
						{
							// check rights
							$this->fill_array($row, $forums, $children, $forums_list);
						}
					}
				}

				// combine data to one array
				foreach ($forums as $row)
				{
					if (!isset($result[$row['id']]))
					{
						$result[$row['id']] = $row;

						$result = $this->subforums_addtorow($result, $children, $row['id'], $main['sub_can_post']);
					}
				}
			}
		}

		return $result;
	}

	function menu_row($value, $current, $label, $mode = "")
	{

		if ($value == $current and ($mode == "" or $mode == 0))
		{
			$selected = " selected='selected'";
		}

		return "<option value='$value'$selected>{$label}</option>\n";
	}

	function forum_filter($forum = array(), $forums_id = array(), $mode = 0, $pid)
	{
		global $ibforums;

		$k = count($forums_id);

		if ($k > 1 or ($k == 1 and $forums_id[0]['parent_id'] != -1))
		{
			$forums = "<form name='forummenu' method='get'>\n";

			$forums .= "<select name='f' class='forminput'>\n";

			$forums .= $this->menu_row(-1, $mode, $ibforums->lang['see_forums']);

			$forums .= $this->menu_row(-2, $mode, $ibforums->lang['all_forums']);

			foreach ($forums_id as $row)
			{
				if ($row['parent_id'] == -1 and !$row['sub_can_post'])
				{
					continue;
				}

				$rtime = $row['last_post'];

				$ftime = ($ibforums->member['board_read'] > $ibforums->forum_read[$row['id']])
					? $ibforums->member['board_read']
					: $ibforums->forum_read[$row['id']];

				$new = ($ftime < $rtime)
					? $ibforums->lang['is_new']
					: "&nbsp;&nbsp;&nbsp;";

				$forums .= $this->menu_row($row['id'], $forum['id'], $new . $row['name'], $mode);
			}

			$forums .= "</select>&nbsp;<input type='button' value='{$ibforums->lang['jmp_go']}' class='forminput' onClick='do_url({$pid});'></form>";

			$forums = View::make("global.forum_filter", ['data' => $forums]);

			return $forums;
		}

		return "";
	}

	function get_highlight_id($id)
	{
		$ibforums = Ibf::app();

		$stmt = $ibforums->db->query("SELECT forum_highlight,highlight_fid,parent_id FROM ibf_forums WHERE id='" . $id . "'");

		if (!$row = $stmt->fetch() or !$row['forum_highlight'])
		{
			return -1;
		}

		if ($row['highlight_fid'] == -1 and $row['parent_id'] != -1)
		{
			$stmt = $ibforums->db->query("SELECT forum_highlight,highlight_fid FROM ibf_forums WHERE id='" . $row['parent_id'] . "'");

			if (!$row = $stmt->fetch() or !$row['forum_highlight'] or !$row['highlight_fid'] or $row['highlight_fid'] == -1)
			{
				return -1;
			}
		}

		return $row['highlight_fid'];
	}

	function current_day()
	{

		$arr = getdate(time());
		return $arr['mday'];
	}

	function current_month()
	{

		$arr = getdate(time());
		return $arr['mon'];
	}

	function yesterday_day($days_down = 1)
	{

		$arr = getdate(time() - 3600 * 24 * $days_down);
		return $arr['mday'];
	}

	function yesterday_month($days_down = 1)
	{

		$arr = getdate(time() - 3600 * 24 * $days_down);
		return $arr['mon'];
	}

	/**
	 * @todo What is it and why is it recursive?
	 * @param string $field
	 * @param int $day
	 * @param int $month
	 * @return null
	 */
	function inc_user_count($field, $day, $month)
	{
		$ibforums = Ibf::app();

		if (!$field or !$day or !$month)
		{
			return;
		}

		$rows = $ibforums->db->exec("UPDATE ibf_users_stat
		    SET " . $field . "=" . $field . "+1
		    WHERE
			day='" . $day . "' and
			month='" . $month . "'");

		// if no changed
		if (!$rows)
		{
			// add new day
			$ibforums->db->exec("INSERT
			    INTO ibf_users_stat
				(day,month)
			    VALUES (" . $day . "," . $month . ")");

			// and try again
			$this->inc_user_count($field, $day, $month);
		}
	}

	function who_was_member($mid)
	{
		$ibforums = Ibf::app();

		if (!$mid)
		{
			return;
		}

		// current day
		$cur_day = $this->current_day();

		// current month
		$cur_mon = $this->current_month();
		$stmt = $ibforums->db->prepare('SELECT count(*) FROM ibf_m_visitors WHERE mid=? AND day=? AND month=?');
		$stmt->execute([$mid, $cur_day, $cur_mon]);
		if ('0' == $stmt->fetchColumn())
		{
			$ibforums->db->prepare("INSERT INTO ibf_m_visitors VALUES (?, ?, ?)")
				->execute([$mid, $cur_day, $cur_mon]);
		}else{
			$this->inc_user_count('members', $cur_day, $cur_mon);
		}
	}

	function who_was_guest_or_bot($table, $field, $session_id, $ip_address)
	{
		$ibforums = Ibf::app();

		// current day
		$cur_day = $this->current_day();

		// current month
		$cur_mon = $this->current_month();

		try
		{
			//$ibforums->db->exec("INSERT
			//	INTO " . $table . "
			//	VALUES ('" . $session_id . "','" . $ip_address . "'," . $cur_day . "," . $cur_mon . ")");
			$this->inc_user_count($field, $cur_day, $cur_mon);
		} catch (PDOException $e)
		{
			//finally
		}
	}

	/**
	 *
	 * @param int $hid
	 * @return string
	 */
	function code_tag_button($hid = 0)
	{
		$ibforums = Ibf::app();

		//todo move to the skin
		$syntax_html = "<IBF_SONG_BUTTON>";
		$syntax_html .= "<select name='syntax' class='codebuttons' onchange=\"alterfont(this.options[this.selectedIndex].value, 'CODE')\">";
		$syntax_html .= "<option value='-1'>CODE</option>";

		$stmt = $ibforums->db->query("SELECT
			id,
			syntax,
			syntax_description
		    FROM ibf_syntax_list");
		if (!$stmt->rowCount())
		{
			return "";
		}

		$code = "";

		while ($syntax = $stmt->fetch())
		{
			if ($hid == $syntax['id'])
			{
				$code = $syntax['syntax'];
			}

			$syntax_html .= "<option value='{$syntax['syntax']}'>{$syntax['syntax_description']}</option>";
		}

		$syntax_html .= "<option value='no'>Без подсветки</option>";

		$syntax_html .= "</select>";

		if ($code)
		{
			$code = "<input type='button' value='CODE={$code}' onclick=\"alterfont('{$code}','CODE')\" class='codebuttons'> ";
		}

		$syntax_html = str_replace("<IBF_SONG_BUTTON>", $code, $syntax_html);

		return $syntax_html;
	}

	/**
	 * Delete members
	 * @param string $ids where part of the query
	 */
	function delete_members($ids)
	{
		$ibforums = Ibf::app();

		$ibforums->db->beginTransaction();

		$ibforums->db->exec("UPDATE ibf_posts
			    SET author_id='0'
			    WHERE author_id" . $ids);

		$ibforums->db->exec("UPDATE ibf_topics
			    SET starter_id='0'
			    WHERE starter_id" . $ids);

		$ibforums->db->exec("DELETE
			    FROM ibf_members
			    WHERE id" . $ids);

		$ibforums->db->exec("DELETE FROM ibf_reputation
			    WHERE member_id" . $ids);

		$ibforums->db->exec("DELETE FROM ibf_pfields_content
			    WHERE member_id" . $ids);

		$ibforums->db->exec("DELETE FROM ibf_member_extra
			    WHERE id" . $ids);

		$ibforums->db->exec("DELETE FROM ibf_messages
			    WHERE member_id" . $ids);

		$ibforums->db->exec("DELETE FROM ibf_contacts
			    WHERE member_id" . $ids);

		$ibforums->db->exec("DELETE FROM ibf_contacts
			    WHERE contact_id" . $ids);

		$ibforums->db->exec("DELETE FROM ibf_tracker
			    WHERE member_id" . $ids);

		$ibforums->db->exec("DELETE FROM ibf_forum_tracker
			    WHERE member_id" . $ids);

		$ibforums->db->exec("DELETE FROM ibf_warn_logs
			    WHERE wlog_mid" . $ids);

		$ibforums->db->exec("DELETE FROM ibf_validating
			    WHERE member_id" . $ids);

		$ibforums->db->exec("DELETE FROM ibf_boards_visibility
			    WHERE user_id" . $ids);

		$ibforums->db->exec("DELETE FROM ibf_log_forums
			    WHERE mid" . $ids);

		$ibforums->db->exec("DELETE FROM ibf_log_topics
			    WHERE mid" . $ids);

		$ibforums->db->exec("DELETE FROM ibf_ip_table
			    WHERE mid" . $ids);

		$ibforums->db->exec("DELETE FROM ibf_moderators
			    WHERE member_id" . $ids);

		$ibforums->db->exec("DELETE FROM ibf_preview_user
			    WHERE mid" . $ids);

		$ibforums->db->exec("DELETE FROM ibf_syntax_access
			    WHERE member_id" . $ids);

		$ibforums->db->exec("DELETE FROM ibf_warnings
			    WHERE mid" . $ids);

		$ibforums->db->exec("DELETE FROM ibf_voters
			    WHERE member_id" . $ids);

		$ibforums->db->exec("DELETE FROM ibf_search_forums
			    WHERE mid" . $ids);

		$ibforums->db->exec("DELETE FROM ibf_search_results
			    WHERE member_id" . $ids);

		// Set the stats DB straight.
		$memb = $ibforums->db->query("SELECT
				id,
				name
			    FROM ibf_members
			    WHERE mgroup<>'" . $ibforums->vars['auth_group'] . "'
			    ORDER BY joined DESC
			    LIMIT 1")->fetch();

		$r = $ibforums->db->query("SELECT COUNT(id) as members
			    FROM ibf_members
			    WHERE mgroup<>'" . $ibforums->vars['auth_group'] . "'")->fetch();

		// Remove "guest" account...
		$r['members']--;
		$r['members'] = $r['members'] < 1
			? 0
			: $r['members'];

		$ibforums->db->exec("UPDATE ibf_stats SET
				MEM_COUNT='" . $r['members'] . "',
				LAST_MEM_NAME='" . $memb['name'] . "',
				LAST_MEM_ID='" . $memb['id'] . "'");
		$ibforums->db->commit();
	}

	/**
	 * Sends Private message and mail notify
	 * @param int $sendto Recipient member id.
	 * @param string $message Message text
	 * @param string $title Message title
	 * @param int $sender_id Sender's member id. Default is current
	 * @param int $popup Force to show popup notification for receiver
	 * @param int $do_send Force to send email copy of the PM
	 * @param int $fatal If not set. mute the database errors. todo Why is it needed here?
	 * @return int Message id.
	 */
	function sendpm($sendto, $message, $title, $sender_id = 0, $popup = 0, $do_send = 0, $fatal = 1)
	{

		settype($sendto, 'int');
		settype($sender_id, 'int');

		if (!$sender_id)
		{
			$sender_id = Ibf::app()->member['id'];
		}

		if (!$sendto || !$sender_id)
		{
			return 0;
		}

		if (!$fatal)
		{
			$old_err = Ibf::app()->db->getAttribute(PDO::ATTR_ERRMODE);
			Ibf::app()->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
		}
		$data = [
			'member_id'    => $sendto,
			'msg_date'     => time(),
			'read_state'   => '0',
			'title'        => addslashes($this->clean_value($title)),
			'message'      => addslashes($this->remove_tags($this->clean_value($message))),
			'from_id'      => $sender_id,
			'vid'          => 'in',
			'recipient_id' => $sendto,
			'tracking'     => 0,
		];

		Ibf::app()->db->insertRow('ibf_messages', $data);
		$message_id = Ibf::app()->db->lastInsertId();
		unset($data);

		$extra = '';
		if ($popup)
		{
			$extra = ",show_popup=1";
		}

		Ibf::app()->db->prepare(
			"UPDATE ibf_members
			SET msg_total = msg_total + 1, new_msg = new_msg + 1, msg_from_id = ?, msg_msg_id = ?
			{$extra}
			WHERE id = ? LIMIT 1"
		)
			->execute([$sender_id, $message_id, $sendto]);

		$stmt = Ibf::app()->db->prepare(
			"SELECT name, email_pm, language, email, disable_mail, mgroup FROM ibf_members WHERE id=?"
		)
			->execute([$sendto]);
		if (( FALSE !== ($to_member = $stmt->fetch()) )
			 && $to_member['mgroup'] != Ibf::app()->vars['auth_group']
			 && !$to_member['disable_mail']
			 && ($to_member['email_pm'] || $do_send)
		)
		{
			require_once Ibf::app()->vars['base_dir'] . "sources/lib/emailer.php";
			$email = new emailer();

			$to_member['language'] = $to_member['language'] == ""
				? 'en'
				: $to_member['language'];

			$email->get_template("pm_notify", $to_member['language']);

			if ($sender_id != Ibf::app()->member['id'])
			{
				$stmt = Ibf::app()->db->prepare("SELECT name FROM ibf_members WHERE id=?")
					->execute([$sender_id]);

				if (FALSE != $member = $stmt->fetch())
				{
					$name = $member['name'];
				} else
				{
					return $message_id;
				}
			} else
			{
				$name = Ibf::app()->member['name'];
			}

			$email->build_message(
				[
					'NAME'     => $to_member['name'],
					'POSTER'   => $name,
					'TITLE'    => $title,
					'LINK'     => "?act=Msg&amp;CODE=03&amp;VID=in&amp;MSID=$message_id",
					'MSG_BODY' => $this->remove_tags($message)
				]
			);

			$email->build_subject(
				[
					'TEMPLATE' => "pm_email_subject",
					'POSTER'   => $name,
				]
			);

			$email->to = $to_member['email'];
			$email->send_mail();
			if (!$fatal)
			{
				Ibf::app()->db->setAttribute(PDO::ATTR_ERRMODE, $old_err);
			}
		}

		return $message_id;
	}

	function str_to_sql($str)
	{
		$str = preg_replace("/\\\/", "\\\\\\", $str);
		$str = preg_replace("/'/", "\\'", $str);
		return $str;
	}

	function sql_to_html($str)
	{
		$str = preg_replace("/\"/", "\"", $str);
		$str = preg_replace("/'/", "&#39;", $str);
		return $str;
	}

	/* ------------------------------------------------------------------------- */
	// txt_stripslashes
	// ------------------
	// Make Big5 safe - only strip if not already...
	/* ------------------------------------------------------------------------- */

	function txt_stripslashes($t)
	{
		if ($this->get_magic_quotes)
		{
			$t = stripslashes($t);
		}

		return $t;
	}

	/* ------------------------------------------------------------------------- */

	// txt_raw2form
	// ------------------
	// makes _POST text safe for text areas
	/* ------------------------------------------------------------------------- */

	function txt_raw2form($t = "")
	{
		$t = str_replace('$', "&#036;", $t);

		$t = preg_replace("/\\\(?!&amp;#|\?#)/", "&#092;", $t);

		return $t;
	}

	/* ------------------------------------------------------------------------- */

	// Safe Slashes - ensures slashes are saved correctly
	// ------------------
	//
	/* ------------------------------------------------------------------------- */

	function txt_safeslashes($t = "")
	{
		return str_replace('\\', "\\\\", $this->txt_stripslashes($t));
	}

	/* ------------------------------------------------------------------------- */

	// txt_htmlspecialchars
	// ------------------
	// Custom version of htmlspecialchars to take into account mb chars
	/* ------------------------------------------------------------------------- */

	function txt_htmlspecialchars($t = "")
	{
		// Use forward look up to only convert & not &#123;
		$t = preg_replace("/&(?!#[0-9]+;)/s", '&amp;', $t);
		$t = str_replace("<", "&lt;", $t);
		$t = str_replace(">", "&gt;", $t);
		$t = str_replace('"', "&quot;", $t);

		return $t; // A nice cup of?
	}

	/* ------------------------------------------------------------------------- */

	// txt_UNhtmlspecialchars
	// ------------------
	// Undoes what the above function does. Yes.
	/* ------------------------------------------------------------------------- */

	function txt_UNhtmlspecialchars($t = "")
	{
		$t = str_replace("&amp;", "&", $t);
		$t = str_replace("&lt;", "<", $t);
		$t = str_replace("&gt;", ">", $t);
		$t = str_replace("&quot;", '"', $t);

		return $t;
	}

	/* ------------------------------------------------------------------------- */

	// return_md5_check
	// ------------------
	// md5 hash for server side validation of form / link stuff
	/* ------------------------------------------------------------------------- */

	function return_md5_check()
	{
		global $ibforums;

		if ($ibforums->member['id'])
		{
			return md5($ibforums->member['email'] . '&' . $ibforums->member['password'] . '&' . $ibforums->member['joined']);
		} else
		{
			return md5("this is only here to prevent it breaking on guests");
		}
	}

	/* ------------------------------------------------------------------------- */

	// C.O.C.S (clean old comma-delimeted strings)
	// ------------------
	// <>
	/* ------------------------------------------------------------------------- */

	function trim_leading_comma($t)
	{
		return preg_replace("/^,/", "", $t);
	}

	function trim_trailing_comma($t)
	{
		return preg_replace("/,$/", "", $t);
	}

	function clean_comma($t)
	{
		return preg_replace("/,{2,}/", ",", $t);
	}

	function clean_perm_string($t)
	{
		$t = $this->clean_comma($t);
		$t = $this->trim_leading_comma($t);
		$t = $this->trim_trailing_comma($t);

		return $t;
	}

	/* ------------------------------------------------------------------------- */

	// size_format
	// ------------------
	// Give it a byte to eat and it'll return nice stuff!
	/* ------------------------------------------------------------------------- */

	function size_format($bytes = "")
	{
		global $ibforums;

		$retval = "";

		if ($bytes >= 1048576)
		{
			$retval = round($bytes / 1048576 * 100) / 100 . $ibforums->lang['sf_mb'];
		} else {
			if ($bytes >= 1024)
			{
				$retval = round($bytes / 1024 * 100) / 100 . $ibforums->lang['sf_k'];
			} else
			{
				$retval = $bytes . $ibforums->lang['sf_bytes'];
			}
		}

		return $retval;
	}

	/* ------------------------------------------------------------------------- */

	// print_forum_rules
	// ------------------
	// Checks and prints forum rules (if required)
	/* ------------------------------------------------------------------------- */

	function print_forum_rules($forum)
	{
		global $ibforums;

		$ruleshtml = "";

		if ($forum['show_rules'])
		{
			if ($forum['rules_title'])
			{
				$rules['title'] = $forum['rules_title'];
				$rules['body']  = $forum['rules_text'];

				if ($forum['red_border'] and $forum['show_rules'] == 2)
				{
					$rules['body'] = "<div class='rules-border'>" . $rules['body'] . '</div>';
				}

				$rules['fid'] = $forum['id'];
				$ruleshtml    = $forum['show_rules'] == 2
					? View::make("global.forum_show_rules_full", ['rules' => $rules])
					: View::make("global.forum_show_rules_link", ['rules' => $rules]);
			}
		}

		return $ruleshtml;
	}

	/* ------------------------------------------------------------------------- */

	//
	// hdl_ban_line() : Get / set ban info
	// Returns array on get and string on "set"
	//
	/* ------------------------------------------------------------------------- */

	function hdl_ban_line($bline)
	{
		global $ibforums;

		if (is_array($bline))
		{
			// Set ( 'timespan' 'unit' )

			$factor = $bline['unit'] == 'd'
				? 86400
				: 3600;

			$date_end = time() + ($bline['timespan'] * $factor);

			return time() . ':' . $date_end . ':' . $bline['timespan'] . ':' . $bline['unit'];
		} else
		{
			$arr = array();

			list($arr['date_start'], $arr['date_end'], $arr['timespan'], $arr['unit']) = explode(":", $bline);

			return $arr;
		}
	}

	/* ------------------------------------------------------------------------- */

	//
	// check_perms() : Nice little sub to check perms
	// Returns TRUE if access is allowed, FALSE if not.
	//
	/* ------------------------------------------------------------------------- */

	function check_perms($forum_perm = "")
	{
		global $ibforums;

		if ($forum_perm == "")
		{
			return FALSE;
		} else {
			if ($forum_perm == '*')
			{
				return TRUE;
			} else
			{
				// Make permission array for this forum
				$forum_perm_array = explode(",", $forum_perm);

				foreach ($ibforums->perm_id_array as $u_id)
				{
					if (in_array($u_id, $forum_perm_array))
					{
						return TRUE;
					}
				}

				// Still here? Not a match then.

				return FALSE;
			}
		}
	}

	/* ------------------------------------------------------------------------- */

	//
	// do_number_format() : Nice little sub to handle common stuff
	//
	/* ------------------------------------------------------------------------- */

	function do_number_format($number)
	{
		global $ibforums;

		if ($ibforums->vars['number_format'] != 'none')
		{
			return number_format($number, 0, '', $this->num_format);
		} else
		{
			return $number;
		}
	}

	/* ------------------------------------------------------------------------- */

	//
	// Return scaled down image
	//
	/* ------------------------------------------------------------------------- */

	function scale_image($arg)
	{
		// max_width, max_height, cur_width, cur_height

		$ret = array(
			'img_width'  => $arg['cur_width'],
			'img_height' => $arg['cur_height']
		);

		if ($arg['cur_width'] > $arg['max_width'])
		{
			$ret['img_width']  = $arg['max_width'];
			$ret['img_height'] = ceil(($arg['cur_height'] * (($arg['max_width'] * 100) / $arg['cur_width'])) / 100);
			$arg['cur_height'] = $ret['img_height'];
			$arg['cur_width']  = $ret['img_width'];
		}

		if ($arg['cur_height'] > $arg['max_height'])
		{
			$ret['img_height'] = $arg['max_height'];
			$ret['img_width']  = ceil(($arg['cur_width'] * (($arg['max_height'] * 100) / $arg['cur_height'])) / 100);
		}

		return $ret;
	}

	/* ------------------------------------------------------------------------- */

	//
	// Show NORMAL created security image(s)...
	//
	/* ------------------------------------------------------------------------- */

	function show_gif_img($this_number = "")
	{
		$numbers = array(
			0 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIUDH5hiKsOnmqSPjtT1ZdnnjCUqBQAOw==',
			1 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIUjAEWyMqoXIprRkjxtZJWrz3iCBQAOw==',
			2 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIUDH5hiKubnpPzRQvoVbvyrDHiWAAAOw==',
			3 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIVDH5hiKbaHgRyUZtmlPtlfnnMiGUFADs=',
			4 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIVjAN5mLDtjFJMRjpj1Rv6v1SHN0IFADs=',
			5 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIUhA+Bpxn/DITL1SRjnps63l1M9RQAOw==',
			6 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIVjIEYyWwH3lNyrQTbnVh2Tl3N5wQFADs=',
			7 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIUhI9pwbztAAwP1napnFnzbYEYWAAAOw==',
			8 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIVDH5hiKubHgSPWXoxVUxC33FZZCkFADs=',
			9 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIVDA6hyJabnnISnsnybXdS73hcZlUFADs=',
		);

		//flush();
		header("Content-type: image/gif");
		echo base64_decode($numbers[$this_number]);
		exit();
	}

	/* ------------------------------------------------------------------------- */

	//
	// Show GD created security image...
	//
	/* ------------------------------------------------------------------------- */

	function show_gd_img($content = "")
	{
		$ibforums = Ibf::app();

		//flush();

		@header("Content-Type: image/jpeg");

		if ($ibforums->vars['use_ttf'] != 1)
		{
			$font_style = 5;
			$no_chars   = mb_strlen($content);

			$charheight = ImageFontHeight($font_style);
			$charwidth  = ImageFontWidth($font_style);
			$strwidth   = $charwidth * intval($no_chars);
			$strheight  = $charheight;

			$imgwidth  = $strwidth + 15;
			$imgheight = $strheight + 15;
			$img_c_x   = $imgwidth / 2;
			$img_c_y   = $imgheight / 2;

			$im       = ImageCreate($imgwidth, $imgheight);
			$text_col = ImageColorAllocate($im, 0, 0, 0);
			$back_col = ImageColorAllocate($im, 200, 200, 200);

			ImageFilledRectangle($im, 0, 0, $imgwidth, $imgheight, $text_col);
			ImageFilledRectangle($im, 3, 3, $imgwidth - 4, $imgheight - 4, $back_col);

			$draw_pos_x = $img_c_x - ($strwidth / 2) + 1;
			$draw_pos_y = $img_c_y - ($strheight / 2) + 1;

			ImageString($im, $font_style, $draw_pos_x, $draw_pos_y, $content, $text_col);
		} else
		{
			$image_x = isset($ibforums->vars['gd_width'])
				? $ibforums->vars['gd_width']
				: 250;
			$image_y = isset($ibforums->vars['gd_height'])
				? $ibforums->vars['gd_height']
				: 70;

			$im = imagecreate($image_x, $image_y);

			$white = ImageColorAllocate($im, 255, 255, 255);
			$black = ImageColorAllocate($im, 0, 0, 0);
			$grey  = ImageColorAllocate($im, 200, 200, 200);

			$no_x_lines = ($image_x - 1) / 5;

			for ($i = 0; $i <= $no_x_lines; $i++)
			{
				// X lines

				ImageLine($im, $i * $no_x_lines, 0, $i * $no_x_lines, $image_y, $grey);

				// Diag lines

				ImageLine($im, $i * $no_x_lines, 0, ($i * $no_x_lines) + $no_x_lines, $image_y, $grey);
			}

			$no_y_lines = ($image_y - 1) / 5;

			for ($i = 0; $i <= $no_y_lines; $i++)
			{
				ImageLine($im, 0, $i * $no_y_lines, $image_x, $i * $no_y_lines, $grey);
			}

			$font = isset($ibforums->vars['gd_font'])
				? $ibforums->vars['gd_font']
				: getcwd() . '/fonts/progbot.ttf';

			$text_bbox = ImageTTFBBox(20, 0, $font, $content);

			$sx = ($image_x - ($text_bbox[2] - $text_bbox[0])) / 2;
			$sy = ($image_y - ($text_bbox[1] - $text_bbox[7])) / 2;
			$sy -= $text_bbox[7];

			imageTTFtext($im, 20, 0, $sx, $sy, $black, $font, $content);
		}

		ImageJPEG($im);
		ImageDestroy($im);

		exit();
	}

	/* ------------------------------------------------------------------------- */

	//
	// Convert newlines to <br> nl2br is buggy with <br> on early PHP builds
	//
	/* ------------------------------------------------------------------------- */

	function my_nl2br($t = "")
	{
		return str_replace("\n", "<br>", $t);
	}

	/* ------------------------------------------------------------------------- */

	//
	// Convert <br> to newlines
	//
	/* ------------------------------------------------------------------------- */

	function my_br2nl($t = "")
	{
		$t = str_replace("<br>", "\n", $t);
		$t = str_replace("<br>", "\n", $t);

		return $t;
	}

	//
	// Creates a profile link if member is a reg. member, else just show name
	//
	/* ------------------------------------------------------------------------- */

	function make_profile_link($name, $id = "", $info = "")
	{
		global $ibforums;

		if ($id > 0)
		{
			return "<a href='{$ibforums->base_url}showuser=$id'>$name</a>{$info}";
		} else
		{
			return $name;
		}
	}

	/* ------------------------------------------------------------------------- */

	//
	// Redirect using HTTP commands, not a page meta tag.
	//
	/* ------------------------------------------------------------------------- */

	function boink_it($url, $type = "")
	{
		global $ibforums;

		// Ensure &amp;s are taken care of
		if (!$type)
		{
			$type = $ibforums->vars['header_redirect'];
		}

		$url = str_replace("&amp;", "&", $url);

		if ($type == 'refresh')
		{
			@header("Refresh: 0;url=" . $url);
		} elseif ($type == 'html')
		{
			@flush();
			//			echo("<html><head><meta http-equiv='refresh' content='0; url=$url'></head><body></body></html>");
			echo("<html><head><meta http-equiv='refresh' content='0; url=" . htmlspecialchars($url) . "'></head><body></body></html>");
			exit();
		} else
		{
			@header("Location: " . $url);
		}

		exit();
	}

	/* ------------------------------------------------------------------------- */

	//
	// Create a random 8 character password
	//
	/* ------------------------------------------------------------------------- */

	function make_password()
	{
		$pass  = "";
		$chars = array(
			"1",
			"2",
			"3",
			"4",
			"5",
			"6",
			"7",
			"8",
			"9",
			"0",
			"a",
			"A",
			"b",
			"B",
			"c",
			"C",
			"d",
			"D",
			"e",
			"E",
			"f",
			"F",
			"g",
			"G",
			"h",
			"H",
			"i",
			"I",
			"j",
			"J",
			"k",
			"K",
			"l",
			"L",
			"m",
			"M",
			"n",
			"N",
			"o",
			"O",
			"p",
			"P",
			"q",
			"Q",
			"r",
			"R",
			"s",
			"S",
			"t",
			"T",
			"u",
			"U",
			"v",
			"V",
			"w",
			"W",
			"x",
			"X",
			"y",
			"Y",
			"z",
			"Z"
		);

		$count = count($chars) - 1;

		srand((double)microtime() * 1000000);

		for ($i = 0; $i < 8; $i++)
		{
			$pass .= $chars[rand(0, $count)];
		}

		return ($pass);
	}

	/* ------------------------------------------------------------------------- */

	//
	// Generate the appropriate folder icon for a forum
	//
	/* ------------------------------------------------------------------------- */

	function forum_new_posts($forum_data, $sub = 0, $state = 0, $moder_rights = "")
	{
		global $ibforums, $std;

		$rtime = $forum_data['last_post'];

		$fid = $forum_data['fid'] == ""
			? $forum_data['id']
			: $forum_data['fid'];

		if ($ibforums->member['id'] && $forum_data['topics'])
		{
			$ftime = ($ibforums->member['board_read'] > $ibforums->forum_read[$fid])
				? $ibforums->member['board_read']
				: $ibforums->forum_read[$fid];
		} else
		{
			$ftime = $rtime;
		}

		if ($state)
		{
			$ftime = 0;
		}

		if (!$sub)
		{
			if (!$forum_data['status'])
			{
				return "<{C_LOCKED}>";
			}
			$sub_cat_img = '';
		} else
		{
			$sub_cat_img = '_CAT';
		}

		if ($ibforums->member['id'])
		{

			$mid = $ibforums->member['id'];

			/*
			  if ( $ibforums->member['id'] == 11454 ) //LuckLess
			  {
			  echo "\$mid={$ibforums->member['id']}<br>\n";
			  echo "\$fid=$fid<br>\n";
			  echo "\$forum_data['has_mod_posts']={$forum_data['has_mod_posts']}<br>\n";
			  echo "\$ibforums->member['g_is_supmod']={$ibforums->member['g_is_supmod']}<br>\n";
			  echo "\$moder_rights={$moder_rights}<br>\n";
			  echo "\$moder_rights[\$fid]={$moder_rights[$fid]}<br>\n";

			  if(is_array($moder_rights[$fid]))
			  {
			  echo "\$moder_rights[\$fid][\$mid]={$moder_rights[$fid][$mid]}<br>\n";
			  if(is_array($moder_rights[$fid][$mid]))
			  {
			  echo "\$moder_rights[ \$fid ][ \$mid]['topic_q']={$moder_rights[ $fid ][ $mid ]['topic_q']}<br>\n";
			  echo "\$moder_rights[ \$fid ][ \$mid]['post_q']={$moder_rights[ $fid ][ $mid ]['post_q']}<br>\n";
			  }
			  }
			  //echo "\$moder_rights[ \$fid ][ \$mid]={$moder_rights[ $fid ][ $mid ]}<br>\n";
			  //echo "\<br>\n";
			  echo "-----------------<br>\n";
			  }
			 */
			if ($forum_data['has_mod_posts'])
			{
				$rights_topicqueue = 0;
				$rights_postqueue  = 0;
				if (is_array($moder_rights[$fid]))
				{
					$rights_topicqueue = $moder_rights[$fid][$mid]['topic_q'];
					$rights_postqueue  = $moder_rights[$fid][$mid]['post_q'];
					if ($ibforums->member['g_is_supmod'] or
					    ($moder_rights[$fid][$mid] and
					     ($rights_topicqueue or $rights_postqueue))
					)
					{
						return "<{C_LOCKED" . $sub_cat_img . "}>";
					}
				}
			}
		}

		if ($forum_data['password'] and $sub == 0)
		{
			return $ftime < $rtime
				? "<{C_ON_RES}>"
				: "<{C_OFF_RES}>";
		}

		return $ftime < $rtime
			? "<{C_ON" . $sub_cat_img . "}>"
			: "<{C_OFF" . $sub_cat_img . "}>";
	}

	/* ------------------------------------------------------------------------- */

	//
	// Generate the appropriate folder icon for a topic
	//
	/* ------------------------------------------------------------------------- */

	function folder_icon($topic, $dot = "", $last_time = -1, $mark = -1)
	{
		global $ibforums, $std;

		if (!$ibforums->member['id'])
		{
			$last_time = '';
		} else
		{
			if ($mark > $last_time)
			{
				$last_time = $mark
					? $mark
					: -1;
			} elseif (!$last_time)
			{
				$last_time = -1;
			}
		}

		if ($dot)
		{
			$dot = "_DOT";
		} else
		{
			$dot = "";
		}

		if ($topic['state'] == 'closed')
		{
			return "<{B_LOCKED}>";
		}

		if ($topic['poll_state'])
		{
			if (!$ibforums->member['id'])
			{
				return "<{B_POLL_NN" . $dot . "}>";
			}

			if ($topic['last_post'] > $topic['last_vote'])
			{
				$topic['last_vote'] = $topic['last_post'];
			}

			if ($topic['last_vote'] > $last_time)
			{
				return "<{B_POLL" . $dot . "}>";
			} else
			{
				return "<{B_POLL_NN" . $dot . "}>";
			}
		}

		if ($topic['state'] == 'moved' or $topic['state'] == 'link')
		{
			return "<{B_MOVED}>";
		} elseif ($topic['state'] == 'mirror')
		{
			if ($ibforums->member['id'] && ($topic['last_post'] > $last_time))
			{
				return "<{B_MIRRORED}>";
			} else
			{
				return "<{B_MIRRORED_NO}>";
			}
		}

		if (!$ibforums->member['id'])
		{
			return "<{B_NORM" . $dot . "}>";
		}

		if (intval($topic['posts']) + 1 >= $ibforums->vars['hot_topic'])
		{
			if ($topic['last_post'] > $last_time)
			{
				return "<{B_HOT" . $dot . "}>";
			} else
			{
				return "<{B_HOT_NN" . $dot . "}>";
			}
		}

		if ($topic['last_post'] > $last_time)
		{
			return "<{B_NEW" . $dot . "}>";
		}

		return "<{B_NORM" . $dot . "}>";
	}

	/* ------------------------------------------------------------------------- */

	// text_tidy:
	// Takes raw text from the DB and makes it all nice and pretty - which also
	// parses un-HTML'd characters. Use this with caution!
	/* ------------------------------------------------------------------------- */

	function text_tidy($txt = "")
	{

		$trans = get_html_translation_table(HTML_ENTITIES);
		$trans = array_flip($trans);

		$txt = strtr($txt, $trans);

		$txt = preg_replace("/\s{2}/", "&nbsp; ", $txt);
		$txt = preg_replace("/\r/", "\n", $txt);
		$txt = preg_replace("/\t/", "&nbsp;&nbsp;", $txt);
		//$txt = preg_replace( "/\\n/"   , "&#92;n"       , $txt );

		return $txt;
	}

	/* ------------------------------------------------------------------------- */

	// Build up page span links
	/* ------------------------------------------------------------------------- */

	function build_pagelinks($data)
	{
		global $ibforums;

		$work = array();

		$section = ($data['leave_out'] == "")
			? 2
			: $data['leave_out']; // Number of pages to show per section( either side of current), IE: 1 ... 4 5 [6] 7 8 ... 10

		$work['pages'] = 1;

		if (($data['TOTAL_POSS'] % $data['PER_PAGE']) == 0)
		{
			$work['pages'] = $data['TOTAL_POSS'] / $data['PER_PAGE'];
		} else
		{
			$number        = ($data['TOTAL_POSS'] / $data['PER_PAGE']);
			$work['pages'] = ceil($number);
		}

		$work['total_page']   = $work['pages'];
		$work['current_page'] = $data['CUR_ST_VAL'] > 0
			? ($data['CUR_ST_VAL'] / $data['PER_PAGE']) + 1
			: 1;

		if ($work['pages'] > 1)
		{
			$work['first_page'] = View::make(
					"global.make_page_jump",
					['tp' => $data['TOTAL_POSS'], 'pp' => $data['PER_PAGE'], 'ub' => $data['BASE_URL']]
				) . " (" . $work['pages'] . ")";

			for ($i = 0; $i <= $work['pages'] - 1; ++$i)
			{
				$RealNo = $i * $data['PER_PAGE'];
				$PageNo = $i + 1;

				if ($RealNo == $data['CUR_ST_VAL'])
				{
					$work['page_span'] .= "&nbsp;<b>[{$PageNo}]</b>";
				} else
				{
					if ($PageNo < ($work['current_page'] - $section))
					{
						$work['st_dots'] = "&nbsp;<a href='{$data['BASE_URL']}&amp;st=0' title='{$ibforums->lang['ps_page']} 1'>&laquo; {$ibforums->lang['ps_first']}</a>&nbsp;...";
						continue;
					}

					// If the next page is out of our section range, add some dotty dots!

					if ($PageNo > ($work['current_page'] + $section))
					{
						$work['end_dots'] = "...&nbsp;";

						if ($work['pages'] - $work['current_page'] > 2 and
						    $work['pages'] - $PageNo > 1
						)
						{
							for ($i = $work['pages'] - 2; $i < $work['pages']; ++$i)
							{
								$RealNo = $i * $data['PER_PAGE'];
								$PageNo = $i + 1;

								$work['end_dots'] .= "&nbsp;<a href='{$data['BASE_URL']}&amp;st={$RealNo}'>{$PageNo}</a>";
							}
						} else
						{
							$work['end_dots'] .= "<a href='{$data['BASE_URL']}&amp;st=" . ($work['pages'] - 1) * $data['PER_PAGE'] . "' title='{$ibforums->lang['ps_page']} {$work['pages']}'>{$ibforums->lang['ps_last']} &raquo;</a>";
						}
						break;
					}

					$work['page_span'] .= "&nbsp;<a href='{$data['BASE_URL']}&amp;st={$RealNo}'>{$PageNo}</a>";
				}
			}

			$work['return'] = $work['first_page'] . $work['st_dots'] . $work['page_span'] . '&nbsp;' . $work['end_dots'];

			if (mb_strpos($data['BASE_URL'], "showtopic") !== FALSE and
			    $data['TOTAL_POSS'] < $ibforums->vars['max_show_all_posts']
			)
			{
				$work['return'] .= " <a href='{$data['BASE_URL']}&amp;view=showall'>" . $ibforums->lang['all_posts'] . "</a>";
			}

		} else
		{
			$work['return'] = $data['L_SINGLE'];
		}

		return $work['return'];
	}

	/* ------------------------------------------------------------------------- */

	// Build the forum jump menu
	/* ------------------------------------------------------------------------- */

	function build_forum_jump($html = 1, $override = 0, $remove_redirects = 0)
	{
		global $ibforums;
		// $html = 0 means don't return the select html stuff
		// $html = 1 means return the jump menu with select and option stuff

		if ($html == 1 and !$ibforums->member['cb_forumlist'])
		{
			return "";
		}

		$last_cat_id = -1;

		if ($remove_redirects)
		{
			$qe = 'AND f.redirect_on <> 1';
		} else
		{
			$qe = '';
		}

		$stmt = $ibforums->db->query("SELECT f.id as forum_id, f.parent_id, f.subwrap, f.sub_can_post, f.name as forum_name,
				   f.position, f.redirect_on, f.read_perms, c.id as cat_id, c.name
			    FROM ibf_forums f
			    LEFT JOIN ibf_categories c ON (c.id=f.category)
			    WHERE c.state IN (1,2) $qe
			    ORDER BY c.position, f.position");

		if ($html == 1)
		{

			$the_html = "<form onsubmit=\"if(document.jumpmenu.f.value == -1){return false;}\" action='{$ibforums->base_url}act=SF' method='get' name='jumpmenu'>
			             <input type='hidden' name='act' value='SF'>\n<input type='hidden' name='s' value='{$ibforums->session_id}'>
			             <select name='f' class='forminput'>
			             <optgroup label=\"{$ibforums->lang['sj_title']}\">
			              <option value='sj_home'>{$ibforums->lang['sj_home']}</option>
			              <option value='sj_search'>{$ibforums->lang['sj_search']}</option>
			              <option value='sj_help'>{$ibforums->lang['sj_help']}</option>
			             </optgroup>
			             <optgroup label=\"{$ibforums->lang['forum_jump']}\">";
		}

		$forum_keys = array();
		$cat_keys   = array();
		$children   = array();
		$subs       = array();
		$subwrap    = array();

		// disable short mode if we're compiling a mod form

		if ($html == 0 or $override == 1)
		{
			$ibforums->vars['short_forum_jump'] = 0;
		}

		while ($i = $stmt->fetch())
		{
			$selected = '';
			$redirect = "";

			if ($html == 1 or $override == 1)
			{
				if ($ibforums->input['f'] and $ibforums->input['f'] == $i['forum_id'])
				{
					$selected = ' selected="selected"';
				}
			}

			if ($i['redirect_on'])
			{
				$redirect = $ibforums->lang['fj_redirect'];
			}

			if ($i['subwrap'] == 1)
			{
				$subwrap[$i['forum_id']] = 1;
			}

			if ($i['subwrap'] == 1 and $i['sub_can_post'] != 1)
			{
				$forum_keys[$i['cat_id']][$i['forum_id']] = "<option value=\"{$i['forum_id']}\"" . $selected . ">&nbsp;&nbsp;- {$i['forum_name']}</option>\n";
			} else
			{
				if ($this->check_perms($i['read_perms']) == TRUE)
				{
					if ($i['parent_id'] > 0)
					{
						$children[$i['parent_id']][] = array(
							$i['forum_id'],
							"<option value=\"{$i['forum_id']}\"" . $selected . ">&nbsp;&nbsp;<IBF_SONG_DEPTH>---- {$i['forum_name']} $redirect</option>\n"
						);
					} else
					{
						$forum_keys[$i['cat_id']][$i['forum_id']] = "<option value=\"{$i['forum_id']}\"" . $selected . ">&nbsp;&nbsp;- {$i['forum_name']} $redirect</option><!--fx:{$i['forum_id']}-->\n";
					}
				} else
				{
					continue;
				}
			}

			if ($last_cat_id != $i['cat_id'])
			{

				// Make sure cats with hidden forums are not shown in forum jump

				$cat_keys[$i['cat_id']] = "<option value='-1' disabled=\"disabled\">{$i['name']}</option>\n";

				$last_cat_id = $i['cat_id'];
			}
		}

		foreach ($cat_keys as $cat_id => $cat_text)
		{
			if (is_array($forum_keys[$cat_id]) && count($forum_keys[$cat_id]) > 0)
			{
				$the_html .= $cat_text;

				foreach ($forum_keys[$cat_id] as $idx => $forum_text)
				{
					if ($subwrap[$idx] != 1)
					{
						$the_html .= $forum_text;
					} else
					{
						if (count($children[$idx]) > 0)
						{
							$the_html .= $forum_text;

							if ($ibforums->vars['short_forum_jump'] != 1)
							{
								$the_html .= $this->subforums_addtoform($idx, $children);
							} else
							{
								$the_html = str_replace('<IBF_SONG_DEPTH>', "", $the_html);

								$the_html = str_replace("</option><!--fx:$idx-->", " (+" . count($children[$idx]) . " {$ibforums->lang['fj_subforums']})</option>", $the_html);
							}
						}
					}
				}
			}
		}

		if ($html == 1)
		{
			$the_html .= "</optgroup>\n</select>&nbsp;<input type='submit' value='{$ibforums->lang['jmp_go']}' class='forminput'></form>";
		}

		return $the_html;
	}

	function subforums_addtoform($id, &$children, $level = '')
	{

		$html = '';

		if (count($children[$id]) > 0)
		{
			foreach ($children[$id] as $ii => $tt)
			{
				$prefix = "";

				// visuality depth
				for ($i = 0; $i < $level; $i++)
				{
					$prefix .= "---";
				}

				$tt[1] = str_replace('<IBF_SONG_DEPTH>', $prefix, $tt[1]);

				$html .= $prefix . $tt[1] . $this->subforums_addtoform($tt[0], $children, $level + 1);
			}
		}

		return $html;
	}

	function build_forum_jump_topics($html = 1, $override = 0, $remove_redirects = 0)
	{
		global $ibforums;
		// $html = 0 means don't return the select html stuff
		// $html = 1 means return the jump menu with select and option stuff

		if (!$ibforums->member['cb_forumlist'])
		{
			return "";
		}

		$last_cat_id = -1;

		if ($remove_redirects)
		{
			$qe = 'AND f.redirect_on <> 1';
		} else
		{
			$qe = '';
		}

		$stmt = $ibforums->db->query("SELECT
				f.id as forum_id,
				f.parent_id,
				f.subwrap,
				f.sub_can_post,
				f.name as forum_name,
				f.position,
				f.redirect_on,
				f.read_perms,
				c.id as cat_id,
				c.name
			    FROM ibf_forums f
		     	    LEFT JOIN ibf_categories c
				ON (c.id=f.category)
			    WHERE
				c.state IN (1,2)
				$qe
			    ORDER BY c.position, f.position");

		if ($html == 1)
		{

			$the_html = "<form onsubmit=\"if(document.jumpmenu.f.value == -1){return false;}\" action='{$ibforums->base_url}act=SF' method='get' name='jumpmenu'>
			             <input type='hidden' name='act' value='SF'>\n<input type='hidden' name='s' value='{$ibforums->session_id}'>
			             <select name='f' class='forminput'>
			             <optgroup label=\"{$ibforums->lang['sj_title']}\">
			              <option value='sj_home'>{$ibforums->lang['sj_home']}</option>
			              <option value='sj_search'>{$ibforums->lang['sj_search']}</option>
			              <option value='sj_help'>{$ibforums->lang['sj_help']}</option>
			             </optgroup>
			             <optgroup label=\"{$ibforums->lang['forum_jump']}\">";
		}

		$forum_keys = array();
		$cat_keys   = array();
		$children   = array();
		$subs       = array();
		$subwrap    = array();

		// disable short mode if we're compiling a mod form

		if ($html == 0 or $override == 1)
		{
			$ibforums->vars['short_forum_jump'] = 0;
		}

		while ($i = $stmt->fetch())
		{
			$selected = '';
			$redirect = "";

			if ($html == 1 or $override == 1)
			{
				if ($ibforums->input['f'] and $ibforums->input['f'] == $i['forum_id'])
				{
					$selected = ' selected="selected"';
				}
			}

			if ($i['redirect_on'])
			{
				$redirect = $ibforums->lang['fj_redirect'];
			}

			if ($i['subwrap'] == 1)
			{
				$subwrap[$i['forum_id']] = 1;
			}

			if ($i['subwrap'] == 1 and $i['sub_can_post'] != 1)
			{
				$forum_keys[$i['cat_id']][$i['forum_id']] = "<option value=\"{$i['forum_id']}\"" . $selected . ">&nbsp;&nbsp;- {$i['forum_name']}</option>\n";
			} else
			{
				if ($this->check_perms($i['read_perms']) == TRUE)
				{
					if ($i['parent_id'] > 0)
					{
						$children[$i['parent_id']][] = array(
							$i['forum_id'],
							"<option value=\"{$i['forum_id']}\"" . $selected . ">&nbsp;&nbsp;<IBF_SONG_DEPTH>---- {$i['forum_name']} $redirect</option>\n"
						);
					} else
					{
						$forum_keys[$i['cat_id']][$i['forum_id']] = "<option value=\"{$i['forum_id']}\"" . $selected . ">&nbsp;&nbsp;- {$i['forum_name']} $redirect</option><!--fx:{$i['forum_id']}-->\n";
					}
				} else
				{
					continue;
				}
			}

			if ($last_cat_id != $i['cat_id'])
			{

				// Make sure cats with hidden forums are not shown in forum jump

				$cat_keys[$i['cat_id']] = "<option value='-1'>{$i['name']}</option>\n";

				$last_cat_id = $i['cat_id'];
			}
		}

		foreach ($cat_keys as $cat_id => $cat_text)
		{
			if (is_array($forum_keys[$cat_id]) && count($forum_keys[$cat_id]) > 0)
			{
				$the_html .= $cat_text;

				foreach ($forum_keys[$cat_id] as $idx => $forum_text)
				{
					if ($subwrap[$idx] != 1)
					{
						$the_html .= $forum_text;
					} else
					{
						if (count($children[$idx]) > 0)
						{
							$the_html .= $forum_text;

							if ($ibforums->vars['short_forum_jump'] != 1)
							{
								$the_html .= $this->subforums_addtoform($idx, $children);
							} else
							{
								$the_html = str_replace('<IBF_SONG_DEPTH>', "", $the_html);

								$the_html = str_replace("</option><!--fx:$idx-->", " (+" . count($children[$idx]) . " {$ibforums->lang['fj_subforums']})</option>", $the_html);
							}
						}
					}
				}
			}
		}

		if ($html == 1)
		{
			$the_html .= "</optgroup>\n</select>&nbsp;<input type='submit' value='{$ibforums->lang['jmp_go']}' class='forminput'></form>";
		}

		return $the_html;
	}

	function clean_email($email = "")
	{

		$email = trim($email);

		$email = str_replace(" ", "", $email);

		$email = preg_replace("#[\;\#\n\r\*\'\"<>&\%\!\(\)\{\}\[\]\?\\/]#", "", $email);

		if (preg_match("/^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,4})(\]?)$/", $email))
		{
			return $email;
		} else
		{
			return FALSE;
		}
	}

	/* ------------------------------------------------------------------------- */
	// Require, parse and return an array containing the language stuff
	/* ------------------------------------------------------------------------- */

	/**
	 * @param $current_lang_array array Current words array
	 * @param $area string Area for loading
	 * @param $lang_type string Used language (ru/en)
	 * @return string
	 */
	function load_words($current_lang_array, $area, $lang_type)
	{
		$lang = [];//todo проще можно, намного
		require Ibf::app()->vars['base_dir'] . "lang/" . $lang_type . "/" . $area . ".php";

		foreach ($lang as $k => $v)
		{
			$current_lang_array[$k] = stripslashes($v);
		}

		unset($lang);

		return $current_lang_array;
	}

	/* ------------------------------------------------------------------------- */

	// Return a date or '--' if the date is undef.
	// We use the rather nice gmdate function in PHP to synchronise our times
	// with GMT. This gives us the following choices:
	//
	// If the user has specified a time offset, we use that. If they haven't set
	// a time zone, we use the default board time offset (which should automagically
	// be adjusted to match gmdate.
	/* ------------------------------------------------------------------------- */

	function old_get_date($date)
	{
		if (!$date)
		{
			return '--';
		}

		if ($this->offset_set == 0)
		{
			// Save redoing this code for each call, only do once per page load
			$this->offset     = $this->get_time_offset_or_set_timezone();
			$this->offset_set = 1;
		}

		return gmdate("j.m.y, H:i", $date + $this->offset);
	}

	// возврат форматированной даты
	function get_date($date, $html = 1)
	{
		global $ibforums;

		// возвращаем прочерк если нет даты
		if (!$date)
		{
			return "&mdash;";
		}

		// определяем временную зону
		$offset = $this->get_time_offset_or_set_timezone();

		// определяем форматирование
		$formatting = array(
			"-1" => array("", ""),
			"0"  => array("[b]", "[/b]"),
			"1"  => array("<b>", "</b>"),
		);
		$html       = isset($formatting[$html])
			? $formatting[$html]
			: current($formatting);
		$datef      = $ibforums->vars['datef_template'];
		$datef_date = $ibforums->vars['datef_date'];

		// отключен человекопонятный формат
		if ($ibforums->member['hotclocks'] == 0)
		{
			// формат для ботов
			$datef_date = "%d.%m.%y";
		} else
		{
			// включена функция "отброса года из даты"
			if ($ibforums->vars['datef_dropyear'] == "1" && strftime("%Y", $date) == strftime("%Y"))
			{
				$datef_date = trim(str_replace("%Y", "", $datef_date));
			}

			// переводим месяц в нормальный формат
			if (!empty($ibforums->lang['month' . strftime("%m", $date)]))
			{
				$datef_date = str_replace("%B", $ibforums->lang['month' . strftime("%m", $date)], $datef_date);
			}
		}

		// вычисляем дату
		$datef_date = strftime($datef_date, $date + $offset);

		// включена функция "человекопонятного времени"
		if ($ibforums->member['hotclocks'] > 0)
		{
			// вычисляем сколько минут прошло
			$mins = floor((time() - $date) / 60);

			// в течении часа
			if ($mins >= 0 && $mins < 60 && in_array($ibforums->member['hotclocks'], array(1, 2)))
			{
				// n минут назад
				if ($mins > 0)
				{
					// определяем окончание слова
					$ending = "";
					$strm   = ($mins > 20)
						? mb_substr((string)$mins, -1)
						: false;
					if ($mins == 1 || $strm == "1")
					{
						$ending = $ibforums->lang['minutes_ending1'];
					} elseif ($mins == 2 || $mins == 3 || $mins == 4 || $strm == "2" || $strm == "3" || $strm == "4")
					{
						$ending = $ibforums->lang['minutes_ending2'];
					}

					// собираем в кучу
					$datef_date = $html[0] . $mins . " " . sprintf($ibforums->lang['minutes_ago'], $ending . $html[1]);
					$datef      = "%date";
				} else
				{
					// менее минуты назад
					$datef_date = $html[0] . $ibforums->lang['minutes_less'] . $html[1];
					$datef      = "%date";
				}
			} elseif ($mins >= 60 && $mins <= 1440 && $ibforums->member['hotclocks'] == 2)
			{
				// n часов назад
				$hours = floor($mins / 60);

				// определяем окончание слова
				$ending = "";
				$strm   = ($hours > 20)
					? mb_substr((string)$hours, -1)
					: false;
				if ($hours == 2 || $hours == 3 || $hours == 4 || $strm == "2" || $strm == "3" || $strm == "4")
				{
					$ending = $ibforums->lang['hours_ending2'];
				} elseif ($hours == 0 || $strm == "0" || $hours == 5 || $strm == "5" || $hours == 6 || $strm == "6" || $hours == 7 || $strm == "7" || $hours == 8 || $strm == "8" || $hours == 9 || $strm == "9")
				{
					$ending = $ibforums->lang['hours_ending1'];
				}

				// собираем в кучу
				$datef_date = $html[0] . $hours . " " . sprintf($ibforums->lang['hours_ago'], $ending . $html[1]);
				$datef      = "%date";
			} elseif (date('Y', $date) !== date('Y'))
			{
				//nothing to do
			} elseif (strftime("%j", $date) == strftime("%j"))
			{
				// сегодняшняя дата
				$datef_date = $html[0] . $ibforums->lang['today'] . $html[1];
			} elseif (strftime("%j", $date) == strftime("%j", time() - 86400))
			{
				// вчерашняя дата
				$datef_date = $ibforums->lang['yesterday'];
			}
		}

		// вычисляем время
		$datef_time = strftime($ibforums->vars['datef_time'], $date + $offset);

		// подставляем по шаблону
		$datef = str_replace(array("%date", "%time"), array($datef_date, $datef_time), $datef);

		return $datef;
	}

	function get_member_time_offset_or_set_timezone($member)
	{
		global $ibforums;

		// определяем временную зону
		$offset = $member['time_offset']
			? : $member['time_offset'];

		if (preg_match("~UTC|\w+/[\w/]+~", $offset))
		{
			// именные временные зоны 'Europe/Moscow'
			date_default_timezone_set($offset);

			$offset = 0;
		} else
		{
			// временная зона по умолчанию
			date_default_timezone_set("UTC");

			// вычисление сдвига времени старым способом
			if ($this->offset_set == 0)
			{
				$this->offset = $offset * 3600;

				if (intval($ibforums->vars['time_adjust']) > 0)
				{
					$this->offset += (intval($ibforums->vars['time_adjust']) * 60);
				}

				// летнее время
				if ($member['dst_in_use'])
				{
					$this->offset += (int)date("I") * 3600;
				} else
				{
					$this->offset_set = 1;
				}
			}

			$offset = $this->offset;
		}
		return $offset;
	}

	function get_time_offset_or_set_timezone()
	{
		global $ibforums;

		return $this->get_member_time_offset_or_set_timezone($ibforums->member);
	}

	function format_date_without_time($date)
	{
		global $ibforums;

		// возвращаем прочерк если нет даты
		if (!$date)
		{
			return "&mdash;";
		}

		$offset = $this->get_time_offset_or_set_timezone();

		return date('j.m.y', $date + $offset);
	}

	/* ------------------------------------------------------------------------- */

	// Sets a cookie, abstract layer allows us to do some checking, etc
	/* ------------------------------------------------------------------------- */

	function my_setcookie($name, $value = "", $sticky = 1)
	{
		global $ibforums;

		//$expires = "";

		if ($sticky == 1)
		{
			$expires = time() + 60 * 60 * 24 * 365;
		}

		$ibforums->vars['cookie_domain'] = $ibforums->vars['cookie_domain'] == ""
			? ""
			: $ibforums->vars['cookie_domain'];
		$ibforums->vars['cookie_path']   = $ibforums->vars['cookie_path'] == ""
			? "/"
			: $ibforums->vars['cookie_path'];

		$name = $ibforums->vars['cookie_id'] . $name;

		@setcookie($name, $value, $expires, $ibforums->vars['cookie_path'], $ibforums->vars['cookie_domain'], TRUE);
	}

	/* ------------------------------------------------------------------------- */

	// Cookies, cookies everywhere and not a byte to eat.
	/* ------------------------------------------------------------------------- */

	function my_getcookie($name)
	{
		global $ibforums;

		if (isset($_COOKIE[$ibforums->vars['cookie_id'] . $name]))
		{
			return $this->clean_value(urldecode($_COOKIE[$ibforums->vars['cookie_id'] . $name]));
		} else
		{
			return FALSE;
		}
	}

	/* ------------------------------------------------------------------------- */

	// Makes incoming info "safe"
	/* ------------------------------------------------------------------------- */

	function parse_incoming()
	{
		//    	global $HTTP_X_FORWARDED_FOR;

		$return = array();

		if (is_array($_GET))
		{
		    foreach ($_GET as $k => $v)
			{
				if ($k == 'INFO')
				{
					continue;
				}
				if (is_array($_GET[$k]))
				{
					while (list($k2, $v2) = each($_GET[$k]))
					{
						$return[$k][$this->clean_key($k2)] = $this->clean_value($v2, true);
					}
				} else
				{
					$return[$k] = $this->clean_value($v, true);
				}
			}
		}

		// Overwrite GET data with post data

		if (is_array($_POST))
		{
		    foreach ($_POST as $k => $v)
		    {
				if (is_array($_POST[$k]))
				{
					while (list($k2, $v2) = each($_POST[$k]))
					{
						$return[$k][$this->clean_key($k2)] = $this->clean_value($v2);
					}
				} else
				{
					$return[$k] = $this->clean_value($v);
				}
			}
		}

		//----------------------------------------
		// Sort out the accessing IP
		// (Thanks to Cosmos and schickb)
		//----------------------------------------

		$addrs = array();

		$addrs[] = $_SERVER['REMOTE_ADDR'];
		if (isset($_SERVER['HTTP_PROXY_USER']))
		{
			$addrs[] = $_SERVER['HTTP_PROXY_USER'];
		}
		//	$addrs[] = $_SERVER['REMOTE_ADDR'];
		//header("Content-type: text/plain"); print_r($addrs); print $_SERVER['HTTP_X_FORWARDED_FOR']; exit();

		$return['IP_ADDRESS'] = $this->select_var($addrs);

		// Make sure we take a valid IP address

		$return['IP_ADDRESS'] = preg_replace("/^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})/", "\\1.\\2.\\3.\\4", $return['IP_ADDRESS']);

		$return['request_method'] = ($_SERVER['REQUEST_METHOD'] != "")
			? mb_strtolower($_SERVER['REQUEST_METHOD'])
			: mb_strtolower($REQUEST_METHOD);

		return $return;
	}

	/* ------------------------------------------------------------------------- */

	// Key Cleaner - ensures no funny business with form elements
	/* ------------------------------------------------------------------------- */

	function clean_key($key)
	{

		if ($key == "")
		{
			return "";
		}

		$key = preg_replace("/\.\./", "", $key);
		$key = preg_replace("/\_\_(.+?)\_\_/", "", $key);
		$key = preg_replace("/^([\w\.\-\_]+)$/", "$1", $key);

		return $key;
	}

	function clean_value($val, $clean_apostroph=false)
	{
		global $ibforums;

		if ($val == "")
		{
			return "";
		}

		if($clean_apostroph) {
			 // Clean dangerous characters (quote, apostroph, etc) from GET parameters
			$val = str_replace("\xBF\x27", '', $val);
			$val = str_replace("'", '', $val);
			$val = str_replace('"', '', $val);
		}

		$val = str_replace("&#032;", " ", $val);

		if ($ibforums->vars['strip_space_chr'])
		{
			$val = str_replace(chr(0xCA), "", $val); //Remove sneaky spaces
		}

		$val = str_replace("&", "&amp;", $val);
		$val = str_replace("<!--", "&#60;&#33;--", $val);
		$val = str_replace("-->", "--&#62;", $val);
		$val = preg_replace("/<(script)/i", "&#60;\\1", $val);
		$val = str_replace(">", "&gt;", $val);
		$val = str_replace("<", "&lt;", $val);
		$val = str_replace("\"", "&quot;", $val);
		$val = preg_replace("/\n/", "<br>", $val); // Convert literal newlines
		$val = preg_replace("/\\\$/", "&#036;", $val);
		$val = preg_replace("/\r/", "", $val); // Remove literal carriage returns
		$val = str_replace("!", "&#33;", $val);
		$val = str_replace("'", "&#39;", $val); // IMPORTANT: It helps to increase sql query safety.
		// Ensure unicode chars are OK

		if ($this->allow_unicode)
		{
			$val = preg_replace("/&amp;#([0-9]+);/s", "&#\\1;", $val);
		}

		// Strip slashes if not already done so.
		if ($this->get_magic_quotes)
		{
			$val = stripslashes($val);
		}

		// Swop user inputted backslashes
		$val = preg_replace("/\\\(?!&amp;#|\?#)/", "&#092;", $val);

		return $val;
	}

	function remove_tags($text = "")
	{
		// Removes < BOARD TAGS > from posted forms

		$text = preg_replace("/(<|&lt;)% (BOARD HEADER|CSS|JAVASCRIPT|TITLE|BOARD|STATS|GENERATOR|COPYRIGHT|NAVIGATION) %(>|&gt;)/i", "&#60;% \\2 %&#62;", $text);

		//$text = str_replace( "<%", "&#60;%", $text );

		return $text;
	}

	function is_number($number = "")
	{

		if ($number == "")
		{
			return -1;
		}

		if (preg_match("/^([0-9]+)$/", $number))
		{
			return $number;
		} else
		{
			return "";
		}
	}

	/* ------------------------------------------------------------------------- */

	// MEMBER FUNCTIONS
	/* ------------------------------------------------------------------------- */

	function set_up_guest($name = 'Guest')
	{
		global $ibforums;

		return array(
			'name'      => $name,
			'id'        => 0,
			'password'  => "",
			'email'     => "",
			'title'     => "Unregistered",
			'mgroup'    => $ibforums->vars['guest_group'],
			'view_sigs' => $ibforums->vars['guests_sig'],
			'view_img'  => $ibforums->vars['guests_img'],
			'view_avs'  => $ibforums->vars['guests_ava'],
		);
	}

	/* ------------------------------------------------------------------------- */

	// GET USER AVATAR
	/* ------------------------------------------------------------------------- */

	function get_avatar($member_avatar = "", $member_view_avatars = 0, $avatar_dims = "x")
	{
		global $ibforums;

		if (!$member_avatar or $member_view_avatars == 0 or !$ibforums->vars['avatars_on'])
		{
			return "";
		}

		if (preg_match("/^noavatar/", $member_avatar))
		{
			return "";
		}

		if ((preg_match("/\.swf/", $member_avatar)) and ($ibforums->vars['allow_flash'] != 1))
		{
			return "";
		}

		$davatar_dims   = explode("x", $ibforums->vars['avatar_dims']);
		$default_a_dims = explode("x", $ibforums->vars['avatar_def']);

		//---------------------------------------
		// Have we enabled URL / Upload avatars?
		//---------------------------------------

		$this_dims = explode("x", $avatar_dims);
		if (!$this_dims[0])
		{
			$this_dims[0] = $davatar_dims[0];
		}
		if ($this_dims[0] > $davatar_dims[0])
		{
			$this_dims[0] = $davatar_dims[0];
		}
		if (!$this_dims[1])
		{
			$this_dims[1] = $davatar_dims[1];
		}

		if (preg_match("/^http:\/\//", $member_avatar))
		{
			// Ok, it's a URL..

			if (preg_match("/\.swf/", $member_avatar))
			{
				return "<OBJECT CLASSID=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" WIDTH={$this_dims[0]} HEIGHT={$this_dims[1]}><PARAM NAME=MOVIE VALUE={$member_avatar}><PARAM NAME=PLAY VALUE=TRUE><PARAM NAME=LOOP VALUE=TRUE><PARAM NAME=QUALITY VALUE=HIGH><EMBED SRC={$member_avatar} WIDTH={$this_dims[0]} HEIGHT={$this_dims[1]} PLAY=TRUE LOOP=TRUE QUALITY=HIGH></EMBED></OBJECT>";
			} else
			{
				return "<img src='{$member_avatar}' border='0' width='{$this_dims[0]}' height='{$this_dims[1]}' alt=''>";
			}

			//---------------------------------------
			// Not a URL? Is it an uploaded avatar?
			//---------------------------------------
		} else {
			if (($ibforums->vars['avup_size_max'] > 1) and (preg_match("/^upload:av-(?:\d+)\.(?:\S+)/", $member_avatar)))
			{
				$member_avatar = preg_replace("/^upload:/", "", $member_avatar);

				if (preg_match("/\.swf/", $member_avatar))
				{
					return "<OBJECT CLASSID=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" WIDTH={$this_dims[0]} HEIGHT={$this_dims[1]}><PARAM NAME=MOVIE VALUE=\"{$ibforums->vars['upload_url']}/$member_avatar\"><PARAM NAME=PLAY VALUE=TRUE><PARAM NAME=LOOP VALUE=TRUE><PARAM NAME=QUALITY VALUE=HIGH><EMBED SRC=\"{$ibforums->vars['upload_url']}/$member_avatar\" WIDTH={$this_dims[0]} HEIGHT={$this_dims[1]} PLAY=TRUE LOOP=TRUE QUALITY=HIGH></EMBED></OBJECT>";
				} else
				{
					return "<img src='{$ibforums->vars['upload_url']}/$member_avatar' border='0' width='{$this_dims[0]}' height='{$this_dims[1]}' alt=''>";
				}
			} //---------------------------------------
			// No, it's not a URL or an upload, must
			// be a normal avatar then
			//---------------------------------------
			else
			{
				if ($member_avatar != "")
				{
					//---------------------------------------
					// Do we have an avatar still ?
					//---------------------------------------

					return "<img src='{$ibforums->vars['AVATARS_URL']}/{$member_avatar}' border='0' alt=''>";
					//---------------------------------------
					// No, ok - return blank
					//---------------------------------------
				} else
				{
					return "";
				}
			}
		}
	}

	/* ------------------------------------------------------------------------- */

	// ERROR FUNCTIONS
	/* ------------------------------------------------------------------------- */

	function JsError($error = array())
	{
		global $ibforums, $print;

		$url = "location.href='{$ibforums->base_url}act=Error&type={$error['MSG']}'";

		$print->redirect_js_screen("", "", "", $url);
	}

	function Error($error)
	{
		global $ibforums;

		//INIT is passed to the array if we've not yet loaded a skin and stuff

		if (isset($ibforums->input['js']) && $ibforums->input['js'] && $ibforums->input['linkID'])
		{
			$this->JsError($error);

			exit();
		}

		if ($error['INIT'] == 1)
		{
			$ibforums->skin = Skins\Factory::createDefaultSkin();

			$ibforums->session_id = $this->my_getcookie('session_id');

			$ibforums->base_url = $ibforums->vars['board_url'] . '/index.' . $ibforums->vars['php_ext'] . '?s=' . $ibforums->session_id;

			if ($ibforums->vars['default_language'] == "")
			{
				$ibforums->vars['default_language'] = 'en';
			}

			$ibforums->lang_id = (isset($ibforums->member['language']) && $ibforums->member['language'])
				? $ibforums->member['language']
				: $ibforums->vars['default_language'];

			if (($ibforums->lang_id != $ibforums->vars['default_language']) and
			    (!is_dir($ibforums->vars['base_dir'] . "lang/" . $ibforums->lang_id))
			)
			{
				$ibforums->lang_id = $ibforums->vars['default_language'];
			}

			$ibforums->lang = $this->load_words($ibforums->lang, "lang_global", $ibforums->lang_id);
		}

		$ibforums->lang = $this->load_words($ibforums->lang, "lang_error", $ibforums->lang_id);

		list($em_1, $em_2) = explode('@', $ibforums->vars['email_in']);

		$msg = $ibforums->lang[$error['MSG']];

		if (!$msg)
		{
			$msg = $ibforums->lang['missing_files'];
		}

		if (!$error['EXTRA'])
		{
			$error['EXTRA'] = "";
		}
		$msg = preg_replace("/<#EXTRA#>/", $error['EXTRA'], $msg);

		if (!isset($error['EXTRA2']) || !$error['EXTRA2'])
		{
			$error['EXTRA2'] = "";
		}
		$msg = preg_replace("/<#EXTRA2#>/", $error['EXTRA2'], $msg);
		//sh: This returns to user just only error string
		//Such behavior is needed for:
		//a) offline clients
		//b) searching bots engines
		//c) plugins

		if ($ibforums->vars['plg_catch_err'])
		{
			$ibforums->vars['plg_catch_err']->Error($msg);
		}

		$html = View::make("global.Error", ['message' => $msg, 'ad_email_one' => $em_1, 'ad_email_two' => $em_2]);

		//-----------------------------------------
		// If we're a guest, show the log in box..
		//-----------------------------------------

		if (!$ibforums->member['id'] and $error['MSG'] != 'server_too_busy' and $error['MSG'] != 'account_susp')
		{
			$html = str_replace("<!--IBF.LOG_IN_TABLE-->",
				View::make("global.error_log_in", ['q_string' => $_SERVER['QUERY_STRING']]), $html);
		}

		//-----------------------------------------
		// Do we have any post data to keepy?
		//-----------------------------------------

		if ($ibforums->input['act'] == 'Post' OR $ibforums->input['act'] == 'Msg' OR $ibforums->input['act'] == 'calendar')
		{
			if ($_POST['Post'])
			{
				$post_thing = View::make(
					"global.error_post_textarea",
					['post' => $this->txt_htmlspecialchars($this->txt_stripslashes($_POST['Post']))]
				);

				$html = str_replace("<!--IBF.POST_TEXTAREA-->", $post_thing, $html);
			}
		}

		$print = new display();

		$print->add_output($html);

		$print->do_output(array(
		                       'OVERRIDE' => 1,
		                       'TITLE'    => $ibforums->lang['error_title'],
		                  ));
	}

	function song_get_forumsread()
	{
		global $ibforums;

		$stmt = $ibforums->db->query("SELECT
				fid,
				logTime
			    FROM ibf_log_forums
			    WHERE mid='" . $ibforums->member['id'] . "'");

		if ($stmt->rowCount())
		{
			while ($read = $stmt->fetch())
			{
				$ibforums->forum_read[$read['fid']] = $read['logTime'];
			}
		} else
		{
			$ibforums->forum_read = array();
		}

		$ibforums->forums_read = unserialize($ibforums->member['forums_read']);

		return TRUE;
	}

	function copy_topicread_status($topic_from_id, $topic_to_id, $forum_to_id)
	{
		$ibforums = Ibf::app();
		settype($topic_from_id, 'integer');
		settype($topic_to_id, 'integer');
		settype($forum_to_id, 'integer');

		$stmt = $ibforums->db->query("REPLACE INTO ibf_log_topics (mid, tid, fid, logTime)
				SELECT mid, $topic_to_id, $forum_to_id, logTime FROM ibf_log_topics WHERE tid = $topic_from_id");
	}

	function song_set_forumread($fid)
	{
		$ibforums = Ibf::app();

		if ($ibforums->member['id'])
		{
			// do safe query
			try
			{
				if (isset($ibforums->forum_read[$fid]))
				{
					$stmt = $ibforums->db->query("UPDATE ibf_log_forums
				    SET logTime='" . time() . "'
				    WHERE
					fid='" . $fid . "' AND
					mid='" . $ibforums->member['id'] . "'");
				} else
				{
					$ibforums->db->query("INSERT INTO ibf_log_forums
				    VALUES ('" . $ibforums->member['id'] . "', '" . $fid . "','" . time() . "')");
				}
			} catch (PDOException $e)
			{
				//nothing
			}
		}
	}

	/**
	 * Возвращает время последнего просмотра топика текущим пользователем
	 *
	 * @param int $topic_id
	 * @return int (timestamp) 0 если не было просмотров
	 */
	function get_topic_last_read($topic_id, $checkmont = true)
	{
		global $ibforums;
		settype($topic_id, 'integer');
		$quid = "SELECT logTime
				    FROM ibf_log_topics
				    WHERE
					tid=$topic_id AND
					mid='{$ibforums->member['id']}'
					LIMIT 1";
		if (FALSE != $row = $ibforums->db->query($quid)->fetch())
		{
			return intval($row['logTime']);
		}
		if ($checkmont)
		{
			// проверим на сколько давно топик создан. Если больше, чем месяц назад
			// то следует редиректить на тот пост, что был сделан за этот самый месяц
			$last = $ibforums->db->query("SELECT min(post_date) FROM ibf_posts WHERE topic_id = {$topic_id}")
				->fetchColumn();

			if ($last < strtotime('-1 month'))
			{
				return strtotime('-1 month');
			}
		}
		return 0;
	}

	function song_set_topicread($fid, $tid)
	{
		global $ibforums;

		if (!$ibforums->member['id'])
		{
			return;
		}
		static $readed_topics = array();

		$read_stamp_exists = false;

		if (!isset($readed_topics[$tid]))
		{
			// check if log records is exists

			if ($this->get_topic_last_read($tid, false))
			{
				$readed_topics[$tid] = true;
				$read_stamp_exists   = true;
			}
		} else
		{
			$read_stamp_exists = true;
		}

		if ($read_stamp_exists)
		{
			$ibforums->db->exec("UPDATE ibf_log_topics
			    SET logTime=" . time() . "
			    WHERE
				tid='{$tid}' AND
				mid='{$ibforums->member['id']}'");
		} else
		{
			$upd = $ibforums->db->exec("UPDATE ibf_log_topics
			    SET logTime=" . time() . "
			    WHERE
				tid='{$tid}' AND
				fid='{$fid}' AND
				mid='{$ibforums->member['id']}'");
			if ($upd === 0)
			{
				//ничего не обновилось
				$ibforums->db->exec("INSERT INTO ibf_log_topics
			    VALUES('{$ibforums->member['id']}', '{$tid}', '{$fid}', " . time() . " )");
			}
		}

		$this->song_set_forumread($fid);
	}

	function board_offline()
	{
		global $ibforums;

		$ibforums->lang = $this->load_words($ibforums->lang, "lang_error", $ibforums->lang_id);

		$msg = preg_replace("/\n/", "<br>", stripslashes($ibforums->vars['offline_msg']));

		$html = View::make("global.board_offline", ['message' => $msg]);

		$print = new display();

		$print->add_output($html);

		$print->do_output(array(
		                       'OVERRIDE' => 1,
		                       'TITLE'    => $ibforums->lang['offline_title'],
		                  ));
	}

	/* ------------------------------------------------------------------------- */

	// Variable chooser
	/* ------------------------------------------------------------------------- */

	function select_var($array)
	{

		if (!is_array($array))
		{
			return -1;
		}

		ksort($array);

		$chosen = -1; // Ensure that we return zero if nothing else is available

		foreach ($array as $k => $v)
		{
			if (isset($v))
			{
				$chosen = $v;
				break;
			}
		}

		return $chosen;
	}

	function flood_begin()
	{
		global $ibforums, $sess, $std;

		if ($ibforums->vars['flood_control'] > 0)
		{
			if ($ibforums->member['id'])
			{
				// Flood check..
				if ($ibforums->member['g_avoid_flood'] != 1)
				{
					if (time() - $ibforums->member['last_post'] < $ibforums->member['g_search_flood'])
					{
						$time_to_flood = ($ibforums->member['last_post'] + $ibforums->member['g_search_flood'] - time());
						$this->Error(array(
						                  'LEVEL'  => 1,
						                  'MSG'    => 'flood_control',
						                  'EXTRA'  => $ibforums->member['g_search_flood'],
						                  'EXTRA2' => $time_to_flood,
						             ));
					}
				}
			} else
			{
				// Additional flood check
				$stmt = $ibforums->db->query("SELECT last_post
			    FROM ibf_sessions
			    WHERE id='" . $sess->session_id . "'");

				$last_post = $stmt->fetch();

				if ($last_post['last_post'])
				{
					if ((time() - $last_post['last_post']) < $ibforums->vars['flood_control'])
					{
						$time_to_flood = ($ibforums->member['last_post'] + $ibforums->member['flood_control'] - time());
						$this->Error(array(
						                  'LEVEL'  => 1,
						                  'MSG'    => 'flood_control',
						                  'EXTRA'  => $ibforums->vars['flood_control'],
						                  'EXTRA2' => $time_to_flood,
						             ));
					}
				}
			}
		}
	}

	function flood_end()
	{
		global $sess;
		$ibforums = Ibf::app();

		$time = time();

		if ($ibforums->member['id'])
		{
			$ibforums->db->query("UPDATE ibf_members
			    SET last_post='" . $time . "'
			    WHERE id='" . $ibforums->member['id'] . "'");
		} else
		{
			$ibforums->db->query("UPDATE ibf_sessions
			    SET last_post='" . $time . "'
			    WHERE id='" . $sess->session_id . "'");
		}
	}

	//###########################################################################
	//===========================================================================
	// INDEXED SEARCH FUNCTIONS by vot
	//
	// for conf_global.php settings:
	// $INFO['search_sql_method'] =	'index';
	//===========================================================================
	// Prototypes:
	//
	// function index_del_title($tid=0)
	// function index_del_post($pid=0)
	// function index_del_posts($pidlist="")
	// function index_del_topic($tid=0)
	// function index_del_topics($tidlist="")
	// function index_reindex_post($pid=0, $tid=0, $fid=0, $post)
	// function index_reindex_title( $tid=0, $fid=0, $title)
	// function index_move_topics($tids,$movetoforum);
	// function index_wordlist ( $post = "")
	// function index_make_index($pid=0, $tid=0, $fid=0, $words=array())
	//===========================================================================
	//###########################################################################
	//-----------------------------------------------
	// Reindex the Post body
	//-----------------------------------------------
	function index_reindex_post($pid = 0, $tid = 0, $fid = 0, $post = "")
	{
		global $ibforums;

		if ($ibforums->vars['search_sql_method'] == 'index')
		{

			$this->index_del_post($pid);

			$wordlist = $this->index_wordlist($post);

			$this->index_make_index($pid, $tid, $fid, $wordlist);
		}
	}

	//-----------------------------------------------
	// Reindex the Topic Title
	//-----------------------------------------------
	function index_reindex_title($tid = 0, $fid = 0, $title)
	{
		global $ibforums;

		if ($ibforums->vars['search_sql_method'] == 'index')
		{

			$this->index_del_title($tid);

			$wordlist = $this->index_wordlist($title);

			$this->index_make_index(0, $tid, $fid, $wordlist); // Post_ID = 0 for the title indexing !
		}
	}

	//------------------------------------------------------
	// delete indexed earlier words for the topic title only
	//------------------------------------------------------

	function index_del_title($tid = 0)
	{
		$ibforums = Ibf::app();

		if ($ibforums->vars['search_sql_method'] == 'index')
		{
			$stmt = $ibforums->db->query("DELETE
		    FROM ibf_search
		    WHERE tid=$tid
			AND pid=0");
		}
	}

	//--------------------------------------------------
	// delete indexed earlier words for this post
	//--------------------------------------------------

	function index_del_post($pid = 0)
	{
		$ibforums = Ibf::app();

		if ($ibforums->vars['search_sql_method'] == 'index')
		{
			$ibforums->db->exec("DELETE
		    FROM ibf_search
		    WHERE pid=$pid");
		}
	}

	//--------------------------------------------------
	// delete indexed earlier words for the postlist
	//--------------------------------------------------

	function index_del_posts($pidlist = "")
	{
		global $ibforums;

		$pidlist = trim($pidlist);
		if ($pidlist)
		{
			if ($ibforums->vars['search_sql_method'] == 'index')
			{

				$ibforums->db->exec("DELETE
		    FROM ibf_search
		    WHERE pid IN ($pidlist)");
			}
		}
	}

	//--------------------------------------------------
	// delete indexed earlier words for all this topic
	//--------------------------------------------------

	function index_del_topic($tid = 0)
	{
		$ibforums = Ibf::app();

		if ($ibforums->vars['search_sql_method'] == 'index')
		{

			$ibforums->db->exec("DELETE
		    FROM ibf_search
		    WHERE tid=$tid");
		}
	}

	//--------------------------------------------------
	// delete indexed earlier words for the topic list
	//--------------------------------------------------

	function index_del_topics($tidlist = "")
	{
		$ibforums = Ibf::app();

		$tidlist = trim($tidlist);
		if ($tidlist)
		{
			if ($ibforums->vars['search_sql_method'] == 'index')
			{

				$ibforums->db->exec("DELETE
		    FROM ibf_search
		    WHERE tid " . $tidlist);
			}
		}
	}

	//-------------------------------------------------
	// Update the search words - MOVE to another forum
	//-------------------------------------------------
	function index_move_topics($tids, $movetoforum)
	{
		$ibforums = Ibf::app();
		$tidlist  = trim($tidlist);
		if ($tids)
		{
			if ($ibforums->vars['search_sql_method'] == 'index')
			{
				$ibforums->db->exec("UPDATE ibf_search
			    SET fid=$movetoforum
			    WHERE
				tid " . $tids);
				// tid ='nnn'  or  tid IN(nnn,nnn,nnn)
			}
		}
	}

	//--------------------------------------------
	// Build New Search Index for a Title or Post
	//--------------------------------------------

	function index_make_index($pid = 0, $tid = 0, $fid = 0, $words = array())
	{
		$ibforums = Ibf::app();

		if ($ibforums->vars['search_sql_method'] == 'index')
		{
			$i = 1;
			foreach ($words as $word)
			{

				// get id of existing word
				$id   = 0;
				$stmt = $ibforums->db->query("SELECT id
			    FROM ibf_search_words
			    WHERE word='$word'");
				//			    WHERE word='".addslashes($word)."'");

				if (FALSE != $row = $stmt->fetch())
				{
					$id = $row['id'];
					//echo $i.": ".$word." ($id)<br>\n";
				}
				// insert the word
				if (!$id)
				{
					$ibforums->db->exec("INSERT INTO ibf_search_words
					(word)
				    VALUES
					('$word')");
					$id = $ibforums->db->lastInsertId();
					//echo $i.": ".$word." ($id) NEW!<br>\n";
				}

				// add the post/topic record for this word
				if ($id)
				{
					$ibforums->db->exec("INSERT INTO ibf_search
				    VALUES ($pid,
					    $tid,
					    $fid,
					    $id)");
				}
				$i++;
			}

			// Mark the Topic/Post as INDEXED allready

			if ($pid)
			{
				$dsql = "UPDATE ibf_posts
			 SET indexed=1
			 WHERE pid='$pid'";
			} else
			{
				$dsql = "UPDATE ibf_topics
			 SET indexed=1
			 WHERE tid='$tid'";
			}

			$ibforums->db->query($dsql);
		}
		return;
	}

	//-----------------------------------------------
	// Parse the content, strip & return unique words
	//-----------------------------------------------
	function index_wordlist($post = "")
	{
		global $ibforums;

		$post = mb_strtolower($post);

		// Replace line endings by a space
		$post = preg_replace("/[\n\r]/is", " ", $post);

		$post = preg_replace("/<script.*?<\/script>/i", " ", $post);
		$post = preg_replace("/<style.*?<\/style>/i", " ", $post);

		// Clean UBB quote tags
		$post = preg_replace("/<\!--quotebegin-([^\-\-\>]+)-->/is", "<\!--quotebegin-->", $post);
		$post = preg_replace("/<\!--quoteebegin-->/is", "<\!--quotebegin-->", $post);
		$post = preg_replace("/<\!--quoteeend-->/is", "<\!--quoteend-->", $post);

		while (preg_match("/<\!--quotebegin-->(?!<\!--quotebegin-->).+<\!--quoteend-->/is", $post))
		{
			$post = preg_replace("/<\!--quotebegin-->(?!<\!--quotebegin-->).+<\!--quoteend-->/is", " ", $post);
		}

		$post = preg_replace("/\[quote[^\]]*\](.+?)\[\/quote\]/is", " ", $post);

		// Clean smiles
		$post = preg_replace("/<\!--emo\&(.+?)-->(.+?)<\!--endemo-->/is", "\\1", $post);
		$post = preg_replace("/:[\w\d]*:/is", " ", $post);

		// Clean UBB tags
		$post = preg_replace("/\[[^\]]*\]/is", " ", $post);

		// Clean HTTP:// prefixes
		$post = preg_replace("/(https|http|news|ftp):\/\//is", " ", $post);
		$post = preg_replace("/mailto:/is", " ", $post);

		// Clean all other HTML tags
		$post = preg_replace("/<[^>]*>/is", " ", $post);

		// Clean Escape characters
		$post = preg_replace("/\&amp;/is", "\&", $post);
		$post = preg_replace("/\&nbsp;/is", " ", $post);
		//  $post = preg_replace("/\&lt;/is", "<", $post);
		//  $post = preg_replace("/\&gt;/is", ">", $post);

		$post = preg_replace("/(&.*?;)/is", " ", $post);

		// Clean not numbers & not letters
		$post = preg_replace("/[^\d\w_]/is", " ", $post);

		// Clean multiple spaces
		$post = trim($post);
		$post = preg_replace("/\s+/is", " ", $post);
		//  $post = preg_replace("/\s{2,}/is", " ", $post);
		//  $post = preg_replace("/(^|\s)\S{1,2}(\s|$)/is", " ", $post);

		$results = explode(" ", $post);

		$word_count = count($results);

		$i    = 1;
		$uniq = array();
		$seen = array();

		foreach ($results as $item)
		{

			$length = mb_strlen($item);

			if (($length >= $ibforums->vars['min_search_word']) && ($length <= $ibforums->vars['max_search_word']))
			{
				if (!isset($seen[$item]))
				{
					$seen[$item] = 1;
					$uniq[]      = $item;
				}
				;
			}
			$i++;
		}

		return $uniq;
	}

	//##############################################################
	// End of INDEXED SEARCH Routines
	//##############################################################

	//+-------------------------------------------------
	// Check if the user is banned by IP ?
	//+-------------------------------------------------
	function is_ip_banned($ipaddr)
	{
		$addr_parts = [];
		preg_match('/(\d+)\.(\d+)\.(\d+)\.(\d+)/', $ipaddr, $addr_parts);
		array_shift($addr_parts);//удаляем всё совпадение, нужны только разбитые части
		array_walk($addr_parts, function(&$item){ $item = sprintf('(?:%s|\*)', $item);  });
		//#(?:^|\|)((?:[ip-part]|\*)\.(?:[ip-part]|\*)\.(?:[ip-part]|\*)\.(?:[ip-part]|\*))(?:$|\|)#
		$regexp = '#(?:^|\|\s*?)(' . $addr_parts[0] . '\.' . $addr_parts[1] . '\.' . $addr_parts[2] . '\.' . $addr_parts[3] . ')(?:\s*?$|\|)#';

		$res = preg_match($regexp, Ibf::app()->vars['ban_ip']);

		return $res;
	}
}

