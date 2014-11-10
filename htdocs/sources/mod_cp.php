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
|   > Moderation Control Panel module
|   > Module written by Matt Mecham
|   > Date started: 19th February 2002 / Revised Start: 23rd September
|
|   > Module Version 2.0.0
+--------------------------------------------------------------------------
*/
use Skins\Views\View;

$idx = new Moderate;

class Moderate
{

	var $output = "";
	var $base_url = "";
	var $html = "";

	var $moderator = array();
	var $forum = array();
	var $topic = array();
	var $tids = array();
	//Jureth - multisource: forums of $tids. Must be tid=>forum_id indexed array.
	var $tids_forums = array();

	var $forums = array();
	var $children = array();
	var $cats = array();

	var $upload_dir = "";

	var $topic_id = "";
	var $forum_id = "";
	var $post_id = "";
	var $start_val = 0;
	var $pass = 0;

	var $modfunc = "";
	var $mm_id = "";

	/***********************************************************************************/
	//
	// Our constructor, load words, load skin, print the topic listing
	//
	/***********************************************************************************/

	function Moderate()
	{
		global $ibforums, $std, $print;

		//-------------------------------------
		// Compile the language file
		//-------------------------------------

		$ibforums->lang = $std->load_words($ibforums->lang, 'lang_modcp', $ibforums->lang_id);
		$ibforums->lang = $std->load_words($ibforums->lang, 'lang_topic', $ibforums->lang_id);

		//--------------------------------------------
		// Get the sync module
		//--------------------------------------------

		if (USE_MODULES == 1)
		{
			require ROOT_PATH . "modules/ipb_member_sync.php";

			$this->modules = new ipb_member_sync();
		}

		//-------------------------------------
		// Check the input
		//-------------------------------------

		if (intval($ibforums->input['forum']))
		{
			$ibforums->input['f']    = intval($ibforums->input['forum']);
			$ibforums->input['CODE'] = 'showtopics';
		}

		$this->forum_id  = intval($ibforums->input['f']);
		$this->start_val = intval($ibforums->input['st']);
		$this->topic_id  = intval($ibforums->input['t']);
		$this->post_id   = intval($ibforums->input['p']);

		$this->base_url = $ibforums->base_url;

		//-------------------------------------
		// Make sure we're a moderator...
		//-------------------------------------

		$this->pass = 0;

		if ($ibforums->member['id'])
		{
			if ($ibforums->member['g_is_supmod'] == 1)
			{
				$this->pass = 1;

			} elseif ($ibforums->member['is_mod'])
			{
				// Load mod..

				// If we're not just viewing the forum list, then check the incoming forum ID and
				// ensure that they have mod powers

				if ($this->forum_id)
				{
					$qe = ' forum_id=' . $this->forum_id . ' AND ';
				} else
				{
					$qe = "";
				}
				$select = "SELECT *
				   FROM ibf_moderators
				   WHERE
					$qe (member_id='" . $ibforums->member['id'] . "' OR
					    (is_group=1 AND group_id='" . $ibforums->member['mgroup'] . "'))";
				$stmt   = $ibforums->db->query($select);

				//echo "mid:".$ibforums->member['id']." sup_mod:".$ibforums->member['g_is_supmod']." is_mod:".$ibforums->member['is_mod']." select:".$select."<br>";
				if ($this->moderator = $stmt->fetch())
				{
					//echo "has mod rights!<br>";
					$this->pass = 1;
				}

			} else {
				$this->pass = 0;
			}
		}

		// Song * mod access

		if (!$this->pass and !($ibforums->member['id'] and (mb_strpos($ibforums->input['CODE'], "ip") !== FALSE or $ibforums->input['CODE'] == "topicchoice")))
		{
			if (!$ibforums->member['is_mod'])
			{
				$std->Error(array('LEVEL' => 1, 'MSG' => 'no_permission'));
			} else
			{
				$this->forum_id           = "";
				$ibforums->input['forum'] = "";
				$ibforums->input['f']     = "";
				$ibforums->input['CODE']  = "";
			}
		}

		// Song * mod access

		//-------------------------------------
		// Load mod module...
		//-------------------------------------

		require(ROOT_PATH . 'sources/lib/modfunctions.php');

		$this->modfunc = new modfunctions();

		//-------------------------------------
		// Finish up set_up
		//-------------------------------------

		$this->upload_dir = $ibforums->vars['upload_dir'];

		$this->upload_dir = preg_replace("!/$!", "", $this->upload_dir);

		//-------------------------------------
		// Convert the code ID's into something
		// use mere mortals can understand....
		//-------------------------------------

		switch ($ibforums->input['CODE'])
		{

			case 'members':
				$this->find_user_one();
				break;
			case 'edituser':
				$this->find_user_one(); // Left for backwards compatibility
				break;
			case 'dofinduser':
				$this->find_user_two();
				break;
			case 'doedituser':
				$this->edit_user();
				break;
			case 'compedit':
				$this->complete_user_edit();
				break;

			//-------------------------

			case 'prune':
				$this->prune_juice();
				break;
			case 'doprune':
				$this->bulk_topic_remove(); // eew!
				break;
			case 'domove':
				$this->do_move();
				break;

			//-------------------------

			case 'domodtopics':
				$this->domod_topics();
				break;

			case 'domodposts':
				$this->mod_domodposts();
				break;

			//-------------------------

			case 'topicchoice':

				/** <--- Jureth --- * Multisource */
				$this->tids_forums = $this->get_tids();
				$this->tids        = array_keys($this->tids_forums);
				//Jureth			$this->load_forum();
				if (!$ibforums->input['f'])
				{
					//we can't load all forums, so load the first captured
					$this->load_forum(reset($this->tids_forums));
				} else
				{
					$this->load_forum();
				}
				/* >--- Jureth --- */

				switch ($ibforums->input['tact'])
				{
					case 'close':
						$this->alter_topics('close_topic', "state='closed'");
						break;
					case 'open':
						$this->alter_topics('open_topic', "state='open'");
						break;
					case 'pin':
						$this->alter_topics('pin_topic', "pinned=1");
						break;
					case 'unpin':
						$this->alter_topics('unpin_topic', "pinned=0");
						break;
					case 'approve':
						$this->alter_topics('topic_q', "approved=1");
						break;
					case 'decline':
						$this->alter_topics('topic_q', "approved=0");
						break;
					case 'hide':
						$this->alter_topics('hide_topic', "hidden=1");
						break;
					case 'show':
						$this->alter_topics('hide_topic', "hidden=0");
						break;
					case 'delete':
						$this->delete_topics();
						break;
					case 'move':
						$this->start_checked_move();
						break;
					case 'domove':
						$this->complete_checked_move();
						break;
					default:
						$this->topic_mmod();
						break; // Yeah, like it'll get here (Added 21st May: Ooh, we will now!)
				}

				break;

			//-------------------------

			case 'showforums':
				$this->show_forums();
				break;
			case 'ip':
				$this->ip_start();
				break;
			case 'doip':
				$this->do_ip();
				break;
			case 'add_ip':
				$this->add_ip();
				break;
			case 'remove_ip':
				$this->remove_ip();
				break;

			case 'highlight':
				$this->syntax_start();
				break;
			case 'syntax_set':
				$this->syntax_set();
				break;
			case 'syntax_rule':
				$this->syntax_rule();
				break;
			case 'syntax_edit':
				$this->syntax_edit();
				break;
			case 'syntax_order':
				$this->syntax_order();
				break;
			case 'rules_edit' :
				$this->rules_edit();
				break;
			case 'rules_select' :
				$this->rules_edit(1);
				break;
			case 'do_rules_apply' :
				$this->do_rules_edit();
				break;
			case 'multi_mod' :
				$this->multi_mod();
				break;
			default:
				$std->Error(array('LEVEL' => 1, 'MSG' => 'no_permission'));
				break;
		}

		if (count($this->nav) < 1)
		{
			$this->nav[] = "<a href='{$this->base_url}&act=modcp'>{$ibforums->lang['cp_modcp_home']}</a>";
		}

		if (!$this->page_title)
		{
			$this->page_title = $ibforums->lang['cp_modcp_ptitle'];
		}

		$print->add_output("$this->output");

		$print->do_output(array('TITLE' => $this->page_title, 'NAV' => $this->nav));

	}

	//-------------------------------------------------
	// MULTI-MOD!
	//-------------------------------------------------

