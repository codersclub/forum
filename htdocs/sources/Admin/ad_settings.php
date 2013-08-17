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
|   > Admin Setting functions
|   > Module written by Matt Mecham
|   > Date started: 20th March 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

$idx = new ad_settings();

class ad_settings
{

	var $base_url;

	function ad_settings()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		//---------------------------------------
		// Kill globals - globals bad, Homer good.
		//---------------------------------------

		$tmp_in = array_merge($_GET, $_POST, $_COOKIE);

		foreach ($tmp_in as $k => $v)
		{
			unset($$k);
		}

		//---------------------------------------

		$stmt = $ibforums->db->query("SELECT VERSION() AS version");

		if (!$row = $stmt->fetch())
		{
			$stmt = $ibforums->db->query("SHOW VARIABLES LIKE 'version'");
			$row  = $stmt->fetch();
		}

		$this->true_version = $row['version'];

		$no_array = explode('.', preg_replace("/^(.+?)[-_]?/", "\\1", $row['version']));

		$one   = (!isset($no_array) || !isset($no_array[0]))
			? 3
			: $no_array[0];
		$two   = (!isset($no_array[1]))
			? 21
			: $no_array[1];
		$three = (!isset($no_array[2]))
			? 0
			: $no_array[2];

		$this->mysql_version = (int)sprintf('%d%02d%02d', $one, $two, intval($three));

