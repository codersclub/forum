<?php

$idx = new Reputation;

class Reputation
{

	var $message = "";
	var $parser = "";

	function Reputation()
	{
		global $ibforums, $std;

		if (!$ibforums->member['id'])
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'not_registered'));
		}

		$this->parser = new PostParser();

		$this->parser->prepareIcons();

		$ibforums->lang = $std->load_words($ibforums->lang, 'lang_rep', $ibforums->lang_id);

		if (!$ibforums->vars['rep_per_page'])
		{
			$ibforums->vars['rep_per_page'] = 30;
		}

		if (!$ibforums->vars['rep_msg_length'])
		{
			$ibforums->vars['rep_msg_length'] = 0;
		}

		$ibforums->input['mid'] = intval($ibforums->input['mid']);

		$ibforums->input['f'] = intval($ibforums->input['f']);

		$ibforums->input['t'] = intval($ibforums->input['t']);

		$ibforums->input['p'] = intval($ibforums->input['p']);

		if ($ibforums->input['CODE'] != 'totals')
		{
			if ($ibforums->input['CODE'] == '01' or $ibforums->input['CODE'] == '02')
			{
				if (!$ibforums->input['mid'])
				{
					$std->Error(array('LEVEL' => 1, 'MSG' => 'missing_files'));
				}

				$stmt = $ibforums->db->query("SELECT name FROM ibf_members WHERE id='" . $ibforums->input['mid'] . "'");

				if (!$stmt->rowCount())
				{
					$std->Error(array('LEVEL' => 1, 'MSG' => 'missing_files'));
				} else
				{
					$r = $stmt->fetch();

					$ibforums->who_name = $r['name'];
				}
			}
		}

		if ($ibforums->input['CODE'] == '01' OR $ibforums->input['CODE'] == '02')
		{
			if (!$ibforums->member['allow_rep'])
			{
				$std->Error(array('LEVEL' => 1, 'MSG' => 'rep_cantchange'));
			}

			if (!$ibforums->input['f'])
			{
				$std->Error(array('LEVEL' => 1, 'MSG' => 'missing_files'));
			}

			if (!$ibforums->input['t'])
			{
				$std->Error(array('LEVEL' => 1, 'MSG' => 'missing_files'));
			}

			if (!$ibforums->input['p'])
			{
				$std->Error(array('LEVEL' => 1, 'MSG' => 'missing_files'));
			}

			$stmt = $ibforums->db->query("SELECT msg_date FROM ibf_reputation WHERE member_id='" . $ibforums->input['mid'] . "' AND
			    from_id='" . $ibforums->member['id'] . "' ORDER BY msg_date DESC LIMIT 1");

			if ($info = $stmt->fetch())
			{
				$ktime = $ibforums->vars['rep_time'];

				if (time() - $info['msg_date'] < 60 * 60 * 24 * $ktime)
				{
					$std->Error(array('LEVEL' => 1, 'MSG' => 'rep_early', 'EXTRA' => $ktime));
				}
			}
		}

		// enter reason
		if (!$ibforums->input['process'])
		{
			$this->show_form(intval($ibforums->input['mid']));
		} else
		{
			// or process ratting

			if (!$ibforums->input['message'])
			{
				$std->Error(array('LEVEL' => 1, 'MSG' => 'stf_no_msg'));
			}

			if ($ibforums->input['CODE'] == 'totals')
			{
				$std->Error(array('LEVEL' => 1, 'MSG' => 'missing_files'));
			}

			if (!($ibforums->input['CODE'] == '01' or $ibforums->input['CODE'] == '02'))
			{
				$std->Error(array('LEVEL' => 1, 'MSG' => 'missing_files'));
			}

			if ($ibforums->member['id'] == $ibforums->input['mid'])
			{
				$std->Error(array('LEVEL' => 1, 'MSG' => 'rep_self'));
			}

			if ($ibforums->input['CODE'] == '02' and $ibforums->member['posts'] < $ibforums->vars['rep_posts'])
			{
				$std->Error(array('LEVEL' => 1, 'MSG' => 'rep_noposts', 'EXTRA' => $ibforums->vars['rep_posts']));
			}

			$this->add_why($ibforums->input['mid']);

			switch ($ibforums->input['CODE'])
			{
				case '01':

					$this->add_rep($ibforums->input['mid']);
					break;

				case '02':

					$this->remove_rep($ibforums->input['mid']);
					break;
			}
		}
	}

	// --------------------------------------------------------------------------------
	// Utility Functions
	// --------------------------------------------------------------------------------
	function update_rep($new, $memid, $field)
	{

		$ibforums = Ibf::app();

		if (time() - $ibforums->lastclick > 2)
		{
			$ibforums->db->exec("UPDATE ibf_members SET {$field}='" . $new . "' WHERE id='" . $memid . "'");
		}
	}

	function get_rep($memid, &$field)
	{
		global $ibforums, $std;

		$stmt = $ibforums->db->query("SELECT inc_postcount FROM ibf_forums WHERE id='" . intval($ibforums->input['f']) . "'");

		if (!$row = $stmt->fetch())
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'missing_files'));
		}

		$field = ($row['inc_postcount'])
			? "rep"
			: "ratting";

		$stmt = $ibforums->db->query("SELECT {$field} as rep FROM ibf_members WHERE id='" . $memid . "'");

		$info = $stmt->fetch();

		return $info['rep'];
	}

	function add_rep($memid = 0)
	{
		global $ibforums, $std, $print;

		if ($ibforums->member['id'] != $memid)
		{
			$field = "";

			$level = $this->get_rep($memid, $field);

			if (!$field)
			{
				$std->Error(array('LEVEL' => 1, 'MSG' => 'missing_files'));
			}

			if (empty($level))
			{
				$level = 0;
			}

			$this->update_rep($level + 1, $memid, $field);

			$ratting_type = ($field == "rep")
				? "t"
				: "f";

			$print->redirect_screen("", "act=rep&amp;CODE=03&amp;type={$ratting_type}&amp;mid=" . $memid);
		} else
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'rep_self'));
		}
	}

	function remove_rep($memid = 0)
	{
		global $ibforums, $std, $print;

		if ($ibforums->member['id'] != $memid)
		{
			$field = "";

			$level = $this->get_rep($memid, $field);

			if (!$field)
			{
				$std->Error(array('LEVEL' => 1, 'MSG' => 'missing_files'));
			}

			if (empty($level))
			{
				$level = 0;
			}

			$this->update_rep($level - 1, $memid, $field);

			$ratting_type = ($field == "rep")
				? "t"
				: "f";

			$print->redirect_screen("", "act=rep&amp;CODE=03&amp;type={$ratting_type}&amp;mid=" . $memid);
		} else
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'rep_self'));
		}
	}

	// Song * silent check correct parameters

	function check_it_out()
	{
		global $ibforums, $std;

		if (!$ibforums->input['p'] or !$ibforums->input['mid'] or !$ibforums->input['t'] or !$ibforums->input['f'])
		{
			return;
		}

		$stmt = $ibforums->db->query("SELECT p.author_id, p.forum_id, p.topic_id, t.title, m.name as member_name, f.name as forum_name
		FROM ibf_posts p, ibf_topics t, ibf_members m, ibf_forums f WHERE
		p.pid='" . $ibforums->input['p'] . "' and t.tid=p.topic_id and m.id=p.author_id and f.id=p.forum_id");

		if ($check = $stmt->fetch())
		{
			if ($check['author_id'] != $ibforums->input['mid'] or $check['forum_id'] != $ibforums->input['f'] or
			    $check['topic_id'] != $ibforums->input['t']
			)
			{
				$mes = "Участником [URL={$ibforums->base_url}showuser={$ibforums->member['id']}]{$ibforums->member['name']}[/URL] ";
				$mes .= "была предпринята попытка подделать адресную строку ссылки изменения рейтинга.\r\n";
				$mes .= "\r\n";

				$mes .= "<b><u>Правильные данные</u></b>:\r\n";
				$mes .= "Пост #{$ibforums->input['p']}: [URL={$ibforums->base_url}showtopic={$check['topic_id']}&view=findpost&p={$ibforums->input['p']}]#{$ibforums->input['p']}[/URL]\r\n";
				$mes .= "Топик #{$check['topic_id']}: [URL={$ibforums->base_url}showtopic={$check['topic_id']}]{$check['title']}[/URL]\r\n";
				$mes .= "Раздел #{$check['forum_id']}: [URL={$ibforums->base_url}showforum={$check['forum_id']}]{$check['forum_name']}[/URL]\r\n";
				$mes .= "Участник #{$check['author_id']}: [URL={$ibforums->base_url}showuser={$check['author_id']}]{$check['member_name']}[/URL]\r\n";
				$mes .= "\r\n";

				$mes .= "<b><u>Данные переданной пользовательской формы</u></b>:\r\n";
				$mes .= "Пост #{$ibforums->input['p']}: [URL={$ibforums->base_url}showtopic={$ibforums->input['t']}&view=findpost&p={$ibforums->input['p']}]#{$ibforums->input['p']}[/URL]\r\n";
				$mes .= "Топик #{$ibforums->input['t']}: [URL={$ibforums->base_url}showtopic={$ibforums->input['t']}]#{$ibforums->input['t']}[/URL]\r\n";
				$mes .= "Раздел #{$ibforums->input['f']}: [URL={$ibforums->base_url}showforum={$ibforums->input['f']}]#{$ibforums->input['f']}[/URL]\r\n";
				$mes .= "Участник #{$ibforums->input['mid']}: [URL={$ibforums->base_url}showuser={$ibforums->input['mid']}]#{$ibforums->input['mid']}[/URL]\r\n";
				$mes .= "\r\n";

				$mes .= "Дата записи выставления данного рейтинга в таблице рейтинга участника [URL={$ibforums->base_url}showuser={$check['author_id']}]{$check['member_name']}[/URL] ";
				$mes .= $std->get_date(time()) . ".\r\n\r\n";
				$mes .= "Проверьте несовпадение всех перечисленных параметров и действуйте в соответствии.\r\n";
				$mes .= "Данное сообщение направлено <b>всем</b> модераторам раздела ";
				$mes .= "[URL={$ibforums->base_url}showforum={$check['forum_id']}]{$check['forum_name']}[/URL], поэтому ";
				$mes .= "действуйте скоординированно.\r\n\r\n";
				$mes .= "Письмо сгенирировано ботом Форума на Исходниках.RU, спасибо за внимание. \r\n/Forum_Bot/";

				$title = "Обнаружена подделка ссылки выставления рейтинга";

				$stmt = $ibforums->db->query("SELECT member_id FROM ibf_moderators WHERE member_id != -1 and forum_id='" . $check['forum_id'] . "'");
				while ($moderator = $stmt->fetch())
				{
					$std->sendpm($moderator['member_id'], $mes, $title, 8617, 1);
				}
			}
		} else
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'missing_files'));
		}
	}

	// Song * silent check correct parameters

	function show_form($memid)
	{
		global $ibforums, $std, $print;

		$output = '';

		if (($ibforums->input['CODE'] == '01' or $ibforums->input['CODE'] == '02') and $ibforums->member['id'] == $memid)
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'rep_self'));
		}

		switch ($ibforums->input['CODE'])
		{

			case '01': //Raising rep

				if (!$ibforums->input['f'])
				{
					$std->Error(array('LEVEL' => 1, 'MSG' => 'missing_files'));
				}

				// Song * silent check correct parameters

				$this->check_it_out();

				// Song * silent check correct parameters

				$info = array();

				$info['memid'] = $memid;

				require "./Skin/" . $ibforums->skin_id . "/skin_rep.php";
				$rep_html = new skin_rep();

				$info['action'] = $ibforums->lang['raise'];
				$info['level']  = $ibforums->input['rep_level'];
				$info['code']   = $ibforums->input['CODE'];
				$info['f']      = $ibforums->input['f'];
				$info['t']      = $ibforums->input['t'];
				$info['p']      = $ibforums->input['p'];

				if ($ibforums->vars['rep_allow_anon'] and $ibforums->member['allow_anon'] and ($ibforums->member['posts'] >= $ibforums->vars['rep_anon_posts']))
				{
					$info['anon'] = "<input type='checkbox' name='anonymno' value='yes'> {$ibforums->lang['vote_anon']}";
				} else
				{
					$info['anon'] = "";
				}

				$output .= $rep_html->ShowForm($info);

				$NAV        = $ibforums->lang['pnav'];
				$page_title = $ibforums->lang['ptitle'];

				break;

			case '02': //Lowering rep

				if (!$ibforums->input['f'])
				{
					$std->Error(array('LEVEL' => 1, 'MSG' => 'missing_files'));
				}

				// Song * silent check correct parameters

				$this->check_it_out();

				// Song * silent check correct parameters

				if ($ibforums->member['posts'] < $ibforums->vars['rep_posts'])
				{
					$std->Error(array('LEVEL' => 1, 'MSG' => 'rep_noposts', 'EXTRA' => $ibforums->vars['rep_posts']));
				}

				$level = $this->get_rep($memid, $field);

				if (empty($level))
				{
					$level = 0;
				}

				if (is_numeric($ibforums->vars['rep_remove']))
				{
					if ($level <= $ibforums->vars['rep_remove'])
					{
						$std->Error(array('LEVEL' => 1, 'MSG' => 'rep_low'));
					}
				}

				$info = array();

				$info['memid'] = $memid;

				require "./Skin/" . $ibforums->skin_id . "/skin_rep.php";
				$rep_html = new skin_rep();

				$info['action'] = $ibforums->lang['lower'];
				$info['level']  = $ibforums->input['rep_level'];
				$info['code']   = $ibforums->input['CODE'];
				$info['f']      = $ibforums->input['f'];
				$info['t']      = $ibforums->input['t'];
				$info['p']      = $ibforums->input['p'];

				if ($ibforums->vars['rep_allow_anon'] and $ibforums->member['allow_anon'] and ($ibforums->member['posts'] >= $ibforums->vars['rep_anon_posts']))
				{
					$info['anon'] = "<input type='checkbox' name='anonymno' value='yes'> {$ibforums->lang['vote_anon']}";
				} else
				{
					$info['anon'] = "";
				}

				$output .= $rep_html->ShowForm($info);

				$NAV        = $ibforums->lang['pnav'];
				$page_title = $ibforums->lang['ptitle'];

				break;

			case '03': //Showing stats

				require "./Skin/" . $ibforums->skin_id . "/skin_rep.php";
				$rep_html = new skin_rep();

				$ratting_type = ($ibforums->input['type'] == "t")
					? "1"
					: "0";

				$stmt = $ibforums->db->query("SELECT COUNT(r.msg_id) as total FROM ibf_reputation r, ibf_forums f
					    WHERE f.id=r.forum_id and f.inc_postcount={$ratting_type} and r.member_id='" . $memid . "'");

				$max = $stmt->fetch();

				$stmt->closeCursor();

				if (!$ibforums->input['st'])
				{
					$ibforums->input['st'] = 0;
				}

				$links = $std->build_pagelinks(array(
				                                    'TOTAL_POSS' => $max['total'],
				                                    'PER_PAGE'   => $ibforums->vars['rep_per_page'],
				                                    'CUR_ST_VAL' => $ibforums->input['st'],
				                                    'L_SINGLE'   => "",
				                                    'L_MULTI'    => $ibforums->lang['multi_pages'],
				                                    'BASE_URL'   => $ibforums->base_url . "act=rep&amp;CODE=03&amp;type={$ibforums->input['type']}&amp;mid=" . $memid,
				                               ));

				$output .= $rep_html->Links($links);
				$output .= "<br>";

				$ratting = ($ibforums->input['type'] == "t")
					? "rep"
					: "ratting";

				$stmt = $ibforums->db->query("SELECT id, name, {$ratting} as rep FROM ibf_members WHERE id='" . $memid . "'");

				$info = $stmt->fetch();

				$stmt = $ibforums->db->query("SELECT COUNT(r.msg_id) as ups FROM ibf_reputation r, ibf_forums f
				            WHERE r.member_id='" . $memid . "' and r.code='01' and
						  f.id=r.forum_id and f.inc_postcount={$ratting_type}");

				if ($count = $stmt->fetch())
				{
					$info['ups'] = $count['ups'];
				} else
				{
					$info['ups'] = 0;
				}

				$info['downs'] = abs($info['rep'] - $info['ups']);

				if (empty($info['rep']) and empty($info['ups']))
				{
					$info['rep'] = $ibforums->lang['no_changes'];
				} else
				{
					$info['rep'] .= " " . $ibforums->lang['rep_postfix'];
				}

				$info['name'] = "<a href='{$ibforums->base_url}act=Profile&MID={$info['id']}'>" . $info['name'] . "</a>";

				$output .= $rep_html->ShowTitle($info);

				$output .= $rep_html->ShowHeader();

				//Jureth			$stmt = $ibforums->db->query("SELECT r.*, m.name, t.title, f.read_perms
				$stmt = $ibforums->db->query("SELECT r.*, m.name, f.name as forum, t.title, f.read_perms
					    FROM (ibf_reputation r, ibf_forums f )
				            LEFT JOIN ibf_members m ON (m.id=r.from_id)
				            LEFT JOIN ibf_topics t ON (r.topic_id=t.tid)
				            WHERE r.member_id='" . $memid . "' and r.forum_id=f.id and f.inc_postcount={$ratting_type}
					    ORDER BY r.msg_date DESC
				            LIMIT " . $ibforums->input['st'] . ", " . $ibforums->vars['rep_per_page']);

				if (!$stmt->rowCount())
				{
					$output .= $rep_html->ShowNone();
				}

				while ($i = $stmt->fetch())
				{
					switch ($i['CODE'])
					{
						case '01':
							$i['img'] = "<{REP_UP}>";
							break;

						case '02':
							$i['img'] = "<{REP_DOWN}>";
							break;
					}

					$i['date'] = $std->get_date($i['msg_date']);

					$i['message'] = $this->parser->prepare(array(
					                                            'TEXT'      => $i['message'],
					                                            'SMILIES'   => 1,
					                                            'CODE'      => 1,
					                                            'SIGNATURE' => 0,
					                                            'HTML'      => 1
					                                       ));

					if ($std->check_perms($i['read_perms']) != TRUE)
					{
						$i['title'] = "";
					}

					$i['title'] = ($i['title'])
						? "<a href='{$ibforums->base_url}act=ST&f={$i['forum_id']}&t={$i['topic_id']}&view=findpost&p={$i['post']}'>{$i['title']}</a>"
						: "<span style='color:lightsteelblue'>{$ibforums->lang['no_topic']}</span>";

					/* <--- Jureth --- */
					$i['forum'] = ($i['title'])
						? "<a href='{$ibforums->base_url}showforum={$i['forum_id']}'>{$i['forum']}</a>"
						: "<span style='color: lightsteelblue'>{$ibforums->lang['no_forum']}</span>";

					if ($i['vis'])
					{
						$i['name'] = ($i['name'])
							? "<a href='{$ibforums->base_url}act=rep&CODE=04&mid={$i['from_id']}'><b>{$i['name']}</b></a>"
							: "Unregistered";
					} else
					{
						$i['name'] = ($ibforums->member['g_access_cp'])
							? $i['name'] = "<a href='{$ibforums->base_url}act=rep&CODE=04&mid={$i['from_id']}'><b><span style='color:lightsteelblue'>{$i['name']}</b></a>,</span> "
							: "";

						if ($i['CODE'] == '01' and $ibforums->vars['rep_good_anon'])
						{
							$i['name'] .= "<span style='color:lightsteelblue'>{$ibforums->vars['rep_good_anon']}</span>";
						} elseif ($i['CODE'] == '02' and $ibforums->vars['rep_bad_anon'])
						{
							$i['name'] .= "<span style='color:lightsteelblue'>{$ibforums->vars['rep_bad_anon']}</span>";
						} else
						{
							$i['name'] .= "<span style='color:lightsteelblue'>{$ibforums->lang['is_anon']}</span>";
						}
					}

					if ($ibforums->member['g_access_cp'] and $ibforums->member['id'] != $i['member_id'])
					{
						$i['admin_undo'] = "<br><a href='{$ibforums->base_url}act=rep&amp;CODE=delete&amp;type={$ibforums->input['type']}&amp;id={$i['msg_id']}&amp;mid={$i['member_id']}'>{$ibforums->lang['undo_change']}</a>";
					}

					$i['memid'] = $memid;

					$output .= $rep_html->ShowRow($i);
				}

				if (!$ibforums->input['t'] or !$ibforums->input['f'])
				{
					$back = "javascript:history.go(-1)";
				} else
				{
					$back = "{$ibforums->base_url}act=ST&t=" . $ibforums->input['t'] . "&f=" . $ibforums->input['f'];
				}

				$output .= $rep_html->ShowFooter($back);

				$output .= "<br>";
				$output .= $rep_html->Links($links);

				$NAV = sprintf($ibforums->lang['snav'], $ibforums->lang['rep_' . $ibforums->input['type']]);

				$page_title = $ibforums->lang['stitle'];

				break;

			case '04': //Showing stats of this member CHANGING rep

				require "./Skin/" . $ibforums->skin_id . "/skin_rep.php";

				$rep_html = new skin_rep();

				if (!$ibforums->member['g_access_cp'])
				{
					$pfix = "' AND r.vis=1 ";
				} else
				{
					$pfix = "' ";
				}

				$stmt = $ibforums->db->query("SELECT COUNT(msg_id) as total FROM ibf_reputation r WHERE from_id = '" . $memid . $pfix);
				$max  = $stmt->fetch();

				$stmt->closeCursor();

				if (!$ibforums->input['st'])
				{
					$ibforums->input['st'] = 0;
				}

				$links = $std->build_pagelinks(array(
				                                    'TOTAL_POSS' => $max['total'],
				                                    'PER_PAGE'   => $ibforums->vars['rep_per_page'],
				                                    'CUR_ST_VAL' => $ibforums->input['st'],
				                                    'L_SINGLE'   => "",
				                                    'L_MULTI'    => $ibforums->lang['multi_pages'],
				                                    'BASE_URL'   => $ibforums->base_url . "act=rep&CODE=04&mid=" . $memid,
				                               ));

				$output .= $rep_html->Links($links);
				$output .= "<br>";

				$stmt = $ibforums->db->query("SELECT m.id, m.name, COUNT(r.from_id) as times
					    FROM ibf_reputation r
					    LEFT JOIN ibf_members m ON (m.id = r.from_id)
					    WHERE r.from_id='" . $memid . $pfix . "GROUP BY r.from_id");

				if (!$stmt->rowCount())
				{
					$std->Error(array('LEVEL' => 1, 'MSG' => 'no_name_search_results'));
				}

				$info = $stmt->fetch();

				$stmt = $ibforums->db->query("SELECT COUNT(r.from_id) as ups
					    FROM ibf_reputation r
					    WHERE r.code='01' AND r.from_id='" . $memid . $pfix);

				$row           = $stmt->fetch();
				$info['ups']   = $row['ups'];
				$info['downs'] = $info['times'] - $info['ups'];

				$info['name'] = "<a href='{$ibforums->base_url}act=Profile&MID={$info['id']}'>" . $info['name'] . "</a>";

				$output .= $rep_html->ShowSelfTitle($info);

				$output .= $rep_html->ShowSelfHeader();

				//Jureth			$stmt = $ibforums->db->query("SELECT r.*, m.name, t.title, f.read_perms
				$stmt = $ibforums->db->query("SELECT r.*, m.name, f.name as forum, t.title, f.read_perms
					    FROM (ibf_reputation r, ibf_forums f )
				            LEFT JOIN ibf_members m ON (m.id=r.member_id)
				            LEFT JOIN ibf_topics t ON (r.forum_id=t.forum_id AND r.topic_id=t.tid)
				            WHERE r.forum_id=f.id and r.from_id='" . $memid . $pfix . "ORDER BY r.msg_date DESC
				            LIMIT " . $ibforums->input['st'] . ", " . $ibforums->vars['rep_per_page']);

				if (!$stmt->rowCount())
				{
					$output .= $rep_html->ShowNone();
				}

				while ($i = $stmt->fetch())
				{
					switch ($i['CODE'])
					{
						case '01':
							$i['img'] = "<{REP_UP}>";
							break;

						case '02':
							$i['img'] = "<{REP_DOWN}>";
							break;
					}

					$i['date'] = $std->get_date($i['msg_date']);

					$i['message'] = $this->parser->prepare(array(
					                                            'TEXT'      => $i['message'],
					                                            'SMILIES'   => 1,
					                                            'CODE'      => 1,
					                                            'SIGNATURE' => 0,
					                                            'HTML'      => 1
					                                       ));

					if ($std->check_perms($i['read_perms']) != TRUE)
					{
						$i['title'] = "";
					}

					if (!$i['title'])
					{
						$i['title'] = "<span style='color:lightsteelblue'>{$ibforums->lang['no_topic']}</span>";
					} else
					{
						$i['title'] = "<a href='{$ibforums->base_url}act=ST&f={$i['forum_id']}&t={$i['topic_id']}&view=findpost&p={$i['post']}'>{$i['title']}</a>";
					}

					/* <--- Jureth --- */
					if (!$i['title'])
					{
						$i['forum'] = "<span style='color: lightsteelblue'>{$ibforums->lang['no_forum']}</span>";
					} else
					{
						$i['forum'] = "<a href='{$ibforums->base_url}showforum={$i['forum_id']}'>{$i['forum']}</a>";
					}

					if ($i['vis'] != 0)
					{
						$i['name'] = "<a href='{$ibforums->base_url}act=rep&CODE=03&mid={$i['member_id']}'><b>{$i['name']}</b></a>";
					} else
					{
						if ($ibforums->member['g_access_cp'])
						{
							$i['name'] = "<a href='{$ibforums->base_url}act=rep&CODE=03&mid={$i['member_id']}'><b><span style='color:lightsteelblue'>{$i['name']}<b></span></a>";
						} else
						{
							$i['name'] = "";
						}
					}

					if ($ibforums->member['g_access_cp'] and $ibforums->member['id'] != $i['member_id'])
					{
						$i['admin_undo'] = "<br><a href='{$ibforums->base_url}act=rep&CODE=delete&id={$i['msg_id']}&mid={$i['member_id']}'>{$ibforums->lang['undo_change']}</a>";
					}

					$i['memid'] = $memid;

					$output .= $rep_html->ShowRow($i);
				}

				if (!$ibforums->input['t'] or !$ibforums->input['f'])
				{
					$back = "javascript:history.go(-1)";
				} else
				{
					$back = "{$ibforums->base_url}act=ST&t=" . $ibforums->input['t'] . "&f=" . $ibforums->input['f'];
				}

				$output .= $rep_html->ShowFooter($back);

				$output .= "<br>";
				$output .= $rep_html->Links($links);

				$NAV = sprintf($ibforums->lang['snav'], "");

				$page_title = $ibforums->lang['stitle'];

				break;

			case 'delete': //Deleting a rep change

				if (!$ibforums->member['g_access_cp'])
				{
					$std->Error(array('LEVEL' => 1, 'MSG' => 'moderate_no_permission'));
				}

				$ibforums->input['id'] = intval($ibforums->input['id']);

				$stmt = $ibforums->db->query("SELECT member_id FROM ibf_reputation WHERE msg_id='" . $ibforums->input['id'] . "' AND
					    member_id='" . $memid . "' LIMIT 1");

				if (!$stmt->rowCount())
				{
					$std->Error(array('LEVEL' => 1, 'MSG' => 'moderate_no_permission'));
				}

				$row = $stmt->fetch();

				if ($row['member_id'] == $ibforums->member['id'])
				{
					$std->Error(array('LEVEL' => 1, 'MSG' => 'rep_self'));
				}

				$ibforums->db->exec("DELETE FROM ibf_reputation WHERE msg_id='" . $ibforums->input['id'] . "'");

				$std->rep_recount($memid);

				$print->redirect_screen($ibforums->lang['del_success'] . "$this->message", "act=rep&amp;CODE=03&amp;type={$ibforums->input['type']}&amp;mid=" . $memid);

				break;

			case 'totals': //Showing board overall stats
				// Song * disable feature
				$std->Error(array('LEVEL' => 1, 'MSG' => 'no_permission'));

				// Song * disable feature
				require "./Skin/" . $ibforums->skin_id . "/skin_rep.php";

				$rep_html = new skin_rep();

				if ($ibforums->member['g_mem_info'] != 1)
				{
					$std->Error(array('LEVEL' => 1, 'MSG' => 'no_permission'));
				}

				if (intval($ibforums->input['max_results']))
				{
					$this->max_results = intval($ibforums->input['max_results']);
				} else
				{
					$this->max_results = 30;
				}

				if (intval($ibforums->input['st']))
				{
					$this->first = intval($ibforums->input['st']);
				} else
				{
					$this->first = 0;
				}

				if ($ibforums->input['sort_key'])
				{
					$this->sort_key = $ibforums->input['sort_key'];
				} else
				{
					$this->sort_key = 'rep';
				}

				if ($ibforums->input['sort_order'])
				{
					$this->sort_order = $ibforums->input['sort_order'];
				} else
				{
					$this->sort_order = 'desc';
				}

				$sort_key = array(
					'name'  => 'sort_by_name',
					'rep'   => 'sort_by_rep',
					'times' => 'sort_by_rep_changes',
				);

				$max_results = array(
					10 => '10',
					20 => '20',
					30 => '30',
					40 => '40',
					50 => '50',
				);

				$sort_order = array(
					'desc' => 'descending_order',
					'asc'  => 'ascending_order',
				);

				$sort_key_html    = "<select name='sort_key' class='forminput'>\n";
				$max_results_html = "<select name='max_results' class='forminput'>\n";
				$sort_order_html  = "<select name='sort_order' class='forminput'>\n";

				foreach ($sort_order as $k => $v)
				{
					$sort_order_html .= $k == $this->sort_order
						? "<option value='$k' selected>" . $ibforums->lang[$sort_order[$k]] . "\n"
						: "<option value='$k'>" . $ibforums->lang[$sort_order[$k]] . "\n";
				}

				foreach ($sort_key as $k => $v)
				{
					$sort_key_html .= $k == $this->sort_key
						? "<option value='$k' selected>" . $ibforums->lang[$sort_key[$k]] . "\n"
						: "<option value='$k'>" . $ibforums->lang[$sort_key[$k]] . "\n";
				}

				foreach ($max_results as $k => $v)
				{
					$max_results_html .= $k == $this->max_results
						? "<option value='$k' selected>" . $max_results[$k] . "\n"
						: "<option value='$k'>" . $max_results[$k] . "\n";
				}

				$ibforums->lang['sorting_text'] = preg_replace("/<#SORT_KEY#>/", $sort_key_html . "</select>", $ibforums->lang['sorting_text']);
				$ibforums->lang['sorting_text'] = preg_replace("/<#SORT_ORDER#>/", $sort_order_html . "</select>", $ibforums->lang['sorting_text']);
				$ibforums->lang['sorting_text'] = preg_replace("/<#MAX_RESULTS#>/", $max_results_html . "</select>", $ibforums->lang['sorting_text']);

				$error = 0;

				if (!isset($sort_key[$this->sort_key]))
				{
					$error = 1;
				}
				if (!isset($sort_order[$this->sort_order]))
				{
					$error = 1;
				}
				if (!isset($max_results[$this->max_results]))
				{
					$error = 1;
				}

				if ($error)
				{
					$std->Error(array('LEVEL' => 5, 'MSG' => 'incorrect_use'));
				}

				$stmt = $ibforums->db->query("SELECT COUNT(id) as total_members FROM ibf_members");

				$max = $stmt->fetch();

				$stmt->closeCursor();

				$links = $std->build_pagelinks(array(
				                                    'TOTAL_POSS' => $max['total_members'],
				                                    'PER_PAGE'   => $this->max_results,
				                                    'CUR_ST_VAL' => $this->first,
				                                    'L_SINGLE'   => "",
				                                    'L_MULTI'    => $ibforums->lang['multi_pages'],
				                                    'BASE_URL'   => $ibforums->base_url . "act=rep&CODE=totals&max_results={$this->max_results}&&sort_order={$this->sort_order}&sort_key={$this->sort_key}"
				                               ));

				$output = $rep_html->Links($links);
				$output .= "<br>";

				$output .= $rep_html->StatsLinks();

				if (!$ibforums->member['g_access_cp'])
				{
					$pfix = " AND r.vis=1 ";
				} else
				{
					$pfix = "";
				}

				$stmt = $ibforums->db->query("SELECT m.name, m.id, m.rep, m.allow_rep, m.allow_anon, COUNT(r.msg_id) AS times
					    FROM ibf_members m
					    LEFT JOIN ibf_reputation r ON (r.from_id = m.id{$pfix})
					    GROUP BY m.id ORDER BY {$this->sort_key} {$this->sort_order}
					    LIMIT {$this->first},{$this->max_results}");

				while ($member = $stmt->fetch())
				{
					$member['name'] = "<a href='{$ibforums->base_url}act=Profile&CODE=03&MID={$member['id']}'><b>{$member['name']}</b></a>";

					if ($ibforums->member['g_access_cp'])
					{
						if (!$member['allow_rep'])
						{
							$member['name'] .= " " . $ibforums->lang['disallow_rep'];
						} else
						{
							if ($ibforums->vars['rep_allow_anon'])
							{
								if ($member['allow_anon'])
								{
									$member['name'] .= " " . $ibforums->lang['allow_anon'];
								} else
								{
									$member['name'] .= " " . $ibforums->lang['disallow_anon'];
								}
							}
						}
					}

					if (is_numeric($member['rep']))
					{
						$member['rep'] .= " " . $ibforums->lang['rep_postfix'] . " <a href='{$ibforums->base_url}act=rep&CODE=03&mid={$member['id']}'>{$ibforums->lang['details']}</a>";
					} else
					{
						$member['rep'] = $ibforums->lang['no_changes'];
					}

					if (empty($member['times']))
					{
						$member['times'] = $ibforums->lang['no_changes'];
					} else
					{
						$member['times'] .= " " . $ibforums->lang['rep_postfix'] . " <a href='{$ibforums->base_url}act=rep&CODE=04&mid={$member['id']}'>{$ibforums->lang['details']}</a>";
					}

					$output .= $rep_html->ShowTotalsRow($member);
				}

				$output .= $rep_html->Page_end();

				if (!$ibforums->input['t'] or !$ibforums->input['f'])
				{
					$back = "javascript:history.go(-1)";
				} else
				{
					$back = "{$ibforums->base_url}act=ST&t=" . $ibforums->input['t'] . "&f=" . $ibforums->input['f'];
				}

				$output .= $rep_html->ShowFooter($back);

				$output .= "<br>";
				$output .= $rep_html->Links($links);

				$NAV        = $ibforums->lang['bnav'];
				$page_title = $ibforums->lang['btitle'];

				break;
		}

		$print->add_output("$output");

		$print->do_output(array('TITLE' => $page_title, 'JS' => 0, 'NAV' => array($NAV)));
	}

	function add_why($memid)
	{
		global $std, $ibforums;

		if ($ibforums->input['anonymno'] == 'yes')
		{
			$show = 0;
		} else
		{
			$show = 1;
		}

		$data = [
			'member_id' => $memid,
			'msg_date'  => time(),
			'message'   => $this->parser->convert(array(
			                                           'TEXT'    => $ibforums->input['message'],
			                                           'SMILIES' => $ibforums->vars['rep_enable_emo'],
			                                           'CODE'    => $ibforums->vars['rep_enable_ibc'],
			                                           'HTML'    => 0
			                                      )),
			'from_id'   => $ibforums->member['id'],
			'forum_id'  => $ibforums->input['f'],
			'topic_id'  => $ibforums->input['t'],
			'post'      => $ibforums->input['p'],
			'CODE'      => $ibforums->input['CODE'],
			'vis'       => $show,
		];

		$ibforums->db->insertRow("ibf_reputation", $data);

		$this->message = "";
	}

}