	function topic_mmod()
	{
		global $std, $ibforums, $print;

		//---------------------------------------
		// Issit coz i is black?
		//---------------------------------------

		if (!mb_strstr($ibforums->input['tact'], 't_'))
		{
			$this->mod_error('stupid_beggar');
		}

		$this->mm_id = intval(str_replace('t_', "", $ibforums->input['tact']));

		//----------------------------------------
		// Init modfunc module
		//----------------------------------------

		$this->modfunc->init($this->forum, "", $this->moderator);

		//----------------------------------------
		// Do we have permission?
		//----------------------------------------

		if ($this->modfunc->mm_authorize() != TRUE)
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'cp_no_perms'
			            ));
		}

		//-------------------------------------
		// Does this forum have this mm_id
		//-------------------------------------

		if ($this->modfunc->mm_check_id_in_forum($this->forum['topic_mm_id'], $this->mm_id) != TRUE)
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'no_mmid'
			            ));
		}

		//-------------------------------------
		// Still here? We're damn good to go sir!
		//-------------------------------------

		$this->parser = new PostParser(1);

		$stmt = $ibforums->db->query("SELECT *
		    FROM ibf_topic_mmod
		    WHERE mm_id={$this->mm_id}");

		if (!$this->mm_data = $stmt->fetch())
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'no_mmid'
			            ));
		}

		$this->modfunc->stm_init();

		//-------------------------------------
		// Open close?
		//-------------------------------------

		if ($this->mm_data['topic_state'] != 'leave')
		{
			if ($this->mm_data['topic_state'] == 'close')
			{
				$this->modfunc->stm_add_close();
			} else {
				if ($this->mm_data['topic_state'] == 'open')
				{
					$this->modfunc->stm_add_open();
				}
			}
		}

		//-------------------------------------
		// pin no-pin?
		//-------------------------------------

		if ($this->mm_data['topic_pin'] != 'leave')
		{
			if ($this->mm_data['topic_pin'] == 'pin')
			{
				$this->modfunc->stm_add_pin();
			} else {
				if ($this->mm_data['topic_pin'] == 'unpin')
				{
					$this->modfunc->stm_add_unpin();
				}
			}
		}

		//-------------------------------------
		// Update what we have so far...
		//-------------------------------------

		$this->modfunc->stm_exec($this->tids);

		//-------------------------------------
		// Topic title (1337 - I am!)
		//-------------------------------------

		$pre = "";
		$end = "";

		if ($this->mm_data['topic_title_st'])
		{
			$pre = preg_replace("/'/", "\\'", $this->mm_data['topic_title_st']);
		}

		if ($this->mm_data['topic_title_end'])
		{
			$end = preg_replace("/'/", "\\'", $this->mm_data['topic_title_end']);

		}

		$ibforums->db->exec("UPDATE ibf_topics
		    SET title=CONCAT('$pre', title, '$end')
		    WHERE tid IN(" . implode(",", $this->tids) . ")");

		//-------------------------------------
		// Add reply?
		//-------------------------------------

		if ($this->mm_data['topic_reply'] and
		    $this->mm_data['topic_reply_content']
		)
		{
			$move_ids = array();

			foreach ($this->tids as $tid)
			{
				$move_ids[] = array($tid, $this->tids_forums[$tid]);
			}

			// Turn off auto forum re-synch,
			// we'll manually do it at the end
			$this->modfunc->auto_update = FALSE;

			$this->modfunc->topic_add_reply($this->parser->convert(array(
			                                                            'TEXT'    => $this->mm_data['topic_reply_content'],
			                                                            'CODE'    => 1,
			                                                            'SMILIES' => 1,
			                                                       )), $move_ids, $this->mm_data['topic_reply_postcount']);
		}

		//-------------------------------------
		// Move topic?
		//-------------------------------------

		if ($this->mm_data['topic_move'])
		{
			//-------------------------------------
			// Move to forum still exist?
			//-------------------------------------

			$stmt = $ibforums->db->query("SELECT
				id,
				name,
				subwrap,
				sub_can_post
			    FROM ibf_forums
			    WHERE id=" . $this->mm_data['topic_move']);

			if ($r = $stmt->fetch())
			{
				if ($r['subwrap'] == 1 AND $r['sub_can_post'] != 1)
				{
					$ibforums->db->exec("UPDATE ibf_topic_mmod
					    SET topic_move=0
					    WHERE mm_id=" . $this->mm_id);
				} else
				{
					if (!in_array($r['id'], $this->tids_forums))
					{
						$this->modfunc->topic_move($this->tids, $this->tids_forums, $r['id'], $this->mm_data['topic_move_link']);

						$this->modfunc->forum_recount($r['id']);
					}
				}
			} else
			{
				$ibforums->db->exec("UPDATE ibf_topic_mmod
				    SET topic_move=0
				    WHERE mm_id=" . $this->mm_id);
			}
		}

		//-------------------------------------
		// Recount root forum
		//-------------------------------------

		//Jureth: useless code - multimod can't be started for different forums simultaneously
		if ($this->tids_forums)
		{
			foreach ($this->tids_forums as $tf)
			{
				$this->modfunc->forum_recount($tf);
			}
		} else
		{
			$this->modfunc->forum_recount($this->forum['id']);
		}

		$stmt = $ibforums->db->query("SELECT name from ibf_forums where id IN( " . implode($this->tids_forums) . ");");
		while ($r = $stmt->fetch())
		{
			$this->moderate_log("Applied multi-mod '{$this->mm_data['mm_title']}' on forum {$r['name']}");
		}

		$print->redirect_screen($ibforums->lang['mm_redirect'], "act=modcp&CODE=showtopics&f=" . $this->forum['id']);

	}

	//-------------------------------------------------
	// IP STUFF!
	//-------------------------------------------------

	function upper_check()
	{
		global $ibforums;

		if (!$ibforums->member['is_mod'] or !$this->forum_id)
		{
			return FALSE;
		}

		$stmt = $ibforums->db->query("SELECT
			DISTINCT(md.member_id) as mid,
			md.view_ip as ip
			FROM ibf_moderators md, ibf_forums_order f
		    WHERE f.pid='" . $this->forum_id . "' and f.id=md.forum_id");

		while ($forum = $stmt->fetch())
		{
			if ($forum['mid'] == $ibforums->member['id'])
			{
				return $forum['ip'];
			}
		}

		return FALSE;

	}

	function ip_start()
	{
		global $std, $ibforums, $print;

		$pass = 0;

		if ($ibforums->member['g_is_supmod'])
		{
			$pass = 1;

		} elseif ($this->moderator['view_ip'])
		{
			$pass = 1;
		} else
		{
			$pass = $this->upper_check();
		}

		if (!$pass)
		{
			$this->mod_error('cp_no_perms');
			return;
		}

		$ip_arr = array();

		if ($ibforums->input['incoming'])
		{
			$ip_arr = explode(".", $ibforums->input['incoming']);
		}

		if ($ibforums->member['g_is_supmod'])
		{
			$query = "SELECT
					ibf_members.name as m_name,
					ibf_ip_table.id,
					f.name as f_name,
                                        ok1,ok2,ok3,ok4,
					IF(NOT IsNULL(comment),Concat(' - ',comment),'') as comment,
					ok1+0 as o1,
					ok2+0 as o2,
					ok3+0 as o3,
					ok4+0 as o4
				  FROM (
					ibf_members,
					ibf_ip_table
					)
                                  LEFT JOIN ibf_forums f
					ON (f.id=ibf_ip_table.fid)
				  WHERE ibf_members.id=ibf_ip_table.mid
				  ORDER BY o1,o2,o3,o4";
		} else
		{
			$query = "SELECT
					ibf_ip_table.id,
					f.name as f_name,
					ok1,ok2,ok3,ok4,
				  	IF(NOT IsNULL(comment),Concat(' - ',comment),'') as comment,
					ok1+0 as o1,
					ok2+0 as o2,
					ok3+0 as o3,
					ok4+0 as o4
				  FROM ibf_ip_table
                                  LEFT JOIN ibf_forums f
					ON (f.id=ibf_ip_table.fid)
				  WHERE mid='" . $ibforums->member['id'] . "'
				  ORDER BY o1,o2,o3,o4";
		}

		$forum = "<td class='row1'>";

		if ($ibforums->member['g_is_supmod'] and $this->forum_id)
		{
			$stmt = $ibforums->db->query("SELECT name
				    FROM ibf_forums
				    WHERE id='" . $this->forum_id . "'");

			if ($i = $stmt->fetch())
			{
				$forum = View::Make("modcp.ip_select_region", ['forum' => $i['name']]);
			}
		}

		$stmt = $ibforums->db->query($query);

		$add_ip = ($this->forum_id)
			? View::Make("modcp.add_ip", ['ip_addr' => $ip_arr,'select' => $forum])
			: View::Make("modcp.add_ip_no");

		$checkboxes = ($this->forum_id)
			? View::Make("modcp.search_ip_checkboxes")
			: "";

		$ip_list = "";

		while ($i = $stmt->fetch())
		{
			if (!$i['f_name'])
			{
				$i['f_name'] = $ibforums->lang['ip_select_all'];
			}

			$selected = "";

			if ($i['ok1'] . "." . $i['ok2'] . "." . $i['ok3'] . "." . $i['ok4'] == $ibforums->input['incoming'])
			{
				$selected = ' selected="selected"';
			}

			if ($ibforums->member['g_is_supmod'])
			{
				$ip_list .= "<option value=\"{$i['id']}\"" . $selected . ">{$i['ok1']} . {$i['ok2']} . {$i['ok3']} . {$i['ok4']} --> {$i['m_name']} --> {$i['f_name']}{$i['comment']}</option>\n";
			} else
			{
				$ip_list .= "<option value=\"{$i['id']}\"" . $selected . ">{$i['ok1']} . {$i['ok2']} . {$i['ok3']} . {$i['ok4']} --> {$i['f_name']}{$i['comment']}</option>\n";
			}
		}

		$this->output .= View::Make("modcp.ip_start_form", ['ip_addr' => $ip_arr,'add_ip' => $add_ip,'ip' => $ip_list,'select' => $forum,'checkboxes' => $checkboxes]);
	}

	//-------------------------------------------------------------------------------

	function remove_ip()
	{
		global $std, $ibforums, $print;

		$pass = 0;

		if ($ibforums->member['g_is_supmod'])
		{
			$pass = 1;

		} elseif ($this->moderator['view_ip'])
		{
			$pass = 1;
		} else
		{
			$pass = $this->upper_check();
		}

		if (!$pass)
		{
			$this->mod_error('cp_no_perms');
			return;
		}

		if (!$ibforums->input['ip_select'])
		{
			$this->mod_error('cp_ip_no');
			return;
		}

		$ibforums->db->exec("DELETE
			    FROM ibf_ip_table
			    WHERE id='" . $ibforums->input['ip_select'] . "'");

		$print->redirect_screen($ibforums->lang['p_moved'], "act=modcp&auth_key={$ibforums->input['s']}&CODE=ip");

	}

	function add_ip()
	{
		global $std, $ibforums, $print;

		$pass = 0;

		if ($ibforums->member['g_is_supmod'])
		{
			$pass = 1;

		} elseif ($this->moderator['view_ip'])
		{
			$pass = 1;
		} else
		{
			$pass = $this->upper_check();
		}

		if (!$pass)
		{
			$this->mod_error('cp_no_perms');
			return;
		}

		if (!$this->forum_id)
		{
			$this->mod_error('cp_ip_no');
			return;
		}

		if (!$ibforums->member['g_is_supmod'] and
		    ($ibforums->input['ip1'] == '*' or
		     $ibforums->input['ip2'] == '*' or
		     $ibforums->input['ip3'] == '*')
		)
		{
			$this->mod_error('cp_no_perms');
			return;
		}

		// select id of parent forum
		$stmt = $ibforums->db->query("SELECT parent_id
			    FROM ibf_forums
			    WHERE id='" . $this->forum_id . "'");

		$forum = $stmt->fetch();

		$fid = intval($forum['parent_id']);

		if (!$ibforums->member['g_is_supmod'])
		{
			$stmt = $ibforums->db->query("SELECT mid
				    FROM ibf_moderators
				    WHERE
					member_id='" . $ibforums->member['id'] . "' and
					forum_id='" . $this->forum_id . "'");

			if (!$stmt->rowCount() and !$this->upper_check())
			{
				$this->mod_error('cp_no_perms');
				return;
			}

		} elseif ($ibforums->input['region'])
		{
			$this->forum_id = 0;
		}

		// look may be this address already has been added
		$stmt = $ibforums->db->query("SELECT id
			    FROM ibf_ip_table
			    WHERE
				(fid='" . $fid . "' or fid='" . $this->forum_id . "' or fid=0) and
				(ok1='" . $ibforums->input['ip1'] . "' or ok1='*') and
				(ok2='" . $ibforums->input['ip2'] . "' or ok2='*') and
				(ok3='" . $ibforums->input['ip3'] . "' or ok3='*') and
				(ok4='" . $ibforums->input['ip4'] . "' or ok4='*')
			    LIMIT 1");

		if ($stmt->rowCount())
		{
			$stmt->closeCursor();

			$this->mod_error('cp_ip_dup');
			return;
		}

		$ibforums->db->exec("INSERT INTO ibf_ip_table
				(mid,fid,ok1,ok2,ok3,ok4,comment)
			    VALUES (
				'" . $ibforums->member['id'] . "',
				'" . $this->forum_id . "',
				'" . $ibforums->input['ip1'] . "',
				'" . $ibforums->input['ip2'] . "',
				'" . $ibforums->input['ip3'] . "',
				'" . $ibforums->input['ip4'] . "',
				'" . $ibforums->input['comment'] . "')");

		$print->redirect_screen($ibforums->lang['p_moved'], "act=modcp&auth_key={$ibforums->input['s']}&CODE=ip&incoming=" . $ibforums->input['ip1'] . "." . $ibforums->input['ip2'] . "." . $ibforums->input['ip3'] . "." . $ibforums->input['ip4'] . "&f={$ibforums->input['f']}");
	}

	function do_ip()
	{
		global $std, $ibforums, $print;

		$pass = 0;

		if ($ibforums->member['g_is_supmod'])
		{
			$pass = 1;

		} elseif ($this->moderator['view_ip'])
		{
			$pass = 1;
		} else
		{
			$pass = $this->upper_check();
		}

		if (!$pass)
		{
			$this->mod_error('cp_no_perms');
			return;
		}

		// check to make sure we have enough input.

		$ip_array     = array();
		$ip_bit_count = 0; // init var to count how many "real" IP bits we have

		foreach (array('ip1', 'ip2', 'ip3', 'ip4') as $ip_bit)
		{
			if ($ibforums->input[$ip_bit] != '*')
			{
				$ibforums->input[$ip_bit] = intval($ibforums->input[$ip_bit]);

				if (!isset($ibforums->input[$ip_bit]))
				{
					continue;
				}

				if ($ibforums->input[$ip_bit] < 1)
				{
					$ibforums->input[$ip_bit] = 0;
				}

				$ip_array[$ip_bit] = $ibforums->input[$ip_bit];
				$ip_bit_count++;
			} else
			{
				$ip_array[$ip_bit] = '*';
			}
		}

		// ensure we have at least 127.*

		if (count($ip_array) < 2)
		{
			$this->mod_error('cp_error_ip');
			return;
		}

		// ensure we don't have *.*

		if ($ip_bit_count < 1)
		{
			$this->mod_error('cp_error_ip');
			return;
		}

		$test_ip_string = $ip_array['ip1'] . '%' . $ip_array['ip2'] . '%' . $ip_array['ip3'] . '%' . $ip_array['ip4'];

		// Check to make sure we don't have 123%*%123%0%0
		// or similar (of course)
		// Test for *%({numeric})  (*.127 for example...)

		if (preg_match("/\*%\d+(%|$)/", $test_ip_string))
		{
			$this->mod_error('cp_error_ip');
			return;
		}

		// Ok, lets finalize the IP string, using the * as the stop character

		$final_ip_string = "";
		$exact_match     = 1;

		foreach ($ip_array as $final_bits)
		{
			if ($final_bits == '0')
			{
				$final_ip_string .= '0.';

			} elseif ($final_bits == '*')
			{
				$final_ip_string .= "%"; //SQL find any
				$exact_match = 0;
				break; // break out of foreach as we're done
			} else
			{
				$final_ip_string .= $final_bits . '.';
			}
		}

		// Remove trailing periods

		$final_ip_string = preg_replace("/\.$/", "", $final_ip_string);

		//print $final_ip_string."<br>".$test_ip_string."<br>".implode('.', $ip_array); exit();

		// See, a gazillion lines of code just to ensure that the user read the frikken manual.

		// H'okay, what have we been asked to do? (that's a metaphorical "we" in a rhetorical question)

		if ($ibforums->input['iptool'] == 'resolve')
		{
			// Attempt a trival gethostbyaddr

			if ($ip_bit_count != 4)
			{
				$this->mod_error('cp_error_resolveip');
				return;
			}

			$resolved = @gethostbyaddr($final_ip_string);

			if (!$resolved)
			{
				$this->mod_error('cp_safe_fail');
				return;
			} else
			{
				$ibforums->lang['ip_resolve_result'] = sprintf($ibforums->lang['ip_resolve_result'], $final_ip_string, $resolved, $final_ip_string);

				$this->output .= View::Make("modcp.mod_simple_page", ['title' => $ibforums->lang['cp_results'],'msg' => $ibforums->lang['ip_resolve_result']]);

				return TRUE;

			}

		} elseif ($ibforums->input['iptool'] == 'members')
		{
			if (!$exact_match)
			{
				$sql = "p.ip_address LIKE '" . $final_ip_string . "'";
			} else
			{
				$sql = "p.ip_address='" . $final_ip_string . "'";
			}

			if ($ibforums->input['ip_sub'] and $this->forum_id)
			{
				if ($ibforums->input['ip_sub_include'])
				{
					$sub = array();

					$sub[] = $this->forum_id;

					$stmt = $ibforums->db->query("SELECT id
						    FROM ibf_forums_order
						    WHERE pid='" . $this->forum_id . "'");

					while ($row = $stmt->fetch())
					{
						$sub[] = $row['id'];
					}

					if (count($sub) == 1)
					{
						$sql .= " and p.forum_id='" . $this->forum_id . "'";
					} else
					{
						$sql .= " and p.forum_id IN (" . implode(",", $sub) . ")";
					}

				} else {
					$sql .= " and p.forum_id='" . $this->forum_id . "'";
				}
			}

			// Get forums we're allowed to view
			$aforum = array();

			$stmt = $ibforums->db->query("SELECT id, read_perms
				    FROM ibf_forums");

			while ($f = $stmt->fetch())
			{
				if ($std->check_perms($f['read_perms']) != TRUE)
				{
					$aforum[] = $f['id'];
				}
			}

			if (count($aforum))
			{
				$sql .= " and not (p.forum_id IN (" . implode(",", $aforum) . "))";
			}

			$guests = (!$ibforums->input['ip_sub_guests'])
				? " and p.author_id != 0"
				: "";

			$stmt = $ibforums->db->query("SELECT
					Count(DISTINCT(p.author_id)) as max
				    FROM ibf_posts p
				    WHERE " . $sql . $guests);

			$total_possible = $stmt->fetch();

			if ($total_possible['max'] < 1)
			{
				$this->mod_error('cp_no_matches');
				return;
			}

			$pages = $std->build_pagelinks(array(
			                                    'TOTAL_POSS' => $total_possible['max'],
			                                    'PER_PAGE'   => 50,
			                                    'CUR_ST_VAL' => $this->start_val,
			                                    'L_SINGLE'   => $ibforums->lang['single_page_forum'],
			                                    'L_MULTI'    => $ibforums->lang['multi_page_forum'],
			                                    'BASE_URL'   => $this->base_url . "act=modcp&CODE=doip&iptool=members&ip1={$ibforums->input['ip1']}&ip2={$ibforums->input['ip2']}&ip3={$ibforums->input['ip3']}&ip4={$ibforums->input['ip4']}",
			                               ));
			$this->output .= View::Make("modcp.ip_member_start", ['pages' => $pages]);

			$stmt = $ibforums->db->query("SELECT
					IFNULL(m.id,1) as id,
					IFNULL(m.name,p.author_name) as name,
					p.ip_address,
					IF(IsNULL(m.posts),'--',m.posts) as posts,
					m.joined,
					m.mgroup
				    FROM ibf_posts p
				    LEFT JOIN ibf_members m
					ON (m.id=p.author_id and p.author_id != 0)
				    WHERE
					p.queued != 1 and " . $sql . $guests . "
				    GROUP BY p.author_name
				    LIMIT {$this->start_val},50");

			while ($row = $stmt->fetch())
			{
				if ($row['mgroup'] == $ibforums->vars['admin_group'])
				{
					$row['ip_address'] = "<i>скрыто</i>";
				}

				$row['joined'] = $std->format_date_without_time($row['joined']);
				$this->output .= View::Make("modcp.ip_member_row", ['row' => $row]);
			}

			$this->output .= View::Make("modcp.ip_member_end", ['pages' => $pages]);
		} else
		{
			// Find posts then!

			if (!$exact_match)
			{
				$sql = "ip_address LIKE '$final_ip_string'";
			} else
			{
				$sql = "ip_address='$final_ip_string'";
			}

			if ($ibforums->input['ip_sub'] and $this->forum_id)
			{
				if ($ibforums->input['ip_sub_include'])
				{
					$sub = array();

					$sub[] = $this->forum_id;

					$stmt = $ibforums->db->query("SELECT id FROM ibf_forums_order WHERE pid='" . $this->forum_id . "'");

					while ($row = $stmt->fetch())
					{
						$sub[] = $row['id'];
					}

					if (count($sub) == 1)
					{
						$sql .= " and forum_id='" . $this->forum_id . "'";
					} else
					{
						$sql .= " and forum_id IN (" . implode(",", $sub) . ")";
					}

				} else {
					$sql .= " and forum_id='" . $this->forum_id . "'";
				}
			}

			// Get forums we're allowed to view
			$aforum = array();

			$stmt = $ibforums->db->query("SELECT
					id,
					read_perms
				    FROM ibf_forums");

			while ($f = $stmt->fetch())
			{
				if ($std->check_perms($f['read_perms']) != TRUE)
				{
					$aforum[] = $f['id'];
				}
			}

			if (count($aforum))
			{
				$sql .= " and not (forum_id IN (" . implode(",", $aforum) . "))";
			}

			$stmt = $ibforums->db->query("SELECT pid
				    FROM ibf_posts
				    WHERE
					queued <> 1 AND
					$sql");

			$max_hits = $stmt->rowCount();

			$posts = "";

			while ($row = $stmt->fetch())
			{
				$posts .= $row['pid'] . ",";
			}

			$stmt->closeCursor();

			$posts = preg_replace("/,$/", "", $posts);

			//------------------------------------------------
			// Do we have any results?
			//------------------------------------------------

			if (!$posts)
			{
				$this->mod_error('cp_no_matches');
				return;
			}

			//------------------------------------------------
			// If we are still here, store the data into the database...
			//------------------------------------------------

			$unique_id = md5(uniqid(microtime(), 1));

			$data = [
				'id'          => $unique_id,
				'search_date' => time(),
				'post_id'     => $posts,
				'post_max'    => $max_hits,
				'sort_key'    => 'p.post_date',
				'sort_order'  => 'desc',
				'member_id'   => $ibforums->member['id'],
				'ip_address'  => $ibforums->input['IP_ADDRESS'],
			];

			$ibforums->db->insertRow("ibf_search_results", $data);

			$this->output .= View::Make("modcp.mod_simple_page", ['title' => $ibforums->lang['cp_results'],'msg' => View::Make("modcp.ip_post_results", ['uid' => $unique_id,'count' => $max_hits])]);

			return TRUE;
		}
	}

	//-------------------------------------------------
	// Complete move dUdE
	//-------------------------------------------------

	function complete_checked_move()
	{
		global $std, $ibforums, $print;

		$pass = 0;

		if ($ibforums->member['g_is_supmod'])
		{
			$pass = 1;

		} elseif ($this->moderator['move_topic'])
		{
			$pass = 1;
		} else
		{
			$pass = 0;
		}

		if (!$pass or !count($this->tids))
		{
			$this->mod_error('cp_no_perms');
			return;
		}

		$dest_id = intval($ibforums->input['df']);
		/* <--- Jureth --- */
		//		$source_id = $this->forum['id'];
		if (count($this->tids_forums))
		{
			//topic sources already checked. We can use them safetly
			$source_id = $this->tids_forums;
		} else
		{
			$source_id = array_fill_keys($this->tids, $this->forum['id']);
		}

		//----------------------------------
		// Check for input..
		//----------------------------------

		//Jureth	if ( !$source_id )
		if (!reset($source_id))
		{
			$this->mod_error('cp_error_move');
			return;
		}

		//----------------------------------

		if (!$dest_id or !$dest_id)
		{
			$this->mod_error('cp_error_move');
			return;
		}

		//----------------------------------

		//Jureth		if ( $source_id == $dest_id )
		if (in_array($dest_id, $source_id))
		{
			$this->mod_error('cp_error_move');
			return;
		}

		//----------------------------------

		$stmt = $ibforums->db->query("SELECT
				id,
				subwrap,
				sub_can_post,
				name
			    FROM ibf_forums
			    WHERE id IN(" . implode(",", $source_id) . "," . $dest_id . ")");
		//Jureth		    WHERE id IN(".$source_id.",".$dest_id.")");

		//Jureth	if ($stmt->rowCount() != 2)
		if ($stmt->rowCount() < 2)
		{
			$this->mod_error('cp_error_move');
			return;
		}

		//Jureth	$source_name = "";
		$source_name = array();
		$dest_name   = "";

		//-----------------------------------
		// Check for an attempt to move into a subwrap forum
		//-----------------------------------

		while ($f = $stmt->fetch())
		{
			//Jureth		if ($f['id'] == $source_id)
			if ($f['id'] == $dest_id)
			{
				//Jureth			$source_name = $f['name'];
				$dest_name = $f['name'];
			} else
			{
				//Jureth			$dest_name = $f['name'];
				$source_name[] = $f['name'];
			}

			if ($f['subwrap'] == 1 and $f['sub_can_post'] != 1)
			{
				$this->mod_error('cp_error_move');
				return;
			}
		}

		//---------------------------------
		// God, I'm lazy....
		//----------------------------------

		$source = $source_id;
		$moveto = $dest_id;

		$this->modfunc->topic_move($this->tids, $source, $moveto);

		//----------------------------------
		// Resync the forums..
		//----------------------------------

		//Jureth	$this->modfunc->forum_recount($source);
		foreach ($source as $s)
		{
			$this->modfunc->forum_recount($s);
		}

		$this->modfunc->forum_recount($moveto);

		//Jureth	$this->moderate_log("Moved topics from $source_name to $dest_name");
		foreach ($source_name as $s)
		{
			$this->moderate_log("Moved topics from $s to $dest_name");
		}

		$print->redirect_screen($ibforums->lang['cp_redirect_topics'], $this->redirect());

	}

	//-------------------------------------------------
	// Start move form
	//-------------------------------------------------

	function start_checked_move()
	{
		global $std, $ibforums, $print;

		$pass = 0;

		if ($ibforums->member['g_is_supmod'])
		{
			$pass = 1;

		} elseif ($this->moderator['move_topic'])
		{
			$pass = 1;
		} else
		{
			$pass = 0;
		}

		if (!$pass or !count($this->tids))
		{
			$this->mod_error('cp_no_perms');
			return;
		}

		$jump_html = $std->build_forum_jump('no_html');

		$this->output .= View::Make("modcp.move_checked_form_start", ['forum_name' => $this->forum['name'],'fid' => $this->forum['id']]);

		$stmt = $ibforums->db->query("SELECT
				tid,
				title
			    FROM ibf_topics
			    WHERE tid IN(" . implode(",", $this->tids) . ")");

		while ($row = $stmt->fetch())
		{
			$this->output .= View::Make("modcp.move_checked_form_entry", ['tid' => $row['tid'],'title' => $row['title']]);
		}

		$this->output .= View::Make("modcp.move_checked_form_end", ['jump_html' => $jump_html]);

	}

	//-------------------------------------------------
	// Delete topics, groovy.
	//-------------------------------------------------

	function delete_topics()
	{
		global $std, $ibforums, $print;

		$pass = 0;

		if ($ibforums->member['g_is_supmod'])
		{
			$pass = 1;

		} elseif ($this->moderator['delete_topic'])
		{
			$pass = 1;
		} else
		{
			$pass = 0;
		}

		if (!$pass or !count($this->tids))
		{
			$this->mod_error('cp_no_perms');
			return;
		}

		// удалить зеркала
		$topic_ids = implode(",", array_keys($this->tids_forums));
		$stmt      = $ibforums->db->query("SELECT tid,forum_id FROM ibf_topics WHERE state='mirror' AND mirrored_topic_id IN ($topic_ids)");
		$tmp_forum = $this->modfunc->forum;
		while ($row = $stmt->fetch())
		{
			$this->modfunc->forum['id'] = $row['forum_id'];
			$this->modfunc->topic_delete($row['tid']);
		}
		$this->modfunc->forum = $tmp_forum;

		$this->modfunc->topic_delete($this->tids_forums);

		$this->moderate_log("Deleted topics from Mod CP (IDs: " . implode(",", $this->tids) . ")");

		$print->redirect_screen($ibforums->lang['cp_redirect_topics'], $this->redirect());

	}

	function add_redirect_part($part, &$result)
	{
		global $ibforums;

		if ($ibforums->input[$part])
		{
			$result .= "&" . $part . "=" . $ibforums->input[$part];
		}

	}

	function redirect()
	{
		global $ibforums;

		//Jureth	$result = "showforum=".$this->forum['id'];

		//Jureth	$this->add_redirect_part("view", &$result);
		//Jureth	$this->add_redirect_part("prune_day", &$result);
		//Jureth	$this->add_redirect_part("sort_by", &$result);
		//Jureth	$this->add_redirect_part("sort_key", &$result);
		//Jureth	$this->add_redirect_part("st", &$result);

		//Jureth
		if ("search" == $ibforums->input['old_act'])
		{
			$result = "act=Search&CODE=show";
			$this->add_redirect_part("searchid", $result);
			$this->add_redirect_part("search_in", $result);
			$this->add_redirect_part("result_type", $result);
			$this->add_redirect_part("new", $result);
			$this->add_redirect_part("hl", $result);
		} else
		{
			$result = "showforum=" . $this->forum['id'];

			$this->add_redirect_part("view", $result);
			$this->add_redirect_part("prune_day", $result);
			$this->add_redirect_part("sort_by", $result);
			$this->add_redirect_part("sort_key", $result);
			$this->add_redirect_part("st", $result);
		}

		return $result;

	}

	//-------------------------------------------------
	// Alter the topics, yay!
	//-------------------------------------------------

	function alter_topics($mod_action = "", $sql = "")
	{
		global $std, $ibforums, $print;

		//echo "action:".$mod_action.":".$this->moderator[ $mod_action ]." pass:".$this->pass."<br>";
		//		$pass = 0;

		//		if ( $ibforums->member['g_is_supmod'] )
		//		{
		//			$pass = 1;
		//
		//		} elseif ( $this->moderator[ $mod_action ] )
		//		{
		//			$pass = 1;
		//		} else
		//		{
		//			$pass = 0;
		//		}

		//echo "mod_action:".$mod_action." sql:".$sql." count:".count($this->tids)."<br>";
		if (!$this->pass or
		    !$mod_action or
		    !$sql or
		    !count($this->tids)
		)
		{
			//echo "has NO rights<br>";
			$this->mod_error('cp_no_perms');
			return;
		}

		if ($mod_action == "pin_topic")
		{
			$sql .= ", pinned_date='" . time() . "'";
		}
		$append_where = '';
		if ($mod_action == "close_topic" || $mod_action == 'open_topic')
		{
			$append_where .= ' AND state <> \'mirror\'';
		}

		$ibforums->db->exec("UPDATE ibf_topics
			    SET $sql
			    WHERE tid IN(" . implode(",", $this->tids) . ") $append_where");

		$this->moderate_log("Altered topics ($sql) (" . implode(",", $this->tids) . ") ");

		//echo "Action done ok.<br>";

		$print->redirect_screen($ibforums->lang['cp_redirect_topics'], $this->redirect());

	}

	//-------------------------------------------------
	// Display the forums we're allowed to manage, yay!
	//--------------------------------------------------

	function show_forums()
	{
		global $std, $ibforums, $print;

		$ibforums->lang = $std->load_words($ibforums->lang, 'lang_boards', $ibforums->lang_id);

		$this->nav[] = "<a href='{$this->base_url}&act=modcp'>{$ibforums->lang['cp_modcp_home']}</a>";
		$this->nav[] = $ibforums->lang['menu_forums'];
	}

	//--------------------------------------------------

	function mod_domodposts()
	{
		global $std, $ibforums, $print;

		if (!($ibforums->member['g_is_supmod'] or
		      $this->moderator['post_q'])
		)
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'no_permission'
			            ));
		}

		$this->load_forum();

		//--------------------------------------------------
		// Which TID's are we playing with?
		//--------------------------------------------------

		$delete_ids  = array();
		$approve_ids = array();

		foreach ($ibforums->input as $key => $value)
		{
			if (preg_match("/^PID_(\d+)$/", $key, $match))
			{
				if ($ibforums->input[$match[0]] == 'approve')
				{
					$approve_ids[] = $match[1];
				} else {
					if ($ibforums->input[$match[0]] == 'remove')
					{
						$delete_ids[] = $match[1];
					}
				}
			}
		}

		//--------------------------------------------------
		// Did we actually select anyfink?
		//--------------------------------------------------

		$total = count($delete_ids) + count($approve_ids);

		if ($total < 1)
		{
			$this->mod_error('cp_error_no_topics');
			return;
		}

		//--------------------------------------------------
		// What did we do?
		//--------------------------------------------------

		if (count($approve_ids) > 0)
		{
			// Sort out the approved bit

			$pids = implode(",", $approve_ids);

			$pid_count = count($approve_ids);

			$ibforums->db->exec("UPDATE ibf_topics
				    SET posts=posts+$pid_count
				    WHERE tid='" . $ibforums->input['tid'] . "'");

			$ibforums->db->exec("UPDATE ibf_posts
				    SET queued=0
				    WHERE pid IN ($pids)");

			// Update the posters ..er.. post count.

			$stmt = $ibforums->db->query("SELECT author_id
				    FROM ibf_posts
				    WHERE
					queued <> 1 and
					pid IN ($pids)");

			$mems = array();

			while ($r = $stmt->fetch())
			{
				if ($r['author_id'] > 0)
				{
					$mems[] = $r['author_id'];
				}
			}

			if (count($mems) > 0)
			{
				if ($this->forum['inc_postcount'])
				{
					$mstring = implode(",", $mems);

					//-----------------------------------
					// Get the groups..
					//-----------------------------------

					$groups = array();

					$stmt = $ibforums->db->query("SELECT *
						    FROM ibf_groups");

					while ($g = $stmt->fetch())
					{
						$groups[$g['g_id']] = $g;
					}

					$stmt = $ibforums->db->query("SELECT
							id,
							mgroup,
							posts
						 FROM ibf_members
						 WHERE id IN ($mstring)");

					while ($member = $stmt->fetch())
					{
						//-----------------------------------
						// Are we auto_promoting?
						//-----------------------------------

						if ($groups[$member['mgroup']]['g_promotion'] != '-1&-1')
						{
							list($gid, $gposts) = explode('&', $groups[$member['mgroup']]['g_promotion']);

							if ($gid > 0 and $gposts > 0)
							{
								if ($member['posts'] + 1 >= $gposts)
								{
									$mgroup = "mgroup='$gid', ";

									if (USE_MODULES == 1)
									{
										$this->modules->register_class($class);
										$this->modules->on_group_change($ibforums->member['id'], $gid);
									}
								}
							}
						}

						$ibforums->db->exec("UPDATE ibf_members
						 SET
							$mgroup
							posts=posts+1
						 WHERE id={$member['id']}");
					}
				}
			}

			// Update the last topic poster,
			// time and number of posts.

			$stmt = $ibforums->db->query("SELECT
					author_id,
					author_name,
					post_date
				    FROM ibf_posts
				    WHERE
					topic_id='" . $ibforums->input['tid'] . "' AND
					queued <> 1
				    ORDER BY pid DESC
				    LIMIT 0,1");

			if ($last = $stmt->fetch())
			{
				$data = [
					'last_post'        => $ibforums->db->quote($last['post_date']),
					'last_poster_id'   => $ibforums->db->quote($last['author_id']),
					'last_poster_name' => $ibforums->db->quote($last['author_name']),
				];

				$ibforums->db->updateRow("ibf_topics", $data, "tid=" . $ibforums->db->quote($ibforums->input['tid']));
			}

		}

		if (count($delete_ids) > 0)
		{
			// Sort out the approved bit

			$pids = implode(",", $delete_ids);

			// Delete 'dem postings

			$ibforums->db->exec("UPDATE ibf_posts SET use_sig = 2, edit_time='" . time() . "', delete_after='" . strtotime('+180 days') . "'
				    WHERE pid IN ($pids)");

			$topic           = new topic;
			$topic->tid      = $this->topic_id;
			$topic->forum_id = $this->forum_id;
			$topic->update_last_post_time();

			// vot: Remove search words for this topics

			$std->index_del_posts($pids);
		}

		// Recount..

		//Jureth		$this->modfunc->forum_recount_queue($this->forum['id']);
		$this->modfunc->forum_recount($this->forum['id']);
		$this->modfunc->stats_recount();

		// Boink

		if (!$ibforums->input['alter'])
		{
			$path = "showtopic={$ibforums->input['tid']}";
		} else
		{
			$path = "showtopic={$ibforums->input['tid']}&view=findpost&p={$ibforums->input['alter']}";
		}

		$print->redirect_screen($ibforums->lang['cp_redirect_mod_topics'], $path, "html");

	}

	//--------------------------------------------------
	// MODERATE NEW TOPICS AND STUFF
	//--------------------------------------------------

	function domod_topics()
	{
		global $std, $ibforums, $print;

		//--------------------------------------------------
		// Which TID's are we playing with?
		//--------------------------------------------------
		if (!($ibforums->member['g_is_supmod'] or $this->moderator['topic_q']))
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_permission'));
		}

		$this->load_forum();

		$delete_ids  = array();
		$approve_ids = array();

		$first_tid = 0;
		foreach ($ibforums->input as $key => $value)
		{
			if (preg_match("/^TID_(\d+)$/", $key, $match))
			{
				if ($ibforums->input[$match[0]] == 'approve')
				{
					$approve_ids[] = $match[1];
					if (!$first_tid)
					{
						$first_tid = $match[1];
					}
				} else {
					if ($ibforums->input[$match[0]] == 'remove')
					{
						$delete_ids[] = $match[1];
					}
				}
			}
		}

		//--------------------------------------------------
		// Did we actually select anyfink?
		//--------------------------------------------------

		$total = count($delete_ids) + count($approve_ids);

		if ($total < 1)
		{
			$this->mod_error('cp_error_no_topics');
			return;
		}

		//--------------------------------------------------
		// What did we do?
		//--------------------------------------------------

		if (count($approve_ids) > 0)
		{
			// Sort out the approved bit

			$tids = implode(",", $approve_ids);

			// Sort out the approved bit

			$ibforums->db->exec("UPDATE ibf_topics
				    SET approved=1
				    WHERE tid IN ($tids)");

			$ibforums->db->exec("UPDATE ibf_posts
				    SET queued=0
				    WHERE topic_id IN ($tids)");

			// Update the posters ..er.. post count.

			$stmt = $ibforums->db->query("SELECT starter_id
				    FROM ibf_topics
				    WHERE tid IN ($tids)");

			$mems = array();

			while ($r = $stmt->fetch())
			{
				if ($r['starter_id'] > 0)
				{
					$mems[] = $r['starter_id'];
				}
			}

			if (count($mems) > 0)
			{
				if ($this->forum['inc_postcount'])
				{
					$mstring = implode(",", $mems);

					//-----------------------------------
					// Get the groups..
					//-----------------------------------

					$groups = array();

					$stmt = $ibforums->db->query("SELECT *
						    FROM ibf_groups");

					while ($g = $stmt->fetch())
					{
						$groups[$g['g_id']] = $g;
					}

					$stmt = $ibforums->db->query("SELECT
							id,
							mgroup,
							posts
						 FROM ibf_members
						 WHERE id IN ($mstring)");

					while ($member = $stmt->fetch())
					{
						//-----------------------------------
						// Are we auto_promoting?
						//-----------------------------------

						if ($groups[$member['mgroup']]['g_promotion'] != '-1&-1')
						{
							list($gid, $gposts) = explode('&', $groups[$member['mgroup']]['g_promotion']);

							if ($gid > 0 and $gposts > 0)
							{
								if ($member['posts'] + 1 >= $gposts)
								{
									$mgroup = "mgroup='$gid', ";

									if (USE_MODULES == 1)
									{
										$this->modules->register_class($class);
										$this->modules->on_group_change($ibforums->member['id'], $gid);
									}
								}
							}
						}

						$ibforums->db->exec("UPDATE ibf_members
							 SET
								$mgroup
								posts=posts+1
							 WHERE id={$member['id']}");
					}
				}
			}

		}

		if (count($delete_ids) > 0)
		{
			// Sort out the approved bit

			$tids = implode(",", $delete_ids);
			// Delete 'dem postings

			$ibforums->db->exec("DELETE FROM ibf_topics
				    WHERE tid IN ($tids)");

			$ibforums->db->exec("DELETE FROM ibf_posts
				    WHERE topic_id IN ($tids)");

			//----------------------------------------
			// vot: Remove search words for this posts

			$std->index_del_posts($pids);

			//----------------------------------------
			// vot: Remove search words for this topics

			$std->index_del_topics("IN(" . $tids . ")");

		}

		// Recount..

		//Jureth		$this->modfunc->forum_recount_queue($this->forum['id']);
		$this->modfunc->forum_recount($this->forum['id']);
		$this->modfunc->stats_recount();

		// Boink

		if ($first_tid)
		{
			$path = "showtopic={$first_tid}";
		} else
		{
			$path = "showforum={$this->forum['id']}";
		}

		$print->redirect_screen($ibforums->lang['cp_redirect_mod_topics'], $path, "html");

	}

	/**
	 *  функция для массового удаления топиков по ряду заданных условий:
	 *  - по дате последнего поста (типа устаревшие топики)
	 *  - по ИД стартера
	 *  - по минимальному количеству постов в топике (например, все топики без ответов)
	 *  - по статусу топика
	 *  - доп. признак: - игнорировать прикрепленные топики или нет
	 *
	 *
	 */
	function bulk_topic_remove()
	{
		global $std, $ibforums, $print;

		$this->load_forum();

		$pass = 0;

		if ($ibforums->member['g_is_supmod'] == 1)
		{
			$pass = 1;
		} else {
			if ($this->moderator['mass_prune'] == 1)
			{
				$pass = 1;
			} else
			{
				$pass = 0;
			}
		}

		if ($pass == 0)
		{
			$this->mod_error('cp_no_perms');
			return;
		}

		//-----------------------------------------------
		// Check auth key
		//-----------------------------------------------

		if ($ibforums->input['key'] != $std->return_md5_check())
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'del_post'));
		}

		//-----------------------------------------------
		// Carry on...
		//-----------------------------------------------
		//todo what is it?
		$db_query = $this->modfunc->sql_prune_create($this->forum['id'], $ibforums->input['starter'], $ibforums->input['state'], $ibforums->input['posts'], $ibforums->input['dateline'], $ibforums->input['ignore_pin']);

		$stmt = $ibforums->db->query($db_query);

		if (!$num_rows = $stmt->rowCount())
		{
			$this->mod_error('cp_error_no_topics');
			return;
		}

		$tid_array = array();

		while ($tid = $stmt->fetch())
		{
			$tid_array[] = $tid['tid'];
		}

		$tid_array = array_fill_keys($tid_array, $this->forum['id']); //Jureth: multisource
		$this->modfunc->topic_delete($tid_array);

		$this->moderate_log("Pruned Forum");

		// Show results..

		$this->output .= View::Make("modcp.mod_simple_page", ['title' => $ibforums->lang['cp_results'],'msg' => $ibforums->lang['cp_result_del'] . $num_rows]);

	}

	//--------------------------------------------------
	// Prune Forum start
	//--------------------------------------------------

	function prune_juice()
	{
		global $std, $ibforums, $print;

		$this->load_forum();

		$pass = 0;

		if ($ibforums->member['g_is_supmod'] == 1)
		{
			$pass = 1;
		} else {
			if ($this->moderator['mass_prune'] == 1)
			{
				$pass = 1;
			} else
			{
				$pass = 0;
			}
		}

		if ($pass == 0)
		{
			$this->mod_error('cp_no_perms');
			return;
		}

		//-----------------------------------------------

		if ($ibforums->input['check'] == 1)
		{

			$link      = "";
			$link_text = $ibforums->lang['cp_prune_dorem'];

			$stmt   = $ibforums->db->query("SELECT COUNT(tid) as tcount
				    FROM ibf_topics
				    WHERE
					approved=1 and
					forum_id='" . $this->forum['id'] . "'");
			$tcount = $stmt->fetch();

			$db_query = "SELECT COUNT(*) as count
				     FROM ibf_topics
				     WHERE
					approved=1 and
					forum_id='" . $this->forum['id'] . "'";

			if ($ibforums->input['dateline'])
			{
				$date = time() - $ibforums->input['dateline'] * 60 * 60 * 24;
				$db_query .= " AND last_post < $date";

				$link .= "&dateline=$date";
			}

			if ($ibforums->input['member'])
			{
				$stmt = $ibforums->db->query("SELECT id
					    FROM ibf_members
					    WHERE name='" . $ibforums->input['member'] . "'");

				if (!$mem = $stmt->fetch())
				{
					$this->mod_error('cp_error_no_mem');
					return;
				} else
				{
					$db_query .= " AND starter_id='" . $mem['id'] . "'";
					$link .= "&starter={$mem['id']}";
				}
			}

			if ($ibforums->input['posts'])
			{
				$db_query .= " AND posts < '" . $ibforums->input['posts'] . "'";
				$link .= "&posts={$ibforums->input['posts']}";
			}

			if ($ibforums->input['topic_type'] != 'all')
			{
				$db_query .= " AND state='" . $ibforums->input['topic_type'] . "'";
				$link .= "&state={$ibforums->input['topic_type']}";
			}

			if ($ibforums->input['ignore_pin'] == 1)
			{
				$db_query .= " AND pinned <> 1";
				$link .= "&ignore_pin=1";
			}

			$stmt  = $ibforums->db->query($db_query);
			$count = $stmt->fetch();

			if ($ibforums->input['df'] == 'prune')
			{
				$link = "&act=modcp&f={$this->forum['id']}&CODE=doprune&" . $link;
			} else
			{
				if ($ibforums->input['df'] == $this->forum['id'])
				{
					$this->mod_error('cp_same_forum');
					return;
				} else {
					if ($ibforums->input['df'] == -1)
					{
						$this->mod_error('cp_no_forum');
						return;
					}
				}

				$link      = "&act=modcp&f={$this->forum['id']}&CODE=domove&df=" . $ibforums->input['df'] . $link;
				$link_text = $ibforums->lang['cp_prune_domove'];
			}

			$confirm_html = View::Make("modcp.prune_confirm", ['tcount' => $tcount['tcount'],'count' => $count['count'],'link' => $link,'link_text' => $link_text,'key' => $std->return_md5_check()]);

		}

		$select = "<select name='topic_type' class='forminput'>";

		foreach (array('open', 'closed', 'link', 'all') as $type)
		{
			if ($ibforums->input['topic_type'] == $type)
			{
				$selected = ' selected';
			} else
			{
				$selected = '';
			}

			$select .= "<option value='$type'" . $selected . ">" . $ibforums->lang['cp_pday_' . $type] . "</option>";
		}

		$select .= "</select>\n";

		$forums = "<option value='prune'>{$ibforums->lang['cp_ac_prune']}</option>";

		$forums .= $std->build_forum_jump(0, 0, 1);

		if ($ibforums->input['df'])
		{
			$forums = preg_replace("/<option value=\"" . $ibforums->input['df'] . "\"/", "<option value=\"" . $ibforums->input['df'] . "\" selected", $forums);
		}

		$this->output .= View::Make("modcp.prune_splash", ['forum' => $this->forum,'forums' => $forums,'select' => $select]);

		if ($confirm_html)
		{
			$this->output = preg_replace("/<!-- IBF\.CONFIRM -->/", "$confirm_html", $this->output);
		}

	}

	//--------------------------------------------------
	// Find a user to edit, dude.
	//--------------------------------------------------

	function find_user_one()
	{
		global $std, $ibforums, $print;

		$pass = 0;

		if ($ibforums->member['g_is_supmod'] == 1)
		{
			$pass = 1;
		} else {
			if ($this->moderator['edit_user'] == 1)
			{
				$pass = 1;
			} else
			{
				$pass = 0;
			}
		}

		if ($pass == 0)
		{
			$this->mod_error('cp_no_perms');
			return;
		}

		$this->output .= View::Make("modcp.find_user");
	}

	function find_user_two()
	{
		global $std, $ibforums, $print;

		if ($ibforums->input['name'] == "")
		{
			$this->mod_error('cp_no_matches');
			return;
		}

		//---------------------------------
		// Query the DB for possible matches
		//---------------------------------

		$stmt = $ibforums->db->query("SELECT
				id,
				name
			    FROM ibf_members
			    WHERE name LIKE '" . $ibforums->input['name'] . "%'
			    LIMIT 0,100");

		if ($stmt->rowCount())
		{
			$select = "<select name='memberid' class='forminput'>";

			while ($member = $stmt->fetch())
			{
				$select .= "\n<option value='" . $member['id'] . "'>" . $member['name'] . "</option>";
			}

			$select .= "</select>";

			$this->output .= View::Make("modcp.find_two", ['select' => $select]);
		} else
		{
			$this->mod_error('cp_no_matches');
			return;
		}
	}

	function edit_user()
	{
		global $std, $ibforums, $print;

		$pass = 0;

		if ($ibforums->member['g_is_supmod'] == 1)
		{
			$pass = 1;
		} else {
			if ($this->moderator['edit_user'] == 1)
			{
				$pass = 1;
			} else
			{
				$pass = 0;
			}
		}

		if ($pass == 0)
		{
			$this->mod_error('cp_no_perms');
			return;
		}

		if ($ibforums->input['memberid'] == "")
		{
			$this->mod_error('cp_no_matches');
			return;
		}

		//--------------------------------------------------

		$stmt = $ibforums->db->query("SELECT
				m.*,
				g.*
			    FROM
				ibf_members m,
				ibf_groups g
			    WHERE
				m.id='" . $ibforums->input['memberid'] . "' AND
				m.mgroup=g.g_id");

		if (!$member = $stmt->fetch())
		{
			$this->mod_error('cp_no_matches');
			return;
		}

		//--------------------------------------------------
		// No editing of admins!
		//--------------------------------------------------

		if ($ibforums->member['g_access_cp'] != 1)
		{
			if ($member['g_access_cp'] == 1)
			{
				$this->mod_error('cp_admin_user');
				return;
			}
		}

		$parser = new PostParser();

		$editable['signature'] = $parser->unconvert($member['signature']);
		$editable['location']  = $member['location'];
		$editable['interests'] = $member['interests'];
		$editable['website']   = $member['website'];
		$editable['id']        = $member['id'];
		$editable['name']      = $member['name'];

		$this->output .= View::Make("modcp.edit_user_form", ['profile' => $editable]);

		//-- mod_member_ips begin

		if (isset($ibforums->input['show_ips']))
		{
			$ip_row .= "\n<tr><td class='pformleft'>\nЗарегистрированные ИП адреса</td>\n<td class='pformright'>\n";
			$stmt = $ibforums->db->query("SELECT
				DISTINCT ip_address
			FROM	ibf_posts
			WHERE
				author_id =  '" . $ibforums->input['memberid'] . "'
			ORDER BY ip_address");
			$num  = $stmt->rowCount();
			if ($num)
			{
				$ip_row .= "<textarea style='font:Courier' cols='80' rows='" . (min(15, 3 + round($num / 4))) . "' name='ips' readonly='readonly' wrap='soft'>\n";
				$ip_row .= "Registered with " . $member['ip_address'];
				$ip_row .= "\n\nUsed ip addresses:\n";
				while ($r = $stmt->fetch())
				{
					$ip_row .= str_pad($r['ip_address'], 20);
				}
			} else
			{
				$ip_row .= "<textarea cols='50' rows='3' name='ips' class='forminput' readonly='readonly' wrap='soft'>\n";
				$ip_row .= "Registered with " . $member['ip_address'];
				$ip_row .= "\n<br>No posts found.";
			}
			$ip_row .= "\n</textarea>\n</td>\n</tr>\n";
			$this->output = preg_replace("`(<\/table>)`is", "$ip_row\\1", $this->output);
		}
		$this->output = preg_replace("`(<input type.*?submit.*?>)`is", "\\1&nbsp;&nbsp;<input type='submit' name='show_ips' value='IP адреса' class='forminput' />", $this->output);

		//-- mod_member_ips end

	}

	//--------------------------------------------------

	function complete_user_edit()
	{
		global $std, $ibforums, $print;

		//-- mod_member_ips begin
		if (isset($ibforums->input['show_ips']))
		{
			$this->edit_user();
		}
		return;
		//-- mod_member_ips end
		//todo ?
		$pass = 0;

		if ($ibforums->member['g_is_supmod'] == 1)
		{
			$pass = 1;
		} else {
			if ($this->moderator['edit_user'] == 1)
			{
				$pass = 1;
			} else
			{
				$pass = 0;
			}
		}

		if ($pass == 0)
		{
			$this->mod_error('cp_no_perms');
			return;
		}

		if ($ibforums->input['memberid'] == "")
		{
			$this->mod_error('cp_no_matches');
			return;
		}

		//--------------------------------------------------

		$stmt = $ibforums->db->query("SELECT
				m.*,
				g.*
			    FROM
				ibf_members m,
				ibf_groups g
			    WHERE
				m.id='" . $ibforums->input['memberid'] . "' AND
				m.mgroup=g.g_id");

		if (!$member = $stmt->fetch())
		{
			$this->mod_error('cp_no_matches');
			return;
		}

		//--------------------------------------------------
		// No editing of admins!
		//--------------------------------------------------

		if ($ibforums->member['g_access_cp'] != 1)
		{
			if ($member['g_access_cp'] == 1)
			{
				$this->mod_error('cp_admin_user');
				return;
			}
		}

		$parser = new PostParser();

		$ibforums->input['signature'] = $parser->convert(array(
		                                                      'TEXT'      => $ibforums->input['signature'],
		                                                      'SMILIES'   => 0,
		                                                      'CODE'      => $ibforums->vars['sig_allow_ibc'],
		                                                      'HTML'      => 0,
		                                                      'SIGNATURE' => 1
		                                                 ));

		if ($parser->error != "")
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => $parser->error
			            ));
		}

		$profile = array(
			'signature' => $ibforums->input['signature'],
			'location'  => $ibforums->input['location'],
			'interests' => $ibforums->input['interests'],
			'website'   => $ibforums->input['website']
		);

		if ($ibforums->input['avatar'] == 1)
		{
			$profile['avatar']      = "";
			$profile['avatar_size'] = "";
			$this->bash_uploaded_avatars($member['id']);
		}

		if ($ibforums->input['photo'] == 1)
		{
			$this->bash_uploaded_photos($member['id']);

			$stmt = $ibforums->db->query("SELECT id
				    FROM ibf_member_extra
				    WHERE id={$member['id']}");

			if ($stmt->rowCount())
			{
				$ibforums->db->exec("UPDATE ibf_member_extra
					    SET
						photo_location='',
						photo_type='',
						photo_dimensions=''
					    WHERE id={$member['id']}");
			} else
			{
				$ibforums->db->exec("INSERT INTO ibf_member_extra
					    SET
						photo_location='',
						photo_type='',
						photo_dimensions='',
						id={$member['id']}");
			}

			//$this->bash_uploaded_avatars($member['id']);
		}

		$ibforums->db->updateRow("ibf_members", array_map([
		                                                  $ibforums->db,
		                                                  'quote'
		                                                  ], $profile), "id=" . $ibforums->db->quote($ibforums->input['memberid']));

		$this->moderate_log("Edited Profile for: {$member['name']}");

		$std->boink_it($ibforums->base_url . "act=modcp&f={$ibforums->input['f']}&CODE=doedituser&memberid={$ibforums->input['memberid']}");
		exit();
	}

	//--------------------------------------------------
	// Faster Pussycat, Kill, Kill!
	//--------------------------------------------------

	function bash_uploaded_photos($id)
	{
		global $ibforums, $std, $print;

		foreach (array('swf', 'jpg', 'jpeg', 'gif', 'png') as $ext)
		{
			if (@file_exists($ibforums->vars['upload_dir'] . "/photo-" . $id . "." . $ext))
			{
				@unlink($ibforums->vars['upload_dir'] . "/photo-" . $id . "." . $ext);
			}
		}
	}

	function bash_uploaded_avatars($id)
	{
		global $ibforums, $std, $print;

		foreach (array('swf', 'jpg', 'jpeg', 'gif', 'png') as $ext)
		{
			if (@file_exists($ibforums->vars['upload_dir'] . "/av-" . $id . "." . $ext))
			{
				@unlink($ibforums->vars['upload_dir'] . "/av-" . $id . "." . $ext);
			}
		}
	}

	//--------------------------------------------------
	// Show default ModCP screen
	//--------------------------------------------------

	function splash()
	{
		global $std, $ibforums, $print;

		// Get the counts for pending topics and posts and other assorted stuff etc and ok.

		$stmt = $ibforums->db->query("SELECT COUNT(tid) as count
			    FROM ibf_topics
			    WHERE
				approved <> 1 AND
				forum_id='" . $this->forum['id'] . "'");
		$row  = $stmt->fetch();

		$tcount = $row['count']
			? $row['count']
			: 0;

		//-------------------------------

		$stmt = $ibforums->db->query("SELECT COUNT(pid) as pcount
			    FROM ibf_posts
			    WHERE
				queued=1 and
				new_topic <> 1 and
				forum_id='" . $this->forum['id'] . "'");
		$row  = $stmt->fetch();

		$pcount = $row['pcount']
			? $row['pcount']
			: 0;

		//-------------------------------

		$this->output .= View::Make("modcp.splash", ['tcount' => $tcount,'pcount' => $pcount,'forum' => $this->forum['name']]);
	}

	/*************************************************/

	function do_move()
	{
		global $std, $ibforums, $print;

		$this->load_forum();

		$pass = 0;

		if ($ibforums->member['g_is_supmod'] == 1)
		{
			$pass = 1;
		} else {
			if ($this->moderator['mass_move'] == 1)
			{
				$pass = 1;
			} else
			{
				$pass = 0;
			}
		}

		if ($pass == 0)
		{
			$this->mod_error('cp_no_perms');
			return;
		}

		//-----------------------------------------------
		// Check auth key
		//-----------------------------------------------

		if ($ibforums->input['key'] != $std->return_md5_check())
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'del_post'));
		}

		//-----------------------------------------------
		// Carry on...
		//-----------------------------------------------

		$db_query = $this->modfunc->sql_prune_create($this->forum['id'], $ibforums->input['starter'], $ibforums->input['state'], $ibforums->input['posts'], $ibforums->input['dateline'], $ibforums->input['ignore_pin']);

		$stmt = $ibforums->db->query($db_query);

		if (!$num_rows = $stmt->rowCount())
		{
			$this->mod_error('cp_error_no_topics');
			return;
		}

		$tid_array = array();

		while ($row = $stmt->fetch())
		{
			$tid_array[] = $row['tid'];
		}

		//----------------------------------

		$source = $this->forum['id'];
		$moveto = $ibforums->input['df'];

		//-----------------------------------
		// Check for an attempt to move into a subwrap forum
		//-----------------------------------

		$stmt = $ibforums->db->query("SELECT
				subwrap,
				id,
				sub_can_post
			    FROM ibf_forums
			    WHERE id='$moveto'");

		$f = $stmt->fetch();

		if ($f['subwrap'] == 1 and $f['sub_can_post'] != 1)
		{
			$this->mod_error('cp_error_no_subforum');
			return;
		}

		$this->modfunc->topic_move($tid_array, $source, $moveto);

		$this->moderate_log("Mass moved topics");

		//----------------------------------
		// Resync the forums..
		//----------------------------------

		$this->modfunc->forum_recount($source);

		$this->modfunc->forum_recount($moveto);

		//----------------------------------
		// Show results..
		//----------------------------------

		$this->output .= View::Make("modcp.mod_simple_page", ['title' => $ibforums->lang['cp_results'],'msg' => $ibforums->lang['cp_result_move'] . $num_rows]);

	}

	//+---------------------------------------------------------------------------------------------

	/*************************************************/
	// MODERATE LOG:
	// ---------------
	//
	// Function for adding the mod action to the DB
	//
	/*************************************************/

	function moderate_log($title = 'unknown')
	{
		global $ibforums;

		$data = [
			'forum_id'     => $ibforums->input['f'],
			'topic_id'     => $ibforums->input['t'],
			'post_id'      => $ibforums->input['p'],
			'member_id'    => $ibforums->member['id'],
			'member_name'  => $ibforums->member['name'],
			'ip_address'   => $ibforums->input['IP_ADDRESS'],
			'http_referer' => $_SERVER['HTTP_REFERER'],
			'ctime'        => time(),
			'topic_title'  => "<i>Via Moderators CP</i>",
			'action'       => $title,
			'query_string' => $_SERVER['QUERY_STRING'],
		];
		$ibforums->db->insertRow("ibf_moderator_logs", $data);

	}

	/*************************************************/

	function load_forum($fid = "")
	{
		global $std, $ibforums;

		if (!$fid)
		{
			$fid = intval($ibforums->input['f']);
		}

		$stmt = $ibforums->db->query("SELECT *
			    FROM ibf_forums
			    WHERE id='" . $fid . "'");

		if (!$this->forum = $stmt->fetch())
		{
			$this->mod_error('cp_err_no_f');
			return;
		}

		$this->modfunc->init($this->forum);

		return TRUE;

	}

	/*************************************************/

	function get_tids()
	{
		global $std, $ibforums;

		$ids = array();

		foreach ($ibforums->input as $key => $value)
		{
			if (preg_match("/^TID_(\d+)$/", $key, $match))
			{
				if ($ibforums->input[$match[0]])
				{
					$ids[] = $match[1];
				}
			}
		}

		//foreach ($ids as $value) {
		////echo $key."=>".$value."<br>";
		////echo "match:".$match[0]."=".$match[1]."<br>";
		//echo " ids:".$value."<br>";
		//}
		//echo " count:".count($ids)."<br>";

		if (count($ids) < 1)
		{
			$this->mod_error('cp_err_no_topics');
			return;
		}

		//Jureth	if ( $ibforums->member['g_is_supmod'] ) return $ids; else
		$new = array();
		if ($ibforums->member['g_is_supmod'])
		{
			$stmt = $ibforums->db->query("SELECT tid, forum_id as `fid`
				    FROM ibf_topics
				    WHERE tid IN (" . implode(",", $ids) . ")");
		} else
		{
			$stmt = $ibforums->db->query("SELECT
					md.*,
					t.tid,
					t.forum_id as `fid`
				    FROM
					ibf_topics t,
					ibf_moderators md
				    WHERE md.forum_id=t.forum_id AND (
					(md.member_id='" . $ibforums->member['id'] . "') OR
					(md.group_id='" . $ibforums->member['mgroup'] . "')) and
				          t.tid IN (" . implode(",", $ids) . ")");

		}
		while ($row = $stmt->fetch())
		{
			//Jureth		$new[] = $row['tid'];
			//Jureth		$this->moderator = $row;

			$new[$row['tid']] = $row['fid'];
			if (!$ibforums->member['g_is_supmod'])
			{
				$this->moderator = $row;
			}
		}

		unset($ids);

		return $new;

	}

	/*************************************************/

	function mod_error($error)
	{
		global $std, $ibforums, $print;

		$error = $ibforums->lang[$error];

		$this->output .= View::Make("modcp.mod_simple_page", ['title' => $ibforums->lang['cp_error'],'msg' => $error]);

		if (count($this->nav) < 1)
		{
			$this->nav[] = "<a href='{$this->base_url}&act=modcp'>{$ibforums->lang['cp_modcp_home']}</a>";
		}

		if (!$this->page_title)
		{
			$this->page_title = $ibforums->lang['cp_modcp_ptitle'];
		}

		$print->add_output("$this->output");
		$print->do_output(array(
		                       'TITLE' => $this->page_title,
		                       'NAV'     => $this->nav
		                  ));

		exit();

	}

	/*************************************************/

	function parse_member($member = array())
	{
		global $ibforums, $std;

		$member['name'] = "<a href='{$this->base_url}&act=Profile&CODE=03&MID={$member['id']}'>{$member['name']}</a>";

		$member['avatar'] = $std->get_avatar($member['avatar'], $ibforums->member['view_avs'], $member['avatar_size']);

		$member['MEMBER_JOINED'] = $ibforums->lang['m_joined'] . ' ' . $std->format_date_without_time($member['joined']);

		$member['MEMBER_GROUP'] = $ibforums->lang['m_group'] . ' ' . $member['g_title'];

		$member['MEMBER_POSTS'] = $ibforums->lang['m_posts'] . ' ' . $member['member_posts'];

		$member['PROFILE_ICON'] = "<a href='{$this->base_url}&act=Profile&CODE=03&MID={$member['id']}'><{P_PROFILE}></a>&nbsp;";

		$member['MESSAGE_ICON'] = "<a href='{$this->base_url}&act=Msg&CODE=04&MID={$member['id']}'><{P_MSG}></a>&nbsp;";

		if (!$member['hide_email'])
		{
			$member['EMAIL_ICON'] = "<a href='{$this->base_url}&act=Mail&CODE=00&MID={$member['id']}'><{P_EMAIL}></a>&nbsp;";
		}

		if ($member['website'] and $member['website'] = preg_match("/^http:\/\/\S+$/", $member['WEBSITE']))
		{
			$member['WEBSITE_ICON'] = "<a href='{$member['website']}' target='_blank'><{P_WEBSITE}></a>&nbsp;";
		}

		if ($member['icq_number'])
		{
			$member['ICQ_ICON'] = "<a href=\"javascript:PopUp('{$this->base_url}&act=ICQ&MID={$member['id']}','Pager','450','330','0','1','1','1')\"><{P_ICQ}></a>&nbsp;";
		}

		if ($member['aim_name'])
		{
			$member['AOL_ICON'] = "<a href=\"javascript:PopUp('{$this->base_url}&act=AOL&MID={$member['id']}','Pager','450','330','0','1','1','1')\"><{P_AOL}></a>&nbsp;";
		}

		//-----------------------------------------------------

		return $member;

	}

	//-----------------------------------------------------
	// Prints the index
	//-----------------------------------------------------

	function print_index()
	{
		global $ibforums, $std, $print;

		$this->output .= View::Make("modcp.cp_index");

	}

	//-------------------------------------------------
	// HIGHLIGHT STUFF!
	//-------------------------------------------------

	function syntax_edit_access($syntax_id)
	{
		global $ibforums;

		$access = 0;
		if ($syntax != '')
		{
			$stmt = $ibforums->db->query("SELECT
					a.syntax_id,
					a.member_id
				     FROM ibf_syntax_list l
				     INNER JOIN ibf_syntax_access a
					ON a.syntax_id = l.id and
					   a.member_id = " . $ibforums->member['id'] . "
				     WHERE l.id = '" . $syntax_id . "'");

			if ($row = $stmt->fetch())
			{
				$access = 1;
			} else
			{
				$access = 0;
			}
		} else
		{
			$access = 1;
		}

		return $access;
	}

	function syntax_start()
	{
		global $std, $ibforums, $print;

		$access = $this->syntax_edit_access('');
		if ($access == 0)
		{
			$this->mod_error('cp_no_perms');
			return;
		}

		$this->output .= View::Make("modcp.highlight_start_form", ['syntax_id' => '','rule' => '']);
	}

	function syntax_set()
	{
		global $std, $ibforums, $print;

		$syntax_id = $ibforums->input['syntax_set'];

		if ($syntax_id == '')
		{
			$this->mod_error('cp_ip_no');
			return;
		}

		$access = $this->syntax_edit_access($syntax_id);
		if ($access == 0)
		{
			$this->mod_error('cp_no_perms');
			return;
		}

		$this->output .= View::Make("modcp.highlight_start_form", ['syntax_id' => $syntax_id,'rule' => '']);
	}

	function syntax_rule()
	{
		global $std, $ibforums, $print;

		$syntax_id = $ibforums->input['syntax_set'];
		$rule      = $ibforums->input['syntax_rule'];

		if (($syntax_id == '') || ($rule == ''))
		{
			$this->mod_error('cp_ip_no');
			return;
		}

		$access = $this->syntax_edit_access($syntax_id);
		if ($access == 0)
		{
			$this->mod_error('cp_no_perms');
			return;
		}

		$this->output .= View::Make("modcp.highlight_start_form", ['syntax_id' => $syntax_id,'rule' => $rule]);
	}

	// Mastilior + Song * save highlight rules to file

	function save_rules($lang, $version, $rules)
	{
		global $std;
		$ibforums = Ibf::app();

		$lang = str_replace('#', 'sharp', $lang);

		// delete old file with rules
		if (is_file(ROOT_PATH . "highlight/h_" . $lang . "_" . intval($version - 1) . ".js"))
		{

			@unlink(ROOT_PATH . "highlight/h_" . $lang . "_" . intval($version - 1) . ".js");
		}

		$this->parser = new PostParser(1);

		$fout = fopen(ROOT_PATH . "highlight/h_" . $lang . "_" . $version . ".js", "w");
		fputs($fout, "window['h_" . $lang . "']=[\n");

		$rules_count = count($rules);
		for ($i = 0; $i < $rules_count; $i++)
		{
			fputs($fout, " [\n");

			fputs($fout, "    " . $rules[$i]["reg_exp"] . ",\n");

			// collect BB tags
			$prepare = "";

			for ($j = 0; $j < 10; $j++)
			{
				$prepare .= $rules[$i]["tag_" . $j] . Chr(1050);

				// convert action to int codes
				switch ($rules[$i]["action_" . $j])
				{
					case 'count':
						$action = 4;
						break;

					case 'tag':
						$action = 3;
						break;

					case 'value':
						$action = 2;
						break;

					case 'none':
						$action = 1;
						break;

					case '':
						$action = 0;
						break;

					default:
						$action = $rules[$i]["action_" . $j];
						break;
				}

				$rules[$i]["action_" . $j] = $action;
			}

			// delete last delimiter
			$prepare = mb_substr($prepare, 0, mb_strlen($prepare) - 1);

			// parse its to HTML
			$ready = $this->parser->prepare(array(
			                                     'TEXT'      => $prepare,
			                                     'SMILIES'   => 0,
			                                     'CODE'      => 1,
			                                     'SIGNATURE' => 0,
			                                     'HTML'      => 1,
			                                     'HID'       => -1,
			                                     'TID'       => -1
			                                ));

			// split parsed string to array
			$html = explode(Chr(1050), $ready);

			// last used rule
			for ($n = 9; $n > -1; $n--)
			{
				if ($rules[$i]["action_" . $n] or $html[$n])
				{
					break;
				}
			}

			// write only used cells
			for ($j = 0; $j < $n + 1; $j++)
			{
				// safe '
				$html[$j] = str_replace("'", "\'", $html[$j]);

				fputs($fout, "    ['" . $html[$j] . "'," . $rules[$i]["action_" . $j] . "]" . (($j != $n)
					? ","
					: "") . "\n");
			}

			fputs($fout, " ]" . (($i < $rules_count - 1)
				? ","
				: "") . "\n");
		}

		fputs($fout, "];\n");
		fclose($fout);

	}

	// Mastilior + Song * save highlight rules to file

	function syntax_edit()
	{
		global $std, $ibforums, $print;

		$syntax_id = $ibforums->input['syntax_set'];
		$rule      = $ibforums->input['syntax_rule'];

		if (($syntax_id == '') || ($rule == ''))
		{
			$this->mod_error('cp_ip_no');
			return;
		}

		$access = $this->syntax_edit_access($syntax_id);
		if ($access == 0)
		{
			$this->mod_error('cp_no_perms');
			return;
		}

		if ($rule == 'new')
		{
			$stmt = $ibforums->db->query("select max(record) as max_record from ibf_syntax_rules where syntax_id = '" . $syntax_id . "'");
			if ($row = $stmt->fetch())
			{
				$rule = $row ['max_record'] + 1;
			} else
			{
				$rule = 1;
			}

			$ibforums->db->prepare("INSERT INTO ibf_syntax_rules
					(
					syntax_id,
					record,
					description,
					reg_exp,
					tag_0, tag_1, tag_2, tag_3, tag_4, tag_5, tag_6, tag_7, tag_8, tag_9,
					action_0, action_1, action_2, action_3, action_4, action_5, action_6, action_7, action_8, action_9
				) values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")
				->execute(
					[
						$syntax_id,
						$rule,
						$_POST['description'],
						$_POST['reg_exp'],
						$ibforums->input['tag_0'],
						$ibforums->input['tag_1'],
						$ibforums->input['tag_2'],
						$ibforums->input['tag_3'],
						$ibforums->input['tag_4'],
						$ibforums->input['tag_5'],
						$ibforums->input['tag_6'],
						$ibforums->input['tag_7'],
						$ibforums->input['tag_8'],
						$ibforums->input['tag_9'],
						$ibforums->input['action_0'],
						$ibforums->input['action_1'],
						$ibforums->input['action_2'],
						$ibforums->input['action_3'],
						$ibforums->input['action_4'],
						$ibforums->input['action_5'],
						$ibforums->input['action_6'],
						$ibforums->input['action_7'],
						$ibforums->input['action_8'],
						$ibforums->input['action_9'],
					]
				);

			$this->output .= View::Make("modcp.highlight_start_form", ['syntax_id' => $syntax_id,'rule' => $rule]);
		} else
		{
			$ibforums->db->prepare("UPDATE ibf_syntax_rules
				SET
					description = ?,
					reg_exp = ?,
					tag_0 = ?,
					tag_1 = ?,
					tag_2 = ?,
					tag_3 = ?,
					tag_4 = ?,
					tag_5 = ?,
					tag_6 = ?,
					tag_7 = ?,
					tag_8 = ?,
					tag_9 = ?,
					action_0 = ?,
					action_1 = ?,
					action_2 = ?,
					action_3 = ?,
					action_4 = ?,
					action_5 = ?,
					action_6 = ?,
					action_7 = ?,
					action_8 = ?,
					action_9 = ?
				WHERE
					syntax_id = ?
				AND
					record = ?")
				->execute(
					[
						$_POST['description'],
						$_POST['reg_exp'],
						$ibforums->input['tag_0'],
						$ibforums->input['tag_1'],
						$ibforums->input['tag_2'],
						$ibforums->input['tag_3'],
						$ibforums->input['tag_4'],
						$ibforums->input['tag_5'],
						$ibforums->input['tag_6'],
						$ibforums->input['tag_7'],
						$ibforums->input['tag_8'],
						$ibforums->input['tag_9'],
						$ibforums->input['action_0'],
						$ibforums->input['action_1'],
						$ibforums->input['action_2'],
						$ibforums->input['action_3'],
						$ibforums->input['action_4'],
						$ibforums->input['action_5'],
						$ibforums->input['action_6'],
						$ibforums->input['action_7'],
						$ibforums->input['action_8'],
						$ibforums->input['action_9'],
						$syntax_id,
						$rule,
					]
				);

			$this->output .= View::Make("modcp.highlight_start_form", ['syntax_id' => $syntax_id,'rule' => $rule]);
		}

		$this->save_syntax_to_js($syntax_id);
	}

	function save_syntax_to_js($id = 0)
	{
		$ibforums = Ibf::app();

		if (!$id)
		{
			return;
		}

		// Song * write rules to js file

		$ibforums->db->exec("UPDATE ibf_syntax_list
		    SET version=version+1
		    WHERE id='" . $id . "'");

		$stmt = $ibforums->db->query("SELECT
			syntax,
			version
		    FROM ibf_syntax_list
		    WHERE id='" . $id . "'");

		if ($row = $stmt->fetch())
		{
			$rules = array();

			$stmt = $ibforums->db->query("SELECT *
			    FROM ibf_syntax_rules
			    WHERE syntax_id='" . $id . "'
			    ORDER BY record");
			while ($record = $stmt->fetch())
			{
				$rules[] = $record;
			}

			$this->save_rules($row['syntax'], $row['version'], $rules);
		}

		// Song * write rules to js file

	}

	function syntax_order()
	{
		global $std, $ibforums, $print;

		$syntax_id = $ibforums->input['syntax_set'];

		if ($syntax_id == '')
		{
			$this->mod_error('cp_ip_no');
			return;
		}

		$access = $this->syntax_edit_access($syntax_id);

		if ($access == 0)
		{
			$this->mod_error('cp_no_perms');
			return;
		}

		$action = $ibforums->input['action'];

		if ($action == 'delete')
		{
			$n       = 0;
			$records = array();

			$stmt = $ibforums->db->query("SELECT record
				     FROM ibf_syntax_rules
				     WHERE syntax_id = '" . $syntax_id . "'");

			while ($row = $stmt->fetch())
			{
				$records [$n++] = $row['record'];
			}

			foreach ($records as $record)
			{
				$delete = $ibforums->input["delete_" . $record];
				if ($delete == 'checked')
				{
					$ibforums->db->exec("DELETE
						     FROM ibf_syntax_rules
						     WHERE
							syntax_id = '" . $syntax_id . "' and
							record = " . $record);
				}
			}
		} else {
			if ($action == 'order')
			{
				$n       = 0;
				$records = array();

				$stmt = $ibforums->db->query("select max(record) as max_record from ibf_syntax_rules where syntax_id = '" . $syntax_id . "'");
				$row  = $stmt->fetch();
				$max  = $row['max_record'] + 1;

				$stmt = $ibforums->db->query("select record from ibf_syntax_rules where syntax_id = '" . $syntax_id . "'");
				while ($row = $stmt->fetch())
				{
					$records [$n++] = $row['record'];
				}

				foreach ($records as $record)
				{
					$order = $max + $ibforums->input["order_" . $record];
					$ibforums->db->exec("update ibf_syntax_rules set record = " . $order . " where syntax_id = '" . $syntax_id . "' and record = " . $record);
				}

				$n       = 0;
				$records = array();

				$stmt = $ibforums->db->query("select record from ibf_syntax_rules where syntax_id = '" . $syntax_id . "' order by record");
				while ($row = $stmt->fetch())
				{
					$records [$n++] = $row['record'];
				}

				$n = 0;
				foreach ($records as $record)
				{
					$ibforums->db->exec("update ibf_syntax_rules set record = " . $n . " where syntax_id = '" . $syntax_id . "' and record = " . $record);
					$n++;
				}
			}
		}

		$this->save_syntax_to_js($syntax_id);

		$this->output .= View::Make("modcp.highlight_start_form", ['syntax_id' => $syntax_id,'rule' => $rule]);
	}

	//-------------------------------------------------------------------------------

	// Song * rules edit

	function rules_edit($edit = 0)
	{
		global $std, $ibforums, $print;

		$pass = 0;

		if ($ibforums->member['g_is_supmod'])
		{
			$pass = 1;

		} elseif ($this->moderator['rules_edit'])
		{
			$pass = 1;
		} else
		{
			$pass = 0;
		}

		if (!$pass)
		{
			$this->mod_error('cp_no_perms');
			return;
		}

		// combine query

		$query = "SELECT f.id,f.name,";

		if (!$ibforums->member['g_is_supmod'])
		{
			$query .= "md.forum_id,";
		}

		$query .= "f.parent_id, c.position as category_pos,
				 if(p.position is null, 0, f.position) as forum_pos,
				 if(p.position is null, f.position, p.position) as parent_pos
			   FROM ibf_forums f
			   LEFT JOIN ibf_moderators md ON (md.forum_id=f.id)
			   LEFT JOIN ibf_categories c ON (c.id=f.category)
			   LEFT JOIN ibf_forums p ON (p.id=f.parent_id)
			   WHERE f.redirect_on=0 ";

		if (!$ibforums->member['g_is_supmod'])
		{
			$query .= "and md.member_id='" . $ibforums->member['id'] . "' ";
		}

		$query .= "GROUP BY f.id ORDER BY category_pos, parent_pos, forum_pos";

		$stmt = $ibforums->db->query($query);
		if (!$stmt->rowCount())
		{
			$this->mod_error('no_rules_edit');
			return;
		}

		$forum_list = "";
		while ($row = $stmt->fetch())
		{
			$row['forum_id'] = ($row['forum_id'])
				? $row['forum_id']
				: $row['id'];

			if ($row['parent_id'] != -1)
			{
				$prefix = "--- ";
			} else
			{
				$prefix = "";
			}

			if ($row['forum_id'] == $ibforums->input['f'])
			{
				$selected = ' selected="selected"';

			} else {
				$selected = '';
			}

			$forum_list .= "<option value='{$row['forum_id']}'{$selected}>{$prefix}{$row['name']}</option>\n";
		}

		if (!$forum_list)
		{
			$this->mod_error('no_rules_edit');
			return;
		}

		$this->output .= View::Make("modcp.forum_rules", ['forum_rules' => $forum_list]);

		if ($edit and $ibforums->input['f'])
		{
			$stmt = $ibforums->db->query("SELECT show_rules,rules_title as title,rules_text as rules,red_border as border FROM ibf_forums
				    WHERE id='" . $ibforums->input['f'] . "'");

			if ($row = $stmt->fetch())
			{
				$no   = "";
				$link = "";
				$txt  = "";

				switch ($row['show_rules'])
				{
					case '0':
						$no = "selected";
						break;

					case '1':
						$link = "selected";
						break;

					case '2':
						$txt = "selected";
						break;
				}

				if ($row['border'])
				{
					$border = "checked";
				}

				$row['rules'] = str_replace("<br>", "\r\n", $row['rules']);

				$this->output .= View::Make("modcp.forum_rules_text", ['title' => $row['title'],'txt' => $row['rules'],'style_no' => $no,'style_link' => $link,'style_txt' => $txt,'border_check' => $border]);
			}
		}
	}

	function do_rules_edit()
	{
		global $std, $ibforums, $print;

		$pass = 0;

		if ($ibforums->member['g_is_supmod'])
		{
			$pass = 1;

		} elseif ($this->moderator['rules_edit'] == 1)
		{
			$pass = 1;
		} else
		{
			$pass = 0;
		}

		if (!$pass)
		{
			$this->mod_error('cp_no_perms');
			return;
		}

		if (!$ibforums->input['f'] or !$ibforums->input['title'])
		{
			$this->mod_error('no_rules_edit');
			return;
		}

		if (!$ibforums->member['g_is_supmod'] and $ibforums->input['style'])
		{
			$stmt = $ibforums->db->query("SELECT mid FROM ibf_moderators WHERE member_id='" . $ibforums->member['id'] . "' and
				    forum_id='" . $ibforums->input['f'] . "'");

			if (!$stmt->rowCount())
			{
				$this->mod_error('cp_no_perms');
				return;
			}
		}

		$ibforums->db->exec("UPDATE ibf_forums SET rules_title='" . addslashes($std->remove_tags($ibforums->input['title'])) . "',
						  rules_text='" . addslashes($std->remove_tags($ibforums->input['rules_txt'])) . "',
						  show_rules='" . $ibforums->input['style'] . "',
						  red_border='" . $ibforums->input['border'] . "'
			    WHERE id='" . $ibforums->input['f'] . "'");

		$print->redirect_screen($ibforums->lang['cp_redirect_mod_topics'], "act=modcp&CODE=rules_select&f=" . $ibforums->input['f']);

	}

	// Song * rules edit

	// Song * multimoderation

	function multi_mod()
	{
		global $std, $ibforums, $print;

		$pass = 0;

		if ($ibforums->member['g_is_supmod'])
		{
			$pass = 1;
		} else {
			if ($this->moderator['multimod_edit'] == 1)
			{
				$pass = 1;
			} else
			{
				$pass = 0;
			}
		}

		if (!$pass)
		{
			$this->mod_error('cp_no_perms');
			return;
		}

		// combine query

		$query = "SELECT f.id,f.name,";

		if (!$ibforums->member['g_is_supmod'])
		{
			$query .= "md.forum_id,";
		}

		$query .= "f.parent_id, c.position as category_pos,
				 if(p.position is null, 0, f.position) as forum_pos,
				 if(p.position is null, f.position, p.position) as parent_pos
			   FROM ibf_forums f
			   LEFT JOIN ibf_moderators md ON (md.forum_id=f.id)
			   LEFT JOIN ibf_categories c ON (c.id=f.category)
			   LEFT JOIN ibf_forums p ON (p.id=f.parent_id)
			   WHERE f.redirect_on=0 ";

		if (!$ibforums->member['g_is_supmod'])
		{
			$query .= "and md.member_id='" . $ibforums->member['id'] . "' ";
		}

		$query .= "GROUP BY f.id ORDER BY category_pos, parent_pos, forum_pos";

		$stmt = $ibforums->db->query($query);
		if (!$stmt->rowCount())
		{
			$this->mod_error('no_multi_mod_edit');
			return;
		}

		$forum_list = "";
		while ($row = $stmt->fetch())
		{
			$row['forum_id'] = ($row['forum_id'])
				? $row['forum_id']
				: $row['id'];

			if ($row['parent_id'] != -1)
			{
				$prefix = "--- ";
			} else
			{
				$prefix = "";
			}

			if ($row['forum_id'] == $ibforums->input['f'])
			{
				$selected = ' selected="selected"';

			} else {
				$selected = '';
			}

			$forum_list .= "<option value='{$row['forum_id']}'{$selected}>{$prefix}{$row['name']}</option>\n";
		}

		if (!$forum_list)
		{
			$this->mod_error('no_rules_edit');
			return;
		}

		$this->output .= View::Make('modcp.multi_mod', ['forums_list' => $forum_list]);
	}

	// Song * multimoderation

}