		switch ($IN['code'])
		{
			case 'fulltext':
				$this->fulltext();
				break;

			case 'dofulltext':
				$this->do_fulltext();
				break;

			case 'phpinfo':
				phpinfo();
				exit;

			case 'glines':
				$this->guidelines();
				break;
			case 'doglines':
				$this->do_guidelines();
				break;

			case 'cookie':
				$this->cookie();
				break;
			case 'docookie':
				$this->save_config(array('cookie_domain', 'cookie_id', 'cookie_path'));
				break;

			case 'warn':
				$this->warn();
				break;
			case 'dowarn':
				$_POST['warn_protected'] = ',' . @implode(",", $_POST['groups']) . ',';
				$this->save_config(array(
				                        'warn_show_rating',
				                        "warn_past_max",
				                        'warn_show_own',
				                        'warn_min',
				                        'warn_protected',
				                        'warn_mod_day',
				                        'warn_gmod_day',
				                        "warn_gmod_ban",
				                        "warn_gmod_modq",
				                        "warn_gmod_post",
				                        "warn_mod_post",
				                        "warn_mod_modq",
				                        "warn_mod_ban",
				                        'warn_max',
				                        "warn_on"
				                   ));
				break;

			case 'secure':
				$this->secure();
				break;
			case 'dosecure':
				$this->save_config(array(
				                        'strip_space_chr',
				                        'validate_day_prune',
				                        'bot_antispam',
				                        'use_ttf',
				                        'gd_width',
				                        'gd_height',
				                        "gd_font",
				                        'disable_admin_anon',
				                        'disable_online_ip',
				                        'disable_reportpost',
				                        'allow_dynamic_img',
				                        'session_expiration',
				                        'match_browser',
				                        'allow_dup_email',
				                        'allow_images',
				                        'force_login',
				                        'no_reg',
				                        'allow_flash',
				                        'new_reg_notify',
				                        'use_mail_form',
				                        'flood_control',
				                        'allow_online_list',
				                        'reg_auth_type'
				                   ));
				break;
			//-------------------------
			case 'post':
				$this->post();
				break;
			case 'dopost':

				//-- mod_global_poll begin
				if (file_exists(ROOT_PATH . "sources/mods/global_poll/mod_global_poll_adm.php"))
				{
					require ROOT_PATH . "sources/mods/global_poll/mod_global_poll_adm.php";
				} else
				{
					die("Could not call required function from file 'sources/mods/global_poll/mod_global_poll_adm.php'<br>Does it exist?");
				}

				$adminGlobalPoll = new AdminGlobalPoll;
				$adminGlobalPoll->save_config();
				//-- mod_global_poll end

				$this->save_config(array(
				                        'poll_disable_noreply',
				                        'siu_thumb',
				                        'siu_width',
				                        'siu_height',
				                        'startpoll_cutoff',
				                        'post_wordwrap',
				                        'allow_result_view',
				                        'max_poll_choices',
				                        'poll_tags',
				                        'guest_name_pre',
				                        'guest_name_suf',
				                        'max_w_flash',
				                        'max_h_flash',
				                        'hot_topic',
				                        'display_max_topics',
				                        'display_max_posts',
				                        'max_emos',
				                        'max_images',
				                        'emo_per_row',
				                        'etfilter_punct',
				                        'etfilter_shout',
				                        'strip_quotes',
				                        'max_post_length',
				                        'show_img_upload',
				                        'pre_polls',
				                        'pre_moved',
				                        'pre_pinned',
				                        'pre_club',
				                        'img_ext'
				                   ));
				break;
			//-------------------------
			case 'avatars':
				$this->avatars();
				break;
			case 'doavatars':
				$this->save_config(array(
				                        'av_gal_cols',
				                        "disable_ipbsize",
				                        "photo_ext",
				                        'subs_autoprune',
				                        'topicpage_contents',
				                        'postpage_contents',
				                        'allow_skins',
				                        'max_sig_length',
				                        'sig_allow_ibc',
				                        'sig_allow_html',
				                        'avatar_ext',
				                        'avatar_url',
				                        'avup_size_max',
				                        'avatars_on',
				                        'avatar_dims',
				                        'avatar_def',
				                        'max_location_length',
				                        'max_interest_length',
				                        'post_titlechange',
				                        'guests_ava',
				                        'guests_img',
				                        'guests_sig'
				                   ));
				break;
			//-------------------------
			case 'dates':
				$this->dates();
				break;
			case 'dodates':
				$this->save_config(array('time_offset', 'clock_short', 'clock_joined', 'clock_long', 'time_adjust'));
				break;
			//-------------------------

			case 'calendar':
				$this->calendar();
				break;
			case 'docalendar':
				$this->save_config(array(
				                        'autohide_bday',
				                        'autohide_calendar',
				                        'show_birthdays',
				                        'show_bday_calendar',
				                        'show_calendar',
				                        'calendar_limit',
				                        'year_limit',
				                        'start_year'
				                   ));
				break;
			//-------------------------

			case 'cpu':
				$this->cpu();
				break;
			case 'docpu':
				$this->save_config(array(
				                        'custom_profile_topic',
				                        'min_search_word',
				                        'short_forum_jump',
				                        'no_au_forum',
				                        'no_au_topic',
				                        'au_cutoff',
				                        'load_limit',
				                        'show_active',
				                        'show_birthdays',
				                        'show_totals',
				                        'allow_search',
				                        'search_post_cut',
				                        'show_user_posted',
				                        'nocache'
				                   ));
				break;
			//-------------------------
			case 'email':
				$this->email();
				break;
			case 'doemail':
				$this->save_config(array(
				                        'email_in',
				                        'email_out',
				                        'mail_method',
				                        'smtp_host',
				                        'smtp_port',
				                        'smtp_user',
				                        'smtp_pass'
				                   ));
				break;
			//-------------------------

			case 'url':
				$this->url();
				break;

			case 'dourl':
				$this->save_config(array(
				                        'board_name',
				                        'board_url',
				                        'home_name',
				                        'home_url',
				                        'html_url',
				                        'upload_url',
				                        'html_dir',
				                        'upload_dir',
				                        'print_headers',
				                        'session_hide',
				                        'disable_gzip',
				                        'header_redirect',
				                        'debug_level',
				                        'client_script_version',
				                        'sql_debug',
				                        'auto_pm_on',
				                        'auto_pm_from',
				                        'auto_pm_subject',
				                        'auto_pm_message',
				                        'safe_mode_skins',
				                        'number_format'
				                   ));
				break;

			//-------------------------
			case 'pm':
				$this->pm();
				break;
			case 'dopm':
				$this->save_config(array('show_max_msg_list', 'msg_allow_code', 'msg_allow_html'));
				break;
			//-------------------------
			case 'news':
				$this->news();
				break;
			case 'donews':
				$this->save_config(array('news_forum_id', 'index_news_link'));
				break;
			//-------------------------
			case 'coppa':
				$this->coppa();
				break;
			case 'docoppa':
				$this->save_config(array('use_coppa', 'coppa_fax', 'coppa_address'));
				break;
			//-------------------------
			case 'board':
				$this->board();
				break;
			case 'doboard':
				$this->save_config(array('board_offline', 'offline_msg'));
				break;
			//-----------------------
			case 'message':
				$this->message();
				break;
			case 'domessage':
				$this->save_config(array('global_message_on', 'global_message'));
				break;
			//-------------------------
			case 'spider':
				$this->spider();
				break;
			case 'dospider':
				$this->save_config(array(
				                        'spider_suit',
				                        "spider_sense",
				                        "spider_visit",
				                        "spider_group",
				                        "spider_active",
				                        'sp_google',
				                        'sp_inktomi',
				                        'sp_lycos',
				                        'sp_jeeves',
				                        'sp_wuseek',
				                        'spider_anon'
				                   ));
				break;
			//-------------------------
			case 'bw':
				$this->badword();
				break;
			case 'bw_add':
				$this->add_badword();
				break;
			case 'bw_remove':
				$this->remove_badword();
				break;
			case 'bw_edit':
				$this->edit_badword();
				break;
			case 'bw_doedit':
				$this->doedit_badword();
				break;
			case 'syntax':
				$this->color_syntax();
				break;
			case 'syntax_add':
				$this->syntax_add();
				break;
			case 'syntax_remove':
				$this->syntax_remove();
				break;
			case 'syntax_edit':
				$this->syntax_edit();
				break;
			case 'syntax_doedit':
				$this->syntax_doedit();
				break;
			case 'syntax_forum_apply':
				$this->syntax_forum_apply();
				break;
			//-------------------------
			case 'emo':
				$this->emoticons();
				break;
			case 'emo_add':
				$this->add_emoticons();
				break;
			case 'emo_remove':
				$this->remove_emoticons();
				break;
			case 'emo_edit':
				$this->edit_emoticons();
				break;
			case 'emo_doedit':
				$this->doedit_emoticons();
				break;
			case 'emo_upload':
				$this->upload_emoticon();
				break;
			//-------------------------
			case 'count':
				$this->countstats();
				break;
			case 'docount':
				$this->docount();
				break;
			// Song

			//Reputation
			case 'rep':
				$this->reput();
				break;
			case 'dorep':
				if ($IN['rep_rcall'])
				{
					$this->reput_recount_all();
				}
				$this->save_config(array(
				                        'rep_remove',
				                        'rep_time',
				                        'rep_posts',
				                        'rep_allow_anon',
				                        'rep_anon_posts',
				                        'rep_msg_length',
				                        'rep_enable_emo',
				                        'rep_enable_ibc',
				                        'rep_good_anon',
				                        'rep_bad_anon',
				                        'rep_goodtitle',
				                        'rep_goodnum',
				                        'rep_badtitle',
				                        'rep_badnum',
				                        'rep_titlechange',
				                        'rep_per_page'
				                   ));
				break;
			//Reputation

			// Song

			default:
				$this->cookie();
				break;
		}

	}

	//-------------------------------------------------------------
	// Full Text options page
	//--------------------------------------------------------------

	function fulltext()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		//---------------------------------------
		// Get the mySQL version.
		// Adapted from phpMyAdmin
		//---------------------------------------

		$stmt = $ibforums->db->query("SELECT VERSION() AS version");

		if (!$row = $stmt->fetch())
		{
			$stmt = $ibforums->db->query("SHOW VARIABLES LIKE 'version'");
			$row  = $stmt->fetch();
		}

		$this->true_version = $row['version'];

		$no_array = explode('.', preg_replace("/^(.+?)[-_]?/", "\\1", $row['version']));

		$one   = (!isset($no_array) || !isset($no_array[0]))
			? 3
			: $no_array[0];
		$two   = (!isset($no_array[1]))
			? 21
			: $no_array[1];
		$three = (!isset($no_array[2]))
			? 0
			: $no_array[2];

		$this->mysql_version = (int)sprintf('%d%02d%02d', $one, $two, intval($three));

		$this->common_header('dofulltext', 'Full Text Searching Set Up', 'You may change the configuration below');

		if ($this->mysql_version < 32323)
		{
			$ADMIN->html .= $SKIN->add_td_basic("<strong>Sorry, your MySQL installation is not capable of utilizing full text searching</strong><br />Contact your webhost to see about a MySQL upgrade.");

			$ADMIN->html .= $SKIN->end_form($button);

			$ADMIN->html .= $SKIN->end_table();

			$ADMIN->output();

			exit();
		} else
		{
			$ADMIN->html .= $SKIN->add_td_basic("<strong>What is full text searching?</strong><br />Full text searching is a very fast and very
												efficient way of searching large amounts of posts without maintaining a manual index.");

			//-------------------------------------------
			// Do we already have full text enabled?
			//-------------------------------------------

			$stmt = $ibforums->db->query("SHOW CREATE TABLE ibf_posts");

			$tbl_info = $stmt->fetch();

			if (preg_match("/FULLTEXT KEY/i", $tbl_info['Create Table']))
			{
				$ADMIN->html .= $SKIN->add_td_basic("<b>The Full text indexes already exist</b><input type='hidden' name='ftexist' value='1' />");

				$ADMIN->html .= $SKIN->add_td_row(array(
				                                       "<b>Type of search to use?</b>",
				                                       $SKIN->form_dropdown("search_sql_method", array(
				                                                                                      0 => array(
					                                                                                      'ftext',
					                                                                                      'Full Text'
				                                                                                      ),
				                                                                                      1 => array(
					                                                                                      'man',
					                                                                                      'Manual'
				                                                                                      ),
				                                                                                      2 => array(
					                                                                                      'index',
					                                                                                      'Indexed'
				                                                                                      )
				                                                                                 ), $INFO['search_sql_method'])
				                                  ));

				$ADMIN->html .= $SKIN->add_td_row(array(
				                                       "<b>Default Search Mode?</b><br>Will only take effect if using full text searching",
				                                       $SKIN->form_dropdown("search_default_method", array(
				                                                                                          0 => array(
					                                                                                          'simple',
					                                                                                          'Simple Search'
				                                                                                          ),
				                                                                                          1 => array(
					                                                                                          'adv',
					                                                                                          'Advanced'
				                                                                                          )
				                                                                                     ), $INFO['search_default_method'])
				                                  ));
			} else
			{
				$ADMIN->html .= $SKIN->add_td_basic("<b>You must create the full text indexes before you can set this section up. Simply submit this form to start setting up the indexes</b>" . "<input type='hidden' name='setup' value='1'>");
			}

			$this->common_footer();
		}
	}

	//-------------------------------------------------------------
	// Save full text options
	//--------------------------------------------------------------

	function do_fulltext()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		if ($IN['ftexist'] == 1)
		{
			$master                          = array();
			$master['search_sql_method']     = $IN['search_sql_method'];
			$master['search_default_method'] = $IN['search_default_method'];

			$ADMIN->rebuild_config($master);
		} else
		{
			// They don't.
			// Check for correct version and if need be, attempt to create the indexes...

			if ($this->mysql_version >= 32323)
			{
				// How many posts do we have?

				$stmt = $ibforums->db->query("SELECT COUNT(*) as cnt from ibf_posts");

				$result = $stmt->fetch();

				// If over 15,000 posts...

				if ($result['cnt'] > 15000)
				{
					// Explain how, why and what to do..

					$ADMIN->page_detail = "";
					$ADMIN->page_title  = "Unable to continue";

					$ADMIN->html .= $SKIN->add_td_basic($this->return_sql_no_no_cant_do_it_sorry_text(), 'left', 'faker');

					$ADMIN->output();
				} else
				{
					// Index away!

					$stmt = $ibforums->db->query("alter table ibf_topics add fulltext(title)");

					$stmt = $ibforums->db->query("alter table ibf_posts add fulltext(post)");

				}
			} else
			{
				$ADMIN->error("Sorry, the version of MySQL that you are using is unable to use FULLTEXT searches");
			}
		}

		$ADMIN->save_log("Full Text Options Updated");

		$ADMIN->done_screen("Full Text Settings updated", "Full Text Set Up", "act=op&code=fulltext");

	}

	//-------------------------------------------------------------
	// WARNY PORNY!
	//--------------------------------------------------------------

	function warn()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$this->common_header('dowarn', 'Member Warning Set-Up', 'You may change the configuration below.');

		$stmt = $ibforums->db->query("SELECT g_id, g_title FROM ibf_groups ORDER BY g_title");

		while ($r = $stmt->fetch())
		{
			$mem_group[] = array($r['g_id'], $r['g_title']);
		}

		$protected = explode(',', trim($INFO['warn_protected']));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Enable the warning system?</b>",
		                                       $SKIN->form_yes_no("warn_on", $INFO['warn_on'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Warning level...</b><br />This is related to the visual indicator in the member's profile.",
		                                       'Minimum ' . $SKIN->form_simple_input('warn_min', $INFO['warn_min'] == ""
			                                       ? 0
			                                       : $INFO['warn_min']) . ' to ' . 'Maximum ' . $SKIN->form_simple_input('warn_max', $INFO['warn_max'] == ""
			                                       ? 10
			                                       : $INFO['warn_max']) . "<br>Minus numbers allowed. If using minus numbers, we recommend that you do not use the graphical blocks and percentage but use the 'rating' mode as 'warn mode' will not take into account a minus start number.<br>Members always start with a zero warn level when registered."
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Protected Groups...</b><br>Groups that cannot be warned<br />You may choose more than one",
		                                       $SKIN->form_multiselect("groups[]", $mem_group, $protected)
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow all other non-protected groups to see <em>their own</em> warn level and reasons?</b>",
		                                       $SKIN->form_yes_no("warn_show_own", $INFO['warn_show_own'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow warnings to continue once the minimum or maximum has been reached?</b>",
		                                       $SKIN->form_yes_no("warn_past_max", $INFO['warn_past_max'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Type of visual indicator?</b>",
		                                       $SKIN->form_dropdown('warn_show_rating', array(
		                                                                                     0 => array(
			                                                                                     0,
			                                                                                     'Warn mode: Show graphical blocks & percentage'
		                                                                                     ),
		                                                                                     1 => array(
			                                                                                     1,
			                                                                                     'Rating mode: Show < min | current | max > level'
		                                                                                     )
		                                                                                ), $INFO['warn_show_rating'])
		                                  ));

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_basic('Forum Moderator Permissions', 'left', 'catrow2');

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Moderators can ban via warn panel?</b><br />Applies to those moderators allowed to use the warning system<br />Admins can automatically do this via the warn panel",
		                                       $SKIN->form_yes_no("warn_mod_ban", $INFO['warn_mod_ban'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Moderators can mod queue members via warn panel?</b><br />Applies to those moderators allowed to use the warning system<br />Admins can automatically do this via the warn panel",
		                                       $SKIN->form_yes_no("warn_mod_modq", $INFO['warn_mod_modq'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Moderators can remove post rights via warn panel?</b><br />Applies to those moderators allowed to use the warning system<br />Admins can automatically do this via the warn panel",
		                                       $SKIN->form_yes_no("warn_mod_post", $INFO['warn_mod_post'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Moderators can warn a member...</b>",
		                                       $SKIN->form_input('warn_mod_day', $INFO['warn_mod_day'] == ""
			                                       ? 1
			                                       : $INFO['warn_mod_day']) . '... times a day'
		                                  ));

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_basic('Global Moderator Permissions', 'left', 'catrow2');

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Global Moderators can ban via warn panel?</b><br />Admins can automatically do this via the warn panel",
		                                       $SKIN->form_yes_no("warn_gmod_ban", $INFO['warn_gmod_ban'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Global Moderators can mod queue members via warn panel?</b><br />Admins can automatically do this via the warn panel",
		                                       $SKIN->form_yes_no("warn_gmod_modq", $INFO['warn_gmod_modq'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Global Moderators can remove post rights via warn panel?</b><br />Admins can automatically do this via the warn panel",
		                                       $SKIN->form_yes_no("warn_gmod_post", $INFO['warn_gmod_post'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Global Moderators can warn a member...</b>",
		                                       $SKIN->form_input('warn_gmod_day', $INFO['warn_gmod_day'] == ""
			                                       ? 1
			                                       : $INFO['warn_gmod_day']) . '... times a day'
		                                  ));

		$this->common_footer();

	}

	//-------------------------------------------------------------
	// SPIDER MAN! CHECK OUT THOSE CRAZY PANTS!
	//--------------------------------------------------------------

	function spider()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$this->common_header('dospider', 'Search Engine Spider/Crawler Set-Up', 'You may change the configuration below.<br />' . $SKIN->js_help_link('set_spider'));

		$stmt = $ibforums->db->query("SELECT g_id, g_title FROM ibf_groups ORDER BY g_title");

		$mem_group = array();

		while ($r = $stmt->fetch())
		{
			if ($INFO['admin_group'] == $r['g_id'])
			{
				if ($MEMBER['mgroup'] != $INFO['admin_group'])
				{
					continue;
				}
			}

			$mem_group[] = array($r['g_id'], $r['g_title']);
		}

		$stmt = $ibforums->db->query("SELECT sname, sid FROM ibf_skins ORDER BY sname");

		$skin_sets = array(0 => array('', "Use default skin"));

		while ($s = $stmt->fetch())
		{
			$skin_sets[] = array($s['sid'], $s['sname']);
		}

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Enable the search engine spider recognition?</b>",
		                                       $SKIN->form_yes_no("spider_sense", $INFO['spider_sense'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Log all spider visits?</b><br />If you're under heavy attack, switch this off!",
		                                       $SKIN->form_yes_no("spider_visit", $INFO['spider_visit'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Treat spider/bot as part of which group?</b>",
		                                       $SKIN->form_dropdown("spider_group", $mem_group, $INFO['spider_group'] == ""
			                                       ? $INFO['guest_group']
			                                       : $INFO['spider_group'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Force spider/bot to use skin:</b>",
		                                       $SKIN->form_dropdown("spider_suit", $skin_sets, $INFO['spider_suit'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Show spider/bot in the active users list?</b>",
		                                       $SKIN->form_yes_no("spider_active", $INFO['spider_active']) . "<br />" . $SKIN->form_checkbox('spider_anon', $INFO['spider_anon']) . " Show as anonymous ( only root admins can see)"
		                                  ));
		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_basic('In the active users list...', 'left', 'catrow2');

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Call Googlebot...</b>",
		                                       $SKIN->form_input('sp_google', $INFO['sp_google'] == ""
			                                       ? 'GoogleBot'
			                                       : $INFO['sp_google'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Call Microsoft / Hotbot...</b>",
		                                       $SKIN->form_input('sp_inktomi', $INFO['sp_inktomi'] == ""
			                                       ? 'Hot Bot'
			                                       : $INFO['sp_inktomi'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Call Lycos...</b>",
		                                       $SKIN->form_input('sp_lycos', $INFO['sp_lycos'] == ""
			                                       ? 'Lycos'
			                                       : $INFO['sp_lycos'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Call Ask Jeeves...</b>",
		                                       $SKIN->form_input('sp_jeeves', $INFO['sp_jeeves'] == ""
			                                       ? 'Ask Jeeves'
			                                       : $INFO['sp_jeeves'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Call What U Seek...</b>",
		                                       $SKIN->form_input('sp_wuseek', $INFO['sp_wuseek'] == ""
			                                       ? 'What U Seek'
			                                       : $INFO['sp_wuseek'])
		                                  ));

		$this->common_footer();

	}

	//-------------------------------------------------------------
	// Board Guidelines
	//--------------------------------------------------------------

	function do_guidelines()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$master             = array();
		$master['gl_show']  = $IN['gl_show'];
		$master['gl_link']  = $IN['gl_link'];
		$master['gl_title'] = $IN['gl_title'];

		$ADMIN->rebuild_config($master);

		$glines = stripslashes($_POST['gl_guidelines']);
		$glines = str_replace("<br>", "<br />", $glines);

		$stmt = $ibforums->db->query("SELECT * FROM ibf_cache_store WHERE cs_key='boardrules'");

		if ($row = $stmt->fetch())
		{
			$ibforums->db->exec("UPDATE ibf_cache_store SET cs_value='" . addslashes($glines) . "' WHERE cs_key='boardrules'");
		} else
		{
			$data = [
				'cs_key'   => 'boardrules',
				'cs_value' => $glines,
			];

			$ibforums->db->insertRow("ibf_cache_store", $data);
		}

		$ADMIN->save_log("Board Guidelines Updated");

		$ADMIN->done_screen("Forum Configurations updated", "Administration CP Home", "act=index");

	}

	//---------------------------------------------

	function guidelines()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$this->common_header('doglines', 'Board Guidelines/Rules', 'You may change the configuration below');

		$stmt = $ibforums->db->query("SELECT * FROM ibf_cache_store WHERE cs_key='boardrules'");

		$row = $stmt->fetch();

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Show link in header to guidelines?</b>",
		                                       $SKIN->form_yes_no("gl_show", $INFO['gl_show'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>External http:// link to guidlines page?</b><br>Leave blank to use internal page",
		                                       $SKIN->form_input("gl_link", $INFO['gl_link'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Title to use in header?</b>",
		                                       $SKIN->form_input("gl_title", $INFO['gl_title'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>If not using external link; guidelines/rules text</b><br>HTML Enabled",
		                                       $SKIN->form_textarea("gl_guidelines", $std->my_br2nl($row['cs_value']), 65, 20)
		                                  ));

		$this->common_footer();

	}

	//-------------------------------------------------------------
	// COPPA
	//--------------------------------------------------------------

	function coppa()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$this->common_header('docoppa', 'COPPA Set-Up', 'You may change the configuration below. Note, enabling <a href="http://www.ftc.gov/ogc/coppa1.htm" target="_blank">COPPA</a> on your board will require children under the age of 13 to get parental consent via a faxed or mailed form.');

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Use COPPA registration system?</b>",
		                                       $SKIN->form_yes_no("use_coppa", $INFO['use_coppa'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Fax number to receive COPPA forms</b>",
		                                       $SKIN->form_input("coppa_fax", $INFO['coppa_fax'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Mail address to receive COPPA forms</b>",
		                                       $SKIN->form_textarea("coppa_address", str_replace("\n\n", "\n", $std->my_br2nl(str_replace("\r\n", "\n", $INFO['coppa_address']))))
		                                  ));

		$this->common_footer();

	}

	//=====================================================

	function docount()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		if ((!$IN['posts']) and (!$IN['members']) and (!$IN['lastreg']))
		{
			$ADMIN->error("Nothing to recount!");
		}

		$stats = array();

		if ($IN['posts'])
		{
			$stmt                   = $ibforums->db->query("SELECT COUNT(pid) as posts FROM ibf_posts WHERE queued <> 1");
			$r                      = $stmt->fetch();
			$stats['TOTAL_REPLIES'] = $r['posts'];
			$stats['TOTAL_REPLIES'] = $stats['TOTAL_REPLIES'] < 1
				? 0
				: $stats['TOTAL_REPLIES'];

			$stmt                  = $ibforums->db->query("SELECT COUNT(tid) as topics FROM ibf_topics WHERE approved = 1");
			$r                     = $stmt->fetch();
			$stats['TOTAL_TOPICS'] = $r['topics'];
			$stats['TOTAL_TOPICS'] = $stats['TOTAL_TOPICS'] < 1
				? 0
				: $stats['TOTAL_TOPICS'];

			$stats['TOTAL_REPLIES'] -= $stats['TOTAL_TOPICS'];
		}

		if ($IN['members'])
		{
			$stmt               = $ibforums->db->query("SELECT COUNT(id) as members from ibf_members WHERE mgroup <> '" . $INFO['auth_group'] . "'");
			$r                  = $stmt->fetch();
			$stats['MEM_COUNT'] = $r['members'];
			// Remove "guest" account...
			$stats['MEM_COUNT']--;
			$stats['MEM_COUNT'] = $stats['MEM_COUNT'] < 1
				? 0
				: $stats['MEM_COUNT'];
		}

		if ($IN['lastreg'])
		{
			$stmt                   = $ibforums->db->query("SELECT id, name FROM ibf_members WHERE mgroup <> '" . $INFO['auth_group'] . "' ORDER BY id DESC LIMIT 0,1");
			$r                      = $stmt->fetch();
			$stats['LAST_MEM_NAME'] = $r['name'];
			$stats['LAST_MEM_ID']   = $r['id'];
		}

		if ($IN['online'])
		{
			$stats['MOST_DATE']  = time();
			$stats['MOST_COUNT'] = 1;
		}

		if (count($stats) > 0)
		{
			$ibforums->db->updateRow("ibf_stats", array_map([$ibforums->db, 'quote'], $stats));
		} else
		{
			$ADMIN->error("Nothing to recount!");
		}

		$ADMIN->done_screen("Statistics Recounted", "Administration CP Home", "act=index");

	}

	function countstats()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$ADMIN->page_detail = "Please choose which statistics to recount.";
		$ADMIN->page_title  = "Recount Statistics Control";

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'docount'),
		                                       2 => array('act', 'op'),
		                                  ));

		//+-------------------------------

		$SKIN->td_header[] = array("Statistic", "70%");
		$SKIN->td_header[] = array("Option", "30%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Recount Statistics");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "Recount total topics and posts",
		                                       $SKIN->form_dropdown('posts', array(
		                                                                          0 => array(1, 'Yes'),
		                                                                          1 => array(0, 'No')
		                                                                     ))
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "Recount Members",
		                                       $SKIN->form_dropdown('members', array(
		                                                                            0 => array(1, 'Yes'),
		                                                                            1 => array(0, 'No')
		                                                                       ))
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "Reset last registered member",
		                                       $SKIN->form_dropdown('lastreg', array(
		                                                                            0 => array(1, 'Yes'),
		                                                                            1 => array(0, 'No')
		                                                                       ))
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "Reset 'Most online' statistic?",
		                                       $SKIN->form_dropdown('online', array(
		                                                                           0 => array(0, 'No'),
		                                                                           1 => array(1, 'Yes')
		                                                                      ))
		                                  ));

		$ADMIN->html .= $SKIN->end_form('Reset these statistics');

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	//-------------------------------------------------------------
	// CALENDAR
	//--------------------------------------------------------------

	function calendar()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$this->common_header('docalendar', 'Calendar Set Up', 'You may change the configuration below');

		$INFO['start_year'] = (isset($INFO['start_year']))
			? $INFO['start_year']
			: 2001;
		$INFO['year_limit'] = (isset($INFO['year_limit']))
			? $INFO['year_limit']
			: 5;

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Show birthdays on the main calendar view?</b>",
		                                       $SKIN->form_yes_no("show_bday_calendar", $INFO['show_bday_calendar'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Show Today's birthdays on the board view?</b>",
		                                       $SKIN->form_yes_no("show_birthdays", $INFO['show_birthdays']) . "<br />" . $SKIN->form_checkbox("autohide_bday", $INFO["autohide_bday"]) . " Auto hide when none to show?"
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Show forthcoming events?</b><br>This will show calendar events on the board index page in the stats section.",
		                                       $SKIN->form_yes_no("show_calendar", $INFO['show_calendar']) . "<br />" . $SKIN->form_checkbox("autohide_calendar", $INFO["autohide_calendar"]) . " Auto hide when none to show?"
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Show forthcoming events from today to [x] days ahead</b><br>This applies to the above option.",
		                                       $SKIN->form_input("calendar_limit", $INFO['calendar_limit'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Starting year for calendar 'Year' drop down box</b><br>This applies to view calendar / post event.",
		                                       $SKIN->form_input("start_year", $INFO['start_year'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Year end limit for 'Year' drop down box</b><br>This applies to view calendar / post event.<br>Example: current year is 2002, you enter 5 - last choosable year = 2007",
		                                       $SKIN->form_input("year_limit", $INFO['year_limit'])
		                                  ));

		$this->common_footer();

	}

	//-------------------------------------------------------------
	// URLs and ADDRESSES
	//--------------------------------------------------------------

	function board()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$this->common_header('doboard', 'Board offline/online', 'You may change the configuration below');

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Turn the board offline?</b><br>The board will still be accessable by those who have permission",
		                                       $SKIN->form_yes_no("board_offline", $INFO['board_offline'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>The offline message to display</b>",
		                                       $SKIN->form_textarea("offline_msg", $INFO['offline_msg'])
		                                  ));

		$this->common_footer();

	}

	//-------------------------------------------------------------
	// Whats the message you want to display? I like cheese! lol
	//--------------------------------------------------------------

	function message()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$this->common_header('domessage', 'Board Message', 'You may change the configuration below. HTML is enabled, and BBCode will be enabled in later versions.');

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Turn the message system off?</b>",
		                                       $SKIN->form_yes_no("global_message_on", $INFO['global_message_on'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>The message to display</b>",
		                                       $SKIN->form_textarea("global_message", $INFO['global_message'])
		                                  ));

		$this->common_footer();

	}

	// Song

	//-------------------------------------------------------------
	// REPUTATION FUNCTIONS
	//-------------------------------------------------------------

	function reput()
	{
		global $IN, $root_path, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();
		$this->common_header('dorep', 'Reputation Settings', 'You may change your reputation settings below');

		$ADMIN->html .= $SKIN->add_td_basic('General Settings', 'left', 'catrow2');

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Number of posts required to use the Reputation system?</b>",
		                                       $SKIN->form_input("rep_posts", $INFO['rep_posts'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>How many days the member can't change the same member's Reputation again?</b>",
		                                       $SKIN->form_input("rep_time", $INFO['rep_time'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Remove posting rights if Reputation below [X]?</b><br>Leave blank for no effect",
		                                       $SKIN->form_input("rep_remove", $INFO['rep_remove'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_basic('Fine-tuning the Look of Reputation :)', 'left', 'catrow2');

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Show [X] Reputation changes per page in stats</b><br>Auto-set to '30' if blank",
		                                       $SKIN->form_input("rep_per_page", $INFO['rep_per_page'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Maximum length for 'Reason' field when changing Reputation? (bytes)</b><br>Blank or 0 for no effect",
		                                       $SKIN->form_input("rep_msg_length", $INFO['rep_msg_length'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow the use of emoticons when changing Reputation?</b>",
		                                       $SKIN->form_yes_no("rep_enable_emo", $INFO['rep_enable_emo'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow the use of IBF CODE when changing Reputation?</b>",
		                                       $SKIN->form_yes_no("rep_enable_ibc", $INFO['rep_enable_ibc'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_basic('Anonymous Voting', 'left', 'catrow2');

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow anonymous voting?</b>",
		                                       $SKIN->form_yes_no("rep_allow_anon", $INFO['rep_allow_anon'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Only allow anonymous voting to members with more than [X] posts?</b>",
		                                       $SKIN->form_input("rep_anon_posts", $INFO['rep_anon_posts'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Name for Anonymous when raising Reputation</b><br>When a member raised someone's rep anonymously, he is represented in the rep change stats with this name",
		                                       $SKIN->form_input("rep_good_anon", $INFO['rep_good_anon'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Name for Anonymous when lowering Reputation</b><br>When a member lowered someone's rep anonymously, he is represented in the rep change stats with this name<br>E.g., you can use 'Coward' or 'Windbag' :)",
		                                       $SKIN->form_input("rep_bad_anon", $INFO['rep_bad_anon'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_basic('Member Titles', 'left', 'catrow2');

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Positive prefix to member's title</b><br>E.g., setting this to 'Good' means: when some Newbie reaches a certain amount of rep points (see next option), he becomes a Good Newbie",
		                                       $SKIN->form_input("rep_goodtitle", $INFO['rep_goodtitle'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Minimum number of rep points letting member have a 'positive' prefix to their title</b><br>Blank or 0 if you don't want to allow this",
		                                       $SKIN->form_input("rep_goodnum", $INFO['rep_goodnum'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Negative prefix to member's title</b><br>E.g., setting this to 'Bad' means: when some Newbie falls below some rep points (see next option), he becomes a Bad Newbie",
		                                       $SKIN->form_input("rep_badtitle", $INFO['rep_badtitle'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Minimum number of rep points (usually negative) letting members have a 'negative' prefix to their title</b><br>Don't set this higher than the positive one - you can get something like Bad Good Newbie :)<br>Blank or 0 if you don't want to allow this",
		                                       $SKIN->form_input("rep_badnum", $INFO['rep_badnum'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Amount of Reputation a member must have before allowing them to change their member title</b><br>Blank or 0 to disable completely",
		                                       $SKIN->form_input("rep_titlechange", $INFO['rep_titlechange'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_basic('Misc', 'left', 'catrow2');

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Recount all members' Reputation?</b><br>Resynchronise all Reputation data",
		                                       $SKIN->form_yes_no("rep_rcall", 0)
		                                  ));

		$this->common_footer();
	}

	function reput_recount_all()
	{
		global $std;
		$ibforums = Ibf::app();

		$stmt = $ibforums->db->query("SELECT id FROM ibf_members");

		while ($user = $stmt->fetch())
		{
			$std->rep_recount($user['id']);
		}

	}

	//=====================================================

	// Song

	//-------------------------------------------------------------
	// EMOTICON FUNCTIONS
	//-------------------------------------------------------------

	function doedit_emoticons()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		if ($IN['before'] == "")
		{
			$ADMIN->error("You must enter text to replace, silly!");
		}

		if ($IN['id'] == "")
		{
			$ADMIN->error("You must pass a valid emoticon id, silly!");
		}

		if (strstr($IN['before'], '&#092;'))
		{
			$ADMIN->error("You cannot use the backslash character in \"{$IN['before']}\". Please use another character");
		}

		$IN['clickable'] = $IN['clickable']
			? 1
			: 0;

		$data = [
			'typed'     => $IN['before'],
			'image'     => $IN['after'],
			'clickable' => $IN['click'],
		];

		$ibforums->db->updateRow("ibf_emoticons", array_map([$ibforums->db, 'quote'], $data), "id='" . $IN['id'] . "'");

		$std->boink_it($SKIN->base_url . "&act=op&code=emo");
		exit();

	}

	//=====================================================

	function edit_emoticons()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$ADMIN->page_detail = "You may edit the emoticon filter below";
		$ADMIN->page_title  = "Edit Emoticon";

		//+-------------------------------

		if ($IN['id'] == "")
		{
			$ADMIN->error("You must pass a valid filter id, silly!");
		}

		//+-------------------------------

		$stmt = $ibforums->db->query("SELECT * FROM ibf_emoticons WHERE id='" . $IN['id'] . "'");

		if (!$r = $stmt->fetch())
		{
			$ADMIN->error("We could not find that emoticon in the database");
		}

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'emo_doedit'),
		                                       2 => array('act', 'op'),
		                                       3 => array('id', $IN['id']),
		                                  ));

		$SKIN->td_header[] = array("Before", "40%");
		$SKIN->td_header[] = array("After", "40%");
		$SKIN->td_header[] = array("+ Clickable", "20%");

		//+-------------------------------

		$emos = array();

		if (!is_dir($INFO['html_dir'] . 'emoticons'))
		{
			$ADMIN->error("Could not locate the emoticons directory - make sure the 'html_dir' path is set correctly");
		}

		//+-------------------------------

		$dh = opendir($INFO['html_dir'] . 'emoticons') or die("Could not open the emoticons directory for reading, check paths and permissions");
		while ($file = readdir($dh))
		{
			if (!preg_match("/^..?$|^index|htm$|html$|^\./i", $file))
			{
				$emos[] = array($file, $file);
			}
		}
		closedir($dh);

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Edit an Emoticon");

		$ADMIN->html .= "<script language='javascript'>
						 <!--
						 	function show_emo() {

						 		var emo_url = '{$INFO['html_url']}/emoticons/' + document.theAdminForm.after.options[document.theAdminForm.after.selectedIndex].value;

						 		document.images.emopreview.src = emo_url;
							}
						//-->
						</script>
						";

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       $SKIN->form_input('before', stripslashes($r['typed'])),
		                                       $SKIN->form_dropdown('after', $emos, $r['image'], "onChange='show_emo()'") . "&nbsp;&nbsp;<img src='html/emoticons/{$r['image']}' name='emopreview' border='0'>",
		                                       $SKIN->form_dropdown('click', array(
		                                                                          0 => array(1, 'Yes'),
		                                                                          1 => array(0, 'No')
		                                                                     ), $r['clickable'])
		                                  ));

		$ADMIN->html .= $SKIN->end_form('Edit Emoticon');

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	//=====================================================

	function remove_emoticons()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		if ($IN['id'] == "")
		{
			$ADMIN->error("You must pass a valid emoticon id, silly!");
		}

		$ibforums->db->exec("DELETE FROM ibf_emoticons WHERE id='" . $IN['id'] . "'");

		$std->boink_it($SKIN->base_url . "&act=op&code=emo");
		exit();

	}

	//=====================================================

	function add_emoticons()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		if ($IN['before'] == "")
		{
			$ADMIN->error("You must enter an emoticon text to replace, silly!");
		}

		if (strstr($IN['before'], '&#092;'))
		{
			$ADMIN->error("You cannot use the backslash character in \"{$IN['before']}\". Please use another character");
		}

		$IN['click'] = $IN['click']
			? 1
			: 0;

		$data = [
			'typed'     => $IN['before'],
			'image'     => $IN['after'],
			'clickable' => $IN['click'],
		];

		$ibforums->db->insertRow("ibf_emoticons", $data);

		$std->boink_it($SKIN->base_url . "&act=op&code=emo");
		exit();

	}

	function perly_length_sort($a, $b)
	{
		if (strlen($a['typed']) == strlen($b['typed']))
		{
			return 0;
		}
		return (strlen($a['typed']) > strlen($b['typed']))
			? -1
			: 1;
	}

	function perly_word_sort($a, $b)
	{
		if (strlen($a['type']) == strlen($b['type']))
		{
			return 0;
		}
		return (strlen($a['type']) > strlen($b['type']))
			? -1
			: 1;
	}

	//=====================================================

	function upload_emoticon()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$FILE_NAME = $_FILES['FILE_UPLOAD']['name'];
		$FILE_SIZE = $_FILES['FILE_UPLOAD']['size'];
		$FILE_TYPE = $_FILES['FILE_UPLOAD']['type'];

		// Naughty Opera adds the filename on the end of the
		// mime type - we don't want this.

		$FILE_TYPE = preg_replace("/^(.+?);.*$/", "\\1", $FILE_TYPE);

		if (!is_dir($INFO['html_dir'] . 'emoticons'))
		{
			$ADMIN->error("Could not locate the emoticons directory - make sure the 'html_dir' path is set correctly");
		}

		// Naughty Mozilla likes to use "none" to indicate an empty upload field.
		// I love universal languages that aren't universal.

		if ($_FILES['FILE_UPLOAD']['name'] == "" or !$_FILES['FILE_UPLOAD']['name'] or ($_FILES['FILE_UPLOAD']['name'] == "none"))
		{
			$ADMIN->error("No file was chosen to upload!");
		}

		//-------------------------------------------------
		// Copy the upload to the uploads directory
		//-------------------------------------------------

		if (!@move_uploaded_file($_FILES['FILE_UPLOAD']['tmp_name'], $INFO['html_dir'] . 'emoticons' . "/" . $FILE_NAME))
		{
			$ADMIN->error("The upload failed");
		} else
		{
			@chmod($INFO['html_dir'] . 'emoticons' . "/" . $FILE_NAME, 0777);
		}

		$std->boink_it($SKIN->base_url . "&act=op&code=emo");
		exit();

	}

	function emoticons()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$ADMIN->page_detail = "You may add/edit or remove emoticons in this section.<br>You can only choose emoticons that have been uploaded into the 'html/emoticons' directory.<br><br>Clickable refers to emoticons that are in the posting screens 'Clickable Emoticons' table.";
		$ADMIN->page_title  = "Emoticon Control";

		//+-------------------------------

		$SKIN->td_header[] = array("Before", "30%");
		$SKIN->td_header[] = array("After", "30%");
		$SKIN->td_header[] = array("+ Clickable", "20%");
		$SKIN->td_header[] = array("Edit", "10%");
		$SKIN->td_header[] = array("Remove", "10%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Current Emoticons");

		$stmt = $ibforums->db->query("SELECT * from ibf_emoticons");

		$emo_url = $INFO['html_url'] . '/emoticons';

		$smilies = array();

		if ($stmt->rowCount())
		{
			while ($r = $stmt->fetch())
			{
				$smilies[] = $r;
			}

			usort($smilies, array('ad_settings', 'perly_length_sort'));

			foreach ($smilies as $array_idx => $r)
			{

				$click = $r['clickable']
					? 'Yes'
					: 'No';

				$ADMIN->html .= $SKIN->add_td_row(array(
				                                       stripslashes($r['typed']),
				                                       "<center><img src='$emo_url/{$r['image']}'></center>",
				                                       "<center>$click</center>",
				                                       "<center><a href='" . $SKIN->base_url . "&act=op&code=emo_edit&id={$r['id']}'>Edit</a></center>",
				                                       "<center><a href='" . $SKIN->base_url . "&act=op&code=emo_remove&id={$r['id']}'>Remove</a></center>",
				                                  ));

			}
		}

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------

		$emos = array();

		if (!is_dir($INFO['html_dir'] . 'emoticons'))
		{
			$ADMIN->error("Could not locate the emoticons directory - make sure the 'html_dir' path is set correctly");
		}

		//+-------------------------------

		$cnt   = 0;
		$start = "";

		$dh = opendir($INFO['html_dir'] . 'emoticons') or die("Could not open the emoticons directory for reading, check paths and permissions");
		while ($file = readdir($dh))
		{
			if (!preg_match("/^..?$|^index|htm$|html$|^\./i", $file))
			{
				$emos[] = array($file, $file);

				if ($cnt == 0)
				{
					$cnt   = 1;
					$start = $file;
				}
			}
		}
		closedir($dh);

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'emo_add'),
		                                       2 => array('act', 'op'),
		                                  ));

		$SKIN->td_header[] = array("Before", "40%");
		$SKIN->td_header[] = array("After", "40%");
		$SKIN->td_header[] = array("+ Clickable", "20%");

		//+-------------------------------

		$ADMIN->html .= "<script language='javascript'>
						 <!--
						 	function show_emo() {

						 		var emo_url = '{$INFO['html_url']}/emoticons/' + document.theAdminForm.after.options[document.theAdminForm.after.selectedIndex].value;

						 		document.images.emopreview.src = emo_url;
							}
						//-->
						</script>
						";

		$ADMIN->html .= $SKIN->start_table("Add a new Emoticon");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       $SKIN->form_input('before'),
		                                       $SKIN->form_dropdown('after', $emos, "", "onChange='show_emo()'") . "&nbsp;&nbsp;<img src='html/emoticons/$start' name='emopreview' border='0'>",
		                                       $SKIN->form_dropdown('click', array(
		                                                                          0 => array(1, 'Yes'),
		                                                                          1 => array(0, 'No')
		                                                                     ))
		                                  ));

		$ADMIN->html .= $SKIN->end_form('Add Emoticon');

		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------
		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'emo_upload'),
		                                       2 => array('act', 'op'),
		                                       3 => array('MAX_FILE_SIZE', '10000000000'),
		                                  ), "uploadform", " enctype='multipart/form-data'");

		$SKIN->td_header[] = array("&nbsp;", "40%");
		$SKIN->td_header[] = array("&nbsp;", "60%");

		$ADMIN->html .= $SKIN->start_table("Upload an Emoticon to the emoticons directory");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Choose a file from your computer to upload</b><br>After uploading, the emoticon will be selectable from the form above.",
		                                       $SKIN->form_upload(),
		                                  ));

		$ADMIN->html .= $SKIN->end_form('Upload Emoticon');

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	//-------------------------------------------------------------
	// BADWORD FUNCTIONS
	//--------------------------------------------------------------

	function doedit_badword()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		if ($IN['before'] == "")
		{
			$ADMIN->error("You must enter a word to replace, silly!");
		}

		if ($IN['id'] == "")
		{
			$ADMIN->error("You must pass a valid filter id, silly!");
		}

		$IN['match'] = $IN['match']
			? 1
			: 0;

		$IN['swop'] = strlen($IN['swop']) > 1
			? $IN['swop']
			: "";

		$data = [
			'type'    => $IN['before'],
			'swop'    => $IN['after'],
			'm_exact' => $IN['match'],
		];

		$ibforums->db->updateRow("ibf_badwords", array_map([$ibforums->db, 'quote'], $data), "wid='" . $IN['id'] . "'");

		$std->boink_it($SKIN->base_url . "&act=op&code=bw");
		exit();

	}

	//=====================================================

	function edit_badword()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$ADMIN->page_detail = "You may edit the chosen filter below";
		$ADMIN->page_title  = "Bad Word Filter";

		//+-------------------------------

		if ($IN['id'] == "")
		{
			$ADMIN->error("You must pass a valid filter id, silly!");
		}

		//+-------------------------------

		$stmt = $ibforums->db->query("SELECT * FROM ibf_badwords WHERE wid='" . $IN['id'] . "'");

		if (!$r = $stmt->fetch())
		{
			$ADMIN->error("We could not find that filter in the database");
		}

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'bw_doedit'),
		                                       2 => array('act', 'op'),
		                                       3 => array('id', $IN['id']),
		                                  ));

		$SKIN->td_header[] = array("Before", "40%");
		$SKIN->td_header[] = array("After", "40%");
		$SKIN->td_header[] = array("Method", "20%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Edit a filter");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       $SKIN->form_input('before', stripslashes($r['type'])),
		                                       $SKIN->form_input('after', stripslashes($r['swop'])),
		                                       $SKIN->form_dropdown('match', array(
		                                                                          0 => array(1, 'Exact'),
		                                                                          1 => array(0, 'Loose')
		                                                                     ), $r['m_exact'])
		                                  ));

		$ADMIN->html .= $SKIN->end_form('Edit Filter');

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	//=====================================================

	function remove_badword()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		if ($IN['id'] == "")
		{
			$ADMIN->error("You must pass a valid filter id, silly!");
		}

		$ibforums->db->exec("DELETE FROM ibf_badwords WHERE wid='" . $IN['id'] . "'");

		$std->boink_it($SKIN->base_url . "&act=op&code=bw");
		exit();

	}

	//=====================================================

	function add_badword()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		if ($IN['before'] == "")
		{
			$ADMIN->error("You must enter a word to replace, silly!");
		}
		$IN['match'] = $IN['match']
			? 1
			: 0;

		$IN['swop'] = strlen($IN['swop']) > 1
			? $IN['swop']
			: "";

		$data = [
			'type'    => $IN['before'],
			'swop'    => $IN['after'],
			'm_exact' => $IN['match'],
		];

		$ibforums->db->insertRow("ibf_badwords", $data);

		$std->boink_it($SKIN->base_url . "&act=op&code=bw");
		exit();

	}

	//=====================================================

	function badword()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$ADMIN->page_detail = "You can add/edit and remove bad word filters in this section.<br>The badword filter allows you to globally replace words from a members post, signature and topic title.<br><br><b>Loose matching</b>: If you entered 'hell' as a bad word, it will replace 'hell' and 'hello' with either your replacement if entered or 6 hashes (case insensitive)<br><br><b>Exact matching</b>: If you entered 'hell' as a bad word, it will replace 'hell' only with either your replacement if entered or 6 hashes (case insensitive)";
		$ADMIN->page_title  = "Bad Word Filter";

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', 'bw_add'),
		                                       2 => array('act', 'op'),
		                                  ));

		//+-------------------------------

		$SKIN->td_header[] = array("Before", "30%");
		$SKIN->td_header[] = array("After", "30%");
		$SKIN->td_header[] = array("Method", "20%");
		$SKIN->td_header[] = array("Edit", "10%");
		$SKIN->td_header[] = array("Remove", "10%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Current Filters");

		$stmt = $ibforums->db->query("SELECT * from ibf_badwords");

		if ($stmt->rowCount())
		{
			while ($r = $stmt->fetch())
			{
				$words[] = $r;
			}

			usort($words, array('ad_settings', 'perly_word_sort'));

			foreach ($words as $idx => $r)
			{

				$replace = $r['swop']
					? stripslashes($r['swop'])
					: '######';

				$method = $r['m_exact']
					? 'Exact'
					: 'Loose';

				$ADMIN->html .= $SKIN->add_td_row(array(
				                                       stripslashes($r['type']),
				                                       $replace,
				                                       $method,
				                                       "<center><a href='" . $SKIN->base_url . "&act=op&code=bw_edit&id={$r['wid']}'>Edit</a></center>",
				                                       "<center><a href='" . $SKIN->base_url . "&act=op&code=bw_remove&id={$r['wid']}'>Remove</a></center>",
				                                  ));
			}

		}

		$ADMIN->html .= $SKIN->end_table();

		$SKIN->td_header[] = array("Before", "40%");
		$SKIN->td_header[] = array("After", "40%");
		$SKIN->td_header[] = array("Method", "20%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Add a new filter");

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       $SKIN->form_input('before'),
		                                       $SKIN->form_input('after'),
		                                       $SKIN->form_dropdown('match', array(
		                                                                          0 => array(1, 'Exact'),
		                                                                          1 => array(0, 'Loose')
		                                                                     ))
		                                  ));

		$ADMIN->html .= $SKIN->end_form('Add Filter');

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	//-------------------------------------------------------------
	// NEWS
	//--------------------------------------------------------------

	function news()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$this->common_header('donews', 'News Export Set-Up', 'You may change the configuration below');

		$stmt = $ibforums->db->query("SELECT id, name FROM ibf_forums ORDER BY name");

		$form_array = array();

		while ($r = $stmt->fetch())
		{
			$form_array[] = array($r['id'], $r['name']);
		}

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Export news topics from which forum?</b>",
		                                       $SKIN->form_dropdown("news_forum_id", $form_array, $INFO['news_forum_id'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Show a 'Latest News' link on the board index?</b>",
		                                       $SKIN->form_yes_no("index_news_link", $INFO['index_news_link'])
		                                  ));

		$this->common_footer();

	}

	//-------------------------------------------------------------
	// PM
	//--------------------------------------------------------------

	function pm()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$this->common_header('dopm', 'Messenger Set up', 'You may change the configuration below');

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow IBF Code in messages?</b>",
		                                       $SKIN->form_yes_no("msg_allow_code", $INFO['msg_allow_code'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow HTML in messages?</b>",
		                                       $SKIN->form_yes_no("msg_allow_html", $INFO['msg_allow_html'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Max. number of messages to show per page when viewing message list</b><br>Default is 50",
		                                       $SKIN->form_input("show_max_msg_list", $INFO['show_max_msg_list'])
		                                  ));

		$this->common_footer();

	}

	//-------------------------------------------------------------
	// EMAIL functions
	//--------------------------------------------------------------

	function email()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$this->common_header('doemail', 'Email Set Up', 'You may change the configuration below');

		$ADMIN->html .= $SKIN->add_td_basic('Email Addresses', 'left', 'catrow2');

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Board incoming email address</b>",
		                                       $SKIN->form_input("email_in", $INFO['email_in'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Board outgoing email address</b>",
		                                       $SKIN->form_input("email_out", $INFO['email_out'])
		                                  ));

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_basic('Mail Method', 'left', 'catrow2');

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Mail Method</b><br>If PHP's mail() isn't available, choose SMTP",
		                                       $SKIN->form_dropdown("mail_method", array(
		                                                                                0 => array(
			                                                                                'mail',
			                                                                                'PHP mail()'
		                                                                                ),
		                                                                                1 => array('smtp', 'SMTP'),
		                                                                           ), $INFO['mail_method'])
		                                  ));

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_basic('SMTP Options', 'left', 'catrow2');

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Over-ride SMTP Host?</b><br>Default is 'localhost'",
		                                       $SKIN->form_input("smtp_host", $INFO['smtp_host'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Over-ride SMTP Port?</b><br>Default is 25",
		                                       $SKIN->form_input("smtp_port", $INFO['smtp_port'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>SMTP UserName</b><br>Not required in most cases when using 'localhost'",
		                                       $SKIN->form_input("smtp_user", $INFO['smtp_user'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>SMTP Password</b><br>Not required in most cases when using 'localhost'",
		                                       $SKIN->form_input("smtp_pass", $INFO['smtp_pass'], 'password')
		                                  ));

		$this->common_footer();

	}

	//-------------------------------------------------------------
	// URLs and ADDRESSES
	//--------------------------------------------------------------

	function url()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$this->common_header('dourl', 'Global Set Up', 'You may change the configuration below');

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_basic('Board Name and HTTP addresses', 'left', 'catrow2');

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Board Name</b>",
		                                       $SKIN->form_input("board_name", $INFO['board_name'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Board Address</b>",
		                                       $SKIN->form_input("board_url", $INFO['board_url'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Website Name</b>",
		                                       $SKIN->form_input("home_name", $INFO['home_name'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Website Address</b>",
		                                       $SKIN->form_input("home_url", $INFO['home_url'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>HTML URL</b><br>For images, etc",
		                                       $SKIN->form_input("html_url", $INFO['html_url'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Upload URL</b>",
		                                       $SKIN->form_input("upload_url", $INFO['upload_url'])
		                                  ));

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_basic('Board Server Paths', 'left', 'catrow2');

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Path to 'html' directory</b><br>Note: this is a path, not a URL",
		                                       $SKIN->form_input("html_dir", $INFO['html_dir'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Upload Directory</b>",
		                                       $SKIN->form_input("upload_dir", $INFO['upload_dir'])
		                                  ));

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_basic('HTTP Environment', 'left', 'catrow2');

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Print HTTP headers?</b><br>(Some NT installs require this off)",
		                                       $SKIN->form_yes_no("print_headers", $INFO['print_headers'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Hide Session ID?</b><br>(Removes Session ID from the URL)",
		                                       $SKIN->form_yes_no("session_hide", $INFO['session_hide'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b><i>DISABLE</I> GZIP encoding?</b><br>(GZIP enables faster page transfer and lower bandwidth use)",
		                                       $SKIN->form_yes_no("disable_gzip", $INFO['disable_gzip'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Type of auto-redirect?</b><br>(This is for quick no page redirects)",
		                                       $SKIN->form_dropdown('header_redirect', array(
		                                                                                    0 => array(
			                                                                                    'location',
			                                                                                    'Location type (*nix savvy)'
		                                                                                    ),
		                                                                                    1 => array(
			                                                                                    'refresh',
			                                                                                    'Refresh (Windows savvy)'
		                                                                                    ),
		                                                                                    2 => array(
			                                                                                    'html',
			                                                                                    'HTML META redirect (If all else fails...)'
		                                                                                    ),
		                                                                               ), $INFO['header_redirect'])
		                                  ));

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_basic('Debugging', 'left', 'catrow2');

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Debug level</b>",
		                                       $SKIN->form_dropdown("debug_level", array(
		                                                                                0 => array(
			                                                                                0,
			                                                                                '0: None - Don\'t show any debug information'
		                                                                                ),
		                                                                                1 => array(
			                                                                                1,
			                                                                                '1: Show server load, page generation times and query count'
		                                                                                ),
		                                                                                2 => array(
			                                                                                2,
			                                                                                '2: Show level 1 (above) and GET and POST information'
		                                                                                ),
		                                                                                3 => array(
			                                                                                3,
			                                                                                '3: Show level 1 + 2 and database queries'
		                                                                                ),
		                                                                           ), $INFO['debug_level'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b><i>ENABLE</I> SQL Debug Mode?</b><br>(If yes, add '&debug=1' to any page to view mySQL debug info)",
		                                       $SKIN->form_yes_no("sql_debug", $INFO['sql_debug'])
		                                  ));

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_basic('Отсылка ПМ новым пользователям', 'left', 'catrow2');

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Отсылать ПМ новым пользователям?</b><br>(Если Да, то новые пользователи после регистрации будут получать ПМ с Вашим приветствием)",
		                                       $SKIN->form_yes_no("auto_pm_on", $INFO['auto_pm_on'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>От кого посылать сообщение??</b><br>(ID пользователя, от которого будет отослано ПМ)<br>(Например, 1 - ID администратора)",
		                                       $SKIN->form_input("auto_pm_from", $INFO['auto_pm_from'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Заголовок</b><br>(Заголовок отсылаемого сообщения)",
		                                       $SKIN->form_input("auto_pm_subject", $INFO['auto_pm_subject'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Содержание сообщения</b><br>(Используйте *username*, для обращения к новому пользователю.)<br>(*username* будет заменено на имя пользователя )",
		                                       $SKIN->form_textarea("auto_pm_message", $INFO['auto_pm_message'])
		                                  ));

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_basic('Global Skin Settings', 'left', 'catrow2');

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Use safe mode skins?</b><br>(Note: You may need to resynchronise your template sets after changing this if you have custom/edited skins)",
		                                       $SKIN->form_dropdown('safe_mode_skins', array(
		                                                                                    0 => array('0', 'No'),
		                                                                                    1 => array('1', 'Yes'),
		                                                                               ), $INFO['safe_mode_skins'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Number Formatting</b><br>You may choose which character to separate thousands from hundreds<br>(EG: USA & UK use a comma)",
		                                       $SKIN->form_dropdown('number_format', array(
		                                                                                  0 => array(
			                                                                                  'none',
			                                                                                  'Don\'t format'
		                                                                                  ),
		                                                                                  1 => array('space', 'Space'),
		                                                                                  2 => array(',', ','),
		                                                                                  3 => array('.', '.'),
		                                                                             ), $INFO['number_format'])
		                                  ));
		$ADMIN->html .= $SKIN->add_td_basic('Other', 'left', 'catrow2');

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Версия клиентских скриптов</b><br>(Измените это значение при обновлении скриптов, чтобы избежать проблем с кешированием у пользователей)",
		                                       $SKIN->form_input("client_script_version", $INFO['client_script_version'])
		                                  ));

		$this->common_footer();

	}

	//-------------------------------------------------------------
	// CPU SAVING
	//--------------------------------------------------------------

	function cpu()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$this->common_header('docpu', 'CPU Saving', 'You can opt to turn some features off to minimize the resource footprint');

		if ($INFO['au_cutoff'] == "")
		{
			$INFO['au_cutoff'] = 15;
		}

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_basic('SQL Savings', 'left', 'catrow2');

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Show Active Users?</b>",
		                                       $SKIN->form_yes_no("show_active", $INFO['show_active'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Cut off for active user display in minutes</b>",
		                                       $SKIN->form_input("au_cutoff", $INFO['au_cutoff'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Show Birthdays?</b>",
		                                       $SKIN->form_yes_no("show_birthdays", $INFO['show_birthdays'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Show Board Totals?</b>",
		                                       $SKIN->form_yes_no("show_totals", $INFO['show_totals'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow custom profile field info be used in TopicView?</b>",
		                                       $SKIN->form_yes_no("custom_profile_topic", $INFO['custom_profile_topic'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Mark topics a user has posted when displaying a forum?</b>",
		                                       $SKIN->form_yes_no("show_user_posted", $INFO['show_user_posted'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Remove 'Users Browsing this <u>forum</u>' feature?</b><br>(This save 1 query per forum view)",
		                                       $SKIN->form_yes_no("no_au_forum", $INFO['no_au_forum'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Remove 'Users Browsing this <u>topic</u>' feature?</b><br>(This save 1 query per topic view)",
		                                       $SKIN->form_yes_no("no_au_topic", $INFO['no_au_topic'])
		                                  ));

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_basic('CPU Savings', 'left', 'catrow2');

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Server Load Limit</b><br>Will display 'busy' message when limit hit<br>Can be left blank for no limit",
		                                       $SKIN->form_input("load_limit", $INFO['load_limit'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow users (where allowed) to use search?</b>",
		                                       $SKIN->form_yes_no("allow_search", $INFO['allow_search'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Cut search post to [x] characters</b><br>Refers to when returning search results as posts<br>Leave blank to return full post with proper formatting",
		                                       $SKIN->form_input("search_post_cut", $INFO['search_post_cut'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Minimum search word length</b><br>Allowing shorter search words can return more results, such as 'if', 'at', etc",
		                                       $SKIN->form_input("min_search_word", $INFO['min_search_word']) . "<br>Note, if you have enabled full text searching, the minimum is 4 characters and cannot be changed via IPB"
		                                  ));

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_basic('Bandwidth Savings', 'left', 'catrow2');

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Print HTTP no-cache headers?</b><br>(This will stop browsers caching pages)",
		                                       $SKIN->form_yes_no("nocache", $INFO['nocache'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Show short forum jump list?</b><br>This will remove sub-forums from the drop down list - useful if you have many",
		                                       $SKIN->form_yes_no("short_forum_jump", $INFO['short_forum_jump'])
		                                  ));

		$this->common_footer();

	}

	//-------------------------------------------------------------
	// DATES
	//--------------------------------------------------------------

	function dates()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$this->common_header('dodates', 'Dates', 'Define date formats');

		$time_array = array();

		require ROOT_PATH . "lang/en/lang_ucp.php";

		foreach ($ibforums->lang as $off => $words)
		{
			if (preg_match("/^time_(\S+)$/", $off, $match))
			{
				$time_select[] = array($match[1], $words);
			}
		}

		$d_date = $std->get_date(time(), 'LONG');

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Native Server Time Zone</b><br><span style='color:red'>If you have chosen the correct timezone and the clock is an hour out, this is because of daylight savings time and your members can correct this by editing their 'Board settings' via their User Control Panel.</span>",
		                                       $SKIN->form_dropdown("time_offset", $time_select, $INFO['time_offset'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Server Time Adjustment (in minutes)</b><br>You fine tune the server time. If you need to subtract minutes from the server time, start the number with a '-' (no quotes).",
		                                       $SKIN->form_input("time_adjust", $INFO['time_adjust']) . "<br>Board time (inc. above time zone and current adj.) is now: $d_date"
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Short time format</b><br>Same configuration as <a href='http://www.php.net/date' target='_blank'>PHP Date</a>",
		                                       $SKIN->form_input("clock_short", $INFO['clock_short'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Join date time format</b><br>Same configuration as <a href='http://www.php.net/date' target='_blank'>PHP Date</a>",
		                                       $SKIN->form_input("clock_joined", $INFO['clock_joined'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Long time format</b><br>Same configuration as <a href='http://www.php.net/date' target='_blank'>PHP Date</a>",
		                                       $SKIN->form_input("clock_long", $INFO['clock_long'])
		                                  ));

		$this->common_footer();

	}

	//-------------------------------------------------------------
	// AVATARS
	//--------------------------------------------------------------

	function avatars()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$this->common_header('doavatars', 'User Profiles', 'Define user profile permissions');

		$INFO['avatar_ext'] = preg_replace("/\|/", ",", $INFO['avatar_ext']);
		$INFO['photo_ext']  = preg_replace("/\|/", ",", $INFO['photo_ext']);

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_basic('User Profiles & Options', 'left', 'catrow2');

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow members to choose skins?</b>",
		                                       $SKIN->form_yes_no("allow_skins", $INFO['allow_skins'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Number of posts a member must have over before allowing them to change their member title?</b><br>Leave blank to disable completely",
		                                       $SKIN->form_input("post_titlechange", $INFO['post_titlechange'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Maximum length (in bytes) for the location field entry</b>",
		                                       $SKIN->form_input("max_location_length", $INFO['max_location_length'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Maximum length (in bytes) for the interests field entry</b>",
		                                       $SKIN->form_input("max_interest_length", $INFO['max_interest_length'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Maximum length (in bytes) for user signatures</b>",
		                                       $SKIN->form_input("max_sig_length", $INFO['max_sig_length'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow HTML in signatures?</b>",
		                                       $SKIN->form_yes_no("sig_allow_html", $INFO['sig_allow_html'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow IBF Code in signatures?</b>",
		                                       $SKIN->form_yes_no("sig_allow_ibc", $INFO['sig_allow_ibc'])
		                                  ));

		if ($INFO['postpage_contents'] == "")
		{
			$INFO['postpage_contents'] = '5,10,15,20,25,30,35,40';
		}

		if ($INFO['topicpage_contents'] == "")
		{
			$INFO['topicpage_contents'] = '5,10,15,20,25,30,35,40';
		}

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>User selectable posts per page dropdown contents</b><br>Separate with a comma, 'Use forum default' added automatically<br>Example: 5,15,20,25,30",
		                                       $SKIN->form_input("postpage_contents", $INFO['postpage_contents'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>User selectable topics per forum page dropdown contents</b><br>Separate with a comma, 'Use forum default' added automatically<br>Example: 5,15,20,25,30",
		                                       $SKIN->form_input("topicpage_contents", $INFO['topicpage_contents'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Auto prune all topic subscriptions if the topic has no replies over [x] days</b><br>Leave blank for no auto prune limit",
		                                       $SKIN->form_input("subs_autoprune", $INFO['subs_autoprune'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allowed photo URL extensions</b><br>Seperate with comma (gif,png,jpeg) etc",
		                                       $SKIN->form_input("photo_ext", strlen($INFO['photo_ext']) > 1
			                                       ? $INFO['photo_ext']
			                                       : "gif,jpg,jpeg,png")
		                                  ));

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_basic('Avatars', 'left', 'catrow2');

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Disable IPB auto sizing large photos/avatars?</b><br/ >This will ask the member to input their dimensions",
		                                       $SKIN->form_yes_no("disable_ipbsize", $INFO['disable_ipbsize'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow the use of avatars?</b>",
		                                       $SKIN->form_yes_no("avatars_on", $INFO['avatars_on'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allowed image extensions</b><br>Seperate with comma (gif,png,jpeg) etc",
		                                       $SKIN->form_input("avatar_ext", $INFO['avatar_ext'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow users to use remote URL avatars?</b>",
		                                       $SKIN->form_yes_no("avatar_url", $INFO['avatar_url'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Max. file size for avatar uploads? (K)</b>",
		                                       $SKIN->form_input("avup_size_max", $INFO['avup_size_max'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Maximum avatar dimensions</b><br>(WIDTH<b>x</b>HEIGHT)",
		                                       $SKIN->form_input("avatar_dims", $INFO['avatar_dims'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Default sizes for gallery avatars</b><br>(WIDTH<b>x</b>HEIGHT)",
		                                       $SKIN->form_input("avatar_def", $INFO['avatar_def'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Number of columns for the avatar gallery?</b>",
		                                       $SKIN->form_input('av_gal_cols', $INFO['av_gal_cols'] = $INFO['av_gal_cols']
			                                       ? $INFO['av_gal_cols']
			                                       : 5)
		                                  ));

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_basic('Guest Permissions', 'left', 'catrow2');

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow GUESTS to view signatures?</b>",
		                                       $SKIN->form_yes_no("guests_sig", $INFO['guests_sig'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow GUESTS to view posted images?</b>",
		                                       $SKIN->form_yes_no("guests_img", $INFO['guests_img'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow GUESTS to view user avatars?</b>",
		                                       $SKIN->form_yes_no("guests_ava", $INFO['guests_ava'])
		                                  ));

		$this->common_footer();

	}

	//-------------------------------------------------------------
	// TOPICS and POSTS
	//--------------------------------------------------------------

	function post()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$INFO['img_ext'] = preg_replace("/\|/", ",", $INFO['img_ext']);

		$this->common_header('dopost', 'Topics, Posts and Posting', 'Configure the viewable post elements and limits.');

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_basic('Topics', 'left', 'catrow2');

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Number of topics per forum page</b>",
		                                       $SKIN->form_input("display_max_topics", $INFO['display_max_topics'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Number of posts needed to make a 'hot topic'?</b>",
		                                       $SKIN->form_input("hot_topic", $INFO['hot_topic'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Topic prefix for PINNED topics</b>",
		                                       $SKIN->form_input("pre_pinned", $INFO['pre_pinned'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Topic prefix for MOVED topics</b>",
		                                       $SKIN->form_input("pre_moved", $INFO['pre_moved'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Topic prefix for POLLS</b>",
		                                       $SKIN->form_input("pre_polls", $INFO['pre_polls'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Topic prefix for club topics</b>",
		                                       $SKIN->form_input("pre_club", $INFO['pre_club'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Stop shouting in topic titles?</b><br>(Will turn: CLICK HERE into Click Here)",
		                                       $SKIN->form_yes_no("etfilter_shout", $INFO['etfilter_shout'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Remove excess exclamation/question marks in topic titles?</b><br>(Will turn: This!!!!! into This!)",
		                                       $SKIN->form_yes_no("etfilter_punct", $INFO['etfilter_punct'])
		                                  ));

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_basic('Posts & Posting', 'left', 'catrow2');

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Number of posts per topic page</b>",
		                                       $SKIN->form_input("display_max_posts", $INFO['display_max_posts'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>No. emoticons per clickable table row</b>",
		                                       $SKIN->form_input("emo_per_row", $INFO['emo_per_row'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Max. no. emoticons per post</b>",
		                                       $SKIN->form_input("max_emos", $INFO['max_emos'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Max. no. images per post</b>",
		                                       $SKIN->form_input("max_images", $INFO['max_images'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Max. size of post (in kilobytes [kb])</b>",
		                                       $SKIN->form_input("max_post_length", $INFO['max_post_length'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Max. width of posted Flash movies (in pixels)</b>",
		                                       $SKIN->form_input("max_w_flash", $INFO['max_w_flash'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Max. height of posted Flash movies (in pixels)</b>",
		                                       $SKIN->form_input("max_h_flash", $INFO['max_h_flash'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Valid postable image extensions</b><br>(Seperate with comma (gif,jpeg,jpg) etc",
		                                       $SKIN->form_input("img_ext", $INFO['img_ext'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Show uploaded images in post?</b>",
		                                       $SKIN->form_yes_no("show_img_upload", $INFO['show_img_upload']) . "<br />" . $SKIN->form_checkbox('siu_thumb', $INFO['siu_thumb']) . "Show Thumb? Size " . $SKIN->form_simple_input('siu_width', $INFO['siu_width']) . " x " . $SKIN->form_simple_input('siu_height', $INFO['siu_height'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Stop Quote Embedding?</b><br>This will remove any quoted text when quoting a post that contains quotes<br><a href='#' title='and if that made any sense, then you have a larger IQ than me.'>..</a>",
		                                       $SKIN->form_yes_no("strip_quotes", $INFO['strip_quotes'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Guest names <i>prefix</i></b><br>(This is for when a guest posts with a members name, it allows for a visual difference to prevent confusion)",
		                                       $SKIN->form_input("guest_name_pre", $INFO['guest_name_pre'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Guest names <i>suffix</i></b><br>(This is for when a guest posts with a members name, it allows for a visual difference to prevent confusion)",
		                                       $SKIN->form_input("guest_name_suf", $INFO['guest_name_suf'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>No. characters to word wrap on?</b><br>Prevents long unbroken words which distort tables. 80 - 100 is recommended",
		                                       $SKIN->form_input("post_wordwrap", $INFO['post_wordwrap'])
		                                  ));

		//-----------------------------------------------------------------------------------------------------------

		//-- mod_global_poll begin
		if (file_exists(ROOT_PATH . "sources/mods/global_poll/mod_global_poll_adm.php"))
		{
			require ROOT_PATH . "sources/mods/global_poll/mod_global_poll_adm.php";
		} else
		{
			die("Could not call required function from file 'sources/mods/global_poll/mod_global_poll_adm.php'<br>Does it exist?");
		}
		$adminGlobalPoll = new AdminGlobalPoll;
		$adminGlobalPoll->ad_settings();
		//-- mod_global_poll end

		$ADMIN->html .= $SKIN->add_td_basic('Polls', 'left', 'catrow2');

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow [IMG] and [URL] tags in polls?</b>",
		                                       $SKIN->form_yes_no("poll_tags", $INFO['poll_tags'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Maximum number of poll choices allowed?</b><br>",
		                                       $SKIN->form_input("max_poll_choices", $INFO['max_poll_choices']
			                                       ? $INFO['max_poll_choices']
			                                       : 10)
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow members to view the results of a poll without losing their vote?</b>",
		                                       $SKIN->form_yes_no('allow_result_view', $INFO['allow_result_view'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>DISABLE the ability for members to post a 'no-reply' poll?</b>",
		                                       $SKIN->form_yes_no('poll_disable_noreply', $INFO['poll_disable_noreply'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Number of hours to keep open the ability for topic starters to attach a poll to their topic</b><br>Does not affect admins or super moderators",
		                                       $SKIN->form_input('startpoll_cutoff', $INFO['startpoll_cutoff']
			                                       ? $INFO['startpoll_cutoff']
			                                       : 24)
		                                  ));

		$this->common_footer();

	}

	//-------------------------------------------------------------
	// SECURITY
	//--------------------------------------------------------------

	function secure()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$this->common_header('dosecure', 'Security', 'Define the level of security your board possess by using the configurations below');

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_basic('Security (Script/Bot Flood Control)', 'left', 'catrow2');

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Enable Script/Bot Flood Control?</b><br>Forces users to input a random code when registering and changing email address, etc to prevent bot's from spamming the forms." . $SKIN->js_help_link('s_reg_antispam'),
		                                       $SKIN->form_dropdown("bot_antispam", array(
		                                                                                 0 => array(
			                                                                                 '0',
			                                                                                 'None'
		                                                                                 ),
		                                                                                 1 => array(
			                                                                                 'gd',
			                                                                                 'Advanced (Requires GD Library)'
		                                                                                 ),
		                                                                                 2 => array(
			                                                                                 'gif',
			                                                                                 'Normal (No special requirements)'
		                                                                                 ),
		                                                                            ), $INFO['bot_antispam'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>IF using GD; Use [YES] TTF method, or [NO] basic method?</b><br />TTF is best if available",
		                                       $SKIN->form_yes_no("use_ttf", isset($INFO['use_ttf'])
			                                       ? $INFO['use_ttf']
			                                       : 1)
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>IF using GD & TTF; Image WIDTH</b>",
		                                       $SKIN->form_input("gd_width", isset($INFO['gd_width'])
			                                       ? $INFO['gd_width']
			                                       : 250)
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>IF using GD & TTF; Image HEIGHT</b>",
		                                       $SKIN->form_input("gd_height", isset($INFO['gd_height'])
			                                       ? $INFO['gd_height']
			                                       : 70)
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>IF using GD & TTF; Path to used .ttf</b>",
		                                       $SKIN->form_input("gd_font", isset($INFO['gd_font'])
			                                       ? $INFO['gd_font']
			                                       : getcwd() . '/fonts/progbot.ttf')
		                                  ));

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_basic('Security (High)', 'left', 'catrow2');

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow dynamic images?</b><br>If 'yes' users can post scripted image generators",
		                                       $SKIN->form_yes_no("allow_dynamic_img", $INFO['allow_dynamic_img'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Session Expiration (in seconds)</b><br>Removes inactive sessions over the limit you specify",
		                                       $SKIN->form_input("session_expiration", $INFO['session_expiration'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Match users browsers while validating?</b>",
		                                       $SKIN->form_yes_no("match_browser", $INFO['match_browser'])
		                                  ));

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_basic('Security (Medium)', 'left', 'catrow2');

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Use secure mail form for member to member mails?</b><br>Hides users email addresses",
		                                       $SKIN->form_yes_no("use_mail_form", $INFO['use_mail_form'])
		                                  ));

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_basic('Security (Low)', 'left', 'catrow2');

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow images to be posted?</b><br>Advanced programmers can force images to run as scripts. IBF limits damage by this method however.",
		                                       $SKIN->form_yes_no("allow_images", $INFO['allow_images'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow flash movies in posts and avatars?</b><br>Flash has a built in scripting language which may or may not compromise security",
		                                       $SKIN->form_yes_no("allow_flash", $INFO['allow_flash'])
		                                  ));

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_basic('Security (Troublesome Users)', 'left', 'catrow2');

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow duplicate emails when user registers?</b><br>Will not check for existing email address",
		                                       $SKIN->form_yes_no("allow_dup_email", $INFO['allow_dup_email'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>New registration email validation?</b><br>Make admin manually preview all new accounts or make new users validate their email address",
		                                       $SKIN->form_dropdown("reg_auth_type", array(
		                                                                                  0 => array(
			                                                                                  'user',
			                                                                                  'User Email Validation'
		                                                                                  ),
		                                                                                  1 => array(
			                                                                                  'admin',
			                                                                                  'Admin Validation'
		                                                                                  ),
		                                                                                  2 => array('0', 'None')
		                                                                             ), $INFO['reg_auth_type'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Remove incomplete registration validations after...</b>",
		                                       $SKIN->form_simple_input('validate_day_prune', $INFO['validate_day_prune'], 3) . "... days"
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Get notified when a new user registers via email?</b>",
		                                       $SKIN->form_dropdown("new_reg_notify", array(
		                                                                                   0 => array('1', 'Yes'),
		                                                                                   1 => array('0', 'No')
		                                                                              ), $INFO['new_reg_notify'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Force guests to log in before allowing access to the board?</b>",
		                                       $SKIN->form_yes_no("force_login", $INFO['force_login'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Disable new registrations?</b>",
		                                       $SKIN->form_yes_no("no_reg", $INFO['no_reg'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Remove chr(0xCA) from input?</b><br />Can be used as a 'hidden' space to emulate registered names - but can cause problems in non Western character sets.",
		                                       $SKIN->form_yes_no('strip_space_chr', $INFO['strip_space_chr'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Disable 'Report this post to a moderator' link?</b>",
		                                       $SKIN->form_yes_no("disable_reportpost", $INFO['disable_reportpost'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Flood control delay (in seconds)</b><br>Make users wait before posting again<br>Can be left blank for no flood control",
		                                       $SKIN->form_input("flood_control", $INFO['flood_control'])
		                                  ));

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_basic('Privacy', 'left', 'catrow2');

		//-----------------------------------------------------------------------------------------------------------

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Allow users to browse the Active Users list?</b>",
		                                       $SKIN->form_yes_no("allow_online_list", $INFO['allow_online_list'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Disable root admin group viewing anonymous online users?</b><br>Anonymous users have an asterisk after their name",
		                                       $SKIN->form_yes_no("disable_admin_anon", $INFO['disable_admin_anon'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Disable root admin group viewing online users IP address in online user list?</b>",
		                                       $SKIN->form_yes_no("disable_online_ip", $INFO['disable_online_ip'])
		                                  ));

		$this->common_footer();

	}

	//-------------------------------------------------------------
	// COOKIES: Yum Yum!
	//--------------------------------------------------------------

	function cookie()
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$this->common_header('docookie', 'Cookies', 'All of these fields can be left blank. Experiment to find the correct settings for your host');

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Cookie Domain</b><br>Hint: use <b>.your-domain.com</b> for global cookies",
		                                       $SKIN->form_input("cookie_domain", $INFO['cookie_domain'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Cookie Name Prefix</b><br>Allows multiple boards on one host.",
		                                       $SKIN->form_input("cookie_id", $INFO['cookie_id'])
		                                  ));

		$ADMIN->html .= $SKIN->add_td_row(array(
		                                       "<b>Cookie Path</b><br>Relative path from domain to root IBF dir",
		                                       $SKIN->form_input("cookie_path", $INFO['cookie_path'])
		                                  ));

		$this->common_footer();

	}

	//-------------------------------------------------------------
	//
	// Save config. Does the hard work, so you don't have to.
	//
	//--------------------------------------------------------------

	function save_config($new)
	{
		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$master = array();

		if (is_array($new))
		{
			if (count($new) > 0)
			{
				foreach ($new as $field)
				{

					// Handle special..

					if ($field == 'img_ext' or $field == 'avatar_ext' or $field == 'photo_ext')
					{
						$_POST[$field] = preg_replace("/[\.\s]/", "", $_POST[$field]);
						$_POST[$field] = str_replace('|', "&#124;", $_POST[$field]);
						$_POST[$field] = preg_replace("/,/", '|', $_POST[$field]);
					} else
					{
						if ($field == 'coppa_address')
						{
							$_POST_VARS[$field] = nl2br($_POST[$field]);
						}
					}

					if ($field == 'gd_font' OR $field == 'html_dir' OR $field == 'upload_dir')
					{
						$_POST[$field] = preg_replace("/'/", "&#39;", $_POST[$field]);
					} else
					{
						$_POST[$field] = preg_replace("/'/", "&#39;", stripslashes($_POST[$field]));
					}

					$master[$field] = stripslashes($_POST[$field]);
				}

				$ADMIN->rebuild_config($master);
			}
		}

		$ADMIN->save_log("Board Settings Updated, Back Up Written");

		$ADMIN->done_screen("Forum Configurations updated", "Administration CP Home", "act=index");

	}

	//-------------------------------------------------------------
	//
	// Common header: Saves writing the same stuff out over and over
	//
	//--------------------------------------------------------------

	function common_header($formcode = "", $section = "", $extra = "")
	{

		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$extra = $extra
			? $extra . "<br>"
			: $extra;

		$ADMIN->page_detail = $extra . "Please check the data you are entering before submitting the changes";
		$ADMIN->page_title  = "Board Settings ($section)";

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form(array(
		                                       1 => array('code', $formcode),
		                                       2 => array('act', 'op'),
		                                  ));

		//+-------------------------------

		$SKIN->td_header[] = array("{none}", "40%");
		$SKIN->td_header[] = array("{none}", "60%");

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_table("Settings");

	}

	//-------------------------------------------------------------
	//
	// Common footer: Saves writing the same stuff out over and over
	//
	//--------------------------------------------------------------

	function common_footer($button = "Submit Changes")
	{

		global $IN, $INFO, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$ibforums = Ibf::app();

		$ADMIN->html .= $SKIN->end_form($button);

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	function return_sql_no_no_cant_do_it_sorry_text()
	{
		return "
<div style='line-height:150%'>
<span id='large'>Unable to automatically create the FULLTEXT indexes</span>
<br /><br />
You have too many posts for an automatic FULLTEXT index creation.
It is more than likely that PHP will time out before the indexes
are complete which could cause some index corruption.
<br />
Creating FULLTEXT indexes is a relatively slow process but it's
one that's worth doing as it will save you a lot of time and CPU
power when your members search.
<br />
On average, a normal webserver is capable of indexing about 80,000
posts an hour but it is a relatively intense process. If you
are using MySQL 4.0.12+ then this time is reduced substaintially.
<br />
<br />
<strong style='color:red;font-size:14px'>How to manually create the indexes</strong>
<br />
If you have shell (SSH / Telnet) access to mysql,
the process is very straightforward. If you do not have access to shell,
then you will have to contact your webhost and ask them to do this for you.
<br /><br />
<strong>Step 1: Initiate mysql</strong>
<br />
In shell type:
<br />
<pre>mysql -u{your_sql_user_name} -p{your_sql_password}</pre>
<br />
Your MySQL username and password can be found in your conf_global.php file
<br />
<br />
<strong>Step 2: Select your database</strong>
<br />
In mysql type:
<br />
<pre>use {your_database_name_here};</pre>
<br />
Make sure you use a trailing semi-colon.
Your MySQL database name can be found in conf_global.php
<br /><br />
<strong>Step 3: Indexing the topics table</strong>
<br />
In mysql type:
<br />
<pre>\g alter table ibf_topics add fulltext(title);</pre>
<br />
If you are not using 'ibf_' as your table extension, adjust that query to suit.
This query can take a while
depending on the number of topics you have.
<br />
<br />
<strong>Step 4: Indexing the posts table</strong>
<br />
In mysql type:
<br />
<pre>\g alter table ibf_posts add fulltext(post);</pre>
<br />
If you are not using 'ibf_' as your table extension, adjust that query to suit.
This query can take a while depending on the number of posts you have.
On average MySQL can index 80,000 posts an hour. If you are using MySQL 4,
the time is greatly reduced.
</div>
";
	}
}

?>
