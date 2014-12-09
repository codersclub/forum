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
  |   > Messenger functions
  |   > Module written by Matt Mecham
  |   > Date started: 26th February 2002
  |
  |	> Module Version Number: 1.0.0
  +--------------------------------------------------------------------------
 */
use Skins\Skin;
use Views\View;

$idx = new Messenger;

class Messenger
{

	var $output = "";
	var $page_title = "";
	var $nav = array();
	var $email = "";
	var $msg_stats = array();
	var $prefs = "";
	var $member = array();
	var $m_group = array();
	var $to_mem = array();
	var $jump_html = "";
	var $vid = "in";
	var $mem_groups = array();
	var $mem_titles = array();
	var $parser = "";
	var $cp_html = "";

	function Messenger()
	{
		global $ibforums, $std, $print;

		//--------------------------------------------
		// Require the HTML and language modules
		//--------------------------------------------

		$ibforums->lang = $std->load_words($ibforums->lang, 'lang_msg', $ibforums->lang_id);
		$ibforums->lang = $std->load_words($ibforums->lang, 'lang_ucp', $ibforums->lang_id);

		//--------------------------------------------

		$this->base_url = $ibforums->base_url;

		$this->base_url_nosess = "{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}";

		//--------------------------------------------
		// Check viewing permissions, etc
		//--------------------------------------------

		$this->member = $ibforums->member;

		if (empty($this->member['g_use_pm']))
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_use_messenger'));
		}

