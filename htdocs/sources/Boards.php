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
  |   > Board index module
  |   > Module written by Matt Mecham
  |   > Date started: 17th February 2002
  |
  |	> Module Version Number: 1.0.0
  +--------------------------------------------------------------------------
 */


$idx = new Boards;

class Boards {

	var $output = "";
	var $base_url = "";
	var $html = "";
	var $forums = array();
	var $mods = array();
	var $cats = array();
	var $children = array();
	var $nav;
	var $news_topic_id = "";
	var $news_forum_id = "";
	var $news_title = "";
	var $news_topic_id2 = "";
	var $news_forum_id2 = "";
	var $news_title2 = "";
	var $our_poll_topic_id = "";
	var $our_poll_forum_id = "";
	var $our_poll_title = "";
	var $sep_char = "";
	var $moderators = "";
	var $contr = array();
	var $fs = array();
	var $cs = array();

	function Boards()
	{
		global $std, $print, $skin_universal;

		$ibf = Ibf::app();
		$this->base_url = $ibf->base_url;

		// Get more words for this invocation!

		$ibf->lang = $std->load_words($ibf->lang, 'lang_boards', $ibf->lang_id);

		$this->html = $std->load_template('skin_boards');

		$this->sep_char = trim($this->html->active_list_sep());

		if (!$ibf->member['id'])
			$ibf->input['last_visit'] = time();

		// Song * show all forums link, 09.03.05

		$show_all_link = ( $ibf->member['id'] and !$ibf->input['show'] )
			? $this->html->ShowAllLink()
			: "";

		$this->output .= $this->html->PageTop($show_all_link);


		// Get the forums and category info from the DB

		$forums_visibility = array();
		$cats_visibility = array();

		// Song * select visibility state of cats and forums

		if ($ibf->member['id'])
		{
			$stmt = $ibf->db->prepare('SELECT id,is_forum,is_visible
			    FROM ibf_boards_visibility
			    WHERE user_id=:id');
			$stmt->execute([':id' => $ibf->member['id']]);

			while ($row = $stmt->fetch())
			{
				if ($row['is_forum'])
				{
					$forums_visibility[$row['id']] = $row['is_visible'];
				} else
				{
					$cats_visibility[$row['id']] = $row['is_visible'];
				}
			}
		}



		// Song * querying only forums from category, 31.01.05

		if ($ibf->input['c'])
			$ibf->input['c'] = intval($ibf->input['c']);

		$cat = ( $ibf->input['c'] )
			? " and c.id=:cat"
			: "";


		// Get all the Categories & Forums

		$stmt = $ibf->db->prepare("SELECT
			f.*,
			c.id as cat_id,
			c.position as cat_position,
			c.state as cat_state,
			c.name as cat_name,
			c.description as cat_desc,
			c.image,
			c.url,
			m.member_name as mod_name,
			m.member_id as mod_id,
			m.post_q,
			m.topic_q
		    FROM (ibf_forums f, ibf_categories c )
		    LEFT JOIN ibf_moderators m ON (f.id=m.forum_id)
		    WHERE c.id=f.category
			{$cat}
		    ORDER BY c.position,f.position");
		if ($ibf->input['c']){
			$stmt->bindParam(':cat', $ibf->input['c']);
		}
		$stmt->execute();

		$last_c_id = -1;

		while ($r = $stmt->fetch())
		{
			if ($last_c_id != $r['cat_id'])
			{
				// state visibility of cat
				$visible = $cats_visibility[$r['cat_id']];

				if (!isset($visible))
					$visible = 1;

				$this->cats[$r['cat_id']] = array('id'			 => $r['cat_id'],
					'position'		 => $r['cat_position'],
					'state'			 => $r['cat_state'],
					'name'			 => $r['cat_name'],
					'icon'			 => $r['cat_icon'],
					'description'	 => $r['cat_desc'],
					'image'			 => $r['image'],
					'url'			 => $r['url'],
					'visible'		 => $visible,
				);
				$last_c_id = $r['cat_id'];
			}

			// state visibility of forum
			$visible = $forums_visibility[$r['id']];
			if (!isset($visible))
				$visible = 1;
			if (!$ibf->member['id'])
				$visible = 0;

			$r['visible'] = $visible;

			if ($r['parent_id'] > 0)
			{
				$this->children[$r['parent_id']][$r['id']] = $r;
			} else
			{
				$this->forums[$r['id']] = $r;
			}

			if ($r['mod_id'])
			{
				$this->mods[$r['id']][$r['mod_id']] = array(
					'post_q'	 => $r['post_q'],
					'topic_q'	 => $r['topic_q']
				);
			}
		}


		//-----------------------------------
		// What are we doing?
		//-----------------------------------

		if ($ibf->input['c'])
		{
			$this->show_single_cat();

			$this->nav[] = $this->cats[$ibf->input['c']]['name'];
		} else
		{
			$this->process_all_cats();

//-- mod_global_poll begin

			if ($ibf->vars['global_poll'])
			{
				if (file_exists($ibf->vars['base_dir'] . "sources/mods/global_poll/mod_global_poll_func.php"))
				{
					require $ibf->vars['base_dir'] . "sources/mods/global_poll/mod_global_poll_func.php";
				} else
					die("Could not call required function from file 'sources/mods/global_poll/mod_global_poll_func.php'<br>Does it exist?");

				$global_poll = new global_poll;

				$this->output .= $global_poll->getOutput();
			}

//-- mod_global_poll end
		}


		// Show Stats for Members only!  //vot

		$stats_html = "";

		if ($ibf->member['id'])
		{

			//*********************************************/
			// Add in show online users
			//*********************************************/

			$active = array('TOTAL'		 => 0,
				'NAMES'		 => "",
				'GUESTS'	 => 0,
				'MEMBERS'	 => 0,
				'ANON'		 => 0,
				'FRIENDS'	 => "",
			);

			if ($ibf->vars['show_active'])
			{
				if (!$ibf->vars['au_cutoff'])
					$ibf->vars['au_cutoff'] = 15;

				// Get the users from the DB

				$cut_off = $ibf->vars['au_cutoff'] * 60;
				$time = time() - $cut_off;

				// Song * friends show online, 05.03.05

				$friends = array();

				if ($ibf->member['id'])
				{
					$stmt = $ibf->db->prepare("SELECT contact_id
					    FROM ibf_contacts
					    WHERE
							member_id = :member_id
						AND
							show_online = :online")
						->bindParam(':member_id', $ibf->member['id'])
						->bindValue(':online', 1)
						->execute();

					while ($contact = $stmt->fetch())
					{
						$friends[$contact['contact_id']] = $contact['contact_id'];
					}
				}


				// Online members

				$stmt = $ibf->db->prepare("
				SELECT
					s.id,
					s.member_id,
					s.member_name,
					s.login_type,
					g.suffix,
					g.prefix
			    FROM ibf_sessions s
	            LEFT JOIN ibf_groups g ON (g.g_id=s.member_group)
		        WHERE
					s.running_time > ?
				AND
					g.g_id NOT IN(?, ?)
				"); // Not BAN, Not Awaiting
				$stmt->execute([$time, 1, 5]);//todo replace constats with config or smth

				// cache all printed members so we don't double print them

				$cached = array();

				while ($result = $stmt->fetch())
				{
					if (mb_strstr($result['id'], '_session'))
					{
						if ($ibf->vars['spider_anon'])
						{
							if ($ibf->member['mgroup'] == $ibf->vars['admin_group'])
							{
								$active['NAMES'] .= "{$result['member_name']}*{$this->sep_char} \n";
							}
						} else
							$active['NAMES'] .= "{$result['prefix']}{$result['member_name']}{$result['suffix']}{$this->sep_char} \n";
					} elseif (!$result['member_id'])
					{
						$active['GUESTS']++;
					} else
					{
						if (empty($cached[$result['member_id']]))
						{
							$cached[$result['member_id']] = 1;

							if ($result['login_type'] == 1)
							{
								$active['ANON']++;

								if ($ibf->member['mgroup'] == $ibf->vars['admin_group'] and
									$ibf->vars['disable_admin_anon'] != 1)
								{
									// Song * friends show online, 05.03.05
									if (isset($friends[$result['member_id']]))
									{
										$active['FRIENDS'] .= "<a href='{$ibf->base_url}showuser={$result['member_id']}'>{$result['prefix']}{$result['member_name']}{$result['suffix']}</a>*{$this->sep_char} \n";
									} else
									{
										$active['NAMES'] .= "<a href='{$ibf->base_url}showuser={$result['member_id']}'>{$result['prefix']}{$result['member_name']}{$result['suffix']}</a>*{$this->sep_char} \n";
									}
								}
							} else
							{
								$active['MEMBERS']++;
								// Song * friends show online, 05.03.05
								if (isset($friends[$result['member_id']]))
								{
									$active['FRIENDS'] .= "<a href='{$ibf->base_url}showuser={$result['member_id']}'>{$result['prefix']}{$result['member_name']}{$result['suffix']}</a>{$this->sep_char} \n";
								} else
								{
									$active['NAMES'] .= "<a href='{$ibf->base_url}showuser={$result['member_id']}'>{$result['prefix']}{$result['member_name']}{$result['suffix']}</a>{$this->sep_char} \n";
								}
							}
						}
					}
				}

				$active['NAMES'] = preg_replace("/" . preg_quote($this->sep_char) . "$/", "", trim($active['NAMES']));

				// Song * friends show online, 05.03.05

				$active['FRIENDS'] = preg_replace("/" . preg_quote($this->sep_char) . "$/", "", trim($active['FRIENDS']));



				$active['TOTAL'] = $active['MEMBERS'] + $active['GUESTS'] + $active['ANON'];

				// Show a link?

				if ($ibf->vars['allow_online_list'])
					$active['links'] = $this->html->active_user_links();

				$ibf->lang['active_users'] = sprintf($ibf->lang['active_users'], $ibf->vars['au_cutoff']);

				// Song * friends show online, 05.03.05

				$friends = ( $active['FRIENDS'] )
					? $this->html->ActiveFriends($active)
					: "";

				$stats_html .= $this->html->ActiveUsers($active, $friends);



				// Song * who was online today and yesterday

				$user_stat_html = $ibf->lang['users_online_today'];

				// fetch today info
				//todo is it possible to make with sql only?
				$stats = $ibf->db->prepare("SELECT members, guests, bots FROM ibf_users_stat WHERE day = :day");
				$today = $stats->bindValue(':day', $std->current_day())
					->execute()
					->fetch();
				$stats->closeCursor();

				if (FALSE === $today)
				{
					$today = [
						'members' => "0",
						'guests'  => "0",
						'bots'    => "0"
					];
				}

				// members
				$user_stat_html = str_replace("#USERS#", $today['members'], $user_stat_html);
				$members = $today['members'];

				// guests
				$user_stat_html = str_replace("#GUESTS#", $today['guests'], $user_stat_html);
				$guests = $today['guests'];

				// bots
				$user_stat_html = str_replace("#BOTS#", $today['bots'], $user_stat_html);
				$bots = $today['bots'];

				// all
				$user_stat_html = str_replace("#ALL#", $members + $guests + $bots, $user_stat_html);

				// yesterday info at the new line
				$user_stat_html .= "<br>" . $ibf->lang['users_online_yesterday'];

				// fetch yesterday info
				$yesterday =  $stats->bindValue(':day', $std->yesterday_day())
					->execute()
					->fetch();
				$stats->closeCursor();

				if (FALSE === $yesterday)
				{
					$yesterday = [
						'members' => "0",
						'guests'  => "0",
						'bots'    => "0"
					];
				}

				// members
				$user_stat_html = str_replace("#USERS#", $yesterday['members'], $user_stat_html);

				// guets
				$user_stat_html = str_replace("#GUESTS#", $yesterday['guests'], $user_stat_html);

				// bots
				$user_stat_html = str_replace("#BOTS#", $yesterday['bots'], $user_stat_html);

				// all
				$user_stat_html = str_replace("#ALL#", $yesterday['members'] + $yesterday['guests'] + $yesterday['bots'], $user_stat_html);
			}

			//-----------------------------------------------
			// Are we viewing Birthdays?
			//-----------------------------------------------

			if ($ibf->vars['show_birthdays'])
			{
				$a = explode(',', gmdate('Y,n,j,G,i,s', time() + $std->get_time_offset_or_set_timezone()));

				$day   = (int)$a[2];
				$month = (int)$a[1];
				$year  = (int)$a[0];

				$count = 0;

				$stmt = $ibf->db->prepare("SELECT
				id, name, bday_day as DAY,
				bday_month as MONTH, bday_year as YEAR
			    FROM ibf_members
			    WHERE
				bday_day=:day and
				bday_month=:month and
				posts > :max_posts");
				$stmt->bindParam(':day', $day, PDO::PARAM_INT);
				$stmt->bindParam(':month', $month, PDO::PARAM_INT);
				$stmt->bindValue(':max_posts', 500, PDO::PARAM_INT);
				$stmt->execute();

				$birthstring = '';
				foreach ($stmt as $user)
				{
					$birthstring .= "<a href='{$this->base_url}showuser={$user['id']}'>{$user['name']}</a>";

					if ($user['YEAR'])
					{
						$pyear = $year - $user['YEAR'];  // $year = 2002 and $user['YEAR'] = 1976
						$birthstring .= "(<b>$pyear</b>)";
					}

					$birthstring .= $this->sep_char . "\n";

					$count++;
				}

				$birthstring = preg_replace("/" . $this->sep_char . "$/", "", trim($birthstring));

				$lang = $ibf->lang['no_birth_users'];

				if ($count > 0)
				{
					$lang = ($count > 1)
						? $ibf->lang['birth_users']
						: $ibf->lang['birth_user'];
					$stats_html .= $this->html->birthdays($birthstring, $count, $lang);
				} else
				{
					$count = "";

					if (!$ibf->vars['autohide_bday'])
					{
						$stats_html .= $this->html->birthdays($birthstring, $count, $lang);
					}
				}
			}

			//-----------------------------------------------
			// Are we viewing the calendar?
			//-----------------------------------------------

			if ($ibf->vars['show_calendar'])
			{
				if ($ibf->vars['calendar_limit'] < 2)
					$ibf->vars['calendar_limit'] = 2;

				$our_unix = time() + $std->get_time_offset_or_set_timezone();

				$max_date = $our_unix + ($ibf->vars['calendar_limit'] * 86400);

				$stmt = $ibf->db->prepare("
				SELECT
					eventid, title, read_perms, priv_event, userid, unix_stamp
			    FROM ibf_calendar_events
			    WHERE
					unix_stamp > :our_unix and
					unix_stamp < :max_date
			    ORDER BY unix_stamp");
				$stmt->bindParam(':our_unix', $our_unix, PDO::PARAM_INT);
				$stmt->bindParam(':max_date', $max_date, PDO::PARAM_INT);
				$stmt->execute();

				$show_events = array();

				while ($event = $stmt->fetch())
				{
					if ($event['priv_event'] == 1 and $ibf->member['id'] != $event['userid'])
					{
						continue;
					}

					//-----------------------------------------
					// Do we have permission to see the event?
					//-----------------------------------------

					if ($event['read_perms'] != '*')
					{
						if (!preg_match("/(^|,)" . $ibf->member['mgroup'] . "(,|$)/", $event['read_perms']))
						{
							continue;
						}
					}

					$c_time = date('j-F-y', $event['unix_stamp']);

					$show_events[] = "<a href='{$ibf->base_url}act=calendar&amp;code=showevent&amp;eventid={$event['eventid']}' title='$c_time'>" . $event['title'] . "</a>";
				}

				$ibf->lang['calender_f_title'] = sprintf($ibf->lang['calender_f_title'], $ibf->vars['calendar_limit']);

				if (count($show_events) > 0)
				{
					$event_string = implode($this->sep_char . ' ', $show_events);
					$stats_html .= $this->html->calendar_events($event_string);
				} elseif (!$ibf->vars['autohide_calendar'])
				{
					$event_string = $ibf->lang['no_calendar_events'];
					$stats_html .= $this->html->calendar_events($event_string);
				}
			}

			//*********************************************/
			// Add in show stats
			//*********************************************/

			if ($ibf->vars['show_totals'])
			{
				$stmt = $ibf->db->prepare("SELECT * FROM ibf_stats");
				$stmt->execute();
				$stats = $stmt->fetch();//current tops

				// Update the most active count if needed
				if ($active['TOTAL'] > $stats['MOST_COUNT'])
				{
					$stats['MOST_COUNT'] = $active['TOTAL'];
					$stats['MOST_DATE'] = time();
				}

				//which is top: today or yesterday?
				if ($members + $guests + $bots < $yesterday['members'] + $yesterday['guests'] + $yesterday['bots']) {
					$date = time() - 3600 * 24;
					$total = $yesterday['members'] + $yesterday['guests'] + $yesterday['bots'];
					$members = $yesterday['members'];
					$guests = $yesterday['guests'];
					$bots = $yesterday['bots'];
				}else{//today
					$date = time();
					$total = $members + $guests + $bots;
				}

				if ($total > $stats['record']){//is it new record?
					$stats['record'] = $total;
					$stats['members'] = $members;
					$stats['guests'] = $guests;
					$stats['bots'] = $bots;
					$stats['record_date'] = $date;
				}

				//tops by cat. We count only past day so we don't need to check $members/$guests/etc
				$stats['h_members'] = max($stats['h_members'], $yesterday['members'], $stats['members']);
				$stats['h_guests']  = max($stats['h_guests'], $yesterday['guests'], $stats['guests']);
				$stats['h_bots']    = max($stats['h_bots'], $yesterday['bots'], $stats['bots']);

				//todo separate function?
				$params = [];
				$sql = [];
				foreach($stats as $key => $value){
					$params[':' . $key] = $value;
					$sql[] = "$key = :$key";
				}
				$ibf->db
					->prepare('UPDATE ibf_stats SET ' . implode(',', $sql))
					->execute($params);

				// Song * record of visit

				$most_time = $std->format_date_without_time($stats['MOST_DATE']);

				$ibf->lang['most_online'] = str_replace("<#NUM#>", $std->do_number_format($stats['MOST_COUNT']), $ibf->lang['most_online']);
				$ibf->lang['most_online'] = str_replace("<#DATE#>", $most_time, $ibf->lang['most_online']);



				$ibf->lang['online_record'] = str_replace("#USERS#", $stats['members'], $ibf->lang['online_record']);
				$ibf->lang['online_record'] = str_replace("#GUESTS#", $stats['guests'], $ibf->lang['online_record']);
				$ibf->lang['online_record'] = str_replace("#BOTS#", $stats['bots'], $ibf->lang['online_record']);

				$record_date = $std->format_date_without_time($stats['record_date']);

				$ibf->lang['category_record'] = str_replace("#USERS#", $stats['h_members'], $ibf->lang['category_record']);
				$ibf->lang['category_record'] = str_replace("#GUESTS#", $stats['h_guests'], $ibf->lang['category_record']);
				$ibf->lang['category_record'] = str_replace("#BOTS#", $stats['h_bots'], $ibf->lang['category_record']);

				$ibf->lang['online_record'] = str_replace("#ALL#", $stats['record'], $ibf->lang['online_record']);
				$ibf->lang['online_record'] = str_replace("#DATE#", $record_date, $ibf->lang['online_record']);


				// Total message & last member

				$total_posts = $stats['TOTAL_REPLIES'] + $stats['TOTAL_TOPICS'];
				$total_posts = $std->do_number_format($total_posts);
				$stats['MEM_COUNT'] = $std->do_number_format($stats['MEM_COUNT']);

				$link = $ibf->base_url . "showuser=" . $stats['LAST_MEM_ID'];

				$ibf->lang['total_word_string'] = str_replace("<#posts#>", "$total_posts", $ibf->lang['total_word_string']);
				$ibf->lang['total_word_string'] = str_replace("<#reg#>", $stats['MEM_COUNT'], $ibf->lang['total_word_string']);
				$ibf->lang['total_word_string'] = str_replace("<#mem#>", $stats['LAST_MEM_NAME'], $ibf->lang['total_word_string']);
				$ibf->lang['total_word_string'] = str_replace("<#link#>", $link, $ibf->lang['total_word_string']);

				if ($user_stat_html)
					$stats_html .= $this->html->TodayOnline($user_stat_html);

				$stats_html .= $this->html->ShowStats($ibf->lang['total_word_string']);
			}
		}

		if ($stats_html)
		{
			$this->output .= $this->html->stats_header();
			$this->output .= $stats_html;
			$this->output .= $this->html->stats_footer();
		}

		//---------------------------------------
		// Add in board info footer
		//---------------------------------------

		$this->output .= $this->html->bottom_links();


// vot	$this->output .= '<% BOTTOM BANNER %>';
		//---------------------------------------
		// Check for news forum.
		//---------------------------------------

		if ($this->news_title and $this->news_topic_id and $this->news_forum_id)
		{
			$t_html = $this->html->newslink($this->news_forum_id, stripslashes($this->news_title), $this->news_topic_id);

			$this->output = str_replace("<!--IBF.NEWSLINK-->", "$t_html", $this->output);
		}

		if ($this->news_title2 and $this->news_topic_id2 and $this->news_forum_id2)
		{
			$t_html = $this->html->secondnewslink($this->news_forum_id2, stripslashes($this->news_title2), $this->news_topic_id2);

			$this->output = str_replace("<!--IBF.SECONDNEWSLINK-->", "$t_html", $this->output);
		}

		if ($this->our_poll_forum_id and $this->our_poll_topic_id and $this->our_poll_forum_id)
		{
			$t_html = $this->html->our_poll_link($this->our_poll_forum_id, stripslashes($this->our_poll_title), $this->our_poll_topic_id);

			$this->output = str_replace("<!--IBF.OUR_POLL_LINK-->", "$t_html", $this->output);
		}


		//---------------------------------------
		// Display quick login if we're not a member
		//---------------------------------------

		if (!$ibf->member['id'])
		{
			$this->output = str_replace("<!--IBF.QUICK_LOG_IN-->", $this->html->quick_log_in(), $this->output);
		}
		if ($ibf->vars['global_message_on'])
		{
			$message = preg_replace("/\n/", "<br>", stripslashes($ibf->vars['global_message']));

			$this->output = str_replace("<!--GLOBAL.MESSAGE-->", $this->html->show_global_message($message), $this->output);
		}

		$print->add_output("$this->output");

		$cp = " (Powered by Invision Power Board)";

		if ($ibf->vars['ips_cp_purchase'])
			$cp = "";

		// Song * RSS, 31.01.05

		$rss = ( $cat )
			? $skin_universal->rss("?c=" . $ibf->input['c'])
			: "";



		$print->do_output(array('TITLE'	 => $ibf->vars['board_name'],
			'JS'	 => 0,
			'NAV'	 => $this->nav,
			'RSS'	 => $rss,
		));
	}

	//*********************************************/
	//
	// PROCESS ALL CATEGORIES
	//
	//*********************************************/

	function process_all_cats()
	{

		global $std, $ibforums;

		// get boards visibility
		if ($ibforums->member['id'])
		{
			$list = explode(",", $ibforums->member['board_layout']);
			$fm_s = -1;
			foreach ($list as $l)
			{
				if (mb_substr($l, 1, 1) == "c")
				{
					if (isset($c_id))
						if (!isset($this->contr[$c_id]))
							$this->contr[$c_id] = $fm_s; else
						{
							$this->contr[$c_id] = $fm_s;
							$fm_s = -1;
						} //-1 no forums,0 show contract,1 some something
					$c_id = mb_substr($l, 2);
					$this->cs[$c_id] = mb_substr($l, 0, 1);
					if (!isset($this->contr[$c_id]))
						$this->contr[$c_id] = $fm_s; else
					{
						$this->contr[$c_id] = $fm_s;
						$fm_s = -1;
					} //-1 no forums,0 show contract,1 some something
				} elseif (mb_substr($l, 1, 1) == "f")
				{
					$this->fs[mb_substr($l, 2)] = mb_substr($l, 0, 1);
					if ($fm_s != 1)
						$fm_s = mb_substr($l, 0, 1);
				}
			}

			if (isset($c_id))
				if (!isset($this->contr[$c_id]))
					$this->contr[$c_id] = $fm_s; else
				{
					$this->contr[$c_id] = $fm_s;
					$fm_s = -1;
				} //-1 no forums,0 show contract,1 some something
		}


		// Loop for each Category

		$cat_counter = 0;
		foreach ($this->cats as $cat_id => $cat_data)
		{
			//----------------------------
			// Is this category turned on?
			//----------------------------
			// if category is hidden
// vot: check for portal
//		if(defined('PORTAL'))
//		{
//		  if ( $cat_data['state'] <= 1 or
//		     !( !$ibforums->member['id'] or
//			$this->cs[ $cat_data['id'] ] == 1 or
//		        $this->cs[ $cat_data['id'] ] == "" or
//			$ibforums->input['show']
//		      )
//		   ) continue;
//		}
//		else
//		{
			if ($cat_data['state'] != 1 or
				!(!$ibforums->member['id'] or
				$this->cs[$cat_data['id']] == 1 or
				$this->cs[$cat_data['id']] == "" or
				$ibforums->input['show']
				)
			)
				continue;

//		}
			// if category is minimized
			// draw minimized category
			if ($cat_data['visible'] == 0)
			{
				$plus = ( $ibforums->member['id'] )
					? $this->html->CatPlus($cat_data['id'])
					: "";

				$this->output .= $this->html->CatHeader_Collapsed($cat_data, $plus);

				$this->output .= $this->html->end_all_cats();
//			$this->output .= $this->html->end_this_cat();
				// vot: Draw inter-categories banner
//			$this->output .= "\$cat_counter=".$cat_counter."<br>";
//			$this->output .= $this->html->draw_middle_banner();

				continue;
			}

			$cat_counter++; // vot: counter for MIDDLE BANNER
			// draw forums and subforums of current category
			$temp_html = "";
			foreach ($this->forums as $forum_id => $forum_data)
			{
				if ($forum_data['category'] == $cat_id)
				{
					$temp_html .= $this->process_forum($forum_id, $forum_data);
				}
			}

			// if there are any open subforums
			// draw expanded caregory
			if ($temp_html)
			{
				$minus = ( $ibforums->member['id'] )
					? $this->html->CatMinus($cat_data['id'])
					: "";

				$this->output .= $this->html->CatHeader_Expanded($cat_data, $minus);
				$this->output .= $temp_html;



				$this->output .= $this->html->end_this_cat();

				unset($temp_html);
			}
//	$this->output .= "\$cat_counter=".$cat_counter."<br>";
			if ($cat_counter == 2)
			{
				$this->output .= "<% MIDDLE BANNER %>";
			}
		}

		$this->output .= $this->html->end_all_cats();
	}

	//*********************************************/
	//
	// SHOW A SINGLE CATEGORY
	//
	//*********************************************/

	function show_single_cat()
	{

		global $std, $ibforums;

		$cat_id = $ibforums->input['c'];

		if (!is_array($this->cats[$cat_id]))
		{
			$std->Error(array('LEVEL'	 => 1, 'MSG'	 => 'missing_files'));
		}

		$cat_data = $this->cats[$cat_id];

		//----------------------------
		// Is this category turned on?
		//----------------------------

		if ($cat_data['state'] == 0)
			$std->Error(array('LEVEL'	 => 1, 'MSG'	 => 'missing_files'));



		//----------------------------
		// Loop for each forum in this category
		//----------------------------

		foreach ($this->forums as $forum_id => $forum_data)
		{
			if ($forum_data['category'] == $cat_id)
			{
				//-----------------------------------
				// We store the HTML in a temp var so
				// we can make sure we have cats for
				// this forum, or hidden forums with a
				// cat will show the cat strip - we don't
				// want that, no - we don't.
				//-----------------------------------

				$temp_html .= $this->process_forum($forum_id, $forum_data);
			}
		}

		if ($temp_html)
		{
			$this->output .= $this->html->CatHeader_Expanded($cat_data);
			$this->output .= $temp_html;
			$this->output .= $this->html->end_this_cat();
		} else
			$std->Error(array('LEVEL'	 => 1, 'MSG'	 => 'missing_files'));

		unset($temp_html);

		$this->output .= $this->html->end_all_cats();
	}

	//*********************************************/
	//
	// RENDER A FORUM
	//
	//*********************************************/

	function process_forum($forum_id = "", $forum_data = "", $level = 0, $op = 1)
	{
		global $std, $ibforums;

		$result = "";
		$expanded = false;

		if ($forum_data['subwrap'])
		{

			$printed_children = 0;

			$can_see_root = FALSE;

			//--------------------------------------
			// This is a sub cat forum...
			//--------------------------------------
			// Do we have any sub forums here?

			if (isset($this->children[$forum_data['id']]) and
				count($this->children[$forum_data['id']]) > 0)
			{
// Song + Shaman * forum visibility

				if ($ibforums->member['id'] and $op and
					!$ibforums->member['show_filter'] and
					$ibforums->member['close_category'] and
					!$ibforums->input['show'])
				{
					// default category is open
					$status = '<{C_ON}>';

					$st = intval($ibforums->input['expfor']);

					if ($st != $forum_id)
						$st = 0;

					if (!$st)
					{
						$status = '<{C_OFF}>';

						foreach ($this->children[$forum_data['id']] as $idx => $data)
						{
							//--------------------------------------
							// Check permissions...
							//--------------------------------------

							if ($std->check_perms($data['read_perms']) != TRUE)
								continue;

							// !!! do not cut "1"
							if ($data['visible'] == 1)
							{
								$temp_status = $std->forum_new_posts($data);

								if ($temp_status == '<{C_ON}>')
								{
									$status = $temp_status;
									break;
								}
							}
						}
					}
				}
// Song + Shaman
				$can_see_root = $std->check_perms($forum_data['read_perms']);

				// Are we allowed to see the postable forum stuff?

				if ($forum_data['sub_can_post'] and $forum_data['redirect_on'] != 1)
				{
					//--------------------------------------
					// Check permissions...
					//--------------------------------------

					if ($can_see_root != FALSE)
					{
						$forum_data['fid'] = $forum_data['id'];

						$newest = $forum_data;

						if (isset($forum_data['last_title']) and $forum_data['last_id'] != "")
						{
							if ($ibforums->vars['index_news_link'] == 1 and
								!empty($ibforums->vars['news_forum_id']) and
								$ibforums->vars['news_forum_id'] == $forum_data['id'])
							{
								$this->news_topic_id = $forum_data['last_id'];
								$this->news_forum_id = $forum_data['id'];
								$this->news_title = $forum_data['last_title'];
							}

							if ($ibforums->vars['index_news_link'] == 1 and
								!empty($ibforums->vars['news_forum_id2']) and
								$ibforums->vars['news_forum_id2'] == $forum_data['id'])
							{
								$this->news_topic_id2 = $forum_data['last_id'];
								$this->news_forum_id2 = $forum_data['id'];
								$this->news_title2 = $forum_data['last_title'];
							}


							if ($ibforums->vars['index_our_poll_link'] == 1 and
								!empty($ibforums->vars['our_poll_forum_id']) and
								$ibforums->vars['our_poll_forum_id'] == $forum_data['id'])
							{
								$this->our_poll_topic_id = $forum_data['last_id'];
								$this->our_poll_forum_id = $forum_data['id'];
								$this->our_poll_title = $forum_data['last_title'];
							}
						}
					} else
						$newest = array();
				} else
					$newest = array();

				$state = 0;

				// write subforums
				foreach ($this->children[$forum_data['id']] as $idx => $data)
				{
					//--------------------------------------
					// Check permissions...
					//--------------------------------------

					if ($std->check_perms($data['read_perms']) != TRUE)
						continue;

					$rtime = $data['last_post'];
// Song * NEW
					if ($ibforums->member['id'] and !$state and $ibforums->member['board_read'] < $rtime)
					{
						$ftime = $ibforums->forum_read[$data['id']];

						if ($ftime < $rtime)
							$state = 1;
					}
// Song * NEW
					if (!$forum_data['sub_can_post'] and $forum_data['redirect_on'] != 1)
					{
						// Do the news stuff first
						if (isset($data['last_title']) and $data['last_id'] != "")
						{
							if (( $ibforums->vars['index_news_link'] == 1 ) and (!empty($ibforums->vars['news_forum_id']) ) and ($ibforums->vars['news_forum_id'] == $data['id']))
							{
								$this->news_topic_id = $data['last_id'];
								$this->news_forum_id = $data['id'];
								$this->news_title = $data['last_title'];
							}

							if (( $ibforums->vars['index_news_link'] == 1 ) and (!empty($ibforums->vars['news_forum_id2']) ) and ($ibforums->vars['news_forum_id2'] == $data['id']))
							{
								$this->news_topic_id2 = $data['last_id'];
								$this->news_forum_id2 = $data['id'];
								$this->news_title2 = $data['last_title'];
							}
						}

						if ($data['last_post'] > $newest['last_post'])
						{
							$newest['last_post'] = $data['last_post'];
							$newest['fid'] = $data['id'];
							$newest['last_id'] = $data['last_id'];
							$newest['last_title'] = $data['last_title'];
							$newest['password'] = $data['password'];
							$newest['last_poster_id'] = $data['last_poster_id'];
							$newest['last_poster_name'] = $data['last_poster_name'];
							$newest['status'] = $data['status'];
						}

						$newest['posts'] += $data['posts'];
						$newest['topics'] += $data['topics'];
					}

					// draw subforums for level 0 and 1 only !
					if ($op)
					{
						$printed_children++;

						// try to write subforums if
						// 1) it's member and
						// 2) it works without filters and
						// 3) forums of this subforum is not minimized or
						// 4) it's mode "show all forums" or
						// 5) forum of this sub-forum is disabled in board layout or
						// 6) forum of this sub-forums is permited (maybe granted access to sub-forums ?)

						if ($ibforums->member['id'] and !$ibforums->member['show_filter'] and
							( $forum_data['visible'] or $ibforums->input['show'] or $can_see_root != TRUE or
							( isset($this->fs[$forum_data['id']]) and $this->fs[$forum_data['id']] == 0 )
							)
						)
						{
							// try write sub-forum if
							// 1) it's mode "show all forums" or
							// 1) it's member and "close category" property is disabled or
							// 3) it's member and "close category" property is enabled and in one of
							//    sub-forums there are new messages or
							// 4) forum of this sub forum is disabled in board layout or
							// 5) forum of this sub-forums is permited (maybe granted access to sub-forums ?)

							if ($ibforums->input['show'] or !$ibforums->member['close_category'] or
								( $ibforums->member['close_category'] and $status == "<{C_ON}>" ) or
								( isset($this->fs[$forum_data['id']]) and $this->fs[$forum_data['id']] == 0 ) or
								$can_see_root != TRUE or $forum_data['visible']
							)
							{
								// try write subforum if it's switched on in board layout or
								// there is mode "show all forums"
								// do not remove $this->fs[ $data['id'] ] == "" !!!

								if ($ibforums->input['show'] or $this->fs[$data['id']] == 1 or $this->fs[$data['id']] == "")
								{
									$showfor = 1;
								} else
								{
									$showfor = 0;
								}

								$expanded = true;

								if ($showfor)
								{
									if ($can_see_root != TRUE and !$level)
										$level--;

									$result .= $this->process_forum($idx, $data, $level + 1, 0);
								} else
									$printed_children--;
							}
						}
					}
				}

				// write forum if
				// 1) it's permited and
				// 2) it's mode "show all forums" or
				// 3) it's guest or
				// 4) it's member but forum is switched on in his board layout

				if ($can_see_root != FALSE and
					(!$ibforums->member['id'] or $ibforums->input['show'] or
					( $ibforums->member['id'] and
					( $this->fs[$forum_data['id']] == 1 or
					$this->fs[$forum_data['id']] == ""
					)
					)
					)
				)
				{
					// If we don't have permission to view any forums
					// and we can't post in this root forum
					// then simply return and the row won't be printed
					// Fix up the last of the data
					$newest['last_title'] = strip_tags($newest['last_title']);
					$newest['last_title'] = str_replace("&#33;", "!", $newest['last_title']);
					$newest['last_title'] = str_replace("&quot;", "\"", $newest['last_title']);
					$newest['last_title'] = preg_replace('/&(#(\d+;?)?)?(\.\.\.)?$/', '', $newest['last_title']);

					if ($newest['password'] != "")
					{
						$newest['last_topic'] = $ibforums->lang['f_protected'];
					} elseif ($newest['last_title'] != "")
					{
						$newest['last_unread'] = $this->html->forumrow_lastunread_link($newest['fid'], $newest['last_id']);

						$newest['last_topic'] = "<a href='{$ibforums->base_url}showtopic={$newest['last_id']}&amp;view=getnewpost'>{$newest['last_title']}</a>";
					} else
					{
						$newest['last_topic'] = $ibforums->lang['f_none'];
					}

					if (isset($newest['last_poster_name']))
					{
						$newest['last_poster'] = $newest['last_poster_id']
							? "<a href='{$ibforums->base_url}showuser={$newest['last_poster_id']}'>{$newest['last_poster_name']}</a>"
							: $newest['last_poster_name'];
					} else
					{
						$newest['last_poster'] = $ibforums->lang['f_none'];
					}

					$newest['img_new_post'] = $std->forum_new_posts($newest, ( count($this->children[$forum_data['fid']]) > 0 or $printed_children > 0 )
							? 1
							: 0, $state, $this->mods);

					if ($newest['img_new_post'] == '<{C_ON_CAT}>')
					{
						$newest['img_new_post'] = $this->html->subforum_img_with_link($newest['img_new_post'], $forum_data['id']);
					}

					$newest['last_post_std'] = date('c', $newest['last_post']);
					$newest['last_post'] = $std->get_date($newest['last_post']);
					$newest['posts'] = $std->do_number_format($newest['posts']);
					$newest['topics'] = $std->do_number_format($newest['topics']);

					foreach ($newest as $k => $v)
					{
						if ($k == 'id')
							continue;

						$forum_data[$k] = $v;
					}

					$forum_data = $this->forum_icon($forum_data);

// Shaman
// do not paint tree if mode is filter
					if ($ibforums->member['show_filter'])
					{
						$forum_data['tree'] = '  <td colspan="2" class="row4" align="center">' . $forum_data['img_new_post'] . '</td>';
					} elseif ($printed_children)
					{
						// if guest do not paint plus and minus

						$plus = ( $ibforums->member['id'] )
							? "<a href='{$ibforums->base_url}expfor={$forum_id}' style='text-decoration:none'><br><{F_PLUS}></a>"
							: "";

						$minus = ( $ibforums->member['id'] )
							? "<a href='{$ibforums->base_url}colfor={$forum_id}' style='text-decoration:none'><br><{F_MINUS}></a>"
							: "";

						$forum_data['tree'] = $expanded
							? $minus
							: $plus;

						if ($expanded)
							$style = 'row2'; else
							$style = 'row4';

						$forum_data['tree'] = '  <td valign="top"' . ($expanded
								? ' rowspan="' . ($printed_children + 1) . '" '
								: ' ') . 'class="' . $style . '">' . $forum_data['tree'] . '</td>
						<td class="row4" align="center">' . $forum_data['img_new_post'] . '</td>';

						/* removed by Лёха
						  } elseif ( 0 == $level )
						  {
						  $forum_data['tree'] = '  <td colspan="2" class="row4" align="center">'.$forum_data['img_new_post'].'</td>';
						  } else
						  {
						  $forum_data['tree'] = '  <td class="row4" align="center">'.$forum_data['img_new_post'].'</td>';
						  }
						 */
						// Collapse boards error correction:
						// added by Лёха ( http://forum.sources.ru/index.php?showtopic=218110 )
//				} elseif ( 0 == $level || (isset($this->fs[ $forum_data['parent_id'] ]) && $this->fs[ $forum_data['parent_id'] ] == 0) )
					} elseif (0 == $level || (isset($this->fs[$forum_data['parent_id']]) && $this->fs[$forum_data['parent_id']] == 0 && $ibforums->input['show'] != 'all'))
					{
						$forum_data['tree'] = '  <td colspan="2" class="row4" align="center">' . $forum_data['img_new_post'] . '</td>';
					} else
					{
						$forum_data['tree'] = '  <td class="row4" align="center">' . $forum_data['img_new_post'] . '</td>';
					}
					// end of  Лёха code


					if (count($this->children[$forum_data['id']]) AND !$expanded)
					{
						$html = "(";

						foreach ($this->children[$forum_data['id']] as $children)
						{
							if ($std->check_perms($children['read_perms']) == FALSE)
								continue;

							$style = "";

							$name = $children['name'];

							if ($ibforums->member['show_filter'])
							{
								$rtime = $children['last_post'];

								$ftime = ( $ibforums->member['board_read'] > $ibforums->forum_read[$children['id']] )
									? $ibforums->member['board_read']
									: $ibforums->forum_read[$children['id']];

								if ($ftime > $rtime)
									$style = " style='text-decoration:none'";
							}

							if ($this->mods and $children['has_mod_posts'])
								if ($ibforums->member['g_is_supmod'] or
									( $this->mods[$children['id']][$ibforums->member['id']] and
									( $this->mods[$children['id']][$ibforums->member['id']]['topic_q'] or
									$this->mods[$children['id']][$ibforums->member['id']]['post_q']
									)
									)
								)
								{
									$name = "<span class='movedprefix'>{$name}</span>";
								}

							$html .= "<a href='{$ibforums->base_url}showforum={$children['id']}'{$style}>{$name}</a> · ";
						}

						if ($html != "(")
						{
							$html = mb_substr($html, 0, mb_strlen($html) - 3);
							$html .= ")";

							if ($forum_data['description'])
								$html .= "<br>";

							$forum_data['description'] = $html . $forum_data['description'];
						}
					}

					// add drawed forum to result
					$result = $this->html->ForumRow($forum_data) . $result;
				}

				return $result;
// Shaman
			} else
				return "";
		} else
		{
			if ($ibforums->member['id'] and !$ibforums->input['show'])
			{
				if (isset($this->fs[$forum_data['id']]) and $this->fs[$forum_data['id']] == 0)
				{
					return "";
				}

				if ($level and isset($this->fs[$forum_data['parent_id']]) and $this->fs[$forum_data['parent_id']] == 0)
				{
					$level = 0;
				}
			}

			//--------------------------------------
			// Check permissions...
			//--------------------------------------

			if ($std->check_perms($forum_data['read_perms']) != TRUE)
				return "";

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

				$forum_data['redirect_hits'] = $std->do_number_format($forum_data['redirect_hits']);
// Shaman
				$forum_data['colspan'] = "";

				if (0 == $level)
					$forum_data['colspan'] = 'colspan="2" ';

				return $this->html->forum_redirect_row($forum_data);
// Shaman
			}

			//--------------------------------------
			// No - normal forum..
			//--------------------------------------

			$forum_data['img_new_post'] = $std->forum_new_posts($forum_data, "", "", $this->mods);

			if ($forum_data['img_new_post'] == '<{C_ON}>')
			{
				$forum_data['img_new_post'] = $this->html->subforum_img_with_link($forum_data['img_new_post'], $forum_data['id']);
			}

			$forum_data['last_post_std'] = date('c', $forum_data['last_post']);
			$forum_data['last_post'] = $std->get_date($forum_data['last_post']);

			$forum_data['last_topic'] = $ibforums->lang['f_none'];

			if (isset($forum_data['last_title']) and $forum_data['last_id'])
			{
				if (( $ibforums->vars['index_news_link'] == 1 ) and (!empty($ibforums->vars['news_forum_id']) ) and ($ibforums->vars['news_forum_id'] == $forum_data['id']))
				{
					$this->news_topic_id = $forum_data['last_id'];
					$this->news_forum_id = $forum_data['id'];
					$this->news_title = $forum_data['last_title'];
				}

				if (( $ibforums->vars['index_news_link'] == 1 ) and (!empty($ibforums->vars['news_forum_id2']) ) and ($ibforums->vars['news_forum_id2'] == $forum_data['id']))
				{
					$this->news_topic_id2 = $forum_data['last_id'];
					$this->news_forum_id2 = $forum_data['id'];
					$this->news_title2 = $forum_data['last_title'];
				}


				// Our poll
				if (( $ibforums->vars['index_our_poll_link'] == 1 ) and
					(!empty($ibforums->vars['our_poll_forum_id']) ) and
					($ibforums->vars['our_poll_forum_id'] == $forum_data['id']))
				{
					$this->our_poll_topic_id = $forum_data['last_id'];
					$this->our_poll_forum_id = $forum_data['id'];
					$this->our_poll_title = $forum_data['last_title'];
				} //our_poll

				$forum_data['last_title'] = strip_tags($forum_data['last_title']);
				$forum_data['last_title'] = str_replace("&#33;", "!", $forum_data['last_title']);
				$forum_data['last_title'] = str_replace("&quot;", "\"", $forum_data['last_title']);
				$forum_data['last_title'] = preg_replace("/&(#(\d+?)?)?$/", '', $forum_data['last_title']);

				if ($forum_data['password'])
				{
					$forum_data['last_topic'] = $ibforums->lang['f_protected'];
				} else
				{
					$forum_data['last_unread'] = $this->html->forumrow_lastunread_link($forum_data['id'], $forum_data['last_id']);

					$forum_data['last_topic'] = "<a href='{$ibforums->base_url}showtopic={$forum_data['last_id']}&amp;view=getnewpost'>{$forum_data['last_title']}</a>";
				}
			}

			if (isset($forum_data['last_poster_name']))
			{
				$forum_data['last_poster'] = ( $forum_data['last_poster_id'] )
					? "<a href='{$ibforums->base_url}showuser={$forum_data['last_poster_id']}'>{$forum_data['last_poster_name']}</a>"
					: $forum_data['last_poster_name'];
			} else
			{
				$forum_data['last_poster'] = $ibforums->lang['f_none'];
			}

			//---------------------------------
			// Moderators
			//---------------------------------

			$forum_data['posts'] = $std->do_number_format($forum_data['posts']);
			$forum_data['topics'] = $std->do_number_format($forum_data['topics']);

			$forum_data = $this->forum_icon($forum_data);

			$forum_data['description'] = str_replace("/r/n", "<br>", $forum_data['description']);
// Shaman
			if (0 == $level)
			{
				$forum_data['tree'] = '  <td colspan="2" class="row4" align="center">' . $forum_data['img_new_post'] . '</td>';
			} else
			{
				$forum_data['tree'] = '  <td class="row4" align="center">' . $forum_data['img_new_post'] . '</td>';
			}

			return $this->html->ForumRow($forum_data) . $result;
// Shaman
		}
	}

	/* Sunny (e-boxes@list.ru, 288-681-633): Forum Icon */

	function forum_icon($forum_data)
	{
		global $ibforums;

		if (mb_strlen($forum_data['icon']) > 4 && intval($ibforums->skin['uid']) != 13 && intval($ibforums->member['forum_icon']) == 1)
		{
			// класс для изображения
			$class = preg_match("~_OFF~is", $forum_data['img_new_post'])
				? ' class="icon_off"'
				: '';

			// создаем html
			$forum_data['img_new_post'] = '<img' . $class . ' src="' . $forum_data['icon'] . '">';
		}

		return $forum_data;
	}
	/* End */
}

