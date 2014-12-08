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
|   > User Profile functions
|   > Module written by Matt Mecham
|   > Date started: 28th February 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/
use Skins\Skin;
use Views\View;

$idx = new Profile;

class Profile
{

	var $output = "";
	var $page_title = "";
	var $nav = array();
	// vot    var $parser;

	var $member = array();
	var $m_group = array();

	var $jump_html = "";
	var $parser = "";

	var $links = array();

	var $bio = "";
	var $notes = "";
	var $size = "m";

	var $show_photo = "";
	var $show_width = "";
	var $show_height = "";
	var $show_name = "";

	var $photo_member = "";

	var $has_photo = FALSE;

	var $lib;

	//----------------------------------------------------------
	function Profile()
	{
		global $ibforums, $std, $print;

		//echo "Profile.php started.";

		$this->parser = new PostParser(1);

		//--------------------------------------------
		// Require the HTML and language modules
		//--------------------------------------------

		$ibforums->lang = $std->load_words($ibforums->lang, 'lang_profile', $ibforums->lang_id);

		$this->base_url        = $ibforums->base_url;
		$this->base_url_nosess = "{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}";

		//--------------------------------------------
		// Check viewing permissions, etc
		//--------------------------------------------

		$this->member  = $ibforums->member;
		$this->m_group = $ibforums->member;

		$std->flood_begin();

		//--------------------------------------------
		// What to do?
		//--------------------------------------------

		switch ($ibforums->input['CODE'])
		{
			case '03':
				$this->view_profile();
				break;

			case 'showphoto':
				$this->show_photo();
				break;

			case 'showcard':
				$this->show_card();
				break;

			case 'show_stat':
				$this->show_stat();
				break;

			case 'save_stat':
				$this->show_stat(1);
				break;

			//------------------------------

			default:
				$this->view_profile();
				break;
		}

		$std->flood_end();

		// If we have any HTML to print, do so...

		$print->add_output("$this->output");
		$print->do_output(array(
		                       'TITLE' => $this->page_title,
		                       'NAV'   => $this->nav
		                  ));

	}