		if (empty($this->member['id']))
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_guests'));
		}

		//--------------------------------------------
		// Get the member stats data
		//--------------------------------------------

		$stmt            = $ibforums->db->query("SELECT vdirs, msg_total, new_msg, msg_msg_id FROM ibf_members WHERE id='" . $this->member['id'] . "'");
		$this->msg_stats = $stmt->fetch();

		//--------------------------------------------
		// Do a little set up, do a litle dance, get
		// down tonight! *boogie*
		//--------------------------------------------

		$this->jump_html = "<select name='VID' class='forminput'>\n";

		$this->msg_stats['dir_data'] = array();

		// Do we have VID?
		// No, it's just the way we walk! Haha, etc.

		if ($ibforums->input['VID'])
		{
			$this->vid = $ibforums->input['VID'];
		}

		if (empty($this->msg_stats['vdirs']))
		{
			$this->msg_stats['vdirs'] = "in:Inbox|sent:Sent Items";
		}

		$folder_links = "";

		foreach (explode("|", $this->msg_stats['vdirs']) as $dir)
		{
			list ($id, $real) = explode(":", $dir);

			if (empty($id))
			{
				continue;
			}

			$this->msg_stats['dir_data'][] = array('id' => $id, 'real' => $real);

			$real       = $this->get_folder_name_by_vid($id, $real);
			$real_label = $real;
			if ($id != 'in' and $id != 'sent')
			{
				$folder_links .= View::make("ucp.menu_bar_msg_folder_link", ['id' => $id, 'real' => $real]);
			}

			if ($this->vid == $id)
			{
				$this->msg_stats['current_dir'] = $real_label;
				$this->msg_stats['current_id']  = $id;
				$this->jump_html .= "<option value='$id' selected='selected'>$real_label</option>\n";
			} else
			{
				$this->jump_html .= "<option value='$id'>$real_label</option>\n";
			}
		}

		$this->jump_html .= "<!--EXTRA--></select>\n\n";

		// Song * delete profile link, 04.03.05

		if ($ibforums->member['profile_delete_time'])
		{
			$days_remained = "-";

			$days = $ibforums->member['profile_delete_time'] - time();

			if ($days > 0)
			{
				$days = round($days / 86400);

				if ($days >= 1)
				{
					$days_remained = $days;
				}
			}

			$delete_profile_link = View::make("ucp.delete_cancel", ['days' => $days_remained]);
		} else
		{
			$delete_profile_link = View::make("ucp.delete_account");
		}

		// Song * delete profile link, 04.03.05

		$menu_html = View::make("ucp.Menu_bar", ['base_url' => $this->base_url, 'delete' => $delete_profile_link]);

		if ($folder_links)
		{
			$menu_html = str_replace("<!--IBF.FOLDER_LINKS-->", $folder_links, $menu_html);
		}

		$print->add_output($menu_html);

		//--------------------------------------------
		// What to do?
		//--------------------------------------------

		switch ($ibforums->input['CODE'])
		{
			case '01':
				$this->msg_list();
				break;
			case '02':
				$this->contact();
				break;
			case '03':
				$this->view_msg();
				break;
			case '04';
				$std->flood_begin();
				$this->send();
				$std->flood_end();
				break;
			case '05':
				$this->delete();
				break;
			case '06':
				$this->multi_act();
				break;
			case '07':
				$this->prefs();
				break;
			case '08':
				$this->do_prefs();
				break;
			case '09':
				$this->add_member();
				break;
			case '10':
				$this->del_member();
				break;
			case '11':
				$this->edit_member();
				break;
			case '12':
				$this->do_edit();
				break;
			case '14':
				$this->archive();
				break;
			case '15':
				$this->do_archive();
				break;
			case '99':
				$this->pm_popup();
				break;

			case '20':
				$this->view_saved();
				break;

			case '21':
				$this->edit_saved();
				break;

			case '30':
				$this->show_tracking();
				break;

			case '31':
				$this->end_tracking();
				break;

			case '32':
				$this->del_tracked();
				break;

			case 'delete':
				$this->start_empty_folders();
				break;
			case 'dofolderdelete':
				$this->end_empty_folders();
				break;

			default:
				$this->msg_list();
				break;
		}

		// If we have any HTML to print, do so...

		$fj = $std->build_forum_jump();
		$fj = preg_replace("!#Forum Jump#!", $ibforums->lang['forum_jump'], $fj);

		$this->output .= View::make("ucp.CP_end");

		$this->output .= View::make("ucp.forum_jump", ['data' => $fj]);

		$print->add_output("$this->output");
		$print->do_output(array('TITLE' => $this->page_title, 'JS' => 0, 'NAV' => $this->nav));
	}

	/**
	 * PM-флуд-контроль
	 */
	function pm_flood_begin()
	{
		global $std, $ibforums;
		// бывает, из-за глюков, отрицательное значение
		if ($ibforums->member['posts'] < 0)
		{
			$ibforums->member['posts'] = 0;
		}
		$limit = $ibforums->db
			->query("SELECT max_pms_per_hour FROM ibf_titles WHERE posts <= '" . $ibforums->member['posts'] . "' ORDER BY posts DESC LIMIT 1")
			->fetchColumn();

		if ($limit == 0)
		{
			return;
		}

		/*
		 * from_id != member_id означает, что ищем в папках других юзверей.
		 * (т.к. на одно сообщение в таблице ibf_messages попадают 2 записи
		 *
		 * vid != 'unsent' означает, что считаем только отправленные сообщения (игнорируя черновики)
		 */
		$q = 'SELECT count(*) as msg_count, min(msg_date) as fist_message
 			FROM ibf_messages
 			WHERE from_id = ' . intval($ibforums->member['id']) . '
 				AND from_id != member_id
 				AND vid != \'unsent\'
 				AND msg_date > ' . strtotime('-1 hour');
		//todo not a good idea, I think
		extract($ibforums->db->query($q)->fetch()); // создаёт $msg_count и $fist_message

		if ($msg_count >= $limit)
		{

			$minutes = (strtotime('+1 hour', $fist_message) - time()) / 60;

			$std->Error(array(
			                 'LEVEL'  => 1,
			                 'MSG'    => 'pms_flood_control',
			                 'EXTRA'  => $limit,
			                 'EXTRA2' => (int)$minutes,
			            ));
		}
	}

	/*	 * ******************************************************* */

	// PM Pop up:
	//
	// Simpy display the pop up window
	/*	 * ******************************************************* */

	function pm_popup()
	{

		global $std, $print, $ibforums;

		// Get the last message stuff

		$stmt = $ibforums->db->query("SELECT m.name, msg.title, msg.msg_date, msg.from_id FROM ibf_members m, ibf_messages msg
			    WHERE msg.member_id='" . $ibforums->member['id'] . "' AND msg.msg_id='" . $this->msg_stats['msg_msg_id'] . "' AND
				  m.id=msg.from_id");

		$row = $stmt->fetch();

		// Fix up the text string...

		$row['msg_date'] = $std->get_date($row['msg_date']);

		$text = preg_replace("/<#NAME#>/", $row['name'], $ibforums->lang['pmp_string']);
		$text = preg_replace("/<#TITLE#>/", $row['title'], $text);
		$text = preg_replace("/<#DATE#>/", $row['msg_date'], $text);

		$html = View::make("msg.pm_popup", ['text' => $text, 'mid' => $this->msg_stats['msg_msg_id']]);

		$print->pop_up_window("PM", $html);
	}

	function get_folder_name_by_vid($vid, $name = '')
	{
		global $ibforums;
		foreach ($this->msg_stats['dir_data'] as $item)
		{
			if ($item['id'] == $vid)
			{
				break;
			}
			$item = '';
		}
		if ($item)
		{
			if ($vid == 'in' && $item['real'] == 'Inbox')
			{
				return $ibforums->lang['mess_inbox'];
			} elseif ($vid == 'sent' && $item['real'] == 'Sent Items')
			{
				return $ibforums->lang['mess_sent'];
			}
			return $item['real'];
		}
		return $name;
	}

	/*	 * ******************************************************* */

	// Empty PM folders:
	//
	// Interface for removing PM's on a folder by folder basis
	/*	 * ******************************************************* */

	function start_empty_folders()
	{
		global $ibforums, $std, $print;

		$this->output .= View::make("msg.empty_folder_header");

		//--------------------------------------------------
		// Get the PM count - 1 query?
		//--------------------------------------------------

		$count = array('unsent' => 0);
		$names = array('unsent' => $ibforums->lang['fd_unsent']);

		foreach ($this->msg_stats['dir_data'] as $k => $v)
		{
			$count[$v['id']] = 0;
			$names[$v['id']] = $v['real'];
		}

		$stmt = $ibforums->db->query("SELECT msg_id, vid FROM ibf_messages WHERE member_id={$ibforums->member['id']} LIMIT 0,1000");

		while ($r = $stmt->fetch())
		{
			if ($r['vid'] == "")
			{
				$count['in']++;
			} else
			{
				$count[$r['vid']]++;
			}
		}

		foreach ($names as $vid => $name)
		{
			$name = $this->get_folder_name_by_vid($vid, $name);
			$this->output .= View::make("msg.empty_folder_row", ['real' => $name, 'id' => $vid, 'cnt' => $count[$vid]]);
		}

		$this->output .= View::make("msg.empty_folder_save_unread");
		$this->output .= View::make("msg.empty_folder_footer");

		$this->page_title = $ibforums->lang['t_welcome'];
		$this->nav        = array("<a href='" . $this->base_url . "act=UserCP&amp;CODE=00'>" . $ibforums->lang['t_title'] . "</a>");
	}

	function end_empty_folders()
	{
		global $ibforums, $std, $print;

		$names = array('unsent' => $ibforums->lang['fd_unsent']);
		$ids   = array();
		$qe    = "";

		foreach ($this->msg_stats['dir_data'] as $k => $v)
		{
			$names[$v['id']] = $v['real'];
		}

		//----------------------------------------------
		// Did we check any boxes?
		//----------------------------------------------

		foreach ($names as $vid => $name)
		{
			if ($ibforums->input['its_' . $vid] == 1)
			{
				$ids[] = $vid;
			}
		}

		if (count($ids) < 1)
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'fd_noneselected'));
		}

		//----------------------------------------------
		// Delete em!
		//----------------------------------------------

		if ($ibforums->input['save_unread'])
		{
			$qe = ' AND read_state=1';
		}

		$ibforums->db->exec("DELETE FROM ibf_messages WHERE member_id={$ibforums->member['id']} AND vid IN('" . implode("','", $ids) . "')" . $qe);

		$stmt = $ibforums->db->query("SELECT COUNT(*) as msg_total FROM ibf_messages WHERE member_id=" . $this->member['id'] . " AND vid <> 'unsent'");

		$total = $stmt->fetch();

		$total['msg_total'] = $total['msg_total'] > 0
			? $total['msg_total']
			: 0;

		$ibforums->db->exec("UPDATE ibf_members SET msg_total=" . $total['msg_total'] . " WHERE id=" . $this->member['id']);

		$std->boink_it($this->base_url . "act=Msg&amp;CODE=delete");
	}

	/*	 * ******************************************************* */

	// ARCHIVE:
	//
	// Allows a user to archive and email a HTML file
	/*	 * ******************************************************* */

	function archive()
	{
		global $ibforums;

		$this->jump_html = preg_replace("/<!--EXTRA-->/", "<option value='all'>" . $ibforums->lang['all_folders'] . "</option>", $this->jump_html);

		$this->output .= View::make("msg.archive_form", ['jump_html' => $this->jump_html]);

		$this->page_title = $ibforums->lang['t_welcome'];
		$this->nav        = array("<a href='" . $this->base_url . "act=UserCP&amp;CODE=00'>" . $ibforums->lang['t_title'] . "</a>");
	}

	function do_archive()
	{
		global $ibforums, $std;

		require_once ROOT_PATH . "/sources/lib/emailer.php";

		$this->email = new emailer();

		//----------------------------------------
		// Did we specify a folder, or choose all?
		//----------------------------------------

		$folder_query = "";
		$msg_ids      = array();
		$older_newer  = '<';

		if ($ibforums->input['oldnew'] == 'older')
		{
			$older_newer = '>';
		}

		if ($ibforums->input['VID'] != 'all')
		{
			$folder_query = " AND vid='" . $ibforums->input['VID'] . "'";
		}

		if ($ibforums->input['dateline'] == 'all')
		{
			$time_cut    = 0;
			$older_newer = '>';
		} else
		{
			$time_cut = time() - ($ibforums->input['dateline'] * 60 * 60 * 24);
		}

		//----------------------------------------
		// Check the input...
		//----------------------------------------

		$ibforums->input['number'] = preg_replace("/^(\d+)$/", "\\1", $ibforums->input['number']);

		if ($ibforums->input['number'] < 5)
		{
			$ibforums->input['number'] = 5;
		}

		if ($ibforums->input['number'] > 50)
		{
			$ibforums->input['number'] = 50;
		}

		$type      = 'html';
		$file_name = "pm_archive.html";
		$ctype     = "text/html";

		if ($ibforums->input['type'] == 'xls')
		{
			$type      = 'xls';
			$file_name = "xls_importable.txt";
			$ctype     = "text/plain";
		}

		$output = "";

		//----------------------------------------
		// Start the datafile..
		//----------------------------------------

		if ($type == 'html')
		{
			$output .= View::make("msg.archive_html_header");
		}

		$this->parser = new PostParser(1);

		//----------------------------------------
		// Get the messages...
		//----------------------------------------

		$archive_query = $stmt = $ibforums->db->query("SELECT mg.*, m.name, m.id, mr.id as rec_id, mr.name as rec_name
					 	FROM ibf_messages mg
						   LEFT JOIN ibf_members m ON (m.id=mg.from_id)
						   LEFT JOIN ibf_members mr ON (mr.id=mg.recipient_id)
						 WHERE mg.member_id={$ibforums->member['id']}
						 AND mg.msg_date $older_newer $time_cut" . $folder_query . "
						 ORDER BY mg.msg_date
						 LIMIT 0," . $ibforums->input['number']);

		if ($stmt->rowCount($archive_query))
		{
			while ($r = $stmt->fetch($archive_query))
			{
				$info = array();

				$msg_ids[] = $r['msg_id'];

				//$from_member = $stmt = $ibforums->db->query("SELECT id, name FROM ibf_members WHERE id='".$r['from_id']."'");
				//$from_mem = $stmt->fetch($from_member);

				$info['msg_date']    = $std->get_date($r['msg_date']);
				$info['msg_title']   = $r['title'];
				$info['msg_sender']  = $r['name'];
				$info['msg_content'] = $this->parser->prepare(array(
				                                                   'TEXT'    => $r['message'],
				                                                   'SMILIES' => 0,
				                                                   'CODE'    => $ibforums->vars['msg_allow_code'],
				                                                   'HTML'    => $ibforums->vars['msg_allow_html']
				                                              ));

				if ($type == 'xls')
				{
					$output .= '"' . $this->strip_quotes($info['msg_title']) . '","' . $this->strip_quotes($info['msg_date']) . '","' . $this->strip_quotes($info['msg_sender']) . '","' . $this->strip_quotes($info['msg_content']) . '"' . "\r";
				} else
				{
					if ($r['vid'] == 'sent')
					{
						$info['msg_sender'] = $r['rec_name'];
						$output .= View::make("msg.archive_html_entry_sent", ['info' => $info]);
					} else
					{
						$output .= View::make("msg.archive_html_entry", ['info' => $info]);
					}
				}
			}

			if ($type == 'html')
			{
				$output .= View::make("msg.archive_html_footer");
			}

			$num_msg = count($msg_ids);

			if ($ibforums->input['delete'] == 'yes')
			{
				$msg_str = implode(",", $msg_ids);

				if (!empty($msg_str))
				{
					$ibforums->db->exec("DELETE FROM ibf_messages WHERE msg_id IN ($msg_str)");

					$ibforums->db->exec("UPDATE ibf_members SET msg_total=msg_total-$num_msg WHERE id ='" . $this->member['id'] . "'");
				}
			}

			$output = str_replace("<#IMG_DIR#>", $ibforums->skin->getImagesPath(), $output);

			$this->email->get_template("pm_archive");

			$this->email->build_message(array(
			                                 'NAME' => $this->member['name'],
			                            ));

			$this->email->subject = $ibforums->lang['arc_email_subject'];
			$this->email->to      = $this->member['email'];
			$this->email->add_attachment($output, $file_name, $ctype);
			$this->email->send_mail();

			$ibforums->lang['arc_complete'] = preg_replace("/<#NUM#>/", "$num_msg", $ibforums->lang['arc_complete']);

			$this->output .= View::make("msg.archive_complete");

			$this->page_title = $ibforums->lang['t_welcome'];
			$this->nav        = array("<a href='" . $this->base_url . "act=UserCP&amp;CODE=00'>" . $ibforums->lang['t_title'] . "</a>");
		} else
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_archive_messages'));
		}
	}

	function strip_quotes($text)
	{

		return preg_replace("/\"/", '\\\"', $text);
	}

	/*	 * ******************************************************* */

	// PREFS:
	//
	// Create/delete/edit messenger folders
	/*	 * ******************************************************* */

	function prefs()
	{
		global $ibforums, $std, $print;

		$this->output .= View::make("msg.prefs_header");

		$max = 1;

		foreach ($this->msg_stats['dir_data'] as $k => $v)
		{
			$extra     = "";
			$v['real'] = $this->get_folder_name_by_vid($v['id'], $v['real']);
			if ($v['id'] == 'in' or $v['id'] == 'sent')
			{
				$extra = "&nbsp;&nbsp;( " . $v['real'] . " - " . $ibforums->lang['cannot_remove'] . " )";
			}

			$this->output .= View::make(
				"msg.prefs_row",
				['data' => ['ID' => $v['id'], 'REAL' => $v['real'], 'EXTRA' => $extra]]
			);

			if (stristr($v['id'], 'dir_'))
			{
				$max = intval(str_replace('dir_', "", $v['id'])) + 1;
			}
		}

		$count = $max + 1;

		$this->output .= View::make("msg.prefs_add_dirs");

		for ($i = $count; $i < $count + 3; $i++)
		{
			$this->output .= View::make(
				"msg.prefs_row",
				['data' => array('ID' => 'dir_' . $i, 'REAL' => '', 'EXTRA' => '')]
			);
		}

		$this->output .= View::make("msg.prefs_footer");

		$this->page_title = $ibforums->lang['t_welcome'];
		$this->nav        = array("<a href='" . $this->base_url . "act=UserCP&amp;CODE=00'>" . $ibforums->lang['t_title'] . "</a>");
	}

	function do_prefs()
	{
		global $ibforums, $std, $print;

		// Check to ensure than we've not tried to remove the inbox and sent items directories.

		if (($ibforums->input['sent'] == "") or ($ibforums->input['in'] == ""))
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'cannot_remove_dir'));
		}

		$v_dir = 'in:' . $ibforums->input['in'] . '|sent:' . $ibforums->input['sent'];

		// Fetch the rest of the dirs

		$ids = array();

		foreach ($ibforums->input as $key => $value)
		{
			if (preg_match("/^dir_(\d+)$/", $key, $match))
			{
				if ($ibforums->input[$match[0]])
				{
					$v_dir .= '|' . $match[0] . ':' . trim(str_replace('|', '&#124;', $ibforums->input[$match[0]]));
				}
			}
		}

		$ibforums->db->exec("UPDATE ibf_members SET vdirs='$v_dir' WHERE id='" . $this->member['id'] . "'");

		$std->boink_it($ibforums->base_url . "act=Msg&amp;CODE=07");
		exit;
	}

	/*	 * ******************************************************* */

	// DELETE_MEMBER:
	//
	// Removes a member from address book.
	/*	 * ******************************************************* */

	function del_member()
	{
		global $ibforums, $std;

		if (!$ibforums->input['MID'])
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_user'));
		}

		if (!preg_match("/^(\d+)$/", $ibforums->input['MID']))
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_user'));
		}

		$ibforums->db->exec("DELETE FROM ibf_contacts WHERE member_id='" . $this->member['id'] . "' AND contact_id='" . $ibforums->input['MID'] . "'");

		$std->boink_it($this->base_url . "act=Msg&amp;CODE=02");
		exit;
	}

	/*	 * ******************************************************* */

	// EDIT_MEMBER:
	//
	// Edit a member from address book.
	/*	 * ******************************************************* */

	function edit_member()
	{
		global $ibforums, $std;

		if (!$ibforums->input['MID'])
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_user'));
		}

		if (!preg_match("/^(\d+)$/", $ibforums->input['MID']))
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_user'));
		}

		$stmt = $ibforums->db->query("SELECT * FROM ibf_contacts WHERE member_id='" . $this->member['id'] . "' AND contact_id='" . $ibforums->input['MID'] . "'");

		$memb = $stmt->fetch();

		if (!$memb['contact_id'])
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_user'));
		}

		$html = "<select name='allow_msg' class='forminput'>";

		if ($memb['allow_msg'])
		{
			$html .= "<option value='yes' selected>{$ibforums->lang['yes']}</option><option value='no'>{$ibforums->lang['no']}";
		} else
		{
			$html .= "<option value='yes'>{$ibforums->lang['yes']}</option><option value='no' selected>{$ibforums->lang['no']}";
		}

		$html .= "</select>";

		// Song * show friends online, 05.03.05

		$show_online = "<select name='show_online' class='forminput'>";

		if ($memb['show_online'])
		{
			$show_online .= "<option value='yes' selected>{$ibforums->lang['yes']}</option><option value='no'>{$ibforums->lang['no']}";
		} else
		{
			$show_online .= "<option value='yes'>{$ibforums->lang['yes']}</option><option value='no' selected>{$ibforums->lang['no']}";
		}

		$show_online .= "</select>";

		// Song * show friends online, 05.03.05

		$this->output .= View::make(
			"msg.address_edit",
			[
				'data' => [
					'SHOW_ONLINE' => $show_online,
					'SELECT'      => $html,
					'MEMBER'      => $memb
				]
			]
		);

		$this->page_title = $ibforums->lang['t_welcome'];

		$this->nav = array(
			"<a href='" . $this->base_url . "&amp;act=UserCP&amp;CODE=00'>" . $ibforums->lang['t_title'] . "</a>",
			"<a href='" . $this->base_url . "act=Msg&amp;CODE=02'>" . $ibforums->lang['t_book'] . "</a>"
		);
	}

	/*	 * ******************************************************* */

	// DO_EDIT_MEMBER:
	//
	// Edit a member from address book.
	/*	 * ******************************************************* */

	function do_edit()
	{
		global $ibforums, $std, $print;

		if (!$ibforums->input['MID'])
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_user'));
		}

		if (!preg_match("/^(\d+)$/", $ibforums->input['MID']))
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_user'));
		}

		$ibforums->input['allow_msg'] = $ibforums->input['allow_msg'] == 'yes'
			? 1
			: 0;

		// Song * friends show online, 05.03.05

		$ibforums->input['show_online'] = $ibforums->input['show_online'] == 'yes'
			? 1
			: 0;

		// Song * friends show online, 05.03.05

		$stmt = $ibforums->db->query("SELECT * FROM ibf_contacts WHERE member_id='" . $this->member['id'] . "' AND contact_id='" . $ibforums->input['MID'] . "'");
		$memb = $stmt->fetch();

		if (!$memb['contact_id'])
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_user'));
		}

		$ibforums->db->exec("UPDATE ibf_contacts SET contact_desc='" . $ibforums->input['mem_desc'] . "',
						    allow_msg='" . $ibforums->input['allow_msg'] . "',
						    show_online='" . $ibforums->input['show_online'] . "'
			    WHERE id='" . $memb['id'] . "'");

		$std->boink_it($this->base_url . "act=Msg&amp;CODE=02");

		exit;
	}

	/*	 * ******************************************************* */

	// CONTACT:
	//
	// Shows the address book.
	/*	 * ******************************************************* */

	function contact()
	{
		global $ibforums;

		$this->output .= View::make("msg.Address_header");

		$stmt = $ibforums->db->query("SELECT * FROM ibf_contacts WHERE member_id='" . $this->member['id'] . "' ORDER BY contact_name ASC");

		if ($stmt->rowCount())
		{
			$this->output .= View::make("msg.Address_table_header");

			while ($row = $stmt->fetch())
			{
				$row['text'] = $row['allow_msg']
					? $ibforums->lang['can_contact']
					: $ibforums->lang['cannot_contact'];
				// Song * friends show online, 06.03.05

				$row['text'] .= $row['show_online']
					? ") (" . $ibforums->lang['show_online']
					: "";

				// Song * friends show online, 06.03.05

				$this->output .= View::make("msg.render_address_row", ['entry' => $row]);
			}

			$this->output .= View::make("msg.end_address_table");
		} else
		{
			$this->output .= View::make("msg.Address_none");
		}

		// Do we have a name to enter?

		$name_to_enter = "";

		if ($ibforums->input['MID'])
		{
			if (preg_match("/^(\d+)$/", $ibforums->input['MID']))
			{
				$stmt = $ibforums->db->query("SELECT name, id FROM ibf_members WHERE id='" . $ibforums->input['MID'] . "'");

				$memb = $stmt->fetch();

				if ($memb['id'])
				{
					$name_to_enter = $memb['name'];
				}
			}
		}

		$this->output .= View::make("msg.address_add", ['mem_to_add' => $name_to_enter]);

		$this->page_title = $ibforums->lang['t_welcome'];
		$this->nav        = array("<a href='" . $this->base_url . "act=UserCP&amp;CODE=00'>" . $ibforums->lang['t_title'] . "</a>");
	}

	/*	 * ******************************************************* */

	// ADD MEMBER:
	//
	// Adds a member to the addy book.
	/*	 * ******************************************************* */

	function add_member()
	{
		global $ibforums, $std, $print;

		if (!$ibforums->input['mem_name'])
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_user'));
		}

		$stmt = $ibforums->db->query("SELECT
				name, id
			    FROM ibf_members
			    WHERE
				LOWER(name)='" . addslashes($ibforums->input['mem_name']) . "'");

		$memb = $stmt->fetch();

		if (!$memb['id'])
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_user'));
		}

		//--------------------------------------
		// Check for adding themself (by barazuk)
		//--------------------------------------
		if ($this->memb['id'] == $ibforums->member['id'])
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'self_in_addr_book'
			            ));
		}

		//--------------------------------------
		// Do we already have this member in our
		// address book?
		//--------------------------------------

		$stmt = $ibforums->db->query("SELECT contact_id FROM ibf_contacts WHERE member_id='" . $this->member['id'] . "' AND contact_id='" . $memb['id'] . "'");

		if ($stmt->rowCount())
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'member_in_add_book'));
		}

		//--------------------------------------
		// Insert it into the DB
		//--------------------------------------

		$ibforums->input['allow_msg'] = $ibforums->input['allow_msg'] == 'yes'
			? 1
			: 0;

		// Song * friends show online, 06.03.05

		$ibforums->input['show_online'] = $ibforums->input['show_online'] == 'yes'
			? 1
			: 0;

		$db_string = [
			'member_id'    => $this->member['id'],
			'contact_name' => $memb['name'],
			'allow_msg'    => $ibforums->input['allow_msg'],
			'show_online'  => $ibforums->input['show_online'],
			'contact_desc' => $ibforums->input['mem_desc'],
			'contact_id'   => $memb['id']
		];
		// Song * friends show online, 06.03.05
		$ibforums->db->insertRow('ibf_contacts', $db_string);

		// BUH BYE!

		$std->boink_it($this->base_url . "act=Msg&amp;CODE=02");
		exit;
	}

	/*	 * ***************************************************************************************************************** */

	// Mutli Act:
	//
	// Removes or moves messages.
	/*	 * ******************************************************* */

	function multi_act()
	{
		global $ibforums, $std, $print;

		//--------------------------------------
		// Get the ID's to delete
		//--------------------------------------

		$ids = array();

		foreach ($ibforums->input as $key => $value)
		{
			if (preg_match("/^msgid_(\d+)$/", $key, $match))
			{
				if ($ibforums->input[$match[0]])
				{
					$ids[] = $match[1];
				}
			}
		}

		$affected_ids = count($ids);

		if ($affected_ids > 0)
		{
			$id_string = implode(",", $ids);

			if ($ibforums->input['delete'])
			{
				$ibforums->db->exec("DELETE FROM ibf_messages WHERE member_id='" . $this->member['id'] . "' AND msg_id IN ($id_string)");

				if ($ibforums->input['saved'])
				{
					// Did we delete from the saved folder? If so, don't update the msg stats and
					// redirect back to the saved folder.

					$std->boink_it($this->base_url . "act=Msg&amp;CODE=20");
					exit;
				} else
				{
					$ibforums->db->exec("UPDATE ibf_members SET msg_total=msg_total-$affected_ids WHERE id='" . $this->member['id'] . "'");
					$std->boink_it($this->base_url . "act=Msg&amp;CODE=01&amp;VID={$this->vid}");
					exit;
				}
			} else {
				if ($ibforums->input['move'])
				{
					$ibforums->db->exec("UPDATE ibf_messages SET vid='" . $this->vid . "' WHERE member_id='" . $this->member['id'] . "' AND msg_id IN ($id_string)");
					$std->boink_it($this->base_url . "act=Msg&amp;CODE=01&amp;VID={$this->vid}");
					exit;
				} else
				{
					$std->Error(array('LEVEL' => 1, 'MSG' => 'no_msg_chosen'));
				}
			}
		} else
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_msg_chosen'));
		}
	}

	/*	 * ******************************************************* */

	// END TRACKING
	//
	// Removes read tracked messages
	/*	 * ******************************************************* */

	function end_tracking()
	{
		global $ibforums, $std, $print;

		//--------------------------------------
		// Get the ID's to delete
		//--------------------------------------

		$ids = array();

		foreach ($ibforums->input as $key => $value)
		{
			if (preg_match("/^msgid_(\d+)$/", $key, $match))
			{
				if ($ibforums->input[$match[0]])
				{
					$ids[] = $match[1];
				}
			}
		}

		$affected_ids = count($ids);

		if ($affected_ids > 0)
		{
			$id_string = implode(",", $ids);

			$ibforums->db->exec("UPDATE ibf_messages SET tracking=0 WHERE tracking=1 AND read_state=1 AND from_id='" . $this->member['id'] . "' AND msg_id IN ($id_string)");

			$std->boink_it($this->base_url . "act=Msg&amp;CODE=30");
		} else
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_msg_chosen'));
		}
	}

	function del_tracked()
	{
		global $ibforums, $std, $print;

		//--------------------------------------
		// Get the ID's to delete
		//--------------------------------------

		$ids = array();

		foreach ($ibforums->input as $key => $value)
		{
			if (preg_match("/^msgid_(\d+)$/", $key, $match))
			{
				if ($ibforums->input[$match[0]])
				{
					$ids[] = $match[1];
				}
			}
		}

		$affected_ids = count($ids);

		if ($affected_ids > 0)
		{
			$id_string = implode(",", $ids);

			$ibforums->db->exec("DELETE FROM ibf_messages WHERE tracking=1 AND read_state=0 AND from_id='" . $this->member['id'] . "' AND msg_id IN ($id_string)");

			$std->boink_it($this->base_url . "act=Msg&amp;CODE=30");
		} else
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_msg_chosen'));
		}
	}

	/*	 * ******************************************************* */

	// DELETE MESSAGE:
	//
	// Removes a message.
	// Yes. there is no small print.
	/*	 * ******************************************************* */

	function delete()
	{
		global $ibforums, $std, $print;

		//--------------------------------------
		// check for a msg ID
		//--------------------------------------

		if (!$ibforums->input['MSID'])
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_msg_chosen'));
		}

		if (!preg_match("/^\d+$/", $ibforums->input['MSID']))
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'invalid_use'));
		}

		//--------------------------------------
		// Delete it from the DB
		//--------------------------------------

		$ibforums->db->exec("DELETE FROM ibf_messages WHERE msg_id='" . $ibforums->input['MSID'] . "' AND member_id='" . $this->member['id'] . "'");

		$ibforums->db->exec("UPDATE ibf_members SET msg_total=msg_total-1 WHERE id='" . $this->member['id'] . "'");

		// BYE!

		$std->boink_it($this->base_url . "act=Msg&amp;CODE=01&amp;VID={$this->vid}");
		exit;
	}

	/*	 * ******************************************************* */

	// VIEW MESSAGE:
	//
	// Views a message, thats it. No, it doesn't do anything else
	// I don't know why. It just does. Accept it and move on dude.
	/*	 * ******************************************************* */

	function view_msg()
	{
		global $ibforums, $std, $print;

		//--------------------------------------
		// check for a msg ID
		//--------------------------------------

		if (!$ibforums->input['MSID'])
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_such_msg'));
		}

		if (!preg_match("/^\d+$/", $ibforums->input['MSID']))
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'invalid_use'));
		}

		//--------------------------------------
		// Get the message from the DB
		// Check to make sure it exists
		//--------------------------------------

		$stmt = $ibforums->db->query("SELECT m.*, s.id as s_id FROM ibf_messages m
			    LEFT JOIN ibf_sessions s
			     ON (s.member_id=m.from_id and s.login_type<>1)
			    WHERE m.msg_id='" . $ibforums->input['MSID'] . "' AND
			      m.member_id='" . $this->member['id'] . "'");

		$msg = $stmt->fetch();

		if (!$msg['msg_id'])
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_such_msg'));
		}

		//--------------------------------------
		// Did we read this in the pop up?
		// If so, reduce new count by 1 (this msg)
		// 'cos if we went via inbox, we'd have
		// no new msg
		//--------------------------------------

		if ($ibforums->member['new_msg'] >= 1)
		{
			$ibforums->db->exec("UPDATE ibf_members SET new_msg=new_msg-1 WHERE id='" . $this->member['id'] . "'");
		}

		//--------------------------------------
		// Is this an unread message?
		//--------------------------------------

		if ($msg['read_state'] < 1)
		{
			$ibforums->db->exec("UPDATE ibf_messages SET read_state=1, read_date='" . time() . "' WHERE msg_id='" . $ibforums->input['MSID'] . "'");
		}

		//--------------------------------------
		// Start formatting the member and msg
		//--------------------------------------

		$this->parser = new PostParser(1);

		$msg['msg_date'] = $std->get_date($msg['msg_date']);

		$stmt = $ibforums->db->query("SELECT g.*, m.* FROM ibf_members m, ibf_groups g WHERE id='" . $msg['from_id'] . "' and g.g_id=m.mgroup");

		$member = $stmt->fetch();

		$member = $this->parse_member($member, $msg);

		$msg['message'] = $this->parser->prepare(array(
		                                              'TEXT'    => $msg['message'],
		                                              'SMILIES' => 1,
		                                              'CODE'    => $ibforums->vars['msg_allow_code'],
		                                              'HTML'    => $ibforums->vars['msg_allow_html']
		                                         ));
		if ($this->member['view_sigs'])
		{
			$member['signature'] = $this->parser->prepare(array(
			                                                   'TEXT'      => $member['signature'],
			                                                   'SMILIES'   => 0,
			                                                   'CODE'      => $ibforums->vars['sig_allow_ibc'],
			                                                   'HTML'      => $ibforums->vars['sig_allow_html'],
			                                                   'SIGNATURE' => 1,
			                                              ));

			if ($ibforums->vars['sig_allow_html'] == 1)
			{
				$member['signature'] = $this->parser->parse_html($member['signature'], 0);
			}

			$member['signature'] = View::make("global.signature_separator", ['sig' => $member['signature']]);
		} else
		{
			$member['signature'] = "";
		}

		$member['VID'] = $this->msg_stats['current_id'];

		if ($msg['s_id'])
		{
			$online = "<{ONLINE}>";
		} else
		{
			$online = "";
		}

		$this->output .= View::make(
			"msg.Render_msg",
			[
				'data' => array(
					'msg'    => $msg,
					'member' => $member,
					'jump'   => $this->jump_html,
					'online' => $online
				)
			]
		);

		$this->page_title = $ibforums->lang['t_welcome'];

		$this->nav = array(
			"<a href='" . $this->base_url . "&amp;act=UserCP&amp;CODE=00'>" . $ibforums->lang['t_title'] . "</a>",
			"<a href='" . $this->base_url . "act=Msg&amp;CODE=01&amp;VID={$member['VID']}'>" . $this->msg_stats['current_dir'] . "</a>"
		);
	}

	/*	 * ******************************************************* */

	// SEND MESSAGE:
	//
	// Sends a message. Yes, it's that simple. Why so much code?
	// Because typing "send a message to member X" doesnt actually
	// do anything.
	/*	 * ******************************************************* */

	function send()
	{
		global $ibforums;

		$this->parser = new PostParser(1);

		if ($ibforums->input['MODE'])
		{
			$this->pm_flood_begin();
			$this->send_msg();
		} else
		{
			$this->send_form();
		}
	}

	//+-----------------------------------------------------------

	function send_form($preview = 0, $errors = "")
	{
		global $ibforums, $std, $print;

		//--------------------------------------
		// Get some more HTML and words, oh yes.
		//--------------------------------------

		$errors = preg_replace("/^<br>/", "", $errors);

		$ibforums->lang = $std->load_words($ibforums->lang, 'lang_post', $ibforums->lang_id);

		if ($preview == 1)
		{

			$old_msg = $this->parser->prepare(array(
			                                       'TEXT'    => $std->remove_tags($ibforums->input['Post']),
			                                       'SMILIES' => 1,
			                                       'CODE'    => $ibforums->vars['msg_allow_code'],
			                                       'HTML'    => $ibforums->vars['msg_allow_html']
			                                  ));

			$this->output .= View::make("msg.preview", ['data' => $old_msg]);
		}

		if ($errors)
		{
			$this->output .= View::make("msg.pm_errors", ['data' => $errors]);
			$preview = 1;
		}

		//--------------------------------------
		// Load the contacts
		//--------------------------------------

		$contacts = $this->build_contact_list();

		$name_to_enter = "";
		$old_message   = "";
		$old_title     = "";

		//--------------------------------------
		// Did we come from a button with a user ID?
		//--------------------------------------

		if (!empty($ibforums->input['MID']))
		{
			$stmt = $ibforums->db->query("SELECT name, id FROM ibf_members WHERE id='" . $ibforums->input['MID'] . "'");
			$name = $stmt->fetch();

			if ($ibforums->input['fwd'] != 1)
			{
				if ($name['id'])
				{
					$name_to_enter = $name['name'];
				}
			}
		}

		//--------------------------------------
		// Are we quoting an old message?
		//--------------------------------------

		if ($preview == 1)
		{
			$old_message = $std->txt_htmlspecialchars($std->txt_stripslashes($_POST['Post']));
			$old_title   = preg_replace("/'/", "&#39;", $std->txt_stripslashes($_POST['msg_title']));
		} else {
			if (!empty($ibforums->input['MSID']))
			{
				$stmt    = $ibforums->db->query("SELECT message, title from ibf_messages WHERE msg_id='" . $ibforums->input['MSID'] . "' and member_id='" . $this->member['id'] . "'");
				$old_msg = $stmt->fetch();
				if ($old_msg['title'])
				{
					if ($ibforums->input['fwd'] == 1)
					{
						$old_title   = "Fwd:" . $old_msg['title'];
						$old_title   = preg_replace("/^(?:Fwd\:){1,}/i", "Fwd:", $old_title);
						$old_message = '[QUOTE]' . sprintf($ibforums->lang['vm_forward_text'], $name['name']) . "\n\n" . $old_msg['message'] . '[/QUOTE]' . "\n";
						$old_message = str_replace("<br>", "\n", $old_message);
					} else
					{
						$old_title   = "Re:" . $old_msg['title'];
						$old_title   = preg_replace("/^(?:Re\:){1,}/i", "Re:", $old_title);
						$old_message = '[QUOTE]' . $old_msg['message'] . '[/QUOTE]' . "\n";
						$old_message = str_replace("<br>", "\n", $old_message);
					}
				}
			}
		}

		//--------------------------------------
		// Build up the HTML for the send form
		//--------------------------------------

		$this->output .= View::make("post.get_javascript");

		$this->output .= View::make(
			"msg.Send_form",
			[
				'data' => array(
					'CONTACTS' => $contacts,
					'MEMBER'   => $this->member,
					'N_ENTER'  => $name_to_enter,
					'O_TITLE'  => $old_title,
					'OID'      => $ibforums->input['OID'],
					// Old unsent msg id for restoring saved msg - used to delete saved when sent
				)
			]
		);

		$ibforums->lang['the_max_length'] = $ibforums->vars['max_post_length'] * 1024;

		$this->output .= View::make(
			"post.pm_postbox_buttons",
			['data' => $old_message, 'syntax_select' => $std->code_tag_button()]
		);

		$this->output .= View::make("msg.send_form_footer");

		//--------------------------------------
		// Add in the smilies box
		//--------------------------------------

		$this->html_add_smilie_box();
		$this->page_title = $ibforums->lang['t_welcome'];
		$this->nav        = array("<a href='" . $this->base_url . "act=UserCP&amp;CODE=00'>" . $ibforums->lang['t_title'] . "</a>");

		//----------------------------------------
		// Do we have permission to mass PM peeps?
		//----------------------------------------

		if ($ibforums->member['g_max_mass_pm'] > 0)
		{
			$ibforums->lang['carbon_copy_desc'] = sprintf($ibforums->lang['carbon_copy_desc'], $ibforums->member['g_max_mass_pm']);

			if (isset($_POST['carbon_copy']))
			{
				$cc_box = preg_replace("#</textarea>#i", "", $std->txt_stripslashes($_POST['carbon_copy']));
			}

			$this->output = str_replace("<!--IBF.MASS_PM_BOX-->",
				View::make("msg.mass_pm_box", ['names' => $cc_box]), $this->output);
		}
	}

	//+-----------------------------------------------------------

	function edit_saved()
	{
		global $ibforums, $std, $print;

		//--------------------------------------
		// Get some more HTML and words, oh yes.
		//--------------------------------------

		$errors = preg_replace("/^<br>/", "", $errors);

		$ibforums->lang = $std->load_words($ibforums->lang, 'lang_post', $ibforums->lang_id);

		//--------------------------------------
		// Load the contacts
		//--------------------------------------

		$contacts = $this->build_contact_list();

		$stmt = $ibforums->db->query("SELECT mg.*, m.name as to_name, m.id as to_id from ibf_messages mg, ibf_members m WHERE msg_id='" . $ibforums->input['MSID'] . "' and member_id='" . $this->member['id'] . "' AND m.id=mg.recipient_id");
		$msg  = $stmt->fetch();

		if (!$msg['msg_id'])
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_such_msg'));
		}

		//--------------------------------------
		// Build up the HTML for the send form
		//--------------------------------------

		$this->output .= View::make(
			"msg.Send_form",
			[
				'data' => array(
					'CONTACTS' => $contacts,
					'MEMBER'   => $this->member,
					'N_ENTER'  => $msg['to_name'],
					'O_TITLE'  => $msg['title'],
					'OID'      => $msg['msg_id'],
				)
			]
		);

		$ibforums->lang['the_max_length'] = $ibforums->vars['max_post_length'] * 1024;

		$this->output .= View::make("post.get_javascript");

		$this->output .= View::make("post.postbox_buttons", ['data' => str_replace("<br>", "\n", $msg['message'])]);

		$this->output .= View::make("msg.send_form_footer");

		//--------------------------------------
		// Add in the smilies box
		//--------------------------------------

		$this->html_add_smilie_box();

		$this->page_title = $ibforums->lang['t_welcome'];
		$this->nav        = array("<a href='" . $this->base_url . "act=UserCP&amp;CODE=00'>" . $ibforums->lang['t_title'] . "</a>");

		//----------------------------------------
		// Do we have permission to mass PM peeps?
		//----------------------------------------

		if ($ibforums->member['g_max_mass_pm'] > 0)
		{
			$ibforums->lang['carbon_copy_desc'] = sprintf($ibforums->lang['carbon_copy_desc'], $ibforums->member['g_max_mass_pm']);

			if (isset($msg['cc_users']))
			{
				$cc_box = preg_replace("#</textarea>#i", "", $msg['cc_users']);
				$cc_box = str_replace("<br>", "\n", $cc_box);
			}

			$this->output = str_replace("<!--IBF.MASS_PM_BOX-->",
				View::make("msg.mass_pm_box", ['names' => $cc_box]), $this->output);
		}
	}

	//+-----------------------------------------------------------

	function send_msg()
	{
		global $ibforums, $std, $print;

		$ibforums->lang = $std->load_words($ibforums->lang, 'lang_error', $ibforums->lang_id);

		$ibforums->input['from_contact'] = $ibforums->input['from_contact']
			? $ibforums->input['from_contact']
			: '-';

		//----------------------------------------------------------------
		if (mb_strlen($ibforums->input['msg_title']) < 2)
		{
			$this->send_form(0, $ibforums->lang['err_no_title']);
			return;
		}
		//----------------------------------------------------------------
		if (mb_strlen($ibforums->input['Post']) < 2)
		{
			$this->send_form(0, $ibforums->lang['err_no_msg']);
			return;
		}
		//----------------------------------------------------------------
		if ($ibforums->input['from_contact'] == '-' and $ibforums->input['entered_name'] == "")
		{
			$this->send_form(0, $ibforums->lang['err_no_chosen_member']);
			return;
		}
		//----------------------------------------------------------------

		require ROOT_PATH . "/sources/lib/emailer.php";

		$this->email = new emailer();

		//--------------------------------------
		// Attempt to get the reciepient details
		//--------------------------------------

		$to_member = array();

		$ibforums->input['entered_name'] = mb_strtolower(str_replace('|', '&#124;', $ibforums->input['entered_name']));
		$ibforums->input['from_contact'] = mb_strtolower(str_replace('|', '&#124;', $ibforums->input['from_contact']));

		if ($ibforums->input['from_contact'] == '-')
		{
			$query = "LOWER(name)='" . $ibforums->input['entered_name'] . "'";
		} else
		{
			$query = "id='" . $ibforums->input['from_contact'] . "'";
		}

		$stmt = $ibforums->db->query("SELECT name, id, view_pop, mgroup, email_pm, language, email, disable_mail FROM ibf_members WHERE " . $query);

		$to_member = $stmt->fetch();

		if (empty($to_member['id']))
		{
			$this->send_form(0, $ibforums->lang['err_no_such_member']);
			return;
		}

		$ibforums->input['Post'] = $this->parser->convert(array(
		                                                       'TEXT'     => $ibforums->input['Post'],
		                                                       'SMILIES'  => 1,
		                                                       'CODE'     => 1,
		                                                       'HTML'     => 0,
		                                                       'MOD_FLAG' => 0,
		                                                  ));
		//--------------------------------------
		// Is this a preview?
		//--------------------------------------

		if ($ibforums->input['preview'] != "")
		{
			$ibforums->input['MID'] = $to_member['id'];
			$this->send_form(1);
			return;
		}

		//--------------------------------------
		// Are we simply saving this for later?
		//--------------------------------------

		if ($ibforums->input['save'] != "")
		{
			$raw = array(
				'member_id'    => $this->member['id'],
				'msg_date'     => time(),
				'read_state'   => 0,
				'title'        => $ibforums->input['msg_title'],
				'message'      => $ibforums->input['Post'],
				'from_id'      => $this->member['id'],
				'vid'          => 'unsent',
				'recipient_id' => $to_member['id'],
				'cc_users'     => $ibforums->input['carbon_copy']
			);

			$saved = 0;

			if ($ibforums->input['OID'])
			{
				// We have an OID which means that this message
				// is already from the unsent folder, lets check that
				// and if true, update rather than create a new unsent
				// row

				$stmt = $ibforums->db->query("SELECT msg_id FROM ibf_messages WHERE msg_id='" . $ibforums->input['OID'] . "' AND
						   member_id='" . $ibforums->member['id'] . "' AND vid='unsent'");

				if ($stmt->rowCount())
				{
					$saved = 1;

					$ibforums->db->updateRow('ibf_messages', array_map([
					                                                   $ibforums->db,
					                                                   'quote'
					                                                   ], $raw), 'msg_id = ' . $ibforums->db->quote($ibforums->input['OID']));
				}
			}

			if ($saved == 0)
			{
				$ibforums->db->insertRow('ibf_messages', $raw);
			}

			$print->redirect_screen($ibforums->lang['pms_redirect'], "&amp;act=Msg&amp;CODE=01");
		}

		//--------------------------------------
		// Can the reciepient use the PM system?
		//--------------------------------------

		$stmt         = $ibforums->db->query("SELECT m.msg_total, g.g_use_pm, g.g_max_messages FROM ibf_groups g, ibf_members m WHERE m.id='" . $to_member['id'] . "' AND g.g_id=m.mgroup");
		$to_msg_stats = $stmt->fetch();

		if ($to_msg_stats['g_use_pm'] != 1)
		{
			$ibforums->input['MID'] = $to_member['id'];
			$this->send_form(0, $ibforums->lang['no_usepm_member']);
			return;
		}

		//--------------------------------------
		// Does the target member have enough room
		// in their inbox for a new message?
		//--------------------------------------

		if ((($to_msg_stats['msg_total']) >= $to_msg_stats['g_max_messages']) and ($to_msg_stats['g_max_messages'] > 0))
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'max_message_to'));
		}

		//--------------------------------------
		// Has the reciepient blocked us?
		//--------------------------------------

		if (!$ibforums->member['g_is_supmod'])
		{
			$stmt = $ibforums->db->query("SELECT contact_id, allow_msg FROM ibf_contacts WHERE contact_id='" . $this->member['id'] . "' AND member_id='" . $to_member['id'] . "'");

			$can_msg = $stmt->fetch();

			if ((isset($can_msg['contact_id'])) and ($can_msg['allow_msg'] != 1))
			{
				$ibforums->input['MID'] = $to_member['id'];
				$this->send_form(0, $ibforums->lang['msg_blocked']);
				return;
			}
		}

		//--------------------------------------
		// Do we have enough room to store a
		// saved copy?
		//--------------------------------------

		if ($ibforums->input['add_sent'] and ($ibforums->member['g_max_messages'] > 0))
		{
			if (($this->msg_stats['msg_total'] + 1) >= $ibforums->member['g_max_messages'])
			{
				$std->Error(array('LEVEL' => 1, 'MSG' => 'max_message_from'));
			}
		}

		//----------------------------------------------------------------
		// Mass PM stuff
		//----------------------------------------------------------------

		$can_mass_pm = 0;
		$cc_array    = array();

		if ($ibforums->member['g_max_mass_pm'] > 0)
		{
			$can_mass_pm = 1;

			$ibforums->input['carbon_copy'] = mb_strtolower(str_replace('|', '&#124;', $ibforums->input['carbon_copy']));

			if (isset($ibforums->input['carbon_copy']) and $ibforums->input['carbon_copy'] != "")
			{
				// Sort out the array

				$ibforums->input['carbon_copy'] = str_replace("<br><br>", "<br>", trim($ibforums->input['carbon_copy']));
				$ibforums->input['carbon_copy'] = preg_replace("/^(<br>){1}/", "", $ibforums->input['carbon_copy']);
				$ibforums->input['carbon_copy'] = preg_replace("/(<br>){1}$/", "", $ibforums->input['carbon_copy']);
				$ibforums->input['carbon_copy'] = preg_replace("/<br>\s+/", ",", $ibforums->input['carbon_copy']);

				$temp_array = explode("<br>", $ibforums->input['carbon_copy']);

				if (is_array($temp_array) and count($temp_array) > 0)
				{
					$new_array = array();

					foreach ($temp_array as $name)
					{
						$name = "'" . trim(mb_strtolower($name)) . "'";

						if (in_array($name, $new_array))
						{
							continue;
						}

						$new_array[] = $name;
					}
				}

				if (is_array($new_array) and count($new_array) > 0)
				{
					$array_count = count($new_array);

					$stmt = $ibforums->db->query("SELECT m.id, m.name, m.msg_total, m.view_pop, m.email_pm, m.language, m.email, m.disable_mail,
							g.g_max_messages, g.g_use_pm FROM ibf_members m, ibf_groups g
						    WHERE LOWER(m.name) IN (" . implode(",", $new_array) . ") and
							m.mgroup=g.g_id");

					if (!$stmt->rowCount())
					{
						$ibforums->input['MID'] = $to_member['id'];
						$this->send_form(0, $ibforums->lang['pme_no_cc_user']);
						return;
					} else
					{
						while ($r = $stmt->fetch())
						{
							$cc_array[$r['id']] = $r;
						}

						//--------------------------------------

						if (count($cc_array) > $ibforums->member['g_max_mass_pm'])
						{
							$ibforums->input['MID'] = $to_member['id'];
							$this->send_form(0, $ibforums->lang['pme_too_many']);
							return;
						}

						//--------------------------------------

						$cc_error = "";

						if (count($cc_array) != $array_count)
						{
							foreach ($new_array as $n)
							{
								$seen = 0;

								foreach ($cc_array as $idx => $cc_user)
								{
									$tmp = "'" . mb_strtolower($cc_user['name']) . "'";

									if ($tmp == $n)
									{
										$seen = 1;
									}
								}

								if ($seen != 1)
								{
									$cc_error .= "<br>" . sprintf($ibforums->lang['pme_failed_nomem'], $n, $n);
								}
							}
						}

						if ($cc_error != "")
						{
							$ibforums->input['MID'] = $to_member['id'];
							$this->send_form(0, $cc_error);
							return;
						}

						//--------------------------------------

						$cc_error    = "";
						$cc_id_array = array();

						foreach ($cc_array as $idx => $cc_user)
						{
							if ($cc_user['g_use_pm'] != 1)
							{
								$cc_error .= "<br>" . sprintf($ibforums->lang['pme_failed_nopm'], $cc_user['name'], $cc_user['name']);
							}

							if ($cc_user['g_max_messages'] > 0 and ($cc_user['msg_total'] + 1 > $cc_user['g_max_messages']))
							{
								$cc_error .= "<br>" . sprintf($ibforums->lang['pme_failed_maxed'], $cc_user['name'], $cc_user['name']);
							}

							$cc_id_array[] = $cc_user['id'];
						}

						if ($cc_error != "")
						{
							$ibforums->input['MID'] = $to_member['id'];
							$this->send_form(0, $cc_error);
							return;
						}

						//--------------------------------------
						// Almost there! now just to check the block list..
						//--------------------------------------

						$stmt = $ibforums->db->query("SELECT m.name, c.allow_msg FROM ibf_members m, ibf_contacts c
							    WHERE contact_id='" . $ibforums->member['id'] . "' AND
								member_id IN (" . implode(",", $cc_id_array) . ") AND m.id=c.member_id");

						while ($c = $stmt->fetch())
						{
							if ($c['allow_msg'] != 1)
							{
								$cc_error .= "<br>" . sprintf($ibforums->lang['pme_failed_block'], $c['name'], $c['name']);
							}
						}

						if ($cc_error != "")
						{
							$ibforums->input['MID'] = $to_member['id'];
							$this->send_form(0, $cc_error);
							return;
						}

						//--------------------------------------
					}
				}
			}
		}

		//-----------------------------------------
		// Add our original ID to the pool and loop
		//-----------------------------------------

		$cc_array[$to_member['id']] = $to_member;

		unset($to_member);

		$ibforums->input['add_tracking'] = ($ibforums->input['add_tracking'] == 1)
			? 1
			: 0;

		foreach ($cc_array as $user_id => $to_member)
		{
			//--------------------------------------
			// Sort out tracking and pop us status
			//--------------------------------------

			$show_popup = $to_member['view_pop'];

			//--------------------------------------
			// Enter the info into the DB
			// Target user side.
			//--------------------------------------

			$data = [

				'member_id'    => $to_member['id'],
				'msg_date'     => time(),
				'read_state'   => '0',
				'title'        => $ibforums->input['msg_title'],
				'message'      => $std->remove_tags($ibforums->input['Post']),
				'from_id'      => $this->member['id'],
				'vid'          => 'in',
				'recipient_id' => $to_member['id'],
				'tracking'     => $ibforums->input['add_tracking'],
			];

			$stmt   = $ibforums->db->insertRow("ibf_messages", $data);
			$new_id = $ibforums->db->lastInsertId();
			unset($data);

			//-----------------------------------------------------

			$stmt = $ibforums->db->query("UPDATE ibf_members SET " . "msg_total = msg_total + 1, " . "new_msg = new_msg + 1, " . "msg_from_id='" . $this->member['id'] . "', " . "msg_msg_id='" . $new_id . "', " . "show_popup='" . $show_popup . "' " . "WHERE id='" . $to_member['id'] . "'");

			//-----------------------------------------------------
			// Has this member requested a PM email nofity?
			//-----------------------------------------------------

			if ($to_member['email_pm'] == 1 and !$to_member['disable_mail'])
			{
				$to_member['language'] = $to_member['language'] == ""
					? 'en'
					: $to_member['language'];

				$this->email->get_template("pm_notify", $to_member['language']);

				$this->email->build_message(array(
				                                 'NAME'     => $to_member['name'],
				                                 'POSTER'   => $ibforums->member['name'],
				                                 'TITLE'    => $ibforums->input['msg_title'],
				                                 'LINK'     => "?act=Msg&amp;CODE=03&amp;VID=in&amp;MSID=$new_id",
				                                 'MSG_BODY' => $std->remove_tags($ibforums->input['Post'])
				                                 // by Mastilior
				                            ));

				$this->email->build_subject(array(
				                                 'TEMPLATE' => "pm_email_subject",
				                                 'POSTER'   => $ibforums->member['name'],
				                            ));
				$this->email->to = $to_member['email'];
				$this->email->send_mail();
			}
		}

		//-----------------------------------------------------
		// Add the data to the current members DB if we are
		// adding it to our "sent items" folder
		//-----------------------------------------------------

		if ($ibforums->input['add_sent'])
		{

			$stmt = $ibforums->db->query("UPDATE ibf_members SET " . "msg_total = msg_total + 1 " . "WHERE id='" . $this->member['id'] . "'");

			$data = [
				'member_id'    => $this->member['id'],
				'msg_date'     => time(),
				'read_state'   => 1,
				'title'        => $ibforums->lang['saved_sent_msg'] . ' ' . $ibforums->input['msg_title'],
				'message'      => $ibforums->input['Post'],
				'from_id'      => $this->member['id'],
				'vid'          => 'sent',
				'recipient_id' => $to_member['id'],
			];

			$ibforums->db->insertRow('ibf_messages', $data);
			unset($data);
		}

		if ($ibforums->input['OID'])
		{
			// We have an OID which means that this message
			// is already from the unsent folder, if true,
			// delete from unsent items.

			$ibforums->db->exec("DELETE from ibf_messages WHERE msg_id='" . $ibforums->input['OID'] . "' AND member_id='" . $ibforums->member['id'] . "' AND vid='unsent'");
		}

		$text = preg_replace("/<#FROM_MEMBER#>/", $this->member['name'], $ibforums->lang['sent_text']);
		$text = preg_replace("/<#TO_MEMBER#>/", $to_member['name'], $text);
		$text = preg_replace("/<#MESSAGE_TITLE#>/", $ibforums->input['msg_title'], $text);

		$print->redirect_screen($text, "&amp;act=Msg&amp;CODE=01");
	}

	/*	 * ******************************************************* */

	// MSG LIST:
	//
	// Views the inbox / folder of choice
	/*	 * ******************************************************* */

	function msg_list()
	{
		global $ibforums, $std, $print;

		$sort_key = "";

		switch ($ibforums->input['sort'])
		{
			case 'rdate':
				$sort_key = 'm.msg_date ASC';
				break;
			case 'title':
				$sort_key = 'm.title ASC';
				break;
			case 'name':
				$sort_key = 'mp.name ASC';
				break;
			default:
				$sort_key = 'm.msg_date DESC';
				break;
		}

		//---------------------------------------------
		// Get the number of messages we have in total.
		//---------------------------------------------

		$stmt  = $ibforums->db->query("SELECT COUNT(*) as msg_total FROM ibf_messages WHERE member_id='" . $this->member['id'] . "' AND vid <> 'unsent'");
		$total = $stmt->fetch();

		$total['msg_total'] = $total['msg_total'] > 0
			? $total['msg_total']
			: 0;

		$ibforums->db->exec("UPDATE ibf_members SET msg_total='" . $total['msg_total'] . "' WHERE id='" . $this->member['id'] . "'");

		//---------------------------------------------
		// Get the number of messages in our curr folder.
		//---------------------------------------------

		$stmt          = $ibforums->db->query("SELECT COUNT(*) as msg_total FROM ibf_messages WHERE member_id='" . $this->member['id'] . "' AND vid='{$this->vid}'");
		$total_current = $stmt->fetch();

		$total_current['msg_total'] = $total_current['msg_total'] > 0
			? $total_current['msg_total']
			: 0;

		//---------------------------------------------
		// Make sure we've not exceeded our alloted allowance.
		//---------------------------------------------

		$info['full_messenger'] = "<br>";
		$info['full_text']      = "";
		$info['total_messages'] = $total['msg_total'];
		$info['img_width']      = 1;
		$info['vid']            = $this->vid;
		$info['date_order']     = $sort_key == 'm.msg_date DESC'
			? 'rdate'
			: 'msg_date';

		$amount_info = sprintf($ibforums->lang['pmpc_info_string'], $total['msg_total'], $ibforums->lang['pmpc_unlimited']);

		if ($ibforums->member['g_max_messages'] > 0)
		{
			$amount_info = sprintf($ibforums->lang['pmpc_info_string'], $total['msg_total'], $ibforums->member['g_max_messages']);

			$info['full_percent'] = $total['msg_total']
				? sprintf("%.0f", (($total['msg_total'] / $ibforums->member['g_max_messages']) * 100))
				: 0;
			$info['img_width']    = $info['full_percent'] > 0
				? intval($info['full_percent']) * 2.4
				: 1;

			if ($info['img_width'] > 300)
			{
				$info['img_width'] = 300;
			}

			if ($total['msg_total'] >= $ibforums->member['g_max_messages'])
			{
				$info['full_messenger'] = "<span class='highlight'>" . $ibforums->lang['folders_full'] . "</span>";
			} else
			{
				$info['full_messenger'] = str_replace("<#PERCENT#>", $info['full_percent'], $ibforums->lang['pmpc_full_string']);
			}
		}

		//---------------------------------------------
		// Generate Pagination
		//---------------------------------------------

		$start = intval($ibforums->input['st']) > 0
			? intval($ibforums->input['st'])
			: 0;
		$p_end = $ibforums->vars['show_max_msg_list'] > 0
			? $ibforums->vars['show_max_msg_list']
			: 50;

		$pages = $std->build_pagelinks(array(
		                                    'TOTAL_POSS' => $total_current['msg_total'],
		                                    'PER_PAGE'   => $p_end,
		                                    'CUR_ST_VAL' => $start,
		                                    'L_SINGLE'   => "",
		                                    'L_MULTI'    => $ibforums->lang['msg_pages'],
		                                    'BASE_URL'   => $this->base_url . "act=Msg&amp;CODE=1&amp;VID=" . $this->vid . "&amp;sort=" . $ibforums->input['sort'],
		                               ));

		//---------------------------------------------
		// Print the header
		//---------------------------------------------
		//todo the only difference is join condition
		if ($this->vid == 'sent')
		{
			$ibforums->lang['message_from'] = $ibforums->lang['message_to'];
			$stmt = $ibforums->db->query(
				"SELECT
					m.*,
					IFNULL(mp.name,'Unknown member') as from_name,
					IF(mp.id, 0, 1) as member_deleted
				FROM ibf_messages m
				LEFT JOIN ibf_members mp ON (mp.id=m.recipient_id)
				WHERE m.member_id='" . $this->member['id'] . "' AND m.vid='" . $this->vid . "'
				ORDER BY $sort_key LIMIT $start, $p_end"
			);
		} else
		{
			$stmt = $ibforums->db->query("SELECT m.*, IFNULL(mp.name,'Unknown member') as from_name
			            FROM ibf_messages m
				    LEFT JOIN ibf_members mp ON (mp.id=m.from_id)
				    WHERE m.member_id='" . $this->member['id'] . "' AND m.vid='" . $this->vid . "'
				    ORDER BY $sort_key LIMIT $start, $p_end");
		}

		$this->output .= View::make(
			"msg.inbox_table_header",
			[
				'dirname'  => $this->msg_stats['current_dir'],
				'info'     => $info,
				'vdi_html' => $this->jump_html,
				'pages'    => $pages
			]
		);

		//---------------------------------------------
		// Get the messages
		//---------------------------------------------

		if ($stmt->rowCount())
		{
			while ($row = $stmt->fetch())
			{
				if ($this->vid == 'sent')
				{
					$row['icon'] = "<{M_READ}>";
				} else
				{
					$row['icon'] = $row['read_state'] == 1
						? "<{M_READ}>"
						: "<{M_UNREAD}>";
				}

				$row['date'] = $std->get_date($row['msg_date']);

				if ($this->vid == 'sent')
				{
					$row['from_id'] = $row['recipient_id'];
				}

				$d_array = array('msg' => $row, 'member' => $this->member, 'stat' => $this->msg_stats);

				$this->output .= View::make("msg.inbox_row", ['data' => $d_array]);
			}
		} else
		{
			$this->output .= View::make("msg.No_msg_inbox");
		}

		$this->output .= View::make(
			"msg.end_inbox",
			['vdi_html' => $this->jump_html, 'amount_info' => $amount_info, 'pages' => $pages]
		);

		//---------------------------------------------
		// Update the message stats if we have to
		//---------------------------------------------

		if ($this->msg_stats['current_id'] == 'in')
		{
			$ibforums->db->exec("UPDATE ibf_members SET new_msg='0' WHERE id='" . $this->member['id'] . "'");
		}

		$this->page_title = $ibforums->lang['t_welcome'];

		$this->nav = array("<a href='" . $this->base_url . "act=UserCP&amp;CODE=00'>" . $ibforums->lang['t_title'] . "</a>");
	}

	//+------------------------------------------------------------------------------------

	/*	 * ******************************************************* */
	// VIEW SAVED:
	//
	// View the saved folder stuff.
	/*	 * ******************************************************* */

	function view_saved()
	{
		global $ibforums, $std, $print;

		//---------------------------------------------
		// Print the header
		//---------------------------------------------

		$this->output .= View::make("msg.unsent_table_header");

		$stmt = $ibforums->db->query("SELECT m.*, mp.name as to_name FROM ibf_messages m, ibf_members mp WHERE member_id='" . $this->member['id'] . "' AND vid='unsent' and mp.id=m.recipient_id ORDER BY msg_date DESC");

		//---------------------------------------------
		// Get the messages
		//---------------------------------------------

		if ($stmt->rowCount())
		{
			while ($row = $stmt->fetch())
			{
				$row['icon']     = "<{M_READ}>";
				$row['date']     = $std->get_date($row['msg_date']);
				$row['cc_users'] = $row['cc_users'] == ""
					? $ibforums->lang['no']
					: $ibforums->lang['yes'];

				$d_array = array('msg' => $row, 'member' => $this->member);

				$this->output .= View::make("msg.unsent_row", ['data' => $d_array]);
			}
		} else
		{
			$this->output .= View::make("msg.No_msg_inbox");
		}

		$this->output .= View::make("msg.unsent_end");

		$this->page_title = $ibforums->lang['t_welcome'];
		$this->nav        = array("<a href='" . $this->base_url . "act=UserCP&amp;CODE=00'>" . $ibforums->lang['t_title'] . "</a>");
	}

	function show_tracking()
	{
		global $ibforums, $std, $print;

		//---------------------------------------------
		// Get all tracked and read messages
		//---------------------------------------------

		$this->output .= View::make("msg.trackread_table_header");

		$stmt = $ibforums->db->query("SELECT m.*, mp.name as to_name, mp.id as memid
 					  FROM ibf_messages m, ibf_members mp
 					WHERE m.tracking=1
 					   AND m.from_id='" . $this->member['id'] . "'
 					   AND m.member_id=mp.id
 					ORDER BY m.read_state DESC, msg_date DESC");

		if ($stmt->rowCount())
		{
			$current = "";

			$change = FALSE;

			while ($row = $stmt->fetch())
			{
				if ($row['read_state'] != $current and !$row['read_state'])
				{
					if ($current == "")
					{
						$this->output .= View::make("msg.No_msg_inbox");
					}

					$this->output .= View::make("msg.trackread_end");
					$this->output .= View::make("msg.trackUNread_table_header");
					$change = TRUE;
				}

				if ($row['read_state'])
				{
					$row['icon'] = "<{M_READ}>";
					$row['date'] = $std->get_date($row['read_date']);
					$this->output .= View::make("msg.trackread_row", ['data' => $row]);
				} else
				{
					$row['icon'] = "<{M_UNREAD}>";
					$row['date'] = $std->get_date($row['msg_date']);
					$this->output .= View::make("msg.trackUNread_row", ['data' => $row]);
				}

				$current = $row['read_state'];
			}

			if (!$change)
			{
				$this->output .= View::make("msg.trackread_end");
				$this->output .= View::make("msg.trackUNread_table_header");
				$this->output .= View::make("msg.No_msg_inbox");
			}

			$this->output .= View::make("msg.trackUNread_end");
		} else
		{
			$this->output .= View::make("msg.No_msg_inbox");
			$this->output .= View::make("msg.trackread_end");

			$this->output .= View::make("msg.trackUNread_table_header");
			$this->output .= View::make("msg.No_msg_inbox");
			$this->output .= View::make("msg.trackUNread_end");
		}

		$this->page_title = $ibforums->lang['t_welcome'];

		$this->nav = array("<a href='" . $this->base_url . "act=UserCP&amp;CODE=00'>" . $ibforums->lang['t_title'] . "</a>");
	}

	/*	 * ****************************************************************** */

	// Parse the member info
	/*	 * ****************************************************************** */

	function parse_member($member = array(), $row = array())
	{
		global $ibforums, $std;

		$member['avatar'] = $std->get_avatar($member['avatar'], $ibforums->member['view_avs'], $member['avatar_size']);

		if ($member['g_icon'])
		{
			$member['member_rank_img'] = "<img src='{$ibforums->vars['TEAM_ICON_URL']}/{$member['g_icon']}' border='0' />";
		}

		$member['member_joined'] = $ibforums->lang['m_joined'] . ' ' . $std->format_date_without_time($member['joined']);

		$member['member_group'] = $ibforums->lang['m_group'] . ' ' . $member['g_title'];

		$member['member_posts'] = $ibforums->lang['m_posts'] . ' ' . $std->do_number_format($member['posts']);
		// Song
		//Reputation
		if (empty($member['rep']))
		{
			$member['rep'] = 0;
		}
		if ($ibforums->vars['rep_goodnum'] and $member['rep'] >= $ibforums->vars['rep_goodnum'])
		{
			$member['title'] = $ibforums->vars['rep_goodtitle'] . ' ' . $member['title'];
		}
		if ($ibforums->vars['rep_badnum'] and $member['rep'] <= $ibforums->vars['rep_badnum'])
		{
			$member['title'] = $ibforums->vars['rep_badtitle'] . ' ' . $member['title'];
		}
		//Reputation
		// Song

		$member['member_number'] = $ibforums->lang['member_no'] . ' ' . $std->do_number_format($member['id']);

		$member['profile_icon'] = "<a href='{$this->base_url}showuser={$member['id']}'><{P_PROFILE}></a>";

		$member['message_icon'] = "<a href='{$this->base_url}act=Msg&amp;CODE=04&amp;MID={$member['id']}'><{P_MSG}></a>";

		if (!$member['hide_email'])
		{
			$member['email_icon'] = "<a href='{$this->base_url}act=Mail&amp;CODE=00&amp;MID={$member['id']}'><{P_EMAIL}></a>";
		}

		if ($member['website'] and preg_match("/^http:\/\/\S+$/", $member['website']))
		{
			$member['website_icon'] = "<a href='{$member['website']}' target='_blank'><{P_WEBSITE}></a>";
		}

		if ($member['icq_number'])
		{
			$member['icq_icon'] = "<a href=\"javascript:PopUp('{$this->base_url}act=ICQ&amp;MID={$member['id']}','Pager','450','330','0','1','1','1')\"><{P_ICQ}></a>";
		}

		if ($member['aim_name'])
		{
			$member['aol_icon'] = "<a href=\"javascript:PopUp('{$this->base_url}act=AOL&amp;MID={$member['id']}','Pager','450','330','0','1','1','1')\"><{P_AOL}></a>";
		}

		if ($member['yahoo'])
		{
			$member['yahoo_icon'] = "<a href=\"javascript:PopUp('{$this->base_url}act=YAHOO&amp;MID={$member['id']}','Pager','450','330','0','1','1','1')\"><{P_YIM}></a>";
		}

		if ($member['msnname'])
		{
			$member['msn_icon'] = "<a href=\"javascript:PopUp('{$this->base_url}act=MSN&amp;MID={$member['id']}','Pager','450','330','0','1','1','1')\"><{P_MSN}></a>";
		}

		if ($member['integ_msg'])
		{
			$member['integ_icon'] = "<a href=\"javascript:PopUp('{$this->base_url}act=integ&amp;MID={$member['id']}','Pager','750','450','0','1','1','1')\"><{INTEGRITY_MSGR}></a>";
		}

		if ($ibforums->member['id'])
		{
			$member['addresscard'] = "<a href=\"javascript:PopUp('{$this->base_url}act=Profile&amp;CODE=showcard&amp;MID={$member['id']}','AddressCard','470','300','0','1','1','1')\" title='{$ibforums->lang['ac_title']}'><{ADDRESS_CARD}></a>";
		}

		//-----------------------------------------------------

		return $member;
	}

	function html_add_smilie_box()
	{
		global $ibforums, $std;

		$show_table = 0;
		$count      = 0;
		$smilies    = "<tr align='center'>\n";

		// Get the smilies from the DB
		// Song * smile skin

		if (!$ibforums->member['id'])
		{
			$id = 1;
		} else
		{
			$id = $ibforums->member['sskin_id'];
		}
		if (!$id)
		{
			$id = 1;
		}

		$stmt = $ibforums->db->query("SELECT typed, image from ibf_emoticons WHERE clickable='1' and skid='" . $id . "'");

		// Song * smile skin

		while ($elmo = $stmt->fetch())
		{

			$show_table++;
			$count++;

			// Make single quotes as URL's with html entites in them
			// are parsed by the browser, so ' causes JS error :o

			if (mb_strstr($elmo['typed'], "&#39;"))
			{
				$in_delim  = '"';
				$out_delim = "'";
			} else
			{
				$in_delim  = "'";
				$out_delim = '"';
			}

			if (!$ibforums->member['id'])
			{
				$sskin = 'Main';
			} else
			{
				$sskin = $ibforums->member['sskin_name'];
				if (!$ibforums->member['view_img'] or $ibforums->member['sskin_id'] == 0)
				{
					$sskin = 0;
				}
			}

			if ($sskin)
			{
				$smile = "<img src='{$ibforums->vars['board_url']}/smiles/$sskin/" . $elmo['image'] . "' alt='{$elmo['typed']}' border='0'>";
			} else
			{
				$smile = $elmo['typed'];
			}

			$smilies .= "<td><a href={$out_delim}javascript:emoticon($in_delim" . $elmo['typed'] . "$in_delim){$out_delim}>{$smile}</a>&nbsp;</td>\n";

			if ($count == $ibforums->vars['emo_per_row'])
			{
				$smilies .= "</tr>\n\n<tr align='center'>";
				$count = 0;
			}
		}

		if ($count != $ibforums->vars['emo_per_row'])
		{
			for ($i = $count; $i < $ibforums->vars['emo_per_row']; ++$i)
			{
				$smilies .= "<td>&nbsp;</td>\n";
			}
			$smilies .= "</tr>";
		}

		$table = View::make("post.smilie_table");

		if ($show_table != 0)
		{
			$table        = preg_replace("/<!--THE SMILIES-->/", $smilies, $table);
			$this->output = preg_replace("/<!--SMILIE TABLE-->/", $table, $this->output);
		}
	}

	function build_contact_list()
	{
		$ibforums = Ibf::app();

		$contacts = "";

		$stmt = $ibforums->db->query("SELECT * FROM ibf_contacts WHERE member_id='" . $this->member['id'] . "' ORDER BY contact_name");

		if ($stmt->rowCount())
		{
			$contacts = "<select name='from_contact' class='forminput'><option value='-'>" . $ibforums->lang['other'] . "</option>\n<option value='-'>--------------------</option>\n";

			while ($entry = $stmt->fetch())
			{
				$contacts .= "<option value='" . $entry['contact_id'] . "'>" . $entry['contact_name'] . "</option>\n";
			}

			$contacts .= "</select>\n";
		} else
		{
			$contacts = $ibforums->lang['address_list_empty'];
		}

		return $contacts;
	}

}
