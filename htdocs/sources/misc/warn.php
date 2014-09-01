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
|   > Warning Module
|   > Module written by Matt Mecham
|   > Date started: 16th May 2003
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new warn;

class  warn
{

	var $output = "";
	var $topic = array();
	var $forum = array();
	var $topic_id = "";
	var $forum_id = "";
	var $moderator = "";
	var $modfunc = "";
	var $mm_data = "";
	var $parser = "";

	var $can_ban = 0;
	var $can_mod_q = 0;
	var $can_rem_post = 0;
	var $times_a_day = 0;
	var $type = 'mod';

	var $warn_member = "";

	//------------------------------------------------------
	// @constructor (no, not bob the builder)
	//------------------------------------------------------

	function warn()
	{
		global $ibforums, $std, $print;

		//-------------------------------------
		// Load modules...
		//-------------------------------------

		$ibforums->lang = $std->load_words($ibforums->lang, 'lang_mod', $ibforums->lang_id);

		$this->html = $std->load_template('skin_mod');

		$this->parser = new PostParser(1);

		//-------------------------------------
		// Make sure we're a moderator...
		//-------------------------------------

		$pass = 0;

		if ($ibforums->member['id'])
		{
			if ($ibforums->member['g_access_cp'])
			{
				$pass               = 1;
				$this->can_ban      = 1;
				$this->can_mod_q    = 1;
				$this->can_rem_post = 1;
				$this->times_a_day  = -1;
				$this->type         = 'admin';

			} else
			{
				if ($ibforums->member['g_is_supmod'])
				{
					$pass               = 1;
					$this->can_ban      = $ibforums->vars['warn_gmod_ban'];
					$this->can_mod_q    = $ibforums->vars['warn_gmod_modq'];
					$this->can_rem_post = $ibforums->vars['warn_gmod_post'];
					$this->times_a_day  = intval($ibforums->vars['warn_gmod_day']);
					$this->type         = 'supmod';

				} elseif ($ibforums->member['is_mod'])
				{
					$stmt = $ibforums->db->query("SELECT forum_id FROM ibf_topics WHERE tid = '" . $ibforums->input['t'] . "'");

					if ($row = $stmt->fetch())
					{
						$stmt = $ibforums->db->query("SELECT * FROM ibf_moderators WHERE (forum_id = '" . $row['forum_id'] . "' AND (member_id='" . $ibforums->member['id'] . "' OR (is_group=1 AND group_id='" . $ibforums->member['mgroup'] . "')))");

					} else
					{
						$stmt = $ibforums->db->query("SELECT * FROM ibf_moderators WHERE (member_id='" . $ibforums->member['id'] . "' OR (is_group=1 AND group_id='" . $ibforums->member['mgroup'] . "'))");
					}

					if ($this->moderator = $stmt->fetch())
					{
						$pass               = 1;
						$this->can_ban      = $ibforums->vars['warn_mod_ban'];
						$this->can_mod_q    = $ibforums->vars['warn_mod_modq'];
						$this->can_rem_post = $ibforums->vars['warn_mod_post'];
						$this->times_a_day  = intval($ibforums->vars['warn_mod_day']);
						$this->type         = 'mod';
					}

				} else
				{
					if ($ibforums->vars['warn_show_own'] and $ibforums->member['id'] == $ibforums->input['mid'])
					{
						$pass               = 1;
						$this->can_ban      = 0;
						$this->can_mod_q    = 0;
						$this->can_rem_post = 0;
						$this->times_a_day  = 0;
						$this->type         = 'member';

					} else
					{
						$pass = 0;
					}
				}
			}
		}

		if (!$pass)
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_permission'));
		}