	//----------------------------------
	function show_stat($tosave = 0)
	{

		global $ibforums, $std, $print;

		$info = array();

		if ($ibforums->member['g_mem_info'] != 1)
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'no_permission'
			            ));
		}

		//--------------------------------------------
		// Check input..
		//--------------------------------------------

		$id = intval($ibforums->input['MID']);

		if (empty($id))
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'incorrect_use'
			            ));
		}

		$info['member_id'] = $id;

		//--------------------------------------------
		// Prepare Query...
		//--------------------------------------------

		$sql = "SELECT
			p.forum_id,
			COUNT(p.pid) as counter,
			f.name as forum_name,
			f.inc_postcount,
			p.author_name
		FROM
			ibf_posts p,
			ibf_forums f
		WHERE
			p.queued=0
			AND	p.author_id='" . $id . "'
			AND	f.id=p.forum_id
		GROUP BY p.forum_id
		ORDER BY counter DESC";

		$stmt = $ibforums->db->query($sql);

		/*-----------------------------
		Count user postings statistics
		------------------------------*/
		$info['member_name'] = "";
		$info['stat']        = array();
		$TotalPosts          = 0;
		$TotalThematic       = 0;

		$i = 0;
		while ($row = $stmt->fetch())
		{
			$info['stat'][$i] = array(
				'FID'    => $row['forum_id'],
				'FNAME'  => $row['forum_name'],
				'COUNT'  => $row['counter'],
				'STATUS' => $row['inc_postcount']
			);
			if ($i == 0)
			{
				$info['member_name'] = $row['author_name'];
			}

			$TotalPosts += $row['counter'];

			if ($row['inc_postcount'])
			{
				$TotalThematic += $row['counter'];
			}

			//echo $row['forum_id']." ".$row['forum_name']." ".$row['counter']." ".$row['inc_postcount']." ".$row['author_name']." ".$TotalPosts."<br>\n";
			$i++;
		}

		// Save or SHOW the stats
		if ($tosave)
		{

			$sql = "UPDATE ibf_members
		SET	posts='" . $TotalThematic . "'
		WHERE	id='{$id}'";

			$ibforums->db->exec($sql);

			$std->boink_it($ibforums->base_url . "showuser={$id}");

		} else
		{
			$info['total'] = $TotalPosts;

			//---------------------------------------------------
			// Is this our profile or WE are a super_moderator ?
			//---------------------------------------------------

			$this->page_title = $ibforums->lang['active_stats'] . " " . $info['member_name'];

			$this->nav = array(
				"<a href='index.php?showuser={$info['member_id']}'>{$ibforums->lang['page_title']} {$info['member_name']}</a>",
				$ibforums->lang['active_stats']
			);

			$this->output = View::make("profile.show_forum_stat", ['info' => $info]);
		}
	}

	//---------------------------------------------------------------------------
	//
	// VIEW CONTACT CARD:
	//
	//---------------------------------------------------------------------------

	function show_card()
	{
		global $ibforums, $std, $print;

		$info = array();

		if ($ibforums->member['g_mem_info'] != 1)
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_permission'));
		}

		//--------------------------------------------
		// Check input..
		//--------------------------------------------

		$id = intval($ibforums->input['MID']);

		if (empty($id))
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'incorrect_use'));
		}

		$stmt = $ibforums->db->query("SELECT *
		    FROM ibf_members
		    WHERE id=$id");

		$member = $stmt->fetch();

		$member['password'] = '';

		$info['aim_name']   = $member['aim_name']
			? $member['aim_name']
			: $ibforums->lang['no_info'];
		$info['icq_number'] = $member['icq_number']
			? $member['icq_number']
			: $ibforums->lang['no_info'];
		$info['yahoo']      = $member['yahoo']
			? $member['yahoo']
			: $ibforums->lang['no_info'];
		$info['location']   = $member['location']
			? $member['location']
			: $ibforums->lang['no_info'];
		$info['interests']  = $member['interests']
			? $member['interests']
			: $ibforums->lang['no_info'];
		$info['msn_name']   = $member['msnname']
			? $member['msnname']
			: $ibforums->lang['no_info'];
		$info['integ_msg']  = $member['integ_msg']
			? $member['integ_msg']
			: $ibforums->lang['no_info'];
		$info['mid']        = $member['id'];

		if (!$member['hide_email'])
		{
			$info['email'] = "<a href='javascript:redirect_to(\"&amp;act=Mail&amp;CODE=00&amp;MID={$member['id']}\",1);'>{$ibforums->lang['click_here']}</a>";
		} else
		{
			$info['email'] = $ibforums->lang['private'];
		}

		$this->load_photo($id);

		if ($this->has_photo == TRUE)
		{
			$photo = View::make(
				"profile.get_photo",
				[
					'show_photo'  => $this->show_photo,
					'show_width'  => $this->show_width,
					'show_height' => $this->show_height
				]
			);
		} else
		{
			$photo = "<{NO_PHOTO}>";
		}

		if ($ibforums->input['download'] == 1)
		{
			$photo = str_replace("<{NO_PHOTO}>", "No Photo Available", $photo);

			$html = View::make(
				"profile.show_card_download",
				['name' => $member['name'], 'photo' => $photo, 'info' => $info]
			);

			@flush();
			@header("Content-type: unknown/unknown");
			@header("Content-Disposition: attachment; filename={$member['name']}.html");
			print $html;
			exit();
		} else
		{
			$html = View::make("profile.show_card", ['name' => $member['name'], 'photo' => $photo, 'info' => $info]);

			$print->pop_up_window($ibforums->lang['photo_title'], $html);
		}

	}

	//---------------------------------------------------------------------------
	//
	// VIEW PHOTO:
	//
	//---------------------------------------------------------------------------

	function show_photo()
	{
		global $ibforums, $std, $print;

		$info = array();

		if ($ibforums->member['g_mem_info'] != 1)
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'no_permission'));
		}

		//--------------------------------------------
		// Check input..
		//--------------------------------------------

		$id = intval($ibforums->input['MID']);

		if (empty($id))
		{
			$std->Error(array('LEVEL' => 1, 'MSG' => 'incorrect_use'));
		}

		$this->load_photo($id);

		if ($this->has_photo == TRUE)
		{
			$photo = View::make(
				"profile.get_photo",
				[
					'show_photo'  => $this->show_photo,
					'show_width'  => $this->show_width,
					'show_height' => $this->show_height
				]
			);
		} else
		{
			$photo = "<{NO_PHOTO}>";
		}

		$html = View::make("profile.show_photo", ['name' => $this->photo_member['name'], 'photo' => $photo]);

		$print->pop_up_window($ibforums->lang['photo_title'], $html);

	}

	//---------------------------------------------------------------------------
	//
	// functions: RETURN PHOTO
	//
	//---------------------------------------------------------------------------

	function load_photo($id)
	{
		global $ibforums, $std, $print;

		$this->show_photo  = "";
		$this->show_height = "";
		$this->show_width  = "";

		$stmt = $ibforums->db->query("SELECT m.id, m.name,
			me.photo_type, me.photo_location, me.photo_dimensions
		    FROM ibf_member_extra me
    	            LEFT JOIN ibf_members m ON me.id=m.id
    		    WHERE m.id=$id");

		$this->photo_member = $stmt->fetch();

		if ($this->photo_member['photo_type'] and $this->photo_member['photo_location'])
		{
			$this->has_photo = TRUE;

			list($show_width, $show_height) = explode(",", $this->photo_member['photo_dimensions']);

			if ($this->photo_member['photo_type'] == 'url')
			{
				$this->show_photo = $this->photo_member['photo_location'];
			} else
			{
				$this->show_photo = $ibforums->vars['upload_url'] . "/" . $this->photo_member['photo_location'];
			}

			if ($show_width > 0)
			{
				$this->show_width = "width='$show_width'";
			}

			if ($show_height > 0)
			{
				$this->show_height = "width='$show_height'";
			}
		}
	}

	//------------------------------------------
	//
	// VIEW REPUTATION:
	//
	//------------------------------------------

	function view_rep($member, &$info, $rep)
	{
		global $ibforums;

		$tmp_rep = empty($member[$rep])
			? 0
			: $member[$rep];

		$tmp_title = "";

		if ($ibforums->vars['rep_goodnum'] and $tmp_rep >= $ibforums->vars['rep_goodnum'])
		{
			$tmp_title = $ibforums->vars['rep_goodtitle'];
		}

		if ($ibforums->vars['rep_badnum']  and $tmp_rep <= $ibforums->vars['rep_badnum'])
		{
			$tmp_title = $ibforums->vars['rep_badtitle'];
		}

		if ($tmp_title and $info['member_title'] != $ibforums->lang['no_info'])
		{
			$info['member_title'] = $tmp_title . ' ' . $info['member_title'];
		}

		if (empty($member[$rep]))
		{
			if (!is_numeric($member[$rep]))
			{
				$member[$rep] = $ibforums->lang['rep_none'];
			} else
			{
				$member[$rep] .= " " . $ibforums->vars['rep_postfix'];
			}

		} else {
			$member[$rep] .= " " . $ibforums->vars['rep_postfix'];
		}

		$info[$rep] = $member[$rep];

	}

	//--------------------------------------------
	function view_profile()
	{
		global $ibforums, $std, $print;

		$info = array();
		// vot echo "view_profile started.";

		if ($ibforums->member['g_mem_info'] != 1)
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'no_permission'
			            ));
		}

		//--------------------------------------------
		// Check input..
		//--------------------------------------------

		$id = intval($ibforums->input['MID']);

		if (empty($id))
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'incorrect_use'
			            ));
		}

		//--------------------------------------------
		// Prepare Query...
		//--------------------------------------------

		$stmt   = $ibforums->db->query("SELECT m.*, s.id as s_id, g.g_id, g.g_title AS group_title
	            FROM (ibf_members m, ibf_groups g )
		    LEFT JOIN ibf_sessions s ON (s.member_id=m.id and s.login_type<>1)
		    WHERE m.id='$id' and m.mgroup=g.g_id");
		$member = $stmt->fetch();

		if (empty($member['id']))
		{
			$std->Error(array(
			                 'LEVEL' => 1,
			                 'MSG'   => 'incorrect_use'
			            ));
		}

		// Play it safe

		$member['password'] = "";

		//--------------------------------------------
		// Find the most posted in forum that the viewing
		// member has access to by this members profile
		//--------------------------------------------

		$stmt = $ibforums->db->query("SELECT id, read_perms
		    FROM ibf_forums");

		$forum_ids = array('0');

		while ($r = $stmt->fetch())
		{
			if ($std->check_perms($r['read_perms']) == TRUE)
			{
				$forum_ids[] = $r['id'];
			}
		}

		$forum_id_str = implode(",", $forum_ids);

		$percent = 0;

		$stmt = $ibforums->db->query("SELECT DISTINCT(p.forum_id), f.name, COUNT(p.author_id) AS f_posts
		    FROM ibf_posts p, ibf_forums f
    		    WHERE p.forum_id IN ($forum_id_str)
			AND p.author_id='" . $member['id'] . "'
			AND p.forum_id=f.id
		    GROUP BY p.forum_id
		    ORDER BY f_posts DESC");

		$favourite = $stmt->fetch();

		$stmt = $ibforums->db->query("SELECT COUNT(pid) AS total_posts
		    FROM ibf_posts
		    WHERE author_id='" . $member['id'] . "'");

		$total_posts = $stmt->fetch();

		$stmt = $ibforums->db->query("SELECT TOTAL_TOPICS, TOTAL_REPLIES
		    FROM ibf_stats");

		$stats = $stmt->fetch();

		$board_posts = $stats['TOTAL_TOPICS'] + $stats['TOTAL_REPLIES'];

		if ($total_posts['total_posts'] > 0)
		{
			$percent = round($favourite['f_posts'] / $total_posts['total_posts'] * 100);
		}

		if ($member['posts'] and $board_posts)
		{
			$info['posts_day'] = round($member['posts'] / (((time() - $member['joined']) / 86400)), 1);
			$info['total_pct'] = sprintf('%.2f', ($member['posts'] / $board_posts * 100));
		}

		if ($info['posts_day'] > $member['posts'])
		{
			$info['posts_day'] = $member['posts'];
		}

		$info['posts']       = $member['posts']
			? $member['posts']
			: 0;
		$info['name']        = $member['name'];
		$info['mid']         = $member['id'];
		$info['fav_forum']   = $favourite['name'];
		$info['fav_id']      = $favourite['forum_id'];
		$info['fav_posts']   = $favourite['f_posts'];
		$info['percent']     = $percent;
		$info['group_title'] = $member['group_title'];
		$mod_forums_count    = $this->Mod_Forums_Count($member['id']);
		if ($mod_forums_count > 0)
		{
			$info['mod_forums'] = "$mod_forums_count <a href=\"javascript:PopUpCD('{$ibforums->base_url}act=Stats&CODE=oneleader&mid=" . $member['id'] . "','500','400')\">" . $ibforums->lang['rep_details'] . "</a>";
		} else
		{
			if ($this->Mod_Forums_All($member['id']) > 0)
			{
				$info['mod_forums'] = $ibforums->lang['mod_all_forums'];
			} else
			{
				$info['mod_forums'] = '';
			}
		}
		$info['board_posts'] = $board_posts;
		$info['joined']      = $std->format_date_without_time($member['joined']);

		$stmt = $ibforums->db->query("SELECT title
		    FROM ibf_titles
		    WHERE posts < '" . $member['posts'] . "'
		    ORDER BY posts DESC
		    LIMIT 1");

		if ($i = $stmt->fetch())
		{
			$rank = $i['title'];
		}

		if (!$rank)
		{
			$rank = $ibforums->lang['no_info'];
		}

		$info['member_title'] = $member['title']
			? $member['title']
			: $rank;

		// Song * online

		if ($member['s_id'])
		{
			$online = "<{ONLINE}>";
		} else
		{
			$online = "";
		}
		$info['online'] = $online;

		// Song * last activity, 15.11.2004

		$info['last_activity'] = $std->get_date($member['last_activity']);

		// Song * dual reputation, 15.01.05

		if ($ibforums->member['show_ratting'] and $member['show_ratting'])
		{
			$this->view_rep($member, $info, 'rep');
			$info['rep'] = View::make("profile.show_rep", ['info' => $info]);

			$this->view_rep($member, $info, 'ratting');
			$info['ratting'] = View::make("profile.show_ratting", ['info' => $info]);
		}

		// /* <--- Jureth ---  Show DigiMoney in profile*/
		$info['fines'] = $std->do_number_format($member['fined']);
		$info['fines'] = View::make("profile.show_fines", ['info' => $info]);
		/* >--- Jureth --- */

		$info['aim_name'] = $member['aim_name']
			? $member['aim_name']
			: $ibforums->lang['no_info'];

		if ($member['icq_number'])
		{
			$info['icq_status'] = "<a href=http://wwp.icq.com/{$member['icq_number']}#pager target='_blank' title='{$member['icq_number']}' alt='{$member['icq_number']}'><img src=http://online.mirabilis.com/scripts/online.dll?icq={$member['icq_number']}&img=5 width=18 height=18 border=0 align=top></a>";
			$info['icq_icon']   = "<a href='http://wwp.icq.com/scripts/search.dll?to={$member['icq_number']}'><{P_ICQ}></a>";
			$info['icq_number'] = $member['icq_number'];
		} else
		{
			$info['icq_status'] = "";
			$info['icq_icon']   = "";
			$info['icq_number'] = "";
		}

		$info['yahoo']     = $member['yahoo']
			? $member['yahoo']
			: $ibforums->lang['no_info'];
		$info['location']  = $member['location']
			? $member['location']
			: $ibforums->lang['no_info'];
		$info['interests'] = $member['interests']
			? $member['interests']
			: $ibforums->lang['no_info'];
		$info['msn_name']  = $member['msnname']
			? $member['msnname']
			: $ibforums->lang['no_info'];
		$info['integ_msg'] = $member['integ_msg']
			? $member['integ_msg']
			: $ibforums->lang['no_info'];

		$ibforums->vars['time_adjust'] = $ibforums->vars['time_adjust'] == ""
			? 0
			: $ibforums->vars['time_adjust'];

		if ($member['dst_in_use'] == 1)
		{
			$member['time_offset'] += 1;
		}

		// This is a useless comment. Completely void of any useful information

		$offset = $std->get_member_time_offset_or_set_timezone($member);
		if ($offset == 0)
		{
			$zone               = new DateTimeZone(date_default_timezone_get());
			$time               = new DateTime("now", $zone);
			$info['local_time'] = $time->format($ibforums->vars['clock_long']);
		} else
		{
			$info['local_time'] = $member['time_offset'] != ""
				? gmdate($ibforums->vars['clock_long'], time() + $offset)
				: $ibforums->lang['no_info'];
		}

		$info['avatar'] = $std->get_avatar($member['avatar'], 1, $member['avatar_size']);

		$data = array(
			'TEXT'      => $member['signature'],
			'SMILIES'   => 1,
			'CODE'      => 1,
			'SIGNATURE' => 0,
			'HTML'      => $ibforums->vars['sig_allow_html'],
			'HID'       => -1,
			'TID'       => 0
		);

		$info['signature'] = $this->parser->prepare($data);

		if ($ibforums->vars['sig_allow_html'] == 1)
		{
			$info['signature'] = $this->parser->parse_html($info['signature'], 0);
		}

		if ($member['website'] and preg_match("/^http:\/\/\S+$/", $member['website']))
		{
			$info['homepage'] = "<a href='{$member['website']}' target='_blank'>{$member['website']}</a>";

		} else {
			$info['homepage'] = $ibforums->lang['no_info'];
		}

		if ($member['gender'] == 'm')
		{
			$info['gender'] = $ibforums->lang['gender_male'];
		} elseif ($member['gender'] == 'f')
		{
			$info['gender'] = $ibforums->lang['gender_female'];
		} else
		{
			$info['gender'] = $ibforums->lang['no_info'];
		}

		if ($member['bday_month'])
		{
			$info['birthday'] = $member['bday_day'] . " " . $ibforums->lang['M_' . $member['bday_month']] . " " . $member['bday_year'];

		} else {
			$info['birthday'] = $ibforums->lang['no_info'];
		}

		if (!$member['hide_email'])
		{
			$info['email'] = "<a href='{$this->base_url}act=Mail&amp;CODE=00&amp;MID={$member['id']}'>{$ibforums->lang['click_here']}</a>";

		} else
		{
			$info['email'] = $ibforums->lang['private'];

			if ($ibforums->member['g_is_supmod'])
			{
				$info['email'] .= " (<a href='{$this->base_url}act=Mail&amp;CODE=00&amp;MID={$member['id']}'>{$ibforums->lang['click_here']}</a>)";
			}
		}

		//---------------------------------------------------
		// Get photo and show profile:
		//---------------------------------------------------

		$this->load_photo($id);

		if ($this->has_photo == TRUE)
		{
			$info['photo'] = View::make(
				"profile.get_photo",
				[
					'show_photo'  => $this->show_photo,
					'show_width'  => $this->show_width,
					'show_height' => $this->show_height
				]
			);

		} else {
			$info['photo'] = "";
		}

		$info['base_url'] = $this->base_url;

		$info['posts'] = $std->do_number_format($info['posts']);

		//---------------------------------------------------
		// Output
		//---------------------------------------------------

		$this->output .= View::make("profile.show_profile", ['info' => $info]);

		//---------------------------------------------------
		// Is this our profile?
		//---------------------------------------------------

		if ($member['id'] == $this->member['id'])
		{
			$this->output = preg_replace_callback("/<!--MEM OPTIONS-->/", function($matches) { return View::make(
					'profile.user_edit',
					['info' => $matches]
				); }, $this->output);
		}

		//---------------------------------------------------
		// Can mods see the hidden parts of this profile?
		//---------------------------------------------------

		$query_extra = 'WHERE fedit=1 AND fhide <> 1';
		$custom_out  = "";
		$field_data  = array();

		//    	if ($ibforums->member['id'])
		//        {
		//        	if ($ibforums->member['g_is_supmod'] == 1)
		//        	{
		//        		$query_extra = "";
		//        	}
		//        	else if ($ibforums->member['mgroup'] == $ibforums->vars['admin_group'])
		//        	{
		//        		$query_extra = "";
		//        	}
		//        }

		$stmt = $ibforums->db->query("SELECT *
		    FROM ibf_pfields_content
                    WHERE member_id='" . $member['id'] . "'");

		while ($content = $stmt->fetch())
		{
			foreach ($content as $k => $v)
			{
				if (preg_match("/^field_(\d+)$/", $k, $match))
				{
					$field_data[$match[1]] = $v;
				}
			}
		}

		$stmt = $ibforums->db->query("SELECT *
		    FROM ibf_pfields_data
                    $query_extra
                    ORDER BY forder");

		while ($row = $stmt->fetch())
		{
			if ($row['ftype'] == 'drop')
			{
				$carray = explode('|', trim($row['fcontent']));

				foreach ($carray as $entry)
				{
					$value = explode('=', $entry);

					$ov = trim($value[0]);
					$td = trim($value[1]);

					if ($field_data[$row['fid']] == $ov)
					{
						$field_data[$row['fid']] = $td;
					}
				}

			} else {
				$field_data[$row['fid']] = ($field_data[$row['fid']] == "")
					? $ibforums->lang['no_info']
					: nl2br($field_data[$row['fid']]);
			}

			$custom_out .= View::make(
				"profile.custom_field",
				['title' => $row['ftitle'], 'value' => $field_data[$row['fid']]]
			);
		}

		if ($custom_out)
		{
			$this->output = str_replace("<!--{CUSTOM.FIELDS}-->", $custom_out, $this->output);
		}

		//---------------------------------------------------
		// Warning stuff!!
		//---------------------------------------------------

		$pass = 0;
		$mod  = 0;

		if ($ibforums->vars['warn_on'] and (!stristr($ibforums->vars['warn_protected'], ',' . $member['mgroup'] . ',')))
		{
			if ($ibforums->member['id'])
			{
				if ($ibforums->member['g_is_supmod'] == 1)
				{
					$pass = 1;
					$mod  = 1;
				} else
				{
					$stmt            = $ibforums->db->query("SELECT *
					    FROM ibf_moderators
					    WHERE (member_id=" . $ibforums->member['id'] . "
						OR (is_group=1 AND group_id=" . $ibforums->member['mgroup'] . "))");
					$this->moderator = $stmt->fetch();

					if ($this->moderator['mid'] AND $this->moderator['allow_warn'] == 1)
					{
						$pass = 1;
						$mod  = 1;
					}
				}

				if ($pass == 0 and ($ibforums->vars['warn_show_own'] and ($member['id'] == $ibforums->member['id'])))
				{
					$pass = 1;
				}

				if ($pass == 1)
				{
					// Work out which image to show.

					if (!$ibforums->vars['warn_show_rating'])
					{
						if ($member['warn_level'] < 1)
						{
							$member['warn_img'] = '<{WARN_0}>';
						} else {
							if ($member['warn_level'] >= $ibforums->vars['warn_max'])
							{
								$member['warn_img']     = '<{WARN_5}>';
								$member['warn_percent'] = 100;
							} else
							{
								$member['warn_percent'] = $member['warn_level']
									? sprintf("%.0f", (($member['warn_level'] / $ibforums->vars['warn_max']) * 100))
									: 0;

								if ($member['warn_percent'] > 100)
								{
									$member['warn_percent'] = 100;
								}

								if ($member['warn_percent'] >= 81)
								{
									$member['warn_img'] = '<{WARN_5}>';
								} else
								{
									if ($member['warn_percent'] >= 61)
									{
										$member['warn_img'] = '<{WARN_4}>';
									} else
									{
										if ($member['warn_percent'] >= 41)
										{
											$member['warn_img'] = '<{WARN_3}>';
										} else
										{
											if ($member['warn_percent'] >= 21)
											{
												$member['warn_img'] = '<{WARN_2}>';
											} else
											{
												if ($member['warn_percent'] >= 1)
												{
													$member['warn_img'] = '<{WARN_1}>';
												} else
												{
													$member['warn_img'] = '<{WARN_0}>';
												}
											}
										}
									}
								}
							}
						}

						if ($member['warn_percent'] < 1)
						{
							$member['warn_percent'] = 0;
						}

						if ($mod == 1)
						{
							$this->output = str_replace("<!--{WARN_LEVEL}-->",
								View::make(
									"profile.warn_level",
									[
										'mid'     => $member['id'],
										'img'     => $member['warn_img'],
										'percent' => $member['warn_percent']
									]
								),$this->output);
						} else
						{
							$this->output = str_replace("<!--{WARN_LEVEL}-->",
								View::make(
									"profile.warn_level_no_mod",
									[
										'mid'     => $member['id'],
										'img'     => $member['warn_img'],
										'percent' => $member['warn_percent']
									]
								),$this->output);
						}
					} else
					{
						// Rating mode:

						if ($mod == 1)
						{
							$this->output = str_replace("<!--{WARN_LEVEL}-->",
								View::make(
									"profile.warn_level_rating",
									[
										'mid'   => $member['id'],
										'level' => $member['warn_level'],
										'min'   => $ibforums->vars['warn_min'],
										'max'   => $ibforums->vars['warn_max']
									]
								), $this->output);
						} else
						{
							$this->output = str_replace("<!--{WARN_LEVEL}-->",
								View::make(
									"profile.warn_level_rating_no_mod",
									[
										'mid'   => $member['id'],
										'level' => $member['warn_level'],
										'min'   => $ibforums->vars['warn_min'],
										'max'   => $ibforums->vars['warn_max']
									]
								), $this->output);
						}
					}
				}
			}
		}

		$this->page_title = $ibforums->lang['page_title'];
		$this->nav        = array($ibforums->lang['page_title']);

	}

	function Mod_Forums_Count($mid)
	{
		global $ibforums, $std;

		$stmt = $ibforums->db->query("SELECT count(*) AS count
			FROM ibf_moderators md
			WHERE md.member_id='" . $mid . "'");
		$i    = $stmt->fetch();

		return $i['count'];
	}

	function Mod_Forums_All($mid)
		// If result above zero, member can moderate all forums
	{
		global $ibforums, $std;

		$sup_ids = array();

		$stmt = $ibforums->db->query("SELECT g_id
		    FROM ibf_groups
		    WHERE g_is_supmod = 1");

		if ($stmt->rowCount())
		{
			while ($i = $stmt->fetch())
			{
				$sup_ids[] = $i['g_id'];
			}
		}

		//--------------------------------------------
		// Get our admins
		//--------------------------------------------

		$admin_ids = array();

		$stmt = $ibforums->db->query("SELECT id, mgroup
		    FROM ibf_members
		    WHERE id='" . $mid . "'
			AND mgroup='" . $ibforums->vars['admin_group'] . "'");

		if ($stmt->rowCount())
		{
			$member_is_admin = 1;
		} else
		{
			$member_is_admin = 0;
		}

		//--------------------------------------------
		// Do the bizz with the super men, er mods.
		//--------------------------------------------

		$admin_ids[] = '0';

		if (count($sup_ids) > 0)
		{

			$stmt = $ibforums->db->query("SELECT id, mgroup
			    FROM ibf_members
			    WHERE id='" . $mid . "'
				AND mgroup IN (" . implode(',', $sup_ids) . ")
				AND mgroup<>'" . $ibforums->vars['admin_group'] . "' ");
			if ($stmt->rowCount())
			{
				$member_is_sup = 1;
			} else
			{
				$member_is_sup = 0;
			}
			return $member_is_sup + $member_is_admin;
		}
	}

}