		if (!$ibforums->vars['warn_on'])
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_permission'));
		}

		//-------------------------------------
		// Ensure we have a valid member id
		//-------------------------------------

		$mid = intval($ibforums->input['mid']);

		if ($mid < 1)
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_such_user'));
		}

		$stmt = $ibforums->db->query("SELECT m.*, g.*
		    FROM ibf_members m
		    LEFT JOIN ibf_groups g on (m.mgroup=g.g_id)
		    WHERE id='" . $mid . "'");

		$this->warn_member = $stmt->fetch();

		if (!$this->warn_member['id'])
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_such_user'));
		}

		if ($ibforums->input['CODE'] == "" OR $ibforums->input['CODE'] == "dowarn")
		{
			//-------------------------------------
			// Protected member? Really? o_O
			//-------------------------------------

			if (stristr($ibforums->vars['warn_protected'], ',' . $this->warn_member['mgroup'] . ','))
			{
				$std->Error(array('LEVEL' => 1, 'MSG' => 'protected_user'));
			}

			//-------------------------------------
			// I've already warned you!!
			//-------------------------------------

			if ($this->times_a_day > 0)
			{
				$time_to_check = time() - 86400;

				$stmt = $ibforums->db->query("SELECT * FROM ibf_warn_logs WHERE wlog_mid={$this->warn_member['id']} AND wlog_date > $time_to_check");

				if ($stmt->rowCount() >= $this->times_a_day)
				{
					$std->Error(array('LEVEL' => 1, 'MSG' => 'warned_already'));
				}
			}
		}

		//-------------------------------------
		// Bouncy, bouncy!
		//-------------------------------------

		switch ($ibforums->input['CODE'])
		{

			case 'dowarn':
				$this->do_warn();
				break;

			case 'view':
				$this->view_log();
				break;

			default:
				$this->show_form();
				break;
		}

		if (count($this->nav) < 1)
		{
			$this->nav[] = $ibforums->lang['w_title'];
		}

		if (!$this->page_title)
		{
			$this->page_title = $ibforums->lang['w_title'];
		}

		$print->add_output($this->output);

		$print->do_output(array('TITLE' => $this->page_title, 'NAV' => $this->nav));

	}

	//-------------------------------------------------
	// Show logs
	//-------------------------------------------------

	function view_log()
	{
		global $std, $ibforums, $print;

		//-------------------------------------
		// Protected member? Really? o_O
		//-------------------------------------

		if (stristr($ibforums->vars['warn_protected'], ',' . $this->warn_member['mgroup'] . ','))
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'protected_user'));
		}

		// Song * new warning system

		if ($this->warn_member['id'] == $ibforums->member['id'])
		{
			$ibforums->db->exec("UPDATE ibf_members SET is_new_warn_exixts=0 WHERE id='" . $this->warn_member['id'] . "'");
		}
		// Song
		$perpage = 50;

		$start = intval($ibforums->input['st']);

		$stmt = $ibforums->db->query("SELECT count(*) as cnt FROM ibf_warn_logs WHERE wlog_mid={$this->warn_member['id']}");

		$row = $stmt->fetch();

		$links = $std->build_pagelinks(array(
		                                    'TOTAL_POSS' => $row['cnt'],
		                                    'PER_PAGE'   => $perpage,
		                                    'CUR_ST_VAL' => $ibforums->input['st'],
		                                    'L_SINGLE'   => "",
		                                    'L_MULTI'    => $ibforums->lang['w_v_pages'],
		                                    'BASE_URL'   => $this->base_url . "act=warn&amp;CODE=view&amp;mid={$this->warn_member['id']}",
		                               ));

		$this->output .= $this->html->warn_view_header($this->warn_member['id'], $this->warn_member['name'], $links);

		if ($row['cnt'] < 1)
		{
			$this->output .= $this->html->warn_view_none();
		} else
		{
			$stmt = $ibforums->db->query("SELECT l.*,  p.id as punisher_id, p.name as punisher_name
					     FROM ibf_warn_logs l
					      LEFT JOIN ibf_members p ON ( p.id=l.wlog_addedby )
					    WHERE l.wlog_mid={$this->warn_member['id']} ORDER BY l.wlog_date DESC LIMIT $start, $perpage");

			while ($r = $stmt->fetch())
			{
				$date = $std->get_date($r['wlog_date']);

				$raw = preg_match("#<content>(.+?)</content>#is", $r['wlog_notes'], $match);

				$content = $this->parser->prepare(array(
				                                       'TEXT'    => $match[1],
				                                       'SMILIES' => 1,
				                                       'CODE'    => 1,
				                                       'HTML'    => 0
				                                  ));

				$puni_name = $std->make_profile_link($r['punisher_name'], $r['punisher_id']);

				if ($r['wlog_type'] == 'pos')
				{
					$this->output .= $this->html->warn_view_positive_row($date, $content, $puni_name);

				} elseif ($r['wlog_type'] == 'null')
				{
					$this->output .= $this->html->warn_view_null_row($date, $content, $puni_name);

				} else
				{
					$this->output .= $this->html->warn_view_negative_row($date, $content, $puni_name);
				}

			}
		}

		$this->output .= $this->html->warn_view_footer();

		$print->pop_up_window("WARN", $this->output);

	}

	//-------------------------------------------------
	// Do the actual warny-e-poos
	//-------------------------------------------------

	function do_warn()
	{
		global $std, $ibforums, $print;

		require_once ROOT_PATH . "/sources/lib/emailer.php";
		$this->email = new emailer();

		$save = array();

		if ($this->type == 'member')
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_permission'));
		}

		$fid = intval($ibforums->input['f']);
		$pid = intval($ibforums->input['p']);

		if (!$fid)
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'incorrect_use'));
		}

		// Song * new time warning system

		if ($ibforums->input['level'] == 'add')
		{
			$restrict_time = $ibforums->input['time'];

			if (!$restrict_time)
			{
				$std->Error(array('LEVEL' => 1, 'MSG' => 'incorrect_use'));
			}

			if (!$pid)
			{
				$std->Error(array('LEVEL' => 1, 'MSG' => 'incorrect_use'));
			}

			// Song * post pid for that violation be, 15.11.2004

			if (!$ibforums->member['g_is_supmod'])
			{
				$stmt = $ibforums->db->query("SELECT m.id,m.name FROM ibf_members m, ibf_warn_logs wl WHERE wl.pid='" . $pid . "'
					and m.id=wl.wlog_addedby and wlog_type != 'pos' LIMIT 1");

				if ($moderator = $stmt->fetch())
				{
					$std->error(array(
					                 'LEVEL' => 1,
					                 'MSG'   => "warn_pid",
					                 'EXTRA' => "<a href='{$ibforums->base_url}showuser={$moderator['id']}'>{$moderator['name']}</a>"
					            ));
				}
			}

			// Song * post pid for that violation be, 15.11.2004

		}

		// Song * new time warning system

		$err = "";

		if (!$ibforums->vars['warn_past_max'])
		{
			$ibforums->vars['warn_min'] = $ibforums->vars['warn_min']
				? $ibforums->vars['warn_min']
				: 0;

			$ibforums->vars['warn_max'] = $ibforums->vars['warn_max']
				? $ibforums->vars['warn_max']
				: 10;

			$warn_level = intval($this->warn_member['warn_level']);

			if ($ibforums->input['level'] == 'add')
			{
				if ($warn_level >= $ibforums->vars['warn_max'])
				{
					$err = 1;
				}
			} elseif ($ibforums->input['level'] == 'remove')
			{
				if ($warn_level <= $ibforums->vars['warn_min'])
				{
					$err = 1;
				}
			}

			if ($err == 1)
			{
				$std->Error(array('LEVEL' => '1', 'MSG' => 'no_warn_max'));
			}
		}

		//-------------------------------------------------
		// Check security fang
		//-------------------------------------------------

		if ($ibforums->input['key'] != $std->return_md5_check())
		{
			$std->Error(array('LEVEL' => '1', 'MSG' => 'del_post'));
		}

		//-------------------------------------------------
		// As Celine Dion once squawked, "Show me the reason"
		//-------------------------------------------------

		if (trim($ibforums->input['reason']) == "")
		{
			$this->show_form('we_no_reason');
			return;
		}

		//-------------------------------------------------
		// Plussy - minussy?
		//-------------------------------------------------

		if ($ibforums->input['level'] == 'add')
		{
			$save['wlog_type'] = "neg";

		} elseif ($ibforums->input['level'] == 'null')
		{
			$save['wlog_type'] = "null";
		} else
		{
			$save['wlog_type'] = "pos";
		}

		$save['wlog_date'] = time();

		//-------------------------------------------------
		// Contacting the member?
		//-------------------------------------------------

		if ($ibforums->input['contact'] != "")
		{
			$save['wlog_contact'] = $ibforums->input['contactmethod'];

			$save['wlog_contact_content'] = "<subject>{$ibforums->input['subject']}</subject><content>{$ibforums->input['contact']}</content>";

			if (trim($ibforums->input['subject']) == "")
			{
				$this->show_form('we_no_subject');
				return;
			}

			if ($ibforums->input['contactmethod'] == 'email')
			{
				//----------------------------------
				// Send the email
				//----------------------------------

				$this->email->get_template("email_member");

				$this->email->build_message(array(
				                                 'MESSAGE'     => str_replace("<br>", "\n", str_replace("\r", "", $ibforums->input['contact'])),
				                                 'MEMBER_NAME' => $this->warn_member['name'],
				                                 'FROM_NAME'   => $ibforums->member['name']
				                            ));

				$this->email->subject = $ibforums->input['subject'];
				$this->email->to      = $this->warn_member['email'];
				$this->email->from    = $ibforums->member['email'];
				$this->email->send_mail();
			} else
			{
				//----------------------------------
				// PM :o
				//----------------------------------

				$show_popup = $this->warn_member['view_pop'];

				//--------------------------------------
				// Enter the info into the DB
				// Target user side.
				//--------------------------------------

				//todo Перенести отправку ЛС, а может и всю систему добавления/удаления наказаний в модель участника
				$data = [
					'member_id'    => $this->warn_member['id'],
					'msg_date'     => time(),
					'read_state'   => '0',
					'title'        => $ibforums->input['subject'],
					'message' => $this->parser->convert(
						[
							'TEXT'    => $std->remove_tags($ibforums->input['contact']),
							'SMILIES' => 1,
							'CODE'    => 0,
							'HTML'    => 0,
						]
					),
					'from_id'      => $ibforums->member['id'],
					'vid'          => 'in',
					'recipient_id' => $this->warn_member['id'],
					'tracking'     => 0,
				];

				$ibforums->db->insertRow("ibf_messages", $data);
				$new_id = $ibforums->db->lastInsertId();

				//-----------------------------------------------------

				$ibforums->db->exec("UPDATE ibf_members SET " . "msg_total = msg_total + 1, " . "new_msg = new_msg + 1, " . "msg_from_id='" . $ibforums->member['id'] . "', " . "msg_msg_id='" . $new_id . "', " . "show_popup='" . $show_popup . "' " . "WHERE id='" . $this->warn_member['id'] . "'");

				//-----------------------------------------------------
				// Has this member requested a PM email nofity?
				//-----------------------------------------------------

				if ($this->warn_member['email_pm'] == 1)
				{
					$this->warn_member['language'] = $this->warn_member['language'] == ""
						? 'en'
						: $this->warn_member['language'];

					$this->email->get_template("pm_notify", $this->warn_member['language']);

					$this->email->build_message(array(
					                                 'NAME'   => $this->warn_member['name'],
					                                 'POSTER' => $ibforums->member['name'],
					                                 'TITLE'  => $ibforums->input['subject'],
					                                 'LINK'   => "?act=Msg&CODE=03&VID=in&MSID=$new_id",
					                            ));

					$this->email->subject = $ibforums->lang['pm_email_subject'];
					$this->email->to      = $this->warn_member['email'];
					$this->email->send_mail();
				}
			}
		}

		if ($ibforums->input['level'] != 'null')
		{

			//-------------------------------------------------
			// Right - is we banned or wha?
			//-------------------------------------------------

			$restrict_post = '';
			$mod_queue     = '';
			$susp          = '';

			if ($ibforums->input['level'] == 'add')
			{
				$ibforums->input['reason'] .= "\r\nСрок действия предупреждения: " . $restrict_time . " дней(я).";
			}

			if ($ibforums->input['mod_indef'] == 1)
			{
				$mod_queue = 1;

			} elseif ($ibforums->input['mod_value'] > 0)
			{
				$mod_queue = $std->hdl_ban_line(array(
				                                     'timespan' => intval($ibforums->input['mod_value']),
				                                     'unit'     => $ibforums->input['mod_unit']
				                                ));
			}

			if ($ibforums->input['post_indef'] == 1)
			{
				$restrict_post = 1;

			} elseif ($ibforums->input['post_value'] > 0)
			{
				$restrict_post = $std->hdl_ban_line(array(
				                                         'timespan' => intval($ibforums->input['post_value']),
				                                         'unit'     => $ibforums->input['post_unit']
				                                    ));
			}

			if ($ibforums->input['ban_indef'] == 1)
			{
				$susp = 1;

			} elseif ($ibforums->input['susp_value'] > 0)
			{
				$susp = $std->hdl_ban_line(array(
				                                'timespan' => intval($ibforums->input['susp_value']),
				                                'unit'     => $ibforums->input['susp_unit']
				                           ));
			}
		}

		$save['wlog_mid']     = $this->warn_member['id'];
		$save['wlog_addedby'] = $ibforums->member['id'];

		//-------------------------------------------------
		// Enter into warn loggy poos (eeew - poo)
		//-------------------------------------------------

		// Song * new time warning system

		if ($ibforums->input['level'] != 'null')
		{
			$warn_level = intval($this->warn_member['warn_level']);

			// Song * ban group, 09.03.05

			// if ban checkbox is dropped, reset group to reset ban
			if ($this->warn_member['mgroup'] == $ibforums->vars['ban_group'] and
			    $ibforums->member['g_is_supmod'] and !$ibforums->input['ban']
			)
			{
				$this->warn_member['mgroup'] = "";
			}

			// Song * ban group, 09.03.05

			if ($this->warn_member['mgroup'] == $ibforums->vars['ban_group'])
			{
				$group = $ibforums->vars['ban_group'];
			} else
			{
				if ($ibforums->input['level'] == 'add')
				{
					// increase level
					$warn_level++;

					// define time when a user will be freed
					$restrict_time = $restrict_time * 24 * 60 * 60;

					$dbs = [
						'mid'          => $this->warn_member['id'],
						'level'        => $warn_level,
						'RestrictDate' => time() + $restrict_time,
					];

					$ibforums->db->insertRow("ibf_warnings", $dbs);

					// update old warnings with new time
					if ($warn_level > 1)
					{
						$ibforums->db->exec("UPDATE ibf_warnings SET RestrictDate=RestrictDate+" . $restrict_time . "
					    WHERE mid='" . $this->warn_member['id'] . "' and level<" . $warn_level);
					}

					// define a new group of user
					// gr. 5 = ban

					if ($warn_level == $ibforums->vars['warn_max'])
					{
						$group = $ibforums->vars['ban_group'];
					} else
					{
						$group = 15 + $warn_level;
					}
				} else
				{
					// delete violation

					$ibforums->db->exec("DELETE FROM ibf_warnings WHERE mid='" . $this->warn_member['id'] . "' and level='" . $warn_level . "'");
					$warn_level--;

					// define a new group of user
					// gr. 3 = group of ussual user

					if ($warn_level > 0)
					{
						$group = 15 + $warn_level;

					} else
					{
						$group = $this->warn_member['old_group'];
					}
				}
			}

			// Song * new time warning system

			// Song * ban group, 09.03.05

			// admins only
			if ($ibforums->member['g_is_supmod'] and $ibforums->input['ban'])
			{
				$group = $ibforums->vars['ban_group'];
			}

			// Song * ban group, 09.03.05

			if ($ibforums->input['level'] == 'add')
			{
				$mes = "\nЗа сообщение: {$ibforums->base_url}showtopic={$ibforums->input['t']}&view=findpost&p={$pid}\n\n";
			} else
			{
				$mes = "\n\n";
			}

			if ($this->warn_member['mgroup'] != $ibforums->vars['ban_group'])
			{
				if ($group == $ibforums->vars['ban_group'])
				{
					$mes .= "[color=red][b]Вы набрали максимальное количество предупреждений и переведены в группу БАН.[/b][/color]";

				} elseif ($group == 3 or $group == 25 or $group == 26 or $group == 9)
				{
					$mes .= "[color=green]Вы обратно переведены в группу участников.[/color]";
				} else
				{
					$mes .= "[color=red]Вы переведены в группу нарушивших правила уровня " . $warn_level . ".[/color]";
				}
			}

			if ($warn_level > 1 and $ibforums->input['level'] == 'add')
			{
				$mes .= "\nСрок действия всех предупреждений, выданных Вам ранее, будет увеличен на срок действия данного.";
			}

			$ibforums->input['reason'] .= $mes;

		} else
		{
			$mes = "Вы получили устное предупреждение от модератора:\n";
			$mes .= $ibforums->input['reason'];
			$mes .= "\nЗа сообщение: {$ibforums->base_url}showtopic={$ibforums->input['t']}&view=findpost&p={$pid}\n\n";
			$ibforums->input['reason'] = $mes;
		}

		// Song * convert links

		$ibforums->input['reason'] = $this->parser->macro($ibforums->input['reason']);

		// Song * convert links

		// Song * post pid for that violation be, 15.11.2004

		$save['pid'] = $pid;

		// Song * post pid for that violation be, 15.11.2004

		$save['wlog_notes'] = "<content>{$ibforums->input['reason']}</content>";
		$save['wlog_notes'] .= "<mod>{$ibforums->input['mod_value']},{$ibforums->input['mod_unit']},{$ibforums->input['mod_indef']}</mod>";
		$save['wlog_notes'] .= "<post>{$ibforums->input['post_value']},{$ibforums->input['post_unit']},{$ibforums->input['post_indef']} </post>";
		$save['wlog_notes'] .= "<susp>{$ibforums->input['susp_value']},{$ibforums->input['susp_unit']}</susp>";

		// update warn logs
		$ibforums->db->insertRow("ibf_warn_logs", $save);

		if ($ibforums->input['level'] != 'null')
		{
			if ($warn_level > $ibforums->vars['warn_max'])
			{
				$warn_level = $ibforums->vars['warn_max'];
			}

			if ($warn_level < 1)
			{
				$warn_level = 0;
			}

			//-------------------------------------------------
			// Update member
			//-------------------------------------------------

			$ibforums->db->exec("UPDATE ibf_members SET mgroup='" . $group . "',
						   warn_level='" . $warn_level . "',
						   warn_lastwarn='" . time() . "',
						   is_new_warn_exixts=1
			    WHERE id='" . $this->warn_member['id'] . "'");

			// Song * new ban control

			// Delete old data, may be after we will insert it again, may be no
			$ibforums->db->exec("DELETE FROM ibf_preview_user WHERE mid='" . $this->warn_member['id'] . "' and fid='" . $fid . "'");

			$main   = "";
			$forum  = "";
			$forum2 = "";

			if ($mod_queue or $restrict_post or $susp)
			{
				if ($ibforums->member['g_is_supmod'])
				{
					if ($ibforums->input['RCanMod'])
					{
						$main .= "mod_posts='" . $mod_queue . "',";
					} else
					{
						if (!$mod_queue)
						{
							$forum .= "mod_posts=NULL,";
						} else
						{
							$forum2 .= "mod_posts='" . $mod_queue . "',";
							$forum .= "mod_posts='" . $mod_queue . "',";
						}
					}

					if ($ibforums->input['RCanRemPost'])
					{
						$main .= "restrict_post='" . $restrict_post . "',";
					} else
					{
						if (!$restrict_post)
						{
							$forum .= "restrict_posts=NULL,";
						} else
						{
							$forum2 .= "restrict_posts='" . $restrict_post . "',";
							$forum .= "restrict_posts='" . $restrict_post . "',";
						}
					}

					if ($ibforums->input['RCanBan'])
					{
						$main .= "temp_ban='" . $susp . "',";
					} else
					{
						if (!$susp)
						{
							$forum .= "temp_ban=NULL,";
						} else
						{
							$forum2 .= "temp_ban='" . $susp . "',";
							$forum .= "temp_ban='" . $susp . "',";
						}
					}

				} else
				{
					if (!$mod_queue)
					{
						$forum .= "mod_posts=NULL,";
					} else
					{
						$forum2 .= "mod_posts='" . $mod_queue . "',";
						$forum .= "mod_posts='" . $mod_queue . "',";
					}

					if (!$restrict_post)
					{
						$forum .= "restrict_posts=NULL,";
					} else
					{
						$forum2 .= "restrict_posts='" . $restrict_post . "',";
						$forum .= "restrict_posts='" . $restrict_post . "',";
					}

					if (!$susp)
					{
						$forum .= "temp_ban=NULL,";
					} else
					{
						$forum2 .= "temp_ban='" . $susp . "',";
						$forum .= "temp_ban='" . $susp . "',";
					}
				}
			}

			//	for gloabal forum

			if ($main)
			{
				$main = mb_substr($main, 0, mb_strlen($main) - 1);
				$ibforums->db->exec("UPDATE ibf_members SET " . $main . " WHERE id='" . $this->warn_member['id'] . "'");
			}

			//       for one forum only

			if ($forum2)
			{
				$forum = mb_substr($forum, 0, mb_strlen($forum) - 1);
				$ibforums->db->exec("INSERT INTO ibf_preview_user SET mid='" . $this->warn_member['id'] . "',fid='" . $fid . "'," . $forum);
			}
		} else
		{
			$ibforums->db->exec("UPDATE ibf_members SET warn_lastwarn='" . time() . "',
						   is_new_warn_exixts=1
			    WHERE id='" . $this->warn_member['id'] . "'");
		}

		// Song * new ban control

		//-------------------------------------------------
		// Now what? Show success screen, that's what!!
		//-------------------------------------------------

		$ibforums->lang['w_done_te'] = sprintf($ibforums->lang['w_done_te'], $this->warn_member['name']);

		$this->output .= $this->html->warn_success();

		// Did we have a topic? eh! eh!! EH!

		$tid = intval($ibforums->input['t']);

		if ($tid > 0)
		{
			$stmt = $ibforums->db->query("SELECT t.tid, t.title, f.id, f.name FROM ibf_topics t, ibf_forums f WHERE tid=$tid AND t.forum_id=f.id");

			$topic = $stmt->fetch();

			$this->output = str_replace("<!--IBF.FORUM_TOPIC-->", $this->html->warn_success_forum($topic['id'], $topic['name'], $topic['tid'], $pid, $topic['title']), $this->output);
		}
	}

	//-------------------------------------------------
	// Show form
	//-------------------------------------------------

	function show_form($errors = "")
	{
		global $std, $ibforums, $print;

		if ($this->type == 'member')
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_permission'));
		}

		$key = $std->return_md5_check();

		if ($errors)
		{
			$this->output .= $this->html->warn_errors($ibforums->lang[$errors]);
		}

		$fid = intval($ibforums->input['f']);
		$pid = intval($ibforums->input['p']);

		if (!$fid or !$pid)
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'incorrect_use'));
		}

		// Song * post pid for that violation be, 15.11.2004

		if (!$ibforums->member['g_is_supmod'] and $ibforums->input['type'] == "add")
		{
			$stmt = $ibforums->db->query("SELECT m.id,m.name FROM ibf_members m, ibf_warn_logs wl WHERE wl.pid='" . $pid . "'
				and m.id=wl.wlog_addedby and wlog_type != 'pos' LIMIT 1");

			if ($moderator = $stmt->fetch())
			{
				$std->error(array(
				                 'LEVEL' => 1,
				                 'MSG'   => "warn_pid",
				                 'EXTRA' => "<a href='{$ibforums->base_url}showuser={$moderator['id']}'>{$moderator['name']}</a>"
				            ));
			}
		}

		// Song * post pid for that violation be, 15.11.2004

		$stmt = $ibforums->db->query("SELECT mod_posts,restrict_posts,temp_ban FROM ibf_preview_user WHERE mid='" . $this->warn_member['id'] . "' and fid='" . $fid . "'");

		$row = $stmt->fetch();

		$type = array('minus' => "", 'add' => "");

		if ($ibforums->input['type'] == 'minus')
		{
			$type['minus'] = 'checked="checked"';
		} else
		{
			$type['add'] = 'checked="checked"';
		}

		$this->output .= $this->html->warn_header($this->warn_member['id'], $this->warn_member['name'], intval($this->warn_member['warn_level']), $ibforums->vars['warn_min'], $ibforums->vars['warn_max'], $key, $fid, intval($ibforums->input['t']), $pid, intval($ibforums->input['st']), $type);

		if ($ibforums->member['g_is_supmod'])
		{
			$stmt = $ibforums->db->query("SELECT name FROM ibf_forums WHERE id='" . $fid . "'");

			$forum = $stmt->fetch();

			$this->output .= "<td class='pformright' valign='bottom'>{$ibforums->lang['region']}</td></tr>";

		} else
		{
			$this->output .= "</tr>";
		}

		if ($this->can_mod_q)
		{
			$mod_tick = 0;
			$mod_arr  = array();

			if ($row['mod_posts'] == 1)
			{
				$mod_tick = 'checked';

			} elseif ($row['mod_posts'] > 0)
			{
				$mod_arr = $std->hdl_ban_line($row['mod_posts']);

				$hours = ceil(($mod_arr['date_end'] - time()) / 3600);

				if ($hours > 24 and (($hours / 24) == ceil($hours / 24)))
				{
					$mod_arr['days']     = 'selected="selected"';
					$mod_arr['timespan'] = $hours / 24;
				} else
				{
					$mod_arr['hours']    = 'selected="selected"';
					$mod_arr['timespan'] = $hours;
				}

				$mod_extra = $this->html->warn_restricition_in_place();
			}

			$this->output .= $this->html->warn_mod_posts($mod_tick, $mod_arr, $mod_extra);

			if ($ibforums->member['g_is_supmod'])
			{
				$this->output .= $this->html->add_radio_buttons('RCanMod', $forum['name']);
			} else
			{
				$this->output .= "</tr>";
			}
		}

		if ($this->can_rem_post)
		{
			$post_tick = 0;
			$post_arr  = array();

			if ($row['restrict_posts'] == 1)
			{
				$post_tick = 'checked';

			} elseif ($row['restrict_posts'] > 0)
			{
				$post_arr = $std->hdl_ban_line($row['restrict_posts']);

				$hours = ceil(($post_arr['date_end'] - time()) / 3600);

				if ($hours > 24 and (($hours / 24) == ceil($hours / 24)))
				{
					$post_arr['days']     = 'selected="selected"';
					$post_arr['timespan'] = $hours / 24;
				} else
				{
					$post_arr['hours']    = 'selected="selected"';
					$post_arr['timespan'] = $hours;
				}

				$post_extra = $this->html->warn_restricition_in_place();
			}

			$this->output .= $this->html->warn_rem_posts($post_tick, $post_arr, $post_extra);

			if ($ibforums->member['g_is_supmod'])
			{
				$this->output .= $this->html->add_radio_buttons('RCanRemPost', $forum['name']);
			} else
			{
				$this->output .= "</tr>";
			}

		}

		if ($this->can_ban)
		{
			$ban_arr = array();

			if ($row['temp_ban'] == 1)
			{
				$ban_tick = 'checked';

			} elseif ($row['temp_ban'])
			{
				$ban_arr = $std->hdl_ban_line($row['temp_ban']);

				$hours = ceil(($ban_arr['date_end'] - time()) / 3600);

				if ($hours > 24 and (($hours / 24) == ceil($hours / 24)))
				{
					$ban_arr['days']     = 'selected="selected"';
					$ban_arr['timespan'] = $hours / 24;
				} else
				{
					$ban_arr['hours']    = 'selected="selected"';
					$ban_arr['timespan'] = $hours;
				}

				$ban_extra = $this->html->warn_restricition_in_place();
			}

			$this->output .= $this->html->warn_suspend($ban_tick, $ban_arr, $ban_extra);

			if ($ibforums->member['g_is_supmod'])
			{
				$this->output .= $this->html->add_radio_buttons('RCanBan', $forum['name']);
			} else
			{
				$this->output .= "</tr>";
			}
		}

		// Song * ban group, 09.03.05

		if ($ibforums->member['g_is_supmod'])
		{
			$ban = ($this->warn_member['mgroup'] == $ibforums->vars['ban_group'])
				? " checked='checked'"
				: "";

			$this->output .= $this->html->warn_ban_group($ban);
		}

		// Song * ban group, 09.03.05

		if ($ibforums->input['type'] == "add")
		{
			$this->output .= $this->html->warn_time();
		}

		$ibforums->input['subject'] = "Вам вынесено предупреждение от модератора";

		$this->output .= $this->html->warn_footer($this->html->lazy_combobox());

	}

}

